<?php
// public/atendimento.php
// Página de atendimento, leads com simulação realizada, um a um

require_once __DIR__ . '/../core/session.php';
require_once __DIR__ . '/../core/helpers.php';
require_once __DIR__ . '/components/header.php';

if (!getSessionValue('user_id')) redirect('/public/login.php');

$pdo = getDbConnection();
$userId = getSessionValue('user_id');

$leadId = isset($_GET['lead_id']) ? (int)$_GET['lead_id'] : null;

if (!$leadId) {
    // Listar leads com simulação realizada
    $stmt = $pdo->prepare("SELECT l.id, l.cpf, l.nome FROM leads l JOIN simulacoes s ON l.id = s.lead_id WHERE s.status = 'simulacao_realizada'");
    $stmt->execute();
    $leads = $stmt->fetchAll();

    echo "<div class='container mx-auto p-4'>";
    echo "<h1>Selecione um Lead para Atendimento</h1>";
    foreach ($leads as $lead) {
        echo "<a href='?lead_id={$lead['id']}' class='block bg-white p-4 shadow mb-2'>{$lead['nome']} - CPF: {$lead['cpf']}</a>";
    }
    echo "</div>";
} else {
    // Detalhes do lead
    $stmt = $pdo->prepare("SELECT * FROM leads WHERE id = :id");
    $stmt->execute(['id' => $leadId]);
    $lead = $stmt->fetch();

    // Histórico de anotações
    $stmt = $pdo->prepare("SELECT * FROM anotacoes WHERE lead_id = :lead_id ORDER BY created_at DESC");
    $stmt->execute(['lead_id' => $leadId]);
    $anotacoes = $stmt->fetchAll();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['nota'])) {
            $nota = $_POST['nota'];
            $stmt = $pdo->prepare("INSERT INTO anotacoes (lead_id, user_id, nota) VALUES (:lead_id, :user_id, :nota)");
            $stmt->execute(['lead_id' => $leadId, 'user_id' => $userId, 'nota' => $nota]);
        } elseif (isset($_POST['edit'])) {
            $nome = $_POST['nome'];
            $telefone1 = $_POST['telefone1'];
            $telefone2 = $_POST['telefone2'];
            $email = $_POST['email'];
            $stmt = $pdo->prepare("UPDATE leads SET nome = :nome, telefone1 = :telefone1, telefone2 = :telefone2, email = :email WHERE id = :id");
            $stmt->execute(['nome' => $nome, 'telefone1' => $telefone1, 'telefone2' => $telefone2, 'email' => $email, 'id' => $leadId]);
        }
        redirect("/public/atendimento.php?lead_id=$leadId");
    }

    ?>
    <div class="container mx-auto p-4">
        <!-- Busca global -->
        <!-- Similar ao dashboard -->

        <h1>Atendimento para <?php echo $lead['nome']; ?></h1>
        <form method="POST">
            <input type="hidden" name="edit" value="1">
            <label>Nome: <input name="nome" value="<?php echo $lead['nome']; ?>"></label>
            <label>Telefone1: <input name="telefone1" value="<?php echo $lead['telefone1']; ?>"></label>
            <label>Telefone2: <input name="telefone2" value="<?php echo $lead['telefone2']; ?>"></label>
            <label>Email: <input name="email" value="<?php echo $lead['email']; ?>"></label>
            <button type="submit" class="bg-blue-500 text-white p-2">Editar</button>
        </form>

        <h2>Anotações</h2>
        <?php foreach ($anotacoes as $anot): ?>
            <div class="bg-gray-100 p-2 mb-2"><?php echo nl2br($anot['nota']); ?> - <?php echo $anot['created_at']; ?></div>
        <?php endforeach; ?>

        <form method="POST">
            <textarea name="nota" placeholder="Nova Anotação" class="border p-2 w-full"></textarea>
            <button type="submit" class="bg-green-500 text-white p-2">Adicionar</button>
        </form>
    </div>
    <?php
}
?>
</body>
</html>
