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

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
error_reporting(0);

// Chave de criptografia
$chave = $_SESSION['hotel'].$chave;

$reserva_id = mysqli_real_escape_string($conn_mysqli, $_POST['reserva_id']);
$reserva_pagamento_tipo = mysqli_real_escape_string($conn_mysqli, $_POST['reserva_pagamento_tipo']);
$reserva_pagamento_valor = mysqli_real_escape_string($conn_mysqli, $_POST['reserva_pagamento_valor']);
$reserva_pagamento_diaria = mysqli_real_escape_string($conn_mysqli, $_POST['reserva_pagamento_diaria']);
$guest_name = mysqli_real_escape_string($conn_mysqli, $_POST['guest_name']);
$guest_email = mysqli_real_escape_string($conn_mysqli, $_POST['guest_email']);
$guest_telefone = mysqli_real_escape_string($conn_mysqli, $_POST['guest_telefone']);
$guest_documento = mysqli_real_escape_string($conn_mysqli, $_POST['guest_documento']);
$guest_nascimento = mysqli_real_escape_string($conn_mysqli, $_POST['guest_nascimento']);
$guest_endereco_cep = mysqli_real_escape_string($conn_mysqli, $_POST['guest_endereco_cep']);
$guest_endereco_rua = mysqli_real_escape_string($conn_mysqli, $_POST['guest_endereco_rua']);
$guest_endereco_bairro = mysqli_real_escape_string($conn_mysqli, $_POST['guest_endereco_bairro']);
$guest_endereco_cidade = mysqli_real_escape_string($conn_mysqli, $_POST['guest_endereco_cidade']);
$guest_endereco_uf = mysqli_real_escape_string($conn_mysqli, $_POST['guest_endereco_uf']);
$checkin = mysqli_real_escape_string($conn_mysqli, $_POST['checkin']);
$checkout = mysqli_real_escape_string($conn_mysqli, $_POST['checkout']);
$noites = mysqli_real_escape_string($conn_mysqli, $_POST['noites']);
$adultos = mysqli_real_escape_string($conn_mysqli, $_POST['adultos']);
$criancas = mysqli_real_escape_string($conn_mysqli, $_POST['criancas']);
$room_msg = mysqli_real_escape_string($conn_mysqli, $_POST['room_msg']);
$room_ratecode = mysqli_real_escape_string($conn_mysqli, $_POST['room_ratecode']);
$room_number = mysqli_real_escape_string($conn_mysqli, $_POST['room_number']);
$room_type = mysqli_real_escape_string($conn_mysqli, $_POST['room_type']);
$company = mysqli_real_escape_string($conn_mysqli, $_POST['company']);

$all_guests = $guest_name;

for ($paxs = 1; $paxs < ($adultos + $criancas); $paxs++) {
    ${"acte_name_$paxs"} = mysqli_real_escape_string($conn_mysqli, $_POST['acte_name_' . $paxs]);
    ${"acte_documento_$paxs"} = mysqli_real_escape_string($conn_mysqli, $_POST['acte_documento_' . $paxs]);
    ${"acte_nascimento_$paxs"} = mysqli_real_escape_string($conn_mysqli, $_POST['acte_nascimento_' . $paxs]);

    $all_guests .= ' - '.mysqli_real_escape_string($conn_mysqli, $_POST['acte_name_' . $paxs]);
}

$query_check = $conexao->prepare("SELECT * FROM $dir"."_excel_gestaorecepcao_roomstatus WHERE room_number LIKE :room_number AND room_status != :room_status");
$query_check->execute(array('room_number' => '%'.$room_number.'%', 'room_status' => 'Designado'));
$resultado = $query_check->rowCount();

if($resultado > 0){
    echo   "<script>
            alert('Apartamento Invalido')
            window.location.replace('chegadas.php')
            </script>";
            exit();
}

if (isset($_POST['change_fnrh'])) {
    $reserva_pagamento_diaria = 0.00;
    $reserva_pagamento_valor = 0.00;
}else if($reserva_pagamento_tipo != 'Faturado'){
    $query = $conexao->prepare("INSERT INTO $dir"."_excel_gestaorecepcao_cashier (username, tipo_lancamento, pagamento_tipo, pagamento_valor, reserva_id, origem) VALUES (:username, :tipo_lancamento, :pagamento_tipo, :pagamento_valor, :reserva_id, :origem)");
    $query->execute(array('username' => $_SESSION['username'], 'tipo_lancamento' => 'Pagamento', 'pagamento_tipo' => $reserva_pagamento_tipo, 'pagamento_valor' => $reserva_pagamento_valor, 'reserva_id' => $reserva_id, 'origem' => 'arrivals'));
}

$dados_arrivalslist = $guest_name.';'.$noites.';'.$adultos.';'.$criancas.';'.$room_type.';'.$room_ratecode.';'.$room_msg.';'.$room_number.';Checkedin;'.$company.';'.$checkin.';'.$checkout;
$dados_criptografados = openssl_encrypt($dados_arrivalslist, $metodo, $chave, 0, $iv);
$dados_final = base64_encode($dados_criptografados);

$query = $conexao->prepare("UPDATE $dir"."_excel_gestaorecepcao_arrivals SET dados_arrivals = :alteracao WHERE id = :reserva_id");
$query->execute(array('alteracao' => $dados_final, 'reserva_id' => $reserva_id));

$query = $conexao->prepare("UPDATE $dir"."_excel_gestaorecepcao_roomstatus SET room_status = :alteracao WHERE room_number LIKE :room_number");
$query->execute(array('alteracao' => 'Ocupado', 'room_number' => '%'.$room_number.'%'));

