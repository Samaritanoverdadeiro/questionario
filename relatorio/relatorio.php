<?php
session_start();
// Verificar se o usuário está logado como admin
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header("Location: ../login.php");
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

// Buscar dados inativos
$instituicoes_inativas = $pdo->query("SELECT * FROM instituicoes WHERE ativo = 0")->fetchAll(PDO::FETCH_ASSOC);
$alunos_inativos = $pdo->query("SELECT u.*, i.nome as instituicao_nome FROM usuarios u LEFT JOIN instituicoes i ON u.instituicao_id = i.id WHERE u.ativo = 0 AND u.tipo = 'aluno'")->fetchAll(PDO::FETCH_ASSOC);
$professores_inativos = $pdo->query("SELECT u.*, i.nome as instituicao_nome FROM usuarios u LEFT JOIN instituicoes i ON u.instituicao_id = i.id WHERE u.ativo = 0 AND u.tipo = 'professor'")->fetchAll(PDO::FETCH_ASSOC);

// Processar reativação
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'reativar_instituicao') {
        $id = $_POST['id'];
        $stmt = $pdo->prepare("UPDATE instituicoes SET ativo = 1 WHERE id = ?");
        if ($stmt->execute([$id])) {
            $_SESSION['mensagem'] = "Instituição reativada com sucesso!";
            $_SESSION['tipo_mensagem'] = "sucesso";
            header("Location: relatorio.php");
            exit;
        }
    }
    elseif ($action === 'reativar_usuario') {
        $id = $_POST['id'];
        $stmt = $pdo->prepare("UPDATE usuarios SET ativo = 1 WHERE id = ?");
        if ($stmt->execute([$id])) {
            $_SESSION['mensagem'] = "Usuário reativado com sucesso!";
            $_SESSION['tipo_mensagem'] = "sucesso";
            header("Location: relatorio.php");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatórios - Banco de Questões</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../estilos/estilo_dashboard_admin.css">
    <link rel="stylesheet" href="../estilos/estilo_relatorios.css">
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
                    <i class="fas fa-arrow-left"></i>
                    <span>Voltar</span>
                </a>
            </div>
            <div class="menu-item">
                <i class="fas fa-building"></i>
                <span>Instituições Inativas</span>
            </div>
            <div class="menu-item">
                <i class="fas fa-user-graduate"></i>
                <span>Alunos Inativos</span>
            </div>
            <div class="menu-item">
                <i class="fas fa-chalkboard-teacher"></i>
                <span>Professores Inativos</span>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <h2>Relatórios de Registros Inativos</h2>
            <div class="header-actions">
                <a href="../dashboard_admin.php" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Voltar ao Dashboard
                </a>
            </div>
        </div>

        <?php if (isset($_SESSION['mensagem'])): ?>
        <div class="message <?php echo $_SESSION['tipo_mensagem']; ?>">
            <?php 
            echo $_SESSION['mensagem']; 
            unset($_SESSION['mensagem']);
            unset($_SESSION['tipo_mensagem']);
            ?>
        </div>
        <?php endif; ?>

        <!-- Abas de Navegação -->
        <div class="switch-view">
            <button class="view-btn active" onclick="showTab('instituicoes')">
                <i class="fas fa-building"></i> Instituições (<?php echo count($instituicoes_inativas); ?>)
            </button>
            <button class="view-btn" onclick="showTab('alunos')">
                <i class="fas fa-user-graduate"></i> Alunos (<?php echo count($alunos_inativos); ?>)
            </button>
            <button class="view-btn" onclick="showTab('professores')">
                <i class="fas fa-chalkboard-teacher"></i> Professores (<?php echo count($professores_inativos); ?>)
            </button>
        </div>

        <!-- Instituições Inativas -->
        <div id="instituicoes" class="tab-content active">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-building"></i> Instituições Inativas</h3>
                </div>
                
                <?php if (count($instituicoes_inativas) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>CNPJ</th>
                            <th>Endereço</th>
                            <th>Telefone</th>
                            <th>Data de Inativação</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($instituicoes_inativas as $instituicao): ?>
                        <tr>
                            <td><?php echo $instituicao['id']; ?></td>
                            <td><?php echo ($instituicao['nome']); ?></td>
                            <td><?php echo ($instituicao['cnpj']); ?></td>
                            <td><?php echo ($instituicao['endereco']); ?></td>
                            <td><?php echo ($instituicao['telefone']); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($instituicao['atualizado_em'] ?? $instituicao['criado_em'])); ?></td>
                            <td class="actions">
                                <form action="relatorio.php" method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="reativar_instituicao">
                                    <input type="hidden" name="id" value="<?php echo $instituicao['id']; ?>">
                                    <button type="submit" class="btn btn-success" onclick="return confirm('Tem certeza que deseja reativar esta instituição?')">
                                        <i class="fas fa-undo"></i> Reativar
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="message info">
                    <i class="fas fa-info-circle"></i> Nenhuma instituição inativa encontrada.
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Alunos Inativos -->
        <div id="alunos" class="tab-content">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-user-graduate"></i> Alunos Inativos</h3>
                </div>
                
                <?php if (count($alunos_inativos) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>E-mail</th>
                            <th>Instituição</th>
                            <th>Data de Inativação</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($alunos_inativos as $aluno): ?>
                        <tr>
                            <td><?php echo $aluno['id']; ?></td>
                            <td><?php echo ($aluno['nome']); ?></td>
                            <td><?php echo ($aluno['email']); ?></td>
                            <td><?php echo ($aluno['instituicao_nome'] ?? 'N/A'); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($aluno['atualizado_em'] ?? $aluno['criado_em'])); ?></td>
                            <td class="actions">
                                <form action="relatorio.php" method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="reativar_usuario">
                                    <input type="hidden" name="id" value="<?php echo $aluno['id']; ?>">
                                    <button type="submit" class="btn btn-success" onclick="return confirm('Tem certeza que deseja reativar este aluno?')">
                                        <i class="fas fa-undo"></i> Reativar
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="message info">
                    <i class="fas fa-info-circle"></i> Nenhum aluno inativo encontrado.
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Professores Inativos -->
        <div id="professores" class="tab-content">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-chalkboard-teacher"></i> Professores Inativos</h3>
                </div>
                
                <?php if (count($professores_inativos) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>E-mail</th>
                            <th>Instituição</th>
                            <th>Data de Inativação</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($professores_inativos as $professor): ?>
                        <tr>
                            <td><?php echo $professor['id']; ?></td>
                            <td><?php echo ($professor['nome']); ?></td>
                            <td><?php echo ($professor['email']); ?></td>
                            <td><?php echo ($professor['instituicao_nome'] ?? 'N/A'); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($professor['atualizado_em'] ?? $professor['criado_em'])); ?></td>
                            <td class="actions">
                                <form action="relatorio.php" method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="reativar_usuario">
                                    <input type="hidden" name="id" value="<?php echo $professor['id']; ?>">
                                    <button type="submit" class="btn btn-success" onclick="return confirm('Tem certeza que deseja reativar este professor?')">
                                        <i class="fas fa-undo"></i> Reativar
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="message info">
                    <i class="fas fa-info-circle"></i> Nenhum professor inativo encontrado.
                </div>
                <?php endif; ?>
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