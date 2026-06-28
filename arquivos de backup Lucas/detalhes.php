<?php
declare(strict_types=1);
session_start();

if (!isset($_SESSION['valid'])) {
    header("Location: index.php");
    exit;
}

require_once "conexao.php";

$idUsuarioLogado = (int)$_SESSION['id'];
$mensagemErro = "";
$produto = [];
$id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: produtos.php');
    exit;
}

try {
    $sql = "SELECT nome, quantidade, preco, dataCad, dataLan, descricao, categoria, foto FROM produtos WHERE id = :id AND user_id = :user_id";
    $stmt = $conexaoDB->prepare($sql);
    
    $stmt->execute([
        'id' => $id,
        'user_id' => (int)$_SESSION['id']
    ]);
    
    $produto = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$produto) {
        header('Location: produtos.php');
        exit;
    }
}
catch (PDOException $e) {
    error_log("Erro de SELECT em detalhes.php: " . $e->getMessage());
    header('Location: produtos.php');
    exit;
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
    
    <main class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow border-0 overflow-hidden">
                    <div class="card-header bg-dark text-info py-3 m-0 border-0">
                        <h3 class="h5 mb-0 fw-bold text-center">🔍 Informações Sobre o Produto</h3>
                    </div>

                    <div class="row g-0 position-relative">
                        <span class="badge bg-dark position-absolute top-0 end-0 m-3 opacity-75 z-1" style="width: auto;">
                            <?= htmlspecialchars($produto['nome_categoria'] ?? 'Geral') ?>
                        </span>

                        <div class="col-md-5 product-img-container border-end">
                            <?php if (!empty($produto['foto']) && file_exists($produto['foto'])): ?>
                                <img src="<?= htmlspecialchars($produto['foto']) ?>" alt="Capa do Jogo: <?= htmlspecialchars($produto['nome']) ?>" class="mt-3">
                            <?php else: ?>
                                <div class="p-5 text-muted small">Sem Imagem Disponível</div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-7 d-flex flex-column bg-white">
                            <div class="card-body p-4 flex-grow-1">
                                <h4 class="fw-bold text-dark mb-3">
                                    <?= htmlspecialchars($produto['nome']) ?>
                                </h4>
                                
                                <hr class="text-muted opacity-25">

                                <div class="row g-3">
                                    <div class="col-sm-6">
                                        <p class="mb-1 text-muted small"><strong>Plataformas:</strong></p>
                                        <p class="text-dark mb-0"><?= !empty($produto['platforms_do_jogo']) || !empty($produto['plataformas_do_jogo']) ? htmlspecialchars($produto['plataformas_do_jogo']) : 'Nenhuma vinculada' ?></p>
                                    </div>
                                    <div class="col-sm-6">
                                        <p class="mb-1 text-muted small"><strong>Data de Lançamento:</strong></p>
                                        <p class="text-dark mb-0"><?= htmlspecialchars($produto['dataLan']) ?></p>
                                    </div>
                                    <div class="col-sm-12">
                                        <p class="mb-1 text-muted small"><strong>Data de Cadastro:</strong></p>
                                        <p class="text-dark mb-0"><?= htmlspecialchars($produto['dataCad']) ?></p>
                                    </div>
                                    <div class="col-sm-12">
                                        <p class="mb-1 text-muted small"><strong>Descrição:</strong></p>
                                        <p class="text-secondary mb-0" style="text-align: justify;"><?= htmlspecialchars($produto['descricao']) ?></p>
                                    </div>
                                </div>
                            </div>

                            <div class="card-footer bg-light p-4 border-top mt-auto">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <span class="text-muted small d-block">Valor Total</span>
                                        <span class="fw-bold text-success h4 mb-0">
                                            R$ <?= number_format($produto['quantidade'] * $produto['preco'], 2, ',', '.') ?>
                                        </span>
                                    </div>
                                    <span class="badge bg-secondary px-3 py-2 fs-6">
                                        Quantidade: <?= (int)$produto['quantidade'] ?> un
                                    </span>
                                </div>

                                <div class="d-flex gap-2">
                                    <a href="editar.php?id=<?= (int)$id ?>" class="btn btn-warning col-6 fw-bold" title="Editar">✏️ Editar</a>
                                    <a href="excluir.php?id=<?= (int)$id ?>" class="btn btn-danger col-6 fw-bold" onclick="return confirm('Excluir este produto?')" title="Excluir">🗑️ Excluir Produto</a>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </main>

    <footer class="bg-dark text-secondary text-center py-3 mt-auto">
        <div class="container">
            <p class="mb-0 small text-light">Custom Collection &copy; <?= date('Y') ?><br><a href="sobre.php"class="fw-bold text-info">Desenvolvido por: Lucas Stoppa | Samuel Lucas | Renato Paes</a></p>
        </div>
    </footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>