<?php
// autenticar.php
session_start();
require_once 'config.php';

// Ativar exibição de erros para debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $senha = $_POST['senha'];
    
    if (!empty($email) && !empty($senha)) {
        try {
            // Buscar usuário no banco de dados
            $stmt = $pdo->prepare("SELECT id, nome, email, senha_hash, tipo, instituicao_id FROM usuarios WHERE email = :email AND ativo = 1");
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // VERIFICAÇÃO CORRIGIDA: Usar hash_equals com SHA-256
                $senha_hash = hash('sha256', $senha);
                
                if (hash_equals($usuario['senha_hash'], $senha_hash)) {
                    // Login bem-sucedido
                    $_SESSION['usuario_id'] = $usuario['id'];
                    $_SESSION['usuario_nome'] = $usuario['nome'];
                    $_SESSION['usuario_email'] = $usuario['email'];
                    $_SESSION['usuario_tipo'] = $usuario['tipo'];
                    $_SESSION['instituicao_id'] = $usuario['instituicao_id'];
                    
                    // Redirecionar conforme o tipo de usuário
                    if ($usuario['tipo'] === 'professor') {
                        header('Location: dashboard_professor.php');
                    } elseif ($usuario['tipo'] === 'aluno') {
                        header('Location: dashboard_aluno.php');
                    } elseif ($usuario['tipo'] === 'admin') {
                        header('Location: dashboard_admin.php');
                    } else {
                        header('Location: index.html?erro=tipo_invalido');
                    }
                    exit();
                } else {
                    // Senha incorreta
                    header('Location: index.html?erro=credenciais_invalidas');
                    exit();
                }
            } else {
                // Usuário não encontrado
                header('Location: index.html?erro=credenciais_invalidas');
                exit();
            }
        } catch (PDOException $e) {
            error_log("Erro de banco de dados: " . $e->getMessage());
            header('Location: index.html?erro=erro_banco');
            exit();
        }
    } else {
        // Campos vazios
        header('Location: index.html?erro=campos_vazios');
        exit();
    }
} else {
    // Método não permitido
    header('Location: index.html');
    exit();
}
?>