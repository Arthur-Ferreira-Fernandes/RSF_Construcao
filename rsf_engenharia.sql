-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 01/04/2026 às 23:07
-- Versão do servidor: 10.4.28-MariaDB
-- Versão do PHP: 8.0.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `rsf_engenharia`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `arquivos`
--

CREATE TABLE `arquivos` (
  `id` int(11) NOT NULL,
  `nome_original` varchar(255) NOT NULL,
  `nome_seguro` varchar(255) NOT NULL,
  `projeto` varchar(150) NOT NULL,
  `tipo_documento` varchar(100) NOT NULL,
  `extensao` varchar(10) NOT NULL,
  `tamanho` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `data_envio` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `despesas`
--

CREATE TABLE `despesas` (
  `id` int(11) NOT NULL,
  `projeto_id` int(11) NOT NULL,
  `descricao` varchar(255) NOT NULL,
  `valor` decimal(15,2) NOT NULL,
  `data_despesa` date NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `status` enum('Ativo','Arquivado') DEFAULT 'Ativo',
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `despesas`
--

INSERT INTO `despesas` (`id`, `projeto_id`, `descricao`, `valor`, `data_despesa`, `usuario_id`, `status`, `criado_em`) VALUES
(1, 1, 'Compra de Cimento', 100.00, '2026-04-01', 1, 'Arquivado', '2026-04-01 20:21:08'),
(2, 1, 'Teste', 100.00, '2026-04-01', 1, 'Arquivado', '2026-04-01 20:21:18');

-- --------------------------------------------------------

--
-- Estrutura para tabela `engenheiros`
--

CREATE TABLE `engenheiros` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `telefone` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `status` enum('Ativo','Inativo') DEFAULT 'Ativo',
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `engenheiros`
--

INSERT INTO `engenheiros` (`id`, `nome`, `telefone`, `email`, `status`, `criado_em`) VALUES
(1, 'Arthur', '11986599562', 'arthur@123.com', 'Ativo', '2026-03-31 12:33:50');

-- --------------------------------------------------------

--
-- Estrutura para tabela `projetos`
--

CREATE TABLE `projetos` (
  `id` int(11) NOT NULL,
  `nome` varchar(150) NOT NULL,
  `engenheiro_responsavel` varchar(100) NOT NULL,
  `valor` decimal(15,2) NOT NULL,
  `descricao` text DEFAULT NULL,
  `endereco` varchar(255) NOT NULL,
  `data_inicio` date NOT NULL,
  `status` enum('Em Orçamento','Em Andamento','Pausado','Concluído') DEFAULT 'Em Andamento',
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `projetos`
--

INSERT INTO `projetos` (`id`, `nome`, `engenheiro_responsavel`, `valor`, `descricao`, `endereco`, `data_inicio`, `status`, `criado_em`) VALUES
(1, 'Teste Edição', '1', 10000.00, 'Teste de edição de obra', 'Rua tie 136', '2026-03-31', 'Em Andamento', '2026-03-31 13:35:31');

-- --------------------------------------------------------

--
-- Estrutura para tabela `recebimentos`
--

CREATE TABLE `recebimentos` (
  `id` int(11) NOT NULL,
  `projeto_id` int(11) NOT NULL,
  `descricao` varchar(255) NOT NULL,
  `valor` decimal(15,2) NOT NULL,
  `data_pagamento` date NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `status` enum('Ativo','Arquivado') DEFAULT 'Ativo',
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `recebimentos`
--

INSERT INTO `recebimentos` (`id`, `projeto_id`, `descricao`, `valor`, `data_pagamento`, `usuario_id`, `status`, `criado_em`) VALUES
(1, 1, 'Medição 1', 100000.00, '2026-04-01', 1, 'Arquivado', '2026-04-01 20:44:28'),
(2, 1, 'Medição 1', 1000.00, '2026-04-01', 1, 'Arquivado', '2026-04-01 20:54:42');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `senha_hash` varchar(255) NOT NULL,
  `nivel_acesso` enum('admin','comum') DEFAULT 'comum',
  `status` enum('Ativo','Inativo') DEFAULT 'Ativo',
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `telefone`, `senha_hash`, `nivel_acesso`, `status`, `criado_em`) VALUES
(1, 'Arthur Ferreira Fernandes', 'arthurfernandesferreira@hotmail.com', '(11) 98659-9562', '$2y$10$gZEBEsVzR/WkR.rilc5MsOrOQ40tJ6U.OE.fCOcnhEsj8lWyNSbYK', 'admin', 'Ativo', '2026-03-31 12:04:14'),
(4, 'Sarah Ferreira', 'sarah@123.com', '(11) 99884-4335', '$2y$10$d3bEslYRx0Xf6/J2UBZar.Izhd.JNNnqieFLip61.2S/YPBaUEdOC', 'comum', 'Ativo', '2026-03-31 14:06:43');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `arquivos`
--
ALTER TABLE `arquivos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices de tabela `despesas`
--
ALTER TABLE `despesas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `projeto_id` (`projeto_id`);

--
-- Índices de tabela `engenheiros`
--
ALTER TABLE `engenheiros`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Índices de tabela `projetos`
--
ALTER TABLE `projetos`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `recebimentos`
--
ALTER TABLE `recebimentos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `projeto_id` (`projeto_id`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `arquivos`
--
ALTER TABLE `arquivos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `despesas`
--
ALTER TABLE `despesas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `engenheiros`
--
ALTER TABLE `engenheiros`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `projetos`
--
ALTER TABLE `projetos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `recebimentos`
--
ALTER TABLE `recebimentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `arquivos`
--
ALTER TABLE `arquivos`
  ADD CONSTRAINT `arquivos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Restrições para tabelas `despesas`
--
ALTER TABLE `despesas`
  ADD CONSTRAINT `despesas_ibfk_1` FOREIGN KEY (`projeto_id`) REFERENCES `projetos` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `recebimentos`
--
ALTER TABLE `recebimentos`
  ADD CONSTRAINT `recebimentos_ibfk_1` FOREIGN KEY (`projeto_id`) REFERENCES `projetos` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
