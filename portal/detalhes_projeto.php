<?php
session_start();
require_once 'scripts/conexao.php';

// 1. TRAVA DE SEGURANÇA E BLOQUEIO DE AÇÕES PARA CLIENTES
if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) { header("Location: login.php"); exit; }
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SESSION['nivel_acesso'] === 'cliente') {
    die("Acesso negado. Clientes têm permissão apenas para visualização.");
}

$id_projeto = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id_projeto) { die("<h2 style='color:#fff; text-align:center; margin-top:50px;'>Projeto não encontrado.</h2>"); }

$mensagem = ''; $tipo_mensagem = '';

// =========================================================================
// 2. LÓGICAS DE AÇÃO (EXCLUSÕES E FINANCEIRO AVULSO)
// =========================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao'])) {
    if ($_POST['acao'] === 'excluir_projeto' && $_SESSION['nivel_acesso'] === 'admin') {
        $stmt_arq_del = $pdo->prepare("SELECT nome_seguro FROM arquivos WHERE projeto = (SELECT nome FROM projetos WHERE id = :id)");
        $stmt_arq_del->execute([':id' => $id_projeto]);
        foreach ($stmt_arq_del->fetchAll() as $arq) { if (file_exists('uploads/' . $arq['nome_seguro'])) unlink('uploads/' . $arq['nome_seguro']); }
        $pdo->prepare("DELETE FROM projetos WHERE id = :id")->execute([':id' => $id_projeto]);
        header("Location: lista_projetos.php?msg=projeto_excluido"); exit;
    }
    if ($_POST['acao'] === 'adicionar_despesa') {
        $valor_despesa = (float) str_replace(['.', ','], ['', '.'], $_POST['valor_despesa'] ?? '0');
        if (!empty($_POST['descricao_despesa']) && $valor_despesa > 0) {
            $pdo->prepare("INSERT INTO despesas (projeto_id, descricao, valor, data_despesa, usuario_id) VALUES (?, ?, ?, ?, ?)")->execute([$id_projeto, trim($_POST['descricao_despesa']), $valor_despesa, $_POST['data_despesa'], $_SESSION['usuario_id']]);
            $mensagem = "Gasto lançado!"; $tipo_mensagem = "sucesso";
        }
    }
    if ($_POST['acao'] === 'arquivar_despesa') {
        $pdo->prepare("UPDATE despesas SET status = 'Arquivado' WHERE id = ?")->execute([$_POST['id_despesa']]);
    }
    if ($_POST['acao'] === 'editar_despesa') {
        $valor_edit = (float) str_replace(['.', ','], ['', '.'], $_POST['valor_edit'] ?? '0');
        $pdo->prepare("UPDATE despesas SET descricao = ?, valor = ?, data_despesa = ? WHERE id = ?")->execute([trim($_POST['descricao_edit']), $valor_edit, $_POST['data_despesa_edit'], $_POST['id_despesa']]);
    }
    if ($_POST['acao'] === 'adicionar_recebimento') {
        $valor_rec = (float) str_replace(['.', ','], ['', '.'], $_POST['valor_rec'] ?? '0');
        if (!empty($_POST['descricao_rec']) && $valor_rec > 0) {
            $pdo->prepare("INSERT INTO recebimentos (projeto_id, descricao, valor, data_pagamento, usuario_id) VALUES (?, ?, ?, ?, ?)")->execute([$id_projeto, trim($_POST['descricao_rec']), $valor_rec, $_POST['data_rec'], $_SESSION['usuario_id']]);
            $mensagem = "Pagamento avulso registrado!"; $tipo_mensagem = "sucesso";
        }
    }
    if ($_POST['acao'] === 'arquivar_recebimento') {
        $pdo->prepare("UPDATE recebimentos SET status = 'Arquivado' WHERE id = ?")->execute([$_POST['id_recebimento']]);
        $mensagem = "Recebimento arquivado com sucesso."; $tipo_mensagem = "sucesso";
    }
    if ($_POST['acao'] === 'editar_recebimento') {
        $valor_edit = (float) str_replace(['.', ','], ['', '.'], $_POST['valor_rec_edit'] ?? '0');
        $pdo->prepare("UPDATE recebimentos SET descricao = ?, valor = ?, data_pagamento = ? WHERE id = ?")->execute([trim($_POST['descricao_rec_edit']), $valor_edit, $_POST['data_rec_edit'], $_POST['id_recebimento']]);
        $mensagem = "Recebimento corrigido!"; $tipo_mensagem = "sucesso";
    }

    // =========================================================================
    // NOVO: SALVAR PROGRESSO DAS TAREFAS (Sem fechar a etapa)
    // =========================================================================
    if ($_POST['acao'] === 'atualizar_progresso') {
        $id_acomp = filter_input(INPUT_POST, 'acompanhamento_id', FILTER_VALIDATE_INT);
        $itens_marcados = $_POST['itens_concluidos'] ?? [];
        $datas_realizadas = $_POST['datas_realizadas'] ?? []; 

        if ($id_acomp) {
            // Zera todos os itens primeiro
            $pdo->prepare("UPDATE acompanhamento_itens SET concluido = 0, data_realizada = NULL WHERE acompanhamento_id = ?")->execute([$id_acomp]);
            
            // Marca apenas os que o usuário deixou "checkados" e salva a data real
            if (!empty($itens_marcados)) {
                $stmt_update_item = $pdo->prepare("UPDATE acompanhamento_itens SET concluido = 1, data_realizada = ? WHERE id = ?");
                foreach ($itens_marcados as $id_item) {
                    $data_real = !empty($datas_realizadas[$id_item]) ? $datas_realizadas[$id_item] : date('Y-m-d');
                    $stmt_update_item->execute([$data_real, $id_item]);
                }
            }
            $mensagem = "Progresso das tarefas atualizado!"; $tipo_mensagem = "sucesso";
        }
    }

    // =========================================================================
    // FECHAR A ETAPA (Agora só lida com o relatório e o pagamento)
    // =========================================================================
    if ($_POST['acao'] === 'receber_e_concluir_semana') {
        $valor_rec = (float) str_replace(['.', ','], ['', '.'], $_POST['valor_rec'] ?? '0');
        $relato_semana = trim($_POST['relato_semana']);
        $id_acomp = filter_input(INPUT_POST, 'acompanhamento_id', FILTER_VALIDATE_INT);

        if ($id_acomp) {
            if ($valor_rec > 0) {
                $pdo->prepare("INSERT INTO recebimentos (projeto_id, descricao, valor, data_pagamento, usuario_id) VALUES (?, ?, ?, ?, ?)")->execute([$id_projeto, trim($_POST['descricao_rec']), $valor_rec, $_POST['data_rec'], $_SESSION['usuario_id']]);
            }
            // Marca a etapa como concluída
            $pdo->prepare("UPDATE acompanhamento_semanal SET diario_obra = ?, status = 'Concluído' WHERE id = ?")->execute([$relato_semana, $id_acomp]);
            
            $mensagem = "Etapa 100% finalizada com sucesso!"; $tipo_mensagem = "sucesso";
        }
    }

    // =========================================================================
    // DESFAZER FECHAMENTO
    // =========================================================================
    if ($_POST['acao'] === 'desfazer_semana' && $_SESSION['nivel_acesso'] === 'admin') {
        $id_acomp = filter_input(INPUT_POST, 'acompanhamento_id', FILTER_VALIDATE_INT);
        if ($id_acomp) {
            $stmt_titulo = $pdo->prepare("SELECT titulo_semana FROM acompanhamento_semanal WHERE id = ?");
            $stmt_titulo->execute([$id_acomp]);
            $semana = $stmt_titulo->fetch();

            // Reabre a etapa mas NÃO apaga as tarefas já concluídas (mantém o progresso salvo)
            $pdo->prepare("UPDATE acompanhamento_semanal SET status = 'Pendente', diario_obra = NULL WHERE id = ?")->execute([$id_acomp]);
            
            if ($semana) {
                $descricao_pagamento = 'Medição - ' . $semana['titulo_semana'];
                $pdo->prepare("UPDATE recebimentos SET status = 'Arquivado' WHERE projeto_id = ? AND descricao = ? AND status = 'Ativo'")->execute([$id_projeto, $descricao_pagamento]);
            }
            $mensagem = "Fechamento desfeito! O progresso da checklist foi mantido e o caixa corrigido."; $tipo_mensagem = "sucesso";
        }
    }
}

