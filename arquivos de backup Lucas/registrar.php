<?php 

declare(strict_types=1);
session_start();

require_once "conexao.php";

$mensagem = "";
$tipoAlerta = "info";
$sucesso = false;

if (($_SERVER['REQUEST_METHOD'] === 'POST') && (isset($_POST['submit']))) {
        $nome = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $sobrenome = trim($_POST['sobrenome'] ?? '');
        $cpf = trim($_POST['cpf'] ?? '');
        $endereco = trim($_POST['userAddress'] ?? '');
        $cep = trim($_POST['userCEP'] ?? '');
        $cidade = trim($_POST['userCity'] ?? '');
        $senha = $_POST['password'] ?? '';

        $caminhoImagem = null;
        $uploadOK = true;

        // if ((empty($nome)) || (empty($email)) || (empty($sobrenome)) || (empty($senha)))  {
        //     $mensagem = "Todos os campos de texto devem ser preenchidos.";
        //     $tipoAlerta = "warning";
        //     $uploadOK = false;
        // }
         if ($uploadOK) {
            try {
                $sqlCheck = "SELECT id, cpf, email FROM usuarios WHERE cpf = :cpf OR email = :email";
                
                $smtlCheck = $conexaoDB->prepare($sqlCheck); 
                
                $smtlCheck->execute([
                    'cpf' => $cpf,
                    'email'   => $email
                ]);

                $registroDuplicado = $smtlCheck->fetch();

                if($registroDuplicado) {
                    $uploadOK = false;
                    $tipoAlerta = "danger";

                    if($registroDuplicado['cpf'] === $cpf) {
                        $mensagem = "Um usuário com cpf: <strong>$cpf</strong> já está cadastrado.";
                    }
                    else {
                        $mensagem = "O endereço de e-mail <strong>$email</strong> já está cadastrado em outra conta. Escolha outro.";
                    }
                }
            }
            catch (PDOException $e) {
                error_log("Erro ao checar duplicidade: " . $e->getMessage());
                $mensagem = "Erro técnico ao verificar disponibilidade de usuarios.";
                $tipoAlerta = "danger";
                $uploadOK = false;
            }
        }
        if (($uploadOK) && (!empty($_FILES['arquivo']['name']))) {
            $sqlProximoID = "SELECT id MAX FROM usuarios";
            $stmtProximoID = $conexaoDB->prepare($sqlProximoID);
            $stmtProximoID->execute();
            $ultimoID = $stmtProximoID->fetch();
            $proximoID = (int)($ultimoID['MAX']) + 1;
            $arquivoImagem = $_FILES['arquivo']['name'];
            // Formato do arquivo
            $arquivoTemp = $_FILES['arquivo']['tmp_name'];
            $tamImagem = $_FILES['arquivo']['size'];

            define("TAM_MAX", 2 * 1024 * 1024); // 2MB
            $extensao = strtolower(pathinfo($arquivoImagem, PATHINFO_EXTENSION));

            $novoNomeArquivo = uniqid('user_ID' . $proximoID . "-", true) . "." . $extensao;
            $caminhoImagem = "Imagens/Users/" . $novoNomeArquivo;

            // Validações estritas de arquivo mudando a flag se houver falhas
            if ($tamImagem > TAM_MAX) {
                $mensagem = "A imagem é muito grande. O tamanho máximo permitido é de 2MB.";
                $tipoAlerta = "danger";
                $uploadOK = false;
            }
            // Verifica se $extensão está dentro do array
            elseif (!in_array($extensao, ["jpg", "jpeg", "png", "gif"])) {
                $mensagem = "Formato inválido. Somente são permitidos arquivos JPG, JPEG, PNG e GIF.";
                $tipoAlerta = "danger";
                $uploadOK = false;
            }
            if ($uploadOK) {
                // Verifica se o diretório existe
                if (!is_dir("Imagens/Users")) {
                    // 0755 => Permissão de acesso => Aula Lunix
                    mkdir("Imagens/Users", 0755, true);
                }

                // Verifica se moveu o arquivo na pasta do servidor
                if(!move_uploaded_file($arquivoTemp, $caminhoImagem)) {
                    $mensagem = "Falha ao mover o arquivo para o servidor.";
                    $tipoAlerta = "danger";
                    $uploadOK = false;
                }
            }

            // Se o upload falhou por qualquer motivo acima, garante que o banco receba NULL
            if(!$uploadOK) {
                $caminhoImagem = null;
            }
        }
        if($uploadOK) {
            try {
                $hashsenha = password_hash($senha, PASSWORD_DEFAULT);

                $sql = "INSERT INTO usuarios (nome, email, sobrenome, senha, foto, cpf, endereco, cidade, cep) VALUES (:nome, :email, :sobrenome, :senha, :foto, :cpf, :endereco, :cidade, :cep)";

                $stmt = $conexaoDB->prepare($sql);
                $stmt->execute([
                    'nome'    => $nome,
                    'email'   => $email,
                    'sobrenome' => $sobrenome,
                    'cpf'     => $cpf,
                    'endereco'  => $endereco,
                    'cidade'    => $cidade,
                    'cep'       => $cep,
                    'senha'   => $hashsenha,
                    'foto'    => $caminhoImagem // A variável chega aqui com o caminho correto da string!
                ]);

                $mensagem = "Registro efetuado com sucesso! Você já pode fazer login.";
                $tipoAlerta = "success";
                $sucesso = true;
            }
            catch (PDOException $e) {
                error_log("Erro no insert de usuarios: " . $e->getMessage());
                $mensagem = "Erro de integridade do servidor ao salvar os dados.";
                $tipoAlerta = "danger";
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="Aula de Web">
        <meta name="keywords" content="PHP, SQL">
        <meta name="author" content="Samuel Lucas">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
        <link rel="stylesheet" href="estilo.css" type="text/css">
        <title>Registrar-se no Sistema de Loja</title>
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
                    <a href="home.php" class="btn btn-sm btn-outline-light">Início</a>
                    <a href="dashboard.php" class="btn btn-sm btn-outline-info">Dashboard</a>
                    <a href="adicionar.php" class="btn btn-sm btn-outline-warning">Adicionar Produto</a>
                    <a href="logout.php" class="btn btn-sm btn-outline-danger">Sair</a>
                </div>
            </div>
        </nav>

         <div class="container" style="max-width: 800px;">
            <div class="mb-3 text-center mt-4">
                <a href="index.php" class="text-decoration-none text-secondary">Voltar para a Página Inicial</a>
            </div>

            <div class="card shadow border-0 mb-3">
                <div class="card-header bg-info text-white text-center py-3">
                    <h3 class="mb-0 h4 text-white">📋 Crie sua Conta</h3>
                </div>

                <div class="card-body p-4">
                     <?php if(!empty($mensagem)): ?>
                        <div class="alert alert-<?= $tipoAlerta ?> shadow-sm" role="alert">
                            <?= $mensagem ?>
                        </div>
                    <?php endif; ?>

                    <?php if($sucesso): ?>
                        <div class="d-grid mt-3">
                            <a href="index.php" class="btn btn-success btn-lg">Ir para a Tela de Login</a>
                        </div>
                    <?php else: ?>
                     <form name="cadastro" method="POST" action="" enctype="multipart/form-data"> 
                        <div class="row g-3">
                            <div class="col-5">
                                <label class="form-label fw-semibold">Nome:</label>
                                <input type="text" class="form-control" name="nome" id="nome" placeholder="Arthur" required value="<?= htmlspecialchars($nome ?? '') ?>">
                            </div>

                            <div class="col-7">
                                <label class="form-label fw-semibold">Sobrenome:</label>
                                <input type="text" class="form-control" name="sobrenome" id="sobrenome" placeholder="Oliveira" required value="<?= htmlspecialchars($sobrenome ?? '') ?>">
                            </div>
                             
                            <div class="col-5">
                                <label class="form-label fw-semibold">CPF:</label>
                                <input type="text" name="cpf" id="cpf" class="form-control" required placeholder="Ex: 123-456-789-10" value="<?= ($cpf ?? '') ?>">
                            </div>

                            <div class="col-7">
                                <label class="form-label fw-semibold">Endereço de E-mail:</label>
                                <input type="email" name="email" id="email" class="form-control" placeholder="Ex: nome@email.com" value="<?= htmlspecialchars($email ?? '') ?>">
                            </div>

                            <div class="col-3">
                                <label class="form-label fw-semibold">CEP:</label>
                                <div class="d-flex align-items-center">
                                    <input type="text" name="userCEP" id="userCEP" class="form-control" required placeholder="Ex: 08858-019">
                                    <div id="spinnerCep" class="spinner-border text-primary d-none" role="status"></div>
                                </div>
                            </div>
                            
                            <div class="col-5">
                                <label class="form-label fw-semibold">Rua:</label>
                                <input type="text" name="userAddress" id="userAddress" class="form-control" required placeholder="Ex: Rua do Limão" value="<?= htmlspecialchars($endereco ?? '') ?>">
                            </div>

                            <div class="col-4">
                                <label class="form-label fw-semibold">Cidade:</label>
                                <input type="text" name="userCity" id="userCity" class="form-control" required placeholder="Ex: São Paulo" value="<?= htmlspecialchars($cidade ?? '') ?>">
                            </div>

                            

                            <div class="col-6">
                                <label class="form-label fw-semibold">Senha:</label>
                                <input type="password" name="password" id="password" class="form-control" required placeholder="Digite uma senha forte!">
                            </div>

                            <div class="col-6">
                                <label class="form-label fw-semibold">Confirme a Senha:</label>
                                <input type="password" name="confirma" id="confirma" class="form-control" required placeholder="Repita a Senha">
                            </div>

                            <div class="mb-4">
                                <label for="arquivo" class="form-label fw-semibold">Insira uma Foto de Perfil:</label>
                                <input type="file" name="arquivo" id="arquivo" class="form-control" accept="image/*">
                                <div class="form-text">Apenas formatos JPG, PNG ou GIF. Tamanho máximo: 2MB.</div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" name="submit" class="btn btn-info text-white btn-lg fw-bold shadow-sm">Finalizar Cadastro</button>
                            </div>
                        </form>

                        <div class="text-center mt-4">
                            <p class="mb-0 text-muted">Já possuí uma conta?
                                <a href="index.php" class="text-decoration-none">Faça Login</a>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <footer class="bg-dark text-secondary text-center py-3 mt-auto">
            <div class="container">
                <p class="mb-0 small text-light">Custom Collection &copy; <?= date('Y') ?><br><a href="sobre.php"class="fw-bold text-info">Desenvolvido por: Lucas Stoppa | Samuel Lucas | Renato Paes</a></p>
            </div>
        </footer>
        
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
        <script src="correios.js"></script>
        <script src="https://unpkg.com/imask"></script>
        <script>
            const cpfUserInput = document.getElementById('cpf');

            // Aplicação de máscaras no CPF
            IMask(cpfUserInput, { mask: '000.000.000-00'});
        </script>
    </body>
</html>