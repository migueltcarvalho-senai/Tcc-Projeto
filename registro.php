<?php
session_start();
require_once __DIR__ . '/config/config.php';

// Se já estiver logado, redireciona para a página principal
if (isset($_SESSION['id'])) {
    header('Location: index.php');
    exit;
}

$erro = '';
$nome = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $confirmarSenha = $_POST['confirmar_senha'] ?? '';

    // Validações com linguagem amigável e pedagógica
    if (empty($nome) || empty($senha) || empty($confirmarSenha)) {
        $erro = 'Por favor, preencha todos os campos do seu cadastro!';
    } elseif (mb_strlen($nome) < 3) {
        $erro = 'O seu nome deve ter pelo menos 3 letrinhas.';
    } elseif (strlen($senha) < 4) {
        $erro = 'A senha precisa ter pelo menos 4 números ou letras.';
    } elseif ($senha !== $confirmarSenha) {
        $erro = 'As duas senhas digitadas não são iguais! Confira e tente de novo.';
    } else {
        // Verifica se já existe um usuário com o mesmo nome
        $checkSql = "SELECT id FROM usuarios WHERE nome = ? LIMIT 1";
        $checkStmt = $conn->prepare($checkSql);

        if ($checkStmt) {
            $checkStmt->bind_param("s", $nome);
            $checkStmt->execute();
            $checkStmt->store_result();

            if ($checkStmt->num_rows > 0) {
                $erro = 'Esse nome já está em uso por outro aprendiz. Que tal tentar outro?';
            } else {
                $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
                $pontuacaoInicial = 0;

                $insertSql = "INSERT INTO usuarios (nome, senha, pontuacaoPrimeiroJogo, pontuacaoSegundojogo) VALUES (?, ?, ?, ?)";
                $insertStmt = $conn->prepare($insertSql);

                if ($insertStmt) {
                    $insertStmt->bind_param("ssii", $nome, $senhaHash, $pontuacaoInicial, $pontuacaoInicial);
                    
                    if ($insertStmt->execute()) {
                        $insertStmt->close();
                        $checkStmt->close();
                        header('Location: login.php?cadastrado=1');
                        exit;
                    } else {
                        $erro = 'Ops! Não conseguimos salvar seu cadastro agora. Tente de novo.';
                    }
                    $insertStmt->close();
                } else {
                    $erro = 'Erro interno ao preparar o registro.';
                }
            }
            $checkStmt->close();
        } else {
            $erro = 'Erro ao consultar o banco de dados.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - Sketchbook Educacional</title>

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
                        'laranja-escuro':   '#803700',
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
                    }
                }
            }
        }
    </script>

    <!-- Folha de Estilo Específica para Autenticação Lúdica -->
    <link rel="stylesheet" href="css/auth.css">
