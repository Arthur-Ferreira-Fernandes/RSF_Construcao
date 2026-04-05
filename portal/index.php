<?php
session_start();

// 1. Verifica se o usuário já tem uma sessão ativa (já fez login antes)
if (isset($_SESSION['logado']) && $_SESSION['logado'] === true) {
    
    // Se for um cliente, vai direto para as obras dele
    if (isset($_SESSION['nivel_acesso']) && $_SESSION['nivel_acesso'] === 'cliente') {
        header("Location: lista_projetos.php");
        exit;
    } 
    
    // Se for admin ou engenheiro, vai para o dashboard da construtora
    header("Location: dashboard.php");
    exit;
}

// 2. Se não estiver logado (ou se for o primeiro acesso), envia para a tela de login
header("Location: login.php");
exit;