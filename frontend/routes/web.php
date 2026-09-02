<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CallController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;
use App\Http\Middleware\RequireSecurityVerification;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — Sohni Chat App
|--------------------------------------------------------------------------
*/

// ============ Pages ============

Route::get('/', fn () => view('welcome'))->name('home');

Route::get('account', function () {
    if (auth()->check()) {
        return redirect('/dashboard');
    }
    return view('account');
})->name('account');

Route::middleware('auth')->group(function () {
    Route::get('verify-email', function () {
        $user = auth()->user();
        if ($user->email_verified_at) {
            return redirect($user->profile_completed_at ? '/dashboard' : '/profile-setup');
        }
        return view('verify-email');
    })->name('verify-email');

    Route::get('profile-setup', function () {
        $user = auth()->user();
        if (! $user->email_verified_at) {
            return redirect('/verify-email');
        }
        if ($user->profile_completed_at) {
            return redirect('/dashboard');
        }
        return view('profile-setup');
    })->name('profile-setup');

    Route::get('verify-security', function () {
        $user = auth()->user();
        if (! $user->email_verified_at) {
            return redirect('/verify-email');
        }
        if (! $user->profile_completed_at) {
            return redirect('/profile-setup');
        }
        if (! $user->require_pin_on_login && ! $user->require_pattern_on_login) {
            return redirect('/dashboard');
        }
        return view('verify-security');
    })->name('verify-security');

    // Protected pages requiring security verification
    Route::middleware(RequireSecurityVerification::class)->group(function () {
        Route::get('dashboard', function () {
            $user = auth()->user();
            if (! $user->email_verified_at) {
                return redirect('/verify-email');
            }
            if (! $user->profile_completed_at) {
                return redirect('/profile-setup');
            }
            return view('dashboard');
        })->name('dashboard');

        Route::get('profile', function () {
            $user = auth()->user();
            if (! $user->email_verified_at) {
                return redirect('/verify-email');
            }
            if (! $user->profile_completed_at) {
                return redirect('/profile-setup');
            }
            return view('profile');
        })->name('profile');

        Route::get('profile/download', [ProfileController::class, 'downloadProfile'])
            ->name('profile.download');

        Route::get('edit-profile', function () {
            $user = auth()->user();
            if (! $user->email_verified_at) {
                return redirect('/verify-email');
            }
            if (! $user->profile_completed_at) {
                return redirect('/profile-setup');
            }
            return view('edit-profile');
        })->name('edit-profile');

        Route::get('settings', function () {
            $user = auth()->user();
            if (! $user->email_verified_at) {
                return redirect('/verify-email');
            }
            if (! $user->profile_completed_at) {
                return redirect('/profile-setup');
            }
            return view('settings');
        })->name('settings');

        Route::get('chat', function () {
            $user = auth()->user();
            if (! $user->email_verified_at) {
                return redirect('/verify-email');
            }
            if (! $user->profile_completed_at) {
                return redirect('/profile-setup');
            }
            return view('chat');
        })->name('chat');

        Route::get('call', function () {
            $user = auth()->user();
            if (! $user->email_verified_at) {
                return redirect('/verify-email');
            }
            if (! $user->profile_completed_at) {
                return redirect('/profile-setup');
            }
            return view('call');
        })->name('call');
    });
});

// Email activation (signed, expiring links from the Gmail inbox)
Route::get('activate/{id}/{token}', [AuthController::class, 'showActivation'])
    ->name('activate.show')->middleware('signed');
Route::post('activate/{id}/{token}', [AuthController::class, 'activate'])
    ->name('activate.do')->middleware('signed');

// ============ API ============

