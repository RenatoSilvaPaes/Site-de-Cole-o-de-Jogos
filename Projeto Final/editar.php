<?php
    declare(strict_types=1);
    session_start();

    if (!isset($_SESSION['valid'])) {
        header('Location: index.php');
        exit;
    }

    require_once 'conexao.php';

    $erros = [];

    $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);

    if ($id <= 0) {
        header('Location: produtos.php');
        exit;
    }

    // --- CORREÇÃO EXTRA: Buscar os dados atuais antes do POST para sabermos o caminho da foto antiga ---
    try {
        $sqlInfo = "SELECT foto FROM produtos WHERE id = :id AND user_id = :user_id";
        $stmtInfo = $conexaoDB->prepare($sqlInfo);
        $stmtInfo->execute([
            'id' => $id,
            'user_id' => (int)$_SESSION['id']
        ]);
        $produtoAntigo = $stmtInfo->fetch();

        $consulta_categorias = "SELECT id, nome FROM categorias ORDER BY nome ASC";
        $stmt_categorias = $conexaoDB->prepare($consulta_categorias);
        $stmt_categorias->execute();
        $categoria = $stmt_categorias->fetchAll(PDO::FETCH_ASSOC);

        // Busca as plataformas trazendo o ID e usando PDO corretamente
        $consulta_plataformas = "SELECT id, nome FROM plataformas ORDER BY nome ASC";
        $stmt_plataformas = $conexaoDB->prepare($consulta_plataformas);
        $stmt_plataformas->execute();
        $plataformas = $stmt_plataformas->fetchAll(PDO::FETCH_ASSOC);
        
        if (!$produtoAntigo) {
            header('Location: produtos.php');
            exit;
        }
    } catch (PDOException $e) {
        error_log("Erro ao buscar foto antiga: " . $e->getMessage());
        header('Location: produtos.php');
        exit;
    }

    if (($_SERVER['REQUEST_METHOD'] === 'POST') && (isset($_POST['update']))) {
        $nome = trim($_POST['nome'] ?? '');
        $quantidade = $_POST['quantidade'] ?? '';
        $preco = $_POST['preco'] ?? '';
        $lancamento = $_POST['dataLan'] ?? '';
        $descricao = $_POST['descricao'] ?? '';
        
        // CORREÇÃO 1: Mudado para $categoriaForm para não apagar a lista $categoria do banco
        $categoriaForm = trim($_POST['categoria'] ?? ''); 
        $plataformasSelecionadas = $_POST['plataforma'] ?? [];

        $caminhoImagem = $produtoAntigo['foto'] ?? ''; 
        $uploadOK = true;

        // Validações estritas de consistência no backend
        // if (empty($nome)) {
        //     $erros[] = "O campo <strong>Nome</strong> não pode ficar vazio.";
        // }
        // if (($quantidade === '') || ((int)$quantidade) < 0) {
        //     $erros[] = "O campo <strong>Quantidade</strong> deve ser um número maior ou igual a zero.";
        // }
        // if ((empty($preco)) || ((float)$preco <= 0)) {
        //     $erros[] = "O campo <strong>Preço</strong> deve conter um valor maior que zero.";
        // }
        // if (empty($lancamento)) {
        //     $erros[] = "O campo <strong>Lançamento</strong> não pode ficar vazio.";
        // }
        // if (empty($descricao)) {
        //     $erros[] = "O campo <strong>Descrição</strong> não pode ficar vazio.";
        // }
        // if (empty($categoria)) {
        //     $erros[] = "O campo <strong>Categoria</strong> não pode ficar vazio.";
        // }
        // if (empty($plataformasSelecionadas)) {
        //     $erros[] = "O campo <strong>Plataforma</strong> não pode ficar vazio.";
        // }

        if (!empty($_FILES['arquivo']['name'])) {
            $arquivoTemp = $_FILES['arquivo']['tmp_name'];
            $tamImagem = $_FILES['arquivo']['size'];
            define("TAM_MAX", 2 * 1024 * 1024); // 2MB

            $extensao = strtolower(pathinfo($_FILES['arquivo']['name'], PATHINFO_EXTENSION));

            if ($tamImagem > TAM_MAX) {
                $erros[] = "A nova imagem é muito grande. O limite é de 2MB.";
                $uploadOK = false;
            }
            elseif (!in_array($extensao, ['jpg', 'jpeg', 'png', 'gif'])) {
                $erros[] = "Formato de foto inválido. Use JPG, PNG ou GIF.";
                $uploadOK = false;
            }

            if ($uploadOK) {
                // Gera um nome único para a nova imagem
                $novoNomeArquivo = uniqid('produtos_', true) . '.' . $extensao;
                $novoCaminho = "Imagens/Produtos/UserID" . (int)$_SESSION['id'] . "/" . $novoNomeArquivo;

                // Verifica se moveu o arquivo na pasta do servidor
                if (move_uploaded_file($arquivoTemp, $novoCaminho)) {
                    // Se o upload deu certo e havia uma foto antiga física, apaga o arquivo do disco
                    if (!empty($caminhoImagem) && file_exists($caminhoImagem)) {
                        unlink($caminhoImagem);
                    }
                    // Atualiza o caminho definitivo para salvar no banco
                    $caminhoImagem = $novoCaminho;
                }
                else {
                    $erros[] = "Falha ao salvar a nova imagem no servidor.";
                }
            }
        }

        // Se passar nas validações, realiza o update de forma segura
        if (empty($erros)) {
            try {
                $sql = "UPDATE produtos
                        SET nome = :nome, quantidade = :quantidade, preco = :preco, foto = :foto, dataLan = :dataLan, categoria = :categoria, descricao = :descricao
                        WHERE id = :id AND user_id = :user_id";
                
                $stmt = $conexaoDB->prepare($sql);
                $stmt->execute([
                    'nome' => $nome,
                    'quantidade' => (int)$quantidade,
                    'preco' => (float)$preco,
                    'foto' => $caminhoImagem,
                    'dataLan' => $lancamento,
                    'categoria' => $categoriaForm, // Atualizado aqui também
                    'descricao' => $descricao,
                    'id' => $id,
                    'user_id' => (int)$_SESSION['id']
                ]);

                $sqlPlataformaJogo = "DELETE FROM jogoPlataforma WHERE id_jogo = :id_jogo";
                $stmtPlataformaJogo = $conexaoDB->prepare($sqlPlataformaJogo);
                $stmtPlataformaJogo->execute(['id_jogo' => $id]);
                $listaPlataformas = $stmtPlataformaJogo->fetchAll(PDO::FETCH_COLUMN);

                $sqlAtualizarPlataforma = "INSERT INTO jogoPlataforma (id_jogo, id_plataforma) VALUES (:id_jogo, :id_plataforma)";
                $stmtAtualizarPlataforma = $conexaoDB->prepare($sqlAtualizarPlataforma);

                foreach ($plataformasSelecionadas as $novasPlataformas) {
                    $stmtAtualizarPlataforma->execute([
                    'id_jogo' => $id,
                    'id_plataforma' => (int)$novasPlataformas
                    ]);
                }

                
                
                header('Location: produtos.php');
                exit;
            }
            catch (PDOException $e) {
                error_log("Erro de UPDATE em editar.php: " . $e->getMessage());
                $erros[] = "Erro técnico: Não foi possível aplicar as alterações no momento.";
            }
        }
    }

    try {
        // Buscamos o produto completo para carregar a interface (GET ou formulário retornado com erro)
        $sql = "SELECT nome, quantidade, preco, descricao, categoria, dataLan, foto FROM produtos WHERE id = :id AND user_id = :user_id";
        $stmt = $conexaoDB->prepare($sql);
        $stmt->execute([
            'id' => $id,
            'user_id' => (int)$_SESSION['id']
        ]);
        $produto = $stmt->fetch();

        $sqlNomeCategoria = "SELECT nome FROM categorias WHERE id = :id";
        $stmtNomeCategoria = $conexaoDB->prepare($sqlNomeCategoria);
        $stmtNomeCategoria->execute(['id' => (int)$produto['categoria']]);
        $categoriaProduto = $stmtNomeCategoria->fetchColumn();

        // AQUI------------------------------------------------------------------------------------------------
        $sqlPlataformasProduto = "SELECT id_plataforma FROM jogoPlataforma WHERE id_jogo = :id_jogo";
        $stmtPlataformasProduto = $conexaoDB->prepare($sqlPlataformasProduto);
        $stmtPlataformasProduto->execute(['id_jogo' => $id]);
        // PDO::FETCH_COLUMN cria um array simples de IDs (ex: [1, 3, 5])
        $plataformasMarcadas = $stmtPlataformasProduto->fetchAll(PDO::FETCH_COLUMN);
        // AQUI------------------------------------------------------------------------------------------------

        
        
        $nomeForm = ($_SERVER['REQUEST_METHOD'] === 'POST') ? ($_POST['nome'] ?? '') : $produto['nome'];
        $quantidadeForm = ($_SERVER['REQUEST_METHOD'] === 'POST') ? ($_POST['quantidade'] ?? '') : $produto['quantidade'];
        $precoForm = ($_SERVER['REQUEST_METHOD'] === 'POST') ? ($_POST['preco'] ?? '') : $produto['preco'];
        $categoriaForm = ($_SERVER['REQUEST_METHOD'] === 'POST') ? ($_POST['categoria'] ?? '') : $produto['categoria'];
        $descricaoForm = ($_SERVER['REQUEST_METHOD'] === 'POST') ? ($_POST['descricao'] ?? '') : $produto['descricao'];
        $lancamentoForm = ($_SERVER['REQUEST_METHOD'] === 'POST') ? ($_POST['dataLan'] ?? '') : $produto['dataLan'];
        $fotoForm = ($_SERVER['REQUEST_METHOD'] === 'POST') ? ($caminhoImagem ?? '') : $produto['foto'];

    }
    catch (PDOException $e) {
        error_log("Erro de SELECT em editar.php: " . $e->getMessage());
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
                <div class="col-md-6">
                    <div class="card shadow border-0">
                        <div class="card-header bg-warning text-dark py-3">
                            <h3 class="h5 mb-0 fw-bold text-center">Alterar informações</h3>
                        </div>

                        <div class="card-body p-4">
                            <?php if(!empty($erros)): ?>
                                <div class="alert alert-danger shadow-sm small" role="alert">
                                    <h5 class="alert-heading h6 fw-bold">Correções necessárias:</h5>
                                    <ul class="mb-0 ps-3">
                                        <?php foreach($erros as $erro): ?>
                                            <li><?= $erro ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>

                                <form name="atualizarProdutos" method="POST" action="editar.php" enctype="multipart/form-data">
                                    <input type="hidden" name="id" value="<?= (int)$id ?>">

                                    <div class="mb-3">
                                        <label for="nome" class="form-label fw-semibold">Nome do Produto:</label>
                                        <input type="text" name="nome" id="nome" class="form-control" required value="<?= htmlspecialchars((string)$nomeForm) ?>">
                                    </div>

                                    <div class="mb-3">
                                        <label for="quantidade" class="form-label fw-semibold">Quantidade:</label>
                                        <input type="number" name="quantidade" id="quantidade" class="form-control" min="0" step="1" required value="<?= (int)$quantidadeForm ?>">
                                    </div>

                                    <div class="mb-3">
                                        <label for="preco" class="form-label fw-semibold">Preço Unitário (R$):</label>
                                        <div class="input-group">
                                            <span class="input-group-text">R$</span>
                                            <input type="number" name="preco" id="preco" class="form-control" min="0.01" step="0.01" required value="<?= (float)$precoForm ?>">
                                        </div>
                                    </div>

                                    <div class="mb-3 col-12">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label for="categoria" class="form-label fw-semibold mb-0">Categoria (Seleção Única):</label>
                                            <a href="addPlataformaCategoria.php?escolha=categoria" class="btn btn-sm btn-outline-warning fw-semibold text-dark px-2 py-1" style="font-size: 0.75rem;">Add Categoria</a>
                                        </div>
                                        <select name="categoria" id="categoria" class="form-select" aria-label="Selecione a Categoria" required>
                                            <option value="" disabled>Selecione uma categoria...</option>
                                            <?php 
                                            foreach ($categoria as $row_cat) {
                                                // Se o ID da categoria for igual ao do produto atual (ou do formulário retornado), marca como selected
                                                $selected = ((int)$row_cat['id'] === (int)$categoriaForm) ? 'selected' : '';
                                                echo "<option value='".$row_cat['id']."' ".$selected.">".$row_cat['nome']."</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>

                                    <div class="mb-3 col-">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label class="form-label fw-semibold mb-0">Plataforma (Múltipla Seleção):</label>
                                            <a href="addPlataformaCategoria.php?escolha=plataforma" class="btn btn-sm btn-outline-warning fw-semibold text-dark px-2 py-1" style="font-size: 0.75rem;">Add Plataforma</a>
                                        </div>
                                        <div class="dropdown">
                                            <button class="btn btn-white border form-select text-start" type="button" id="dropdownPlataformas" data-bs-toggle="dropdown" aria-expanded="false">
                                                <span id="textoBotaoPlataformas">Selecione as plataformas...</span>
                                            </button>
                                            <ul class="dropdown-menu w-100 p-3" aria-labelledby="dropdownPlataformas" style="max-height: 200px; overflow-y: auto;">
                                                <?php 
                                                    foreach ($plataformas as $row_plat) {
                                                        // Verifica se o ID da plataforma atual está no array de plataformas do produto
                                                        $checked = in_array($row_plat['id'], $plataformasMarcadas) ? 'checked' : '';

                                                        echo "
                                                        <li class='mb-2'>
                                                            <div class='form-check'>
                                                                <input class='form-check-input chk-plataforma' type='checkbox' name='plataforma[]' value='".$row_plat['id']."' id='plat_".$row_plat['id']."' data-nome='".$row_plat['nome']."' ".$checked.">
                                                                <label class='form-check-label w-100' for='plat_".$row_plat['id']."'>
                                                                    ".$row_plat['nome']."
                                                                </label>
                                                            </div>
                                                        </li>";
                                                    }
                                                ?>

                                            </ul>
                                        </div>
                                        <div class="form-text">Clique para abrir e selecione uma ou mais opções.</div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="dataLan" class="form-label fw-semibold">Data de Lançamento:</label>
                                        <input type="date" name="dataLan" id="dataLan" class="form-control" required value="<?= htmlspecialchars((string)$lancamentoForm) ?>">
                                    </div>

                                    <div class="mb-3">
                                        <label for="arquivo" class="form-label fw-semibold">Foto do Produto:</label>
                                        <input type="file" name="arquivo" id="arquivo" class="form-control" accept="image/*">
                                        
                                        <?php if (!empty($fotoForm) && file_exists($fotoForm)): ?>
                                            <div class="form-text text-muted mt-1">
                                                Imagem atual cadastrada: 
                                                <a href="<?= htmlspecialchars($fotoForm) ?>" target="_blank" class="text-decoration-none">Ver foto atual</a>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="mb-4">
                                        <label for="descricao" class="form-label fw-semibold">Descrição Detalhada:</label>
                                        <textarea name="descricao" id="descricao" class="form-control" rows="4" required><?= htmlspecialchars((string)$descricaoForm) ?></textarea>
                                    </div>

                                    <div class="d-flex gap-2">
                                        <a href="produtos.php" class="btn btn-secondary w-50">Cancelar</a>
                                        <button type="submit" name="update" class="btn btn-primary w-50">Salvar Alterações</button>
                                    </div>
                                </form>
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

    <script>
        function atualizarTextoPlataformas() {
            const marcados = Array.from(document.querySelectorAll('.chk-plataforma:checked'))
                                .map(cb => cb.getAttribute('data-nome'));
            const botao = document.getElementById('textoBotaoPlataformas');
            if (marcados.length > 0) {
                botao.textContent = marcados.join(', ');
            } else {
                botao.textContent = "Selecione as plataformas...";
            }
        }

        // Executa ao carregar a página para mostrar o que veio do banco
        document.addEventListener('DOMContentLoaded', atualizarTextoPlataformas);
        
        // Executa sempre que o usuário marcar/desmarcar algo
        document.querySelectorAll('.chk-plataforma').forEach(cb => {
            cb.addEventListener('change', atualizarTextoPlataformas);
        });
    </script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>