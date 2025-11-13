<?php
// Bootstrap CodeIgniter
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);
require __DIR__ . '/vendor/autoload.php';

// Load environment variables
$dotenv = \Dotenv\Dotenv::createUnsafeImmutable(__DIR__);
$dotenv->load();

// Load CodeIgniter
require __DIR__ . '/app/Config/Paths.php';
$paths = new Config\Paths();
require SYSTEMPATH . 'bootstrap.php';

$db = \Config\Database::connect();

echo "=== CONSULTORES ===\n";
$fields = $db->getFieldNames('consultores');
print_r($fields);

echo "\n=== PROVEEDORES ===\n";
$fields = $db->getFieldNames('proveedores');
print_r($fields);

echo "\n=== USERS ===\n";
$fields = $db->getFieldNames('users');
print_r($fields);
