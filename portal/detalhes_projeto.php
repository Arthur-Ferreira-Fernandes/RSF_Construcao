<?php
session_start();
require_once 'scripts/conexao.php';

// =========================================================================
// 1. TRAVA DE SEGURANÇA BÁSICA
// =========================================================================
if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    header("Location: login.php");
    exit;
}

$id_projeto = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id_projeto) {
    die("<h2 style='color:#fff; text-align:center; margin-top:50px;'>Projeto não encontrado. <a href='lista_projetos.php' style='color:#FFCC00;'>Voltar</a></h2>");
}

$mensagem = '';
$tipo_mensagem = '';

// =========================================================================
// 2. LÓGICA: ADICIONAR NOVO PAGAMENTO DO CLIENTE (RECEBIMENTO)
// =========================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'adicionar_recebimento') {
    $descricao_rec = trim($_POST['descricao_rec']);
    $data_rec = $_POST['data_rec'];
    
    $valor_post = $_POST['valor_rec'] ?? '0';
    $valor_post = str_replace('.', '', $valor_post);
    $valor_post = str_replace(',', '.', $valor_post);
    $valor_rec = (float) $valor_post;

    if (!empty($descricao_rec) && !empty($data_rec) && $valor_rec > 0) {
        try {
            $sql_rec = "INSERT INTO recebimentos (projeto_id, descricao, valor, data_pagamento, usuario_id) 
                        VALUES (:projeto_id, :descricao, :valor, :data_pagamento, :usuario_id)";
            $stmt_rec = $pdo->prepare($sql_rec);
            $stmt_rec->execute([
                ':projeto_id' => $id_projeto,
                ':descricao' => $descricao_rec,
                ':valor' => $valor_rec,
                ':data_pagamento' => $data_rec,
                ':usuario_id' => $_SESSION['usuario_id']
            ]);
            $mensagem = "Pagamento do cliente registrado com sucesso!";
            $tipo_mensagem = "sucesso";
        } catch (PDOException $e) {
            $mensagem = "Erro ao registrar o recebimento.";
            $tipo_mensagem = "erro";
        }
    } else {
        $mensagem = "Preencha todos os campos do recebimento corretamente.";
        $tipo_mensagem = "erro";
    }
}

// =========================================================================
// LÓGICA: ARQUIVAR RECEBIMENTO (EXCLUSIVO PARA ADMIN)
// =========================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'arquivar_recebimento') {
    $id_recebimento = filter_input(INPUT_POST, 'id_recebimento', FILTER_VALIDATE_INT);
    
    if ($_SESSION['nivel_acesso'] !== 'admin') {
        $mensagem = "Apenas administradores podem arquivar pagamentos.";
        $tipo_mensagem = "erro";
    } elseif ($id_recebimento) {
        try {
            $stmt_arq_rec = $pdo->prepare("UPDATE recebimentos SET status = 'Arquivado' WHERE id = :id");
            $stmt_arq_rec->execute([':id' => $id_recebimento]);
            $mensagem = "Recebimento arquivado. O valor foi removido do saldo da obra.";
            $tipo_mensagem = "sucesso";
        } catch (PDOException $e) {
            $mensagem = "Erro ao arquivar o recebimento.";
            $tipo_mensagem = "erro";
        }
    }
}

// =========================================================================
// LÓGICA: EDITAR RECEBIMENTO VIA MODAL (EXCLUSIVO PARA ADMIN)
// =========================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'editar_recebimento') {
    $id_recebimento = filter_input(INPUT_POST, 'id_recebimento', FILTER_VALIDATE_INT);
    $descricao_edit = trim($_POST['descricao_rec_edit']);
    $data_edit = $_POST['data_rec_edit'];
    
    $valor_post = $_POST['valor_rec_edit'] ?? '0';
    $valor_post = str_replace('.', '', $valor_post);
    $valor_post = str_replace(',', '.', $valor_post);
    $valor_edit = (float) $valor_post;

    if ($_SESSION['nivel_acesso'] !== 'admin') {
        $mensagem = "Apenas administradores podem editar pagamentos.";
        $tipo_mensagem = "erro";
    } elseif ($id_recebimento && !empty($descricao_edit) && !empty($data_edit) && $valor_edit > 0) {
        try {
            $stmt_edit_rec = $pdo->prepare("UPDATE recebimentos SET descricao = :descricao, valor = :valor, data_pagamento = :data_pagamento WHERE id = :id");
            $stmt_edit_rec->execute([
                ':descricao' => $descricao_edit, 
                ':valor' => $valor_edit, 
                ':data_pagamento' => $data_edit, 
                ':id' => $id_recebimento
            ]);
            $mensagem = "Pagamento do cliente atualizado com sucesso!";
            $tipo_mensagem = "sucesso";
        } catch (PDOException $e) {
            $mensagem = "Erro ao atualizar o pagamento.";
            $tipo_mensagem = "erro";
        }
    } else {
        $mensagem = "Preencha todos os campos do modal de edição corretamente.";
        $tipo_mensagem = "erro";
    }
}

