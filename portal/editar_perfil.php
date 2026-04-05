<?php
session_start();
require_once 'scripts/conexao.php';

if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    header("Location: login.php");
    exit;
}

$id_sessao = $_SESSION['usuario_id'];
$nivel_acesso = $_SESSION['nivel_acesso'] ?? '';

// INTELIGÊNCIA DE TABELAS E COLUNAS
$tabela = ($nivel_acesso === 'cliente') ? 'clientes' : 'usuarios';
$coluna_senha = ($nivel_acesso === 'cliente') ? 'senha' : 'senha_hash';

$mensagem = '';
$tipo_mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha_nova = $_POST['senha_nova'];

    if (empty($nome) || empty($email)) {
        $mensagem = "Nome e E-mail são obrigatórios.";
        $tipo_mensagem = "erro";
    } else {
        try {
            if (!empty($senha_nova)) {
                $senha_hash = password_hash($senha_nova, PASSWORD_DEFAULT);
                // Utiliza a coluna correta de acordo com quem está a fazer login
                $stmt = $pdo->prepare("UPDATE $tabela SET nome = ?, email = ?, $coluna_senha = ? WHERE id = ?");
                $stmt->execute([$nome, $email, $senha_hash, $id_sessao]);
            } else {
                $stmt = $pdo->prepare("UPDATE $tabela SET nome = ?, email = ? WHERE id = ?");
                $stmt->execute([$nome, $email, $id_sessao]);
            }
            
            $_SESSION['nome_usuario'] = $nome;
            $mensagem = "Perfil atualizado com sucesso!";
            $tipo_mensagem = "sucesso";
        } catch (PDOException $e) {
            $mensagem = "Erro ao atualizar. O e-mail já pode estar em uso.";
            $tipo_mensagem = "erro";
        }
    }
}

try {
    $stmt = $pdo->prepare("SELECT nome, email FROM $tabela WHERE id = ? LIMIT 1");
    $stmt->execute([$id_sessao]);
    $perfil = $stmt->fetch();
    if (!$perfil) { die("Perfil não encontrado na base de dados."); }
} catch (PDOException $e) {
    die("Erro ao carregar os dados do perfil.");
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
    <link rel="stylesheet" href="../styles/dashboard.css">
    
    <style>
        .form-card { 
            background: #1a1a1a; 
            padding: 30px; 
            border-radius: 8px; 
            border-top: 4px solid #3498db; 
            max-width: 600px; 
            margin: 40px auto; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.3); 
        }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; color: #aaa; margin-bottom: 8px; font-weight: bold; font-size: 0.9rem; }
        .form-group input { width: 100%; padding: 12px; background: #222; border: 1px solid #444; color: #fff; border-radius: 4px; box-sizing: border-box; }
        .form-group input:focus { border-color: #3498db; outline: none; }
        .btn-salvar { background: #3498db; color: #fff; border: none; padding: 15px; width: 100%; font-weight: bold; font-size: 1.1rem; border-radius: 4px; cursor: pointer; transition: 0.3s; display: flex; justify-content: center; align-items: center; gap: 10px;}
        .btn-salvar:hover { background: #2980b9; }
    </style>
</head>
<body>

    <?php include 'header.php'; ?>

    <main class="container" style="padding: 20px;">
        
        <?php if (!empty($mensagem)): ?>
            <div style="max-width: 600px; margin: 0 auto 20px auto; padding: 15px; border-radius: 4px; font-weight: bold; background-color: <?= $tipo_mensagem === 'sucesso' ? 'rgba(46, 204, 113, 0.2)' : 'rgba(231, 76, 60, 0.2)' ?>; color: <?= $tipo_mensagem === 'sucesso' ? '#2ecc71' : '#e74c3c' ?>; border-left: 4px solid <?= $tipo_mensagem === 'sucesso' ? '#2ecc71' : '#e74c3c' ?>;">
                <i class="fas <?= $tipo_mensagem === 'sucesso' ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i> <?= htmlspecialchars($mensagem) ?>
            </div>
        <?php endif; ?>

        <div class="form-card">
            <h2 style="margin-top: 0; color: #3498db; text-align: center;"><i class="fas fa-user-circle"></i> Meu Perfil</h2>
            
            <form method="POST" action="editar_perfil.php">
                <div class="form-group">
                    <label>Nome Completo</label>
                    <input type="text" name="nome" value="<?= htmlspecialchars($perfil['nome'] ?? '') ?>" required>
                </div>
                
                <div class="form-group">
                    <label>E-mail de Login</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($perfil['email'] ?? '') ?>" required>
                </div>
                
                <div class="form-group" style="margin-top: 35px; border-top: 1px dashed #444; padding-top: 20px;">
                    <label style="color: #f1c40f;"><i class="fas fa-lock"></i> Alterar Senha (Opcional)</label>
                    <input type="password" name="senha_nova" placeholder="Digite a nova senha">
                    <small style="color: #888; font-size: 0.8rem; margin-top: 5px; display: block;"><i class="fas fa-info-circle"></i> Deixe o campo em branco se desejar manter a senha atual.</small>
                </div>

                <button type="submit" class="btn-salvar"><i class="fas fa-save"></i> Salvar Alterações</button>
            </form>
        </div>

    </main>

</body>
</html>