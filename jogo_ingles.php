<?php
/* ============================================================
   jogo_ingles.php — Complete the Sentence! (Gap Fill Quiz)
   English for Kids — Fundamental 1
   Refatorado para total compatibilidade Linux / MySQL case-sensitive
   ============================================================ */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/proteger.php';

$id_jogo = 1;

// Resolução dinâmica do nome real das tabelas (Linux case-sensitivity)
$tbl_tema      = db_table($conn, 'Tema');
$tbl_pergunta  = db_table($conn, 'Pergunta');
$tbl_conjunto  = db_table($conn, 'Conjunto de respostas erradas');

// 1. Obter todos os temas disponíveis para o jogo de inglês
$temas_disponiveis = [];
$sql_temas = "
    SELECT
        t.`id`,
        t.`nome`,
        COUNT(p.`id`) AS total_perguntas
    FROM {$tbl_tema} t
    LEFT JOIN {$tbl_pergunta} p ON p.`id_tema` = t.`id`
    WHERE t.`id_jogo` = ? OR t.`id_jogo` IS NULL
    GROUP BY t.`id`, t.`nome`
    ORDER BY t.`id` ASC
";
$stmt_temas = $conn->prepare($sql_temas);
if ($stmt_temas) {
    $stmt_temas->bind_param("i", $id_jogo);
    $stmt_temas->execute();
    $res_temas = $stmt_temas->get_result();
    if ($res_temas) {
        while ($t = $res_temas->fetch_assoc()) {
            $temas_disponiveis[] = [
                'id'              => (int) $t['id'],
                'nome'            => $t['nome'],
                'total_perguntas' => (int) $t['total_perguntas'],
            ];
        }
    }
    $stmt_temas->close();
}

// Fallback de segurança: busca temas que possuam perguntas vinculadas caso id_jogo esteja nulo
if (empty($temas_disponiveis)) {
    $sql_fb = "
        SELECT
            t.`id`,
            t.`nome`,
            COUNT(p.`id`) AS total_perguntas
        FROM {$tbl_tema} t
        INNER JOIN {$tbl_pergunta} p ON p.`id_tema` = t.`id`
        GROUP BY t.`id`, t.`nome`
        ORDER BY t.`id` ASC
    ";
    $res_fb = $conn->query($sql_fb);
    if ($res_fb) {
        while ($t = $res_fb->fetch_assoc()) {
            $temas_disponiveis[] = [
                'id'              => (int) $t['id'],
                'nome'            => $t['nome'],
                'total_perguntas' => (int) $t['total_perguntas'],
            ];
        }
    }
}

// 2. Determinar o tema selecionado via GET
$tema_param = $_GET['tema'] ?? $_GET['id_tema'] ?? null;
$tema_ativo = null;

if (!empty($temas_disponiveis)) {
    if ($tema_param !== null && $tema_param !== '') {
        foreach ($temas_disponiveis as $t) {
            if ((string) $t['id'] === (string) $tema_param || strcasecmp($t['nome'], (string) $tema_param) === 0) {
                $tema_ativo = $t;
                break;
            }
        }
    }

    if (!$tema_ativo) {
        $tema_ativo = $temas_disponiveis[0];
    }
}

// 3. Buscar as perguntas do tema selecionado
$perguntas = [];
if ($tema_ativo) {
    $sql_perguntas = "
        SELECT
            p.*,
            t.`nome` AS tema_nome,
            c.`conjunto`
        FROM {$tbl_pergunta} p
        INNER JOIN {$tbl_tema} t ON t.`id` = p.`id_tema`
        LEFT JOIN {$tbl_conjunto} c ON (c.`Pergunta_id` = p.`id` OR c.`pergunta_id` = p.`id`)
        WHERE p.`id_tema` = ?
        ORDER BY p.`id` ASC
    ";
    $stmt_perguntas = $conn->prepare($sql_perguntas);
    if ($stmt_perguntas) {
        $stmt_perguntas->bind_param("i", $tema_ativo['id']);
        $stmt_perguntas->execute();
        $res_perguntas = $stmt_perguntas->get_result();

        if ($res_perguntas) {
            while ($linha = $res_perguntas->fetch_assoc()) {
                $perguntas[] = [
                    'id'             => (int) $linha['id'],
                    'primeira_parte' => $linha['primeira parte'] ?? $linha['primeira_parte'] ?? '',
                    'resposta'       => $linha['resposta'] ?? '',
                    'segunda_parte'  => $linha['segunda parte'] ?? $linha['segunda_parte'] ?? '',
                    'id_tema'        => (int) ($linha['id_tema'] ?? 0),
                    'tema_nome'      => $linha['tema_nome'] ?? '',
                    'erradas'        => json_decode($linha['conjunto'] ?? '[]', true) ?: [],
                ];
            }
        }
        $stmt_perguntas->close();
    }
}

