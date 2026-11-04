<?php
/**
 * salvar_pontuacao.php — Endpoint para registro de pontuação e estatísticas
 * Fórmula: acertos * 100 - erros * 65
 * Atualiza no banco: questoesRespondidas, acertos, pontuacao
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/proteger.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['sucesso' => false, 'erro' => 'Método não permitido.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);
if (!is_array($data)) {
    $data = $_POST;
}

$questoesRespondidas = isset($data['questoesRespondidas']) ? (int) $data['questoesRespondidas'] : 0;
$acertos = isset($data['acertos']) ? (int) $data['acertos'] : 0;

if ($questoesRespondidas < 0 || $acertos < 0) {
    http_response_code(400);
    echo json_encode(['sucesso' => false, 'erro' => 'Dados de pontuação inválidos.'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Garante que acertos não exceda o total de questões
if ($acertos > $questoesRespondidas) {
    $acertos = $questoesRespondidas;
}

$erros = max(0, $questoesRespondidas - $acertos);

// Fórmula solicitada: acertos * 100 - erros * 65
$pontuacao_partida = ($acertos * 100) - ($erros * 65);

$userId = (int) ($_SESSION['id'] ?? 0);
if ($userId <= 0) {
    http_response_code(401);
    echo json_encode(['sucesso' => false, 'erro' => 'Usuário não autenticado.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$tbl_usuarios = db_table($conn, 'usuario');

// Atualiza o banco somando as novas estatísticas
$sql_update = "
    UPDATE {$tbl_usuarios}
    SET pontuacao = GREATEST(0, pontuacao + ?),
        questoesRespondidas = questoesRespondidas + ?,
        acertos = acertos + ?
    WHERE id = ?
";
$stmt = $conn->prepare($sql_update);

if ($stmt) {
    $stmt->bind_param("iiii", $pontuacao_partida, $questoesRespondidas, $acertos, $userId);
    $exec = $stmt->execute();
    $stmt->close();

    if ($exec) {
        // Consulta totais atualizados
        $stmt_sel = $conn->prepare("SELECT pontuacao, questoesRespondidas, acertos FROM {$tbl_usuarios} WHERE id = ?");
        $totais = ['pontuacao' => 0, 'questoesRespondidas' => 0, 'acertos' => 0];
        if ($stmt_sel) {
            $stmt_sel->bind_param("i", $userId);
            $stmt_sel->execute();
            $res_totais = $stmt_sel->get_result();
            if ($row = $res_totais->fetch_assoc()) {
                $totais = $row;
            }
            $stmt_sel->close();
        }

        // Atualiza na sessão do PHP
        $_SESSION['pontuacao']           = (int) $totais['pontuacao'];
        $_SESSION['questoesRespondidas'] = (int) $totais['questoesRespondidas'];
        $_SESSION['acertos']             = (int) $totais['acertos'];

        echo json_encode([
            'sucesso'                    => true,
            'pontuacao_partida'          => $pontuacao_partida,
            'pontuacao_total'            => (int) $totais['pontuacao'],
            'questoesRespondidas_partida'=> $questoesRespondidas,
            'acertos_partida'            => $acertos,
            'erros_partida'              => $erros,
            'acertos_total'              => (int) $totais['acertos'],
            'questoesRespondidas_total'  => (int) $totais['questoesRespondidas'],
        ], JSON_UNESCAPED_UNICODE);
        exit;
    } else {
        http_response_code(500);
        echo json_encode(['sucesso' => false, 'erro' => 'Erro ao salvar pontuação no banco.'], JSON_UNESCAPED_UNICODE);
        exit;
    }
} else {
    http_response_code(500);
    echo json_encode(['sucesso' => false, 'erro' => 'Erro interno ao preparar atualização.'], JSON_UNESCAPED_UNICODE);
    exit;
}
