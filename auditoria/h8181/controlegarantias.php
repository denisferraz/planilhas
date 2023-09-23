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

$data_auditoria = $_SESSION['data_auditoria'];
$comentarios = $_SESSION['comentario_garantias'];
$status_auditoria = $_SESSION['status_auditoria'];

if($status_auditoria == 'Em Andamento Pre'){
    echo "<script>
    alert('Relatorios Pos Auditoria não Importados!')
    window.location.replace('ratecheck.php')
    </script>";
    exit();
}else if($status_auditoria == 'Concluida'){
    echo "<script>
    alert('Auditoria não foi Iniciada!')
    top.location.replace('index.php')
    </script>";
    exit();
}


//Todas os Apartamentos
$dados_presentlist = $_SESSION['dados_presentlist'];

$dados_filtrados = array_filter($dados_presentlist, function($item) use ($data_auditoria) {
    return $item['checkin'] == $data_auditoria;
});

// Ordenar o array por 'room_number'
usort($dados_filtrados, function($a, $b) {
    return $a['room_number'] <=> $b['room_number'];
});

$quantidade_dados = count($dados_filtrados);

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
    <title>Auditoria Digital</title>
</head>
<body>

<div class="container">
<!-- Diarias -->
<fieldset>
<legend>Garantias</legend>
<form action="acao.php" method="POST" id="formulario_auditoria">
<table>
<th colspan="7">Controle de Garantias</th>
<tr><td style="background-color: black" colspan="7"></td></tr>
<tr><td align="center" colspan="7">Chegadas do Dia: <b><?php echo $quantidade_dados ?></b></td>
<tr><td style="background-color: black" colspan="7"></td></tr>
<tr style="background-color: grey">
    <td align="center"><b>Qtd</b></td>
    <td align="center"><b>Apto.</b></td>
    <td align="center"><b>Hospede</b></td>
    <td align="center"><b>Checkin</b></td>
    <td align="center"><b>Checkout</b></td>
    <td align="center"><b>Valor</b></td>
    <td align="center"><b>Garantia</b></td>
</tr>


<?php
$qtd = 0;
$quantidade = 0;
foreach ($dados_filtrados as $select) {
    $id = $select['id'];
    $room_number = $select['room_number'];
    $guest_name = $select['guest_name'];
    $checkin = $select['checkin'];
    $checkout = $select['checkout'];
    $auditoria_diarias = $select['auditoria_diarias'];
    $auditoria_garantia = $select['auditoria_garantia'];
    $comentario = $select['comentario'];

    $qtd++;
    $quantidade++;

    if($qtd % 2 == 0){
    $cor_tr = 'darkgrey';
    }else{
    $cor_tr = 'white';  
    }
    ?>
<tr style="background-color: <?php echo $cor_tr; ?>">
    <td align="center"><b><?php echo $qtd; ?></b></td>
    <td align="center"><?php echo $room_number; ?></td>
    <td><?php echo $guest_name; ?></td>
    <td align="center"><?php echo date('d/m/Y', strtotime("$checkin")); ?></td>
    <td align="center"><?php echo date('d/m/Y', strtotime("$checkout")); ?></td>
    <td>
        <input class="input-field-auditoria replace-comma" type="text" name="auditoria_diarias_<?php echo $quantidade ?>" value="<?php echo $auditoria_diarias ?>" required>
    </td>
    <td>
        <select name="auditoria_garantia_<?php echo $quantidade ?>">
        <option value="Sem Garantia" <?php if ($auditoria_garantia == 'Sem Garantia') echo 'selected'; ?>>Sem Garantia</option>
        <option value="Faturado" <?php if ($auditoria_garantia == 'Faturado') echo 'selected'; ?>>Faturado</option>
        <option value="Hotel Card" <?php if ($auditoria_garantia == 'Hotel Card') echo 'selected'; ?>>Hotel Card</option>
        <option value="Transferencia Bancaria" <?php if ($auditoria_garantia == 'Transferencia Bancaria') echo 'selected'; ?>>Transferencia Bancaria</option>
        <option value="Pgto Direto - Cartão" <?php if ($auditoria_garantia == 'Pgto Direto - Cartão') echo 'selected'; ?>>Pgto Direto - Cartão</option>
        <option value="Pgto Direto - Cash" <?php if ($auditoria_garantia == 'Pgto Direto - Cash') echo 'selected'; ?>>Pgto Direto - Cash</option>
        <option value="Cortesia | House Use" <?php if ($auditoria_garantia == 'Cortesia | House Use') echo 'selected'; ?>>Cortesia | House Use</option>
        <option value="PIX" <?php if ($auditoria_garantia == 'PIX') echo 'selected'; ?>>PIX</option>
        <option value="Voucher ALL" <?php if ($auditoria_garantia == 'Voucher ALL') echo 'selected'; ?>>Voucher ALL</option>
        <option value="Pre Autorização" <?php if ($auditoria_garantia == 'Pre Autorização') echo 'selected'; ?>>Pre Autorização</option>
        <option value="Outros" <?php if ($auditoria_garantia == 'Outros') echo 'selected'; ?>>Outros</option>
        </select>
    </td>
</tr>
<input type="hidden" name="id_<?php echo $quantidade ?>" value="<?php echo $id ?>">
<?php } ?>
</table>
<br><br>
<b>Comentarios:</b><br>
<textarea name="comentarios" id="" cols="100" rows="5" required><?php echo $comentarios; ?></textarea><br>
<input type="hidden" name="quantidade" value="<?php echo $quantidade_dados ?>">
<input type="hidden" name="id_job" value="Garantias">
<input type="submit" class="submit" value="Validar Dados">
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