// Randomiza a ordem das perguntas
shuffle($perguntas);

// Lista de fundos disponíveis para a lacuna
$fundos_disponiveis = [
    'fundo_resposta_completar/1.png',
    'fundo_resposta_completar/2.png',
    'fundo_resposta_completar/3.png',
    'fundo_resposta_completar/4.png',
    'fundo_resposta_completar/5.png',
    'fundo_resposta_completar/6.png',
    'fundo_resposta_completar/7.png',
];

$total_fundos = count($fundos_disponiveis);
$ultimo_fundo_idx = -1;
foreach ($perguntas as &$p) {
    do {
        $novo_fundo_idx = mt_rand(0, $total_fundos - 1);
    } while ($total_fundos > 1 && $novo_fundo_idx === $ultimo_fundo_idx);

    $p['fundo_lacuna'] = $fundos_disponiveis[$novo_fundo_idx];
    $ultimo_fundo_idx = $novo_fundo_idx;
}
unset($p);

$total_perguntas = count($perguntas);

// Serializar para o JavaScript com garantia de array JSON válido
$perguntas_json = json_encode($perguntas, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?: '[]';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete the Sentence – English for Kids</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@500;600;700&family=Nunito:wght@700;800;900&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'papel-branco':     '#FFFFFF',
                        'papel-claro':      '#F8FAF6',
                        'papel-linhas':     '#E2E8F0',
                        'papelao-fundo':    '#c6a175',
                        'tinta-escura':     '#0F172A',
                        'verde-vibrante':   '#00C853',
                        'verde-escuro':     '#054D20',
                        'laranja-vibrante': '#FF6D00',
                        'azul-vibrante':    '#00B0FF',
                        'azul-escuro':      '#004B87',
                        'amarelo-vibrante': '#FFD600',
                        'amarelo-escuro':   '#806B00',
                        'rosa-vibrante':    '#FF4081',
                    },
                    fontFamily: {
                        'gooddog': ['Gooddog', 'cursive', 'sans-serif'],
                        'bingo':   ['Bingo', 'cursive', 'sans-serif'],
                        'fredoka': ['Fredoka', 'sans-serif'],
                        'nunito':  ['Nunito', 'sans-serif'],
                    },
                    boxShadow: {
                        'sombra-cartum-verde':  '0px 6px 0px #054D20',
                        'sombra-cartum-azul':   '0px 6px 0px #004B87',
                        'sombra-cartum-branca': '0px 5px 0px #0F172A',
                    }
                }
            }
        }
    </script>

    <!-- CSS do jogo -->
    <link rel="stylesheet" href="css/jogo_ingles.css">
    <!-- CSS de Acessibilidade & Modo Escuro -->
    <link rel="stylesheet" href="css/acessibilidade.css">
