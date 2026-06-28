<?php
declare(strict_types=1);
session_start();

// Se o usuário já estiver validado na sessão, joga ele direto para a index
if (!isset($_SESSION['valid'])) {
    header("Location: index.php");
    exit;
}

// Importar a conexão moderna baseada em PDO
require_once "conexao.php";

$idUsuarioLogado   = (int)$_SESSION['id'];
$nomeUsuarioLogado = $_SESSION['name'] ?? 'Usuário';

// Inicialização das variáveis estatísticas
$totalFinanceiroEstoque = 0.0;
$totalItensFisicos = 0;
$totalProdutosCriticos = 0;
$produtosMaisCaros = [];
$totalPorCategoria = [];
$totalPorPlataforma = [];

try {
    // CONSULTA 1: Calcula o Valor Total do Estoque e Total de Itens Físicos
    $sqlGeral = "SELECT
                    SUM(preco * quantidade) AS valor_total,
                    SUM(quantidade) AS total_itens
                 FROM produtos
                 WHERE user_id = :usuario_id";
    
    $stmtGeral = $conexaoDB->prepare($sqlGeral);
    $stmtGeral->execute(['usuario_id' => $idUsuarioLogado]);
    $dadosGerais = $stmtGeral->fetch();

    $totalFinanceiroEstoque = (float)($dadosGerais['valor_total'] ?? 0.0);
    $totalItensFisicos = (int)($dadosGerais['total_itens'] ?? 0);

    // CONSULTA 3: Busca os 5 produtos mais valiosos para o gráfico
    $sqlTopProdutos = "SELECT nome, preco
                        FROM produtos
                        WHERE user_id = :usuario_id
                        ORDER BY preco DESC
                        LIMIT 5";
                        
    $stmtTop = $conexaoDB->prepare($sqlTopProdutos);
    $stmtTop->execute(['usuario_id' => $idUsuarioLogado]);
    $produtosMaisCaros = $stmtTop->fetchAll();

    // CONSULTA AJUSTADA: Busca o NOME da categoria e soma a quantidade de produtos
    $sqlCategorias = "SELECT c.nome AS nome_categoria, SUM(p.quantidade) AS total 
                      FROM produtos p
                      INNER JOIN categorias c ON p.categoria = c.id
                      WHERE p.user_id = :usuario_id 
                      GROUP BY c.id, c.nome
                      HAVING total > 0
                      ORDER BY total DESC";
    $stmtCat = $conexaoDB->prepare($sqlCategorias);
    $stmtCat->execute(['usuario_id' => $idUsuarioLogado]);
    $totalPorCategoria = $stmtCat->fetchAll();

    // CONSULTA AJUSTADA: Junta as tabelas para buscar o NOME da plataforma e soma a quantidade de produtos vinculados
    $sqlPlataformas = "SELECT plat.nome AS nome_plataforma, SUM(p.quantidade) AS total 
                       FROM produtos p
                       INNER JOIN jogoPlataforma jp ON p.id = jp.id_jogo
                       INNER JOIN plataformas plat ON jp.id_plataforma = plat.id
                       WHERE p.user_id = :usuario_id 
                       GROUP BY plat.id, plat.nome
                       HAVING total > 0
                       ORDER BY total DESC";
    $stmtPlat = $conexaoDB->prepare($sqlPlataformas);
    $stmtPlat->execute(['usuario_id' => $idUsuarioLogado]);
    $totalPorPlataforma = $stmtPlat->fetchAll();

}
catch (PDOException $e) {
    error_log("Erro ao gerar indicadores do dashboard: ". $e->getMessage());
}

