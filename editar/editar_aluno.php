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

// Buscar instituições para o select
$stmt_instituicoes = $pdo->query("SELECT * FROM instituicoes");
$instituicoes = $stmt_instituicoes->fetchAll(PDO::FETCH_ASSOC);

// Verificar se foi passado um ID para edição
$aluno = null;
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT u.*, i.nome as instituicao_nome 
                          FROM usuarios u 
                          LEFT JOIN instituicoes i ON u.instituicao_id = i.id 
                          WHERE u.id = ? AND u.tipo = 'aluno'");
    $stmt->execute([$id]);
    $aluno = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$aluno) {
        $_SESSION['mensagem'] = "Aluno não encontrado!";
        $_SESSION['tipo_mensagem'] = "erro";
        // Obter a aba da URL ou usar 'alunos' como padrão
        $aba = isset($_GET['aba']) ? $_GET['aba'] : 'alunos';
        header("Location: ../dashboard_admin.php?aba=" . $aba);
        exit;
    }
}

// Processar o formulário de edição
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $instituicao_id = $_POST['instituicao_id'];
    $ativo = isset($_POST['ativo']) ? 1 : 0;
    
    // Verificar se o email já existe para outro usuário
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
    $stmt->execute([$email, $id]);
    $usuario_existente = $stmt->fetch();
    
    if ($usuario_existente) {
        $_SESSION['mensagem'] = "Este e-mail já está em uso por outro usuário!";
        $_SESSION['tipo_mensagem'] = "erro";
    } else {
        // Atualizar o aluno
        $stmt = $pdo->prepare("UPDATE usuarios SET nome = ?, email = ?, instituicao_id = ?, ativo = ? WHERE id = ? AND tipo = 'aluno'");
        if ($stmt->execute([$nome, $email, $instituicao_id, $ativo, $id])) {
            $_SESSION['mensagem'] = "Aluno atualizado com sucesso!";
            $_SESSION['tipo_mensagem'] = "sucesso";
            
            // Redirecionar mantendo a aba correta
            $aba = isset($_POST['aba_ativa']) ? $_POST['aba_ativa'] : 'alunos';
            header("Location: ../dashboard_admin.php?aba=" . $aba);
            exit;
        } else {
            $_SESSION['mensagem'] = "Erro ao atualizar aluno!";
            $_SESSION['tipo_mensagem'] = "erro";
        }
    }
    
    // Recarregar dados do aluno
    $stmt = $pdo->prepare("SELECT u.*, i.nome as instituicao_nome 
                          FROM usuarios u 
                          LEFT JOIN instituicoes i ON u.instituicao_id = i.id 
                          WHERE u.id = ? AND u.tipo = 'aluno'");
    $stmt->execute([$id]);
    $aluno = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Obter a aba da URL para usar no formulário
$aba = isset($_GET['aba']) ? $_GET['aba'] : 'alunos';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Aluno - Banco de Questões</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../estilos/estilo_dashboard_admin.css">
    <link rel="stylesheet" href="../estilos/estilo_editar.css">
</head>
<body>
    <div class="sidebar">
        <div class="logo">
            <h1>Banco de Questões</h1>
            <p>Painel Administrativo</p>
        </div>
        <div class="menu">
            <div class="menu-item">
                <a href="../dashboard_admin.php?aba=<?php echo $aba; ?>" style="color: inherit; text-decoration: none;">
                    <i class="fas fa-arrow-left"></i>
                    <span>Voltar</span>
                </a>
            </div>
        </div>
    </div>

    <div class="main-content">
        <div class="container">
            <a href="../dashboard_admin.php?aba=<?php echo $aba; ?>" class="btn btn-primary back-btn">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
            
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-user-graduate"></i> Editar Aluno</h2>
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
                
                <?php if ($aluno): ?>
                <form action="editar_aluno.php" method="POST">
                    <input type="hidden" name="id" value="<?php echo $aluno['id']; ?>">
                    <input type="hidden" name="aba_ativa" value="<?php echo $aba; ?>">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nome">Nome Completo</label>
                            <input type="text" class="form-control" id="nome" name="nome" 
                                   value="<?php echo htmlspecialchars($aluno['nome']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="email">E-mail</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($aluno['email']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="instituicao_id">Instituição</label>
                            <select class="form-control" id="instituicao_id" name="instituicao_id" required>
                                <option value="">Selecione uma instituição</option>
                                <?php foreach ($instituicoes as $instituicao): ?>
                                <option value="<?php echo $instituicao['id']; ?>" 
                                    <?php echo ($instituicao['id'] == $aluno['instituicao_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($instituicao['nome']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="tipo">Tipo de Usuário</label>
                            <input type="text" class="form-control" id="tipo" value="Aluno" disabled>
                            <small>O tipo de usuário não pode ser alterado.</small>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="form-checkbox">
                            <input type="checkbox" id="ativo" name="ativo" 
                                   <?php echo $aluno['ativo'] ? 'checked' : ''; ?>>
                            <label for="ativo">Aluno ativo</label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Data de Criação</label>
                        <input type="text" class="form-control" 
                               value="<?php echo date('d/m/Y H:i', strtotime($aluno['criado_em'])); ?>" disabled>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Salvar Alterações
                        </button>
                        
                        <a href="../dashboard_admin.php?aba=<?php echo $aba; ?>" class="btn btn-secondary">
                            Cancelar
                        </a>
                    </div>
                </form>
                <?php else: ?>
                <div class="message erro">
                    Nenhum aluno selecionado para edição.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>