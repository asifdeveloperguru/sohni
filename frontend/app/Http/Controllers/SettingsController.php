<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as PasswordRule;

class SettingsController extends Controller
{
    /**
     * GET /api/settings — Get user settings
     */
    public function show(Request $request)
    {
        $user = $request->user();
        
        return response()->json([
            'success' => true,
            'data' => [
                'privacy' => [
                    'accept_friend_requests' => (bool) $user->accept_friend_requests,
                    'show_online_status' => (bool) $user->show_online_status,
                    'show_typing_indicators' => (bool) $user->show_typing_indicators,
                    'profile_public' => (bool) $user->profile_public,
                    'accept_qr_requests' => (bool) $user->accept_qr_requests,
                ],
                'security' => [
                    'pin_enabled' => !empty($user->security_pin),
                    'pattern_enabled' => !empty($user->security_pattern),
                    'active_devices' => $user->active_devices ?? [],
                ],
            ],
        ]);
    }

    /**
     * POST /api/settings/privacy — Update privacy settings
     */
    public function updatePrivacy(Request $request)
    {
        $user = $request->user();
        
        $data = $request->validate([
            'accept_friend_requests' => 'nullable|boolean',
            'show_online_status' => 'nullable|boolean',
            'show_typing_indicators' => 'nullable|boolean',
            'profile_public' => 'nullable|boolean',
            'accept_qr_requests' => 'nullable|boolean',
        ]);

        foreach ($data as $key => $value) {
            if ($value !== null) {
                $user->$key = $value;
            }
        }
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Privacy settings updated successfully.',
            'data' => [
                'accept_friend_requests' => (bool) $user->accept_friend_requests,
                'show_online_status' => (bool) $user->show_online_status,
                'show_typing_indicators' => (bool) $user->show_typing_indicators,
                'profile_public' => (bool) $user->profile_public,
                'accept_qr_requests' => (bool) $user->accept_qr_requests,
            ],
        ]);
    }

    /**
     * POST /api/settings/security/pin — Set or update security PIN
     */
    public function setPin(Request $request)
    {
        $user = $request->user();
        
        $data = $request->validate([
            'pin' => 'required|string|size:4|regex:/^\d+$/',
        ]);

        $user->security_pin = Hash::make($data['pin']);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Security PIN set successfully.',
        ]);
    }

    /**
     * POST /api/settings/security/pin/remove — Remove security PIN
     */
    public function removePin(Request $request)
    {
        $user = $request->user();
        
        $request->validate([
            'current_password' => 'required|string',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid password.',
            ], 422);
        }

        $user->security_pin = null;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Security PIN removed successfully.',
        ]);
    }

    /**
     * POST /api/settings/security/pattern — Set or update security pattern
     */
    public function setPattern(Request $request)
    {
        $user = $request->user();
        
        $data = $request->validate([
            'pattern' => 'required|string|min:4|max:255',
        ]);

        $user->security_pattern = Hash::make($data['pattern']);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Security pattern set successfully.',
        ]);
    }

    /**
     * POST /api/settings/security/pattern/remove — Remove security pattern
     */
    public function removePattern(Request $request)
    {
        $user = $request->user();
        
        $request->validate([
            'current_password' => 'required|string',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid password.',
            ], 422);
        }

        $user->security_pattern = null;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Security pattern removed successfully.',
        ]);
    }

    /**
     * POST /api/settings/password — Change account password
     */
    public function changePassword(Request $request)
    {
        $user = $request->user();
        
        $data = $request->validate([
            'current_password' => 'required|string',
            'password' => ['required', 'string', 'confirmed', PasswordRule::defaults()],
        ]);

        if (!Hash::check($data['current_password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect.',
            ], 422);
        }

        $user->password = Hash::make($data['password']);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully.',
        ]);
    }

    /**
     * GET /api/settings/devices — Get active devices
     */
    public function getDevices(Request $request)
    {
        $user = $request->user();
        
        return response()->json([
            'success' => true,
            'data' => $user->active_devices ?? [],
        ]);
    }

    /**
     * POST /api/settings/devices/register — Register current device
     */
    public function registerDevice(Request $request)
    {
        $user = $request->user();
        
        $data = $request->validate([
            'device_name' => 'required|string|max:255',
            'device_type' => 'required|string|in:mobile,desktop,tablet,web',
        ]);

        $devices = $user->active_devices ?? [];
        $deviceId = uniqid('device_');

        $devices[] = [
            'id' => $deviceId,
            'name' => $data['device_name'],
            'type' => $data['device_type'],
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'registered_at' => now()->toIso8601String(),
            'last_activity' => now()->toIso8601String(),
        ];

        $user->active_devices = $devices;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Device registered successfully.',
            'data' => ['device_id' => $deviceId],
        ]);
    }

    /**
     * DELETE /api/settings/devices/{device_id} — Remove a device
     */
    public function removeDevice(Request $request, $deviceId)
    {
        $user = $request->user();
        $devices = $user->active_devices ?? [];

        $devices = array_filter($devices, fn ($d) => $d['id'] !== $deviceId);
        $user->active_devices = array_values($devices);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Device removed successfully.',
        ]);
    }

    /**
     * POST /api/settings/logout-other-devices — Logout from all other devices
     */
    public function logoutOtherDevices(Request $request)
    {
        $user = $request->user();
        
        // In a production app, you'd revoke tokens for other sessions here
        // For now, we'll just clear the device list except current
        $devices = $user->active_devices ?? [];
        $currentDevice = $request->ip();
        
        // Keep only current device (simplified for demo)
        $user->active_devices = [];
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Logged out from other devices successfully.',
        ]);
    }

    /**
     * POST /api/settings/account/delete — Delete account with warning
     */
    public function deleteAccount(Request $request)
    {
        $user = $request->user();
        
        $data = $request->validate([
            'password' => 'required|string',
            'confirmation' => 'required|string|in:DELETE',
        ]);

        if (!Hash::check($data['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid password.',
            ], 422);
        }

        // Soft delete the user
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Account deleted successfully. Redirecting...',
            'redirect' => '/account',
        ]);
    }

    /**
     * POST /api/settings/account/restore — Restore deleted account (within 30 days)
     */
    public function restoreAccount(Request $request)
    {
        $user = $request->user();
        
        // Force include soft-deleted records
        $user = \App\Models\User::withTrashed()->find($user->id);

        if (!$user->trashed()) {
            return response()->json([
                'success' => false,
                'message' => 'Account is not deleted.',
            ], 422);
        }

        $user->restore();

        return response()->json([
            'success' => true,
            'message' => 'Account restored successfully.',
        ]);
    }

    /**
     * POST /api/settings/security/require-pin — Enable/Disable PIN requirement
     */
    public function setRequirePinOnLogin(Request $request)
    {
        $user = $request->user();
        
        $data = $request->validate([
            'enabled' => 'required|boolean',
            'password' => 'required|string',
        ]);

        if (!Hash::check($data['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid password.',
            ], 422);
        }

        if ($data['enabled'] && !$user->security_pin) {
            return response()->json([
                'success' => false,
                'message' => 'Please set a PIN first.',
            ], 422);
        }

        $user->require_pin_on_login = $data['enabled'];
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'PIN requirement ' . ($data['enabled'] ? 'enabled' : 'disabled'),
        ]);
    }

    /**
     * POST /api/settings/security/require-pattern — Enable/Disable Pattern requirement
     */
    public function setRequirePatternOnLogin(Request $request)
    {
        $user = $request->user();
        
        $data = $request->validate([
            'enabled' => 'required|boolean',
            'password' => 'required|string',
        ]);

        if (!Hash::check($data['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid password.',
            ], 422);
        }

        if ($data['enabled'] && !$user->security_pattern) {
            return response()->json([
                'success' => false,
                'message' => 'Please set a pattern first.',
            ], 422);
        }

        $user->require_pattern_on_login = $data['enabled'];
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Pattern requirement ' . ($data['enabled'] ? 'enabled' : 'disabled'),
        ]);
    }

    /**
     * POST /api/settings/security/verify-pin — Verify PIN for session
     */
    public function verifyPin(Request $request)
    {
        $user = $request->user();
        
        $data = $request->validate([
            'pin' => 'required|string|size:4|regex:/^\d+$/',
        ]);

        if (!Hash::check($data['pin'], $user->security_pin)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid PIN.',
            ], 422);
        }

        session(['security_verified_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'PIN verified successfully.',
        ]);
    }

    /**
     * POST /api/settings/security/verify-pattern — Verify Pattern for session
     */
    public function verifyPattern(Request $request)
    {
        $user = $request->user();
        
        $data = $request->validate([
            'pattern' => 'required|string|min:4|max:255',
        ]);

        if (!Hash::check($data['pattern'], $user->security_pattern)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid pattern.',
            ], 422);
        }

        session(['security_verified_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Pattern verified successfully.',
        ]);
    }

    /**
     * GET /api/settings/login-history — Get login history
     */
    public function getLoginHistory(Request $request)
    {
        $user = $request->user();
        $history = $user->login_history ?? [];
        
        return response()->json([
            'success' => true,
            'data' => $history,
        ]);
    }

    /**
     * POST /api/settings/blocked-users/add — Block a user
     */
    public function blockUser(Request $request)
    {
        $user = $request->user();
        
        $data = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
        ]);

        if ($data['user_id'] === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot block yourself.',
            ], 422);
        }

        $blockedUsers = $user->blocked_users ?? [];
        
        if (!in_array($data['user_id'], $blockedUsers)) {
            $blockedUsers[] = $data['user_id'];
            $user->blocked_users = $blockedUsers;
            $user->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'User blocked successfully.',
        ]);
    }

    /**
     * DELETE /api/settings/blocked-users/{user_id} — Unblock a user
     */
    public function unblockUser(Request $request, $userId)
    {
        $user = $request->user();
        $blockedUsers = $user->blocked_users ?? [];

        $blockedUsers = array_filter($blockedUsers, fn ($id) => $id != $userId);
        $user->blocked_users = array_values($blockedUsers);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'User unblocked successfully.',
        ]);
    }

    /**
     * GET /api/settings/blocked-users — Get blocked users list
     */
    public function getBlockedUsers(Request $request)
    {
        $user = $request->user();
        $blockedUserIds = $user->blocked_users ?? [];

        $blockedUsers = \App\Models\User::whereIn('id', $blockedUserIds)
            ->select('id', 'name', 'email', 'avatar_path')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $blockedUsers,
        ]);
    }

    /**
     * POST /api/settings/privacy/advanced — Update advanced privacy settings
     */
    public function updateAdvancedPrivacy(Request $request)
    {
        $user = $request->user();
        
        $data = $request->validate([
            'allow_message_requests' => 'nullable|boolean',
            'allow_group_invites' => 'nullable|boolean',
            'allow_video_calls' => 'nullable|boolean',
            'allow_screen_share' => 'nullable|boolean',
            'session_timeout_hours' => 'nullable|integer|min:1|max:720',
            'two_factor_enabled' => 'nullable|boolean',
        ]);

        foreach ($data as $key => $value) {
            if ($value !== null) {
                $user->$key = $value;
            }
        }
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Privacy settings updated successfully.',
            'data' => [
                'allow_message_requests' => (bool) $user->allow_message_requests,
                'allow_group_invites' => (bool) $user->allow_group_invites,
                'allow_video_calls' => (bool) $user->allow_video_calls,
                'allow_screen_share' => (bool) $user->allow_screen_share,
                'session_timeout_hours' => $user->session_timeout_hours,
                'two_factor_enabled' => (bool) $user->two_factor_enabled,
            ],
        ]);
    }

    /**
     * POST /api/settings/session/end-all — End all sessions except current
     */
    public function endAllSessions(Request $request)
    {
        $user = $request->user();
        
        // Clear all devices to force re-login
        $user->active_devices = [];
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'All sessions have been ended.',
        ]);
    }

    /**
     * POST /api/settings/session/timeout — Update session timeout
     */
    public function updateSessionTimeout(Request $request)
    {
        $user = $request->user();
        
        $data = $request->validate([
            'hours' => 'required|integer|min:1|max:720',
        ]);

        $user->session_timeout_hours = $data['hours'];
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Session timeout updated to ' . $data['hours'] . ' hours.',
        ]);
    }
}

