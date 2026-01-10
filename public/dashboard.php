<?php
// public/dashboard.php
// Dashboard com cards e gráficos

require_once __DIR__ . '/../core/session.php';
require_once __DIR__ . '/../core/helpers.php';
require_once __DIR__ . '/components/header.php';  // Inclui o header fornecido

if (!getSessionValue('user_id')) redirect('/public/login.php');

$pdo = getDbConnection();

// Total de leads
$totalLeads = $pdo->query("SELECT COUNT(*) FROM leads")->fetchColumn();

// Total de leads simulados (qualquer status exceto pendente)
$totalSimulados = $pdo->query("SELECT COUNT(*) FROM simulacoes WHERE status != 'pendente'")->fetchColumn();

// Total de leads com simulação realizada
$totalRealizadas = $pdo->query("SELECT COUNT(*) FROM simulacoes WHERE status = 'simulacao_realizada'")->fetchColumn();

// Soma total dos valores simulados
$somaValores = $pdo->query("SELECT SUM(valor_simulado) FROM simulacoes WHERE status = 'simulacao_realizada'")->fetchColumn() ?? 0;

// Dados para gráficos (últimos 30 dias)
$startDate = date('Y-m-d', strtotime('-30 days'));
$endDate = date('Y-m-d');

// Total simulado por dia
$stmt = $pdo->prepare("SELECT DATE(data_simulacao) as dia, COUNT(*) as total FROM simulacoes WHERE data_simulacao >= :start AND data_simulacao <= :end AND status != 'pendente' GROUP BY dia");
$stmt->execute(['start' => $startDate, 'end' => $endDate]);
$simuladosPorDia = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Soma valores por dia
$stmt = $pdo->prepare("SELECT DATE(data_simulacao) as dia, SUM(valor_simulado) as soma FROM simulacoes WHERE data_simulacao >= :start AND data_simulacao <= :end AND status = 'simulacao_realizada' GROUP BY dia");
$stmt->execute(['start' => $startDate, 'end' => $endDate]);
$valoresPorDia = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Preparar dados para Chart.js
$labels = [];
$simuladosData = [];
$valoresData = [];

for ($i = 0; $i < 30; $i++) {
    $date = date('Y-m-d', strtotime("- $i days"));
    $labels[] = $date;
    $simuladosData[] = 0;
    $valoresData[] = 0;
}

foreach ($simuladosPorDia as $row) {
    $index = array_search($row['dia'], $labels);
    if ($index !== false) $simuladosData[$index] = $row['total'];
}

foreach ($valoresPorDia as $row) {
    $index = array_search($row['dia'], $labels);
    if ($index !== false) $valoresData[$index] = $row['soma'];
}

$labels = array_reverse($labels);
$simuladosData = array_reverse($simuladosData);
$valoresData = array_reverse($valoresData);
?>

<div class="container mx-auto p-4">
    <!-- Campo de busca -->
    <form method="GET" class="mb-4">
        <input type="text" name="search" placeholder="Busca por CPF, Telefone ou Nome" class="border p-2 w-full">
        <button type="submit" class="bg-blue-500 text-white p-2">Buscar</button>
    </form>

    <?php
    if (isset($_GET['search'])) {
        $search = $_GET['search'];
        $stmt = $pdo->prepare("SELECT * FROM leads WHERE cpf LIKE :search OR nome LIKE :search OR telefone1 LIKE :search OR telefone2 LIKE :search OR email LIKE :search");
        $stmt->execute(['search' => "%$search%"]);
        $results = $stmt->fetchAll();
        // Exibir resultados em tabela ou cards
        echo "<h2>Resultados da Busca</h2>";
        echo "<table class='border w-full'><tr><th>CPF</th><th>Nome</th><th>Telefone1</th></tr>";
        foreach ($results as $row) {
            echo "<tr><td>{$row['cpf']}</td><td>{$row['nome']}</td><td>{$row['telefone1']}</td></tr>";
        }
        echo "</table>";
    }
    ?>

    <!-- Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white p-4 shadow">Total Leads: <?php echo $totalLeads; ?></div>
        <div class="bg-white p-4 shadow">Total Simulados: <?php echo $totalSimulados; ?></div>
        <div class="bg-white p-4 shadow">Simulações Realizadas: <?php echo $totalRealizadas; ?></div>
        <div class="bg-white p-4 shadow">Soma Valores: R$ <?php echo number_format($somaValores, 2, ',', '.'); ?></div>
    </div>

    <!-- Gráficos -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
        <div>
            <canvas id="simuladosChart"></canvas>
        </div>
        <div>
            <canvas id="valoresChart"></canvas>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const labels = <?php echo json_encode($labels); ?>;
    new Chart(document.getElementById('simuladosChart'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{ label: 'Total Simulado por Dia', data: <?php echo json_encode($simuladosData); ?>, borderColor: 'blue' }]
        }
    });
    new Chart(document.getElementById('valoresChart'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{ label: 'Soma Valores por Dia', data: <?php echo json_encode($valoresData); ?>, borderColor: 'green' }]
        }
    });
</script>
</body>
</html>
