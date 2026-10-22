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
    $sucesso = 'Cadastro realizado com sucesso! Faça login para continuar.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Aceita tanto 'nome' quanto 'login' para compatibilidade
    $nome = trim($_POST['nome'] ?? $_POST['login'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if (empty($nome) || empty($senha)) {
        $erro = 'Por favor, preencha todos os campos.';
    } else {
        // Busca o usuário pelo nome na tabela usuarios
        $sql = "SELECT id, nome, senha, pontuacaoPrimeiroJogo, pontuacaoSegundojogo FROM usuarios WHERE nome = ? LIMIT 1";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("s", $nome);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            // Suporta senha com hash seguro (password_verify) ou texto puro pré-existente
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
                $erro = 'Nome de usuário ou senha incorretos.';
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
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }
        body {
            background-color: #f3f4f6;
            color: #1f2937;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container-auth {
            background-color: #ffffff;
            width: 100%;
            max-width: 400px;
            padding: 32px;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border: 1px solid #e5e7eb;
        }
        .auth-header {
            margin-bottom: 24px;
            text-align: center;
        }
        .auth-header h1 {
            font-size: 24px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 8px;
        }
        .auth-header p {
            font-size: 14px;
            color: #6b7280;
        }
        .form-group {
            margin-bottom: 18px;
        }
        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-size: 14px;
            font-weight: 500;
            color: #374151;
        }
        .form-control {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
            color: #111827;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
        }
        .btn-submit {
            width: 100%;
            padding: 12px;
            background-color: #2563eb;
            color: #ffffff;
            border: none;
            border-radius: 6px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .btn-submit:hover {
            background-color: #1d4ed8;
        }
        .alert {
            padding: 12px;
            border-radius: 6px;
            font-size: 14px;
            margin-bottom: 20px;
        }
        .alert-error {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        .alert-success {
            background-color: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }
        .auth-footer {
            margin-top: 24px;
            text-align: center;
            font-size: 14px;
            color: #6b7280;
        }
        .auth-footer a {
            color: #2563eb;
            text-decoration: none;
            font-weight: 500;
        }
        .auth-footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container-auth">
        <div class="auth-header">
            <h1>Entrar</h1>
            <p>Acesse sua conta para continuar</p>
        </div>

        <?php if (!empty($erro)): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($erro) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($sucesso)): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($sucesso) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="nome">Nome de Usuário</label>
                <input 
                    type="text" 
                    id="nome" 
                    name="nome" 
                    class="form-control" 
                    placeholder="Digite seu nome de usuário" 
                    value="<?= htmlspecialchars($nome) ?>" 
                    required 
                    autofocus
                >
            </div>

            <div class="form-group">
                <label for="senha">Senha</label>
                <input 
                    type="password" 
                    id="senha" 
                    name="senha" 
                    class="form-control" 
                    placeholder="Digite sua senha" 
                    required
                >
            </div>

            <button type="submit" class="btn-submit">Entrar</button>
        </form>

        <div class="auth-footer">
            Não tem uma conta? <a href="registro.php">Cadastre-se</a>
        </div>
    </div>
</body>
</html>
