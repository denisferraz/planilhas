<?php

session_start();

require('../../conexao.php');
require('../../verifica_login.php');

error_reporting(0);

$dir = substr(__DIR__, -7);

if($dir != $_SESSION['hotel']){
    echo "<script>
    alert('Você não tem permissão para acessar esta pagina!')
    window.location.replace('../../index.html')
    </script>";
    exit();
}

$min_dia = date('Y-m-d', strtotime("$hoje") -3600);

$status_auditoria = $_SESSION['status_auditoria'];

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel='stylesheet' type='text/css' media='screen' href='../../css/style.css'>
    <link rel="icon" type="image/x-icon" href="../../images/favicon.ico">
    <link rel="shortcut icon" href="../../images/favicon.ico" type="image/x-icon">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Auditoria Digital</title>
</head>
<body>
<h1><?php echo $_SESSION['hotel_name']; ?> - Auditoria Digital</h1>
<span class="card-group-right"><a href="../../logout.php"><button>Clique aqui para Sair</button></a></span>
<span class="card-group-left"><a href="../../painel.php"><button>Voltar</button></a></span>
<?php
if($_SESSION['hierarquia'] != 'Colaborador'){
$id = base64_encode('Novo,123');
?>
<span class="card-group-left"><a href="budget.php"><button>Budget [POA]</button></a></span>
<?php
}
?>

    <div class="container">
<?php if($status_auditoria == 'Concluida'){ ?>
        <h1>Anexe os Arquivos para iniciar a Conferencia da Auditoria Digital</h1><br>
        <form action="importar_excel_pre.php" method="POST" enctype="multipart/form-data" onsubmit="exibirPopup()">
        <label><b>Data da Auditoria</b></label>
        <input type="date" name="data_auditoria" min="<?php echo $min_dia ?>" max="<?php echo $hoje ?>" value="<?php echo $hoje ?>" required><br>
        <label><b>Salve os relatorios como: XLS Relatório Arquivo</b><br><br>
        <b>[1]</b> - Recepção->Hospedes na Casa [ <b>M:\Users\Vega\Downloads\inhouse.xls</b> ]<br>
        <b>[2]</b> - Caixa->Recebimentos e Pagamentos [ <b>M:\Users\Vega\Downloads\caixa.xls</b> ]<br>
        <br>
        </label>
        <input type="file" name="xlsFile[]" accept=".xls" multiple required><br><br>
        <input type="submit" value="Upload">
        </form>
<?php }else{ ?>
    <h1>Anexe os Arquivos para Continuar a Conferencia da Auditoria Digital</h1><br>
        <form action="importar_excel_pos.php" method="POST" enctype="multipart/form-data" onsubmit="exibirPopup()">
        <label><b>Salve os relatorios como: XLS Relatório Arquivo</b><br><br>
        <b>[1]</b> - Gerenciais->Resumo Diario de Situação [ <b>M:\Users\Vega\Downloads\rds.xls</b> ]<br>
        <b>[2]</b> - Reservas->Reservas No-Shows no Periodo [ <b>M:\Users\Vega\Downloads\noshow.xls</b> ]<br>
        <br>
        </label>
        <input type="file" name="xlsFile[]" accept=".xls" multiple required><br><br>
        <input type="submit" value="Upload">
        </form>
<?php } ?>
        <br>
        <?php
        if($_SESSION['hierarquia'] != 'Colaborador' && $status_auditoria == 'Concluida'){
        ?>
        <br>
        <a href="assinatura.php"><button>Assinatura N + 1</button></a>
        <?php }else if($status_auditoria == 'Em Andamento Pre' || $status_auditoria == 'Em Andamento Pos'){ ?>
        <br>
        <a href="auditoria.php"><button>Ja anexou?<br>Clique Aqui para Preencher</button></a>
        <?php } ?>
    </div>
    <script>
    function exibirPopup() {
        Swal.fire({
            icon: 'warning',
            title: 'Estamos Conferindo seus Arquivos...',
            text: 'Você sera redirecionado assim que acabarmos!',
            showCancelButton: false,
            showConfirmButton: false,
            allowOutsideClick: false,
            willOpen: () => {
                Swal.showLoading();
            }
        });
    }
</script>
</body>
</html>
