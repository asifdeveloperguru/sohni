<?php
declare(strict_types=1);
require __DIR__ . '/../app/bootstrap.php';

if (is_post()) {
    Security::verifyCsrf();
}

Auth::logout();
redirect('/login.php');
