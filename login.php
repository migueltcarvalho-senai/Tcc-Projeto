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
$loginInput = '';

// Mensagem de sucesso após cadastro vindo de registro.php
if (isset($_GET['cadastrado']) && $_GET['cadastrado'] == '1') {
    $sucesso = 'Conta criada com sucesso! Faça seu login abaixo.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loginInput = trim($_POST['login'] ?? $_POST['nome'] ?? '');
    $senha      = $_POST['senha'] ?? '';

    if (empty($loginInput) || empty($senha)) {
        $erro = 'Por favor, preencha todos os campos.';
    } else {
        $tbl_usuarios = db_table($conn, "usuario");
        // Permite login por nome de usuário ou e-mail
        $sql = "SELECT id, nome, email, senha, pontuacao, questoesRespondidas, acertos FROM {$tbl_usuarios} WHERE nome = ? OR email = ? LIMIT 1";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("ss", $loginInput, $loginInput);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            $senhaValida = false;
            if ($user) {
                if (password_verify($senha, $user['senha'])) {
                    $senhaValida = true;
                } elseif ($senha === $user['senha']) {
                    // Atualiza hash se estiver em texto puro antigo
                    $senhaValida = true;
                    $novoHash = password_hash($senha, PASSWORD_DEFAULT);
                    $updateStmt = $conn->prepare("UPDATE {$tbl_usuarios} SET senha = ? WHERE id = ?");
                    if ($updateStmt) {
                        $updateStmt->bind_param("si", $novoHash, $user['id']);
                        $updateStmt->execute();
                        $updateStmt->close();
                    }
                }
            }

            if ($senhaValida) {
                $_SESSION['id']                  = (int) $user['id'];
                $_SESSION['user']                = $user['nome'];
                $_SESSION['email']               = $user['email'];
                $_SESSION['pontuacao']           = (int) ($user['pontuacao'] ?? 0);
                $_SESSION['questoesRespondidas'] = (int) ($user['questoesRespondidas'] ?? 0);
                $_SESSION['acertos']             = (int) ($user['acertos'] ?? 0);

                header('Location: index.php');
                exit;
            } else {
                $erro = 'Nome de usuário/e-mail ou senha incorretos.';
            }

            $stmt->close();
        } else {
            $erro = 'Erro interno ao consultar o banco de dados.';
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
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>📚</text></svg>">

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

    <!-- Estilos das Telas de Autenticação -->
    <link rel="stylesheet" href="css/auth.css">
</head>
<body class="font-bingo min-h-screen text-tinta-escura flex flex-col justify-center items-center p-3 sm:p-6 relative">

    <!-- ELEMENTOS OSCILANTES DE FUNDO -->
    <div id="elementos-fundo-oscilantes" aria-hidden="true">
        <div class="elemento-fundo-oscilante elemento-fundo-1"></div>
        <div class="elemento-fundo-oscilante elemento-fundo-2"></div>
        <div class="elemento-fundo-oscilante elemento-fundo-3"></div>
        <div class="elemento-fundo-oscilante elemento-fundo-4"></div>
    </div>

    <!-- LIVRO / CADERNO PRINCIPAL DE LOGIN -->
    <main class="w-full max-w-lg caderno-pagina-branca rounded-3xl p-5 sm:p-8 md:p-10 relative">

        <!-- ESPIRAL DO CADERNO NO TOPO -->
        <div class="w-full flex justify-between items-center -mt-9 sm:-mt-12 mb-4 z-20 px-2 sm:px-4">
            <div class="espiral-caderno"></div>
            <div class="espiral-caderno"></div>
            <div class="espiral-caderno"></div>
            <div class="espiral-caderno"></div>
            <div class="espiral-caderno"></div>
            <div class="espiral-caderno hidden sm:block"></div>
            <div class="espiral-caderno hidden sm:block"></div>
            <div class="espiral-caderno hidden md:block"></div>
        </div>

        <!-- CABEÇALHO COM TÍTULO VIBRANTE E LETRAS ENTORTADAS -->
        <div class="text-center mb-6">
            <h1 class="font-gooddog text-6xl sm:text-7xl md:text-8xl font-bold tracking-wider leading-none select-none my-1">
                <span class="letra-vibrante text-laranja-vibrante" style="transform: rotate(-6deg);">L</span>
                <span class="letra-vibrante text-azul-vibrante" style="transform: rotate(5deg);">O</span>
                <span class="letra-vibrante text-verde-vibrante" style="transform: rotate(-4deg);">G</span>
                <span class="letra-vibrante text-amarelo-vibrante" style="transform: rotate(6deg);">I</span>
                <span class="letra-vibrante text-rosa-vibrante" style="transform: rotate(-5deg);">N</span>
                <span class="letra-vibrante text-laranja-vibrante" style="transform: rotate(7deg);">!</span>
            </h1>
            <p class="font-bingo text-base sm:text-lg font-bold text-gray-700 tracking-wide mt-2">
                Digite seu usuário ou e-mail e sua senha para entrar
            </p>
        </div>

        <!-- ALERTA DE ERRO -->
        <?php if (!empty($erro)): ?>
            <div class="alerta-ludico alerta-erro p-3.5 mb-5 text-center">
                <span class="font-bingo text-base sm:text-lg font-bold"><?= htmlspecialchars($erro) ?></span>
            </div>
        <?php endif; ?>

        <!-- ALERTA DE SUCESSO -->
        <?php if (!empty($sucesso)): ?>
            <div class="alerta-ludico alerta-sucesso p-3.5 mb-5 text-center">
                <span class="font-bingo text-base sm:text-lg font-bold"><?= htmlspecialchars($sucesso) ?></span>
            </div>
        <?php endif; ?>

        <!-- FORMULÁRIO DE LOGIN -->
        <form method="POST" action="login.php" class="space-y-4">
            <!-- CAMPO IDENTIFICADOR (NOME OU EMAIL) -->
            <div class="campo-wrapper campo-inclinado-1">
                <label for="login" class="block font-bingo text-lg sm:text-xl font-bold text-tinta-escura mb-1 flex items-center gap-2">
                    <span class="inline-block w-3 h-3 bg-verde-vibrante border-2 border-tinta-escura rounded-sm"></span>
                    NOME OU E-MAIL:
                </label>
                <input 
                    type="text" 
                    id="login" 
                    name="login" 
                    class="input-ludico w-full py-3 px-4 font-bingo text-lg sm:text-xl font-bold placeholder:text-gray-400 placeholder:font-normal" 
                    placeholder="Digite seu nome ou e-mail" 
                    value="<?= htmlspecialchars($loginInput) ?>" 
                    required 
                    autofocus
                    autocomplete="username"
                >
            </div>

            <!-- CAMPO SENHA -->
            <div class="campo-wrapper campo-inclinado-2">
                <label for="senha" class="block font-bingo text-lg sm:text-xl font-bold text-tinta-escura mb-1 flex items-center gap-2">
                    <span class="inline-block w-3 h-3 bg-azul-vibrante border-2 border-tinta-escura rounded-sm"></span>
                    SENHA:
                </label>
                <input 
                    type="password" 
                    id="senha" 
                    name="senha" 
                    class="input-ludico w-full py-3 px-4 font-bingo text-lg sm:text-xl font-bold placeholder:text-gray-400 placeholder:font-normal" 
                    placeholder="Digite sua senha" 
                    required
                    autocomplete="current-password"
                >
            </div>

            <!-- BOTÃO DE ENTRAR -->
            <div class="pt-2">
                <button type="submit" class="botao-cartum botao-verde w-full py-3.5 sm:py-4 text-2xl sm:text-3xl font-gooddog font-bold tracking-wider flex items-center justify-center">
                    <span>ENTRAR</span>
                </button>
            </div>
        </form>

        <!-- RODAPÉ DO CADERNO / LINK PARA REGISTRO -->
        <div class="mt-6 pt-4 border-t-2 border-dashed border-gray-300 text-center">
            <p class="font-bingo text-base sm:text-lg font-bold text-gray-700 mb-2">
                Ainda não tem uma conta?
            </p>
            <a href="registro.php" class="botao-cartum botao-laranja inline-block px-6 py-2.5 text-base sm:text-lg font-bold">
                CRIAR CONTA
            </a>
        </div>
    </main>

</body>
</html>
