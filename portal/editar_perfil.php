<?php
session_start();
require_once 'scripts/conexao.php';

if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    header("Location: login.php");
    exit;
}

$mensagem = '';
$tipo_mensagem = '';
$id_usuario = $_SESSION['usuario_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $telefone = trim($_POST['telefone']);
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $nova_senha = $_POST['nova_senha']; 

    if (empty($nome) || empty($telefone) || empty($email)) {
        $mensagem = "Nome, telefone e e-mail são obrigatórios.";
        $tipo_mensagem = "erro";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensagem = "Formato de e-mail inválido.";
        $tipo_mensagem = "erro";
    } else {
        try {
            if (!empty($nova_senha)) {
                if (strlen($nova_senha) < 6) {
                    throw new Exception("A nova senha deve ter pelo menos 6 caracteres.");
                }
                
                $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
                $sql = "UPDATE usuarios SET nome = :nome, telefone = :telefone, email = :email, senha_hash = :senha WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':nome' => $nome, ':telefone' => $telefone, ':email' => $email, ':senha' => $senha_hash, ':id' => $id_usuario]);
            } else {
                $sql = "UPDATE usuarios SET nome = :nome, telefone = :telefone, email = :email WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':nome' => $nome, ':telefone' => $telefone, ':email' => $email, ':id' => $id_usuario]);
            }

            $_SESSION['usuario_nome'] = $nome;

            $mensagem = "Seu perfil foi atualizado com sucesso!";
            $tipo_mensagem = "sucesso";

        } catch (Exception $e) {
            $mensagem = $e->getMessage();
            $tipo_mensagem = "erro";
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $mensagem = "Este e-mail já está sendo usado por outra pessoa.";
            } else {
                $mensagem = "Erro ao atualizar o perfil.";
            }
            $tipo_mensagem = "erro";
        }
    }
}

try {
    $stmt = $pdo->prepare("SELECT nome, email, telefone FROM usuarios WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id_usuario]);
    $dados_usuario = $stmt->fetch();
} catch (PDOException $e) {
    die("Erro ao carregar seus dados.");
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Perfil | RSF Engenharia</title>
    
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
            <h2 style="color: #3498db;"><i class="fas fa-user-edit"></i> Configurações da Minha Conta</h2>
            <p style="color: #aaa; font-size: 0.9rem; margin-top: -15px; margin-bottom: 25px;">
                Mantenha suas informações de contato sempre atualizadas.
            </p>
            
            <form method="POST" action="editar_perfil.php">
                <div class="form-grid">
                    
                    <div class="form-group full-width" style="margin-bottom: 5px;">
                        <label for="nome">Nome Completo *</label>
                        <div class="input-icon-wrapper">
                            <i class="fas fa-user"></i>
                            <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($dados_usuario['nome']) ?>" required>
                        </div>
                    </div>

                    <div class="form-group full-width" style="margin-bottom: 5px;">
                        <label for="telefone">Telefone / WhatsApp *</label>
                        <div class="input-icon-wrapper">
                            <i class="fas fa-phone-alt"></i>
                            <input type="tel" id="telefone" name="telefone" value="<?= htmlspecialchars($dados_usuario['telefone'] ?? '') ?>" maxlength="15" oninput="mascaraTelefone(this)" required>
                        </div>
                    </div>

                    <div class="form-group full-width" style="margin-bottom: 5px;">
                        <label for="email">E-mail de Acesso *</label>
                        <div class="input-icon-wrapper">
                            <i class="fas fa-envelope"></i>
                            <input type="email" id="email" name="email" value="<?= htmlspecialchars($dados_usuario['email']) ?>" required>
                        </div>
                    </div>

                    <div class="form-group full-width" style="margin-bottom: 10px;">
                        <label for="nova_senha">Nova Senha (Opcional)</label>
                        <div class="input-icon-wrapper">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="nova_senha" name="nova_senha" placeholder="Deixe em branco para não alterar">
                        </div>
                        <small style="color: #888; margin-top: 5px;"><i class="fas fa-info-circle"></i> Só preencha se quiser mudar a senha atual.</small>
                    </div>

                </div>

                <div class="form-actions" style="margin-top: 20px;">
                    <button type="submit" class="btn-submit" style="background-color: #3498db; color: #fff; width: 100%;">
                        <i class="fas fa-sync-alt"></i> Atualizar Meu Perfil
                    </button>
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