-- ==============================================================================
-- GEMGAME MULTIPLAYER (KAHOOT / QUIZIZZ MODE) — MIGRATION
-- Projeto: Sketchbook Educacional | tccdb
-- Compatível com MySQL 8.x / MariaDB | utf8mb4 | Linux case-sensitive
-- ==============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------------------------
-- 1. TABELA: gemgame_sala (Salas síncronas gerenciadas pelo Host)
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `gemgame_sala_resposta`;
DROP TABLE IF EXISTS `gemgame_sala_jogador`;
DROP TABLE IF EXISTS `gemgame_sala`;

CREATE TABLE `gemgame_sala` (
  `id`                    INT          NOT NULL AUTO_INCREMENT,
  `pin`                   VARCHAR(6)   NOT NULL,
  `quiz_id`              INT          NOT NULL,
  `host_id`              INT          NOT NULL,
  `estado`               ENUM('aguardando', 'pergunta', 'resultado_pergunta', 'ranking', 'finalizado') NOT NULL DEFAULT 'aguardando',
  `pergunta_atual_indice` INT          NOT NULL DEFAULT 0,
  `pergunta_atual_id`    INT          DEFAULT NULL,
  `tempo_inicio_pergunta` BIGINT       DEFAULT NULL,
  `duracao_pergunta`     INT          NOT NULL DEFAULT 25,
  `criado_em`             DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em`        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_sala_pin` (`pin`),
  KEY `fk_sala_quiz` (`quiz_id`),
  KEY `fk_sala_host` (`host_id`),
  CONSTRAINT `fk_sala_quiz` FOREIGN KEY (`quiz_id`) REFERENCES `gemgame_quiz` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_sala_host` FOREIGN KEY (`host_id`) REFERENCES `usuario` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 2. TABELA: gemgame_sala_jogador (Alunos conectados na sala do Host)
-- ------------------------------------------------------------------------------
CREATE TABLE `gemgame_sala_jogador` (
  `id`          INT          NOT NULL AUTO_INCREMENT,
  `sala_id`     INT          NOT NULL,
  `usuario_id`  INT          DEFAULT NULL,
  `nickname`    VARCHAR(100) NOT NULL,
  `pontuacao`   INT          NOT NULL DEFAULT 0,
  `acertos`     INT          NOT NULL DEFAULT 0,
  `ultimo_ping` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `criado_em`   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_jogador_sala` (`sala_id`),
  KEY `fk_jogador_usuario` (`usuario_id`),
  CONSTRAINT `fk_jogador_sala` FOREIGN KEY (`sala_id`) REFERENCES `gemgame_sala` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_jogador_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 3. TABELA: gemgame_sala_resposta (Respostas dos alunos por pergunta da sala)
-- ------------------------------------------------------------------------------
CREATE TABLE `gemgame_sala_resposta` (
  `id`                 INT      NOT NULL AUTO_INCREMENT,
  `sala_id`            INT      NOT NULL,
  `pergunta_id`        INT      NOT NULL,
  `jogador_id`         INT      NOT NULL,
  `resposta_id`        INT      NOT NULL,
  `correta`            TINYINT(1) NOT NULL DEFAULT 0,
  `tempo_resposta_ms`  INT      NOT NULL DEFAULT 0,
  `pontos_ganhos`      INT      NOT NULL DEFAULT 0,
  `respondido_em`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_jogador_pergunta` (`sala_id`, `pergunta_id`, `jogador_id`),
  KEY `fk_resp_sala` (`sala_id`),
  KEY `fk_resp_pergunta` (`pergunta_id`),
  KEY `fk_resp_jogador` (`jogador_id`),
  KEY `fk_resp_resposta` (`resposta_id`),
  CONSTRAINT `fk_resp_sala` FOREIGN KEY (`sala_id`) REFERENCES `gemgame_sala` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_resp_pergunta` FOREIGN KEY (`pergunta_id`) REFERENCES `gemgame_pergunta` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_resp_jogador` FOREIGN KEY (`jogador_id`) REFERENCES `gemgame_sala_jogador` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_resp_resposta` FOREIGN KEY (`resposta_id`) REFERENCES `gemgame_resposta` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
