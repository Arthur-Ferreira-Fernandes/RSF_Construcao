<?php
session_start();
require_once 'scripts/conexao.php';

// Trava de segurança
if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    header("Location: login.php");
    exit;
}

try {
    $stmt1 = $pdo->query("SELECT COUNT(*) as total FROM projetos WHERE status = 'Em Andamento'");
    $obras_andamento = $stmt1->fetch()['total'];

    $stmt2 = $pdo->query("SELECT SUM(valor) as total_valor FROM projetos WHERE status = 'Em Andamento'");
    $valor_bruto = $stmt2->fetch()['total_valor'] ?? 0;
    $valor_total = number_format($valor_bruto, 2, ',', '.'); // Formata para R$ 1.500.000,00

    $stmt3 = $pdo->query("SELECT COUNT(*) as total FROM projetos WHERE status = 'Concluído'");
    $obras_concluidas = $stmt3->fetch()['total'];

    $stmt4 = $pdo->query("SELECT COUNT(*) as total FROM arquivos");
    $total_documentos = $stmt4->fetch()['total'];

    $stmt5 = $pdo->query("SELECT SUM(valor) as total_orcado FROM projetos WHERE status = 'Em Orçamento'");
    $valor_orcado_bruto = $stmt5->fetch()['total_orcado'] ?? 0;
    $valor_orcado = number_format($valor_orcado_bruto, 2, ',', '.');

} catch (PDOException $e) {
    $obras_andamento = $obras_concluidas = $total_documentos = 0;
    $valor_total = $valor_orcado = '0,00';
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
</head>
<body>

    <?php include 'header.php'; ?>

    <main class="dashboard-content">
        
        <div class="boas-vindas">
            <h1>Visão Geral da Operação</h1>
            <p>Bem-vindo ao painel central, <strong><?= htmlspecialchars($_SESSION['usuario_nome']) ?></strong>.</p>
        </div>

        <div class="analytics-grid">
            
            <div class="analytics-card card-orcamento">
                <div class="analytics-icone"><i class="fas fa-file-invoice-dollar"></i></div>
                <div class="analytics-info">
                    <h3>R$ <?= $valor_orcado ?></h3>
                    <p>Em Orçamento (Propostas)</p>
                </div>
            </div>

            <div class="analytics-card card-andamento">
                <div class="analytics-icone"><i class="fas fa-hard-hat"></i></div>
                <div class="analytics-info">
                    <h3><?= $obras_andamento ?></h3>
                    <p>Obras em Andamento</p>
                </div>
            </div>

            <div class="analytics-card card-valor">
                <div class="analytics-icone"><i class="fas fa-chart-line"></i></div>
                <div class="analytics-info">
                    <h3>R$ <?= $valor_total ?></h3>
                    <p>Capital em Obras Ativas</p>
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

        <div class="secao-atalhos">
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