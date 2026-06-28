<?php
    declare(strict_types=1);
    session_start();

    // Caso o usuário já esteja logado, ele é redirecionado para a páginal principal
    if (isset($_SESSION['valid'])) {
        header('Location: index.php');
        exit;
    }

    // Faz a ligação com o 'conexao.php'
    require_once 'conexao.php';

    $mensagem = '';
    $tipoAviso = 'danger';

    // Processa o formulário após clicar no botão 'Fazer Login'
    if (($_SERVER['REQUEST_METHOD'] === 'POST') && (isset($_POST['fazerLogin']))) {
        $userEmail = trim($_POST['useremail']);
        $userPassword = $_POST['senha'];

        if((empty($userEmail)) || (empty($userPassword))) {
            $mensagem = "Email de usuário ou senha não podem estar vazios. ";
        }
        else {
            try {
                $comandoSQL = "SELECT id, nome, sobrenome, cpf, email, senha FROM usuarios WHERE email = :email";
                $stmt = $conexaoDB->prepare($comandoSQL);
                $stmt->execute([
                    'email' => $userEmail
                ]);
                $resultadoBusca = $stmt->fetch();

                // Verifica se a pesquisa SQL retornou um resultado e se a senha digitada bate com a senha no banco de dados
                if (($resultadoBusca) && (password_verify($userPassword, $resultadoBusca['senha']))) {
                    $_SESSION['valid'] = $resultadoBusca['email'];
                    $_SESSION['name'] = $resultadoBusca['nome'];
                    $_SESSION['id'] = $resultadoBusca['id'];

                    // Se o login for realizado com sucesso, redireciona o usuário para home.php
                    header('Location: home.php');
                    exit;
                }
                else {
                    $mensagem = 'Nome de usuário e/ou senha inválidos.';
                }
            }
            catch (PDOException $erroExcessao) {
                error_log("Erro no processo de login: " . $erroExcessao);
                $mensagem = "Erro técnico: Não foi possível processar a autenticação no momento.";
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
        <meta name="author" content="PC Self-Custom">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
        <link rel="stylesheet" href="estilo.css" type="text/css">
        <title>Tela de Login - Colecionáveis</title>
    </head>

    <body class="d-flex flex-column min-vh-100 bg-light">
        <div class="container" style="max-width: 1000px;">
            <div class="card shadow-sm mt-5">
                <div class="card-header text-center bg-info py-3">
                    <h3 class="mb-0 text-white">Acesso ao Sistema de Colecionador</h3>
                </div>

                <div class="card-body p-4">
                    <?php if (!empty($mensagem)): ?>
                        <div class="alert alert-<?= $tipoAviso ?> shadow-sm mb-4" role="alert">
                            <?= htmlspecialchars($mensagem) ?>
                        </div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-md-6">
                            <img class="mx-3" src="Imagens/Site/logo_png.png" width="375px;" alt="Logo do Site">
                        </div>

                        <div class="col-md-6">
                            <form name="login" method="POST" action="">
                                <div class="mb-3 mt-5">
                                    <label for="useremail" class="form-label fw-semibold">E-mail de Usuário:</label>
                                    <input type="email" name="useremail" id="useremail" class="form-control" required autofocus>
                                </div>

                                <div class="mb-3">
                                    <label for="senha" class="form-label fw-semibold">Senha de Usuário:</label>
                                    <input type="password" name="senha" id="senha" class="form-control">
                                </div>

                                <div class="mb-3">
                                    <button type="submit" name="fazerLogin" class="btn btn-primary fw-bold btn-lg shadow-sm">Fazer Login</button>
                                </div>

                                <div class="mb-3">
                                    <p class="text-muted mb-0">Não possuí uma conta? <a href="registrar.php" class="text-decoration-none">Cadastre-se Aqui!</a></p>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <footer class="bg-dark text-secondary text-center py-3 mt-auto">
            <div class="container">
                <p class="mb-0 small text-light">Custom Collection &copy; <?= date('Y') ?><br><a href="sobre.php"class="fw-bold text-info">Desenvolvido por: Lucas Stoppa | Samuel Lucas | Renato Paes</a></p>
            </div>
        </footer>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    </body>
</html>