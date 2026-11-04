<?php
/**
 * ranking.php — Página de Placar Geral e Ranking dos Alunos
 * Sketchbook Educacional - Ensino Fundamental 1
 *
 * Exibe todos os usuários cadastrados ordenados do maior para o menor número de acertos.
 * Calcula os erros dinamicamente via subtração no banco: (questoesRespondidas - acertos) AS erros.
 * Compatível 100% com Linux e Windows.
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/proteger.php';

$userId        = (int) ($_SESSION['id'] ?? 0);
$nomeUsuario   = $_SESSION['user'] ?? '';
$pontosUsuario = (int) ($_SESSION['pontuacao'] ?? 0);

$tbl_usuarios = db_table($conn, 'usuario');

// Seleção simples com a subtração de questões respondidas para determinar erros
// Ordenado do usuário com mais acertos para o com menos acertos
$sql = "
    SELECT 
        id, 
        nome, 
        pontuacao, 
        questoesRespondidas, 
        acertos, 
        (questoesRespondidas - acertos) AS erros 
    FROM {$tbl_usuarios} 
    ORDER BY acertos DESC, pontuacao DESC, id ASC
";

$res = $conn->query($sql);
$ranking = [];
$posicaoUsuario = 0;
$dadosUsuarioLogado = null;

if ($res) {
    $pos = 1;
    while ($row = $res->fetch_assoc()) {
        $row['posicao'] = $pos;
        $row['acertos'] = (int) ($row['acertos'] ?? 0);
        $row['questoesRespondidas'] = (int) ($row['questoesRespondidas'] ?? 0);
        $row['erros'] = max(0, (int) ($row['erros'] ?? 0));
        $row['pontuacao'] = (int) ($row['pontuacao'] ?? 0);

        if ((int) $row['id'] === $userId) {
            $posicaoUsuario = $pos;
            $dadosUsuarioLogado = $row;
            $_SESSION['pontuacao'] = $row['pontuacao'];
            $pontosUsuario = $row['pontuacao'];
        }

        $ranking[] = $row;
        $pos++;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title>Top Sabidos — Ranking dos Alunos</title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>📚</text></svg>">

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
                        'papel-branco':     '#FFFFFF',
                        'papel-claro':      '#F8FAF6',
                        'papel-linhas':     '#E2E8F0',
                        'papelao-fundo':    '#c6a175',
                        'tinta-escura':     '#0F172A',
                        'verde-vibrante':   '#00C853',
                        'verde-escuro':     '#054D20',
                        'laranja-vibrante': '#FF6D00',
                        'laranja-escuro':   '#803700',
                        'azul-vibrante':    '#00B0FF',
                        'azul-escuro':      '#004B87',
                        'amarelo-vibrante': '#FFD600',
                        'amarelo-escuro':   '#806B00',
                        'rosa-vibrante':    '#FF4081',
                        'ouro':             '#F59E0B',
                        'prata':            '#94A3B8',
                        'bronze':           '#D97706',
                    },
                    fontFamily: {
                        'gooddog': ['Gooddog', 'cursive', 'sans-serif'],
                        'bingo':   ['Bingo', 'cursive', 'sans-serif'],
                        'fredoka': ['Fredoka', 'sans-serif'],
                        'nunito':  ['Nunito', 'sans-serif'],
                    },
                    boxShadow: {
                        'sombra-cartum-verde':  '0px 6px 0px #054D20',
                        'sombra-cartum-laranja':'0px 6px 0px #803700',
                        'sombra-cartum-azul':   '0px 6px 0px #004B87',
                        'sombra-cartum-amarela':'0px 6px 0px #806B00',
                        'sombra-cartum-branca': '0px 5px 0px #0F172A',
                    }
                }
            }
        }
    </script>

    <!-- CSS customizado do Ranking -->
    <link rel="stylesheet" href="css/ranking.css">
    <!-- CSS de Acessibilidade & Modo Escuro -->
    <link rel="stylesheet" href="css/acessibilidade.css">
</head>
<body class="font-bingo min-h-screen text-tinta-escura flex flex-col justify-between p-2 sm:p-4 md:p-6 relative">

    <!-- ELEMENTOS OSCILANTES DE FUNDO -->
    <div id="elementos-fundo-oscilantes" aria-hidden="true">
        <div class="elemento-fundo-oscilante elemento-fundo-1"></div>
        <div class="elemento-fundo-oscilante elemento-fundo-2"></div>
        <div class="elemento-fundo-oscilante elemento-fundo-3"></div>
        <div class="elemento-fundo-oscilante elemento-fundo-4"></div>
    </div>

    <!-- LIVRO / CADERNO PRINCIPAL -->
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

        <!-- CABEÇALHO DO RANKING -->
        <header id="cabecalho-ranking" class="w-full flex flex-wrap items-center justify-between gap-3 pb-3 mb-4 border-b-2 border-slate-200">
            <!-- Botão Voltar -->
            <a href="index.php" id="btn-voltar" class="cartao-dinamico flex items-center gap-2 bg-papel-claro borda-tinta-escura rounded-2xl px-4 py-2 shadow-sombra-cartum-branca font-bingo font-bold text-sm text-tinta-escura hover:bg-yellow-100 transition-colors no-underline">
                <svg class="w-4 h-4 fill-none stroke-current" stroke-width="3" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
                </svg>
                <span>ATIVIDADES</span>
            </a>

            <!-- Informações do Aluno Logado -->
            <div class="flex items-center gap-2 flex-wrap">
                <span class="bg-papel-claro borda-tinta-escura rounded-xl px-3 py-1.5 font-bingo font-bold text-xs sm:text-sm text-tinta-escura shadow-sombra-cartum-branca">
                    ALUNO: <?= htmlspecialchars($nomeUsuario) ?>
                </span>
                <?php if ($posicaoUsuario > 0): ?>
                <span class="bg-azul-vibrante text-white borda-tinta-escura rounded-xl px-3 py-1.5 font-bingo font-bold text-xs sm:text-sm shadow-sombra-cartum-branca">
                    SUA POSIÇÃO: <?= $posicaoUsuario ?>º LUGAR
                </span>
                <?php endif; ?>
                <span class="bg-amarelo-vibrante borda-tinta-escura rounded-xl px-3 py-1.5 font-bingo font-bold text-xs sm:text-sm text-tinta-escura shadow-sombra-cartum-branca">
                    <strong class="font-gooddog text-lg"><?= $pontosUsuario ?></strong> PTS
                </span>
            </div>
        </header>

        <!-- CONTEÚDO PRINCIPAL DO RANKING -->
        <main id="conteudo-ranking" class="w-full my-auto flex flex-col items-center gap-6 max-w-5xl mx-auto py-2">

            <!-- TÍTULO DA PÁGINA -->
            <div class="text-center space-y-1">
                <div class="inline-flex items-center gap-2 bg-amarelo-vibrante text-tinta-escura px-4 py-1 rounded-full border-2 border-tinta-escura font-bingo font-extrabold text-xs uppercase tracking-wider mb-1">
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                    </svg>
                    <span>PLACAR GERAL DA ESCOLA</span>
                </div>
                <h1 class="font-gooddog text-4xl sm:text-5xl md:text-6xl font-bold text-tinta-escura tracking-wider leading-tight">
                    TOP SABIDOS — RANKING
                </h1>
                <p class="font-bingo text-sm sm:text-base md:text-lg font-extrabold text-gray-600 tracking-wide">
                    Veja quem acertou mais desafios em inglês!
                </p>
            </div>

            <?php if (!empty($ranking)): ?>

                <!-- PÓDIO DOS 3 PRIMEIROS (SE HOUVER PELO MENOS 1 ALUNO) -->
                <?php
                $primeiro = $ranking[0] ?? null;
                $segundo  = $ranking[1] ?? null;
                $terceiro = $ranking[2] ?? null;
                ?>
                <div class="w-full max-w-4xl podio-container grid grid-cols-1 md:grid-cols-3 gap-4 items-end mt-2 mb-2">
                    
                    <!-- 2º LUGAR (PRATA) -->
                    <?php if ($segundo): ?>
                    <div class="podio-card podio-2 p-4 text-center order-2 md:order-1 <?= ((int)$segundo['id'] === $userId) ? 'ring-4 ring-azul-escuro' : '' ?>">
                        <div class="w-12 h-12 mx-auto mb-2 bg-slate-200 border-3 border-tinta-escura rounded-2xl flex items-center justify-center font-gooddog text-2xl font-black text-slate-800 shadow-sm">
                            2º
                        </div>
                        <span class="block font-bingo text-xs font-black text-slate-700 uppercase tracking-widest">2º LUGAR</span>
                        <h3 class="font-gooddog text-2xl font-bold text-tinta-escura truncate px-2" title="<?= htmlspecialchars($segundo['nome']) ?>">
                            <?= htmlspecialchars($segundo['nome']) ?>
                        </h3>
                        <?php if ((int)$segundo['id'] === $userId): ?>
                            <span class="inline-block bg-azul-escuro text-white text-[10px] font-bingo font-bold px-2 py-0.5 rounded-md my-1">VOCÊ</span>
                        <?php endif; ?>
                        <div class="grid grid-cols-2 gap-2 mt-3 text-xs font-bingo">
                            <div class="bg-white/80 rounded-xl p-2 border border-slate-400">
                                <span class="block text-verde-escuro font-black text-sm"><?= $segundo['acertos'] ?></span>
                                <span class="text-[10px] text-gray-600">ACERTOS</span>
                            </div>
                            <div class="bg-white/80 rounded-xl p-2 border border-slate-400">
                                <span class="block text-red-600 font-black text-sm"><?= $segundo['erros'] ?></span>
                                <span class="text-[10px] text-gray-600">ERROS</span>
                            </div>
                        </div>
                        <div class="mt-2 bg-tinta-escura text-white rounded-xl py-1 font-gooddog text-lg font-bold">
                            <?= $segundo['pontuacao'] ?> PONTOS
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- 1º LUGAR (CAMPEÃO - OURO) -->
                    <?php if ($primeiro): ?>
                    <div class="podio-card podio-1 p-5 text-center order-1 md:order-2 md:-translate-y-3 <?= ((int)$primeiro['id'] === $userId) ? 'ring-4 ring-azul-escuro' : '' ?>">
                        <div class="w-16 h-16 mx-auto mb-2 bg-amarelo-vibrante border-4 border-tinta-escura rounded-2xl flex items-center justify-center text-tinta-escura shadow-md">
                            <svg class="w-10 h-10 fill-current" viewBox="0 0 24 24">
                                <path d="M19 5h-2V3H7v2H5c-1.1 0-2 .9-2 2v1c0 2.55 1.92 4.63 4.39 4.94.63 1.5 1.98 2.63 3.61 2.96V19H7v2h10v-2h-4v-3.1c1.63-.33 2.98-1.46 3.61-2.96C19.08 12.63 21 10.55 21 8V7c0-1.1-.9-2-2-2zM5 8V7h2v3.82C5.84 10.4 5 9.3 5 8zm14 0c0 1.3-.84 2.4-2 2.82V7h2v1z"/>
                            </svg>
                        </div>
                        <span class="inline-block bg-tinta-escura text-amarelo-vibrante text-xs font-bingo font-black px-3 py-0.5 rounded-full uppercase tracking-wider">
                            1º CAMPEÃO
                        </span>
                        <h2 class="font-gooddog text-3xl sm:text-4xl font-bold text-tinta-escura mt-1 truncate px-2" title="<?= htmlspecialchars($primeiro['nome']) ?>">
                            <?= htmlspecialchars($primeiro['nome']) ?>
                        </h2>
                        <?php if ((int)$primeiro['id'] === $userId): ?>
                            <span class="inline-block bg-azul-escuro text-white text-[10px] font-bingo font-bold px-2.5 py-0.5 rounded-md my-1">VOCÊ</span>
                        <?php endif; ?>
                        <div class="grid grid-cols-2 gap-2 mt-3 text-xs font-bingo">
                            <div class="bg-white/90 rounded-xl p-2 border-2 border-tinta-escura">
                                <span class="block text-verde-escuro font-black text-base"><?= $primeiro['acertos'] ?></span>
                                <span class="text-[10px] text-gray-700">ACERTOS</span>
                            </div>
                            <div class="bg-white/90 rounded-xl p-2 border-2 border-tinta-escura">
                                <span class="block text-red-600 font-black text-base"><?= $primeiro['erros'] ?></span>
                                <span class="text-[10px] text-gray-700">ERROS</span>
                            </div>
                        </div>
                        <div class="mt-2.5 bg-tinta-escura text-amarelo-vibrante rounded-xl py-1.5 font-gooddog text-xl font-bold tracking-wider">
                            <?= $primeiro['pontuacao'] ?> PONTOS
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- 3º LUGAR (BRONZE) -->
                    <?php if ($terceiro): ?>
                    <div class="podio-card podio-3 p-4 text-center order-3 <?= ((int)$terceiro['id'] === $userId) ? 'ring-4 ring-azul-escuro' : '' ?>">
                        <div class="w-12 h-12 mx-auto mb-2 bg-amber-200 border-3 border-tinta-escura rounded-2xl flex items-center justify-center font-gooddog text-2xl font-black text-amber-900 shadow-sm">
                            3º
                        </div>
                        <span class="block font-bingo text-xs font-black text-amber-900 uppercase tracking-widest">3º LUGAR</span>
                        <h3 class="font-gooddog text-2xl font-bold text-tinta-escura truncate px-2" title="<?= htmlspecialchars($terceiro['nome']) ?>">
                            <?= htmlspecialchars($terceiro['nome']) ?>
                        </h3>
                        <?php if ((int)$terceiro['id'] === $userId): ?>
                            <span class="inline-block bg-azul-escuro text-white text-[10px] font-bingo font-bold px-2 py-0.5 rounded-md my-1">VOCÊ</span>
                        <?php endif; ?>
                        <div class="grid grid-cols-2 gap-2 mt-3 text-xs font-bingo">
                            <div class="bg-white/80 rounded-xl p-2 border border-amber-500">
                                <span class="block text-verde-escuro font-black text-sm"><?= $terceiro['acertos'] ?></span>
                                <span class="text-[10px] text-gray-600">ACERTOS</span>
                            </div>
                            <div class="bg-white/80 rounded-xl p-2 border border-amber-500">
                                <span class="block text-red-600 font-black text-sm"><?= $terceiro['erros'] ?></span>
                                <span class="text-[10px] text-gray-600">ERROS</span>
                            </div>
                        </div>
                        <div class="mt-2 bg-tinta-escura text-white rounded-xl py-1 font-gooddog text-lg font-bold">
                            <?= $terceiro['pontuacao'] ?> PONTOS
                        </div>
                    </div>
                    <?php endif; ?>

                </div>

                <!-- LISTA COMPLETA DE TODOS OS USUÁRIOS -->
                <div class="w-full max-w-4xl space-y-3 mt-4">
                    <div class="flex items-center justify-between px-2">
                        <span class="font-bingo font-black text-xs sm:text-sm text-gray-500 uppercase tracking-widest">
                            TODOS OS ALUNOS (<?= count($ranking) ?> CADASTRADOS)
                        </span>
                        <span class="font-bingo text-xs font-extrabold text-gray-500">
                            ORDENADO POR MAIS ACERTOS
                        </span>
                    </div>

                    <!-- LINHAS DO RANKING -->
                    <div class="space-y-2.5">
                        <?php foreach ($ranking as $u): ?>
                            <?php 
                            $isCurrentUser = ((int) $u['id'] === $userId);
                            $badgeColor = 'bg-white text-tinta-escura';
                            if ($u['posicao'] === 1) $badgeColor = 'bg-amarelo-vibrante text-tinta-escura';
                            elseif ($u['posicao'] === 2) $badgeColor = 'bg-slate-200 text-slate-800';
                            elseif ($u['posicao'] === 3) $badgeColor = 'bg-amber-200 text-amber-900';
                            ?>
                            <div class="linha-ranking p-3 sm:p-4 flex flex-wrap sm:flex-nowrap items-center justify-between gap-3 <?= $isCurrentUser ? 'linha-ranking-usuario-atual' : '' ?>">
                                
                                <!-- POSIÇÃO E NOME -->
                                <div class="flex items-center gap-3 min-w-[180px] flex-1">
                                    <span class="badge-posicao <?= $badgeColor ?>">
                                        <?= $u['posicao'] ?>º
                                    </span>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <h4 class="font-gooddog text-xl sm:text-2xl font-bold text-tinta-escura leading-none">
                                                <?= htmlspecialchars($u['nome']) ?>
                                            </h4>
                                            <?php if ($isCurrentUser): ?>
                                                <span class="bg-azul-escuro text-white text-[10px] font-bingo font-black px-2 py-0.5 rounded-full">
                                                    VOCÊ
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <span class="font-bingo text-xs text-gray-500">
                                            <?= $u['questoesRespondidas'] ?> questões respondidas
                                        </span>
                                    </div>
                                </div>

                                <!-- ESTATÍSTICAS: ACERTOS, ERROS E PONTOS -->
                                <div class="flex items-center gap-2 sm:gap-4 flex-wrap sm:flex-nowrap shrink-0">
                                    
                                    <!-- ACERTOS (VERDE) -->
                                    <div class="bg-green-100 border-2 border-verde-escuro rounded-xl px-3 py-1 text-center min-w-[70px]">
                                        <span class="block font-gooddog text-lg sm:text-xl font-bold text-verde-escuro leading-none">
                                            <?= $u['acertos'] ?>
                                        </span>
                                        <span class="block font-bingo text-[10px] font-black text-verde-escuro uppercase">
                                            ACERTOS
                                        </span>
                                    </div>

                                    <!-- ERROS (SUBTRAÇÃO: questoesRespondidas - acertos) -->
                                    <div class="bg-red-100 border-2 border-red-600 rounded-xl px-3 py-1 text-center min-w-[70px]">
                                        <span class="block font-gooddog text-lg sm:text-xl font-bold text-red-600 leading-none">
                                            <?= $u['erros'] ?>
                                        </span>
                                        <span class="block font-bingo text-[10px] font-black text-red-600 uppercase">
                                            ERROS
                                        </span>
                                    </div>

                                    <!-- PONTUAÇÃO FINAL -->
                                    <div class="bg-amarelo-vibrante border-2 border-tinta-escura rounded-xl px-3 py-1 text-center min-w-[85px] shadow-sm">
                                        <span class="block font-gooddog text-xl sm:text-2xl font-bold text-tinta-escura leading-none">
                                            <?= $u['pontuacao'] ?>
                                        </span>
                                        <span class="block font-bingo text-[10px] font-black text-tinta-escura uppercase">
                                            PONTOS
                                        </span>
                                    </div>

                                </div>

                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

            <?php else: ?>

                <!-- NENHUM USUÁRIO ENCONTRADO -->
                <div class="text-center py-12">
                    <div class="w-16 h-16 mx-auto mb-4 bg-papel-claro border-2 border-tinta-escura rounded-2xl flex items-center justify-center font-bold text-xl text-gray-500">
                        0
                    </div>
                    <p class="font-bingo font-bold text-xl text-gray-600">
                        Nenhum usuário cadastrado ainda.<br>
                        Comece a jogar para inaugurar o ranking!
                    </p>
                    <a href="index.php" class="inline-flex items-center gap-2 mt-4 bg-azul-vibrante text-white font-bingo font-bold px-6 py-2.5 rounded-2xl border-3 border-tinta-escura shadow-sombra-cartum-branca no-underline hover:bg-azul-escuro transition-colors">
                        Ir para os Jogos
                    </a>
                </div>

            <?php endif; ?>

        </main>

        <!-- RODAPÉ -->
        <footer class="w-full text-center pt-4 pb-2 text-xs font-bingo font-bold text-gray-500">
            Escola do Saber &bull; Aprendizado de Inglês Interativo
        </footer>

    </div>

    <!-- Script de Acessibilidade & Modo Escuro -->
    <script src="js/acessibilidade.js"></script>
</body>
</html>
