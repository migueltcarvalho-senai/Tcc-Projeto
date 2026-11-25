<?php
/* ============================================================
   gemgame_api.php — API REST em tempo real para GemGame Multiplayer
   Modo Kahoot / Quizizz (Host ↔ Alunos)
   Compatível com PHP 8.x + MySQL + Polling Inteligente (HTTP Heartbeat)
   ============================================================ */

require_once __DIR__ . '/config/config.php';

header('Content-Type: application/json; charset=utf-8');

// Inicia sessão caso ainda não esteja ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$acao = $_GET['acao'] ?? $_POST['acao'] ?? '';

// Tabelas dinâmicas
$tbl_sala       = db_table($conn, 'gemgame_sala');
$tbl_jogador    = db_table($conn, 'gemgame_sala_jogador');
$tbl_resposta_j = db_table($conn, 'gemgame_sala_resposta');
$tbl_quiz       = db_table($conn, 'gemgame_quiz');
$tbl_pergunta   = db_table($conn, 'gemgame_pergunta');
$tbl_resposta   = db_table($conn, 'gemgame_resposta');

function json_resposta(array $dados, int $status = 200): never {
    http_response_code($status);
    echo json_encode($dados, JSON_UNESCAPED_UNICODE);
    exit;
}

// Cores e símbolos oficiais estilo Kahoot (ordem A, B, C, D)
$SIMBOLOS_KAHOOT = [
    0 => ['cor' => 'vermelho', 'hex' => '#EF4444', 'simbolo' => '▲', 'letra' => 'A'],
    1 => ['cor' => 'azul',     'hex' => '#3B82F6', 'simbolo' => '◆', 'letra' => 'B'],
    2 => ['cor' => 'amarelo',  'hex' => '#EAB308', 'simbolo' => '●', 'letra' => 'C'],
    3 => ['cor' => 'verde',    'hex' => '#10B981', 'simbolo' => '■', 'letra' => 'D'],
    4 => ['cor' => 'roxo',     'hex' => '#8B5CF6', 'simbolo' => '★', 'letra' => 'E'],
    5 => ['cor' => 'laranja',  'hex' => '#F97316', 'simbolo' => '✦', 'letra' => 'F'],
];

/* ============================================================
   1. AÇÃO: CRIAR SALA (HOST)
   ============================================================ */
if ($acao === 'criar_sala') {
    $quiz_id = (int) ($_POST['quiz_id'] ?? 0);
    $host_id = (int) ($_SESSION['id'] ?? 0);

    if ($quiz_id <= 0 || $host_id <= 0) {
        json_resposta(['sucesso' => false, 'erro' => 'Quiz ou usuário inválido. Faça login para hospedar uma sala.'], 400);
    }

    // Verifica se quiz tem perguntas
    $stmtQ = $conn->prepare("SELECT id, titulo FROM {$tbl_quiz} WHERE id = ?");
    $stmtQ->bind_param('i', $quiz_id);
    $stmtQ->execute();
    $quizInfo = $stmtQ->get_result()->fetch_assoc();
    $stmtQ->close();

    if (!$quizInfo) {
        json_resposta(['sucesso' => false, 'erro' => 'Quiz não encontrado.'], 404);
    }

    // Gera PIN único de 6 dígitos
    $pin = '';
    for ($tentativa = 0; $tentativa < 10; $tentativa++) {
        $pin_candidato = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        $stmtChk = $conn->prepare("SELECT id FROM {$tbl_sala} WHERE pin = ? AND estado != 'finalizado' LIMIT 1");
        $stmtChk->bind_param('s', $pin_candidato);
        $stmtChk->execute();
        $existe = $stmtChk->get_result()->num_rows > 0;
        $stmtChk->close();
        if (!$existe) {
            $pin = $pin_candidato;
            break;
        }
    }

    if ($pin === '') {
        json_resposta(['sucesso' => false, 'erro' => 'Não foi possível gerar um PIN no momento. Tente novamente.'], 500);
    }

    // Insere sala
    $stmtIns = $conn->prepare("INSERT INTO {$tbl_sala} (pin, quiz_id, host_id, estado, duracao_pergunta) VALUES (?, ?, ?, 'aguardando', 25)");
    $stmtIns->bind_param('sii', $pin, $quiz_id, $host_id);
    if ($stmtIns->execute()) {
        $sala_id = (int) $conn->insert_id;
        $stmtIns->close();
        json_resposta([
            'sucesso' => true,
            'sala_id' => $sala_id,
            'pin'     => $pin,
            'quiz'    => $quizInfo
        ]);
    } else {
        $stmtIns->close();
        json_resposta(['sucesso' => false, 'erro' => 'Erro ao registrar sala no banco.'], 500);
    }
}

