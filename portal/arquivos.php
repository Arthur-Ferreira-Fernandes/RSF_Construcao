<?php
session_start();
require_once 'scripts/conexao.php';

if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    header("Location: login.php");
    exit;
}

// 2. Trava de Redirecionamento Invisível (Evita a tela branca sem CSS)
// Apenas "admin" pode aceder. Se não for admin, é redirecionado para o seu respectivo painel.
if ($_SESSION['nivel_acesso'] !== 'admin') {
    $destino = ($_SESSION['nivel_acesso'] === 'cliente') ? 'lista_projetos.php' : 'dashboard.php';
    header("Location: " . $destino);
    exit;
}

$mensagem = '';
$tipo_mensagem = '';

$lista_projetos = [];
try {
    $stmt_proj = $pdo->query("SELECT nome FROM projetos ORDER BY nome ASC");
    $lista_projetos = $stmt_proj->fetchAll();
} catch (PDOException $e) {
    $mensagem = "Erro ao carregar lista de projetos.";
    $tipo_mensagem = "erro";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['documento'])) {
    
    $projeto = trim($_POST['projeto']);
    $tipo_documento = trim($_POST['tipo_documento']);
    
    $ficheiro = $_FILES['documento'];
    $nome_original = $ficheiro['name'];
    $tamanho = $ficheiro['size'];
    $tmp_name = $ficheiro['tmp_name'];
    $erro_upload = $ficheiro['error'];

    $extensoes_permitidas = ['pdf', 'jpg', 'jpeg', 'png'];
    $extensao = strtolower(pathinfo($nome_original, PATHINFO_EXTENSION));

    if (empty($projeto) || empty($tipo_documento)) {
        $mensagem = "Por favor, selecione o projeto e o tipo de documento.";
        $tipo_mensagem = "erro";
    } elseif ($erro_upload === UPLOAD_ERR_NO_FILE) {
        $mensagem = "Por favor, selecione um documento para enviar.";
        $tipo_mensagem = "erro";
    } elseif (!in_array($extensao, $extensoes_permitidas)) {
        $mensagem = "Formato não permitido. Envie apenas PDF, JPG ou PNG.";
        $tipo_mensagem = "erro";
    } elseif ($tamanho > 10485760) { 
        $mensagem = "O documento é demasiado grande. O limite máximo é 10MB.";
        $tipo_mensagem = "erro";
    } else {
        $nome_seguro = uniqid('rsf_', true) . '.' . $extensao;
        $destino = 'uploads/' . $nome_seguro;

        if (move_uploaded_file($tmp_name, $destino)) {
            try {
                $sql = "INSERT INTO arquivos (nome_original, nome_seguro, projeto, tipo_documento, extensao, tamanho, usuario_id) 
                        VALUES (:nome_original, :nome_seguro, :projeto, :tipo, :extensao, :tamanho, :usuario_id)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':nome_original' => $nome_original,
                    ':nome_seguro' => $nome_seguro,
                    ':projeto' => $projeto,
                    ':tipo' => $tipo_documento,
                    ':extensao' => $extensao,
                    ':tamanho' => $tamanho,
                    ':usuario_id' => $_SESSION['usuario_id']
                ]);

                $mensagem = "Documento anexado ao projeto com sucesso!";
                $tipo_mensagem = "sucesso";

            } catch (PDOException $e) {
                unlink($destino);
                $mensagem = "Erro ao registar na base de dados: " . $e->getMessage();
                $tipo_mensagem = "erro";
            }
        } else {
            $mensagem = "Falha ao guardar o ficheiro no servidor.";
            $tipo_mensagem = "erro";
        }
    }
}

