<?php
/* ============================================================
   jogo-ingles.php — Complete the Sentence! (Gap Fill Quiz)
   English for Kids — Fundamental 1
   Schema: tccdb | Jogo → Pergunta → Conjunto de respostas erradas
   ============================================================ */

   require_once 'config/config.php';

   $id_jogo = 1;
   $sql = "
       SELECT
           p.`id`,
           p.`primeira parte`   AS primeira_parte,
           p.`resposta`,
           p.`segunda parte`    AS segunda_parte,
           c.`conjunto`
       FROM `Pergunta` p
       LEFT JOIN `Conjunto de respostas erradas` c ON c.`Pergunta_id` = p.`id`
       WHERE p.`Jogo_id` = ?
       ORDER BY p.`id` ASC
   ";
   $stmt = $conn->prepare($sql);
   $stmt->bind_param("i", $id_jogo);
   $stmt->execute();
   $resultado = $stmt->get_result();

   $perguntas = [];
   while ($linha = $resultado->fetch_assoc()) {
       $perguntas[] = [
           'id'             => (int) $linha['id'],
           'primeira_parte' => $linha['primeira_parte'] ?? '',
           'resposta'       => $linha['resposta'],
           'segunda_parte'  => $linha['segunda_parte'] ?? '',
           'erradas'        => json_decode($linha['conjunto'] ?? '[]', true) ?: [],
       ];
   }
   $stmt->close();

// Randomiza a ordem das perguntas a cada carregamento da página
shuffle($perguntas);

$total_perguntas = count($perguntas);

