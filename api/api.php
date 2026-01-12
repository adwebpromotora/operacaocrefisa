<?php
session_start();

// Cabeçalhos para permitir requisições do frontend (ajuste o origin em produção)
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');          // ← Mude para seu domínio em produção
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Incluir configuração do banco (caminho relativo correto da pasta api/ para core/)
require_once '../core/config.php';

// Verifica autenticação em TODAS as requisições
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Não autenticado. Faça login para continuar.']);
    exit;
}

// Tratar requisição OPTIONS (preflight do CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents('php://input'), true) ?? [];

// =======================
// Rotas principais (CRUD de leads)
// =======================
if (!isset($_GET['action'])) {
    switch ($method) {
        case 'GET':
            // Listar leads com busca opcional
            $search = $_GET['search'] ?? '';
            $query = "SELECT * FROM leads 
                      WHERE nome LIKE :search 
                         OR cpf LIKE :search 
                         OR telefone LIKE :search 
                         OR whatsapp LIKE :search 
                         OR email LIKE :search 
                         OR anotacao LIKE :search 
                      ORDER BY created_at DESC";
            
            $stmt = $pdo->prepare($query);
            $stmt->execute(['search' => "%$search%"]);
            $leads = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode($leads);
            break;

        case 'POST':
            // Adicionar novo lead
            $required = ['nome', 'cpf', 'valorLiberado', 'status'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    http_response_code(400);
                    echo json_encode(['error' => "Campo obrigatório faltando: $field"]);
                    exit;
                }
            }

            $stmt = $pdo->prepare("
                INSERT INTO leads 
                (nome, cpf, telefone, whatsapp, email, valor_liberado, anotacao, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['nome'],
                $data['cpf'],
                $data['telefone'] ?? null,
                $data['whatsapp'] ?? null,
                $data['email'] ?? null,
                $data['valorLiberado'],
                $data['anotacao'] ?? null,
                $data['status']
            ]);

            echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
            break;

        case 'PUT':
            // Atualizar lead existente
            if (empty($data['id'])) {
                http_response_code(400);
                echo json_encode(['error' => 'ID do lead é obrigatório']);
                exit;
            }

            $stmt = $pdo->prepare("
                UPDATE leads SET 
                    nome = ?, cpf = ?, telefone = ?, whatsapp = ?, 
                    email = ?, valor_liberado = ?, anotacao = ?, status = ? 
                WHERE id = ?
            ");
            $stmt->execute([
                $data['nome'],
                $data['cpf'],
                $data['telefone'] ?? null,
                $data['whatsapp'] ?? null,
                $data['email'] ?? null,
                $data['valorLiberado'],
                $data['anotacao'] ?? null,
                $data['status'],
                $data['id']
            ]);

            echo json_encode(['success' => true]);
            break;

        case 'DELETE':
            if (empty($data['id'])) {
                http_response_code(400);
                echo json_encode(['error' => 'ID do lead é obrigatório']);
                exit;
            }

            $stmt = $pdo->prepare("DELETE FROM leads WHERE id = ?");
            $stmt->execute([$data['id']]);

            echo json_encode(['success' => true]);
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Método não permitido']);
            break;
    }
    exit;
}

// =======================
// Ações específicas (comments e indicators)
// =======================
$action = $_GET['action'] ?? '';

if ($action === 'comments') {
    $leadId = $_GET['lead_id'] ?? null;
    if (!$leadId) {
        http_response_code(400);
        echo json_encode(['error' => 'lead_id é obrigatório']);
        exit;
    }

    if ($method === 'GET') {
        // Listar comentários
        $stmt = $pdo->prepare("
            SELECT * FROM lead_comments 
            WHERE lead_id = ? 
            ORDER BY comment_date ASC
        ");
        $stmt->execute([$leadId]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } 
    elseif ($method === 'POST') {
        // Adicionar comentário
        $comment = trim($data['comment'] ?? '');
        if (empty($comment)) {
            http_response_code(400);
            echo json_encode(['error' => 'Comentário não pode ser vazio']);
            exit;
        }

        $stmt = $pdo->prepare("
            INSERT INTO lead_comments (lead_id, comment) 
            VALUES (?, ?)
        ");
        $stmt->execute([$leadId, $comment]);
        echo json_encode(['success' => true]);
    } 
    else {
        http_response_code(405);
        echo json_encode(['error' => 'Método não permitido para comments']);
    }
} 
elseif ($action === 'indicators') {
    // Apenas GET permitido
    if ($method !== 'GET') {
        http_response_code(405);
        echo json_encode(['error' => 'Apenas GET permitido para indicators']);
        exit;
    }

    $indicators = [];
    $indicators['totalLeads'] = (int) $pdo->query("SELECT COUNT(*) FROM leads")->fetchColumn();

    $statuses = ['fazer contato', 'erro', 'simulado', 'sem interesse', 'em negociação', 'vendido'];
    foreach ($statuses as $status) {
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(valor_liberado), 0) FROM leads WHERE status = ?");
        $stmt->execute([$status]);
        $indicators[$status] = (float) $stmt->fetchColumn();
    }

    echo json_encode($indicators);
} 
else {
    http_response_code(400);
    echo json_encode(['error' => 'Ação inválida']);
}
