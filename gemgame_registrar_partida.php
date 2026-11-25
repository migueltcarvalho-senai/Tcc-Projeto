<?php
/* ============================================================
   gemgame_registrar_partida.php — Salva histórico de partida
   Sketchbook Educacional | POST + JSON | Autenticado
   ============================================================ */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/proteger.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['sucesso' => false]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['sucesso' => false]);
    exit;
}

$quiz_id         = isset($data['quiz_id'])         ? (int) $data['quiz_id']         : 0;
$acertos         = isset($data['acertos'])         ? (int) $data['acertos']         : 0;
$total_perguntas = isset($data['total_perguntas']) ? (int) $data['total_perguntas'] : 0;
$jogador_id      = (int) ($_SESSION['id'] ?? 0);

if ($quiz_id <= 0 || $jogador_id <= 0 || $total_perguntas <= 0) {
    http_response_code(400);
    echo json_encode(['sucesso' => false]);
    exit;
}

// Garante que acertos não exceda o total
$acertos = min($acertos, $total_perguntas);
$erros   = max(0, $total_perguntas - $acertos);

// Mesma fórmula usada em salvar_pontuacao.php
$pontuacao_partida = ($acertos * 100) - ($erros * 65);

$tbl_partida = db_table($conn, 'gemgame_partida');
$stmt = $conn->prepare(
    "INSERT INTO {$tbl_partida}
        (quiz_id, jogador_id, acertos, total_perguntas, pontuacao_partida)
     VALUES (?, ?, ?, ?, ?)"
);

if ($stmt) {
    $stmt->bind_param('iiiii', $quiz_id, $jogador_id, $acertos, $total_perguntas, $pontuacao_partida);
    $ok = $stmt->execute();
    $stmt->close();
    echo json_encode(['sucesso' => (bool) $ok]);
} else {
    http_response_code(500);
    echo json_encode(['sucesso' => false]);
}
