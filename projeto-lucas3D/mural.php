<?php
include "conexao.php";

// Inserir novo pedido/recado
if(isset($_POST['cadastra'])){
    $nome  = mysqli_real_escape_string($conexao, $_POST['nome']);
    $email = mysqli_real_escape_string($conexao, $_POST['email']);
    $msg   = mysqli_real_escape_string($conexao, $_POST['msg']);

    $sql = "INSERT INTO componentes (nome, email, mensagem) VALUES ('$nome', '$email', '$msg')";
    mysqli_query($conexao, $sql) or die("Erro ao inserir dados: " . mysqli_error($conexao));
    header("Location: mural.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="utf-8"/>
<title>Mural de pedidos - Twitter Style</title>
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

/* Formulário de tweet */
#formulario_mural {
    padding: 15px 20px;
    border-bottom: 1px solid #2f3336;
}

#mural {
    display: flex;
    flex-direction: column;
}

#mural label {
    display: none;
}

#mural input[type="text"],
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
}

#mural textarea {
    min-height: 120px;
    border-bottom: none;
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

/* Lista de tweets */
.componentes {
    padding: 15px 20px;
    border-bottom: 1px solid #2f3336;
    display: flex;
    transition: background-color 0.2s;
}

.componentes:hover {
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

.tweet-content {
    flex: 1;
}

.tweet-header {
    display: flex;
    align-items: center;
    margin-bottom: 5px;
}

.tweet-author {
    font-weight: 700;
    margin-right: 5px;
}

.tweet-handle, .tweet-time {
    color: #71767b;
    margin-right: 5px;
}

.tweet-dot {
    color: #71767b;
    margin: 0 5px;
}

.tweet-text {
    font-size: 16px;
    line-height: 1.5;
    margin-bottom: 10px;
}

.tweet-actions-footer {
    display: flex;
    justify-content: space-between;
    max-width: 80%;
    color: #71767b;
}

.tweet-action {
    display: flex;
    align-items: center;
    cursor: pointer;
}

.tweet-action i {
    margin-right: 5px;
    padding: 8px;
    border-radius: 50%;
    transition: background-color 0.2s, color 0.2s;
}

.tweet-action:hover i {
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
            email: { required: true, email: true },
            msg: { required: true, minlength: 10 }
        },
        messages: {
            nome: { required: "Digite o seu nome", minlength: "O nome deve ter no mínimo 4 caracteres" },
            email: { required: "Digite o seu e-mail", email: "Digite um e-mail válido" },
            msg: { required: "Digite sua mensagem", minlength: "A mensagem deve ter no mínimo 10 caracteres" }
        }
    });

    // Contador de caracteres
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
            <h1>Página Inicial</h1>
        </div>

        <div id="formulario_mural">
            <form id="mural" method="post">
                <input type="text" name="nome" placeholder="Seu nome"/><br/>
                <input type="text" name="email" placeholder="Seu e-mail"/><br/>
                <textarea name="msg" placeholder="O que está acontecendo?"></textarea>
                <div class="tweet-actions">
                    <div class="tweet-icons">
                        <i class="far fa-image"></i>
                        <i class="fas fa-poll"></i>
                        <i class="far fa-smile"></i>
                        <i class="far fa-calendar"></i>
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div>
                        <span class="char-count" style="color: #71767b;">280</span>
                        <input type="submit" value="Publicar" name="cadastra" class="btn"/>
                    </div>
                </div>
            </form>
        </div>

        <?php
        $seleciona = mysqli_query($conexao, "SELECT * FROM componentes ORDER BY id DESC");
        while($res = mysqli_fetch_assoc($seleciona)){
            $initial = substr($res['nome'], 0, 1);
            $time_ago = "· " . rand(1, 23) . "h";
            
            echo '<div class="componentes">';
            echo '<div class="avatar">' . strtoupper($initial) . '</div>';
            echo '<div class="tweet-content">';
            echo '<div class="tweet-header">';
            echo '<span class="tweet-author">' . htmlspecialchars($res['nome']) . '</span>';
            echo '<span class="tweet-handle">@' . strtolower(str_replace(' ', '', htmlspecialchars($res['nome']))) . '</span>';
            echo '<span class="tweet-dot">·</span>';
            echo '<span class="tweet-time">' . $time_ago . '</span>';
            echo '</div>';
            echo '<div class="tweet-text">' . nl2br(htmlspecialchars($res['mensagem'])) . '</div>';
            echo '<div class="tweet-actions-footer">';
            echo '<div class="tweet-action"><i class="far fa-comment"></i><span>' . rand(1, 50) . '</span></div>';
            echo '<div class="tweet-action"><i class="fas fa-retweet"></i><span>' . rand(1, 30) . '</span></div>';
            echo '<div class="tweet-action"><i class="far fa-heart"></i><span>' . rand(1, 100) . '</span></div>';
            echo '<div class="tweet-action"><i class="fas fa-share"></i></div>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }
        ?>

        <div id="footer">
            <p>Mural de Pedidos - Estilo Twitter © 2023</p>
        </div>
    </div>

    <!-- Right sidebar (trends e sugestões) -->
    <div class="right-sidebar">
        <div class="search-container">
            <i class="fas fa-search" style="color: #71767b; margin-right: 10px;"></i>
            <input type="text" placeholder="Buscar no Mural">
        </div>

        <div class="trends-container">
            <div class="trends-header">O que está happening</div>
            <div class="trend-item">
                <div class="trend-category">Tendência em Brasil</div>
                <div class="trend-name">#MuralDePedidos</div>
                <div class="trend-tweets">5.218 Tweets</div>
            </div>
            <div class="trend-item">
                <div class="trend-category">Tendência em Tecnologia</div>
                <div class="trend-name">PHP 8</div>
                <div class="trend-tweets">12.4K Tweets</div>
            </div>
            <div class="trend-item">
                <div class="trend-category">Tendência em Design</div>
                <div class="trend-name">UI/UX</div>
                <div class="trend-tweets">23.5K Tweets</div>
            </div>
        </div>

        <div class="who-to-follow">
            <div class="who-to-follow-header">Quem seguir</div>
            <div class="follow-item">
                <div class="follow-profile">
                    <div class="follow-avatar">D</div>
                    <div class="follow-info">
                        <div class="follow-name">Desenvolvedor PHP</div>
                        <div class="follow-handle">@phpdev</div>
                    </div>
                    <button class="follow-btn">Seguir</button>
                </div>
            </div>
            <div class="follow-item">
                <div class="follow-profile">
                    <div class="follow-avatar">W</div>
                    <div class="follow-info">
                        <div class="follow-name">Web Designer</div>
                        <div class="follow-handle">@webdesign</div>
                    </div>
                    <button class="follow-btn">Seguir</button>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>