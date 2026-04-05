<?php
session_start();
require_once 'scripts/conexao.php';

// Trava de segurança principal (Apenas Admin ou Gestor)
if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true || $_SESSION['nivel_acesso'] !== 'admin') {
    die("<h2 style='color:#fff; text-align:center; margin-top:50px;'>Acesso Negado. Apenas administradores podem editar as informações base da obra. <a href='dashboard.php' style='color:#FFCC00;'>Voltar</a></h2>");
}

$id_projeto = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id_projeto) {
    die("<h2 style='color:#fff; text-align:center; margin-top:50px;'>Projeto inválido. <a href='lista_projetos.php' style='color:#FFCC00;'>Voltar</a></h2>");
}

$mensagem = '';
$tipo_mensagem = '';

// =========================================================================
// PROCESSA A ATUALIZAÇÃO DO PROJETO NO BANCO DE DADOS
// =========================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $descricao = trim($_POST['descricao']);
    $endereco = trim($_POST['endereco']);
    $engenheiro_responsavel = !empty($_POST['engenheiro_responsavel']) ? $_POST['engenheiro_responsavel'] : null;
    $cliente_id = !empty($_POST['cliente_id']) ? $_POST['cliente_id'] : null;
    $data_inicio = $_POST['data_inicio'];
    
    // NOVOS CAMPOS CAPTURADOS DO FORMULÁRIO:
    $data_fim_prevista = !empty($_POST['data_fim_prevista']) ? $_POST['data_fim_prevista'] : null;
    $frequencia_medicao = $_POST['frequencia_medicao'] ?? 'Semanal';
    
    $status = $_POST['status'];

    $valor_post = $_POST['valor'] ?? '0';
    $valor_post = str_replace('.', '', $valor_post);
    $valor_post = str_replace(',', '.', $valor_post);
    $valor = (float) $valor_post;

    if (empty($nome) || empty($data_inicio)) {
        $mensagem = "O nome da obra e a data de início são obrigatórios.";
        $tipo_mensagem = "erro";
    } else {
        try {
            // A INSTRUÇÃO SQL CORRIGIDA: Agora inclui data_fim_prevista e frequencia_medicao
            $sql = "UPDATE projetos 
                    SET nome = :nome, descricao = :descricao, endereco = :endereco, 
                        engenheiro_responsavel = :engenheiro_responsavel, cliente_id = :cliente_id, 
                        data_inicio = :data_inicio, data_fim_prevista = :data_fim_prevista, 
                        frequencia_medicao = :frequencia_medicao, valor = :valor, status = :status 
                    WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            
            $stmt->execute([
                ':nome' => $nome,
                ':descricao' => $descricao,
                ':endereco' => $endereco,
                ':engenheiro_responsavel' => $engenheiro_responsavel,
                ':cliente_id' => $cliente_id,
                ':data_inicio' => $data_inicio,
                ':data_fim_prevista' => $data_fim_prevista,
                ':frequencia_medicao' => $frequencia_medicao,
                ':valor' => $valor,
                ':status' => $status,
                ':id' => $id_projeto
            ]);

            header("Location: detalhes_projeto.php?id=" . $id_projeto . "&msg=atualizado");
            exit;

        } catch (PDOException $e) {
            $mensagem = "Erro ao atualizar os dados do projeto no banco de dados.";
            $tipo_mensagem = "erro";
        }
    }
}

