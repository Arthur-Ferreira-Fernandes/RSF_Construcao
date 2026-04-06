-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 06/04/2026 às 15:24
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
  `valor_orcado` decimal(15,2) DEFAULT 0.00,
  `data_previsao` date DEFAULT NULL,
  `data_realizada` date DEFAULT NULL,
  `concluido` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `acompanhamento_itens`
--

INSERT INTO `acompanhamento_itens` (`id`, `acompanhamento_id`, `descricao`, `valor_orcado`, `data_previsao`, `data_realizada`, `concluido`) VALUES
(110, 51, 'montagem de linha para execução de alvenaria', 3500.00, '2025-08-30', NULL, 0),
(111, 51, 'montagem de torres de andaime para execução de  alvenaria', 7500.00, '2025-08-30', NULL, 0),
(112, 52, 'execução de alvenaria frizada 25%', 16000.00, '2025-07-16', NULL, 0),
(113, 52, 'execução de alvenaria frizada 50%', 16000.00, '2025-07-30', NULL, 0),
(114, 52, 'execução de alvenaria frizada 75%', 16000.00, '2025-08-15', NULL, 0),
(115, 52, 'execução de alvenaria frizada 100%', 16000.00, '2025-08-30', NULL, 0),
(116, 53, 'execução de alvenaria estrutural dos oitão superior 25%', 1200.00, '2025-08-15', NULL, 0),
(117, 53, 'execução de graute dos oitão superior 25%', 1200.00, '2025-08-15', NULL, 0),
(118, 53, 'execução de alvenaria estrutural dos oitão superior 50%', 1200.00, '2025-08-30', NULL, 0),
(119, 53, 'execução de graute dos oitão superior 50%', 1200.00, '2025-08-30', NULL, 0),
(120, 53, 'execução de alvenaria estrutural dos oitão superior 75%', 1200.00, '2025-09-15', NULL, 0),
(121, 53, 'execução de graute dos oitão superior 75%', 1200.00, '2025-09-15', NULL, 0),
(122, 53, 'execução de alvenaria estrutural dos oitão superior 100%', 1200.00, '2025-09-30', NULL, 0),
(123, 53, 'execução de graute dos oitão superior 100%', 1200.00, '2025-09-30', NULL, 0),
(124, 54, 'execução de canaleta estrutural em alvenaria estrutural 25%', 900.00, '2025-08-15', NULL, 0),
(125, 54, 'execução de canaleta estrutural em alvenaria estrutural  50%', 900.00, '2025-08-30', NULL, 0),
(126, 54, 'execução de canaleta estrutural em alvenaria estrutural 75%', 900.00, '2025-09-15', NULL, 0),
(127, 54, 'execução de canaleta estrutural em alvenaria estrutural 100%', 900.00, '2025-09-30', NULL, 0),
(128, 55, 'isamento de pilares metalicos', 8500.00, '2025-06-30', NULL, 0),
(129, 55, 'isamento de mão francesa', 7000.00, '2025-07-05', NULL, 0),
(130, 55, 'isamento de canaletas intermediarias metalicas', 9500.00, '2025-07-20', NULL, 0),
(131, 55, 'isamento de vigas metalicas', 10000.00, '2025-08-20', NULL, 0),
(132, 56, 'fixação de pilares metalicos', 22500.00, '2025-07-05', NULL, 0),
(133, 56, 'fixação de mão francesa', 12500.00, '2025-08-20', NULL, 0),
(134, 56, 'fixação de canaleta intermediaria', 20500.00, '2025-07-20', NULL, 0),
(135, 56, 'fixação e travamento de viga metalica', 22500.00, '2025-08-20', NULL, 0),
(136, 56, 'fixação de viga de topo metalica', 20500.00, '2025-08-20', NULL, 0),
(137, 57, 'montagem de caxaria de pilares de concreto cabine primaria', 3500.00, '2025-07-10', NULL, 0),
(138, 57, 'lançamento de ferragem de pilares de concreto cabine', 1250.00, '2025-07-15', NULL, 0),
(139, 57, 'concretagem de pilares cabine', 2500.00, '2025-07-16', NULL, 0),
(140, 57, 'execução de alvenaria estrutural da cabine', 2100.00, '2025-07-26', NULL, 0),
(141, 57, 'execução de graute de alvenaria estrutural da cabine', 1500.00, '2025-07-26', NULL, 0),
(142, 57, 'execução de canaleta estrutural da cabine', 1500.00, '2025-08-05', NULL, 0),
(143, 57, 'montagem de caxaria de viga de topo cabine primaria', 1500.00, '2025-07-25', NULL, 0),
(144, 57, 'desforma de pilares', 1200.00, '2025-07-19', NULL, 0),
(145, 57, 'montagem de laje de cabine primaria', 2500.00, '2025-08-02', NULL, 0),
(146, 57, 'lançamento de ferragens das vigas de topo', 2000.00, '2025-08-02', NULL, 0),
(147, 57, 'concretagem das vigas de topo', 1250.00, '2025-08-06', NULL, 0),
(148, 57, 'concretagem de laje de cabine', 1250.00, '2025-08-06', NULL, 0),
(149, 58, 'montagem de linha de escoramento', 15500.00, '2025-08-01', NULL, 0),
(150, 58, 'assoalhamento de laje com maderite', 17500.00, '2025-08-11', NULL, 0),
(151, 58, 'preparação de assoalho para receber ferragem de laje', 10500.00, '2025-08-14', NULL, 0),
(152, 58, 'lançamento de ferragem de laje', 12000.00, '2025-08-25', NULL, 0),
(153, 58, 'travamento de ferragem de laje', 4500.00, '2025-08-27', NULL, 0),
(154, 58, 'colocação de espaçadores junto a ferragem da laje', 4500.00, '2025-08-28', NULL, 0),
(155, 58, 'concretagem de laje maciça', 10500.00, '2025-08-29', NULL, 0),
(156, 58, 'execução de bambole na laje para nivelamento', 10000.00, '2025-08-29', NULL, 0),
(157, 58, 'desfoma de laje', 9500.00, '2025-10-01', NULL, 0),
(158, 58, 'reescoramento de laje', 8500.00, '2025-10-03', NULL, 0),
(159, 59, 'montagem de caxaria de escada', 2500.00, '2025-08-25', NULL, 0),
(160, 59, 'montagem de ferragem de escada', 4500.00, '2025-08-27', NULL, 0),
(161, 59, 'montagem de caxaria de degraus de escada', 3500.00, '2025-08-28', NULL, 0),
(162, 59, 'concretagem de escada', 2500.00, '2025-08-29', NULL, 0),
(163, 60, 'montagem de estrutura das tesouras do telhado', 15000.00, '2025-09-21', NULL, 0),
(164, 60, 'montagem das terças', 7750.00, '2025-09-25', NULL, 0),
(165, 60, 'Montagem das cantoneiras', 5000.00, '2025-09-25', NULL, 0),
(166, 60, 'Lançamento das tesouras', 10000.00, '2025-10-15', NULL, 0),
(167, 60, 'lançamento das terças', 5000.00, '2025-10-20', NULL, 0),
(168, 60, 'lançamento dos montantes', 2500.00, '2025-10-27', NULL, 0),
(169, 60, 'colocação de telhas tipo sanduiche', 5000.00, '2025-11-10', NULL, 0),
(170, 61, 'preparação e nivelamento de terreno', 3500.00, '2025-10-23', NULL, 0),
(171, 61, 'lançamento de lona de proteção', 2500.00, '2025-10-29', NULL, 0),
(172, 61, 'lançamento de malha pop pesada', 10000.00, '2025-11-05', NULL, 0),
(173, 61, 'lançamento de ferragem positiva e negativa do piso', 5000.00, '2025-11-05', NULL, 0),
(174, 61, 'travamento de ferragem e malhas', 5000.00, '2025-11-12', NULL, 0),
(175, 61, 'concretagem de piso', 4000.00, '2025-11-13', NULL, 0),
(176, 61, 'alisamento de concreto ( bambole )', 10000.00, '2025-11-13', NULL, 0);

