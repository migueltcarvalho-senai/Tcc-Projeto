<?php
/* ============================================================
   jogo_palavras.php — Montagem de Palavras! (Spell the Word)
   English for Kids — Fundamental 1
   Refatorado para total compatibilidade Linux / MySQL case-sensitive
   ============================================================ */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/proteger.php';

$id_jogo = 3;

// Resolução dinâmica dos nomes reais das tabelas (Linux case-sensitivity)
$tbl_tema     = db_table($conn, 'Tema');
$tbl_palavras = db_table($conn, 'palavras');

// 1. Obter todos os temas disponíveis para o jogo de palavras
$temas_disponiveis = [];
$sql_temas = "
    SELECT
        t.`id`,
        t.`nome`,
        COUNT(p.`id`) AS total_palavras
    FROM {$tbl_tema} t
    LEFT JOIN {$tbl_palavras} p ON p.`tema_id` = t.`id`
    WHERE t.`id_jogo` = ?
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
                'id'             => (int) $t['id'],
                'nome'           => $t['nome'],
                'total_palavras' => (int) $t['total_palavras'],
            ];
        }
    }
    $stmt_temas->close();
}

// Fallback: busca temas que possuam palavras cadastradas
if (empty($temas_disponiveis)) {
    $sql_fb = "
        SELECT
            t.`id`,
            t.`nome`,
            COUNT(p.`id`) AS total_palavras
        FROM {$tbl_tema} t
        INNER JOIN {$tbl_palavras} p ON p.`tema_id` = t.`id`
        GROUP BY t.`id`, t.`nome`
        ORDER BY t.`id` ASC
    ";
    $res_fb = $conn->query($sql_fb);
    if ($res_fb) {
        while ($t = $res_fb->fetch_assoc()) {
            $temas_disponiveis[] = [
                'id'             => (int) $t['id'],
                'nome'           => $t['nome'],
                'total_palavras' => (int) $t['total_palavras'],
            ];
        }
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

// 3. Buscar as palavras do tema selecionado
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

                // str_split gera array de letras; shuffle é feito no JS para re-embaralhar a cada tentativa
                $letras = str_split(strtolower($palavra_original));

                $palavras_jogo[] = [
                    'id'       => (int) $linha['id'],
                    'palavra'  => strtolower($palavra_original),
                    'traducao' => trim($linha['traducao'] ?? $palavra_original),
                    'letras'   => $letras,
                    'tamanho'  => count($letras),
                ];
            }
        }
        $stmt_palavras->close();
    }
}

// Randomiza ordem das palavras
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
    <title>Spell the Word – English for Kids</title>

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
                        'sombra-cartum-rosa':   '0px 6px 0px #c0185b',
                    }
                }
            }
        }
    </script>

    <!-- CSS do jogo de montagem de palavras -->
    <link rel="stylesheet" href="css/jogo_palavras.css">
    <!-- CSS de Acessibilidade & Modo Escuro -->
    <link rel="stylesheet" href="css/acessibilidade.css">
