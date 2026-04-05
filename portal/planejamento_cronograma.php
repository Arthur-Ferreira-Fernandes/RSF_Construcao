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
$id_projeto = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id_projeto) { header("Location: lista_projetos.php"); exit; }

// =========================================================================
// SALVAR O PLANEJAMENTO NO BANCO DE DADOS
// =========================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulos = $_POST['titulos'];
    $inicios = $_POST['inicios'];
    $finais = $_POST['finais'];
    $metas = $_POST['metas']; // Agora é um Array de Arrays (Vários itens por semana)

    try {
        // Ao excluir a semana, o ON DELETE CASCADE apaga os itens antigos automaticamente
        $pdo->prepare("DELETE FROM acompanhamento_semanal WHERE projeto_id = ?")->execute([$id_projeto]);

        $sql_semana = "INSERT INTO acompanhamento_semanal (projeto_id, titulo_semana, data_inicio, data_fim, meta_semana, status, usuario_id) 
                       VALUES (?, ?, ?, ?, 'Metas em Checklist', 'Pendente', ?)";
        $stmt_semana = $pdo->prepare($sql_semana);
        $stmt_item = $pdo->prepare("INSERT INTO acompanhamento_itens (acompanhamento_id, descricao) VALUES (?, ?)");

        for ($i = 0; $i < count($titulos); $i++) {
            // Cria a Semana
            $stmt_semana->execute([$id_projeto, $titulos[$i], $inicios[$i], $finais[$i], $_SESSION['usuario_id']]);
            $id_acomp = $pdo->lastInsertId();

            // Cadastra os itens (tarefas) específicos desta semana
            if (isset($metas[$i]) && is_array($metas[$i])) {
                foreach ($metas[$i] as $tarefa) {
                    if (trim($tarefa) !== '') {
                        $stmt_item->execute([$id_acomp, trim($tarefa)]);
                    }
                }
            }
        }

        header("Location: detalhes_projeto.php?id=" . $id_projeto . "&msg=planejamento_concluido");
        exit;
    } catch (PDOException $e) {
        $erro = "Erro ao salvar o cronograma.";
    }
}

// =========================================================================
// GERA AS SEMANAS DINAMICAMENTE PARA PREENCHIMENTO
// =========================================================================
$stmt = $pdo->prepare("SELECT nome, data_inicio, data_fim_prevista, frequencia_medicao FROM projetos WHERE id = ?");
$stmt->execute([$id_projeto]);
$projeto = $stmt->fetch();

if (!$projeto || empty($projeto['data_fim_prevista'])) {
    die("<h2 style='color:#fff; text-align:center; margin-top:50px;'>A obra precisa de uma Data Fim Prevista para gerar o planejamento.</h2>");
}

$periodos = [];
$inicio_obj = new DateTime($projeto['data_inicio']);
$fim_obj = new DateTime($projeto['data_fim_prevista']);
$frequencia = $projeto['frequencia_medicao'] ?? 'Semanal';
$atual = clone $inicio_obj;
$contador = 1;

while ($atual <= $fim_obj) {
    $inicio_periodo = clone $atual;
    $fim_periodo = clone $atual;
    if ($frequencia === 'Semanal') { $fim_periodo->modify('+6 days'); } elseif ($frequencia === 'Mensal') { $fim_periodo->modify('last day of this month'); }
    if ($fim_periodo > $fim_obj) { $fim_periodo = clone $fim_obj; }

    $periodos[] = [
        'titulo' => $frequencia === 'Diária' ? "Dia $contador" : ($frequencia === 'Semanal' ? "Semana $contador" : "Mês $contador"),
        'inicio' => $inicio_periodo->format('Y-m-d'), 'fim' => $fim_periodo->format('Y-m-d')
    ];
    $atual = clone $fim_periodo; $atual->modify('+1 day'); $contador++;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Planejar Cronograma | RSF Engenharia</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../styles/dashboard.css">
    <style>
        body { background-color: #111; color: #fff; font-family: 'Montserrat', sans-serif; }
        .container { max-width: 900px; margin: 40px auto; padding: 20px; }
        .card-periodo { background: #1a1a1a; border-left: 4px solid #f1c40f; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 4px 10px rgba(0,0,0,0.3); }
        .card-periodo h3 { color: #f1c40f; margin-top: 0; margin-bottom: 5px; }
        .input-tarefa { width: 100%; padding: 12px; background: #222; border: 1px solid #444; color: #fff; border-radius: 4px; box-sizing: border-box; font-family: inherit; margin-bottom: 10px; }
        .btn-add-tarefa { background: transparent; border: 1px dashed #f1c40f; color: #f1c40f; padding: 8px 15px; border-radius: 4px; cursor: pointer; font-weight: bold; margin-top: 5px; }
        .btn-add-tarefa:hover { background: rgba(241, 196, 15, 0.1); }
        .btn-salvar { background-color: #f1c40f; color: #111; width: 100%; padding: 15px; border: none; font-size: 1.1rem; font-weight: bold; border-radius: 4px; cursor: pointer; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1 style="text-align: center; margin-bottom: 10px;"><i class="fas fa-sitemap" style="color: #f1c40f;"></i> Checklist do Cronograma</h1>
        <p style="text-align: center; color: #aaa; margin-bottom: 40px;">Adicione as tarefas uma a uma para criar uma checklist de verificação.</p>

        <form method="POST" action="planejamento_cronograma.php?id=<?= $id_projeto ?>">
            <?php foreach ($periodos as $index => $p): ?>
                <div class="card-periodo">
                    <h3><?= $p['titulo'] ?></h3>
                    <span style="color: #888; font-size: 0.85rem; display: block; margin-bottom: 15px;">Período: <?= date('d/m', strtotime($p['inicio'])) ?> a <?= date('d/m/Y', strtotime($p['fim'])) ?></span>
                    
                    <input type="hidden" name="titulos[]" value="<?= $p['titulo'] ?>">
                    <input type="hidden" name="inicios[]" value="<?= $p['inicio'] ?>">
                    <input type="hidden" name="finais[]" value="<?= $p['fim'] ?>">
                    
                    <div id="lista_tarefas_<?= $index ?>">
                        <input type="text" name="metas[<?= $index ?>][]" class="input-tarefa" placeholder="Tarefa 1 (Ex: Escavação do terreno)" required>
                    </div>
                    <button type="button" class="btn-add-tarefa" onclick="addTarefa(<?= $index ?>)"><i class="fas fa-plus"></i> Adicionar nova Tarefa</button>
                </div>
            <?php endforeach; ?>

            <button type="submit" class="btn-salvar"><i class="fas fa-save"></i> Salvar Linha de Base da Obra</button>
        </form>
    </div>

    <script>
        function addTarefa(index) {
            let container = document.getElementById('lista_tarefas_' + index);
            let input = document.createElement('input');
            input.type = 'text';
            input.name = 'metas[' + index + '][]';
            input.className = 'input-tarefa';
            input.placeholder = 'Nova tarefa...';
            input.required = true;
            container.appendChild(input);
            input.focus();
        }
    </script>
</body>
</html>