-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3308
-- Generation Time: Sep 23, 2026 at 07:05 PM
-- Server version: 8.4.3
-- PHP Version: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `tccdb`
--
CREATE DATABASE IF NOT EXISTS `tccdb` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `tccdb`;

-- --------------------------------------------------------

--
-- Table structure for table `perguntasprimeirojogo`
--

CREATE TABLE `perguntasprimeirojogo` (
  `id` int NOT NULL,
  `pergunta` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `perguntassegundojogo`
--

CREATE TABLE `perguntassegundojogo` (
  `id` int NOT NULL,
  `pergunta` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `respostasprimeirojogo`
--

CREATE TABLE `respostasprimeirojogo` (
  `id` int NOT NULL,
  `id_pergunta` int DEFAULT NULL,
  `tipo` enum('certa','resposta') DEFAULT NULL,
  `resposta` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `respostassegundojogo`
--

CREATE TABLE `respostassegundojogo` (
  `id` int NOT NULL,
  `id_pergunta` int DEFAULT NULL,
  `tipo` enum('certa','resposta') DEFAULT NULL,
  `resposta` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `usuario`
--

CREATE TABLE `usuario` (
  `id` int NOT NULL,
  `nome` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `senha` varchar(255) DEFAULT NULL,
  `pontuacaoPrimeiroJogo` int DEFAULT '0',
  `pontuacaoSegundoJogo` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `perguntasprimeirojogo`
--
ALTER TABLE `perguntasprimeirojogo`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `perguntassegundojogo`
--
ALTER TABLE `perguntassegundojogo`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `respostasprimeirojogo`
--
ALTER TABLE `respostasprimeirojogo`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_pergunta` (`id_pergunta`);

--
-- Indexes for table `respostassegundojogo`
--
ALTER TABLE `respostassegundojogo`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_pergunta` (`id_pergunta`);

--
-- Indexes for table `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `perguntasprimeirojogo`
--
ALTER TABLE `perguntasprimeirojogo`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `perguntassegundojogo`
--
ALTER TABLE `perguntassegundojogo`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `respostasprimeirojogo`
--
ALTER TABLE `respostasprimeirojogo`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `respostassegundojogo`
--
ALTER TABLE `respostassegundojogo`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `respostasprimeirojogo`
--
ALTER TABLE `respostasprimeirojogo`
  ADD CONSTRAINT `respostasprimeirojogo_ibfk_1` FOREIGN KEY (`id_pergunta`) REFERENCES `perguntasprimeirojogo` (`id`);

--
-- Constraints for table `respostassegundojogo`
--
ALTER TABLE `respostassegundojogo`
  ADD CONSTRAINT `respostassegundojogo_ibfk_1` FOREIGN KEY (`id_pergunta`) REFERENCES `perguntassegundojogo` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