/* Serializar para o JavaScript de forma segura */
$perguntas_json = json_encode($perguntas, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP);
?>
<!DOCTYPE html>
<html lang="en">
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
    <link rel="stylesheet" href="css/jogo-ingles.css">
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

            <!-- Botão Voltar -->
            <a href="index.php"
               class="cartao-dinamico flex items-center gap-2 bg-papel-claro borda-tinta-escura rounded-2xl px-4 py-2 shadow-sombra-cartum-branca font-bingo font-bold text-sm text-tinta-escura"
               style="text-decoration: none;">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                    <path d="M19 12H5M12 5l-7 7 7 7"/>
                </svg>
                BACK
            </a>

            <!-- Placar -->
            <div id="placar-jogo"
                 class="flex items-center gap-3 bg-papel-claro borda-tinta-escura rounded-2xl px-4 py-2 shadow-sombra-cartum-branca">
                <span class="text-xl">⭐</span>
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
                    Choose the correct word to fill the gap ✏️
                </p>
            </div>

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
                    <span id="lacuna-resposta" class="lacuna">_____</span>
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
                <p class="text-6xl mb-4">📭</p>
                <p class="font-bingo font-bold text-xl text-gray-600">
                    No questions found.<br>
                    Add questions to the database first!
                </p>
                <a href="index.php"
                   class="botao-acao mt-6 bg-azul-vibrante text-white inline-flex"
                   style="text-decoration:none;">
                    ← Go Back
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
                COMPLETE THE SENTENCE
            </div>
        </footer>

    </div><!-- /livro-conteiner-principal -->

    <!-- OVERLAY DE RESULTADO FINAL -->
    <div id="overlay-resultado" role="dialog" aria-modal="true" aria-labelledby="titulo-resultado">
        <div id="caixa-resultado">

            <div class="emoji-resultado" id="emoji-resultado">🎉</div>

            <h2 id="titulo-resultado"
                class="font-gooddog text-4xl font-bold text-tinta-escura tracking-wider mt-3">
                GREAT JOB!
            </h2>
            <p id="texto-resultado"
               class="font-bingo text-lg font-bold text-gray-600 mt-2 mb-5">
                You finished all questions!
            </p>

            <div class="inline-block bg-amarelo-vibrante borda-tinta-escura rounded-2xl px-6 py-3 shadow-sombra-cartum-branca mb-6">
                <span class="font-bingo font-bold text-2xl text-tinta-escura">
                    Score: <span id="pontos-finais">0</span> / <?= $total_perguntas ?>
                </span>
            </div>

            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <button onclick="reiniciarJogo()"
                        class="botao-acao bg-verde-vibrante text-white">
                    🔄 Play Again
                </button>
                <a href="index.php"
                   class="botao-acao bg-papel-claro text-tinta-escura"
                   style="text-decoration: none;">
                    🏠 Go Back
                </a>
            </div>

        </div>
    </div>

    <!-- ============================================================
         JAVASCRIPT DO JOGO
         ============================================================ -->
    <script>
    /* ----------------------------------------------------------
       Dados injetados pelo PHP (ordem já randomizada no servidor)
    ---------------------------------------------------------- */
    const PERGUNTAS = <?= $perguntas_json ?>;
    const TOTAL     = PERGUNTAS.length;

    /* ----------------------------------------------------------
       Estado global do jogo
    ---------------------------------------------------------- */
    let indicePerguntaAtual = 0;
    let pontuacao           = 0;
    let respondeuAtual      = false;

    /* ----------------------------------------------------------
       Referências ao DOM
    ---------------------------------------------------------- */
    const elPrimeiraParte    = document.getElementById('texto-primeira-parte');
    const elSegundaParte     = document.getElementById('texto-segunda-parte');
    const elLacuna           = document.getElementById('lacuna-resposta');
    const elGradeOpcoes      = document.getElementById('grade-opcoes');
    const elFeedback         = document.getElementById('area-feedback');
    const elPlacar           = document.getElementById('texto-placar');
    const elLabelProgresso   = document.getElementById('label-progresso');
    const elLabelPorcentagem = document.getElementById('label-porcentagem');
    const elBarraInterna     = document.getElementById('barra-progresso-interna');
    const elOverlay          = document.getElementById('overlay-resultado');
    const elPontosFinais     = document.getElementById('pontos-finais');
    const elEmojiResultado   = document.getElementById('emoji-resultado');
    const elTituloResultado  = document.getElementById('titulo-resultado');
    const elTextoResultado   = document.getElementById('texto-resultado');

    /* ----------------------------------------------------------
       Carrega a pergunta atual
    ---------------------------------------------------------- */
    function carregarPergunta() {
        if (indicePerguntaAtual >= TOTAL) {
            mostrarResultadoFinal();
            return;
        }

        respondeuAtual = false;
        const p = PERGUNTAS[indicePerguntaAtual];

        elPrimeiraParte.textContent = p.primeira_parte;
        elSegundaParte.textContent  = p.segunda_parte;

        elLacuna.textContent = '_____';
        elLacuna.className   = 'lacuna';

        elFeedback.textContent = '';
        elFeedback.style.color = '';

        const pct = Math.round((indicePerguntaAtual / TOTAL) * 100);
        elLabelProgresso.textContent   = `Question ${indicePerguntaAtual + 1} of ${TOTAL}`;
        elLabelPorcentagem.textContent = `${pct}%`;
        elBarraInterna.style.width     = `${pct}%`;

        gerarOpcoes(p);
    }

    /* ----------------------------------------------------------
       Gera os cards de opção embaralhados (Fisher-Yates)
    ---------------------------------------------------------- */
    function gerarOpcoes(pergunta) {
        const opcoes = [pergunta.resposta, ...pergunta.erradas];

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

    /* ----------------------------------------------------------
       Verifica se a opção escolhida é a correta
    ---------------------------------------------------------- */
    function verificarResposta(cardClicado, escolha, correta) {
        if (respondeuAtual) return;
        respondeuAtual = true;

        const acertou = escolha.toLowerCase().trim() === correta.toLowerCase().trim();

        if (acertou) {
            cardClicado.classList.add('opcao-correta');
            elLacuna.textContent = escolha;
            elLacuna.classList.add('lacuna-correta');

            elFeedback.textContent = '✅ Correct! Well done! 🌟';
            elFeedback.style.color = '#054D20';

            pontuacao++;
            elPlacar.textContent = `${pontuacao} / ${TOTAL}`;

            desabilitarOutrasOpcoes(cardClicado);
            setTimeout(avancarPergunta, 1400);

        } else {
            cardClicado.classList.add('opcao-errada');
            elLacuna.textContent = escolha;
            elLacuna.classList.add('lacuna-errada');

            elFeedback.textContent = '❌ Try again! You can do it! 💪';
            elFeedback.style.color = '#b91c1c';

            setTimeout(() => {
                respondeuAtual = false;
                cardClicado.classList.remove('opcao-errada');
                elLacuna.textContent = '_____';
                elLacuna.className   = 'lacuna';
                elFeedback.textContent = '';
                elFeedback.style.color = '';
            }, 900);
        }
    }

    /* ----------------------------------------------------------
       Desabilita todos os cards exceto o correto
    ---------------------------------------------------------- */
    function desabilitarOutrasOpcoes(cardCorreto) {
        elGradeOpcoes.querySelectorAll('.cartao-opcao').forEach(card => {
            if (card !== cardCorreto) card.classList.add('opcao-desabilitada');
        });
    }

    /* ----------------------------------------------------------
       Avança para a próxima pergunta
    ---------------------------------------------------------- */
    function avancarPergunta() {
        indicePerguntaAtual++;
        carregarPergunta();
    }

    /* ----------------------------------------------------------
       Exibe o overlay de resultado final
    ---------------------------------------------------------- */
    function mostrarResultadoFinal() {
        elBarraInterna.style.width     = '100%';
        elLabelPorcentagem.textContent = '100%';
        elLabelProgresso.textContent   = `Finished! ${TOTAL} of ${TOTAL}`;

        const taxa = pontuacao / TOTAL;
        if (taxa === 1) {
            elEmojiResultado.textContent  = '🏆';
            elTituloResultado.textContent = 'PERFECT SCORE!';
            elTextoResultado.textContent  = 'Amazing! You got everything right!';
        } else if (taxa >= 0.7) {
            elEmojiResultado.textContent  = '🎉';
            elTituloResultado.textContent = 'GREAT JOB!';
            elTextoResultado.textContent  = 'You did really well! Keep it up!';
        } else if (taxa >= 0.4) {
            elEmojiResultado.textContent  = '😊';
            elTituloResultado.textContent = 'GOOD TRY!';
            elTextoResultado.textContent  = 'You\'re learning! Play again to improve!';
        } else {
            elEmojiResultado.textContent  = '💪';
            elTituloResultado.textContent = 'KEEP GOING!';
            elTextoResultado.textContent  = 'Practice makes perfect! Try again!';
        }

        elPontosFinais.textContent = pontuacao;
        elOverlay.classList.add('ativo');
    }

    /* ----------------------------------------------------------
       Reinicia o jogo — uma nova ordem é definida pelo PHP
       ao recarregar a página com "Play Again"
    ---------------------------------------------------------- */
    function reiniciarJogo() {
        // Recarrega a página para que o PHP gere uma nova ordem aleatória
        window.location.reload();
    }

    /* ----------------------------------------------------------
       Inicialização
    ---------------------------------------------------------- */
    if (TOTAL > 0) carregarPergunta();
    </script>

</body>
</html>
