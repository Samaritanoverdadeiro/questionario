<?php
// Iniciar sessão
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

        $_SESSION['mensagem'] = "Instituição cadastrada com sucesso!";
        $_SESSION['tipo_mensagem'] = "sucesso";
        header("Location: instituicoes.php");
        exit;
    } elseif ($action === 'toggle_instituicao_status') {
        $id = $_POST['id'];
        $novo_status = $_POST['novo_status'];

        // Iniciar transação para segurança
        $pdo->beginTransaction();

        try {
            // Atualizar status da instituição
            $stmt = $pdo->prepare("UPDATE instituicoes SET ativo = ? WHERE id = ?");
            $stmt->execute([$novo_status, $id]);

            if ($novo_status == 0) {
                // Se está desativando, desativar apenas os professores vinculados (mantendo o vínculo)
                $stmt = $pdo->prepare("UPDATE usuarios SET ativo = 0 WHERE instituicao_id = ? AND tipo = 'professor'");
                $stmt->execute([$id]);

                $_SESSION['mensagem'] = "Instituição e professores desativados com sucesso!";
            } else {
                // Se está ativando, ativar apenas os professores vinculados (os alunos permanecem inalterados)
                $stmt = $pdo->prepare("UPDATE usuarios SET ativo = 1 WHERE instituicao_id = ? AND tipo = 'professor'");
                $stmt->execute([$id]);

                $_SESSION['mensagem'] = "Instituição e professores ativados com sucesso!";
            }

            // Confirmar transação
            $pdo->commit();
            $_SESSION['tipo_mensagem'] = "sucesso";
        } catch (Exception $e) {
            // Em caso de erro, reverter transação
            $pdo->rollBack();
            $_SESSION['mensagem'] = "Erro ao alterar status: " . $e->getMessage();
            $_SESSION['tipo_mensagem'] = "erro";
        }

        header("Location: instituicoes.php");
        exit;
    } elseif ($action === 'delete_instituicao') {
        $id = $_POST['id'];

        // Iniciar transação para garantir consistência
        $pdo->beginTransaction();

        try {
            // 1. Desativar a instituição
            $stmt = $pdo->prepare("UPDATE instituicoes SET ativo = 0 WHERE id = ?");
            $stmt->execute([$id]);

            // 2. Desativar apenas os professores vinculados a esta instituição (sem remover o vínculo)
            $stmt = $pdo->prepare("UPDATE usuarios SET ativo = 0 WHERE instituicao_id = ? AND tipo = 'professor'");
            $stmt->execute([$id]);

            // Confirmar transação
            $pdo->commit();

            $_SESSION['mensagem'] = "Instituição desativada com sucesso! Professores vinculados também foram desativados.";
            $_SESSION['tipo_mensagem'] = "sucesso";
        } catch (Exception $e) {
            // Em caso de erro, reverter transação
            $pdo->rollBack();
            $_SESSION['mensagem'] = "Erro ao desativar instituição: " . $e->getMessage();
            $_SESSION['tipo_mensagem'] = "erro";
        }

        header("Location: instituicoes.php");
        exit;
    }
}

// Buscar instituições
$stmt_instituicoes = $pdo->query("SELECT * FROM instituicoes ORDER BY ativo DESC, nome ASC");
$instituicoes = $stmt_instituicoes->fetchAll(PDO::FETCH_ASSOC);

