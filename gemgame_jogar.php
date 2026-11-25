<?php
/* ============================================================
   gemgame_jogar.php — GemGame: Motor do Quiz
   Sketchbook Educacional | Ensino Fundamental 1
   ============================================================ */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/proteger.php';

$nomeUsuario   = $_SESSION['user'] ?? '';
$userId        = (int) ($_SESSION['id'] ?? 0);
$pontosUsuario = (int) ($_SESSION['pontuacao'] ?? 0);

// ── Valida o parâmetro do quiz ──
$quiz_id = isset($_GET['quiz']) ? (int) $_GET['quiz'] : 0;
if ($quiz_id <= 0) {
    header('Location: gemgame_listar.php');
    exit;
}

$tbl_quiz     = db_table($conn, 'gemgame_quiz');
$tbl_pergunta = db_table($conn, 'gemgame_pergunta');
$tbl_resposta = db_table($conn, 'gemgame_resposta');

// ── Carrega os dados do quiz ──
$quiz = null;
$stmt = $conn->prepare("SELECT id, titulo, descricao FROM {$tbl_quiz} WHERE id = ?");
if ($stmt) {
    $stmt->bind_param('i', $quiz_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $quiz = $res->fetch_assoc();
    $stmt->close();
}
if (!$quiz) {
    header('Location: gemgame_listar.php');
    exit;
}

// ── Carrega perguntas com respostas ──
$sql_pergs = "
    SELECT p.id, p.enunciado, p.imagem, p.ordem
    FROM {$tbl_pergunta} p
    WHERE p.quiz_id = ?
    ORDER BY p.ordem ASC, p.id ASC
";
$perguntas_raw = [];
$stmt = $conn->prepare($sql_pergs);
if ($stmt) {
    $stmt->bind_param('i', $quiz_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $perguntas_raw[] = $row;
    }
    $stmt->close();
}

if (empty($perguntas_raw)) {
    header('Location: gemgame_listar.php');
    exit;
}

// ── Carrega todas as respostas de uma vez ──
$ids_perguntas = array_column($perguntas_raw, 'id');
$placeholders  = implode(',', array_fill(0, count($ids_perguntas), '?'));
$tipos         = str_repeat('i', count($ids_perguntas));

$respostas_map = [];
$sql_resps = "
    SELECT id, pergunta_id, texto, correta
    FROM {$tbl_resposta}
    WHERE pergunta_id IN ({$placeholders})
    ORDER BY pergunta_id ASC, id ASC
";
$stmt = $conn->prepare($sql_resps);
if ($stmt) {
    $stmt->bind_param($tipos, ...$ids_perguntas);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $respostas_map[(int) $row['pergunta_id']][] = [
            'id'      => (int) $row['id'],
            'texto'   => $row['texto'],
            'correta' => (bool) $row['correta'],
        ];
    }
    $stmt->close();
}

// ── Monta estrutura final e embaralha respostas ──
$perguntas_jogo = [];
shuffle($perguntas_raw); // embaralha ordem das perguntas

foreach ($perguntas_raw as $p) {
    $respostas = $respostas_map[(int) $p['id']] ?? [];
    shuffle($respostas); // Fisher-Yates equivalente via PHP shuffle
    $perguntas_jogo[] = [
        'enunciado' => $p['enunciado'],
        'imagem'    => $p['imagem'],
        'respostas' => $respostas,
    ];
}

// ── Serializa para o JavaScript ──
$quiz_json = json_encode($perguntas_jogo, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP);
$total     = count($perguntas_jogo);

