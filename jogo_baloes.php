<?php
/* ============================================================
   jogo_baloes.php — Estoure os Balões! (Pop the Balloons)
   English for Kids — Fundamental 1 | Sentence Builder Game
   Refatorado para total compatibilidade Linux / MySQL case-sensitive
   ============================================================ */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/proteger.php';

$id_jogo = 2;

// Resolução dinâmica do nome real das tabelas (Linux case-sensitivity)
$tbl_tema   = db_table($conn, 'Tema');
$tbl_frases = db_table($conn, 'Frases');

// 1. Obter todos os temas disponíveis para o jogo dos balões
$temas_disponiveis = [];
$sql_temas = "
    SELECT
        t.`id`,
        t.`nome`,
        COUNT(f.`id`) AS total_frases
    FROM {$tbl_tema} t
    LEFT JOIN {$tbl_frases} f ON f.`id_tema` = t.`id`
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
                'id'           => (int) $t['id'],
                'nome'         => $t['nome'],
                'total_frases' => (int) $t['total_frases'],
            ];
        }
    }
    $stmt_temas->close();
}

// Fallback de segurança: busca temas que possuam frases caso id_jogo esteja nulo
if (empty($temas_disponiveis)) {
    $sql_fb = "
        SELECT
            t.`id`,
            t.`nome`,
            COUNT(f.`id`) AS total_frases
        FROM {$tbl_tema} t
        INNER JOIN {$tbl_frases} f ON f.`id_tema` = t.`id`
        GROUP BY t.`id`, t.`nome`
        ORDER BY t.`id` ASC
    ";
    $res_fb = $conn->query($sql_fb);
    if ($res_fb) {
        while ($t = $res_fb->fetch_assoc()) {
            $temas_disponiveis[] = [
                'id'           => (int) $t['id'],
                'nome'         => $t['nome'],
                'total_frases' => (int) $t['total_frases'],
            ];
        }
    }
}

// 2. Determinar o tema ativo selecionado via GET
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

// 3. Buscar as frases do tema selecionado
$frases_jogo = [];
if ($tema_ativo) {
    $sql_frases = "
        SELECT
            f.*,
            t.`nome` AS tema_nome
        FROM {$tbl_frases} f
        INNER JOIN {$tbl_tema} t ON t.`id` = f.`id_tema`
        WHERE f.`id_tema` = ?
        ORDER BY f.`id` ASC
    ";
    $stmt_frases = $conn->prepare($sql_frases);
    if ($stmt_frases) {
        $id_tema_busca = $tema_ativo['id'];
        $stmt_frases->bind_param("i", $id_tema_busca);
        $stmt_frases->execute();
        $res_frases = $stmt_frases->get_result();

        if ($res_frases) {
            while ($linha = $res_frases->fetch_assoc()) {
                $frase_original = trim($linha['frase'] ?? '');
                if ($frase_original === '') continue;

                $palavras = explode(' ', $frase_original);
                $palavras = array_values(array_filter($palavras, function($p) {
                    return $p !== '';
                }));

                $frases_jogo[] = [
                    'id'             => (int) $linha['id'],
                    'frase_controle' => $frase_original,
                    'palavras'       => $palavras,
                    'id_tema'        => (int) ($linha['id_tema'] ?? $tema_ativo['id']),
                    'tema_nome'      => $linha['tema_nome'] ?? $tema_ativo['nome'],
                ];
            }
        }
        $stmt_frases->close();
    }
}

// Randomiza a ordem das frases na partida
shuffle($frases_jogo);

$total_frases = count($frases_jogo);

