<?php
session_start();
require_once 'scripts/conexao.php';

// Se já estiver logado, redireciona
if (isset($_SESSION['logado']) && $_SESSION['logado'] === true) {
    if ($_SESSION['nivel_acesso'] === 'cliente') {
        header("Location: lista_projetos.php");
    } else {
        header("Location: dashboard.php");
    }
    exit;
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $senha = trim($_POST['senha']);

    if (empty($email) || empty($senha)) {
        $erro = "Por favor, preencha o e-mail e a senha.";
    } else {
        try {
            // TENTATIVA 1: Equipa da Construtora (Tabela: usuarios | Coluna: senha_hash)
            $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = :email AND status = 'Ativo'");
            $stmt->execute([':email' => $email]);
            $usuario = $stmt->fetch();

            if ($usuario && password_verify($senha, $usuario['senha_hash'])) {
                $_SESSION['logado'] = true;
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['nivel_acesso'] = $usuario['nivel_acesso']; // admin ou comum
                $_SESSION['nome_usuario'] = $usuario['nome'];
                
                header("Location: dashboard.php");
                exit;
            }

            // TENTATIVA 2: Portal do Cliente (Tabela: clientes | Coluna: senha)
            $stmt_cli = $pdo->prepare("SELECT * FROM clientes WHERE email = :email AND status = 'Ativo'");
            $stmt_cli->execute([':email' => $email]);
            $cliente = $stmt_cli->fetch();

            if ($cliente && password_verify($senha, $cliente['senha'])) {
                $_SESSION['logado'] = true;
                $_SESSION['usuario_id'] = $cliente['id'];
                $_SESSION['nivel_acesso'] = 'cliente';
                $_SESSION['nome_usuario'] = $cliente['nome'];
                
                header("Location: lista_projetos.php");
                exit;
            }

            $erro = "Credenciais inválidas ou conta inativa.";

        } catch (PDOException $e) {
            $erro = "Erro ao conectar com o banco de dados.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | RSF Engenharia</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../styles/login.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <h2><i class="fas fa-hard-hat" style="color: #f1c40f;"></i> RSF Engenharia</h2>
                <p>Acesso ao Portal de Obras</p>
            </div>

            <?php if (!empty($erro)): ?>
                <div class="alerta-erro" style="background-color: rgba(231,76,60,0.1); color: #e74c3c; padding: 10px; border-radius: 4px; margin-bottom: 20px; text-align: center; font-size: 0.9rem; border: 1px solid #e74c3c;">
                    <i class="fas fa-exclamation-circle"></i> <?= $erro ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php">
                <div class="input-group">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" placeholder="E-mail" required autofocus>
                </div>
                
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="senha" placeholder="Senha" required>
                </div>
                
                <button type="submit" class="btn-login">Entrar</button>
            </form>
            
            <div class="login-footer" style="margin-top: 20px; text-align: center; color: #666; font-size: 0.8rem;">
                <a href="../index.html" class="back-link"><i class="fas fa-arrow-left"></i> Voltar ao site principal</a>
            </div>
        </div>
    </div>
</body>
</html>