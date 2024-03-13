<?php

session_start();

require('../../conexao.php');
require('../../verifica_login.php');

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
error_reporting(0);

$dir = substr(__DIR__, -7);

if($dir != $_SESSION['hotel']){
    echo "<script>
    alert('Você não tem permissão para acessar esta pagina!')
    window.location.replace('../../index.html')
    </script>";
    exit();
}

$hoje = date('Y-m-d');

// Chave de criptografia
$chave = $_SESSION['hotel'].$chave;

//Transformar Database em Arrays
$query = $conexao->prepare("SELECT * FROM $dir"."_excel_gestaorecepcao_inhouse WHERE id > 0");
$query->execute();

$presentlist_array = [];
while($select = $query->fetch(PDO::FETCH_ASSOC)){
    $dados_presentlist = $select['dados_presentlist'];
    $id = $select['id'];

// Para descriptografar os dados
$dados = base64_decode($dados_presentlist);
$dados_decifrados = openssl_decrypt($dados, $metodo, $chave, 0, $iv);

$dados_array = explode(';', $dados_decifrados);

$presentlist_array[] = [
    'id' => $id,
    'guest_name' => $dados_array[0],
    'checkin' => $dados_array[1],
    'checkout' => $dados_array[2],
    'noites' => $dados_array[3],
    'adultos' => $dados_array[4],
    'criancas' => $dados_array[5],
    'room_ratecode' => $dados_array[6],
    'room_msg' => $dados_array[9],
    'room_number' => $dados_array[8],
    'room_company' => $dados_array[10],
    'room_balance' => $dados_array[7],
    'alteracao' => $dados_array[11]
];

}

$filtered_presentlist = [];
foreach ($presentlist_array as $item) {
    if ($item['checkout'] === $hoje) {
        $filtered_presentlist[] = $item;
    }
}

$query_chegadas = $conexao->prepare("SELECT * FROM $dir"."_excel_gestaorecepcao_arrivals WHERE id > 0");
$query_chegadas->execute();

$arrivalslist_array = [];
while($select = $query_chegadas->fetch(PDO::FETCH_ASSOC)){
    $dados_arrivals = $select['dados_arrivals'];
    
// Para descriptografar os dados
$dados = base64_decode($dados_arrivals);
$dados_decifrados = openssl_decrypt($dados, $metodo, $chave, 0, $iv);

$dados_array = explode(';', $dados_decifrados);

$arrivalslist_array[] = [
    'id' => $id,
    'guest_name' => $dados_array[0],
    'noites' => $dados_array[1],
    'adultos' => $dados_array[2],
    'criancas' => $dados_array[3],
    'room_type' => $dados_array[4],
    'room_ratecode' => $dados_array[5],
    'room_msg' => $dados_array[6],
    'room_number' => $dados_array[7],
    'alteracao' => $dados_array[8]
];

}

$filtered_arrivalslist = [];
foreach ($arrivalslist_array as $item) {
    if ($item['alteracao'] === 'Pendente') {
        $filtered_arrivalslist[] = $item;
    }
}

$chegadas_qtd = count($filtered_arrivalslist);


//Room Types
$roomTypes = [];
$query = $conexao->prepare("SELECT * FROM $dir"."_excel_gestaorecepcao_roomtypes WHERE id > 0");
$query->execute();
while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
    $roomTypes[] = $row['room_type'];
}

//Quantidade Total de Apartamentos
$roomTotalQuantidades = array();

$query_Total = $conexao->prepare("SELECT room_type, COUNT(*) as count FROM $dir"."_excel_gestaorecepcao_roomstatus WHERE id > 0 AND room_status != 'Bloqueado' GROUP BY room_type");
$query_Total->execute();
$resultados_Total = $query_Total->fetchAll(PDO::FETCH_ASSOC);

foreach ($resultados_Total as $resultado_Total) {
    $room_type = $resultado_Total['room_type'];
    $count_Total = $resultado_Total['count'];
    $roomTotalQuantidades[$room_type] = $count_Total;
}

$roomTotalQtd = array_sum(array_intersect_key($roomTotalQuantidades, array_flip($roomTypes)));

//Status de todos os quartos LIMPOS SUJO BLOQUEADOS
$roomStatusQuantidades = array();

