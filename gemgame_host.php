<?php
/* ============================================================
   gemgame_host.php — Tela de Projeção / Transmissão do Host (Professor)
   Modo Kahoot / Quizizz | Tempo Real com Polling HTTP
   Sketchbook Educacional | Ensino Fundamental 1
   ============================================================ */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/proteger.php';

$nomeUsuario = $_SESSION['user'] ?? '';
$host_id     = (int) ($_SESSION['id'] ?? 0);
$sala_id     = (int) ($_GET['sala'] ?? 0);
$quiz_id     = (int) ($_GET['quiz'] ?? 0);

$tbl_sala = db_table($conn, 'gemgame_sala');
$tbl_quiz = db_table($conn, 'gemgame_quiz');

// Se foi passado quiz_id mas não sala, cria a sala automaticamente
if ($sala_id <= 0 && $quiz_id > 0) {
    // Cria a sala no banco
    $pin = '';
    for ($i = 0; $i < 10; $i++) {
        $cand = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        $stmtC = $conn->prepare("SELECT id FROM {$tbl_sala} WHERE pin = ? AND estado != 'finalizado' LIMIT 1");
        $stmtC->bind_param('s', $cand);
        $stmtC->execute();
        $achou = $stmtC->get_result()->num_rows > 0;
        $stmtC->close();
        if (!$achou) { $pin = $cand; break; }
    }
    if ($pin !== '') {
        $stmtI = $conn->prepare("INSERT INTO {$tbl_sala} (pin, quiz_id, host_id, estado, duracao_pergunta) VALUES (?, ?, ?, 'aguardando', 25)");
        $stmtI->bind_param('sii', $pin, $quiz_id, $host_id);
        if ($stmtI->execute()) {
            $sala_id = (int) $conn->insert_id;
            $stmtI->close();
            header("Location: gemgame_host.php?sala={$sala_id}");
            exit;
        }
        $stmtI->close();
    }
}

// Carrega sala
$stmt = $conn->prepare("SELECT s.*, q.titulo AS quiz_titulo FROM {$tbl_sala} s INNER JOIN {$tbl_quiz} q ON q.id = s.quiz_id WHERE s.id = ? AND s.host_id = ?");
$stmt->bind_param('ii', $sala_id, $host_id);
$stmt->execute();
$sala = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$sala) {
    header('Location: gemgame_listar.php');
    exit;
}

