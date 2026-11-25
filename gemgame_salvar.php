<?php
/* ============================================================
   gemgame_salvar.php — Endpoint: Salva Quiz no Banco
   Sketchbook Educacional | POST only | Autenticado
   ============================================================ */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/proteger.php';

// Aceita apenas POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: gemgame_listar.php');
    exit;
}

/* ── Helpers ── */
function redirecionar_erro(string $msg): never {
    header('Location: gemgame_criar.php?erro=' . urlencode($msg));
    exit;
}

/* ── 1. Validação dos campos principais ── */
$titulo   = trim($_POST['titulo']   ?? '');
$descricao = trim($_POST['descricao'] ?? '');
$perguntas = $_POST['perguntas'] ?? [];

if ($titulo === '') {
    redirecionar_erro('O título do quiz não pode estar vazio.');
}
if (!is_array($perguntas) || count($perguntas) === 0) {
    redirecionar_erro('O quiz precisa ter pelo menos uma pergunta.');
}

/* ── 2. Validação, sanitização e processamento de upload de cada pergunta ── */
$perguntas_validas = [];
$dir_upload = __DIR__ . '/uploads/gemgame/';
if (!is_dir($dir_upload)) {
    @mkdir($dir_upload, 0777, true);
}

$num_pergunta = 1;
foreach ($perguntas as $idx => $pergunta) {
    $enunciado = trim($pergunta['enunciado'] ?? '');
    $respostas = $pergunta['respostas'] ?? [];
    $correta   = isset($pergunta['correta']) ? (int) $pergunta['correta'] : -1;
    $ordem     = (int) ($pergunta['ordem'] ?? ($num_pergunta - 1));

    if ($enunciado === '') {
        redirecionar_erro("A pergunta {$num_pergunta} está sem enunciado.");
    }
    if (!is_array($respostas) || count($respostas) < 4) {
        redirecionar_erro("A pergunta {$num_pergunta} precisa de pelo menos 4 respostas.");
    }
    if ($correta < 0 || !isset($respostas[$correta])) {
        redirecionar_erro("A pergunta {$num_pergunta} não tem uma resposta marcada como correta.");
    }

    $correta_texto = trim($respostas[$correta] ?? '');
    if ($correta_texto === '') {
        redirecionar_erro("A resposta correta da pergunta {$num_pergunta} não pode ser vazia.");
    }

    // Filtra respostas vazias
    $respostas_saneadas = [];
    foreach ($respostas as $r_idx => $texto) {
        $texto_limpo = trim($texto);
        if ($texto_limpo !== '') {
            $respostas_saneadas[$r_idx] = $texto_limpo;
        }
    }

    if (count($respostas_saneadas) < 4) {
        redirecionar_erro("A pergunta {$num_pergunta} precisa de pelo menos 4 respostas não vazias.");
    }

    // Processamento de upload de imagem
    $caminho_imagem = null;
    $arquivo = $_FILES['perguntas']['tmp_name'][$idx]['imagem'] ?? '';
    $erro_up = $_FILES['perguntas']['error'][$idx]['imagem'] ?? UPLOAD_ERR_NO_FILE;

    if ($erro_up === UPLOAD_ERR_OK && $arquivo !== '' && is_uploaded_file($arquivo)) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($arquivo);
        $permitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

        if (!in_array($mime, $permitidos, true)) {
            redirecionar_erro("Imagem inválida na pergunta {$num_pergunta}. Use JPG, PNG, GIF ou WebP.");
        }

        if ($_FILES['perguntas']['size'][$idx]['imagem'] > 3 * 1024 * 1024) {
            redirecionar_erro("A imagem da pergunta {$num_pergunta} excede o limite de 3 MB.");
        }

        $ext = match($mime) {
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/gif'  => 'gif',
            'image/webp' => 'webp',
        };
        $nome_arquivo = 'q_' . uniqid('', true) . '.' . $ext;
        $destino = $dir_upload . $nome_arquivo;

        if (!move_uploaded_file($arquivo, $destino)) {
            redirecionar_erro("Falha ao salvar a imagem da pergunta {$num_pergunta}. Tente novamente.");
        }

        $caminho_imagem = 'uploads/gemgame/' . $nome_arquivo;
    }

    $perguntas_validas[] = [
        'enunciado' => $enunciado,
        'ordem'     => $ordem,
        'respostas' => $respostas_saneadas,
        'correta'   => $correta,
        'imagem'    => $caminho_imagem,
    ];

    $num_pergunta++;
}

/* ── 4. Persistência no banco (transação) ── */
$tbl_quiz     = db_table($conn, 'gemgame_quiz');
$tbl_pergunta = db_table($conn, 'gemgame_pergunta');
$tbl_resposta = db_table($conn, 'gemgame_resposta');

$conn->begin_transaction();

try {
    // 4.1 Insere o quiz
    $stmt_quiz = $conn->prepare(
        "INSERT INTO {$tbl_quiz} (titulo, descricao) VALUES (?, ?)"
    );
    if (!$stmt_quiz) throw new RuntimeException('Erro ao preparar inserção do quiz.');

    $stmt_quiz->bind_param('ss', $titulo, $descricao);
    if (!$stmt_quiz->execute()) throw new RuntimeException('Erro ao salvar o quiz no banco.');
    $quiz_id = (int) $conn->insert_id;
    $stmt_quiz->close();

    // 4.2 Insere cada pergunta + respostas
    $stmt_perg = $conn->prepare(
        "INSERT INTO {$tbl_pergunta} (quiz_id, enunciado, imagem, ordem) VALUES (?, ?, ?, ?)"
    );
    $stmt_resp = $conn->prepare(
        "INSERT INTO {$tbl_resposta} (pergunta_id, texto, correta) VALUES (?, ?, ?)"
    );
    if (!$stmt_perg || !$stmt_resp) {
        throw new RuntimeException('Erro ao preparar inserção de perguntas/respostas.');
    }

    foreach ($perguntas_validas as $p) {
        $stmt_perg->bind_param('issi', $quiz_id, $p['enunciado'], $p['imagem'], $p['ordem']);
        if (!$stmt_perg->execute()) throw new RuntimeException('Erro ao salvar pergunta.');
        $pergunta_id = (int) $conn->insert_id;

        foreach ($p['respostas'] as $r_idx => $texto_resp) {
            $eh_correta = ($r_idx === $p['correta']) ? 1 : 0;
            $stmt_resp->bind_param('isi', $pergunta_id, $texto_resp, $eh_correta);
            if (!$stmt_resp->execute()) throw new RuntimeException('Erro ao salvar resposta.');
        }
    }

    $stmt_perg->close();
    $stmt_resp->close();

    $conn->commit();

    // Redireciona para o quiz criado com mensagem de sucesso
    header('Location: gemgame_listar.php?criado=' . $quiz_id);
    exit;

} catch (RuntimeException $e) {
    $conn->rollback();
    error_log('[GemGame] ' . $e->getMessage());
    redirecionar_erro('Ocorreu um erro ao salvar o quiz. Tente novamente.');
}
