<?php

session_start();

require('../../conexao.php');
require('../../verifica_login.php');

error_reporting(0);

$dir = substr(__DIR__, -5);

if($dir != $_SESSION['hotel']){
    echo "<script>
    alert('Você não tem permissão para acessar esta pagina!')
    window.location.replace('../../index.html')
    </script>";
    exit();
}

$hoje = date('Y-m-d');

//Quantidade Total de Apartamentos
$roomTotalQuantidades = array();

$query_Total = $conexao->prepare("SELECT room_type, COUNT(*) as count FROM $dir"."_excel_gestaorecepcao_roomstatus WHERE id > 0 AND (room_status != 'O.O.O-CL.' AND room_status != 'O.O.O-DTY') GROUP BY room_type");
$query_Total->execute();
$resultados_Total = $query_Total->fetchAll(PDO::FETCH_ASSOC);

foreach ($resultados_Total as $resultado_Total) {
    $room_type = $resultado_Total['room_type'];
    $count_Total = $resultado_Total['count'];
    $roomTotalQuantidades[$room_type] = $count_Total;
}

$roomTypes = ['QEC', 'QSC', 'QEE', 'TWC', 'SKC', 'D2C'];
$roomTotalQtd = array_sum(array_intersect_key($roomTotalQuantidades, array_flip($roomTypes)));

//Status de todos os quartos LIMPOS SUJO BLOQUEADOS
$roomStatusQuantidades = array();

$query_rooms = $conexao->prepare("SELECT room_status, COUNT(*) as count FROM $dir"."_excel_gestaorecepcao_roomstatus WHERE id > 0 GROUP BY room_status");
$query_rooms->execute();

$resultados_rooms = $query_rooms->fetchAll(PDO::FETCH_ASSOC);

foreach ($resultados_rooms as $resultado_rooms) {
    $room_status = $resultado_rooms['room_status'];
    $count_rooms = $resultado_rooms['count'];
    $roomStatusQuantidades[$room_status] = $count_rooms;
}

//Vagos Limpos por Categoria
$roomTypeQuantidades = array();

$query_rooms_type = $conexao->prepare("SELECT room_type, COUNT(*) as count FROM $dir"."_excel_gestaorecepcao_roomstatus WHERE id > 0 AND (room_status = 'AV.-CL.' OR room_status = 'Limpo' OR room_status = 'Designado') GROUP BY room_type");
$query_rooms_type->execute();
$resultados_rooms_type = $query_rooms_type->fetchAll(PDO::FETCH_ASSOC);

$roomTypeQuantidades = array(
    'QEC' => 0,
    'QSC' => 0,
    'QEE' => 0,
    'TWC' => 0,
    'SKC' => 0,
    'D2C' => 0
);

foreach ($resultados_rooms_type as $resultado_rooms_type) {
    $room_type = $resultado_rooms_type['room_type'];
    $count_rooms_type = $resultado_rooms_type['count'];
    $roomTypeQuantidades[$room_type] = $count_rooms_type;
}

//Ocupados de Acordo ao In House
$roomInhouseQuantidades = array();

$query_Inhouse = $conexao->prepare("SELECT r.room_type, COUNT(*) as count FROM $dir"."_excel_gestaorecepcao_roomstatus AS r WHERE r.room_number IN (SELECT i.room_number FROM $dir"."_excel_gestaorecepcao_inhouse AS i WHERE i.id > 0 AND i.checkout > '{$hoje}') GROUP BY r.room_type");
$query_Inhouse->execute();
$resultados_Inhouse = $query_Inhouse->fetchAll(PDO::FETCH_ASSOC);

$roomInhouseQuantidades = array(
    'QEC' => 0,
    'QSC' => 0,
    'QEE' => 0,
    'TWC' => 0,
    'SKC' => 0,
    'D2C' => 0
);

foreach ($resultados_Inhouse as $resultado_Inhouse) {
    $room_type = $resultado_Inhouse['room_type'];
    $count = $resultado_Inhouse['count'];
    $roomInhouseQuantidades[$room_type] = $count;
}

$roomTypes = ['QEC', 'QSC', 'QEE', 'TWC', 'SKC', 'D2C'];
$roomTotalQtd -= array_sum(array_intersect_key($roomInhouseQuantidades, array_flip($roomTypes)));

//Todas as Chegadas do Dia
$query_chegadas = $conexao->prepare("SELECT * FROM $dir"."_excel_gestaorecepcao_arrivals WHERE id > 0 AND alteracao = 'Pendente'");
$query_chegadas->execute();
$chegadas_qtd = $query_chegadas->rowCount();

