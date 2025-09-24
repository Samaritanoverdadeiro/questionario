-- MySQL dump 10.13  Distrib 8.0.42, for Win64 (x86_64)
--
-- Host: localhost    Database: banco_questoes
-- ------------------------------------------------------
-- Server version	8.0.42

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `alternativas`
--

DROP TABLE IF EXISTS `alternativas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `alternativas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `questao_id` int NOT NULL,
  `texto` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_correta` tinyint(1) DEFAULT '0',
  `ordem` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_questao_id` (`questao_id`),
  CONSTRAINT `alternativas_ibfk_1` FOREIGN KEY (`questao_id`) REFERENCES `questoes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `alternativas`
--

LOCK TABLES `alternativas` WRITE;
/*!40000 ALTER TABLE `alternativas` DISABLE KEYS */;
/*!40000 ALTER TABLE `alternativas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `avaliacao_questoes`
--

DROP TABLE IF EXISTS `avaliacao_questoes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `avaliacao_questoes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `avaliacao_id` int NOT NULL,
  `questao_id` int NOT NULL,
  `pontos` decimal(5,2) DEFAULT '1.00',
  `ordem` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_avaliacao_questao` (`avaliacao_id`,`questao_id`),
  KEY `questao_id` (`questao_id`),
  CONSTRAINT `avaliacao_questoes_ibfk_1` FOREIGN KEY (`avaliacao_id`) REFERENCES `avaliacoes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `avaliacao_questoes_ibfk_2` FOREIGN KEY (`questao_id`) REFERENCES `questoes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `avaliacao_questoes`
--

LOCK TABLES `avaliacao_questoes` WRITE;
/*!40000 ALTER TABLE `avaliacao_questoes` DISABLE KEYS */;
/*!40000 ALTER TABLE `avaliacao_questoes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `avaliacoes`
--

DROP TABLE IF EXISTS `avaliacoes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `avaliacoes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `titulo` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` text COLLATE utf8mb4_unicode_ci,
  `data_inicio` datetime DEFAULT NULL,
  `data_fim` datetime DEFAULT NULL,
  `duracao_minutos` int DEFAULT NULL,
  `turma_id` int NOT NULL,
  `disciplina_id` int NOT NULL,
  `professor_id` int NOT NULL,
  `visivel` tinyint(1) DEFAULT '1',
  `tipo` enum('online','presencial','híbrida') COLLATE utf8mb4_unicode_ci DEFAULT 'online',
  `criado_em` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `disciplina_id` (`disciplina_id`),
  KEY `professor_id` (`professor_id`),
  KEY `idx_avaliacoes_turma` (`turma_id`),
  CONSTRAINT `avaliacoes_ibfk_1` FOREIGN KEY (`turma_id`) REFERENCES `turmas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `avaliacoes_ibfk_2` FOREIGN KEY (`disciplina_id`) REFERENCES `disciplinas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `avaliacoes_ibfk_3` FOREIGN KEY (`professor_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `avaliacoes`
--

