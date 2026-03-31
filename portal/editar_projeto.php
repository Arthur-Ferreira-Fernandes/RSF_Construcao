<?php
session_start();
require_once 'scripts/conexao.php';

if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    header("Location: login.php");
    exit;
}

$mensagem = '';
$tipo_mensagem = '';

$id_projeto = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id_projeto && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("<h2 style='color:#fff; text-align:center; margin-top:50px;'>ID de projeto inválido. <a href='lista_projetos.php' style='color:#FFCC00;'>Voltar</a></h2>");
}

$lista_engenheiros = [];
try {
    $stmt_eng = $pdo->query("SELECT id, nome FROM usuarios WHERE status = 'Ativo' ORDER BY nome ASC");
    $lista_engenheiros = $stmt_eng->fetchAll();
} catch (PDOException $e) {
    die("Erro ao carregar lista de engenheiros.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $id_projeto_post = filter_input(INPUT_POST, 'id_projeto', FILTER_VALIDATE_INT);
    $nome = trim($_POST['nome']);
    $engenheiro_id = trim($_POST['engenheiro_id']);
    $descricao = trim($_POST['descricao']);
    $endereco = trim($_POST['endereco']);
    $data_inicio = $_POST['data_inicio'];
    $status = $_POST['status'];
    
    $valor_post = $_POST['valor'] ?? '0';
    $valor_post = str_replace('.', '', $valor_post);
    $valor_post = str_replace(',', '.', $valor_post);
    $valor = (float) $valor_post;

    if (empty($nome) || empty($engenheiro_id) || empty($valor) || empty($endereco) || empty($data_inicio)) {
        $mensagem = "Preencha todos os campos obrigatórios.";
        $tipo_mensagem = "erro";
        $id_projeto = $id_projeto_post; 
    } else {
        try {
            $sql = "UPDATE projetos SET 
                    nome = :nome, 
                    engenheiro_responsavel = :engenheiro, 
                    valor = :valor, 
                    descricao = :descricao, 
                    endereco = :endereco, 
                    data_inicio = :data_inicio,
                    status = :status
                    WHERE id = :id";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nome' => $nome,
                ':engenheiro' => $engenheiro_id,
                ':valor' => $valor,
                ':descricao' => $descricao,
                ':endereco' => $endereco,
                ':data_inicio' => $data_inicio,
                ':status' => $status,
                ':id' => $id_projeto_post
            ]);

            header("Location: detalhes_projeto.php?id=" . $id_projeto_post . "&msg=atualizado");
            exit;

        } catch (PDOException $e) {
            $mensagem = "Erro ao atualizar o projeto no banco de dados.";
            $tipo_mensagem = "erro";
            $id_projeto = $id_projeto_post;
        }
    }
}

try {
    $stmt = $pdo->prepare("SELECT * FROM projetos WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id_projeto]);
    $projeto_atual = $stmt->fetch();

    if (!$projeto_atual) {
        die("<h2 style='color:#fff; text-align:center; margin-top:50px;'>Obra não encontrada. <a href='lista_projetos.php' style='color:#FFCC00;'>Voltar</a></h2>");
    }
} catch (PDOException $e) {
    die("Erro ao buscar dados do projeto.");
}

$valor_formatado = number_format($projeto_atual['valor'], 2, ',', '.');
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Projeto | RSF Engenharia</title>
    
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

        <div class="form-card">
            <h2 style="color: #3498db;"><i class="fas fa-edit"></i> Editar Obra: <?= htmlspecialchars($projeto_atual['nome']) ?></h2>
            
            <form method="POST" action="editar_projeto.php">
                
                <input type="hidden" name="id_projeto" value="<?= $id_projeto ?>">

                <div class="form-grid">
                    
                    <div class="form-group full-width">
                        <label for="nome">Nome do Projeto *</label>
                        <div class="input-icon-wrapper">
                            <i class="fas fa-building"></i>
                            <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($projeto_atual['nome']) ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="status">Status da Obra *</label>
                        <div class="input-icon-wrapper">
                            <i class="fas fa-tasks" style="z-index: 1;"></i>
                            <select id="status" name="status" required style="padding-left: 40px; width: 100%;">
                                <option value="Em Orçamento" <?= ($projeto_atual['status'] == 'Em Orçamento') ? 'selected' : '' ?>>Em Orçamento</option>
                                <option value="Em Andamento" <?= ($projeto_atual['status'] == 'Em Andamento') ? 'selected' : '' ?>>Em Andamento</option>
                                <option value="Pausado" <?= ($projeto_atual['status'] == 'Pausado') ? 'selected' : '' ?>>Pausado</option>
                                <option value="Concluído" <?= ($projeto_atual['status'] == 'Concluído') ? 'selected' : '' ?>>Concluído</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="engenheiro">Engenheiro Responsável (RT) *</label>
                        <div class="input-icon-wrapper">
                            <i class="fas fa-user-tie" style="z-index: 1;"></i>
                            <select id="engenheiro" name="engenheiro_id" required style="padding-left: 40px; width: 100%;">
                                <?php foreach ($lista_engenheiros as $eng): ?>
                                    <option value="<?= $eng['id'] ?>" <?= ($projeto_atual['engenheiro_responsavel'] == $eng['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($eng['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="valor">Valor Orçado da Obra *</label>
                        <div class="input-icon-wrapper">
                            <span class="prefix">R$</span>
                            <input type="text" id="valor" name="valor" value="<?= $valor_formatado ?>" onkeyup="mascaraMoeda(this)" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="data_inicio">Data de Início da Obra *</label>
                        <input type="date" id="data_inicio" name="data_inicio" value="<?= htmlspecialchars($projeto_atual['data_inicio']) ?>" required>
                    </div>

                    <div class="form-group full-width">
                        <label for="endereco">Endereço Completo da Obra *</label>
                        <div class="input-icon-wrapper">
                            <i class="fas fa-map-marker-alt"></i>
                            <input type="text" id="endereco" name="endereco" value="<?= htmlspecialchars($projeto_atual['endereco']) ?>" required>
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <label for="descricao">Descrição / Escopo do Projeto</label>
                        <textarea id="descricao" name="descricao"><?= htmlspecialchars($projeto_atual['descricao']) ?></textarea>
                    </div>

                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-submit" style="background-color: #3498db; color: #fff;"><i class="fas fa-sync-alt"></i> Atualizar Dados da Obra</button>
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