<?php

session_start();

require('../../conexao.php');
require('../../verifica_login.php');

error_reporting(0);

$dir = substr(__DIR__, -7);

if($dir != $_SESSION['hotel']){
    echo "<script>
    alert('Você não tem permissão para acessar esta pagina!')
    top.location.replace('../../index.html');
    </script>";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data_auditoria = $_POST["data_auditoria"];
    $id = strtotime("$data_auditoria");
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
    <title>No-Shows</title>
</head>
<body>
<a href="../../painel.php"><button class="botao-logout-left"><b>Voltar</b></button></a>
<a href="../../logout.php"><button class="botao-logout-right"><?php echo $_SESSION['name'] ?> <b>[Logout]</b></button></a>
<h1><?php echo $_SESSION['hotel_name']; ?></h1>
<div id="container-topo">
</div>
<br>
<div id="container-topo">
<form method="POST" id="formulario_freestay">
<label><b>Selecione o Periodo</b></label>
<input type="month" name="data_auditoria" value="<?php echo $data_auditoria; ?>" required><br>
</form>
<div class="botao-acao">
        <!-- Adicionando um identificador (id) ao botão para fácil manipulação pelo JavaScript -->
        <button class="botao" id="botaoSelecionarData">Selecionar Data</button>
    </div>
</div>

<?php if ($id > 0) { ?>
<iframe name="iframe" id="iframe" src="noshows.php?id=<?php echo $id; ?>"></iframe>
<?php } ?>

<script>
document.getElementById('botaoSelecionarData').addEventListener('click', function() {
    // Captura o formulário
    var formulario = document.getElementById('formulario_freestay');

    // Envie o formulário
    formulario.submit();
});
</script>
</body>
</html>
