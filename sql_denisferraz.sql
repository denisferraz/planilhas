-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Tempo de geração: 12/09/2023 às 12:52
-- Versão do servidor: 10.6.14-MariaDB-cll-lve
-- Versão do PHP: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `u661915792_app_checkos`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `excel_hotels`
--

CREATE TABLE `excel_hotels` (
  `1` int(11) NOT NULL,
  `hotel_rid` varchar(5) NOT NULL,
  `hotel_name` varchar(100) NOT NULL,
  `hotel_status` varchar(50) NOT NULL,
  `hotel_validade` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `excel_hotels`
--

INSERT INTO `excel_hotels` (`1`, `hotel_rid`, `hotel_name`, `hotel_status`, `hotel_validade`) VALUES
(1, 'h8185', 'Novotel Salvador Hangar Aeroporto', 'Ativo', '2023-12-31'),
(2, 'h8181', 'Ibis Salvador Hangar Aeroporto', 'Ativo', '2023-12-31'),
(3, 'hB275', 'Novotel Salvador Rio Vermelho', 'Ativo', '2023-12-31'),
(4, 'h3147', 'Mercure SP Pinheiros', 'Ativo', '2023-12-31');

-- --------------------------------------------------------

--
-- Estrutura para tabela `excel_users`
--

CREATE TABLE `excel_users` (
  `id` int(11) NOT NULL,
  `username` varchar(35) NOT NULL,
  `userpassword` varchar(35) NOT NULL,
  `nome` varchar(55) NOT NULL,
  `hotel` varchar(250) NOT NULL,
  `hierarquia` varchar(30) NOT NULL,
  `userstatus` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `excel_users`
--

INSERT INTO `excel_users` (`id`, `username`, `userpassword`, `nome`, `hotel`, `hierarquia`, `userstatus`) VALUES
(1, 'H8185FD', '81dc9bdb52d04dc20036dbd8313ed055', 'Denis Ferraz', 'h8185;h8181;hB275;h3147', 'Gerente', 'Ativo'),
(5, 'H8185RE', 'e8343e3aec2a1c5e792d62a13603f19a', 'Erica Rocha', 'h8185;h8181', 'Supervisor', 'Ativo'),
(6, 'H8185ML', 'e8343e3aec2a1c5e792d62a13603f19a', 'Luiza Marques', 'h8185;h8181', 'Supervisor', 'Ativo'),
(8, 'H8181PA', '4afe4d3ff3ff52198a8a6075a655ad03', 'Anderson Peres', 'h8185;h8181', 'Colaborador', 'Ativo'),
(10, 'H8185RG', 'e8343e3aec2a1c5e792d62a13603f19a', 'Gabriela Ribeiro', 'h8185;h8181', 'Colaborador', 'Ativo'),
(12, 'H8181SA', 'e8343e3aec2a1c5e792d62a13603f19a', 'Amanda Silva', 'h8185;h8181', 'Colaborador', 'Ativo'),
(15, 'H8181FM', 'e8343e3aec2a1c5e792d62a13603f19a', 'Matheus Fontes', 'h8185;h8181', 'Colaborador', 'Ativo'),
(16, 'H8181SC', 'e8343e3aec2a1c5e792d62a13603f19a', 'Cleomar Santos', 'h8185;h8181', 'Colaborador', 'Ativo'),
(18, 'H8185CA', 'ed7785bc00bded4742db58635d572455', 'Aimee Carvalho', 'h8185;h8181', 'Colaborador', 'Ativo'),
(20, 'H8181SE', 'e8343e3aec2a1c5e792d62a13603f19a', 'Eva Silva', 'h8185;h8181', 'Colaborador', 'Ativo'),
(23, 'H8181AS', 'e8343e3aec2a1c5e792d62a13603f19a', 'Aislan Silva', 'h8185;h8181', 'Colaborador', 'Ativo'),
(24, 'H8181SD', 'e8343e3aec2a1c5e792d62a13603f19a', 'Daniel Silva', 'h8185;h8181', 'Colaborador', 'Ativo'),
(27, 'H8181ER', 'e8343e3aec2a1c5e792d62a13603f19a', 'Eric Reis', 'h8185;h8181', 'Colaborador', 'Ativo'),
(29, 'H8185PL', '2de7d34d0f99efe2ebfc90a063053f87', 'Lucas Pinto', 'h8185;h8181', 'Colaborador', 'Ativo'),
(30, 'H8185LM', 'e8343e3aec2a1c5e792d62a13603f19a', 'Leandra Magalhaes', 'h8185;h8181', 'Colaborador', 'Ativo'),
(33, 'H8185MM', 'e8343e3aec2a1c5e792d62a13603f19a', 'Moises Melo', 'h8185;h8181', 'Colaborador', 'Ativo'),
(35, 'H8181BB', 'e8343e3aec2a1c5e792d62a13603f19a', 'Bruno Brugni', 'h8185;h8181', 'Colaborador', 'Ativo'),
(36, 'H8181GE', 'e8343e3aec2a1c5e792d62a13603f19a', 'Elton Goncalves', 'h8185;h8181', 'Colaborador', 'Ativo'),
(39, 'H8181FG', 'e8343e3aec2a1c5e792d62a13603f19a', 'Geraldo Ferreira', 'h8185;h8181', 'Colaborador', 'Ativo'),
(40, 'H8185JJ', '81dc9bdb52d04dc20036dbd8313ed055', 'Jose Junior', 'h8185;h8181', 'Gerente', 'Ativo'),
(42, 'H3147LD', 'e8343e3aec2a1c5e792d62a13603f19a', 'Daiane Lima', 'h3147', 'Gerente', 'Ativo'),
(43, 'HB275FO', '1062b93a7e82d2f467b44aee06cafc92', 'Helder Pinheiro', 'hB275', 'Gerente', 'Ativo'),
(44, 'H8185AC', '81dc9bdb52d04dc20036dbd8313ed055', 'Camila Ayres', 'h8185;h8181', 'Gerente', 'Ativo');

-- --------------------------------------------------------

--
-- Estrutura para tabela `h8181_excel_gestaorecepcao_arrivals`
--

CREATE TABLE `h8181_excel_gestaorecepcao_arrivals` (
  `id` int(11) NOT NULL,
  `data_importacao` datetime NOT NULL DEFAULT current_timestamp(),
  `dados_arrivals` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- Estrutura para tabela `h8181_excel_gestaorecepcao_cashier`
--

CREATE TABLE `h8181_excel_gestaorecepcao_cashier` (
  `id` int(11) NOT NULL,
  `username` varchar(35) NOT NULL,
  `tipo_lancamento` varchar(35) NOT NULL,
  `pagamento_tipo` varchar(55) NOT NULL,
  `pagamento_valor` varchar(55) NOT NULL,
  `reserva_id` int(11) NOT NULL,
  `origem` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- Estrutura para tabela `h8181_excel_gestaorecepcao_inhouse`
--

CREATE TABLE `h8181_excel_gestaorecepcao_inhouse` (
  `id` int(11) NOT NULL,
  `data_importacao` datetime NOT NULL DEFAULT current_timestamp(),
  `dados_presentlist` mediumtext NOT NULL,
  `reserva_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- Estrutura para tabela `h8181_excel_gestaorecepcao_roomstatus`
--

CREATE TABLE `h8181_excel_gestaorecepcao_roomstatus` (
  `id` int(11) NOT NULL,
  `data_importacao` datetime NOT NULL DEFAULT current_timestamp(),
  `room_number` mediumtext NOT NULL,
  `room_status` mediumtext NOT NULL,
  `room_type` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- Estrutura para tabela `h8181_excel_gestaorecepcao_roomtypes`
--

CREATE TABLE `h8181_excel_gestaorecepcao_roomtypes` (
  `id` int(11) NOT NULL,
  `room_type` varchar(50) NOT NULL,
  `room_type_qtd` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `h8185_excel_gestaorecepcao_arrivals`
--

CREATE TABLE `h8185_excel_gestaorecepcao_arrivals` (
  `id` int(11) NOT NULL,
  `data_importacao` datetime NOT NULL DEFAULT current_timestamp(),
  `dados_arrivals` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- Estrutura para tabela `h8185_excel_gestaorecepcao_cashier`
--

CREATE TABLE `h8185_excel_gestaorecepcao_cashier` (
  `id` int(11) NOT NULL,
  `username` varchar(35) NOT NULL,
  `tipo_lancamento` varchar(35) NOT NULL,
  `pagamento_tipo` varchar(55) NOT NULL,
  `pagamento_valor` varchar(55) NOT NULL,
  `reserva_id` int(11) NOT NULL,
  `origem` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- Estrutura para tabela `h8185_excel_gestaorecepcao_inhouse`
--

CREATE TABLE `h8185_excel_gestaorecepcao_inhouse` (
  `id` int(11) NOT NULL,
  `data_importacao` datetime NOT NULL DEFAULT current_timestamp(),
  `dados_presentlist` mediumtext NOT NULL,
  `reserva_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- Estrutura para tabela `h8185_excel_gestaorecepcao_roomstatus`
--

CREATE TABLE `h8185_excel_gestaorecepcao_roomstatus` (
  `id` int(11) NOT NULL,
  `data_importacao` datetime NOT NULL DEFAULT current_timestamp(),
  `room_number` mediumtext NOT NULL,
  `room_status` mediumtext NOT NULL,
  `room_type` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- Estrutura para tabela `h8185_excel_gestaorecepcao_roomtypes`
--

CREATE TABLE `h8185_excel_gestaorecepcao_roomtypes` (
  `id` int(11) NOT NULL,
  `room_type` varchar(50) NOT NULL,
  `room_type_qtd` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `excel_hotels`
--
ALTER TABLE `excel_hotels`
  ADD PRIMARY KEY (`1`);

--
-- Índices de tabela `excel_users`
--
ALTER TABLE `excel_users`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `h8181_excel_gestaorecepcao_arrivals`
--
ALTER TABLE `h8181_excel_gestaorecepcao_arrivals`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `h8181_excel_gestaorecepcao_cashier`
--
ALTER TABLE `h8181_excel_gestaorecepcao_cashier`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `h8181_excel_gestaorecepcao_inhouse`
--
ALTER TABLE `h8181_excel_gestaorecepcao_inhouse`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `h8181_excel_gestaorecepcao_roomstatus`
--
ALTER TABLE `h8181_excel_gestaorecepcao_roomstatus`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `h8181_excel_gestaorecepcao_roomtypes`
--
ALTER TABLE `h8181_excel_gestaorecepcao_roomtypes`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `h8185_excel_gestaorecepcao_arrivals`
--
ALTER TABLE `h8185_excel_gestaorecepcao_arrivals`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `h8185_excel_gestaorecepcao_cashier`
--
ALTER TABLE `h8185_excel_gestaorecepcao_cashier`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `h8185_excel_gestaorecepcao_inhouse`
--
ALTER TABLE `h8185_excel_gestaorecepcao_inhouse`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `h8185_excel_gestaorecepcao_roomstatus`
--
ALTER TABLE `h8185_excel_gestaorecepcao_roomstatus`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `h8185_excel_gestaorecepcao_roomtypes`
--
ALTER TABLE `h8185_excel_gestaorecepcao_roomtypes`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `excel_hotels`
--
ALTER TABLE `excel_hotels`
  MODIFY `1` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `excel_users`
--
ALTER TABLE `excel_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT de tabela `h8181_excel_gestaorecepcao_arrivals`
--
ALTER TABLE `h8181_excel_gestaorecepcao_arrivals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `h8181_excel_gestaorecepcao_cashier`
--
ALTER TABLE `h8181_excel_gestaorecepcao_cashier`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `h8181_excel_gestaorecepcao_inhouse`
--
ALTER TABLE `h8181_excel_gestaorecepcao_inhouse`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `h8181_excel_gestaorecepcao_roomstatus`
--
ALTER TABLE `h8181_excel_gestaorecepcao_roomstatus`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `h8181_excel_gestaorecepcao_roomtypes`
--
ALTER TABLE `h8181_excel_gestaorecepcao_roomtypes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `h8185_excel_gestaorecepcao_arrivals`
--
ALTER TABLE `h8185_excel_gestaorecepcao_arrivals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `h8185_excel_gestaorecepcao_cashier`
--
ALTER TABLE `h8185_excel_gestaorecepcao_cashier`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `h8185_excel_gestaorecepcao_inhouse`
--
ALTER TABLE `h8185_excel_gestaorecepcao_inhouse`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `h8185_excel_gestaorecepcao_roomstatus`
--
ALTER TABLE `h8185_excel_gestaorecepcao_roomstatus`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `h8185_excel_gestaorecepcao_roomtypes`
--
ALTER TABLE `h8185_excel_gestaorecepcao_roomtypes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
