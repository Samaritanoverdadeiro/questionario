<?php 
require_once '../login/auth.php';

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

// Buscar instituições para o select (se a tabela existir)
try {
    $stmt_instituicoes = $pdo->query("SELECT id, nome FROM instituicoes ORDER BY nome ASC");
    $instituicoes = $stmt_instituicoes->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $instituicoes = [];
}

// Processar formulário de cadastro de disciplina
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_disciplina') {
        $nome = $_POST['nome'];
        $codigo = $_POST['codigo'];
        $instituicao_id = $_POST['instituicao_id'] ?? null;

        // Validar se instituição foi selecionada
        if (empty($instituicao_id)) {
            $_SESSION['mensagem'] = "Por favor, selecione uma instituição!";
            $_SESSION['tipo_mensagem'] = "erro";
            header("Location: questoes.php");
            exit;
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO disciplinas (nome, codigo, instituicao_id) VALUES (?, ?, ?)");
            $stmt->execute([$nome, $codigo, $instituicao_id]);

            $_SESSION['mensagem'] = "Disciplina cadastrada com sucesso!";
            $_SESSION['tipo_mensagem'] = "sucesso";
        } catch (PDOException $e) {
            $_SESSION['mensagem'] = "Erro ao cadastrar disciplina: " . $e->getMessage();
            $_SESSION['tipo_mensagem'] = "erro";
        }
        
        header("Location: questoes.php");
        exit;
    }
    
    // Processar exclusão de disciplina
    if ($action === 'delete_disciplina') {
        $id = $_POST['id'];

        try {
            // Verificar se há questões vinculadas a esta disciplina
            $stmt_check = $pdo->prepare("SELECT COUNT(*) as total FROM questoes WHERE disciplina_id = ?");
            $stmt_check->execute([$id]);
            $questoes_vinculadas = $stmt_check->fetch()['total'];

            if ($questoes_vinculadas > 0) {
                $_SESSION['mensagem'] = "Não é possível excluir a disciplina pois existem questões vinculadas a ela!";
                $_SESSION['tipo_mensagem'] = "erro";
            } else {
                $stmt = $pdo->prepare("DELETE FROM disciplinas WHERE id = ?");
                $stmt->execute([$id]);

                $_SESSION['mensagem'] = "Disciplina excluída com sucesso!";
                $_SESSION['tipo_mensagem'] = "sucesso";
            }
        } catch (PDOException $e) {
            $_SESSION['mensagem'] = "Erro ao excluir disciplina: " . $e->getMessage();
            $_SESSION['tipo_mensagem'] = "erro";
        }
        
        header("Location: questoes.php");
        exit;
    }
    
    // Processar formulário de questões
    if ($action === 'add_questao') {
        $enunciado = $_POST['enunciado'];
        $tipo = $_POST['tipo'];
        $dificuldade = $_POST['dificuldade'];
        $disciplina_id = $_POST['disciplina_id'];
        $autor_id = $_POST['autor_id'];

        $stmt = $pdo->prepare("INSERT INTO questoes (enunciado, tipo, dificuldade, disciplina_id, autor_id, ativa) VALUES (?, ?, ?, ?, ?, 1)");
        $stmt->execute([$enunciado, $tipo, $dificuldade, $disciplina_id, $autor_id]);

        $_SESSION['mensagem'] = "Questão cadastrada com sucesso!";
        $_SESSION['tipo_mensagem'] = "sucesso";
        header("Location: questoes.php");
        exit;
    } elseif ($action === 'toggle_questao_status') {
        $id = $_POST['id'];
        $novo_status = $_POST['novo_status'];

        $stmt = $pdo->prepare("UPDATE questoes SET ativa = ? WHERE id = ?");
        $stmt->execute([$novo_status, $id]);

        $_SESSION['mensagem'] = $novo_status ? "Questão ativada com sucesso!" : "Questão desativada com sucesso!";
        $_SESSION['tipo_mensagem'] = "sucesso";
        
        header("Location: questoes.php");
        exit;
    } elseif ($action === 'delete_questao') {
        $id = $_POST['id'];

        $stmt = $pdo->prepare("DELETE FROM questoes WHERE id = ?");
        $stmt->execute([$id]);

        $_SESSION['mensagem'] = "Questão excluída com sucesso!";
        $_SESSION['tipo_mensagem'] = "sucesso";
        
        header("Location: questoes.php");
        exit;
    }
}

