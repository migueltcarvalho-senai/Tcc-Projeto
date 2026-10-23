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
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome           = trim($_POST['nome'] ?? '');
    $email          = trim($_POST['email'] ?? '');
    $senha          = $_POST['senha'] ?? '';
    $confirmarSenha = $_POST['confirmar_senha'] ?? '';

    if (empty($nome) || empty($email) || empty($senha) || empty($confirmarSenha)) {
        $erro = 'Por favor, preencha todos os campos.';
    } elseif (mb_strlen($nome) < 3) {
        $erro = 'O nome de usuário deve conter pelo menos 3 caracteres.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'Por favor, informe um endereço de e-mail válido.';
    } elseif (strlen($senha) < 4) {
        $erro = 'A senha deve conter pelo menos 4 caracteres.';
    } elseif ($senha !== $confirmarSenha) {
        $erro = 'As senhas informadas não coincidem. Tente novamente.';
    } else {
        $tbl_usuarios = db_table($conn, "usuarios");
        
        // Verifica se já existe usuário com o mesmo nome ou email
        $checkSql = "SELECT id, nome, email FROM {$tbl_usuarios} WHERE nome = ? OR email = ? LIMIT 1";
        $checkStmt = $conn->prepare($checkSql);

        if ($checkStmt) {
            $checkStmt->bind_param("ss", $nome, $email);
            $checkStmt->execute();
            $checkRes = $checkStmt->get_result();

            if ($checkRow = $checkRes->fetch_assoc()) {
                if (strcasecmp($checkRow['nome'], $nome) === 0) {
                    $erro = 'Esse nome de usuário já está em uso. Por favor, escolha outro.';
                } else {
                    $erro = 'Esse e-mail já está cadastrado. Faça login ou use outro.';
                }
            } else {
                $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
                $pontuacaoInicial = 0;
                $questoesInicial  = 0;
                $acertosInicial   = 0;

                $insertSql = "INSERT INTO {$tbl_usuarios} (nome, senha, email, pontuacao, questoesRespondidas, acertos) VALUES (?, ?, ?, ?, ?, ?)";
                $insertStmt = $conn->prepare($insertSql);

                if ($insertStmt) {
                    $insertStmt->bind_param("sssiii", $nome, $senhaHash, $email, $pontuacaoInicial, $questoesInicial, $acertosInicial);
                    
                    if ($insertStmt->execute()) {
                        $insertStmt->close();
                        $checkStmt->close();
                        header('Location: login.php?cadastrado=1');
                        exit;
                    } else {
                        $erro = 'Erro ao salvar o cadastro. Tente novamente.';
                    }
                    $insertStmt->close();
                } else {
                    $erro = 'Erro interno ao preparar o registro.';
                }
            }
            $checkStmt->close();
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

    <!-- LIVRO / CADERNO PRINCIPAL DE REGISTRO -->
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
            <p class="font-bingo text-base sm:text-lg font-bold text-gray-700 tracking-wide mt-2">
                Preencha os campos abaixo para criar sua conta
            </p>
        </div>

        <!-- ALERTA DE ERRO -->
        <?php if (!empty($erro)): ?>
            <div class="alerta-ludico alerta-erro p-3.5 mb-5 text-center">
                <span class="font-bingo text-base sm:text-lg font-bold"><?= htmlspecialchars($erro) ?></span>
            </div>
        <?php endif; ?>

        <!-- FORMULÁRIO DE CADASTRO -->
        <form method="POST" action="registro.php" class="space-y-4">
            <!-- CAMPO NOME -->
            <div class="campo-wrapper campo-inclinado-1">
                <label for="nome" class="block font-bingo text-lg sm:text-xl font-bold text-tinta-escura mb-1 flex items-center gap-2">
                    <span class="inline-block w-3 h-3 bg-verde-vibrante border-2 border-tinta-escura rounded-sm"></span>
                    NOME DE USUÁRIO:
                </label>
                <input 
                    type="text" 
                    id="nome" 
                    name="nome" 
                    class="input-ludico w-full py-3 px-4 font-bingo text-lg sm:text-xl font-bold placeholder:text-gray-400 placeholder:font-normal" 
                    placeholder="Digite seu nome de usuário" 
                    value="<?= htmlspecialchars($nome) ?>" 
                    required 
                    autofocus
                    autocomplete="username"
                >
            </div>

            <!-- CAMPO EMAIL -->
            <div class="campo-wrapper campo-inclinado-2">
                <label for="email" class="block font-bingo text-lg sm:text-xl font-bold text-tinta-escura mb-1 flex items-center gap-2">
                    <span class="inline-block w-3 h-3 bg-amarelo-vibrante border-2 border-tinta-escura rounded-sm"></span>
                    E-MAIL:
                </label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    class="input-ludico w-full py-3 px-4 font-bingo text-lg sm:text-xl font-bold placeholder:text-gray-400 placeholder:font-normal" 
                    placeholder="exemplo@escola.com" 
                    value="<?= htmlspecialchars($email) ?>" 
                    required 
                    autocomplete="email"
                >
            </div>

            <!-- CAMPO SENHA -->
            <div class="campo-wrapper campo-inclinado-3">
                <label for="senha" class="block font-bingo text-lg sm:text-xl font-bold text-tinta-escura mb-1 flex items-center gap-2">
                    <span class="inline-block w-3 h-3 bg-laranja-vibrante border-2 border-tinta-escura rounded-sm"></span>
                    SENHA:
                </label>
                <input 
                    type="password" 
                    id="senha" 
                    name="senha" 
                    class="input-ludico w-full py-3 px-4 font-bingo text-lg sm:text-xl font-bold placeholder:text-gray-400 placeholder:font-normal" 
                    placeholder="Mínimo de 4 caracteres" 
                    required
                    autocomplete="new-password"
                >
            </div>

            <!-- CAMPO CONFIRMAÇÃO DE SENHA -->
            <div class="campo-wrapper campo-inclinado-1">
                <label for="confirmar_senha" class="block font-bingo text-lg sm:text-xl font-bold text-tinta-escura mb-1 flex items-center gap-2">
                    <span class="inline-block w-3 h-3 bg-azul-vibrante border-2 border-tinta-escura rounded-sm"></span>
                    CONFIRMAR SENHA:
                </label>
                <input 
                    type="password" 
                    id="confirmar_senha" 
                    name="confirmar_senha" 
                    class="input-ludico w-full py-3 px-4 font-bingo text-lg sm:text-xl font-bold placeholder:text-gray-400 placeholder:font-normal" 
                    placeholder="Repita sua senha" 
                    required
                    autocomplete="new-password"
                >
            </div>

            <!-- BOTÃO DE CADASTRO -->
            <div class="pt-2">
                <button type="submit" class="botao-cartum botao-laranja w-full py-3.5 sm:py-4 text-2xl sm:text-3xl font-gooddog font-bold tracking-wider flex items-center justify-center">
                    <span>CRIAR CONTA</span>
                </button>
            </div>
        </form>

        <!-- RODAPÉ DO CADERNO / LINK PARA LOGIN -->
        <div class="mt-6 pt-4 border-t-2 border-dashed border-gray-300 text-center">
            <p class="font-bingo text-base sm:text-lg font-bold text-gray-700 mb-2">
                Já possui uma conta?
            </p>
            <a href="login.php" class="botao-cartum botao-verde inline-block px-6 py-2.5 text-base sm:text-lg font-bold">
                FAZER LOGIN
            </a>
        </div>
    </main>

</body>
</html>