</head>
<body class="font-bingo min-h-screen text-tinta-escura flex flex-col justify-between p-2 sm:p-4 md:p-6 relative">

    <!-- ELEMENTOS OSCILANTES DE FUNDO (PAPELÃO) -->
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
            <a href="temas_palavras.php"
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
                <span class="inline-block w-3 h-3 bg-rosa-vibrante border-2 border-tinta-escura rounded-sm"></span>
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
                <div class="w-7 h-7 bg-rosa-vibrante border border-tinta-escura rounded-lg flex items-center justify-center font-bold text-xs text-white">
                    PTS
                </div>
                <div>
                    <span class="block text-xs font-black text-rosa-vibrante uppercase tracking-wider font-bingo">SCORE</span>
                    <span id="texto-placar"
                          class="block font-bingo text-base font-bold text-tinta-escura leading-none">
                        0 / <?= $total_palavras ?>
                    </span>
                </div>
            </div>
        </header>

        <!-- ÁREA PRINCIPAL DO JOGO -->
        <main id="area-jogo" class="w-full my-auto flex flex-col items-center gap-5 max-w-3xl mx-auto">

            <!-- TÍTULO -->
            <div class="text-center">
                <h1 class="font-gooddog text-4xl sm:text-5xl md:text-6xl font-bold text-tinta-escura tracking-wider leading-tight">
                    SPELL THE WORD!
                </h1>
                <p class="font-bingo text-base sm:text-lg font-extrabold text-gray-700 tracking-wide mt-1">
                    Click the letters in the correct order!
                </p>
            </div>

            <!-- SELETOR DE TEMAS RÁPIDO -->
            <?php if (count($temas_disponiveis) > 1): ?>
            <div id="seletor-temas" class="flex flex-wrap items-center justify-center gap-2 -mt-1">
                <span class="font-bingo font-black text-xs text-gray-500 uppercase tracking-widest mr-1">TOPICS:</span>
                <?php foreach ($temas_disponiveis as $t): ?>
                    <a href="jogo_palavras.php?tema=<?= urlencode($t['id']) ?>"
                       class="cartao-dinamico px-3.5 py-1.5 rounded-xl font-bingo font-bold text-xs border-2 border-tinta-escura transition-all <?= ($tema_ativo && $tema_ativo['id'] === $t['id']) ? 'bg-rosa-vibrante text-white shadow-sombra-cartum-rosa scale-105' : 'bg-papel-claro text-gray-600 hover:bg-white' ?>"
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

            <!-- TRADUÇÃO (PISTA) -->
            <div id="area-traducao" class="w-full text-center">
                <span class="label-traduzir">translate this word:</span>
                <span id="texto-traducao" class="texto-traducao" aria-live="polite"></span>
            </div>

            <!-- ÁREA DA MESA COM AS LETRAS -->
            <div id="area-letras" aria-label="Letter area">
                <!-- Preenchido pelo JavaScript com os botões de letras -->
            </div>

            <!-- PALAVRA EM CONSTRUÇÃO -->
            <div id="area-palavra-construida" class="w-full max-w-2xl">
                <div class="w-full flex items-center justify-between px-1 mb-2">
                    <span class="label-construindo">Your word:</span>
                    <button id="btn-apagar"
                            class="botao-apagar"
                            type="button"
                            title="Apagar última letra">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                            <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                        </svg>
                        <span>CLEAR</span>
                    </button>
                </div>
                <div id="slots-palavra" aria-live="polite">
                    <span class="slot-label">SPELL IT</span>
                    <!-- Preenchido pelo JavaScript -->
                </div>
            </div>

            <!-- FEEDBACK -->
            <div id="area-feedback"
                 class="text-center min-h-[2.5rem]"
                 aria-live="polite">
            </div>

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
                <a href="temas_palavras.php"
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
                <span class="w-3.5 h-3.5 bg-rosa-vibrante border border-tinta-escura rounded-full inline-block"></span>
                <span>ENGLISH FOR KIDS — GAME 03</span>
            </div>
            <div class="bg-papel-claro borda-tinta-escura px-3 py-1.5 rounded-xl text-tinta-escura uppercase font-extrabold shadow-sm font-bingo">
                SPELL THE WORD<?= $tema_ativo ? ' — ' . htmlspecialchars(strtoupper($tema_ativo['nome'])) : '' ?>
            </div>
        </footer>

    </div><!-- /livro-conteiner-principal -->

    <!-- OVERLAY DE FINAL DE JOGO -->
    <div id="overlay-resultado" role="dialog" aria-modal="true" aria-label="Game complete">
        <div class="card-resultado card-resultado-animando">
            <div class="text-3xl font-gooddog text-rosa-vibrante font-bold mb-2">COMPLETE!</div>
            <h2 class="font-gooddog text-5xl font-bold text-tinta-escura leading-tight">
                WELL DONE!
            </h2>
            <p class="font-bingo text-lg font-extrabold text-gray-600 mt-1">
                You spelled all the words correctly!
            </p>

            <div class="my-6 bg-papel-claro borda-tinta-escura rounded-2xl p-4 shadow-sombra-cartum-branca">
                <span class="block font-bingo text-xs font-black text-azul-escuro uppercase tracking-wider">
                    FINAL SCORE
                </span>
                <span id="score-final" class="font-gooddog text-5xl font-bold text-rosa-vibrante">
                    <?= $total_palavras ?> / <?= $total_palavras ?>
                </span>
                <div id="info-pontuacao-palavras" class="mt-2 pt-2 border-t-2 border-dashed border-gray-300"></div>
            </div>

            <div class="flex flex-col sm:flex-row gap-3 justify-center items-center">
                <button type="button"
                        id="btn-jogar-novamente"
                        class="botao-acao bg-rosa-vibrante text-white w-full sm:w-auto">
                    <span>PLAY AGAIN</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                        <path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </button>

                <a href="temas_palavras.php"
                   class="botao-acao bg-amarelo-vibrante text-tinta-escura w-full sm:w-auto"
                   style="text-decoration:none;">
                    <span>OTHER TOPICS</span>
                </a>

                <a href="index.php"
                   class="botao-acao bg-azul-vibrante text-white w-full sm:w-auto"
                   style="text-decoration:none;">
                    <span>MENU</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                        <path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>

    <!-- JAVASCRIPT DO JOGO DE MONTAGEM DE PALAVRAS -->
    <script>
    (function() {
        'use strict';

        const palavrasData   = <?= $palavras_json ?: '[]' ?>;
        const totalPalavras  = Array.isArray(palavrasData) ? palavrasData.length : 0;

        const areaLetras        = document.getElementById('area-letras');
        const textoTraducao     = document.getElementById('texto-traducao');
        const slotsPalavra      = document.getElementById('slots-palavra');
        const areaFeedback      = document.getElementById('area-feedback');
        const textoPlacar       = document.getElementById('texto-placar');
        const labelProgresso    = document.getElementById('label-progresso');
        const labelPorcentagem  = document.getElementById('label-porcentagem');
        const barraInterna      = document.getElementById('barra-progresso-interna');
        const btnApagar         = document.getElementById('btn-apagar');
        const overlayResultado  = document.getElementById('overlay-resultado');
        const btnJogarNovamente = document.getElementById('btn-jogar-novamente');

        // Paleta de cores (5 cores vibrantes cicladas pelo índice da letra)
        const CORES_LETRAS = ['letra-cor-0', 'letra-cor-1', 'letra-cor-2', 'letra-cor-3', 'letra-cor-4'];

        // Rotações leves para espalhar as letras visualmente
        const ROTACOES = [-8, -4, 0, 4, 8, -6, 6, -2, 2, -10, 10, -3, 3];

        let indicePalavraAtual = 0;
        let acertos            = 0;
        let letrasSelecionadas = [];   // array de índices das letras clicadas (na ordem do embaralhamento)
        let letrasEmbaralhadas = [];   // array de objetos { letra, idxOriginal }
        let estaValidando      = false;

        /* ---- Botão CLEAR ---- */
        if (btnApagar) {
            btnApagar.addEventListener('click', () => {
                if (estaValidando) return;
                reiniciarPalavraAtual();
            });
        }

        /* ---- Botão PLAY AGAIN ---- */
        if (btnJogarNovamente) {
            btnJogarNovamente.addEventListener('click', () => {
                if (overlayResultado) overlayResultado.classList.remove('ativo');
                indicePalavraAtual = 0;
                acertos = 0;
                if (totalPalavras > 0) carregarPalavra(0);
            });
        }

        /* ---- Iniciar jogo ---- */
        if (totalPalavras > 0) {
            carregarPalavra(indicePalavraAtual);
        }

        /* ============================================================
           FUNÇÕES PRINCIPAIS
           ============================================================ */

        function carregarPalavra(indice) {
            if (indice >= totalPalavras) {
                finalizarJogo();
                return;
            }

            estaValidando      = false;
            letrasSelecionadas = [];
            if (areaFeedback)   areaFeedback.innerHTML = '';
            atualizarProgresso();

            const palavraObj = palavrasData[indice];
            if (!palavraObj) return;

            // Exibir tradução
            if (textoTraducao) {
                textoTraducao.textContent = palavraObj.traducao || '';
            }

            // Embaralhar letras (cópia com índice original)
            const letras = Array.isArray(palavraObj.letras) ? [...palavraObj.letras] : [];
            letrasEmbaralhadas = letras.map((l, i) => ({ letra: l, idxOriginal: i }));
            embaralharArray(letrasEmbaralhadas);

            renderizarSlots(letras.length);
            renderizarLetras();
        }

        function renderizarSlots(qtd) {
            if (!slotsPalavra) return;
            slotsPalavra.innerHTML = '<span class="slot-label">SPELL IT</span>';
            for (let i = 0; i < qtd; i++) {
                const slot = document.createElement('div');
                slot.className = 'letra-slot letra-slot-vazio';
                slot.id = 'slot-' + i;
                slot.textContent = '_';
                slotsPalavra.appendChild(slot);
            }
        }

        function renderizarLetras() {
            if (!areaLetras) return;
            areaLetras.innerHTML = '';

            letrasEmbaralhadas.forEach((item, idx) => {
                const btn = document.createElement('button');
                btn.type      = 'button';
                btn.className = 'letra-btn ' + CORES_LETRAS[idx % CORES_LETRAS.length];
                btn.textContent = item.letra.toUpperCase();
                btn.id = 'letra-btn-' + idx;
                btn.setAttribute('data-idx', idx);
                btn.setAttribute('aria-label', 'Letra ' + item.letra.toUpperCase());

                // Rotação leve aleatória para espalhar visualmente
                const rot = ROTACOES[idx % ROTACOES.length] + (Math.random() * 4 - 2);
                btn.style.transform = 'rotate(' + rot.toFixed(1) + 'deg)';
                btn.style.setProperty('--rot', rot.toFixed(1) + 'deg');

                btn.addEventListener('click', () => {
                    if (estaValidando) return;
                    if (btn.classList.contains('usada')) return;
                    selecionarLetra(btn, idx);
                });

                areaLetras.appendChild(btn);
            });
        }

        function selecionarLetra(btnEl, idx) {
            const palavraObj    = palavrasData[indicePalavraAtual];
            if (!palavraObj) return;
            const tamanho = palavraObj.tamanho || palavraObj.letras.length;

            // Marcar botão como usado
            btnEl.classList.add('usada');
            letrasSelecionadas.push(idx);

            // Preencher slot correspondente
            const slotIdx = letrasSelecionadas.length - 1;
            const slot    = document.getElementById('slot-' + slotIdx);
            if (slot) {
                slot.textContent  = letrasEmbaralhadas[idx].letra.toUpperCase();
                slot.className    = 'letra-slot letra-slot-animando';
            }

            // Verificar se palavra foi completada
            if (letrasSelecionadas.length >= tamanho) {
                verificarPalavra();
            }
        }

        function verificarPalavra() {
            estaValidando = true;

            const palavraObj = palavrasData[indicePalavraAtual];
            if (!palavraObj) return;

            // Montar palavra a partir dos índices selecionados (mantendo a ordem de clique)
            const palavraMontada = letrasSelecionadas
                .map(idx => letrasEmbaralhadas[idx].letra)
                .join('');

            const palavraCorreta = palavraObj.palavra;

            if (palavraMontada === palavraCorreta) {
                // ACERTO
                acertos++;

                const tamanho = palavraObj.tamanho;
                for (let i = 0; i < tamanho; i++) {
                    const slot = document.getElementById('slot-' + i);
                    if (slot) slot.className = 'letra-slot letra-slot-certo';
                }

                if (areaFeedback) {
                    areaFeedback.innerHTML = '<span class="feedback-acerto">CORRECT! WELL DONE!</span>';
                }

                atualizarProgresso();

                setTimeout(() => {
                    indicePalavraAtual++;
                    carregarPalavra(indicePalavraAtual);
                }, 1400);

            } else {
                // ERRO
                const tamanho = palavraObj.tamanho;
                for (let i = 0; i < tamanho; i++) {
                    const slot = document.getElementById('slot-' + i);
                    if (slot) slot.className = 'letra-slot letra-slot-erro';
                }

                if (areaFeedback) {
                    areaFeedback.innerHTML = '<span class="feedback-erro">OOPS! TRY AGAIN!</span>';
                }

                setTimeout(() => {
                    reiniciarPalavraAtual();
                }, 1600);
            }
        }

        function reiniciarPalavraAtual() {
            estaValidando      = false;
            letrasSelecionadas = [];
            if (areaFeedback) areaFeedback.innerHTML = '';

            const palavraObj = palavrasData[indicePalavraAtual];
            if (!palavraObj) return;

            // Re-embaralha as letras para variedade
            embaralharArray(letrasEmbaralhadas);

            renderizarSlots(palavraObj.tamanho || palavraObj.letras.length);
            renderizarLetras();
        }

        function atualizarProgresso() {
            if (!totalPalavras) return;
            const respondidas = indicePalavraAtual;
            const pct = Math.round((respondidas / totalPalavras) * 100);

            if (labelProgresso)   labelProgresso.textContent   = 'Word ' + (indicePalavraAtual + 1) + ' of ' + totalPalavras;
            if (labelPorcentagem) labelPorcentagem.textContent = pct + '%';
            if (barraInterna)     barraInterna.style.width     = pct + '%';
            if (textoPlacar)      textoPlacar.textContent      = acertos + ' / ' + totalPalavras;
        }

        function finalizarJogo() {
            if (!overlayResultado) return;
            const scoreFinal = document.getElementById('score-final');
            if (scoreFinal) {
                scoreFinal.textContent = acertos + ' / ' + totalPalavras;
            }

            if (labelProgresso)   labelProgresso.textContent   = 'Completed! ' + totalPalavras + ' of ' + totalPalavras;
            if (labelPorcentagem) labelPorcentagem.textContent = '100%';
            if (barraInterna)     barraInterna.style.width     = '100%';

            // Salva estatísticas e pontuação calculada no backend (acertos*100 - erros*65)
            fetch('salvar_pontuacao.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ questoesRespondidas: totalPalavras, acertos: acertos })
            }).then(r => r.json()).then(data => {
                const elInfo = document.getElementById('info-pontuacao-palavras');
                if (elInfo && data && data.sucesso) {
                    const sinal = data.pontuacao_partida > 0 ? '+' : '';
                    elInfo.innerHTML = `
                        <span class="block text-xs font-black text-rosa-escuro uppercase tracking-wider font-bingo">PONTOS GANHOS</span>
                        <span class="font-gooddog text-3xl font-bold text-rosa-vibrante">${sinal}${data.pontuacao_partida} pts</span>
                        <span class="block text-xs font-bold text-gray-500 font-bingo mt-1">Pontuação Total: ${data.pontuacao_total} pts</span>
                    `;
                }
            }).catch(err => console.error(err));

            overlayResultado.classList.add('ativo');
        }

        /* Fisher-Yates shuffle (in-place) */
        function embaralharArray(arr) {
            for (let i = arr.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [arr[i], arr[j]] = [arr[j], arr[i]];
            }
        }

    })();
    </script>

    <!-- Script de Acessibilidade & Modo Escuro -->
    <script src="js/acessibilidade.js"></script>
</body>
</html>
