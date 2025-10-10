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

    if ($action === 'add_aluno') {
        $nome = $_POST['nome'];
        $email = $_POST['email'];
        $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
        $instituicao_id = $_POST['instituicao_id'];

        $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha_hash, tipo, instituicao_id) VALUES (?, ?, ?, 'aluno', ?)");
        $stmt->execute([$nome, $email, $senha, $instituicao_id]);

        $_SESSION['mensagem'] = "Aluno cadastrado com sucesso!";
        $_SESSION['tipo_mensagem'] = "sucesso";
        header("Location: alunos.php");
        exit;
    } elseif ($action === 'toggle_aluno_status') {
        $id = $_POST['id'];
        $novo_status = $_POST['novo_status'];

        $stmt = $pdo->prepare("UPDATE usuarios SET ativo = ? WHERE id = ?");
        $stmt->execute([$novo_status, $id]);

        $_SESSION['mensagem'] = $novo_status ? "Aluno ativado com sucesso!" : "Aluno desativado com sucesso!";
        $_SESSION['tipo_mensagem'] = "sucesso";
        
        header("Location: alunos.php");
        exit;
    } elseif ($action === 'delete_aluno') {
        $id = $_POST['id'];

        $stmt = $pdo->prepare("UPDATE usuarios SET ativo = 0 WHERE id = ?");
        $stmt->execute([$id]);

        $_SESSION['mensagem'] = "Aluno desativado com sucesso!";
        $_SESSION['tipo_mensagem'] = "sucesso";
        
        header("Location: alunos.php");
        exit;
    }
}

// Buscar alunos
$stmt_alunos = $pdo->query("
    SELECT u.*, i.nome as instituicao_nome 
    FROM usuarios u 
    LEFT JOIN instituicoes i ON u.instituicao_id = i.id 
    WHERE u.tipo = 'aluno'
    ORDER BY u.ativo DESC, u.nome ASC
");
$alunos = $stmt_alunos->fetchAll(PDO::FETCH_ASSOC);

// Contadores
$count_alunos_ativos = $pdo->query("SELECT COUNT(*) as total FROM usuarios WHERE tipo = 'aluno' AND ativo = 1")->fetch()['total'];
$count_alunos_inativos = $pdo->query("SELECT COUNT(*) as total FROM usuarios WHERE tipo = 'aluno' AND ativo = 0")->fetch()['total'];
$count_alunos_total = $count_alunos_ativos + $count_alunos_inativos;

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
    <title>Gerenciar Alunos - Banco de Questões</title>
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
            <div class="menu-item active">
                <i class="fas fa-user-graduate"></i>
                <span>Alunos</span>
            </div>
            <div class="menu-item">
                <a href="professores.php" style="color: inherit; text-decoration: none;">
                    <i class="fas fa-chalkboard-teacher"></i>
                    <span>Professores</span>
                </a>
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
            <h2><i class="fas fa-user-graduate"></i> Gerenciar Alunos</h2>
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
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div class="stat-text">
                    <h3><?php echo $count_alunos_total; ?></h3>
                    <p>Total de Alunos</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background-color: rgba(76, 201, 240, 0.1); color: var(--success);">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-text">
                    <h3><?php echo $count_alunos_ativos; ?></h3>
                    <p>Alunos Ativos</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background-color: rgba(248, 150, 30, 0.1); color: var(--warning);">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <div class="stat-text">
                    <h3><?php echo $count_alunos_inativos; ?></h3>
                    <p>Alunos Inativos</p>
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
                <h3><i class="fas fa-plus-circle"></i> Cadastrar Novo Aluno</h3>
            </div>
            <form action="alunos.php" method="POST">
                <input type="hidden" name="action" value="add_aluno">
                <div class="form-row">
                    <div class="form-group">
                        <label for="aluno_nome">Nome Completo *</label>
                        <input type="text" class="form-control" id="aluno_nome" name="nome" required>
                    </div>
                    <div class="form-group">
                        <label for="aluno_email">E-mail *</label>
                        <input type="email" class="form-control" id="aluno_email" name="email" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="aluno_senha">Senha *</label>
                        <input type="password" class="form-control" id="aluno_senha" name="senha" required>
                    </div>
                    <div class="form-group">
                        <label for="aluno_instituicao">Instituição *</label>
                        <select class="form-control" id="aluno_instituicao" name="instituicao_id" required>
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
                    <i class="fas fa-save"></i> Cadastrar Aluno
                </button>
            </form>
        </div>

        <!-- Lista de alunos -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-list"></i> Alunos Cadastrados</h3>
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
                        <?php if (count($alunos) > 0): ?>
                            <?php foreach ($alunos as $aluno): ?>
                                <tr>
                                    <td><?php echo $aluno['id']; ?></td>
                                    <td><?php echo htmlspecialchars($aluno['nome']); ?></td>
                                    <td><?php echo htmlspecialchars($aluno['email']); ?></td>
                                    <td><?php echo htmlspecialchars($aluno['instituicao_nome'] ?? 'N/A'); ?></td>
                                    <td>
                                        <span class="badge <?php echo $aluno['ativo'] ? 'badge-success' : 'badge-warning'; ?>">
                                            <?php echo $aluno['ativo'] ? 'Ativo' : 'Inativo'; ?>
                                        </span>
                                    </td>
                                    <td class="actions">
                                        <a href="../editar/editar_aluno.php?id=<?php echo $aluno['id']; ?>" class="btn btn-primary" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        <!-- Botão para alternar status -->
                                        <form action="alunos.php" method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="toggle_aluno_status">
                                            <input type="hidden" name="id" value="<?php echo $aluno['id']; ?>">
                                            <input type="hidden" name="novo_status" value="<?php echo $aluno['ativo'] ? 0 : 1; ?>">
                                            <button type="submit" class="btn <?php echo $aluno['ativo'] ? 'btn-warning' : 'btn-success'; ?>"
                                                title="<?php echo $aluno['ativo'] ? 'Desativar' : 'Ativar'; ?>"
                                                onclick="return confirm('<?php echo $aluno['ativo'] ?
                                                                                'Tem certeza que deseja desativar este aluno?' :
                                                                                'Tem certeza que deseja ativar este aluno?'; ?>')">
                                                <i class="fas <?php echo $aluno['ativo'] ? 'fa-times' : 'fa-check'; ?>"></i>
                                            </button>
                                        </form>

                                        <!-- Botão de deletar (apenas para alunos inativos) -->
                                        <?php if (!$aluno['ativo']): ?>
                                            <form action="alunos.php" method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="delete_aluno">
                                                <input type="hidden" name="id" value="<?php echo $aluno['id']; ?>">
                                                <button type="submit" class="btn btn-danger" title="Excluir permanentemente"
                                                    onclick="return confirm('Tem certeza que deseja excluir permanentemente este aluno?\\n\\nEsta ação não pode ser desfeita!')">
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
                                    <i class="fas fa-user-graduate" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                                    <p>Nenhum aluno cadastrado.</p>
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
        document.getElementById('aluno_email').addEventListener('blur', function(e) {
            const email = e.target.value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (email && !emailRegex.test(email)) {
                alert('Por favor, insira um email válido.');
                e.target.focus();
            }
        });

        // Validação de senha
        document.getElementById('aluno_senha').addEventListener('input', function(e) {
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