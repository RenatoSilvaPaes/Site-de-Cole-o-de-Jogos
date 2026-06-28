<?php
    declare(strict_types=1);
    session_start();

    // Corta a execução imediatamente se o usuário não estiver logado
    if (!isset($_SESSION['valid'])) {
        header('Location: index.php');
        exit;
    }

    // Importa a conexão moderna baseada em PDO ($strcon)
    require_once "conexao.php";

    $erros = [];
    $sucesso = false;
    $mensagemSucesso = "";

    // Executa o bloco apenas se a requisição for um POST vindo do formulário
    if (($_SERVER['REQUEST_METHOD'] === 'POST') && (isset($_POST['Submit']))) {
        // Captura e higieniza as entradas textuais
        $nome = trim($_POST['nome'] ?? '');
        $qtd = $_POST['qtd'] ?? '';
        $preco = $_POST['preco'] ?? '';
        $categoria
        $plataforma
        // ID do usuário logado
        $loginID = $_SESSION['id'];

        // Validação de consistência de dados no Back-end
        if(empty($nome)) {
           $erros[] =  "O campo <strong>Nome do Produto</strong> não pode ficar vazio.";
        }
        
        // Quando é INT ou FLOAT, não pode usar empty()
        if(($qtd === '') || ((int)$qtd < 0)) {
           $erros[] =  "O campo <strong>Quantidade</strong> deve conter um número válido (maior ou igual a zero).";
        }

        if((empty($preco)) || ((float)$preco <= 0)) {
           $erros[] =  "O campo <strong>Preço</strong> deve conter um valor monetário maior que zero.";
        }

        // 3. Se passou pelas validações sem nenhum erro, realiza o insert via PDO
        if (empty($erros)) {
            try {
                // Query preparada (Prepared Statement) separando a estrutura dos dados reais
                $sql = "INSERT INTO produtos (nomeProduto, qtd, preco, login_id) VALUES (:nome, :qtd, :preco, :login_id)";
                $stmt = $conexaoDB->prepare($sql);

                // O PDO injeta e higieniza os valores automaticamente, forçando a tipagem correta
                $stmt->execute([
                    'nome' => $nome,
                    'qtd' => (int)$qtd,
                    'preco' => (float)$preco,
                    'login_id' => (int)$loginID
                ]);

                $sucesso = true;
                $mensagemSucesso = "Produto <strong>" . htmlspecialchars($nome) . "</strong> cadastrado com sucesso!";
            }
            catch (PDOException $e) {
                error_log("Erro de inserção em adicionar.php: " . $e->getMessage());
                $erros[] = "Erro técnico: Não foi possível salvar o produto no banco de dados.";
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="Aula de Web">
        <meta name="keywords" content="PHP, SQL">
        <meta name="author" content="Lucas Stoppa">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
        <link rel="stylesheet" href="estilo.css" type="text/css">
        <title>Status do Cadastro - Sistema Loja</title>
    </head>

    <body class="bg-light py-5">
        <div class="container" style="max-width: 500px;">
            <div class="card shadow border-0">
                <div class="card-header bg-dark text-white text-center py-3">
                    <h4 class="mb-0 h5">Status do Processamento</h4>
                </div>

                <div class="card-body p-4 text-center">
                    <?php if($sucesso): ?>
                        <div class="alert alert-success shadow-sm mb-4" role="alert">
                            <h5 class="alert-heading">Operação Concluída!</h5>
                            <p class="mb-0 small"><?= $mensagemSucesso ?></p>
                        </div>

                        <div class="d-grid gap-2">
                            <a href="produtos.php" class="btn btn-primary fw-bold">Ver Lista de Produtos</a>
                            <a href="adicionar.html" class="btn btn-outline-secondary btn-sm">Cadastrar Outro Item</a>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-danger shadow-sm text-star mb-4" role="alert">
                            <h5 class="alert-heading">Falha na Validação</h5>
                            <ul class="mb-0 small ps-3">
                                <?php foreach($erros as $erro): ?>
                                    <li><?= $erro ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>

                        <div class="d-grid">
                            <a href="javascript:window.history.back()" class="btn btn-warning fw-bold">Voltar e Corrigir</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    </body>
</html>