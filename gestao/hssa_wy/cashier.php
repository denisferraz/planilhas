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

// Chave de criptografia
$chave = $_SESSION['hotel'].$chave;

//Reservas Checkins do Dia
$query = $conexao->prepare("SELECT * FROM $dir"."_excel_gestaorecepcao_cashier WHERE id > 0 AND tipo_lancamento = 'Pagamento' ORDER BY username");
$query->execute();
$query_qtd = $query->rowCount();

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
    <title>Gestão Recepção - Downtime</title>
</head>
<body>

<div class="container">
<!-- Cashier -->
<fieldset>
<legend> (<?php echo $query_qtd ?>) Pagamentos Lançados</legend>
<?php
while($select = $query->fetch(PDO::FETCH_ASSOC)){
    $id = $select['id'];
    $pagamento_tipo = $select['pagamento_tipo'];
    $pagamento_valor = $select['pagamento_valor'];
    $reserva_id = $select['reserva_id'];
    $pagamento_valor = number_format($pagamento_valor, 2, ',', '.');
    $origem = $select['origem'];
    $username = $select['username'];

    if($origem == 'arrivals'){
        $lista = 'arrivals';
        $id_dados = 7;
    }else{
        $lista = 'presentlist';
        $id_dados = 8;
    }

$query2 = $conexao->prepare("SELECT * FROM $dir"."_excel_gestaorecepcao_$origem WHERE id = '{$reserva_id}'");
$query2->execute();
while($select2 = $query2->fetch(PDO::FETCH_ASSOC)){
    $dados_cripto = $select2['dados_'.$lista];

    // Para descriptografar os dados
    $dados = base64_decode($dados_cripto);
    $dados_decifrados = openssl_decrypt($dados, $metodo, $chave, 0, $iv);

    $dados_array = explode(';', $dados_decifrados);

    $guest_name = $dados_array[0];
    $room_number = $dados_array[$id_dados];
}

    ?>
<div class="appointment">
    <span class="time"><?php  echo $username ?> - [ <?php  echo $room_number?> ]</span> <span class="name"><?php echo $guest_name ?> | <?php echo $pagamento_tipo ?></span> <span class="time">R$[ <?php  echo $pagamento_valor ?> ]</span>
</div>
<?php
} ?>
</form>
</fieldset>
</div>
<script>
function selecionarRadio(element) {
  var radio = element.querySelector('input[type="radio"]');
  radio.checked = true;
}
</script>
</body>
</html>
