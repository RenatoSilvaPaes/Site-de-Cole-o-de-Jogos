# Site de Coleção de Jogos
Projeto Final do Módulo de PHP do Senac Vila Prudente, criado por:
Renato Silva Paes,
Lucas Gabriel Moreira Stoppa,
e Samuel Lucas Alvez dos Santos.

O objetivo deste projteo é: criar um site o qual um usuário pode fazer o registro de sua coleção de jogos digitais, seja em versão digital e/ou física. Cada detalhe na hora de cadastro do produto foi pensado de forma bem delicada. A seguir, segue as orientações de cada página do nosso site, além dos arquivos do projteo, os quais não há como visualizar no navegador.

index.php -> Página Inicial do Site. Antes de poder acessar qualquer outra página, o usuário precisa passar por ela. Aqui se encontra como o usuário irá fazer login e/ou criar uma nova conta. Caso o usuário isira dados incorretos e/ou dados não encontrados no banco de dados, o sistema impede que ele prossiga.

registrar.php -> Página da cadastro de novos usuários. Quase todos os campos são obrigatórios, com excessão da foto de perfil, a qual é opcional. Ele verifica se o email e CPF digitados pelo usuário não estão cadastrados no banco de dados do sistema. Caso ele ache, não é permitido o cadastro desses dados duplicados. Além disso, há uma conexão com API dos correios, permitindo que, quando o usuário digiteum CEP válido, os campos de "Rua", "Cidade" e "Estado" são preenchidos automáticamente! Caso o usuário faça upload de uma imagem (com o tamanho e formato permitidos, caso contrário o sistema barra o cadastro), essa imagem é baixada para o servidor, na pasta 'Imagens/Users', com um nome único para cada imagem.

home.php -> Página Principal do Site. Nela, são encontradas a maioria das páginas do projeto. Ela carrega a foto de perfil escolhida pelo usuário (caso ele não tenho escolhido uma foto, aparece um emoji de usuário padrão), além de dar a opção do usuário fazer um backup do banco de dados. No cabeçalho, é possíevl ver os seguintes botões: backup (explicado anteriormente); Dashboard, que faz um cálculo dos produtos mais caros e valorização da coleção; Adicionar Produto, o qual permite o usuário adicionar novos jogos a sua coleção (será explicado mais para frente no arquivo); e Sair, permitindo que o usuário faça logout do sistema e que outro tente entrer e/ou fazer um novo cadastro. Abaixo da foto, há 3 cards que dão acesso: ao acervo/coleção do usuário (este que pode ficar no formato de lista ou grade); ao perfil do usuário, podem modificar dados e/ou excluir sua conta; e adicionar não só novos produtos, mas também categorias e plataformas ao banco de dados.
