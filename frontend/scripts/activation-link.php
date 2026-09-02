<?php
// Dev utility: prints the signed activation link for the most recent unverified user
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = App\Models\User::whereNull('email_verified_at')->orderByDesc('id')->first();
if (! $user) {
    echo "No unverified user found.\n";
    exit(1);
}

echo Illuminate\Support\Facades\URL::temporarySignedRoute('activate.show', now()->addMinutes(60), [
    'id' => $user->id,
    'token' => $user->verification_token,
]) . "\n";
