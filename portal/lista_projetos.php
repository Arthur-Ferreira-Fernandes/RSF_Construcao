<?php
session_start();
require_once 'scripts/conexao.php';

// Trava de segurança
if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    header("Location: login.php");
    exit;
}

// 1. CARREGA OS ENGENHEIROS E CLIENTES (Para os filtros)
$lista_engenheiros = [];
$lista_clientes = [];
try {
    $stmt_eng = $pdo->query("SELECT id, nome FROM usuarios WHERE status = 'Ativo' ORDER BY nome ASC");
    $lista_engenheiros = $stmt_eng->fetchAll();

    $stmt_cli = $pdo->query("SELECT id, nome FROM clientes WHERE status = 'Ativo' ORDER BY nome ASC");
    $lista_clientes = $stmt_cli->fetchAll();
} catch (PDOException $e) {
    die("Erro ao carregar listas para os filtros.");
}

// 2. CAPTURA OS DADOS DO FILTRO (Via GET)
$filtro_nome = $_GET['nome_projeto'] ?? '';
$filtro_status = $_GET['status'] ?? '';
$filtro_engenheiro = $_GET['engenheiro_id'] ?? '';
$filtro_cliente = $_GET['cliente_id'] ?? ''; // NOVO: Filtro de Cliente
$filtro_data = $_GET['data_inicio'] ?? '';

// 3. MONTA A CONSULTA SQL DINAMICAMENTE
// Atualizado com o LEFT JOIN para a tabela de clientes
$sql = "SELECT p.id, p.nome, p.valor, p.status, p.data_inicio, 
               u.nome AS engenheiro_nome, 
               c.nome AS cliente_nome 
        FROM projetos p 
        LEFT JOIN usuarios u ON p.engenheiro_responsavel = u.id 
        LEFT JOIN clientes c ON p.cliente_id = c.id 
        WHERE 1=1";

$parametros = [];

if (!empty($filtro_nome)) {
    $sql .= " AND p.nome LIKE :nome";
    $parametros[':nome'] = '%' . $filtro_nome . '%';
}

if (!empty($filtro_status)) {
    $sql .= " AND p.status = :status";
    $parametros[':status'] = $filtro_status;
}

if (!empty($filtro_engenheiro)) {
    $sql .= " AND p.engenheiro_responsavel = :eng_id";
    $parametros[':eng_id'] = $filtro_engenheiro;
}

// NOVO: Adiciona a condição do cliente na pesquisa
if (!empty($filtro_cliente)) {
    $sql .= " AND p.cliente_id = :cliente_id";
    $parametros[':cliente_id'] = $filtro_cliente;
}

if (!empty($filtro_data)) {
    $sql .= " AND p.data_inicio = :data_inicio";
    $parametros[':data_inicio'] = $filtro_data;
}

$sql .= " ORDER BY p.id DESC";

// 4. EXECUTA A BUSCA
$projetos = [];
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($parametros);
    $projetos = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Erro ao carregar a lista de projetos.");
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
    <title>Portfólio de Obras | RSF Engenharia</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../styles/lista_projetos.css">
