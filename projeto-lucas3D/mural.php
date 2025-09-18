<?php
include "conexao.php";

// Configurações do Cloudinary (certifique-se de que estas variáveis estão definidas em conexao.php)
// $cloud_name, $api_key, $api_secret

// Inserir novo produto
if(isset($_POST['cadastra'])){
    // Pegando os dados do formulário (tratamento contra SQL Injection)
    $nome = mysqli_real_escape_string($conexao, $_POST['nome']);
    $descricao = mysqli_real_escape_string($conexao, $_POST['descricao']);
    $preco = floatval($_POST['preco']);
    $imagem_url = ""; // Inicializa a variável que vai guardar a URL da imagem
    
    // --------------------------
    // Upload da imagem para Cloudinary
    // --------------------------
    if(isset($_FILES['imagem']) && $_FILES['imagem']['error'] == 0){
        $cfile = new CURLFile($_FILES['imagem']['tmp_name'], $_FILES['imagem']['type'], $_FILES['imagem']['name']);

        $timestamp = time();
        $string_to_sign = "timestamp=$timestamp$api_secret";
        $signature = sha1($string_to_sign);

        $data = [
            'file' => $cfile,
            'timestamp' => $timestamp,
            'api_key' => $api_key,
            'signature' => $signature
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.cloudinary.com/v1_1/$cloud_name/image/upload");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        if($response === false){ die("Erro no cURL: " . curl_error($ch)); }
        curl_close($ch);

        $result = json_decode($response, true);
        if(isset($result['secure_url'])){
            $imagem_url = $result['secure_url'];
        } else {
            die("Erro no upload: " . print_r($result, true));
        }
    }

    // ==========================
    // Inserindo no banco de dados
    // ==========================
    if($imagem_url != ""){
        // ATENÇÃO: Altere 'produtos' para o nome da sua tabela
        $sql = "INSERT INTO produtos (nome, descricao, preco, imagem_url) VALUES ('$nome', '$descricao', $preco, '$imagem_url')";
        mysqli_query($conexao, $sql) or die("Erro ao inserir: " . mysqli_error($conexao));
    }

    // ==========================
    // REDIRECIONAMENTO
    // ==========================
    header("Location: mural.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="utf-8"/>
<title>Mural de Produtos - Twitter Style</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="scripts/jquery.js"></script>
<script src="scripts/jquery.validate.js"></script>
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
    min-height: 100vh;
}

#main {
    display: flex;
    width: 100%;
    max-width: 1265px;
}

/* Sidebar estilo Twitter */
.sidebar {
    width: 275px;
    padding: 10px;
    position: fixed;
    height: 100vh;
    border-right: 1px solid #2f3336;
}

.logo {
    padding: 10px 15px;
    margin-bottom: 10px;
}

.logo i {
    color: #1d9bf0;
    font-size: 30px;
}

.menu-item {
    display: flex;
    align-items: center;
    padding: 12px 15px;
    margin: 5px 0;
    border-radius: 30px;
    font-size: 20px;
    font-weight: 400;
    cursor: pointer;
    transition: background-color 0.2s;
}

.menu-item:hover {
    background-color: #181818;
}

.menu-item i {
    margin-right: 15px;
}

.menu-item span {
    font-size: 19px;
}

.tweet-btn {
    background-color: #1d9bf0;
    color: white;
    border: none;
    border-radius: 30px;
    padding: 15px 0;
    width: 90%;
    font-size: 17px;
    font-weight: 700;
    margin-top: 10px;
    cursor: pointer;
    transition: background-color 0.2s;
}

.tweet-btn:hover {
    background-color: #1a8cd8;
}

/* Conteúdo principal */
#geral {
    margin-left: 275px;
    width: 600px;
    border-left: 1px solid #2f3336;
    border-right: 1px solid #2f3336;
    min-height: 100vh;
}

#header {
    padding: 15px 20px;
    border-bottom: 1px solid #2f3336;
    position: sticky;
    top: 0;
    background-color: rgba(0, 0, 0, 0.65);
    backdrop-filter: blur(12px);
    z-index: 10;
}

#header h1 {
    font-size: 20px;
    font-weight: 700;
}

/* Formulário de produto */
#formulario_mural {
    padding: 15px 20px;
    border-bottom: 1px solid #2f3336;
}

