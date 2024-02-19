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

$data_auditoria = $_SESSION['data_auditoria'];

$query_status = $conexao->prepare("SELECT * FROM $dir"."_excel_auditoria_auditorias WHERE id > 0 AND data_auditoria = '{$data_auditoria}'");
$query_status->execute();
while($select_status = $query_status->fetch(PDO::FETCH_ASSOC)){
    $status_auditoria = $select_status['auditoria_status'];
    $comentarios = $select_status['comentario_garantias'];
}

if($status_auditoria == 'Pendente'){
    echo "<script>
    alert('Auditoria não foi Iniciada!')
    top.location.replace('index.php')
    </script>";
    exit();
}

$chave = $_SESSION['hotel'].$chave;

//$_SESSION['dados_presentlist']
$query = $conexao->prepare("SELECT * FROM $dir"."_excel_auditoria WHERE data_auditoria = '{$data_auditoria}'");
$query->execute();

$dados_presentlist = [];
while($select = $query->fetch(PDO::FETCH_ASSOC)){
    $dados_auditoria = $select['dados_auditoria'];
    $id = $select['id'];

// Para descriptografar os dados
$dados = base64_decode($dados_auditoria);
$dados_decifrados = openssl_decrypt($dados, $metodo, $chave, 0, $iv);

$dados_array = explode(';', $dados_decifrados);

if($dados_array[0] == 'inhouse'){
$dados_presentlist[] = [
  'id' => $id,
  'reserva' => $dados_array[2],
  'room_number' => $dados_array[3],
  'guest_name' => $dados_array[4],
  'checkin' => $dados_array[5],
  'checkout' => $dados_array[6],
  'room_rate' => $dados_array[7],
  'comentario_checkins' => $dados_array[8],
  'comentario_freestay' => $dados_array[9],
  'auditoria_diarias' => $dados_array[10],
  'auditoria_garantia' => $dados_array[11]
];
}}

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
<?php if($status_auditoria != 'Finalizada'){ ?>
<form action="acao.php" method="POST" id="formulario_auditoria">
<?php } ?>
<table>
<th colspan="9">Controle de Checkins</th>
<tr><td style="background-color: black" colspan="9"></td></tr>
<tr><td align="center" colspan="9">Chegadas do Dia: <b><?php echo $quantidade_dados ?></b></td>
<tr><td style="background-color: black" colspan="9"></td></tr>
<tr style="background-color: grey">
    <td align="center"><b>Qtd</b></td>
    <td align="center"><b>Apto.</b></td>
    <td align="center"><b>Hospede</b></td>
    <td align="center"><b>Checkin</b></td>
    <td align="center"><b>Checkout</b></td>
    <td align="center"><b>Diária</b></td>
    <td align="center"><b>Conferido</b></td>
    <td align="center"><b>Garantia</b></td>
    <td align="center"><b>Valor</b></td>
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
    $room_rate = $select['room_rate'];
    $comentario = $select['comentario_checkins'];
    $auditoria_diarias = $select['auditoria_diarias'];
    $auditoria_garantia = $select['auditoria_garantia'];

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
    <td align="center">R$<?php echo number_format($room_rate, 2, ',', '.'); ?></td>
    <td align="center"><input type="checkbox" name="comentarios_<?php echo $quantidade ?>" value="Sim" <?php if($comentario == 'Sim') echo 'checked'; ?>></td>
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
        <option value="Voucher Rewards" <?php if ($auditoria_garantia == 'Voucher Rewards') echo 'selected'; ?>>Voucher Rewards</option>
        <option value="Pre Autorização" <?php if ($auditoria_garantia == 'Pre Autorização') echo 'selected'; ?>>Pre Autorização</option>
        <option value="Outros" <?php if ($auditoria_garantia == 'Outros') echo 'selected'; ?>>Outros</option>
        </select>
    </td>
    <td>
        <input class="input-field-auditoria replace-comma" type="text" name="auditoria_diarias_<?php echo $quantidade ?>" value="<?php echo $auditoria_diarias ?>" required>
    </td>
</tr>
<input type="hidden" name="id_<?php echo $quantidade ?>" value="<?php echo $id ?>">
<?php } ?>
</table>
<br><br>
<b>Comentarios:</b><br>
<textarea name="comentarios" id="" cols="100" rows="5"><?php echo $comentarios; ?></textarea><br>
<input type="hidden" name="quantidade" value="<?php echo $quantidade_dados ?>">
<input type="hidden" name="id_job" value="Garantias">
<input type="hidden" name="dados_auditoria" value="<?php echo $dados_presentlist ?>">
<?php if($status_auditoria != 'Finalizada'){ ?>
<input type="submit" class="submit" value="Validar Dados">
<?php } ?>
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