$query = $conexao->prepare("SELECT room_type, COUNT(*) as count FROM $dir"."_excel_gestaorecepcao_arrivals WHERE id > 0 AND alteracao = 'Pendente' GROUP BY room_type");
$query->execute();
$resultados = $query->fetchAll(PDO::FETCH_ASSOC);

$quantidades = array();

$quantidades = array(
    'QEC' => 0,
    'QSC' => 0,
    'QEE' => 0,
    'TWC' => 0,
    'SKC' => 0,
    'D2C' => 0
);

foreach ($resultados as $resultado) {
    $room_type = $resultado['room_type'];
    $count = $resultado['count'];
    $quantidades[$room_type] = $count;
}

$roomTypes = ['QEC', 'QSC', 'QEE', 'TWC', 'SKC', 'D2C'];
$roomTotalQtd -= array_sum(array_intersect_key($quantidades, array_flip($roomTypes)));

//Saidas do Dia
$query_saidas = $conexao->prepare("SELECT * FROM $dir"."_excel_gestaorecepcao_inhouse WHERE id > 0 AND checkout = '{$hoje}'");
$query_saidas->execute();
$saidas_qtd = $query_saidas->rowCount();

//In House
$query_house = $conexao->prepare("SELECT * FROM $dir"."_excel_gestaorecepcao_inhouse WHERE id > 0");
$query_house->execute();
$inhouse_qtd = $query_house->rowCount();

//Quantidade total de quartos
$query_quantidade_quartos = $conexao->prepare("SELECT * FROM $dir"."_excel_gestaorecepcao_roomstatus WHERE id > 0");
$query_quantidade_quartos->execute();

$quantidade_quartos = $query_quantidade_quartos->rowCount();

echo "<meta HTTP-EQUIV='refresh' CONTENT='1800'>";

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="images/favicon.ico" type="image/x-icon">
    <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="../../css/style_tabela.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Gestão Recepção - Downtime</title>
</head>
<body>
<a href="../../painel.php"><button class="botao-logout-left"><b>Voltar</b></button></a>
<a href="../../logout.php"><button class="botao-logout-right"><?php echo $_SESSION['name'] ?> <b>[Logout]</b></button></a>

<div id="container-topo">
<h1>Gestão Recepção (Downtime)</h1>
<div class="botao-topo"><a href="index.php"><button>Importar</button></a></div>
</div>
<div id="container-topo">
<div class="botao-topo"><a href="javascript:void(0)" onclick='window.open("cashier.php","iframe")'><button>Caixa</button></a></div>
<div class="botao-topo"><a href="javascript:void(0)" onclick='window.open("lancamentos.php","iframe")'><button>Lançamentos</button></a></div>
<div class="botao-topo"><a href="javascript:void(0)" onclick='window.open("checkins.php","iframe")'><button>Checkins</button></a></div>
<div class="botao-topo"><a href="javascript:void(0)" onclick='window.open("checkouts.php","iframe")'><button>Checkouts</button></a></div>
<div class="botao-topo"><a href="javascript:void(0)" onclick='window.open("canceladas.php","iframe")'><button>Canceladas</button></a></div>
<div class="botao-topo"><a href="javascript:void(0)" onclick='window.open("prorrogadas.php","iframe")'><button>Prorrogadas</button></a></div>
</div>
<div id="container-topo">
<div class="botao-topo-chegadas"><a href="javascript:void(0)" onclick='window.open("chegadas.php","iframe")'><button>Chegadas</button></a></div>
<div class="botao-topo-inhouse"><a href="javascript:void(0)" onclick='window.open("inhouse.php","iframe")'><button>In House</button></a></div>
<div class="botao-topo-saidas"><a href="javascript:void(0)" onclick='window.open("saidas.php","iframe")'><button>Saidas</button></a></div>
<div class="botao-topo-rooms"><a href="javascript:void(0)" onclick='window.open("roomstatus.php","iframe")'><button>Room Status</button></a></div>
</div>

<div class="container">

