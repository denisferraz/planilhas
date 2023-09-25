<?php

session_start();

require('../../conexao.php');
require('../../verifica_login.php');

$dir = substr(__DIR__, -5);

if($dir != $_SESSION['hotel']){
    echo "<script>
    alert('Você não tem permissão para acessar esta pagina!')
    top.location.replace('../../index.html')
    </script>";
    exit();
}

if($_SESSION['status_plano'] == 'Concluido'){
    echo "<script>
    alert('PLano de Quartos não foi Iniciado!')
    top.location.replace('index.php');
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <title>Plano de Quarto</title>
</head>
<body>

<div class="container">
<!-- Diarias -->
<fieldset>
<legend>Camareiras</legend>
<form action="acao.php" method="POST" id="formulario_plano">
<table>
<th colspan="2">Camareiras</th>
<tr><td style="background-color: black" colspan="2"></td></tr>
<tr><td align="center" colspan="2">Total Camareiras (<b><?php echo $_SESSION['camareiras'] ?></b>)</td>
<tr><td style="background-color: black" colspan="2"></td></tr>
<tr style="background-color: grey">
    <td align="center"><b>ID Camareira</b></td>
    <td align="center"><b>Nome Camareira</b></td>
</tr>


<?php
$qtd = 0;
$quantidade = 0;
for($id_camareiras = 1; $id_camareiras <= $_SESSION['camareiras']; $id_camareiras++){

    $qtd++;
    $quantidade++;

    if($qtd % 2 == 0){
    $cor_tr = 'darkgrey';
    }else{
    $cor_tr = 'white';  
    }

    ?>
<tr style="background-color: <?php echo $cor_tr; ?>">
    <td align="center"><b><?php echo $_SESSION['id_camareira_'.$id_camareiras]; ?></b></td>
    <td><input class="input-field" type="text" name="camareira_<?php echo $quantidade ?>" value="<?php echo $_SESSION['camareira_'.$id_camareiras]; ?>" required></td>
</tr>
<?php } ?>
</table>
<br>
<input type="hidden" name="quantidade" value="<?php echo $_SESSION['camareiras'] ?>">
<input type="hidden" name="id_job" value="camareiras">
<input type="submit" class="submit" value="Confirmar">
</form>
</fieldset>
</div>
<script>
$(document).ready(function() {
    $('.replace-comma').on('input', function() {
        // Substituir vírgulas por pontos
        $(this).val($(this).val().replace(',', '.'));
    });
});
</script>
</body>
</html>
