<?php
    declare(strict_types=1);
    session_start();

    if (!isset($_SESSION['valid'])) {
        header('Location: index.php');
        exit;
    }

    require_once 'conexao.php';

    $idUsuarioLogado = (int)$_SESSION['id'];
    $mensagemErro = "";

    // EXCLUSÃO DO USUÁRIO NO BANCO DE DAODS
    if (($_SERVER['REQUEST_METHOD'] === 'POST') && (isset($_POST['confirmarAcaoExclusao']))) {
        try {
            // Faz a busca do caminho da foto antes de deletar do registro
            $sqlFoto = "SELECT foto FROM usuarios WHERE id = :id";
            $stmtFoto = $conexaoDB->prepare($sqlFoto);
            $stmtFoto->execute(['id' => $idUsuarioLogado]);
            $fotoUser = $stmtFoto->fetchColumn(); // fetchColumn, pois eu só quero a coluna das fotos

            // INÍCIO DA TRANSAÇÃO
            // Garante que ou tudo é excluído com sucesso, ou nada muda no banco (Atomicidade)
            $conexaoDB->beginTransaction();

            $sqlFotosProdutos = "SELECT foto FROM produtos WHERE user_id = :user_id";
            $stmtFotosProdutos = $conexaoDB->prepare($sqlFotosProdutos);
            $stmtFotosProdutos->execute(['user_id' => $idUsuarioLogado]);
            $listaFotos = $stmtFotosProdutos->fetchAll(PDO::FETCH_COLUMN);

            foreach($listaFotos as $fotoProduto) {
                unlink($fotoProduto);
            }

            rmdir('Imagens/Produtos/UserID' . $idUsuarioLogado);

            // Remove todos os produtos vinculados a este usuário do banco de dados
            $sqlDELETEprodutos = "DELETE FROM produtos WHERE user_id = :user_id";
            $stmtDELETEprodutos = $conexaoDB->prepare($sqlDELETEprodutos);
            $stmtDELETEprodutos->execute(['user_id' => $idUsuarioLogado]);

            // Remove o usuário da tabela 'usuarios'
            $sqlDELETEuser = "DELETE FROM usuarios WHERE id = :id";
            $stmtDELETEuser = $conexaoDB->prepare($sqlDELETEuser);
            $stmtDELETEuser->execute(['id' => $idUsuarioLogado]);

            // Caso a remoção, tanto da tabela produtos quanto da tabela usuarios, funcionar, faz a gravação no banco de dados
            $conexaoDB->commit();

            // Apaga a foto de perfil do usuário, se ele existir
            if (($fotoUser) && (file_exists((string)$fotoUser))) {
                unlink((string)$fotoUser);
            }

            // DESTRUIÇÃO DA SESSÃO: Limpa a memória do navegador e joga para o registro
            $_SESSION = array(); // Limpa o array session, que contém todos os dados do usuário logado
            session_destroy();

            // Redireciona o usuário para uma página de despedida ou registro com aviso na URL
            header('Location: index.php?status=conta_encerrada');
            exit;
        }
        catch (PDOException $erroExcessao) {
            // Se algo falhar (ex: queda do banco no meio do processo), desfaz as alterações na memória
            if ($conexaoDB->inTransaction()) {
                $conexaoDB->rollBack();
            }

            error_log("Erro ao encerrar conta do usuário " . $idUsuarioLogado . ": " . $erroExcessao->getMessage());
            $mensagemErro = "Erro técnico: Não foi possível processar o encerramento da sua conta. Tente novamente mais tarde.";
        }
    }
?>

<!DOCTYPE html>
<html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="Aula de Web">
        <meta name="keywords" content="PHP, SQL, HTML">
        <meta name="author" content="Lucas Stoppa">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
        <link rel="stylesheet" href="estilo.css" type="text/css">
        <title>Encerrar Conta</title>
    </head>

    <body class="bg-light d-flex flex-column min-vh-100">
        <div class="container py-5" style="max-width: 550px;">
            <div class="mb-3 text-center">
                <a href="perfil.php" class="text-decoration-none text-secondary ">Voltar para Meu Cadastro</a>
            </div>

            <div class="card shadow border-0 border-top border-danger border-4">
                <div class="card-header bg-white py-4 text-center">
                    <h3 class="mb-0 h4 text-danger fw-bold">Aviso Crítico de Segurança!</h3>
                </div>

                <div class="card-body p-4">
                    <?php if (!empty($mensagemErro)):?>
                        <?= $mensagemErro ?>
                    <?php endif; ?>

                    <div class="text-center mb-4">
                        <p class="fs-5 text-dark fw-semibold">Olá, <?= htmlspecialchars($_SESSION['name'] ?? 'Usuário') ?>!</p>
                        <p class="text-muted">Você está prestes a excluir permanentemente a sua conta do nosso sistema.</p>
                    </div>

                    <div class="alert alert-warning border-0 bg-warning bg-opacity-10 text-dark p-3 small mb-4" role="alert">
                        <h6 class="fw-bold"><i class="bi bi-info-circle-fill"></i>O que acontecerá ao confirmar?</h6>
                        <ul class="mb-0 ps-3 mt-2">
                            <li>Seu perfil de acesso será <strong>completamente apagado</strong>.</li>
                            <li>Todos os produtos que você cadastrou no estoque serão <strong>excluídos permanentemente</strong>.</li>
                            <li>Sua foto de perfil será destruída do nosso servido.</li>
                            <li>Esta operação é <strong>irreversível</strong>. Dados apagados não poderão ser recuperados.</li>
                        </ul>
                    </div>

                    <form method="POST" action="">
                        <div class="mb-4 text-center">
                            <label class="form-check-label fw-medium text-secondary">
                                <input type="checkbox" class="form-check-input me-2" required>
                                Estou ciente e aceito os termos de exclusão permanente dos meus dados.
                            </label>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" name="confirmarAcaoExclusao" class="btn btn-danger btn-lg fw-bold shadow-sm" 
                            onclick="return confirm('Confirma o encerramento definitivo? Essa ação NÃO tem volta.')">
                                Excluir Minha Conta e Dados
                            </button>
                            <a href="home.php" class="btn btn-outline-secondary">Cancelar e Voltar ao Início</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    </body>
</html>