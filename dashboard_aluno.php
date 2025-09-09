<?php
// dashboard_aluno.php
session_start();

// Verificar se o usuário está logado e é aluno
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'aluno') {
    header('Location: index.html?erro=acesso_negado');
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Aluno - Sistema de Questões</title>
    <link rel="stylesheet" href="estilos/estilo_dashboard_aluno.css">
</head>
<body>
    <div class="header">
        <h1>Sistema de Questões - Painel do Aluno</h1>
        <div class="user-info">
            <span><?php echo htmlspecialchars($_SESSION['usuario_email']); ?></span>
            <button class="logout-btn" onclick="window.location.href='logout.php'">Sair</button>
        </div>
    </div>
    
    <div class="container">
        <div class="welcome-banner">
            <h2>Bem-vindo, <?php echo htmlspecialchars($_SESSION['usuario_nome']); ?>!</h2>
            <p>Aqui você pode acessar simulados, praticar questões e acompanhar seu desempenho.</p>
        </div>
        
        <div class="dashboard-grid">
            <div class="card">
                <h3>Simulados Disponíveis</h3>
                <ul>
                    <li><a href="#">Simulado de Matemática</a></li>
                    <li><a href="#">Simulado de Português</a></li>
                    <li><a href="#">Simulado de Ciências</a></li>
                    <li><a href="#">Ver todos os simulados</a></li>
                </ul>
            </div>
            
            <div class="card">
                <h3>Praticar Questões</h3>
                <ul>
                    <li><a href="#">Questões de Matemática</a></li>
                    <li><a href="#">Questões de Português</a></li>
                    <li><a href="#">Questões de Ciências</a></li>
                    <li><a href="#">Questões aleatórias</a></li>
                </ul>
            </div>
            
            <div class="card">
                <h3>Meu Desempenho</h3>
                <ul>
                    <li><a href="#">Histórico de simulados</a></li>
                    <li><a href="#">Estatísticas por matéria</a></li>
                    <li><a href="#">Comparativo com a turma</a></li>
                    <li><a href="#">Áreas para melhorar</a></li>
                </ul>
            </div>
            
            <div class="card">
                <h3>Configurações</h3>
                <ul>
                    <li><a href="#">Meu perfil</a></li>
                    <li><a href="#">Preferências de estudo</a></li>
                    <li><a href="#">Notificações</a></li>
                    <li><a href="#">Ajuda e suporte</a></li>
                </ul>
            </div>
        </div>
        
        <div class="card progress-card">
            <h3>Meu Progresso</h3>
            <div class="progress-container">
                <div class="progress-item">
                    <p>Matemática</p>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: 75%"></div>
                    </div>
                    <span class="progress-value">75%</span>
                </div>
                
                <div class="progress-item">
                    <p>Português</p>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: 60%"></div>
                    </div>
                    <span class="progress-value">60%</span>
                </div>
                
                <div class="progress-item">
                    <p>Ciências</p>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: 85%"></div>
                    </div>
                    <span class="progress-value">85%</span>
                </div>
            </div>
            
            <div class="stats">
                <div class="stat">
                    <div class="stat-number">24</div>
                    <div class="stat-label">Simulados Feitos</div>
                </div>
                <div class="stat">
                    <div class="stat-number">78%</div>
                    <div class="stat-label">Acerto Médio</div>
                </div>
                <div class="stat">
                    <div class="stat-number">15</div>
                    <div class="stat-label">Dias Consecutivos</div>
                </div>
            </div>
        </div>
    </div>

    <script src="js/dashboard.js"></script>
</body>
</html>