<?php
session_start();
require_once 'scripts/conexao.php';

if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    header("Location: login.php");
    exit;
}

$id_projeto = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id_projeto) {
    die("<h2 style='color:#fff; text-align:center; margin-top:50px;'>Projeto não encontrado. <a href='lista_projetos.php' style='color:#FFCC00;'>Voltar</a></h2>");
}

if (isset($_GET['excluir_arquivo'])) {
    $id_arquivo = filter_input(INPUT_GET, 'excluir_arquivo', FILTER_VALIDATE_INT);
    
    if ($id_arquivo) {
        try {
            $stmt_del = $pdo->prepare("SELECT nome_seguro FROM arquivos WHERE id = :id_arq");
            $stmt_del->execute([':id_arq' => $id_arquivo]);
            $arquivo_para_deletar = $stmt_del->fetch();

            if ($arquivo_para_deletar) {
                $caminho_fisico = 'uploads/' . $arquivo_para_deletar['nome_seguro'];
                if (file_exists($caminho_fisico)) {
                    unlink($caminho_fisico);
                }

                $pdo->prepare("DELETE FROM arquivos WHERE id = :id_arq")->execute([':id_arq' => $id_arquivo]);
                
                header("Location: detalhes_projeto.php?id=" . $id_projeto . "&msg=excluido");
                exit;
            }
        } catch (PDOException $e) {
            die("Erro ao tentar excluir o arquivo.");
        }
    }
}

try {
    $sql_projeto = "SELECT p.*, e.nome AS engenheiro_nome, e.telefone AS engenheiro_telefone, e.email AS engenheiro_email 
                    FROM projetos p 
                    LEFT JOIN engenheiros e ON p.engenheiro_responsavel = e.id 
                    WHERE p.id = :id LIMIT 1";
    
    $stmt = $pdo->prepare($sql_projeto);
    $stmt->execute([':id' => $id_projeto]);
    $projeto = $stmt->fetch();

    if (!$projeto) {
        die("<h2 style='color:#fff; text-align:center; margin-top:50px;'>Obra não localizada. <a href='lista_projetos.php' style='color:#FFCC00;'>Voltar</a></h2>");
    }

    $sql_arquivos = "SELECT a.*, u.nome AS enviado_por 
                     FROM arquivos a 
                     JOIN usuarios u ON a.usuario_id = u.id 
                     WHERE a.projeto = :nome_projeto 
                     ORDER BY a.data_envio DESC";
    
    $stmt_arq = $pdo->prepare($sql_arquivos);
    $stmt_arq->execute([':nome_projeto' => $projeto['nome']]);
    $arquivos = $stmt_arq->fetchAll();

} catch (PDOException $e) {
    die("Erro interno ao carregar os dados.");
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
        
        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'excluido'): ?>
            <div class="alerta-sucesso">
                <i class="fas fa-check-circle"></i> Arquivo excluído com sucesso do sistema.
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'atualizado'): ?>
            <div class="alerta-sucesso">
                <i class="fas fa-check-circle"></i> Informações do projeto atualizadas com sucesso!
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

        <div class="info-grid">
            <div class="info-card destaque-valor">
                <h3><i class="fas fa-dollar-sign"></i> Valor Orçado</h3>
                <p class="valor">R$ <?= number_format($projeto['valor'], 2, ',', '.') ?></p>
                <p class="sub-info">Custo estimado da obra</p>
            </div>

            <div class="info-card destaque-rt">
                <h3><i class="fas fa-user-tie"></i> Responsável Técnico</h3>
                <p><?= htmlspecialchars($projeto['engenheiro_nome'] ?? 'Não Atribuído') ?></p>
                <p class="sub-info"><i class="fas fa-phone"></i> <?= htmlspecialchars($projeto['engenheiro_telefone'] ?? '--') ?></p>
            </div>

            <div class="info-card">
                <h3><i class="fas fa-calendar-alt"></i> Cronograma</h3>
                <p>Início: <?= date('d/m/Y', strtotime($projeto['data_inicio'])) ?></p>
                <p class="sub-info">Criado no sistema em: <?= date('d/m/Y', strtotime($projeto['criado_em'])) ?></p>
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
                            <tr>
                                <td colspan="5" style="text-align: center; color: #888; padding: 30px;">
                                    Nenhum arquivo anexado a este projeto ainda.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($arquivos as $arq): ?>
                                <tr>
                                    <td style="color: var(--primary-yellow); font-weight: bold; font-size: 0.8rem;">
                                        <?= htmlspecialchars($arq['tipo_documento']) ?>
                                    </td>
                                    <td style="font-weight: bold;"><?= htmlspecialchars($arq['nome_original']) ?></td>
                                    <td><?= htmlspecialchars($arq['enviado_por']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($arq['data_envio'])) ?></td>
                                    
                                    <td>
                                        <a href="uploads/<?= htmlspecialchars($arq['nome_seguro']) ?>" target="_blank" class="btn-download">
                                            <i class="fas fa-external-link-alt"></i> Abrir
                                        </a>
                                        
                                        <a href="detalhes_projeto.php?id=<?= $projeto['id'] ?>&excluir_arquivo=<?= $arq['id'] ?>" 
                                           class="btn-excluir" 
                                           onclick="return confirm('ATENÇÃO: Tem certeza que deseja excluir este arquivo permanentemente do servidor?');">
                                            <i class="fas fa-trash-alt"></i> Excluir
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>

</body>
</html>