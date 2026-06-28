<?php
declare(strict_types=1);
session_start(); 

if (!isset($_SESSION['valid'])) {
    header('Location: index.php');
    exit;
}

require_once "conexao.php";

$idUsuarioLogado = (int)$_SESSION['id'];
$mensagemErro = "";

$busca = trim($_GET['busca'] ?? '');

// 3 cards de largura x 4 cards de altura = 12 itens por página
$itensPorPagina = 12;

$paginaAtual = (int)($_GET['pagina'] ?? 1);
if ($paginaAtual < 1) {
    $paginaAtual = 1;
}

$offset = ($paginaAtual - 1) * $itensPorPagina;

try {
    if (!empty($busca)) {
        $sqlContar = "SELECT COUNT(*) FROM produtos WHERE user_id = :user_id AND nome LIKE :busca";
        $stmtContar = $conexaoDB->prepare($sqlContar);
        $stmtContar->execute([
            'user_id' => $idUsuarioLogado,
            'busca'    => "%$busca%"
        ]);
    } else {
        $sqlContar = "SELECT COUNT(*) FROM produtos WHERE user_id = :user_id";
        $stmtContar = $conexaoDB->prepare($sqlContar);
        $stmtContar->execute(['user_id' => $idUsuarioLogado]);
    }
    
    $totalRegistros = (int)$stmtContar->fetchColumn();
    
    $totalPaginas = (int)ceil($totalRegistros / $itensPorPagina);

    // SQL utilizando os JOINs para trazer categorias e agrupamento de plataformas
    if (!empty($busca)) {
        $sqlProdutos = "SELECT p.id, p.nome, p.quantidade AS qtd, p.preco, p.dataCad, p.foto, p.descricao,
                               c.nome AS nome_categoria,
                               GROUP_CONCAT(pl.nome SEPARATOR ', ') AS plataformas_do_jogo
                        FROM produtos p
                        LEFT JOIN categorias c ON p.categoria = c.id
                        LEFT JOIN jogoPlataforma jp ON p.id = jp.id_jogo
                        LEFT JOIN plataformas pl ON jp.id_plataforma = pl.id
                        WHERE p.user_id = :user_id AND p.nome LIKE :busca 
                        GROUP BY p.id
                        ORDER BY p.id DESC 
                        LIMIT :limit OFFSET :offset";
    } else {
        $sqlProdutos = "SELECT p.id, p.nome, p.quantidade AS qtd, p.preco, p.dataCad, p.foto, p.descricao,
                               c.nome AS nome_categoria,
                               GROUP_CONCAT(pl.nome SEPARATOR ', ') AS plataformas_do_jogo
                        FROM produtos p
                        LEFT JOIN categorias c ON p.categoria = c.id
                        LEFT JOIN jogoPlataforma jp ON p.id = jp.id_jogo
                        LEFT JOIN plataformas pl ON jp.id_plataforma = pl.id
                        WHERE p.user_id = :user_id 
                        GROUP BY p.id
                        ORDER BY p.id DESC 
                        LIMIT :limit OFFSET :offset";
    }

    $stmtProdutos = $conexaoDB->prepare($sqlProdutos);
    
    $stmtProdutos->bindValue(':user_id', $idUsuarioLogado, PDO::PARAM_INT);
    if (!empty($busca)) {
        $stmtProdutos->bindValue(':busca', "%$busca%", PDO::PARAM_STR);
    }
    $stmtProdutos->bindValue(':limit', $itensPorPagina, PDO::PARAM_INT);
    $stmtProdutos->bindValue(':offset', $offset, PDO::PARAM_INT);
    
    $stmtProdutos->execute();
    $produtos = $stmtProdutos->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Erro na listagem paginada: " . $e->getMessage());
    $mensagemErro = "Falha ao carregar a lista de produtos.";
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Galeria de Itens - Custom Collection</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Define uma altura fixa para as imagens mantendo a proporção do card */
        .card-img-container {
            height: 220px;
            overflow: hidden;
            background-color: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card-img-container img {
            object-fit: cover;
            width: 100%;
            height: 100%;
        }
    </style>
</head>
<body class="bg-light d-flex flex-column min-vh-100">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container">
            <div class="navbar-brand fw-bold text-info">
                <h2 class="m-0 d-flex align-items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor" class="bi bi-files" viewBox="0 0 16 16">
                        <path d="M13 0H6a2 2 0 0 0-2 2 2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h7a2 2 0 0 0 2-2 2 2 0 0 0 2-2V2a2 2 0 0 0-2-2m0 13V4a2 2 0 0 0-2-2H5a1 1 0 0 1 1-1h7a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1M3 4a1 1 0 0 1 1-1h7a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1z"/>
                    </svg>
                    Custom Collection
                </h2>
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
                <a href="dashboard.php" class="btn btn-sm btn-outline-info d-flex align-items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-activity" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M6 2a.5.5 0 0 1 .47.33L10 12.036l1.53-4.208A.5.5 0 0 1 12 7.5h3.5a.5.5 0 0 1 0 1h-3.15l-1.88 5.17a.5.5 0 0 1-.94 0L6 3.964 4.47 8.171A.5.5 0 0 1 4 8.5H.5a.5.5 0 0 1 0-1h3.15l1.88-5.17A.5.5 0 0 1 6 2"/>
                    </svg>
                    Dashboard
                </a>
                <a href="adicionar.php" class="btn btn-sm btn-outline-warning d-flex align-items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-lg" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2"/>
                    </svg>
                    Adicionar Produto
                </a>
                <a href="logout.php" class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-box-arrow-left" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M6 12.5a.5.5 0 0 0 .5.5h8a.5.5 0 0 0 .5-.5v-9a.5.5 0 0 0-.5-.5h-8a.5.5 0 0 0-.5.5v2a.5.5 0 0 1-1 0v-2A1.5 1.5 0 0 1 6.5 2h8A1.5 1.5 0 0 1 16 3.5v9a1.5 1.5 0 0 1-1.5 1.5h-8A1.5 1.5 0 0 1 5 12.5v-2a.5.5 0 0 1 1 0z"/>
                        <path fill-rule="evenodd" d="M.146 8.354a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L1.707 7.5H10.5a.5.5 0 0 1 0 1H1.707l2.147 2.146a.5.5 0 0 1-.708.708z"/>
                    </svg>
                    Sair
                </a>
            </div>
        </div>
    </nav>

    <main class="container my-5 flex-grow-1">
        
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4 p-3 rounded shadow-sm bg-primary">
            <div>
                <h2 class="h4 mb-0 fw-bold text-light">Sua Coleção</h2>
                <small class="text-light">Mostrando até 12 itens por página</small>
            </div>
    
            <div class="d-flex flex-wrap gap-2 justify-content-sm-end align-items-center" style="max-width: 550px; width: 100%;">
                <form method="GET" action="" class="d-flex gap-2 flex-grow-1">
                    <input type="text" name="busca" class="form-control form-control-sm" 
                        placeholder="Buscar pelo nome..." value="<?= htmlspecialchars($busca) ?>">
                    <button type="submit" class="btn btn-sm btn-outline-light fw-bold" title="Buscar">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                            <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                        </svg>
                    </button>
                    <?php if (!empty($busca)): ?>
                        <a href="?" class="btn btn-sm btn-secondary d-flex align-items-center">Limpar</a>
                    <?php endif; ?>
                </form>

                <a href="produtos.php" class="btn btn-sm btn-outline-light fw-semibold d-flex align-items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-card-list" viewBox="0 0 16 16">
                        <path d="M14.5 3a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5zm-13-1A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2z"/>
                        <path d="M5 8a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7A.5.5 0 0 1 5 8m0-2.5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5m0 5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5m-1-5a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0M4 8a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0m0 2.5a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0"/>
                    </svg>
                </a>
            </div>
        </div>

        <?php if (!empty($mensagemErro)): ?>
            <div class="alert alert-danger" role="alert"><?= $mensagemErro ?></div>
        <?php elseif (empty($produtos)): ?>
            <div class="alert alert-info text-center shadow-sm" role="alert">
                📦 Nenhum item encontrado na sua coleção.
            </div>
        <?php else: ?>
            
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-4">
                <?php foreach ($produtos as $p): ?>
                    <div class="col">
                        <div class="card h-100 shadow-sm border-0 position-relative">
                            
                            <div class="card-img-container rounded-top">
                                <?php if (!empty($p['foto']) && file_exists($p['foto'])): ?>
                                    <img src="<?= htmlspecialchars($p['foto']) ?>" alt="Capa do Jogo: <?= htmlspecialchars($p['nome']) ?>">
                                <?php else: ?>
                                    <span class="text-muted small">Sem Imagem Disponível</span>
                                <?php endif; ?>
                            </div>

                            <span class="badge bg-dark position-absolute top-0 end-0 m-2 opacity-75">
                                <?= htmlspecialchars($p['nome_categoria'] ?? 'Geral') ?>
                            </span>

                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title fw-bold text-dark mb-1 text-truncate" title="<?= htmlspecialchars($p['nome']) ?>">
                                    <?= htmlspecialchars($p['nome']) ?>
                                </h5>
                                
                                <p class="card-text text-muted small mb-2 text-truncate">
                                    <strong>Plataformas:</strong> <br>
                                    <?= !empty($p['platforms_do_jogo']) || !empty($p['plataformas_do_jogo']) ? htmlspecialchars($p['plataformas_do_jogo']) : 'Nenhuma vinculada' ?>
                                </p>

                                <div class="d-flex justify-content-between align-items-center pt-2 mt-2 border-top">
                                    <span class="fw-bold text-success h5 mb-0">
                                        R$ <?= number_format((float)$p['preco'], 2, ',', '.') ?>
                                    </span>
                                    <span class="badge bg-secondary">
                                        Quantidade: <?= (int)$p['qtd'] ?> un
                                    </span>
                                </div>
                            </div>

                            <div class="card-footer bg-white border-0 pb-3 d-flex gap-1 justify-content-center">
                                <a href="detalhes.php?id=<?= (int)$p['id'] ?>" class="btn btn-sm btn-info text-white flex-grow-1" title="Visualizar">👁️ Ver</a>
                                <a href="editar.php?id=<?= (int)$p['id'] ?>" class="btn btn-sm btn-warning flex-grow-1" title="Editar">✏️ Editar</a>
                                <a href="excluir.php?id=<?= (int)$p['id'] ?>" class="btn btn-sm btn-danger px-2" onclick="return confirm('Excluir este produto?')" title="Excluir">🗑️</a>
                            </div>

                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if ($totalPaginas > 1): ?>
                <nav aria-label="Navegação de páginas" class="mt-5">
                    <ul class="pagination justify-content-center mb-0">
                        
                        <li class="page-item <?= $paginaAtual <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="?pagina=<?= $paginaAtual - 1 ?>&busca=<?= urlencode($busca) ?>">Anterior</a>
                        </li>

                        <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                            <li class="page-item <?= $paginaAtual === $i ? 'active' : '' ?>">
                                <a class="page-link" href="?pagina=<?= $i ?>&busca=<?= urlencode($busca) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>

                        <li class="page-item <?= $paginaAtual >= $totalPaginas ? 'disabled' : '' ?>">
                            <a class="page-link" href="?pagina=<?= $paginaAtual + 1 ?>&busca=<?= urlencode($busca) ?>">Próximo</a>
                        </li>
                        
                    </ul>
                </nav>
            <?php endif; ?>

        <?php endif; ?>

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