/* ============================================================
   2. AÇÃO: ENTRAR NA SALA (JOGADOR)
   ============================================================ */
if ($acao === 'entrar_sala') {
    $pin      = trim($_POST['pin'] ?? '');
    $nickname = trim($_POST['nickname'] ?? '');
    $user_id  = isset($_SESSION['id']) ? (int) $_SESSION['id'] : null;

    if ($pin === '' || mb_strlen($nickname) < 2) {
        json_resposta(['sucesso' => false, 'erro' => 'Informe o PIN e um apelido com no mínimo 2 letras.'], 400);
    }

    // Busca sala ativa
    $stmtSala = $conn->prepare("SELECT id, estado, quiz_id FROM {$tbl_sala} WHERE pin = ? LIMIT 1");
    $stmtSala->bind_param('s', $pin);
    $stmtSala->execute();
    $sala = $stmtSala->get_result()->fetch_assoc();
    $stmtSala->close();

    if (!$sala) {
        json_resposta(['sucesso' => false, 'erro' => 'PIN de jogo não encontrado ou expirado.'], 404);
    }

    if ($sala['estado'] === 'finalizado') {
        json_resposta(['sucesso' => false, 'erro' => 'Este jogo já foi encerrado.'], 400);
    }

    $sala_id = (int) $sala['id'];

    // Sanitiza nickname
    $nickname = mb_substr($nickname, 0, 30);

    // Evita duplicidade exata de nickname na mesma sala
    $stmtChkNick = $conn->prepare("SELECT id FROM {$tbl_jogador} WHERE sala_id = ? AND LOWER(nickname) = LOWER(?) LIMIT 1");
    $stmtChkNick->bind_param('is', $sala_id, $nickname);
    $stmtChkNick->execute();
    if ($stmtChkNick->get_result()->num_rows > 0) {
        $nickname .= ' ' . random_int(1, 99);
    }
    $stmtChkNick->close();

    $stmtInsJ = $conn->prepare("INSERT INTO {$tbl_jogador} (sala_id, usuario_id, nickname, pontuacao, acertos) VALUES (?, ?, ?, 0, 0)");
    $stmtInsJ->bind_param('iis', $sala_id, $user_id, $nickname);
    if ($stmtInsJ->execute()) {
        $jogador_id = (int) $conn->insert_id;
        $stmtInsJ->close();

        // Guarda na sessão para persistência
        $_SESSION['gemgame_sala_id']    = $sala_id;
        $_SESSION['gemgame_jogador_id'] = $jogador_id;
        $_SESSION['gemgame_nickname']   = $nickname;

        json_resposta([
            'sucesso'    => true,
            'sala_id'    => $sala_id,
            'jogador_id' => $jogador_id,
            'nickname'   => $nickname,
            'pin'        => $pin
        ]);
    } else {
        $stmtInsJ->close();
        json_resposta(['sucesso' => false, 'erro' => 'Erro ao ingressar na sala.'], 500);
    }
}

/* ============================================================
   3. AÇÃO: STATUS DO HOST (POLLING 1s DO PROFESSOR/PROJETOR)
   ============================================================ */
