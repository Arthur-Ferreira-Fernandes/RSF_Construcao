<?php
session_start();
require_once 'scripts/conexao.php';

if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true || $_SESSION['nivel_acesso'] !== 'admin') {
    die("Acesso restrito a administradores.");
}

$id_projeto = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id_projeto) { header("Location: lista_projetos.php"); exit; }

// =========================================================================
// SALVAR O PLANEJAMENTO NO BANCO DE DADOS (ORDEM E SOMA AUTOMÁTICA)
// =========================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // O array de IDs únicos mantém a ordem exata de como estão na tela
    $etapas_ids = $_POST['etapa_id'] ?? []; 
    
    try {
        $pdo->prepare("DELETE FROM acompanhamento_semanal WHERE projeto_id = ?")->execute([$id_projeto]);

        $sql_semana = "INSERT INTO acompanhamento_semanal (projeto_id, ordem, titulo_semana, data_inicio, data_fim, meta_semana, valor_orcado, status, usuario_id) 
                       VALUES (?, ?, ?, ?, ?, 'Metas em Checklist', ?, 'Pendente', ?)";
        $stmt_semana = $pdo->prepare($sql_semana);
        
        $sql_item = "INSERT INTO acompanhamento_itens (acompanhamento_id, descricao, valor_orcado, data_previsao) VALUES (?, ?, ?, ?)";
        $stmt_item = $pdo->prepare($sql_item);

        $ordem_index = 1;

        foreach ($etapas_ids as $uid) {
            $titulo = $_POST['titulos'][$uid] ?? "Etapa";
            $inicio = $_POST['inicios'][$uid] ?? null;
            $fim = $_POST['finais'][$uid] ?? null;
            
            // Calcula a soma total da etapa baseado nos itens
            $soma_etapa = 0;
            $valores_itens = $_POST['valores_itens'][$uid] ?? [];
            foreach ($valores_itens as $val) {
                $soma_etapa += (float) str_replace(['.', ','], ['', '.'], $val ?? '0');
            }

            // Salva a etapa com a sua nova ordem e o valor total calculado
            $stmt_semana->execute([$id_projeto, $ordem_index, trim($titulo), $inicio, $fim, $soma_etapa, $_SESSION['usuario_id']]);
            $id_acomp = $pdo->lastInsertId();

            // Salva as tarefas dessa etapa
            $metas = $_POST['metas'][$uid] ?? [];
            $datas_prev = $_POST['datas_previsao'][$uid] ?? [];
            
            foreach ($metas as $k => $tarefa) {
                if (trim($tarefa) !== '') {
                    $valor_item = (float) str_replace(['.', ','], ['', '.'], $valores_itens[$k] ?? '0');
                    $data_tarefa = !empty($datas_prev[$k]) ? $datas_prev[$k] : null;
                    
                    $stmt_item->execute([$id_acomp, trim($tarefa), $valor_item, $data_tarefa]);
                }
            }
            $ordem_index++;
        }

        header("Location: detalhes_projeto.php?id=" . $id_projeto . "&msg=planejamento_concluido");
        exit;
    } catch (PDOException $e) {
        $erro = "Erro ao salvar o cronograma. Verifique os dados.";
    }
}

// =========================================================================
// CARREGA DADOS EXISTENTES ORDENADOS CORRETAMENTE
// =========================================================================
$stmt = $pdo->prepare("SELECT nome, data_inicio, data_fim_prevista FROM projetos WHERE id = ?");
$stmt->execute([$id_projeto]);
$projeto = $stmt->fetch();
if (!$projeto) { die("Projeto não encontrado."); }

$stmt_plan = $pdo->prepare("SELECT * FROM acompanhamento_semanal WHERE projeto_id = ? ORDER BY ordem ASC, data_inicio ASC");
$stmt_plan->execute([$id_projeto]);
$etapas_salvas = $stmt_plan->fetchAll();

