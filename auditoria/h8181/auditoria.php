<?php

session_start();

require('../../conexao.php');
require('../../verifica_login.php');

error_reporting(0);

$dir = substr(__DIR__, -5);

if($dir != $_SESSION['hotel']){
    echo "<script>
    alert('Você não tem permissão para acessar esta pagina!')
    top.location.replace('../../index.html')
    </script>";
    exit();
}

$hoje = date('Y-m-d');

$status_auditoria = $_SESSION['status_auditoria'];

if($status_auditoria == 'Concluida'){
    echo "<script>
    alert('Auditoria não foi Iniciada!')
    top.location.replace('index.php')
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
    <link rel="icon" href="images/favicon.ico" type="image/x-icon">
    <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
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
<div class="botao-topo-inhouse"><a href="javascript:void(0)" onclick='window.open("ratecheck.php","iframe")'><button>Diarias</button></a></div>
<div class="botao-topo-inhouse"><a href="javascript:void(0)" onclick='window.open("creditlimit.php","iframe")'><button>Saldo Elevado</button></a></div>
<div class="botao-topo-chegadas"><a href="javascript:void(0)" onclick='window.open("controlebac.php","iframe")'><button>Bac</button></a></div>
<div class="botao-topo-chegadas"><a href="javascript:void(0)" onclick='window.open("controlegarantias.php","iframe")'><button>Garantias</button></a></div>
<div class="botao-topo-saidas"><a href="javascript:void(0)" onclick='window.open("noshow.php","iframe")'><button>No Show</button></a></div>
<div class="botao-topo-saidas"><a href="javascript:void(0)" onclick='window.open("freestay.php","iframe")'><button>Free Stay</button></a></div>
<div class="botao-topo-chegadas"><a href="javascript:void(0)" onclick='window.open("taxbase.php","iframe")'><button>Tax Base</button></a></div>
</div>
<br>
<?php if($status_auditoria == 'Em Andamento Pre'){ ?>
<iframe name="iframe" id="iframe" src="ratecheck.php"></iframe>
<?php }else{ ?>
<iframe name="iframe" id="iframe" src="gerencial.php"></iframe>
<?php } ?>
</body>
</html>
