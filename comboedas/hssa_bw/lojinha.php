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

error_reporting(0);

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../css/style_tabela.css">
    <link rel="icon" type="image/x-icon" href="../../images/favicon.ico">
    <link rel="shortcut icon" href="../../images/favicon.ico" type="image/x-icon">
    <title>Comboedas</title>
</head>
<body>

<div class="container">
    <center>
<table>
<th colspan="3">Lojinha</th>
<tr><td style="background-color: black" colspan="3"></td></tr>
<tr><td align="center" colspan="3">Opções de Resgate</td>
<tr><td style="background-color: black" colspan="3"></td></tr>
<tr style="background-color: grey">
    <td align="center"><b>Item</b></td>
    <td align="center"><b>Pontos</b></td>
    <td align="center"><b>Limite Mensal</b></td>
</tr>
<?php
$qtd = 0;

//Todas Lojinhas
$query = $conexao->prepare("SELECT * FROM $dir"."_excel_comboedas_lojinha WHERE id > 0 AND status_item = 'Ativo'");
$query->execute();

while($select = $query->fetch(PDO::FETCH_ASSOC)){

    $qtd++;
    if($qtd % 2 == 0){
        $cor_tr = 'darkgrey';
        }else{
        $cor_tr = 'white';  
        }
?>

<tr style="background-color: <?php echo $cor_tr; ?>">
    <td align="center"><?php echo $select['item']; ?></td>
    <td align="center"><?php echo $select['pontos']; ?></td>
    <td align="center"><?php echo $select['limite_mensal']; ?></td>
</tr>

<?php } ?>
</table>
</div>

</body>
</html>
