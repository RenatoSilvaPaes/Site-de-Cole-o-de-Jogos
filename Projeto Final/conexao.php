<?php
    declare(strict_types=1);

    $servidor = 'localhost';
    $bancoDados = 'colecionaveis';
    $userAdmin = 'renato';
    $senha = '123';

    try {
        $dns = "mysql:host=$servidor;dbname=$bancoDados;charset=utf8mb4";

        $configuracoes = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        $conexaoDB = new PDO($dns, $userAdmin, $senha, $configuracoes);
    }
    catch (PDOException $erroExcessao) {
        error_log("Erro ao se conectar com o banco de dados do site: " . $erroExcessao->getMessage());
        die("Não foi possível se conectar ao banco de dados.");
    }
?>