-- ============================================================
-- MIGRAÇÃO: Jogo de Inglês — Gap Fill Quiz
-- Schema: tccdb
-- Executar no phpMyAdmin ou terminal MySQL
-- ============================================================

CREATE DATABASE IF NOT EXISTS `tccdb`
    DEFAULT CHARACTER SET utf8mb4
    COLLATE utf8mb4_0900_ai_ci;

USE `tccdb`;

-- --------------------------------------------------------
-- Tabela: Jogo (Catálogo)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `Jogo` (
  `id`       INT          NOT NULL AUTO_INCREMENT,
  `nome`     VARCHAR(255) NOT NULL,
  `descricao` VARCHAR(255) NULL,
  PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------
-- Tabela: Tema
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `Tema` (
  `id` INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
  `id_jogo` INT,
  `nome` varchar(255) NOT NULL,
  FOREIGN KEY (`id_jogo`) REFERENCES `Jogo` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------
-- Tabela: Pergunta (Jogo 1)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `Pergunta` (
  `id`           INT          NOT NULL AUTO_INCREMENT,
  `primeira parte` VARCHAR(255) NULL,
  `resposta`     VARCHAR(100) NOT NULL,
  `segunda parte` VARCHAR(255) NULL,
  `id_tema`      INT          NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_Pergunta_Jogo1`
    FOREIGN KEY (`id_tema`) REFERENCES `Tema` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------
-- Tabela: Conjunto de respostas erradas (Jogo 1)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `Conjunto de respostas erradas` (
  `id`               INT          NOT NULL AUTO_INCREMENT,
  `nome do conjunto` VARCHAR(255) NOT NULL,
  `conjunto`         JSON         NOT NULL,
  `Pergunta_id`      INT          NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_Conjunto_Pergunta1`
    FOREIGN KEY (`Pergunta_id`) REFERENCES `Pergunta` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------
-- Tabela: Frases (Jogo 2)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `Frases` (
  `id`      INT          NOT NULL AUTO_INCREMENT,
  `frase`   VARCHAR(255) NOT NULL,
  `id_tema` INT          NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_Frases_Tema`
    FOREIGN KEY (`id_tema`) REFERENCES `Tema` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ============================================================
-- Catálogo de Jogos (Sem temas ou frases automáticas)
-- ============================================================
INSERT INTO `Jogo` (`id`, `nome`, `descricao`) VALUES
(1, 'complete sentence', 'Complete the Sentence - English for Kids'),
(2, 'ballons', 'Estoure os Balões - Build the Sentence')
ON DUPLICATE KEY UPDATE `nome` = VALUES(`nome`), `descricao` = VALUES(`descricao`);

-- ============================================================
-- NOTA:
-- Inserções automáticas de temas e frases foram removidas.
-- Eles serão criados manualmente pelo administrador.
-- ============================================================
