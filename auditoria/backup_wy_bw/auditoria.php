<?php

session_start();

require('../../conexao.php');
require('../../verifica_login.php');

error_reporting(0);

$dir = substr(__DIR__, -7);

if($dir != $_SESSION['hotel']){
    echo "<script>
    alert('Você não tem permissão para acessar esta pagina!')
    top.location.replace('../../index.html');
    </script>";
    exit();
}

$hoje = date('Y-m-d');

$status_auditoria = $_SESSION['status_auditoria'];

if($status_auditoria == 'Concluida'){
    echo "<script>
    alert('Auditoria não foi Iniciada!')
    top.location.replace('index.php');
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
    <link rel="icon" type="image/x-icon" href="../../images/favicon.ico">
    <link rel="shortcut icon" href="../../images/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="../../css/style_tabela.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Auditoria Digital</title>
</head>
<body>
<a href="../../painel.php"><button class="botao-logout-left"><b>Voltar</b></button></a>
<a href="../../logout.php"><button class="botao-logout-right"><?php echo $_SESSION['name'] ?> <b>[Logout]</b></button></a>

<div id="container-topo">
<h1>Auditoria Digital</h1>
<?php if($status_auditoria == 'Em Andamento Pre'){ ?>
<div class="botao-topo"><a href="index.php"><button>Importar Pós Auditoria</button></a></div>
<?php }else{ ?>
<div class="botao-topo"><a href="finalizar.php"><button>Finalizar Auditoria</button></a></div>
<?php } ?>
</div>

<div id="container-topo">
<div class="botao-topo-chegadas"><a href="javascript:void(0)" onclick='window.open("gerencial.php","iframe")'><button>Gerencial</button></a></div>
<div class="botao-topo-inhouse"><a href="javascript:void(0)" onclick='window.open("controlegarantias.php","iframe")'><button>Garantias</button></a></div>
<div class="botao-topo-inhouse"><a href="javascript:void(0)" onclick='window.open("freestay.php","iframe")'><button>Free Stay</button></a></div>
<div class="botao-topo-inhouse"><a href="javascript:void(0)" onclick='window.open("caixa.php","iframe")'><button>Caixa</button></a></div>
<div class="botao-topo-chegadas"><a href="javascript:void(0)" onclick='window.open("noshow.php","iframe")'><button>No Show</button></a></div>
</div>
<br>
<div id="container-topo">
<div class="botao-acao"><button onclick='window.open("importar.php","iframe")' class="botao">Importar</button></div>
<div class="botao-acao"><button onclick="executarForm('salvar_parcial')" class="botao">Salvar Parcial</button></div>
<div class="botao-acao"><button onclick='window.open("exportar.php","iframe")' class="botao">Exportar</button></div>
</div>
<?php if($status_auditoria == 'Em Andamento Pre'){ ?>
<iframe name="iframe" id="iframe" src="controlegarantias.php"></iframe>
<?php }else{ ?>
<iframe name="iframe" id="iframe" src="gerencial.php"></iframe>
<?php } ?>

<script>
function executarForm(valor) {
  var iframe = document.getElementById("iframe");
  var iframeWindow = iframe.contentWindow;
  var formulario = iframeWindow.document.getElementById("formulario_auditoria");

  if(valor === 'salvar_parcial'){

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