</head>
<body>

    <?php include 'header.php'; ?>

    <main class="container">
        
        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'projeto_excluido'): ?>
            <div style="background-color: rgba(46, 204, 113, 0.2); color: #2ecc71; border-left: 4px solid #2ecc71; padding: 15px; border-radius: 4px; margin-bottom: 25px; font-weight: bold;">
                <i class="fas fa-check-circle"></i> Obra excluída permanentemente do sistema.
            </div>
        <?php endif; ?>

        <div class="header-acoes">
            <h2><i class="fas fa-hard-hat"></i> Portfólio de Obras</h2>
            <a href="cadastrar_projeto.php" class="btn-novo"><i class="fas fa-plus"></i> Nova Obra</a>
        </div>

        <div class="filtros-card">
            <h3><i class="fas fa-filter"></i> Filtros de Pesquisa</h3>
            
            <form method="GET" action="lista_projetos.php" class="filtros-grid">
                
                <div class="filtro-group">
                    <label>Nome da Obra</label>
                    <input type="text" name="nome_projeto" placeholder="Ex: Galpão" value="<?= htmlspecialchars($filtro_nome) ?>">
                </div>

                <div class="filtro-group">
                    <label>Cliente</label>
                    <select name="cliente_id">
                        <option value="">Todos os Clientes</option>
                        <?php foreach ($lista_clientes as $cli): ?>
                            <option value="<?= $cli['id'] ?>" <?= $filtro_cliente == $cli['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cli['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filtro-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="">Todos os Status</option>
                        <option value="Em Orçamento" <?= $filtro_status == 'Em Orçamento' ? 'selected' : '' ?>>Em Orçamento</option>
                        <option value="Em Andamento" <?= $filtro_status == 'Em Andamento' ? 'selected' : '' ?>>Em Andamento</option>
                        <option value="Pausado" <?= $filtro_status == 'Pausado' ? 'selected' : '' ?>>Pausado</option>
                        <option value="Concluído" <?= $filtro_status == 'Concluído' ? 'selected' : '' ?>>Concluído</option>
                    </select>
                </div>

                <div class="filtro-group">
                    <label>Responsável (RT)</label>
                    <select name="engenheiro_id">
                        <option value="">Toda a Equipa</option>
                        <?php foreach ($lista_engenheiros as $eng): ?>
                            <option value="<?= $eng['id'] ?>" <?= $filtro_engenheiro == $eng['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($eng['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filtro-group">
                    <label>Data de Início</label>
                    <input type="date" name="data_inicio" value="<?= htmlspecialchars($filtro_data) ?>">
                </div>

                <div class="filtros-acoes">
                    <button type="submit" class="btn-filtrar"><i class="fas fa-search"></i> Buscar</button>
                    <a href="lista_projetos.php" class="btn-limpar"><i class="fas fa-times"></i> Limpar</a>
                </div>

            </form>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Nome do Projeto</th>
                        <th>Cliente</th>
                        <th>Engenheiro (RT)</th>
                        <th>Início</th>
                        <th>Status</th>
                        <th>Valor Orçado</th>
                        <th style="text-align: center;">Ação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($projetos)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; color: #888; padding: 30px;">
                                <i class="fas fa-search-minus fa-2x" style="margin-bottom: 10px; display: block;"></i>
                                Nenhuma obra encontrada com estes filtros.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($projetos as $proj): ?>
                            <tr class="linha-clicavel" onclick="window.location='detalhes_projeto.php?id=<?= $proj['id'] ?>'">
                                
                                <td class="nome-projeto" style="font-weight: bold; font-size: 1.0rem;">
                                    <?= htmlspecialchars($proj['nome']) ?>
                                </td>
                                
                                <td style="color: #3498db; font-weight: bold;">
                                    <?= htmlspecialchars($proj['cliente_nome'] ?? '--') ?>
                                </td>

                                <td><i class="fas fa-user-tie" style="color:#888; margin-right:5px;"></i> <?= htmlspecialchars($proj['engenheiro_nome'] ?? 'Não Atribuído') ?></td>
                                
                                <td style="font-size: 0.9rem; color: #ccc;"><?= date('d/m/Y', strtotime($proj['data_inicio'])) ?></td>
                                
                                <td>
                                    <span class="badge-status <?= getStatusClass($proj['status']) ?>">
                                        <?= htmlspecialchars($proj['status']) ?>
                                    </span>
                                </td>
                                
                                <td class="valor-obra">R$ <?= number_format($proj['valor'], 2, ',', '.') ?></td>
                                
                                <td style="text-align: center;">
                                    <span class="btn-detalhes"><i class="fas fa-folder-open"></i> Abrir</span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </main>

</body>
</html>