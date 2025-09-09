<?php
// dashboard_professor.php
session_start();

// Verificar se o usuário está logado e é professor
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'professor') {
    header('Location: index.html?erro=acesso_negado');
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Professor - Sistema de Questões</title>
    <link rel="stylesheet" href="estilos/estilo_dashboard_professor.css">
</head>
<body>
    <div class="header">
        <h1>Sistema de Questões - Painel do Professor</h1>
        <div class="user-info">
            <span><?php echo htmlspecialchars($_SESSION['usuario_email']); ?></span>
            <button class="logout-btn" onclick="window.location.href='logout.php'">Sair</button>
        </div>
    </div>
    
    <div class="container">
        <div class="welcome-banner">
            <h2>Bem-vindo, <?php echo htmlspecialchars($_SESSION['usuario_nome']); ?>!</h2>
            <p>Você tem acesso completo ao sistema de gerenciamento de questões.</p>
        </div>
        
        <div class="dashboard-grid">
            <div class="card">
                <h3>Gerenciar Questões</h3>
                <ul>
                    <li><a href="#">Criar nova questão</a></li>
                    <li><a href="#">Visualizar todas as questões</a></li>
                    <li><a href="#">Editar questões existentes</a></li>
                    <li><a href="#">Importar questões</a></li>
                </ul>
            </div>
            
            <div class="card">
                <h3>Simulados e Provas</h3>
                <ul>
                    <li><a href="#">Criar novo simulado</a></li>
                    <li><a href="#">Gerenciar simulados</a></li>
                    <li><a href="#">Visualizar resultados</a></li>
                    <li><a href="#">Configurações de avaliação</a></li>
                </ul>
            </div>
            
            <div class="card">
                <h3>Relatórios e Estatísticas</h3>
                <ul>
                    <li><a href="#">Desempenho dos alunos</a></li>
                    <li><a href="#">Questões mais utilizadas</a></li>
                    <li><a href="#">Relatório de acesso</a></li>
                    <li><a href="#">Exportar dados</a></li>
                </ul>
            </div>
            
            <div class="card">
                <h3>Configurações</h3>
                <ul>
                    <li><a href="#">Meu perfil</a></li>
                    <li><a href="#">Preferências do sistema</a></li>
                    <li><a href="#">Gerenciar turmas</a></li>
                    <li><a href="#">Ajuda e suporte</a></li>
                </ul>
            </div>
        </div>
        
        <div class="card" style="margin-top: 30px;">
            <h3>Estatísticas do Sistema</h3>
            <div class="stats">
                <div class="stat">
                    <div class="stat-number">158</div>
                    <div class="stat-label">Questões</div>
                </div>
                <div class="stat">
                    <div class="stat-number">24</div>
                    <div class="stat-label">Simulados</div>
                </div>
                <div class="stat">
                    <div class="stat-number">327</div>
                    <div class="stat-label">Alunos</div>
                </div>
                <div class="stat">
                    <div class="stat-number">92%</div>
                    <div class="stat-label">Aproveitamento</div>
                </div>
            </div>
        </div>
    </div>

    <script src="js/dashboard.js"></script>
</body>
</html>