if ($acao === 'host_status') {
    $sala_id = (int) ($_GET['sala_id'] ?? $_POST['sala_id'] ?? 0);
    if ($sala_id <= 0) json_resposta(['sucesso' => false, 'erro' => 'ID de sala inválido.'], 400);

    $stmt = $conn->prepare("SELECT s.*, q.titulo AS quiz_titulo FROM {$tbl_sala} s INNER JOIN {$tbl_quiz} q ON q.id = s.quiz_id WHERE s.id = ?");
    $stmt->bind_param('i', $sala_id);
    $stmt->execute();
    $sala = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$sala) json_resposta(['sucesso' => false, 'erro' => 'Sala não encontrada.'], 404);

    $estado = $sala['estado'];
    $duracao = (int) $sala['duracao_pergunta'];
    $agora = time();

    // Busca total de jogadores na sala
    $stmtJ = $conn->prepare("SELECT id, nickname, pontuacao, acertos FROM {$tbl_jogador} WHERE sala_id = ? ORDER BY pontuacao DESC, id ASC");
    $stmtJ->bind_param('i', $sala_id);
    $stmtJ->execute();
    $resJ = $stmtJ->get_result();
    $jogadores = [];
    while ($r = $resJ->fetch_assoc()) {
        $jogadores[] = [
            'id'        => (int) $r['id'],
            'nickname'  => $r['nickname'],
            'pontuacao' => (int) $r['pontuacao'],
            'acertos'   => (int) $r['acertos']
        ];
    }
    $stmtJ->close();
    $total_jogadores = count($jogadores);

    // Carrega perguntas do quiz
    $quiz_id = (int) $sala['quiz_id'];
    $stmtP = $conn->prepare("SELECT id, enunciado, imagem, ordem FROM {$tbl_pergunta} WHERE quiz_id = ? ORDER BY ordem ASC, id ASC");
    $stmtP->bind_param('i', $quiz_id);
    $stmtP->execute();
    $perguntas = $stmtP->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmtP->close();
    $total_perguntas = count($perguntas);

    $indice_atual = (int) $sala['pergunta_atual_indice'];
    $pergunta_atual = $perguntas[$indice_atual] ?? null;

    $tempo_inicio = (int) ($sala['tempo_inicio_pergunta'] ?? $agora);
    $tempo_decorrido = max(0, $agora - $tempo_inicio);
    $tempo_restante = max(0, $duracao - $tempo_decorrido);

    $respostas_contagem = [];
    $total_respostas_enviadas = 0;
    $respostas_opcoes = [];

    if ($pergunta_atual) {
        $pid = (int) $pergunta_atual['id'];

        // Busca alternativas da pergunta
        $stmtR = $conn->prepare("SELECT id, texto, correta FROM {$tbl_resposta} WHERE pergunta_id = ? ORDER BY id ASC");
        $stmtR->bind_param('i', $pid);
        $stmtR->execute();
        $resR = $stmtR->get_result();
        $idxOp = 0;
        while ($r = $resR->fetch_assoc()) {
            $simb = $SIMBOLOS_KAHOOT[$idxOp % count($SIMBOLOS_KAHOOT)];
            $respostas_opcoes[] = [
                'id'       => (int) $r['id'],
                'texto'    => $r['texto'],
                'correta'  => (bool) $r['correta'],
                'cor'      => $simb['cor'],
                'hex'      => $simb['hex'],
                'simbolo'  => $simb['simbolo'],
                'letra'    => $simb['letra'],
                'votos'    => 0
            ];
            $idxOp++;
        }
        $stmtR->close();

        // Contabiliza respostas dos jogadores nesta pergunta
        $stmtV = $conn->prepare("SELECT resposta_id, COUNT(*) as qtd FROM {$tbl_resposta_j} WHERE sala_id = ? AND pergunta_id = ? GROUP BY resposta_id");
        $stmtV->bind_param('ii', $sala_id, $pid);
        $stmtV->execute();
        $resV = $stmtV->get_result();
        $votos_map = [];
        while ($rowV = $resV->fetch_assoc()) {
            $votos_map[(int) $rowV['resposta_id']] = (int) $rowV['qtd'];
            $total_respostas_enviadas += (int) $rowV['qtd'];
        }
        $stmtV->close();

        foreach ($respostas_opcoes as &$op) {
            $op['votos'] = $votos_map[$op['id']] ?? 0;
        }
        unset($op);

        // TRANSIÇÃO AUTOMÁTICA: se estiver em 'pergunta' e (o tempo acabou OU todos os jogadores responderam)
        if ($estado === 'pergunta') {
            $todos_responderam = ($total_jogadores > 0 && $total_respostas_enviadas >= $total_jogadores);
            $tempo_esgotado    = ($tempo_restante <= 0);

            if ($todos_responderam || $tempo_esgotado) {
                $stmtUp = $conn->prepare("UPDATE {$tbl_sala} SET estado = 'resultado_pergunta' WHERE id = ?");
                $stmtUp->bind_param('i', $sala_id);
                $stmtUp->execute();
                $stmtUp->close();
                $estado = 'resultado_pergunta';
            }
        }
    }

    json_resposta([
        'sucesso'            => true,
        'estado'             => $estado,
        'pin'                => $sala['pin'],
        'quiz_titulo'        => $sala['quiz_titulo'],
        'total_jogadores'    => $total_jogadores,
        'jogadores'          => $jogadores,
        'pergunta_indice'    => $indice_atual,
        'total_perguntas'    => $total_perguntas,
        'tempo_restante'     => $tempo_restante,
        'duracao'            => $duracao,
        'respostas_enviadas' => $total_respostas_enviadas,
        'pergunta_atual'     => $pergunta_atual ? [
            'id'        => (int) $pergunta_atual['id'],
            'enunciado' => $pergunta_atual['enunciado'],
            'imagem'    => $pergunta_atual['imagem'],
            'opcoes'    => $respostas_opcoes
        ] : null
    ]);
}

