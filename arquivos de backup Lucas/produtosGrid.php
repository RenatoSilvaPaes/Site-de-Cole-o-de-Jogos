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
            <p class="mb-0 small text-light">Custom Collection &copy; <?= date('Y') ?><br><a href="sobre.php"class="fw-bold text-info">Desenvolvido por: Lucas Stoppa | Samuel Lucas | Renato Paes</a></p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>