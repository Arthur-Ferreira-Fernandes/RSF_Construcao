<?php
session_start();

require_once 'scripts/conexao.php';

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $senha = $_POST['senha']; 

    if (empty($email) || empty($senha)) {
        $erro = 'Por favor, preencha todos os campos.';
    } else {
        $stmt = $pdo->prepare("SELECT id, nome, senha_hash, nivel_acesso FROM usuarios WHERE email = :email AND status = 'Ativo' LIMIT 1");
        $stmt->execute([':email' => $email]);
        $usuario = $stmt->fetch();

        if ($usuario && password_verify($senha, $usuario['senha_hash'])) {
            
            session_regenerate_id(true);
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['nivel_acesso'] = $usuario['nivel_acesso']; 
            $_SESSION['logado'] = true;
            
            header("Location: dashboard.php");
            exit;
        } else {
            $erro = 'Acesso negado: Credenciais inválidas.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal da Engenharia | RSF Construção</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="../styles/login.css">
</head>
<body>

    <div class="login-container">
        <div class="logo-area">
            <h1>RSF <span>Construção</span></h1>
            <p>Portal da Engenharia</p>
        </div>

        <?php if (!empty($erro)): ?>
            <div class="error-msg">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($erro) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="input-group">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" placeholder="E-mail corporativo" required autofocus>
            </div>

            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="senha" placeholder="Senha de acesso" required>
            </div>

            <button type="submit" class="btn-login">Entrar no Sistema</button>
        </form>

        <a href="../index.html" class="back-link"><i class="fas fa-arrow-left"></i> Voltar ao site principal</a>
    </div>

</body>
</html>