<?php
$host     = getenv('DB_HOST')     ?: 'sistemas_operacaocrefisa-db';
$dbname   = getenv('DB_NAME')     ?: 'operacaocrefisa';
$username = getenv('DB_USER')     ?: 'crefisa';
$password = getenv('DB_PASSWORD') ?: 'AdWebc@132005';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    die("Erro de conexão com o banco: " . $e->getMessage());
}
?>