#mural {
    display: flex;
    flex-direction: column;
}

#mural label {
    color: #71767b;
    margin-bottom: 5px;
    font-size: 14px;
}

#mural input[type="text"],
#mural input[type="number"],
#mural textarea {
    background-color: transparent;
    border: none;
    color: #e7e9ea;
    font-size: 19px;
    padding: 15px 0;
    resize: none;
    outline: none;
    width: 100%;
    border-bottom: 1px solid #2f3336;
    margin-bottom: 15px;
}

#mural textarea {
    min-height: 120px;
    border-bottom: none;
}

#mural input[type="file"] {
    background-color: transparent;
    color: #e7e9ea;
    padding: 10px 0;
    margin-bottom: 15px;
}

.tweet-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 10px;
}

.tweet-icons {
    display: flex;
    gap: 15px;
    color: #1d9bf0;
}

.tweet-icons i {
    cursor: pointer;
    font-size: 18px;
}

.btn {
    background-color: #1d9bf0;
    color: white;
    border: none;
    border-radius: 30px;
    padding: 10px 20px;
    font-weight: 700;
    cursor: pointer;
    transition: background-color 0.2s;
}

.btn:hover {
    background-color: #1a8cd8;
}

.btn:disabled {
    background-color: #1d9bf0;
    opacity: 0.5;
    cursor: default;
}

/* Lista de produtos */
.produtos {
    padding: 15px 20px;
    border-bottom: 1px solid #2f3336;
    transition: background-color 0.2s;
}

.produtos:hover {
    background-color: #080808;
}

.avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background-color: #1d9bf0;
    margin-right: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: white;
    font-weight: bold;
}

.produto-content {
    flex: 1;
}

.produto-header {
    display: flex;
    align-items: center;
    margin-bottom: 5px;
}

.produto-nome {
    font-weight: 700;
    margin-right: 5px;
}

.produto-preco {
    color: #1d9bf0;
    font-weight: 700;
    margin-left: 10px;
}

.produto-descricao {
    font-size: 16px;
    line-height: 1.5;
    margin-bottom: 10px;
}

.produto-imagem {
    width: 100%;
    border-radius: 15px;
    margin: 10px 0;
    max-height: 350px;
    object-fit: cover;
}

.produto-actions-footer {
    display: flex;
    justify-content: space-between;
    max-width: 80%;
    color: #71767b;
}

.produto-action {
    display: flex;
    align-items: center;
    cursor: pointer;
}

.produto-action i {
    margin-right: 5px;
    padding: 8px;
    border-radius: 50%;
    transition: background-color 0.2s, color 0.2s;
}

.produto-action:hover i {
    background-color: rgba(29, 155, 240, 0.1);
    color: #1d9bf0;
}

/* Footer */
#footer {
    padding: 15px 20px;
    text-align: center;
    color: #71767b;
    font-size: 14px;
    border-top: 1px solid #2f3336;
}

/* Right sidebar */
.right-sidebar {
    width: 350px;
    padding: 15px;
    margin-left: 20px;
}

.search-container {
    background-color: #202327;
    border-radius: 30px;
    padding: 12px 15px;
    margin-bottom: 20px;
}

.search-container input {
    background-color: transparent;
    border: none;
    color: #e7e9ea;
    font-size: 16px;
    width: 100%;
    outline: none;
}

.trends-container, .who-to-follow {
    background-color: #16181c;
    border-radius: 15px;
    margin-bottom: 20px;
}

.trends-header, .who-to-follow-header {
    padding: 15px;
    border-bottom: 1px solid #2f3336;
    font-weight: 700;
    font-size: 20px;
}

.trend-item, .follow-item {
    padding: 15px;
    border-bottom: 1px solid #2f3336;
    transition: background-color 0.2s;
    cursor: pointer;
}

.trend-item:hover, .follow-item:hover {
    background-color: #1e2022;
}

.trend-category, .follow-category {
    font-size: 13px;
    color: #71767b;
}

.trend-name {
    font-weight: 700;
    margin: 5px 0;
}

.trend-tweets {
    font-size: 13px;
    color: #71767b;
}

.follow-profile {
    display: flex;
    align-items: center;
    margin-bottom: 10px;
}