<div class="appointment-list">
<fieldset>
<legend> Resumo do dia | (<?php echo $chegadas_qtd ?>) Chegadas | (<?php echo $saidas_qtd ?>) Saidas | (<?php echo $inhouse_qtd ?>) In House | Ocupação ( <?php echo round(($inhouse_qtd + $chegadas_qtd - $saidas_qtd) / $quantidade_quartos * 100, 2) ?>% )</legend>
<div class="appointment-resumo-1">
Chegadas/Vagos Limpo<br>
<span class="name">QEC</span> (<span class="time"><?php echo $quantidades['QEC'] ?>/<?php echo $roomTypeQuantidades['QEC'] ?></span>) - <span class="name">QSC</span> (<span class="time"><?php echo $quantidades['QSC'] ?>/<?php echo $roomTypeQuantidades['QSC'] ?></span>) - <span class="name">D2C</span> (<span class="time"><?php echo $quantidades['D2C'] ?>/<?php echo $roomTypeQuantidades['D2C'] ?></span>) - <span class="name">QEE</span> (<span class="time"><?php echo $quantidades['QEE'] ?>/<?php echo $roomTypeQuantidades['QEE'] ?></span>) - <span class="name">TWC</span> (<span class="time"><?php echo $quantidades['TWC'] ?>/<?php echo $roomTypeQuantidades['TWC'] ?></span>) - <span class="name">SKC</span> (<span class="time"><?php echo $quantidades['SKC'] ?>/<?php echo $roomTypeQuantidades['SKC'] ?></span>)
</div>
<div class="appointment-resumo-2">
<span class="name">Vagos Limpo</span> (<span class="time"><?php echo $roomStatusQuantidades['AV.-CL.']+$roomStatusQuantidades['Limpo']+$roomStatusQuantidades['Designado'] ?></span>) - <span class="name">Vagos Sujo</span> (<span class="time"><?php echo $roomStatusQuantidades['AV.-DTY']+$roomStatusQuantidades['Sujo'] ?></span>) - <span class="name">Bloqueados</span> (<span class="time"><?php echo $roomStatusQuantidades['O.O.O-CL.']+$roomStatusQuantidades['O.O.O-DTY']+$roomStatusQuantidades['Bloqueado'] ?></span>)
</div>
<div class="appointment-resumo-1">
Disponiveis ( <b><?php echo $roomTotalQtd; ?></b> )<br>
<span class="name">QEC</span> (<span class="time"><?php echo $roomTotalQuantidades['QEC']-$roomInhouseQuantidades['QEC']-$quantidades['QEC'] ?></span>) - <span class="name">QSC</span> (<span class="time"><?php echo $roomTotalQuantidades['QSC']-$roomInhouseQuantidades['QSC']-$quantidades['QSC'] ?></span>) - <span class="name">D2C</span> (<span class="time"><?php echo $roomTotalQuantidades['D2C']-$roomInhouseQuantidades['D2C']-$quantidades['D2C'] ?></span>) - <span class="name">QEE</span> (<span class="time"><?php echo $roomTotalQuantidades['QEE']-$roomInhouseQuantidades['QEE']-$quantidades['QEE'] ?></span>) - <span class="name">TWC</span> (<span class="time"><?php echo $roomTotalQuantidades['TWC']-$roomInhouseQuantidades['TWC']-$quantidades['TWC'] ?></span>) - <span class="name">SKC</span> (<span class="time"><?php echo $roomTotalQuantidades['SKC']-$roomInhouseQuantidades['SKC']-$quantidades['SKC'] ?></span>)
</div>
</fieldset>
</div>
</div>
<div id="container-topo">
<div class="botao-acao"><button onclick='window.open("newarrival.php","iframe")' class="botao">New Arrival</button></div>
<div class="botao-acao"><button onclick="executarForm('Checkin')" class="botao">Checkin</button></div>
<div class="botao-acao"><button onclick="executarForm('CancelarReserva')" class="botao">Cancelar Reserva</button></a></div>
<div class="botao-acao"><button onclick="executarForm('Prorrogar')" class="botao">Prorrogar Reserva</button></a></div>
<div class="botao-acao"><button onclick="executarForm('Checkout')" class="botao">Checkout</button></a></div>
<div class="botao-acao"><button onclick="executarForm('Produtos')" class="botao">Lançar Produtos</button></a></div>
<div class="botao-acao"><button onclick="executarForm('Pagamentos')" class="botao">Lançar Pagamentos</button></a></div>
</div>
<iframe name="iframe" id="iframe" src="chegadas.php"></iframe>

<script>
function executarForm(valor) {
  var iframe = document.getElementById("iframe");
  var iframeWindow = iframe.contentWindow;

  if(valor === 'Checkin' || valor === 'CancelarReserva'){
    var formulario = iframeWindow.document.getElementById("chegadas");
  }else{
    var formulario = iframeWindow.document.getElementById("Inhouse");
  }

  // Criar o elemento input hidden
  var inputHidden = document.createElement("input");
  inputHidden.type = "hidden";
  inputHidden.name = "id_job";
  inputHidden.value = valor;

  // Adicionar o elemento input hidden ao formulário
  formulario.appendChild(inputHidden);

  // Faça qualquer manipulação adicional no formulário, se necessário

  formulario.submit();
}
</script>


</body>
</html>
