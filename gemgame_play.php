<?php
/* ============================================================
   gemgame_play.php — Tela do Aluno (Controle Remoto Kahoot)
   Aparecem SOMENTE os 4 botões coloridos com formas geométricas
   Sketchbook Educacional | Ensino Fundamental 1
   ============================================================ */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$sala_id    = (int) ($_GET['sala'] ?? $_SESSION['gemgame_sala_id'] ?? 0);
$jogador_id = (int) ($_GET['jogador'] ?? $_SESSION['gemgame_jogador_id'] ?? 0);

if ($sala_id <= 0 || $jogador_id <= 0) {
    header('Location: gemgame_entrar.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Jogando GemGame</title>
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
                        'papelao-fundo': '#c6a175',
                        'tinta-escura':  '#0F172A',
                    },
                    fontFamily: {
                        'gooddog': ['Gooddog', 'cursive', 'sans-serif'],
                        'bingo':   ['Bingo', 'cursive', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <link rel="stylesheet" href="css/index_php.css">

    <style>
        /* Desabilita seleção e zoom no celular */
        * {
            -webkit-tap-highlight-color: transparent;
            user-select: none;
        }

        /* Botões gigantes de resposta Kahoot */
        .botao-kahoot {
            border: 4px solid #0F172A;
            border-radius: 24px;
            box-shadow: 0px 8px 0px #0F172A;
            transition: transform 0.1s, filter 0.15s, box-shadow 0.1s;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .botao-kahoot:active {
            transform: translateY(6px);
            box-shadow: 0px 2px 0px #0F172A;
        }

        /* Cores oficiais */
        .btn-vermelho { background-color: #EF4444; }
        .btn-azul     { background-color: #3B82F6; }
        .btn-amarelo  { background-color: #EAB308; }
        .btn-verde    { background-color: #10B981; }
        .btn-roxo     { background-color: #8B5CF6; }
        .btn-laranja  { background-color: #F97316; }

        .circulo-timer-player {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: #0F172A;
            color: #FFD600;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Gooddog', cursive;
            font-size: 1.6rem;
            border: 3px solid #FFD600;
        }
    </style>
</head>
<body class="font-bingo min-h-screen bg-[#c6a175] flex flex-col justify-between p-3 sm:p-5 relative select-none">

    <!-- BARRA SUPERIOR DO ALUNO -->
    <header class="w-full bg-white/95 border-3 border-tinta-escura rounded-2xl px-4 py-2.5 flex items-center justify-between shadow-md z-20">
        <div class="flex items-center gap-2">
            <span class="text-xl">👤</span>
            <span id="label-nickname" class="font-bingo font-black text-sm text-tinta-escura">Aluno</span>
        </div>

        <!-- CRONÔMETRO REDUZIDO -->
        <div id="box-timer-player" class="flex items-center gap-2 hidden">
            <span class="font-bingo font-black text-xs text-slate-500">TEMPO</span>
            <div id="timer-player" class="circulo-timer-player">25</div>
        </div>

        <!-- PLACAR DO ALUNO -->
        <div class="flex items-center gap-1.5 bg-yellow-100 border-2 border-yellow-400 rounded-xl px-3 py-1">
            <span class="font-gooddog text-xl font-bold text-tinta-escura leading-none" id="label-pontos">0</span>
            <span class="text-xs font-black text-slate-600">PTS</span>
        </div>
    </header>

    <!-- ============================================================
         1. FASE LOBBY: AGUARDANDO INÍCIO
         ============================================================ -->
    <main id="fase-lobby-player" class="flex-1 flex flex-col items-center justify-center text-center p-4 my-auto">
        <div class="w-full max-w-sm bg-white border-4 border-tinta-escura rounded-3xl p-8 shadow-2xl flex flex-col items-center gap-4">
            <span class="text-6xl animate-bounce">🎮</span>
            <h1 class="font-gooddog text-4xl font-bold text-tinta-escura">
                VOCÊ ESTÁ NO JOGO!
            </h1>
            <p class="font-bingo font-extrabold text-slate-600 text-sm leading-relaxed">
                Olhe para a tela do professor para acompanhar a partida. O jogo começará em instantes!
            </p>
            <div class="bg-cyan-50 border-2 border-cyan-300 rounded-2xl p-3 w-full mt-2">
                <span class="block text-xs font-black text-cyan-800 uppercase font-bingo">SUA MISSÃO:</span>
                <span class="text-xs text-slate-700 font-bold">Quando a pergunta aparecer no projetor, toque no botão da cor certa aqui!</span>
            </div>
        </div>
    </main>

    <!-- ============================================================
         2. FASE PERGUNTA: OS 4 BOTÕES GIGANTES (ESTILO KAHOOT)
         ============================================================ -->
    <main id="fase-pergunta-player" class="flex-1 w-full flex flex-col justify-between py-2 hidden">
        <!-- ÁREA DOS 4 BOTÕES -->
        <div id="grade-botoes-player" class="flex-1 grid grid-cols-2 gap-3 sm:gap-5 min-h-[360px] my-2">
            <!-- Gerado dinamicamente com as cores e formas -->
        </div>
    </main>

    <!-- ============================================================
         3. FASE RESPOSTA ENVIADA: AGUARDANDO FIM DO TEMPO
         ============================================================ -->
    <main id="fase-aguardando-player" class="flex-1 flex flex-col items-center justify-center text-center p-4 my-auto hidden">
        <div class="w-full max-w-sm bg-white border-4 border-tinta-escura rounded-3xl p-8 shadow-2xl flex flex-col items-center gap-4">
            <span class="text-6xl animate-pulse">⏳</span>
            <h2 class="font-gooddog text-4xl font-bold text-tinta-escura">
                RESPOSTA ENVIADA!
            </h2>
            <p class="font-bingo font-extrabold text-slate-600 text-sm">
                Aguardando o tempo esgotar ou os outros alunos responderem...
            </p>
        </div>
    </main>

    <!-- ============================================================
         4. FASE RESULTADO: ACERTOU OU ERROU?
         ============================================================ -->
    <main id="fase-resultado-player" class="flex-1 flex flex-col items-center justify-center text-center p-4 my-auto hidden">
        <div id="card-feedback-player" class="w-full max-w-sm border-4 border-tinta-escura rounded-3xl p-8 shadow-2xl flex flex-col items-center gap-4 text-white">
            <span id="icone-feedback-player" class="text-7xl">🎉</span>
            <h2 id="titulo-feedback-player" class="font-gooddog text-5xl font-black">
                CORRETO!
            </h2>
            <div id="box-pontos-ganhos" class="bg-white/20 border-2 border-white/50 rounded-2xl px-6 py-2">
                <span id="pontos-ganhos-player" class="font-gooddog text-3xl font-bold">+950 PTS</span>
            </div>
            <p id="msg-feedback-player" class="font-bingo font-bold text-sm">
                Mandou muito bem! Fique atento ao placar!
            </p>
        </div>
    </main>

    <!-- ============================================================
         5. FASE RANKING: POSIÇÃO DO ALUNO
         ============================================================ -->
    <main id="fase-ranking-player" class="flex-1 flex flex-col items-center justify-center text-center p-4 my-auto hidden">
        <div class="w-full max-w-sm bg-white border-4 border-tinta-escura rounded-3xl p-8 shadow-2xl flex flex-col items-center gap-4">
            <span class="text-6xl">🏆</span>
            <h2 class="font-gooddog text-3xl font-bold text-slate-500">
                SUA POSIÇÃO:
            </h2>
            <div class="bg-amarelo-vibrante border-4 border-tinta-escura rounded-3xl px-8 py-3 shadow-md">
                <span id="posicao-player" class="font-gooddog text-6xl font-black text-tinta-escura">1º</span>
            </div>
            <p class="font-bingo font-bold text-slate-700 text-sm">
                Total acumulado: <strong id="total-pontos-ranking" class="text-verde-escuro">0 pts</strong>
            </p>
            <span class="text-xs text-slate-400 font-bingo font-bold animate-pulse">Aguarde a próxima questão no projetor...</span>
        </div>
    </main>

    <!-- ============================================================
         6. FASE FINAL: PÓDIO FINAL
         ============================================================ -->
    <main id="fase-final-player" class="flex-1 flex flex-col items-center justify-center text-center p-4 my-auto hidden">
        <div class="w-full max-w-sm bg-white border-4 border-tinta-escura rounded-3xl p-8 shadow-2xl flex flex-col items-center gap-4">
            <span class="text-7xl">🥇</span>
            <h2 class="font-gooddog text-4xl font-bold text-tinta-escura">
                PARTIDA CONCLUÍDA!
            </h2>
            <div class="bg-cyan-50 border-3 border-cyan-400 rounded-2xl p-4 w-full">
                <span class="block font-bingo font-bold text-xs text-cyan-800 uppercase">POSIÇÃO FINAL:</span>
                <span id="posicao-final-player" class="font-gooddog text-5xl font-black text-tinta-escura">1º LUGAR</span>
                <span id="pontos-finais-player" class="block font-gooddog text-2xl text-verde-escuro mt-1">4500 PTS</span>
            </div>
            <a href="hub.php" class="cartao-dinamico w-full bg-verde-vibrante text-white font-gooddog text-2xl font-bold py-3.5 rounded-2xl border-3 border-verde-escuro no-underline mt-2">
                VOLTAR AO HUB
            </a>
        </div>
    </main>

    <script>
    (function() {
        'use strict';

        const SALA_ID    = <?= $sala_id ?>;
        const JOGADOR_ID = <?= $jogador_id ?>;

        let estadoAtual = '';
        let jaRespondeuNesta = false;
        let perguntaIndiceAtual = -1;

        const fases = {
            lobby:      document.getElementById('fase-lobby-player'),
            pergunta:   document.getElementById('fase-pergunta-player'),
            aguardando: document.getElementById('fase-aguardando-player'),
            resultado:  document.getElementById('fase-resultado-player'),
            ranking:    document.getElementById('fase-ranking-player'),
            final:      document.getElementById('fase-final-player'),
        };

        function trocarFase(nome) {
            Object.values(fases).forEach(f => f.classList.add('hidden'));
            if (fases[nome]) fases[nome].classList.remove('hidden');

            const boxTimer = document.getElementById('box-timer-player');
            if (nome === 'pergunta') {
                boxTimer.classList.remove('hidden');
            } else {
                boxTimer.classList.add('hidden');
            }
        }

        /* POLLING DO ALUNO (A CADA 1000ms) */
        async function consultarStatusPlayer() {
            try {
                const res = await fetch(`gemgame_api.php?acao=player_status&sala_id=${SALA_ID}&jogador_id=${JOGADOR_ID}`);
                const data = await res.json();
                if (!data.sucesso) return;

                atualizarTelaPlayer(data);
            } catch (err) {
                console.error('Erro polling aluno:', err);
            }
        }

        function atualizarTelaPlayer(d) {
            // Atualiza dados fixos do topo
            document.getElementById('label-pontos').textContent = d.minha_pontuacao;
            document.getElementById('timer-player').textContent = d.tempo_restante;

            // Se mudou de pergunta, reseta estado local de resposta
            if (d.pergunta_indice !== perguntaIndiceAtual) {
                perguntaIndiceAtual = d.pergunta_indice;
                jaRespondeuNesta = false;
            }

            // GESTÃO DE ESTADOS
            if (d.estado === 'aguardando') {
                trocarFase('lobby');
            } else if (d.estado === 'pergunta') {
                if (d.ja_respondeu || jaRespondeuNesta) {
                    trocarFase('aguardando');
                } else {
                    trocarFase('pergunta');
                    renderizarBotoes(d.opcoes_botoes);
                }
            } else if (d.estado === 'resultado_pergunta') {
                trocarFase('resultado');
                renderizarFeedbackResultado(d);
            } else if (d.estado === 'ranking') {
                trocarFase('ranking');
                document.getElementById('posicao-player').textContent = `${d.minha_posicao}º`;
                document.getElementById('total-pontos-ranking').textContent = `${d.minha_pontuacao} pts`;
            } else if (d.estado === 'finalizado') {
                trocarFase('final');
                document.getElementById('posicao-final-player').textContent = `${d.minha_posicao}º LUGAR`;
                document.getElementById('pontos-finais-player').textContent = `${d.minha_pontuacao} PTS (${d.meus_acertos} acertos)`;
            }
        }

        /* RENDERIZA OS BOTÕES COLORIDOS KAHOOT (SEM TEXTO DE PERGUNTA) */
        function renderizarBotoes(opcoes) {
            const grade = document.getElementById('grade-botoes-player');
            if (grade.children.length === opcoes.length && grade.dataset.pergunta === String(perguntaIndiceAtual)) {
                return; // já renderizado
            }

            grade.innerHTML = '';
            grade.dataset.pergunta = String(perguntaIndiceAtual);

            opcoes.forEach(op => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = `botao-kahoot btn-${op.cor} text-white`;
                btn.innerHTML = `<span class="text-7xl font-gooddog drop-shadow-md">${op.simbolo}</span>`;
                btn.addEventListener('click', () => enviarResposta(op.id));
                grade.appendChild(btn);
            });
        }

        /* ENVIA A RESPOSTA ESCOLHIDA */
        async function enviarResposta(respostaId) {
            if (jaRespondeuNesta) return;
            jaRespondeuNesta = true;
            trocarFase('aguardando');

            const fd = new FormData();
            fd.append('sala_id', SALA_ID);
            fd.append('jogador_id', JOGADOR_ID);
            fd.append('resposta_id', respostaId);

            try {
                await fetch('gemgame_api.php?acao=player_responder', {
                    method: 'POST',
                    body: fd
                });
            } catch (err) {
                console.error(err);
            }
        }

        /* RENDERIZA FEEDBACK DE ACERTO/ERRO */
        function renderizarFeedbackResultado(d) {
            const card = document.getElementById('card-feedback-player');
            const icone = document.getElementById('icone-feedback-player');
            const titulo = document.getElementById('titulo-feedback-player');
            const boxPontos = document.getElementById('box-pontos-ganhos');
            const pontosTxt = document.getElementById('pontos-ganhos-player');
            const msg = document.getElementById('msg-feedback-player');

            if (d.minha_resposta && d.minha_resposta.correta) {
                card.className = 'w-full max-w-sm border-4 border-green-900 rounded-3xl p-8 shadow-2xl flex flex-col items-center gap-4 text-white bg-green-500';
                icone.textContent = '🎉';
                titulo.textContent = 'CORRETO!';
                boxPontos.classList.remove('hidden');
                pontosTxt.textContent = `+${d.minha_resposta.pontos_ganhos} PTS`;
                msg.textContent = 'Excelente! Você acertou!';
            } else {
                card.className = 'w-full max-w-sm border-4 border-red-900 rounded-3xl p-8 shadow-2xl flex flex-col items-center gap-4 text-white bg-red-500';
                icone.textContent = '❌';
                titulo.textContent = 'OPS!';
                boxPontos.classList.add('hidden');
                msg.textContent = 'Não foi dessa vez. Continue tentando!';
            }
        }

        consultarStatusPlayer();
        setInterval(consultarStatusPlayer, 1000);

    })();
    </script>
</body>
</html>
