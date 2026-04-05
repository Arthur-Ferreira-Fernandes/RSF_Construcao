<?php
session_start();
require_once 'scripts/conexao.php';

if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    header("Location: login.php");
    exit;
}

// 2. Trava de Redirecionamento Invisível (Evita a tela branca sem CSS)
// Apenas "admin" pode aceder. Se não for admin, é redirecionado para o seu respectivo painel.
if ($_SESSION['nivel_acesso'] !== 'admin') {
    $destino = ($_SESSION['nivel_acesso'] === 'cliente') ? 'lista_projetos.php' : 'dashboard.php';
    header("Location: " . $destino);
    exit;
}

$mensagem = '';
$tipo_mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $telefone = trim($_POST['telefone']);
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);

    if (empty($nome) || empty($telefone) || empty($email)) {
        $mensagem = "Por favor, preencha todos os campos.";
        $tipo_mensagem = "erro";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensagem = "O formato do e-mail é inválido.";
        $tipo_mensagem = "erro";
    } else {
        try {
            $sql = "INSERT INTO engenheiros (nome, telefone, email) VALUES (:nome, :telefone, :email)";
            $stmt = $pdo->prepare($sql);
            
            $stmt->execute([
                ':nome' => $nome,
                ':telefone' => $telefone,
                ':email' => $email
            ]);

            $mensagem = "Engenheiro cadastrado com sucesso!";
            $tipo_mensagem = "sucesso";

        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $mensagem = "Este e-mail já está cadastrado para outro engenheiro.";
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
    <title>Cadastrar RT | RSF Engenharia</title>
    
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

        <div class="form-card" style="max-width: 600px; margin: 0 auto;">
            <h2><i class="fas fa-user-plus"></i> Cadastrar Engenheiro (RT)</h2>
            <p style="color: #aaa; font-size: 0.9rem; margin-top: -15px; margin-bottom: 25px;">
                Adicione um Responsável Técnico para vinculá-lo às obras.
            </p>
            
            <form method="POST" action="cadastrar_engenheiro.php">
                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="nome">Nome Completo *</label>
                    <div class="input-icon-wrapper">
                        <i class="fas fa-user"></i>
                        <input type="text" id="nome" name="nome" placeholder="Ex: Carlos Almeida" required autofocus>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="telefone">Telefone / WhatsApp *</label>
                    <div class="input-icon-wrapper">
                        <i class="fas fa-phone-alt"></i>
                        <input type="tel" id="telefone" name="telefone" placeholder="(11) 90000-0000" maxlength="15" oninput="mascaraTelefone(this)" required>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 25px;">
                    <label for="email">E-mail de Contato *</label>
                    <div class="input-icon-wrapper">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" placeholder="engenheiro@rsfconstrucao.com" required>
                    </div>
                </div>

                <div class="form-actions" style="margin-top: 10px;">
                    <button type="submit" class="btn-submit" style="width: 100%;"><i class="fas fa-save"></i> Adicionar à Equipe</button>
                </div>
            </form>
        </div>

    </main>

    <script>
        function mascaraTelefone(input) {
            let v = input.value.replace(/\D/g, ''); 
            
            if (v.length > 11) {
                v = v.substring(0, 11); 
            }

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