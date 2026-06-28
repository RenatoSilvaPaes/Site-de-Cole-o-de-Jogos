<?php
    declare(strict_types=1);
    session_start();

    if (!isset($_SESSION['valid'])) {
        header('Location: index.php');
        exit;
    }

    require_once "conexao.php";

    
    $id = (int)($_GET['id'] ?? 00);

    if ($id > 0) {
        try {
            $sqlFoto = "SELECT foto FROM produtos WHERE id = :id AND user_id = :user_id";
            $stmtFoto = $conexaoDB->prepare($sqlFoto);
            $stmtFoto->execute([
                'id' => $id,
                'user_id' => (int)$_SESSION['id']
            ]);
            $fotoProduto = $stmtFoto->fetchColumn();

            unlink((string)$fotoProduto);


            $sql = "DELETE FROM produtos WHERE id = :id AND user_id = :user_id";

            $stmt = $conexaoDB->prepare($sql);
            $stmt->execute([
                'id' => $id,
                'user_id' => (int)$_SESSION['id']
            ]);

            // Redirecionamente limpo e imadiato após o sucesso
            header('Location: produtos.php');
            exit;
        }
        catch(PDOException $e) {
            error_log("Erro ao excluir produto ID $id: " . $e->getMessage());
            $mensagemErro = "Erro técnico: Não foi possível processar a exclusão do item no momento.";
        }
    }
    else {
        header('Location: produtos.php');
        exit;
    }
?>


<!DOCTYPE html>
<html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="Aula de Web">
        <meta name="keywords" content="PHP, SQL">
        <meta name="author" content="Samuel Lucas">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
        <link rel="stylesheet" href="estilo.css" type="text/css">
        <title>Erro na Operação - Sistema Loja</title>
    </head>

    <body class="bg-light py-5">
        <div class="container" style="max-width: 500px;">
            <div class="card shadow border-0">
                <div class="card-header bg-danger text-white text-center py-3">
                    <h4 class="mb-0 h5">Falha na Exclusão</h4>
                </div>

                <div class="card-body p-4 text-center">
                    <div class="alert alert-danger mb-4" role="alert">
                        <?= htmlspecialchars($mensagemErro) ?>
                    </div>

                    <a href="produtos.php" class="btn btn-secondary w-100 fw-bold">Voltar para o Estoque</a>
                </div>
            </div>
        </div>

        <footer class="bg-dark text-secondary text-center py-3 mt-auto">
            <div class="container">
                <div class="row">
                    <div class="col-4">
                        <p class="mb-0 small text-light">Desenvolvido por:<br>
                            <br><a href="sobre.php" target="_blank" class="fw-bold text-info mb-0">
                                Lucas Stoppa | Samuel Lucas | Renato Paes
                            </a>
                        </p>
                    </div>
                    <div class="col-4">
                        <p class="mb-0 small text-light">Custom Collection &copy;
                            <?= date('Y') ?><br>
                        <p class="mt-0 small">
                            <br><a href="politica.php" target="_blank" class="fw-bold text-info">Política de Privacidade</a>
                        </p>
                    </div>
                    <div class="col-4">
                        <p class="mt-0 small text-light">
                            Contato:
                        </p>
                        <p class="mt-0 small">
                            <a href="https://www.instagram.com/custom_collection.26?igsh=NHNpamsxOXkydGY2" target="_blank" class="fw-bold text-info">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-instagram" viewBox="0 0 16 16">
                                    <path d="M8 0C5.829 0 5.556.01 4.703.048 3.85.088 3.269.222 2.76.42a3.9 3.9 0 0 0-1.417.923A3.9 3.9 0 0 0 .42 2.76C.222 3.268.087 3.85.048 4.7.01 5.555 0 5.827 0 8.001c0 2.172.01 2.444.048 3.297.04.852.174 1.433.372 1.942.205.526.478.972.923 1.417.444.445.89.719 1.416.923.51.198 1.09.333 1.942.372C5.555 15.99 5.827 16 8 16s2.444-.01 3.298-.048c.851-.04 1.434-.174 1.943-.372a3.9 3.9 0 0 0 1.416-.923c.445-.445.718-.891.923-1.417.197-.509.332-1.09.372-1.942C15.99 10.445 16 10.173 16 8s-.01-2.445-.048-3.299c-.04-.851-.175-1.433-.372-1.941a3.9 3.9 0 0 0-.923-1.417A3.9 3.9 0 0 0 13.24.42c-.51-.198-1.092-.333-1.943-.372C10.443.01 10.172 0 7.998 0zm-.717 1.442h.718c2.136 0 2.389.007 3.232.046.78.035 1.204.166 1.486.275.373.145.64.319.92.599s.453.546.598.92c.11.281.24.705.275 1.485.039.843.047 1.096.047 3.231s-.008 2.389-.047 3.232c-.035.78-.166 1.203-.275 1.485a2.5 2.5 0 0 1-.599.919c-.28.28-.546.453-.92.598-.28.11-.704.24-1.485.276-.843.038-1.096.047-3.232.047s-2.39-.009-3.233-.047c-.78-.036-1.203-.166-1.485-.276a2.5 2.5 0 0 1-.92-.598 2.5 2.5 0 0 1-.6-.92c-.109-.281-.24-.705-.275-1.485-.038-.843-.046-1.096-.046-3.233s.008-2.388.046-3.231c.036-.78.166-1.204.276-1.486.145-.373.319-.64.599-.92s.546-.453.92-.598c.282-.11.705-.24 1.485-.276.738-.034 1.024-.044 2.515-.045zm4.988 1.328a.96.96 0 1 0 0 1.92.96.96 0 0 0 0-1.92m-4.27 1.122a4.109 4.109 0 1 0 0 8.217 4.109 4.109 0 0 0 0-8.217m0 1.441a2.667 2.667 0 1 1 0 5.334 2.667 2.667 0 0 1 0-5.334"/>
                                </svg>
                                @CustomCollection26
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </footer>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    </body>
</html>