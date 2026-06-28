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
    
    $produto = $stmt->fetch();

    $sqlCategoria = "SELECT nome FROM categorias WHERE id = :id";
    $stmtCategoria = $conexaoDB->prepare($sqlCategoria);
    $stmtCategoria->execute(['id' => $produto['categoria']]);
    $categoria = $stmtCategoria->fetchColumn();

    $sqlPlataformas = "SELECT id_plataforma FROM jogoPlataforma WHERE id_jogo = :id_jogo";
    $stmtPlataformas = $conexaoDB->prepare($sqlPlataformas);
    $stmtPlataformas->execute(['id_jogo' => $id]);
    $plataformasID = $stmtPlataformas->fetchAll(PDO::FETCH_COLUMN);

    foreach($plataformasID as $plataformas) {
        $sqlFINAL = "SELECT nome FROM plataformas WHERE id = :id";
        $stmtFINAL = $conexaoDB->prepare($sqlFINAL);
        $stmtFINAL->execute(['id' => (int)$plataformas]);
        $plataformasNOME[] = $stmtFINAL->fetchColumn();
    }

    if (!$produto) {
        header('Location: produtos.php');
        exit;
    }
}
catch (PDOException $e) {
    // Se der erro de sintaxe, vai salvar no log para você ler depois
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
    <title>Consulta de Produtos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Garante que a imagem se adapte perfeitamente sem distorcer e sem criar espaços gigantes */
        .product-img-container {
            max-height: 450px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f8f9fa;
        }
        .product-img-container img {
            width: 100%;
            height: 100%;
            object-fit: contain; /* Mantém a proporção da capa do jogo */
        }
    </style>
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
                <a href="produtos.php" class="btn btn-sm btn-outline-primary">
                    <span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-archive" viewBox="0 0 16 16">
                        <path d="M0 2a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1v7.5a2.5 2.5 0 0 1-2.5 2.5h-9A2.5 2.5 0 0 1 1 12.5V5a1 1 0 0 1-1-1zm2 3v7.5A1.5 1.5 0 0 0 3.5 14h9a1.5 1.5 0 0 0 1.5-1.5V5zm13-3H1v2h14zM5 7.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5"/>
                        </svg>
                    </span>
                    Produtos
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
                        <span class="badge bg-white position-absolute top-0 end-0 m-3 opacity-75 z-1" style="width: auto;">
                        </span>

                        <div class="col-md-5 product-img-container border-end p-1">
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
                                        <p class="mb-1 text-muted small"><strong>Data de Cadastro:</strong></p>
                                        <p class="text-dark mb-0"><?= htmlspecialchars($produto['dataCad']) ?></p>
                                    </div>
                                    <div class="col-sm-6">
                                        <p class="mb-1 text-muted small"><strong>Data de Lançamento:</strong></p>
                                        <p class="text-dark mb-0"><?= htmlspecialchars($produto['dataLan']) ?></p>
                                    </div>
                                    <div class="col-sm-6">
                                        <p class="mb-1 text-muted small"><strong>Plataformas:</strong></p>
                                        <p class="text-dark mb-0">
                                            <?php foreach($plataformasNOME as $plataforma): ?> 
                                                <?php if($plataforma === end($plataformasNOME)): ?>
                                                    <?= htmlspecialchars($plataforma) ?> 
                                                <?php else: ?>
                                                    <?= htmlspecialchars($plataforma) ?>, 
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </p>
                                    </div>
                                    <div class="col-sm-6">
                                        <p class="mb-1 text-muted small"><strong>Categoria:</strong></p>
                                        <p class="text-dark mb-0"> <?= htmlspecialchars($categoria) ?> </p>
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
                                        Quantidade: <?= (int)$produto['quantidade'] ?>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>