-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 05/04/2026 às 16:40
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
-- Estrutura para tabela `acompanhamento_itens`
--

CREATE TABLE `acompanhamento_itens` (
  `id` int(11) NOT NULL,
  `acompanhamento_id` int(11) NOT NULL,
  `descricao` varchar(255) NOT NULL,
  `concluido` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `acompanhamento_itens`
--

INSERT INTO `acompanhamento_itens` (`id`, `acompanhamento_id`, `descricao`, `concluido`) VALUES
(1, 2, 'Teste', 0),
(2, 2, 'Teste', 0),
(3, 3, 'Teste', 0),
(4, 4, 'Teste', 0),
(5, 5, 'Teste', 0),
(6, 6, 'Teste', 0),
(7, 7, 'Teste', 0),
(8, 8, 'Teste', 0),
(9, 9, 'Teste', 0),
(10, 10, 'Teste', 0),
(11, 11, 'Teste', 0),
(12, 12, 'Teste', 0),
(13, 13, 'Teste', 0),
(14, 14, 'Teste', 0),
(15, 15, 'Teste', 0),
(16, 16, 'Teste', 0),
(17, 17, 'Teste', 0),
(18, 18, 'Teste', 0),
(19, 19, 'Teste', 0),
(20, 20, 'Teste', 0),
(21, 21, 'Teste', 0),
(22, 22, 'Teste', 0),
(23, 23, 'Teste', 0),
(24, 24, 'Teste', 0),
(25, 25, 'Teste', 0),
(26, 26, 'Teste', 0),
(27, 27, 'Teste', 0),
(28, 28, 'Teste', 0);

-- --------------------------------------------------------

--
-- Estrutura para tabela `acompanhamento_semanal`
--

CREATE TABLE `acompanhamento_semanal` (
  `id` int(11) NOT NULL,
  `projeto_id` int(11) NOT NULL,
  `titulo_semana` varchar(100) NOT NULL,
  `data_inicio` date NOT NULL,
  `data_fim` date NOT NULL,
  `meta_semana` text NOT NULL,
  `diario_obra` text DEFAULT NULL,
  `status` enum('No Prazo','Atrasado','Concluído') DEFAULT 'No Prazo',
  `usuario_id` int(11) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `acompanhamento_semanal`
--

INSERT INTO `acompanhamento_semanal` (`id`, `projeto_id`, `titulo_semana`, `data_inicio`, `data_fim`, `meta_semana`, `diario_obra`, `status`, `usuario_id`, `criado_em`) VALUES
(2, 2, 'Semana 1', '2026-04-04', '2026-04-10', 'Metas em Checklist', NULL, '', 1, '2026-04-04 18:41:16'),
(3, 2, 'Semana 2', '2026-04-11', '2026-04-17', 'Metas em Checklist', NULL, '', 1, '2026-04-04 18:41:16'),
(4, 2, 'Semana 3', '2026-04-18', '2026-04-24', 'Metas em Checklist', NULL, '', 1, '2026-04-04 18:41:16'),
(5, 2, 'Semana 4', '2026-04-25', '2026-05-01', 'Metas em Checklist', NULL, '', 1, '2026-04-04 18:41:16'),
(6, 2, 'Semana 5', '2026-05-02', '2026-05-08', 'Metas em Checklist', NULL, '', 1, '2026-04-04 18:41:16'),
(7, 2, 'Semana 6', '2026-05-09', '2026-05-15', 'Metas em Checklist', NULL, '', 1, '2026-04-04 18:41:17'),
(8, 2, 'Semana 7', '2026-05-16', '2026-05-22', 'Metas em Checklist', NULL, '', 1, '2026-04-04 18:41:17'),
(9, 2, 'Semana 8', '2026-05-23', '2026-05-29', 'Metas em Checklist', NULL, '', 1, '2026-04-04 18:41:17'),
(10, 2, 'Semana 9', '2026-05-30', '2026-06-05', 'Metas em Checklist', NULL, '', 1, '2026-04-04 18:41:17'),
(11, 2, 'Semana 10', '2026-06-06', '2026-06-12', 'Metas em Checklist', NULL, '', 1, '2026-04-04 18:41:17'),
(12, 2, 'Semana 11', '2026-06-13', '2026-06-19', 'Metas em Checklist', NULL, '', 1, '2026-04-04 18:41:17'),
(13, 2, 'Semana 12', '2026-06-20', '2026-06-26', 'Metas em Checklist', NULL, '', 1, '2026-04-04 18:41:17'),
(14, 2, 'Semana 13', '2026-06-27', '2026-07-03', 'Metas em Checklist', NULL, '', 1, '2026-04-04 18:41:17'),
(15, 2, 'Semana 14', '2026-07-04', '2026-07-10', 'Metas em Checklist', NULL, '', 1, '2026-04-04 18:41:17'),
(16, 2, 'Semana 15', '2026-07-11', '2026-07-17', 'Metas em Checklist', NULL, '', 1, '2026-04-04 18:41:17'),
(17, 2, 'Semana 16', '2026-07-18', '2026-07-24', 'Metas em Checklist', NULL, '', 1, '2026-04-04 18:41:17'),
(18, 2, 'Semana 17', '2026-07-25', '2026-07-31', 'Metas em Checklist', NULL, '', 1, '2026-04-04 18:41:17'),
(19, 2, 'Semana 18', '2026-08-01', '2026-08-07', 'Metas em Checklist', NULL, '', 1, '2026-04-04 18:41:17'),
(20, 2, 'Semana 19', '2026-08-08', '2026-08-14', 'Metas em Checklist', NULL, '', 1, '2026-04-04 18:41:18'),
(21, 2, 'Semana 20', '2026-08-15', '2026-08-21', 'Metas em Checklist', NULL, '', 1, '2026-04-04 18:41:18'),
(22, 2, 'Semana 21', '2026-08-22', '2026-08-28', 'Metas em Checklist', NULL, '', 1, '2026-04-04 18:41:18'),
(23, 2, 'Semana 22', '2026-08-29', '2026-09-04', 'Metas em Checklist', NULL, '', 1, '2026-04-04 18:41:18'),
(24, 2, 'Semana 23', '2026-09-05', '2026-09-11', 'Metas em Checklist', NULL, '', 1, '2026-04-04 18:41:18'),
(25, 2, 'Semana 24', '2026-09-12', '2026-09-18', 'Metas em Checklist', NULL, '', 1, '2026-04-04 18:41:18'),
(26, 2, 'Semana 25', '2026-09-19', '2026-09-25', 'Metas em Checklist', NULL, '', 1, '2026-04-04 18:41:18'),
(27, 2, 'Semana 26', '2026-09-26', '2026-10-02', 'Metas em Checklist', NULL, '', 1, '2026-04-04 18:41:18'),
(28, 2, 'Semana 27', '2026-10-03', '2026-10-04', 'Metas em Checklist', NULL, '', 1, '2026-04-04 18:41:18');

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
-- Estrutura para tabela `clientes`
--

CREATE TABLE `clientes` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `documento` varchar(20) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `senha` varchar(255) DEFAULT NULL,
  `status` enum('Ativo','Arquivado') DEFAULT 'Ativo',
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `clientes`
--

INSERT INTO `clientes` (`id`, `nome`, `documento`, `telefone`, `email`, `senha`, `status`, `criado_em`) VALUES
(1, 'Arthur Cliente', '425.260.468-07', '(11) 98659-9562', 'arthurfernandesferreira@hotmail.com', NULL, 'Ativo', '2026-04-02 20:22:53'),
(2, 'Sarah Ferreira Cliente', '11.111.111/1111-11', '(11) 11111-1111', 'sarahfcliente@123.com', '$2y$10$UWdJGijrhes8Yq3Yy1TJfe9rTnictk8/E.5WTomW3xCzb4Q58ep1u', 'Ativo', '2026-04-04 19:02:56');

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
  `cliente_id` int(11) DEFAULT NULL,
  `valor` decimal(15,2) NOT NULL,
  `descricao` text DEFAULT NULL,
  `endereco` varchar(255) NOT NULL,
  `data_inicio` date NOT NULL,
  `data_fim_prevista` date DEFAULT NULL,
  `frequencia_medicao` enum('Diária','Semanal','Mensal') DEFAULT 'Semanal',
  `status` enum('Em Orçamento','Em Andamento','Pausado','Concluído') DEFAULT 'Em Andamento',
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `projetos`
--

INSERT INTO `projetos` (`id`, `nome`, `engenheiro_responsavel`, `cliente_id`, `valor`, `descricao`, `endereco`, `data_inicio`, `data_fim_prevista`, `frequencia_medicao`, `status`, `criado_em`) VALUES
(2, 'Teste', '4', 2, 500000.00, 'Teste', 'Rua tie 136', '2026-04-04', '2026-10-04', 'Semanal', 'Em Andamento', '2026-04-04 18:32:25');

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
(6, 2, 'Medição - Semana 1', 18518.52, '2026-04-10', 1, 'Arquivado', '2026-04-04 18:41:33'),
(7, 2, 'Medição - Semana 1', 18518.52, '2026-04-10', 1, 'Ativo', '2026-04-04 18:43:41');

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
(1, 'Arthur Ferreira Fernandes', 'arthurfernandesferreira@hotmail.com', '(11) 98659-9562', '$2y$10$eSRVmxs1MuHSlxL6FM97vupBFovYBwqmj2NegNtVUEQ57xZpItfrW', 'admin', 'Ativo', '2026-03-31 12:04:14'),
(4, 'Sarah Ferreira', 'sarah@123.com', '(11) 99884-4335', '$2y$10$d3bEslYRx0Xf6/J2UBZar.Izhd.JNNnqieFLip61.2S/YPBaUEdOC', 'comum', 'Ativo', '2026-03-31 14:06:43');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `acompanhamento_itens`
--
ALTER TABLE `acompanhamento_itens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `acompanhamento_id` (`acompanhamento_id`);

--
-- Índices de tabela `acompanhamento_semanal`
--
ALTER TABLE `acompanhamento_semanal`
  ADD PRIMARY KEY (`id`),
  ADD KEY `projeto_id` (`projeto_id`);

--
-- Índices de tabela `arquivos`
--
ALTER TABLE `arquivos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices de tabela `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`);

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
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`);

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
-- AUTO_INCREMENT de tabela `acompanhamento_itens`
--
ALTER TABLE `acompanhamento_itens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT de tabela `acompanhamento_semanal`
--
ALTER TABLE `acompanhamento_semanal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT de tabela `arquivos`
--
ALTER TABLE `arquivos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `despesas`
--
ALTER TABLE `despesas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `engenheiros`
--
ALTER TABLE `engenheiros`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `projetos`
--
ALTER TABLE `projetos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `recebimentos`
--
ALTER TABLE `recebimentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `acompanhamento_itens`
--
ALTER TABLE `acompanhamento_itens`
  ADD CONSTRAINT `acompanhamento_itens_ibfk_1` FOREIGN KEY (`acompanhamento_id`) REFERENCES `acompanhamento_semanal` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `acompanhamento_semanal`
--
ALTER TABLE `acompanhamento_semanal`
  ADD CONSTRAINT `acompanhamento_semanal_ibfk_1` FOREIGN KEY (`projeto_id`) REFERENCES `projetos` (`id`) ON DELETE CASCADE;

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
-- Restrições para tabelas `projetos`
--
ALTER TABLE `projetos`
  ADD CONSTRAINT `projetos_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `recebimentos`
--
ALTER TABLE `recebimentos`
  ADD CONSTRAINT `recebimentos_ibfk_1` FOREIGN KEY (`projeto_id`) REFERENCES `projetos` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