.follow-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: #1d9bf0;
    margin-right: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
}

.follow-info {
    flex: 1;
}

.follow-name {
    font-weight: 700;
}

.follow-handle {
    color: #71767b;
}

.follow-btn {
    background-color: transparent;
    border: 1px solid #3d3d3d;
    color: #e7e9ea;
    border-radius: 30px;
    padding: 5px 15px;
    font-weight: 700;
    cursor: pointer;
    transition: background-color 0.2s;
}

.follow-btn:hover {
    background-color: #1a8cd8;
    border-color: #1a8cd8;
}

/* Responsividade */
@media (max-width: 1200px) {
    .sidebar {
        width: 80px;
    }
    
    .menu-item span {
        display: none;
    }
    
    .menu-item i {
        margin-right: 0;
        font-size: 24px;
    }
    
    .tweet-btn span {
        display: none;
    }
    
    .tweet-btn {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
    }
    
    .tweet-btn i {
        display: block;
        font-size: 20px;
    }
    
    #geral {
        margin-left: 80px;
    }
    
    .right-sidebar {
        display: none;
    }
}

@media (max-width: 800px) {
    .sidebar {
        display: none;
    }
    
    #geral {
        margin-left: 0;
        width: 100%;
    }
}

/* Validação */
label.error {
    color: #f4212e;
    font-size: 14px;
    margin-top: 5px;
    display: block;
}

