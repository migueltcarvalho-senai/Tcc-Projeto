<?php
/* ============================================================
   gemgame_criar.php — GemGame: Formulário de Criação de Quiz
   Sketchbook Educacional | Ensino Fundamental 1
   ============================================================ */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/proteger.php';

$nomeUsuario   = $_SESSION['user'] ?? '';
$pontosUsuario = (int) ($_SESSION['pontuacao'] ?? 0);

// Mensagem de erro da query string (vinda de gemgame_salvar.php em caso de falha)
$erro = $_GET['erro'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GemGame — Criar Quiz</title>
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
                        'vermelho-vivo':    '#EF4444',
                        'cinza-borda':      '#CBD5E1',
                    },
                    fontFamily: {
                        'gooddog': ['Gooddog', 'cursive', 'sans-serif'],
                        'bingo':   ['Bingo', 'cursive', 'sans-serif'],
                    },
                    boxShadow: {
                        'sombra-cartum-ciano':  '0px 6px 0px #164E63',
                        'sombra-cartum-verde':  '0px 6px 0px #054D20',
                        'sombra-cartum-branca': '0px 5px 0px #0F172A',
                        'sombra-cartum-amarela':'0px 6px 0px #806B00',
                        'sombra-cartum-red':    '0px 5px 0px #991B1B',
                    }
                }
            }
        }
    </script>

    <link rel="stylesheet" href="css/index_php.css">
    <link rel="stylesheet" href="css/acessibilidade.css">

    <style>
        /* ── Painel de pergunta ── */
        .painel-pergunta {
            background: #F8FAFC;
            border: 3px solid #0F172A;
            border-radius: 20px;
            box-shadow: 0px 5px 0px #0F172A;
            transition: box-shadow 0.2s;
        }
        .painel-pergunta:focus-within {
            border-color: #06B6D4;
            box-shadow: 0px 5px 0px #164E63;
        }

        /* ── Número da pergunta (círculo) ── */
        .numero-pergunta {
            background: #0F172A;
            color: #FFD600;
            font-family: 'Gooddog', cursive;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            font-weight: bold;
            flex-shrink: 0;
        }

        /* ── Linha de resposta ── */
        .linha-resposta {
            display: flex;
            align-items: center;
            gap: 10px;
            background: white;
            border: 2px solid #CBD5E1;
            border-radius: 14px;
            padding: 8px 12px;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .linha-resposta:focus-within {
            border-color: #06B6D4;
        }

        /* Radio customizado — anel ciano quando "correta" */
        .radio-correta {
            width: 20px;
            height: 20px;
            accent-color: #00C853;
            cursor: pointer;
            flex-shrink: 0;
        }
        .linha-resposta.marcada-correta {
            border-color: #00C853;
            background: #F0FFF4;
            box-shadow: 0 0 0 2px #00C85330;
        }

        /* ── Preview de imagem ── */
        #previews-imagem img {
            max-height: 100px;
            border-radius: 10px;
            border: 2px solid #CBD5E1;
            object-fit: cover;
        }

        /* ── Campo de texto global ── */
        .campo-texto {
            font-family: 'Bingo', cursive;
            font-weight: bold;
            font-size: 0.9rem;
            border: 2px solid #CBD5E1;
            border-radius: 12px;
            padding: 10px 14px;
            width: 100%;
            outline: none;
            background: white;
            color: #0F172A;
            transition: border-color 0.2s;
        }
        .campo-texto:focus {
            border-color: #06B6D4;
        }

        /* ── Botão de remover (X vermelho) ── */
        .btn-remover {
            background: #FEE2E2;
            border: 2px solid #EF4444;
            color: #EF4444;
            border-radius: 10px;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 1rem;
            font-weight: bold;
            flex-shrink: 0;
            transition: background 0.15s;
        }
        .btn-remover:hover { background: #EF4444; color: white; }

        /* ── Animação de entrada dos painéis ── */
        @keyframes deslizar-baixo {
            from { opacity: 0; transform: translateY(-12px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .animar-entrada {
            animation: deslizar-baixo 0.3s ease forwards;
        }

        /* ── Upload de imagem estilizado ── */
        .area-upload {
            border: 2px dashed #CBD5E1;
            border-radius: 14px;
            padding: 12px 16px;
            text-align: center;
            cursor: pointer;
            transition: border-color 0.2s, background 0.2s;
        }
        .area-upload:hover {
            border-color: #06B6D4;
            background: #ECFEFF;
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
                <a href="gemgame_listar.php"
                   class="cartao-dinamico flex items-center gap-2 bg-papel-claro borda-tinta-escura rounded-xl px-4 py-2 shadow-sombra-cartum-branca font-bingo font-bold text-sm text-tinta-escura hover:bg-yellow-100 transition-colors no-underline">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                        <path d="M19 12H5M12 5l-7 7 7 7"/>
                    </svg>
                    QUIZZES
                </a>
                <span class="bg-papel-claro borda-tinta-escura rounded-xl px-3.5 py-1.5 font-bingo font-bold text-xs sm:text-sm text-tinta-escura shadow-sombra-cartum-branca">
                    ALUNO: <?= htmlspecialchars($nomeUsuario) ?>
                </span>
            </div>
            <span class="bg-ciano-vibrante borda-tinta-escura rounded-xl px-4 py-1.5 font-gooddog font-bold text-base text-tinta-escura shadow-sombra-cartum-ciano">
                💎 CRIAR QUIZ
            </span>
        </header>

        <!-- MENSAGEM DE ERRO (se houver) -->
        <?php if ($erro): ?>
        <div class="w-full max-w-3xl mx-auto mb-5 bg-red-50 border-2 border-red-400 rounded-2xl px-5 py-3 font-bingo font-bold text-red-700 text-sm flex items-center gap-3">
            <span class="text-xl">⚠️</span>
            <?= htmlspecialchars($erro) ?>
        </div>
        <?php endif; ?>

        <!-- FORMULÁRIO PRINCIPAL -->
        <main class="w-full flex-1 flex flex-col items-center">
        <form id="form-criar-quiz"
              action="gemgame_salvar.php"
              method="POST"
              enctype="multipart/form-data"
              class="w-full max-w-3xl flex flex-col gap-6">

            <!-- ── SEÇÃO 1: DADOS DO QUIZ ── -->
            <section class="painel-pergunta p-6 flex flex-col gap-4">
                <div class="flex items-center gap-3 mb-1">
                    <span class="font-gooddog text-2xl text-tinta-escura tracking-wider">📋 DADOS DO QUIZ</span>
                </div>

                <!-- Título -->
                <div class="flex flex-col gap-1">
                    <label for="titulo" class="font-bingo font-bold text-xs text-gray-500 uppercase tracking-wider">
                        Título do Quiz <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           id="titulo"
                           name="titulo"
                           class="campo-texto"
                           placeholder="Ex.: Capitais do Mundo"
                           maxlength="255"
                           required>
                </div>

                <!-- Descrição -->
                <div class="flex flex-col gap-1">
                    <label for="descricao" class="font-bingo font-bold text-xs text-gray-500 uppercase tracking-wider">
                        Descrição (opcional)
                    </label>
                    <textarea id="descricao"
                              name="descricao"
                              class="campo-texto"
                              rows="2"
                              maxlength="500"
                              placeholder="Uma frase curta sobre o tema do quiz..."></textarea>
                </div>
            </section>

            <!-- ── SEÇÃO 2: PERGUNTAS ── -->
            <div class="flex items-center justify-between">
                <span class="font-gooddog text-2xl text-tinta-escura tracking-wider">❓ PERGUNTAS</span>
                <span id="contador-perguntas" class="font-bingo font-bold text-xs text-gray-500 bg-gray-100 px-3 py-1 rounded-xl border border-gray-200">
                    1 pergunta
                </span>
            </div>

            <!-- Container dinâmico de perguntas -->
            <div id="container-perguntas" class="flex flex-col gap-5">
                <!-- A primeira pergunta é inserida pelo JS ao carregar -->
            </div>

            <!-- Botão adicionar pergunta -->
            <button type="button"
                    id="btn-adicionar-pergunta"
                    class="cartao-dinamico w-full flex items-center justify-center gap-3 bg-papel-claro font-bingo font-extrabold text-sm text-tinta-escura py-3.5 rounded-2xl shadow-sombra-cartum-branca hover:bg-ciano-vibrante hover:text-tinta-escura transition-colors"
                    style="border: 3px dashed #0F172A;">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                ADICIONAR PERGUNTA
            </button>

            <!-- ── BOTÃO SALVAR ── -->
            <div class="flex flex-col sm:flex-row gap-3 pb-4">
                <a href="gemgame_listar.php"
                   class="flex-1 text-center bg-papel-claro font-bingo font-extrabold text-sm text-tinta-escura py-3.5 rounded-2xl shadow-sombra-cartum-branca no-underline hover:bg-gray-200 transition-colors"
                   style="border: 3px solid #0F172A;">
                    CANCELAR
                </a>
                <button type="submit"
                        id="btn-salvar"
                        class="cartao-dinamico flex-1 sm:flex-[2] flex items-center justify-center gap-3 bg-verde-vibrante font-bingo font-extrabold text-sm text-white py-3.5 rounded-2xl shadow-sombra-cartum-verde"
                        style="border: 3px solid #054D20;">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    SALVAR QUIZ
                </button>
            </div>

        </form>
        </main>
    </div>

    <script src="js/acessibilidade.js"></script>
    <script>
    /* ============================================================
       gemgame_criar.php — JavaScript do Formulário Dinâmico
       ============================================================ */
    (function () {
        'use strict';

        let indicePergunta = 0; // índice global crescente para IDs únicos

        const containerPerguntas  = document.getElementById('container-perguntas');
        const btnAdicionarPergunta = document.getElementById('btn-adicionar-pergunta');
        const contadorPerguntas   = document.getElementById('contador-perguntas');

        /* ── Atualiza o contador visual de perguntas ── */
        function atualizarContador() {
            const total = containerPerguntas.querySelectorAll('.painel-pergunta').length;
            contadorPerguntas.textContent = total + (total === 1 ? ' pergunta' : ' perguntas');

            // Renumera visualmente os círculos
            containerPerguntas.querySelectorAll('.numero-pergunta').forEach((el, i) => {
                el.textContent = i + 1;
            });

            // Atualiza os índices de ordem dos campos ocultos
            containerPerguntas.querySelectorAll('.campo-ordem').forEach((el, i) => {
                el.value = i;
            });

            // Esconde o botão de remover se só há 1 pergunta
            const btnsRemover = containerPerguntas.querySelectorAll('.btn-remover-pergunta');
            btnsRemover.forEach(btn => {
                btn.style.display = total <= 1 ? 'none' : 'flex';
            });
        }

        /* ── Cria HTML de uma linha de resposta ── */
        function criarLinhaResposta(idxPerg, idxResp) {
            const div = document.createElement('div');
            div.className = 'linha-resposta';
            div.dataset.idxResp = idxResp;
            div.innerHTML = `
                <input type="radio"
                       class="radio-correta"
                       name="perguntas[${idxPerg}][correta]"
                       value="${idxResp}"
                       title="Marcar como resposta correta"
                       required>
                <input type="text"
                       name="perguntas[${idxPerg}][respostas][${idxResp}]"
                       class="campo-texto"
                       style="border:none; padding: 4px 0; background:transparent; flex:1;"
                       placeholder="Resposta ${idxResp + 1}…"
                       maxlength="500"
                       required>
                <button type="button" class="btn-remover btn-remover-resposta" title="Remover resposta" aria-label="Remover resposta">✕</button>
            `;

            // Destaque visual quando o radio é marcado
            const radio = div.querySelector('input[type="radio"]');
            radio.addEventListener('change', () => {
                const painel = div.closest('.painel-pergunta');
                painel.querySelectorAll('.linha-resposta').forEach(l => l.classList.remove('marcada-correta'));
                div.classList.add('marcada-correta');
            });

            // Remover resposta (mínimo 4)
            div.querySelector('.btn-remover-resposta').addEventListener('click', () => {
                const painel       = div.closest('.painel-pergunta');
                const linhas       = painel.querySelectorAll('.linha-resposta');
                if (linhas.length <= 4) {
                    mostrarErroInline(painel, 'O mínimo é 4 respostas por pergunta.');
                    return;
                }
                div.remove();
                reindexarRespostas(painel, idxPerg);
            });

            return div;
        }

        /* ── Reindexa os campos de resposta após remoção ── */
        function reindexarRespostas(painel, idxPerg) {
            const linhas = painel.querySelectorAll('.linha-resposta');
            linhas.forEach((linha, i) => {
                linha.dataset.idxResp = i;
                const radio = linha.querySelector('input[type="radio"]');
                const texto = linha.querySelector('input[type="text"]');
                radio.name  = `perguntas[${idxPerg}][correta]`;
                radio.value = i;
                texto.name  = `perguntas[${idxPerg}][respostas][${i}]`;
                texto.placeholder = `Resposta ${i + 1}…`;
            });
        }

        /* ── Exibe aviso inline e some após 3s ── */
        function mostrarErroInline(painel, msg) {
            let aviso = painel.querySelector('.aviso-inline');
            if (!aviso) {
                aviso = document.createElement('p');
                aviso.className = 'aviso-inline font-bingo font-bold text-xs text-red-500 mt-1';
                painel.appendChild(aviso);
            }
            aviso.textContent = msg;
            clearTimeout(aviso._timer);
            aviso._timer = setTimeout(() => aviso.remove(), 3000);
        }

        /* ── Cria o painel completo de uma pergunta ── */
        function criarPainelPergunta() {
            const idx = indicePergunta++;
            const painel = document.createElement('div');
            painel.className = 'painel-pergunta animar-entrada p-5 flex flex-col gap-4';

            // ── Cabeçalho da pergunta ──
            const cabecalho = document.createElement('div');
            cabecalho.className = 'flex items-center gap-3';
            cabecalho.innerHTML = `
                <div class="numero-pergunta">?</div>
                <span class="font-gooddog text-lg text-tinta-escura tracking-wide flex-1">PERGUNTA</span>
                <button type="button" class="btn-remover btn-remover-pergunta" title="Remover pergunta" aria-label="Remover pergunta" style="width:36px;height:36px;">✕</button>
            `;
            painel.appendChild(cabecalho);

            // ── Campo oculto de ordem ──
            const campoOrdem = document.createElement('input');
            campoOrdem.type  = 'hidden';
            campoOrdem.name  = `perguntas[${idx}][ordem]`;
            campoOrdem.value = idx;
            campoOrdem.className = 'campo-ordem';
            painel.appendChild(campoOrdem);

            // ── Enunciado ──
            const wrapEnunciado = document.createElement('div');
            wrapEnunciado.className = 'flex flex-col gap-1';
            wrapEnunciado.innerHTML = `
                <label class="font-bingo font-bold text-xs text-gray-500 uppercase tracking-wider">
                    Enunciado <span class="text-red-500">*</span>
                </label>
                <textarea name="perguntas[${idx}][enunciado]"
                          class="campo-texto"
                          rows="2"
                          maxlength="1000"
                          placeholder="Qual é a capital da França?"
                          required></textarea>
            `;
            painel.appendChild(wrapEnunciado);

            // ── Upload de imagem ──
            const wrapImagem = document.createElement('div');
            wrapImagem.className = 'flex flex-col gap-2';
            wrapImagem.innerHTML = `
                <label class="font-bingo font-bold text-xs text-gray-500 uppercase tracking-wider">
                    Imagem (opcional)
                </label>
                <label class="area-upload cursor-pointer" for="imagem_${idx}">
                    <span class="font-bingo font-bold text-xs text-gray-400">📷 Clique para selecionar uma imagem</span>
                    <input type="file"
                           id="imagem_${idx}"
                           name="perguntas[${idx}][imagem]"
                           accept="image/jpeg,image/png,image/gif,image/webp"
                           class="hidden">
                </label>
                <div class="preview-img flex gap-2 flex-wrap"></div>
            `;
            painel.appendChild(wrapImagem);

            // Preview dinâmico da imagem
            const inputFile = wrapImagem.querySelector('input[type="file"]');
            const previewDiv = wrapImagem.querySelector('.preview-img');
            const labelUpload = wrapImagem.querySelector('label.area-upload span');
            inputFile.addEventListener('change', () => {
                previewDiv.innerHTML = '';
                if (inputFile.files[0]) {
                    const url = URL.createObjectURL(inputFile.files[0]);
                    const img = document.createElement('img');
                    img.src = url;
                    img.className = 'max-h-24 rounded-xl border-2 border-gray-200 object-cover';
                    previewDiv.appendChild(img);
                    labelUpload.textContent = '📷 ' + inputFile.files[0].name;
                } else {
                    labelUpload.textContent = '📷 Clique para selecionar uma imagem';
                }
            });

            // ── Separador ──
            const sep = document.createElement('hr');
            sep.className = 'border-gray-200';
            painel.appendChild(sep);

            // ── Label respostas ──
            const labelRespostas = document.createElement('div');
            labelRespostas.className = 'flex items-center justify-between';
            labelRespostas.innerHTML = `
                <span class="font-bingo font-bold text-xs text-gray-500 uppercase tracking-wider">
                    Respostas <span class="text-red-500">*</span>
                    <span class="text-gray-400 normal-case">(marque a correta com ○)</span>
                </span>
                <span class="font-bingo font-bold text-xs text-gray-400">mín. 4</span>
            `;
            painel.appendChild(labelRespostas);

            // ── Container de respostas ──
            const containerRespostas = document.createElement('div');
            containerRespostas.className = 'flex flex-col gap-2';

            // Cria 4 respostas iniciais
            for (let r = 0; r < 4; r++) {
                containerRespostas.appendChild(criarLinhaResposta(idx, r));
            }
            painel.appendChild(containerRespostas);

            // ── Botão adicionar resposta ──
            const btnAddResp = document.createElement('button');
            btnAddResp.type = 'button';
            btnAddResp.className = 'self-start font-bingo font-bold text-xs text-ciano-escuro flex items-center gap-1.5 px-3 py-1.5 bg-ciano-claro rounded-xl border border-ciano-escuro hover:bg-ciano-vibrante hover:text-white transition-colors';
            btnAddResp.innerHTML = `<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3.5" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4"/></svg> MAIS RESPOSTA`;
            btnAddResp.addEventListener('click', () => {
                const totalRespostas = containerRespostas.querySelectorAll('.linha-resposta').length;
                containerRespostas.appendChild(criarLinhaResposta(idx, totalRespostas));
            });
            painel.appendChild(btnAddResp);

            // ── Remover pergunta ──
            cabecalho.querySelector('.btn-remover-pergunta').addEventListener('click', () => {
                if (containerPerguntas.querySelectorAll('.painel-pergunta').length <= 1) {
                    mostrarErroInline(painel, 'O quiz precisa ter pelo menos 1 pergunta.');
                    return;
                }
                painel.style.opacity = '0';
                painel.style.transform = 'translateY(-8px)';
                painel.style.transition = 'opacity 0.25s, transform 0.25s';
                setTimeout(() => { painel.remove(); atualizarContador(); }, 250);
            });

            return painel;
        }

        /* ── Adicionar pergunta ao clicar no botão ── */
        btnAdicionarPergunta.addEventListener('click', () => {
            containerPerguntas.appendChild(criarPainelPergunta());
            atualizarContador();
            // Scroll suave até a nova pergunta
            containerPerguntas.lastElementChild.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });

        /* ── Validação antes de enviar ── */
        document.getElementById('form-criar-quiz').addEventListener('submit', function (e) {
            const paineis = containerPerguntas.querySelectorAll('.painel-pergunta');
            let valido = true;
            let primeiroErro = null;

            paineis.forEach((painel, i) => {
                const enunciado = painel.querySelector('textarea');
                if (!enunciado.value.trim()) {
                    valido = false;
                    mostrarErroInline(painel, `Pergunta ${i + 1}: o enunciado não pode estar vazio.`);
                    if (!primeiroErro) primeiroErro = painel;
                }

                const radioMarcado = painel.querySelector('input[type="radio"]:checked');
                if (!radioMarcado) {
                    valido = false;
                    mostrarErroInline(painel, `Pergunta ${i + 1}: marque qual resposta é a correta.`);
                    if (!primeiroErro) primeiroErro = painel;
                }
            });

            if (!valido) {
                e.preventDefault();
                if (primeiroErro) {
                    primeiroErro.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                return;
            }

            // Feedback visual no botão
            const btn = document.getElementById('btn-salvar');
            btn.textContent = 'SALVANDO…';
            btn.disabled = true;
        });

        /* ── Inicializa com 1 pergunta ── */
        containerPerguntas.appendChild(criarPainelPergunta());
        atualizarContador();

    })();
    </script>
</body>
</html>