/* ============================================================
   4. AÇÃO: COMANDOS DO HOST (INICIAR, ENCERRAR, AVANÇAR)
   ============================================================ */
if ($acao === 'host_comando') {
    $sala_id  = (int) ($_POST['sala_id'] ?? 0);
    $comando  = trim($_POST['comando'] ?? '');
    $host_id  = (int) ($_SESSION['id'] ?? 0);

    // Valida que quem está enviando é o host da sala
    $stmtS = $conn->prepare("SELECT id, estado, quiz_id, pergunta_atual_indice FROM {$tbl_sala} WHERE id = ? AND host_id = ?");
    $stmtS->bind_param('ii', $sala_id, $host_id);
    $stmtS->execute();
    $sala = $stmtS->get_result()->fetch_assoc();
    $stmtS->close();

    if (!$sala) {
        json_resposta(['sucesso' => false, 'erro' => 'Acesso não autorizado para gerenciar esta sala.'], 403);
    }

    $agora = time();

    if ($comando === 'iniciar_jogo') {
        // Busca primeira pergunta
        $stmtP = $conn->prepare("SELECT id FROM {$tbl_pergunta} WHERE quiz_id = ? ORDER BY ordem ASC, id ASC LIMIT 1");
        $stmtP->bind_param('i', $sala['quiz_id']);
        $stmtP->execute();
        $pPrimeira = $stmtP->get_result()->fetch_assoc();
        $stmtP->close();

        $pid = $pPrimeira ? (int) $pPrimeira['id'] : null;

        $stmtUp = $conn->prepare("UPDATE {$tbl_sala} SET estado = 'pergunta', pergunta_atual_indice = 0, pergunta_atual_id = ?, tempo_inicio_pergunta = ? WHERE id = ?");
        $stmtUp->bind_param('iii', $pid, $agora, $sala_id);
        $stmtUp->execute();
        $stmtUp->close();

        json_resposta(['sucesso' => true, 'estado' => 'pergunta']);
    }

    if ($comando === 'encerrar_pergunta') {
        // Encerra a pergunta atual imediatamente (Professor clicou no botão)
        $stmtUp = $conn->prepare("UPDATE {$tbl_sala} SET estado = 'resultado_pergunta' WHERE id = ?");
        $stmtUp->bind_param('i', $sala_id);
        $stmtUp->execute();
        $stmtUp->close();

        json_resposta(['sucesso' => true, 'estado' => 'resultado_pergunta']);
    }

    if ($comando === 'ver_ranking') {
        $stmtUp = $conn->prepare("UPDATE {$tbl_sala} SET estado = 'ranking' WHERE id = ?");
        $stmtUp->bind_param('i', $sala_id);
        $stmtUp->execute();
        $stmtUp->close();

        json_resposta(['sucesso' => true, 'estado' => 'ranking']);
    }

    if ($comando === 'proxima_pergunta') {
        $novoIndice = ((int) $sala['pergunta_atual_indice']) + 1;

        // Verifica se há próxima pergunta
        $stmtP = $conn->prepare("SELECT id FROM {$tbl_pergunta} WHERE quiz_id = ? ORDER BY ordem ASC, id ASC LIMIT 1 OFFSET ?");
        $stmtP->bind_param('ii', $sala['quiz_id'], $novoIndice);
        $stmtP->execute();
        $proxP = $stmtP->get_result()->fetch_assoc();
        $stmtP->close();

        if ($proxP) {
            $proxPid = (int) $proxP['id'];
            $stmtUp = $conn->prepare("UPDATE {$tbl_sala} SET estado = 'pergunta', pergunta_atual_indice = ?, pergunta_atual_id = ?, tempo_inicio_pergunta = ? WHERE id = ?");
            $stmtUp->bind_param('iiii', $novoIndice, $proxPid, $agora, $sala_id);
            $stmtUp->execute();
            $stmtUp->close();

            json_resposta(['sucesso' => true, 'estado' => 'pergunta', 'pergunta_indice' => $novoIndice]);
        } else {
            // Acabaram as perguntas -> finaliza jogo
            $stmtUp = $conn->prepare("UPDATE {$tbl_sala} SET estado = 'finalizado' WHERE id = ?");
            $stmtUp->bind_param('i', $sala_id);
            $stmtUp->execute();
            $stmtUp->close();

            json_resposta(['sucesso' => true, 'estado' => 'finalizado']);
        }
    }

    json_resposta(['sucesso' => false, 'erro' => 'Comando não reconhecido.'], 400);
}

