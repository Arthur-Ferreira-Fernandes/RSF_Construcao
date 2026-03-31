<?php
session_start();
require_once 'scripts/conexao.php';

if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    header("Location: login.php");
    exit;
}

if ($_SESSION['nivel_acesso'] !== 'admin') {
    die("<h2 style='color:#fff; text-align:center; margin-top:50px;'>Acesso Negado. Apenas Administradores podem acessar esta página. <a href='dashboard.php' style='color:#FFCC00;'>Voltar ao Início</a></h2>");
}

$mensagem = '';
$tipo_mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $nome = trim($_POST['nome']);
    $telefone = trim($_POST['telefone']);
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $senha = $_POST['senha'];
    $nivel_acesso = $_POST['nivel_acesso'];

    if (empty($nome) || empty($telefone) || empty($email) || empty($senha) || empty($nivel_acesso)) {
        $mensagem = "Preencha todos os campos obrigatórios.";
        $tipo_mensagem = "erro";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensagem = "Formato de e-mail inválido.";
        $tipo_mensagem = "erro";
    } elseif (strlen($senha) < 6) {
        $mensagem = "A senha deve ter pelo menos 6 caracteres.";
        $tipo_mensagem = "erro";
    } else {
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

        try {
            $sql = "INSERT INTO usuarios (nome, telefone, email, senha_hash, nivel_acesso) 
                    VALUES (:nome, :telefone, :email, :senha_hash, :nivel_acesso)";
            $stmt = $pdo->prepare($sql);
            
            $stmt->execute([
                ':nome' => $nome,
                ':telefone' => $telefone,
                ':email' => $email,
                ':senha_hash' => $senha_hash,
                ':nivel_acesso' => $nivel_acesso
            ]);

            $mensagem = "Membro da equipe cadastrado! Ele já pode logar e ser alocado em obras.";
            $tipo_mensagem = "sucesso";

        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $mensagem = "Este e-mail já está em uso por outro usuário.";
            } else {
                $mensagem = "Erro ao salvar no banco de dados.";
            }
            $tipo_mensagem = "erro";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão da Equipe | RSF Engenharia</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../styles/projetos.css">
</head>
<body>

    <?php include 'header.php'; ?>

    <main class="container">
        
        <?php if (!empty($mensagem)): ?>
            <div class="alerta <?= $tipo_mensagem ?>">
                <?= htmlspecialchars($mensagem) ?>
            </div>
        <?php endif; ?>

        <div class="form-card" style="max-width: 700px; margin: 0 auto;">
            <h2><i class="fas fa-user-plus"></i> Cadastrar Membro da Equipe</h2>
            <p style="color: #aaa; font-size: 0.9rem; margin-top: -15px; margin-bottom: 25px;">
                Crie o perfil unificado. O usuário poderá acessar o sistema e ser definido como Responsável Técnico de obras.
            </p>
            
            <form method="POST" action="cadastrar_usuario.php">
                <div class="form-grid">
                    
                    <div class="form-group full-width">
                        <label for="nome">Nome Completo *</label>
                        <div class="input-icon-wrapper">
                            <i class="fas fa-user"></i>
                            <input type="text" id="nome" name="nome" placeholder="Ex: Carlos Almeida" required autofocus>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="telefone">Telefone / WhatsApp *</label>
                        <div class="input-icon-wrapper">
                            <i class="fas fa-phone-alt"></i>
                            <input type="tel" id="telefone" name="telefone" placeholder="(11) 90000-0000" maxlength="15" oninput="mascaraTelefone(this)" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="nivel_acesso">Nível de Acesso *</label>
                        <div class="input-icon-wrapper">
                            <i class="fas fa-shield-alt" style="z-index: 1;"></i>
                            <select id="nivel_acesso" name="nivel_acesso" required style="padding-left: 40px; width: 100%;">
                                <option value="comum">Usuário Comum (Engenheiro)</option>
                                <option value="admin">Administrador (Gestão Total)</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <label for="email">E-mail de Acesso *</label>
                        <div class="input-icon-wrapper">
                            <i class="fas fa-envelope"></i>
                            <input type="email" id="email" name="email" placeholder="engenheiro@rsfconstrucao.com" required>
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <label for="senha">Senha Inicial *</label>
                        <div class="input-icon-wrapper">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="senha" name="senha" placeholder="Mínimo 6 caracteres" required>
                        </div>
                    </div>

                </div>

                <div class="form-actions" style="margin-top: 20px;">
                    <button type="submit" class="btn-submit" style="width: 100%;"><i class="fas fa-save"></i> Criar Usuário</button>
                </div>
            </form>
        </div>

    </main>

    <script>
        function mascaraTelefone(input) {
            let v = input.value.replace(/\D/g, ''); 
            if (v.length > 11) v = v.substring(0, 11); 
            if (v.length > 10) {
                v = v.replace(/^(\d{2})(\d{5})(\d{4}).*/, '($1) $2-$3');
            } else if (v.length > 6) {
                v = v.replace(/^(\d{2})(\d{4})(\d{0,4}).*/, '($1) $2-$3');
            } else if (v.length > 2) {
                v = v.replace(/^(\d{2})(\d{0,5})/, '($1) $2');
            } else if (v.length > 0) {
                v = v.replace(/^(\d*)/, '($1');
            }
            input.value = v;
        }
    </script>
</body>
</html>