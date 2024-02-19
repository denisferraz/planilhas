<?php

session_start();

require('../../conexao.php');
require('../../verifica_login.php');

$dir = substr(__DIR__, -7);

if($dir != $_SESSION['hotel']){
    echo "<script>
    alert('Você não tem permissão para acessar esta pagina!')
    window.location.replace('../../index.html')
    </script>";
    exit();
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../../images/favicon.ico">
    <link rel="shortcut icon" href="../../images/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="../../css/style_tabela.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Comboedas</title>
</head>
<body>
<a href="../../painel.php"><button class="botao-logout-left"><b>Voltar</b></button></a>
<a href="../../logout.php"><button class="botao-logout-right"><?php echo $_SESSION['name'] ?> <b>[Logout]</b></button></a>

<div id="container-topo">
<h1>Comboedas <?php echo $_SESSION['hotel_name']; ?></h1>
</div>

<div id="container-topo">
<div class="botao-acao"><button onclick='window.open("comboedas.php","iframe")' class="botao">Dashboard</button></div>
<div class="botao-acao"><button onclick='window.open("extrato.php","iframe")' class="botao">Extrato</button></div>
<div class="botao-acao"><button onclick='window.open("lojinha.php","iframe")' class="botao">Lojinha</button></div>
</div>
<div id="container-topo">
<?php
if($_SESSION['hierarquia'] != 'Colaborador'){
?>
<div class="botao-topo-chegadas"><a href="javascript:void(0)" onclick='window.open("pontos.php","iframe")'><button><b>Cadastrar Pontos</b></button></a></div>
<div class="botao-topo-inhouse"><a href="javascript:void(0)" onclick='window.open("extrato_all.php","iframe")'><button><b>Extratos Gerais</b></button></a></div>
<div class="botao-topo-inhouse"><a href="javascript:void(0)" onclick='window.open("loja.php","iframe")'><button><b>Gerenciar Lojinha</b></button></a></div>
<?php } ?>
</div>
<iframe name="iframe" id="iframe" src="comboedas.php"></iframe>

</body>
</html>
