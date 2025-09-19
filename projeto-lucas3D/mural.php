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
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/jquery.validate.min.js"></script>
<style>
:root {
    --twitter-primary: #1D9BF0;
    --twitter-background: #000000;
    --twitter-text: #E7E9EA;
    --twitter-gray: #71767B;
    --twitter-dark-gray: #2F3336;
    --twitter-hover: #181818;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
}

body {
    background-color: var(--twitter-background);
    color: var(--twitter-text);
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
    border-right: 1px solid var(--twitter-dark-gray);
    display: flex;
    flex-direction: column;
}

.logo {
    padding: 10px 15px;
    margin-bottom: 10px;
}

.logo i {
    color: var(--twitter-primary);
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
    background-color: var(--twitter-hover);
}

.menu-item i {
    margin-right: 15px;
}

.menu-item span {
    font-size: 19px;
}

.tweet-btn {
    background-color: var(--twitter-primary);
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
    border-left: 1px solid var(--twitter-dark-gray);
    border-right: 1px solid var(--twitter-dark-gray);
    min-height: 100vh;
}

#header {
    padding: 15px 20px;
    border-bottom: 1px solid var(--twitter-dark-gray);
    position: sticky;
    top: 0;
    background-color: rgba(0, 0, 0, 0.65);
    backdrop-filter: blur(12px);
    z-index: 10;
    font-size: 20px;
    font-weight: 700;
}

/* Formulário de produto */
#formulario_mural {
    padding: 15px 20px;
    border-bottom: 1px solid var(--twitter-dark-gray);
}

#mural {
    display: flex;
    flex-direction: column;
}

#mural label {
    color: var(--twitter-gray);
    margin-bottom: 5px;
    font-size: 14px;
    display: block;
}

#mural input[type="text"],
#mural input[type="number"],
#mural textarea {
    background-color: transparent;
    border: none;
    color: var(--twitter-text);
    font-size: 19px;
    padding: 15px 0;
    resize: none;
    outline: none;
    width: 100%;
    border-bottom: 1px solid var(--twitter-dark-gray);
    margin-bottom: 15px;
}

#mural textarea {
    min-height: 120px;
    border-bottom: none;
}

#mural input[type="file"] {
    background-color: transparent;
    color: var(--twitter-text);
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
    color: var(--twitter-primary);
}

.tweet-icons i {
    cursor: pointer;
    font-size: 18px;
}

.btn {
    background-color: var(--twitter-primary);
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
    background-color: var(--twitter-primary);
    opacity: 0.5;
    cursor: default;
}

/* Lista de produtos */
.produtos {
    padding: 15px 20px;
    border-bottom: 1px solid var(--twitter-dark-gray);
    transition: background-color 0.2s;
    cursor: pointer;
}

.produtos:hover {
    background-color: var(--twitter-hover);
}

.avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background-color: var(--twitter-primary);
    margin-right: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: white;
    font-weight: bold;
    flex-shrink: 0;
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
    color: var(--twitter-primary);
    font-weight: 700;
    margin-left: 10px;
}

.produto-descricao {
    font-size: 16px;
    line-height: 1.5;
    margin-bottom: 10px;
    word-break: break-word;
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
    color: var(--twitter-gray);
    margin-top: 10px;
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
    color: var(--twitter-primary);
}

/* Footer */
#footer {
    padding: 15px 20px;
    text-align: center;
    color: var(--twitter-gray);
    font-size: 14px;
    border-top: 1px solid var(--twitter-dark-gray);
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
    display: flex;
    align-items: center;
}

.search-container i {
    color: var(--twitter-gray);
    margin-right: 10px;
}

.search-container input {
    background-color: transparent;
    border: none;
    color: var(--twitter-text);
    font-size: 16px;
    width: 100%;
    outline: none;
}

.trends-container, .who-to-follow {
    background-color: #16181c;
    border-radius: 15px;
    margin-bottom: 20px;
    overflow: hidden;
}

.trends-header, .who-to-follow-header {
    padding: 15px;
    border-bottom: 1px solid var(--twitter-dark-gray);
    font-weight: 700;
    font-size: 20px;
}

.trend-item, .follow-item {
    padding: 15px;
    border-bottom: 1px solid var(--twitter-dark-gray);
    transition: background-color 0.2s;
    cursor: pointer;
}

.trend-item:hover, .follow-item:hover {
    background-color: #1e2022;
}

.trend-item:last-child, .follow-item:last-child {
    border-bottom: none;
}

.trend-category, .follow-category {
    font-size: 13px;
    color: var(--twitter-gray);
    display: flex;
    justify-content: space-between;
}

.trend-name {
    font-weight: 700;
    margin: 5px 0;
    font-size: 15px;
}

.trend-tweets {
    font-size: 13px;
    color: var(--twitter-gray);
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
    background-color: var(--twitter-primary);
    margin-right: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    flex-shrink: 0;
}

.follow-info {
    flex: 1;
}

.follow-name {
    font-weight: 700;
}

.follow-handle {
    color: var(--twitter-gray);
}

.follow-btn {
    background-color: transparent;
    border: 1px solid #3d3d3d;
    color: var(--twitter-text);
    border-radius: 30px;
    padding: 5px 15px;
    font-weight: 700;
    cursor: pointer;
    transition: background-color 0.2s;
}

.follow-btn:hover {
    background-color: var(--twitter-primary);
    border-color: var(--twitter-primary);
}

/* Tweet time */
.tweet-time {
    color: var(--twitter-gray);
    margin-left: 5px;
}

