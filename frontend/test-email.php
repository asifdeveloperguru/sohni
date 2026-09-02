<?php
/**
 * Test email sender - sends a test email via configured SMTP
 * Run: php artisan tinker < test-email.php
 * Or: php test-email.php
 */

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Mail;

echo "🚀 Sending test email...\n";

try {
    Mail::send('emails.verify-account', [
        'user' => (object)[
            'email' => 'asimran0011@gmail.com',
            'name' => 'Test User'
        ],
        'link' => 'https://sohni.site/activate/999/test-token?expires=999&signature=test'
    ], function ($message) {
        $message->to('asimran0011@gmail.com')
                ->subject('✨ Test Email from Sohni - SMTP Verification');
    });

    echo "✅ Email sent successfully to asimran0011@gmail.com\n";
    echo "📧 Check your inbox (including spam folder)!\n";

} catch (\Exception $e) {
    echo "❌ Email failed: " . $e->getMessage() . "\n";
    echo "📋 Full error:\n";
    echo $e->getTraceAsString() . "\n";
}
