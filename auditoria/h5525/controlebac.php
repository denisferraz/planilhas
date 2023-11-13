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
$comentarios = $_SESSION['comentario_bac'];
$status_auditoria = $_SESSION['status_auditoria'];
$limite_credito = $_SESSION['limite_credito'];

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

if (date('d', strtotime($data_auditoria)) % 2 == 0) {
    $ordem_query = 'ASC';
} else {
    $ordem_query = 'DESC';
}

$dados_presentlist = $_SESSION['dados_presentlist'];
$quantidade_dados = count($dados_presentlist);

$limite = ceil($quantidade_dados * 0.3);

// Ordenar o array por 'room_number' em ordem ascendente ou descendente
usort($dados_presentlist, function($a, $b) use ($ordem_query) {
    if ($ordem_query == 'ASC') {
        return $a['room_number'] <=> $b['room_number'];
    } else {
        return $b['room_number'] <=> $a['room_number'];
    }
});

// Limitar o array aos primeiros $limite elementos
$dados_filtrados = array_slice($dados_presentlist, 0, $limite);

$query_qtd = count($dados_filtrados);

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
<legend>Controle du Bac</legend>
<form action="acao.php" method="POST" id="formulario_auditoria">
<table>
<th colspan="18">Controle du Bac</th>
<tr><td style="background-color: black" colspan="18"></td></tr>
<tr><td align="center" colspan="18">Total Uhs Ocupadas: <b><?php echo $quantidade_dados ?></b> | 30% Uhs a Serem Conferidas: <b><?php echo $query_qtd ?></b></td>
<tr><td style="background-color: black" colspan="18"></td></tr>
<tr style="background-color: grey">
    <td align="center" colspan="5"><b>Reserva</b></td>
    <td align="center" colspan="1"><b>Diárias + Extras</b></td>
    <td align="center" colspan="3"><b>Garantias (R$)</b></td>
    <td align="center" colspan="1"><b>-</b></td>
    <td align="center" colspan="4"><b>Pasta (Cupons)</b></td>
    <td align="center" colspan="4"><b>FNRH</b></td>
</tr>
<tr style="background-color: grey">
    <td align="center"><b>Qtd.</b></td>
    <td align="center"><b>Apto.</b></td>
    <td align="center"><b>Hospede</b></td>
    <td align="center"><b>Checkin</b></td>
    <td align="center"><b>Checkout</b></td>
    <td align="center"><b>Saldo Atual</b></td>
    <td align="center"><b>Diárias</b></td>
    <td align="center"><b>Extras</b></td>
    <td align="center"><b>Tipo</b></td>
    <td align="center"><b>Valor Diária</b></td>
    <td align="center"><b>Pasta Limpa</b></td>
    <td align="center"><b>PDV</b></td>
    <td align="center"><b>Pasta</b></td>
    <td align="center"><b>Assinado</b></td>
    <td align="center"><b>Adt.</b></td>
    <td align="center"><b>FNRH</b></td>
    <td align="center"><b>Chd.</b></td>
    <td align="center"><b>Doc</b></td>
</tr>


<?php
$qtd = 0;
$quantidade = 0;
foreach ($dados_filtrados as $select) {
    $id = $select['id'];
    $guest_name = $select['guest_name'];
    $checkin = $select['checkin'];
    $checkout = $select['checkout'];
    $adultos = $select['adultos'];
    $criancas = $select['criancas'];
    $room_balance = $select['room_balance'];
    $room_number = $select['room_number'];
    $auditoria_diarias = $select['auditoria_diarias'];
    $auditoria_extras = $select['auditoria_extras'];
    $auditoria_garantia = $select['auditoria_garantia'];
    $auditoria_valor = $select['auditoria_valor'];
    $auditoria_pasta_limpa = $select['auditoria_pasta_limpa'];
    $auditoria_pasta_pdv = $select['auditoria_pasta_pdv'];
    $auditoria_pasta_pasta = $select['auditoria_pasta_pasta'];
    $auditoria_pasta_ass = $select['auditoria_pasta_ass'];
    $auditoria_fnrh = $select['auditoria_fnrh'];
    $auditoria_doc = $select['auditoria_doc'];

    $qtd++;
    $quantidade++;

    if($room_balance > $limite_credito){
    $cor_td = 'rgb(255, 200, 200)';
    }else{
    $cor_td = 'lightgreen';
    }

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
    <td style="background-color: <?php echo $cor_td; ?>">R$<?php echo number_format($room_balance ,2,",","."); ?></td>
    <td>
        <input class="input-field-auditoria replace-comma" type="text" name="auditoria_diarias_<?php echo $quantidade ?>" value="<?php echo $auditoria_diarias ?>" required>
    </td>
    <td>
        <input class="input-field-auditoria replace-comma" type="text" name="auditoria_extras_<?php echo $quantidade ?>" value="<?php echo $auditoria_extras ?>" required>
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
    <td>
        <input class="input-field-auditoria replace-comma" type="text" name="auditoria_valor_<?php echo $quantidade ?>" value="<?php echo $auditoria_valor ?>" required>
    </td>
    <td>
        <select name="auditoria_pasta_limpa_<?php echo $quantidade ?>">
        <option value="Sim" <?php if ($auditoria_pasta_limpa == 'Sim') echo 'selected'; ?>>Sim</option>
        <option value="Não" <?php if ($auditoria_pasta_limpa == 'Não') echo 'selected'; ?>>Não</option>
        </select>
    </td>
    <td>
        <input class="input-field-auditoria-2" type="text" name="auditoria_pasta_pdv_<?php echo $quantidade ?>" value="<?php echo $auditoria_pasta_pdv ?>" required>
    </td>
    <td>
        <input class="input-field-auditoria-2" type="text" name="auditoria_pasta_pasta_<?php echo $quantidade ?>" value="<?php echo $auditoria_pasta_pasta ?>" required>
    </td>
    <td>
        <input class="input-field-auditoria-2" type="text" name="auditoria_pasta_ass_<?php echo $quantidade ?>" value="<?php echo $auditoria_pasta_ass ?>" required>
    </td>
    <td align="center"><?php echo $adultos; ?></td>
    <td>
        <select name="auditoria_fnrh_<?php echo $quantidade ?>">
        <option value="Sim" <?php if ($auditoria_fnrh == 'Sim') echo 'selected'; ?>>Sim</option>
        <option value="Não" <?php if ($auditoria_fnrh == 'Não') echo 'selected'; ?>>Não</option>
        </select>
    </td>
    <td align="center"><?php echo $criancas; ?></td>
    <td>
        <select name="auditoria_doc_<?php echo $quantidade ?>">
        <option value="Sim" <?php if ($auditoria_doc == 'Sim') echo 'selected'; ?>>Sim</option>
        <option value="Não" <?php if ($auditoria_doc == 'Não') echo 'selected'; ?>>Não</option>
        </select>
    </td>
</tr>
<input type="hidden" name="id_<?php echo $quantidade ?>" value="<?php echo $id ?>">
<?php } ?>
</table>
<br><br>
<b>Comentarios:</b><br>
<textarea name="comentarios" id="" cols="100" rows="5" required><?php echo $comentarios; ?></textarea><br>
<input type="hidden" name="quantidade" value="<?php echo $query_qtd ?>">
<input type="hidden" name="id_job" value="controlebac">
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
