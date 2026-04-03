<?php
session_start();
require_once 'scripts/conexao.php';

// 1. Trava de segurança principal
if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    header("Location: login.php");
    exit;
}

$mensagem = '';
$tipo_mensagem = '';

// =========================================================================
// LÓGICA: ADICIONAR NOVO CLIENTE
// =========================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'adicionar_cliente') {
    $nome = trim($_POST['nome']);
    $documento = trim($_POST['documento']);
    $telefone = trim($_POST['telefone']);
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);

    if (!empty($nome)) {
        try {
            $sql = "INSERT INTO clientes (nome, documento, telefone, email) VALUES (:nome, :documento, :telefone, :email)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nome' => $nome,
                ':documento' => $documento,
                ':telefone' => $telefone,
                ':email' => $email
            ]);
            $mensagem = "Cliente cadastrado com sucesso!";
            $tipo_mensagem = "sucesso";
        } catch (PDOException $e) {
            $mensagem = "Erro ao cadastrar o cliente.";
            $tipo_mensagem = "erro";
        }
    } else {
        $mensagem = "O Nome do cliente é obrigatório.";
        $tipo_mensagem = "erro";
    }
}

// =========================================================================
// LÓGICA: EDITAR CLIENTE (VIA MODAL)
// =========================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'editar_cliente') {
    $id_cliente = filter_input(INPUT_POST, 'id_cliente', FILTER_VALIDATE_INT);
    $nome = trim($_POST['nome_edit']);
    $documento = trim($_POST['documento_edit']);
    $telefone = trim($_POST['telefone_edit']);
    $email = filter_var(trim($_POST['email_edit']), FILTER_SANITIZE_EMAIL);

    if ($id_cliente && !empty($nome)) {
        try {
            $sql = "UPDATE clientes SET nome = :nome, documento = :documento, telefone = :telefone, email = :email WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nome' => $nome,
                ':documento' => $documento,
                ':telefone' => $telefone,
                ':email' => $email,
                ':id' => $id_cliente
            ]);
            $mensagem = "Dados do cliente atualizados com sucesso!";
            $tipo_mensagem = "sucesso";
        } catch (PDOException $e) {
            $mensagem = "Erro ao atualizar os dados.";
            $tipo_mensagem = "erro";
        }
    }
}

// =========================================================================
// LÓGICA: ARQUIVAR CLIENTE (EXCLUSIVO PARA ADMIN)
// =========================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'arquivar_cliente') {
    $id_cliente = filter_input(INPUT_POST, 'id_cliente', FILTER_VALIDATE_INT);
    $novo_status = $_POST['novo_status']; // Pode ser 'Inativo' ou 'Arquivado' dependendo de como você quer chamar
    
    if ($_SESSION['nivel_acesso'] !== 'admin') {
        $mensagem = "Apenas administradores podem arquivar clientes.";
        $tipo_mensagem = "erro";
    } elseif ($id_cliente) {
        try {
            $stmt = $pdo->prepare("UPDATE clientes SET status = 'Arquivado' WHERE id = :id");
            $stmt->execute([':id' => $id_cliente]);
            $mensagem = "Cliente arquivado com sucesso.";
            $tipo_mensagem = "sucesso";
        } catch (PDOException $e) {
            $mensagem = "Erro ao arquivar o cliente.";
            $tipo_mensagem = "erro";
        }
    }
}