/* ============================================================
   5. AÇÃO: STATUS DO JOGADOR (POLLING 1s DO ALUNO)
   ============================================================ */
if ($acao === 'player_status') {
    $sala_id    = (int) ($_GET['sala_id'] ?? $_POST['sala_id'] ?? 0);
    $jogador_id = (int) ($_GET['jogador_id'] ?? $_POST['jogador_id'] ?? 0);

    if ($sala_id <= 0 || $jogador_id <= 0) {
        json_resposta(['sucesso' => false, 'erro' => 'Dados de jogador inválidos.'], 400);
    }

    // Atualiza heartbeat do jogador
    $stmtPing = $conn->prepare("UPDATE {$tbl_jogador} SET ultimo_ping = NOW() WHERE id = ?");
    $stmtPing->bind_param('i', $jogador_id);
    $stmtPing->execute();
    $stmtPing->close();

    // Consulta sala
    $stmtS = $conn->prepare("SELECT s.*, q.titulo AS quiz_titulo FROM {$tbl_sala} s INNER JOIN {$tbl_quiz} q ON q.id = s.quiz_id WHERE s.id = ?");
    $stmtS->bind_param('i', $sala_id);
    $stmtS->execute();
    $sala = $stmtS->get_result()->fetch_assoc();
    $stmtS->close();

    if (!$sala) json_resposta(['sucesso' => false, 'erro' => 'Sala não existe mais.'], 404);

    // Consulta dados do jogador
    $stmtJ = $conn->prepare("SELECT id, nickname, pontuacao, acertos FROM {$tbl_jogador} WHERE id = ?");
    $stmtJ->bind_param('i', $jogador_id);
    $stmtJ->execute();
    $meuJogador = $stmtJ->get_result()->fetch_assoc();
    $stmtJ->close();

    if (!$meuJogador) json_resposta(['sucesso' => false, 'erro' => 'Jogador desconectado.'], 404);

    // Calcula posição no ranking
    $stmtRank = $conn->prepare("SELECT COUNT(*) + 1 as posicao FROM {$tbl_jogador} WHERE sala_id = ? AND pontuacao > ?");
    $minhaPont = (int) $meuJogador['pontuacao'];
    $stmtRank->bind_param('ii', $sala_id, $minhaPont);
    $stmtRank->execute();
    $rankInfo = $stmtRank->get_result()->fetch_assoc();
    $minha_posicao = (int) ($rankInfo['posicao'] ?? 1);
    $stmtRank->close();

    $estado = $sala['estado'];
    $duracao = (int) $sala['duracao_pergunta'];
    $tempo_inicio = (int) ($sala['tempo_inicio_pergunta'] ?? time());
    $tempo_restante = max(0, $duracao - (time() - $tempo_inicio));

    $pergunta_id = (int) ($sala['pergunta_atual_id'] ?? 0);
    $ja_respondeu = false;
    $minha_resposta_info = null;

    if ($pergunta_id > 0) {
        $stmtRespJ = $conn->prepare("SELECT rj.*, r.correta, r.texto FROM {$tbl_resposta_j} rj INNER JOIN {$tbl_resposta} r ON r.id = rj.resposta_id WHERE rj.sala_id = ? AND rj.pergunta_id = ? AND rj.jogador_id = ? LIMIT 1");
        $stmtRespJ->bind_param('iii', $sala_id, $pergunta_id, $jogador_id);
        $stmtRespJ->execute();
        $minha_resposta = $stmtRespJ->get_result()->fetch_assoc();
        $stmtRespJ->close();

        if ($minha_resposta) {
            $ja_respondeu = true;
            $minha_resposta_info = [
                'resposta_id'   => (int) $minha_resposta['resposta_id'],
                'correta'       => (bool) $minha_resposta['correta'],
                'pontos_ganhos' => (int) $minha_resposta['pontos_ganhos']
            ];
        }
    }

    // Carrega botões das opções no formato Kahoot (sem o texto do enunciado!)
    $opcoes_botoes = [];
    if ($pergunta_id > 0) {
        $stmtOps = $conn->prepare("SELECT id FROM {$tbl_resposta} WHERE pergunta_id = ? ORDER BY id ASC");
        $stmtOps->bind_param('i', $pergunta_id);
        $stmtOps->execute();
        $resOps = $stmtOps->get_result();
        $opIdx = 0;
        while ($opRow = $resOps->fetch_assoc()) {
            $simb = $SIMBOLOS_KAHOOT[$opIdx % count($SIMBOLOS_KAHOOT)];
            $opcoes_botoes[] = [
                'id'      => (int) $opRow['id'],
                'cor'     => $simb['cor'],
                'hex'     => $simb['hex'],
                'simbolo' => $simb['simbolo'],
                'letra'   => $simb['letra']
            ];
            $opIdx++;
        }
        $stmtOps->close();
    }

    json_resposta([
        'sucesso'             => true,
        'estado'              => $estado,
        'quiz_titulo'         => $sala['quiz_titulo'],
        'pergunta_indice'     => (int) $sala['pergunta_atual_indice'],
        'tempo_restante'      => $tempo_restante,
        'duracao'             => $duracao,
        'minha_pontuacao'     => (int) $meuJogador['pontuacao'],
        'meus_acertos'        => (int) $meuJogador['acertos'],
        'minha_posicao'       => $minha_posicao,
        'ja_respondeu'        => $ja_respondeu,
        'minha_resposta'      => $minha_resposta_info,
        'opcoes_botoes'       => $opcoes_botoes
    ]);
}

