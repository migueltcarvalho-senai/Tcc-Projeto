-- ==============================================================================
-- GEMGAME — Migração do Sistema de Quizzes
-- Projeto: Sketchbook Educacional | tccdb
-- Compatível com MySQL 8.x / MariaDB | utf8mb4
-- ==============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------------------------
-- 1. TABELA: gemgame_quiz (Catálogo de Quizzes)
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `gemgame_quiz`;
CREATE TABLE `gemgame_quiz` (
  `id`         INT          NOT NULL AUTO_INCREMENT,
  `titulo`     VARCHAR(255) NOT NULL,
  `descricao`  VARCHAR(500) DEFAULT NULL,
  `criado_em`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 2. TABELA: gemgame_pergunta (Perguntas de cada Quiz)
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `gemgame_pergunta`;
CREATE TABLE `gemgame_pergunta` (
  `id`        INT  NOT NULL AUTO_INCREMENT,
  `quiz_id`   INT  NOT NULL,
  `enunciado` TEXT NOT NULL,
  `imagem`    VARCHAR(500) DEFAULT NULL,   -- caminho relativo do arquivo enviado (upload)
  `ordem`     INT  NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `fk_pergunta_quiz` (`quiz_id`),
  CONSTRAINT `fk_pergunta_quiz`
    FOREIGN KEY (`quiz_id`) REFERENCES `gemgame_quiz` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 3. TABELA: gemgame_resposta (Respostas — 1 certa + 3 ou mais erradas)
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `gemgame_resposta`;
CREATE TABLE `gemgame_resposta` (
  `id`          INT          NOT NULL AUTO_INCREMENT,
  `pergunta_id` INT          NOT NULL,
  `texto`       VARCHAR(500) NOT NULL,
  `correta`     TINYINT(1)   NOT NULL DEFAULT 0,   -- 1 = correta | 0 = errada
  PRIMARY KEY (`id`),
  KEY `fk_resposta_pergunta` (`pergunta_id`),
  CONSTRAINT `fk_resposta_pergunta`
    FOREIGN KEY (`pergunta_id`) REFERENCES `gemgame_pergunta` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 4. TABELA: gemgame_partida (Histórico de Partidas Jogadas)
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `gemgame_partida`;
CREATE TABLE `gemgame_partida` (
  `id`                INT      NOT NULL AUTO_INCREMENT,
  `quiz_id`           INT      NOT NULL,
  `jogador_id`        INT      NOT NULL,
  `acertos`           INT      NOT NULL DEFAULT 0,
  `total_perguntas`   INT      NOT NULL DEFAULT 0,
  `pontuacao_partida` INT      NOT NULL DEFAULT 0,
  `jogado_em`         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_partida_quiz`    (`quiz_id`),
  KEY `fk_partida_jogador` (`jogador_id`),
  CONSTRAINT `fk_partida_quiz`
    FOREIGN KEY (`quiz_id`)    REFERENCES `gemgame_quiz` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_partida_jogador`
    FOREIGN KEY (`jogador_id`) REFERENCES `usuario`      (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ==============================================================================
-- FIM DA MIGRAÇÃO GEMGAME
-- ==============================================================================