LOCK TABLES `avaliacoes` WRITE;
/*!40000 ALTER TABLE `avaliacoes` DISABLE KEYS */;
/*!40000 ALTER TABLE `avaliacoes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `disciplinas`
--

DROP TABLE IF EXISTS `disciplinas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `disciplinas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigo` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `instituicao_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo` (`codigo`),
  KEY `instituicao_id` (`instituicao_id`),
  CONSTRAINT `disciplinas_ibfk_1` FOREIGN KEY (`instituicao_id`) REFERENCES `instituicoes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `disciplinas`
--

LOCK TABLES `disciplinas` WRITE;
/*!40000 ALTER TABLE `disciplinas` DISABLE KEYS */;
/*!40000 ALTER TABLE `disciplinas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `instituicoes`
--

DROP TABLE IF EXISTS `instituicoes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `instituicoes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cnpj` varchar(18) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `endereco` text COLLATE utf8mb4_unicode_ci,
  `telefone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT '1',
  `criado_em` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cnpj` (`cnpj`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `instituicoes`
--

LOCK TABLES `instituicoes` WRITE;
/*!40000 ALTER TABLE `instituicoes` DISABLE KEYS */;
INSERT INTO `instituicoes` VALUES (1,'Escola Modelo','46234','dadas','22626',1,'2025-09-09 16:13:54'),(2,'carlos montenegro','346345','casa da mae joana','4343422',1,'2025-09-10 16:26:28'),(5,'gggggg','4444444','ssssss','233333',1,'2025-09-16 16:20:21'),(6,'ttttttttt','24646574','45345drfgetr','245624624',1,'2025-09-23 16:49:55');
/*!40000 ALTER TABLE `instituicoes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `logs_acesso`
--

DROP TABLE IF EXISTS `logs_acesso`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `logs_acesso` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int DEFAULT NULL,
  `acao` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `detalhes` text COLLATE utf8mb4_unicode_ci,
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `timestamp` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `logs_acesso_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `logs_acesso`
--

LOCK TABLES `logs_acesso` WRITE;
/*!40000 ALTER TABLE `logs_acesso` DISABLE KEYS */;
/*!40000 ALTER TABLE `logs_acesso` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `questoes`
--

DROP TABLE IF EXISTS `questoes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `questoes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `enunciado` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` enum('multipla_escolha','verdadeiro_falso','dissertativa') COLLATE utf8mb4_unicode_ci DEFAULT 'multipla_escolha',
  `dificuldade` enum('facil','medio','dificil') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `disciplina_id` int NOT NULL,
  `autor_id` int NOT NULL,
  `ativa` tinyint(1) DEFAULT '1',
  `criado_em` datetime DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `aplicada_em` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `disciplina_id` (`disciplina_id`),
  KEY `idx_questoes_autor` (`autor_id`),
  CONSTRAINT `questoes_ibfk_1` FOREIGN KEY (`disciplina_id`) REFERENCES `disciplinas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `questoes_ibfk_2` FOREIGN KEY (`autor_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `questoes`
--

LOCK TABLES `questoes` WRITE;
/*!40000 ALTER TABLE `questoes` DISABLE KEYS */;
/*!40000 ALTER TABLE `questoes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `respostas_alunos`
--

DROP TABLE IF EXISTS `respostas_alunos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `respostas_alunos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `avaliacao_id` int NOT NULL,
  `questao_id` int NOT NULL,
  `aluno_id` int NOT NULL,
  `alternativa_id` int DEFAULT NULL,
  `resposta_texto` text COLLATE utf8mb4_unicode_ci,
  `respondido_em` datetime DEFAULT CURRENT_TIMESTAMP,
  `corrigido` tinyint(1) DEFAULT '0',
  `pontuacao_obtida` decimal(5,2) DEFAULT '0.00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_resposta_aluno_questao` (`avaliacao_id`,`questao_id`,`aluno_id`),
  KEY `questao_id` (`questao_id`),
  KEY `alternativa_id` (`alternativa_id`),
  KEY `idx_respostas_aluno` (`aluno_id`,`avaliacao_id`),
  CONSTRAINT `respostas_alunos_ibfk_1` FOREIGN KEY (`avaliacao_id`) REFERENCES `avaliacoes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `respostas_alunos_ibfk_2` FOREIGN KEY (`questao_id`) REFERENCES `questoes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `respostas_alunos_ibfk_3` FOREIGN KEY (`aluno_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `respostas_alunos_ibfk_4` FOREIGN KEY (`alternativa_id`) REFERENCES `alternativas` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `respostas_alunos`
--

LOCK TABLES `respostas_alunos` WRITE;
/*!40000 ALTER TABLE `respostas_alunos` DISABLE KEYS */;
/*!40000 ALTER TABLE `respostas_alunos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `resultados_avaliacao`
--

DROP TABLE IF EXISTS `resultados_avaliacao`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `resultados_avaliacao` (
  `id` int NOT NULL AUTO_INCREMENT,
  `avaliacao_id` int NOT NULL,
  `aluno_id` int NOT NULL,
  `nota_final` decimal(5,2) DEFAULT '0.00',
  `total_possivel` decimal(5,2) NOT NULL,
  `status` enum('concluido','pendente','expirado') COLLATE utf8mb4_unicode_ci DEFAULT 'pendente',
  `concluido_em` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_avaliacao_aluno` (`avaliacao_id`,`aluno_id`),
  KEY `idx_resultados_aluno` (`aluno_id`),
  CONSTRAINT `resultados_avaliacao_ibfk_1` FOREIGN KEY (`avaliacao_id`) REFERENCES `avaliacoes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `resultados_avaliacao_ibfk_2` FOREIGN KEY (`aluno_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `resultados_avaliacao`
--

LOCK TABLES `resultados_avaliacao` WRITE;
/*!40000 ALTER TABLE `resultados_avaliacao` DISABLE KEYS */;
/*!40000 ALTER TABLE `resultados_avaliacao` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `turma_disciplina`
--

DROP TABLE IF EXISTS `turma_disciplina`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `turma_disciplina` (
  `id` int NOT NULL AUTO_INCREMENT,
  `turma_id` int NOT NULL,
  `disciplina_id` int NOT NULL,
  `professor_id` int NOT NULL,
  `ano_letivo` year NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_turma_disciplina_professor` (`turma_id`,`disciplina_id`,`professor_id`,`ano_letivo`),
  KEY `disciplina_id` (`disciplina_id`),
  KEY `professor_id` (`professor_id`),
  CONSTRAINT `turma_disciplina_ibfk_1` FOREIGN KEY (`turma_id`) REFERENCES `turmas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `turma_disciplina_ibfk_2` FOREIGN KEY (`disciplina_id`) REFERENCES `disciplinas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `turma_disciplina_ibfk_3` FOREIGN KEY (`professor_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `turma_disciplina`
--

LOCK TABLES `turma_disciplina` WRITE;
/*!40000 ALTER TABLE `turma_disciplina` DISABLE KEYS */;
/*!40000 ALTER TABLE `turma_disciplina` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `turmas`
--

DROP TABLE IF EXISTS `turmas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `turmas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ano` year NOT NULL,
  `instituicao_id` int NOT NULL,
  `professor_responsavel_id` int DEFAULT NULL,
  `criado_em` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `instituicao_id` (`instituicao_id`),
  KEY `professor_responsavel_id` (`professor_responsavel_id`),
  CONSTRAINT `turmas_ibfk_1` FOREIGN KEY (`instituicao_id`) REFERENCES `instituicoes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `turmas_ibfk_2` FOREIGN KEY (`professor_responsavel_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `turmas`
--

LOCK TABLES `turmas` WRITE;
/*!40000 ALTER TABLE `turmas` DISABLE KEYS */;
/*!40000 ALTER TABLE `turmas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `senha_hash` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` enum('aluno','professor','admin') COLLATE utf8mb4_unicode_ci NOT NULL,
  `instituicao_id` int DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT '1',
  `criado_em` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `instituicao_id` (`instituicao_id`),
  CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`instituicao_id`) REFERENCES `instituicoes` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (3,'João da Silva','joao.aluno@email.com','55a5e9e78207b4df8699d60886fa070079463547b095d1a05bc719bb4e6cd251','aluno',1,1,'2025-09-09 16:14:04'),(4,'Maria negronna','maria.professor@email.com','6b08d780140e292a4af8ba3f2333fc1357091442d7e807c6cad92e8dcd0240b7','professor',1,1,'2025-09-09 16:14:04'),(5,'Carlos Admin','admin@escola.com','240be518fabd2724ddb6f04eeb1da5967448d7e831c08c8fa822809f74c720a9','admin',1,1,'2025-09-10 14:53:40'),(6,'pedro loss','pedroaluno@gmail.com','$2y$12$2s2EQ0SML89GCux9C3/zTuJQI69n1KGCqHvJfSIQ00I6xllr9mbB.','aluno',2,1,'2025-09-10 16:27:25'),(7,'carlos loiro','carlos@gmail.com','$2y$12$b3PUclR1OxCrv4liTAl4QOVtbTkPolODNq5KxLldnv27Y2u4AYuEW','professor',2,1,'2025-09-10 16:29:07'),(8,'eeeeee','3333333@gmail.com','$2y$12$KtuLlwqlRT8pwU4UwqMslO6YutEhVMKWcp4rG6DcfwulYczACQSr6','aluno',5,1,'2025-09-16 16:21:04'),(9,'RRRRRR','a@gmail.com','$2y$12$hRttk/hne8wgTSpG91eJEewxZG7LdmWzdiJA0Hct9k5BmlqwVCE2y','professor',5,1,'2025-09-16 16:21:22'),(10,'ttt','ttt@gggg','$2y$12$OjJ5XwqQpP.WmYje8CDnLeMwFzCM/ucEdpv3a.4lfkuBvwdnstx0.','professor',5,1,'2025-09-17 13:58:41'),(11,'llll','llll@f','$2y$12$QNtxKfg1RHhPCfm5x7JTGetjP6cztg7cn9TRcEPd0RmSIaFeMakqu','professor',1,1,'2025-09-17 14:23:18'),(12,'fhgj','dfg@3','$2y$12$OQuU5bOaWQ5IJhuXx1/sBOSoS.5UbQZFrxrZsRy1qFY/6gR4s4yeq','aluno',2,1,'2025-09-17 14:23:32'),(14,'ttt','ttt@tttt','$2y$12$0OaPeBnq7s.qAUu2EfAamOPwGaqJmdXdrJSUyhLK8cW5OB7ovjpG2','professor',6,1,'2025-09-23 16:53:35');
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-09-24 15:47:16
