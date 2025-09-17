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

// Verificar se foi passado um ID para edição
$instituicao = null;
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM instituicoes WHERE id = ?");
    $stmt->execute([$id]);
    $instituicao = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$instituicao) {
        $_SESSION['mensagem'] = "Instituição não encontrada!";
        $_SESSION['tipo_mensagem'] = "erro";
        // Obter a aba da URL ou usar 'instituicoes' como padrão
        $aba = isset($_GET['aba']) ? $_GET['aba'] : 'instituicoes';
        header("Location: ../dashboard_admin.php?aba=" . $aba);
        exit;
    }
}

// Processar o formulário de edição
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $nome = $_POST['nome'];
    $cnpj = $_POST['cnpj'];
    $endereco = $_POST['endereco'];
    $telefone = $_POST['telefone'];
    
    // Atualizar a instituição
    $stmt = $pdo->prepare("UPDATE instituicoes SET nome = ?, cnpj = ?, endereco = ?, telefone = ? WHERE id = ?");
    if ($stmt->execute([$nome, $cnpj, $endereco, $telefone, $id])) {
        $_SESSION['mensagem'] = "Instituição atualizada com sucesso!";
        $_SESSION['tipo_mensagem'] = "sucesso";
        
        // Redirecionar mantendo a aba correta (instituições)
        $aba = isset($_POST['aba_ativa']) ? $_POST['aba_ativa'] : 'instituicoes';
        header("Location: ../dashboard_admin.php?aba=" . $aba);
        exit;
    } else {
        $_SESSION['mensagem'] = "Erro ao atualizar instituição!";
        $_SESSION['tipo_mensagem'] = "erro";
    }
    
    // Recarregar dados da instituição
    $stmt = $pdo->prepare("SELECT * FROM instituicoes WHERE id = ?");
    $stmt->execute([$id]);
    $instituicao = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Obter a aba da URL para usar no formulário
$aba = isset($_GET['aba']) ? $_GET['aba'] : 'instituicoes';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Instituição - Banco de Questões</title>
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
                    <h2><i class="fas fa-building"></i> Editar Instituição</h2>
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
                
                <?php if ($instituicao): ?>
                <form action="editar_instituicao.php" method="POST">
                    <input type="hidden" name="id" value="<?php echo $instituicao['id']; ?>">
                    <input type="hidden" name="aba_ativa" value="<?php echo $aba; ?>">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nome">Nome da Instituição</label>
                            <input type="text" class="form-control" id="nome" name="nome" 
                                   value="<?php echo htmlspecialchars($instituicao['nome']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="cnpj">CNPJ</label>
                            <input type="text" class="form-control" id="cnpj" name="cnpj" 
                                   value="<?php echo htmlspecialchars($instituicao['cnpj']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="endereco">Endereço</label>
                            <input type="text" class="form-control" id="endereco" name="endereco" 
                                   value="<?php echo htmlspecialchars($instituicao['endereco']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="telefone">Telefone</label>
                            <input type="text" class="form-control" id="telefone" name="telefone" 
                                   value="<?php echo htmlspecialchars($instituicao['telefone']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Data de Criação</label>
                        <input type="text" class="form-control" 
                               value="<?php echo date('d/m/Y H:i', strtotime($instituicao['criado_em'])); ?>" disabled>
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
                    Nenhuma instituição selecionada para edição.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>