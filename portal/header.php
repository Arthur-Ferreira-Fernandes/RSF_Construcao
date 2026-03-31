<link rel="stylesheet" href="../styles/header.css">

<header class="topbar">
    <div class="topbar-logo">
        <a href="dashboard.php" style="text-decoration: none; color: inherit;">
            <h2>RSF <span>Engenharia</span></h2>
        </a>
    </div>
    
    <nav class="topbar-nav">
        <a href="dashboard.php"><i class="fas fa-chart-pie"></i> Painel</a>
        <a href="lista_projetos.php"><i class="fas fa-hard-hat"></i> Obras</a>
        <a href="arquivos.php"><i class="fas fa-folder-open"></i> Arquivos</a>
        
        <?php if (isset($_SESSION['nivel_acesso']) && $_SESSION['nivel_acesso'] === 'admin'): ?>
            <a href="gerenciar_equipe.php"><i class="fas fa-users-cog"></i> Equipe & Acessos</a>
        <?php endif; ?>
        
        <a href="editar_perfil.php" style="color: #3498db;"><i class="fas fa-user-circle"></i> Minha Conta</a>
        <a href="scripts/logout.php" class="btn-sair"><i class="fas fa-sign-out-alt"></i> Sair</a>
    </nav>
</header>