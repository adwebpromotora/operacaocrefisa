<?php
// core/helpers.php
// Funções auxiliares

function redirect($url) {
    header("Location: $url");
    exit;
}

function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Função para conectar ao DB (use PDO para segurança)
function getDbConnection() {
    $host = 'localhost';  // Ajuste conforme seu VPS
    $dbname = 'operacaocrefisa';
    $user = 'root';  // Ajuste
    $pass = '';  // Ajuste
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        die("Erro de conexão: " . $e->getMessage());
    }
}
