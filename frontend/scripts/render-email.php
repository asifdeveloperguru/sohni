<?php
// Dev utility: renders the activation email template to verify it compiles
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = new App\Models\User();
$user->email = 'preview@gmail.com';

$html = view('emails.verify-account', [
    'user' => $user,
    'link' => 'https://example.com/activate/1/abc?expires=123&signature=xyz',
])->render();

file_put_contents(__DIR__ . '/email-preview.html', $html);
echo strlen($html) . " bytes rendered OK -> scripts/email-preview.html\n";
