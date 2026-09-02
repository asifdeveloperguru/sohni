<?php
// Dev utility: sends a test email to confirm SMTP delivery works
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    Illuminate\Support\Facades\Mail::raw(
        'SMTP test from Sohni — if you can read this, real email delivery works! 🎉',
        function ($message) {
            $message->to('mujahidhussaincarpentry@gmail.com')->subject('✅ Sohni SMTP Test');
        }
    );
    echo "SUCCESS: Test email sent — check the inbox.\n";
} catch (Throwable $e) {
    echo 'FAILED: ' . $e->getMessage() . "\n";
}
