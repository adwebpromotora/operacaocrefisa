<?php
session_start();
header('Content-Type: application/json');

// Verifica se o usuário está logado
if (isset($_SESSION['user_id'])) {
    echo json_encode(['loggedIn' => true]);
} else {
    echo json_encode(['loggedIn' => false]);
}
?>