input.error, textarea.error {
    border-color: #f4212e !important;
}
</style>
<script>
$(document).ready(function() {
    $("#mural").validate({
        rules: {
            nome: { required: true, minlength: 4 },
            descricao: { required: true, minlength: 10 },
            preco: { required: true, number: true, min: 0.01 }
        },
        messages: {
            nome: { required: "Digite o nome do produto", minlength: "O nome deve ter no mínimo 4 caracteres" },
            descricao: { required: "Digite a descrição do produto", minlength: "A descrição deve ter no mínimo 10 caracteres" },
            preco: { required: "Digite o preço do produto", number: "Digite um valor numérico válido", min: "O preço deve ser maior que zero" }
        }
    });

    // Contador de caracteres para descrição
    $('textarea').on('input', function() {
        const maxLength = 280;
        const currentLength = $(this).val().length;
        const remaining = maxLength - currentLength;
        
        if (remaining < 0) {
            $('.char-count').css('color', '#f4212e');
        } else if (remaining <= 20) {
            $('.char-count').css('color', '#ffd400');
        } else {
            $('.char-count').css('color', '#71767b');
        }
        
        $('.char-count').text(remaining);
    });
});
</script>
</head>
<body>
<div id="main">
    <!-- Sidebar estilo Twitter -->
    <div class="sidebar">
        <div class="logo">
            <i class="fab fa-twitter"></i>
        </div>
        <div class="menu-item">
            <i class="fas fa-home"></i>
            <span>Página Inicial</span>
        </div>
        <div class="menu-item">
            <i class="fas fa-shopping-cart"></i>
            <span>Produtos</span>
        </div>
        <div class="menu-item">
            <i class="fas fa-bell"></i>
            <span>Notificações</span>
        </div>
        <div class="menu-item">
            <i class="fas fa-envelope"></i>
            <span>Mensagens</span>
        </div>
        <div class="menu-item">
            <i class="fas fa-bookmark"></i>
            <span>Itens salvos</span>
        </div>
        <div class="menu-item">
            <i class="fas fa-user"></i>
            <span>Perfil</span>
        </div>
        <div class="menu-item">
            <i class="fas fa-ellipsis-h"></i>
            <span>Mais</span>
        </div>
        <button class="tweet-btn">
            <span>Publicar</span>
            <i class="fas fa-plus" style="display: none;"></i>
        </button>
    </div>

    <div id="geral">
        <div id="header">
            <h1>Mural de Produtos</h1>
        </div>

        <div id="formulario_mural">
            <form id="mural" method="post" enctype="multipart/form-data">
                <label>Nome do produto:</label>
                <input type="text" name="nome" placeholder="Nome do produto"/>
                
                <label>Descrição:</label>
                <textarea name="descricao" placeholder="Descreva o produto"></textarea>
                
                <label>Preço (R$):</label>
                <input type="number" step="0.01" name="preco" placeholder="0.00"/>
                
                <label>Imagem do produto:</label>
                <input type="file" name="imagem" accept="image/*"/>
                
                <div class="tweet-actions">
                    <div class="tweet-icons">
                        <i class="far fa-image"></i>
                        <i class="fas fa-tag"></i>
                        <i class="far fa-smile"></i>
                    </div>
                    <div>
                        <span class="char-count" style="color: #71767b;">280</span>
                        <input type="submit" value="Publicar Produto" name="cadastra" class="btn"/>
                    </div>
                </div>
            </form>
        </div>

        <?php
        // ATENÇÃO: Altere 'produtos' para o nome da sua tabela
        $seleciona = mysqli_query($conexao, "SELECT * FROM produtos ORDER BY id DESC");
        while($res = mysqli_fetch_assoc($seleciona)){
            $initial = substr($res['nome'], 0, 1);
            $time_ago = "· " . rand(1, 23) . "h";
            
            echo '<div class="produtos">';
            echo '<div style="display: flex;">';
            echo '<div class="avatar">' . strtoupper($initial) . '</div>';
            echo '<div class="produto-content">';
            echo '<div class="produto-header">';
            echo '<span class="produto-nome">' . htmlspecialchars($res['nome']) . '</span>';
            echo '<span class="produto-preco">R$ ' . number_format($res['preco'], 2, ',', '.') . '</span>';
            echo '<span class="tweet-dot">·</span>';
            echo '<span class="tweet-time">' . $time_ago . '</span>';
            echo '</div>';
            echo '<div class="produto-descricao">' . nl2br(htmlspecialchars($res['descricao'])) . '</div>';
            
            if (!empty($res['imagem_url'])) {
                echo '<img src="' . htmlspecialchars($res['imagem_url']) . '" alt="' . htmlspecialchars($res['nome']) . '" class="produto-imagem">';
            }
            
            echo '<div class="produto-actions-footer">';
            echo '<div class="produto-action"><i class="far fa-comment"></i><span>' . rand(1, 50) . '</span></div>';
            echo '<div class="produto-action"><i class="fas fa-shopping-cart"></i><span>Comprar</span></div>';
            echo '<div class="produto-action"><i class="far fa-heart"></i><span>' . rand(1, 100) . '</span></div>';
            echo '<div class="produto-action"><i class="fas fa-share"></i></div>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }
        ?>

        <div id="footer">
            <p>Mural de Produtos - Estilo Twitter © 2023</p>
        </div>
    </div>

    <!-- Right sidebar (trends e sugestões) -->
    <div class="right-sidebar">
        <div class="search-container">
            <i class="fas fa-search" style="color: #71767b; margin-right: 10px;"></i>
            <input type="text" placeholder="Buscar produtos">
        </div>

        <div class="trends-container">
            <div class="trends-header">Produtos em Destaque</div>
            <div class="trend-item">
                <div class="trend-category">Tendência em Eletrônicos</div>
                <div class="trend-name">#Smartphones</div>
                <div class="trend-tweets">5.218 Produtos</div>
            </div>
            <div class="trend-item">
                <div class="trend-category">Tendência em Moda</div>
                <div class="trend-name">Roupas de Verão</div>
                <div class="trend-tweets">12.4K Produtos</div>
            </div>
            <div class="trend-item">
                <div class="trend-category">Tendência em Casa</div>
                <div class="trend-name">Decoração</div>
                <div class="trend-tweets">23.5K Produtos</div>
            </div>
        </div>

        <div class="who-to-follow">
            <div class="who-to-follow-header">Lojas em Destaque</div>
            <div class="follow-item">
                <div class="follow-profile">
                    <div class="follow-avatar">T</div>
                    <div class="follow-info">
                        <div class="follow-name">Tech Store</div>
                        <div class="follow-handle">@techstore</div>
                    </div>
                    <button class="follow-btn">Seguir</button>
                </div>
            </div>
            <div class="follow-item">
                <div class="follow-profile">
                    <div class="follow-avatar">F</div>
                    <div class="follow-info">
                        <div class="follow-name">Fashion Shop</div>
                        <div class="follow-handle">@fashionshop</div>
                    </div>
                    <button class="follow-btn">Seguir</button>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>