$pinFormatado = substr($sala['pin'], 0, 3) . ' ' . substr($sala['pin'], 3, 3);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Host GemGame — PIN: <?= $sala['pin'] ?></title>
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
                        'vermelho-kahoot':  '#EF4444',
                        'azul-kahoot':      '#3B82F6',
                        'amarelo-kahoot':   '#EAB308',
                        'verde-kahoot':     '#10B981',
                    },
                    fontFamily: {
                        'gooddog': ['Gooddog', 'cursive', 'sans-serif'],
                        'bingo':   ['Bingo', 'cursive', 'sans-serif'],
                        'fredoka': ['Fredoka', 'sans-serif'],
                    },
                    boxShadow: {
                        'sombra-cartum-branca': '0px 5px 0px #0F172A',
                        'sombra-cartum-verde':  '0px 6px 0px #054D20',
                        'sombra-cartum-ciano':  '0px 6px 0px #164E63',
                        'sombra-cartum-red':    '0px 6px 0px #991B1B',
                    }
                }
            }
        }
    </script>

    <link rel="stylesheet" href="css/index_php.css">
    <link rel="stylesheet" href="css/acessibilidade.css">

    <style>
        /* Animações dos cartões de respostas no Host */
        .card-opcao-kahoot {
            border: 4px solid #0F172A;
            border-radius: 20px;
            box-shadow: 0px 6px 0px #0F172A;
            transition: transform 0.2s, opacity 0.3s;
        }
        .card-opcao-kahoot.esmaecido {
            opacity: 0.35;
            filter: grayscale(0.5);
        }
        .card-opcao-kahoot.correta-destaque {
            transform: scale(1.03);
            border-color: #00C853;
            box-shadow: 0px 8px 0px #054D20;
            z-index: 10;
        }

        /* Tag animada do jogador no lobby */
        .tag-jogador {
            animation: popIn 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        @keyframes popIn {
            0%   { transform: scale(0.4); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }

        /* Cronômetro circular */
        .circulo-timer {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: #0F172A;
            color: #FFD600;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Gooddog', cursive;
            font-size: 2.2rem;
            border: 4px solid #FFD600;
            box-shadow: 0px 4px 0px rgba(0,0,0,0.3);
            transition: background 0.3s, color 0.3s;
        }
        .circulo-timer.alerta {
            background: #EF4444;
            color: #FFFFFF;
            border-color: #991B1B;
            animation: pulsarTimer 0.6s infinite alternate;
        }
        @keyframes pulsarTimer {
            0% { transform: scale(1); }
            100% { transform: scale(1.1); }
        }

        /* Barras de votação no resultado */
        .barra-votos {
            height: 18px;
            border-radius: 9999px;
            background: rgba(15, 23, 42, 0.2);
            overflow: hidden;
            border: 2px solid #0F172A;
        }
        .barra-votos-preenchimento {
            height: 100%;
            transition: width 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        /* Pódio final */
        .degrau-podio {
            border: 4px solid #0F172A;
            border-radius: 20px 20px 0 0;
            box-shadow: 0px 6px 0px #0F172A;
        }
    </style>
</head>
<body class="font-bingo min-h-screen text-tinta-escura flex flex-col justify-between p-2 sm:p-4 md:p-6 relative select-none">

    <!-- ELEMENTOS OSCILANTES DE FUNDO -->
    <div id="elementos-fundo-oscilantes" aria-hidden="true">
        <div class="elemento-fundo-oscilante elemento-fundo-1"></div>
        <div class="elemento-fundo-oscilante elemento-fundo-2"></div>
        <div class="elemento-fundo-oscilante elemento-fundo-3"></div>
        <div class="elemento-fundo-oscilante elemento-fundo-4"></div>
    </div>

    <!-- LIVRO / CADERNO PRINCIPAL (PROJETOR FULLSCREEN) -->
    <div id="livro-conteiner-principal" class="w-full flex-1 flex flex-col justify-between caderno-pagina-branca rounded-3xl shadow-2xl p-4 sm:p-6 md:p-8 relative">

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

        <!-- CABEÇALHO DO HOST -->
        <header class="w-full flex flex-wrap items-center justify-between gap-3 pb-3 mb-4 border-b-2 border-slate-200">
            <div class="flex items-center gap-3">
                <a href="gemgame_listar.php" title="Sair da sala" class="cartao-dinamico flex items-center gap-2 bg-papel-claro borda-tinta-escura rounded-xl px-3.5 py-1.5 shadow-sombra-cartum-branca font-bingo font-bold text-xs sm:text-sm text-tinta-escura hover:bg-red-100 transition-colors no-underline">
                    <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>
                    <span>ENCERRAR SALA</span>
                </a>
                <span class="bg-papel-claro borda-tinta-escura rounded-xl px-3.5 py-1.5 font-bingo font-bold text-xs sm:text-sm text-tinta-escura shadow-sombra-cartum-branca">
                    QUIZ: <?= htmlspecialchars($sala['quiz_titulo']) ?>
                </span>
            </div>

            <!-- PIN DE DESTAQUE NO TOPO -->
            <div class="flex items-center gap-2">
                <span class="font-bingo font-black text-xs text-slate-500 uppercase">PIN DO JOGO:</span>
                <span class="bg-amarelo-vibrante borda-tinta-escura rounded-2xl px-5 py-1.5 font-gooddog text-3xl font-black text-tinta-escura tracking-widest shadow-sombra-cartum-branca">
                    <?= $pinFormatado ?>
                </span>
            </div>
        </header>

        <!-- ============================================================
             FASE 1: LOBBY DE ESPERA (AGUARDANDO JOGADORES)
             ============================================================ -->
        <section id="fase-lobby" class="w-full flex-1 flex flex-col items-center justify-between py-4">
            <div class="text-center space-y-3">
                <span class="bg-ciano-vibrante borda-tinta-escura rounded-full px-5 py-1 text-xs font-black text-tinta-escura tracking-wider uppercase shadow-sombra-cartum-branca">
                    MODO AO VIVO • SALA ABERTA
                </span>
                <h1 class="font-gooddog text-5xl sm:text-6xl md:text-7xl font-bold text-tinta-escura leading-tight">
                    ENTREM NO JOGO!
                </h1>
                <p class="font-bingo text-lg sm:text-xl font-extrabold text-slate-700">
                    Acesse pelo celular ou computador em <span class="bg-yellow-100 px-3 py-1 rounded-xl border border-yellow-300 font-black text-tinta-escura">gemgame_entrar.php</span> e digite o PIN:
                </p>
                <div class="inline-block bg-amarelo-vibrante borda-tinta-escura rounded-3xl px-8 py-3 shadow-sombra-cartum-branca mt-2">
                    <span class="font-gooddog text-6xl sm:text-7xl font-bold tracking-widest text-tinta-escura"><?= $pinFormatado ?></span>
                </div>
            </div>

            <!-- MURAL DE JOGADORES CONECTADOS -->
            <div class="w-full max-w-4xl bg-slate-50 borda-tinta-escura rounded-3xl p-6 shadow-sombra-cartum-branca my-6 flex flex-col gap-4">
                <div class="flex items-center justify-between border-b-2 border-slate-200 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="text-2xl">👥</span>
                        <span class="font-gooddog text-2xl font-bold text-tinta-escura">ALUNOS CONECTADOS:</span>
                    </div>
                    <span id="contador-alunos-lobby" class="bg-papel-branco borda-tinta-escura rounded-xl px-4 py-1 font-gooddog text-2xl text-verde-escuro font-bold">
                        0
                    </span>
                </div>
                <!-- Grade de Avatares/Nomes -->
                <div id="grade-alunos-lobby" class="flex flex-wrap gap-3 items-center justify-center min-h-[140px] max-h-[260px] overflow-y-auto p-2">
                    <p id="aviso-esperando-alunos" class="font-bingo font-bold text-slate-400 text-sm animate-pulse">
                        Aguardando os alunos entrarem com o PIN...
                    </p>
                </div>
            </div>

            <!-- BOTÃO INICIAR JOGO -->
            <div class="w-full max-w-md">
                <button type="button" id="btn-iniciar-jogo" class="cartao-dinamico w-full bg-verde-vibrante hover:bg-green-500 text-white font-gooddog text-3xl font-bold py-4 rounded-3xl shadow-sombra-cartum-verde border-4 border-verde-escuro cursor-pointer flex items-center justify-center gap-3">
                    <span>▶ INICIAR O JOGO</span>
                </button>
            </div>
        </section>

        <!-- ============================================================
             FASE 2: PERGUNTA ATIVA & CRONÔMETRO
             ============================================================ -->
        <section id="fase-pergunta" class="w-full flex-1 flex flex-col justify-between py-2 hidden">
            <!-- Barra superior da questão -->
            <div class="w-full flex items-center justify-between gap-4 mb-4">
                <span id="label-num-pergunta" class="bg-slate-900 text-white font-gooddog text-xl px-4 py-1.5 rounded-2xl shadow-sombra-cartum-branca">
                    QUESTÃO 1 DE 5
                </span>

                <!-- CRONÔMETRO -->
                <div id="cronometro-host" class="circulo-timer">25</div>

                <!-- CONTADOR DE RESPOSTAS -->
                <div class="flex items-center gap-3">
                    <div class="bg-papel-claro borda-tinta-escura rounded-2xl px-4 py-2 text-center shadow-sombra-cartum-branca">
                        <span class="block text-xs font-black text-slate-500 font-bingo">RESPOSTAS</span>
                        <span id="contador-respostas-host" class="font-gooddog text-2xl font-bold text-tinta-escura">0 / 0</span>
                    </div>
                    <button type="button" id="btn-encerrar-pergunta" class="cartao-dinamico bg-red-500 hover:bg-red-600 text-white font-bingo font-bold text-xs sm:text-sm px-4 py-3 rounded-2xl shadow-sombra-cartum-red border-2 border-red-900 cursor-pointer">
                        ⏹ ENCERRAR
                    </button>
                </div>
            </div>

            <!-- ENUNCIADO E IMAGEM -->
            <div class="w-full flex-1 flex flex-col items-center justify-center my-auto max-w-4xl mx-auto text-center px-4">
                <div id="bloco-img-pergunta" class="mb-4 hidden">
                    <img id="img-pergunta-host" src="" alt="Imagem" class="max-h-56 rounded-2xl border-4 border-tinta-escura shadow-sombra-cartum-branca object-contain bg-white">
                </div>
                <h2 id="enunciado-host" class="font-gooddog text-4xl sm:text-5xl md:text-6xl font-bold text-tinta-escura leading-tight tracking-wide">
                    Enunciado da pergunta aqui...
                </h2>
            </div>

            <!-- AS 4 OPÇÕES COM CORES E FORMAS KAHOOT -->
            <div id="grade-opcoes-host" class="w-full grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                <!-- Inserido dinamicamente via JS -->
            </div>
        </section>

        <!-- ============================================================
             FASE 3: RESULTADO DA PERGUNTA (GRÁFICO DE VOTOS)
             ============================================================ -->
        <section id="fase-resultado-pergunta" class="w-full flex-1 flex flex-col justify-between py-2 hidden">
            <div class="w-full flex items-center justify-between mb-4">
                <span class="bg-verde-vibrante text-white font-gooddog text-xl px-4 py-1.5 rounded-2xl shadow-sombra-cartum-verde border-2 border-verde-escuro">
                    TEMPO ESGOTADO!
                </span>
                <button type="button" id="btn-ir-ranking" class="cartao-dinamico bg-azul-vibrante hover:bg-blue-500 text-white font-gooddog text-2xl font-bold px-6 py-2.5 rounded-2xl shadow-sombra-cartum-ciano border-3 border-azul-escuro cursor-pointer flex items-center gap-2">
                    <span>PLACAR PARCIAL ➔</span>
                </button>
            </div>

            <!-- ENUNCIADO REPETIDO RESUMIDO -->
            <div class="text-center max-w-3xl mx-auto my-2">
                <h3 id="enunciado-resultado-host" class="font-gooddog text-2xl sm:text-3xl font-bold text-slate-700"></h3>
            </div>

            <!-- OPÇÕES COM BARRAS DE VOTOS -->
            <div id="grade-resultado-votos-host" class="w-full grid grid-cols-1 sm:grid-cols-2 gap-4 my-auto max-w-5xl mx-auto">
                <!-- Inserido dinamicamente via JS -->
            </div>
        </section>

        <!-- ============================================================
             FASE 4: LEADERBOARD / PLACAR DA RODADA
             ============================================================ -->
        <section id="fase-ranking" class="w-full flex-1 flex flex-col justify-between py-2 hidden">
            <div class="w-full flex items-center justify-between mb-4">
                <h2 class="font-gooddog text-4xl sm:text-5xl font-bold text-tinta-escura">
                    🏆 TOP SABIDOS DA RODADA
                </h2>
                <button type="button" id="btn-proxima-pergunta" class="cartao-dinamico bg-verde-vibrante hover:bg-green-500 text-white font-gooddog text-2xl font-bold px-6 py-2.5 rounded-2xl shadow-sombra-cartum-verde border-3 border-verde-escuro cursor-pointer flex items-center gap-2">
                    <span>PRÓXIMA ➔</span>
                </button>
            </div>

            <div id="lista-ranking-host" class="w-full max-w-3xl mx-auto flex flex-col gap-3 my-auto">
                <!-- Preenchido via JS com os 5 primeiros -->
            </div>
        </section>

        <!-- ============================================================
             FASE 5: PÓDIO FINAL
             ============================================================ -->
        <section id="fase-finalizado" class="w-full flex-1 flex flex-col items-center justify-between py-4 hidden">
            <div class="text-center">
                <span class="text-6xl">🎉</span>
                <h2 class="font-gooddog text-5xl sm:text-6xl md:text-7xl font-bold text-tinta-escura mt-2">
                    FIM DE JOGO! PÓDIO FINAL
                </h2>
                <p class="font-bingo font-bold text-slate-600 text-lg">Parabéns a todos os participantes!</p>
            </div>

            <!-- PÓDIO (2º, 1º, 3º) -->
            <div id="podio-final-host" class="w-full max-w-4xl flex items-end justify-center gap-4 my-8 h-80">
                <!-- Preenchido via JS -->
            </div>

            <a href="gemgame_listar.php" class="cartao-dinamico bg-papel-claro borda-tinta-escura rounded-2xl px-8 py-3.5 shadow-sombra-cartum-branca font-gooddog font-bold text-2xl text-tinta-escura no-underline hover:bg-yellow-100 transition-colors">
                VOLTAR AO MENU DE QUIZZES
            </a>
        </section>

    </div>

    <script>
    (function() {
        'use strict';

        const SALA_ID = <?= $sala_id ?>;
        let estadoAtual = '';
        let timerInterval = null;

        const fases = {
            lobby:     document.getElementById('fase-lobby'),
            pergunta:  document.getElementById('fase-pergunta'),
            resultado: document.getElementById('fase-resultado-pergunta'),
            ranking:   document.getElementById('fase-ranking'),
            final:     document.getElementById('fase-finalizado'),
        };

        function trocarFase(nomeFase) {
            Object.values(fases).forEach(f => f.classList.add('hidden'));
            if (fases[nomeFase]) fases[nomeFase].classList.remove('hidden');
        }

        /* POLLING DO HOST (A CADA 1s) */
        async function consultarStatusHost() {
            try {
                const res = await fetch(`gemgame_api.php?acao=host_status&sala_id=${SALA_ID}`);
                const data = await res.json();
                if (!data.sucesso) return;

                atualizarTelaHost(data);
            } catch (err) {
                console.error('Erro polling host:', err);
            }
        }

        function atualizarTelaHost(d) {
            // Se o estado mudou
            if (d.estado !== estadoAtual) {
                estadoAtual = d.estado;
                if (estadoAtual === 'aguardando') trocarFase('lobby');
                else if (estadoAtual === 'pergunta') trocarFase('pergunta');
                else if (estadoAtual === 'resultado_pergunta') trocarFase('resultado');
                else if (estadoAtual === 'ranking') trocarFase('ranking');
                else if (estadoAtual === 'finalizado') trocarFase('final');
            }

            // ATUALIZAÇÃO POR FASE
            if (estadoAtual === 'aguardando') {
                renderizarLobby(d);
            } else if (estadoAtual === 'pergunta') {
                renderizarPergunta(d);
            } else if (estadoAtual === 'resultado_pergunta') {
                renderizarResultadoPergunta(d);
            } else if (estadoAtual === 'ranking') {
                renderizarRanking(d);
            } else if (estadoAtual === 'finalizado') {
                renderizarPodioFinal(d);
            }
        }

        /* 1. LOBBY */
        function renderizarLobby(d) {
            document.getElementById('contador-alunos-lobby').textContent = d.total_jogadores;
            const grade = document.getElementById('grade-alunos-lobby');
            const aviso = document.getElementById('aviso-esperando-alunos');

            if (d.total_jogadores > 0) {
                if (aviso) aviso.remove();
                grade.innerHTML = '';
                d.jogadores.forEach(j => {
                    const tag = document.createElement('span');
                    tag.className = 'tag-jogador bg-papel-branco borda-tinta-escura rounded-2xl px-4 py-2 font-bingo font-black text-base text-tinta-escura shadow-sombra-cartum-branca flex items-center gap-2';
                    tag.innerHTML = `<span>👤</span> ${escapeHtml(j.nickname)}`;
                    grade.appendChild(tag);
                });
            }
        }

        /* 2. PERGUNTA ATIVA */
        function renderizarPergunta(d) {
            if (!d.pergunta_atual) return;

            document.getElementById('label-num-pergunta').textContent = `QUESTÃO ${(d.pergunta_indice + 1)} DE ${d.total_perguntas}`;
            document.getElementById('enunciado-host').textContent = d.pergunta_atual.enunciado;
            document.getElementById('contador-respostas-host').textContent = `${d.respostas_enviadas} / ${d.total_jogadores}`;

            // Cronômetro
            const timerEl = document.getElementById('cronometro-host');
            timerEl.textContent = d.tempo_restante;
            if (d.tempo_restante <= 5) {
                timerEl.classList.add('alerta');
            } else {
                timerEl.classList.remove('alerta');
            }

            // Imagem
            const imgBox = document.getElementById('bloco-img-pergunta');
            const imgEl = document.getElementById('img-pergunta-host');
            if (d.pergunta_atual.imagem) {
                imgEl.src = d.pergunta_atual.imagem;
                imgBox.classList.remove('hidden');
            } else {
                imgBox.classList.add('hidden');
            }

            // Opções estilo Kahoot (Vermelho ▲, Azul ◆, Amarelo ●, Verde ■)
            const grade = document.getElementById('grade-opcoes-host');
            grade.innerHTML = '';
            d.pergunta_atual.opcoes.forEach(op => {
                const card = document.createElement('div');
                card.className = 'card-opcao-kahoot flex items-center gap-4 p-5 text-white';
                card.style.backgroundColor = op.hex;
                card.innerHTML = `
                    <span class="w-12 h-12 rounded-2xl bg-white/25 flex items-center justify-center font-gooddog text-3xl font-black shrink-0">
                        ${op.simbolo}
                    </span>
                    <span class="font-bingo font-black text-xl sm:text-2xl leading-snug flex-1 drop-shadow">
                        ${escapeHtml(op.texto)}
                    </span>
                `;
                grade.appendChild(card);
            });
        }

        /* 3. RESULTADO DA PERGUNTA (GRÁFICO) */
        function renderizarResultadoPergunta(d) {
            if (!d.pergunta_atual) return;
            document.getElementById('enunciado-resultado-host').textContent = d.pergunta_atual.enunciado;

            const totalVotos = Math.max(1, d.respostas_enviadas);
            const grade = document.getElementById('grade-resultado-votos-host');
            grade.innerHTML = '';

            d.pergunta_atual.opcoes.forEach(op => {
                const pct = Math.round((op.votos / totalVotos) * 100);
                const card = document.createElement('div');
                const ehCorreta = op.correta;

                card.className = `card-opcao-kahoot p-5 text-white flex flex-col justify-between ${ehCorreta ? 'correta-destaque' : 'esmaecido'}`;
                card.style.backgroundColor = op.hex;

                card.innerHTML = `
                    <div class="flex items-center justify-between gap-3 mb-3">
                        <div class="flex items-center gap-3">
                            <span class="w-10 h-10 rounded-xl bg-white/25 flex items-center justify-center font-gooddog text-2xl font-black">
                                ${op.simbolo}
                            </span>
                            <span class="font-bingo font-black text-xl">${escapeHtml(op.texto)}</span>
                        </div>
                        ${ehCorreta ? '<span class="bg-white text-green-700 font-bingo font-black text-xs px-3 py-1 rounded-xl border-2 border-green-800">✔ CORRETA</span>' : ''}
                    </div>
                    <div>
                        <div class="barra-votos mb-1">
                            <div class="barra-votos-preenchimento bg-white" style="width: ${pct}%;"></div>
                        </div>
                        <div class="flex justify-between font-bingo font-bold text-xs text-white/90">
                            <span>${op.votos} voto(s)</span>
                            <span>${pct}%</span>
                        </div>
                    </div>
                `;
                grade.appendChild(card);
            });
        }

        /* 4. RANKING / PLACAR PARCIAL */
        function renderizarRanking(d) {
            const container = document.getElementById('lista-ranking-host');
            container.innerHTML = '';
            const top5 = d.jogadores.slice(0, 5);

            top5.forEach((j, i) => {
                const linha = document.createElement('div');
                const cores = ['#FFD600', '#94A3B8', '#D97706', '#06B6D4', '#00C853'];
                const cor = cores[i] || '#06B6D4';

                linha.className = 'w-full bg-papel-branco borda-tinta-escura rounded-2xl p-4 shadow-sombra-cartum-branca flex items-center justify-between';
                linha.innerHTML = `
                    <div class="flex items-center gap-4">
                        <span class="w-10 h-10 rounded-xl font-gooddog text-2xl font-bold flex items-center justify-center border-2 border-tinta-escura" style="background-color: ${cor}">
                            ${i + 1}º
                        </span>
                        <span class="font-bingo font-black text-xl text-tinta-escura">${escapeHtml(j.nickname)}</span>
                    </div>
                    <span class="font-gooddog text-3xl font-bold text-verde-escuro">${j.pontuacao} pts</span>
                `;
                container.appendChild(linha);
            });

            // Se for última questão, muda texto do botão
            const btnProx = document.getElementById('btn-proxima-pergunta');
            if ((d.pergunta_indice + 1) >= d.total_perguntas) {
                btnProx.innerHTML = '<span>🏆 VER PÓDIO FINAL</span>';
            } else {
                btnProx.innerHTML = '<span>PRÓXIMA ➔</span>';
            }
        }

        /* 5. PÓDIO FINAL */
        function renderizarPodioFinal(d) {
            const podioEl = document.getElementById('podio-final-host');
            podioEl.innerHTML = '';
            const j1 = d.jogadores[0] || null;
            const j2 = d.jogadores[1] || null;
            const j3 = d.jogadores[2] || null;

            // 2º Lugar
            if (j2) {
                podioEl.innerHTML += `
                    <div class="flex flex-col items-center flex-1 max-w-[200px]">
                        <span class="font-bingo font-black text-lg text-tinta-escura mb-2">${escapeHtml(j2.nickname)}</span>
                        <span class="font-gooddog text-xl text-slate-700">${j2.pontuacao} pts</span>
                        <div class="degrau-podio bg-slate-300 w-full h-44 flex flex-col items-center justify-center">
                            <span class="text-4xl">🥈</span>
                            <span class="font-gooddog text-3xl font-bold text-tinta-escura">2º</span>
                        </div>
                    </div>
                `;
            }
            // 1º Lugar
            if (j1) {
                podioEl.innerHTML += `
                    <div class="flex flex-col items-center flex-1 max-w-[220px]">
                        <span class="text-3xl animate-bounce">👑</span>
                        <span class="font-bingo font-black text-xl text-tinta-escura mb-1">${escapeHtml(j1.nickname)}</span>
                        <span class="font-gooddog text-2xl text-amber-700 font-bold">${j1.pontuacao} pts</span>
                        <div class="degrau-podio bg-amarelo-vibrante w-full h-64 flex flex-col items-center justify-center">
                            <span class="text-5xl">🥇</span>
                            <span class="font-gooddog text-4xl font-bold text-tinta-escura">1º</span>
                        </div>
                    </div>
                `;
            }
            // 3º Lugar
            if (j3) {
                podioEl.innerHTML += `
                    <div class="flex flex-col items-center flex-1 max-w-[200px]">
                        <span class="font-bingo font-black text-lg text-tinta-escura mb-2">${escapeHtml(j3.nickname)}</span>
                        <span class="font-gooddog text-xl text-amber-900">${j3.pontuacao} pts</span>
                        <div class="degrau-podio bg-amber-600 w-full h-32 flex flex-col items-center justify-center text-white">
                            <span class="text-4xl">🥉</span>
                            <span class="font-gooddog text-3xl font-bold">3º</span>
                        </div>
                    </div>
                `;
            }
        }

        /* ENVIO DE COMANDOS DO HOST */
        async function enviarComandoHost(cmd) {
            const fd = new FormData();
            fd.append('sala_id', SALA_ID);
            fd.append('comando', cmd);

            try {
                const res = await fetch('gemgame_api.php?acao=host_comando', {
                    method: 'POST',
                    body: fd
                });
                const d = await res.json();
                if (d.sucesso) {
                    consultarStatusHost();
                } else {
                    alert(d.erro || 'Erro ao executar comando.');
                }
            } catch (err) {
                console.error(err);
            }
        }

        // EVENTOS DOS BOTÕES
        document.getElementById('btn-iniciar-jogo').addEventListener('click', () => enviarComandoHost('iniciar_jogo'));
        document.getElementById('btn-encerrar-pergunta').addEventListener('click', () => enviarComandoHost('encerrar_pergunta'));
        document.getElementById('btn-ir-ranking').addEventListener('click', () => enviarComandoHost('ver_ranking'));
        document.getElementById('btn-proxima-pergunta').addEventListener('click', () => enviarComandoHost('proxima_pergunta'));

        function escapeHtml(s) {
            const div = document.createElement('div');
            div.textContent = s || '';
            return div.innerHTML;
        }

        // Inicia Polling a cada 1000ms
        consultarStatusHost();
        setInterval(consultarStatusHost, 1000);

    })();
    </script>
</body>
</html>
