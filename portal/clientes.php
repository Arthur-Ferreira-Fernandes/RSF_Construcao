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

$mensagem = ''; $tipo_mensagem = '';
// =========================================================================
// LÓGICAS DE AÇÃO (CADASTRAR, EDITAR E ARQUIVAR)
// =========================================================================

// CADASTRAR CLIENTE (Com Senha para o Portal)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'cadastrar_cliente') {
    $nome = trim($_POST['nome']);
    $documento = trim($_POST['documento']);
    $telefone = trim($_POST['telefone']);
    $email = trim($_POST['email']);
    $senha_plana = $_POST['senha'];

    if (!empty($nome)) {
        // Criptografa a senha para o cliente poder logar no portal
        $senha_hash = !empty($senha_plana) ? password_hash($senha_plana, PASSWORD_DEFAULT) : '';
        
        try {
            $stmt = $pdo->prepare("INSERT INTO clientes (nome, documento, telefone, email, senha) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$nome, $documento, $telefone, $email, $senha_hash]);
            $mensagem = "Cliente cadastrado com sucesso! Ele já pode acessar o portal."; $tipo_mensagem = "sucesso";
        } catch (PDOException $e) {
            $mensagem = "Erro ao cadastrar. O e-mail ou documento já pode estar em uso."; $tipo_mensagem = "erro";
        }
    }
}

// EDITAR CLIENTE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'editar_cliente') {
    $id = $_POST['id_cliente'];
    $nome = trim($_POST['nome_edit']);
    $documento = trim($_POST['documento_edit']);
    $telefone = trim($_POST['telefone_edit']);
    $email = trim($_POST['email_edit']);
    $nova_senha = $_POST['senha_edit'];

    if (!empty($id) && !empty($nome)) {
        try {
            if (!empty($nova_senha)) {
                // Atualiza também a senha se o admin digitou uma nova
                $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE clientes SET nome = ?, documento = ?, telefone = ?, email = ?, senha = ? WHERE id = ?");
                $stmt->execute([$nome, $documento, $telefone, $email, $senha_hash, $id]);
                $mensagem = "Dados e senha atualizados!"; 
            } else {
                // Atualiza apenas os dados mantendo a senha antiga
                $stmt = $pdo->prepare("UPDATE clientes SET nome = ?, documento = ?, telefone = ?, email = ? WHERE id = ?");
                $stmt->execute([$nome, $documento, $telefone, $email, $id]);
                $mensagem = "Dados atualizados com sucesso!";
            }
            $tipo_mensagem = "sucesso";
        } catch (PDOException $e) {
            $mensagem = "Erro ao atualizar cliente."; $tipo_mensagem = "erro";
        }
    }
}

// ARQUIVAR CLIENTE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'arquivar_cliente') {
    $id = filter_input(INPUT_POST, 'id_cliente', FILTER_VALIDATE_INT);
    if ($id) {
        $pdo->prepare("UPDATE clientes SET status = 'Inativo' WHERE id = ?")->execute([$id]);
        $mensagem = "Cliente arquivado."; $tipo_mensagem = "sucesso";
    }
}

