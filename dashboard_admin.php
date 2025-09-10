<?php
// Iniciar sessão
session_start();

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

// Processar logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit;
}

// Buscar dados do banco
// Instituições
$stmt_instituicoes = $pdo->query("SELECT * FROM instituicoes");
$instituicoes = $stmt_instituicoes->fetchAll(PDO::FETCH_ASSOC);

// Alunos
$stmt_alunos = $pdo->query("
    SELECT u.*, i.nome as instituicao_nome 
    FROM usuarios u 
    LEFT JOIN instituicoes i ON u.instituicao_id = i.id 
    WHERE u.tipo = 'aluno'
");
$alunos = $stmt_alunos->fetchAll(PDO::FETCH_ASSOC);

// Professores
$stmt_professores = $pdo->query("
    SELECT u.*, i.nome as instituicao_nome 
    FROM usuarios u 
    LEFT JOIN instituicoes i ON u.instituicao_id = i.id 
    WHERE u.tipo = 'professor'
");
$professores = $stmt_professores->fetchAll(PDO::FETCH_ASSOC);

// Contadores para o dashboard
$count_instituicoes = $pdo->query("SELECT COUNT(*) as total FROM instituicoes")->fetch()['total'];
$count_alunos = $pdo->query("SELECT COUNT(*) as total FROM usuarios WHERE tipo = 'aluno'")->fetch()['total'];
$count_professores = $pdo->query("SELECT COUNT(*) as total FROM usuarios WHERE tipo = 'professor'")->fetch()['total'];
$count_questoes = $pdo->query("SELECT COUNT(*) as total FROM questoes")->fetch()['total'];

// Processar formulários
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_instituicao') {
        $nome = $_POST['nome'];
        $cnpj = $_POST['cnpj'];
        $endereco = $_POST['endereco'];
        $telefone = $_POST['telefone'];
        
        $stmt = $pdo->prepare("INSERT INTO instituicoes (nome, cnpj, endereco, telefone) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nome, $cnpj, $endereco, $telefone]);
        
        header("Location: dashboard_admin.php");
        exit;
    }
    elseif ($action === 'add_aluno') {
        $nome = $_POST['nome'];
        $email = $_POST['email'];
        $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
        $instituicao_id = $_POST['instituicao_id'];
        
        $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha_hash, tipo, instituicao_id) VALUES (?, ?, ?, 'aluno', ?)");
        $stmt->execute([$nome, $email, $senha, $instituicao_id]);
        
        header("Location: dashboard_admin.php");
        exit;
    }
    elseif ($action === 'add_professor') {
        $nome = $_POST['nome'];
        $email = $_POST['email'];
        $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
        $instituicao_id = $_POST['instituicao_id'];
        
        $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha_hash, tipo, instituicao_id) VALUES (?, ?, ?, 'professor', ?)");
        $stmt->execute([$nome, $email, $senha, $instituicao_id]);
        
        header("Location: dashboard_admin.php");
        exit;
    }
    elseif ($action === 'delete_user') {
        $id = $_POST['id'];
        $stmt = $pdo->prepare("UPDATE usuarios SET ativo = 0 WHERE id = ?");
        $stmt->execute([$id]);
        
        header("Location: dashboard_admin.php");
        exit;
    }
    elseif ($action === 'delete_instituicao') {
        $id = $_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM instituicoes WHERE id = ?");
        $stmt->execute([$id]);
        
        header("Location: dashboard_admin.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Banco de Questões</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="estilos/estilo_dashboard_admin.css">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">
            <h1>Banco de Questões</h1>
            <p>Painel Administrativo</p>
        </div>
        <div class="menu">
            <div class="menu-item active">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </div>
            <div class="menu-item">
                <i class="fas fa-building"></i>
                <span>Instituições</span>
            </div>
            <div class="menu-item">
                <i class="fas fa-user-graduate"></i>
                <span>Alunos</span>
            </div>
            <div class="menu-item">
                <i class="fas fa-chalkboard-teacher"></i>
                <span>Professores</span>
            </div>
            <div class="menu-item">
                <i class="fas fa-book"></i>
                <span>Questões</span>
            </div>
            <div class="menu-item">
                <i class="fas fa-clipboard-list"></i>
                <span>Provas</span>
            </div>
            <div class="menu-item">
                <i class="fas fa-cog"></i>
                <span>Configurações</span>
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
        <div class="header">
            <h2>Dashboard Administrativo</h2>
            <div class="header-actions">
                <button class="btn btn-primary" onclick="window.location.reload()">
                    <i class="fas fa-sync-alt"></i> Atualizar
                </button>
                <a href="?logout=true" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Sair
                </a>
            </div>
        </div>

        <!-- Statistics -->
        <div class="stats">
            <div class="stat-card">
                <div class="stat-icon" style="background-color: rgba(67, 97, 238, 0.1); color: var(--primary);">
                    <i class="fas fa-building"></i>
                </div>
                <div class="stat-text">
                    <h3><?php echo $count_instituicoes; ?></h3>
                    <p>Instituições</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background-color: rgba(76, 201, 240, 0.1); color: var(--success);">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div class="stat-text">
                    <h3><?php echo $count_alunos; ?></h3>
                    <p>Alunos</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background-color: rgba(248, 150, 30, 0.1); color: var(--warning);">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
                <div class="stat-text">
                    <h3><?php echo $count_professores; ?></h3>
                    <p>Professores</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background-color: rgba(247, 37, 133, 0.1); color: var(--danger);">
                    <i class="fas fa-book"></i>
                </div>
                <div class="stat-text">
                    <h3><?php echo $count_questoes; ?></h3>
                    <p>Questões</p>
                </div>
            </div>
        </div>

        <!-- Resto do conteúdo permanece igual -->
        <!-- Switch View Buttons -->
        <div class="switch-view">
            <button class="view-btn active" onclick="showTab('instituicoes')">Instituições</button>
            <button class="view-btn" onclick="showTab('alunos')">Alunos</button>
            <button class="view-btn" onclick="showTab('professores')">Professores</button>
        </div>

        <!-- Instituições -->
        <div id="instituicoes" class="tab-content active">
            <div class="card">
                <div class="card-header">
                    <h3>Cadastrar Nova Instituição</h3>
                </div>
                <form action="dashboard_admin.php" method="POST">
                    <input type="hidden" name="action" value="add_instituicao">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="instituicao_nome">Nome da Instituição</label>
                            <input type="text" class="form-control" id="instituicao_nome" name="nome" required>
                        </div>
                        <div class="form-group">
                            <label for="instituicao_cnpj">CNPJ</label>
                            <input type="text" class="form-control" id="instituicao_cnpj" name="cnpj" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="instituicao_endereco">Endereço</label>
                            <input type="text" class="form-control" id="instituicao_endereco" name="endereco" required>
                        </div>
                        <div class="form-group">
                            <label for="instituicao_telefone">Telefone</label>
                            <input type="text" class="form-control" id="instituicao_telefone" name="telefone" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Cadastrar Instituição</button>
                </form>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3>Instituições Cadastradas</h3>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>CNPJ</th>
                            <th>Endereço</th>
                            <th>Telefone</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($instituicoes as $instituicao): ?>
                        <tr>
                            <td><?php echo $instituicao['id']; ?></td>
                            <td><?php echo($instituicao['nome']); ?></td>
                            <td><?php echo($instituicao['cnpj']); ?></td>
                            <td><?php echo($instituicao['endereco']); ?></td>
                            <td><?php echo($instituicao['telefone']); ?></td>
                            <td class="actions">
                                <button class="btn btn-primary"><i class="fas fa-edit"></i></button>
                                <form action="dashboard_admin.php" method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="delete_instituicao">
                                    <input type="hidden" name="id" value="<?php echo $instituicao['id']; ?>">
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Tem certeza que deseja excluir esta instituição?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Alunos -->
        <div id="alunos" class="tab-content">
            <div class="card">
                <div class="card-header">
                    <h3>Cadastrar Novo Aluno</h3>
                </div>
                <form action="dashboard_admin.php" method="POST">
                    <input type="hidden" name="action" value="add_aluno">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="aluno_nome">Nome Completo</label>
                            <input type="text" class="form-control" id="aluno_nome" name="nome" required>
                        </div>
                        <div class="form-group">
                            <label for="aluno_email">E-mail</label>
                            <input type="email" class="form-control" id="aluno_email" name="email" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="aluno_senha">Senha</label>
                            <input type="password" class="form-control" id="aluno_senha" name="senha" required>
                        </div>
                        <div class="form-group">
                            <label for="aluno_instituicao">Instituição</label>
                            <select class="form-control" id="aluno_instituicao" name="instituicao_id" required>
                                <option value="">Selecione uma instituição</option>
                                <?php foreach ($instituicoes as $instituicao): ?>
                                <option value="<?php echo $instituicao['id']; ?>">
                                    <?php echo($instituicao['nome']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Cadastrar Aluno</button>
                </form>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3>Alunos Cadastrados</h3>
                </div>
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
                        <?php foreach ($alunos as $aluno): ?>
                        <tr>
                            <td><?php echo $aluno['id']; ?></td>
                            <td><?php echo($aluno['nome']); ?></td>
                            <td><?php echo($aluno['email']); ?></td>
                            <td><?php echo($aluno['instituicao_nome'] ?? 'N/A'); ?></td>
                            <td>
                                <span class="badge <?php echo $aluno['ativo'] ? 'badge-success' : 'badge-warning'; ?>">
                                    <?php echo $aluno['ativo'] ? 'Ativo' : 'Inativo'; ?>
                                </span>
                            </td>
                            <td class="actions">
                                <button class="btn btn-primary"><i class="fas fa-edit"></i></button>
                                <form action="dashboard_admin.php" method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="delete_user">
                                    <input type="hidden" name="id" value="<?php echo $aluno['id']; ?>">
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Tem certeza que deseja desativar este aluno?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Professores -->
        <div id="professores" class="tab-content">
            <div class="card">
                <div class="card-header">
                    <h3>Cadastrar Novo Professor</h3>
                </div>
                <form action="dashboard_admin.php" method="POST">
                    <input type="hidden" name="action" value="add_professor">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="professor_nome">Nome Completo</label>
                            <input type="text" class="form-control" id="professor_nome" name="nome" required>
                        </div>
                        <div class="form-group">
                            <label for="professor_email">E-mail</label>
                            <input type="email" class="form-control" id="professor_email" name="email" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="professor_senha">Senha</label>
                            <input type="password" class="form-control" id="professor_senha" name="senha" required>
                        </div>
                        <div class="form-group">
                            <label for="professor_instituicao">Instituição</label>
                            <select class="form-control" id="professor_instituicao" name="instituicao_id" required>
                                <option value="">Selecione uma instituição</option>
                                <?php foreach ($instituicoes as $instituicao): ?>
                                <option value="<?php echo $instituicao['id']; ?>">
                                    <?php echo($instituicao['nome']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Cadastrar Professor</button>
                </form>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3>Professores Cadastrados</h3>
                </div>
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
                        <?php foreach ($professores as $professor): ?>
                        <tr>
                            <td><?php echo $professor['id']; ?></td>
                            <td><?php echo($professor['nome']); ?></td>
                            <td><?php echo($professor['email']); ?></td>
                            <td><?php echo($professor['instituicao_nome'] ?? 'N/A'); ?></td>
                            <td>
                                <span class="badge <?php echo $professor['ativo'] ? 'badge-success' : 'badge-warning'; ?>">
                                    <?php echo $professor['ativo'] ? 'Ativo' : 'Inativo'; ?>
                                </span>
                            </td>
                            <td class="actions">
                                <button class="btn btn-primary"><i class="fas fa-edit"></i></button>
                                <form action="dashboard_admin.php" method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="delete_user">
                                    <input type="hidden" name="id" value="<?php echo $professor['id']; ?>">
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Tem certeza que deseja desativar este professor?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function showTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Show selected tab
            document.getElementById(tabName).classList.add('active');
            
            // Update active button
            document.querySelectorAll('.view-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            event.target.classList.add('active');
        }
    </script>
</body>
</html>