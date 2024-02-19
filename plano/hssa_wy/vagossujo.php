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

//Todas os Apartamentos
$dados_vago_sujo = $_SESSION['dados_roomstatus'];

$dados_roomstatus = array_filter($dados_vago_sujo, function($item) {
    return $item['room_status_1'] == "Vago" && $item['room_status_2'] == "Sujo";
});

// Ordenar o array por 'room_number'
usort($dados_roomstatus, function($a, $b) {
    return $a['room_number'] <=> $b['room_number'];
});

$quantidade_dados = count($dados_roomstatus);

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
<legend>Plano de Quartos</legend>
<form action="acao.php" method="POST" id="formulario_plano">
<table>
<th colspan="6">Vagos Sujos</th>
<tr><td style="background-color: black" colspan="6"></td></tr>
<tr><td align="center" colspan="6">Total (<b><?php echo count($dados_roomstatus); ?></b>)</td>
<tr><td style="background-color: black" colspan="6"></td></tr>
<tr style="background-color: grey">
    <td align="center"><b>Quarto</b></td>
    <td align="center"><b>Tipo</b></td>
    <td align="center"><b>Hospede</b></td>
    <td align="center"><b>Prevista</b></td>
    <td align="center"><b>Room Status</b></td>
    <td align="center"><b>Camareira</b></td>
</tr>


<?php
$qtd = 0;
$quantidade = 0;
foreach ($dados_roomstatus as $select) {
    $id = $select['id'];
    $id_camareira = $select['id_camareira'];
    $room_number = $select['room_number'];
    $guest_name = $select['guest_name'];
    $room_stay_status = $select['room_stay_status'];
    $room_status_1 = $select['room_status_1'];
    $room_status_2 = $select['room_status_2'];
    $room_type = $select['room_type'];

    $qtd++;
    $quantidade++;

    if($qtd % 2 == 0){
    $cor_tr = 'darkgrey';
    }else{
    $cor_tr = 'white';  
    }
    ?>
<tr style="background-color: <?php echo $cor_tr; ?>">
    <td align="center"><?php echo $room_number; ?></td>
    <td align="center"><?php echo $room_type; ?></td>
    <td><?php echo $guest_name; ?></td>
    <td><?php echo $room_stay_status; ?></td>
    <td><?php echo $room_status_1; ?> - <?php echo $room_status_2; ?></td>
    <td>
        <select name="camareira_<?php echo $quantidade ?>">
        <option value="0" <?php if ($id_camareira == 0) echo 'selected'; ?>>Sem Camareira</option>
        <?php
        for($camareiras = 1; $camareiras <= $_SESSION['qtd_camareira']; $camareiras++){

            if($camareiras == 0){
                continue;
            }
        ?>
        <option value="<?php echo $_SESSION['id_camareira_'.$camareiras]; ?>" <?php if ($id_camareira == $_SESSION['id_camareira_'.$camareiras]) echo 'selected'; ?>><?php echo $_SESSION['camareira_'.$camareiras]; ?></option>
        <?php
        }
        ?>
        </select>
    </td>
</tr>
<input type="hidden" name="id_<?php echo $quantidade ?>" value="<?php echo $id ?>">
<?php } ?>
</table>
<input type="hidden" name="quantidade" value="<?php echo $quantidade_dados ?>">
<input type="hidden" name="id_job" value="gerar_plano">
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
