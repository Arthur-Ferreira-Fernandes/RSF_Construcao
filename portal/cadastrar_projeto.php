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
    die("Erro ao carregar lista de engenheiros.");
}

$stmt_cli = $pdo->query("SELECT id, nome FROM clientes WHERE status = 'Ativo' ORDER BY nome ASC");
$clientes = $stmt_cli->fetchAll();

$mensagem = '';
$tipo_mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $nome = trim($_POST['nome']);
    $engenheiro_id = trim($_POST['engenheiro_id']); 
    $descricao = trim($_POST['descricao']);
    $endereco = trim($_POST['endereco']);
    $data_inicio = $_POST['data_inicio'];
    
    $valor_post = $_POST['valor'] ?? '0';
    $valor_post = str_replace('.', '', $valor_post); 
    $valor_post = str_replace(',', '.', $valor_post); 
    $valor = (float) $valor_post;

    if (empty($nome) || empty($engenheiro_id) || empty($valor) || empty($endereco) || empty($data_inicio)) {
        $mensagem = "Preencha todos os campos obrigatórios.";
        $tipo_mensagem = "erro";
    } else {
        try {
            $cliente_id = !empty($_POST['cliente_id']) ? $_POST['cliente_id'] : null;

            $sql = "INSERT INTO projetos (nome, descricao, endereco, engenheiro_responsavel, cliente_id, data_inicio, valor, status) 
            VALUES (:nome, :descricao, :endereco, :engenheiro_responsavel, :cliente_id, :data_inicio, :valor, :status)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nome' => $nome,
                ':descricao' => $descricao,
                ':endereco' => $endereco,
                ':engenheiro_responsavel' => $engenheiro_responsavel,
                ':cliente_id' => $cliente_id,
                ':data_inicio' => $data_inicio,
                ':valor' => $valor,
                ':status' => $status
            ]);
            $mensagem = "Projeto cadastrado com sucesso!";
            $tipo_mensagem = "sucesso";

        } catch (PDOException $e) {
            $mensagem = "Erro ao cadastrar o projeto no banco de dados.";
            $tipo_mensagem = "erro";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Projeto | RSF Engenharia</title>
    
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
            <h2><i class="fas fa-hard-hat"></i> Cadastrar Nova Obra / Projeto</h2>
            
            <form method="POST" action="cadastrar_projeto.php">
                <div class="form-grid">
                    
                    <div class="form-group full-width">
                        <label for="nome">Nome do Projeto *</label>
                        <div class="input-icon-wrapper">
                            <i class="fas fa-building"></i>
                            <input type="text" id="nome" name="nome" placeholder="Ex: Galpão Logístico Cajamar" required autofocus>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="engenheiro">Engenheiro Responsável (RT) *</label>
                        <div class="input-icon-wrapper">
                            <i class="fas fa-user-tie" style="z-index: 1;"></i>
                            
                            <select id="engenheiro" name="engenheiro_id" required style="padding-left: 40px; width: 100%;">
                                <option value="" disabled selected>Selecione o RT da obra...</option>
                                
                                <?php foreach ($lista_engenheiros as $eng): ?>
                                    <option value="<?= $eng['id'] ?>">
                                        <?= htmlspecialchars($eng['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                                
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="cliente_id">Cliente / Contratante</label>
                        <div class="input-icon-wrapper">
                            <i class="fas fa-handshake" style="z-index: 1;"></i>
                            <select id="cliente_id" name="cliente_id" style="padding-left: 40px; width: 100%;">
                                <option value="">-- Selecione o Cliente (Opcional) --</option>
                                <?php foreach ($clientes as $cli): ?>
                                    <option value="<?= $cli['id'] ?>"><?= htmlspecialchars($cli['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="valor">Valor Orçado da Obra *</label>
                        <div class="input-icon-wrapper">
                            <span class="prefix">R$</span>
                            <input type="text" id="valor" name="valor" placeholder="0,00" onkeyup="mascaraMoeda(this)" required>
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <label for="endereco">Endereço Completo da Obra *</label>
                        <div class="input-icon-wrapper">
                            <i class="fas fa-map-marker-alt"></i>
                            <input type="text" id="endereco" name="endereco" placeholder="Rua, Número, Bairro, Cidade - SP" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="data_inicio">Data de Início da Obra *</label>
                        <input type="date" id="data_inicio" name="data_inicio" required>
                    </div>

                    <div class="form-group full-width">
                        <label for="descricao">Descrição / Escopo do Projeto</label>
                        <textarea id="descricao" name="descricao" placeholder="Detalhes técnicos, cronograma macro, observações importantes..."></textarea>
                    </div>

                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-submit"><i class="fas fa-save"></i> Salvar Projeto</button>
                </div>
            </form>
        </div>

    </main>

    <script>
        function mascaraMoeda(input) {
            let valor = input.value;
            
            valor = valor.replace(/\D/g, "");
            
            if (valor === "") {
                input.value = "";
                return;
            }

            valor = (parseInt(valor) / 100).toFixed(2) + '';
            
            valor = valor.replace(".", ",");
            
            valor = valor.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
            
            input.value = valor;
        }
    </script>

</body>
</html>