/* ============================================================
   6. AÇÃO: ENVIAR RESPOSTA DO JOGADOR (CLIQUE NO BOTÃO)
   ============================================================ */
if ($acao === 'player_responder') {
    $sala_id     = (int) ($_POST['sala_id'] ?? 0);
    $jogador_id  = (int) ($_POST['jogador_id'] ?? 0);
    $resposta_id = (int) ($_POST['resposta_id'] ?? 0);

    if ($sala_id <= 0 || $jogador_id <= 0 || $resposta_id <= 0) {
        json_resposta(['sucesso' => false, 'erro' => 'Dados de resposta incompletos.'], 400);
    }

    // Consulta sala
    $stmtS = $conn->prepare("SELECT estado, pergunta_atual_id, tempo_inicio_pergunta, duracao_pergunta FROM {$tbl_sala} WHERE id = ?");
    $stmtS->bind_param('i', $sala_id);
    $stmtS->execute();
    $sala = $stmtS->get_result()->fetch_assoc();
    $stmtS->close();

    if (!$sala || $sala['estado'] !== 'pergunta') {
        json_resposta(['sucesso' => false, 'erro' => 'A pergunta não está aberta para respostas no momento.'], 400);
    }

    $pid = (int) $sala['pergunta_atual_id'];
    $duracao = (int) $sala['duracao_pergunta'];
    $tempo_inicio = (int) $sala['tempo_inicio_pergunta'];
    $agora = time();
    $tempo_decorrido = max(0, $agora - $tempo_inicio);

    if ($tempo_decorrido > ($duracao + 2)) {
        json_resposta(['sucesso' => false, 'erro' => 'O tempo para esta pergunta já se esgotou.'], 400);
    }

    // Verifica se já respondeu
    $stmtJa = $conn->prepare("SELECT id FROM {$tbl_resposta_j} WHERE sala_id = ? AND pergunta_id = ? AND jogador_id = ?");
    $stmtJa->bind_param('iii', $sala_id, $pid, $jogador_id);
    $stmtJa->execute();
    if ($stmtJa->get_result()->num_rows > 0) {
        $stmtJa->close();
        json_resposta(['sucesso' => false, 'erro' => 'Você já respondeu a esta pergunta.'], 400);
    }
    $stmtJa->close();

    // Verifica se a resposta é correta
    $stmtCor = $conn->prepare("SELECT correta FROM {$tbl_resposta} WHERE id = ? AND pergunta_id = ?");
    $stmtCor->bind_param('ii', $resposta_id, $pid);
    $stmtCor->execute();
    $respRow = $stmtCor->get_result()->fetch_assoc();
    $stmtCor->close();

    if (!$respRow) {
        json_resposta(['sucesso' => false, 'erro' => 'Alternativa inválida.'], 400);
    }

    $ehCorreta = (bool) $respRow['correta'];
    $tempo_ms = $tempo_decorrido * 1000;

    // Cálculo da pontuação estilo Kahoot (Base 1000 com bônus de velocidade para acertos)
    $pontos_ganhos = 0;
    if ($ehCorreta) {
        // Quanto mais rápido responder, mais perto de 1000 pontos (mínimo 500 para acerto)
        $fator_tempo = max(0, ($duracao - $tempo_decorrido) / max(1, $duracao));
        $pontos_ganhos = (int) round(500 + (500 * $fator_tempo));
    }

    // Registra resposta
    $stmtInsR = $conn->prepare("INSERT INTO {$tbl_resposta_j} (sala_id, pergunta_id, jogador_id, resposta_id, correta, tempo_resposta_ms, pontos_ganhos) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $ehCorretaInt = $ehCorreta ? 1 : 0;
    $stmtInsR->bind_param('iiiiiii', $sala_id, $pid, $jogador_id, $resposta_id, $ehCorretaInt, $tempo_ms, $pontos_ganhos);
    $stmtInsR->execute();
    $stmtInsR->close();

    // Atualiza pontuação do jogador
    $stmtUpJ = $conn->prepare("UPDATE {$tbl_jogador} SET pontuacao = pontuacao + ?, acertos = acertos + ? WHERE id = ?");
    $stmtUpJ->bind_param('iii', $pontos_ganhos, $ehCorretaInt, $jogador_id);
    $stmtUpJ->execute();
    $stmtUpJ->close();

    // AUTO-ENCERRAMENTO: Verifica se todos os jogadores responderam
    $stmtTotalJ = $conn->prepare("SELECT COUNT(*) as tot FROM {$tbl_jogador} WHERE sala_id = ?");
    $stmtTotalJ->bind_param('i', $sala_id);
    $stmtTotalJ->execute();
    $totJ = (int) ($stmtTotalJ->get_result()->fetch_assoc()['tot'] ?? 0);
    $stmtTotalJ->close();

    $stmtTotalR = $conn->prepare("SELECT COUNT(*) as tot FROM {$tbl_resposta_j} WHERE sala_id = ? AND pergunta_id = ?");
    $stmtTotalR->bind_param('ii', $sala_id, $pid);
    $stmtTotalR->execute();
    $totR = (int) ($stmtTotalR->get_result()->fetch_assoc()['tot'] ?? 0);
    $stmtTotalR->close();

    if ($totJ > 0 && $totR >= $totJ) {
        $stmtUpSala = $conn->prepare("UPDATE {$tbl_sala} SET estado = 'resultado_pergunta' WHERE id = ?");
        $stmtUpSala->bind_param('i', $sala_id);
        $stmtUpSala->execute();
        $stmtUpSala->close();
    }

    json_resposta([
        'sucesso'       => true,
        'correta'       => $ehCorreta,
        'pontos_ganhos' => $pontos_ganhos
    ]);
}

json_resposta(['sucesso' => false, 'erro' => 'Ação não especificada.'], 400);
