<?php require_once '../login/auth.php';

// Verificar se o usuário está logado como admin
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Conexão com o banco de dados
$host = 'localhost';
$dbname = 'banco_questoes';
$username = 'root';
$password = 'senacrs';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}

// Buscar instituições ativas para o select
$stmt_instituicoes = $pdo->query("SELECT * FROM instituicoes WHERE ativo = 1 ORDER BY nome ASC");
$instituicoes = $stmt_instituicoes->fetchAll(PDO::FETCH_ASSOC);

// Processar formulários
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_professor') {
        $nome = $_POST['nome'];
        $email = $_POST['email'];
        $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
        $instituicao_id = $_POST['instituicao_id'];

        $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha_hash, tipo, instituicao_id) VALUES (?, ?, ?, 'professor', ?)");
        $stmt->execute([$nome, $email, $senha, $instituicao_id]);

        $_SESSION['mensagem'] = "Professor cadastrado com sucesso!";
        $_SESSION['tipo_mensagem'] = "sucesso";
        header("Location: professores.php");
        exit;
    } elseif ($action === 'toggle_professor_status') {
        $id = $_POST['id'];
        $novo_status = $_POST['novo_status'];

        $stmt = $pdo->prepare("UPDATE usuarios SET ativo = ? WHERE id = ?");
        $stmt->execute([$novo_status, $id]);

        $_SESSION['mensagem'] = $novo_status ? "Professor ativado com sucesso!" : "Professor desativado com sucesso!";
        $_SESSION['tipo_mensagem'] = "sucesso";
        
        header("Location: professores.php");
        exit;
    } elseif ($action === 'delete_professor') {
        $id = $_POST['id'];

        $stmt = $pdo->prepare("UPDATE usuarios SET ativo = 0 WHERE id = ?");
        $stmt->execute([$id]);

        $_SESSION['mensagem'] = "Professor desativado com sucesso!";
        $_SESSION['tipo_mensagem'] = "sucesso";
        
        header("Location: professores.php");
        exit;
    }
}

