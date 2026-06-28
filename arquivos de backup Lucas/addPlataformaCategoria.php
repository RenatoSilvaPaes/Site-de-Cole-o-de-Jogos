<?php
    declare(strict_types=1);
    session_start();

    if(!isset($_SESSION['valid'])) {
        header('Location: index.php');
        exit;
    }

    require_once 'conexao.php';

    $resultadoBusca = [];
    $buscaRealizada = false;
    $addCategoria = 'categoria';
    $addPlataforma = 'plataforma';
    $adicionar = $_POST['escolha'] ?? $_GET['escolha'] ?? '';
    $tabelaSQL = '';
    $titulo = '';
    $mensagem = '';
    $tipoAlerta = 'info';

    if ($adicionar === $addCategoria) {
        $tabelaSQL = 'categorias';
        $titulo = 'Categoria';
    }
    else if ($adicionar === $addPlataforma) {
        $tabelaSQL = 'plataformas';
        $titulo = 'Plataforma';
    }

    // ----------------------------------------------------------
    // PARTE 1: Faz a adição de novas Plataformas/Categorias, dependendo da variável $tabelaSQL
    if(($_SERVER['REQUEST_METHOD'] === 'POST') && (isset($_POST['cadastrar']))) {
        // Remove espaços a mais e faz a conversão para ter somente a primeira letra maiúscula
        $nomeParaCadastro = trim($_POST['nome'] ?? '');
        $nomeParaCadastro = mb_convert_case($nomeParaCadastro, MB_CASE_TITLE, "UTF-8");

        if (empty($nomeParaCadastro)) {
            $mensagem = "O campo de nome precisa ser preenchido para poder adicionar ao banco de dados!";
            $tipoAlerta = "warning";
        }
        else {
            try {
                // Checagem para verificar se o nome já existe no banco de dados
                $checkSQL = "SELECT id FROM $tabelaSQL WHERE nome = :nome";
                $checkStmt = $conexaoDB->prepare($checkSQL);
                $checkStmt->execute(['nome' => $nomeParaCadastro]);

                if ($checkStmt->fetch()) {
                    $mensagem = "AVISO! A $titulo já existe no banco de dados. Digite outra $titulo.";
                    $tipoAlerta = "warning";
                }
                else {
                    // Inserção do nome no banco de dados
                    $insertSQL = "INSERT INTO $tabelaSQL (nome) VALUES (:nome)";
                    $insertStmt = $conexaoDB->prepare($insertSQL);
                    $insertStmt->execute(['nome' => $nomeParaCadastro]);

                    $mensagem = "$titulo '$nomeParaCadastro' cadastrado com sucesso!";
                    $tipoAlerta = 'success';
                    $nomeParaCadastro = ''; // Limpa o input para novos cadastros
                }
            }
            catch (PDOException $erroExcessao) {
                error_log("Erro ao cadastrar nova $titulo: " . $erroExcessao->getMessage());
                $mensagem   = "Erro técnico: Não foi possível salvar o material no momento.";
                $tipoAlerta = "danger";
            }
        }
    }

    try {
        $sqlBusca = "SELECT * FROM $tabelaSQL";
        $stmt = $conexaoDB->query($sqlBusca);
        $resultadoBusca = $stmt->fetchAll();
        $buscaRealizada = true;
    }
    catch (PDOException $erroExcessao) {
        error_log("Não foi possível carregar a lista de plataformas: " . $erroExcessao->getMessage());
    }
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Consulta de Produtos - Sistema Loja</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex flex-column min-vh-100">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container">
            <div class="navbar-brand fw-bold text-info">
                <span><h2>
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor" class="bi bi-files" viewBox="0 0 16 16">
                    <path d="M13 0H6a2 2 0 0 0-2 2 2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h7a2 2 0 0 0 2-2 2 2 0 0 0 2-2V2a2 2 0 0 0-2-2m0 13V4a2 2 0 0 0-2-2H5a1 1 0 0 1 1-1h7a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1M3 4a1 1 0 0 1 1-1h7a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1z"/>
                    </svg>
                    Custom Collection
                </h2></span>
            </div>
            <div class="d-flex gap-2">
                <a href="home.php" class="btn btn-sm btn-outline-light">
                    <span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-house-door" viewBox="0 0 16 16">
                        <path d="M8.354 1.146a.5.5 0 0 0-.708 0l-6 6A.5.5 0 0 0 1.5 7.5v7a.5.5 0 0 0 .5.5h4.5a.5.5 0 0 0 .5-.5v-4h2v4a.5.5 0 0 0 .5.5H14a.5.5 0 0 0 .5-.5v-7a.5.5 0 0 0-.146-.354L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293zM2.5 14V7.707l5.5-5.5 5.5 5.5V14H10v-4a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5v4z"/>
                        </svg>
                    </span>
                    Início
                </a>
                <a href="dashboard.php" class="btn btn-sm btn-outline-info">
                    <span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-activity" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M6 2a.5.5 0 0 1 .47.33L10 12.036l1.53-4.208A.5.5 0 0 1 12 7.5h3.5a.5.5 0 0 1 0 1h-3.15l-1.88 5.17a.5.5 0 0 1-.94 0L6 3.964 4.47 8.171A.5.5 0 0 1 4 8.5H.5a.5.5 0 0 1 0-1h3.15l1.88-5.17A.5.5 0 0 1 6 2"/>
                        </svg>
                    </span>
                    Dashboard
                </a>
                <a href="adicionar.php" class="btn btn-sm btn-outline-warning">
                    <span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-lg" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2"/>
                        </svg>
                    </span>
                    Adicionar Produto
                </a>
                <a href="logout.php" class="btn btn-sm btn-outline-danger">
                    <span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-box-arrow-left" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M6 12.5a.5.5 0 0 0 .5.5h8a.5.5 0 0 0 .5-.5v-9a.5.5 0 0 0-.5-.5h-8a.5.5 0 0 0-.5.5v2a.5.5 0 0 1-1 0v-2A1.5 1.5 0 0 1 6.5 2h8A1.5 1.5 0 0 1 16 3.5v9a1.5 1.5 0 0 1-1.5 1.5h-8A1.5 1.5 0 0 1 5 12.5v-2a.5.5 0 0 1 1 0z"/>
                        <path fill-rule="evenodd" d="M.146 8.354a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L1.707 7.5H10.5a.5.5 0 0 1 0 1H1.707l2.147 2.146a.5.5 0 0 1-.708.708z"/>
                        </svg>
                    </span>
                    Sair
                </a>
            </div>
        </div>
    </nav>

        <div class="container my-3">
            <div class="row justify-content-center">
                <div class="col-6">
                    <div class="card shadow-sm h-100">
                        <div class="card-header text-center bg-warning py-3">
                            <h3 class="mb-0 text-white">Adicionar uma Nova <?= $titulo ?> à Lista</h3>
                        </div>

                        <div class="card-body p-4">
                            <p class="text-muted text-center">Cadastre novas <?= strtolower($titulo) ?>s para poder adicionar aos itens de sua coleção.</p>

                            <?php if(!empty($mensagem)): ?>
                                <div class="alert alert-<?= $tipoAlerta ?> shadow-sm text-center small mb-3" role="alert">
                                    <?= htmlspecialchars($mensagem) ?>
                                </div>
                            <?php endif; ?>

                            <form name="adicionar" action="" method="POST">
                                <div class="mb-3">
                                    <label for="nome" class="form-label fw-semibold">Nome da <?= $titulo ?>:</label>
                                    <input type="text" name="nome" id="nome" class="form-control" placeholder="Ex: <?= strtolower($titulo) === $addPlataforma ? 'Playstation; Nintendo, Xbox' : 'FPS, Plataforma, Ação' ?>">
                                    <div class="form-text">O sistema irá padronizar a escrita automáticamente.</div>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" name="cadastrar" class="btn btn-warning btn-lg fw-bold">Cadastrar <?= $titulo ?></button>
                                    <hr>
                                    <a href="adicionar.php" class="btn btn-outline-secondary secondary">Voltar ao Cadastro de Itens</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-6">
                    <div class="card shadow-sm">
                        <div class="card-header text-center bg-secondary py-3">
                            <h3 class="mb-0 text-white">Lista de <?= $titulo ?>s Atualizada</h3>
                        </div>

                        <div class="card-body p-4">
                            <?php if(empty($resultadoBusca)): ?>
                                <div class="alert alert-light text-center small text-muted my-3">Nenhuma <?= $titulo ?> encontrada no banco de dados.</div>
                            <?php else: ?>
                                <div class="table-responsive" style="max-height: 310px; overflow-y: auto;">
                                    <table class="table table-striped table-hover align-middle border mb-0 small">
                                        <thead class="table-dark sticky-top">
                                            <tr>
                                                <th style="width: 25%;" class="text-center">ID #</th>
                                                <th style="width: 75%;">Nome da <?= $titulo ?></th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            <?php foreach ($resultadoBusca as $itemLista): ?>
                                                <tr>
                                                    <td class="text-center text-muted font-monospace"><?= (int)$itemLista['id'] ?></td>
                                                    <td class="fw-semibold text-secondary">
                                                        <span>
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-joystick" viewBox="0 0 16 16">
                                                            <path d="M10 2a2 2 0 0 1-1.5 1.937v5.087c.863.083 1.5.377 1.5.726 0 .414-.895.75-2 .75s-2-.336-2-.75c0-.35.637-.643 1.5-.726V3.937A2 2 0 1 1 10 2"/>
                                                            <path d="M0 9.665v1.717a1 1 0 0 0 .553.894l6.553 3.277a2 2 0 0 0 1.788 0l6.553-3.277a1 1 0 0 0 .553-.894V9.665c0-.1-.06-.19-.152-.23L9.5 6.715v.993l5.227 2.178a.125.125 0 0 1 .001.23l-5.94 2.546a2 2 0 0 1-1.576 0l-5.94-2.546a.125.125 0 0 1 .001-.23L6.5 7.708l-.013-.988L.152 9.435a.25.25 0 0 0-.152.23"/>
                                                            </svg>
                                                        </span>
                                                        <?= htmlspecialchars($itemLista['nome']) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="form-text text-muted text-end mt-2 small">
                                    Total: <?= count($resultadoBusca) ?> registros.
                                </div>
                            <?php endif; ?>
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

