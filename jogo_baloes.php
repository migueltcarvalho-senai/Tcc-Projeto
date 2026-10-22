<?php
/* ============================================================
   jogo_baloes.php — Estoure os Balões! (Pop the Balloons)
   English for Kids — Fundamental 1 | Sentence Builder Game
   Schema: tccdb | Jogo ('ballons') → Tema → Frases
   ============================================================ */

require_once 'config/config.php';

$id_jogo = 2;

// 1. Obter todos os temas disponíveis para o jogo dos balões
$sql_temas = "
    SELECT
        t.`id`,
        t.`nome`,
        COUNT(f.`id`) AS total_frases
    FROM `Tema` t
    LEFT JOIN `Frases` f ON f.`id_tema` = t.`id`
    WHERE t.`id_jogo` = ?
    GROUP BY t.`id`, t.`nome`
    ORDER BY t.`id` ASC
";
$stmt_temas = $conn->prepare($sql_temas);
$stmt_temas->bind_param("i", $id_jogo);
$stmt_temas->execute();
$res_temas = $stmt_temas->get_result();

$temas_disponiveis = [];
while ($t = $res_temas->fetch_assoc()) {
    $temas_disponiveis[] = [
        'id'           => (int) $t['id'],
        'nome'         => $t['nome'],
        'total_frases' => (int) $t['total_frases'],
    ];
}
$stmt_temas->close();

