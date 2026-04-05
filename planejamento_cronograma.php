<?php
session_start();
require_once 'scripts/conexao.php';

if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true || $_SESSION['nivel_acesso'] !== 'admin') {
    die("Acesso restrito a administradores.");
}

$id_projeto = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id_projeto) { header("Location: lista_projetos.php"); exit; }

// =========================================================================
// SALVAR O PLANEJAMENTO NO BANCO DE DADOS
// =========================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulos = $_POST['titulos'];
    $inicios = $_POST['inicios'];
    $finais = $_POST['finais'];
    $metas = $_POST['metas']; // Array de Arrays (Vários itens por semana)

    try {
        $pdo->prepare("DELETE FROM acompanhamento_semanal WHERE projeto_id = ?")->execute([$id_projeto]);

        $sql_semana = "INSERT INTO acompanhamento_semanal (projeto_id, titulo_semana, data_inicio, data_fim, meta_semana, status, usuario_id) 
                       VALUES (?, ?, ?, ?, 'Metas em Checklist', 'Pendente', ?)";
        $stmt_semana = $pdo->prepare($sql_semana);
        $stmt_item = $pdo->prepare("INSERT INTO acompanhamento_itens (acompanhamento_id, descricao) VALUES (?, ?)");

        for ($i = 0; $i < count($titulos); $i++) {
            $stmt_semana->execute([$id_projeto, $titulos[$i], $inicios[$i], $finais[$i], $_SESSION['usuario_id']]);
            $id_acomp = $pdo->lastInsertId();

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planejar Cronograma | RSF Engenharia</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../styles/dashboard.css">
    <link rel="icon" type="image/png" href="../img/logo.png">

    <style>
        body { background-color: #111; color: #fff; font-family: 'Montserrat', sans-serif; }
        .container { max-width: 900px; margin: 40px auto; padding: 20px; }
        .card-periodo { background: #1a1a1a; border-left: 4px solid #f1c40f; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 4px 10px rgba(0,0,0,0.3); }
        .card-periodo h3 { color: #f1c40f; margin-top: 0; margin-bottom: 5px; }
        
        /* Estilos do Novo Controlador de Tarefas */
        .tarefa-item { display: flex; gap: 8px; margin-bottom: 10px; align-items: center; animation: fadeIn 0.3s; }
        .input-tarefa { flex: 1; padding: 12px; background: #222; border: 1px solid #444; color: #fff; border-radius: 4px; box-sizing: border-box; font-family: inherit; margin-bottom: 0 !important; }
        .input-tarefa:focus { outline: none; border-color: #3498db; }
        
        .btn-acao-tarefa { background: #333; color: #aaa; border: none; padding: 12px 14px; border-radius: 4px; cursor: pointer; transition: 0.2s; font-size: 1rem; }
        .btn-acao-tarefa:hover { background: #444; color: #fff; }
        .btn-acao-tarefa.btn-remover:hover { background: #e74c3c; color: #fff; }
        .btn-acao-tarefa.btn-mover:hover { background: #3498db; color: #fff; }

        .btn-add-tarefa { background: transparent; border: 1px dashed #f1c40f; color: #f1c40f; padding: 10px 15px; border-radius: 4px; cursor: pointer; font-weight: bold; margin-top: 10px; width: 100%; transition: 0.3s; }
        .btn-add-tarefa:hover { background: rgba(241, 196, 15, 0.1); }
        .btn-salvar { background-color: #f1c40f; color: #111; width: 100%; padding: 15px; border: none; font-size: 1.1rem; font-weight: bold; border-radius: 4px; cursor: pointer; margin-top: 20px; transition: 0.3s; }
        .btn-salvar:hover { background-color: #d4ac0d; }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }

        @media (max-width: 768px) {
            .btn-acao-tarefa { padding: 12px; } /* Botões maiores para toque no telemóvel */
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 style="text-align: center; margin-bottom: 10px;"><i class="fas fa-sitemap" style="color: #f1c40f;"></i> Checklist do Cronograma</h1>
        <p style="text-align: center; color: #aaa; margin-bottom: 40px;">Adicione, ordene ou exclua as tarefas para criar uma linha de base perfeita.</p>

        <form method="POST" action="planejamento_cronograma.php?id=<?= $id_projeto ?>">
            <?php foreach ($periodos as $index => $p): ?>
                <div class="card-periodo">
                    <h3><?= $p['titulo'] ?></h3>
                    <span style="color: #888; font-size: 0.85rem; display: block; margin-bottom: 15px;">Período: <?= date('d/m', strtotime($p['inicio'])) ?> a <?= date('d/m/Y', strtotime($p['fim'])) ?></span>
                    
                    <input type="hidden" name="titulos[]" value="<?= $p['titulo'] ?>">
                    <input type="hidden" name="inicios[]" value="<?= $p['inicio'] ?>">
                    <input type="hidden" name="finais[]" value="<?= $p['fim'] ?>">
                    
                    <div id="lista_tarefas_<?= $index ?>">
                        <div class="tarefa-item">
                            <input type="text" name="metas[<?= $index ?>][]" class="input-tarefa" placeholder="Tarefa 1 (Ex: Escavação do terreno)" required>
                            <button type="button" class="btn-acao-tarefa btn-mover" onclick="moverCima(this)" title="Mover para Cima"><i class="fas fa-arrow-up"></i></button>
                            <button type="button" class="btn-acao-tarefa btn-mover" onclick="moverBaixo(this)" title="Mover para Baixo"><i class="fas fa-arrow-down"></i></button>
                            <button type="button" class="btn-acao-tarefa btn-remover" onclick="removerTarefa(this)" title="Excluir Tarefa"><i class="fas fa-trash-alt"></i></button>
                        </div>
                    </div>
                    <button type="button" class="btn-add-tarefa" onclick="addTarefa(<?= $index ?>)"><i class="fas fa-plus"></i> Adicionar Tarefa</button>
                </div>
            <?php endforeach; ?>

            <div style="display: flex; gap: 15px; margin-top: 20px;">
                <a href="detalhes_projeto.php?id=<?= $id_projeto ?>" style="flex: 1; text-align: center; padding: 15px; background: transparent; border: 1px solid #aaa; color: #aaa; text-decoration: none; border-radius: 4px; font-weight: bold;">Cancelar</a>
                <button type="submit" class="btn-salvar" style="flex: 2; margin-top: 0;"><i class="fas fa-save"></i> Salvar Linha de Base</button>
            </div>
        </form>
    </div>

    <script>
        // Adiciona uma nova linha com todos os botões de controle
        function addTarefa(index) {
            let container = document.getElementById('lista_tarefas_' + index);
            
            let wrapper = document.createElement('div');
            wrapper.className = 'tarefa-item';
            
            // O HTML interno da nova linha
            wrapper.innerHTML = `
                <input type="text" name="metas[${index}][]" class="input-tarefa" placeholder="Nova tarefa..." required>
                <button type="button" class="btn-acao-tarefa btn-mover" onclick="moverCima(this)" title="Mover para Cima"><i class="fas fa-arrow-up"></i></button>
                <button type="button" class="btn-acao-tarefa btn-mover" onclick="moverBaixo(this)" title="Mover para Baixo"><i class="fas fa-arrow-down"></i></button>
                <button type="button" class="btn-acao-tarefa btn-remover" onclick="removerTarefa(this)" title="Excluir Tarefa"><i class="fas fa-trash-alt"></i></button>
            `;
            
            container.appendChild(wrapper);
            wrapper.querySelector('input').focus();
        }

        // Função para excluir a linha
        function removerTarefa(botao) {
            let wrapper = botao.parentNode;
            let container = wrapper.parentNode;
            // Se for o último item restante, não deixa apagar para não ficar vazio
            if (container.children.length > 1) {
                container.removeChild(wrapper);
            } else {
                alert("A etapa precisa ter pelo menos uma tarefa planejada.");
            }
        }

        // Função para mover a linha para cima
        function moverCima(botao) {
            let wrapper = botao.parentNode;
            if (wrapper.previousElementSibling) {
                wrapper.parentNode.insertBefore(wrapper, wrapper.previousElementSibling);
            }
        }

        // Função para mover a linha para baixo
        function moverBaixo(botao) {
            let wrapper = botao.parentNode;
            if (wrapper.nextElementSibling) {
                wrapper.parentNode.insertBefore(wrapper.nextElementSibling, wrapper);
            }
        }
    </script>
</body>
</html>