<?php
// verificar_hash.php - FERRAMENTA DE DEBUG
require_once 'config.php';

$email = 'aluno@escola.com'; // Altere para testar diferentes usuários

try {
    $stmt = $pdo->prepare("SELECT senha_hash FROM usuarios WHERE email = :email");
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "Hash para $email: " . $usuario['senha_hash'] . "<br><br>";
        
        // Testar algumas senhas
        $senhas_testar = ['senha_aluno', 'senha_errada', '123456'];
        
        foreach ($senhas_testar as $senha) {
            $resultado = password_verify($senha, $usuario['senha_hash']) ? '✓ CORRETO' : '✗ INCORRETO';
            echo "Senha '$senha': $resultado<br>";
        }
    } else {
        echo "Usuário não encontrado.";
    }
    
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
?>