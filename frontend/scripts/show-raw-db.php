<?php
// Dev utility: shows raw DB values to confirm encryption at rest
$db = new PDO('sqlite:' . __DIR__ . '/../database/database.sqlite');

echo "USERS TABLE (raw, what an admin sees in the DB):\n";
$u = $db->query('SELECT name, first_name, phone, address FROM users ORDER BY id DESC LIMIT 1')->fetch(PDO::FETCH_ASSOC);
foreach ($u as $k => $v) {
    echo "  $k = " . substr((string) $v, 0, 70) . "...\n";
}

echo "\nEDUCATIONS TABLE (raw):\n";
$e = $db->query('SELECT title, grade, marks FROM educations LIMIT 1')->fetch(PDO::FETCH_ASSOC);
foreach ($e as $k => $v) {
    echo "  $k = " . substr((string) $v, 0, 70) . "...\n";
}
