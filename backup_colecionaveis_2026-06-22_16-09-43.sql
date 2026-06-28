-- Backup gerado automaticamente via Sistema
-- Data: 22/06/2026 16:09:43

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `categorias`;
CREATE TABLE `categorias` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `nome` varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nome` (`nome`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `categorias` VALUES ('4', 'Ação');
INSERT INTO `categorias` VALUES ('5', 'Aventura');
INSERT INTO `categorias` VALUES ('10', 'Corrida');
INSERT INTO `categorias` VALUES ('1', 'FPS');
INSERT INTO `categorias` VALUES ('6', 'Hack/Slash');
INSERT INTO `categorias` VALUES ('9', 'MOBA');
INSERT INTO `categorias` VALUES ('3', 'Plataforma');
INSERT INTO `categorias` VALUES ('7', 'RPG');
INSERT INTO `categorias` VALUES ('12', 'Simulação');
INSERT INTO `categorias` VALUES ('11', 'Souslike');
INSERT INTO `categorias` VALUES ('8', 'Terror');
INSERT INTO `categorias` VALUES ('2', 'TPS');

DROP TABLE IF EXISTS `jogoplataforma`;
CREATE TABLE `jogoplataforma` (
  `id_jogo` int(10) NOT NULL,
  `id_plataforma` int(10) NOT NULL,
  PRIMARY KEY (`id_jogo`,`id_plataforma`),
  KEY `fk_id_plataforma` (`id_plataforma`),
  CONSTRAINT `fk_id_jogo` FOREIGN KEY (`id_jogo`) REFERENCES `produtos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_id_plataforma` FOREIGN KEY (`id_plataforma`) REFERENCES `plataformas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `jogoplataforma` VALUES ('9', '3');
INSERT INTO `jogoplataforma` VALUES ('9', '4');
INSERT INTO `jogoplataforma` VALUES ('10', '1');
INSERT INTO `jogoplataforma` VALUES ('10', '2');
INSERT INTO `jogoplataforma` VALUES ('10', '3');
INSERT INTO `jogoplataforma` VALUES ('10', '4');
INSERT INTO `jogoplataforma` VALUES ('17', '4');
INSERT INTO `jogoplataforma` VALUES ('18', '1');
INSERT INTO `jogoplataforma` VALUES ('18', '2');
INSERT INTO `jogoplataforma` VALUES ('18', '3');
INSERT INTO `jogoplataforma` VALUES ('19', '1');
INSERT INTO `jogoplataforma` VALUES ('19', '2');
INSERT INTO `jogoplataforma` VALUES ('19', '4');
INSERT INTO `jogoplataforma` VALUES ('20', '1');
INSERT INTO `jogoplataforma` VALUES ('20', '2');
INSERT INTO `jogoplataforma` VALUES ('20', '3');
INSERT INTO `jogoplataforma` VALUES ('20', '4');
INSERT INTO `jogoplataforma` VALUES ('21', '1');
INSERT INTO `jogoplataforma` VALUES ('21', '2');
INSERT INTO `jogoplataforma` VALUES ('21', '3');
INSERT INTO `jogoplataforma` VALUES ('21', '4');
INSERT INTO `jogoplataforma` VALUES ('22', '1');
INSERT INTO `jogoplataforma` VALUES ('22', '2');
INSERT INTO `jogoplataforma` VALUES ('22', '3');
INSERT INTO `jogoplataforma` VALUES ('22', '4');
INSERT INTO `jogoplataforma` VALUES ('23', '1');
INSERT INTO `jogoplataforma` VALUES ('23', '2');
INSERT INTO `jogoplataforma` VALUES ('23', '3');
INSERT INTO `jogoplataforma` VALUES ('23', '4');
INSERT INTO `jogoplataforma` VALUES ('24', '1');
INSERT INTO `jogoplataforma` VALUES ('24', '2');
INSERT INTO `jogoplataforma` VALUES ('24', '3');
INSERT INTO `jogoplataforma` VALUES ('24', '4');
INSERT INTO `jogoplataforma` VALUES ('25', '1');
INSERT INTO `jogoplataforma` VALUES ('25', '2');
INSERT INTO `jogoplataforma` VALUES ('25', '3');
INSERT INTO `jogoplataforma` VALUES ('25', '4');
INSERT INTO `jogoplataforma` VALUES ('26', '1');
INSERT INTO `jogoplataforma` VALUES ('26', '2');
INSERT INTO `jogoplataforma` VALUES ('26', '3');
INSERT INTO `jogoplataforma` VALUES ('26', '4');
INSERT INTO `jogoplataforma` VALUES ('27', '1');
INSERT INTO `jogoplataforma` VALUES ('27', '2');
INSERT INTO `jogoplataforma` VALUES ('27', '4');