// Buscar professores
$stmt_professores = $pdo->query("
    SELECT u.*, i.nome as instituicao_nome 
    FROM usuarios u 
    LEFT JOIN instituicoes i ON u.instituicao_id = i.id 
    WHERE u.tipo = 'professor'
    ORDER BY u.ativo DESC, u.nome ASC
");
$professores = $stmt_professores->fetchAll(PDO::FETCH_ASSOC);

// Contadores
$count_professores_ativos = $pdo->query("SELECT COUNT(*) as total FROM usuarios WHERE tipo = 'professor' AND ativo = 1")->fetch()['total'];
$count_professores_inativos = $pdo->query("SELECT COUNT(*) as total FROM usuarios WHERE tipo = 'professor' AND ativo = 0")->fetch()['total'];
$count_professores_total = $count_professores_ativos + $count_professores_inativos;

// Processar logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: ../login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Professores - Banco de Questões</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../estilos/estilo_dashboard_admin.css">
    <link rel="stylesheet" href="../estilos/estilo_dashboard_tabs.css">
    <link rel="stylesheet" href="../estilos/estilo_sidebar_instituicoes.css">
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">
            <h1>Banco de Questões</h1>
            <p>Painel Administrativo</p>
        </div>
        <div class="menu">
            <div class="menu-item">
                <a href="../dashboard_admin.php" style="color: inherit; text-decoration: none;">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
            </div>
            <div class="menu-item">
                <a href="instituicoes.php" style="color: inherit; text-decoration: none;">
                    <i class="fas fa-building"></i>
                    <span>Instituições</span>
                </a>
            </div>
            <div class="menu-item">
                <a href="alunos.php" style="color: inherit; text-decoration: none;">
                    <i class="fas fa-user-graduate"></i>
                    <span>Alunos</span>
                </a>
            </div>
            <div class="menu-item active">
                <i class="fas fa-chalkboard-teacher"></i>
                <span>Professores</span>
            </div>
            <div class="menu-item">
                <a href="questoes.php" style="color: inherit; text-decoration: none;">
                    <i class="fas fa-book"></i>
                    <span>Questões</span>
                </a>
            </div>
            <div class="menu-item">
                <a href="provas.php" style="color: inherit; text-decoration: none;">
                    <i class="fas fa-clipboard-list"></i>
                    <span>Provas</span>
                </a>
            </div>
            <div class="menu-item">
                <a href="relatorios.php" style="color: inherit; text-decoration: none;">
                    <i class="fas fa-chart-bar"></i>
                    <span>Relatórios</span>
                </a>
            </div>
            <div class="menu-item">
                <a href="configuracoes.php" style="color: inherit; text-decoration: none;">
                    <i class="fas fa-cog"></i>
                    <span>Configurações</span>
                </a>
            </div>
        </div>
        <div class="user-info">
            <div class="user-avatar">A</div>
            <div>
                <div>Admin</div>
                <small><?php echo $_SESSION['usuario_email'] ?? 'admin@bancoquestoes.com'; ?></small>
            </div>
            <a href="?logout=true" class="logout-btn" style="margin-left: auto; padding: 8px 12px;">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="content-header">
            <h2><i class="fas fa-chalkboard-teacher"></i> Gerenciar Professores</h2>
            <div class="header-actions">
                <button class="btn btn-primary" onclick="window.location.reload()">
                    <i class="fas fa-sync-alt"></i> Atualizar
                </button>
                <a href="../dashboard_admin.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Voltar ao Dashboard
                </a>
            </div>
        </div>

        <!-- Statistics -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-icon" style="background-color: rgba(67, 97, 238, 0.1); color: var(--primary);">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
                <div class="stat-text">
                    <h3><?php echo $count_professores_total; ?></h3>
                    <p>Total de Professores</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background-color: rgba(76, 201, 240, 0.1); color: var(--success);">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-text">
                    <h3><?php echo $count_professores_ativos; ?></h3>
                    <p>Professores Ativos</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background-color: rgba(248, 150, 30, 0.1); color: var(--warning);">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <div class="stat-text">
                    <h3><?php echo $count_professores_inativos; ?></h3>
                    <p>Professores Inativos</p>
                </div>
            </div>
        </div>

        <!-- Mensagens de feedback -->
        <?php if (isset($_SESSION['mensagem'])): ?>
            <div class="alert alert-<?php echo $_SESSION['tipo_mensagem'] === 'erro' ? 'danger' : 'success'; ?>">
                <?php echo $_SESSION['mensagem']; ?>
                <?php unset($_SESSION['mensagem']);
                unset($_SESSION['tipo_mensagem']); ?>
            </div>
        <?php endif; ?>

        <!-- Formulário de cadastro -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-plus-circle"></i> Cadastrar Novo Professor</h3>
            </div>
            <form action="professores.php" method="POST">
                <input type="hidden" name="action" value="add_professor">
                <div class="form-row">
                    <div class="form-group">
                        <label for="professor_nome">Nome Completo *</label>
                        <input type="text" class="form-control" id="professor_nome" name="nome" required>
                    </div>
                    <div class="form-group">
                        <label for="professor_email">E-mail *</label>
                        <input type="email" class="form-control" id="professor_email" name="email" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="professor_senha">Senha *</label>
                        <input type="password" class="form-control" id="professor_senha" name="senha" required>
                    </div>
                    <div class="form-group">
                        <label for="professor_instituicao">Instituição *</label>
                        <select class="form-control" id="professor_instituicao" name="instituicao_id" required>
                            <option value="">Selecione uma instituição</option>
                            <?php foreach ($instituicoes as $instituicao): ?>
                                <option value="<?php echo $instituicao['id']; ?>">
                                    <?php echo htmlspecialchars($instituicao['nome']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Cadastrar Professor
                </button>
            </form>
        </div>

        <!-- Lista de professores -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-list"></i> Professores Cadastrados</h3>
            </div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>E-mail</th>
                            <th>Instituição</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($professores) > 0): ?>
                            <?php foreach ($professores as $professor): ?>
                                <tr>
                                    <td><?php echo $professor['id']; ?></td>
                                    <td><?php echo htmlspecialchars($professor['nome']); ?></td>
                                    <td><?php echo htmlspecialchars($professor['email']); ?></td>
                                    <td><?php echo htmlspecialchars($professor['instituicao_nome'] ?? 'N/A'); ?></td>
                                    <td>
                                        <span class="badge <?php echo $professor['ativo'] ? 'badge-success' : 'badge-warning'; ?>">
                                            <?php echo $professor['ativo'] ? 'Ativo' : 'Inativo'; ?>
                                        </span>
                                    </td>
                                    <td class="actions">
                                        <a href="../editar/editar_professor.php?id=<?php echo $professor['id']; ?>" class="btn btn-primary" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        <!-- Botão para alternar status -->
                                        <form action="professores.php" method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="toggle_professor_status">
                                            <input type="hidden" name="id" value="<?php echo $professor['id']; ?>">
                                            <input type="hidden" name="novo_status" value="<?php echo $professor['ativo'] ? 0 : 1; ?>">
                                            <button type="submit" class="btn <?php echo $professor['ativo'] ? 'btn-warning' : 'btn-success'; ?>"
                                                title="<?php echo $professor['ativo'] ? 'Desativar' : 'Ativar'; ?>"
                                                onclick="return confirm('<?php echo $professor['ativo'] ?
                                                                                'Tem certeza que deseja desativar este professor?\\n\\n• Ele será mantido vinculado à instituição\\n• Será reativado automaticamente se a instituição for reativada' :
                                                                                'Tem certeza que deseja ativar este professor?'; ?>')">
                                                <i class="fas <?php echo $professor['ativo'] ? 'fa-times' : 'fa-check'; ?>"></i>
                                            </button>
                                        </form>

                                        <!-- Botão de deletar (apenas para professores inativos) -->
                                        <?php if (!$professor['ativo']): ?>
                                            <form action="professores.php" method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="delete_professor">
                                                <input type="hidden" name="id" value="<?php echo $professor['id']; ?>">
                                                <button type="submit" class="btn btn-danger" title="Excluir permanentemente"
                                                    onclick="return confirm('Tem certeza que deseja excluir permanentemente este professor?\\n\\nEsta ação não pode ser desfeita!')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 2rem;">
                                    <i class="fas fa-chalkboard-teacher" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                                    <p>Nenhum professor cadastrado.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Validação de email
        document.getElementById('professor_email').addEventListener('blur', function(e) {
            const email = e.target.value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (email && !emailRegex.test(email)) {
                alert('Por favor, insira um email válido.');
                e.target.focus();
            }
        });

        // Validação de senha
        document.getElementById('professor_senha').addEventListener('input', function(e) {
            const senha = e.target.value;
            if (senha.length > 0 && senha.length < 6) {
                e.target.setCustomValidity('A senha deve ter pelo menos 6 caracteres.');
            } else {
                e.target.setCustomValidity('');
            }
        });
    </script>
</body>

</html>