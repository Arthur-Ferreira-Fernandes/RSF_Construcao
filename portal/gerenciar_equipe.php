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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao'])) {
    
    if ($_POST['acao'] === 'mudar_nivel') {
        $id_alvo = filter_input(INPUT_POST, 'id_usuario', FILTER_VALIDATE_INT);
        $novo_nivel = $_POST['novo_nivel'];

        if ($id_alvo == $_SESSION['usuario_id']) {
            $mensagem = "Você não pode alterar seu próprio nível de acesso.";
            $tipo_mensagem = "erro";
        } elseif ($id_alvo && in_array($novo_nivel, ['admin', 'comum'])) {
            try {
                $stmt = $pdo->prepare("UPDATE usuarios SET nivel_acesso = :nivel WHERE id = :id");
                $stmt->execute([':nivel' => $novo_nivel, ':id' => $id_alvo]);
                $mensagem = "Permissão atualizada com sucesso!";
                $tipo_mensagem = "sucesso";
            } catch (PDOException $e) {
                $mensagem = "Erro ao atualizar permissões.";
                $tipo_mensagem = "erro";
            }
        }
    }

    if ($_POST['acao'] === 'alternar_status') {
        $id_alvo = filter_input(INPUT_POST, 'id_usuario', FILTER_VALIDATE_INT);
        $novo_status = $_POST['novo_status'];

        if ($id_alvo == $_SESSION['usuario_id']) {
            $mensagem = "Você não pode arquivar sua própria conta de administrador.";
            $tipo_mensagem = "erro";
        } elseif ($id_alvo && in_array($novo_status, ['Ativo', 'Inativo'])) {
            try {
                $stmt = $pdo->prepare("UPDATE usuarios SET status = :status WHERE id = :id");
                $stmt->execute([':status' => $novo_status, ':id' => $id_alvo]);
                
                $mensagem = $novo_status === 'Inativo' ? "Usuário arquivado e acesso revogado." : "Acesso do usuário restaurado!";
                $tipo_mensagem = "sucesso";
            } catch (PDOException $e) {
                $mensagem = "Erro ao alterar o status do usuário.";
                $tipo_mensagem = "erro";
            }
        }
    }
}

