<?php
session_start();
require_once 'scripts/conexao.php';

// Trava de segurança
if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    header("Location: login.php");
    exit;
}

// =========================================================================
// INTELIGÊNCIA DE DADOS (ANALYTICS GLOBAL)
// =========================================================================
try {
    // ---- MÉTRICAS OPERACIONAIS E COMERCIAIS ----
    $stmt1 = $pdo->query("SELECT COUNT(*) as total FROM projetos WHERE status = 'Em Andamento'");
    $obras_andamento = $stmt1->fetch()['total'];

    $stmt2 = $pdo->query("SELECT SUM(valor) as total_valor FROM projetos WHERE status = 'Em Andamento'");
    $valor_bruto = $stmt2->fetch()['total_valor'] ?? 0;
    $valor_total = number_format($valor_bruto, 2, ',', '.');

    $stmt3 = $pdo->query("SELECT COUNT(*) as total FROM projetos WHERE status = 'Concluído'");
    $obras_concluidas = $stmt3->fetch()['total'];

    $stmt4 = $pdo->query("SELECT COUNT(*) as total FROM arquivos");
    $total_documentos = $stmt4->fetch()['total'];

    $stmt5 = $pdo->query("SELECT SUM(valor) as total_orcado FROM projetos WHERE status = 'Em Orçamento'");
    $valor_orcado_bruto = $stmt5->fetch()['total_orcado'] ?? 0;
    $valor_orcado = number_format($valor_orcado_bruto, 2, ',', '.');

    // ---- NOVAS MÉTRICAS DE FLUXO DE CAIXA ----
    // Total de Entradas (Recebimentos Ativos de TODAS as obras)
    $stmt6 = $pdo->query("SELECT SUM(valor) as total_recebido FROM recebimentos WHERE status = 'Ativo'");
    $total_recebido_bruto = $stmt6->fetch()['total_recebido'] ?? 0;
    $total_recebido = number_format($total_recebido_bruto, 2, ',', '.');

    // Total de Saídas (Despesas Ativas de TODAS as obras)
    $stmt7 = $pdo->query("SELECT SUM(valor) as total_gasto FROM despesas WHERE status = 'Ativo'");
    $total_gasto_bruto = $stmt7->fetch()['total_gasto'] ?? 0;
    $total_gasto = number_format($total_gasto_bruto, 2, ',', '.');

    // Saldo Global em Caixa da Construtora
    $saldo_global_bruto = $total_recebido_bruto - $total_gasto_bruto;
    $saldo_global = number_format($saldo_global_bruto, 2, ',', '.');
    
    // Define a cor do cartão de Saldo (Verde se for positivo/zero, Vermelho se for negativo)
    $cor_saldo = $saldo_global_bruto >= 0 ? 'card-lucro' : 'card-prejuizo';

} catch (PDOException $e) {
    $obras_andamento = $obras_concluidas = $total_documentos = 0;
    $valor_total = $valor_orcado = $total_recebido = $total_gasto = $saldo_global = '0,00';
    $cor_saldo = 'card-lucro';
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | RSF Engenharia</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../styles/dashboard.css">
    <link rel="icon" type="image/png" href="../img/logo.png">
    
</head>
<body>

    <?php include 'header.php'; ?>

    <main class="dashboard-content">
        
        <div class="boas-vindas">
            <h1>Visão Geral da Operação</h1>
            <p>Bem-vindo ao painel central, <strong><?= htmlspecialchars($_SESSION['usuario_nome']) ?></strong>.</p>
        </div>

        <h2 class="secao-titulo"><i class="fas fa-wallet" style="color: #2ecc71;"></i> Fluxo de Caixa Consolidado</h2>
        <div class="analytics-grid" style="margin-bottom: 20px;">
            
            <div class="analytics-card card-receita">
                <div class="analytics-icone"><i class="fas fa-arrow-circle-up"></i></div>
                <div class="analytics-info">
                    <h3>R$ <?= $total_recebido ?></h3>
                    <p>Total Recebido (Clientes)</p>
                </div>
            </div>

            <div class="analytics-card card-despesa">
                <div class="analytics-icone"><i class="fas fa-arrow-circle-down"></i></div>
                <div class="analytics-info">
                    <h3>R$ <?= $total_gasto ?></h3>
                    <p>Total Gasto (Custo Obras)</p>
                </div>
            </div>

            <div class="analytics-card <?= $cor_saldo ?>">
                <div class="analytics-icone"><i class="fas fa-cash-register"></i></div>
                <div class="analytics-info">
                    <h3>R$ <?= $saldo_global ?></h3>
                    <p>Saldo Global em Caixa</p>
                </div>
            </div>

        </div>

        <h2 class="secao-titulo"><i class="fas fa-hard-hat" style="color: var(--primary-yellow);"></i> Operação e Comercial</h2>
        <div class="analytics-grid">
            
            <div class="analytics-card card-orcamento">
                <div class="analytics-icone"><i class="fas fa-file-invoice-dollar"></i></div>
                <div class="analytics-info">
                    <h3>R$ <?= $valor_orcado ?></h3>
                    <p>Em Orçamento (Propostas)</p>
                </div>
            </div>

            <div class="analytics-card card-andamento">
                <div class="analytics-icone"><i class="fas fa-tools"></i></div>
                <div class="analytics-info">
                    <h3><?= $obras_andamento ?></h3>
                    <p>Obras em Andamento</p>
                </div>
            </div>

            <div class="analytics-card card-valor">
                <div class="analytics-icone"><i class="fas fa-chart-line"></i></div>
                <div class="analytics-info">
                    <h3>R$ <?= $valor_total ?></h3>
                    <p>Capital Orçado (Obras Ativas)</p>
                </div>
            </div>

            <div class="analytics-card card-concluido">
                <div class="analytics-icone"><i class="fas fa-check-double"></i></div>
                <div class="analytics-info">
                    <h3><?= $obras_concluidas ?></h3>
                    <p>Projetos Entregues</p>
                </div>
            </div>

        </div>

        <div class="secao-atalhos" style="margin-top: 50px;">
            <h2><i class="fas fa-bolt"></i> Acesso Rápido</h2>
            
            <div class="grid-cards">
                <a href="cadastrar_projeto.php">
                    <div class="card">
                        <i class="fas fa-drafting-compass"></i>
                        <h3>Novo Projeto</h3>
                        <p>Abertura de nova obra, definição de escopo técnico, orçamento e alocação de RT.</p>
                    </div>
                </a>

                <a href="lista_projetos.php">
                    <div class="card">
                        <i class="fas fa-clipboard-list"></i>
                        <h3>Portfólio de Obras</h3>
                        <p>Visão geral de todos os projetos, status atual e acompanhamento financeiro.</p>
                    </div>
                </a>

                <a href="arquivos.php">
                    <div class="card">
                        <i class="fas fa-folder-open"></i>
                        <h3>Gestor de Arquivos</h3>
                        <p>Envie e consulte Plantas, ARTs, Relatórios Fotográficos e Alvarás das obras.</p>
                    </div>
                </a>

                <a href="clientes.php">
                    <div class="card" style="border-left-color: #3498db;">
                        <i class="fas fa-handshake" style="color: #3498db;"></i>
                        <h3>Base de Clientes</h3>
                        <p>Cadastre contratantes, gerencie os contatos e acompanhe quantas obras cada um possui.</p>
                    </div>
                </a>

                <?php if (isset($_SESSION['nivel_acesso']) && $_SESSION['nivel_acesso'] === 'admin'): ?>
                <a href="gerenciar_equipe.php">
                    <div class="card" style="border-left-color: #e74c3c;">
                        <i class="fas fa-users-cog" style="color: #e74c3c;"></i>
                        <h3>Equipe e Acessos</h3>
                        <p>Gerencie a equipe, altere níveis de permissão (Admin/Comum) e libere novos acessos.</p>
                    </div>
                </a>
                <?php endif; ?>
            </div>
        </div>

    </main>

</body>
</html>