$roomStatusQuantidades = array(
    'Limpo' => 0,
    'Designado' => 0,
    'Sujo' => 0,
    'Bloqueado' => 0
);

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

$query_rooms_type = $conexao->prepare("SELECT room_type, COUNT(*) as count FROM $dir"."_excel_gestaorecepcao_roomstatus WHERE id > 0 AND (room_status = 'Limpo' OR room_status = 'Designado') GROUP BY room_type");
$query_rooms_type->execute();
$resultados_rooms_type = $query_rooms_type->fetchAll(PDO::FETCH_ASSOC);

foreach ($roomTypes as $room_type) {
    $roomTypeQuantidades[$room_type] = 0;
}

foreach ($resultados_rooms_type as $resultado_rooms_type) {
    $room_type = $resultado_rooms_type['room_type'];
    $count_rooms_type = $resultado_rooms_type['count'];
    $roomTypeQuantidades[$room_type] = $count_rooms_type;
}

//Ocupados de Acordo ao In House
$roomInhouseQuantidades = array();

$room_numbers = [];
foreach ($presentlist_array as $item) {
    if ($item['checkout'] > $hoje) {
        $room_numbers[] = $item['room_number'];
    }
}

$room_numbers_str = implode(',', $room_numbers);
$query_Inhouse = $conexao->prepare("SELECT r.room_type, COUNT(*) as count FROM $dir"."_excel_gestaorecepcao_roomstatus AS r WHERE r.room_number IN ($room_numbers_str) GROUP BY r.room_type");
$query_Inhouse->execute();
$resultados_Inhouse = $query_Inhouse->fetchAll(PDO::FETCH_ASSOC);

foreach ($roomTypes as $room_type) {
    $roomInhouseQuantidades[$room_type] = 0;
}

foreach ($resultados_Inhouse as $resultado_Inhouse) {
    $room_type = $resultado_Inhouse['room_type'];
    $count = $resultado_Inhouse['count'];
    $roomInhouseQuantidades[$room_type] = $count;
}

$roomTotalQtd -= array_sum(array_intersect_key($roomInhouseQuantidades, array_flip($roomTypes)));

//Agrupar Arrivals
$grouped_arrivals = [];
foreach ($arrivalslist_array as $item) {
    $room_type = $item['room_type'];
    
    if (!isset($grouped_arrivals[$room_type])) {
        $grouped_arrivals[$room_type] = [];
    }
    $grouped_arrivals[$room_type][] = $item;
}

$quantidades = array();

foreach ($roomTypes as $room_type) {
    $quantidades[$room_type] = 0;
}

foreach ($grouped_arrivals as $room_type => $items) {
    $count = count($items);
    // Verifique se o $room_type está presente no array $quantidades antes de atribuir a contagem.
    if (isset($quantidades[$room_type])) {
        $quantidades[$room_type] = $count;
    }
}

$roomTotalQtd -= array_sum(array_intersect_key($quantidades, array_flip($roomTypes)));

//Saidas do Dia
$filtered_saidas = [];
foreach ($presentlist_array as $item) {
    if ($item['checkout'] === $hoje && $item['alteracao'] === 'Pendente') {
        $filtered_saidas[] = $item;
    }
}
$saidas_qtd = count($filtered_saidas);

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
    <link rel="icon" type="image/x-icon" href="../../images/favicon.ico">
    <link rel="shortcut icon" href="../../images/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="../../css/style_tabela.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Gestão Recepção - Downtime</title>
</head>
<body>
<a href="../../painel.php"><button class="botao-logout-left"><b>Voltar</b></button></a>
<a href="../../logout.php"><button class="botao-logout-right"><?php echo $_SESSION['name'] ?> <b>[Logout]</b></button></a>

