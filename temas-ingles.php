<?php
/* ============================================================
   temas-ingles.php — Seleção de Temas (Post-its Estilizados)
   English for Kids — Fundamental 1
   Schema: tccdb | Tema → Pergunta
   ============================================================ */

require_once 'config/config.php';

// Busca todos os temas cadastrados via SELECT * no banco de dados
$sql_temas = "SELECT * FROM `Tema` ORDER BY `id` ASC";
$resultado_temas = $conn->query($sql_temas);

$temas = [];
if ($resultado_temas) {
    while ($linha = $resultado_temas->fetch_assoc()) {
        $temas[] = $linha;
    }
}

// Designs disponíveis na pasta post-it (1.png a 6.png)
$designs_disponiveis = [1, 2, 3, 4, 5, 6];
$total_designs = count($designs_disponiveis);

// Mapeamento dos designs garantindo que 2 post-its iguais NUNCA fiquem lado a lado
$designs_cards = [];
$ultimo_design = null;

foreach ($temas as $i => $tema) {
    // Escolhe o próximo design de forma sequencial cíclica
    $design_escolhido = $designs_disponiveis[$i % $total_designs];

    // Se por acaso coincidir com o anterior, avança para o próximo
    if ($design_escolhido === $ultimo_design) {
        $design_escolhido = $designs_disponiveis[($i + 1) % $total_designs];
    }

    $designs_cards[$i] = $design_escolhido;
    $ultimo_design = $design_escolhido;
}

