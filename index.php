<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Questões</title>
    <link rel="stylesheet" href="estilos/estilo_index.css">
</head>
<body>
    <div class="container">
        <div class="login-card">
            <div class="logo">
                <h1><img src="imagens/logo_feco.png" width="300"></h1>
                
            </div>
            
            <!-- Mensagens de erro -->
            <?php if (isset($_GET['erro'])): ?>
            <div class="alert alert-erro">
                <?php
                $erro = $_GET['erro'];
                switch ($erro) {
                    case 'credenciais_invalidas':
                        echo 'E-mail ou senha incorretos.';
                        break;
                    case 'campos_vazios':
                        echo 'Por favor, preencha todos os campos.';
                        break;
                    case 'erro_banco':
                        echo 'Erro no sistema. Tente novamente mais tarde.';
                        break;
                    case 'tipo_invalido':
                        echo 'Tipo de usuário não reconhecido.';
                        break;
                    case 'acesso_negado':
                        echo 'Acesso não autorizado. Faça login primeiro.';
                        break;
                    default:
                        echo 'Erro desconhecido.';
                }
                ?>
            </div>
            <?php endif; ?>
            
            <form id="loginForm" action="/login/autenticar.php" method="POST">
                <div class="form-group">
                    <label for="email">E-mail</label>
                    <input type="email" id="email" name="email" required autocomplete="email" placeholder="seu.email@exemplo.com">
                </div>
                
                <div class="form-group">
                    <label for="senha">Senha</label>
                    <input type="password" id="senha" name="senha" required autocomplete="current-password" placeholder="Sua senha">
                </div>
                
                <button type="submit" class="btn-login">Entrar no Sistema</button>
            </form>
            
            <div class="login-help">
                <h3>Contas para Teste:</h3>
                <p><strong>Aluno:</strong> joao.aluno@email.com / senha: senha123</p>
                <p><strong>Professor:</strong> maria.professor@email.com / senha: senha456</p>
                <p><strong>admin:</strong> admin@escola.com / senha: admin123</p>
                <p><em>Nota: Estas são contas de exemplo. Use as credenciais reais do seu banco de dados.</em></p>
            </div>
        </div>
    </div>

    <script src="js/script.js"></script>
</body>
</html>