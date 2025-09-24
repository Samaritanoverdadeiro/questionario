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

-- Dump completed on 2025-09-24 14:02:21
