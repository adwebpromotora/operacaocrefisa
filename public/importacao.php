<?php
// public/importacao.php
// Página de importação de CSV

require_once __DIR__ . '/../core/session.php';
require_once __DIR__ . '/../core/helpers.php';
require_once __DIR__ . '/components/header.php';

if (!getSessionValue('user_id')) redirect('/public/login.php');

$pdo = getDbConnection();
$preview = [];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['csv']) && $_FILES['csv']['error'] === 0) {
        $file = $_FILES['csv']['tmp_name'];
        $handle = fopen($file, 'r');

        if (isset($_POST['preview'])) {
            // Pré-visualização
            $preview = [];
            while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                $preview[] = $data;  // Assumindo colunas: cpf, nome, telefone1, telefone2, email
            }
            fclose($handle);
        } elseif (isset($_POST['import'])) {
            // Importar
            $stmt = $pdo->prepare("INSERT IGNORE INTO leads (cpf, nome, telefone1, telefone2, email) VALUES (:cpf, :nome, :telefone1, :telefone2, :email)");
            while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                $stmt->execute([
                    'cpf' => $data[0],
                    'nome' => $data[1],
                    'telefone1' => $data[2] ?? null,
                    'telefone2' => $data[3] ?? null,
                    'email' => $data[4] ?? null
                ]);
            }
            fclose($handle);
            redirect('/public/dashboard.php');
        }
    } else {
        $error = 'Erro ao upload do arquivo';
    }
}
?>

<div class="container mx-auto p-4">
    <h1>Importação de Leads</h1>
    <form method="POST" enctype="multipart/form-data">
        <input type="file" name="csv" accept=".csv" required>
        <button type="submit" name="preview" class="bg-yellow-500 text-white p-2">Pré-visualizar</button>
    </form>

    <?php if (!empty($preview)): ?>
        <h2>Pré-visualização</h2>
        <table class="border w-full">
            <?php foreach ($preview as $row): ?>
                <tr><?php foreach ($row as $cell): ?><td class="border p-2"><?php echo htmlspecialchars($cell); ?></td><?php endforeach; ?></tr>
            <?php endforeach; ?>
        </table>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csv" value="<?php echo $_FILES['csv']['tmp_name']; ?>">  <!-- Não ideal, mas para simulação -->
            <button type="submit" name="import" class="bg-green-500 text-white p-2">Importar</button>
        </form>
    <?php endif; ?>

    <?php if ($error) echo "<p>$error</p>"; ?>
</div>
</body>
</html>