// =========================================================================
// 3. LÓGICA: ADICIONAR NOVA DESPESA (CUSTO DA OBRA)
// =========================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'adicionar_despesa') {
    $descricao_despesa = trim($_POST['descricao_despesa']);
    $data_despesa = $_POST['data_despesa'];
    
    $valor_post = $_POST['valor_despesa'] ?? '0';
    $valor_post = str_replace('.', '', $valor_post);
    $valor_post = str_replace(',', '.', $valor_post);
    $valor_despesa = (float) $valor_post;

    if (!empty($descricao_despesa) && !empty($data_despesa) && $valor_despesa > 0) {
        try {
            $sql_custo = "INSERT INTO despesas (projeto_id, descricao, valor, data_despesa, usuario_id) 
                          VALUES (:projeto_id, :descricao, :valor, :data_despesa, :usuario_id)";
            $stmt_custo = $pdo->prepare($sql_custo);
            $stmt_custo->execute([
                ':projeto_id' => $id_projeto,
                ':descricao' => $descricao_despesa,
                ':valor' => $valor_despesa,
                ':data_despesa' => $data_despesa,
                ':usuario_id' => $_SESSION['usuario_id']
            ]);
            $mensagem = "Gasto lançado com sucesso no financeiro da obra!";
            $tipo_mensagem = "sucesso";
        } catch (PDOException $e) {
            $mensagem = "Erro ao registrar a despesa.";
            $tipo_mensagem = "erro";
        }
    } else {
        $mensagem = "Preencha todos os campos do gasto corretamente.";
        $tipo_mensagem = "erro";
    }
}

// =========================================================================
// 4. LÓGICA: ARQUIVAR DESPESA (EXCLUSIVO PARA ADMIN)
// =========================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'arquivar_despesa') {
    $id_despesa = filter_input(INPUT_POST, 'id_despesa', FILTER_VALIDATE_INT);
    
    if ($_SESSION['nivel_acesso'] !== 'admin') {
        $mensagem = "Apenas administradores podem arquivar despesas do sistema.";
        $tipo_mensagem = "erro";
    } elseif ($id_despesa) {
        try {
            $stmt_arq_desp = $pdo->prepare("UPDATE despesas SET status = 'Arquivado' WHERE id = :id");
            $stmt_arq_desp->execute([':id' => $id_despesa]);
            $mensagem = "Gasto arquivado. O valor foi removido do total de despesas.";
            $tipo_mensagem = "sucesso";
        } catch (PDOException $e) {
            $mensagem = "Erro ao arquivar a despesa.";
            $tipo_mensagem = "erro";
        }
    }
}

// =========================================================================
// 5. LÓGICA: EDITAR DESPESA (VIA JANELA MODAL)
// =========================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'editar_despesa') {
    $id_despesa = filter_input(INPUT_POST, 'id_despesa', FILTER_VALIDATE_INT);
    $descricao_edit = trim($_POST['descricao_edit']);
    $data_edit = $_POST['data_despesa_edit'];
    
    $valor_post = $_POST['valor_edit'] ?? '0';
    $valor_post = str_replace('.', '', $valor_post);
    $valor_post = str_replace(',', '.', $valor_post);
    $valor_edit = (float) $valor_post;

    if ($id_despesa && !empty($descricao_edit) && !empty($data_edit) && $valor_edit > 0) {
        try {
            $stmt_edit = $pdo->prepare("UPDATE despesas SET descricao = :descricao, valor = :valor, data_despesa = :data_despesa WHERE id = :id");
            $stmt_edit->execute([
                ':descricao' => $descricao_edit, 
                ':valor' => $valor_edit, 
                ':data_despesa' => $data_edit, 
                ':id' => $id_despesa
            ]);
            $mensagem = "Gasto atualizado com sucesso!";
            $tipo_mensagem = "sucesso";
        } catch (PDOException $e) {
            $mensagem = "Erro ao atualizar a despesa.";
            $tipo_mensagem = "erro";
        }
    } else {
        $mensagem = "Preencha todos os campos do modal de edição corretamente.";
        $tipo_mensagem = "erro";
    }
}

