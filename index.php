<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Banco de Questões</title>
    <link rel="stylesheet" href="estilos/estilo_index.css">
</head>
<body>
    <div class="container">
        <div class="login-card">
            <div class="logo">
                <h1>Banco de Questões</h1>
                <p>Sistema Acadêmico</p>
            </div>
            
            <form id="loginForm" action="autenticar.php" method="POST">
                <div class="form-group">
                    <label for="email">E-mail</label>
                    <input type="email" id="email" name="email" required autocomplete="email">
                </div>
                
                <div class="form-group">
                    <label for="senha">Senha</label>
                    <input type="password" id="senha" name="senha" required autocomplete="current-password">
                </div>
                
                <button type="submit" class="btn-login">Entrar</button>
                
                <div class="login-links">
                    <a href="#">Esqueci minha senha</a>
                </div>
            </form>
            
            
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value;
            
            // Verificação simples de email para demonstração
            // Em um sistema real, isso seria validado no backend
            if (email.includes('@admin.')) {
                alert('Você está entrando como administrador');
            } else if (email.includes('@prof.')) {
                alert('Você está entrando como professor');
            } else {
                alert('Você está entrando como aluno');
            }
            
            // Em um sistema real, não faríamos esta verificação no frontend
            // Esta é apenas uma demonstração visual
        });
    </script>
</body>
</html>