$lista_usuarios = [];
try {
    $stmt = $pdo->query("SELECT id, nome, email, telefone, nivel_acesso, status FROM usuarios ORDER BY status ASC, nome ASC");
    $lista_usuarios = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Erro ao carregar a equipe.");
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Equipe | RSF Engenharia</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../styles/lista_projetos.css">
    <link rel="icon" type="image/png" href="../img/logo.png">

    
    <style>
        .form-inline { display: inline-flex; align-items: center; gap: 5px; margin-right: 10px; }
        .select-nivel { padding: 6px 10px; background-color: #222; color: #fff; border: 1px solid #444; border-radius: 4px; font-size: 0.85rem; }
        .btn-acao { border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-weight: bold; font-size: 0.8rem; transition: 0.3s; }
        
        .btn-salvar-nivel { background-color: var(--primary-yellow); color: #111; }
        .btn-salvar-nivel:hover { background-color: #e6b800; }
        
        .btn-arquivar { background-color: transparent; color: #e74c3c; border: 1px solid #e74c3c; }
        .btn-arquivar:hover { background-color: #e74c3c; color: #fff; }
        
        .btn-restaurar { background-color: transparent; color: #2ecc71; border: 1px solid #2ecc71; }
        .btn-restaurar:hover { background-color: #2ecc71; color: #fff; }

        .badge-admin { background-color: rgba(231, 76, 60, 0.2); color: #e74c3c; border: 1px solid #e74c3c; padding: 4px 8px; border-radius: 12px; font-size: 0.75rem; font-weight: bold;}
        .badge-comum { background-color: rgba(52, 152, 219, 0.2); color: #3498db; border: 1px solid #3498db; padding: 4px 8px; border-radius: 12px; font-size: 0.75rem; font-weight: bold;}
        .badge-inativo { background-color: #333; color: #aaa; border: 1px solid #555; padding: 4px 8px; border-radius: 12px; font-size: 0.75rem; font-weight: bold; }

        .linha-arquivada { opacity: 0.5; background-color: #151515 !important; }
        .linha-arquivada td { text-decoration: line-through; color: #888; }
        .linha-arquivada td.acoes-livres { text-decoration: none; } /* Mantém os botões sem risco */

        .alerta { padding: 15px; border-radius: 4px; margin-bottom: 20px; font-weight: bold; }
        .alerta.sucesso { background-color: rgba(46, 204, 113, 0.2); color: #2ecc71; border-left: 4px solid #2ecc71; }
        .alerta.erro { background-color: rgba(231, 76, 60, 0.2); color: #e74c3c; border-left: 4px solid #e74c3c; }
        
        .acoes-container { display: flex; align-items: center; justify-content: flex-end; gap: 10px; }
    </style>
</head>
<body>

    <?php include 'header.php'; ?>

    <main class="container">
        
        <?php if (!empty($mensagem)): ?>
            <div class="alerta <?= $tipo_mensagem ?>">
                <?= htmlspecialchars($mensagem) ?>
            </div>
        <?php endif; ?>

        <div class="header-acoes">
            <h2><i class="fas fa-users-cog"></i> Gestão de Acessos e Equipe</h2>
            <a href="cadastrar_usuario.php" class="btn-novo"><i class="fas fa-user-plus"></i> Novo Usuário</a>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Contato</th>
                        <th>Status / Nível</th>
                        <th style="text-align: right;">Ações de Controle</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lista_usuarios as $user): ?>
                        <tr class="<?= $user['status'] === 'Inativo' ? 'linha-arquivada' : '' ?>">
                            <td style="font-weight: bold;"><?= htmlspecialchars($user['nome']) ?></td>
                            
                            <td>
                                <div style="font-size: 0.85rem;"><i class="fas fa-envelope"></i> <?= htmlspecialchars($user['email']) ?></div>
                                <div style="font-size: 0.85rem; margin-top: 4px;"><i class="fas fa-phone-alt"></i> <?= htmlspecialchars($user['telefone'] ?? '--') ?></div>
                            </td>
                            
                            <td>
                                <?php if ($user['status'] === 'Inativo'): ?>
                                    <span class="badge-inativo"><i class="fas fa-archive"></i> Arquivado / Bloqueado</span>
                                <?php elseif ($user['nivel_acesso'] === 'admin'): ?>
                                    <span class="badge-admin"><i class="fas fa-shield-alt"></i> Admin</span>
                                <?php else: ?>
                                    <span class="badge-comum"><i class="fas fa-user"></i> Comum</span>
                                <?php endif; ?>
                            </td>
                            
                            <td class="acoes-livres">
                                <div class="acoes-container">
                                    <?php if ($user['id'] == $_SESSION['usuario_id']): ?>
                                        <span style="color: #666; font-size: 0.85rem;">(Seu próprio perfil)</span>
                                    <?php else: ?>
                                        
                                        <?php if ($user['status'] === 'Ativo'): ?>
                                        <form method="POST" action="gerenciar_equipe.php" class="form-inline">
                                            <input type="hidden" name="acao" value="mudar_nivel">
                                            <input type="hidden" name="id_usuario" value="<?= $user['id'] ?>">
                                            <select name="novo_nivel" class="select-nivel">
                                                <option value="admin" <?= ($user['nivel_acesso'] == 'admin') ? 'selected' : '' ?>>Admin</option>
                                                <option value="comum" <?= ($user['nivel_acesso'] == 'comum') ? 'selected' : '' ?>>Comum</option>
                                            </select>
                                            <button type="submit" class="btn-acao btn-salvar-nivel" title="Salvar Nível"><i class="fas fa-check"></i></button>
                                        </form>
                                        <?php endif; ?>

                                        <form method="POST" action="gerenciar_equipe.php" class="form-inline" style="margin-right: 0;">
                                            <input type="hidden" name="acao" value="alternar_status">
                                            <input type="hidden" name="id_usuario" value="<?= $user['id'] ?>">
                                            
                                            <?php if ($user['status'] === 'Ativo'): ?>
                                                <input type="hidden" name="novo_status" value="Inativo">
                                                <button type="submit" class="btn-acao btn-arquivar" onclick="return confirm('Deseja arquivar este usuário? Ele perderá o acesso ao sistema.');">
                                                    <i class="fas fa-archive"></i> Arquivar
                                                </button>
                                            <?php else: ?>
                                                <input type="hidden" name="novo_status" value="Ativo">
                                                <button type="submit" class="btn-acao btn-restaurar">
                                                    <i class="fas fa-undo-alt"></i> Restaurar Acesso
                                                </button>
                                            <?php endif; ?>
                                        </form>

                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </main>

</body>
</html>