// Fallback de segurança: caso id_jogo ainda não esteja configurado no banco, busca temas vinculados a frases
if (empty($temas_disponiveis)) {
    $sql_fb = "
        SELECT
            t.`id`,
            t.`nome`,
            COUNT(f.`id`) AS total_frases
        FROM `Tema` t
        INNER JOIN `Frases` f ON f.`id_tema` = t.`id`
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

// 2. Determinar o tema ativo selecionado via GET (tema ou id_tema)
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

    // Se nenhum tema foi especificado ou encontrado, usa o primeiro tema
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
        FROM `Frases` f
        INNER JOIN `Tema` t ON t.`id` = f.`id_tema`
        WHERE f.`id_tema` = ?
        ORDER BY f.`id` ASC
    ";
    $stmt_frases = $conn->prepare($sql_frases);
    $id_tema_busca = $tema_ativo['id'];
    $stmt_frases->bind_param("i", $id_tema_busca);
    $stmt_frases->execute();
    $res_frases = $stmt_frases->get_result();

    while ($linha = $res_frases->fetch_assoc()) {
        $frase_original = trim($linha['frase']);

        /* ============================================================
           MÉTODO EXPLODE DO PHP CONFORME ESPECIFICADO NO PROMPT:
           Separa a frase da tabela Frases em palavras individuais
           e guarda a frase original em variável de controle.
           ============================================================ */
        $palavras = explode(' ', $frase_original);
        // Remove eventuais espaços duplicados vazios
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
    $stmt_frases->close();
}

// Randomiza a ordem das frases na partida
shuffle($frases_jogo);

$total_frases = count($frases_jogo);

/* Serializar para o JavaScript de forma segura */
$frases_json = json_encode($frases_jogo, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP);

// Carrega dinamicamente os balões de cores disponíveis na pasta baloes/cores
$baloes_cores_arquivos = glob('baloes/cores/*.gif');
$baloes_cores = [];
if (!empty($baloes_cores_arquivos)) {
    foreach ($baloes_cores_arquivos as $arq) {
        $baloes_cores[] = str_replace('\\', '/', $arq);
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
$baloes_cores_json = json_encode($baloes_cores, JSON_UNESCAPED_SLASHES);
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
                <span class="text-lg">🎈</span>
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
                <span class="text-xl">⭐</span>
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
                    Pop the balloons in the correct order to build the sentence! 🎈
                </p>
            </div>

            <!-- SELETOR DE TEMAS RÁPIDO (se houver mais de um) -->
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
                <p class="text-6xl mb-4">🎈</p>
                <p class="font-bingo font-bold text-2xl text-gray-700">
                    No sentences found for this topic.<br>
                    Please add sentences in the Frases table!
                </p>
                <a href="temas_baloes.php"
                   class="botao-acao mt-6 bg-azul-vibrante text-white inline-flex"
                   style="text-decoration: none;">
                    ← Back to Topics
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
            <div class="text-6xl mb-3">🎉🎈⭐</div>
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
                    <span>🎈</span>
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

    <!-- ============================================================
         JAVASCRIPT DO JOGO DOS BALÕES
         ============================================================ -->
    <script>
    (function() {
        'use strict';

        // 1. Dados das frases recebidas do PHP (já processadas com explode)
        const frasesData = <?= $frases_json ?: '[]' ?>;
        const totalFrases = frasesData.length;

        if (totalFrases === 0) return;

        // 2. Elementos da Interface
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

        // 3. Estados do Jogo
        let indiceFraseAtual = 0;
        let acertos = 0;
        let palavrasSelecionadas = []; // palavras na ordem em que o usuário clicou
        let estaValidando = false;

        // 4. Balões Coloridos e Animação de Explosão (Pop)
        const GIF_POP = 'baloes/pop.gif';
        const baloesCoresDisponiveis = <?= $baloes_cores_json ?>;

        // Pré-carregamento dos GIFs das cores e do pop.gif para resposta instantânea ao clique
        [GIF_POP, ...baloesCoresDisponiveis].forEach(src => {
            const img = new Image();
            img.src = src;
        });

        // 5. Iniciar o Jogo
        carregarFrase(indiceFraseAtual);

        btnDesfazer.addEventListener('click', () => {
            if (estaValidando) return;
            reiniciarFraseAtual();
        });

        btnJogarNovamente.addEventListener('click', () => {
            overlayResultado.classList.remove('ativo');
            indiceFraseAtual = 0;
            acertos = 0;
            carregarFrase(0);
        });

        // 6. Carregar Frase da Rodada
        function carregarFrase(indice) {
            if (indice >= totalFrases) {
                finalizarJogo();
                return;
            }

            estaValidando = false;
            palavrasSelecionadas = [];
            areaFeedback.innerHTML = '';
            if (boxFraseCorreta) boxFraseCorreta.innerHTML = '';
            atualizarProgresso();

            const fraseObj = frasesData[indice];
            const palavrasOriginais = fraseObj.palavras; // geradas pelo explode do PHP

            // Renderiza os slots da frase em construção
            renderizarSlots(palavrasOriginais.length);

            // Renderiza os balões na tela com as palavras embaralhadas
            renderizarBaloes(palavrasOriginais);
        }

        // 7. Renderizar Slots Vazios na Frase
        function renderizarSlots(qtd) {
            fraseConstrucao.innerHTML = '<span class="frase-construcao-label">Sentence Builder</span>';
            for (let i = 0; i < qtd; i++) {
                const slot = document.createElement('div');
                slot.className = 'palavra-slot';
                slot.id = `slot-${i}`;
                slot.textContent = '...';
                fraseConstrucao.appendChild(slot);
            }
        }

        // 8. Renderizar Balões Espalhados pela Área Azul
        function renderizarBaloes(palavras) {
            areaBaloes.innerHTML = '';

            // Cria uma cópia com id para cada palavra e embaralha
            const listaPalavras = palavras.map((p, idx) => ({
                id: `p-${idx}-${Date.now()}`,
                texto: p,
                indiceOriginal: idx
            }));

            // Algoritmo Fisher-Yates de embaralhamento
            for (let i = listaPalavras.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [listaPalavras[i], listaPalavras[j]] = [listaPalavras[j], listaPalavras[i]];
            }

            const total = listaPalavras.length;
            const containerWidth = areaBaloes.clientWidth || 850;
            const containerHeight = areaBaloes.clientHeight || 540;

            const isMobile = window.innerWidth <= 640;
            const balaoLargura = isMobile ? 125 : 165;
            const balaoAltura  = isMobile ? 152 : 198;

            // Margens de segurança estritas (impedem qualquer sobreposição física ou de flutuação)
            const BORDA_X = isMobile ? 18 : 32;
            const BORDA_Y = isMobile ? 16 : 24;

            // Gera slots espaciais garantidos por geometria
            const slotsDisponiveis = [];

            if (total <= 3) {
                // Linha única com espaçamento horizontal máximo e desnível suave
                const espacoUtil = containerWidth - (2 * BORDA_X);
                const stepX = (espacoUtil - balaoLargura) / Math.max(total - 1, 1);
                const baseY = (containerHeight - balaoAltura) / 2;

                for (let i = 0; i < total; i++) {
                    const posX = (total === 1)
                        ? (containerWidth - balaoLargura) / 2
                        : BORDA_X + (i * stepX);
                    const desnível = (i % 2 === 0 ? -18 : 18);
                    const posY = Math.max(BORDA_Y, Math.min(containerHeight - balaoAltura - BORDA_Y, baseY + desnível));

                    slotsDisponiveis.push({ x: posX, y: posY });
                }
            } else if (total === 4) {
                // 2 Colunas x 2 Linhas: ampla margem horizontal e vertical
                const topY    = BORDA_Y + 12;
                const bottomY = containerHeight - balaoAltura - BORDA_Y - 12;

                const col0X = BORDA_X + (containerWidth * 0.12);
                const col1X = containerWidth - balaoLargura - BORDA_X - (containerWidth * 0.12);

                slotsDisponiveis.push(
                    { x: col0X, y: topY },
                    { x: col1X, y: topY },
                    { x: col0X + 24, y: bottomY },
                    { x: col1X - 24, y: bottomY }
                );
            } else if (total === 5) {
                // Padrão Quinconce: 3 balões no topo e 2 balões nos vãos de baixo (nunca colidem!)
                const topY    = BORDA_Y + 10;
                const bottomY = containerHeight - balaoAltura - BORDA_Y - 10;

                const stepTopX = (containerWidth - (2 * BORDA_X) - balaoLargura) / 2;
                const x0 = BORDA_X;
                const x1 = BORDA_X + stepTopX;
                const x2 = containerWidth - balaoLargura - BORDA_X;

                // Os 2 balões de baixo ficam exatamente nos vãos intermediários
                const x3 = (x0 + x1) / 2;
                const x4 = (x1 + x2) / 2;

                slotsDisponiveis.push(
                    { x: x0, y: topY },
                    { x: x1, y: topY - 6 },
                    { x: x2, y: topY },
                    { x: x3, y: bottomY },
                    { x: x4, y: bottomY }
                );
            } else {
                // 6 ou mais balões: 2 linhas bem separadas no topo e no fundo
                const topY    = BORDA_Y + 8;
                const bottomY = containerHeight - balaoAltura - BORDA_Y - 8;
                const cols = Math.ceil(total / 2);
                const stepX = (containerWidth - (2 * BORDA_X) - balaoLargura) / (cols - 1);

                for (let i = 0; i < cols; i++) {
                    slotsDisponiveis.push({ x: BORDA_X + (i * stepX), y: topY });
                }
                for (let i = 0; i < (total - cols); i++) {
                    const offsetMeio = (cols > 2) ? stepX / 2 : 0;
                    slotsDisponiveis.push({ x: BORDA_X + offsetMeio + (i * stepX), y: bottomY });
                }
            }

            // Embaralha a atribuição de slots para manter o jogo dinâmico e lúdico
            const slotsSorteados = [...slotsDisponiveis];
            for (let i = slotsSorteados.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [slotsSorteados[i], slotsSorteados[j]] = [slotsSorteados[j], slotsSorteados[i]];
            }

            // Redistribuição randômica das cores dos balões (embaralha a paleta de cores para cada rodada)
            const coresEmbaralhadas = [...baloesCoresDisponiveis];
            for (let i = coresEmbaralhadas.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [coresEmbaralhadas[i], coresEmbaralhadas[j]] = [coresEmbaralhadas[j], coresEmbaralhadas[i]];
            }

            listaPalavras.forEach((item, index) => {
                const balao = document.createElement('div');
                const animacaoClasse = `balao-flutuar-${(index % 3) + 1}`;
                balao.className = `balao-container ${animacaoClasse}`;
                balao.dataset.palavra = item.texto;
                balao.dataset.id = item.id;

                // Atribui uma cor da paleta embaralhada garantindo diversidade visual entre os balões
                const corEscolhida = coresEmbaralhadas[index % coresEmbaralhadas.length];

                // Posição com margem garantida
                const slot = slotsSorteados[index % slotsSorteados.length];
                const posX = Math.max(BORDA_X, Math.min(containerWidth - balaoLargura - BORDA_X, Math.round(slot.x)));
                const posY = Math.max(BORDA_Y, Math.min(containerHeight - balaoAltura - BORDA_Y, Math.round(slot.y)));

                balao.style.left = `${posX}px`;
                balao.style.top = `${posY}px`;

                // Variação orgânica de flutuação e rotação
                const duracaoFlutuar = (2.8 + Math.random() * 1.4).toFixed(2);
                const delayFlutuar = -(Math.random() * 2.5).toFixed(2);
                const rotacaoInicial = (Math.random() * 6 - 3).toFixed(1);
                balao.style.animationDuration = `${duracaoFlutuar}s`;
                balao.style.animationDelay = `${delayFlutuar}s`;
                balao.style.transform = `rotate(${rotacaoInicial}deg)`;

                // Imagem do balão colorido em GIF (o arquivo GIF já possui animação de oscilação contínua)
                const img = document.createElement('img');
                img.src = corEscolhida;
                img.className = 'balao-img';
                img.alt = `Balloon with word ${item.texto}`;
                img.draggable = false;

                // Texto ampliado sobreposto ao balão com ajuste de escala para visibilidade total
                const spanPalavra = document.createElement('span');
                spanPalavra.className = 'balao-palavra';
                spanPalavra.textContent = item.texto;

                // Ajuste proporcional do tamanho da fonte para palavras longas nunca cortarem
                const len = item.texto.length;
                if (len >= 8) {
                    spanPalavra.style.fontSize = isMobile ? '0.95rem' : '1.3rem';
                } else if (len >= 6) {
                    spanPalavra.style.fontSize = isMobile ? '1.1rem' : '1.45rem';
                } else {
                    spanPalavra.style.fontSize = isMobile ? '1.25rem' : '1.65rem';
                }

                balao.appendChild(img);
                balao.appendChild(spanPalavra);

                // Evento de clique para estourar o balão
                balao.addEventListener('click', () => {
                    if (estaValidando || balao.classList.contains('balao-explodindo')) return;
                    estourarBalao(balao, img, spanPalavra, item.texto);
                });

                areaBaloes.appendChild(balao);
            });
        }

        // 9. Ação de Estourar o Balão (Pop com pop.gif)
        function estourarBalao(balao, img, spanPalavra, palavra) {
            balao.classList.add('balao-explodindo');

            // Esconde o texto da palavra imediatamente para evidenciar a explosão
            spanPalavra.style.display = 'none';

            // Adiciona a palavra na frase em construção
            const slotIdx = palavrasSelecionadas.length;
            palavrasSelecionadas.push(palavra);
            preencherSlot(slotIdx, palavra);

            // Troca para o GIF de explosão com timestamp para forçar reinício da animação a partir do frame 0
            img.src = `${GIF_POP}?t=${Date.now()}`;

            // Aguarda o ciclo de 9 frames do pop.gif (~500ms) para sumir suavemente
            setTimeout(() => {
                balao.classList.add('balao-sumindo');
                setTimeout(() => {
                    if (balao.parentNode) {
                        balao.parentNode.removeChild(balao);
                    }
                }, 250);

                // Verifica se todas as palavras foram selecionadas
                verificarConclusaoRodada();
            }, 500);
        }

        // 10. Preencher Slot na Frase em Construção
        function preencherSlot(slotIdx, palavra) {
            const slot = document.getElementById(`slot-${slotIdx}`);
            if (slot) {
                slot.textContent = palavra;
                slot.classList.add('palavra-slot-preenchido', 'palavra-slot-animando');
            }
        }

        // 11. Verificar Conclusão da Rodada
        function verificarConclusaoRodada() {
            const fraseObj = frasesData[indiceFraseAtual];
            const totalPalavras = fraseObj.palavras.length;

            if (palavrasSelecionadas.length < totalPalavras) {
                return; // ainda restam balões
            }

            // Todas as palavras foram selecionadas! Compara com a frase de controle
            estaValidando = true;

            const fraseMontada = palavrasSelecionadas.join(' ').trim().toLowerCase();
            const fraseEsperada = fraseObj.frase_controle.trim().toLowerCase();

            if (fraseMontada === fraseEsperada) {
                // ACERTOU!
                acertos++;
                if (boxFraseCorreta) boxFraseCorreta.innerHTML = '';
                areaFeedback.innerHTML = `
                    <span class="feedback-acerto flex items-center justify-center gap-2">
                        <span>⭐ EXCELLENT! YOU BUILT THE SENTENCE! ⭐</span>
                    </span>
                `;

                // Destaca slots como sucesso
                for (let i = 0; i < totalPalavras; i++) {
                    const slot = document.getElementById(`slot-${i}`);
                    if (slot) {
                        slot.style.borderColor = '#00C853';
                        slot.style.backgroundColor = '#E8F5E9';
                        slot.style.color = '#054D20';
                    }
                }

                atualizarProgresso();

                // Aguarda 1.4s e avança para a próxima frase
                setTimeout(() => {
                    indiceFraseAtual++;
                    carregarFrase(indiceFraseAtual);
                }, 1400);

            } else {
                // ERROU! Destaca os slots da frase errada em vermelho
                for (let i = 0; i < totalPalavras; i++) {
                    const slot = document.getElementById(`slot-${i}`);
                    if (slot) {
                        slot.classList.add('palavra-slot-erro');
                    }
                }

                // Exibe a frase certa abaixo da frase errada
                if (boxFraseCorreta) {
                    boxFraseCorreta.innerHTML = `
                        <div class="card-correcao w-full max-w-xl text-center">
                            <span class="card-correcao-tag">
                                <span>💡 CORRECT SENTENCE:</span>
                            </span>
                            <div class="card-correcao-frase">
                                "${fraseObj.frase_controle}"
                            </div>
                            <button type="button" id="btn-tentar-agora" class="mt-2 bg-verde-vibrante text-white text-xs font-bingo font-extrabold px-3.5 py-1.5 rounded-xl border-2 border-tinta-escura shadow-sm hover:scale-105 transition-transform flex items-center gap-1.5 cursor-pointer">
                                <span>TRY AGAIN NOW</span>
                                <span>🔄</span>
                            </button>
                        </div>
                    `;
                }

                areaFeedback.innerHTML = `
                    <span class="feedback-erro flex items-center justify-center gap-2">
                        <span>❌ OOPS! TAKE A LOOK AT THE CORRECT SENTENCE! 🔄</span>
                    </span>
                `;

                // Efeito visual de tremor na frase errada
                fraseConstrucao.classList.add('animate-bounce');

                let resetTimer = null;
                const executarReset = () => {
                    if (resetTimer) {
                        clearTimeout(resetTimer);
                        resetTimer = null;
                    }
                    fraseConstrucao.classList.remove('animate-bounce');
                    if (boxFraseCorreta) boxFraseCorreta.innerHTML = '';
                    reiniciarFraseAtual();
                };

                const btnTentar = document.getElementById('btn-tentar-agora');
                if (btnTentar) {
                    btnTentar.addEventListener('click', executarReset);
                }

                // Dá 3.8s para a criança ler a frase certa confortavelmente antes de reiniciar
                resetTimer = setTimeout(executarReset, 3800);
            }
        }

        // 12. Reiniciar Frase Atual (em caso de erro ou botão Reset)
        function reiniciarFraseAtual() {
            palavrasSelecionadas = [];
            estaValidando = false;
            areaFeedback.innerHTML = '';
            if (boxFraseCorreta) boxFraseCorreta.innerHTML = '';
            const fraseObj = frasesData[indiceFraseAtual];
            renderizarSlots(fraseObj.palavras.length);
            renderizarBaloes(fraseObj.palavras);
        }

        // 13. Atualizar Barra de Progresso e Placar
        function atualizarProgresso() {
            textoPlacar.textContent = `${acertos} / ${totalFrases}`;
            labelProgresso.textContent = `Sentence ${Math.min(indiceFraseAtual + 1, totalFrases)} of ${totalFrases}`;

            const percent = Math.round((indiceFraseAtual / totalFrases) * 100);
            labelPorcentagem.textContent = `${percent}%`;
            barraInterna.style.width = `${percent}%`;
        }

        // 14. Finalizar Jogo (Overlay de Vitória)
        function finalizarJogo() {
            textoPlacar.textContent = `${acertos} / ${totalFrases}`;
            barraInterna.style.width = '100%';
            labelPorcentagem.textContent = '100%';

            document.getElementById('score-final').textContent = `${acertos} / ${totalFrases}`;
            overlayResultado.classList.add('ativo');
        }

        // Ajustar posições dos balões se a tela for redimensionada
        window.addEventListener('resize', () => {
            if (!estaValidando && palavrasSelecionadas.length === 0) {
                const fraseObj = frasesData[indiceFraseAtual];
                if (fraseObj) {
                    renderizarBaloes(fraseObj.palavras);
                }
            }
        });

    })();
    </script>
</body>
</html>
