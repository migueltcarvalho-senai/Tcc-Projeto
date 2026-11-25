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

CREATE TABLE IF NOT EXISTS `usuario`(
  id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
  nome INT NOT NULL,
  senha INT NOT NULL,
  email INT NOT NULL
) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `Jogo` (
  `id`       INT          NOT NULL AUTO_INCREMENT,
  `nome`     VARCHAR(255)          NOT NULL,
  `descricao` VARCHAR(255) NULL,
  PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


CREATE TABLE IF NOT EXISTS Tema (
id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
id_jogo INT,
nome varchar(255) NOT NULL,
FOREIGN KEY (id_jogo) REFERENCES jogo (id)
)ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
-- --------------------------------------------------------
-- Tabela: Pergunta
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `Pergunta` (
  `id`           INT          NOT NULL AUTO_INCREMENT,
  `primeira parte` VARCHAR(255) NULL,
  `resposta`     VARCHAR(100) NOT NULL,
  `segunda parte` VARCHAR(255) NULL,
  `tema_id`      INT          NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_Pergunta_Jogo1`
    FOREIGN KEY (`tema_id`) REFERENCES `tema` (`id`)
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
('complete sentence', 'Complete the Sentence - English for Kids');
('construct the sentence', 'Pop the ballons and construct the sentence');

INSERT INTO `Tema` (`id`, `id_jogo`, `nome`) VALUES
(1, 1, 'animals'),
(4, 1, 'school'),
(5, 1, 'family'),
(6, 1, 'sports')
ON DUPLICATE KEY UPDATE `nome` = VALUES(`nome`);

INSERT INTO `Pergunta` (`id`, `primeira parte`, `resposta`, `segunda parte`, `tema_id`) VALUES
(1, 'I have a',             'golden fish', 'at my aquarium.',                    1),
(2, 'My grandpa''s',        'cow',         'always does MOO MOO.',               1),
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
(5, 'Animais comuns', '["dog","bear","seal","soup"]',          5),
(6, 'Animais comuns', '["keyboard","refrigerator","brick"]', 6),
(7, 'Animais comuns', '["airplane","bathtub","stapler"]', 7),
(8, 'Animais comuns', '["toaster","hammer","pillow"]', 8),
(9, 'Animais comuns', '["umbrella","flashlight","microwave"]', 9),
(10, 'Animais comuns', '["calculator","curtain","screwdriver"]', 10),
(11, 'Animais comuns', '["suitcase","mirror","bicycle"]', 11),
(12, 'Animais comuns', '["bookshelf","television","wallet"]', 12),
(13, 'Animais comuns', '["laptop","armchair","frying pan"]', 13),
(14, 'Animais comuns', '["dishwasher","carpet","traffic light"]', 14),
(15, 'Animais comuns', '["scissors","headphone","stapler"]', 15),
(16, 'Animais comuns', '["telescope","sofa","doorbell"]', 16),
(17, 'Animais comuns', '["skateboard","lawnmower","notebook"]', 17),
(18, 'Animais comuns', '["chandelier","pencil","mattress"]', 18),
(19, 'Animais comuns', '["radiator","typewriter","broom"]', 19),
(20, 'Animais comuns', '["microwave","printer","bookshelf"]', 20);


INSERT INTO `Pergunta` (`id`, `primeira parte`, `resposta`, `segunda parte`, `tema_id`) VALUES
(21, 'In soccer, you use your ', 'feet', ' to kick the ball.', 6),
(22, 'You throw the ball into a hoop when you play ', 'basketball', '.', 6),
(23, 'To practice swimming, you need a ', 'pool', ' with water.', 6),
(24, 'In tennis, you hit the ball with a ', 'racket', '.', 6),
(25, 'You ride a ', 'bike', ' when you go cycling.', 6),
(26, 'In volleyball, you use your ', 'hands', ' to hit the ball over the net.', 6),
(27, 'When you play baseball, you use a ', 'bat', ' to hit the small ball.', 6),
(28, 'In a marathon, people ', 'run', ' for a very long time.', 6),
(29, 'You wear gloves to fight in a ', 'boxing', ' match.', 6),
(30, 'To play golf, you need to put the small ball in a ', 'hole', '.', 6),
(31, 'In table tennis, the ball bounces on a ', 'table', '.', 6),
(32, 'You need a board to surf on the ', 'waves', ' in the ocean.', 6),
(33, 'A team in soccer has eleven ', 'players', ' on the field.', 6),
(34, 'In gymnastics, athletes love to ', 'jump', ' and stretch.', 6),
(35, 'You need snow to go ', 'skiing', ' in the mountains.', 6),
(36, 'A person who catches the ball in soccer is the ', 'goalkeeper', '.', 6),
(37, 'In judo, you wear a special white or blue ', 'belt', ' around your waist.', 6),
(38, 'A place where you play soccer is called a ', 'field', '.', 6),
(39, 'To play ice hockey, you must slide on the ', 'ice', '.', 6),
(40, 'When you win the game, you get a gold ', 'medal', '!', 6),
(41, 'I use a ', 'pencil', ' to write in my notebook.', 4),
(42, 'The ', 'teacher', ' writes on the board and helps us learn.', 4),
(43, 'We read stories from a ', 'book', ' in the library.', 4),
(44, 'I carry my school things in my ', 'backpack', '.', 4),
(45, 'We sit on a ', 'chair', ' at our desk during class.', 4),
(46, 'You use an ', 'eraser', ' to fix mistakes when you draw.', 4),
(47, 'During break time, we play with our ', 'friends', ' in the yard.', 4),
(48, 'We cut paper using a pair of ', 'scissors', ' in art class.', 4),
(49, 'To draw straight lines, I always use a ', 'ruler', '.', 4),
(50, 'We eat our sandwiches in the ', 'cafeteria', ' at lunch.', 4),
(51, 'I need a blue ', 'pen', ' to write my name on the test.', 4),
(52, 'We learn about numbers and addition in ', 'math', ' class.', 4),
(53, 'The yellow school ', 'bus', ' takes us home every day.', 4),
(54, 'We paint pictures and mix colors in ', 'art', ' class.', 4),
(55, 'You raise your ', 'hand', ' when you want to ask a question.', 4),
(56, 'We listen to songs and sing in ', 'music', ' class.', 4),
(57, 'The ', 'bell', ' rings when it is time to go home.', 4),
(58, 'You sharpen your pencil with a ', 'sharpener', ' when it breaks.', 4),
(59, 'We write with white ', 'chalk', ' on the old green board.', 4),
(60, 'My favorite school ', 'subject', ' to study is science.', 4);

INSERT INTO `Conjunto de respostas erradas` (`id`, `nome do conjunto`, `conjunto`, `Pergunta_id`) VALUES
(21, 'sports', '["hands","head","nose","ears"]', 21),
(22, 'sports', '["swimming","running","sleeping","reading"]', 22),
(23, 'sports', '["car","bed","tree","book"]', 23),
(24, 'sports', '["pencil","shoe","spoon","hat"]', 24),
(25, 'sports', '["horse","boat","plane","train"]', 25),
(26, 'sports', '["feet","knees","eyes","teeth"]', 26),
(27, 'sports', '["book","shoe","racket","pillow"]', 27),
(28, 'sports', '["sleep","fly","sing","eat"]', 28),
(29, 'sports', '["swimming","sleeping","reading","cooking"]', 29),
(30, 'sports', '["tree","window","car","cloud"]', 30),
(31, 'sports', '["bed","chair","wall","door"]', 31),
(32, 'sports', '["street","house","desert","forest"]', 32),
(33, 'sports', '["dogs","cars","trees","apples"]', 33),
(34, 'sports', '["sleep","cry","cook","read"]', 34),
(35, 'sports', '["swimming","surfing","sleeping","shopping"]', 35),
(36, 'sports', '["teacher","doctor","singer","driver"]', 36),
(37, 'sports', '["hat","shoe","sock","glove"]', 37),
(38, 'sports', '["kitchen","bathroom","bedroom","store"]', 38),
(39, 'sports', '["fire","sand","grass","mud"]', 39),
(40, 'sports', '["apple","chair","sock","potato"]', 40),
(41, 'school', '["shoe","apple","car","dog"]', 41),
(42, 'school', '["doctor","farmer","singer","pilot"]', 42),
(43, 'school', '["shoe","tree","bicycle","plate"]', 43),
(44, 'school', '["refrigerator","window","television","bed"]', 44),
(45, 'school', '["cloud","flower","water","bird"]', 45),
(46, 'school', '["spoon","fork","apple","cat"]', 46),
(47, 'school', '["monkeys","cars","pencils","chairs"]', 47),
(48, 'school', '["cats","shoes","apples","books"]', 48),
(49, 'school', '["banana","fish","hat","sock"]', 49),
(50, 'school', '["bathroom","moon","hospital","supermarket"]', 50),
(51, 'school', '["car","dog","tree","apple"]', 51),
(52, 'school', '["sleeping","running","eating","swimming"]', 52),
(53, 'school', '["plane","boat","horse","bicycle"]', 53),
(54, 'school', '["gym","lunch","sleeping","running"]', 54),
(55, 'school', '["foot","nose","ear","knee"]', 55),
(56, 'school', '["math","science","eating","sleeping"]', 56),
(57, 'school', '["dog","cat","bird","fish"]', 57),
(58, 'school', '["pillow","blanket","towel","soap"]', 58),
(59, 'school', '["milk","water","juice","bread"]', 59),
(60, 'school', '["food","animal","toy","color"]', 60);

INSERT INTO `Pergunta` (`id`, `primeira parte`, `resposta`, `segunda parte`, `tema_id`) VALUES
(61, 'My mother''s husband is my ', 'father', '.', 5),
(62, 'The brother of my dad is my ', 'uncle', '.', 5),
(63, 'My father''s sister is my ', 'aunt', '.', 5),
(64, 'The son of my parents is my ', 'brother', '.', 5),
(65, 'The daughter of my parents is my ', 'sister', '.', 5),
(66, 'The mother of my dad is my ', 'grandmother', '.', 5),
(67, 'The father of my mom is my ', 'grandfather', '.', 5),
(68, 'The child of my aunt and uncle is my ', 'cousin', '.', 5),
(69, 'My mom and my dad together are my ', 'parents', '.', 5),
(70, 'A very young child that cries is a ', 'baby', '.', 5),
(71, 'My brother and I are the ', 'children', ' of our parents.', 5),
(72, 'The woman who gave birth to me is my ', 'mother', '.', 5),
(73, 'My parents, my siblings and I make a happy ', 'family', '.', 5),
(74, 'My grandma and grandpa together are my ', 'grandparents', '.', 5),
(75, 'A boy is the ', 'son', ' of his mother and father.', 5),
(76, 'A girl is the ', 'daughter', ' of her mother and father.', 5),
(77, 'The man who is married to my mom is my ', 'dad', '.', 5),
(78, 'My brother is a boy and my sister is a ', 'girl', '.', 5),
(79, 'My dad''s brother has a wife, she is my ', 'aunt', '.', 5),
(80, 'My grandfather is an old ', 'man', ' with white hair.', 5);

INSERT INTO `Conjunto de respostas erradas` (`id`, `nome do conjunto`, `conjunto`, `Pergunta_id`) VALUES
(61, 'family', '["car","dog","book","shoe"]', 61),
(62, 'family', '["cat","bird","apple","pencil"]', 62),
(63, 'family', '["horse","door","window","table"]', 63),
(64, 'family', '["chair","fish","tree","cloud"]', 64),
(65, 'family', '["car","boat","flower","moon"]', 65),
(66, 'family', '["dog","cat","house","car"]', 66),
(67, 'family', '["hat","shoe","spoon","plate"]', 67),
(68, 'family', '["book","pen","desk","ruler"]', 68),
(69, 'family', '["dogs","cats","birds","fishes"]', 69),
(70, 'family', '["car","house","tree","star"]', 70),
(71, 'family', '["apples","chairs","shoes","cars"]', 71),
(72, 'family', '["apple","banana","orange","grape"]', 72),
(73, 'family', '["book","shoe","hat","sock"]', 73),
(74, 'family', '["cats","dogs","cars","trees"]', 74),
(75, 'family', '["cat","dog","bird","fish"]', 75),
(76, 'family', '["car","bus","train","plane"]', 76),
(77, 'family', '["dog","cat","bird","fish"]', 77),
(78, 'family', '["car","tree","book","shoe"]', 78),
(79, 'family', '["apple","banana","car","dog"]', 79),
(80, 'family', '["cat","dog","bird","fish"]', 80);

INSERT INTO `Frases` (`frase`, `id_tema`) VALUES  
('The tiger is a very strong animal', 1),
('The panda is eating green bamboo', 1),
('The turtle walks very slowly', 1),
('The spider has eight long legs', 1),
('The penguin lives in the cold ice', 1),
('The bear is sleeping in the dark cave', 1),
('The horse is running on the big farm', 1),
('The snake is green and long', 1),
('The dolphin is swimming in the blue ocean', 1),
('The butterfly has beautiful colorful wings', 1),
('The capybara is chilling like a boss', 1),
('The little dog is doing a funny TikTok dance', 1),
('The cat is hiding inside the cardboard box', 1),
('The pink pig loves to play in the mud', 1),
('The zebra has black and white stripes', 1),
('The kangaroo is jumping very high', 1),
('The wise owl is looking at the night sky', 1),
('The little mouse is eating yellow cheese', 1),
('The bee is making sweet honey', 1),
('The shark is the big monster of the sea', 1);

INSERT INTO `Frases` (`frase`, `id_tema`) VALUES  
('The loving mother is cooking a delicious pizza', 5),
('The cool father is playing a racing video game', 5),
('My big brother is watching a funny video on TikTok', 5),
('The little sister is drawing a colorful rainbow', 5),
('The sweet grandmother is making a big chocolate cake', 5),
('The old grandfather is sleeping on the comfortable sofa', 5),
('The happy family is taking a beautiful photo for Instagram', 5),
('My funny uncle is dancing at the birthday party', 5),
('The young aunt is buying a new toy at the mall', 5),
('My baby cousin is playing with the cute dog', 5),
('The father is driving the big blue car', 5),
('The mother is helping me with my English homework', 5),
('My sister is listening to pop music on her phone', 5),
('The brother is playing soccer in the green park', 5),
('The grandfather is telling an amazing old story', 5),
('The grandmother loves her beautiful flower garden', 5),
('My cousin is wearing a cool superhero t-shirt', 5),
('The whole family is watching a fun movie together', 5),
('My parents are buying sweet ice cream for us', 5),
('The twin brothers are doing a crazy viral dance', 5);

INSERT INTO `Frases` (`frase`, `id_tema`) VALUES  
('The yellow pencil is inside the case', 4),
('The open book is on the student desk', 4),
('The teacher is writing on the blackboard', 4),
('The heavy backpack is full of notebooks', 4),
('The kids are playing soccer during the break', 4),
('The yellow school bus is waiting outside', 4),
('The blue pen is next to the white eraser', 4),
('The students are using the computer in the lab', 4),
('The physical education class is very fun', 4),
('The girl is drawing a beautiful picture in art class', 4),
('The friends are making a TikTok dance at school', 4),
('The math test is easy like a video game', 4),
('The school bell is ringing for the recess', 4),
('The smart boy is doing his English homework', 4),
('The children are eating pizza in the cafeteria', 4),
('The scissors are for cutting the colorful paper', 4),
('The principal is talking in the big microphone', 4),
('The science project is a cool volcano', 4),
('The dictionary is helping me learn new words', 4),
('The music class is my favorite part of the day', 4);

INSERT INTO `Frases` (`frase`, `id_tema`) VALUES  
('The boy is playing soccer in the green field', 6),
('The tall player is playing basketball like an NBA star', 6),
('The girl is swimming very fast in the blue pool', 6),
('The runner is using cool sneakers to win the race', 6),
('The volleyball match is very famous on Instagram', 6),
('The yellow tennis ball is flying over the big net', 6),
('The cool boy is doing a skateboard trick for TikTok', 6),
('The gamer is playing an e-sports tournament on his computer', 6),
('The champion is happy with the shiny gold medal', 6),
('The surfer is riding a giant wave at the beautiful beach', 6),
('The famous soccer player is scoring a beautiful goal', 6),
('The kids are playing ping pong on the big table', 6),
('The karate master is breaking the wood with a fast kick', 6),
('The strong man is lifting heavy weights in the gym', 6),
('The gymnastics girl is jumping very high in the air', 6),
('The bicycle is super fast in the amazing street race', 6),
('The baseball player is hitting the ball with a big bat', 6),
('The snowboarder is sliding down the cold snow mountain', 6),
('The happy fans are making a viral video in the big stadium', 6),
('The boxing champion is training hard for the great fight', 6);