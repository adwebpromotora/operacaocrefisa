<?php
$host = 'sistemas_operacaocrefisa-db'; // Seu host MySQL
$dbname = 'operacaocrefisa';
$username = 'crefisa'; // Seu usuário MySQL
$password = 'AdWebc@132005'; // Sua senha MySQL

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro de conexão: " . $e->getMessage());
}
?>
