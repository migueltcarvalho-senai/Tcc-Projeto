<?php
require_once __DIR__ . '/config/proteger.php';
$nomeUsuario = $_SESSION['user'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sketchbook Educacional</title>
    <!-- Google Fonts: Fredoka e Nunito -->
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
                        'papel-branco': '#FFFFFF',
                        'papel-claro': '#F8FAF6',
                        'papel-linhas': '#E2E8F0',
                        'papelao-fundo': '#c6a175',
                        'tinta-escura': '#0F172A',
                        'verde-vibrante': '#00C853',
                        'verde-escuro': '#054D20',
                        'laranja-vibrante': '#FF6D00',
                        'laranja-escuro': '#803700',
                        'azul-vibrante': '#00B0FF',
                        'azul-escuro': '#004B87',
                        'amarelo-vibrante': '#FFD600',
                        'amarelo-escuro': '#806B00',
                        'rosa-vibrante': '#FF4081',
                    },
                    fontFamily: {
                        'gooddog': ['Gooddog', 'cursive', 'sans-serif'],
                        'bingo': ['Bingo', 'cursive', 'sans-serif'],
                        'fredoka': ['Fredoka', 'sans-serif'],
                        'nunito': ['Nunito', 'sans-serif'],
                    },
                    boxShadow: {
                        'sombra-cartum-verde': '0px 6px 0px #054D20',
                        'sombra-cartum-laranja': '0px 6px 0px #803700',
                        'sombra-cartum-azul': '0px 6px 0px #004B87',
                        'sombra-cartum-amarela': '0px 6px 0px #806B00',
                        'sombra-cartum-branca': '0px 5px 0px #0F172A',
                    }
                }
            }
        }
    </script>
    <!-- Estilos customizados da página -->
    <link rel="stylesheet" href="css/index_php.css">
