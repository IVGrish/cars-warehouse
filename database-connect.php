<?php
try {
    $db = new PDO('mysql:host=localhost;dbname=cars_warehouse', getenv('DB_USER'), getenv('DB_PASS'));
} catch (PDOException $e) {
    echo "Can't connect: " . $e->getMessage();
    exit();
}

$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

