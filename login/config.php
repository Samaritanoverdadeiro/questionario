<?php
// config.php
$host = 'localhost';
$dbname = 'banco_questoes';
$username = 'root'; // Altere para seu usuário do MySQL
$password = 'senacrs';   // Altere para sua senha do MySQL

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}
?>