<div id="container-topo">
<h1>Gestão Recepção (Downtime)</h1>
<div class="botao-topo"><a href="index.php"><button class="botao">Importar</button></a></div>
</div>
<div id="container-topo">
<div class="botao-topo"><a href="javascript:void(0)" onclick='window.open("alteracoes.php","iframe")'><button>Alterações</button></a></div>
<div class="botao-topo"><a href="javascript:void(0)" onclick='window.open("cashier.php","iframe")'><button>Fechar Caixa</button></a></div>
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
<?php
$totalRoomTypes = count($roomTypes);
foreach ($roomTypes as $index => $room_type) {
    echo "<span class=\"name\">".$room_type."</span> (<span class=\"time\">".$quantidades[$room_type]."/".$roomTypeQuantidades[$room_type]."</span>)";
    
    if ($index != $totalRoomTypes - 1) {
        echo " |-| ";
    }

}
?>
</div>
<div class="appointment-resumo-2">
<span class="name">Vagos Limpo</span> (<span class="time"><?php echo $roomStatusQuantidades['Limpo']+$roomStatusQuantidades['Designado'] ?></span>) - <span class="name">Vagos Sujo</span> (<span class="time"><?php echo $roomStatusQuantidades['Sujo'] ?></span>) - <span class="name">Bloqueados</span> (<span class="time"><?php echo $roomStatusQuantidades['Bloqueado'] ?></span>)
</div>
<div class="appointment-resumo-1">
Disponiveis ( <b><?php echo $roomTotalQtd; ?></b> )<br>
<?php
$totalRoomTypes = count($roomTypes);
foreach ($roomTypes as $index => $room_type) {
    echo "<span class=\"name\">".$room_type."</span> (<span class=\"time\">".$roomTotalQuantidades[$room_type]-$roomInhouseQuantidades[$room_type]-$quantidades[$room_type]."</span>)";
    
    if ($index != $totalRoomTypes - 1) {
        echo " |-| ";
    }

}
?>
</div>
</fieldset>
</div>
</div>
<div id="container-topo">
<div class="botao-acao"><button onclick='window.open("newarrival.php","iframe")' class="botao">New Arrival</button></div>
<div class="botao-acao"><button onclick="executarForm('Checkin')" class="botao">Checkin</button></div>
<div class="botao-acao"><button onclick="executarForm('DesignarApto')" class="botao">Tirar Designação</button></div>
<div class="botao-acao"><button onclick="executarForm('EditarReserva')" class="botao">Editar Reserva</button></div>
<div class="botao-acao"><button onclick="executarForm('CancelarReserva')" class="botao">Cancelar Reserva</button></a></div>
<div class="botao-acao"><button onclick="executarForm('Prorrogar')" class="botao">Prorrogar Reserva</button></a></div>
<div class="botao-acao"><button onclick="executarForm('RoomMove')" class="botao">Trocar Uh</button></div>
<div class="botao-acao"><button onclick="executarForm('Checkout')" class="botao">Checkout</button></a></div>
<div class="botao-acao"><button onclick="executarForm('Produtos')" class="botao">Lançar Produtos</button></a></div>
<div class="botao-acao"><button onclick="executarForm('Pagamentos')" class="botao">Lançar Pagamentos</button></a></div>
</div>
<iframe name="iframe" id="iframe" src="chegadas.php"></iframe>

<script>
function executarForm(valor) {
  var iframe = document.getElementById("iframe");
  var iframeWindow = iframe.contentWindow;

  if(valor === 'Checkin' || valor === 'CancelarReserva' || valor === 'DesignarApto' || valor === 'EditarReserva'){
    var formulario = iframeWindow.document.getElementById("chegadas");
  }else{
    var formulario = iframeWindow.document.getElementById("Inhouse");
  }

  if(valor === 'DesignarApto'){
  // Criar o elemento input hidden
  var inputHidden2 = document.createElement("input");
  inputHidden2.type = "hidden";
  inputHidden2.name = "id_acao";
  inputHidden2.value = valor;
  valor = 'Checkin';
  }else{
// Criar o elemento input hidden
  var inputHidden2 = document.createElement("input");
  inputHidden2.type = "hidden";
  inputHidden2.name = "id_acao";
  inputHidden2.value = ''; 
  }

  // Criar o elemento input hidden
  var inputHidden = document.createElement("input");
  inputHidden.type = "hidden";
  inputHidden.name = "id_job";
  inputHidden.value = valor;

  // Adicionar o elemento input hidden ao formulário
  formulario.appendChild(inputHidden);
  formulario.appendChild(inputHidden2);

  // Faça qualquer manipulação adicional no formulário, se necessário

  formulario.submit();
}
</script>


</body>
</html>
