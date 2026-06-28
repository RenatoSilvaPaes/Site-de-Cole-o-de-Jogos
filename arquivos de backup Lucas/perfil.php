<?php
    declare(strict_types=1);
    session_start();

    // Verifica se a sessão foi validada
    if (!isset($_SESSION['valid'])) {
        header('Location: index.php');
        exit;
    }

    require_once 'conexao.php';

    $idUser = (int)$_SESSION['id'];
    $mensagem = '';
    $listaErros = [];
    $sucesso = false;

    // -------------------------------------------------------------------
    // PARTE 1: Faz a busca dos dados do usuário no banco de dados
    // -------------------------------------------------------------------
    try {
        $sqlBuscaDadosUser = "SELECT nome, sobrenome, email, cpf, senha, cep, endereco, cidade, foto FROM usuarios WHERE id = :id";
        $stmt = $conexaoDB->prepare($sqlBuscaDadosUser);
        $stmt->execute([
            'id' => $idUser
        ]);
        $usuarioAtual = $stmt->fetch();

        // Verifica se a busca retornou algum resultado
        if(!$usuarioAtual) {
            header('Location: index.php');
            exit;
        }
    }
    catch (PDOException $erroExcessao) {
        error_log("Erro ao carregar dados do perfil: " . $erroExcessao->getMessage());
        die("Erro técnico ao carregar o seu perfil.");
    }

    // -------------------------------------------------------------------
    // PARTE 2: Atualizar os dados cadastrais ao clicar em 'Salvar Alterações de Perfil'
    // -------------------------------------------------------------------
    if (($_SERVER['REQUEST_METHOD'] === 'POST') && (isset($_POST['atualizarCadastro']))) {
        $nomeUser = trim($_POST['userName'] ?? '');
        $sobrenomeUser = trim($_POST['userLastname'] ?? '');
        $emailUser = trim($_POST['userEmail'] ?? '');
        $cpfUser = trim($_POST['userCPF'] ?? '');
        $senhaUser = $_POST['userPassword'] ?? ''; // Nova Senha (OPCIONAL)
        $cepUser = $_POST['userCEP'] ?? '';
        $enderecoUser = trim($_POST['userAddress'] ?? '');
        $cidadeUser = trim($_POST['userCity'] ?? '');

        $caminhoImagem = $usuarioAtual['foto']; // Por padrão, mantém a foto atual
        $uploadOK = true;

        // Validação dos campos obrigatórios do formulário
        if ((empty($nomeUser)) || (empty($sobrenomeUser)) || (empty($emailUser)) || (empty($cpfUser)) || (empty($cepUser)) || (empty($enderecoUser)) || (empty($cidadeUser))) {
            $listaErros[] = "Os campos são <strong>Nome</strong>, <strong>Sobrenome</strong>, <strong>CPF</strong>, <strong>E-mail</strong>,
            <strong>CEP</strong>, <strong>Cidade</strong> e <strong>Endereço</strong>, obrigatórios!";
        }

        // Processamento da nova foto (se enviada)
        if (!empty($_FILES['uploadFoto']['name'])) {
            $arquivoTEMP = $_FILES['uploadFoto']['tmp_name'];
            $tamanhoImagem = $_FILES['uploadFoto']['size'];
            $tamanhoMaxImagem = 2 * 1024 * 1024; // 2MB
            define("TAMANHO_MAXIMO", $tamanhoMaxImagem);

            $extensaoArquivo = strtolower(pathinfo($_FILES['uploadFoto']['name'], PATHINFO_EXTENSION));

            if (TAMANHO_MAXIMO > $tamanhoMaxImagem) {
                $listaErros[] = "A nova imagem é muito grande. O tamanho máximo permitido é de 2MB";
                $uploadOK = false;
            }
            elseif (!in_array($extensaoArquivo, ['jpg', 'jpeg', 'png', 'gif'])) {
                $listaErros[] = "Formato de foto inválido. Use JPG, PNG ou GIF.";
                $uploadOK = false;
            }

            if ($uploadOK) {
                // Gera um nome único para a imagem e grava no servidor
                $novoNomeArquivo = uniqid('userID_' . $idUser, true) . '.' . $extensaoArquivo;
                $caminhoImagem = "Imagens/Users/" . $novoNomeArquivo;

                // Verifica se fez o upload do arquivo de imagem no servidor
                if (move_uploaded_file($arquivoTEMP, $caminhoImagem)) {
                    // Verifica se há uma foto antiga no servidor e apaga
                    if (($usuarioAtual['foto']) && (file_exists($usuarioAtual['foto']))) {
                        unlink($usuarioAtual['foto']);
                    }
                }
                else {
                    $listaErros[] = "Falha ao salvar a imagem no servidor.";
                    $caminhoImagem = $usuarioAtual['foto'];
                }
            }
        }

        // Caso não ocorra nenhum erro, faz a atualização no banco de dados
        if (empty($listaErros)) {
            try {
                // Caso o usuário preencha o campo da senha, gera um novo hash, caso contrário, mantém o hash antigo
                $hashSenhaFinal = !empty($senhaUser) ? password_hash($senhaUser. PASSWORD_DEFAULT) : $usuarioAtual['senha'];

                $sqlUpdateUser = "UPDATE usuarios
                                SET nome = :nome, sobrenome = :sobrenome, email = :email, cpf = :cpf, senha = :senha, cep = :cep, endereco = :endereco, cidade = :cidade, foto = :foto
                                WHERE id = :id";
                $stmtUpdateUser = $conexaoDB->prepare($sqlUpdateUser);
                $stmtUpdateUser->execute([
                    'nome' => $nomeUser,
                    'sobrenome' => $sobrenomeUser,
                    'email' => $emailUser,
                    'cpf' => $cpfUser,
                    'senha' => $hashSenhaFinal,
                    'cep' => $cepUser,
                    'endereco' => $enderecoUser,
                    'cidade' => $cidadeUser,
                    'foto' => $caminhoImagem,
                    'id' => $idUser
                ]);

                // Atualiza os dados do usuário
                $_SESSION['name'] = $nomeUser;
                $_SESSION['valid'] = $emailUser;

                $sucesso = true;
                $mensagem = "Suas alterações foram salvas com sucesso!";

                // Atualiza a foto de perfil na página
                $usuarioAtual['foto'] = $caminhoImagem;
            }
            catch (PDOException $erroExcessao) {
                error_log("Erro ao atualizar o cadastro: " . $erroExcessao->getMessage());
                $listaErros[] = "Erro: Não foi possível aplicar as alterações. O usuário ou e-mail podem estar em uso.";

                // Limpa a foto nova, caso o banco rejeite o update (ex: username duplicado)
                if (($caminhoImagem !== $usuarioAtual['foto']) && (file_exists($caminhoImagem))) {
                    unlink($caminhoImagem);                }
            }
        }
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
        <main class="container mt-4">
            <div class="row justify-content-center">
                <div class="col-md-7 col-lg-6">
                    <div class="card shadow-ms">
                        <div class="card-header text-center bg-info py-3">
                            <h3 class="mb-0 text-white">Dados do Meu Perfil</h3>
                        </div>

                        <div class="card-body p-4">
                            <div class="text-center mb-4">
                                <!-- CONDICIONAL PARA VER SE A FOTO ESTÁ NO BANCO DE DADOS E SE ELA EXISTE NO SERVIDOR -->
                                <?php if((!empty($usuarioAtual['foto'])) && (file_exists($usuarioAtual['foto']))): ?>
                                    <!-- COLOCAR FOTO DE PERFIL -->
                                    <img src="<?= htmlspecialchars($usuarioAtual['foto']) ?>" class="img-thumbnail rounded-circle shadow-sm" style="width: 120px; height: 120px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="bg-secondary text-dark rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm"
                                    style="width: 120px; height: 120px; font-size: 3rem;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" fill="currentColor" class="bi bi-person-exclamation" viewBox="0 0 16 16">
                                            <path d="M11 5a3 3 0 1 1-6 0 3 3 0 0 1 6 0M8 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4m.256 7a4.5 4.5 0 0 1-.229-1.004H3c.001-.246.154-.986.832-1.664C4.484 10.68 5.711 10 8 10q.39 0 .74.025c.226-.341.496-.65.804-.918Q8.844 9.002 8 9c-5 0-6 3-6 4s1 1 1 1z"/>
                                            <path d="M16 12.5a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0m-3.5-2a.5.5 0 0 0-.5.5v1.5a.5.5 0 0 0 1 0V11a.5.5 0 0 0-.5-.5m0 4a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1"/>
                                        </svg>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- PARA ATUALIZAÇÃO DE DADOS CADASTRAIS -->
                            <?php if(!empty($sucesso)): ?>
                                <div class="alert alert-success shadow-sm small text-center mb-4" role="alert">
                                    <?= $mensagem ?>
                                </div>
                            <?php endif; ?>

                            <!-- CONDICIONAL PARA VERIFICAR SE HÁ ERROS NO ARRAY $erros[] -->
                            <?php if(!empty($listaErros)): ?>
                                <div class="alert alert-danger shadow-sm small mb-4" role="alert">
                                    <h6 class="fw-bold mb-2">Atenção!</h6>
                                    <ul class="mb-0 ps-3">
                                        <!-- LAÇO DE REPETIÇÃO PARA IMPRIMIR OS ERROS COMO LISTA -->
                                        <?php foreach($listaErros as $erro): ?>
                                            <li><?= $erro ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>

                            <!-- FORMULÁRIO DE DADOS DO USUÁRIO -->
                            <form name="editarDadosUser" method="POST" action="" enctype="multipart/form-data" accept-charset="UTF-8"> <!-- enctype para quando é feito upload de arquivos -->
                                <div class="mb-3">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="userName" class="form-label fw-semibold">Nome:</label>
                                            <input type="text" name="userName" id="userName" class="form-control" required value="<?= htmlspecialchars($_SERVER['REQUEST_METHOD'] === 'POST' ? ($nomeUser ?? '') : $usuarioAtual['nome']) ?>">
                                        </div>

                                        <div class="col-md-6">
                                            <label for="userLastname" class="form-label fw-semibold">Sobrenome:</label>
                                            <input type="text" name="userLastname" id="userLastname" class="form-control" required value="<?= htmlspecialchars($_SERVER['REQUEST_METHOD'] === 'POST' ? ($sobrenomeUser ?? '') : $usuarioAtual['sobrenome']) ?>">
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="userCPF" class="form-label fw-semibold">CPF:</label>
                                            <input type="text" name="userCPF" id="userCPF" class="form-control" required value="<?= htmlspecialchars($_SERVER['REQUEST_METHOD'] === 'POST' ? ($cpfUser ?? '') : $usuarioAtual['cpf']) ?>">
                                        </div>

                                        <div class="col-md-6">
                                            <label for="userEmail" class="form-label fw-semibold">E-mail:</label>
                                            <input type="email" name="userEmail" id="userEmail" class="form-control" required value="<?= htmlspecialchars($_SERVER['REQUEST_METHOD'] === 'POST' ? ($emailUser ?? '') : $usuarioAtual['email']) ?>">
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="userCEP" class="form-label fw-semibold">CEP:</label>
                                            <div class="d-flex align-items-center">
                                                <input type="text" name="userCEP" id="userCEP" class="form-control" required value="<?= htmlspecialchars($_SERVER['REQUEST_METHOD'] === 'POST' ? ($cepUser ?? '') : $usuarioAtual['cep']) ?>">
                                                <div id="spinnerCep" class="spinner-border text-primary d-none" role="status"></div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <label for="userCity" class="form-label fw-semibold">Cidade:</label>
                                            <input type="text" name="userCity" id="userCity" class="form-control" required value="<?= htmlspecialchars($_SERVER['REQUEST_METHOD'] === 'POST' ? ($cidadeUser ?? '') : $usuarioAtual['cidade']) ?>">
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="userAddress" class="form-label fw-semibold">Endereço:</label>
                                    <input type="text" name="userAddress" id="userAddress" class="form-control" required value="<?= htmlspecialchars($_SERVER['REQUEST_METHOD'] === 'POST' ? ($enderecoUser ?? '') : $usuarioAtual['endereco']) ?>">
                                </div>

                                <div class="mb-3">
                                    <label for="userPassword" class="form-label fw-semibold">Senha: <span class="text-muted small">(Deixe em branco para manter a senha atual)</span></label>
                                    <input type="password" name="userPassword" id="userPassword" class="form-control" placeholder="Preencha aqui apenas se você quiser mudar sua senha!">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Trocar/Adicionar foto de perfil:</label>
                                    <input type="file" name="uploadFoto" class="form-control" accept="image/*">
                                    <div class="form-text">Formatos aceitos: JPG, PNG e GIF. Tamanho Máximo: 2MB.</div>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" name="atualizarCadastro" class="btn btn-primary btn-lg fw-bold">Salvar Alterações de Perfil</button>
                                    <a href="home.php" class="btn btn-outline-secondary">Voltar para a Página Inicial</a>
                                </div>
                            </form>

                            <hr class="text-muted my-4">

                            <div class="bg-danger bg-opacity-10 p-3 rounded border border-danger border-opacity-25">
                                <h4 class="h6 text-danger fw-bold text-center mb-1">Excluir Conta</h4>
                                <p class="text-muted small mb-3 text-center">Deseja encerrar suas atividades no sistema e apagar suas infromações do nosso banco de dados?</p>
                                <div class="text-center">
                                    <a href="excluir.php" class="btn btn-outline-danger fw-semibold">Encerrar Conta do Sistema</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <footer class="bg-dark text-secondary text-center py-3 mt-4">
            <div class="container">
                <p class="mb-0 small text-light">Custom Collection &copy; <?= date('Y') ?><br><a href="sobre.php"class="fw-bold text-info">Desenvolvido por: Lucas Stoppa | Samuel Lucas | Renato Paes</a></p>
            </div>
        </footer>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
        <script src="https://unpkg.com/imask"></script>
        <script src="correios.js"></script>
        <script>
            const cpfUserInput = document.getElementById('userCPF');

            // Aplicação de máscaras no CPF
            IMask(cpfUserInput, { mask: '000.000.000-00'});
        </script>
    </body>
</html>