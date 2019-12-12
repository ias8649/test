<?php
require_once('./settings.php');

try {
    $db = new \PDO(DB_DNS, DB_USER, DB_PASSWORD, []);
    
} catch (Exception $e) {
    
    echo 'Произошла ошибка: ',  $e->getMessage(), "\n";
}
try {
    $query = "CREATE TABLE IF NOT EXISTS users (id INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT, 
                                    username VARCHAR(100) UNIQUE NOT NULL, 
                                    created_at DATE NOT NULL,
                                    deleted_at DATE
                                    );";
    $db->exec($query);
} catch (Exception $e) {
    echo 'Произошла ошибка: ',  $e->getMessage(), "\n";
}