// Preparação dos dados para o JavaScript do Chart.js
$labelsGrafico = [];
$valoresGrafico = [];
foreach ($produtosMaisCaros as $prod) {
    $labelsGrafico[] = $prod['nome'];
    $valoresGrafico[] = (float)$prod['preco'];
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
                <a href="produtos.php" class="btn btn-sm btn-outline-primary">
                    <span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-archive" viewBox="0 0 16 16">
                        <path d="M0 2a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1v7.5a2.5 2.5 0 0 1-2.5 2.5h-9A2.5 2.5 0 0 1 1 12.5V5a1 1 0 0 1-1-1zm2 3v7.5A1.5 1.5 0 0 0 3.5 14h9a1.5 1.5 0 0 0 1.5-1.5V5zm13-3H1v2h14zM5 7.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5"/>
                        </svg>
                    </span>
                    Produtos
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

    <div class="container my-5">
        <div class="row g-4 mb-5">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm bg-white h-100 text-center">
                    <div class="card-body p-4 d-flex flex-column align-items-center justify-content-center">
                        <div class="text-success p-3 mb-3">
                            <span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="currentColor" class="bi bi-currency-dollar" viewBox="0 0 16 16">
                                <path d="M4 10.781c.148 1.667 1.513 2.85 3.591 3.003V15h1.043v-1.216c2.27-.179 3.678-1.438 3.678-3.3 0-1.59-.947-2.51-2.956-3.028l-.722-.187V3.467c1.122.11 1.879.714 2.07 1.616h1.47c-.166-1.6-1.54-2.748-3.54-2.875V1H7.591v1.233c-1.939.23-3.27 1.472-3.27 3.156 0 1.454.966 2.483 2.661 2.917l.61.162v4.031c-1.149-.17-1.94-.8-2.131-1.718zm3.391-3.836c-1.043-.263-1.6-.825-1.6-1.616 0-.944.704-1.641 1.8-1.828v3.495l-.2-.05zm1.591 1.872c1.287.323 1.852.859 1.852 1.769 0 1.097-.826 1.828-2.2 1.939V8.73z"/>
                                </svg>
                            </span>
                        </div>
                        <div class="w-100">
                            <h6 class="text-muted mb-2 small text-uppercase fw-bold">Total Investido na Coleção:</h6>
                            <h3 class="mb-0 fw-bold fs-5 fs-md-4 text-success text-nowrap">
                                R$ <?= number_format($totalFinanceiroEstoque, 2, ',', '.') ?>
                            </h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card border-0 shadow-sm bg-white h-100 text-center">
                    <div class="card-body p-4 d-flex flex-column align-items-center justify-content-center">
                        <div class="text-secondary p-3 mb-3">
                            <span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="currentColor" class="bi bi-files" viewBox="0 0 16 16">
                                <path d="M13 0H6a2 2 0 0 0-2 2 2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h7a2 2 0 0 0 2-2 2 2 0 0 0 2-2V2a2 2 0 0 0-2-2m0 13V4a2 2 0 0 0-2-2H5a1 1 0 0 1 1-1h7a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1M3 4a1 1 0 0 1 1-1h7a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1z"/>
                                </svg>
                            </span>
                        </div>
                        <div class="w-100">
                            <h6 class="text-muted mb-2 small text-uppercase fw-bold">Total Itens na Coleção</h6>
                            <h3 class="mb-0 fw-bold fs-5 fs-md-4 text-secondary text-nowrap">
                                <?= $totalItensFisicos ?> <span class="fs-6 text-muted fw-normal">unidades</span>
                            </h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm p-4 h-100">
                    <h5 class="card-title fw-bold mb-4 text-secondary">🏅 Top 5 Itens Mais Valiosos (R$)</h5>
                    <?php if(empty($produtosMaisCaros)): ?>
                        <div class="text-center py-5 text-muted">Nenhum dado disponível para gerar o gráfico.</div>
                    <?php else: ?>
                        <div style="position: relative; height:100%; width:100%">
                            <canvas id="graficoPrecos"></canvas>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card border-0 shadow-sm p-4 h-100">
                    <h5 class="card-title fw-bold mb-3 text-secondary">📋 Resumo Analítico</h5>
                    
                    <h6 class="fw-bold text-muted mt-3 mb-2 small text-uppercase">Por Categoria</h6>
                    <?php if(empty($totalPorCategoria)): ?>
                        <p class="text-muted small">Nenhuma categoria com produto cadastrado.</p>
                    <?php else: ?>
                        <div style="max-height: 200px; overflow-y: auto; padding-right: 5px;">
                            <ul class="list-group list-group-flush mb-4">
                                <?php foreach($totalPorCategoria as $cat): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent">
                                        <span class="text-secondary"><?= htmlspecialchars((string)$cat['nome_categoria']) ?></span>
                                        <span class="badge bg-primary rounded-pill"><?= $cat['total'] ?> un</span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <h6 class="fw-bold text-muted mt-3 mb-2 small text-uppercase">Por Plataforma</h6>
                    <?php if(empty($totalPorPlataforma)): ?>
                        <p class="text-muted small">Nenhuma plataforma com produto cadastrado.</p>
                    <?php else: ?>
                        <div style="max-height: 200px; overflow-y: auto; padding-right: 5px;">
                            <ul class="list-group list-group-flush">
                                <?php foreach($totalPorPlataforma as $plat): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent">
                                        <span class="text-secondary"><?= htmlspecialchars((string)$plat['nome_plataforma']) ?></span>
                                        <span class="badge bg-info text-dark rounded-pill"><?= $plat['total'] ?> un</span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-secondary text-center py-3 mt-auto">
        <div class="container">
            <p class="mb-0 small text-light">Custom Collection &copy; <?= date('Y') ?><br><a href="sobre.php"class="fw-bold text-info">Desenvolvido por: Lucas Stoppa | Samuel Lucas | Renato Paes</a></p>
        </div>
    </footer>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const labelsProdutos = <?= json_encode($labelsGrafico) ?>;
    const valoresPrecos = <?= json_encode($valoresGrafico) ?>;

    if (labelsProdutos.length > 0) {
        const ctx = document.getElementById('graficoPrecos').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labelsProdutos,
                datasets: [{
                    label: 'Preço Unitário (R$)',
                    data: valoresPrecos,
                    backgroundColor: 'rgba(13, 110, 253, 0.75)',
                    borderColor: 'rgb(13, 110, 253)',
                    hoverBorderColor: 'red',
                    borderWidth: 2,
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'R$ ' + value.toLocaleString('pt-BR', {minimumFractionDigits: 2});
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>