// =========================================================================
// 6. LÓGICA: EXCLUIR ARQUIVO (VIA GET)
// =========================================================================
if (isset($_GET['excluir_arquivo'])) {
    $id_arquivo = filter_input(INPUT_GET, 'excluir_arquivo', FILTER_VALIDATE_INT);
    if ($id_arquivo) {
        try {
            $stmt_del = $pdo->prepare("SELECT nome_seguro FROM arquivos WHERE id = :id_arq AND projeto = (SELECT nome FROM projetos WHERE id = :id_proj)");
            $stmt_del->execute([':id_arq' => $id_arquivo, ':id_proj' => $id_projeto]);
            $arquivo_para_deletar = $stmt_del->fetch();

            if ($arquivo_para_deletar) {
                $caminho_fisico = 'uploads/' . $arquivo_para_deletar['nome_seguro'];
                if (file_exists($caminho_fisico)) { unlink($caminho_fisico); }
                $pdo->prepare("DELETE FROM arquivos WHERE id = :id_arq")->execute([':id_arq' => $id_arquivo]);
                
                // Redireciona para limpar a URL
                header("Location: detalhes_projeto.php?id=" . $id_projeto . "&msg=excluido");
                exit;
            }
        } catch (PDOException $e) {}
    }
}

// =========================================================================
// 7. CARREGAMENTO GERAL DE DADOS DA PÁGINA
// =========================================================================
try {
    // 7.1. Dados do Projeto e do Engenheiro Responsável
    $sql_projeto = "SELECT p.*, u.nome AS engenheiro_nome, u.telefone AS engenheiro_telefone 
                    FROM projetos p 
                    LEFT JOIN usuarios u ON p.engenheiro_responsavel = u.id 
                    WHERE p.id = :id LIMIT 1";
    $stmt = $pdo->prepare($sql_projeto);
    $stmt->execute([':id' => $id_projeto]);
    $projeto = $stmt->fetch();

    if (!$projeto) { die("<h2 style='color:#fff; text-align:center; margin-top:50px;'>Obra não localizada.</h2>"); }

    // 7.2. Arquivos Anexados
    $sql_arquivos = "SELECT a.*, u.nome AS enviado_por 
                     FROM arquivos a 
                     JOIN usuarios u ON a.usuario_id = u.id 
                     WHERE a.projeto = :nome_projeto ORDER BY a.data_envio DESC";
    $stmt_arq = $pdo->prepare($sql_arquivos);
    $stmt_arq->execute([':nome_projeto' => $projeto['nome']]);
    $arquivos = $stmt_arq->fetchAll();

    // 7.3. Despesas (Gastos Realizados ATIVOS)
    $sql_despesas = "SELECT d.*, u.nome AS registrado_por FROM despesas d 
                     LEFT JOIN usuarios u ON d.usuario_id = u.id 
                     WHERE d.projeto_id = :id AND d.status = 'Ativo' 
                     ORDER BY d.data_despesa DESC, d.id DESC";
    $stmt_desp = $pdo->prepare($sql_despesas);
    $stmt_desp->execute([':id' => $id_projeto]);
    $despesas = $stmt_desp->fetchAll();

    // 7.4. Recebimentos (Entradas do Cliente)
    $stmt_rec = $pdo->prepare("SELECT r.*, u.nome AS registrado_por FROM recebimentos r 
                               LEFT JOIN usuarios u ON r.usuario_id = u.id 
                               WHERE r.projeto_id = :id AND r.status = 'Ativo' 
                               ORDER BY r.data_pagamento DESC, r.id DESC");
    $stmt_rec->execute([':id' => $id_projeto]);
    $recebimentos = $stmt_rec->fetchAll();

    // =========================================================================
    // 8. CÁLCULOS FINANCEIROS PARA O DASHBOARD DA OBRA
    // =========================================================================
    $total_orcado = (float) $projeto['valor'];
    
    // Calcula Entradas (Recebimentos)
    $total_recebido = 0;
    foreach ($recebimentos as $r) { $total_recebido += (float) $r['valor']; }
    $falta_receber = $total_orcado - $total_recebido;
    $porcentagem_recebida = $total_orcado > 0 ? ($total_recebido / $total_orcado) * 100 : 0;
    $largura_barra_rec = $porcentagem_recebida > 100 ? 100 : $porcentagem_recebida;

    // Calcula Saídas (Despesas)
    $total_gasto = 0;
    foreach ($despesas as $d) { $total_gasto += (float) $d['valor']; }
    
    // Saldo Real da Obra (Caixa) = Recebido - Gasto
    $saldo_em_caixa = $total_recebido - $total_gasto;
    
    // Progresso de gastos em relação ao orçado
    $porcentagem_gasta = $total_orcado > 0 ? ($total_gasto / $total_orcado) * 100 : 0;
    $largura_barra_gasto = $porcentagem_gasta > 100 ? 100 : $porcentagem_gasta;

    // Define a cor da barra de gastos
    $cor_barra_gasto = 'progresso-seguro'; // Verde
    if ($porcentagem_gasta >= 75 && $porcentagem_gasta < 95) { 
        $cor_barra_gasto = 'progresso-alerta'; // Amarelo
    } elseif ($porcentagem_gasta >= 95) { 
        $cor_barra_gasto = 'progresso-perigo'; // Vermelho
    }

} catch (PDOException $e) {
    die("Erro interno ao carregar os dados financeiros e documentos.");
}

function getStatusClass($status) {
    switch ($status) {
        case 'Em Orçamento': return 'status-orcamento';
        case 'Em Andamento': return 'status-andamento';
        case 'Pausado':      return 'status-pausado';
        case 'Concluído':    return 'status-concluido';
        default:             return 'status-andamento';
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($projeto['nome']) ?> | RSF Engenharia</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="../styles/detalhes.css">
</head>
<body>

    <?php include 'header.php'; ?>

    <main class="container">
        
        <?php if (!empty($mensagem)): ?>
            <div class="alerta <?= $tipo_mensagem === 'sucesso' ? 'alerta-sucesso' : 'alerta-erro' ?>" style="margin-bottom: 20px; padding: 15px; border-radius: 4px; font-weight: bold; background-color: <?= $tipo_mensagem === 'sucesso' ? 'rgba(46, 204, 113, 0.2)' : 'rgba(231, 76, 60, 0.2)' ?>; color: <?= $tipo_mensagem === 'sucesso' ? '#2ecc71' : '#e74c3c' ?>; border-left: 4px solid <?= $tipo_mensagem === 'sucesso' ? '#2ecc71' : '#e74c3c' ?>;">
                <i class="fas <?= $tipo_mensagem === 'sucesso' ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i> <?= htmlspecialchars($mensagem) ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'atualizado'): ?>
            <div class="alerta-sucesso" style="margin-bottom: 20px; padding: 15px; border-radius: 4px; font-weight: bold; background-color: rgba(46, 204, 113, 0.2); color: #2ecc71; border-left: 4px solid #2ecc71;">
                <i class="fas fa-check-circle"></i> Dados da obra atualizados com sucesso!
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'excluido'): ?>
            <div class="alerta-sucesso" style="margin-bottom: 20px; padding: 15px; border-radius: 4px; font-weight: bold; background-color: rgba(46, 204, 113, 0.2); color: #2ecc71; border-left: 4px solid #2ecc71;">
                <i class="fas fa-check-circle"></i> Arquivo excluído com sucesso do sistema.
            </div>
        <?php endif; ?>

        <div class="projeto-header">
            <div class="projeto-titulo">
                <h1><span class="id-badge">#<?= str_pad($projeto['id'], 4, '0', STR_PAD_LEFT) ?></span> <?= htmlspecialchars($projeto['nome']) ?></h1>
                <span class="badge-status <?= getStatusClass($projeto['status']) ?>">
                    <?= htmlspecialchars($projeto['status']) ?>
                </span>
            </div>
            <div class="acoes-projeto">
                <a href="editar_projeto.php?id=<?= $projeto['id'] ?>" class="btn-editar">
                    <i class="fas fa-edit"></i> Editar Obra
                </a>
            </div>
        </div>

        <div class="info-grid" style="margin-bottom: 20px;">
            <div class="info-card destaque-rt">
                <h3><i class="fas fa-user-tie"></i> Responsável Técnico</h3>
                <p><?= htmlspecialchars($projeto['engenheiro_nome'] ?? 'Não Atribuído') ?></p>
                <p class="sub-info"><i class="fas fa-phone"></i> <?= htmlspecialchars($projeto['engenheiro_telefone'] ?? '--') ?></p>
            </div>
            <div class="info-card">
                <h3><i class="fas fa-calendar-alt"></i> Início da Obra</h3>
                <p><?= date('d/m/Y', strtotime($projeto['data_inicio'])) ?></p>
            </div>
            <div class="info-card">
                <h3><i class="fas fa-map-marker-alt"></i> Localização</h3>
                <p style="font-size: 0.95rem; line-height: 1.4;"><?= htmlspecialchars($projeto['endereco']) ?></p>
            </div>
        </div>

        <div class="info-card" style="margin-bottom: 40px;">
            <h3><i class="fas fa-align-left"></i> Escopo / Descrição Técnica</h3>
            <p style="font-size: 0.95rem; font-weight: normal; line-height: 1.6; color: #ccc;">
                <?= nl2br(htmlspecialchars($projeto['descricao'])) ?>
            </p>
        </div>

        <div class="financeiro-card card-entradas">
            <div class="financeiro-header">
                <h2><i class="fas fa-hand-holding-usd"></i> Pagamentos do Cliente (Entradas)</h2>
                <div class="financeiro-resumo">
                    <p>Falta Receber do Cliente</p>
                    <strong class="lucro">R$ <?= number_format(max(0, $falta_receber), 2, ',', '.') ?></strong>
                </div>
            </div>

            <div class="progress-container">
                <div class="progress-bar progresso-azul" style="width: <?= $largura_barra_rec ?>%;"></div>
            </div>
            <div class="progress-labels">
                <span>Recebido: R$ <?= number_format($total_recebido, 2, ',', '.') ?> (<?= number_format($porcentagem_recebida, 1, ',', '.') ?>%)</span>
                <span>Valor Contrato: R$ <?= number_format($total_orcado, 2, ',', '.') ?></span>
            </div>

            <form method="POST" action="detalhes_projeto.php?id=<?= $id_projeto ?>" class="form-despesa">
                <input type="hidden" name="acao" value="adicionar_recebimento">
                
                <div class="form-group" style="flex: 2;">
                    <label>Descrição (Ex: Medição 1, Sinal)</label>
                    <input type="text" name="descricao_rec" required style="width: 100%; padding: 10px; background: #222; border: 1px solid #444; color: #fff; border-radius: 4px;">
                </div>
                
                <div class="form-group">
                    <label>Data</label>
                    <input type="date" name="data_rec" value="<?= date('Y-m-d') ?>" required style="width: 100%; padding: 10px; background: #222; border: 1px solid #444; color: #fff; border-radius: 4px;">
                </div>

                <div class="form-group">
                    <label>Valor (R$)</label>
                    <input type="text" name="valor_rec" placeholder="0,00" onkeyup="mascaraMoeda(this)" required style="width: 100%; padding: 10px; background: #222; border: 1px solid #444; color: #fff; border-radius: 4px;">
                </div>

                <button type="submit" class="btn-submit btn-submit-azul"><i class="fas fa-plus"></i> Receber</button>
            </form>
            
            <div class="table-wrapper" style="margin-top: 25px;">
                <table>
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Referência do Pagamento</th>
                            <th>Registrado por</th>
                            <th>Valor</th>
                            <?php if ($_SESSION['nivel_acesso'] === 'admin'): ?>
                                <th style="text-align: right;">Ações</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recebimentos)): ?>
                            <tr><td colspan="<?= $_SESSION['nivel_acesso'] === 'admin' ? '5' : '4' ?>" style="text-align: center; color: #888; padding: 20px;">Nenhum pagamento ativo registrado.</td></tr>
                        <?php else: ?>
                            <?php foreach ($recebimentos as $rec): ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($rec['data_pagamento'])) ?></td>
                                    <td style="font-weight: bold;"><?= htmlspecialchars($rec['descricao']) ?></td>
                                    <td style="font-size: 0.85rem; color: #aaa;"><?= htmlspecialchars($rec['registrado_por']) ?></td>
                                    <td class="texto-entrada">+ R$ <?= number_format($rec['valor'], 2, ',', '.') ?></td>
                                    
                                    <?php if ($_SESSION['nivel_acesso'] === 'admin'): ?>
                                    <td style="text-align: right; white-space: nowrap;">
                                        <button type="button" class="btn-editar-sm" style="color: #3498db;" title="Corrigir Pagamento" onclick="abrirModalRecebimento(<?= $rec['id'] ?>, '<?= htmlspecialchars($rec['descricao'], ENT_QUOTES) ?>', '<?= $rec['data_pagamento'] ?>', '<?= number_format($rec['valor'], 2, ',', '.') ?>')">
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        <form method="POST" style="display:inline;" action="detalhes_projeto.php?id=<?= $id_projeto ?>">
                                            <input type="hidden" name="acao" value="arquivar_recebimento">
                                            <input type="hidden" name="id_recebimento" value="<?= $rec['id'] ?>">
                                            <button type="submit" class="btn-arquivar-sm" title="Arquivar Pagamento" onclick="return confirm('Deseja arquivar este pagamento? Ele sairá da soma de valores recebidos.');">
                                                <i class="fas fa-archive"></i>
                                            </button>
                                        </form>
                                    </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="financeiro-card">
            <div class="financeiro-header">
                <h2><i class="fas fa-file-invoice-dollar"></i> Custos e Despesas (Saídas)</h2>
                <div class="financeiro-resumo">
                    <p>Saldo em Caixa (Recebido - Gasto)</p>
                    <strong class="<?= $saldo_em_caixa >= 0 ? 'lucro' : 'prejuizo' ?>">
                        R$ <?= number_format($saldo_em_caixa, 2, ',', '.') ?>
                    </strong>
                </div>
            </div>

            <div class="progress-container">
                <div class="progress-bar <?= $cor_barra_gasto ?>" style="width: <?= $largura_barra_gasto ?>%;"></div>
            </div>
            <div class="progress-labels">
                <span>Gasto: R$ <?= number_format($total_gasto, 2, ',', '.') ?> (<?= number_format($porcentagem_gasta, 1, ',', '.') ?>% do Orçamento)</span>
                <span>Orçamento Total: R$ <?= number_format($total_orcado, 2, ',', '.') ?></span>
            </div>

            <form method="POST" action="detalhes_projeto.php?id=<?= $id_projeto ?>" class="form-despesa">
                <input type="hidden" name="acao" value="adicionar_despesa">
                
                <div class="form-group" style="flex: 2;">
                    <label>Descrição do Gasto (Ex: Cimento, Frete)</label>
                    <input type="text" name="descricao_despesa" required style="width: 100%; padding: 10px; background: #222; border: 1px solid #444; color: #fff; border-radius: 4px;">
                </div>
                
                <div class="form-group">
                    <label>Data</label>
                    <input type="date" name="data_despesa" value="<?= date('Y-m-d') ?>" required style="width: 100%; padding: 10px; background: #222; border: 1px solid #444; color: #fff; border-radius: 4px;">
                </div>

                <div class="form-group">
                    <label>Valor (R$)</label>
                    <input type="text" name="valor_despesa" placeholder="0,00" onkeyup="mascaraMoeda(this)" required style="width: 100%; padding: 10px; background: #222; border: 1px solid #444; color: #fff; border-radius: 4px;">
                </div>

                <button type="submit" class="btn-submit" style="background-color: #e67e22; border: none; font-weight: bold; border-radius: 4px; cursor: pointer; color:#111;">
                    <i class="fas fa-plus"></i> Pagar
                </button>
            </form>
            
            <div class="table-wrapper" style="margin-top: 25px;">
                <table>
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Descrição</th>
                            <th>Registrado por</th>
                            <th>Valor</th>
                            <th style="text-align: right;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($despesas)): ?>
                            <tr>
                                <td colspan="5" style="text-align: center; color: #888; padding: 20px;">Nenhum gasto ativo lançado nesta obra.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($despesas as $desp): ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($desp['data_despesa'])) ?></td>
                                    <td style="font-weight: bold;"><?= htmlspecialchars($desp['descricao']) ?></td>
                                    <td style="font-size: 0.85rem; color: #aaa;"><?= htmlspecialchars($desp['registrado_por']) ?></td>
                                    <td style="color: #e74c3c; font-weight: bold;">- R$ <?= number_format($desp['valor'], 2, ',', '.') ?></td>
                                    <td style="text-align: right; white-space: nowrap;">
                                        
                                        <button type="button" class="btn-editar-sm" title="Corrigir Gasto" onclick="abrirModal(<?= $desp['id'] ?>, '<?= htmlspecialchars($desp['descricao'], ENT_QUOTES) ?>', '<?= $desp['data_despesa'] ?>', '<?= number_format($desp['valor'], 2, ',', '.') ?>')">
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        <?php if ($_SESSION['nivel_acesso'] === 'admin'): ?>
                                        <form method="POST" style="display:inline;" action="detalhes_projeto.php?id=<?= $id_projeto ?>">
                                            <input type="hidden" name="acao" value="arquivar_despesa">
                                            <input type="hidden" name="id_despesa" value="<?= $desp['id'] ?>">
                                            <button type="submit" class="btn-arquivar-sm" title="Arquivar Gasto" onclick="return confirm('Deseja arquivar este gasto? Ele sairá da soma total da obra.');">
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
        </div>

        <div class="secao-arquivos">
            <h2><i class="fas fa-folder-open"></i> Documentação Vinculada a esta Obra</h2>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Tipo</th>
                            <th>Nome do Arquivo</th>
                            <th>Enviado por</th>
                            <th>Data</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($arquivos)): ?>
                            <tr><td colspan="5" style="text-align: center; color: #888; padding: 30px;">Nenhum arquivo anexado a este projeto ainda.</td></tr>
                        <?php else: ?>
                            <?php foreach ($arquivos as $arq): ?>
                                <tr>
                                    <td style="color: var(--primary-yellow); font-weight: bold; font-size: 0.8rem;"><?= htmlspecialchars($arq['tipo_documento']) ?></td>
                                    <td style="font-weight: bold;"><?= htmlspecialchars($arq['nome_original']) ?></td>
                                    <td><?= htmlspecialchars($arq['enviado_por']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($arq['data_envio'])) ?></td>
                                    <td>
                                        <a href="uploads/<?= htmlspecialchars($arq['nome_seguro']) ?>" target="_blank" class="btn-download" style="margin-right: 15px;"><i class="fas fa-external-link-alt"></i> Abrir</a>
                                        <a href="detalhes_projeto.php?id=<?= $projeto['id'] ?>&excluir_arquivo=<?= $arq['id'] ?>" class="btn-excluir" onclick="return confirm('ATENÇÃO: Tem certeza que deseja excluir este arquivo permanentemente do servidor?');"><i class="fas fa-trash-alt"></i> Excluir</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="modalEditarGasto" class="modal-overlay">
            <div class="modal-content">
                <span class="close-modal" onclick="fecharModal()">&times;</span>
                <h2 style="color: #3498db; margin-top: 0;"><i class="fas fa-edit"></i> Corrigir Despesa</h2>
                
                <form method="POST" action="detalhes_projeto.php?id=<?= $id_projeto ?>">
                    <input type="hidden" name="acao" value="editar_despesa">
                    <input type="hidden" name="id_despesa" id="modal_id_despesa">
                    
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label style="color: #aaa; font-size: 0.85rem; font-weight: bold;">Descrição do Gasto</label>
                        <input type="text" name="descricao_edit" id="modal_descricao" required style="width: 100%; padding: 10px; background: #222; border: 1px solid #444; color: #fff; border-radius: 4px; box-sizing: border-box;">
                    </div>

                    <div class="form-group" style="margin-bottom: 15px;">
                        <label style="color: #aaa; font-size: 0.85rem; font-weight: bold;">Data</label>
                        <input type="date" name="data_despesa_edit" id="modal_data" required style="width: 100%; padding: 10px; background: #222; border: 1px solid #444; color: #fff; border-radius: 4px; box-sizing: border-box;">
                    </div>

                    <div class="form-group" style="margin-bottom: 25px;">
                        <label style="color: #aaa; font-size: 0.85rem; font-weight: bold;">Valor (R$)</label>
                        <input type="text" name="valor_edit" id="modal_valor" onkeyup="mascaraMoeda(this)" required style="width: 100%; padding: 10px; background: #222; border: 1px solid #444; color: #fff; border-radius: 4px; box-sizing: border-box;">
                    </div>

                    <button type="submit" class="btn-submit" style="background-color: #3498db; color: #fff; width: 100%; border: none; padding: 12px; font-weight: bold; border-radius: 4px; cursor: pointer;">
                        <i class="fas fa-save"></i> Salvar Correção
                    </button>
                </form>
            </div>
        </div>
        <div id="modalEditarRecebimento" class="modal-overlay">
            <div class="modal-content" style="border-top-color: #3498db;">
                <span class="close-modal" onclick="fecharModalRecebimento()">&times;</span>
                <h2 style="color: #3498db; margin-top: 0;"><i class="fas fa-hand-holding-usd"></i> Corrigir Recebimento</h2>
                
                <form method="POST" action="detalhes_projeto.php?id=<?= $id_projeto ?>">
                    <input type="hidden" name="acao" value="editar_recebimento">
                    <input type="hidden" name="id_recebimento" id="modal_rec_id">
                    
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label style="color: #aaa; font-size: 0.85rem; font-weight: bold;">Referência do Pagamento</label>
                        <input type="text" name="descricao_rec_edit" id="modal_rec_descricao" required style="width: 100%; padding: 10px; background: #222; border: 1px solid #444; color: #fff; border-radius: 4px; box-sizing: border-box;">
                    </div>

                    <div class="form-group" style="margin-bottom: 15px;">
                        <label style="color: #aaa; font-size: 0.85rem; font-weight: bold;">Data</label>
                        <input type="date" name="data_rec_edit" id="modal_rec_data" required style="width: 100%; padding: 10px; background: #222; border: 1px solid #444; color: #fff; border-radius: 4px; box-sizing: border-box;">
                    </div>

                    <div class="form-group" style="margin-bottom: 25px;">
                        <label style="color: #aaa; font-size: 0.85rem; font-weight: bold;">Valor (R$)</label>
                        <input type="text" name="valor_rec_edit" id="modal_rec_valor" onkeyup="mascaraMoeda(this)" required style="width: 100%; padding: 10px; background: #222; border: 1px solid #444; color: #fff; border-radius: 4px; box-sizing: border-box;">
                    </div>

                    <button type="submit" class="btn-submit" style="background-color: #3498db; color: #fff; width: 100%; border: none; padding: 12px; font-weight: bold; border-radius: 4px; cursor: pointer;">
                        <i class="fas fa-save"></i> Salvar Correção
                    </button>
                </form>
            </div>
        </div>

    </main>

    <script>
        // Função para abrir o modal de edição e preencher os inputs com os dados da tabela
        function abrirModal(id, descricao, data, valor) {
            document.getElementById('modal_id_despesa').value = id;
            document.getElementById('modal_descricao').value = descricao;
            document.getElementById('modal_data').value = data;
            document.getElementById('modal_valor').value = valor;
            
            document.getElementById('modalEditarGasto').style.display = 'flex';
        }

        // Função para fechar a janelinha
        function fecharModal() {
            document.getElementById('modalEditarGasto').style.display = 'none';
        }

        // Fecha a janelinha se o usuário clicar no fundo escuro fora dela
        window.onclick = function(event) {
            var modal = document.getElementById('modalEditarGasto');
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }

        // Funções para o Modal de Recebimentos
        function abrirModalRecebimento(id, descricao, data, valor) {
            document.getElementById('modal_rec_id').value = id;
            document.getElementById('modal_rec_descricao').value = descricao;
            document.getElementById('modal_rec_data').value = data;
            document.getElementById('modal_rec_valor').value = valor;
            document.getElementById('modalEditarRecebimento').style.display = 'flex';
        }

        function fecharModalRecebimento() {
            document.getElementById('modalEditarRecebimento').style.display = 'none';
        }

        // Adiciona a regra para fechar o novo modal se clicar fora dele
        window.addEventListener('click', function(event) {
            var modalGasto = document.getElementById('modalEditarGasto');
            var modalRec = document.getElementById('modalEditarRecebimento');
            if (event.target == modalGasto) { modalGasto.style.display = "none"; }
            if (event.target == modalRec) { modalRec.style.display = "none"; }
        });

        // Máscara para dinheiro padrão brasileiro
        function mascaraMoeda(input) {
            let valor = input.value;
            valor = valor.replace(/\D/g, ""); // Remove letras
            if (valor === "") { input.value = ""; return; }
            valor = (parseInt(valor) / 100).toFixed(2) + ''; // Transforma em decimal
            valor = valor.replace(".", ","); // Troca ponto por vírgula
            valor = valor.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.'); // Coloca os pontos de milhar
            input.value = valor;
        }
    </script>
</body>
</html>