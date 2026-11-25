<?php
/* ============================================================
   gemgame_listar.php — GemGame: Lista de Quizzes Disponíveis
   Sketchbook Educacional | Ensino Fundamental 1
   ============================================================ */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/proteger.php';

$nomeUsuario   = $_SESSION['user'] ?? '';
$userId        = (int) ($_SESSION['id'] ?? 0);
$pontosUsuario = (int) ($_SESSION['pontuacao'] ?? 0);

// Busca pontuação atualizada
if ($userId > 0) {
    $tbl_usuarios = db_table($conn, 'usuario');
    $stmt = $conn->prepare("SELECT pontuacao FROM {$tbl_usuarios} WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            $pontosUsuario = (int) $row['pontuacao'];
            $_SESSION['pontuacao'] = $pontosUsuario;
        }
        $stmt->close();
    }
}

// Busca todos os quizzes com contagem de perguntas e partidas jogadas pelo usuário
$tbl_quiz     = db_table($conn, 'gemgame_quiz');
$tbl_pergunta = db_table($conn, 'gemgame_pergunta');
$tbl_partida  = db_table($conn, 'gemgame_partida');

$sql = "
    SELECT
        q.id,
        q.titulo,
        q.descricao,
        q.criado_em,
        COUNT(DISTINCT p.id)  AS total_perguntas,
        COUNT(DISTINCT pt.id) AS vezes_jogado
    FROM {$tbl_quiz} q
    LEFT JOIN {$tbl_pergunta} p  ON p.quiz_id  = q.id
    LEFT JOIN {$tbl_partida}  pt ON pt.quiz_id = q.id AND pt.jogador_id = ?
    GROUP BY q.id, q.titulo, q.descricao, q.criado_em
    ORDER BY q.criado_em DESC
";

$quizzes = [];
$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $quizzes[] = $row;
    }
    $stmt->close();
}

$total_quizzes = count($quizzes);