// =========================================================================
// CARREGA A LISTA DE CLIENTES E CONTA QUANTAS OBRAS CADA UM TEM
// =========================================================================
$clientes = [];
try {
    // Usamos subconsultas (SELECT dentro de SELECT) para calcular tudo com precisão absoluta,
    // garantindo que não haja duplicação de valores se o cliente tiver 10 obras.
    $sql_lista = "SELECT c.*, 
                 (SELECT COUNT(*) FROM projetos WHERE cliente_id = c.id) as total_obras,
                 (SELECT SUM(valor) FROM projetos WHERE cliente_id = c.id) as valor_total_contratado,
                 (SELECT SUM(r.valor) FROM recebimentos r INNER JOIN projetos p ON r.projeto_id = p.id WHERE p.cliente_id = c.id AND r.status = 'Ativo') as valor_total_pago
                 FROM clientes c 
                 WHERE c.status = 'Ativo' 
                 ORDER BY c.nome ASC";
                 
    $stmt = $pdo->query($sql_lista);
    $clientes = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Erro ao carregar a lista de clientes.");
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Base de Clientes | RSF Engenharia</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="../styles/lista_projetos.css">

    <style>
        /* Estilos adicionais para as Modais de Cliente */
        .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.8); z-index: 9999; align-items: center; justify-content: center; animation: fadeIn 0.3s; }
        .modal-content { background-color: var(--darker-bg); padding: 30px; border-radius: 8px; width: 100%; max-width: 500px; border-top: 4px solid #3498db; position: relative; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        .close-modal { position: absolute; top: 15px; right: 20px; color: #888; font-size: 1.5rem; font-weight: bold; cursor: pointer; transition: 0.3s; }
        .close-modal:hover { color: #e74c3c; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }
        
        .form-group label { color: #aaa; font-size: 0.85rem; font-weight: bold; display: block; margin-bottom: 5px; }
        .form-group input { width: 100%; padding: 10px; background: #222; border: 1px solid #444; color: #fff; border-radius: 4px; box-sizing: border-box; }
        .form-group input:focus { outline: none; border-color: #3498db; }
        
        .btn-acao-sm { background: none; border: none; cursor: pointer; font-size: 1.1rem; padding: 5px; margin-right: 15px; transition: 0.3s; }
        .btn-edit { color: #3498db; }
        .btn-edit:hover { color: #2980b9; transform: scale(1.1); }
        .btn-arq { color: #e74c3c; margin-right: 0; }
        .btn-arq:hover { color: #c0392b; transform: scale(1.1); }
        
        .badge-obras { background-color: #2ecc71; color: #111; padding: 4px 8px; border-radius: 12px; font-size: 0.75rem; font-weight: bold; }
        .badge-zero { background-color: #555; color: #aaa; }
    </style>
</head>
<body>

    <?php include 'header.php'; ?>

    <main class="container">
        
        <?php if (!empty($mensagem)): ?>
            <div style="background-color: <?= $tipo_mensagem === 'sucesso' ? 'rgba(46, 204, 113, 0.2)' : 'rgba(231, 76, 60, 0.2)' ?>; color: <?= $tipo_mensagem === 'sucesso' ? '#2ecc71' : '#e74c3c' ?>; border-left: 4px solid <?= $tipo_mensagem === 'sucesso' ? '#2ecc71' : '#e74c3c' ?>; padding: 15px; border-radius: 4px; margin-bottom: 20px; font-weight: bold;">
                <i class="fas <?= $tipo_mensagem === 'sucesso' ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i> <?= htmlspecialchars($mensagem) ?>
            </div>
        <?php endif; ?>

        <div class="header-acoes">
            <h2><i class="fas fa-handshake"></i> Base de Clientes (CRM)</h2>
            <button class="btn-novo" onclick="abrirModalAdd()"><i class="fas fa-user-plus"></i> Novo Cliente</button>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Nome / Contato</th>
                        <th style="text-align: center;">Obras</th>
                        <th>Total Contratado</th>
                        <th>Valor Recebido</th>
                        <th>Saldo a Receber</th>
                        <th style="text-align: right;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($clientes)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; color: #888; padding: 30px;">
                                <i class="fas fa-users-slash fa-2x" style="margin-bottom: 10px; display: block;"></i>
                                Nenhum cliente cadastrado no sistema ainda.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php 
                        foreach ($clientes as $cli): 
                            $contratado = (float)$cli['valor_total_contratado'];
                            $pago = (float)$cli['valor_total_pago'];
                            $saldo = $contratado - $pago;
                        ?>
                            <tr>
                                <td>
                                    <div style="font-weight: bold; color: #3498db; font-size: 1.05rem;"><?= htmlspecialchars($cli['nome']) ?></div>
                                    <div style="font-size: 0.8rem; color: #aaa; margin-top: 4px;">
                                        <i class="fas fa-id-card"></i> <?= htmlspecialchars($cli['documento'] ?: 'Sem doc.') ?> | 
                                        <i class="fas fa-phone-alt"></i> <?= htmlspecialchars($cli['telefone'] ?: '--') ?>
                                    </div>
                                </td>
                                
                                <td style="text-align: center;">
                                    <span class="badge-obras <?= $cli['total_obras'] == 0 ? 'badge-zero' : '' ?>">
                                        <?= $cli['total_obras'] ?>
                                    </span>
                                </td>
                                
                                <td style="color: #ccc; font-weight: bold;">
                                    R$ <?= number_format($contratado, 2, ',', '.') ?>
                                </td>
                                
                                <td style="color: #2ecc71; font-weight: bold;">
                                    R$ <?= number_format($pago, 2, ',', '.') ?>
                                </td>
                                
                                <td style="color: <?= $saldo > 0 ? '#e67e22' : '#888' ?>; font-weight: bold;">
                                    R$ <?= number_format(max(0, $saldo), 2, ',', '.') ?>
                                </td>
                                
                                <td style="text-align: right; white-space: nowrap;">
                                    
                                    <button type="button" class="btn-acao-sm btn-edit" title="Editar Cliente" 
                                            onclick="abrirModalEdit(<?= $cli['id'] ?>, '<?= htmlspecialchars($cli['nome'], ENT_QUOTES) ?>', '<?= htmlspecialchars($cli['documento'], ENT_QUOTES) ?>', '<?= htmlspecialchars($cli['telefone'], ENT_QUOTES) ?>', '<?= htmlspecialchars($cli['email'], ENT_QUOTES) ?>')">
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    <?php if (isset($_SESSION['nivel_acesso']) && $_SESSION['nivel_acesso'] === 'admin'): ?>
                                    <form method="POST" style="display:inline;" action="clientes.php">
                                        <input type="hidden" name="acao" value="arquivar_cliente">
                                        <input type="hidden" name="id_cliente" value="<?= $cli['id'] ?>">
                                        <button type="submit" class="btn-acao-sm btn-arq" title="Arquivar Cliente" onclick="return confirm('Deseja arquivar este cliente?');">
                                            <i class="fas fa-archive"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>

                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div id="modalAddCliente" class="modal-overlay">
            <div class="modal-content">
                <span class="close-modal" onclick="fecharModalAdd()">&times;</span>
                <h2 style="color: #3498db; margin-top: 0;"><i class="fas fa-user-plus"></i> Cadastrar Cliente</h2>
                
                <form method="POST" action="clientes.php">
                    <input type="hidden" name="acao" value="adicionar_cliente">
                    
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label>Nome Completo ou Razão Social *</label>
                        <input type="text" name="nome" required autofocus>
                    </div>

                    <div class="form-group" style="margin-bottom: 15px;">
                        <label>CPF ou CNPJ</label>
                        <input type="text" name="documento" onkeyup="mascaraDoc(this)">
                    </div>

                    <div class="form-group" style="margin-bottom: 15px;">
                        <label>Telefone / WhatsApp</label>
                        <input type="text" name="telefone" onkeyup="mascaraTelefone(this)" maxlength="15">
                    </div>

                    <div class="form-group" style="margin-bottom: 25px;">
                        <label>E-mail de Contato</label>
                        <input type="email" name="email">
                    </div>

                    <button type="submit" style="background-color: #3498db; color: #fff; width: 100%; border: none; padding: 12px; font-weight: bold; border-radius: 4px; cursor: pointer;">
                        <i class="fas fa-save"></i> Salvar Cliente
                    </button>
                </form>
            </div>
        </div>

        <div id="modalEditCliente" class="modal-overlay">
            <div class="modal-content">
                <span class="close-modal" onclick="fecharModalEdit()">&times;</span>
                <h2 style="color: #3498db; margin-top: 0;"><i class="fas fa-edit"></i> Editar Cliente</h2>
                
                <form method="POST" action="clientes.php">
                    <input type="hidden" name="acao" value="editar_cliente">
                    <input type="hidden" name="id_cliente" id="edit_id">
                    
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label>Nome Completo ou Razão Social *</label>
                        <input type="text" name="nome_edit" id="edit_nome" required>
                    </div>

                    <div class="form-group" style="margin-bottom: 15px;">
                        <label>CPF ou CNPJ</label>
                        <input type="text" name="documento_edit" id="edit_documento" onkeyup="mascaraDoc(this)">
                    </div>

                    <div class="form-group" style="margin-bottom: 15px;">
                        <label>Telefone / WhatsApp</label>
                        <input type="text" name="telefone_edit" id="edit_telefone" onkeyup="mascaraTelefone(this)" maxlength="15">
                    </div>

                    <div class="form-group" style="margin-bottom: 25px;">
                        <label>E-mail de Contato</label>
                        <input type="email" name="email_edit" id="edit_email">
                    </div>

                    <button type="submit" style="background-color: #3498db; color: #fff; width: 100%; border: none; padding: 12px; font-weight: bold; border-radius: 4px; cursor: pointer;">
                        <i class="fas fa-sync-alt"></i> Atualizar Dados
                    </button>
                </form>
            </div>
        </div>

    </main>

    <script>
        // Funções para controle das Modais
        function abrirModalAdd() { document.getElementById('modalAddCliente').style.display = 'flex'; }
        function fecharModalAdd() { document.getElementById('modalAddCliente').style.display = 'none'; }
        
        function abrirModalEdit(id, nome, documento, telefone, email) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_nome').value = nome;
            document.getElementById('edit_documento').value = documento;
            document.getElementById('edit_telefone').value = telefone;
            document.getElementById('edit_email').value = email;
            document.getElementById('modalEditCliente').style.display = 'flex';
        }
        function fecharModalEdit() { document.getElementById('modalEditCliente').style.display = 'none'; }

        // Fecha as modais se clicar fora delas
        window.onclick = function(event) {
            let mAdd = document.getElementById('modalAddCliente');
            let mEdit = document.getElementById('modalEditCliente');
            if (event.target == mAdd) mAdd.style.display = "none";
            if (event.target == mEdit) mEdit.style.display = "none";
        }

        // Máscara de Telefone que já usávamos
        function mascaraTelefone(input) {
            let v = input.value.replace(/\D/g, ''); 
            if (v.length > 11) v = v.substring(0, 11); 
            if (v.length > 10) { v = v.replace(/^(\d{2})(\d{5})(\d{4}).*/, '($1) $2-$3'); } 
            else if (v.length > 6) { v = v.replace(/^(\d{2})(\d{4})(\d{0,4}).*/, '($1) $2-$3'); } 
            else if (v.length > 2) { v = v.replace(/^(\d{2})(\d{0,5})/, '($1) $2'); } 
            else if (v.length > 0) { v = v.replace(/^(\d*)/, '($1'); }
            input.value = v;
        }

        // Máscara inteligente para CPF ou CNPJ
        function mascaraDoc(input) {
            let v = input.value.replace(/\D/g, '');
            if (v.length <= 11) { // CPF
                v = v.replace(/(\d{3})(\d)/, '$1.$2');
                v = v.replace(/(\d{3})(\d)/, '$1.$2');
                v = v.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            } else { // CNPJ
                v = v.substring(0, 14);
                v = v.replace(/^(\d{2})(\d)/, '$1.$2');
                v = v.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
                v = v.replace(/\.(\d{3})(\d)/, '.$1/$2');
                v = v.replace(/(\d{4})(\d)/, '$1-$2');
            }
            input.value = v;
        }
    </script>
</body>
</html>