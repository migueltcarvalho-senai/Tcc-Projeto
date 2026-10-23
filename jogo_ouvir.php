<?php
/* ============================================================
   jogo_ouvir.php — Ouça e Escolha! (Listen and Choose)
   English for Kids — Fundamental 1 | Web Speech API Quiz
   Refatorado para total compatibilidade Linux / MySQL case-sensitive
   ============================================================ */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/proteger.php';

// Resolução dinâmica dos nomes reais das tabelas (Linux case-sensitivity)
$tbl_tema     = db_table($conn, 'Tema');
$tbl_palavras = db_table($conn, 'palavras');

// 1. Obter todos os temas que possuem palavras cadastradas
$temas_disponiveis = [];
$sql_temas = "
    SELECT
        t.`id`,
        t.`nome`,
        COUNT(p.`id`) AS total_palavras
    FROM {$tbl_tema} t
    INNER JOIN {$tbl_palavras} p ON p.`tema_id` = t.`id`
    GROUP BY t.`id`, t.`nome`
    ORDER BY t.`id` ASC
";
$res_temas = $conn->query($sql_temas);
if ($res_temas) {
    while ($t = $res_temas->fetch_assoc()) {
        $temas_disponiveis[] = [
            'id'             => (int) $t['id'],
            'nome'           => $t['nome'],
            'total_palavras' => (int) $t['total_palavras'],
        ];
    }
}

// 2. Determinar o tema ativo via GET
$tema_param = $_GET['tema'] ?? $_GET['id_tema'] ?? null;
$tema_ativo = null;

if (!empty($temas_disponiveis)) {
    if ($tema_param !== null && $tema_param !== '') {
        foreach ($temas_disponiveis as $t) {
            if (
                (string) $t['id'] === (string) $tema_param ||
                strcasecmp($t['nome'], (string) $tema_param) === 0
            ) {
                $tema_ativo = $t;
                break;
            }
        }
    }
    if (!$tema_ativo) {
        $tema_ativo = $temas_disponiveis[0];
    }
}

// 3. Buscar as palavras do tema selecionado (ou todas se não houver tema específico)
$palavras_jogo = [];
if ($tema_ativo) {
    $sql_palavras = "
        SELECT p.`id`, p.`palavra`, p.`traducao`
        FROM {$tbl_palavras} p
        WHERE p.`tema_id` = ?
        ORDER BY p.`id` ASC
    ";
    $stmt_palavras = $conn->prepare($sql_palavras);
    if ($stmt_palavras) {
        $id_tema_busca = $tema_ativo['id'];
        $stmt_palavras->bind_param("i", $id_tema_busca);
        $stmt_palavras->execute();
        $res_palavras = $stmt_palavras->get_result();
        if ($res_palavras) {
            while ($linha = $res_palavras->fetch_assoc()) {
                $palavra_original = trim($linha['palavra'] ?? '');
                if ($palavra_original === '') continue;

                $palavras_jogo[] = [
                    'id'       => (int) $linha['id'],
                    'palavra'  => strtolower($palavra_original),
                    'traducao' => trim($linha['traducao'] ?? $palavra_original),
                ];
            }
        }
        $stmt_palavras->close();
    }
}

// Randomiza a ordem das palavras para esta partida
shuffle($palavras_jogo);

$total_palavras = count($palavras_jogo);