if (isset($_GET['excluir_arquivo']) && $_SESSION['nivel_acesso'] !== 'cliente') {
    $id_arquivo = filter_input(INPUT_GET, 'excluir_arquivo', FILTER_VALIDATE_INT);
    $stmt_del = $pdo->prepare("SELECT nome_seguro FROM arquivos WHERE id = :id_arq AND projeto = (SELECT nome FROM projetos WHERE id = :id_proj)");
    $stmt_del->execute([':id_arq' => $id_arquivo, ':id_proj' => $id_projeto]);
    if ($arquivo_para_deletar = $stmt_del->fetch()) {
        if (file_exists('uploads/' . $arquivo_para_deletar['nome_seguro'])) unlink('uploads/' . $arquivo_para_deletar['nome_seguro']);
        $pdo->prepare("DELETE FROM arquivos WHERE id = :id_arq")->execute([':id_arq' => $id_arquivo]);
        header("Location: detalhes_projeto.php?id=" . $id_projeto . "&msg=excluido"); exit;
    }
}

// =========================================================================
// CARREGAMENTO GERAL DE DADOS
// =========================================================================
try {
    $sql_projeto = "SELECT p.*, u.nome AS engenheiro_nome, u.telefone AS engenheiro_telefone, c.nome AS cliente_nome, c.telefone AS cliente_telefone FROM projetos p LEFT JOIN usuarios u ON p.engenheiro_responsavel = u.id LEFT JOIN clientes c ON p.cliente_id = c.id WHERE p.id = :id";
    if ($_SESSION['nivel_acesso'] === 'cliente') { $sql_projeto .= " AND p.cliente_id = :cliente_id"; }
    $sql_projeto .= " LIMIT 1";

    $stmt = $pdo->prepare($sql_projeto);
    $params = [':id' => $id_projeto];
    if ($_SESSION['nivel_acesso'] === 'cliente') { $params[':cliente_id'] = $_SESSION['usuario_id']; }
    $stmt->execute($params);
    $projeto = $stmt->fetch();
    if (!$projeto) { die("<h2 style='color:#fff; text-align:center; margin-top:50px;'>Obra não localizada ou acesso negado.</h2>"); }

    $stmt_arq = $pdo->prepare("SELECT a.*, u.nome AS enviado_por FROM arquivos a JOIN usuarios u ON a.usuario_id = u.id WHERE a.projeto = :nome_projeto ORDER BY a.data_envio DESC");
    $stmt_arq->execute([':nome_projeto' => $projeto['nome']]);
    $arquivos = $stmt_arq->fetchAll();

    $stmt_desp = $pdo->prepare("SELECT d.*, u.nome AS registrado_por FROM despesas d LEFT JOIN usuarios u ON d.usuario_id = u.id WHERE d.projeto_id = :id AND d.status = 'Ativo' ORDER BY d.data_despesa DESC, d.id DESC");
    $stmt_desp->execute([':id' => $id_projeto]);
    $despesas = $stmt_desp->fetchAll();

    $stmt_rec = $pdo->prepare("SELECT r.*, u.nome AS registrado_por FROM recebimentos r LEFT JOIN usuarios u ON r.usuario_id = u.id WHERE r.projeto_id = :id AND r.status = 'Ativo' ORDER BY r.data_pagamento DESC, r.id DESC");
    $stmt_rec->execute([':id' => $id_projeto]);
    $recebimentos = $stmt_rec->fetchAll();

    $total_orcado = (float) $projeto['valor'];
    $total_recebido = array_sum(array_column($recebimentos, 'valor'));
    $total_gasto = array_sum(array_column($despesas, 'valor'));
    
    $largura_barra_rec = min(($total_orcado > 0 ? ($total_recebido / $total_orcado) * 100 : 0), 100);
    $porcentagem_gasta = $total_orcado > 0 ? ($total_gasto / $total_orcado) * 100 : 0;
    $largura_barra_gasto = min($porcentagem_gasta, 100);
    $cor_barra_gasto = ($porcentagem_gasta >= 95) ? 'progresso-perigo' : (($porcentagem_gasta >= 75) ? 'progresso-alerta' : 'progresso-seguro');

    $periodos_gerados = [];
    $stmt_plan = $pdo->prepare("SELECT * FROM acompanhamento_semanal WHERE projeto_id = :id ORDER BY ordem ASC, data_inicio ASC");
    $stmt_plan->execute([':id' => $id_projeto]);
    $periodos_bd = $stmt_plan->fetchAll();

    if (count($periodos_bd) > 0) {
        foreach ($periodos_bd as $per) {
            $orcamento_semana = (float) $per['valor_orcado'];

            $stmt_g = $pdo->prepare("SELECT SUM(valor) as total FROM despesas WHERE projeto_id = :id AND status = 'Ativo' AND data_despesa BETWEEN :ini AND :fim");
            $stmt_g->execute([':id' => $id_projeto, ':ini' => $per['data_inicio'], ':fim' => $per['data_fim']]);
            $gasto_real = $stmt_g->fetch()['total'] ?? 0;

            $stmt_r = $pdo->prepare("SELECT SUM(valor) as total FROM recebimentos WHERE projeto_id = :id AND status = 'Ativo' AND data_pagamento BETWEEN :ini AND :fim");
            $stmt_r->execute([':id' => $id_projeto, ':ini' => $per['data_inicio'], ':fim' => $per['data_fim']]);
            $recebido_real = $stmt_r->fetch()['total'] ?? 0;

            $stmt_itens = $pdo->prepare("SELECT * FROM acompanhamento_itens WHERE acompanhamento_id = ? ORDER BY id ASC");
            $stmt_itens->execute([$per['id']]);
            $itens = $stmt_itens->fetchAll();

            $periodos_gerados[] = [
                'id_acomp' => $per['id'],
                'titulo' => $per['titulo_semana'],
                'inicio' => $per['data_inicio'],
                'fim' => $per['data_fim'],
                'orcamento_semana' => $orcamento_semana,
                'itens' => $itens,
                'gasto_real' => $gasto_real,
                'recebido_real' => $recebido_real,
                'status' => $per['status'],
                'relato' => $per['diario_obra']
            ];
        }
    }
} catch (PDOException $e) { die("Erro interno ao carregar dados."); }

