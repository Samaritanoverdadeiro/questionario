document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            // Validação básica no frontend
            const email = document.getElementById('email').value;
            const senha = document.getElementById('senha').value;
            
            // Validação básica
            if (!email || !senha) {
                e.preventDefault();
                alert('Por favor, preencha todos os campos.');
                return;
            }
            
            // Validação de formato de email
            if (!validarEmail(email)) {
                e.preventDefault();
                alert('Por favor, insira um e-mail válido.');
                return;
            }
            
            // Se passou nas validações, o formulário será enviado
            // A autenticação real acontece no servidor (autenticar.php)
        });
    }
    
    // Função para validar formato de e-mail
    function validarEmail(email) {
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regex.test(email);
    }
});