// =========================================================================
// CARREGA OS DADOS ATUAIS DA OBRA PARA PREENCHER O FORMULÁRIO
// =========================================================================
try {
    $stmt = $pdo->prepare("SELECT * FROM projetos WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id_projeto]);
    $projeto = $stmt->fetch();
    
    if (!$projeto) {
        die("<h2 style='color:#fff; text-align:center; margin-top:50px;'>Obra não encontrada.</h2>");
    }
} catch (PDOException $e) {
    die("Erro ao carregar os dados.");
}

// =========================================================================
// CARREGA LISTAS PARA OS DROPDOWNS (SELECTS)
// =========================================================================
$stmt_eng = $pdo->query("SELECT id, nome FROM usuarios WHERE status = 'Ativo' ORDER BY nome ASC");
$engenheiros = $stmt_eng->fetchAll();

$stmt_cli = $pdo->query("SELECT id, nome FROM clientes WHERE status = 'Ativo' ORDER BY nome ASC");
$clientes = $stmt_cli->fetchAll();

$valor_formatado = number_format($projeto['valor'], 2, ',', '.');
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Obra | RSF Engenharia</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../styles/projetos.css">
</head>
<body>

    <?php include 'header.php'; ?>

    <main class="container">
        
        <?php if (!empty($mensagem)): ?>
            <div class="alerta <?= $tipo_mensagem ?>">
                <?= htmlspecialchars($mensagem) ?>
            </div>
        <?php endif; ?>

        <div class="form-card" style="max-width: 800px; margin: 0 auto;">
            <h2><i class="fas fa-edit"></i> Editar Dados da Obra</h2>
            <p style="color: #aaa; font-size: 0.9rem; margin-top: -15px; margin-bottom: 25px;">
                Adicione a data de previsão de término para que o sistema gere as metas automaticamente.
            </p>
            
            <form method="POST" action="editar_projeto.php?id=<?= $id_projeto ?>">
                <div class="form-grid">
                    
                    <div class="form-group full-width">
                        <label for="nome">Nome do Projeto / Obra *</label>
                        <div class="input-icon-wrapper">
                            <i class="fas fa-hard-hat"></i>
                            <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($projeto['nome']) ?>" required>
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <label for="descricao">Escopo / Descrição Técnica</label>
                        <textarea id="descricao" name="descricao" rows="3"><?= htmlspecialchars($projeto['descricao']) ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="cliente_id">Cliente Contratante</label>
                        <div class="input-icon-wrapper">
                            <i class="fas fa-handshake" style="z-index: 1;"></i>
                            <select id="cliente_id" name="cliente_id" style="padding-left: 40px; width: 100%;">
                                <option value="">-- Sem Cliente Vinculado --</option>
                                <?php foreach ($clientes as $cli): ?>
                                    <option value="<?= $cli['id'] ?>" <?= ($projeto['cliente_id'] == $cli['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cli['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="engenheiro_responsavel">Engenheiro (RT)</label>
                        <div class="input-icon-wrapper">
                            <i class="fas fa-user-tie" style="z-index: 1;"></i>
                            <select id="engenheiro_responsavel" name="engenheiro_responsavel" style="padding-left: 40px; width: 100%;">
                                <option value="">-- Não Atribuído --</option>
                                <?php foreach ($engenheiros as $eng): ?>
                                    <option value="<?= $eng['id'] ?>" <?= ($projeto['engenheiro_responsavel'] == $eng['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($eng['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <label for="endereco">Endereço da Obra</label>
                        <div class="input-icon-wrapper">
                            <i class="fas fa-map-marker-alt"></i>
                            <input type="text" id="endereco" name="endereco" value="<?= htmlspecialchars($projeto['endereco']) ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="data_inicio">Data de Início *</label>
                        <div class="input-icon-wrapper">
                            <i class="fas fa-calendar-alt"></i>
                            <input type="date" id="data_inicio" name="data_inicio" value="<?= $projeto['data_inicio'] ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="data_fim_prevista">Previsão de Término</label>
                        <div class="input-icon-wrapper">
                            <i class="fas fa-flag-checkered"></i>
                            <input type="date" id="data_fim_prevista" name="data_fim_prevista" value="<?= htmlspecialchars($projeto['data_fim_prevista'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="frequencia_medicao">Frequência das Metas</label>
                        <div class="input-icon-wrapper">
                            <i class="fas fa-ruler-combined" style="z-index: 1;"></i>
                            <select id="frequencia_medicao" name="frequencia_medicao" style="padding-left: 40px; width: 100%;">
                                <option value="Semanal" <?= ($projeto['frequencia_medicao'] == 'Semanal') ? 'selected' : '' ?>>Semanal</option>
                                <option value="Diária" <?= ($projeto['frequencia_medicao'] == 'Diária') ? 'selected' : '' ?>>Diária</option>
                                <option value="Mensal" <?= ($projeto['frequencia_medicao'] == 'Mensal') ? 'selected' : '' ?>>Mensal</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="status">Status da Obra *</label>
                        <div class="input-icon-wrapper">
                            <i class="fas fa-info-circle" style="z-index: 1;"></i>
                            <select id="status" name="status" style="padding-left: 40px; width: 100%;">
                                <option value="Em Orçamento" <?= ($projeto['status'] == 'Em Orçamento') ? 'selected' : '' ?>>Em Orçamento</option>
                                <option value="Em Andamento" <?= ($projeto['status'] == 'Em Andamento') ? 'selected' : '' ?>>Em Andamento</option>
                                <option value="Pausado" <?= ($projeto['status'] == 'Pausado') ? 'selected' : '' ?>>Pausado</option>
                                <option value="Concluído" <?= ($projeto['status'] == 'Concluído') ? 'selected' : '' ?>>Concluído</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <label for="valor">Valor Orçado Total (R$)</label>
                        <div class="input-icon-wrapper">
                            <i class="fas fa-dollar-sign"></i>
                            <input type="text" id="valor" name="valor" value="<?= $valor_formatado ?>" onkeyup="mascaraMoeda(this)">
                        </div>
                    </div>

                </div>

                <div class="form-actions" style="margin-top: 20px;">
                    <a href="detalhes_projeto.php?id=<?= $id_projeto ?>" class="btn-submit" style="background-color: transparent; border: 1px solid #aaa; color: #aaa; text-decoration: none; text-align: center;">Cancelar</a>
                    <button type="submit" class="btn-submit" style="width: 100%;"><i class="fas fa-save"></i> Salvar Alterações</button>
                </div>
            </form>
        </div>

    </main>

    <script>
        function mascaraMoeda(input) {
            let valor = input.value;
            valor = valor.replace(/\D/g, "");
            if (valor === "") { input.value = ""; return; }
            valor = (parseInt(valor) / 100).toFixed(2) + '';
            valor = valor.replace(".", ",");
            valor = valor.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
            input.value = valor;
        }
    </script>
</body>
</html>