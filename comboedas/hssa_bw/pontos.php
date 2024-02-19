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
<form action="acao.php" method="POST">
    <center>
<table>
<th colspan="5">Pontos</th>
<tr><td style="background-color: black" colspan="5"></td></tr>
<tr><td align="center" colspan="5">Cadastrar</td>
<tr><td style="background-color: black" colspan="5"></td></tr>
<tr style="background-color: grey">
    <td align="center"><b>Colaborador</b></td>
    <td align="center"><b>Data</b></td>
    <td align="center"><b>Pontos</b></td>
    <td align="center"><b>Tipo</b></td>
    <td align="center"><b>Comentario</b></td>
</tr>
<tr>
    <td><select name="colaborador">
    <?php
    $query_colaborador = $conexao->prepare("SELECT * FROM excel_users WHERE hotel LIKE '%" . $_SESSION['hotel'] . "%' ORDER BY nome ASC");
    $query_colaborador->execute();
    while($select_colaborador = $query_colaborador->fetch(PDO::FETCH_ASSOC)){
    ?>
            <option value="<?php echo $select_colaborador['nome'] ?>"><?php echo $select_colaborador['nome'] ?></option>
    <?php } ?>
        </select></td>
    <td align="center"><?php echo date('d/m/Y', strtotime("$hoje")); ?></td>
    <td><input class="input-field-auditoria" type="number" min="1" name="pontos" required></td>
    <td><select name="pontos_tipo">
            <option value="Entrada">Entrada</option>
            <option value="Saida">Saida</option>
        </select></td>
        <td><input class="input-field" type="text" name="comentarios" required></td>
</tr>
</table>
<br>
<input type="hidden" name="id_job" value="pontos">
<input type="submit" class="submit" value="Confirmar">
</form>
</div>

</body>
</html>
