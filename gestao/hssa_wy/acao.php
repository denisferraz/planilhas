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

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
//error_reporting(0);

$id = mysqli_real_escape_string($conn_mysqli, $_GET['id']);
$id = explode(';', base64_decode($id));

$acao = $id[0];

// Chave de criptografia
$chave = $_SESSION['hotel'].$chave;

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

<?php
if($acao == 'RoomStatus'){

$room_status = $id[1];
$room_number = $id[2];

$query = $conexao->prepare("UPDATE $dir"."_excel_gestaorecepcao_roomstatus SET room_status = :room_status WHERE room_number = :room_number");
$query->execute(array('room_status' => $room_status, 'room_number' => $room_number));

echo   "<script>
    alert('Quarto $room_number foi $room_status com Sucesso')
    window.location.replace('roomstatus.php')
        </script>";
        exit();

}else if($acao == 'DesignarApto'){

    $room_id = $id[1];
    $room_type = $id[2];

    $query = $conexao->prepare("SELECT * FROM $dir"."_excel_gestaorecepcao_roomstatus WHERE room_type = :room_type AND (room_status = 'AV.-CL.' OR room_status = 'Limpo')");
    $query->execute(array('room_type' => $room_type));

    $query_reserva = $conexao->prepare("SELECT * FROM $dir"."_excel_gestaorecepcao_arrivals WHERE id = :room_id");
    $query_reserva->execute(array('room_id' => $room_id));
    while($select_reserva = $query_reserva->fetch(PDO::FETCH_ASSOC)){
        $dados_arrivals = $select_reserva['dados_arrivals'];

        // Para descriptografar os dados
        $dados = base64_decode($dados_arrivals);
        $dados_decifrados = openssl_decrypt($dados, $metodo, $chave, 0, $iv);

        $dados_array = explode(';', $dados_decifrados);

        $guest_name = $dados_array[0];
        $noites = $dados_array[1];
    }

//Designar quarto
?>
<form action="acao.php?id=<?php echo base64_encode("Designar;123") ?>" method="POST">
<div class="appointment">
<label>Reserva: <b><?php echo $guest_name ?></b> - Periodo: <b><?php echo date('d/m/Y') ?></b> a <b><?php echo date('d/m/Y', strtotime("+{$noites} days")) ?></b></label>  
</div>
<div class="appointment">
        <label id="room_number">Selecione o Quarto [ <b><?php echo $room_type ?></b> ]</label>
        <select name="room_number" id="room_number">
<?php
while($select = $query->fetch(PDO::FETCH_ASSOC)){  
?>
            <option value="<?php echo $select['room_number'] ?>"><?php echo $select['room_number'] ?></option>
<?php } ?>
        </select>
        </div>
        <input type="hidden" name="room_id" value="<?php echo $room_id ?>">
        <input type="submit" class="submit" value="Designar">
</form>

<?php
}else if($acao == 'Designar'){

    $room_number = mysqli_real_escape_string($conn_mysqli, $_POST['room_number']);
    $room_id = mysqli_real_escape_string($conn_mysqli, $_POST['room_id']);

    $query = $conexao->prepare("UPDATE $dir"."_excel_gestaorecepcao_roomstatus SET room_status = :room_status WHERE room_number = :room_number");
    $query->execute(array('room_status' => 'Designado', 'room_number' => $room_number));

    $query = $conexao->prepare("SELECT * FROM $dir"."_excel_gestaorecepcao_arrivals WHERE id = :id");
    $query->execute(array('id' => $room_id));

    $arrivalslist_array = [];
    while($select = $query->fetch(PDO::FETCH_ASSOC)){
        $dados_arrivals = $select['dados_arrivals'];

    // Para descriptografar os dados
    $dados = base64_decode($dados_arrivals);
    $dados_decifrados = openssl_decrypt($dados, $metodo, $chave, 0, $iv);

    $dados_array = explode(';', $dados_decifrados);

        $guest_name = $dados_array[0];
        $noites = $dados_array[1];
        $adultos = $dados_array[2];
        $criancas = $dados_array[3];
        $room_type = $dados_array[4];
        $room_ratecode = $dados_array[5];
        $room_msg = $dados_array[6];
        $alteracao = $dados_array[8];
        $company = $dados_array[9];

    }

    $dados_arrivalslist = $guest_name.';'.$noites.';'.$adultos.';'.$criancas.';'.$room_type.';'.$room_ratecode.';'.$room_msg.';'.$room_number.';Pendente'.';'.$company;
    $dados_criptografados = openssl_encrypt($dados_arrivalslist, $metodo, $chave, 0, $iv);
    $dados_final = base64_encode($dados_criptografados);

    $query = $conexao->prepare("UPDATE $dir"."_excel_gestaorecepcao_arrivals SET dados_arrivals = :dados_arrivals WHERE id = :id");
    $query->execute(array('dados_arrivals' => $dados_final, 'id' => $room_id));

    echo   "<script>
    alert('Quarto $room_number foi designado com Sucesso')
    window.location.replace('chegadas.php')
        </script>";
        exit();

}else if($acao == 'Chegadas'){

    $hoje = date('Y-m-d');

    if(empty($id[2])){
    $id_job = mysqli_real_escape_string($conn_mysqli, $_POST['id_job']);
    $reserva_id = mysqli_real_escape_string($conn_mysqli, $_POST['reserva_id']);
    }else{
    $id_job = $id[1];
    $reserva_id = $id[2];
    }

    $query_reserva = $conexao->prepare("SELECT * FROM $dir"."_excel_gestaorecepcao_arrivals WHERE id = :reserva_id");
    $query_reserva->execute(array('reserva_id' => $reserva_id));

    $arrivalslist_array = [];
    while($select = $query_reserva->fetch(PDO::FETCH_ASSOC)){
        $dados_arrivals = $select['dados_arrivals'];

    // Para descriptografar os dados
    $dados = base64_decode($dados_arrivals);
    $dados_decifrados = openssl_decrypt($dados, $metodo, $chave, 0, $iv);

    $dados_array = explode(';', $dados_decifrados);

        $guest_name = $dados_array[0];
        $noites = $dados_array[1];
        $adultos = $dados_array[2];
        $criancas = $dados_array[3];
        $room_type = $dados_array[4];
        $room_ratecode = $dados_array[5];
        $room_msg = $dados_array[6];
        $room_number = $dados_array[7];
        $alteracao = $dados_array[8];
        $company = $dados_array[9];

    }

    if($id_job == 'Checkin'){
    $query = $conexao->prepare("SELECT  * FROM $dir"."_excel_gestaorecepcao_arrivals WHERE id = :id");
    $query->execute(array('id' => $reserva_id));
    $resultado = $query->rowCount();

    if($resultado == '' || empty($reserva_id)){
        echo   "<script>
        alert('Selecione um quarto antes de fazer Checkin')
        window.location.replace('chegadas.php')
        </script>";
        exit();
    }else{

//Finalizar Checkin
?>
<form action="fnrh.php" method="POST">
<div class="appointment">
<label>Reserva: <b><?php echo $guest_name ?></b> [ <b><?php echo $room_number ?></b> - R$<?php echo $room_ratecode ?> ] - Periodo: <b><?php echo date('d/m/Y') ?></b> a <b><?php echo date('d/m/Y', strtotime("+{$noites} days")) ?></b></label>  
</div>
<div class="appointment">
        <label id="reserva_pagamento_tipo">Tipo Pagamento</label>
        <select name="reserva_pagamento_tipo" id="reserva_pagamento_tipo">
            <option value="Dinheiro">Dinheiro</option>
            <option value="Visa">Visa</option>
            <option value="Visa Electron">Visa Electron</option>
            <option value="Mastercard">Mastercard</option>
            <option value="Rede Shop">Rede Shop</option>
            <option value="Elo Debito">Elo Debito</option>
            <option value="Elo Credito">Elo Credito</option>
            <option value="American Express">American Express</option>
            <option value="Deposito Bancario">Deposito Bancario</option>
            <option value="Faturado">Faturado</option>
            <option value="Outros">Outros</option>
        </select><br>
        <label id="reserva_pagamento_valor">Valor</label>
        <input class="input-field" type="text" id="reserva_pagamento_valor" name="reserva_pagamento_valor" placeholder="100.00" required><br>
        <label id="reserva_pagamento_diaria">Total Reserva</label>
        <input class="input-field" type="text" id="reserva_pagamento_diaria" name="reserva_pagamento_diaria" placeholder="100.00" required><br>
</div>
<div class="appointment">
        <label id="guest_name">Hospede</label>
        <input class="input-field" type="text" id="guest_name" name="guest_name" value="<?php echo $guest_name ?>" required><br>
        <label id="guest_email">E-mail</label>
        <input class="input-field" type="email" id="guest_email" name="guest_email" placeholder="exemplo@exemplo.com" required><br>
        <label id="guest_telefone">Telefone</label>
        <input class="input-field" type="text" id="guest_telefone" name="guest_telefone" placeholder="7135054300" required><br>
        <label id="guest_documento">Documento</label>
        <input class="input-field" type="text" id="guest_documento" name="guest_documento" placeholder="123.456.789-10" required><br>
        <label for="guest_endereco_cep">Endereço [<b>CEP</b>]</label>
        <input class="input-field" type="text" id="guest_endereco_cep" name="guest_endereco_cep" placeholder="CEP..." required><br>
        <label for="guest_endereco_rua">Endereço [<b>Rua</b>]</label>
        <input class="input-field" type="text" id="guest_endereco_rua" name="guest_endereco_rua" placeholder="Rua..." required><br>
        <label for="guest_endereco_bairro">Endereço [<b>Bairro</b>]</label>
        <input class="input-field" type="text" id="guest_endereco_bairro" name="guest_endereco_bairro" placeholder="Bairro..." required><br>
        <label for="guest_endereco_cidade">Endereço [<b>Cidade</b>]</label>
        <input class="input-field" type="text" id="guest_endereco_cidade" name="guest_endereco_cidade" placeholder="Cidade..." required><br>
        <label for="guest_endereco_uf">Endereço [<b>Estado</b>]</label>
        <input class="input-field" type="text" id="guest_endereco_uf" name="guest_endereco_uf" placeholder="Estado..." required><br>
</div>
<div class="appointment">
    <label id="room_msg">Comentários</label><br>
    <textarea class="input-field" id="room_msg" name="room_msg" required><?php echo $room_msg ?></textarea><br>
</div>
        <input type="hidden" name="room_ratecode" value="<?php echo $room_ratecode ?>">
        <input type="hidden" name="room_number" value="<?php echo $room_number ?>">
        <input type="hidden" name="room_type" value="<?php echo $room_type ?>">
        <input type="hidden" name="checkin" value="<?php echo $hoje ?>">
        <input type="hidden" name="checkout" value="<?php echo date('Y-m-d', strtotime("+$noites days")) ?>">
        <input type="hidden" name="noites" value="<?php echo $noites ?>">
        <input type="hidden" name="adultos" value="<?php echo $adultos ?>">
        <input type="hidden" name="criancas" value="<?php echo $criancas ?>">
        <input type="hidden" name="company" value="<?php echo $company ?>">
        <input type="hidden" name="reserva_id" value="<?php echo $reserva_id ?>">
        <input type="submit" class="submit" value="Confirmar">
</form>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        $('#guest_endereco_cep').on('keyup', function() {
            var cep = $(this).val().replace(/\D/g, '');
            if (cep.length === 8) {
                $.getJSON('https://viacep.com.br/ws/' + cep + '/json/', function(data) {
                    if (!data.erro) {
                        $('#guest_endereco_rua').val(data.logradouro);
                        $('#guest_endereco_bairro').val(data.bairro);
                        $('#guest_endereco_cidade').val(data.localidade);
                        $('#guest_endereco_uf').val(data.uf);
                    }
                });
            }
        });
    });
</script>

<?php
    }

    }else if($id_job == 'CancelarReserva'){
        
    if(empty($reserva_id)){
            echo   "<script>
            alert('Selecione uma Reserva antes para Cancelar')
            window.location.replace('chegadas.php')
            </script>";
            exit();
    }else{

    $dados_arrivalslist = $guest_name.';'.$noites.';'.$adultos.';'.$criancas.';'.$room_type.';'.$room_ratecode.';'.$room_msg.';;Cancelada;'.$company;
    $dados_criptografados = openssl_encrypt($dados_arrivalslist, $metodo, $chave, 0, $iv);
    $dados_final = base64_encode($dados_criptografados);

    $query = $conexao->prepare("UPDATE $dir"."_excel_gestaorecepcao_arrivals SET dados_arrivals = :alteracao WHERE id = :reserva_id");
    $query->execute(array('alteracao' => $dados_final, 'reserva_id' => $reserva_id));

    if(!empty($room_number)){
    $query = $conexao->prepare("UPDATE $dir"."_excel_gestaorecepcao_roomstatus SET room_status = :alteracao WHERE room_number = :room_number");
    $query->execute(array('alteracao' => 'Limpo', 'room_number' => $room_number));
    }

    echo   "<script>
        alert('Reserva Cancelada com Sucesso')
        window.location.replace('chegadas.php')
        </script>";
        exit();

        }
    }else if($id_job == 'Reinstate'){

    $room_number = $id[3];

    $dados_arrivalslist = $guest_name.';'.$noites.';'.$adultos.';'.$criancas.';'.$room_type.';'.$room_ratecode.';'.$room_msg.';;Pendente;'.$company;
    $dados_criptografados = openssl_encrypt($dados_arrivalslist, $metodo, $chave, 0, $iv);
    $dados_final = base64_encode($dados_criptografados);

    $query = $conexao->prepare("UPDATE $dir"."_excel_gestaorecepcao_arrivals SET dados_arrivals = :alteracao WHERE id = :reserva_id");
    $query->execute(array('alteracao' => $dados_final, 'reserva_id' => $reserva_id));

    $query = $conexao->prepare("UPDATE $dir"."_excel_gestaorecepcao_roomstatus SET room_status = :alteracao WHERE room_number = :room_number");
    $query->execute(array('alteracao' => 'Sujo', 'room_number' => $room_number));

    $query = $conexao->prepare("DELETE FROM $dir"."_excel_gestaorecepcao_inhouse WHERE reserva_id = :reserva_id");
    $query->execute(array('reserva_id' => $reserva_id));

        echo   "<script>
        alert('Reinstate realizado com Sucesso')
        window.location.replace('chegadas.php')
        </script>";
        exit();

    }

}else if($acao == 'Inhouse'){

    if(empty($id[2])){
    $id_job = mysqli_real_escape_string($conn_mysqli, $_POST['id_job']);
    $reserva_id = mysqli_real_escape_string($conn_mysqli, $_POST['reserva_id']);
    $reserva_saldo = mysqli_real_escape_string($conn_mysqli, $_POST['reserva_saldo']);
    }else{
    $id_job = $id[1];
    $reserva_id = $id[2];
    $reserva_saldo = $id[3];
    }

    if(empty($reserva_id)){
        echo   "<script>
        alert('Selecione uma Reserva antes para Continuar')
        window.location.replace('inhouse.php')
        </script>";
        exit();
    }else{

        //Saldos
        $query_saldos = $conexao->prepare("SELECT * FROM $dir"."_excel_gestaorecepcao_saldos WHERE id > 0");
        $query_saldos->execute();

        $saldos_array = [];
        while($select_saldos = $query_saldos->fetch(PDO::FETCH_ASSOC)){
            $dados_saldos = $select_saldos['dados_saldos'];
            $id_saldo = $select_saldos['id'];

        // Para descriptografar os dados
        $dados = base64_decode($dados_saldos);
        $dados_decifrados = openssl_decrypt($dados, $metodo, $chave, 0, $iv);

        $dados_array = explode(';', $dados_decifrados);

        $saldos_array[] = [
            'id' => $id_saldo,
            'reserva' => $dados_array[0],
            'diarias' => $dados_array[1],
            'aeb' => $dados_array[2],
            'credito' => $dados_array[3],
            'saldo' => $dados_array[4]
        ];

        }

        $query2 = $conexao->prepare("SELECT * FROM $dir"."_excel_gestaorecepcao_inhouse WHERE id = :reserva_id");
        $query2->execute(array('reserva_id' => $reserva_id));
        while($select = $query2->fetch(PDO::FETCH_ASSOC)){
            $dados_presentlist = $select['dados_presentlist'];
            $id = $select['id'];
        
        // Para descriptografar os dados
        $dados = base64_decode($dados_presentlist);
        $dados_decifrados = openssl_decrypt($dados, $metodo, $chave, 0, $iv);
        
        $dados_array = explode(';', $dados_decifrados);
        
            $guest_name = $dados_array[0];
            $checkin = $dados_array[1];
            $checkout = $dados_array[2];
            $noites = $dados_array[3];
            $adultos = $dados_array[4];
            $criancas = $dados_array[5];
            $room_ratecode = $dados_array[6];
            $room_msg = $dados_array[9];
            $room_number = $dados_array[8];
            $room_company = $dados_array[10];
            $room_balance = floatval($dados_array[7]);
            $alteracao = $dados_array[11];
            $reserva = $dados_array[12];

            $filtered_array_saldos = [];
            foreach ($saldos_array as $item) {
                if ($item['reserva'] === $reserva) {
                    $filtered_array_saldos[] = $item;
                }
            }
            
            foreach ($filtered_array_saldos as $select_saldos2) {
                $reserva_saldo = $select_saldos2['reserva'];
                $id_saldos = $select_saldos2['id'];
                $diarias = $select_saldos2['diarias'];
                $aeb = $select_saldos2['aeb'];
                $credito = $select_saldos2['credito'];
                $saldo = $select_saldos2['saldo'];
            }


            $balance = "<b>Saldo: R$$saldo [ Diarias: R$$diarias + AeB: R$$aeb - Crédito: R$$credito ]</b>";
            $room_balance = $saldo;
        }

        if($id_job == 'Checkout'){
?>
<form action="acao.php?id=<?php echo base64_encode("Inhouse;Checkedout") ?>" method="POST">
<div class="appointment">
<label>[ <b><?php echo $room_number ?></b> ] <b><?php echo $guest_name ?></b><br>Balance: <?php echo $balance ?> - Periodo: <b><?php echo date('d/m/Y', strtotime("$checkin")) ?></b> a <b><?php echo date('d/m/Y', strtotime("$checkout")) ?></b></label>  
</div>
    <div class="appointment">
    <label id="pagamento_tipo">Tipo Pagamento</label>
        <select name="pagamento_tipo" id="pagamento_tipo">
            <option value="Dinheiro">Dinheiro</option>
            <option value="Visa">Visa</option>
            <option value="Visa Electron">Visa Electron</option>
            <option value="Mastercard">Mastercard</option>
            <option value="Rede Shop">Rede Shop</option>
            <option value="Elo Debito">Elo Debito</option>
            <option value="Elo Credito">Elo Credito</option>
            <option value="American Express">American Express</option>
            <option value="Deposito Bancario">Deposito Bancario</option>
            <option value="Faturado">Faturado</option>
            <option value="Outros">Outros</option>
        </select><br>
        <label id="pagamento_valor">Valor</label>
        <input class="input-field" type="text" id="pagamento_valor" name="pagamento_valor" placeholder="<?php echo $room_balance ?>" required><br>
    </div>
        <input type="hidden" name="reserva_id" value="<?php echo $reserva_id ?>">
        <input type="hidden" name="credito" value="<?php echo $credito ?>">
        <input type="hidden" name="saldo" value="<?php echo $saldo ?>">
        <input type="hidden" name="reserva_saldo" value="<?php echo $reserva_saldo ?>">
        <input type="hidden" name="id_job" value="Checkedout">
        <input type="submit" class="submit" value="Confirmar">
</form>
<?php
        }else if($id_job == 'Checkedout'){

        $pagamento_valor = mysqli_real_escape_string($conn_mysqli, $_POST['pagamento_valor']);
        $pagamento_tipo = mysqli_real_escape_string($conn_mysqli, $_POST['pagamento_tipo']);
        $reserva_saldo = mysqli_real_escape_string($conn_mysqli, $_POST['reserva_saldo']);
        $credito = mysqli_real_escape_string($conn_mysqli, $_POST['credito']);
        $saldo = mysqli_real_escape_string($conn_mysqli, $_POST['saldo']);

        $credito -= $pagamento_valor;
        $saldo -= $pagamento_valor;

        $dados_saldos = $reserva_saldo.';'.number_format(floatval($diarias), 2, ',', '.').';'.number_format(floatval($aeb), 2, ',', '.').';'.number_format(floatval($credito), 2, ',', '.').';'.number_format(floatval($saldo), 2, ',', '.');
        $dados_criptografados = openssl_encrypt($dados_saldos, $metodo, $chave, 0, $iv);
        $dados_final = base64_encode($dados_criptografados);

        $query = $conexao->prepare("UPDATE $dir"."_excel_gestaorecepcao_saldos SET dados_saldos = :alteracao WHERE id = :reserva_id");
        $query->execute(array('alteracao' => $dados_final, 'reserva_id' => $id_saldos));

        $dados_presentlist = $guest_name.';'.$checkin.';'.$checkout.';'.$noites.';'.$adultos.';'.$criancas.';'.$room_ratecode.';'.$room_balance.';'.$room_number.';'.$room_msg.';'.$room_company.';Checkedout;'.$reserva_saldo;
        $dados_criptografados = openssl_encrypt($dados_presentlist, $metodo, $chave, 0, $iv);
        $dados_final = base64_encode($dados_criptografados);

        $query = $conexao->prepare("UPDATE $dir"."_excel_gestaorecepcao_inhouse SET dados_presentlist = :alteracao WHERE id = :reserva_id");
        $query->execute(array('alteracao' => $dados_final, 'reserva_id' => $reserva_id));

        $query = $conexao->prepare("UPDATE $dir"."_excel_gestaorecepcao_roomstatus SET room_status = :alteracao WHERE room_number = :room_number");
        $query->execute(array('alteracao' => 'Sujo', 'room_number' => $room_number));

        $query = $conexao->prepare("INSERT INTO $dir"."_excel_gestaorecepcao_cashier (username, tipo_lancamento, pagamento_tipo, pagamento_valor, reserva_id, origem) VALUES (:username, :tipo_lancamento, :pagamento_tipo, :pagamento_valor, :reserva_id, :origem)");
        $query->execute(array('username' => $_SESSION['username'], 'tipo_lancamento' => 'Pagamento', 'pagamento_tipo' => $pagamento_tipo, 'pagamento_valor' => $pagamento_valor, 'reserva_id' => $reserva_id, 'origem' => 'inhouse'));

        echo   "<script>
        alert('Checkout Apartamento $room_number realizado com Sucesso')
        window.location.replace('inhouse.php')
        </script>";
        exit();

        }else if($id_job == 'Prorrogar'){
            ?>
<form action="acao.php?id=<?php echo base64_encode("Inhouse;Novadata") ?>" method="POST">
<div class="appointment">
<label>[ <b><?php echo $room_number ?></b> ] <b><?php echo $guest_name ?></b><br>Periodo Original: <b><?php echo date('d/m/Y', strtotime("$checkin")) ?></b> a <b><?php echo date('d/m/Y', strtotime("$checkout")) ?></b></label>  
</div>
    <div class="appointment">
        <label id="reserva_nova_data">Nova Data</label>
        <input class="input-field" min="<?php echo $hoje; ?>" type="date" id="reserva_nova_data" name="reserva_nova_data" required><br>
    </div>
        <input type="hidden" name="reserva_id" value="<?php echo $reserva_id ?>">
        <input type="hidden" name="id_job" value="Novadata">
        <input type="submit" class="submit" value="Confirmar">
</form>

<?php
        }else if($id_job == 'Novadata'){

        $checkout = mysqli_real_escape_string($conn_mysqli, $_POST['reserva_nova_data']);

        $dados_presentlist = $guest_name.';'.$checkin.';'.$checkout.';'.$noites.';'.$adultos.';'.$criancas.';'.$room_ratecode.';'.$room_balance.';'.$room_number.';'.$room_msg.';'.$room_company.';Prorrogado;'.$reserva_saldo;
        $dados_criptografados = openssl_encrypt($dados_presentlist, $metodo, $chave, 0, $iv);
        $dados_final = base64_encode($dados_criptografados);

        $query = $conexao->prepare("UPDATE $dir"."_excel_gestaorecepcao_inhouse SET dados_presentlist = :alteracao WHERE id = :reserva_id");
        $query->execute(array('alteracao' => $dados_final, 'reserva_id' => $reserva_id));

        echo   "<script>
        alert('Reserva Prorrogada com Sucesso')
        window.location.replace('inhouse.php')
        </script>";
        exit();

        }else if($id_job == 'Reinstate'){

        $dados_presentlist = $guest_name.';'.$checkin.';'.$checkout.';'.$noites.';'.$adultos.';'.$criancas.';'.$room_ratecode.';'.$room_balance.';'.$room_number.';'.$room_msg.';'.$room_company.';Pendente;'.$reserva_saldo;
        $dados_criptografados = openssl_encrypt($dados_presentlist, $metodo, $chave, 0, $iv);
        $dados_final = base64_encode($dados_criptografados);

        $query = $conexao->prepare("UPDATE $dir"."_excel_gestaorecepcao_inhouse SET dados_presentlist = :alteracao WHERE id = :reserva_id");
        $query->execute(array('alteracao' => $dados_final, 'reserva_id' => $reserva_id));

        $query = $conexao->prepare("UPDATE $dir"."_excel_gestaorecepcao_roomstatus SET room_status = :alteracao WHERE room_number = :room_number");
        $query->execute(array('alteracao' => 'Ocupado', 'room_number' => $room_number));

        echo   "<script>
        alert('Checkout Reinstate com Sucesso')
        window.location.replace('inhouse.php')
        </script>";
        exit();

        }else if($id_job == 'Pagamentos'){

    ?>
            <form action="acao.php?id=<?php echo base64_encode("Inhouse;123") ?>" method="POST">
            <div class="appointment">
            <label>Reserva: <b><?php echo $guest_name ?></b> [ <b><?php echo $room_number ?></b> ] Balance: <b>R$<?php echo $balance ?></b>- Periodo: <b><?php echo date('d/m/Y', strtotime("$checkin")) ?></b> a <b><?php echo date('d/m/Y', strtotime("$checkout")) ?></b></label>  
            </div>
                <div class="appointment">
                <label id="pagamento_tipo">Tipo Pagamento</label>
                    <select name="pagamento_tipo" id="pagamento_tipo">
                        <option value="Dinheiro">Dinheiro</option>
                        <option value="Visa">Visa</option>
                        <option value="Visa Electron">Visa Electron</option>
                        <option value="Mastercard">Mastercard</option>
                        <option value="Rede Shop">Rede Shop</option>
                        <option value="Elo Debito">Elo Debito</option>
                        <option value="Elo Credito">Elo Credito</option>
                        <option value="American Express">American Express</option>
                        <option value="Deposito Bancario">Deposito Bancario</option>
                        <option value="Faturado">Faturado</option>
                        <option value="Outros">Outros</option>
                    </select><br>
                    <label id="pagamento_valor">Valor</label>
                    <input class="input-field" type="text" id="pagamento_valor" name="pagamento_valor" placeholder="<?php echo $balance ?>" required><br>
                </div>
                    <input type="hidden" name="reserva_id" value="<?php echo $reserva_id ?>">
                    <input type="hidden" name="id_job" value="PagamentosOk">
                    <input type="submit" class="submit" value="Confirmar">
            </form>
    <?php

        }else if($id_job == 'PagamentosOk'){

            $pagamento_tipo = mysqli_real_escape_string($conn_mysqli, $_POST['pagamento_tipo']);
            $pagamento_valor = mysqli_real_escape_string($conn_mysqli, $_POST['pagamento_valor']);

            $room_balance -= $pagamento_valor;

            $query = $conexao->prepare("INSERT INTO $dir"."_excel_gestaorecepcao_cashier (username, tipo_lancamento, pagamento_tipo, pagamento_valor, reserva_id, origem) VALUES (:username, :tipo_lancamento, :pagamento_tipo, :pagamento_valor, :reserva_id, :origem)");
            $query->execute(array('username' => $_SESSION['username'], 'tipo_lancamento' => 'Pagamento', 'pagamento_tipo' => $pagamento_tipo, 'pagamento_valor' => $pagamento_valor, 'reserva_id' => $reserva_id, 'origem' => 'inhouse'));

            $dados_presentlist = $guest_name.';'.$checkin.';'.$checkout.';'.$noites.';'.$adultos.';'.$criancas.';'.$room_ratecode.';'.$room_balance.';'.$room_number.';'.$room_msg.';'.$room_company.';Pendente';
            $dados_criptografados = openssl_encrypt($dados_presentlist, $metodo, $chave, 0, $iv);
            $dados_final = base64_encode($dados_criptografados);

            $query = $conexao->prepare("UPDATE $dir"."_excel_gestaorecepcao_inhouse SET dados_presentlist = :alteracao WHERE id = :reserva_id");
            $query->execute(array('alteracao' => $dados_final, 'reserva_id' => $reserva_id));

            echo   "<script>
            alert('Pagamento Lançado com Sucesso')
            window.location.replace('inhouse.php')
            </script>";
            exit();

        }else if($id_job == 'Produtos'){

    ?>
            <form action="acao.php?id=<?php echo base64_encode("Inhouse;123") ?>" method="POST">
            <div class="appointment">
            <label>Reserva: <b><?php echo $guest_name ?></b> [ <b><?php echo $room_number ?></b> ] Balance: <b>R$<?php echo $balance ?></b>- Periodo: <b><?php echo date('d/m/Y', strtotime("$checkin")) ?></b> a <b><?php echo date('d/m/Y', strtotime("$checkout")) ?></b></label>  
            </div>
                <div class="appointment">
                <label id="produto_tipo">Descrição Produto</label>
                    <input class="input-field" type="text" id="produto_tipo" name="produto_tipo" placeholder="Descrição do Produto" required><br>
                    <label id="produto_valor">Valor do Produto</label>
                    <input class="input-field" type="text" id="produto_valor" name="produto_valor" placeholder="100.00" required><br>
                </div>
                    <input type="hidden" name="reserva_id" value="<?php echo $reserva_id ?>">
                    <input type="hidden" name="id_job" value="ProdutosOk">
                    <input type="submit" class="submit" value="Confirmar">
            </form>
    <?php
    
        }else if($id_job == 'ProdutosOk'){

            $pagamento_tipo = mysqli_real_escape_string($conn_mysqli, $_POST['produto_tipo']);
            $pagamento_valor = mysqli_real_escape_string($conn_mysqli, $_POST['produto_valor']);

            $room_balance += $pagamento_valor;

            $query = $conexao->prepare("INSERT INTO $dir"."_excel_gestaorecepcao_cashier (username, tipo_lancamento, pagamento_tipo, pagamento_valor, reserva_id, origem) VALUES (:username, :tipo_lancamento, :pagamento_tipo, :pagamento_valor, :reserva_id, :origem)");
            $query->execute(array('username' => $_SESSION['username'], 'tipo_lancamento' => 'Produto', 'pagamento_tipo' => $pagamento_tipo, 'pagamento_valor' => $pagamento_valor, 'reserva_id' => $reserva_id, 'origem' => 'inhouse'));

            $dados_presentlist = $guest_name.';'.$checkin.';'.$checkout.';'.$noites.';'.$adultos.';'.$criancas.';'.$room_ratecode.';'.$room_balance.';'.$room_number.';'.$room_msg.';'.$room_company.';Pendente';
            $dados_criptografados = openssl_encrypt($dados_presentlist, $metodo, $chave, 0, $iv);
            $dados_final = base64_encode($dados_criptografados);

            $query = $conexao->prepare("UPDATE $dir"."_excel_gestaorecepcao_inhouse SET dados_presentlist = :alteracao WHERE id = :reserva_id");
            $query->execute(array('alteracao' => $dados_final, 'reserva_id' => $reserva_id));

            echo   "<script>
            alert('Produto Lançado com Sucesso')
            window.location.replace('inhouse.php')
            </script>";
            exit();

        }

}


}else if($acao == 'NewArrival'){

    $guest_name = mysqli_real_escape_string($conn_mysqli, $_POST['guest_name']);
    $noites = mysqli_real_escape_string($conn_mysqli, $_POST['noites']);
    $adultos = mysqli_real_escape_string($conn_mysqli, $_POST['adultos']);
    $criancas = mysqli_real_escape_string($conn_mysqli, $_POST['criancas']);
    $room_ratecode = mysqli_real_escape_string($conn_mysqli, $_POST['room_ratecode']);
    $room_msg = mysqli_real_escape_string($conn_mysqli, $_POST['room_msg']);
    $room_type = mysqli_real_escape_string($conn_mysqli, $_POST['room_type']);
    $company = mysqli_real_escape_string($conn_mysqli, $_POST['company']);

    $dados_arrivalslist = $guest_name.';'.$noites.';'.$adultos.';'.$criancas.';'.$room_type.';'.$room_ratecode.';'.$room_msg.';;Pendente;'.$company;
    $dados_criptografados = openssl_encrypt($dados_arrivalslist, $metodo, $chave, 0, $iv);
    $dados_final = base64_encode($dados_criptografados);

    $query = $conexao->prepare("INSERT INTO $dir"."_excel_gestaorecepcao_arrivals (dados_arrivals) VALUES (:dados_arrivals)");
    $query->execute(array('dados_arrivals' => $dados_final));

        echo   "<script>
        alert('Reserva em nome de $guest_name Confirmada!')
        window.location.replace('chegadas.php')
        </script>";
        exit();

}
?>

</div>
</body>
</html>