Route::prefix('api')->group(function () {

    // Public auth endpoints
    Route::post('auth/signup', [AuthController::class, 'signup']);
    Route::post('auth/signin', [AuthController::class, 'signin']);

    // Authenticated endpoints
    Route::middleware('auth')->group(function () {
        Route::get('auth/verification-status', [AuthController::class, 'verificationStatus']);
        Route::post('auth/resend-verification', [AuthController::class, 'resendVerification']);
        Route::post('auth/logout', [AuthController::class, 'logout']);

        Route::post('profile/complete', [ProfileController::class, 'complete']);
        Route::get('profile', [ProfileController::class, 'show']);
        Route::post('profile/update', [ProfileController::class, 'update']);
        Route::get('sohni-ids/generate', [ProfileController::class, 'generateId']);
        Route::get('friends/recent', [ProfileController::class, 'recentFriends']);
        Route::get('followers/recent', [ProfileController::class, 'recentFollowers']);
        Route::get('friends/accepted', [ProfileController::class, 'acceptedFriends']);

        Route::get('chat/conversations', [ChatController::class, 'conversations']);
        Route::get('chat/conversation/{conversation}', [ChatController::class, 'conversation']);
        Route::get('chat/messages/{conversation}', [ChatController::class, 'messages']);
        Route::post('chat/messages', [ChatController::class, 'send']);
        Route::post('chat/start', [ChatController::class, 'start']);
        Route::post('chat/request', [ChatController::class, 'sendRequest']);
        Route::post('chat/keys', [ChatController::class, 'publishKey']);
        Route::post('chat/upload/init', [ChatController::class, 'uploadInit']);
        Route::post('chat/upload/chunk', [ChatController::class, 'uploadChunk']);
        Route::post('chat/upload/complete', [ChatController::class, 'uploadComplete']);
        Route::get('chat/file/{message}', [ChatController::class, 'downloadFile']);

        Route::get('calls/ice', [CallController::class, 'iceServers']);
        Route::post('calls', [CallController::class, 'start']);
        Route::get('calls/{call:room_id}', [CallController::class, 'show']);
        Route::post('calls/{call:room_id}/join', [CallController::class, 'join']);
        Route::post('calls/{call:room_id}/decline', [CallController::class, 'decline']);
        Route::post('calls/{call:room_id}/leave', [CallController::class, 'leave']);
        Route::post('calls/{call:room_id}/signal', [CallController::class, 'signal']);

        Route::post('reports', [ReportController::class, 'store']);

        // Settings endpoints
        Route::get('settings', [SettingsController::class, 'show']);
        Route::post('settings/privacy', [SettingsController::class, 'updatePrivacy']);
        Route::post('settings/privacy/advanced', [SettingsController::class, 'updateAdvancedPrivacy']);
        
        Route::post('settings/security/pin', [SettingsController::class, 'setPin']);
        Route::post('settings/security/pin/remove', [SettingsController::class, 'removePin']);
        Route::post('settings/security/pin/require', [SettingsController::class, 'setRequirePinOnLogin']);
        Route::post('settings/security/pin/verify', [SettingsController::class, 'verifyPin']);
        
        Route::post('settings/security/pattern', [SettingsController::class, 'setPattern']);
        Route::post('settings/security/pattern/remove', [SettingsController::class, 'removePattern']);
        Route::post('settings/security/pattern/require', [SettingsController::class, 'setRequirePatternOnLogin']);
        Route::post('settings/security/pattern/verify', [SettingsController::class, 'verifyPattern']);
        
        Route::post('settings/password', [SettingsController::class, 'changePassword']);
        
        Route::get('settings/devices', [SettingsController::class, 'getDevices']);
        Route::post('settings/devices/register', [SettingsController::class, 'registerDevice']);
        Route::delete('settings/devices/{device_id}', [SettingsController::class, 'removeDevice']);
        Route::post('settings/logout-other-devices', [SettingsController::class, 'logoutOtherDevices']);
        Route::post('settings/session/end-all', [SettingsController::class, 'endAllSessions']);
        Route::post('settings/session/timeout', [SettingsController::class, 'updateSessionTimeout']);
        
        Route::get('settings/login-history', [SettingsController::class, 'getLoginHistory']);
        Route::get('settings/blocked-users', [SettingsController::class, 'getBlockedUsers']);
        Route::post('settings/blocked-users/add', [SettingsController::class, 'blockUser']);
        Route::delete('settings/blocked-users/{user_id}', [SettingsController::class, 'unblockUser']);
        
        Route::post('settings/account/delete', [SettingsController::class, 'deleteAccount']);
        Route::post('settings/account/restore', [SettingsController::class, 'restoreAccount']);
    });
});

