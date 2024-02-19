<?php

session_start();

require('../../conexao.php');
require('../../verifica_login.php');

error_reporting(0);

$dir = substr(__DIR__, -7);

if($dir != $_SESSION['hotel'] || $_SESSION['hierarquia'] == 'Colaborador'){
    echo "<script>
    alert('Você não tem permissão para acessar esta pagina!')
    top.location.replace('../../index.html');
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
    <title>Auditoria Digital - Budget [POA]</title>
</head>
<body>
<a href="../../painel.php"><button class="botao-logout-left"><b>Voltar</b></button></a>
<a href="../../logout.php"><button class="botao-logout-right"><?php echo $_SESSION['name'] ?> <b>[Logout]</b></button></a>

<div id="container-topo">
<h1>Auditoria Digital - Budget [POA]</h1>
</div>

<div id="container-topo">
<div class="botao-acao"><button onclick="executarForm('salvar_parcial')" class="botao">Salvar</button></div>
</div>

<iframe name="iframe" id="iframe" src="poa.php"></iframe>

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
