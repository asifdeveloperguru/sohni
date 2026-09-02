/**
 * Mesh WebRTC calling for Sohni.
 *
 * Media is peer-to-peer and protected by DTLS-SRTP, so it is end-to-end encrypted
 * by construction: the server relays signaling only and never sees audio or video.
 *
 * Mesh scales as N(N-1)/2 connections, which is why participants are capped at 6.
 */
(function (global) {
    'use strict';

    const CONFIG = {
        maxBitrate: { audio: 32000, video: 900000 },
        // Ordered lowest-first so we can walk down when the network degrades.
        videoLadder: [150000, 400000, 900000],
    };

    class SohniCall {
        constructor(options) {
            this.csrf = options.csrf;
            this.echo = options.echo;
            this.onLocalStream = options.onLocalStream || (() => {});
            this.onRemoteStream = options.onRemoteStream || (() => {});
            this.onPeerLeft = options.onPeerLeft || (() => {});
            this.onStateChange = options.onStateChange || (() => {});
            this.onStats = options.onStats || (() => {});
            this.onError = options.onError || (() => {});

            this.roomId = null;
            this.myId = null;
            this.mode = 'video';
            this.iceServers = [];
            this.localStream = null;
            this.peers = new Map();
            this.channel = null;
            this.statsTimer = null;
        }

        async loadIceServers() {
            const res = await fetch('/api/calls/ice', { headers: { Accept: 'application/json' } });
            const json = await res.json();
            if (!json.success) throw new Error('Could not load ICE servers');
            this.iceServers = json.data.ice_servers;
            this.hasTurn = json.data.has_turn;
            return json.data;
        }

        /**
         * Browsers only expose good AEC/NS/AGC when these constraints are requested
         * explicitly; without them calls echo badly on laptop speakers.
         */
        async captureLocalMedia(mode) {
            const audio = {
                echoCancellation: true,
                noiseSuppression: true,
                autoGainControl: true,
                channelCount: 1,
                sampleRate: 48000,
            };

            const video = mode === 'video' ? {
                width: { ideal: 1280, max: 1280 },
                height: { ideal: 720, max: 720 },
                frameRate: { ideal: 30, max: 30 },
                facingMode: 'user',
            } : false;

            this.localStream = await navigator.mediaDevices.getUserMedia({ audio, video });
            this.onLocalStream(this.localStream);
            return this.localStream;
        }

        async start({ roomId, myId, mode }) {
            this.roomId = roomId;
            this.myId = myId;
            this.mode = mode;

            await this.loadIceServers();
            await this.captureLocalMedia(mode);
            this.subscribe();
            this.startStatsLoop();
        }

        subscribe() {
            this.channel = this.echo.join(`call.${this.roomId}`);

            this.channel
                .here(members => {
                    // Deterministic tie-break: the lower id offers, so two peers
                    // never generate competing offers for the same connection.
                    members
                        .filter(m => Number(m.id) !== this.myId)
                        .forEach(m => this.connectTo(Number(m.id), Number(m.id) > this.myId));
                    this.onStateChange('connected', members);
                })
                .joining(member => {
                    this.connectTo(Number(member.id), Number(member.id) > this.myId);
                    this.onStateChange('peer-joined', member);
                })
                .leaving(member => {
                    this.dropPeer(Number(member.id));
                    this.onStateChange('peer-left', member);
                })
                .error(err => this.onError(err));

            this.channel.listen('.signal', payload => {
                if (Number(payload.to) !== this.myId) return;
                this.handleSignal(Number(payload.from), payload.kind, payload.payload);
            });

            this.channel.listen('.state', payload => this.onStateChange(payload.action, payload.data));
        }

        createPeer(peerId) {
            const pc = new RTCPeerConnection({
                iceServers: this.iceServers,
                iceCandidatePoolSize: 4,
                bundlePolicy: 'max-bundle',
                rtcpMuxPolicy: 'require',
            });

            const state = {
                pc,
                makingOffer: false,
                ignoreOffer: false,
                polite: peerId < this.myId,
                stream: new MediaStream(),
                bitrateIndex: CONFIG.videoLadder.length - 1,
            };

            this.localStream.getTracks().forEach(track => pc.addTrack(track, this.localStream));

            pc.ontrack = event => {
                event.streams[0].getTracks().forEach(t => state.stream.addTrack(t));
                this.onRemoteStream(peerId, state.stream);
            };

            pc.onicecandidate = event => {
                if (event.candidate) this.send(peerId, 'candidate', event.candidate.toJSON());
            };

            pc.onnegotiationneeded = async () => {
                try {
                    state.makingOffer = true;
                    await pc.setLocalDescription();
                    this.send(peerId, 'offer', { sdp: pc.localDescription });
                } catch (e) {
                    this.onError(e);
                } finally {
                    state.makingOffer = false;
                }
            };

            pc.oniceconnectionstatechange = () => {
                if (pc.iceConnectionState === 'failed') pc.restartIce();
                if (pc.iceConnectionState === 'disconnected') {
                    setTimeout(() => {
                        if (pc.iceConnectionState === 'disconnected') pc.restartIce();
                    }, 2000);
                }
            };

            this.peers.set(peerId, state);
            return state;
        }

        async connectTo(peerId, shouldOffer) {
            if (this.peers.has(peerId)) return;
            const state = this.createPeer(peerId);

            if (shouldOffer) {
                try {
                    state.makingOffer = true;
                    await state.pc.setLocalDescription();
                    this.send(peerId, 'offer', { sdp: state.pc.localDescription });
                } catch (e) {
                    this.onError(e);
                } finally {
                    state.makingOffer = false;
                }
            }
        }

        /**
         * Perfect negotiation (W3C): resolves glare without dropping the connection.
         */
        async handleSignal(peerId, kind, payload) {
            let state = this.peers.get(peerId);
            if (!state) state = this.createPeer(peerId);

            const pc = state.pc;

            try {
                if (kind === 'offer' || kind === 'answer') {
                    const description = payload.sdp;
                    const offerCollision = description.type === 'offer'
                        && (state.makingOffer || pc.signalingState !== 'stable');

                    state.ignoreOffer = !state.polite && offerCollision;
                    if (state.ignoreOffer) return;

                    await pc.setRemoteDescription(description);

                    if (description.type === 'offer') {
                        await pc.setLocalDescription();
                        this.send(peerId, 'answer', { sdp: pc.localDescription });
                    }

                    await this.applyBitrateCaps(state);
                } else if (kind === 'candidate') {
                    try {
                        await pc.addIceCandidate(payload);
                    } catch (e) {
                        if (!state.ignoreOffer) throw e;
                    }
                }
            } catch (e) {
                this.onError(e);
            }
        }

        async applyBitrateCaps(state) {
            for (const sender of state.pc.getSenders()) {
                if (!sender.track) continue;
                const params = sender.getParameters();
                if (!params.encodings || !params.encodings.length) params.encodings = [{}];

                if (sender.track.kind === 'video') {
                    params.encodings[0].maxBitrate = CONFIG.videoLadder[state.bitrateIndex];
                    params.encodings[0].maxFramerate = 30;
                    params.degradationPreference = 'balanced';
                } else {
                    params.encodings[0].maxBitrate = CONFIG.maxBitrate.audio;
                }

                try { await sender.setParameters(params); } catch (e) { /* not fatal */ }
            }
        }

        /**
         * Steps video bitrate down on packet loss and back up when the link recovers,
         * which keeps audio intelligible instead of letting video starve it.
         */
        startStatsLoop() {
            this.statsTimer = setInterval(async () => {
                for (const [peerId, state] of this.peers) {
                    try {
                        const stats = await state.pc.getStats();
                        let loss = 0, rtt = 0, kbps = 0;

                        stats.forEach(report => {
                            if (report.type === 'remote-inbound-rtp') {
                                loss = report.fractionLost || 0;
                                rtt = (report.roundTripTime || 0) * 1000;
                            }
                            if (report.type === 'outbound-rtp' && report.kind === 'video') {
                                kbps = Math.round((report.targetBitrate || 0) / 1000);
                            }
                        });

                        const previous = state.bitrateIndex;
                        if (loss > 0.08 && state.bitrateIndex > 0) state.bitrateIndex--;
                        else if (loss < 0.02 && state.bitrateIndex < CONFIG.videoLadder.length - 1) state.bitrateIndex++;

                        if (previous !== state.bitrateIndex) await this.applyBitrateCaps(state);

                        this.onStats(peerId, {
                            loss: Math.round(loss * 100),
                            rtt: Math.round(rtt),
                            kbps,
                            quality: loss > 0.08 ? 'poor' : loss > 0.03 ? 'fair' : 'good',
                        });
                    } catch (e) { /* stats are best-effort */ }
                }
            }, 3000);
        }

        send(to, kind, payload) {
            return fetch(`/api/calls/${this.roomId}/signal`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': this.csrf,
                },
                body: JSON.stringify({ to, kind, payload }),
            }).catch(e => this.onError(e));
        }

        setMuted(muted) {
            this.localStream?.getAudioTracks().forEach(t => (t.enabled = !muted));
        }

        setCameraOff(off) {
            this.localStream?.getVideoTracks().forEach(t => (t.enabled = !off));
        }

        async shareScreen() {
            const display = await navigator.mediaDevices.getDisplayMedia({ video: true });
            const track = display.getVideoTracks()[0];

            for (const [, state] of this.peers) {
                const sender = state.pc.getSenders().find(s => s.track && s.track.kind === 'video');
                if (sender) await sender.replaceTrack(track);
            }

            track.onended = () => this.stopScreenShare();
            return display;
        }

        async stopScreenShare() {
            const camTrack = this.localStream?.getVideoTracks()[0];
            if (!camTrack) return;

            for (const [, state] of this.peers) {
                const sender = state.pc.getSenders().find(s => s.track && s.track.kind === 'video');
                if (sender) await sender.replaceTrack(camTrack);
            }
        }

        dropPeer(peerId) {
            const state = this.peers.get(peerId);
            if (!state) return;
            state.pc.close();
            this.peers.delete(peerId);
            this.onPeerLeft(peerId);
        }

        hangUp() {
            clearInterval(this.statsTimer);
            this.peers.forEach(state => state.pc.close());
            this.peers.clear();
            this.localStream?.getTracks().forEach(t => t.stop());
            if (this.channel) this.echo.leave(`call.${this.roomId}`);

            return fetch(`/api/calls/${this.roomId}/leave`, {
                method: 'POST',
                headers: { Accept: 'application/json', 'X-CSRF-TOKEN': this.csrf },
            }).catch(() => {});
        }
    }

    global.SohniCall = SohniCall;
})(window);
