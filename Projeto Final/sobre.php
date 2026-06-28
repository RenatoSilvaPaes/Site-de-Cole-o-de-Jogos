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
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-8">
                <div class="card border-0">
                    <div class="card-body p-4 p-md-5">
                        <h1 class="card-title mb-4 text-dark border-bottom pb-2 fw-bold">Sobre Nós</h1>
                        <p class="text-secondary lead">
                            Este é um site desenvolvido por alunos da turma M31 do Senac Vila Prudente, somos a <strong>Custom Collection</strong>. Desenvolvido por Lucas Stoppa, Renato Paes e Samuel Lucas.
                        </p>

                        <hr class="my-4">

                        <div class="mb-4">
                            <h3 class="h5 text-dark fw-bold">1. Início do Projeto:</h3>
                            <p class="text-secondary">Nosso site foi desenvolvido para nos desafiar:</p>
                            <ul class="text-secondary">
                                <li><strong>Desenvolvimento:</strong> O tema foi fornecido pelo nosso professor, tendo como foco principal ser uma página para catalogar alguma coleção, e o tema da coleção era de livre escolha.</li>
                                <li><strong>Ideias:</strong> Como o tema era livre, escolhemos algo que nós 3 gostamos... Jogos! (era isso ou carrinhos da Hotweels kk).</li>
                                <li><strong>Objetivo:</strong> Fizemos com base no que conhecemos, pensando em quais opções o colecionador gostaria de ter para catalogar seus itens.</li>
                            </ul>
                        </div>

                        <div class="mb-4">
                            <h3 class="h5 text-dark fw-bold">2. Como foi Desenvolvido?</h3>
                            <p class="text-secondary">Nosso site foi desenvolvido utilizando as seguintes linguagens:</p>
                            <ul class="text-secondary">
                                <li><strong>HTML:</strong> Parte visual e funcional do site.</li>
                                <li><strong>PHP:</strong> Parte em que a página conversa com o banco de dados.</li>
                                <li><strong>JavaScript:</strong> Parte em que a página acessa outras API's em sites externos (como o dos Correios por exemplo, que traz o nome da rua, bairro, etc com base apenas no CEP).</li>
                            </ul>
                        </div>

                        <div class="mb-4">
                            <h3 class="h5 text-dark fw-bold">3. Etapas do Projeto</h3>
                            <ul class="text-secondary">
                                <li><strong>Etapas:</strong></li>
                                    <ul class="mb-1"><strong>Brainstorm:</strong> Etapa para pensar sobre como fazer cada página, e quando fazer ela.</ul>
                                    <ul class="mb-1"><strong>Banco de Dados:</strong> Foi o segundo passo para a crição do nosso site. O professor forneceu a base para a criação do banco de dados, apenas para manter um padrão entre os projetos da sala. O que fizemos foi modificar com base nas nossas necessidades para se encaixar no nosso projeto.</ul>
                                    <ul class="mb-1"><strong>Páginas:</strong> O próximo passo para o desenvolvimento foi justamente criar as páginas, como elas deveriam ser visualmente, quais funcionalidades deveriam ter, como acessar cada funcionalidade sem poluir a tela. Essa etapa é onde as ideias saem do papel e começam a tomar forma, tanto visualmente quanto funcionalmente. O que cada botão vai fazer? Onde ele vai ficar disposto na tela? Como deixar a página mais intuitiva para o usuário final? Todas essas perguntas surgem a todo momento, a cada modificação feita na tela, e a cada necessidade ou ideia que surge no caminho.</ul>
                                    <ul class="mb-1"><strong>Funcionalidades:</strong> Tudo o que cada botão, link, caixa de texto, caixa de seleção, lista, etc. Deveriam fazer na página ou no banco de dados. Exemplo na página inicial o botão de produtos, que leva para a página que mostra todos os seus produtos cadastrados, quer editar o produto? Quando clicar no botão de cadastrar novo produto, você será redirecionado para a página de cadastrar produto, e nessa página, ja virá com a lista de todas as categorias cadastradas, todas as plataformas, obviamente o botão de cadastrar também... Tudo isso são funções dentro da página, o que torna ela funcional de fato.</ul>
                                <li><strong>Conhecimentos Individuais:</strong> Cada um de nós tem mais afinidade com determinado assunto ou tema, com isso em mente, separamos nossos esforços onde cada um tem mais conhecimento do assunto. Seja parte visual (Front-End), ou na parte funcional (Back-End), pois assim cada parte do projeto flui melhor, cada um no seu assunto dominante, sabendo onde está pisando, onde está mexendo, o que está acessando, etc. Isso faz o projeto caminhar muito mais rápido, onde cada um fazendo uma etapa em que domina o assunto, ninguém fica preso numa etapa desconhecida, ou que não domina e por assim atrapalhando o projeto todo.</li>
                                <li><strong>Divisão:</strong> A divisão ocorreu naturalmente, Lucas foi para a parte do Front-End na parte visual, Samuel e Renato ficaram na parte do Back-End, nas funcionalidades das páginas.</li>
                            </ul>
                        </div>

                        <div class="mb-4">
                            <h3 class="h5 text-dark fw-bold">4. Finalização</h3>
                            <p class="text-secondary">
                                Este projeto foi apenas uma porta de entrada para nosso projeto final do curso. Isso foi só uma demonstração do nosso talento e das nossas habilidade no assunto.
                            </p>
                        </div>
                        
                        <div class="text-end text-muted small border-top pt-3">
                            Última atualização: <?= date('d/m/Y') ?>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

</body>

</html>