function getStatusClass($status) {
    switch ($status) {
        case 'Em Orçamento': return 'status-orcamento'; case 'Em Andamento': return 'status-andamento';
        case 'Pausado': return 'status-pausado'; case 'Concluído': return 'status-concluido'; default: return 'status-andamento';
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
            <div style="margin-bottom: 20px; padding: 15px; border-radius: 4px; font-weight: bold; background-color: <?= $tipo_mensagem === 'sucesso' ? 'rgba(46, 204, 113, 0.2)' : 'rgba(231, 76, 60, 0.2)' ?>; color: <?= $tipo_mensagem === 'sucesso' ? '#2ecc71' : '#e74c3c' ?>; border-left: 4px solid <?= $tipo_mensagem === 'sucesso' ? '#2ecc71' : '#e74c3c' ?>;">
                <i class="fas <?= $tipo_mensagem === 'sucesso' ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i> <?= htmlspecialchars($mensagem) ?>
            </div>
        <?php endif; ?>

        <div class="projeto-header">
            <div class="projeto-titulo">
                <h1><span class="id-badge">#<?= str_pad($projeto['id'], 4, '0', STR_PAD_LEFT) ?></span> <?= htmlspecialchars($projeto['nome']) ?></h1>
                <span class="badge-status <?= getStatusClass($projeto['status']) ?>"><?= htmlspecialchars($projeto['status']) ?></span>
            </div>
            <?php if (isset($_SESSION['nivel_acesso']) && $_SESSION['nivel_acesso'] === 'admin'): ?>
                <div class="acoes-projeto" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                    <a href="editar_projeto.php?id=<?= $projeto['id'] ?>" class="btn-editar"><i class="fas fa-edit"></i> Editar Obra</a>
                    <a href="planejamento_cronograma.php?id=<?= $projeto['id'] ?>" class="btn-editar" style="background-color: #3498db; color: #fff;"><i class="fas fa-sitemap"></i> Planejar Metas</a>
                    <form method="POST" action="detalhes_projeto.php?id=<?= $id_projeto ?>" style="margin: 0;">
                        <input type="hidden" name="acao" value="excluir_projeto">
                        <button type="submit" class="btn-excluir-obra" onclick="return confirm('ATENÇÃO: Deseja excluir esta obra?');"><i class="fas fa-trash-alt"></i> Excluir</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>

        <div class="info-grid" style="margin-bottom: 20px;">
            <div class="info-card" style="border-left: 4px solid #3498db;">
                <h3 style="color: #3498db;"><i class="fas fa-handshake"></i> Cliente Contratante</h3>
                <p style="font-weight: bold;"><?= htmlspecialchars($projeto['cliente_nome'] ?? 'Cliente não vinculado') ?></p>
            </div>
            <div class="info-card destaque-rt">
                <h3><i class="fas fa-user-tie"></i> Engenheiro (RT)</h3>
                <p><?= htmlspecialchars($projeto['engenheiro_nome'] ?? 'Não Atribuído') ?></p>
            </div>
            <div class="info-card">
                <h3><i class="fas fa-calendar-alt"></i> Prazos Globais</h3>
                <p>Início: <?= date('d/m/Y', strtotime($projeto['data_inicio'])) ?></p>
                <p class="sub-info">Prev. Fim: <?= !empty($projeto['data_fim_prevista']) ? date('d/m/Y', strtotime($projeto['data_fim_prevista'])) : '--' ?></p>
            </div>
        </div>

        <div style="display: flex; gap: 20px; margin-bottom: 30px; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 300px; background: #1a1a1a; padding: 20px; border-radius: 8px; border-top: 3px solid #3498db; box-shadow: 0 4px 10px rgba(0,0,0,0.3);">
                <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                    <strong style="color: #3498db;"><i class="fas fa-arrow-up"></i> Recebido do Cliente</strong>
                    <strong style="color: #fff;">R$ <?= number_format($total_recebido, 2, ',', '.') ?></strong>
                </div>
                <div class="progress-container" style="height: 12px; margin-bottom: 5px;"><div class="progress-bar progresso-azul" style="width: <?= $largura_barra_rec ?>%;"></div></div>
                <span style="font-size: 0.8rem; color: #888;">Contrato Global: R$ <?= number_format($total_orcado, 2, ',', '.') ?></span>
            </div>

            <?php if ($_SESSION['nivel_acesso'] !== 'cliente'): ?>
            <div style="flex: 1; min-width: 300px; background: #1a1a1a; padding: 20px; border-radius: 8px; border-top: 3px solid #e74c3c; box-shadow: 0 4px 10px rgba(0,0,0,0.3);">
                <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                    <strong style="color: #e74c3c;"><i class="fas fa-arrow-down"></i> Gasto da Obra</strong>
                    <strong style="color: #fff;">R$ <?= number_format($total_gasto, 2, ',', '.') ?></strong>
                </div>
                <div class="progress-container" style="height: 12px; margin-bottom: 5px;"><div class="progress-bar <?= $cor_barra_gasto ?>" style="width: <?= $largura_barra_gasto ?>%;"></div></div>
                <span style="font-size: 0.8rem; color: #888;">Orçamento Previsto: R$ <?= number_format($total_orcado, 2, ',', '.') ?></span>
            </div>
            <?php endif; ?>
        </div>

        <div class="secao-arquivos" style="margin-bottom: 50px;">
            <div class="header-acoes" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2><i class="fas fa-tasks" style="color: #f1c40f;"></i> Diário da Obra</h2>
            </div>

            <div class="semanas-grid">
                <?php if (empty($periodos_gerados)): ?>
                    <p style="color: #888; grid-column: 1 / -1; text-align: center; padding: 20px; border: 1px dashed #444; border-radius: 8px;">Cronograma ainda não planejado pela engenharia.</p>
                <?php else: ?>
                    <?php foreach ($periodos_gerados as $p): 
                        $concluido = ($p['status'] === 'Concluído');
                        $estourou_orcamento = ($p['gasto_real'] > $p['orcamento_semana']);
                        $cor_borda = $concluido ? '#2ecc71' : (($_SESSION['nivel_acesso'] !== 'cliente' && $estourou_orcamento) ? '#e74c3c' : '#f1c40f');
                        
                        // CONTAGEM DE TAREFAS E VERIFICAÇÃO PARA O BOTÃO
                        $total_itens = count($p['itens']);
                        $itens_concluidos = 0;
                        foreach($p['itens'] as $it) { if($it['concluido'] == 1) $itens_concluidos++; }
                        
                        // A etapa só está "Pronta para Fechar" se não houver tarefas ou se todas estiverem marcadas
                        $tudo_concluido_para_fechar = ($total_itens == 0 || $total_itens == $itens_concluidos);
                    ?>
                        <div class="card-semana" style="border-left-color: <?= $cor_borda ?>; opacity: <?= $concluido ? '0.85' : '1' ?>;">
                            <div class="semana-header" style="flex-direction: column; align-items: flex-start; gap: 10px;">
                                <div style="display: flex; justify-content: space-between; width: 100%;">
                                    <h3 style="color: <?= $cor_borda ?>"><?= $p['titulo'] ?></h3>
                                    <?php if ($concluido): ?>
                                        <span class="badge-status status-concluido" style="font-size: 0.7rem;"><i class="fas fa-check"></i> Concluído</span>
                                    <?php else: ?>
                                        <span class="badge-status status-andamento" style="font-size: 0.7rem; background-color: #333; color: #aaa; border: none;">Pendente</span>
                                    <?php endif; ?>
                                </div>
                                <span class="semana-data" style="margin-top: -5px;"><?= date('d/m', strtotime($p['inicio'])) ?> até <?= date('d/m/Y', strtotime($p['fim'])) ?></span>
                            </div>

                            <?php if ($_SESSION['nivel_acesso'] !== 'cliente'): ?>
                            <div style="display: flex; justify-content: space-between; align-items: center; background: #222; padding: 10px; border-radius: 4px; margin-bottom: 15px; border-left: 3px solid <?= $cor_borda ?>;">
                                <span style="color: #aaa; font-size: 0.8rem; text-transform: uppercase; font-weight: bold;">Orçamento Planejado</span>
                                <strong style="color: <?= $cor_borda ?>; font-size: 1.1rem;">R$ <?= number_format($p['orcamento_semana'], 2, ',', '.') ?></strong>
                            </div>
                            <?php endif; ?>

                            <div style="margin-bottom: 15px; padding: 10px; background-color: rgba(241, 196, 15, 0.05); border-left: 3px solid <?= $cor_borda ?>; border-radius: 4px;">
                                <strong style="display: block; color: <?= $cor_borda ?>; font-size: 0.8rem; margin-bottom: 5px;"><i class="fas fa-tasks"></i> Orçamento Detalhado da Etapa</strong>
                                <ul style="list-style: none; padding-left: 0; margin-top: 5px; font-size: 0.85rem; color: #ccc;">
                                    <?php if(empty($p['itens'])): ?>
                                        <li style="color:#888; font-style:italic;">Sem tarefas...</li>
                                    <?php else: foreach($p['itens'] as $item): 
                                        $info_data = "";
                                        $cor_badge = "#888";
                                        if ($item['concluido'] == 1 && $item['data_realizada']) {
                                            $data_real_txt = date('d/m/Y', strtotime($item['data_realizada']));
                                            $atrasado = ($item['data_previsao'] && $item['data_realizada'] > $item['data_previsao']);
                                            if ($atrasado) {
                                                $info_data = "<i class='fas fa-exclamation-triangle'></i> Entregue: {$data_real_txt} (Atraso)";
                                                $cor_badge = "#e74c3c";
                                            } else {
                                                $info_data = "<i class='fas fa-check-double'></i> Entregue: {$data_real_txt}";
                                                $cor_badge = "#2ecc71";
                                            }
                                        } else {
                                            $data_prev = $item['data_previsao'] ? date('d/m/Y', strtotime($item['data_previsao'])) : '--';
                                            $info_data = "<i class='fas fa-calendar-day'></i> Previsto: " . $data_prev;
                                        }
                                    ?>
                                        <li style="margin-bottom: 8px; display: flex; justify-content: space-between; border-bottom: 1px solid #333; padding-bottom: 6px; gap: 10px; align-items: center; flex-wrap: wrap;">
                                            <span style="flex: 2; min-width: 200px;"><i class="fas <?= $item['concluido'] ? 'fa-check-square' : 'fa-square' ?>" style="color: <?= $item['concluido'] ? '#2ecc71' : '#888' ?>;"></i> <?= htmlspecialchars($item['descricao']) ?></span>
                                            <?php if ($_SESSION['nivel_acesso'] !== 'cliente'): ?>
                                                <strong style="color: #f1c40f; font-size: 0.85rem; flex: 1; text-align: right; margin-right: 15px;">R$ <?= number_format($item['valor_orcado'], 2, ',', '.') ?></strong>
                                            <?php endif; ?>
                                            <span style="font-size: 0.75rem; color: <?= $cor_badge ?>; background: #222; padding: 4px 8px; border-radius: 10px; white-space: nowrap; border: 1px solid <?= $cor_badge ?>;"><?= $info_data ?></span>
                                        </li>
                                    <?php endforeach; endif; ?>
                                </ul>
                            </div>

                            <?php if ($concluido && !empty($p['relato'])): ?>
                                <div style="margin-bottom: 15px; padding: 10px; background-color: rgba(46, 204, 113, 0.1); border-radius: 4px; border: 1px solid rgba(46, 204, 113, 0.3);">
                                    <strong style="display: block; color: #2ecc71; font-size: 0.8rem; margin-bottom: 5px;"><i class="fas fa-clipboard-check"></i> Relato da Engenharia</strong>
                                    <p style="margin: 0; font-size: 0.9rem; color: #ddd; line-height: 1.4;"><?= nl2br(htmlspecialchars($p['relato'])) ?></p>
                                </div>
                            <?php endif; ?>

                            <?php if ($_SESSION['nivel_acesso'] !== 'cliente'): ?>
                            <div class="semana-financeiro" style="flex-direction: column; gap: 10px; background-color: #111; padding: 15px; border-radius: 6px;">
                                <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 5px; border-bottom: 1px dashed #333;">
                                    <span style="color: #aaa; font-size: 0.8rem;">Gasto Real:</span>
                                    <strong style="color: <?= $estourou_orcamento ? '#e74c3c' : '#ccc' ?>;">R$ <?= number_format($p['gasto_real'], 2, ',', '.') ?></strong>
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span style="color: #aaa; font-size: 0.8rem;">Recebido:</span>
                                    <strong style="color: #3498db;">R$ <?= number_format($p['recebido_real'], 2, ',', '.') ?></strong>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($_SESSION['nivel_acesso'] !== 'cliente'): 
                                $json_itens = htmlspecialchars(json_encode($p['itens']), ENT_QUOTES, 'UTF-8');
                            ?>
                                <?php if (!$concluido): ?>
                                
                                    <?php if (!$tudo_concluido_para_fechar): ?>
                                        <button class="btn-receber-parcela" onclick="abrirModalProgresso('<?= $p['id_acomp'] ?>', '<?= $p['titulo'] ?>', '<?= $json_itens ?>')" style="border-color: #f1c40f; color: #f1c40f;">
                                            <i class="fas fa-tasks"></i> Atualizar Progresso (<?= $itens_concluidos ?>/<?= $total_itens ?>)
                                        </button>
                                        
                                    <?php else: ?>
                                        <button class="btn-receber-parcela" onclick="abrirModalReceberParcela('<?= $p['id_acomp'] ?>', '<?= $p['titulo'] ?>', '<?= $p['fim'] ?>', '<?= number_format($p['orcamento_semana'], 2, ',', '.') ?>')" style="border-color: #2ecc71; color: #2ecc71; background-color: rgba(46, 204, 113, 0.1);">
                                            <i class="fas fa-check-double"></i> Relatar e Fechar Etapa
                                        </button>
                                        <button onclick="abrirModalProgresso('<?= $p['id_acomp'] ?>', '<?= $p['titulo'] ?>', '<?= $json_itens ?>')" style="background:transparent; border:none; color:#888; font-size:0.8rem; margin-top:10px; cursor:pointer; text-decoration:underline;">Revisar Tarefas</button>
                                    <?php endif; ?>
                                    
                                <?php else: ?>
                                    <?php if ($_SESSION['nivel_acesso'] === 'admin'): ?>
                                    <form method="POST" action="detalhes_projeto.php?id=<?= $id_projeto ?>" style="margin-top: 15px;">
                                        <input type="hidden" name="acao" value="desfazer_semana">
                                        <input type="hidden" name="acompanhamento_id" value="<?= $p['id_acomp'] ?>">
                                        <button type="submit" onclick="return confirm('Deseja reabrir esta etapa? O relatório e o pagamento sumirão, MAS as tarefas continuarão marcadas como concluídas para você não perder o trabalho.');" style="width: 100%; border: 1px solid #e74c3c; color: #e74c3c; background: transparent; padding: 8px; border-radius: 4px; cursor: pointer; display: flex; justify-content: center; gap: 8px; align-items: center;">
                                            <i class="fas fa-undo"></i> Desfazer Fechamento
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div style="display: flex; gap: 20px; flex-wrap: wrap;">
            <div class="financeiro-card card-entradas" style="flex: 1; min-width: 400px; border-top: 2px solid #3498db;">
                <h2 style="color: #3498db; margin-top: 0; font-size: 1.2rem;"><i class="fas fa-list"></i> Histórico de Pagamentos</h2>
                <?php if ($_SESSION['nivel_acesso'] !== 'cliente'): ?>
                <form method="POST" action="detalhes_projeto.php?id=<?= $id_projeto ?>" style="margin-bottom: 20px; display: flex; gap: 10px;">
                    <input type="hidden" name="acao" value="adicionar_recebimento">
                    <input type="text" name="descricao_rec" placeholder="Sinal ou Avulso" required style="flex: 2; padding: 10px; background: #222; border: 1px solid #444; color: #fff; border-radius: 4px;">
                    <input type="date" name="data_rec" value="<?= date('Y-m-d') ?>" required style="flex: 1; padding: 10px; background: #222; border: 1px solid #444; color: #fff; border-radius: 4px;">
                    <input type="text" name="valor_rec" placeholder="R$ 0,00" onkeyup="mascaraMoeda(this)" required style="flex: 1; padding: 10px; background: #222; border: 1px solid #444; color: #fff; border-radius: 4px;">
                    <button type="submit" class="btn-submit btn-submit-azul" style="padding: 10px 15px;"><i class="fas fa-plus"></i></button>
                </form>
                <?php endif; ?>
                <div class="table-wrapper">
                    <table>
                        <thead><tr><th>Data</th><th>Descrição</th><th>Valor</th><?php if ($_SESSION['nivel_acesso'] !== 'cliente'): ?><th style="text-align: right;">Ações</th><?php endif; ?></tr></thead>
                        <tbody>
                            <?php if (empty($recebimentos)): ?>
                                <tr><td colspan="<?= $_SESSION['nivel_acesso'] !== 'cliente' ? '4' : '3' ?>" style="text-align: center; color: #888; padding: 20px;">Nenhum pagamento efetuado.</td></tr>
                            <?php else: foreach ($recebimentos as $rec): ?>
                                <tr>
                                    <td><?= date('d/m', strtotime($rec['data_pagamento'])) ?></td>
                                    <td style="font-size: 0.9rem;"><?= htmlspecialchars($rec['descricao']) ?></td>
                                    <td style="color: #3498db; font-weight: bold;">+ <?= number_format($rec['valor'], 2, ',', '.') ?></td>
                                    <?php if ($_SESSION['nivel_acesso'] !== 'cliente'): ?>
                                    <td style="text-align: right; white-space: nowrap;">
                                        <button type="button" class="btn-editar-sm" onclick="abrirModalEditarRec(<?= $rec['id'] ?>, '<?= htmlspecialchars($rec['descricao'], ENT_QUOTES) ?>', '<?= $rec['data_pagamento'] ?>', '<?= number_format($rec['valor'], 2, ',', '.') ?>')"><i class="fas fa-edit"></i></button>
                                        <form method="POST" style="display:inline;" action="detalhes_projeto.php?id=<?= $id_projeto ?>"><input type="hidden" name="acao" value="arquivar_recebimento"><input type="hidden" name="id_recebimento" value="<?= $rec['id'] ?>"><button type="submit" class="btn-arquivar-sm" onclick="return confirm('Arquivar este recebimento?');"><i class="fas fa-archive"></i></button></form>
                                    </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php if ($_SESSION['nivel_acesso'] !== 'cliente'): ?>
            <div class="financeiro-card" style="flex: 1; min-width: 400px; border-top: 2px solid #e74c3c;">
                <h2 style="color: #e74c3c; margin-top: 0; font-size: 1.2rem;"><i class="fas fa-file-invoice-dollar"></i> Lançar Despesa (Notas)</h2>
                <form method="POST" action="detalhes_projeto.php?id=<?= $id_projeto ?>">
                    <input type="hidden" name="acao" value="adicionar_despesa">
                    <div class="form-group" style="margin-bottom: 10px;"><input type="text" name="descricao_despesa" placeholder="Descrição (Ex: Cimento)" required style="width: 100%; padding: 10px; background: #222; border: 1px solid #444; color: #fff; border-radius: 4px;"></div>
                    <div style="display: flex; gap: 10px;"><input type="date" name="data_despesa" value="<?= date('Y-m-d') ?>" required style="flex: 1; padding: 10px; background: #222; border: 1px solid #444; color: #fff; border-radius: 4px;"><input type="text" name="valor_despesa" placeholder="R$ 0,00" onkeyup="mascaraMoeda(this)" required style="flex: 1; padding: 10px; background: #222; border: 1px solid #444; color: #fff; border-radius: 4px;"></div>
                    <button type="submit" class="btn-submit" style="width: 100%; margin-top: 10px; background-color: #e74c3c;"><i class="fas fa-arrow-down"></i> Registrar Gasto</button>
                </form>
                <div class="table-wrapper" style="margin-top: 20px;">
                    <table>
                        <thead><tr><th>Data</th><th>Despesa</th><th>Valor</th><th style="text-align: right;">Ações</th></tr></thead>
                        <tbody>
                            <?php if (empty($despesas)): ?>
                                <tr><td colspan="4" style="text-align: center; color: #888; padding: 20px;">Nenhum gasto registrado.</td></tr>
                            <?php else: foreach (array_slice($despesas, 0, 8) as $desp): ?>
                                <tr>
                                    <td><?= date('d/m', strtotime($desp['data_despesa'])) ?></td>
                                    <td style="font-size: 0.9rem;"><?= htmlspecialchars($desp['descricao']) ?></td>
                                    <td style="color: #e74c3c; font-weight: bold;">- <?= number_format($desp['valor'], 2, ',', '.') ?></td>
                                    <td style="text-align: right; white-space: nowrap;">
                                        <button type="button" class="btn-editar-sm" onclick="abrirModalGasto(<?= $desp['id'] ?>, '<?= htmlspecialchars($desp['descricao'], ENT_QUOTES) ?>', '<?= $desp['data_despesa'] ?>', '<?= number_format($desp['valor'], 2, ',', '.') ?>')"><i class="fas fa-edit"></i></button>
                                        <form method="POST" style="display:inline;" action="detalhes_projeto.php?id=<?= $id_projeto ?>"><input type="hidden" name="acao" value="arquivar_despesa"><input type="hidden" name="id_despesa" value="<?= $desp['id'] ?>"><button type="submit" class="btn-arquivar-sm" onclick="return confirm('Arquivar este gasto?');"><i class="fas fa-archive"></i></button></form>
                                    </td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="secao-arquivos" style="margin-top: 40px;">
            <h2><i class="fas fa-folder-open"></i> Documentação da Obra</h2>
            <div class="table-wrapper">
                <table>
                    <thead><tr><th>Tipo</th><th>Nome do Arquivo</th><th>Enviado por</th><th>Data</th><th>Ações</th></tr></thead>
                    <tbody>
                        <?php if (empty($arquivos)): ?>
                            <tr><td colspan="5" style="text-align: center; color: #888; padding: 30px;">Nenhum arquivo anexado.</td></tr>
                        <?php else: foreach ($arquivos as $arq): ?>
                            <tr>
                                <td style="color: var(--primary-yellow); font-weight: bold; font-size: 0.8rem;"><?= htmlspecialchars($arq['tipo_documento']) ?></td>
                                <td style="font-weight: bold;"><?= htmlspecialchars($arq['nome_original']) ?></td>
                                <td><?= htmlspecialchars($arq['enviado_por']) ?></td>
                                <td><?= date('d/m/Y', strtotime($arq['data_envio'])) ?></td>
                                <td>
                                    <a href="uploads/<?= htmlspecialchars($arq['nome_seguro']) ?>" target="_blank" class="btn-download" style="margin-right: 15px;">Abrir</a>
                                    <?php if ($_SESSION['nivel_acesso'] !== 'cliente'): ?>
                                    <a href="detalhes_projeto.php?id=<?= $projeto['id'] ?>&excluir_arquivo=<?= $arq['id'] ?>" class="btn-excluir" onclick="return confirm('Excluir este arquivo permanentemente?');">Excluir</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if ($_SESSION['nivel_acesso'] !== 'cliente'): ?>
        
        <div id="modalAtualizarProgresso" class="modal-overlay">
            <div class="modal-content" style="border-top-color: #f1c40f;">
                <span class="close-modal" onclick="fecharModalProgresso()">&times;</span>
                <h2 style="color: #f1c40f; margin-top: 0;"><i class="fas fa-tasks"></i> Progresso: <span id="texto_progresso_parcela"></span></h2>
                <form method="POST" action="detalhes_projeto.php?id=<?= $id_projeto ?>">
                    <input type="hidden" name="acao" value="atualizar_progresso">
                    <input type="hidden" name="acompanhamento_id" id="progresso_acomp_id">
                    
                    <p style="color: #aaa; font-size: 0.9rem; margin-bottom: 20px;">Marque as tarefas que a equipa já concluiu e guarde o progresso. A etapa só poderá ser fechada quando todas estiverem concluídas.</p>
                    
                    <div id="container_progresso_checklist"></div>
                    
                    <button type="submit" style="background-color: #f1c40f; color: #111; width: 100%; border: none; padding: 15px; font-weight: bold; border-radius: 4px; cursor: pointer; font-size: 1.1rem; margin-top: 15px;">Salvar Progresso</button>
                </form>
            </div>
        </div>

        <div id="modalReceberParcela" class="modal-overlay">
            <div class="modal-content" style="border-top-color: #2ecc71;">
                <span class="close-modal" onclick="fecharModalReceberParcela()">&times;</span>
                <h2 style="color: #2ecc71; margin-top: 0;"><i class="fas fa-check-double"></i> Fechar: <span id="texto_parcela"></span></h2>
                <form method="POST" action="detalhes_projeto.php?id=<?= $id_projeto ?>">
                    <input type="hidden" name="acao" value="receber_e_concluir_semana">
                    <input type="hidden" name="acompanhamento_id" id="parcela_acomp_id">
                    <input type="hidden" name="descricao_rec" id="parcela_descricao">
                    
                    <div style="background-color: rgba(46, 204, 113, 0.1); padding: 15px; border-radius: 4px; border: 1px solid #2ecc71; margin-bottom: 20px;">
                        <strong style="color: #2ecc71;"><i class="fas fa-thumbs-up"></i> 100% das Tarefas Concluídas!</strong>
                        <p style="color: #ccc; font-size: 0.85rem; margin: 5px 0 0 0;">Faça o relato final da engenharia e registre o faturamento para fechar esta etapa.</p>
                    </div>

                    <div class="form-group" style="margin-bottom: 20px;">
                        <label style="color: #fff; font-size: 0.95rem; font-weight: bold;"><i class="fas fa-pen"></i> Relato Final (Diário de Obra)</label>
                        <textarea name="relato_semana" rows="3" placeholder="O que foi executado nesta etapa? Dificuldades? Entregas?" required style="width: 100%; padding: 10px; background: #222; border: 1px solid #444; color: #fff; border-radius: 4px; box-sizing: border-box;"></textarea>
                    </div>

                    <div style="display: flex; gap: 15px; margin-bottom: 25px; align-items: flex-end;">
                        <div class="form-group" style="flex: 1;">
                            <label style="color: #aaa; font-size: 0.85rem; font-weight: bold;">Data do Pagto.</label>
                            <input type="date" name="data_rec" id="parcela_data" required style="width: 100%; padding: 10px; background: #222; border: 1px solid #444; color: #fff; border-radius: 4px; box-sizing: border-box;">
                        </div>
                        <div class="form-group" style="flex: 1.5;">
                            <label style="color: #aaa; font-size: 0.85rem; font-weight: bold;">Valor Recebido (R$)</label>
                            <input type="text" name="valor_rec" id="parcela_valor" onkeyup="mascaraMoeda(this)" style="width: 100%; padding: 10px; background: #222; border: 1px solid #3498db; color: #3498db; border-radius: 4px; box-sizing: border-box; font-size: 1.2rem; font-weight: bold;">
                        </div>
                    </div>
                    <button type="submit" style="background-color: #2ecc71; color: #111; width: 100%; border: none; padding: 15px; font-weight: bold; border-radius: 4px; cursor: pointer; font-size: 1.1rem;">Concluir Obra e Gravar Caixa</button>
                </form>
            </div>
        </div>

        <div id="modalEditarRecebimento" class="modal-overlay">...</div>
        <div id="modalEditarGasto" class="modal-overlay">...</div>
        <?php endif; ?>
    </main>

    <script>
        <?php if ($_SESSION['nivel_acesso'] !== 'cliente'): ?>
        
        function abrirModalProgresso(id_acomp, titulo, itensJson) {
            document.getElementById('texto_progresso_parcela').innerText = titulo;
            document.getElementById('progresso_acomp_id').value = id_acomp;
            
            let itens = JSON.parse(itensJson);
            let container = document.getElementById('container_progresso_checklist');
            container.innerHTML = '';
            
            let hoje = new Date().toISOString().split('T')[0];
            
            if(itens.length > 0) {
                itens.forEach(item => {
                    let isChecked = item.concluido == 1 ? 'checked' : '';
                    let dataPrevTxt = item.data_previsao ? item.data_previsao.split('-').reverse().join('/') : '--';
                    let dataReal = item.data_realizada ? item.data_realizada : (item.data_previsao ? item.data_previsao : hoje);
                    let displayDate = item.concluido == 1 ? 'block' : 'none';

                    container.innerHTML += `
                    <div style="background: #222; padding: 12px; border-radius: 4px; margin-bottom: 12px; border: 1px solid #333;">
                        <label style="display:flex; align-items:flex-start; gap:12px; color:#fff; cursor:pointer; font-size: 0.95rem; margin-bottom: 5px; font-weight: bold;">
                            <input type="checkbox" name="itens_concluidos[]" value="${item.id}" ${isChecked} style="transform: scale(1.3); margin-top: 3px;" onchange="document.getElementById('data_real_${item.id}').style.display = this.checked ? 'block' : 'none'">
                            ${item.descricao}
                        </label>
                        <div style="margin-left: 30px; color: #888; font-size: 0.8rem; margin-bottom: 5px;">
                            <i class="fas fa-bullseye"></i> Previsto para: ${dataPrevTxt}
                        </div>
                        <div id="data_real_${item.id}" style="display: ${displayDate}; margin-left: 30px; margin-top: 10px; border-top: 1px dashed #444; padding-top: 10px;">
                            <span style="color: #aaa; font-size: 0.8rem; margin-right: 10px;">Entregue no dia:</span>
                            <input type="date" name="datas_realizadas[${item.id}]" value="${dataReal}" style="padding: 6px; background: #111; border: 1px solid #555; color: #fff; border-radius: 4px; font-family: inherit;">
                        </div>
                    </div>`;
                });
            }
            document.getElementById('modalAtualizarProgresso').style.display = 'flex';
        }
        function fecharModalProgresso() { document.getElementById('modalAtualizarProgresso').style.display = 'none'; }

        function abrirModalReceberParcela(id_acomp, titulo, data_fim, valor_sugerido) {
            document.getElementById('texto_parcela').innerText = titulo;
            document.getElementById('parcela_acomp_id').value = id_acomp;
            document.getElementById('parcela_descricao').value = 'Medição - ' + titulo;
            document.getElementById('parcela_data').value = data_fim; 
            document.getElementById('parcela_valor').value = valor_sugerido;
            document.getElementById('modalReceberParcela').style.display = 'flex';
        }
        function fecharModalReceberParcela() { document.getElementById('modalReceberParcela').style.display = 'none'; }
        
        window.onclick = function(event) {
            if (event.target == document.getElementById('modalAtualizarProgresso')) fecharModalProgresso();
            if (event.target == document.getElementById('modalReceberParcela')) fecharModalReceberParcela();
        }
        <?php endif; ?>

        function mascaraMoeda(input) {
            let valor = input.value.replace(/\D/g, "");
            if (valor === "") { input.value = ""; return; }
            valor = (parseInt(valor) / 100).toFixed(2) + '';
            valor = valor.replace(".", ",");
            valor = valor.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
            input.value = valor;
        }
    </script>
</body>
</html>