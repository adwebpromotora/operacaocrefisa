<?php
// public/login.php
// Página de login (adicional, pois o sistema precisa de autenticação)

require_once __DIR__ . '/../core/session.php';
require_once __DIR__ . '/../core/helpers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $pdo = getDbConnection();
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && verifyPassword($senha, $user['senha'])) {
        setSessionValue('user_id', $user['id']);
        setSessionValue('nome', $user['nome']);
        setSessionValue('user_profile', 'gestor');  // Ajuste conforme perfis
        redirect('/public/dashboard.php');
    } else {
        $error = 'Credenciais inválidas';
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="/css/styles.css">
</head>
<body>
    <form method="POST">
        <input type="email" name="email" required placeholder="Email">
        <input type="password" name="senha" required placeholder="Senha">
        <button type="submit">Entrar</button>
        <?php if (isset($error)) echo "<p>$error</p>"; ?>
    </form>
</body>
</html>
