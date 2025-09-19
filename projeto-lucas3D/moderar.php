<?php
include "conexao.php"; // conexão com MySQL + variáveis do Cloudinary

// Função para deletar imagem do Cloudinary
function deletarImagemCloudinary($public_id, $cloud_name, $api_key, $api_secret) {
    $timestamp = time();
    $string_to_sign = "public_id=$public_id&timestamp=$timestamp$api_secret";
    $signature = sha1($string_to_sign);

    $data = [
        'public_id' => $public_id,
        'timestamp' => $timestamp,
        'api_key' => $api_key,
        'signature' => $signature
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.cloudinary.com/v1_1/$cloud_name/image/destroy");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}

// Excluir produto
if(isset($_GET['excluir'])) {
    $id = intval($_GET['excluir']);
    $res = mysqli_query($conexao, "SELECT imagem_url FROM produtos WHERE id = $id");
    $dados = mysqli_fetch_assoc($res);

    if($dados && !empty($dados['imagem_url'])) {
        $url = $dados['imagem_url'];
        $parts = explode("/", $url);
        $filename = end($parts);
        $public_id = pathinfo($filename, PATHINFO_FILENAME);
        deletarImagemCloudinary($public_id, $cloud_name, $api_key, $api_secret);
    }

    mysqli_query($conexao, "DELETE FROM produtos WHERE id = $id") or die("Erro ao excluir: " . mysqli_error($conexao));
    header("Location: moderar.php");
    exit;
}

// Editar produto
if(isset($_POST['editar'])) {
    $id = intval($_POST['id']);
    $nome = mysqli_real_escape_string($conexao, $_POST['nome']);
    $descricao = mysqli_real_escape_string($conexao, $_POST['descricao']);
    $preco = floatval($_POST['preco']);

    $update_sql = "UPDATE produtos SET nome='$nome', descricao='$descricao', preco=$preco WHERE id=$id";
    mysqli_query($conexao, $update_sql) or die("Erro ao atualizar: " . mysqli_error($conexao));
    header("Location: moderar.php");
    exit;
}

// Selecionar produtos para exibição
$editar_id = isset($_GET['editar']) ? intval($_GET['editar']) : 0;
$produtos = mysqli_query($conexao, "SELECT * FROM produtos ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8"/>
    <title>Moderar Produtos</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }
        
        body {
            background-color: #000;
            color: #e7e9ea;
            display: flex;
            justify-content: center;
        }
        
        .container {
            display: flex;
            width: 100%;
            max-width: 1265px;
            min-height: 100vh;
        }
        
        /* Sidebar esquerda */
        .sidebar-left {
            width: 275px;
            padding: 8px;
            position: fixed;
            height: 100vh;
            border-right: 1px solid #2f3336;
        }
        
        .logo {
            padding: 12px;
            margin-bottom: 16px;
        }
        
        .logo svg {
            width: 30px;
            height: 30px;
            fill: #e7e9ea;
        }
        
        .nav-item {
            display: flex;
            align-items: center;
            padding: 12px;
            margin-bottom: 4px;
            border-radius: 29px;
            font-size: 20px;
            font-weight: 400;
            cursor: pointer;
        }
        
        .nav-item:hover {
            background-color: #181818;
        }
        
        .nav-item.active {
            font-weight: 700;
        }
        
        .nav-icon {
            margin-right: 16px;
            width: 26px;
            height: 26px;
        }
        
        /* Conteúdo principal */
        .main-content {
            flex: 1;
            margin-left: 275px;
            border-right: 1px solid #2f3336;
            min-height: 100vh;
        }
        
        .header {
            position: sticky;
            top: 0;
            background-color: rgba(0, 0, 0, 0.65);
            backdrop-filter: blur(12px);
            padding: 16px;
            border-bottom: 1px solid #2f3336;
            z-index: 10;
        }
        
        .header h1 {
            font-size: 20px;
            font-weight: 700;
        }
        
        /* Sidebar direita */
        .sidebar-right {
            width: 350px;
            padding: 16px;
        }
        
        .search-container {
            position: sticky;
            top: 16px;
            background-color: #202327;
            border-radius: 24px;
            padding: 12px 16px;
            margin-bottom: 16px;
        }
        
        .search-input {
            background: transparent;
            border: none;
            color: #e7e9ea;
            font-size: 15px;
            width: 100%;
            outline: none;
        }
        
        /* Estilos dos produtos */
        .produtos-container {
            padding: 16px;
        }
        
        .produto {
            border-bottom: 1px solid #2f3336;
            padding: 16px 0;
            position: relative;
        }
        
        .produto:last-child {
            border-bottom: none;
        }
        
        .produto p {
            margin-bottom: 8px;
            font-size: 15px;
        }
        
        .produto strong {
            color: #71767b;
        }
        
        .produto img {
            max-width: 100%;
            border-radius: 16px;
            margin-top: 12px;
            border: 1px solid #2f3336;
        }
        
        .produto-actions {
            display: flex;
            margin-top: 12px;
            color: #71767b;
        }
        
        .action-btn {
            display: flex;
            align-items: center;
            margin-right: 24px;
            cursor: pointer;
            font-size: 13px;
        }
        
        .action-btn:hover {
            color: #1d9bf0;
        }
        
        .delete-btn:hover {
            color: #f91880;
        }
        
        .action-icon {
            margin-right: 4px;
            width: 18px;
            height: 18px;
        }
        
        /* Formulário de edição */
        .edit-form {
            background-color: #16181c;
            border-radius: 16px;
            padding: 16px;
            margin-top: 12px;
        }
        
        .edit-form input, .edit-form textarea {
            width: 100%;
            background-color: #000;
            border: 1px solid #2f3336;
            border-radius: 4px;
            padding: 12px;
            margin-bottom: 12px;
            color: #e7e9ea;
            font-size: 15px;
        }
        
        .edit-form input:focus, .edit-form textarea:focus {
            outline: 1px solid #1d9bf0;
        }
        
        .edit-form input[type="submit"] {
            background-color: #1d9bf0;
            color: #fff;
            font-weight: 700;
            border: none;
            border-radius: 24px;
            padding: 8px 16px;
            width: auto;
            cursor: pointer;
        }
        
        .edit-form input[type="submit"]:hover {
            background-color: #1a8cd8;
        }
        
        .cancel-btn {
            background-color: transparent;
            color: #1d9bf0;
            border: 1px solid #1d9bf0;
            border-radius: 24px;
            padding: 8px 16px;
            margin-left: 12px;
            text-decoration: none;
            font-size: 14px;
            display: inline-block;
        }
        
        .cancel-btn:hover {
            background-color: rgba(29, 155, 240, 0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar esquerda -->
        <div class="sidebar-left">
            <div class="logo">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    </svg>
            </div>
            
            <div class="nav-item active">
                <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true">
                    <g><path d="M12 1.696L.622 8.807l1.06 1.696L3 9.679V19.5C3 20.881 4.119 22 5.5 22h13c1.381 0 2.5-1.119 2.5-2.5V9.679l1.318.824 1.06-1.696L12 1.696zM12 16.5c-1.933 0-3.5-1.567-3.5-3.5s1.567-3.5 3.5-3.5 3.5 1.567 3.5 3.5-1.567 3.5-3.5 3.5z"></path></g>
                </svg>
                <span>Produtos</span>
            </div>
            
            <div class="nav-item">
                <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true">
                    <g><path d="M10.25 3.75c-3.59 0-6.5 2.91-6.5 6.5s2.91 6.5 6.5 6.5c1.795 0 3.419-.726 4.596-1.904 1.178-1.177 1.904-2.801 1.904-4.596 0-3.59-2.91-6.5-6.5-6.5zm-8.5 6.5c0-4.694 3.806-8.5 8.5-8.5s8.5 3.806 8.5 8.5c0 1.986-.682 3.815-1.824 5.262l4.781 4.781-1.414 1.414-4.781-4.781c-1.447 1.142-3.276 1.824-5.262 1.824-4.694 0-8.5-3.806-8.5-8.5z"></path></g>
                </svg>
                <span>Explorar</span>
            </div>
            
            <div class="nav-item">
                <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true">
                    <g><path d="M19.993 9.042C19.48 5.017 16.054 2 11.996 2s-7.49 3.021-7.999 7.051L2.866 18H7.1c.463 2.282 2.481 4 4.9 4s4.437-1.718 4.9-4h4.236l-1.143-8.958zM12 20c-1.306 0-2.417-.835-2.829-2h5.658c-.412 1.165-1.523 2-2.829 2zm-6.866-4l.847-6.698C6.364 6.272 8.941 4 11.996 4s5.627 2.268 6.013 5.295L18.864 16H5.134z"></path></g>
                </svg>
                <span>Notificações</span>
            </div>
            
            <div class="nav-item">
                <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true">
                    <g><path d="M1.998 5.5c0-1.381 1.119-2.5 2.5-2.5h15c1.381 0 2.5 1.119 2.5 2.5v13c0 1.381-1.119 2.5-2.5 2.5h-15c-1.381 0-2.5-1.119-2.5-2.5v-13zm2.5-.5c-.276 0-.5.224-.5.5v2.764l8 3.638 8-3.636V5.5c0-.276-.224-.5-.5-.5h-15zm15.5 5.463l-8 3.636-8-3.638V18.5c0 .276.224.5.5.5h15c.276 0 .5-.224.5-.5v-8.037z"></path></g>
                </svg>
                <span>Mensagens</span>
            </div>
        </div>
        
        <!-- Conteúdo principal -->
        <div class="main-content">
            <div class="header">
                <h1>Moderar Produtos</h1>
            </div>
            
            <div class="produtos-container">
                <?php while($res = mysqli_fetch_assoc($produtos)): ?>
                    <div class="produto">
                        <p><strong>ID:</strong> <?= $res['id'] ?></p>
                        <p><strong>Nome:</strong> <?= htmlspecialchars($res['nome']) ?></p>
                        <p><strong>Preço:</strong> R$ <?= number_format($res['preco'], 2, ',', '.') ?></p>
                        <p><strong>Descrição:</strong> <?= nl2br(htmlspecialchars($res['descricao'])) ?></p>
                        <p><img src="<?= htmlspecialchars($res['imagem_url']) ?>" alt="<?= htmlspecialchars($res['nome']) ?>"></p>

                        <div class="produto-actions">
                            <div class="action-btn delete-btn" onclick="if(confirm('Tem certeza que deseja excluir?')) window.location.href='moderar.php?excluir=<?= $res['id'] ?>'">
                                <svg class="action-icon" viewBox="0 0 24 24" aria-hidden="true">
                                    <g><path d="M16 6V4.5C16 3.12 14.88 2 13.5 2h-3C9.12 2 8 3.12 8 4.5V6H3v2h1.06l.81 11.21C4.95 20.62 5.94 22 7.19 22h9.62c1.25 0 2.24-1.38 2.32-2.79L19.94 8H21V6h-5zm-6-1.5c0-.28.22-.5.5-.5h3c.28 0 .5.22.5.5V6h-4V4.5zm8.94 13.71c-.03.34-.35.79-.82.79H7.19c-.47 0-.79-.45-.82-.79L5.56 8h13.89l-.81 11.21zM9 17h6c.55 0 1 .45 1 1s-.45 1-1 1H9c-.55 0-1-.45-1-1s.45-1 1-1zm0-3h6c.55 0 1 .45 1 1s-.45 1-1 1H9c-.55 0-1-.45-1-1s.45-1 1-1zm0-3h6c.55 0 1 .45 1 1s-.45 1-1 1H9c-.55 0-1-.45-1-1s.45-1 1-1z"></path></g>
                                </svg>
                                Excluir
                            </div>
                            
                            <div class="action-btn" onclick="window.location.href='moderar.php?editar=<?= $res['id'] ?>'">
                                <svg class="action-icon" viewBox="0 0 24 24" aria-hidden="true">
                                    <g><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34a.9959.9959 0 0 0-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"></path></g>
                                </svg>
                                Editar
                            </div>
                        </div>

                        <!-- Formulário de edição inline -->
                        <?php if($editar_id == $res['id']): ?>
                            <div class="edit-form">
                                <form method="post" action="moderar.php">
                                    <input type="hidden" name="id" value="<?= $res['id'] ?>">
                                    <input type="text" name="nome" value="<?= htmlspecialchars($res['nome']) ?>" placeholder="Nome do produto" required>
                                    <textarea name="descricao" placeholder="Descrição do produto" required><?= htmlspecialchars($res['descricao']) ?></textarea>
                                    <input type="number" step="0.01" name="preco" value="<?= $res['preco'] ?>" placeholder="Preço" required>
                                    <input type="submit" name="editar" value="Salvar">
                                    <a class="cancel-btn" href="moderar.php">Cancelar</a>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
        
        <!-- Sidebar direita -->
        <div class="sidebar-right">
            <div class="search-container">
                <input type="text" class="search-input" placeholder="Pesquisar">
            </div>
            
            <div style="background-color: #16181c; border-radius: 16px; padding: 16px; margin-bottom: 16px;">
                <h2 style="font-size: 20px; margin-bottom: 12px;">O que está acontecendo</h2>
                <div style="padding: 12px 0; border-bottom: 1px solid #2f3336;">
                    <div style="font-size: 13px; color: #71767b;">Tendência no Brasil</div>
                    <div style="font-weight: 700;">#ModeraçãoDeProdutos</div>
                    <div style="font-size: 13px; color: #71767b;">1.234 posts</div>
                </div>
                <div style="padding: 12px 0;">
                    <div style="font-size: 13px; color: #71767b;">Tendência em Tecnologia</div>
                    <div style="font-weight: 700;">#PHP</div>
                    <div style="font-size: 13px; color: #71767b;">5.678 posts</div>
                </div>
            </div>
            
            <div style="background-color: #16181c; border-radius: 16px; padding: 16px;">
                <h2 style="font-size: 20px; margin-bottom: 12px;">Quem seguir</h2>
                <div style="padding: 12px 0; display: flex; align-items: center;">
                    <div style="width: 48px; height: 48px; background-color: #1d9bf0; border-radius: 50%; margin-right: 12px;"></div>
                    <div style="flex: 1;">
                        <div style="font-weight: 700;">Dev PHP</div>
                        <div style="font-size: 14px; color: #71767b;">@devphp</div>
                    </div>
                    <button style="background-color: #eff3f4; color: #0f1419; border: none; border-radius: 24px; padding: 8px 16px; font-weight: 700; cursor: pointer;">Seguir</button>
                </div>
                <div style="padding: 12px 0; display: flex; align-items: center;">
                    <div style="width: 48px; height: 48px; background-color: #1d9bf0; border-radius: 50%; margin-right: 12px;"></div>
                    <div style="flex: 1;">
                        <div style="font-weight: 700;">Web Developer</div>
                        <div style="font-size: 14px; color: #71767b;">@webdev</div>
                    </div>
                    <button style="background-color: #eff3f4; color: #0f1419; border: none; border-radius: 24px; padding: 8px 16px; font-weight: 700; cursor: pointer;">Seguir</button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>