-- --------------------------------------------------------

--
-- Estrutura para tabela `acompanhamento_semanal`
--

CREATE TABLE `acompanhamento_semanal` (
  `id` int(11) NOT NULL,
  `projeto_id` int(11) NOT NULL,
  `ordem` int(11) DEFAULT 0,
  `titulo_semana` varchar(100) NOT NULL,
  `data_inicio` date NOT NULL,
  `data_fim` date NOT NULL,
  `valor_orcado` decimal(15,2) DEFAULT 0.00,
  `meta_semana` text NOT NULL,
  `diario_obra` text DEFAULT NULL,
  `status` enum('No Prazo','Atrasado','Concluído') DEFAULT 'No Prazo',
  `usuario_id` int(11) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `acompanhamento_semanal`
--

INSERT INTO `acompanhamento_semanal` (`id`, `projeto_id`, `ordem`, `titulo_semana`, `data_inicio`, `data_fim`, `valor_orcado`, `meta_semana`, `diario_obra`, `status`, `usuario_id`, `criado_em`) VALUES
(51, 2, 1, '1º ETAPA', '2025-06-30', '2025-08-30', 11000.00, 'Metas em Checklist', NULL, '', 1, '2026-04-06 13:06:33'),
(52, 2, 2, '2º ETAPA', '2025-06-30', '2025-08-30', 64000.00, 'Metas em Checklist', NULL, '', 1, '2026-04-06 13:06:33'),
(53, 2, 3, '3º ETAPA', '2025-07-30', '2025-09-30', 9600.00, 'Metas em Checklist', NULL, '', 1, '2026-04-06 13:06:34'),
(54, 2, 4, '4º ETAPA', '2025-07-30', '2025-09-15', 3600.00, 'Metas em Checklist', NULL, '', 1, '2026-04-06 13:06:34'),
(55, 2, 5, '5º ETAPA', '2025-06-20', '2025-08-20', 35000.00, 'Metas em Checklist', NULL, '', 1, '2026-04-06 13:06:34'),
(56, 2, 6, '6º ETAPA', '2025-06-20', '2025-08-20', 98500.00, 'Metas em Checklist', NULL, '', 1, '2026-04-06 13:06:34'),
(57, 2, 7, '7º ETAPA', '2025-06-30', '2025-08-06', 22050.00, 'Metas em Checklist', NULL, '', 1, '2026-04-06 13:06:34'),
(58, 2, 8, '8º ETAPA', '2025-07-21', '2025-10-03', 103000.00, 'Metas em Checklist', NULL, '', 1, '2026-04-06 13:06:35'),
(59, 2, 9, '9º ETAPA', '2025-08-15', '2025-08-29', 13000.00, 'Metas em Checklist', NULL, '', 1, '2026-04-06 13:06:35'),
(60, 2, 10, '10º ETAPA', '2025-09-01', '2025-10-27', 50250.00, 'Metas em Checklist', NULL, '', 1, '2026-04-06 13:06:35'),
(61, 2, 11, '11º ETAPA', '2025-10-13', '2025-11-13', 40000.00, 'Metas em Checklist', NULL, '', 1, '2026-04-06 13:06:35');

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
(1, 'Arthur Cliente', '425.260.468-07', '(11) 98659-9562', 'arthurfernandesferreira@hotmail.com', NULL, '', '2026-04-02 20:22:53'),
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
(2, 'Alvenaria laje e piso terreo', '5', 2, 500000.00, 'Teste', 'Avenida Nossa Senhra da Saude 1187 - Vila das Merces', '2025-06-30', '2025-11-13', 'Semanal', 'Em Andamento', '2026-04-04 18:32:25');

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
(7, 2, 'Medição - Semana 1', 18518.52, '2026-04-10', 1, 'Arquivado', '2026-04-04 18:43:41'),
(8, 2, 'Medição - Semana 1', 100000.00, '2026-04-10', 1, 'Arquivado', '2026-04-05 15:21:57'),
(9, 2, 'Medição - Semana 1', 100000.00, '2026-04-10', 1, 'Arquivado', '2026-04-05 15:22:06'),
(10, 2, 'Medição - Semana 2', 100000.00, '2026-04-17', 1, 'Arquivado', '2026-04-05 15:22:09'),
(11, 2, 'Medição - Semana 2', 100000.00, '2026-04-17', 1, 'Arquivado', '2026-04-05 15:22:19'),
(12, 2, 'Medição - Semana 2', 100000.00, '2026-04-17', 1, 'Arquivado', '2026-04-05 15:22:58'),
(13, 2, 'Medição - 1º ETAPA', 11000.00, '2025-08-30', 1, 'Arquivado', '2026-04-06 12:38:08'),
(14, 2, 'Medição - 1º ETAPA', 11000.00, '2025-08-30', 1, 'Arquivado', '2026-04-06 13:10:56');

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
(4, 'Sarah Ferreira', 'sarah@123.com', '(11) 99884-4335', '$2y$10$d3bEslYRx0Xf6/J2UBZar.Izhd.JNNnqieFLip61.2S/YPBaUEdOC', 'comum', 'Ativo', '2026-03-31 14:06:43'),
(5, 'Rodrigo Souza Fernandes', 'rsfassessoria@yahoo.com.br', '(11) 94031-0835', '$2y$10$XDhV85TChl89rhIgYS50lOJG0bws9O78IwuYsWgWmQNh.iKjamS8C', 'admin', 'Ativo', '2026-04-06 13:17:37');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=177;

--
-- AUTO_INCREMENT de tabela `acompanhamento_semanal`
--
ALTER TABLE `acompanhamento_semanal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