// Contadores
$count_instituicoes_ativas = $pdo->query("SELECT COUNT(*) as total FROM instituicoes WHERE ativo = 1")->fetch()['total'];
$count_instituicoes_inativas = $pdo->query("SELECT COUNT(*) as total FROM instituicoes WHERE ativo = 0")->fetch()['total'];
$count_instituicoes_total = $count_instituicoes_ativas + $count_instituicoes_inativas;

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
    <title>Gerenciar Instituições - Banco de Questões</title>
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
            <div class="menu-item active">
                <i class="fas fa-building"></i>
                <span>Instituições</span>
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
            <h2><i class="fas fa-building"></i> Gerenciar Instituições</h2>
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
                    <i class="fas fa-building"></i>
                </div>
                <div class="stat-text">
                    <h3><?php echo $count_instituicoes_total; ?></h3>
                    <p>Total de Instituições</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background-color: rgba(76, 201, 240, 0.1); color: var(--success);">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-text">
                    <h3><?php echo $count_instituicoes_ativas; ?></h3>
                    <p>Instituições Ativas</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background-color: rgba(248, 150, 30, 0.1); color: var(--warning);">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <div class="stat-text">
                    <h3><?php echo $count_instituicoes_inativas; ?></h3>
                    <p>Instituições Inativas</p>
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
                <h3><i class="fas fa-plus-circle"></i> Cadastrar Nova Instituição</h3>
            </div>
            <form action="instituicoes.php" method="POST">
                <input type="hidden" name="action" value="add_instituicao">
                <div class="form-row">
                    <div class="form-group">
                        <label for="instituicao_nome">Nome da Instituição *</label>
                        <input type="text" class="form-control" id="instituicao_nome" name="nome" required>
                    </div>
                    <div class="form-group">
                        <label for="instituicao_cnpj">CNPJ *</label>
                        <input type="text" class="form-control" id="instituicao_cnpj" name="cnpj" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="instituicao_endereco">Endereço *</label>
                        <input type="text" class="form-control" id="instituicao_endereco" name="endereco" required>
                    </div>
                    <div class="form-group">
                        <label for="instituicao_telefone">Telefone *</label>
                        <input type="text" class="form-control" id="instituicao_telefone" name="telefone" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Cadastrar Instituição
                </button>
            </form>
        </div>

        <!-- Lista de instituições -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-list"></i> Instituições Cadastradas</h3>
            </div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>CNPJ</th>
                            <th>Endereço</th>
                            <th>Telefone</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($instituicoes) > 0): ?>
                            <?php foreach ($instituicoes as $instituicao): ?>
                                <tr>
                                    <td><?php echo $instituicao['id']; ?></td>
                                    <td><?php echo htmlspecialchars($instituicao['nome']); ?></td>
                                    <td><?php echo htmlspecialchars($instituicao['cnpj']); ?></td>
                                    <td><?php echo htmlspecialchars($instituicao['endereco']); ?></td>
                                    <td><?php echo htmlspecialchars($instituicao['telefone']); ?></td>
                                    <td>
                                        <span class="badge <?php echo $instituicao['ativo'] ? 'badge-success' : 'badge-warning'; ?>">
                                            <?php echo $instituicao['ativo'] ? 'Ativa' : 'Inativa'; ?>
                                        </span>
                                    </td>
                                    <td class="actions">
                                        <a href="../editar/editar_instituicao.php?id=<?php echo $instituicao['id']; ?>" class="btn btn-primary" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        <!-- Botão para alternar status -->
                                        <form action="instituicoes.php" method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="toggle_instituicao_status">
                                            <input type="hidden" name="id" value="<?php echo $instituicao['id']; ?>">
                                            <input type="hidden" name="novo_status" value="<?php echo $instituicao['ativo'] ? 0 : 1; ?>">
                                            <button type="submit" class="btn <?php echo $instituicao['ativo'] ? 'btn-warning' : 'btn-success'; ?>"
                                                title="<?php echo $instituicao['ativo'] ? 'Desativar' : 'Ativar'; ?>"
                                                onclick="return confirm('<?php echo $instituicao['ativo'] ?
                                                                                'Tem certeza que deseja desativar esta instituição?\\n\\n• Todos os professores vinculados serão desativados' :
                                                                                'Tem certeza que deseja ativar esta instituição?\\n\\n• Todos os professores vinculados serão ativados\\n• Os alunos não serão afetados'; ?>')">
                                                <i class="fas <?php echo $instituicao['ativo'] ? 'fa-times' : 'fa-check'; ?>"></i>
                                            </button>
                                        </form>

                                        <!-- Botão de deletar (apenas para instituições inativas) -->
                                        <?php if (!$instituicao['ativo']): ?>
                                            <form action="instituicoes.php" method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="delete_instituicao">
                                                <input type="hidden" name="id" value="<?php echo $instituicao['id']; ?>">
                                                <button type="submit" class="btn btn-danger" title="Excluir permanentemente"
                                                    onclick="return confirm('Tem certeza que deseja excluir permanentemente esta instituição?\\n\\nEsta ação não pode be desfeita!')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 2rem;">
                                    <i class="fas fa-building" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                                    <p>Nenhuma instituição cadastrada.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Função para formatar CNPJ
        document.getElementById('instituicao_cnpj').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 14) value = value.slice(0, 14);

            if (value.length > 12) {
                value = value.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5');
            } else if (value.length > 8) {
                value = value.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})/, '$1.$2.$3/$4');
            } else if (value.length > 5) {
                value = value.replace(/^(\d{2})(\d{3})(\d{3})/, '$1.$2.$3');
            } else if (value.length > 2) {
                value = value.replace(/^(\d{2})(\d{3})/, '$1.$2');
            }

            e.target.value = value;
        });

        // Função para formatar telefone
        document.getElementById('instituicao_telefone').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 11) value = value.slice(0, 11);

            if (value.length > 10) {
                value = value.replace(/^(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
            } else if (value.length > 6) {
                value = value.replace(/^(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
            } else if (value.length > 2) {
                value = value.replace(/^(\d{2})(\d{4})/, '($1) $2');
            } else if (value.length > 0) {
                value = value.replace(/^(\d{2})/, '($1)');
            }

            e.target.value = value;
        });
    </script>
</body>

</html>