$cronograma = [];
foreach ($etapas_salvas as $etapa) {
    $stmt_itens = $pdo->prepare("SELECT * FROM acompanhamento_itens WHERE acompanhamento_id = ? ORDER BY id ASC");
    $stmt_itens->execute([$etapa['id']]);
    $etapa['itens'] = $stmt_itens->fetchAll();
    $cronograma[] = $etapa;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planejar Escopo | RSF Engenharia</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../styles/dashboard.css">
    <style>
        body { background-color: #111; color: #fff; font-family: 'Montserrat', sans-serif; padding-bottom: 50px; }
        .container { max-width: 1000px; margin: 40px auto; padding: 20px; }
        
        .card-etapa { background: #1a1a1a; border-left: 4px solid #3498db; padding: 25px; border-radius: 8px; margin-bottom: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.4); position: relative;}
        .controles-etapa { position: absolute; top: -15px; right: -15px; display: flex; gap: 5px; }
        .btn-ctrl-etapa { background: #3498db; color: #fff; border: none; width: 35px; height: 35px; border-radius: 50%; font-size: 1rem; cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 10px rgba(0,0,0,0.4); transition: 0.3s;}
        .btn-ctrl-etapa:hover { transform: scale(1.1); filter: brightness(1.2); }
        .btn-ctrl-del { background: #e74c3c; } .btn-ctrl-del:hover { background: #c0392b; }
        
        .grid-header-etapa { display: grid; grid-template-columns: 2.5fr 1fr 1fr; gap: 15px; margin-bottom: 15px; align-items: end;}
        
        .form-group label { display: block; color: #aaa; font-size: 0.8rem; font-weight: bold; margin-bottom: 5px; text-transform: uppercase;}
        .form-control { width: 100%; padding: 12px; background: #222; border: 1px solid #444; color: #fff; border-radius: 4px; box-sizing: border-box; }
        .form-control:focus { outline: none; border-color: #3498db; }
        
        .tarefa-item { display: flex; gap: 8px; margin-bottom: 10px; align-items: center; background: #111; padding: 8px; border-radius: 4px; border: 1px solid #333;}
        .input-tarefa { padding: 10px; background: #222; border: 1px solid #444; color: #fff; border-radius: 4px; box-sizing: border-box; }
        .input-tarefa:focus { outline: none; border-color: #f1c40f; }
        
        .btn-acao { background: #333; color: #aaa; border: none; padding: 10px 12px; border-radius: 4px; cursor: pointer; transition: 0.2s; }
        .btn-acao:hover { background: #444; color: #fff; }
        .btn-remover-item:hover { background: #e74c3c; color: #fff; }
        
        .btn-add-tarefa { background: transparent; border: 1px dashed #f1c40f; color: #f1c40f; padding: 10px 15px; border-radius: 4px; cursor: pointer; font-weight: bold; margin-top: 10px; width: 100%; transition: 0.3s; }
        .btn-add-tarefa:hover { background: rgba(241, 196, 15, 0.1); }
        
        .btn-add-etapa { background: #3498db; color: #fff; border: none; padding: 15px 30px; border-radius: 4px; font-weight: bold; font-size: 1.1rem; cursor: pointer; display: block; margin: 0 auto 40px auto; box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);}
        .btn-salvar { background-color: #2ecc71; color: #111; width: 100%; padding: 18px; border: none; font-size: 1.2rem; font-weight: bold; border-radius: 4px; cursor: pointer; box-shadow: 0 4px 15px rgba(46, 204, 113, 0.3);}

        @media (max-width: 768px) {
            .grid-header-etapa { grid-template-columns: 1fr; gap: 10px;}
            .tarefa-item { flex-wrap: wrap; }
            .tarefa-item input { flex: 100% !important; margin-bottom: 5px !important;}
            .controles-etapa { position: relative; top: 0; right: 0; justify-content: flex-end; margin-bottom: 15px;}
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 style="text-align: center; margin-bottom: 10px;"><i class="fas fa-layer-group" style="color: #3498db;"></i> Planejamento Ágil</h1>
        <p style="text-align: center; color: #aaa; margin-bottom: 40px;">Organize as etapas. O valor de cada tarefa soma automaticamente no total da etapa.</p>

        <?php if (isset($erro)): ?>
            <div style="background: rgba(231,76,60,0.2); color: #e74c3c; padding: 15px; border-radius: 4px; text-align: center; margin-bottom: 20px; font-weight: bold;"><?= $erro ?></div>
        <?php endif; ?>

        <form method="POST" action="planejamento_cronograma.php?id=<?= $id_projeto ?>" id="formCronograma" onsubmit="return validarCronograma()">
            <div id="container_etapas"></div>

            <button type="button" class="btn-add-etapa" onclick="addEtapa()"><i class="fas fa-plus-circle"></i> Adicionar Nova Etapa</button>

            <div style="display: flex; gap: 15px; margin-top: 20px; border-top: 1px solid #333; padding-top: 30px;">
                <a href="detalhes_projeto.php?id=<?= $id_projeto ?>" style="flex: 1; text-align: center; padding: 18px; background: #222; border: 1px solid #444; color: #aaa; text-decoration: none; border-radius: 4px; font-weight: bold; font-size: 1.1rem;">Cancelar</a>
                <button type="submit" class="btn-salvar" style="flex: 2;"><i class="fas fa-save"></i> Salvar Linha de Base</button>
            </div>
        </form>
    </div>

    <script>
        let cronogramaSalvo = <?= json_encode($cronograma) ?>;

        window.onload = function() {
            if (cronogramaSalvo.length > 0) {
                cronogramaSalvo.forEach(etapa => { addEtapa(etapa); });
            } else { addEtapa(); }
        };

        function gerarUID() { return Date.now().toString(36) + Math.random().toString(36).substr(2); }

        // Cria a Etapa
        function addEtapa(dadosEtapa = null) {
            let container = document.getElementById('container_etapas');
            let uid = gerarUID(); // ID Único para agrupar as tarefas desta etapa
            
            let titulo = dadosEtapa ? dadosEtapa.titulo_semana : `Nova Etapa`;
            let dataIni = dadosEtapa ? dadosEtapa.data_inicio : '';
            let dataFim = dadosEtapa ? dadosEtapa.data_fim : '';
            let valorCalculado = dadosEtapa ? (parseFloat(dadosEtapa.valor_orcado).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2})) : '0,00';
            
            let htmlEtapa = `
                <div class="card-etapa" id="etapa_${uid}">
                    <input type="hidden" name="etapa_id[]" value="${uid}">
                    
                    <div class="controles-etapa">
                        <button type="button" class="btn-ctrl-etapa" onclick="moverCardCima(this)" title="Subir Etapa"><i class="fas fa-arrow-up"></i></button>
                        <button type="button" class="btn-ctrl-etapa" onclick="moverCardBaixo(this)" title="Descer Etapa"><i class="fas fa-arrow-down"></i></button>
                        <button type="button" class="btn-ctrl-etapa btn-ctrl-del" onclick="removerEtapa(this)" title="Excluir Etapa"><i class="fas fa-times"></i></button>
                    </div>
                    
                    <div class="grid-header-etapa">
                        <div class="form-group"><label>Nome da Etapa</label><input type="text" name="titulos[${uid}]" class="form-control" value="${titulo}" required></div>
                        <div class="form-group"><label>Data Início</label><input type="date" name="inicios[${uid}]" class="form-control" value="${dataIni}" required></div>
                        <div class="form-group"><label>Data Fim</label><input type="date" name="finais[${uid}]" class="form-control" value="${dataFim}" required></div>
                    </div>

                    <div style="background: #222; padding: 15px; border-radius: 4px; display: flex; justify-content: space-between; align-items: center; border-left: 3px solid #f1c40f; margin-bottom: 20px;">
                        <span style="color: #aaa; font-size: 0.85rem; font-weight: bold; text-transform: uppercase;">Orçamento Total da Etapa</span>
                        <strong style="color: #f1c40f; font-size: 1.3rem;">R$ <span id="soma_etapa_${uid}">${valorCalculado}</span></strong>
                    </div>
                    
                    <div id="lista_tarefas_${uid}">
                        </div>
                    <button type="button" class="btn-add-tarefa" onclick="addTarefa('${uid}')"><i class="fas fa-plus"></i> Adicionar Tarefa na Etapa</button>
                </div>
            `;
            
            let tempDiv = document.createElement('div');
            tempDiv.innerHTML = htmlEtapa;
            container.appendChild(tempDiv.firstElementChild);

            if (dadosEtapa && dadosEtapa.itens && dadosEtapa.itens.length > 0) {
                dadosEtapa.itens.forEach(item => { addTarefa(uid, item.descricao, item.valor_orcado, item.data_previsao); });
            } else {
                addTarefa(uid);
            }
        }

        // Adiciona uma Tarefa dentro de uma Etapa
        function addTarefa(uid, desc = '', valor = 0, dataPrev = '') {
            let container = document.getElementById('lista_tarefas_' + uid);
            let wrapper = document.createElement('div');
            wrapper.className = 'tarefa-item';
            
            let valorFormatado = valor ? parseFloat(valor).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : '';

            wrapper.innerHTML = `
                <input type="text" name="metas[${uid}][]" class="input-tarefa" value="${desc}" placeholder="Descrição da Tarefa..." required style="flex: 2;">
                <input type="text" name="valores_itens[${uid}][]" class="input-tarefa input-valor-${uid}" value="${valorFormatado}" placeholder="R$ 0,00" onkeyup="mascaraMoeda(this); recalcularTotal('${uid}')" required style="flex: 1; border-color: #f1c40f; color: #f1c40f; font-weight: bold;">
                <input type="date" name="datas_previsao[${uid}][]" class="input-tarefa" value="${dataPrev}" style="flex: 1; color: #ccc;" title="Entrega">
                <button type="button" class="btn-acao btn-mover-item" onclick="moverItemCima(this)"><i class="fas fa-arrow-up"></i></button>
                <button type="button" class="btn-acao btn-mover-item" onclick="moverItemBaixo(this)"><i class="fas fa-arrow-down"></i></button>
                <button type="button" class="btn-acao btn-remover-item" onclick="removerTarefa(this, '${uid}')"><i class="fas fa-trash-alt"></i></button>
            `;
            container.appendChild(wrapper);
            recalcularTotal(uid);
        }

        // MOTOR DE CÁLCULO: Soma todos os itens da etapa sempre que você digita
        function recalcularTotal(uid) {
            let inputs = document.querySelectorAll(`.input-valor-${uid}`);
            let total = 0;
            inputs.forEach(inp => {
                let v = inp.value.replace(/\./g, '').replace(',', '.');
                if(v && !isNaN(v)) total += parseFloat(v);
            });
            document.getElementById(`soma_etapa_${uid}`).innerText = total.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }

        // Movimentação de Etapas Inteiras
        function moverCardCima(btn) { let card = btn.closest('.card-etapa'); if (card.previousElementSibling) card.parentNode.insertBefore(card, card.previousElementSibling); }
        function moverCardBaixo(btn) { let card = btn.closest('.card-etapa'); if (card.nextElementSibling) card.parentNode.insertBefore(card.nextElementSibling, card); }
        function removerEtapa(btn) { if (confirm("Remover etapa completa?")) btn.closest('.card-etapa').remove(); }

        // Movimentação e Remoção de Tarefas
        function moverItemCima(btn) { let w = btn.parentNode; if (w.previousElementSibling) w.parentNode.insertBefore(w, w.previousElementSibling); }
        function moverItemBaixo(btn) { let w = btn.parentNode; if (w.nextElementSibling) w.parentNode.insertBefore(w.nextElementSibling, w); }
        function removerTarefa(botao, uid) {
            let w = botao.parentNode;
            if (w.parentNode.children.length > 1) { w.parentNode.removeChild(w); recalcularTotal(uid); } 
            else { alert("A etapa precisa ter no mínimo uma tarefa."); }
        }

        function mascaraMoeda(input) {
            let valor = input.value.replace(/\D/g, "");
            if (valor === "") { input.value = ""; return; }
            valor = (parseInt(valor) / 100).toFixed(2) + '';
            valor = valor.replace(".", ",");
            valor = valor.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
            input.value = valor;
        }

        function validarCronograma() {
            if (document.querySelectorAll('.card-etapa').length === 0) { alert("Crie pelo menos uma etapa."); return false; }
            return true;
        }
    </script>
</body>
</html>