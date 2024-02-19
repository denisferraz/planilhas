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
<th colspan="4">Lojinha</th>
<tr><td style="background-color: black" colspan="4"></td></tr>
<tr><td align="center" colspan="4">Cadastrar Opções</td>
<tr><td style="background-color: black" colspan="4"></td></tr>

<tr style="background-color: grey">
    <td align="center"><b>Item</b></td>
    <td align="center"><b>Pontos</b></td>
    <td align="center"><b>Limite Mensal</b></td>
    <td align="center"><b>Cadastrar</b></td>
</tr>
<tr>
<form action="acao.php" method="POST">
    <input type="hidden" name="id_job" value="loja">
    <input type="hidden" name="acao" value="Cadastrar">
<td><input class="input-field" type="text" name="item" required></td>
<td><input class="input-field-auditoria" type="number" min="1" name="pontos" required></td>
<td><input class="input-field-auditoria" type="number" min="1" name="limite_mensal" required></td>
<td><input type="submit" value="Cadastrar"></td>
</form>
</tr>

<tr><td style="background-color: black" colspan="4"></td></tr>
<tr><td align="center" colspan="4">Gerenciar Opções</td>
<tr><td style="background-color: black" colspan="4"></td></tr>
<tr style="background-color: grey">
    <td align="center"><b>Item</b></td>
    <td align="center"><b>Pontos</b></td>
    <td align="center"><b>Limite Mensal</b></td>
    <td align="center"><b>Excluir</b></td>
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
    <td align="center">
    <form action="acao.php" method="POST">
    <input type="hidden" name="id_job" value="loja">
    <input type="hidden" name="acao" value="Excluir">
    <input type="hidden" name="id" value="<?php echo $select['id']; ?>">
    <input type="submit" value="Excluir">
    </form>
</td>
</tr>

<?php } ?>
</table>
</div>

</body>
</html>
