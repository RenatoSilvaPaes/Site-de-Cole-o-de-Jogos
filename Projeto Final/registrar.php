<?php 

declare(strict_types=1);
session_start();

require_once "conexao.php";

$mensagemErro = [];
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
        $confirmaSenha = $_POST['confirma'];

        $caminhoImagem = null;

        if ((strlen($cpf) < 14) || (strlen($cep) < 8) || ($senha !== $confirmaSenha)) {
            if(strlen($cpf) < 14) {
                $mensagemErro[] = "O CPF deve ter 14 caracteres.";
            }
            
            if (strlen($cep) < 8){
                $mensagemErro[] = "O CEP deve ter 8 caracteres.";
            }

            if($senha !== $confirmaSenha) {
                $mensagemErro[] = "Os campos de senha precisam ser iguais!";
            }
            
            $tipoAlerta = 'warning';
            $uploadOK = false;
        }
        else{
            $uploadOK = true;
        }

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
                        $mensagemErro[] = "Um usuário com cpf: <strong>$cpf</strong> já está cadastrado.";
                    }

                    if ($registroDuplicado['email'] === $email) {
                        $mensagemErro[] = "O endereço de e-mail <strong>$email</strong> já está cadastrado em outra conta. Escolha outro.";
                    }
                }
            }
            catch (PDOException $e) {
                error_log("Erro ao checar duplicidade: " . $e->getMessage());
                $mensagemErro[] = "Erro técnico ao verificar disponibilidade de usuarios.";
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

            $novoNomeArquivo = uniqid('userID_' . $proximoID . "-", true) . "." . $extensao;
            $caminhoImagem = "Imagens/Users/" . $novoNomeArquivo;

            // Validações estritas de arquivo mudando a flag se houver falhas
            if ($tamImagem > TAM_MAX) {
                $mensagemErro[] = "A imagem é muito grande. O tamanho máximo permitido é de 2MB.";
                $tipoAlerta = "danger";
                $uploadOK = false;
            }
            // Verifica se $extensão está dentro do array
            elseif (!in_array($extensao, ["jpg", "jpeg", "png", "gif"])) {
                $mensagemErro[] = "Formato inválido. Somente são permitidos arquivos JPG, JPEG, PNG e GIF.";
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
                    $mensagemErro[] = "Falha ao mover o arquivo para o servidor.";
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
                $mensagemErro[] = "Erro de integridade do servidor ao salvar os dados.";
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
        <main class="mb-5">
            <div class="container" style="max-width: 800px;">
                <div class="mb-3 text-center mt-4">
                    <a href="index.php" class="text-decoration-none text-secondary">Voltar para a Página Inicial</a>
                </div>

                <div class="card shadow border-0 mb-3">
                    <div class="card-header bg-info text-white text-center py-3">
                        <h3 class="mb-0 h4 text-white">📋 Crie sua Conta</h3>
                    </div>

                    <div class="card-body p-4">
                        <?php if(!empty($mensagemErro) && !$uploadOK): ?>
                            <div class="alert alert-<?= $tipoAlerta ?> shadow-sm" role="alert">
                                <ul class="mb-0 ps-3">
                                    <?php foreach($mensagemErro as $erro): ?>
                                        <li><?= $erro ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <?php if($sucesso): ?>
                            <div class="text-center alert alert-<?= $tipoAlerta ?> shadow-sm" role="alert">
                                <?= $mensagem ?>
                            </div>

                            <div class="d-grid mt-3">
                                <a href="index.php" class="btn btn-success btn-lg">Ir para a Tela de Login</a>
                            </div>
                        <?php else: ?>
                        <form name="cadastro" method="POST" action="" enctype="multipart/form-data"> 
                            <div class="row g-3">
                                <div class="col-5">
                                    <label class="form-label fw-semibold">Nome:<span class="text-muted"> (Obrigatório)</span></label>
                                    <input type="text" class="form-control" name="nome" id="nome" placeholder="Arthur" required value="<?= htmlspecialchars($nome ?? '') ?>">
                                </div>

                                <div class="col-7">
                                    <label class="form-label fw-semibold">Sobrenome:<span class="text-muted"> (Obrigatório)</span></label>
                                    <input type="text" class="form-control" name="sobrenome" id="sobrenome" placeholder="Oliveira" required value="<?= htmlspecialchars($sobrenome ?? '') ?>">
                                </div>
                                
                                <div class="col-5">
                                    <label class="form-label fw-semibold">CPF:<span class="text-muted"> (Obrigatório)</span></label>
                                    <input type="text" name="cpf" id="cpf" class="form-control" required placeholder="Ex: 123.456.789-10" value="<?= ($cpf ?? '') ?>">
                                </div>

                                <div class="col-7">
                                    <label class="form-label fw-semibold">Endereço de E-mail:<span class="text-muted"> (Obrigatório)</span></label>
                                    <input type="email" name="email" id="email" class="form-control" placeholder="Ex: nome@email.com" value="<?= htmlspecialchars($email ?? '') ?>">
                                </div>

                                <div class="col-3">
                                    <label class="form-label fw-semibold">CEP:<span class="text-muted"> (Obrigatório)</span></label>
                                    <div class="d-flex align-items-center">
                                        <input type="text" name="userCEP" id="userCEP" class="form-control" required placeholder="Ex: 08858-019" value="<?= htmlspecialchars($cep ?? '') ?>">
                                        <div id="spinnerCep" class="spinner-border text-primary d-none" role="status"></div>
                                    </div>
                                </div>
                                
                                <div class="col-5">
                                    <label class="form-label fw-semibold">Rua:<span class="text-muted"> (Obrigatório)</span></label>
                                    <input type="text" name="userAddress" id="userAddress" class="form-control" required placeholder="Ex: Rua do Limão" value="<?= htmlspecialchars($endereco ?? '') ?>">
                                </div>

                                <div class="col-4">
                                    <label class="form-label fw-semibold">Cidade:<span class="text-muted"> (Obrigatório)</span></label>
                                    <input type="text" name="userCity" id="userCity" class="form-control" required placeholder="Ex: São Paulo" value="<?= htmlspecialchars($cidade ?? '') ?>">
                                </div>

                                

                                <div class="col-6">
                                    <label class="form-label fw-semibold">Senha:<span class="text-muted"> (Obrigatório)</span></label>
                                    <input type="password" name="password" id="password" class="form-control" required placeholder="Digite uma senha forte!">
                                </div>

                                <div class="col-6">
                                    <label class="form-label fw-semibold">Confirme a Senha:<span class="text-muted"> (Obrigatório)</span></label>
                                    <input type="password" name="confirma" id="confirma" class="form-control" required placeholder="Repita a Senha">
                                </div>

                                <div class="mb-4">
                                    <label for="arquivo" class="form-label fw-semibold">Insira uma Foto de Perfil:<span class="text-muted"> (Opicional)</span></label>
                                    <input type="file" name="arquivo" id="arquivo" class="form-control" accept="image/*">
                                    <div class="form-text">Apenas formatos JPG, PNG ou GIF. Tamanho máximo: 2MB.</div>
                                </div>

                                <div class="mb-4">
                                    <div class="accordion">
                                        <div class="accordion-item">
                                            <h2 class="accordion-header">
                                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#politicaPrivacidade" aria-expanded="false" aria-controls="politicaPrivacidade">
                                                    Política de Privacidade
                                                </button>
                                            </h2>
                                            <div class="accordion-collapse collapse" id="politicaPrivacidade">
                                                <div class="accordion-body">
                                                    <p style="text-align: justify;">
                                                        Esta Política de Privacidade descreve como coletamos, usamos e protegemos suas informações ao visitar o site Custom Collection. 
                                                        Nosso compromisso é garantir a transparência e a segurança dos seus dados.
                                                        <ol>
                                                            <li style="text-align: justify;">
                                                                Coleta de Informações
                                                                Nosso site pode coletar informações de diferentes formas, incluindo:
                                                                <ol type="a">
                                                                    <li>
                                                                        Comentários: Quando você deixa um comentário, coletamos os dados informados no formulário, além do endereço IP 
                                                                        e o tipo de navegador, para detecção de spam.
                                                                    </li>

                                                                    <li>
                                                                        Formulários de contato: Informações inseridas voluntariamente pelo usuário, como nome e e-mail, podem ser armazenadas 
                                                                        para comunicação. 
                                                                    </li>

                                                                    <li>
                                                                        Cookies: Utilizamos cookies para melhorar sua experiência de navegação e lembrar suas preferências.
                                                                    </li>
                                                                </ol>
                                                            </li>
                                                                
                                                            <li style="text-align: justify;">
                                                                Uso das Informações
                                                                As informações coletadas podem ser utilizadas para:

                                                                Melhorar a experiência do usuário e personalizar o conteúdo exibido.
                                                                Garantir a segurança do site e evitar fraudes.
                                                                Analisar tendências e estatísticas de acesso.
                                                                Enviar cookies/comunicações relacionadas ao site, caso o usuário tenha consentido.
                                                            </li>
                                                                
                                                            <li style="text-align: justify;">
                                                                Compartilhamento de Dados
                                                                Não vendemos, alugamos ou compartilhamo suas informações pessoais com terceiros, exceto quando necessário para cumprir obrigações 
                                                                legais ou quando exigido por autoridades competentes.
                                                            </li>
                                                                
                                                            <li style="text-align: justify;">
                                                                Cookies e Tecnologias de Rastreamento
                                                                Nosso site utiliza cookies para armazenar preferências do usuário e aprimorar a navegação. Você pode configurar seu navegador para 
                                                                recusar cookies, mas isso pode afetar sua experiência no site.
                                                            </li>
                                                                
                                                            <li style="text-align: justify;">
                                                                Direitos do Usuário
                                                                Você pode solicitar a remoção ou correção de seus dados pessoais a qualquer momento, entrando em contato conosco. Caso tenha uma 
                                                                conta ou tenha deixado comentários, também pode solicitar um arquivo com os dados pessoais armazenados sobre você.
                                                            </li>
                                                            
                                                            <li style="text-align: justify;">
                                                                Segurança das Informações
                                                                Adotamos medidas técnicas e organizacionais para proteger suas informações contra acessos não autorizados, uso indevido e perda de 
                                                                dados.
                                                            </li>
                                                                                                                        
                                                            <li style="text-align: justify;">
                                                                Links para Terceiros
                                                                Nosso site pode conter links para outros sites. Não nos responsabilizamos pela política de privacidade e pelo conteúdo desses sites 
                                                                externos.
                                                            </li>

                                                            <li style="text-align: justify;">
                                                                Alterações nesta Política
                                                                Podemos atualizar esta Política de Privacidade periodicamente. Recomendamos que os usuários revisem esta página regularmente para 
                                                                estar cientes de quaisquer alterações.
                                                            </li>
                                                        </ol>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <label class="form-check-label fw-medium text-secondary mt-2">
                                        <input type="checkbox" class="form-check-input me-2" required>
                                        Li e concordo com os termos de Política de Privacidade.<span class="text-muted"> (Obrigatório)</span>
                                    </label>
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

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
        <script src="correios.js"></script>
        <script src="https://unpkg.com/imask"></script>
        <script>
            const cpfUserInput = document.getElementById('cpf');
            const cep = document.getElementById('userCEP');

            // Aplicação de máscaras no CPF
            IMask(cpfUserInput, { mask: '000.000.000-00' });
            IMask(cep, {mask: '00000000'});
        </script>
    </body>
</html>