// =========================================================================
// CARREGA A LISTA DE CLIENTES COM INTELIGÊNCIA FINANCEIRA (LTV)
// =========================================================================
$clientes = [];
try {
    $sql_lista = "SELECT c.*, 
                 (SELECT COUNT(*) FROM projetos WHERE cliente_id = c.id) as total_obras,
                 (SELECT SUM(valor) FROM projetos WHERE cliente_id = c.id) as valor_total_contratado,
                 (SELECT SUM(r.valor) FROM recebimentos r INNER JOIN projetos p ON r.projeto_id = p.id WHERE p.cliente_id = c.id AND r.status = 'Ativo') as valor_total_pago
                 FROM clientes c 
                 WHERE c.status = 'Ativo' 
                 ORDER BY c.nome ASC";
                 
    $stmt = $pdo->query($sql_lista);
    $clientes = $stmt->fetchAll();
} catch (PDOException $e) { die("Erro ao carregar a lista de clientes."); }
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>CRM de Clientes | RSF Engenharia</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../styles/dashboard.css">
    <style>
        .table-wrapper { overflow-x: auto; background-color: #1a1a1a; border-radius: 8px; border: 1px solid #333; margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th, td { padding: 15px; border-bottom: 1px solid #333; }
        th { background-color: #222; color: #aaa; font-size: 0.85rem; text-transform: uppercase; }
        tr:hover { background-color: #222; }
        .btn-acao-sm { background: none; border: none; cursor: pointer; font-size: 1.1rem; padding: 5px; transition: 0.3s; margin-left: 10px; }
        .btn-edit { color: #3498db; } .btn-edit:hover { color: #2980b9; transform: scale(1.1); }
        .btn-arq { color: #e74c3c; } .btn-arq:hover { color: #c0392b; transform: scale(1.1); }
        .badge-obras { background-color: #333; padding: 4px 10px; border-radius: 20px; font-weight: bold; color: #fff; font-size: 0.85rem; }
        .badge-zero { background-color: transparent; border: 1px solid #444; color: #666; }
        
        /* Modais */
        .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 9999; align-items: center; justify-content: center; }
        .modal-content { background: #1a1a1a; padding: 30px; border-radius: 8px; width: 100%; max-width: 500px; border-top: 4px solid #f1c40f; position: relative; }
        .close-modal { position: absolute; top: 15px; right: 20px; color: #888; font-size: 1.5rem; cursor: pointer; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; color: #aaa; font-size: 0.85rem; margin-bottom: 5px; font-weight: bold; }
        .form-group input { width: 100%; padding: 12px; background: #222; border: 1px solid #444; color: #fff; border-radius: 4px; box-sizing: border-box; }
    </style>
</head>
<body>

    <?php include 'header.php'; ?>

    <main class="container" style="max-width: 1200px; margin: 0 auto; padding: 20px;">
        
        <?php if (!empty($mensagem)): ?>
            <div style="margin-bottom: 20px; padding: 15px; border-radius: 4px; font-weight: bold; background-color: <?= $tipo_mensagem === 'sucesso' ? 'rgba(46, 204, 113, 0.2)' : 'rgba(231, 76, 60, 0.2)' ?>; color: <?= $tipo_mensagem === 'sucesso' ? '#2ecc71' : '#e74c3c' ?>; border-left: 4px solid <?= $tipo_mensagem === 'sucesso' ? '#2ecc71' : '#e74c3c' ?>;">
                <?= htmlspecialchars($mensagem) ?>
            </div>
        <?php endif; ?>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h1 style="margin: 0;"><i class="fas fa-handshake" style="color: #f1c40f;"></i> Carteira de Clientes (CRM)</h1>
            <button onclick="document.getElementById('modalAddCliente').style.display='flex'" style="background: #f1c40f; color: #111; border: none; padding: 10px 20px; font-weight: bold; border-radius: 4px; cursor: pointer;"><i class="fas fa-user-plus"></i> Novo Cliente</button>
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
                        <tr><td colspan="6" style="text-align: center; color: #888; padding: 30px;">Nenhum cliente cadastrado.</td></tr>
                    <?php else: foreach ($clientes as $cli): 
                        $contratado = (float)$cli['valor_total_contratado'];
                        $pago = (float)$cli['valor_total_pago'];
                        $saldo = $contratado - $pago;
                    ?>
                        <tr>
                            <td>
                                <div style="font-weight: bold; color: #3498db; font-size: 1.05rem;"><?= htmlspecialchars($cli['nome']) ?></div>
                                <div style="font-size: 0.8rem; color: #aaa; margin-top: 4px;">
                                    <i class="fas fa-envelope"></i> <?= htmlspecialchars($cli['email'] ?: 'Sem e-mail') ?> <br>
                                    <i class="fas fa-phone-alt"></i> <?= htmlspecialchars($cli['telefone'] ?: '--') ?> | 
                                    <i class="fas fa-id-card"></i> <?= htmlspecialchars($cli['documento'] ?: '--') ?>
                                </div>
                            </td>
                            <td style="text-align: center;"><span class="badge-obras <?= $cli['total_obras'] == 0 ? 'badge-zero' : '' ?>"><?= $cli['total_obras'] ?></span></td>
                            <td style="color: #ccc; font-weight: bold;">R$ <?= number_format($contratado, 2, ',', '.') ?></td>
                            <td style="color: #2ecc71; font-weight: bold;">R$ <?= number_format($pago, 2, ',', '.') ?></td>
                            <td style="color: <?= $saldo > 0 ? '#e67e22' : '#888' ?>; font-weight: bold;">R$ <?= number_format(max(0, $saldo), 2, ',', '.') ?></td>
                            <td style="text-align: right; white-space: nowrap;">
                                <button class="btn-acao-sm btn-edit" title="Editar Cliente e Senha" onclick="abrirModalEdit(<?= $cli['id'] ?>, '<?= htmlspecialchars($cli['nome'], ENT_QUOTES) ?>', '<?= htmlspecialchars($cli['documento'], ENT_QUOTES) ?>', '<?= htmlspecialchars($cli['telefone'], ENT_QUOTES) ?>', '<?= htmlspecialchars($cli['email'], ENT_QUOTES) ?>')"><i class="fas fa-edit"></i></button>
                                <form method="POST" style="display:inline;" action="clientes.php">
                                    <input type="hidden" name="acao" value="arquivar_cliente">
                                    <input type="hidden" name="id_cliente" value="<?= $cli['id'] ?>">
                                    <button type="submit" class="btn-acao-sm btn-arq" title="Arquivar Cliente" onclick="return confirm('Arquivar cliente? Ele perderá acesso ao portal.');"><i class="fas fa-archive"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>

        <div id="modalAddCliente" class="modal-overlay">
            <div class="modal-content">
                <span class="close-modal" onclick="document.getElementById('modalAddCliente').style.display='none'">&times;</span>
                <h2 style="color: #f1c40f; margin-top: 0;"><i class="fas fa-user-plus"></i> Cadastrar Cliente</h2>
                <form method="POST" action="clientes.php">
                    <input type="hidden" name="acao" value="cadastrar_cliente">
                    <div class="form-group"><label>Nome Completo / Empresa *</label><input type="text" name="nome" required></div>
                    <div style="display: flex; gap: 15px;">
                        <div class="form-group" style="flex: 1;">
                            <label>CPF / CNPJ</label>
                            <input type="text" name="documento" placeholder="000.000.000-00" oninput="mascaraDocumento(this)" maxlength="18">
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label>Telefone / WhatsApp</label>
                            <input type="text" name="telefone" placeholder="(00) 00000-0000" oninput="mascaraTelefone(this)" maxlength="15">
                        </div>
                    </div>
                    
                    <div style="background: #222; padding: 15px; border-radius: 4px; border: 1px dashed #555; margin-bottom: 20px;">
                        <strong style="color:#3498db; font-size: 0.9rem; display:block; margin-bottom: 10px;"><i class="fas fa-key"></i> Acesso ao Portal do Cliente</strong>
                        <div class="form-group"><label>E-mail (Usado no Login)</label><input type="email" name="email"></div>
                        <div class="form-group" style="margin-bottom:0;"><label>Senha Temporária</label><input type="text" name="senha" placeholder="Ex: rsf123"></div>
                    </div>
                    
                    <button type="submit" style="background: #f1c40f; color: #111; width: 100%; padding: 12px; border: none; font-weight: bold; border-radius: 4px; cursor: pointer;">Salvar Cliente</button>
                </form>
            </div>
        </div>

        <div id="modalEditCliente" class="modal-overlay">
            <div class="modal-content" style="border-top-color: #3498db;">
                <span class="close-modal" onclick="document.getElementById('modalEditCliente').style.display='none'">&times;</span>
                <h2 style="color: #3498db; margin-top: 0;"><i class="fas fa-user-edit"></i> Editar Cliente</h2>
                <form method="POST" action="clientes.php">
                    <input type="hidden" name="acao" value="editar_cliente">
                    <input type="hidden" name="id_cliente" id="edit_id">
                    <div class="form-group"><label>Nome Completo / Empresa *</label><input type="text" name="nome_edit" id="edit_nome" required></div>
                    <div style="display: flex; gap: 15px;">
                        <div class="form-group" style="flex: 1;">
                            <label>CPF / CNPJ</label>
                            <input type="text" name="documento_edit" id="edit_documento" placeholder="000.000.000-00" oninput="mascaraDocumento(this)" maxlength="18">
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label>Telefone / WhatsApp</label>
                            <input type="text" name="telefone_edit" id="edit_telefone" placeholder="(00) 00000-0000" oninput="mascaraTelefone(this)" maxlength="15">
                        </div>
                    </div>
                    
                    <div style="background: #222; padding: 15px; border-radius: 4px; border: 1px dashed #555; margin-bottom: 20px;">
                        <strong style="color:#3498db; font-size: 0.9rem; display:block; margin-bottom: 10px;"><i class="fas fa-key"></i> Acesso ao Portal</strong>
                        <div class="form-group"><label>E-mail de Login</label><input type="email" name="email_edit" id="edit_email"></div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label>Redefinir Senha</label>
                            <input type="text" name="senha_edit" placeholder="Deixe em branco para não alterar">
                        </div>
                    </div>

                    <button type="submit" style="background: #3498db; color: #fff; width: 100%; padding: 12px; border: none; font-weight: bold; border-radius: 4px; cursor: pointer;">Salvar Alterações</button>
                </form>
            </div>
        </div>
    </main>

    <script>
        function abrirModalEdit(id, nome, doc, tel, email) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_nome').value = nome;
            document.getElementById('edit_documento').value = doc;
            document.getElementById('edit_telefone').value = tel;
            document.getElementById('edit_email').value = email;
            document.getElementById('modalEditCliente').style.display = 'flex';
        }

        window.onclick = function(event) {
            if (event.target == document.getElementById('modalAddCliente')) document.getElementById('modalAddCliente').style.display = "none";
            if (event.target == document.getElementById('modalEditCliente')) document.getElementById('modalEditCliente').style.display = "none";
        }

        // --- MÁSCARA INTELIGENTE PARA CPF E CNPJ ---
        function mascaraDocumento(input) {
            let v = input.value.replace(/\D/g, "");
            if (v.length <= 11) { // Formata CPF
                v = v.replace(/(\d{3})(\d)/, "$1.$2");
                v = v.replace(/(\d{3})(\d)/, "$1.$2");
                v = v.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
            } else { // Formata CNPJ
                v = v.replace(/^(\d{2})(\d)/, "$1.$2");
                v = v.replace(/^(\d{2})\.(\d{3})(\d)/, "$1.$2.$3");
                v = v.replace(/\.(\d{3})(\d)/, ".$1/$2");
                v = v.replace(/(\d{4})(\d)/, "$1-$2");
            }
            input.value = v;
        }

        // --- MÁSCARA PARA TELEFONE (Fixo e Celular) ---
        function mascaraTelefone(input) {
            let v = input.value.replace(/\D/g, "");
            if (v.length > 11) v = v.slice(0, 11);
            if (v.length > 10) {
                v = v.replace(/^(\d{2})(\d{5})(\d{4}).*/, "($1) $2-$3");
            } else if (v.length > 5) {
                v = v.replace(/^(\d{2})(\d{4})(\d{0,4}).*/, "($1) $2-$3");
            } else if (v.length > 2) {
                v = v.replace(/^(\d{2})(\d{0,5})/, "($1) $2");
            } else if (v.length > 0) {
                v = v.replace(/^(\d{0,2})/, "($1");
            }
            input.value = v;
        }
    </script>
</body>
</html>