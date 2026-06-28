CREATE DATABASE colecionaveis;

USE colecionaveis;

CREATE TABLE `usuarios` (

  `id` int(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,

  `nome` varchar(50) NOT NULL,

  `sobrenome` varchar(50) NOT NULL,

  `cpf` varchar(15) NOT NULL,

  `email` varchar(60) NOT NULL,

  `cep` varchar(8) NOT NULL,

  `endereco` varchar(50) NOT NULL,

  `cidade` varchar(20) NOT NULL,

  `senha` varchar(255) NOT NULL,

  `foto` varchar(50) DEFAULT NULL

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

 

CREATE TABLE `produtos` (

  `id` int(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,

  `nome` varchar(60) NOT NULL,

  `descricao` varchar(140) NOT NULL,

  `quantidade` int(5) NOT NULL,

  `preco` decimal(10,2) NOT NULL,

  `dataCad` date NOT NULL,

  `user_id` int(10) NOT NULL,

  `foto` varchar(50) NOT NULL,

  `categoria` INT(10) NOT NULL,

  CONSTRAINT `fk_id_user` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,

  CONSTRAINT `fk_id_categoria` FOREIGN KEY (`categoria`) REFERENCES `categorias` (`id`) ON DELETE CASCADE ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

 

CREATE TABLE `plataformas` (

  `id` INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,

  `nome` VARCHAR(20) NOT NULL UNIQUE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



CREATE TABLE `categorias` (

  `id` INT (10) NOT NULL AUTO_INCREMENT PRIMARY KEY,

  `nome` VARCHAR(20) NOT NULL UNIQUE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



INSERT INTO `categorias` (`nome`) VALUES

('FPS'),

('TPS'),

('Plataforma'),

('Ação'),

('Aventura'),

('Hack/Slash'),

('RPG'),

('Terror'),

('MOBA'),

('Corrida');



-- Popula a tabela de materiais com registros iniciais

INSERT INTO `plataformas` (`nome`) VALUES

('Playstation'),

('Xbox'),

('Nintendo'),

('PC'),

('Mobile');



CREATE TABLE `jogoPlataforma` (

  `id_jogo` INT(10) NOT NULL,

  `id_plataforma` INT(10) NOT NULL,

  CONSTRAINT `pk_id_jogo_plataforma` PRIMARY KEY (`id_jogo`, `id_plataforma`),

  CONSTRAINT `fk_id_jogo` FOREIGN KEY (`id_jogo`) REFERENCES `produtos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,

  CONSTRAINT `fk_id_plataforma` FOREIGN KEY (`id_plataforma`) REFERENCES `plataformas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
 


-- Usuário e Privilégios

CREATE USER IF NOT EXISTS 'renato'@'localhost' IDENTIFIED BY '123';

GRANT ALL PRIVILEGES ON `colecionaveis`.* TO 'renato'@'localhost';

FLUSH PRIVILEGES;