</head>
<body class="font-bingo min-h-screen text-tinta-escura flex flex-col justify-between p-2 sm:p-4 md:p-6 relative">

    <!-- ELEMENTOS OSCILANTES DE FUNDO (CORREM DE UM LADO AO OUTRO) -->
    <div id="elementos-fundo-oscilantes" aria-hidden="true">
        <div class="elemento-fundo-oscilante elemento-fundo-1"></div>
        <div class="elemento-fundo-oscilante elemento-fundo-2"></div>
        <div class="elemento-fundo-oscilante elemento-fundo-3"></div>
        <div class="elemento-fundo-oscilante elemento-fundo-4"></div>
    </div>

    <!-- ESTRUTURA GLOBAL DO LIVRO (PÁGINA INTEIRA NA HORIZONTAL - BRANCO VIBRANTE) -->
    <div id="livro-conteiner-principal" class="w-full flex-1 flex flex-col justify-between caderno-pagina-branca rounded-3xl shadow-2xl p-4 sm:p-6 md:p-8 relative">

        <!-- ESPIRAL DO CADERNO NO TOPO -->
        <div id="espiral-topo-caderno" class="w-full flex justify-between items-center -mt-8 sm:-mt-10 mb-4 z-20 px-2 sm:px-6">
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

        <!-- BARRA SUPERIOR DO LIVRO -->
        <header id="cabecalho-principal" class="w-full flex items-center justify-end pb-3 mb-4">
            <a href="logout.php" title="Encerrar sessão" class="cartao-dinamico bg-red-500 hover:bg-red-600 text-white borda-tinta-escura rounded-xl px-4 py-2 font-bingo font-bold text-sm shadow-sombra-cartum-branca transition-colors">
                SAIR
            </a>
        </header>

        <!-- SEÇÃO CENTRAL -->
        <main id="conteudo-principal" class="w-full my-auto flex flex-col items-center">
            
            <!-- TÍTULO DA ATIVIDADE -->
            <div id="secao-titulo-atividade" class="text-center max-w-4xl mb-8 space-y-2">
                <h2 class="font-gooddog text-5xl sm:text-6xl md:text-7xl lg:text-8xl font-bold text-tinta-escura leading-tight tracking-wider">
                    O QUE VAMOS APRENDER HOJE?
                </h2>
                <p class="font-bingo text-lg sm:text-xl md:text-2xl font-extrabold text-gray-700 tracking-wide">
                    Escolha um dos cards coloridos abaixo para começar!
                </p>
            </div>

            <!-- GRADE DE CARDS (3 CARDS: 2 JOGOS + RANKING) -->
            <div id="grade-atividades" class="w-full max-w-5xl grid grid-cols-1 md:grid-cols-3 gap-6 px-2 py-4">
                
                <!-- CARD 1: JOGO DE INGLÊS -->
                <a id="card-ingles" href="temas_ingles.php" class="cartao-dinamico cartao-zigzag-cima cartao-inclinado-esquerda bg-verde-vibrante text-white cartao-borda-verde cartao-arredondado p-6 shadow-sombra-cartum-verde flex flex-col justify-between min-h-[250px] cursor-pointer group no-underline">
                    <div class="flex items-center justify-between">
                        <span class="bg-papel-branco text-verde-escuro font-bingo text-xs font-extrabold px-3 py-1 border-2 border-verde-escuro rounded-xl">
                            JOGO 01
                        </span>
                        <div class="w-11 h-11 bg-white/25 border-2 border-verde-escuro rounded-xl flex items-center justify-center text-white font-bold text-sm tracking-wider">
                            EN
                        </div>
                    </div>
                    
                    <div class="my-4">
                        <h3 class="font-gooddog text-3xl sm:text-4xl font-bold leading-tight uppercase text-white tracking-wider">
                            COMPLETE THE SENTENCE
                        </h3>
                        <p class="font-bingo text-sm font-extrabold text-green-100 mt-1 tracking-wide">
                            Fill in the gap in English!
                        </p>
                    </div>

                    <div class="w-full bg-papel-branco text-verde-escuro font-bingo font-extrabold text-base py-3 px-4 border-2 border-verde-escuro rounded-2xl text-center flex items-center justify-center gap-2 group-hover:bg-amarelo-vibrante group-hover:text-tinta-escura transition-colors">
                        <span>JOGAR AGORA</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="3.5" viewBox="0 0 24 24">
                            <path d="M5 12h14M12 5l7 7-7 7"/>
                        </svg>
                    </div>
                </a>

                <!-- CARD 2: JOGO DOS BALÕES -->
                <a id="card-baloes" href="temas_baloes.php" style="text-decoration: none;" class="cartao-dinamico cartao-zigzag-baixo cartao-inclinado-direita bg-laranja-vibrante text-white cartao-borda-laranja cartao-arredondado p-6 shadow-sombra-cartum-laranja flex flex-col justify-between min-h-[250px] cursor-pointer group no-underline">
                    <div class="flex items-center justify-between">
                        <span class="bg-papel-branco text-laranja-escuro font-bingo text-xs font-extrabold px-3 py-1 border-2 border-laranja-escuro rounded-xl">
                            JOGO 02
                        </span>
                        <div class="w-11 h-11 bg-white/25 border-2 border-laranja-escuro rounded-xl flex items-center justify-center text-white">
                            <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24">
                                <path d="M12 2C8.69 2 6 4.69 6 8c0 4.25 4.5 8.5 5 9v3h-2v2h6v-2h-2v-3c.5-.5 5-4.75 5-9 0-3.31-2.69-6-6-6zm0 10c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4z"/>
                            </svg>
                        </div>
                    </div>
                    
                    <div class="my-4">
                        <h3 class="font-gooddog text-3xl sm:text-4xl font-bold leading-tight uppercase text-white tracking-wider">
                            ESTOURE OS BALÕES
                        </h3>
                        <p class="font-bingo text-sm font-extrabold text-orange-100 mt-1 tracking-wide">
                            Pop balloons to build sentences!
                        </p>
                    </div>

                    <div class="w-full bg-papel-branco text-laranja-escuro font-bingo font-extrabold text-base py-3 px-4 border-2 border-laranja-escuro rounded-2xl text-center flex items-center justify-center gap-2 group-hover:bg-amarelo-vibrante group-hover:text-tinta-escura transition-colors">
                        <span>JOGAR AGORA</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="3.5" viewBox="0 0 24 24">
                            <path d="M5 12h14M12 5l7 7-7 7"/>
                        </svg>
                    </div>
                </a>

                <!-- CARD 3: RANKING -->
                <div id="card-ranking" onclick="abrirModulo('Ranking')" class="cartao-dinamico cartao-zigzag-cima cartao-inclinado-leve-esquerda bg-azul-vibrante text-white cartao-borda-azul cartao-arredondado p-6 shadow-sombra-cartum-azul flex flex-col justify-between min-h-[250px] cursor-pointer group">
                    <div class="flex items-center justify-between">
                        <span class="bg-papel-branco text-azul-escuro font-bingo text-xs font-extrabold px-3 py-1 border-2 border-azul-escuro rounded-xl">
                            TOP SABIDOS
                        </span>
                        <div class="w-11 h-11 bg-white/25 border-2 border-azul-escuro rounded-xl flex items-center justify-center text-white">
                            <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24">
                                <path d="M19 5h-2V3H7v2H5c-1.1 0-2 .9-2 2v1c0 2.55 1.92 4.63 4.39 4.94.63 1.5 1.98 2.63 3.61 2.96V19H7v2h10v-2h-4v-3.1c1.63-.33 2.98-1.46 3.61-2.96C19.08 12.63 21 10.55 21 8V7c0-1.1-.9-2-2-2zM5 8V7h2v3.82C5.84 10.4 5 9.3 5 8zm14 0c0 1.3-.84 2.4-2 2.82V7h2v1z"/>
                            </svg>
                        </div>
                    </div>
                    
                    <div class="my-4">
                        <h3 class="font-gooddog text-3xl sm:text-4xl font-bold leading-tight uppercase text-white tracking-wider">
                            RANKING
                        </h3>
                        <p class="font-bingo text-sm font-extrabold text-blue-100 mt-1 tracking-wide">
                            Veja os melhores pontuadores!
                        </p>
                    </div>

                    <div class="w-full bg-papel-branco text-azul-escuro font-bingo font-extrabold text-base py-3 px-4 border-2 border-azul-escuro rounded-2xl text-center flex items-center justify-center gap-2 group-hover:bg-amarelo-vibrante group-hover:text-tinta-escura transition-colors">
                        <span>VER RANKING</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="3.5" viewBox="0 0 24 24">
                            <path d="M5 12h14M12 5l7 7-7 7"/>
                        </svg>
                    </div>
                </div>

            </div>

        </main>

    </div>

    <!-- JAVASCRIPT SIMPLES PARA FEEDBACK INTERATIVO -->
    <script>
        function abrirModulo(modulo) {
            alert('Atividade Selecionada: ' + modulo);
        }
    </script>
</body>
</html>