DROP TABLE IF EXISTS `plataformas`;
CREATE TABLE `plataformas` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `nome` varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nome` (`nome`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `plataformas` VALUES ('6', 'Google Stadia');
INSERT INTO `plataformas` VALUES ('5', 'Mobile');
INSERT INTO `plataformas` VALUES ('3', 'Nintendo');
INSERT INTO `plataformas` VALUES ('4', 'PC');
INSERT INTO `plataformas` VALUES ('1', 'Playstation');
INSERT INTO `plataformas` VALUES ('2', 'Xbox');

DROP TABLE IF EXISTS `produtos`;
CREATE TABLE `produtos` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `nome` varchar(60) NOT NULL,
  `descricao` varchar(140) NOT NULL,
  `quantidade` int(5) NOT NULL,
  `preco` decimal(10,2) NOT NULL,
  `dataCad` date NOT NULL,
  `dataLan` date NOT NULL,
  `user_id` int(10) NOT NULL,
  `foto` varchar(100) NOT NULL,
  `categoria` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_id_user` (`user_id`),
  KEY `fk_id_categoria` (`categoria`),
  CONSTRAINT `fk_id_categoria` FOREIGN KEY (`categoria`) REFERENCES `categorias` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_id_user` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `produtos` VALUES ('9', 'Pizza Tower', 'Pizza Tower é um jogo de plataforma 2D de ritmo acelerado, com ênfase no movimento, na exploração e na luta por pontos.', '1', '36.99', '2026-06-18', '2023-01-26', '5', 'Imagens/Produtos/UserID5/produtos_6a33ece69d4289.46972759.jpg', '3');
INSERT INTO `produtos` VALUES ('10', 'Crash Bandicoot N.Sane Trilogy', 'Seu marsupial favorito, Crash Bandicoot™, está de volta! Ele está aprimorado, inspirado e preparado para dançar na coleção de jogos.', '3', '219.90', '2026-06-19', '2017-06-30', '5', 'Imagens/Produtos/UserID5/produtos_6a352e54a2a123.72015031.jpg', '3');
INSERT INTO `produtos` VALUES ('17', 'Supra Mayro Kratt', 'this is a game nintendo made but never showd to public', '1', '0.01', '2013-06-13', '2026-06-22', '5', 'Imagens/Produtos/UserID5/produtos_6a39340343b540.22299897.jpg', '10');
INSERT INTO `produtos` VALUES ('18', 'Crash Team Racing Nitro-Fueled', 'rash está de volta ao volante!\r\n\r\nVocê terá a autêntica experiência do Crash Team Racing e muito mais, agora totalmente remasterizado.', '2', '229.90', '2026-06-10', '2019-06-20', '5', 'Imagens/Produtos/UserID5/produtos_6a39351f34b284.05609601.jpg', '10');
INSERT INTO `produtos` VALUES ('19', 'Killing Floor 3', 'O ano é 2091. Junte-se à Nightfall, a última linha de defesa contra o exército inumano de zeds monstruosos da megacorporação Horzine.', '1', '107.99', '2026-06-22', '2025-07-24', '5', 'Imagens/Produtos/UserID5/produtos_6a3935686564e2.53047680.jpg', '1');
INSERT INTO `produtos` VALUES ('20', 'Crash Bandicoot 4: It\'s About Time', 'Já era hora - Crash Bandicoot™ 4: It\'s About Time, sucesso entre críticos, agora está na Steam! Embarque em uma aventura atemporal.', '1', '249.90', '2026-06-16', '2020-10-02', '5', 'Imagens/Produtos/UserID5/produtos_6a3936156e2287.22509708.jpg', '3');
INSERT INTO `produtos` VALUES ('21', 'Megaman 11', 'Mega Man está de volta à ação! A mais nova entrada na série que vendeu milhões mistura a clássica desafiadora ação de plataforma 2D.', '2', '129.00', '2026-06-02', '2018-10-02', '5', 'Imagens/Produtos/UserID5/produtos_6a3936de03e444.94128006.jpg', '3');
INSERT INTO `produtos` VALUES ('22', 'Mighty No. 9', 'Mighty No. 9 é um jogo japonês 2D de ação em plataforma que une os melhores elementos dos aclamados clássicos de 8 e 16-bit.', '1', '99.00', '2026-06-16', '2021-06-21', '5', 'Imagens/Produtos/UserID5/produtos_6a3937c75982d8.62284114.jpg', '3');
INSERT INTO `produtos` VALUES ('23', 'Mega Man X Legacy Collection', 'Mega Man X Legacy Collection vem com um arsenal de novidades.', '1', '89.00', '2026-06-11', '2018-07-24', '5', 'Imagens/Produtos/UserID5/produtos_6a39388922ed59.05764787.jpg', '3');
INSERT INTO `produtos` VALUES ('24', 'Mega Man X Legacy Collection 2', 'Mega Man X Legacy Collection 2 também inclui um arsenal de novidades.', '1', '89.00', '2026-06-17', '2018-07-24', '5', 'Imagens/Produtos/UserID5/produtos_6a39390a67b753.70097252.jpg', '3');
INSERT INTO `produtos` VALUES ('25', 'Resident Evil 4', 'O agente Leon S. Kennedy, um dos sobreviventes do incidente, foi enviado para resgatar a filha raptada do presidente.', '1', '169.00', '2026-06-03', '2023-03-23', '5', 'Imagens/Produtos/UserID5/produtos_6a39397843e0b4.43839002.png', '8');
INSERT INTO `produtos` VALUES ('26', 'Resident Evil Village', 'Vivencie o horror de sobrevivência como nunca na 8ª grande sequência da franquia Resident Evil - Resident Evil Village.', '1', '169.00', '2026-06-03', '2021-05-07', '5', 'Imagens/Produtos/UserID5/produtos_6a3939bde7fce0.15078233.png', '8');
INSERT INTO `produtos` VALUES ('27', 'Resident Evil Requiem', 'Uma nova era do survival horror chega com Resident Evil Requiem, o capítulo mais recente e imersivo da icônica série Resident Evil.', '1', '299.00', '2026-06-03', '2026-02-27', '5', 'Imagens/Produtos/UserID5/produtos_6a393a1741fff1.57895522.jpg', '8');

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE `usuarios` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `nome` varchar(50) NOT NULL,
  `sobrenome` varchar(50) NOT NULL,
  `cpf` varchar(15) NOT NULL,
  `email` varchar(60) NOT NULL,
  `cep` varchar(8) NOT NULL,
  `endereco` varchar(50) NOT NULL,
  `cidade` varchar(20) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `foto` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `usuarios` VALUES ('5', 'Renato', 'Paes', '442.250.778-88', 'paes.nato@gmail.com', '04359070', 'Rua Madre Angelina, 30', 'São Paulo', '$2y$10$RHhKeWRxemgoAmYXXyiCeu7iISK3BcImWjrQPKImr8h71gqvyYMmG', 'Imagens/Users/userID_56a300f301f0d77.07981496.gif');

SET FOREIGN_KEY_CHECKS=1;