// Classes de inclinação lúdica para cada posição
$inclinacoes = [
    'postit-inclinado-1',
    'postit-inclinado-2',
    'postit-inclinado-3',
    'postit-inclinado-4',
    'postit-inclinado-5',
    'postit-inclinado-6'
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choose a Topic – English for Kids</title>

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
                        'gooddog':     ['Gooddog', 'cursive', 'sans-serif'],
                        'bingo':       ['Bingo', 'cursive', 'sans-serif'],
                        'cutedolphin': ['"Cute Dolphin"', 'cursive', 'sans-serif'],
                        'fredoka':     ['Fredoka', 'sans-serif'],
                        'nunito':      ['Nunito', 'sans-serif'],
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

    <!-- CSS personalizado dos Temas e Post-its -->
    <link rel="stylesheet" href="css/temas-ingles.css">
</head>
<body class="font-bingo min-h-screen text-tinta-escura flex flex-col justify-between p-2 sm:p-4 md:p-6 relative">

    <!-- ELEMENTOS OSCILANTES DE FUNDO -->
    <div id="elementos-fundo-oscilantes" aria-hidden="true">
        <div class="elemento-fundo-oscilante elemento-fundo-1"></div>
        <div class="elemento-fundo-oscilante elemento-fundo-2"></div>
        <div class="elemento-fundo-oscilante elemento-fundo-3"></div>
        <div class="elemento-fundo-oscilante elemento-fundo-4"></div>
    </div>

    <!-- LIVRO / PÁGINA DE CADERNO -->
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
        <header id="cabecalho-principal"
                class="w-full flex flex-wrap items-center justify-between gap-4 pb-4 mb-4">

            <!-- Botão Voltar para a Home -->
            <a href="index.php"
               class="cartao-dinamico flex items-center gap-2 bg-papel-claro borda-tinta-escura rounded-2xl px-4 py-2 shadow-sombra-cartum-branca font-bingo font-bold text-sm text-tinta-escura hover:bg-yellow-100 transition-colors"
               style="text-decoration: none;">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                    <path d="M19 12H5M12 5l-7 7 7 7"/>
                </svg>
                BACK TO MENU
            </a>

            <!-- Perfil do Aluno -->
            <div id="perfil-aluno"
                 class="flex items-center gap-3 bg-papel-claro borda-tinta-escura rounded-2xl px-4 py-2 shadow-sombra-cartum-branca">
                <div class="w-9 h-9 bg-amarelo-vibrante borda-tinta-escura rounded-xl flex items-center justify-center font-bold">
                    <span class="text-lg">⭐</span>
                </div>
                <div>
                    <span class="block text-xs font-black text-verde-vibrante uppercase tracking-wider font-bingo">LEVEL 1</span>
                    <span class="block font-bingo text-base font-bold text-tinta-escura leading-none">APRENDIZ SABIDO</span>
                </div>
            </div>

            <!-- Badge Informativa do Jogo -->
            <div class="hidden sm:flex items-center gap-2 bg-papel-claro borda-tinta-escura rounded-2xl px-4 py-2 shadow-sombra-cartum-branca font-bingo font-extrabold text-sm text-azul-escuro">
                <span>🇬🇧</span>
                <span>ENGLISH FOR KIDS</span>
            </div>
        </header>

        <!-- ÁREA PRINCIPAL: SELEÇÃO DE TEMAS -->
        <main id="conteudo-principal" class="w-full my-auto flex flex-col items-center gap-6 max-w-5xl mx-auto py-2">

            <!-- TÍTULO DA PÁGINA -->
            <div class="text-center max-w-3xl">
                <h1 class="font-gooddog text-5xl sm:text-6xl md:text-7xl font-bold text-tinta-escura tracking-wider leading-tight">
                    CHOOSE A TOPIC!
                </h1>
                <p class="font-bingo text-base sm:text-xl font-extrabold text-gray-700 tracking-wide mt-2">
                    Click on a post-it note below to start the game!
                </p>
            </div>

            <?php if (!empty($temas)): ?>

            <!-- FORMULÁRIO DE SELEÇÃO: ENVIA GET 'tema' COM O ID DO TEMA SELECIONADO -->
            <form action="jogo-ingles.php" method="GET" id="form-selecao-temas" class="w-full">
                <div id="grade-postits"
                     class="w-full grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8 sm:gap-8 md:gap-10 justify-items-center items-center py-4">

                    <?php foreach ($temas as $indice => $t): ?>
                        <?php
                            $id_tema    = (int) $t['id'];
                            $nome_tema  = $t['nome'];
                            $num_design = $designs_cards[$indice];
                            $inclinacao = $inclinacoes[$indice % count($inclinacoes)];
                        ?>

                        <!-- CARD EM FORMATO DE INPUT / SUBMIT BUTTON -->
                        <button type="submit"
                                name="tema"
                                value="<?= $id_tema ?>"
                                class="cartao-postit <?= $inclinacao ?> group"
                                title="Play Topic: <?= htmlspecialchars($nome_tema) ?>"
                                aria-label="Topic <?= htmlspecialchars($nome_tema) ?>">

                            <!-- Imagem do Post-it estilizado -->
                            <img src="post-it/<?= $num_design ?>.png"
                                 alt="Post-it <?= htmlspecialchars($nome_tema) ?>"
                                 class="postit-imagem"
                                 draggable="false">

                            <!-- Conteúdo do Post-it com a fonte Cute Dolphin -->
                            <div class="postit-conteudo">
                                <h2 class="postit-titulo font-cutedolphin postit-texto-escuro">
                                    <?= htmlspecialchars($nome_tema) ?>
                                </h2>

                                <span class="postit-badge-play postit-badge-escuro mt-1">
                                    <span>PLAY</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                        <path d="M5 12h14M12 5l7 7-7 7"/>
                                    </svg>
                                </span>
                            </div>
                        </button>

                    <?php endforeach; ?>

                </div>
            </form>

            <?php else: ?>

            <!-- Mensagem caso não haja nenhum tema cadastrado -->
            <div class="text-center py-12">
                <p class="text-6xl mb-4">📭</p>
                <p class="font-bingo font-bold text-2xl text-gray-700">
                    No topics found in the database.<br>
                    Please insert topics in the Tema table!
                </p>
                <a href="index.php"
                   class="botao-acao mt-6 bg-azul-vibrante text-white inline-flex"
                   style="text-decoration: none;">
                    ← Back to Menu
                </a>
            </div>

            <?php endif; ?>

        </main>

        <!-- RODAPÉ -->
        <footer id="rodape-temas"
                class="w-full pt-4 mt-6 flex flex-wrap items-center justify-between gap-3 text-xs md:text-sm font-bingo font-bold text-gray-700">
            <div class="flex items-center gap-2">
                <span class="w-3.5 h-3.5 bg-verde-vibrante border border-tinta-escura rounded-full inline-block"></span>
                <span>ENGLISH FOR KIDS — TOPIC SELECTION</span>
            </div>
            <div class="bg-papel-claro borda-tinta-escura px-3 py-1.5 rounded-xl text-tinta-escura uppercase font-extrabold shadow-sm font-bingo">
                COMPLETE THE SENTENCE
            </div>
        </footer>

    </div><!-- /livro-conteiner-principal -->

</body>
</html>