// Serialização segura para o JavaScript
$frases_json = json_encode($frases_jogo, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?: '[]';

// Carrega os balões de cores disponíveis na pasta baloes/cores usando __DIR__ absoluto seguro
$baloes_cores_arquivos = glob(__DIR__ . '/baloes/cores/*.gif');
$baloes_cores = [];
if (!empty($baloes_cores_arquivos)) {
    foreach ($baloes_cores_arquivos as $arq) {
        $baloes_cores[] = 'baloes/cores/' . basename($arq);
    }
}
if (empty($baloes_cores)) {
    $baloes_cores = [
        'baloes/cores/balao_amarelo.gif',
        'baloes/cores/balao_laranja.gif',
        'baloes/cores/balao_rosa.gif',
        'baloes/cores/balao_verde.gif',
        'baloes/cores/balao_vermelho.gif',
    ];
}
$baloes_cores_json = json_encode($baloes_cores, JSON_UNESCAPED_SLASHES) ?: '[]';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pop the Balloons – English for Kids</title>

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

    <!-- CSS do jogo de balões -->
    <link rel="stylesheet" href="css/jogo_baloes.css">
    <!-- CSS de Acessibilidade & Modo Escuro -->
    <link rel="stylesheet" href="css/acessibilidade.css">
</head>
<body class="font-bingo min-h-screen text-tinta-escura flex flex-col justify-between p-2 sm:p-4 md:p-6 relative">

    <!-- NUVENS FLUTUANTES NO FUNDO AZUL CÉU -->
    <div id="elementos-fundo-ceu" aria-hidden="true">
        <div class="nuvem nuvem-1"></div>
        <div class="nuvem nuvem-2"></div>
        <div class="nuvem nuvem-3"></div>
        <div class="nuvem nuvem-4"></div>
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
            <a href="temas_baloes.php"
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
                <span class="inline-block w-3 h-3 bg-laranja-vibrante border-2 border-tinta-escura rounded-sm"></span>
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
                        0 / <?= $total_frases ?>
                    </span>
                </div>
            </div>
        </header>

        <!-- ÁREA PRINCIPAL DO JOGO -->
        <main id="area-jogo" class="w-full my-auto flex flex-col items-center gap-6 max-w-5xl mx-auto">

            <!-- TÍTULO -->
            <div class="text-center">
                <h1 class="font-gooddog text-4xl sm:text-5xl md:text-6xl font-bold text-tinta-escura tracking-wider leading-tight">
                    POP THE BALLOONS!
                </h1>
                <p class="font-bingo text-base sm:text-lg font-extrabold text-gray-700 tracking-wide mt-1">
                    Pop the balloons in the correct order to build the sentence!
                </p>
            </div>

            <!-- SELETOR DE TEMAS RÁPIDO -->
            <?php if (count($temas_disponiveis) > 1): ?>
            <div id="seletor-temas" class="flex flex-wrap items-center justify-center gap-2 -mt-1">
                <span class="font-bingo font-black text-xs text-gray-500 uppercase tracking-widest mr-1">TOPICS:</span>
                <?php foreach ($temas_disponiveis as $t): ?>
                    <a href="jogo_baloes.php?tema=<?= urlencode($t['id']) ?>"
                       class="cartao-dinamico px-3.5 py-1.5 rounded-xl font-bingo font-bold text-xs border-2 border-tinta-escura transition-all <?= ($tema_ativo && $tema_ativo['id'] === $t['id']) ? 'bg-amarelo-vibrante text-tinta-escura shadow-sombra-cartum-branca scale-105' : 'bg-papel-claro text-gray-600 hover:bg-white' ?>"
                       style="text-decoration: none;">
                        <?= htmlspecialchars(ucfirst($t['nome'])) ?> (<?= $t['total_frases'] ?>)
                    </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- BARRA DE PROGRESSO -->
            <div class="w-full max-w-md">
                <div class="flex justify-between items-center mb-1 font-bingo font-bold text-sm text-gray-600">
                    <span id="label-progresso">Sentence 1 of <?= $total_frases ?></span>
                    <span id="label-porcentagem">0%</span>
                </div>
                <div id="barra-progresso-externa">
                    <div id="barra-progresso-interna" style="width: 0%"></div>
                </div>
            </div>

            <?php if ($total_frases > 0): ?>

            <!-- ÁREA VISUAL ONDE OS BALÕES FLUTUAM -->
            <div id="area-baloes" class="area-baloes" aria-label="Balloon play area">
                <!-- Preenchido dinamicamente pelo JavaScript com balões flutuantes -->
            </div>

            <!-- FRASE EM CONSTRUÇÃO -->
            <div class="w-full max-w-3xl flex flex-col items-center gap-2">
                <div class="w-full flex items-center justify-between px-1">
                    <span class="font-bingo font-black text-xs text-gray-500 uppercase tracking-wider">
                        YOUR SENTENCE:
                    </span>
                    <button id="btn-desfazer"
                            class="botao-desfazer"
                            type="button"
                            title="Undo and restart this sentence">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                            <path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        <span>RESET WORDS</span>
                    </button>
                </div>

                <div id="frase-construcao" class="frase-construcao" aria-live="polite">
                    <span class="frase-construcao-label">Sentence Builder</span>
                    <!-- Preenchido pelo JavaScript com os slots de palavras -->
                </div>

                <!-- ÁREA PARA EXIBIR A FRASE CERTA ABAIXO DA ERRADA EM CASO DE ERRO -->
                <div id="box-frase-correta" class="w-full flex justify-center"></div>
            </div>

            <!-- FEEDBACK INLINE -->
            <div id="area-feedback"
                 class="text-center font-bingo font-bold text-xl min-h-[2.2rem]"
                 aria-live="polite">
            </div>

            <?php else: ?>
            <!-- Nenhuma frase encontrada no banco -->
            <div class="text-center py-12">
                <div class="w-16 h-16 mx-auto mb-4 bg-papel-claro border-2 border-tinta-escura rounded-2xl flex items-center justify-center font-bold text-xl text-gray-500">
                    0
                </div>
                <p class="font-bingo font-bold text-2xl text-gray-700">
                    No sentences found for this topic.<br>
                    Please add sentences in the Frases table!
                </p>
                <a href="temas_baloes.php"
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
                <span class="w-3.5 h-3.5 bg-verde-vibrante border border-tinta-escura rounded-full inline-block"></span>
                <span>ENGLISH FOR KIDS — GAME 02</span>
            </div>
            <div class="bg-papel-claro borda-tinta-escura px-3 py-1.5 rounded-xl text-tinta-escura uppercase font-extrabold shadow-sm font-bingo">
                POP THE BALLOONS<?= $tema_ativo ? ' — ' . htmlspecialchars(strtoupper($tema_ativo['nome'])) : '' ?>
            </div>
        </footer>

    </div><!-- /livro-conteiner-principal -->

    <!-- OVERLAY DE FINAL DE JOGO -->
    <div id="overlay-resultado" role="dialog" aria-modal="true" aria-label="Game complete">
        <div class="card-resultado card-resultado-animando">
            <div class="text-3xl font-gooddog text-verde-vibrante font-bold mb-2">COMPLETE!</div>
            <h2 class="font-gooddog text-5xl font-bold text-tinta-escura leading-tight">
                CONGRATULATIONS!
            </h2>
            <p class="font-bingo text-lg font-extrabold text-gray-600 mt-1">
                You popped all the balloons and completed all sentences!
            </p>

            <div class="my-6 bg-papel-claro borda-tinta-escura rounded-2xl p-4 shadow-sombra-cartum-branca">
                <span class="block font-bingo text-xs font-black text-azul-escuro uppercase tracking-wider">
                    FINAL SCORE
                </span>
                <span id="score-final" class="font-gooddog text-5xl font-bold text-verde-vibrante">
                    <?= $total_frases ?> / <?= $total_frases ?>
                </span>
                <div id="info-pontuacao-baloes" class="mt-2 pt-2 border-t-2 border-dashed border-gray-300"></div>
            </div>

            <div class="flex flex-col sm:flex-row gap-3 justify-center items-center">
                <button type="button"
                        id="btn-jogar-novamente"
                        class="botao-acao bg-verde-vibrante text-white w-full sm:w-auto">
                    <span>PLAY AGAIN</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                        <path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </button>

                <a href="temas_baloes.php"
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

    <!-- JAVASCRIPT DO JOGO DOS BALÕES -->
    <script>
    (function() {
        'use strict';

        const frasesData = <?= $frases_json ?: '[]' ?>;
        const totalFrases = Array.isArray(frasesData) ? frasesData.length : 0;

        const areaBaloes        = document.getElementById('area-baloes');
        const fraseConstrucao   = document.getElementById('frase-construcao');
        const boxFraseCorreta   = document.getElementById('box-frase-correta');
        const areaFeedback      = document.getElementById('area-feedback');
        const textoPlacar       = document.getElementById('texto-placar');
        const labelProgresso    = document.getElementById('label-progresso');
        const labelPorcentagem  = document.getElementById('label-porcentagem');
        const barraInterna      = document.getElementById('barra-progresso-interna');
        const btnDesfazer       = document.getElementById('btn-desfazer');
        const overlayResultado  = document.getElementById('overlay-resultado');
        const btnJogarNovamente = document.getElementById('btn-jogar-novamente');

        let indiceFraseAtual = 0;
        let acertos = 0;
        let palavrasSelecionadas = [];
        let estaValidando = false;

        const GIF_POP = 'baloes/pop.gif';
        const baloesCoresDisponiveis = <?= $baloes_cores_json ?>;

        [GIF_POP, ...(Array.isArray(baloesCoresDisponiveis) ? baloesCoresDisponiveis : [])].forEach(src => {
            const img = new Image();
            img.src = src;
        });

        if (btnDesfazer) {
            btnDesfazer.addEventListener('click', () => {
                if (estaValidando) return;
                reiniciarFraseAtual();
            });
        }

        if (btnJogarNovamente) {
            btnJogarNovamente.addEventListener('click', () => {
                if (overlayResultado) overlayResultado.classList.remove('ativo');
                indiceFraseAtual = 0;
                acertos = 0;
                if (totalFrases > 0) carregarFrase(0);
            });
        }

        if (totalFrases > 0) {
            carregarFrase(indiceFraseAtual);
        }

        function carregarFrase(indice) {
            if (indice >= totalFrases) {
                finalizarJogo();
                return;
            }

            estaValidando = false;
            palavrasSelecionadas = [];
            if (areaFeedback) areaFeedback.innerHTML = '';
            if (boxFraseCorreta) boxFraseCorreta.innerHTML = '';
            atualizarProgresso();

            const fraseObj = frasesData[indice];
            if (!fraseObj) return;
            const palavrasOriginais = Array.isArray(fraseObj.palavras) ? fraseObj.palavras : [];

            renderizarSlots(palavrasOriginais.length);
            renderizarBaloes(palavrasOriginais);
        }

        function renderizarSlots(qtd) {
            if (!fraseConstrucao) return;
            fraseConstrucao.innerHTML = '<span class="frase-construcao-label">Sentence Builder</span>';
            for (let i = 0; i < qtd; i++) {
                const slot = document.createElement('div');
                slot.className = 'palavra-slot';
                slot.id = `slot-${i}`;
                slot.textContent = '...';
                fraseConstrucao.appendChild(slot);
            }
        }

        function renderizarBaloes(palavras) {
            if (!areaBaloes) return;
            areaBaloes.innerHTML = '';

            const palavrasEmbaralhadas = [...palavras].map((p, idxOriginal) => ({
                texto: p,
                idxOriginal: idxOriginal
            }));

            for (let i = palavrasEmbaralhadas.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [palavrasEmbaralhadas[i], palavrasEmbaralhadas[j]] = [palavrasEmbaralhadas[j], palavrasEmbaralhadas[i]];
            }

            const total = palavrasEmbaralhadas.length;
            const posicoes = calcularPosicoesBaloes(total);

            palavrasEmbaralhadas.forEach((item, index) => {
                const pos = posicoes[index] || { left: 15 + index * 15, top: 20 };
                const corGif = baloesCoresDisponiveis[index % baloesCoresDisponiveis.length] || 'baloes/cores/balao_amarelo.gif';

                const balao = document.createElement('div');
                balao.className = 'balao-container balao-item balao-animando';
                balao.id = `balao-${index}`;
                balao.style.left = `${pos.left}%`;
                balao.style.top  = `${pos.top}%`;

                const animDuracao = (2.4 + (index % 3) * 0.4).toFixed(1);
                const animDelay   = (index * 0.25).toFixed(2);
                balao.style.animation = `balaoFlutuandoSuave ${animDuracao}s ease-in-out ${animDelay}s infinite alternate`;

                const img = document.createElement('img');
                img.src = corGif;
                img.alt = 'Balão colorido';
                img.className = 'balao-img';
                img.draggable = false;

                const textoSpan = document.createElement('span');
                textoSpan.className = 'balao-palavra balao-texto';
                textoSpan.textContent = item.texto;

                if (item.texto.length > 7) {
                    textoSpan.style.fontSize = '1.05rem';
                } else if (item.texto.length > 5) {
                    textoSpan.style.fontSize = '1.25rem';
                }

                balao.appendChild(img);
                balao.appendChild(textoSpan);

                balao.addEventListener('click', () => {
                    if (estaValidando) return;
                    if (balao.classList.contains('balao-estourado')) return;
                    estourarBalao(balao, img, item.texto);
                });

                areaBaloes.appendChild(balao);
            });
        }

        function calcularPosicoesBaloes(qtd) {
            const layouts = {
                1: [{ left: 50, top: 45 }],
                2: [{ left: 30, top: 45 }, { left: 70, top: 45 }],
                3: [{ left: 22, top: 45 }, { left: 50, top: 40 }, { left: 78, top: 45 }],
                4: [
                    { left: 26, top: 28 }, { left: 74, top: 28 },
                    { left: 26, top: 70 }, { left: 74, top: 70 }
                ],
                5: [
                    { left: 20, top: 28 }, { left: 50, top: 24 }, { left: 80, top: 28 },
                    { left: 35, top: 70 }, { left: 65, top: 70 }
                ],
                6: [
                    { left: 20, top: 28 }, { left: 50, top: 26 }, { left: 80, top: 28 },
                    { left: 20, top: 70 }, { left: 50, top: 72 }, { left: 80, top: 70 }
                ]
            };

            if (layouts[qtd]) return layouts[qtd];

            const coords = [];
            const cols = Math.min(4, Math.ceil(qtd / 2));
            for (let i = 0; i < qtd; i++) {
                const row = i < cols ? 0 : 1;
                const colIdx = i % cols;
                coords.push({
                    left: Math.round(18 + (colIdx * (64 / Math.max(1, cols - 1)))),
                    top:  row === 0 ? 28 : 70
                });
            }
            return coords;
        }

        function estourarBalao(balaoEl, imgEl, palavra) {
            balaoEl.classList.add('balao-estourado');
            balaoEl.style.animation = 'none';

            imgEl.src = `${GIF_POP}?t=${Date.now()}`;

            const textoEl = balaoEl.querySelector('.balao-texto');
            if (textoEl) {
                textoEl.style.opacity = '0';
                textoEl.style.transform = 'translate(-50%, -50%) scale(1.4)';
            }

            const slotIdx = palavrasSelecionadas.length;
            palavrasSelecionadas.push(palavra);
            preencherSlot(slotIdx, palavra);

            setTimeout(() => {
                balaoEl.style.opacity = '0';
                balaoEl.style.pointerEvents = 'none';
            }, 480);

            verificarConclusaoRodada();
        }

        function preencherSlot(slotIdx, palavra) {
            const slot = document.getElementById(`slot-${slotIdx}`);
            if (slot) {
                slot.textContent = palavra;
                slot.classList.add('palavra-slot-preenchido', 'palavra-slot-animando');
            }
        }

        function verificarConclusaoRodada() {
            const fraseObj = frasesData[indiceFraseAtual];
            if (!fraseObj || !Array.isArray(fraseObj.palavras)) return;
            const totalPalavras = fraseObj.palavras.length;

            if (palavrasSelecionadas.length < totalPalavras) {
                return;
            }

            estaValidando = true;

            const fraseMontada = palavrasSelecionadas.join(' ').trim().toLowerCase();
            const fraseEsperada = String(fraseObj.frase_controle || '').trim().toLowerCase();

            if (fraseMontada === fraseEsperada) {
                acertos++;
                if (boxFraseCorreta) boxFraseCorreta.innerHTML = '';
                if (areaFeedback) {
                    areaFeedback.innerHTML = `
                        <span class="feedback-acerto flex items-center justify-center gap-2">
                            <span>EXCELLENT! YOU BUILT THE SENTENCE!</span>
                        </span>
                    `;
                }

                for (let i = 0; i < totalPalavras; i++) {
                    const slot = document.getElementById(`slot-${i}`);
                    if (slot) {
                        slot.style.borderColor = '#00C853';
                        slot.style.backgroundColor = '#E8F5E9';
                        slot.style.color = '#054D20';
                    }
                }

                atualizarProgresso();

                setTimeout(() => {
                    indiceFraseAtual++;
                    carregarFrase(indiceFraseAtual);
                }, 1400);

            } else {
                for (let i = 0; i < totalPalavras; i++) {
                    const slot = document.getElementById(`slot-${i}`);
                    if (slot) {
                        slot.classList.add('palavra-slot-erro');
                    }
                }

                if (boxFraseCorreta) {
                    boxFraseCorreta.innerHTML = `
                        <div class="card-correcao w-full max-w-xl text-center">
                            <span class="card-correcao-tag">
                                <span>CORRECT SENTENCE:</span>
                            </span>
                            <div class="card-correcao-frase">
                                "${fraseObj.frase_controle}"
                            </div>
                        </div>
                    `;
                }

                if (areaFeedback) {
                    areaFeedback.innerHTML = `
                        <span class="feedback-erro flex items-center justify-center gap-2">
                            <span>OOPS! TRY AGAIN!</span>
                        </span>
                    `;
                }

                setTimeout(() => {
                    reiniciarFraseAtual();
                }, 2800);
            }
        }

        function reiniciarFraseAtual() {
            estaValidando = false;
            palavrasSelecionadas = [];
            if (areaFeedback) areaFeedback.innerHTML = '';
            if (boxFraseCorreta) boxFraseCorreta.innerHTML = '';

            const fraseObj = frasesData[indiceFraseAtual];
            if (!fraseObj) return;
            const palavrasOriginais = Array.isArray(fraseObj.palavras) ? fraseObj.palavras : [];

            renderizarSlots(palavrasOriginais.length);
            renderizarBaloes(palavrasOriginais);
        }

        function atualizarProgresso() {
            if (!totalFrases) return;
            const respondidas = indiceFraseAtual;
            const pct = Math.round((respondidas / totalFrases) * 100);

            if (labelProgresso)   labelProgresso.textContent   = `Sentence ${indiceFraseAtual + 1} of ${totalFrases}`;
            if (labelPorcentagem) labelPorcentagem.textContent = `${pct}%`;
            if (barraInterna)     barraInterna.style.width     = `${pct}%`;
            if (textoPlacar)      textoPlacar.textContent      = `${acertos} / ${totalFrases}`;
        }

        function finalizarJogo() {
            if (!overlayResultado) return;
            const scoreFinal = document.getElementById('score-final');
            if (scoreFinal) {
                scoreFinal.textContent = `${acertos} / ${totalFrases}`;
            }

            if (labelProgresso)   labelProgresso.textContent   = `Completed! ${totalFrases} of ${totalFrases}`;
            if (labelPorcentagem) labelPorcentagem.textContent = '100%';
            if (barraInterna)     barraInterna.style.width     = '100%';

            // Salva estatísticas e pontuação calculada no backend (acertos*100 - erros*65)
            fetch('salvar_pontuacao.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ questoesRespondidas: totalFrases, acertos: acertos })
            }).then(r => r.json()).then(data => {
                const elInfo = document.getElementById('info-pontuacao-baloes');
                if (elInfo && data && data.sucesso) {
                    const sinal = data.pontuacao_partida > 0 ? '+' : '';
                    elInfo.innerHTML = `
                        <span class="block text-xs font-black text-verde-escuro uppercase tracking-wider font-bingo">PONTOS GANHOS</span>
                        <span class="font-gooddog text-3xl font-bold text-verde-vibrante">${sinal}${data.pontuacao_partida} pts</span>
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
