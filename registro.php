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

    // Validações básicas
    if (empty($nome) || empty($senha) || empty($confirmarSenha)) {
        $erro = 'Por favor, preencha todos os campos.';
    } elseif (mb_strlen($nome) < 3) {
        $erro = 'O nome de usuário deve conter pelo menos 3 caracteres.';
    } elseif (strlen($senha) < 4) {
        $erro = 'A senha deve conter pelo menos 4 caracteres.';
    } elseif ($senha !== $confirmarSenha) {
        $erro = 'As senhas informadas não coincidem.';
    } else {
        // Verifica se já existe um usuário com o mesmo nome
        $checkSql = "SELECT id FROM usuarios WHERE nome = ? LIMIT 1";
        $checkStmt = $conn->prepare($checkSql);

        if ($checkStmt) {
            $checkStmt->bind_param("s", $nome);
            $checkStmt->execute();
            $checkStmt->store_result();

            if ($checkStmt->num_rows > 0) {
                $erro = 'Este nome de usuário já está em uso. Por favor, escolha outro.';
            } else {
                // Criação do hash seguro da senha
                $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
                $pontuacaoInicial = 0;

                // Inserção na tabela usuarios
                $insertSql = "INSERT INTO usuarios (nome, senha, pontuacaoPrimeiroJogo, pontuacaoSegundojogo) VALUES (?, ?, ?, ?)";
                $insertStmt = $conn->prepare($insertSql);

                if ($insertStmt) {
                    $insertStmt->bind_param("ssii", $nome, $senhaHash, $pontuacaoInicial, $pontuacaoInicial);
                    
                    if ($insertStmt->execute()) {
                        $insertStmt->close();
                        $checkStmt->close();
                        // Redireciona para login com mensagem de sucesso
                        header('Location: login.php?cadastrado=1');
                        exit;
                    } else {
                        $erro = 'Erro ao cadastrar usuário. Tente novamente.';
                    }
                    $insertStmt->close();
                } else {
                    $erro = 'Erro interno ao preparar a inserção.';
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
            <h1>Criar Conta</h1>
            <p>Cadastre-se para registrar seus pontos</p>
        </div>

        <?php if (!empty($erro)): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($erro) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="registro.php">
            <div class="form-group">
                <label for="nome">Nome de Usuário</label>
                <input 
                    type="text" 
                    id="nome" 
                    name="nome" 
                    class="form-control" 
                    placeholder="Ex: Joãozinho" 
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
                    placeholder="Mínimo de 4 caracteres" 
                    required
                >
            </div>

            <div class="form-group">
                <label for="confirmar_senha">Confirmar Senha</label>
                <input 
                    type="password" 
                    id="confirmar_senha" 
                    name="confirmar_senha" 
                    class="form-control" 
                    placeholder="Repita a senha" 
                    required
                >
            </div>

            <button type="submit" class="btn-submit">Cadastrar</button>
        </form>

        <div class="auth-footer">
            Já possui uma conta? <a href="login.php">Fazer login</a>
        </div>
    </div>
</body>
</html>