</head>
<body class="font-bingo min-h-screen text-tinta-escura flex flex-col justify-between p-2 sm:p-4 md:p-6 relative">

    <!-- ELEMENTOS OSCILANTES DE FUNDO -->
    <div id="elementos-fundo-oscilantes" aria-hidden="true">
        <div class="elemento-fundo-oscilante elemento-fundo-1"></div>
        <div class="elemento-fundo-oscilante elemento-fundo-2"></div>
        <div class="elemento-fundo-oscilante elemento-fundo-3"></div>
        <div class="elemento-fundo-oscilante elemento-fundo-4"></div>
    </div>

    <!-- LIVRO / CADERNO -->
    <div id="livro-conteiner-principal"
         class="w-full flex-1 flex flex-col justify-between caderno-pagina-branca rounded-3xl shadow-2xl p-4 sm:p-6 md:p-8 relative">

        <!-- ESPIRAL DO CADERNO NO TOPO -->
        <div id="espiral-topo-caderno"
             class="w-full flex justify-between items-center -mt-8 sm:-mt-10 mb-4 z-20 px-2 sm:px-6">
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

        <!-- CABEÇALHO -->
        <header id="cabecalho-jogo"
                class="w-full flex flex-wrap items-center justify-between gap-4 pb-4 mb-4">

            <!-- Botão Voltar para Seleção de Temas -->
            <a href="temas_ingles.php"
               class="cartao-dinamico flex items-center gap-2 bg-papel-claro borda-tinta-escura rounded-2xl px-4 py-2 shadow-sombra-cartum-branca font-bingo font-bold text-sm text-tinta-escura hover:bg-yellow-100 transition-colors"
               style="text-decoration: none;">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                    <path d="M19 12H5M12 5l-7 7 7 7"/>
                </svg>
                TOPICS
            </a>

            <!-- Badge do Tema Ativo -->
            <?php if ($tema_ativo): ?>
            <div id="badge-tema-ativo"
                 class="flex items-center gap-2.5 bg-papel-claro borda-tinta-escura rounded-2xl px-4 py-2 shadow-sombra-cartum-branca">
                <span class="inline-block w-3 h-3 bg-azul-vibrante border-2 border-tinta-escura rounded-sm"></span>
                <div>
                    <span class="block text-[10px] font-black text-azul-escuro uppercase tracking-wider font-bingo">TOPIC</span>
                    <span class="block font-bingo text-sm font-extrabold text-tinta-escura uppercase leading-none">
                        <?= htmlspecialchars($tema_ativo['nome']) ?>
                    </span>
                </div>
            </div>
            <?php endif; ?>

            <!-- Placar -->
            <div id="placar-jogo"
                 class="flex items-center gap-3 bg-papel-claro borda-tinta-escura rounded-2xl px-4 py-2 shadow-sombra-cartum-branca">
                <div class="w-7 h-7 bg-amarelo-vibrante border border-tinta-escura rounded-lg flex items-center justify-center font-bold text-xs">
                    PTS
                </div>
                <div>
                    <span class="block text-xs font-black text-verde-vibrante uppercase tracking-wider font-bingo">SCORE</span>
                    <span id="texto-placar"
                          class="block font-bingo text-base font-bold text-tinta-escura leading-none">
                        0 / <?= $total_perguntas ?>
                    </span>
                </div>
            </div>
        </header>

        <!-- ÁREA PRINCIPAL DO JOGO -->
        <main id="area-jogo" class="w-full my-auto flex flex-col items-center gap-6 max-w-3xl mx-auto">

            <!-- TÍTULO -->
            <div class="text-center">
                <h1 class="font-gooddog text-4xl sm:text-5xl md:text-6xl font-bold text-tinta-escura tracking-wider leading-tight">
                    COMPLETE THE SENTENCE!
                </h1>
                <p class="font-bingo text-base sm:text-lg font-extrabold text-gray-600 tracking-wide mt-1">
                    Choose the correct word to fill the gap
                </p>
            </div>

            <!-- SELETOR DE TEMAS -->
            <?php if (count($temas_disponiveis) > 1): ?>
            <div id="seletor-temas" class="flex flex-wrap items-center justify-center gap-2 -mt-2">
                <span class="font-bingo font-black text-xs text-gray-500 uppercase tracking-widest mr-1">TOPICS:</span>
                <?php foreach ($temas_disponiveis as $t): ?>
                    <a href="jogo_ingles.php?tema=<?= urlencode($t['id']) ?>"
                       class="cartao-dinamico px-3.5 py-1.5 rounded-xl font-bingo font-bold text-xs border-2 border-tinta-escura transition-all <?= ($tema_ativo && $tema_ativo['id'] === $t['id']) ? 'bg-amarelo-vibrante text-tinta-escura shadow-sombra-cartum-branca scale-105' : 'bg-papel-claro text-gray-600 hover:bg-white' ?>"
                       style="text-decoration: none;">
                        <?= htmlspecialchars(ucfirst($t['nome'])) ?> (<?= $t['total_perguntas'] ?>)
                    </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- BARRA DE PROGRESSO -->
            <div class="w-full max-w-lg">
                <div class="flex justify-between items-center mb-1 font-bingo font-bold text-sm text-gray-600">
                    <span id="label-progresso">Question 1 of <?= $total_perguntas ?></span>
                    <span id="label-porcentagem">0%</span>
                </div>
                <div id="barra-progresso-externa">
                    <div id="barra-progresso-interna" style="width: 0%"></div>
                </div>
            </div>

            <?php if ($total_perguntas > 0): ?>

            <!-- CAIXA DA PERGUNTA -->
            <div id="caixa-pergunta" class="w-full max-w-2xl">
                <p class="text-center font-bingo text-xs font-black text-gray-400 uppercase tracking-widest mb-3">
                    Fill in the blank:
                </p>
                <div class="texto-pergunta">
                    <span id="texto-primeira-parte"></span>
                    <span id="conteiner-lacuna" class="conteiner-lacuna">
                        <img id="fundo-lacuna-img"
                             class="fundo-lacuna-img"
                             src="<?= htmlspecialchars($perguntas[0]['fundo_lacuna'] ?? 'fundo_resposta_completar/1.png') ?>"
                             alt="Fundo colorido da lacuna"
                             aria-hidden="true"
                             draggable="false">
                        <span id="lacuna-resposta" class="lacuna">_____</span>
                    </span>
                    <span id="texto-segunda-parte"></span>
                </div>
            </div>

            <!-- GRADE DE CARDS DE OPÇÃO -->
            <div id="grade-opcoes"
                 class="w-full max-w-2xl"
                 role="list"
                 aria-label="Answer options">
                <!-- Preenchido pelo JavaScript -->
            </div>

            <!-- FEEDBACK INLINE -->
            <div id="area-feedback"
                 class="text-center font-bingo font-bold text-lg min-h-[2rem]"
                 aria-live="polite">
            </div>

            <?php else: ?>
            <!-- Nenhuma pergunta disponível -->
            <div class="text-center py-12">
                <div class="w-16 h-16 mx-auto mb-4 bg-papel-claro border-2 border-tinta-escura rounded-2xl flex items-center justify-center font-bold text-xl text-gray-500">
                    0
                </div>
                <p class="font-bingo font-bold text-xl text-gray-600">
                    No questions found.<br>
                    Add questions to the database first!
                </p>
                <a href="temas_ingles.php"
                   class="botao-acao mt-6 bg-azul-vibrante text-white inline-flex"
                   style="text-decoration:none;">
                    Back to Topics
                </a>
            </div>
            <?php endif; ?>

        </main>

        <!-- RODAPÉ -->
        <footer id="rodape-jogo"
                class="w-full pt-4 mt-6 flex flex-wrap items-center justify-between gap-3 text-xs md:text-sm font-bingo font-bold text-gray-700">
            <div class="flex items-center gap-2">
                <span class="w-3.5 h-3.5 bg-verde-vibrante border border-tinta-escura rounded-full inline-block"></span>
                <span>ENGLISH FOR KIDS — GAME 01</span>
            </div>
            <div class="bg-papel-claro borda-tinta-escura px-3 py-1.5 rounded-xl text-tinta-escura uppercase font-extrabold shadow-sm font-bingo">
                COMPLETE THE SENTENCE<?= $tema_ativo ? ' — ' . htmlspecialchars(strtoupper($tema_ativo['nome'])) : '' ?>
            </div>
        </footer>

    </div><!-- /livro-conteiner-principal -->

    <!-- OVERLAY DE RESULTADO FINAL -->
    <div id="overlay-resultado" role="dialog" aria-modal="true" aria-labelledby="titulo-resultado">
        <div id="caixa-resultado">

            <div class="text-3xl font-gooddog text-verde-vibrante font-bold mb-2">COMPLETE!</div>

            <h2 id="titulo-resultado"
                class="font-gooddog text-4xl font-bold text-tinta-escura tracking-wider mt-3">
                GREAT JOB!
            </h2>
            <p id="texto-resultado"
               class="font-bingo text-lg font-bold text-gray-600 mt-2 mb-5">
                You finished all questions!
            </p>

            <div class="inline-block bg-amarelo-vibrante borda-tinta-escura rounded-2xl px-6 py-3 shadow-sombra-cartum-branca mb-4">
                <span class="font-bingo font-bold text-2xl text-tinta-escura">
                    Score: <span id="pontos-finais">0</span> / <?= $total_perguntas ?>
                </span>
            </div>

            <div id="info-pontuacao-jogo" class="my-2"></div>

            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <button onclick="reiniciarJogo()"
                        class="botao-acao bg-verde-vibrante text-white">
                    Play Again
                </button>
                <a href="temas_ingles.php"
                   class="botao-acao bg-amarelo-vibrante text-tinta-escura"
                   style="text-decoration: none;">
                    Other Topics
                </a>
                <a href="index.php"
                   class="botao-acao bg-papel-claro text-tinta-escura"
                   style="text-decoration: none;">
                    Menu
                </a>
            </div>

        </div>
    </div>

    <!-- JAVASCRIPT DO JOGO -->
    <script>
    const PERGUNTAS = <?= $perguntas_json ?>;
    const TOTAL     = Array.isArray(PERGUNTAS) ? PERGUNTAS.length : 0;

    let indicePerguntaAtual = 0;
    let pontuacao           = 0;
    let respondeuAtual      = false;

    const elPrimeiraParte    = document.getElementById('texto-primeira-parte');
    const elSegundaParte     = document.getElementById('texto-segunda-parte');
    const elLacuna           = document.getElementById('lacuna-resposta');
    const elConteinerLacuna  = document.getElementById('conteiner-lacuna');
    const elFundoLacunaImg   = document.getElementById('fundo-lacuna-img');
    const elGradeOpcoes      = document.getElementById('grade-opcoes');
    const elFeedback         = document.getElementById('area-feedback');
    const elPlacar           = document.getElementById('texto-placar');
    const elLabelProgresso   = document.getElementById('label-progresso');
    const elLabelPorcentagem = document.getElementById('label-porcentagem');
    const elBarraInterna     = document.getElementById('barra-progresso-interna');
    const elOverlay          = document.getElementById('overlay-resultado');
    const elPontosFinais     = document.getElementById('pontos-finais');
    const elTituloResultado  = document.getElementById('titulo-resultado');
    const elTextoResultado   = document.getElementById('texto-resultado');

    const LISTA_FUNDOS = [
        'fundo_resposta_completar/1.png',
        'fundo_resposta_completar/2.png',
        'fundo_resposta_completar/3.png',
        'fundo_resposta_completar/4.png',
        'fundo_resposta_completar/5.png',
        'fundo_resposta_completar/6.png',
        'fundo_resposta_completar/7.png'
    ];
    LISTA_FUNDOS.forEach(src => {
        const img = new Image();
        img.src = src;
    });

    function carregarPergunta() {
        if (!TOTAL || indicePerguntaAtual >= TOTAL) {
            mostrarResultadoFinal();
            return;
        }

        respondeuAtual = false;
        const p = PERGUNTAS[indicePerguntaAtual];
        if (!p) return;

        if (elFundoLacunaImg && p.fundo_lacuna) {
            elFundoLacunaImg.src = p.fundo_lacuna;
        }

        if (elPrimeiraParte) elPrimeiraParte.textContent = p.primeira_parte || '';
        if (elSegundaParte)  elSegundaParte.textContent  = p.segunda_parte || '';

        if (elLacuna) {
            elLacuna.textContent = '_____';
            elLacuna.className   = 'lacuna';
        }
        if (elConteinerLacuna) {
            elConteinerLacuna.className = 'conteiner-lacuna';
        }

        if (elFeedback) {
            elFeedback.textContent = '';
            elFeedback.style.color = '';
        }

        const pct = Math.round((indicePerguntaAtual / TOTAL) * 100);
        if (elLabelProgresso)   elLabelProgresso.textContent   = `Question ${indicePerguntaAtual + 1} of ${TOTAL}`;
        if (elLabelPorcentagem) elLabelPorcentagem.textContent = `${pct}%`;
        if (elBarraInterna)     elBarraInterna.style.width     = `${pct}%`;

        gerarOpcoes(p);
    }

    function gerarOpcoes(pergunta) {
        if (!elGradeOpcoes) return;
        const opcoes = [pergunta.resposta, ...(Array.isArray(pergunta.erradas) ? pergunta.erradas : [])];

        for (let i = opcoes.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [opcoes[i], opcoes[j]] = [opcoes[j], opcoes[i]];
        }

        elGradeOpcoes.innerHTML = '';

        opcoes.forEach(opcao => {
            const btn = document.createElement('button');
            btn.className   = 'cartao-opcao';
            btn.textContent = opcao;
            btn.setAttribute('role', 'listitem');
            btn.addEventListener('click', () =>
                verificarResposta(btn, opcao, pergunta.resposta)
            );
            elGradeOpcoes.appendChild(btn);
        });
    }

    function verificarResposta(cardClicado, escolha, correta) {
        if (respondeuAtual) return;
        respondeuAtual = true;

        const acertou = String(escolha).toLowerCase().trim() === String(correta).toLowerCase().trim();

        if (acertou) {
            cardClicado.classList.add('opcao-correta');
            if (elLacuna) {
                elLacuna.textContent = escolha;
                elLacuna.classList.add('lacuna-correta');
            }
            if (elConteinerLacuna) {
                elConteinerLacuna.classList.add('lacuna-acertou');
            }

            if (elFeedback) {
                elFeedback.textContent = 'Correct! Well done!';
                elFeedback.style.color = '#054D20';
            }

            pontuacao++;
            if (elPlacar) elPlacar.textContent = `${pontuacao} / ${TOTAL}`;

            desabilitarOutrasOpcoes(cardClicado);
            setTimeout(avancarPergunta, 1400);

        } else {
            cardClicado.classList.add('opcao-errada');
            if (elLacuna) {
                elLacuna.textContent = escolha;
                elLacuna.classList.add('lacuna-errada');
            }
            if (elConteinerLacuna) {
                elConteinerLacuna.classList.add('lacuna-errou');
            }

            if (elFeedback) {
                elFeedback.textContent = 'Try again! You can do it!';
                elFeedback.style.color = '#b91c1c';
            }

            setTimeout(() => {
                respondeuAtual = false;
                cardClicado.classList.remove('opcao-errada');
                if (elLacuna) {
                    elLacuna.textContent = '_____';
                    elLacuna.className   = 'lacuna';
                }
                if (elConteinerLacuna) {
                    elConteinerLacuna.classList.remove('lacuna-errou');
                }
                if (elFeedback) {
                    elFeedback.textContent = '';
                    elFeedback.style.color = '';
                }
            }, 900);
        }
    }

    function desabilitarOutrasOpcoes(cardCorreto) {
        if (!elGradeOpcoes) return;
        elGradeOpcoes.querySelectorAll('.cartao-opcao').forEach(card => {
            if (card !== cardCorreto) card.classList.add('opcao-desabilitada');
        });
    }

    function avancarPergunta() {
        indicePerguntaAtual++;
        carregarPergunta();
    }

    function mostrarResultadoFinal() {
        if (!elOverlay) return;
        if (elBarraInterna)     elBarraInterna.style.width     = '100%';
        if (elLabelPorcentagem) elLabelPorcentagem.textContent = '100%';
        if (elLabelProgresso)   elLabelProgresso.textContent   = `Finished! ${TOTAL} of ${TOTAL}`;

        const taxa = TOTAL > 0 ? pontuacao / TOTAL : 0;
        if (taxa === 1) {
            if (elTituloResultado) elTituloResultado.textContent = 'PERFECT SCORE!';
            if (elTextoResultado)  elTextoResultado.textContent  = 'Amazing! You got everything right!';
        } else if (taxa >= 0.7) {
            if (elTituloResultado) elTituloResultado.textContent = 'GREAT JOB!';
            if (elTextoResultado)  elTextoResultado.textContent  = 'You did really well! Keep it up!';
        } else if (taxa >= 0.4) {
            if (elTituloResultado) elTituloResultado.textContent = 'GOOD TRY!';
            if (elTextoResultado)  elTextoResultado.textContent  = 'You are learning! Play again to improve!';
        } else {
            if (elTituloResultado) elTituloResultado.textContent = 'KEEP GOING!';
            if (elTextoResultado)  elTextoResultado.textContent  = 'Practice makes perfect! Try again!';
        }

        if (elPontosFinais) elPontosFinais.textContent = pontuacao;

        // Salva estatísticas e pontuação calculada no backend (acertos*100 - erros*65)
        fetch('salvar_pontuacao.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ questoesRespondidas: TOTAL, acertos: pontuacao })
        }).then(r => r.json()).then(data => {
            const elInfo = document.getElementById('info-pontuacao-jogo');
            if (elInfo && data && data.sucesso) {
                const sinal = data.pontuacao_partida > 0 ? '+' : '';
                elInfo.innerHTML = `
                    <div class="bg-papel-claro borda-tinta-escura rounded-2xl p-3 shadow-sombra-cartum-branca mb-3 text-center">
                        <span class="block text-xs font-black text-verde-escuro uppercase tracking-wider font-bingo">PONTOS GANHOS</span>
                        <span class="font-gooddog text-3xl font-bold text-verde-vibrante">${sinal}${data.pontuacao_partida} pts</span>
                        <span class="block text-xs font-bold text-gray-500 font-bingo mt-1">Pontuação Total: ${data.pontuacao_total} pts</span>
                    </div>
                `;
            }
        }).catch(err => console.error(err));

        elOverlay.classList.add('ativo');
    }

    function reiniciarJogo() {
        window.location.reload();
    }

    if (TOTAL > 0) {
        carregarPergunta();
    }
    </script>

    <!-- Script de Acessibilidade & Modo Escuro -->
    <script src="js/acessibilidade.js"></script>

</body>
</html>
