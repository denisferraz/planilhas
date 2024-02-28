<?php
session_start();

require('../../conexao.php');
require('../../verifica_login.php');

$dir = substr(__DIR__, -7);

if($dir != $_SESSION['hotel']){
    echo "<script>
    alert('Você não tem permissão para acessar esta pagina!')
    top.location.replace('../../index.html')
    </script>";
    exit();
}

error_reporting(0);

$reserva = mysqli_real_escape_string($conn_mysqli, $_GET['id']);
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
    <title>Anexar Arquivo PDF</title>
</head>
<body>

<div class="container">
    <form method="POST" id="arquivo_freestay" enctype="multipart/form-data">
    <h1>Salve arquivo [<?php echo $reserva ?>]</h1><center>
            <label>Selecionar um Arquivo PDF</label>
            <input type="file" name="arquivos" id="arquivo" accept=".pdf" required>
            <br>
            <input type="hidden" name="arquivo" value="<?php echo $reserva ?>">
            <input type="hidden" name="reserva" value="<?php echo $reserva ?>" />
            <br>
            <div class="botao-topo"><button type="submit">Salvar PDF</button></div>
    </center></form>
</div>

</body>
</html>

<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $reserva = mysqli_real_escape_string($conn_mysqli, $_POST['reserva']);
    $arquivo = mysqli_real_escape_string($conn_mysqli, $_POST['arquivo']).'.pdf';
    $arquivos = $_FILES['arquivos'];
    $dirAtual = 'arquivos/';

    if($arquivos['type'] != 'application/pdf'){
        echo "<script>
        alert('Selecione apenas arquivos tipo PDF')
        </script>";
        exit();
    }

    if (!is_dir($dirAtual)) {
        mkdir($dirAtual, 0777, true);
    }

    $caminhoCompleto = $dirAtual . $arquivo;

    move_uploaded_file($arquivos['tmp_name'], $caminhoCompleto);

    echo "<script>
    alert('Arquivo Cadastrado com Sucesso $reserva')
    window.close()
    </script>";
    exit();

}


?>