<?php
    $host = 'localhost'; 
    $dbname = 'rsf_engenharia'; 
    $user = 'root'; 
    $pass = ''; 

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log($e->getMessage());
        die("Erro de conexão com o banco de dados. A equipe técnica foi notificada.");
    }
?>