</head>
<body class="font-bingo text-tinta-escura flex flex-col justify-center items-center min-h-screen p-3 sm:p-6 md:p-8">

    <!-- ELEMENTOS OSCILANTES DE FUNDO -->
    <div id="elementos-fundo-oscilantes" aria-hidden="true">
        <div class="elemento-fundo-oscilante elemento-fundo-1"></div>
        <div class="elemento-fundo-oscilante elemento-fundo-2"></div>
        <div class="elemento-fundo-oscilante elemento-fundo-3"></div>
        <div class="elemento-fundo-oscilante elemento-fundo-4"></div>
    </div>

    <!-- FOLHA DO LIVRO / CADERNO ESCOLAR -->
    <main id="livro-conteiner-principal" class="w-full max-w-xl caderno-pagina-branca p-5 sm:p-8 md:p-10 my-6">

        <!-- ESPIRAL DO CADERNO NO TOPO -->
        <div id="espiral-topo-caderno" class="w-full flex justify-between items-center -mt-9 sm:-mt-12 mb-6 z-20 px-2 sm:px-6" aria-hidden="true">
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
        </div>

        <!-- BARRA SUPERIOR COM BOTÃO VOLTAR -->
        <div class="flex items-center justify-between w-full mb-6 pb-3 border-b-2 border-dashed border-gray-300">
            <a href="index.php" class="botao-cartum botao-branco px-3 sm:px-4 py-2 text-xs sm:text-sm font-bold flex items-center gap-1.5">
                <span>← VOLTAR AO INÍCIO</span>
            </a>
            <div class="flex items-center gap-1.5 bg-verde-vibrante border-2 border-tinta-escura rounded-lg px-3 py-1 shadow-[0px_3px_0px_#0F172A] transform -rotate-1 text-white">
                <span class="text-xs sm:text-sm font-black uppercase">✏️ NOVO APRENDIZ</span>
            </div>
        </div>

        <!-- CABEÇALHO COM TÍTULO VIBRANTE E LETRAS ENTORTADAS -->
        <div class="text-center mb-7">
            <h1 class="font-gooddog text-5xl sm:text-6xl md:text-7xl font-bold tracking-wider leading-none select-none my-1">
                <span class="letra-vibrante text-verde-vibrante" style="transform: rotate(-6deg);">C</span>
                <span class="letra-vibrante text-laranja-vibrante" style="transform: rotate(5deg);">A</span>
                <span class="letra-vibrante text-azul-vibrante" style="transform: rotate(-4deg);">D</span>
                <span class="letra-vibrante text-amarelo-vibrante" style="transform: rotate(6deg);">A</span>
                <span class="letra-vibrante text-rosa-vibrante" style="transform: rotate(-5deg);">S</span>
                <span class="letra-vibrante text-verde-vibrante" style="transform: rotate(4deg);">T</span>
                <span class="letra-vibrante text-laranja-vibrante" style="transform: rotate(-4deg);">R</span>
                <span class="letra-vibrante text-azul-vibrante" style="transform: rotate(6deg);">O</span>
                <span class="letra-vibrante text-amarelo-vibrante" style="transform: rotate(-6deg);">!</span>
            </h1>
            <p class="font-bingo text-lg sm:text-xl font-bold text-gray-700 tracking-wide mt-2">
                Escreva seu nome no caderno para salvar suas medalhas e pontos!
            </p>
        </div>

        <!-- ALERTA DE ERRO -->
        <?php if (!empty($erro)): ?>
            <div class="alerta-ludico alerta-erro p-3.5 mb-6 flex items-center gap-2.5">
                <span class="text-2xl">⚠️</span>
                <span class="font-bingo text-base sm:text-lg font-bold"><?= htmlspecialchars($erro) ?></span>
            </div>
        <?php endif; ?>

        <!-- FORMULÁRIO DE CADASTRO -->
        <form method="POST" action="registro.php" class="space-y-5">
            <!-- CAMPO NOME (LIGEIRAMENTE INCLINADO) -->
            <div class="campo-wrapper campo-inclinado-1">
                <label for="nome" class="block font-bingo text-xl sm:text-2xl font-bold text-tinta-escura mb-1.5 flex items-center gap-2">
                    <span class="inline-block w-3.5 h-3.5 bg-verde-vibrante border-2 border-tinta-escura rounded-sm"></span>
                    QUAL É O SEU NOME?
                </label>
                <input 
                    type="text" 
                    id="nome" 
                    name="nome" 
                    class="input-ludico w-full py-3.5 px-4 font-bingo text-xl sm:text-2xl font-bold placeholder:text-gray-400 placeholder:font-normal" 
                    placeholder="Ex: Maria Clara" 
                    value="<?= htmlspecialchars($nome) ?>" 
                    required 
                    autofocus
                    autocomplete="username"
                >
            </div>

            <!-- CAMPO SENHA (LIGEIRAMENTE INCLINADO) -->
            <div class="campo-wrapper campo-inclinado-2">
                <label for="senha" class="block font-bingo text-xl sm:text-2xl font-bold text-tinta-escura mb-1.5 flex items-center gap-2">
                    <span class="inline-block w-3.5 h-3.5 bg-laranja-vibrante border-2 border-tinta-escura rounded-sm"></span>
                    CRIE UMA SENHA SECRETA:
                </label>
                <input 
                    type="password" 
                    id="senha" 
                    name="senha" 
                    class="input-ludico w-full py-3.5 px-4 font-bingo text-xl sm:text-2xl font-bold placeholder:text-gray-400 placeholder:font-normal" 
                    placeholder="Pelo menos 4 caracteres" 
                    required
                    autocomplete="new-password"
                >
            </div>

            <!-- CAMPO CONFIRMAÇÃO DE SENHA (LIGEIRAMENTE INCLINADO) -->
            <div class="campo-wrapper campo-inclinado-3">
                <label for="confirmar_senha" class="block font-bingo text-xl sm:text-2xl font-bold text-tinta-escura mb-1.5 flex items-center gap-2">
                    <span class="inline-block w-3.5 h-3.5 bg-azul-vibrante border-2 border-tinta-escura rounded-sm"></span>
                    CONFIRME SUA SENHA:
                </label>
                <input 
                    type="password" 
                    id="confirmar_senha" 
                    name="confirmar_senha" 
                    class="input-ludico w-full py-3.5 px-4 font-bingo text-xl sm:text-2xl font-bold placeholder:text-gray-400 placeholder:font-normal" 
                    placeholder="Repita a senha aqui" 
                    required
                    autocomplete="new-password"
                >
            </div>

            <!-- BOTÃO DE CADASTRO -->
            <div class="pt-2">
                <button type="submit" class="botao-cartum botao-laranja w-full py-3.5 sm:py-4 text-2xl sm:text-3xl font-gooddog font-bold tracking-wider flex items-center justify-center gap-3">
                    <span>CRIAR MINHA CONTA 🎉</span>
                </button>
            </div>
        </form>

        <!-- RODAPÉ DO CADERNO / LINK PARA LOGIN -->
        <div class="mt-8 pt-5 border-t-2 border-dashed border-gray-300 text-center">
            <p class="font-bingo text-base sm:text-lg font-bold text-gray-700 mb-2.5">
                Já tem seu nome anotado aqui?
            </p>
            <a href="login.php" class="botao-cartum botao-verde inline-block px-5 py-2.5 text-base sm:text-lg font-bold">
                FAZER LOGIN NO LIVRO 🔑
            </a>
        </div>
    </main>

</body>
</html>
