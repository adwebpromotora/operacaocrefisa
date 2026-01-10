<?php
// public/simulacoes.php
// Página de simulações, 5 em 5 leads pendentes

require_once __DIR__ . '/../core/session.php';
require_once __DIR__ . '/../core/helpers.php';
require_once __DIR__ . '/components/header.php';

if (!getSessionValue('user_id')) redirect('/public/login.php');

$pdo = getDbConnection();

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 5;
$offset = ($page - 1) * $limit;

// Contar pendentes
$totalPendente = $pdo->query("SELECT COUNT(*) FROM leads l LEFT JOIN simulacoes s ON l.id = s.lead_id WHERE s.status IS NULL OR s.status = 'pendente'")->fetchColumn();
$totalPages = ceil($totalPendente / $limit);

// Buscar leads pendentes
$stmt = $pdo->prepare("SELECT l.* FROM leads l LEFT JOIN simulacoes s ON l.id = s.lead_id WHERE s.status IS NULL OR s.status = 'pendente' LIMIT :limit OFFSET :offset");
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$leads = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $leadId = $_POST['lead_id'];
    $status = $_POST['status'];
    $valor = ($status === 'simulacao_realizada') ? $_POST['valor'] : null;

    $stmt = $pdo->prepare("INSERT INTO simulacoes (lead_id, status, valor_simulado) VALUES (:lead_id, :status, :valor) ON DUPLICATE KEY UPDATE status = :status, valor_simulado = :valor");
    $stmt->execute(['lead_id' => $leadId, 'status' => $status, 'valor' => $valor]);
    redirect('/public/simulacoes.php?page=' . $page);
}
?>

<div class="container mx-auto p-4">
    <!-- Busca -->
    <form method="GET" class="mb-4">
        <input type="text" name="search" placeholder="Busca por CPF, Telefone ou Nome" class="border p-2 w-full">
        <button type="submit" class="bg-blue-500 text-white p-2">Buscar</button>
    </form>

    <?php if (isset($_GET['search'])) {
        // Código de busca similar ao dashboard
        // ...
    } ?>

    <h1>Simulações Pendente</h1>
    <?php foreach ($leads as $lead): ?>
        <div class="bg-white p-4 shadow mb-4">
            <p>CPF: <?php echo $lead['cpf']; ?></p>
            <p>Nome: <?php echo $lead['nome']; ?></p>
            <p>Telefone1: <?php echo $lead['telefone1']; ?></p>
            <form method="POST">
                <input type="hidden" name="lead_id" value="<?php echo $lead['id']; ?>">
                <select name="status" onchange="toggleValor(this)">
                    <option value="sem_cadastro">Sem Cadastro</option>
                    <option value="sem_perfil">Sem Perfil</option>
                    <option value="simulacao_realizada">Simulação Realizada</option>
                </select>
                <input type="number" name="valor" placeholder="Valor Simulado" style="display:none;" step="0.01">
                <button type="submit" class="bg-green-500 text-white p-2">Salvar</button>
            </form>
        </div>
    <?php endforeach; ?>

    <!-- Paginação -->
    <div>
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?php echo $i; ?>" class="p-2 <?php echo $i == $page ? 'bg-blue-500 text-white' : ''; ?>"><?php echo $i; ?></a>
        <?php endfor; ?>
    </div>
</div>

<script>
    function toggleValor(select) {
        const input = select.nextElementSibling;
        input.style.display = (select.value === 'simulacao_realizada') ? 'block' : 'none';
    }
</script>
</body>
</html>