$lista_arquivos = [];
try {
    $sql_busca = "SELECT a.*, u.nome AS enviado_por 
                  FROM arquivos a 
                  JOIN usuarios u ON a.usuario_id = u.id 
                  ORDER BY a.data_envio DESC";
    $stmt_busca = $pdo->query($sql_busca);
    $lista_arquivos = $stmt_busca->fetchAll();
} catch (PDOException $e) {
    if (empty($mensagem)) {
        $mensagem = "Não foi possível carregar a lista de documentos.";
        $tipo_mensagem = "erro";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestor de Ficheiros | RSF Engenharia</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="../styles/arquivos.css">
</head>
<body>

    <?php include 'header.php'; ?>

    <main class="container">
        
        <?php if (!empty($mensagem)): ?>
            <div class="alerta <?= $tipo_mensagem ?>">
                <?= htmlspecialchars($mensagem) ?>
            </div>
        <?php endif; ?>

        <div class="upload-card">
            <h3><i class="fas fa-cloud-upload-alt"></i> Anexar Documento ao Projeto</h3>
            
            <form class="form-upload" method="POST" action="arquivos.php" enctype="multipart/form-data">
                
                <div class="form-group">
                    <label>Projeto / Obra *</label>
                    <select name="projeto" required>
                        <option value="" disabled selected>Selecione a obra...</option>
                        
                        <?php foreach ($lista_projetos as $proj): ?>
                            <option value="<?= htmlspecialchars($proj['nome']) ?>">
                                <?= htmlspecialchars($proj['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                        
                    </select>
                </div>

                <div class="form-group">
                    <label>Tipo de Documento *</label>
                    <select name="tipo_documento" required>
                        <option value="" disabled selected>O que é este ficheiro?</option>
                        <option value="Planta Baixa / Arquitetura">Planta Baixa / Arquitetura</option>
                        <option value="Projeto Estrutural">Projeto Estrutural</option>
                        <option value="Projeto Hidráulico/Elétrico">Projeto Hidráulico/Elétrico</option>
                        <option value="ART / RRT">ART / RRT</option>
                        <option value="Alvará / AVCB">Alvará / AVCB</option>
                        <option value="Relatório Fotográfico">Relatório Fotográfico</option>
                        <option value="Orçamento de Materiais">Orçamento de Materiais</option>
                        <option value="Outros">Outros</option>
                    </select>
                </div>

                <div class="full-width">
                    <div class="form-group" style="flex-grow: 2;">
                        <input type="file" name="documento" accept=".pdf, .jpg, .jpeg, .png" required>
                    </div>
                    <button type="submit" class="btn-upload"><i class="fas fa-save"></i> Guardar Ficheiro</button>
                </div>

            </form>
        </div>

        <h2>Acervo Digital da Engenharia</h2>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th style="width: 50px;"></th>
                        <th>Detalhes do Documento</th>
                        <th>Projeto</th>
                        <th>Enviado por</th>
                        <th>Data</th>
                        <th>Ação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($lista_arquivos)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; color: #888; padding: 30px;">
                                O acervo está vazio. Faça o primeiro upload acima.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($lista_arquivos as $arq): ?>
                            <tr>
                                <td style="text-align: center;">
                                    <?php if ($arq['extensao'] === 'pdf'): ?>
                                        <i class="fas fa-file-pdf icone-pdf fa-2x"></i>
                                    <?php else: ?>
                                        <i class="fas fa-file-image icone-img fa-2x"></i>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong style="display: block; margin-bottom: 5px;"><?= htmlspecialchars($arq['nome_original']) ?></strong>
                                    <span class="badge"><?= htmlspecialchars($arq['tipo_documento']) ?></span>
                                </td>
                                <td>
                                    <span class="badge badge-projeto"><?= htmlspecialchars($arq['projeto']) ?></span>
                                </td>
                                <td><?= htmlspecialchars($arq['enviado_por']) ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($arq['data_envio'])) ?></td>
                                <td>
                                    <a href="uploads/<?= htmlspecialchars($arq['nome_seguro']) ?>" target="_blank" class="btn-download">
                                        <i class="fas fa-external-link-alt"></i> Abrir
                                    </a>
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