// Serialização segura para o JavaScript
$palavras_json = json_encode(
    $palavras_jogo,
    JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP
) ?: '[]';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listen and Choose – English for Kids</title>

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
                        'sombra-cartum-amarela':'0px 6px 0px #806B00',
                    }
                }
            }
        }
    </script>

    <!-- CSS do jogo de escuta -->
    <link rel="stylesheet" href="css/jogo_ouvir.css">
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

    <!-- LIVRO / CADERNO PRINCIPAL -->
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
            <a href="temas_ouvir.php"
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
                <span class="inline-block w-3 h-3 bg-amarelo-vibrante border-2 border-tinta-escura rounded-sm"></span>
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
                <div class="w-7 h-7 bg-amarelo-vibrante border border-tinta-escura rounded-lg flex items-center justify-center font-bold text-xs text-tinta-escura">
                    PTS
                </div>
                <div>
                    <span class="block text-xs font-black text-amarelo-escuro uppercase tracking-wider font-bingo">SCORE</span>
                    <span id="texto-placar"
                          class="block font-bingo text-base font-bold text-tinta-escura leading-none">
                        0 / <?= $total_palavras ?>
                    </span>
                </div>
            </div>
        </header>

        <!-- ÁREA PRINCIPAL DO JOGO -->
        <main id="area-jogo" class="w-full my-auto flex flex-col items-center gap-5 max-w-4xl mx-auto">

            <!-- TÍTULO -->
            <div class="text-center">
                <h1 class="font-gooddog text-4xl sm:text-5xl md:text-6xl font-bold text-tinta-escura tracking-wider leading-tight">
                    LISTEN AND CHOOSE!
                </h1>
                <p class="font-bingo text-base sm:text-lg font-extrabold text-gray-700 tracking-wide mt-1">
                    Listen to the English word and click on the correct option!
                </p>
            </div>

            <!-- SELETOR DE TEMAS RÁPIDO -->
            <?php if (count($temas_disponiveis) > 1): ?>
            <div id="seletor-temas" class="flex flex-wrap items-center justify-center gap-2 -mt-1">
                <span class="font-bingo font-black text-xs text-gray-500 uppercase tracking-widest mr-1">TOPICS:</span>
                <?php foreach ($temas_disponiveis as $t): ?>
                    <a href="jogo_ouvir.php?tema=<?= urlencode($t['id']) ?>"
                       class="cartao-dinamico px-3.5 py-1.5 rounded-xl font-bingo font-bold text-xs border-2 border-tinta-escura transition-all <?= ($tema_ativo && $tema_ativo['id'] === $t['id']) ? 'bg-amarelo-vibrante text-tinta-escura shadow-sombra-cartum-amarela scale-105' : 'bg-papel-claro text-gray-600 hover:bg-white' ?>"
                       style="text-decoration: none;">
                        <?= htmlspecialchars(ucfirst($t['nome'])) ?> (<?= $t['total_palavras'] ?>)
                    </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- BARRA DE PROGRESSO -->
            <div class="w-full max-w-md">
                <div class="flex justify-between items-center mb-1 font-bingo font-bold text-sm text-gray-600">
                    <span id="label-progresso">Word 1 of <?= $total_palavras ?></span>
                    <span id="label-porcentagem">0%</span>
                </div>
                <div id="barra-progresso-externa">
                    <div id="barra-progresso-interna" style="width: 0%"></div>
                </div>
            </div>

            <?php if ($total_palavras > 0): ?>

            <!-- CAIXA DE ÁUDIO CENTRAL COM BOTÃO DE OUVIDO -->
            <div id="caixa-ouvir">
                <span class="font-bingo font-black text-xs text-gray-500 uppercase tracking-widest">
                    CLICK TO HEAR THE WORD AGAIN:
                </span>

                <!-- BOTÃO GRANDE DE VOZ (WEB SPEECH API) -->
                <button type="button" id="btn-ouvir" class="btn-ouvir-voz" title="Ouvir pronúncia da palavra">
                    <svg class="w-8 h-8 fill-current" viewBox="0 0 24 24">
                        <path d="M3 9v6h4l5 5V4L7 9H3zm13.5 3c0-1.77-1.02-3.29-2.5-4.03v8.05c1.48-.73 2.5-2.25 2.5-4.02zM14 3.23v2.06c2.89.86 5 3.54 5 6.71s-2.11 5.85-5 6.71v2.06c4.01-.91 7-4.49 7-8.77s-2.99-7.86-7-8.77z"/>
                    </svg>
                    <span>LISTEN</span>
                </button>

                <!-- DICA DE TRADUÇÃO -->
                <div class="mt-1 flex items-center gap-2">
                    <span class="font-bingo font-black text-xs text-gray-400 uppercase tracking-wider">MEANING:</span>
                    <span id="dica-traducao" class="font-bingo font-bold text-lg text-tinta-escura bg-yellow-100 border border-yellow-300 rounded-lg px-2.5 py-0.5">
                        ...
                    </span>
                </div>
            </div>

            <!-- GRADE DE 4 ALTERNATIVAS -->
            <div id="grade-opcoes" role="list" aria-label="Word options">
                <!-- Preenchido pelo JavaScript com as 4 opções -->
            </div>

            <!-- FEEDBACK INLINE -->
            <div id="area-feedback" aria-live="polite"></div>

            <?php else: ?>
            <!-- Nenhuma palavra encontrada -->
            <div class="text-center py-12">
                <div class="w-16 h-16 mx-auto mb-4 bg-papel-claro border-2 border-tinta-escura rounded-2xl flex items-center justify-center font-bold text-xl text-gray-500">
                    0
                </div>
                <p class="font-bingo font-bold text-2xl text-gray-700">
                    No words found for this topic.<br>
                    Please run palavras_migration.sql!
                </p>
                <a href="temas_ouvir.php"
                   class="botao-acao mt-6 bg-azul-vibrante text-white inline-flex"
                   style="text-decoration: none;">
                    Back to Topics
                </a>
            </div>
            <?php endif; ?>

        </main>

        <!-- RODAPÉ -->
        <footer id="rodape-jogo"
                class="w-full pt-4 mt-6 flex flex-wrap items-center justify-between gap-3 text-xs md:text-sm font-bingo font-bold text-gray-700">
            <div class="flex items-center gap-2">
                <span class="w-3.5 h-3.5 bg-amarelo-vibrante border border-tinta-escura rounded-full inline-block"></span>
                <span>ENGLISH FOR KIDS — GAME 04</span>
            </div>
            <div class="bg-papel-claro borda-tinta-escura px-3 py-1.5 rounded-xl text-tinta-escura uppercase font-extrabold shadow-sm font-bingo">
                LISTEN AND CHOOSE<?= $tema_ativo ? ' — ' . htmlspecialchars(strtoupper($tema_ativo['nome'])) : '' ?>
            </div>
        </footer>

    </div>

    <!-- OVERLAY DE FINAL DE JOGO -->
    <div id="overlay-resultado" role="dialog" aria-modal="true" aria-label="Game complete">
        <div class="card-resultado card-resultado-animando">
            <div class="text-3xl font-gooddog text-amarelo-vibrante font-bold mb-2">COMPLETE!</div>
            <h2 class="font-gooddog text-5xl font-bold text-tinta-escura leading-tight">
                AWESOME EARS!
            </h2>
            <p class="font-bingo text-lg font-extrabold text-gray-600 mt-1">
                You listened and identified all the words correctly!
            </p>

            <div class="my-6 bg-papel-claro borda-tinta-escura rounded-2xl p-4 shadow-sombra-cartum-branca">
                <span class="block font-bingo text-xs font-black text-azul-escuro uppercase tracking-wider">
                    FINAL SCORE
                </span>
                <span id="score-final" class="font-gooddog text-5xl font-bold text-amarelo-escuro">
                    <?= $total_palavras ?> / <?= $total_palavras ?>
                </span>
                <div id="info-pontuacao-ouvir" class="mt-2 pt-2 border-t-2 border-dashed border-gray-300"></div>
            </div>

            <div class="flex flex-col sm:flex-row gap-3 justify-center items-center">
                <button type="button"
                        id="btn-jogar-novamente"
                        class="botao-acao bg-amarelo-vibrante text-tinta-escura w-full sm:w-auto">
                    <span>PLAY AGAIN</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                        <path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </button>

                <a href="temas_ouvir.php"
                   class="botao-acao bg-azul-vibrante text-white w-full sm:w-auto"
                   style="text-decoration:none;">
                    <span>OTHER TOPICS</span>
                </a>

                <a href="index.php"
                   class="botao-acao bg-papel-claro text-tinta-escura w-full sm:w-auto"
                   style="text-decoration:none;">
                    <span>MENU</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                        <path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>

    <!-- JAVASCRIPT DO JOGO DE ESCUTA -->
    <script>
    (function() {
        'use strict';

        // Pool de 200 palavras em inglês fornecida pelo usuário
        const wordsPool = [
            // Animals (27)
            "dog", "elephant", "tiger", "lion", "monkey",
            "rabbit", "dolphin", "giraffe", "eagle", "bear",
            "wolf", "penguin", "snake", "horse", "frog",
            "shark", "butterfly", "kangaroo", "owl", "turtle",
            "fox", "whale", "deer", "parrot", "leopard",
            "cheetah", "hamster",

            // Sports (27)
            "basketball", "soccer", "tennis", "swimming",
            "volleyball", "baseball", "cycling", "golf",
            "boxing", "hockey", "running", "skiing",
            "surfing", "wrestling", "rugby", "badminton",
            "karate", "archery", "fencing", "rowing",
            "skateboarding", "gymnastics", "handball",
            "marathon", "softball", "football", "cricket",

            // Family (27)
            "mother", "father", "brother", "sister",
            "grandmother", "grandfather", "uncle", "aunt",
            "cousin", "daughter", "son", "nephew",
            "niece", "parents", "husband", "wife",
            "baby", "grandparents", "twins", "sibling",
            "stepfather", "stepmother", "stepbrother",
            "stepsister", "godmother", "godfather",
            "grandchild",

            // School objects (29)
            "pencil", "notebook", "eraser", "backpack",
            "ruler", "marker", "book", "scissors", "desk",
            "chair", "calculator", "blackboard", "glue",
            "crayon", "folder", "sharpener", "paper",
            "highlighter", "stapler", "textbook", "pencilcase",
            "compass", "protractor", "chalk", "whiteboard",
            "paintbrush", "dictionary", "pen", "binder"
        ];

        const palavrasData  = <?= $palavras_json ?: '[]' ?>;
        const totalPalavras = Array.isArray(palavrasData) ? palavrasData.length : 0;

        const btnOuvir          = document.getElementById('btn-ouvir');
        const dicaTraducao      = document.getElementById('dica-traducao');
        const gradeOpcoes       = document.getElementById('grade-opcoes');
        const areaFeedback      = document.getElementById('area-feedback');
        const textoPlacar       = document.getElementById('texto-placar');
        const labelProgresso    = document.getElementById('label-progresso');
        const labelPorcentagem  = document.getElementById('label-porcentagem');
        const barraInterna      = document.getElementById('barra-progresso-interna');
        const overlayResultado  = document.getElementById('overlay-resultado');
        const btnJogarNovamente = document.getElementById('btn-jogar-novamente');

        let indiceAtual     = 0;
        let acertos         = 0;
        let estaValidando   = false;
        let palavraCorrente = '';

        if (btnOuvir) {
            btnOuvir.addEventListener('click', () => {
                if (palavraCorrente) {
                    pronunciarPalavra(palavraCorrente);
                }
            });
        }

        if (btnJogarNovamente) {
            btnJogarNovamente.addEventListener('click', () => {
                if (overlayResultado) overlayResultado.classList.remove('ativo');
                indiceAtual = 0;
                acertos = 0;
                if (totalPalavras > 0) carregarRodada(0);
            });
        }

        if (totalPalavras > 0) {
            carregarRodada(indiceAtual);
        }

        /* Web Speech API — Pronúncia nativa em inglês */
        function pronunciarPalavra(texto) {
            if (!('speechSynthesis' in window)) {
                console.warn('Web Speech API não disponível.');
                return;
            }

            window.speechSynthesis.cancel();

            const utter = new SpeechSynthesisUtterance(texto);
            utter.lang  = 'en-US';
            utter.rate  = 0.85;
            utter.pitch = 1.05;

            if (btnOuvir) {
                btnOuvir.classList.add('falando');
                utter.onend = () => btnOuvir.classList.remove('falando');
                utter.onerror = () => btnOuvir.classList.remove('falando');
            }

            // Seleciona voz nativa de inglês se disponível
            const vozes = window.speechSynthesis.getVoices();
            const vozEn = vozes.find(v => v.lang.startsWith('en'));
            if (vozEn) utter.voice = vozEn;

            window.speechSynthesis.speak(utter);
        }

        // Garante carregamento das vozes no Chrome
        if ('speechSynthesis' in window) {
            window.speechSynthesis.onvoiceschanged = () => {
                // vozes carregadas
            };
        }

        function carregarRodada(indice) {
            if (indice >= totalPalavras) {
                finalizarJogo();
                return;
            }

            estaValidando = false;
            if (areaFeedback) areaFeedback.innerHTML = '';
            atualizarProgresso();

            const item = palavrasData[indice];
            if (!item) return;

            palavraCorrente = item.palavra.toLowerCase().trim();

            if (dicaTraducao) {
                dicaTraducao.textContent = item.traducao || item.palavra;
            }

            // Pronuncia automaticamente ao iniciar a questão
            setTimeout(() => {
                pronunciarPalavra(palavraCorrente);
            }, 300);

            // Monta as 4 alternativas: 1 correta + 3 distratores sorteados da pool
            const opcoes = gerarOpcoes(palavraCorrente);
            renderizarOpcoes(opcoes);
        }

        function gerarOpcoes(correta) {
            const distratoresDisponiveis = wordsPool.filter(w => w.toLowerCase() !== correta);

            // Embaralha distratores
            for (let i = distratoresDisponiveis.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [distratoresDisponiveis[i], distratoresDisponiveis[j]] = [distratoresDisponiveis[j], distratoresDisponiveis[i]];
            }

            const selecionados = [correta, distratoresDisponiveis[0], distratoresDisponiveis[1], distratoresDisponiveis[2]];

            // Embaralha as 4 alternativas finais
            for (let i = selecionados.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [selecionados[i], selecionados[j]] = [selecionados[j], selecionados[i]];
            }

            return selecionados;
        }

        function renderizarOpcoes(opcoes) {
            if (!gradeOpcoes) return;
            gradeOpcoes.innerHTML = '';

            opcoes.forEach((palavra, index) => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = `cartao-opcao opcao-cor-${index % 4}`;
                btn.textContent = palavra;
                btn.setAttribute('role', 'listitem');

                btn.addEventListener('click', () => {
                    if (estaValidando) return;
                    verificarResposta(btn, palavra);
                });

                gradeOpcoes.appendChild(btn);
            });
        }

        function verificarResposta(cardClicado, palavraEscolhida) {
            estaValidando = true;

            if (palavraEscolhida.toLowerCase() === palavraCorrente) {
                // ACERTO
                acertos++;
                cardClicado.classList.add('opcao-correta');

                if (areaFeedback) {
                    areaFeedback.innerHTML = '<span class="feedback-acerto">CORRECT! WELL DONE!</span>';
                }

                // Desabilita as outras opções
                const todos = gradeOpcoes.querySelectorAll('.cartao-opcao');
                todos.forEach(c => {
                    if (c !== cardClicado) c.classList.add('opcao-desabilitada');
                });

                atualizarProgresso();

                setTimeout(() => {
                    indiceAtual++;
                    carregarRodada(indiceAtual);
                }, 1400);

            } else {
                // ERRO
                cardClicado.classList.add('opcao-errada');

                if (areaFeedback) {
                    areaFeedback.innerHTML = '<span class="feedback-erro">TRY AGAIN! LISTEN CLOSELY!</span>';
                }

                // Repete o áudio para ajudar a criança
                pronunciarPalavra(palavraCorrente);

                setTimeout(() => {
                    estaValidando = false;
                    cardClicado.classList.remove('opcao-errada');
                    if (areaFeedback) areaFeedback.innerHTML = '';
                }, 1400);
            }
        }

        function atualizarProgresso() {
            if (!totalPalavras) return;
            const pct = Math.round((indiceAtual / totalPalavras) * 100);

            if (labelProgresso)   labelProgresso.textContent   = `Word ${indiceAtual + 1} of ${totalPalavras}`;
            if (labelPorcentagem) labelPorcentagem.textContent = `${pct}%`;
            if (barraInterna)     barraInterna.style.width     = `${pct}%`;
            if (textoPlacar)      textoPlacar.textContent      = `${acertos} / ${totalPalavras}`;
        }

        function finalizarJogo() {
            if (!overlayResultado) return;
            const scoreFinal = document.getElementById('score-final');
            if (scoreFinal) {
                scoreFinal.textContent = `${acertos} / ${totalPalavras}`;
            }

            if (labelProgresso)   labelProgresso.textContent   = `Completed! ${totalPalavras} of ${totalPalavras}`;
            if (labelPorcentagem) labelPorcentagem.textContent = '100%';
            if (barraInterna)     barraInterna.style.width     = '100%';

            // Salva estatísticas e pontuação calculada no backend (acertos*100 - erros*65)
            fetch('salvar_pontuacao.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ questoesRespondidas: totalPalavras, acertos: acertos })
            }).then(r => r.json()).then(data => {
                const elInfo = document.getElementById('info-pontuacao-ouvir');
                if (elInfo && data && data.sucesso) {
                    const sinal = data.pontuacao_partida > 0 ? '+' : '';
                    elInfo.innerHTML = `
                        <span class="block text-xs font-black text-amarelo-escuro uppercase tracking-wider font-bingo">PONTOS GANHOS</span>
                        <span class="font-gooddog text-3xl font-bold text-amarelo-escuro">${sinal}${data.pontuacao_partida} pts</span>
                        <span class="block text-xs font-bold text-gray-500 font-bingo mt-1">Pontuação Total: ${data.pontuacao_total} pts</span>
                    `;
                }
            }).catch(err => console.error(err));

            overlayResultado.classList.add('ativo');
        }

    })();
    </script>

    <!-- Script de Acessibilidade & Modo Escuro -->
    <script src="js/acessibilidade.js"></script>

</body>
</html>
