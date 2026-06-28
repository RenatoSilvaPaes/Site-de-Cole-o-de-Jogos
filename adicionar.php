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
$mensagemSucesso = ""; 

try {
    // Busca as categorias trazendo o ID e usando PDO corretamente
    $consulta_categorias = "SELECT id, nome FROM categorias ORDER BY nome ASC";
    $stmt_categorias = $conexaoDB->prepare($consulta_categorias);
    $stmt_categorias->execute();
    $categorias = $stmt_categorias->fetchAll(PDO::FETCH_ASSOC);

    // Busca as plataformas trazendo o ID e usando PDO corretamente
    $consulta_plataformas = "SELECT id, nome FROM plataformas ORDER BY nome ASC";
    $stmt_plataformas = $conexaoDB->prepare($consulta_plataformas);
    $stmt_plataformas->execute();
    $plataformas = $stmt_plataformas->fetchAll(PDO::FETCH_ASSOC);
}
catch (PDOException $e) {
    echo "Erro ao buscar dados iniciais: " . $e->getMessage();
    exit;
}

// ==========================================
// PROCESSAMENTO DO FORMULÁRIO 
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    
    // Captura e limpa as entradas textuais simples
    $nome = trim($_POST['nome'] ?? '');
    $categoria_id = !empty($_POST['categoria']) ? (int)$_POST['categoria'] : null;
    $qtd = !empty($_POST['qtd']) ? (int)$_POST['qtd'] : 0;
    $dataCad = trim($_POST['dataCad'] ?? '');
    $dataLan = trim($_POST['dataLan'] ?? '');
    $preco = !empty($_POST['preco']) ? (float)$_POST['preco'] : 0.0;
    $descricao = trim($_POST['descricao'] ?? '');
    
    // Captura o array de plataformas vindas do checkbox
    $plataformasSelecionadas = $_POST['plataforma'] ?? [];
    
    // Flag de validação do upload
    $uploadOK = true;
    $caminhoImagem = null;

    // Processamento da Imagem
    if (isset($_FILES['arquivo']) && !empty($_FILES['arquivo']['name'])) {
        $arquivoImagem = $_FILES['arquivo']['name'];
        $arquivoTemp = $_FILES['arquivo']['tmp_name'];
        $tamImagem = $_FILES['arquivo']['size'];

        define("TAM_MAX", 2 * 1024 * 1024); // 2MB
        $extensao = strtolower(pathinfo($arquivoImagem, PATHINFO_EXTENSION));

        $novoNomeArquivo = uniqid('produtos_', true) . "." . $extensao;
        $caminhoImagem = "Imagens/Produtos/UserID" . $idUsuarioLogado . "/" . $novoNomeArquivo;

        if ($tamImagem > TAM_MAX) {
            $mensagemErro = "A imagem é muito grande. O tamanho máximo permitido é de 2MB.";
            $uploadOK = false;
        } elseif (!in_array($extensao, ["jpg", "jpeg", "png", "gif"])) {
            $mensagemErro = "Formato inválido. Somente são permitidos arquivos JPG, JPEG, PNG e GIF.";
            $uploadOK = false;
        }
        
        if ($uploadOK) {
            if (!is_dir("Imagens/Produtos/UserID" . $idUsuarioLogado)) {
                mkdir("Imagens/Produtos/UserID" . $idUsuarioLogado, 0755, true);
            }
            if (!move_uploaded_file($arquivoTemp, $caminhoImagem)) {
                $mensagemErro = "Falha ao mover o arquivo para o servidor.";
                $uploadOK = false;
            }
        }
    }

    // Se as validações de arquivo passaram, podemos tentar salvar no Banco
    if ($uploadOK) {
        try {
            // Inicia uma Transação para garantir que se um insert falhar, nenhum dado fica quebrado
            $conexaoDB->beginTransaction();

            // 1. Monta o script de inserção do produto
            $sqlInsert = "INSERT INTO produtos (nome, descricao, quantidade, preco, dataCad, dataLan, user_id, foto, categoria) 
                          VALUES (:nome, :descricao, :quantidade, :preco, :dataCad, :dataLan, :user_id, :foto, :categoria)";
            
            $stmtInsert = $conexaoDB->prepare($sqlInsert);
            
            $stmtInsert->execute([
                ':nome'        => $nome,
                ':descricao'   => $descricao,
                ':quantidade'  => $qtd, 
                ':preco'       => $preco,
                ':dataCad'     => $dataCad,
                ':dataLan'     => $dataLan,
                ':user_id'     => $idUsuarioLogado,
                ':foto'        => $caminhoImagem,
                ':categoria'   => $categoria_id
            ]);

            // 2. RECUPERA O ID DO PRODUTO QUE ACABOU DE SER CRIADO
            $idJogoCriado = (int)$conexaoDB->lastInsertId();

            // 3. SALVA AS PLATAFORMAS SELECIONADAS NA TABELA INTERMEDIÁRIA
            if (!empty($plataformasSelecionadas) && is_array($plataformasSelecionadas)) {
                $sqlJogoPlat = "INSERT INTO jogoPlataforma (id_jogo, id_plataforma) VALUES (:id_jogo, :id_plataforma)";
                $stmtJogoPlat = $conexaoDB->prepare($sqlJogoPlat);
                
                foreach ($plataformasSelecionadas as $idPlataforma) {
                    $stmtJogoPlat->execute([
                        ':id_jogo'        => $idJogoCriado,
                        ':id_plataforma'  => (int)$idPlataforma
                    ]);
                }
            }

            // Confirma todas as operações no banco de dados
            $conexaoDB->commit();

            // Redireciona de volta para a lista após tudo dar certo!
            header("Location: produtos.php");
            exit;

        } catch (PDOException $e) {
            // Desfaz as alterações se der algum erro
            if ($conexaoDB->inTransaction()) {
                $conexaoDB->rollBack();
            }
            error_log("Erro ao cadastrar produto e plataformas: " . $e->getMessage());
            $mensagemErro = "Erro interno ao salvar o produto no banco de dados. Verifique os campos.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cadastro de Produtos - Sistema Loja</title>
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

        <main class="container my-5 flex-grow-1">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    
                    <?php if (!empty($mensagemErro)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo $mensagemErro; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <div class="card shadow border-0">
                        <div class="card-header bg-warning text-dark py-3">
                            <h3 class="h5 mb-0 fw-bold text-center">Cadastrar Novo Produto</h3>
                        </div>

                        <div class="card-body p-4">
                            <p class="text-muted small mb-4">Insira os dados do item abaixo. todos os campos são de preenchimento obrigatório!</p>
                            
                            <form action="adicionar.php" method="post" name="form1" enctype="multipart/form-data">
                                
                                <div class="row">
                                    <div class="mb-3 col-6">
                                        <label for="nome" class="form-label fw-semibold">Nome do Produto:</label>
                                        <input type="text" name="nome" id="nome" class="form-control" required autofocus placeholder="Ex: Pizza Tower">
                                    </div>
                                    <div class="mb-3 col-6">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label for="categoria" class="form-label fw-semibold mb-0">Categoria (Seleção Única):</label>
                                            <a href="addPlataformaCategoria.php?escolha=categoria" class="btn btn-sm btn-outline-warning fw-semibold text-dark px-2 py-1" style="font-size: 0.75rem;">Add Categoria</a>
                                        </div>
                                        <select name="categoria" id="categoria" class="form-select" aria-label="Selecione a Categoria" required>
                                            <option value="" selected disabled>Selecione uma categoria...</option>
                                            <?php 
                                            foreach ($categorias as $row_cat) {
                                                echo "<option value='".$row_cat['id']."'>".$row_cat['nome']."</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="mb-3 col-6">
                                        <label for="qtd" class="form-label fw-semibold">Quantidade em Estoque:</label>
                                        <input type="number" name="qtd" id="qtd" class="form-control" min="1" step="1" required placeholder="Ex: 10">
                                        <div class="form-text">Apenas valores inteiros maiores que zero!</div>
                                    </div>
                                    
                                    <div class="mb-3 col-6">
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
                                                    echo "
                                                    <li class='mb-2'>
                                                        <div class='form-check'>
                                                            <input class='form-check-input chk-plataforma' type='checkbox' name='plataforma[]' value='".$row_plat['id']."' id='plat_".$row_plat['id']."' data-nome='".$row_plat['nome']."'>
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
                                </div>
                                
                                <div class="row">
                                    <div class="mb-3 col-6">
                                        <label for="dataCad" class="form-label fw-semibold">Data de Entrada na Coleção:</label>
                                        <input type="date" name="dataCad" id="dataCad" class="form-control" max="<?php echo date('Y-m-d'); ?>" required>
                                        <div class="form-text">Quando você adquiriu este item?</div>
                                    </div>

                                    <div class="mb-3 col-6">
                                        <label for="dataLan" class="form-label fw-semibold">Data de Lançamento do Jogo:</label>
                                        <input type="date" name="dataLan" id="dataLan" class="form-control" required>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="mb-3 col-6">
                                        <label for="preco" class="form-label fw-semibold">Preço Unitário (R$):</label>
                                        <div class="input-group">
                                            <span class="input-group-text">R$</span>
                                            <input type="number" name="preco" id="preco" class="form-control" min="0.01" step="0.01" required>
                                        </div>
                                        <div class="form-text">Utilize ponto para separar centavos.</div>
                                    </div>
                                    
                                    <div class="mb-3 col-6">
                                        <label for="foto" class="form-label fw-semibold">Foto do Produto:</label>
                                        <input type="file" name="arquivo" id="foto" class="form-control" accept="image/*" required>
                                        <div class="form-text">Selecione uma imagem (Máx. 2MB).</div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="mb-3 col-12">
                                        <label for="descricao" class="form-label fw-semibold">Descrição do Produto:</label>
                                        <textarea name="descricao" id="descricao" class="form-control" maxlength="140" rows="2" required placeholder="Breve descrição do colecionável (Máx. 140 caracteres)..."></textarea>
                                    </div>
                                </div>

                                <div class="d-grid mt-4">
                                    <button type="submit" name="submit" class="btn btn-warning btn-lg fw-bold shadow-sm">Concluir Cadastro do Item</button>
                                </div>
                            </form>
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

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const checkboxes = document.querySelectorAll('.chk-plataforma');
                const textoBotao = document.getElementById('textoBotaoPlataformas');

                checkboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', function() {
                        const marcados = Array.from(checkboxes).filter(i => i.checked);
                        
                        if (marcados.length > 0) {
                            const nomesSelecionados = marcados.map(i => i.getAttribute('data-nome')).join(', ');
                            textoBotao.textContent = nomesSelecionados;
                        } else {
                            textoBotao.textContent = 'Selecione as plataformas...';
                        }
                    });
                });
            });
        </script>
</body>
</html>