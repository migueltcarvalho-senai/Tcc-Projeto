-- ============================================================
-- MIGRAÇÃO FINAL: Jogo de Inglês — Gap Fill Quiz
-- Schema: tccdb
-- Executar no phpMyAdmin ou terminal MySQL
-- ============================================================

CREATE DATABASE IF NOT EXISTS `tccdb`
    DEFAULT CHARACTER SET utf8mb4
    COLLATE utf8mb4_0900_ai_ci;

USE `tccdb`;

-- --------------------------------------------------------
-- Tabela: Jogo
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `Jogo` (
  `id`       INT          NOT NULL AUTO_INCREMENT,
  `nome`     INT          NOT NULL,
  `descricao` VARCHAR(255) NULL,
  PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------
-- Tabela: Pergunta
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `Pergunta` (
  `id`           INT          NOT NULL AUTO_INCREMENT,
  `primeira parte` VARCHAR(255) NULL,
  `resposta`     VARCHAR(100) NOT NULL,
  `segunda parte` VARCHAR(255) NULL,
  `Jogo_id`      INT          NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_Pergunta_Jogo1`
    FOREIGN KEY (`Jogo_id`) REFERENCES `Jogo` (`id`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------
-- Tabela: Conjunto de respostas erradas
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `Conjunto de respostas erradas` (
  `id`               INT          NOT NULL AUTO_INCREMENT,
  `nome do conjunto` VARCHAR(255) NOT NULL,
  `conjunto`         JSON         NOT NULL,
  `Pergunta_id`      INT          NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_Conjunto_Pergunta1`
    FOREIGN KEY (`Pergunta_id`) REFERENCES `Pergunta` (`id`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ============================================================
-- DADOS: Jogo 1 — Complete the Sentence (Farm Animals)
-- ============================================================

INSERT INTO `Jogo` (`nome`, `descricao`) VALUES
(1, 'Complete the Sentence - English for Kids');

INSERT INTO `Pergunta` (`id`, `primeira parte`, `resposta`, `segunda parte`, `Jogo_id`) VALUES
(1, 'I have a',             'golden fish', 'at my aquarium.',                    1),
(2, 'My grandpa''s',        'cow',         'always goes MOO MOO.',               1),
(3, 'The',                  'Chicken',     'always goes CÓÓ CÓÓ at the farm.',   1),
(4, 'My uncle will give me a strong black', 'horse', '.',                        1),
(5, 'We will ride a',       'horse',       'together!',                          1),
(6, 'The fluffy long-eared', 'rabbit', 'loves eating fresh carrots.', 1),
(7, 'The ferocious male', 'lion', 'with a big mane roars in the savanna.', 1),
(8, 'The long-necked', 'giraffe', 'eats leaves from the highest tree branches.', 1),
(9, 'The tiny squeaking', 'mouse', 'nibbles on a small piece of cheese.', 1),
(10, 'My friendly pet', 'dog', 'barks happily and wags its tail.', 1),
(11, 'The agile', 'monkey', 'swings between branches and peels bananas.', 1),
(12, 'The massive gray', 'elephant', 'uses its long trunk to spray water.', 1),
(13, 'The black and white striped', 'zebra', 'grazes on the savanna grass.', 1),
(14, 'The purring', 'cat', 'drinks milk and chases laser lights.', 1),
(15, 'The slow-moving', 'turtle', 'retracts its head into its hard shell.', 1),
(16, 'The wild gray', 'wolf', 'howls at the full moon with its pack.', 1),
(17, 'The colorful feathered', 'parrot', 'mimics human speech and words.', 1),
(18, 'The furry brown', 'bear', 'catches salmon and hibernates in a cave.', 1),
(19, 'The intelligent aquatic', 'dolphin', 'leaps high out of the ocean water.', 1),
(20, 'The spotted', 'cheetah', 'is the fastest land animal on Earth.', 1);

INSERT INTO `Conjunto de respostas erradas` (`id`, `nome do conjunto`, `conjunto`, `Pergunta_id`) VALUES
(1, 'Animais comuns', '["horse","cow","dog","rabbit"]',        1),
(2, 'Animais comuns', '["dog","cat","chair","tree"]',          2),
(3, 'Animais comuns', '["bulldog","squirrel","apple","otter"]',3),
(4, 'Animais comuns', '["chair","door","tree","ground"]',      4),
(5, 'Animais comuns', '["dog","bear","seal","soup"]',          5);
(6, 'Animais comuns', '["rabbit","keyboard","refrigerator","brick"]', 6),
(7, 'Animais comuns', '["airplane","lion","bathtub","stapler"]', 7),
(8, 'Animais comuns', '["toaster","hammer","giraffe","pillow"]', 8),
(9, 'Animais comuns', '["mouse","umbrella","flashlight","microwave"]', 9),
(10, 'Animais comuns', '["calculator","curtain","dog","screwdriver"]', 10),
(11, 'Animais comuns', '["suitcase","monkey","mirror","bicycle"]', 11),
(12, 'Animais comuns', '["elephant","bookshelf","television","wallet"]', 12),
(13, 'Animais comuns', '["laptop","zebra","armchair","frying pan"]', 13),
(14, 'Animais comuns', '["cat","dishwasher","carpet","traffic light"]', 14),
(15, 'Animais comuns', '["scissors","turtle","headphone","stapler"]', 15),
(16, 'Animais comuns', '["wolf","telescope","sofa","doorbell"]', 16),
(17, 'Animais comuns', '["skateboard","parrot","lawnmower","notebook"]', 17),
(18, 'Animais comuns', '["chandelier","bear","pencil","mattress"]', 18),
(19, 'Animais comuns', '["dolphin","radiator","typewriter","broom"]', 19),
(20, 'Animais comuns', '["microwave","cheetah","printer","bookshelf"]', 20);
