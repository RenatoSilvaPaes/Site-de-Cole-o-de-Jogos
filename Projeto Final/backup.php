<?php
declare(strict_types=1);
// session_start(); // Ative quando a tela de login estiver pronta

// Verifica se o botão de backup foi realmente clicado
if (isset($_POST['btn_backup'])) {
    
    // Importa a sua conexão oficial. Ajuste o caminho do arquivo se ele estiver em outra pasta.
    require_once "conexao.php"; 

    try {
        // Usando a sua variável $conexaoDB criada no conexao.php
        $sqlBackup = "-- Backup gerado automaticamente via Sistema\n";
        $sqlBackup .= "-- Data: " . date('d/m/Y H:i:s') . "\n\n";
        $sqlBackup .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        // 1. Pegar todas as tabelas do seu banco 'colecionaveis'
        $tablesStmt = $conexaoDB->query("SHOW TABLES");
        $tables = $tablesStmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($tables as $table) {
            // Estrutura da tabela (CREATE TABLE)
            $createTableStmt = $conexaoDB->query("SHOW CREATE TABLE `$table`");
            $createTableRow = $createTableStmt->fetch();
            
            $sqlBackup .= "DROP TABLE IF EXISTS `$table`;\n";
            // O comando SHOW CREATE TABLE traz o nome da tabela e o comando na segunda coluna
            $sqlBackup .= current(array_slice($createTableRow, 1, 1)) . ";\n\n";

            // Dados da tabela (INSERTs)
            $rowsStmt = $conexaoDB->query("SELECT * FROM `$table`");
            $rows = $rowsStmt->fetchAll();

            foreach ($rows as $row) {
                $sqlBackup .= "INSERT INTO `$table` VALUES (";
                $values = [];
                foreach ($row as $value) {
                    if (is_null($value)) {
                        $values[] = "NULL";
                    } else {
                        // O pdo->quote() limpa e adiciona as aspas automaticamente nas strings
                        $values[] = $conexaoDB->quote((string)$value);
                    }
                }
                $sqlBackup .= implode(", ", $values) . ");\n";
            }
            $sqlBackup .= "\n";
        }

        $sqlBackup .= "SET FOREIGN_KEY_CHECKS=1;\n";
        $sqlBackup .= "CREATE USER IF NOT EXISTS 'renato'@'localhost' IDENTIFIED BY '123';\n";
        $sqlBackup .= "GRANT ALL PRIVILEGES ON `colecionaveis`.* TO 'renato'@'localhost';\n";
        $sqlBackup .= "FLUSH PRIVILEGES;\n";


        // ================= FORÇAR O DOWNLOAD NO NAVEGADOR =================
        // Nome dinâmico utilizando a variável $bancoDados vinda do seu conexao.php
        $filename = "backup_" . $bancoDados . "_" . date('Y-m-d_H-i-s') . ".sql";

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($sqlBackup));

        echo $sqlBackup;
        exit;

    } catch (PDOException $e) {
        error_log("Erro ao gerar backup: " . $e->getMessage());
        die("Erro ao gerar o arquivo de backup.");
    }
} else {
    // Se tentarem acessar direto pela URL, volta para a página principal
    header("Location: home.php"); // Altere home.php para o nome real da sua página principal, se necessário
    exit;
}