$reserva_pagamento_aeb = 0.00;
$reserva_pagamento_outros = 0.00;
$reserva_pagamento_saldo = $reserva_pagamento_diaria - $reserva_pagamento_valor;
$dados_saldos = $reserva_id.';'.number_format(floatval($reserva_pagamento_diaria), 2, ',', '.').';'.number_format(floatval($reserva_pagamento_aeb), 2, ',', '.').';'.number_format(floatval($reserva_pagamento_valor), 2, ',', '.').';'.number_format(floatval($reserva_pagamento_saldo), 2, ',', '.').';'.number_format(floatval($reserva_pagamento_outros), 2, ',', '.');
$dados_criptografados = openssl_encrypt($dados_saldos, $metodo, $chave, 0, $iv);
$dados_final = base64_encode($dados_criptografados);

$query = $conexao->prepare("INSERT INTO $dir"."_excel_gestaorecepcao_saldos (dados_saldos) VALUES (:dados_saldos)");
$query->execute(array('dados_saldos' => $dados_final));

$dados_presentlist = $all_guests.';'.$checkin.';'.$checkout.';'.$noites.';'.$adultos.';'.$criancas.';'.$room_ratecode.';'.$reserva_pagamento_valor.';'.$room_number.';'.$room_msg.';;Pendente;'.$reserva_id;
$dados_criptografados = openssl_encrypt($dados_presentlist, $metodo, $chave, 0, $iv);
$dados_final = base64_encode($dados_criptografados);

$query = $conexao->prepare("INSERT INTO $dir"."_excel_gestaorecepcao_inhouse (dados_presentlist, reserva_id) VALUES (:dados_presentlist, :reserva_id)");
$query->execute(array('dados_presentlist' => $dados_final, 'reserva_id' => $reserva_id));


if (isset($_POST['change_fnrh'])) {
    echo   "<script>
    alert('Checkin Realizado com Sucesso')
    window.location.replace('chegadas.php')
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
    <link rel="stylesheet" href="../../css/style_tabela.css">
    <link rel="icon" type="image/x-icon" href="../../images/favicon.ico">
    <link rel="shortcut icon" href="../../images/favicon.ico" type="image/x-icon">
    <title>FNRH - <?php echo $guest_name ?></title>

    <style>
        /* CSS to hide the button when printing */
        @media print {
            .botao {
                display: none;
            }
        }
    </style>

</head>
<body>
<div class="container">
<Center>
<button onclick="printPage()" class="botao">Imprimir FNRH</button><br>
<br>
<h1><?php echo $_SESSION['hotel_name'] ?> - FNRH</h1>
<br>
<div class="appointment">
    <label>[ <b><?php echo $room_number ?></b> ] <b><?php echo $guest_name ?></b> - Periodo: <b><?php echo date('d/m/Y', strtotime("$checkin")) ?></b> a <b><?php echo date('d/m/Y', strtotime("$checkout")) ?></b></label>  
</div>
</center>
<br>
<div class="appointment-resumo-1">
    <label><b>[Titular] Nome Completo:</b> <?php echo $guest_name ?></label><br>
    <label><b>Nascimento:</b> <?php echo date('d/m/Y', strtotime("$guest_nascimento")) ?></label><br>
    <label><b>Documento:</b> <?php echo $guest_documento ?></label><br><br>
    <?php for($paxs = 1 ; $paxs < ($adultos + $criancas) ; $paxs++){ 
        $acte_nascimento = ${"acte_nascimento_$paxs"};
        $acte_name = ${"acte_name_$paxs"};
        $acte_documento = ${"acte_documento_$paxs"};
        ?>
    <label><b>[Acompanhante <?php echo $paxs; ?>] Nome Completo:</b> <?php echo $acte_name ?></label><br>
    <label><b>Nascimento:</b> <?php echo date('d/m/Y', strtotime("$acte_nascimento")) ?></label><br>
    <label><b>Documento:</b> <?php echo $acte_documento ?></label><br><br>
    <?php } ?>
</div>
<div class="appointment-resumo-1">
    <label><b>E-mail:</b> <?php echo $guest_email ?></label><br>
    <label><b>Telefone:</b> <?php echo $guest_telefone ?></label><br><br>
    <label><b>Endereço</b></label><br>
    <label><b>CEP:</b> <?php echo $guest_endereco_cep ?></label><br>
    <label><b>Rua:</b> <?php echo $guest_endereco_rua ?></label><br>
    <label><b>Bairro:</b> <?php echo $guest_endereco_bairro ?></label><br>
    <label><b>Cidade:</b> <?php echo $guest_endereco_cidade ?></label><br>
    <label><b>Estado:</b> <?php echo $guest_endereco_uf ?></label><br>
</div>
<br>
<div class="appointment-resumo-2">
<label><b>Apartamento:</b> <?php echo $room_number ?></label><br><br>
<label><b>Checkin:</b> <?php echo date('d/m/Y', strtotime("$checkin")) ?></label><br>
<label><b>Checkout:</b> <?php echo date('d/m/Y', strtotime("$checkout")) ?></label><br>
<label><b>Rate Code:</b> <?php echo $room_ratecode ?></label><br><br>
<label><b>Adultos:</b> <?php echo $adultos ?></label><br> 
<label><b>Crianças:</b> <?php echo $criancas ?></label><br>
</div>
<br>
<div class="appointment-resumo-1">
<label><b>Assinatura(s)</b></label><br><br><br><br>
<center><b>
_______________________________________________________<br>
<?php echo $guest_name ?>
<?php for($paxs = 1 ; $paxs < ($adultos + $criancas) ; $paxs++){ 
    $acte_name = ${"acte_name_$paxs"}; ?>
<br><br><br><br>_______________________________________________________<br>
<?php echo $acte_name; } ?></b>
</center>
</div>
</div>
<script>
        function printPage() {
            window.print();
        }
</script>
</body>
</html>