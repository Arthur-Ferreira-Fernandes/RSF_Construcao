<?php
session_start();
require_once 'scripts/conexao.php';

if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    header("Location: login.php");
    exit;
}

$lista_engenheiros = [];
try {
    $stmt_eng = $pdo->query("SELECT id, nome FROM usuarios WHERE status = 'Ativo' ORDER BY nome ASC");
    $lista_engenheiros = $stmt_eng->fetchAll();
} catch (PDOException $e) {
    die("Erro ao carregar lista de engenheiros para o filtro.");
}

$filtro_nome = $_GET['nome_projeto'] ?? '';
$filtro_status = $_GET['status'] ?? '';
$filtro_engenheiro = $_GET['engenheiro_id'] ?? '';
$filtro_data = $_GET['data_inicio'] ?? '';

$sql = "SELECT p.id, p.nome, p.valor, p.status, p.data_inicio, u.nome AS engenheiro_nome 
        FROM projetos p 
        LEFT JOIN usuarios u ON p.engenheiro_responsavel = u.id 
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

if (!empty($filtro_data)) {
    $sql .= " AND p.data_inicio = :data_inicio";
    $parametros[':data_inicio'] = $filtro_data;
}

$sql .= " ORDER BY p.id DESC";

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
    <title>Obras em Andamento | RSF Engenharia</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../styles/lista_projetos.css">
</head>
<body>

    <?php include 'header.php'; ?>

    <main class="container">
        
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
                        <option value="">Toda a Equipe</option>
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
                        <th>ID</th>
                        <th>Nome do Projeto</th>
                        <th>Engenheiro (RT)</th>
                        <th>Início</th>
                        <th>Status</th>
                        <th>Valor (R$)</th>
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
                                
                                <td class="id-projeto">#<?= str_pad($proj['id'], 4, '0', STR_PAD_LEFT) ?></td>
                                <td class="nome-projeto"><?= htmlspecialchars($proj['nome']) ?></td>
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