// Mensagem de sucesso após criação de quiz
$quiz_criado_id = isset($_GET['criado']) ? (int) $_GET['criado'] : 0;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GemGame — Escolha um Quiz</title>
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
                        'ciano-claro':      '#ECFEFF',
                        'roxo-vibrante':    '#7C3AED',
                        'roxo-escuro':      '#3B0764',
                        'rosa-vibrante':    '#FF4081',
                        'laranja-vibrante': '#FF6D00',
                        'laranja-escuro':   '#803700',
                    },
                    fontFamily: {
                        'gooddog': ['Gooddog', 'cursive', 'sans-serif'],
                        'bingo':   ['Bingo', 'cursive', 'sans-serif'],
                        'fredoka': ['Fredoka', 'sans-serif'],
                    },
                    boxShadow: {
                        'sombra-cartum-ciano':  '0px 6px 0px #164E63',
                        'sombra-cartum-verde':  '0px 6px 0px #054D20',
                        'sombra-cartum-branca': '0px 5px 0px #0F172A',
                        'sombra-cartum-amarela':'0px 6px 0px #806B00',
                    }
                }
            }
        }
    </script>

    <link rel="stylesheet" href="css/index_php.css">
    <link rel="stylesheet" href="css/acessibilidade.css">

    <style>
        .cartao-quiz {
            transition: transform 0.28s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.25s ease;
            will-change: transform;
        }
        .cartao-quiz:hover {
            transform: translateY(-8px) scale(1.025);
            z-index: 20;
        }
        .cartao-quiz:active {
            transform: translateY(1px) scale(0.98);
        }

        .badge-jogado {
            background: linear-gradient(135deg, #00C853, #054D20);
        }

        /* Estado vazio */
        .estado-vazio {
            border: 3px dashed #94A3B8;
            border-radius: 24px;
        }

        /* Inclinação suave dos cards de quiz */
        .quiz-par   { transform: rotate(-1.5deg); }
        .quiz-impar { transform: rotate(1.5deg);  }
        .quiz-par:hover,
        .quiz-impar:hover {
            transform: translateY(-8px) rotate(0deg) scale(1.025) !important;
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

        <!-- CABEÇALHO -->
        <header class="w-full flex flex-wrap items-center justify-between gap-3 pb-3 mb-6">
            <div class="flex items-center gap-2.5 flex-wrap">
                <!-- Voltar ao Hub -->
                <a href="hub.php"
                   class="cartao-dinamico flex items-center gap-2 bg-papel-claro borda-tinta-escura rounded-xl px-4 py-2 shadow-sombra-cartum-branca font-bingo font-bold text-sm text-tinta-escura hover:bg-yellow-100 transition-colors no-underline">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                        <path d="M19 12H5M12 5l-7 7 7 7"/>
                    </svg>
                    MENU
                </a>
                <span class="bg-papel-claro borda-tinta-escura rounded-xl px-3.5 py-1.5 font-bingo font-bold text-xs sm:text-sm text-tinta-escura shadow-sombra-cartum-branca">
                    ALUNO: <?= htmlspecialchars($nomeUsuario) ?>
                </span>
                <span class="bg-amarelo-vibrante borda-tinta-escura rounded-xl px-3.5 py-1.5 font-bingo font-bold text-xs sm:text-sm text-tinta-escura shadow-sombra-cartum-branca flex items-center gap-1.5">
                    <span class="font-gooddog text-xl font-bold leading-none"><?= $pontosUsuario ?></span> PTS
                </span>
            </div>

            <!-- Botão Criar Quiz -->
            <a href="gemgame_criar.php"
               class="cartao-dinamico flex items-center gap-2 bg-ciano-vibrante text-tinta-escura font-bingo font-extrabold text-sm px-5 py-2.5 rounded-2xl shadow-sombra-cartum-ciano no-underline"
               style="border: 3px solid #164E63;">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                CRIAR QUIZ
            </a>
        </header>

        <!-- CONTEÚDO PRINCIPAL -->
        <main class="w-full flex-1 flex flex-col items-center">

            <!-- TÍTULO -->
            <div class="text-center max-w-3xl mb-6">
                <h1 class="font-gooddog text-5xl sm:text-6xl md:text-7xl font-bold text-tinta-escura tracking-wider leading-tight">
                    💎 GEMGAME AO VIVO
                </h1>
                <p class="font-bingo text-base sm:text-xl font-extrabold text-gray-600 tracking-wide mt-2">
                    Jogue em tempo real com sua turma no estilo Kahoot, ou crie seu próprio quiz!
                </p>
            </div>

            <!-- BANNER DE ENTRADA RÁPIDA COM PIN (PARA ALUNOS) -->
            <div class="w-full max-w-2xl bg-amarelo-vibrante borda-tinta-escura rounded-3xl p-5 shadow-sombra-cartum-branca mb-8 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-3 text-center sm:text-left">
                    <span class="text-4xl animate-bounce">📱</span>
                    <div>
                        <h3 class="font-gooddog text-2xl font-bold text-tinta-escura leading-none">TEM UM PIN DE JOGO?</h3>
                        <p class="font-bingo font-black text-xs text-slate-800 mt-1">Digite o PIN do projetor para entrar na partida ao vivo!</p>
                    </div>
                </div>
                <form action="gemgame_entrar.php" method="GET" class="flex items-center gap-2 w-full sm:w-auto">
                    <input type="text"
                           name="pin"
                           maxlength="6"
                           placeholder="PIN (6 dígitos)"
                           class="w-36 text-center font-gooddog text-2xl font-bold bg-white borda-tinta-escura rounded-xl py-1.5 focus:outline-none shadow-sm">
                    <button type="submit" class="cartao-dinamico bg-verde-vibrante hover:bg-green-500 text-white font-bingo font-black text-sm px-4 py-2.5 rounded-xl border-2 border-verde-escuro cursor-pointer">
                        ENTRAR ➔
                    </button>
                </form>
            </div>

            <!-- MENSAGEM DE SUCESSO -->
            <?php if ($quiz_criado_id > 0): ?>
            <div class="w-full max-w-3xl mb-5 bg-green-50 border-2 border-green-400 rounded-2xl px-5 py-3 font-bingo font-bold text-green-700 text-sm flex items-center gap-3">
                <span class="text-xl">✅</span>
                Quiz criado com sucesso! Ele já aparece na lista abaixo.
            </div>
            <?php endif; ?>

            <?php if ($total_quizzes > 0): ?>

            <!-- GRADE DE QUIZZES -->
            <div class="w-full max-w-6xl grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 px-2 pb-6">
                <?php foreach ($quizzes as $i => $quiz): ?>
                <?php
                    $inclinacao   = ($i % 2 === 0) ? 'quiz-par' : 'quiz-impar';
                    $jaJogou      = (int) $quiz['vezes_jogado'] > 0;
                    $totalPergs   = (int) $quiz['total_perguntas'];
                    $dataFormatada = date('d/m/Y', strtotime($quiz['criado_em']));
                ?>
                <div class="<?= $inclinacao ?> cartao-quiz bg-white rounded-3xl flex flex-col min-h-[220px] overflow-hidden relative"
                     style="border: 4px solid #164E63; box-shadow: 0px 6px 0px #164E63;">

                    <!-- FAIXA TOPO CIANO -->
                    <div class="bg-ciano-vibrante px-5 py-3 flex items-center justify-between"
                         style="border-bottom: 3px solid #164E63;">
                        <span class="font-bingo font-extrabold text-tinta-escura text-xs">
                            💎 QUIZ #<?= str_pad($quiz['id'], 3, '0', STR_PAD_LEFT) ?>
                        </span>
                        <div class="flex items-center gap-2">
                            <?php if ($jaJogou): ?>
                            <span class="badge-jogado text-white font-bingo font-extrabold text-xs px-2.5 py-0.5 rounded-xl">
                                ✅ JOGADO
                            </span>
                            <?php endif; ?>
                            <span class="bg-tinta-escura text-white font-bingo font-extrabold text-xs px-2.5 py-0.5 rounded-xl">
                                <?= $totalPergs ?> <?= $totalPergs === 1 ? 'PERGUNTA' : 'PERGUNTAS' ?>
                            </span>
                        </div>
                    </div>

                    <!-- CORPO DO CARD -->
                    <div class="flex flex-col flex-1 p-5 justify-between">
                        <div>
                            <h2 class="font-gooddog text-2xl font-bold text-tinta-escura leading-tight tracking-wide uppercase">
                                <?= htmlspecialchars($quiz['titulo']) ?>
                            </h2>
                            <?php if (!empty($quiz['descricao'])): ?>
                            <p class="font-bingo text-xs font-bold text-gray-500 mt-1 leading-snug line-clamp-2">
                                <?= htmlspecialchars($quiz['descricao']) ?>
                            </p>
                            <?php endif; ?>
                            <p class="font-bingo text-xs text-gray-400 mt-2">
                                Criado em <?= $dataFormatada ?>
                            </p>
                        </div>

                        <!-- BOTÕES DE JOGO (AO VIVO KAHOOT & SOLO) -->
                        <?php if ($totalPergs > 0): ?>
                        <div class="mt-4 flex flex-col gap-2">
                            <!-- Botão Transmitir (Host Kahoot) -->
                            <a href="gemgame_host.php?quiz=<?= $quiz['id'] ?>"
                               class="w-full bg-ciano-vibrante hover:bg-cyan-400 text-tinta-escura font-bingo font-black text-xs sm:text-sm py-2 px-3 rounded-2xl text-center flex items-center justify-center gap-2 no-underline shadow-sm transition-transform active:scale-95"
                               style="border: 3px solid #164E63;">
                                <span>📺 ABRIR SALA (HOST)</span>
                            </a>

                            <!-- Botão Praticar Solo -->
                            <a href="gemgame_jogar.php?quiz=<?= $quiz['id'] ?>"
                               class="w-full bg-slate-100 hover:bg-slate-200 text-slate-700 font-bingo font-bold text-xs py-1.5 px-3 rounded-xl text-center flex items-center justify-center gap-1.5 no-underline border border-slate-300 transition-colors">
                                <span>Praticar Solo</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </div>
                        <?php else: ?>
                        <div class="mt-4 w-full bg-gray-200 text-gray-500 font-bingo font-extrabold text-sm py-2.5 px-3 rounded-2xl text-center border-2 border-gray-300 cursor-not-allowed">
                            SEM PERGUNTAS
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <?php else: ?>

            <!-- ESTADO VAZIO -->
            <div class="estado-vazio w-full max-w-lg mx-auto flex flex-col items-center justify-center gap-5 py-16 px-8 text-center">
                <span class="text-7xl" aria-hidden="true">💎</span>
                <h2 class="font-gooddog text-3xl font-bold text-gray-400 tracking-wider">
                    NENHUM QUIZ AINDA!
                </h2>
                <p class="font-bingo font-bold text-gray-400 text-sm leading-relaxed">
                    Seja o primeiro a criar um quiz para a turma jogar!
                </p>
                <a href="gemgame_criar.php"
                   class="cartao-dinamico flex items-center gap-2 bg-ciano-vibrante text-tinta-escura font-bingo font-extrabold text-base px-8 py-3 rounded-2xl shadow-sombra-cartum-ciano no-underline"
                   style="border: 3px solid #164E63;">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    CRIAR MEU PRIMEIRO QUIZ
                </a>
            </div>

            <?php endif; ?>

        </main>
    </div>

    <script src="js/acessibilidade.js"></script>
</body>
</html>
