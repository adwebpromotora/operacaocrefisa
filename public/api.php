<?php
session_start();
include '../core/config.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Não autenticado']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents('php://input'), true);

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            // Buscar lead específico
            $id = $_GET['id'];
            $stmt = $pdo->prepare("SELECT * FROM leads WHERE id = ?");
            $stmt->execute([$id]);
            $lead = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode($lead ?: []);
        } else {
            // Buscar leads (com busca opcional)
            $search = isset($_GET['search']) ? $_GET['search'] : '';
            $query = "SELECT * FROM leads WHERE nome LIKE :search OR cpf LIKE :search OR telefone LIKE :search OR whatsapp LIKE :search OR email LIKE :search OR anotacao LIKE :search";
            $stmt = $pdo->prepare($query);
            $stmt->execute(['search' => "%$search%"]);
            $leads = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($leads);
        }
        break;

    case 'POST':
        // Adicionar lead
        $stmt = $pdo->prepare("INSERT INTO leads (nome, cpf, telefone, whatsapp, email, valor_liberado, anotacao, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$data['nome'], $data['cpf'], $data['telefone'], $data['whatsapp'], $data['email'], $data['valorLiberado'], $data['anotacao'], $data['status']]);
        echo json_encode(['id' => $pdo->lastInsertId()]);
        break;

    case 'PUT':
        // Editar lead
        $id = $data['id'];
        $stmt = $pdo->prepare("UPDATE leads SET nome=?, cpf=?, telefone=?, whatsapp=?, email=?, valor_liberado=?, anotacao=?, status=? WHERE id=?");
        $stmt->execute([$data['nome'], $data['cpf'], $data['telefone'], $data['whatsapp'], $data['email'], $data['valorLiberado'], $data['anotacao'], $data['status'], $id]);
        echo json_encode(['success' => true]);
        break;

    case 'DELETE':
        // Deletar lead (não solicitado, mas útil)
        $id = $data['id'];
        $stmt = $pdo->prepare("DELETE FROM leads WHERE id=?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
        break;
}

if (isset($_GET['action']) && $_GET['action'] === 'comments') {
    $leadId = $_GET['lead_id'];
    if ($method === 'GET') {
        // Buscar comentários
        $stmt = $pdo->prepare("SELECT * FROM lead_comments WHERE lead_id = ? ORDER BY comment_date ASC");
        $stmt->execute([$leadId]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } elseif ($method === 'POST') {
        // Adicionar comentário
        $stmt = $pdo->prepare("INSERT INTO lead_comments (lead_id, comment) VALUES (?, ?)");
        $stmt->execute([$leadId, $data['comment']]);
        echo json_encode(['success' => true]);
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'indicators') {
    // Indicadores
    $indicators = [];
    $indicators['totalLeads'] = $pdo->query("SELECT COUNT(*) FROM leads")->fetchColumn();

    $statuses = ['fazer contato', 'erro', 'simulado', 'sem interesse', 'em negociação', 'vendido'];
    foreach ($statuses as $status) {
        $stmt = $pdo->prepare("SELECT SUM(valor_liberado) FROM leads WHERE status = ?");
        $stmt->execute([$status]);
        $indicators[$status] = (float) $stmt->fetchColumn() ?: 0;
    }
    echo json_encode($indicators);
}
?>
