<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $email = $data['email'];
    $senha = $data['senha'];

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = :email AND senha = :senha");
    $stmt->execute(['email' => $email, 'senha' => $senha]);
    $user = $stmt->fetch();

    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Credenciais inválidas']);
    }
}
?>
