<?php
session_start();
require_once __DIR__ . '/config/config.php';

// Se já estiver logado, redireciona para a página principal
if (isset($_SESSION['id'])) {
    header('Location: index.php');
    exit;
}

$erro = '';
$sucesso = '';
$nome = '';

// Mensagem de sucesso após cadastro vindo de registro.php
if (isset($_GET['cadastrado']) && $_GET['cadastrado'] == '1') {
    $sucesso = 'Parabéns! Sua conta foi criada com sucesso. Faça seu login abaixo!';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? $_POST['login'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if (empty($nome) || empty($senha)) {
        $erro = 'Por favor, digite seu nome e sua senha!';
    } else {
        $sql = "SELECT id, nome, senha, pontuacaoPrimeiroJogo, pontuacaoSegundojogo FROM usuarios WHERE nome = ? LIMIT 1";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("s", $nome);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            $senhaValida = false;
            if ($user) {
                if (password_verify($senha, $user['senha'])) {
                    $senhaValida = true;
                } elseif ($senha === $user['senha']) {
                    $senhaValida = true;
                    // Atualiza para hash seguro no primeiro login
                    $novoHash = password_hash($senha, PASSWORD_DEFAULT);
                    $updateStmt = $conn->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
                    if ($updateStmt) {
                        $updateStmt->bind_param("si", $novoHash, $user['id']);
                        $updateStmt->execute();
                        $updateStmt->close();
                    }
                }
            }

            if ($senhaValida) {
                $_SESSION['id'] = $user['id'];
                $_SESSION['user'] = $user['nome'];
                $_SESSION['pontuacaoPrimeiroJogo'] = $user['pontuacaoPrimeiroJogo'] ?? 0;
                $_SESSION['pontuacaoSegundojogo'] = $user['pontuacaoSegundojogo'] ?? 0;

                header('Location: index.php');
                exit;
            } else {
                $erro = 'Nome de aprendiz ou senha incorretos. Tente de novo!';
            }

            $stmt->close();
        } else {
            $erro = 'Ops! Não conseguimos conectar ao banco de dados agora.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sketchbook Educacional</title>

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
            <div class="flex items-center gap-1.5 bg-amarelo-vibrante border-2 border-tinta-escura rounded-lg px-3 py-1 shadow-[0px_3px_0px_#0F172A] transform rotate-1">
                <span class="text-xs sm:text-sm font-black text-tinta-escura uppercase">📚 CADERNO ESCOLAR</span>
            </div>
        </div>

        <!-- CABEÇALHO COM TÍTULO VIBRANTE E LETRAS ENTORTADAS -->
        <div class="text-center mb-7">
            <h1 class="font-gooddog text-6xl sm:text-7xl md:text-8xl font-bold tracking-wider leading-none select-none my-1">
                <span class="letra-vibrante text-laranja-vibrante" style="transform: rotate(-6deg);">L</span>
                <span class="letra-vibrante text-azul-vibrante" style="transform: rotate(5deg);">O</span>
                <span class="letra-vibrante text-verde-vibrante" style="transform: rotate(-4deg);">G</span>
                <span class="letra-vibrante text-amarelo-vibrante" style="transform: rotate(6deg);">I</span>
                <span class="letra-vibrante text-rosa-vibrante" style="transform: rotate(-5deg);">N</span>
                <span class="letra-vibrante text-laranja-vibrante" style="transform: rotate(7deg);">!</span>
            </h1>
            <p class="font-bingo text-lg sm:text-xl font-bold text-gray-700 tracking-wide mt-2">
                Digite seu nome e sua senha para abrir suas atividades!
            </p>
        </div>

        <!-- ALERTA DE ERRO -->
        <?php if (!empty($erro)): ?>
            <div class="alerta-ludico alerta-erro p-3.5 mb-6 flex items-center gap-2.5">
                <span class="text-2xl">⚠️</span>
                <span class="font-bingo text-base sm:text-lg font-bold"><?= htmlspecialchars($erro) ?></span>
            </div>
        <?php endif; ?>

        <!-- ALERTA DE SUCESSO -->
        <?php if (!empty($sucesso)): ?>
            <div class="alerta-ludico alerta-sucesso p-3.5 mb-6 flex items-center gap-2.5">
                <span class="text-2xl">🎉</span>
                <span class="font-bingo text-base sm:text-lg font-bold"><?= htmlspecialchars($sucesso) ?></span>
            </div>
        <?php endif; ?>

        <!-- FORMULÁRIO DE LOGIN -->
        <form method="POST" action="login.php" class="space-y-5">
            <!-- CAMPO NOME (LIGEIRAMENTE INCLINADO) -->
            <div class="campo-wrapper campo-inclinado-1">
                <label for="nome" class="block font-bingo text-xl sm:text-2xl font-bold text-tinta-escura mb-1.5 flex items-center gap-2">
                    <span class="inline-block w-3.5 h-3.5 bg-verde-vibrante border-2 border-tinta-escura rounded-sm"></span>
                    NOME DO APRENDIZ:
                </label>
                <input 
                    type="text" 
                    id="nome" 
                    name="nome" 
                    class="input-ludico w-full py-3.5 px-4 font-bingo text-xl sm:text-2xl font-bold placeholder:text-gray-400 placeholder:font-normal" 
                    placeholder="Ex: Joãozinho" 
                    value="<?= htmlspecialchars($nome) ?>" 
                    required 
                    autofocus
                    autocomplete="username"
                >
            </div>

            <!-- CAMPO SENHA (LIGEIRAMENTE INCLINADO PARA O OUTRO LADO) -->
            <div class="campo-wrapper campo-inclinado-2">
                <label for="senha" class="block font-bingo text-xl sm:text-2xl font-bold text-tinta-escura mb-1.5 flex items-center gap-2">
                    <span class="inline-block w-3.5 h-3.5 bg-azul-vibrante border-2 border-tinta-escura rounded-sm"></span>
                    SUA SENHA:
                </label>
                <input 
                    type="password" 
                    id="senha" 
                    name="senha" 
                    class="input-ludico w-full py-3.5 px-4 font-bingo text-xl sm:text-2xl font-bold placeholder:text-gray-400 placeholder:font-normal" 
                    placeholder="Digite sua senha" 
                    required
                    autocomplete="current-password"
                >
            </div>

            <!-- BOTÃO DE ENTRAR -->
            <div class="pt-2">
                <button type="submit" class="botao-cartum botao-verde w-full py-3.5 sm:py-4 text-2xl sm:text-3xl font-gooddog font-bold tracking-wider flex items-center justify-center gap-3">
                    <span>ENTRAR NO MEU LIVRO 🚀</span>
                </button>
            </div>
        </form>

        <!-- RODAPÉ DO CADERNO / LINK PARA REGISTRO -->
        <div class="mt-8 pt-5 border-t-2 border-dashed border-gray-300 text-center">
            <p class="font-bingo text-base sm:text-lg font-bold text-gray-700 mb-2.5">
                Ainda não tem seu nome anotado no caderno?
            </p>
            <a href="registro.php" class="botao-cartum botao-laranja inline-block px-5 py-2.5 text-base sm:text-lg font-bold">
                CRIAR MINHA CONTA AGORA ✨
            </a>
        </div>
    </main>

</body>
</html>