// Buscar disciplinas com nome da instituição
try {
    $stmt_disciplinas = $pdo->query("
        SELECT d.*, i.nome as instituicao_nome 
        FROM disciplinas d 
        LEFT JOIN instituicoes i ON d.instituicao_id = i.id 
        ORDER BY d.nome ASC
    ");
    $disciplinas = $stmt_disciplinas->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Se der erro na junção, busca apenas as disciplinas
    $stmt_disciplinas = $pdo->query("SELECT * FROM disciplinas ORDER BY nome ASC");
    $disciplinas = $stmt_disciplinas->fetchAll(PDO::FETCH_ASSOC);
}

// Buscar professores ativos para o select
$stmt_professores = $pdo->query("SELECT id, nome FROM usuarios WHERE tipo = 'professor' AND ativo = 1 ORDER BY nome ASC");
$professores = $stmt_professores->fetchAll(PDO::FETCH_ASSOC);

// Buscar questões
$stmt_questoes = $pdo->query("
    SELECT q.*, 
           d.nome as disciplina_nome,
           u.nome as autor_nome
    FROM questoes q 
    LEFT JOIN disciplinas d ON q.disciplina_id = d.id 
    LEFT JOIN usuarios u ON q.autor_id = u.id 
    ORDER BY q.ativa DESC, q.id DESC
");
$questoes = $stmt_questoes->fetchAll(PDO::FETCH_ASSOC);

// Contadores
$count_questoes_ativas = $pdo->query("SELECT COUNT(*) as total FROM questoes WHERE ativa = 1")->fetch()['total'];
$count_questoes_inativas = $pdo->query("SELECT COUNT(*) as total FROM questoes WHERE ativa = 0")->fetch()['total'];
$count_questoes_total = $count_questoes_ativas + $count_questoes_inativas;

// Contador de disciplinas
$count_disciplinas = $pdo->query("SELECT COUNT(*) as total FROM disciplinas")->fetch()['total'];

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
    <title>Gerenciar Questões - Banco de Questões</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../estilos/estilo_dashboard_admin.css">
    <link rel="stylesheet" href="../estilos/estilo_dashboard_tabs.css">
    <link rel="stylesheet" href="../estilos/estilo_sidebar_instituicoes.css">
    <link rel="stylesheet" href="../estilos/estilo_disciplina.css">
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
            <div class="menu-item">
                <a href="professores.php" style="color: inherit; text-decoration: none;">
                    <i class="fas fa-chalkboard-teacher"></i>
                    <span>Professores</span>
                </a>
            </div>
            <div class="menu-item active">
                <i class="fas fa-book"></i>
                <span>Questões</span>
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
            <h2><i class="fas fa-book"></i> Gerenciar Questões</h2>
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
                    <i class="fas fa-book"></i>
                </div>
                <div class="stat-text">
                    <h3><?php echo $count_questoes_total; ?></h3>
                    <p>Total de Questões</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background-color: rgba(76, 201, 240, 0.1); color: var(--success);">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-text">
                    <h3><?php echo $count_questoes_ativas; ?></h3>
                    <p>Questões Ativas</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background-color: rgba(248, 150, 30, 0.1); color: var(--warning);">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <div class="stat-text">
                    <h3><?php echo $count_questoes_inativas; ?></h3>
                    <p>Questões Inativas</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background-color: rgba(108, 117, 125, 0.1); color: var(--secondary);">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <div class="stat-text">
                    <h3><?php echo $count_disciplinas; ?></h3>
                    <p>Disciplinas</p>
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

        <!-- Container Disciplinas -->
        <div class="disciplinas-container">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-graduation-cap"></i> Gerenciar Disciplinas</h3>
                </div>
                
                <!-- Formulário de cadastro de disciplina -->
                <div class="form-section">
                    <h4><i class="fas fa-plus-circle"></i> Cadastrar Nova Disciplina</h4>
                    <form action="questoes.php" method="POST" class="disciplina-form">
                        <input type="hidden" name="action" value="add_disciplina">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="disciplina_nome">Nome da Disciplina *</label>
                                <input type="text" class="form-control" id="disciplina_nome" name="nome" required 
                                       placeholder="Ex: Matemática, Português, História...">
                            </div>
                            <div class="form-group">
                                <label for="disciplina_codigo">Código *</label>
                                <input type="text" class="form-control" id="disciplina_codigo" name="codigo" required 
                                       placeholder="Ex: MAT101, POR201...">
                            </div>
                        </div>

                        <?php if (count($instituicoes) > 0): ?>
                        <div class="form-group">
                            <label for="disciplina_instituicao">Instituição *</label>
                            <select class="form-control" id="disciplina_instituicao" name="instituicao_id" required>
                                <option value="">Selecione uma instituição</option>
                                <?php foreach ($instituicoes as $instituicao): ?>
                                    <option value="<?php echo $instituicao['id']; ?>">
                                        <?php echo htmlspecialchars($instituicao['nome']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            Nenhuma instituição cadastrada. Por favor, cadastre uma instituição primeiro.
                        </div>
                        <?php endif; ?>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary" <?php echo count($instituicoes) === 0 ? 'disabled' : ''; ?>>
                                <i class="fas fa-save"></i> Cadastrar Disciplina
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="openModal()">
                                <i class="fas fa-list"></i> Ver Todas as Disciplinas
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Container Questões -->
        <div class="questoes-container">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-book"></i> Gerenciar Questões</h3>
                </div>

                <!-- Formulário de cadastro de questão -->
                <div class="form-section">
                    <h4><i class="fas fa-plus-circle"></i> Cadastrar Nova Questão</h4>
                    <form action="questoes.php" method="POST">
                        <input type="hidden" name="action" value="add_questao">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="questao_disciplina">Disciplina *</label>
                                <select class="form-control" id="questao_disciplina" name="disciplina_id" required>
                                    <option value="">Selecione uma disciplina</option>
                                    <?php foreach ($disciplinas as $disciplina): ?>
                                        <option value="<?php echo $disciplina['id']; ?>">
                                            <?php echo htmlspecialchars($disciplina['nome'] . ' (' . $disciplina['codigo'] . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="questao_autor">Autor (Professor) *</label>
                                <select class="form-control" id="questao_autor" name="autor_id" required>
                                    <option value="">Selecione um professor</option>
                                    <?php foreach ($professores as $professor): ?>
                                        <option value="<?php echo $professor['id']; ?>">
                                            <?php echo htmlspecialchars($professor['nome']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="questao_enunciado">Enunciado da Questão *</label>
                            <textarea class="form-control" id="questao_enunciado" name="enunciado" rows="4" required placeholder="Digite o enunciado da questão..."></textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="questao_tipo">Tipo de Questão *</label>
                                <select class="form-control" id="questao_tipo" name="tipo" required>
                                    <option value="">Selecione o tipo</option>
                                    <option value="multipla_escolha">Múltipla Escolha</option>
                                    <option value="verdadeiro_falso">Verdadeiro ou Falso</option>
                                    <option value="dissertativa">Dissertativa</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="questao_dificuldade">Nível de Dificuldade *</label>
                                <select class="form-control" id="questao_dificuldade" name="dificuldade" required>
                                    <option value="">Selecione o nível</option>
                                    <option value="facil">Fácil</option>
                                    <option value="medio">Médio</option>
                                    <option value="dificil">Difícil</option>
                                </select>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Cadastrar Questão
                        </button>
                    </form>
                </div>

                <!-- Lista de questões -->
                <div class="lista-section">
                    <h4><i class="fas fa-list"></i> Questões Cadastradas</h4>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Enunciado</th>
                                    <th>Disciplina</th>
                                    <th>Autor</th>
                                    <th>Tipo</th>
                                    <th>Dificuldade</th>
                                    <th>Status</th>
                                    <th>Criado em</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($questoes) > 0): ?>
                                    <?php foreach ($questoes as $questao): ?>
                                        <tr>
                                            <td><?php echo $questao['id']; ?></td>
                                            <td>
                                                <div style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                    <?php echo htmlspecialchars($questao['enunciado']); ?>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($questao['disciplina_nome'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($questao['autor_nome'] ?? 'N/A'); ?></td>
                                            <td>
                                                <span class="badge badge-info">
                                                    <?php 
                                                    $tipos = [
                                                        'multipla_escolha' => 'Múltipla Escolha',
                                                        'verdadeiro_falso' => 'Verdadeiro/Falso',
                                                        'dissertativa' => 'Dissertativa'
                                                    ];
                                                    echo $tipos[$questao['tipo']] ?? $questao['tipo'];
                                                    ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge 
                                                    <?php echo $questao['dificuldade'] === 'facil' ? 'badge-success' : 
                                                          ($questao['dificuldade'] === 'medio' ? 'badge-warning' : 'badge-danger'); ?>">
                                                    <?php echo ucfirst($questao['dificuldade']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge <?php echo $questao['ativa'] ? 'badge-success' : 'badge-warning'; ?>">
                                                    <?php echo $questao['ativa'] ? 'Ativa' : 'Inativa'; ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($questao['criado_em'])); ?></td>
                                            <td class="actions">
                                                <a href="../editar/editar_questao.php?id=<?php echo $questao['id']; ?>" class="btn btn-primary" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>

                                                <!-- Botão para alternar status -->
                                                <form action="questoes.php" method="POST" style="display: inline;">
                                                    <input type="hidden" name="action" value="toggle_questao_status">
                                                    <input type="hidden" name="id" value="<?php echo $questao['id']; ?>">
                                                    <input type="hidden" name="novo_status" value="<?php echo $questao['ativa'] ? 0 : 1; ?>">
                                                    <button type="submit" class="btn <?php echo $questao['ativa'] ? 'btn-warning' : 'btn-success'; ?>"
                                                        title="<?php echo $questao['ativa'] ? 'Desativar' : 'Ativar'; ?>"
                                                        onclick="return confirm('<?php echo $questao['ativa'] ?
                                                                                        'Tem certeza que deseja desativar esta questão?' :
                                                                                        'Tem certeza que deseja ativar esta questão?'; ?>')">
                                                        <i class="fas <?php echo $questao['ativa'] ? 'fa-times' : 'fa-check'; ?>"></i>
                                                    </button>
                                                </form>

                                                <!-- Botão de deletar -->
                                                <form action="questoes.php" method="POST" style="display: inline;">
                                                    <input type="hidden" name="action" value="delete_questao">
                                                    <input type="hidden" name="id" value="<?php echo $questao['id']; ?>">
                                                    <button type="submit" class="btn btn-danger" title="Excluir permanentemente"
                                                        onclick="return confirm('Tem certeza que deseja excluir permanentemente esta questão?\\n\\nEsta ação não pode ser desfeita!')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9" style="text-align: center; padding: 2rem;">
                                            <i class="fas fa-book" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                                            <p>Nenhuma questão cadastrada.</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para listar disciplinas -->
    <div id="disciplinasModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h3><i class="fas fa-list"></i> Todas as Disciplinas</h3>
            
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Nome</th>
                            <th>Instituição</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($disciplinas) > 0): ?>
                            <?php foreach ($disciplinas as $disciplina): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($disciplina['codigo']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($disciplina['nome']); ?></td>
                                    <td>
                                        <?php 
                                        if (isset($disciplina['instituicao_nome'])) {
                                            echo htmlspecialchars($disciplina['instituicao_nome']);
                                        } else {
                                            echo "ID: " . htmlspecialchars($disciplina['instituicao_id']);
                                        }
                                        ?>
                                    </td>
                                    <td class="actions">
                                        <!-- Botão de deletar disciplina -->
                                        <form action="questoes.php" method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="delete_disciplina">
                                            <input type="hidden" name="id" value="<?php echo $disciplina['id']; ?>">
                                            <button type="submit" class="btn btn-danger" title="Excluir disciplina"
                                                onclick="return confirm('Tem certeza que deseja excluir a disciplina \\'<?php echo htmlspecialchars($disciplina['nome']); ?>\\'?\\n\\nEsta ação não pode ser desfeita!')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="text-align: center; padding: 2rem;">
                                    <i class="fas fa-graduation-cap" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                                    <p>Nenhuma disciplina cadastrada.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Funções para abrir/fechar modal
        function openModal() {
            document.getElementById('disciplinasModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('disciplinasModal').style.display = 'none';
        }

        // Fechar modal ao clicar fora
        window.onclick = function(event) {
            const modal = document.getElementById('disciplinasModal');
            if (event.target === modal) {
                closeModal();
            }
        }

        // Contador de caracteres para o enunciado
        document.getElementById('questao_enunciado').addEventListener('input', function(e) {
            const maxLength = 1000;
            const currentLength = e.target.value.length;
            
            if (currentLength > maxLength) {
                e.target.value = e.target.value.substring(0, maxLength);
                alert('O enunciado não pode ter mais de ' + maxLength + ' caracteres.');
            }
        });
    </script>
</body>

</html>