/* Responsividade */
@media (max-width: 1200px) {
    .sidebar {
        width: 88px;
        align-items: center;
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
        display: block !important;
        font-size: 20px;
    }
    
    #geral {
        margin-left: 88px;
        width: 600px;
    }
    
    .right-sidebar {
        display: none;
    }
}

@media (max-width: 1000px) {
    .right-sidebar {
        display: none;
    }
    
    #geral {
        width: 100%;
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

/* Loading spinner */
.spinner {
    width: 20px;
    height: 20px;
    border: 3px solid rgba(255, 255, 255, 0.3);
    border-radius: 50%;
    border-top-color: #fff;
    animation: spin 1s ease-in-out infinite;
    display: none;
    margin-right: 10px;
}

@keyframes spin {
    to { transform: rotate(360deg); }
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
        },
        submitHandler: function(form) {
            $('.spinner').show();
            $('.btn').prop('disabled', true);
            form.submit();
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
            $('.char-count').css('color', 'var(--twitter-gray)');
        }
        
        $('.char-count').text(remaining);
    });

    // Preview de imagem antes do upload
    $('input[type="file"]').change(function() {
        if (this.files && this.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#image-preview').remove();
                $('<img>').attr({
                    id: 'image-preview',
                    src: e.target.result,
                    class: 'produto-imagem',
                    style: 'max-width: 200px; max-height: 200px; margin-top: 10px;'
                }).insertAfter('input[type="file"]');
            }
            reader.readAsDataURL(this.files[0]);
        }
    });
});
</script>
</head>
<body>
<div id="main">
    <!-- Sidebar estilo Twitter -->
    <div class="sidebar">
        <div class="logo">
            <i class="fab fa-x-twitter"></i>
        </div>
        <div class="menu-item">
            <i class="fas fa-home"></i>
            <span>Página Inicial</span>
        </div>
        <div class="menu-item">
            <i class="fas fa-search"></i>
            <span>Explorar</span>
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
            <i class="fas fa-shopping-cart"></i>
            <span>Produtos</span>
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
            <h1>Página Inicial</h1>
        </div>

        <div id="formulario_mural">
            <form id="mural" method="post" enctype="multipart/form-data">
                <div style="display: flex; padding: 15px;">
                    <div class="avatar">U</div>
                    <div style="flex: 1;">
                        <label>Nome do produto:</label>
                        <input type="text" name="nome" placeholder="Nome do produto"/>
                        
                        <label>Descrição:</label>
                        <textarea name="descricao" placeholder="Descreva o produto"></textarea>
                        
                        <label>Preço (R$):</label>
                        <input type="number" step="0.01" name="preco" placeholder="0.00"/>
                        
                        <label>Imagem do produto:</label>
                        <input type="file" name="imagem" accept="image/*"/>
                    </div>
                </div>
                
                <div class="tweet-actions">
                    <div class="tweet-icons">
                        <i class="far fa-image" onclick="$('input[type=file]').click();"></i>
                        <i class="fas fa-tag"></i>
                        <i class="far fa-smile"></i>
                    </div>
                    <div style="display: flex; align-items: center;">
                        <span class="char-count" style="color: var(--twitter-gray);">280</span>
                        <div class="spinner"></div>
                        <input type="submit" value="Publicar" name="cadastra" class="btn"/>
                    </div>
                </div>
            </form>
        </div>

        <?php
        // ATENÇÃO: Altere 'produtos' para o nome da sua tabela
        $table_check = mysqli_query($conexao, "SHOW TABLES LIKE 'produtos'");
        if(mysqli_num_rows($table_check) > 0){
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
                echo '<span class="tweet-time">' . $time_ago . '</span>';
                echo '</div>';
                echo '<div class="produto-descricao">' . nl2br(htmlspecialchars($res['descricao'])) . '</div>';
                
                if (!empty($res['imagem_url'])) {
                    echo '<img src="' . htmlspecialchars($res['imagem_url']) . '" alt="' . htmlspecialchars($res['nome']) . '" class="produto-imagem">';
                }
                
                echo '<div class="produto-header">';
                echo '<span class="produto-preco">R$ ' . number_format($res['preco'], 2, ',', '.') . '</span>';
                echo '</div>';
                
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
        } else {
            echo "<p>A tabela 'produtos' não existe no banco de dados.</p>";
        }
        ?>

        <div id="footer">
            <p>Mural de Produtos - Estilo Twitter © 2023</p>
        </div>
    </div>

    <!-- Right sidebar (trends e sugestões) -->
    <div class="right-sidebar">
        <div class="search-container">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Buscar produtos">
        </div>

        <div class="trends-container">
            <div class="trends-header">Produtos em Destaque</div>
            <div class="trend-item">
                <div class="trend-category">
                    <span>Tendência em Eletrônicos</span>
                    <i class="fas fa-ellipsis"></i>
                </div>
                <div class="trend-name">#Smartphones</div>
                <div class="trend-tweets">5.218 Produtos</div>
            </div>
            <div class="trend-item">
                <div class="trend-category">
                    <span>Tendência em Moda</span>
                    <i class="fas fa-ellipsis"></i>
                </div>
                <div class="trend-name">Roupas de Verão</div>
                <div class="trend-tweets">12.4K Produtos</div>
            </div>
            <div class="trend-item">
                <div class="trend-category">
                    <span>Tendência em Casa</span>
                    <i class="fas fa-ellipsis"></i>
                </div>
                <div class="trend-name">Decoração</div>
                <div class="trend-tweets">23.5K Produtos</div>
            </div>
            <div class="trend-item" style="color: var(--twitter-primary);">
                Mostrar mais
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
            <div class="follow-item" style="color: var(--twitter-primary);">
                Mostrar mais
            </div>
        </div>
    </div>
</div>
</body>
</html>