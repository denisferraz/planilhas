<?php

session_start();

require('../../conexao.php');
require('../../verifica_login.php');

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
error_reporting(0);

$dir = substr(__DIR__, -5);

if($dir != $_SESSION['hotel'] || $_SESSION['id'] == 0){
    echo "<script>
    alert('Você não tem permissão para acessar esta pagina!')
    top.location.replace('../../index.html');
    </script>";
    exit();
}

$hoje = date('Y-m-d');

$data_plano = date('Y-m-d', $_SESSION['id']);

$query_quartos = $conexao->prepare("SELECT * FROM $dir"."_excel_plano_quartos WHERE id > 0 AND data_plano = '{$data_plano}'");
$query_quartos->execute();

$dados_roomstatus = [];

while($select_quartos = $query_quartos->fetch(PDO::FETCH_ASSOC)){
    $id  = $select_quartos['id'];
    $qtd_camareira = $select_quartos['qtd_camareira'];
    $id_camareira = $select_quartos['id_camareira'];
    $room_number = $select_quartos['room_number'];
    $guest_name = $select_quartos['guest_name'];
    $room_stay_status = $select_quartos['room_stay_status'];
    $room_status_1 = $select_quartos['room_status_1'];
    $room_status_2 = $select_quartos['room_status_2'];
    $room_type = $select_quartos['room_type'];

    $dados_roomstatus[] = [
        'id' => $id,
        'id_camareira' => $id_camareira,
        'room_number' => $room_number,
        'guest_name' => $guest_name,
        'room_stay_status' => $room_stay_status,
        'room_status_1' => $room_status_1,
        'room_status_2' => $room_status_2,
        'room_type' => $room_type
    ];
}

$_SESSION['dados_roomstatus'] = $dados_roomstatus;
$_SESSION['qtd_camareira'] = $qtd_camareira;

$query_camareiras = $conexao->prepare("SELECT * FROM $dir"."_excel_plano_camareiras WHERE id > -2 AND data_plano = '{$data_plano}'");
$query_camareiras->execute();
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
    <title>Plano de Quartos</title>
</head>
<body>
<a href="index.php?id=0"><button class="botao-logout-left"><b>Voltar</b></button></a>
<a href="../../logout.php"><button class="botao-logout-right"><?php echo $_SESSION['name'] ?> <b>[Logout]</b></button></a>

<div id="container-topo">
<h1><?php echo $_SESSION['hotel_name']; ?><br>Plano de Quartos - <?php echo date('d/m/Y', $_SESSION['id']); ?></h1>
</div>


<div id="container-topo">
<div class="botao-topo-inhouse"><a href="javascript:void(0)" onclick='window.open("dashboard.php","iframe")'><button>Dashboard</button></a></div>
<div class="botao-topo-saidas"><a href="javascript:void(0)" onclick='window.open("bloqueados.php","iframe")'><button>Bloqueados</button></a></div>
<div class="botao-topo-saidas"><a href="javascript:void(0)" onclick='window.open("vagoslimpo.php","iframe")'><button>Vagos Limpo</button></a></div>
<div class="botao-topo-chegadas"><a href="javascript:void(0)" onclick='window.open("ocupados.php","iframe")'><button>Ocupados</button></a></div>
<div class="botao-topo-chegadas"><a href="javascript:void(0)" onclick='window.open("previstos.php","iframe")'><button>Previstos</button></a></div>
<div class="botao-topo-chegadas"><a href="javascript:void(0)" onclick='window.open("vagossujo.php","iframe")'><button>Vagos Sujo</button></a></div>
</div>
<br>
<div id="container-topo">
<?php

$_SESSION['camareira_0'] = 'Sem Camareira';

while($select_camareiras = $query_camareiras->fetch(PDO::FETCH_ASSOC)){
  $id_camareira = $select_camareiras['id_camareira'];
  $camareira = $select_camareiras['camareira'];

  $_SESSION['camareira_'.$id_camareira] = $camareira;
  $_SESSION['id_camareira_'.$id_camareira] = $id_camareira;

?>
<div class="botao-topo-inhouse"><a href="javascript:void(0)" onclick='window.open("camareira.php?id=<?php echo $id_camareira; ?>","iframe")'><button><?php echo $camareira; ?></button></a></div>
<?php
}
?>
</div>
<br>
<div id="container-topo">
<div class="botao-acao"><button onclick='window.open("camareiras.php","iframe")' class="botao">Camareiras</button></div>
<div class="botao-acao"><button onclick="executarForm('salvar_plano')" class="botao">Salvar Plano</button></div>
<div class="botao-acao"><button onclick="executarForm('imprimir_plano')" class="botao">Imprimir Plano</button></div>
</div>

<iframe name="iframe" id="iframe" src="dashboard.php"></iframe>

<script>
function executarForm(valor) {
  var iframe = document.getElementById("iframe");
  var iframeWindow = iframe.contentWindow;
  var formulario = iframeWindow.document.getElementById("formulario_plano");

  if(valor === 'salvar_plano' || valor === 'imprimir_plano'){

    // Remova o atributo 'required' de todos os elementos de input no formulário
  var inputs = formulario.getElementsByTagName("input");
  for (var i = 0; i < inputs.length; i++) {
    inputs[i].removeAttribute("required");

  }
  }

  // Criar o elemento input hidden
  var inputHidden = document.createElement("input");
  inputHidden.type = "hidden";
  inputHidden.name = "id_acao";
  inputHidden.value = valor;

  // Adicionar o elemento input hidden ao formulário
  formulario.appendChild(inputHidden);

  // Faça qualquer manipulação adicional no formulário, se necessário

  formulario.submit();
}
</script>

</body>
</html>
