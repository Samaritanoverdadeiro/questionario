<?php
// debug_login.php - Ferramenta de diagnóstico
require_once 'config.php';

echo "<h2>Debug do Sistema de Login</h2>";

// Verificar se o config.php está funcionando
try {
    $pdo->query("SELECT 1");
    echo "<p style='color: green;'>✓ Conexão com o banco de dados OK</p>";
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Erro na conexão: " . $e->getMessage() . "</p>";
}

// Listar usuários na base
echo "<h3>Usuários no banco de dados:</h3>";
try {
    $stmt = $pdo->query("SELECT id, nome, email, tipo, ativo FROM usuarios");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($usuarios) > 0) {
        echo "<table border='1' cellpadding='8'>";
        echo "<tr><th>ID</th><th>Nome</th><th>Email</th><th>Tipo</th><th>Ativo</th></tr>";
        foreach ($usuarios as $usuario) {
            echo "<tr>";
            echo "<td>" . $usuario['id'] . "</td>";
            echo "<td>" . $usuario['nome'] . "</td>";
            echo "<td>" . $usuario['email'] . "</td>";
            echo "<td>" . $usuario['tipo'] . "</td>";
            echo "<td>" . $usuario['ativo'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>Nenhum usuário encontrado no banco de dados.</p>";
    }
} catch (PDOException $e) {
    echo "<p style='color: red;'>Erro ao buscar usuários: " . $e->getMessage() . "</p>";
}

// Testar hash de uma senha
echo "<h3>Teste de Hash de Senha:</h3>";
echo "<form method='post'>";
echo "Email: <input type='email' name='test_email' required><br>";
echo "Senha: <input type='password' name='test_senha' required><br>";
echo "<input type='submit' value='Testar Login'>";
echo "</form>";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_email'])) {
    $email = $_POST['test_email'];
    $senha = $_POST['test_senha'];
    
    try {
        $stmt = $pdo->prepare("SELECT id, nome, email, senha_hash, tipo FROM usuarios WHERE email = :email");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo "<h4>Resultado do teste para: " . $email . "</h4>";
            echo "<p>Hash no BD: " . $usuario['senha_hash'] . "</p>";
            
            $senha_verificada = password_verify($senha, $usuario['senha_hash']);
            echo "<p>Senha verificada: " . ($senha_verificada ? "<span style='color: green;'>✓ CORRETA</span>" : "<span style='color: red;'>✗ INCORRETA</span>") . "</p>";
            
            if ($senha_verificada) {
                echo "<p>Tipo de usuário: " . $usuario['tipo'] . "</p>";
                echo "<p style='color: green;'>Login deveria funcionar!</p>";
            } else {
                echo "<p style='color: red;'>Senha não confere com o hash armazenado.</p>";
            }
        } else {
            echo "<p style='color: red;'>Usuário não encontrado: " . $email . "</p>";
        }
    } catch (PDOException $e) {
        echo "<p style='color: red;'>Erro: " . $e->getMessage() . "</p>";
    }
}
?>