// Registra a partida ao jogar
// (a pontuação efetiva é salva via salvar_pontuacao.php no fim do jogo)
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GemGame — <?= htmlspecialchars($quiz['titulo']) ?></title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>💎</text></svg>">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@500;600;700&family=Nunito:wght@700;800;900&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'papel-branco':     '#FFFFFF',
                        'papel-claro':      '#F8FAF6',
                        'papelao-fundo':    '#c6a175',
                        'tinta-escura':     '#0F172A',
                        'verde-vibrante':   '#00C853',
                        'verde-escuro':     '#054D20',
                        'amarelo-vibrante': '#FFD600',
                        'amarelo-escuro':   '#806B00',
                        'ciano-vibrante':   '#06B6D4',
                        'ciano-escuro':     '#164E63',
                        'vermelho-vivo':    '#EF4444',
                        'vermelho-escuro':  '#991B1B',
                        'laranja-vibrante': '#FF6D00',
                    },
                    fontFamily: {
                        'gooddog': ['Gooddog', 'cursive', 'sans-serif'],
                        'bingo':   ['Bingo', 'cursive', 'sans-serif'],
                    },
                    boxShadow: {
                        'sombra-cartum-ciano':  '0px 6px 0px #164E63',
                        'sombra-cartum-verde':  '0px 6px 0px #054D20',
                        'sombra-cartum-branca': '0px 5px 0px #0F172A',
                        'sombra-cartum-amarela':'0px 6px 0px #806B00',
                        'sombra-cartum-red':    '0px 5px 0px #991B1B',
                    }
                }
            }
        }
    </script>

    <link rel="stylesheet" href="css/index_php.css">
    <link rel="stylesheet" href="css/acessibilidade.css">

    <style>
        /* ── Cards de resposta ── */
        .cartao-resposta {
            cursor: pointer;
            border: 3px solid #164E63;
            border-radius: 20px;
            background: white;
            padding: 14px 18px;
            font-family: 'Bingo', cursive;
            font-weight: 800;
            font-size: 1rem;
            color: #0F172A;
            text-align: left;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0px 5px 0px #164E63;
            transition: transform 0.22s cubic-bezier(0.34, 1.56, 0.64, 1),
                        box-shadow 0.2s ease,
                        background 0.15s ease,
                        border-color 0.15s ease;
            will-change: transform;
            width: 100%;
        }
        .cartao-resposta:hover:not(.desabilitado) {
            transform: translateY(-5px) scale(1.015);
            border-color: #06B6D4;
            box-shadow: 0px 7px 0px #06B6D4;
            background: #ECFEFF;
        }
        .cartao-resposta:active:not(.desabilitado) {
            transform: translateY(1px) scale(0.98);
        }

        /* Letra do cartão (A, B, C, D) */
        .letra-resposta {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #164E63;
            color: #FFD600;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Gooddog', cursive;
            font-size: 1rem;
            font-weight: bold;
            flex-shrink: 0;
            transition: background 0.15s;
        }

        /* Estados de acerto e erro */
        .cartao-resposta.acertou {
            background: #DCFCE7;
            border-color: #054D20;
            box-shadow: 0px 5px 0px #054D20;
            transform: translateY(-5px) scale(1.02);
            animation: pulsar-acerto 0.5s ease;
        }
        .cartao-resposta.acertou .letra-resposta {
            background: #00C853;
        }

        .cartao-resposta.errou {
            background: #FEE2E2;
            border-color: #991B1B;
            box-shadow: 0px 5px 0px #991B1B;
            animation: tremer 0.4s ease;
        }
        .cartao-resposta.errou .letra-resposta {
            background: #EF4444;
        }

        /* Correta destacada quando o aluno erra */
        .cartao-resposta.correta-revelada {
            background: #DCFCE7;
            border-color: #054D20;
            box-shadow: 0px 5px 0px #054D20;
        }
        .cartao-resposta.correta-revelada .letra-resposta {
            background: #00C853;
        }

        .cartao-resposta.desabilitado {
            cursor: not-allowed;
            opacity: 0.6;
        }

        @keyframes pulsar-acerto {
            0%   { transform: translateY(-5px) scale(1.02); }
            50%  { transform: translateY(-8px) scale(1.05); }
            100% { transform: translateY(-5px) scale(1.02); }
        }

        @keyframes tremer {
            0%, 100% { transform: translateX(0); }
            20%      { transform: translateX(-8px); }
            40%      { transform: translateX(8px); }
            60%      { transform: translateX(-5px); }
            80%      { transform: translateX(5px); }
        }

        /* ── Barra de progresso ── */
        #barra-progresso-interna {
            height: 100%;
            border-radius: 9999px;
            background: linear-gradient(90deg, #06B6D4, #00C853);
            transition: width 0.5s ease;
        }

        /* ── Feedback texto ── */
        #texto-feedback {
            min-height: 24px;
            font-family: 'Gooddog', cursive;
            font-size: 1.3rem;
            font-weight: bold;
            letter-spacing: 0.05em;
            transition: color 0.2s;
        }

        /* ── Imagem da pergunta ── */
        #imagem-pergunta {
            max-height: 200px;
            border-radius: 16px;
            border: 3px solid #164E63;
            object-fit: cover;
            box-shadow: 0px 4px 0px #164E63;
        }

        /* ── Entrada da pergunta ── */
        @keyframes entrar-pergunta {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .animar-pergunta {
            animation: entrar-pergunta 0.35s ease forwards;
        }

        /* ── Overlay de resultado ── */
        #overlay-resultado {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.75);
            z-index: 100;
            align-items: center;
            justify-content: center;
            padding: 16px;
            backdrop-filter: blur(4px);
        }
        #overlay-resultado.ativo {
            display: flex;
            animation: entrar-pergunta 0.4s ease forwards;
        }

        #painel-resultado {
            background: white;
            border: 5px solid #0F172A;
            border-radius: 28px;
            box-shadow: 0px 8px 0px #0F172A;
            max-width: 460px;
            width: 100%;
            padding: 36px 32px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 16px;
            text-align: center;
        }

        /* ── Círculo de score ── */
        .circulo-score {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            background: linear-gradient(135deg, #06B6D4, #164E63);
            border: 5px solid #0F172A;
            box-shadow: 0px 6px 0px #0F172A;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body class="font-bingo min-h-screen text-tinta-escura flex flex-col justify-between p-2 sm:p-4 md:p-6 relative">

    <!-- ELEMENTOS OSCILANTES DE FUNDO -->
    <div id="elementos-fundo-oscilantes" aria-hidden="true">
        <div class="elemento-fundo-oscilante elemento-fundo-1"></div>
        <div class="elemento-fundo-oscilante elemento-fundo-2"></div>
        <div class="elemento-fundo-oscilante elemento-fundo-3"></div>
        <div class="elemento-fundo-oscilante elemento-fundo-4"></div>
    </div>

    <!-- OVERLAY DE RESULTADO FINAL -->
    <div id="overlay-resultado" role="dialog" aria-modal="true" aria-label="Resultado do quiz">
        <div id="painel-resultado">

            <!-- Emoji + Círculo de score -->
            <div id="emoji-resultado" class="text-5xl" aria-hidden="true">🏆</div>
            <div class="circulo-score">
                <span id="acertos-finais" class="font-gooddog text-3xl font-bold text-white leading-none">0</span>
                <span class="font-bingo font-bold text-xs text-cyan-200">/ <?= $total ?></span>
            </div>

            <h2 id="titulo-resultado" class="font-gooddog text-3xl font-bold text-tinta-escura tracking-wider">
                RESULTADO!
            </h2>
            <p id="texto-resultado" class="font-bingo font-bold text-sm text-gray-600">
                Calculando…
            </p>

            <!-- Pontuação ganha -->
            <div id="info-pontuacao" class="w-full">
                <!-- Preenchido via JS após fetch -->
            </div>

            <!-- Botões -->
            <div class="flex flex-col sm:flex-row gap-3 w-full mt-2">
                <a href="gemgame_listar.php"
                   class="flex-1 text-center bg-papel-claro font-bingo font-extrabold text-sm text-tinta-escura py-3 rounded-2xl no-underline hover:bg-gray-100 transition-colors"
                   style="border: 3px solid #0F172A; box-shadow: 0px 4px 0px #0F172A;">
                    OUTROS QUIZZES
                </a>
                <button id="btn-jogar-novamente"
                        class="flex-1 bg-ciano-vibrante font-bingo font-extrabold text-sm text-tinta-escura py-3 rounded-2xl cursor-pointer hover:bg-amarelo-vibrante transition-colors"
                        style="border: 3px solid #164E63; box-shadow: 0px 4px 0px #164E63;">
                    JOGAR NOVAMENTE
                </button>
            </div>
        </div>
    </div>

    <!-- LIVRO / CADERNO -->
    <div id="livro-conteiner-principal"
         class="w-full flex-1 flex flex-col caderno-pagina-branca rounded-3xl shadow-2xl p-4 sm:p-6 md:p-8 relative">

        <!-- ESPIRAL DO CADERNO -->
        <div class="w-full flex justify-between items-center -mt-8 sm:-mt-10 mb-4 z-20 px-2 sm:px-6">
            <div class="espiral-caderno"></div>
            <div class="espiral-caderno"></div>
            <div class="espiral-caderno"></div>
            <div class="espiral-caderno"></div>
            <div class="espiral-caderno"></div>
            <div class="espiral-caderno"></div>
            <div class="espiral-caderno"></div>
            <div class="espiral-caderno hidden sm:block"></div>
            <div class="espiral-caderno hidden sm:block"></div>
            <div class="espiral-caderno hidden md:block"></div>
            <div class="espiral-caderno hidden md:block"></div>
            <div class="espiral-caderno hidden md:block"></div>
        </div>

        <!-- CABEÇALHO: HUD do jogo -->
        <header class="w-full flex flex-wrap items-center justify-between gap-3 pb-3 mb-4">
            <!-- Voltar -->
            <a href="gemgame_listar.php"
               class="cartao-dinamico flex items-center gap-2 bg-papel-claro borda-tinta-escura rounded-xl px-4 py-2 shadow-sombra-cartum-branca font-bingo font-bold text-sm text-tinta-escura hover:bg-yellow-100 transition-colors no-underline">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                    <path d="M19 12H5M12 5l-7 7 7 7"/>
                </svg>
                SAIR
            </a>

            <!-- Título do quiz -->
            <div class="flex-1 text-center px-2 min-w-0">
                <span class="font-gooddog text-lg sm:text-xl font-bold text-tinta-escura tracking-wide truncate block">
                    💎 <?= htmlspecialchars($quiz['titulo']) ?>
                </span>
            </div>

            <!-- Placar -->
            <div class="bg-ciano-vibrante borda-tinta-escura rounded-xl px-4 py-1.5 shadow-sombra-cartum-ciano font-bingo font-extrabold text-sm text-tinta-escura flex items-center gap-1.5">
                <span>✅</span>
                <span id="placar-hud">0 / <?= $total ?></span>
            </div>
        </header>

        <!-- BARRA DE PROGRESSO -->
        <div class="w-full mb-5">
            <div class="flex items-center justify-between mb-1.5">
                <span id="label-progresso" class="font-bingo font-bold text-xs text-gray-500">
                    Pergunta 1 de <?= $total ?>
                </span>
                <span id="label-porcentagem" class="font-bingo font-bold text-xs text-ciano-escuro">0%</span>
            </div>
            <div class="w-full h-4 bg-gray-100 rounded-full border-2 border-gray-200 overflow-hidden">
                <div id="barra-progresso-interna" style="width: 0%;"></div>
            </div>
        </div>

        <!-- ÁREA DO JOGO -->
        <main id="area-jogo" class="w-full flex-1 flex flex-col items-center max-w-2xl mx-auto gap-5">

            <!-- ENUNCIADO DA PERGUNTA -->
            <div id="bloco-pergunta" class="w-full animar-pergunta">

                <!-- Imagem (oculta por padrão, exibida via JS) -->
                <div id="container-imagem" class="w-full flex justify-center mb-4 hidden">
                    <img id="imagem-pergunta" src="" alt="Imagem da pergunta">
                </div>

                <!-- Texto da pergunta -->
                <div class="bg-papel-claro rounded-2xl px-5 py-4 mb-2"
                     style="border: 3px solid #0F172A; box-shadow: 0px 5px 0px #0F172A;">
                    <p id="enunciado-pergunta"
                       class="font-gooddog text-xl sm:text-2xl font-bold text-tinta-escura tracking-wide leading-snug text-center">
                        Carregando…
                    </p>
                </div>

                <!-- Feedback de acerto/erro -->
                <p id="texto-feedback" class="text-center mt-1"></p>
            </div>

            <!-- GRADE DE RESPOSTAS -->
            <div id="grade-respostas"
                 class="w-full grid grid-cols-1 sm:grid-cols-2 gap-3">
                <!-- Preenchida dinamicamente pelo JS -->
            </div>

        </main>
    </div>

    <script>
    /* ============================================================
       gemgame_jogar.php — Motor do Quiz (JavaScript)
       ============================================================ */
    (function () {
        'use strict';

        // ── Dados vindos do PHP ──
        const PERGUNTAS = <?= $quiz_json ?>;
        const TOTAL     = PERGUNTAS.length;
        const LETRAS    = ['A', 'B', 'C', 'D', 'E', 'F'];

        // ── Estado do jogo ──
        let indicePerguntaAtual = 0;
        let acertos             = 0;
        let respondeuAtual      = false;

        // ── Referências ao DOM ──
        const elEnunciado      = document.getElementById('enunciado-pergunta');
        const elGradeRespostas = document.getElementById('grade-respostas');
        const elFeedback       = document.getElementById('texto-feedback');
        const elPlacarHud      = document.getElementById('placar-hud');
        const elBarraInterna   = document.getElementById('barra-progresso-interna');
        const elLabelProgresso = document.getElementById('label-progresso');
        const elLabelPorc      = document.getElementById('label-porcentagem');
        const elContainerImg   = document.getElementById('container-imagem');
        const elImagem         = document.getElementById('imagem-pergunta');
        const elOverlay        = document.getElementById('overlay-resultado');
        const elAcertosFinais  = document.getElementById('acertos-finais');
        const elTituloResult   = document.getElementById('titulo-resultado');
        const elTextoResult    = document.getElementById('texto-resultado');
        const elEmojiResult    = document.getElementById('emoji-resultado');
        const elBlocoPerguntas = document.getElementById('bloco-pergunta');

        /* ── Atualiza a barra de progresso ── */
        function atualizarProgresso(indice) {
            const pct = TOTAL > 0 ? Math.round((indice / TOTAL) * 100) : 0;
            elBarraInterna.style.width   = pct + '%';
            elLabelPorc.textContent      = pct + '%';
            elLabelProgresso.textContent = `Pergunta ${Math.min(indice + 1, TOTAL)} de ${TOTAL}`;
        }

        /* ── Renderiza uma pergunta ── */
        function carregarPergunta() {
            if (indicePerguntaAtual >= TOTAL) {
                mostrarResultadoFinal();
                return;
            }

            respondeuAtual = false;
            const perg = PERGUNTAS[indicePerguntaAtual];

            // Animação de entrada
            elBlocoPerguntas.classList.remove('animar-pergunta');
            void elBlocoPerguntas.offsetWidth; // reflow
            elBlocoPerguntas.classList.add('animar-pergunta');

            // Enunciado
            elEnunciado.textContent = perg.enunciado;
            elFeedback.textContent  = '';
            elFeedback.style.color  = '';

            // Imagem
            if (perg.imagem) {
                elImagem.src            = perg.imagem;
                elImagem.alt            = 'Imagem da pergunta ' + (indicePerguntaAtual + 1);
                elContainerImg.classList.remove('hidden');
            } else {
                elContainerImg.classList.add('hidden');
                elImagem.src = '';
            }

            // Progresso
            atualizarProgresso(indicePerguntaAtual);

            // Respostas
            elGradeRespostas.innerHTML = '';
            perg.respostas.forEach((resp, i) => {
                const btn = document.createElement('button');
                btn.type      = 'button';
                btn.className = 'cartao-resposta';
                btn.dataset.correta = resp.correta ? '1' : '0';

                const spanLetra = document.createElement('span');
                spanLetra.className = 'letra-resposta';
                spanLetra.textContent = LETRAS[i] ?? (i + 1);

                const spanTexto = document.createElement('span');
                spanTexto.className = 'flex-1';
                spanTexto.textContent = resp.texto;

                btn.appendChild(spanLetra);
                btn.appendChild(spanTexto);
                btn.addEventListener('click', () => processarResposta(btn));
                elGradeRespostas.appendChild(btn);
            });
        }

        /* ── Processa o clique em uma resposta ── */
        function processarResposta(btnClicado) {
            if (respondeuAtual) return;
            respondeuAtual = true;

            const eCorreta = btnClicado.dataset.correta === '1';

            if (eCorreta) {
                acertos++;
                btnClicado.classList.add('acertou');
                elFeedback.textContent = '✅ Correto! Muito bem!';
                elFeedback.style.color = '#054D20';
                elPlacarHud.textContent = `${acertos} / ${TOTAL}`;
                desabilitarTodos(btnClicado);
                setTimeout(avancarPergunta, 1300);

            } else {
                btnClicado.classList.add('errou');
                elFeedback.textContent = '❌ Ops! Tente de novo!';
                elFeedback.style.color = '#991B1B';

                // Revela a correta
                elGradeRespostas.querySelectorAll('.cartao-resposta').forEach(btn => {
                    if (btn.dataset.correta === '1') btn.classList.add('correta-revelada');
                });

                desabilitarTodos(null);
                setTimeout(avancarPergunta, 1800);
            }
        }

        /* ── Desabilita todos os cards de resposta ── */
        function desabilitarTodos(exceto) {
            elGradeRespostas.querySelectorAll('.cartao-resposta').forEach(btn => {
                if (btn !== exceto) btn.classList.add('desabilitado');
                btn.style.pointerEvents = 'none';
            });
        }

        /* ── Avança para a próxima pergunta ── */
        function avancarPergunta() {
            indicePerguntaAtual++;
            carregarPergunta();
        }

        /* ── Exibe o resultado final e salva a pontuação ── */
        function mostrarResultadoFinal() {
            // Progresso 100%
            elBarraInterna.style.width   = '100%';
            elLabelPorc.textContent      = '100%';
            elLabelProgresso.textContent = `Concluído! ${TOTAL} de ${TOTAL}`;

            const erros = TOTAL - acertos;
            const taxa  = TOTAL > 0 ? acertos / TOTAL : 0;

            // Emoji e mensagem por desempenho
            let emoji, titulo, texto;
            if (taxa === 1) {
                emoji  = '🏆'; titulo = 'PERFEITO!';
                texto  = 'Acertou tudo! Você é incrível!';
            } else if (taxa >= 0.75) {
                emoji  = '🌟'; titulo = 'MUITO BEM!';
                texto  = 'Ótimo resultado! Continue assim!';
            } else if (taxa >= 0.5) {
                emoji  = '👍'; titulo = 'BOA TENTATIVA!';
                texto  = 'Você está aprendendo! Jogue de novo para melhorar!';
            } else {
                emoji  = '💪'; titulo = 'NÃO DESISTA!';
                texto  = 'Praticar é a chave! Tente novamente!';
            }

            elEmojiResult.textContent   = emoji;
            elTituloResult.textContent  = titulo;
            elTextoResult.textContent   = texto;
            elAcertosFinais.textContent = acertos;

            // Salva pontuação via endpoint compartilhado
            fetch('salvar_pontuacao.php', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json' },
                body:    JSON.stringify({
                    questoesRespondidas: TOTAL,
                    acertos:             acertos
                })
            })
            .then(r => r.json())
            .then(data => {
                const elInfo = document.getElementById('info-pontuacao');
                if (elInfo && data && data.sucesso) {
                    const sinal = data.pontuacao_partida >= 0 ? '+' : '';
                    elInfo.innerHTML = `
                        <div class="w-full bg-papel-claro rounded-2xl p-4 text-center"
                             style="border: 3px solid #0F172A; box-shadow: 0px 4px 0px #0F172A;">
                            <span class="block font-bingo font-bold text-xs text-gray-500 uppercase tracking-wider mb-1">
                                PONTOS GANHOS
                            </span>
                            <span class="font-gooddog text-4xl font-bold text-ciano-escuro">
                                ${sinal}${data.pontuacao_partida}
                            </span>
                            <span class="block font-bingo font-bold text-xs text-gray-400 mt-1">
                                Total acumulado: <strong>${data.pontuacao_total} pts</strong>
                            </span>
                        </div>
                    `;
                }
            })
            .catch(() => {});

            // Registra partida no histórico
            fetch('gemgame_registrar_partida.php', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json' },
                body:    JSON.stringify({
                    quiz_id:          <?= $quiz_id ?>,
                    acertos:          acertos,
                    total_perguntas:  TOTAL
                })
            }).catch(() => {});

            elOverlay.classList.add('ativo');
        }

        /* ── Jogar novamente: recarrega a página ── */
        document.getElementById('btn-jogar-novamente').addEventListener('click', () => {
            window.location.reload();
        });

        /* ── Inicia o jogo ── */
        if (TOTAL > 0) {
            carregarPergunta();
        }

    })();
    </script>

    <script src="js/acessibilidade.js"></script>
</body>
</html>
