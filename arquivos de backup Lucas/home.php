<?php
    declare(strict_types=1);
    session_start();

    if (!isset($_SESSION['valid'])) {
        header('Location: index.php');
        exit;
    }

    require_once 'conexao.php';

    $idUser = (int)$_SESSION['id'];

    try {
        $sqlDadosUser = "SELECT nome, sobrenome, foto FROM usuarios WHERE id = :id";
        $stmt = $conexaoDB->prepare($sqlDadosUser);
        $stmt->execute(['id' => $idUser]);
        $usuarioAtual = $stmt->fetch();

        // Verifica se a busca retornou algum resultado
        if(!$usuarioAtual) {
            header('Location: index.php');
            exit;
        }
    }
    catch(PDOException $erroExcessao) {
        error_log("Erro ao carregar dados do perfil: " . $erroExcessao->getMessage());
        die("Erro técnico ao carregar o seu perfil.");
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="estilo.css" type="text/css">
    <title>Homepage - Colecionáveis</title>
</head>

<body class="d-flex flex-column min-vh-100 bg-light">

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

    <header class="bg-white shadow-sm">
        <div class="container text-center py-5">
            <?php if((!empty($usuarioAtual['foto'])) && (file_exists($usuarioAtual['foto']))): ?>
                <img src="<?= htmlspecialchars($usuarioAtual['foto']) ?>" alt="Foto de Perfil" 
                     class="img-thumbnail rounded-circle shadow-sm" style="width: 180px; height: 180px; object-fit: cover;">
            <?php else: ?>
                <div class="bg-secondary text-white rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm"
                     style="width: 180px; height: 180px; font-size: 3rem;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="70" height="70" fill="currentColor" class="bi bi-person-exclamation" viewBox="0 0 16 16">
                        <path d="M11 5a3 3 0 1 1-6 0 3 3 0 0 1 6 0M8 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4m.256 7a4.5 4.5 0 0 1-.229-1.004H3c.001-.246.154-.986.832-1.664C4.484 10.68 5.711 10 8 10q.39 0 .74.025c.226-.341.496-.65.804-.918Q8.844 9.002 8 9c-5 0-6 3-6 4s1 1 1 1z"/>
                        <path d="M16 12.5a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0m-3.5-2a.5.5 0 0 0-.5.5v1.5a.5.5 0 0 0 1 0V11a.5.5 0 0 0-.5-.5m0 4a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1"/>
                    </svg>
                </div>
            <?php endif; ?>
        </div>
    </header>

    <main class="container my-5 flex-grow-1">
        <div class="row g-4 justify-content-center">
            
            <div class="col-md-4">
                <div class="card h-100 shadow border-0">
                    <div class="card-body p-4 text-center d-flex flex-column justify-content-between">
                        <div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor" class="bi bi-boxes mb-4" viewBox="0 0 16 16" style="color: rgb(13, 110, 253);">
                                <path d="M7.752.066a.5.5 0 0 1 .496 0l3.75 2.143a.5.5 0 0 1 .252.434v3.995l3.498 2A.5.5 0 0 1 16 9.07v4.286a.5.5 0 0 1-.252.434l-3.75 2.143a.5.5 0 0 1-.496 0l-3.502-2-3.502 2.001a.5.5 0 0 1-.496 0l-3.75-2.143A.5.5 0 0 1 0 13.357V9.071a.5.5 0 0 1 .252-.434L3.75 6.638V2.643a.5.5 0 0 1 .252-.434zM4.25 7.504 1.508 9.071l2.742 1.567 2.742-1.567zM7.5 9.933l-2.75 1.571v3.134l2.75-1.571zm1 3.134 2.75 1.571v-3.134L8.5 9.933zm.508-3.996 2.742 1.567 2.742-1.567-2.742-1.567zm2.242-2.433V3.504L8.5 5.076V8.21zM7.5 8.21V5.076L4.75 3.504v3.134zM5.258 2.643 8 4.21l2.742-1.567L8 1.076zM15 9.933l-2.75 1.571v3.134L15 13.067zM3.75 14.638v-3.134L1 9.933v3.134z"/>
                            </svg>
                            <h3 class="text-muted">Meus Itens</h3>
                            <p class="text-muted mt-3">Adicione, edite, precifique ou remova itens e relíquias da sua coleção pessoal.</p>
                        </div>
                        <a href="produtos.php" class="btn btn-sm btn-outline-primary fw-semibold w-100 mt-3">Gerenciar Acervo</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card h-100 shadow border-0">
                    <div class="card-body p-4 text-center d-flex flex-column justify-content-between">
                        <div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor" class="bi bi-person-gear mb-4" viewBox="0 0 16 16" style="color: rgb(108, 117, 125);">
                                <path d="M11 5a3 3 0 1 1-6 0 3 3 0 0 1 6 0M8 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4m.256 7a4.5 4.5 0 0 1-.229-1.004H3c.001-.246.154-.986.832-1.664C4.484 10.68 5.711 10 8 10q.39 0 .74.025c.226-.341.496-.65.804-.918Q8.844 9.002 8 9c-5 0-6 3-6 4s1 1 1 1zm3.63-4.54c.18-.613 1.048-.613 1.229 0l.043.148a.64.64 0 0 0 .921.382l.136-.074c.561-.306 1.175.308.87.869l-.075.136a.64.64 0 0 0 .382.92l.149.045c.612.18.612 1.048 0 1.229l-.15.043a.64.64 0 0 0-.38.921l.074.136c.305.561-.309 1.175-.87.87l-.136-.075a.64.64 0 0 0-.92.382l-.045.149c-.18.612-1.048.612-1.229 0l-.043-.15a.64.64 0 0 0-.921-.38l-.136.074c-.561.305-1.175-.309-.87-.87l.075-.136a.64.64 0 0 0-.382-.92l-.148-.045c-.613-.18-.613-1.048 0-1.229l.148-.043a.64.64 0 0 0 .382-.921l-.074-.136c-.306-.561.308-1.175.869-.87l.136.075a.64.64 0 0 0 .92-.382zM14 12.5a1.5 1.5 0 1 0-3 0 1.5 1.5 0 0 0 3 0"/>
                            </svg>
                            <h3 class="text-muted">Meu Perfil</h3>
                            <p class="text-muted mt-3">Altere seus dados cadastrais, atualize suas informações de endereço ou troque sua foto</p>
                        </div>
                        <a href="perfil.php" class="btn btn-sm btn-outline-secondary fw-semibold w-100 mt-3">Editar Cadastro</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card h-100 shadow border-0">
                    <div class="card-body p-4 text-center d-flex flex-column justify-content-between">
                        <div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor" class="bi bi-clipboard2-plus mb-4" viewBox="0 0 16 16" style="color: rgb(255, 193, 7);">
                                <path d="M9.5 0a.5.5 0 0 1 .5.5.5.5 0 0 0 .5.5.5.5 0 0 1 .5.5V2a.5.5 0 0 1-.5.5h-5A.5.5 0 0 1 5 2v-.5a.5.5 0 0 1 .5-.5.5.5 0 0 0 .5-.5.5.5 0 0 1 .5-.5z"/>
                                <path d="M3 2.5a.5.5 0 0 1 .5-.5H4a.5.5 0 0 0 0-1h-.5A1.5 1.5 0 0 0 2 2.5v12A1.5 1.5 0 0 0 3.5 16h9a1.5 1.5 0 0 0 1.5-1.5v-12A1.5 1.5 0 0 0 12.5 1H12a.5.5 0 0 0 0 1h.5a.5.5 0 0 1 .5.5v12a.5.5 0 0 1-.5.5h-9a.5.5 0 0 1-.5-.5z"/>
                                <path d="M8.5 6.5a.5.5 0 0 0-1 0V8H6a.5.5 0 0 0 0 1h1.5v1.5a.5.5 0 0 0 1 0V9H10a.5.5 0 0 0 0-1H8.5z"/>
                            </svg>
                            <h3 class="text-muted">Adicionar</h3>
                            <p class="text-muted mt-3">Adicione produtos para sua coleção, categorias que você achar melhor e/ou novas plataformas.</p>
                        </div>
                        <div class="row g-1 mt-3">
                            <div class="col-4"><a href="adicionar.php" class="btn btn-xs btn-outline-warning fw-semibold w-100 p-1" style="font-size: 0.75rem;">Produtos</a></div>
                            <div class="col-4"><a href="addPlataformaCategoria.php?escolha=categoria" class="btn btn-xs btn-outline-primary fw-semibold w-100 p-1" style="font-size: 0.75rem;">Categoria</a></div>
                            <div class="col-4"><a href="addPlataformaCategoria.php?escolha=plataforma" class="btn btn-xs btn-outline-secondary fw-semibold w-100 p-1" style="font-size: 0.75rem;">Plataforma</a></div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>
        
    <footer class="bg-dark text-secondary text-center py-3 mt-auto">
        <div class="container">
            <p class="mb-0 small text-light">Custom Collection &copy; <?= date('Y') ?><br><a href="sobre.php" class="fw-bold text-info">Desenvolvido por: Lucas Stoppa | Samuel Lucas | Renato Paes</a></p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>