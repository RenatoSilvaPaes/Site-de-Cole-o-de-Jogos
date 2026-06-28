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

    // SQL CORRIGIDO: Fazendo os JOINs corretos com 'jogoPlataforma' e 'plataformas'
    if (!empty($busca)) {
        $sqlProdutos = "SELECT p.id, p.nome, p.quantidade AS qtd, p.preco, p.dataCad, p.foto, 
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
        $sqlProdutos = "SELECT p.id, p.nome, p.quantidade AS qtd, p.preco, p.dataCad, p.foto, 
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

    <main class="container my-5 flex-grow-1"> 
        <div class="card shadow border-0">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4 bg-primary p-3 rounded shadow-sm">
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

                    <a href="produtosGrid.php" class="btn btn-sm btn-outline-light fw-semibold d-flex align-items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-grid" viewBox="0 0 16 16">
                        <path d="M1 2.5A1.5 1.5 0 0 1 2.5 1h3A1.5 1.5 0 0 1 7 2.5v3A1.5 1.5 0 0 1 5.5 7h-3A1.5 1.5 0 0 1 1 5.5zM2.5 2a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5zm6.5.5A1.5 1.5 0 0 1 10.5 1h3A1.5 1.5 0 0 1 15 2.5v3A1.5 1.5 0 0 1 13.5 7h-3A1.5 1.5 0 0 1 9 5.5zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5zM1 10.5A1.5 1.5 0 0 1 2.5 9h3A1.5 1.5 0 0 1 7 10.5v3A1.5 1.5 0 0 1 5.5 15h-3A1.5 1.5 0 0 1 1 13.5zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5zm6.5.5A1.5 1.5 0 0 1 10.5 9h3a1.5 1.5 0 0 1 1.5 1.5v3a1.5 1.5 0 0 1-1.5 1.5h-3A1.5 1.5 0 0 1 9 13.5zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5z"/>
                        </svg>

                    </a>
                </div>
            </div>
            
            <div class="card-body p-4">

                <?php if (!empty($mensagemErro)): ?>
                    <div class="alert alert-danger" role="alert"><?= $mensagemErro ?></div>
                <?php elseif (empty($produtos)): ?>
                    <div class="alert alert-info text-center shadow-sm" role="alert">
                        📦 Nenhum produto encontrado para os critérios informados.
                    </div>
                <?php else: ?>
                    
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover align-middle mb-4 small">
                            <thead class="table-dark text-center">
                                <tr>
                                    <th class="text-start" style="min-width: 200px;">Nome do Item</th>
                                    <th>Qtd</th>
                                    <th>Preço Unitário</th>
                                    <th>Cadastro</th>
                                    <th>Categoria</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($produtos as $p): ?>
                                    <tr>

                                        <td class="text-start">
                                            <span class="fw-semibold text-dark d-block mb-0"><?= htmlspecialchars($p['nome']) ?></span>
                                            <small class="text-muted d-block" style="font-size: 0.825rem;">
                                                <?= htmlspecialchars($p['nome']) ?><?php if (!empty($p['plataformas_do_jogo'])): ?> - (<?= htmlspecialchars($p['plataformas_do_jogo']) ?>)<?php endif; ?>
                                            </small>
                                        </td>
                                        
                                        <td class="text-center"><span class="badge bg-secondary px-2 py-1"><?= (int)$p['qtd'] ?> un</span></td>
                                        
                                        <td class="text-center fw-bold text-success">R$ <?= number_format((float)$p['preco'], 2, ',', '.') ?></td>
                                        
                                        <td class="text-center"><?= date('d/m/Y', strtotime($p['dataCad'])) ?></td>
                                        
                                        <td class="text-center text-dark fw-medium">
                                            <?= htmlspecialchars($p['nome_categoria'] ?? 'Sem Categoria') ?>
                                        </td>

                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-1">
                                                <a href="detalhes.php?id=<?= (int)$p['id'] ?>" class="btn btn-sm btn-info text-white" title="Detalhes">👁️</a>
                                                <a href="editar.php?id=<?= (int)$p['id'] ?>" class="btn btn-sm btn-warning" title="Editar">✏️</a>
                                                <a href="excluir.php?id=<?= (int)$p['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Excluir este produto?')" title="Excluir">🗑️</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if ($totalPaginas > 1): ?>
                        <nav aria-label="Navegação de páginas do estoque">
                            <ul class="pagination justify-content-center mb-0">
                                
                                <li class="page-item <?= $paginaAtual <= 1 ? 'disabled' : '' ?>">
                                    <a class="page-link" href="produtos.php?pagina=<?= $paginaAtual - 1 ?>&busca=<?= urlencode($busca) ?>">Anterior</a>
                                </li>

                                <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                                    <li class="page-item <?= $paginaAtual === $i ? 'active' : '' ?>">
                                        <a class="page-link" href="produtos.php?pagina=<?= $i ?>&busca=<?= urlencode($busca) ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>

                                <li class="page-item <?= $paginaAtual >= $totalPaginas ? 'disabled' : '' ?>">
                                    <a class="page-link" href="produtos.php?pagina=<?= $paginaAtual + 1 ?>&busca=<?= urlencode($busca) ?>">Próximo</a>
                                </li>
                                
                            </ul>
                        </nav>
                    <?php endif; ?>

                <?php endif; ?>

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