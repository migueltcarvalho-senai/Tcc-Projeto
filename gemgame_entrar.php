<?php
/* ============================================================
   gemgame_entrar.php — Tela de Entrada de Alunos no Quiz (PIN + Nickname)
   Sketchbook Educacional | Ensino Fundamental 1
   ============================================================ */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$nomePadrao = $_SESSION['user'] ?? '';
$pinInicial = trim($_GET['pin'] ?? '');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Entrar no Quiz — GemGame</title>
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
                        'ciano-vibrante':   '#06B6D4',
                        'ciano-escuro':     '#164E63',
                    },
                    fontFamily: {
                        'gooddog': ['Gooddog', 'cursive', 'sans-serif'],
                        'bingo':   ['Bingo', 'cursive', 'sans-serif'],
                    },
                    boxShadow: {
                        'sombra-cartum-branca': '0px 6px 0px #0F172A',
                        'sombra-cartum-verde':  '0px 6px 0px #054D20',
                    }
                }
            }
        }
    </script>

    <link rel="stylesheet" href="css/index_php.css">
    <link rel="stylesheet" href="css/acessibilidade.css">
</head>
<body class="font-bingo min-h-screen text-tinta-escura flex flex-col justify-center items-center p-3 sm:p-6 relative">

    <!-- ELEMENTOS OSCILANTES DE FUNDO -->
    <div id="elementos-fundo-oscilantes" aria-hidden="true">
        <div class="elemento-fundo-oscilante elemento-fundo-1"></div>
        <div class="elemento-fundo-oscilante elemento-fundo-2"></div>
        <div class="elemento-fundo-oscilante elemento-fundo-3"></div>
        <div class="elemento-fundo-oscilante elemento-fundo-4"></div>
    </div>

    <!-- CARTÃO DO LIVRO CENTRAL -->
    <div class="w-full max-w-md caderno-pagina-branca rounded-3xl shadow-2xl p-6 sm:p-8 relative z-10 flex flex-col items-center">

        <!-- ESPIRAL NO TOPO -->
        <div class="w-full flex justify-between items-center -mt-10 sm:-mt-12 mb-4 px-2">
            <div class="espiral-caderno"></div>
            <div class="espiral-caderno"></div>
            <div class="espiral-caderno"></div>
            <div class="espiral-caderno"></div>
            <div class="espiral-caderno"></div>
            <div class="espiral-caderno"></div>
        </div>

        <!-- LOGO & TÍTULO -->
        <div class="text-center mb-6">
            <span class="text-5xl inline-block mb-1">💎</span>
            <h1 class="font-gooddog text-4xl sm:text-5xl font-bold text-tinta-escura tracking-wider">
                GEMGAME AO VIVO
            </h1>
            <p class="font-bingo font-bold text-slate-600 text-sm mt-1">
                Insira o PIN mostrado no projetor para entrar na partida!
            </p>
        </div>

        <!-- MENSAGEM DE ERRO (SE HOUVER) -->
        <div id="msg-erro" class="w-full mb-4 bg-red-50 border-2 border-red-400 rounded-2xl px-4 py-2 font-bingo font-bold text-red-600 text-xs hidden"></div>

        <!-- FORMULÁRIO -->
        <form id="form-entrar" class="w-full flex flex-col gap-4">

            <!-- CAMPO PIN -->
            <div class="flex flex-col gap-1">
                <label for="campo-pin" class="font-bingo font-black text-xs text-slate-500 uppercase">
                    PIN DO JOGO
                </label>
                <input type="text"
                       id="campo-pin"
                       name="pin"
                       value="<?= htmlspecialchars($pinInicial) ?>"
                       inputmode="numeric"
                       pattern="[0-9]*"
                       maxlength="6"
                       placeholder="Ex: 123456"
                       autocomplete="off"
                       required
                       class="w-full text-center font-gooddog text-4xl sm:text-5xl font-black tracking-widest text-tinta-escura bg-yellow-50 border-4 border-tinta-escura rounded-2xl p-3 focus:bg-white focus:border-ciano-vibrante focus:outline-none shadow-sombra-cartum-branca">
            </div>

            <!-- CAMPO NICKNAME -->
            <div class="flex flex-col gap-1 mt-2">
                <label for="campo-nickname" class="font-bingo font-black text-xs text-slate-500 uppercase">
                    SEU NOME / APELIDO
                </label>
                <input type="text"
                       id="campo-nickname"
                       name="nickname"
                       value="<?= htmlspecialchars($nomePadrao) ?>"
                       maxlength="30"
                       placeholder="Digite seu nome aqui"
                       autocomplete="nickname"
                       required
                       class="w-full text-center font-bingo text-xl font-black text-tinta-escura bg-white border-3 border-tinta-escura rounded-2xl p-3.5 focus:border-ciano-vibrante focus:outline-none shadow-sombra-cartum-branca">
            </div>

            <!-- BOTÃO ENTRAR -->
            <button type="submit" id="btn-conectar" class="cartao-dinamico w-full bg-verde-vibrante hover:bg-green-500 text-white font-gooddog text-3xl font-bold py-4 rounded-2xl shadow-sombra-cartum-verde border-3 border-verde-escuro mt-4 cursor-pointer flex items-center justify-center gap-2">
                <span>CONECTAR ➔</span>
            </button>

            <!-- VOLTAR AO HUB -->
            <a href="hub.php" class="text-center font-bingo font-bold text-xs text-slate-500 hover:text-slate-800 transition-colors mt-2 no-underline">
                Voltar ao Menu Principal
            </a>

        </form>

    </div>

    <script>
    (function() {
        const form = document.getElementById('form-entrar');
        const pinInput = document.getElementById('campo-pin');
        const nickInput = document.getElementById('campo-nickname');
        const btnConectar = document.getElementById('btn-conectar');
        const msgErro = document.getElementById('msg-erro');

        // Formata apenas números no PIN
        pinInput.addEventListener('input', () => {
            pinInput.value = pinInput.value.replace(/\D/g, '').slice(0, 6);
        });

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            msgErro.classList.add('hidden');
            btnConectar.disabled = true;
            btnConectar.innerHTML = '<span>CONECTANDO...</span>';

            const fd = new FormData();
            fd.append('pin', pinInput.value.trim());
            fd.append('nickname', nickInput.value.trim());

            try {
                const res = await fetch('gemgame_api.php?acao=entrar_sala', {
                    method: 'POST',
                    body: fd
                });
                const data = await res.json();

                if (data.sucesso) {
                    window.location.href = `gemgame_play.php?sala=${data.sala_id}&jogador=${data.jogador_id}`;
                } else {
                    msgErro.textContent = data.erro || 'Não foi possível entrar na sala.';
                    msgErro.classList.remove('hidden');
                    btnConectar.disabled = false;
                    btnConectar.innerHTML = '<span>CONECTAR ➔</span>';
                }
            } catch (err) {
                msgErro.textContent = 'Erro ao conectar ao servidor. Verifique sua conexão.';
                msgErro.classList.remove('hidden');
                btnConectar.disabled = false;
                btnConectar.innerHTML = '<span>CONECTAR ➔</span>';
            }
        });
    })();
    </script>
</body>
</html>
