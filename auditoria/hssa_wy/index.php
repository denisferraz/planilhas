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

$query2 = $conexao->prepare("SELECT * FROM $dir"."_excel_auditoria_auditorias WHERE id > 0 ORDER BY id DESC LIMIT 1");
$query2->execute();
while($select2 = $query2->fetch(PDO::FETCH_ASSOC)){
    $auditoria = $select2['data_auditoria'];
    $status_auditoria = $select2['auditoria_status'];
}

$query = $conexao->prepare("SELECT * FROM $dir"."_excel_auditoria_auditorias WHERE id > 0 AND auditoria_status != 'Pendente' ORDER BY id DESC LIMIT 7");
$query->execute();

if (isset($_GET['id'])) {
    $_SESSION['id'] = mysqli_real_escape_string($conn_mysqli, $_GET['id']);
    $data_auditoria = date('Y-m-d', $_SESSION['id']);
}

if($_SESSION['id'] != 0){

$_SESSION['data_auditoria'] = $data_auditoria;

$_SESSION['freestay'] = 0;
$_SESSION['Garantias'] = 0;
$_SESSION['caixa'] = 0;
$_SESSION['gerencial'] = 0;
$_SESSION['noshow'] = 0;

    echo "<script>
    window.location.replace('auditoria.php')
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
<?php if($status_auditoria == 'Pendente'){ ?>
        <h1>Anexe os Arquivos para iniciar a Conferencia da Auditoria Digital</h1><br>
        <form action="importar_excel_pre.php" method="POST" enctype="multipart/form-data" onsubmit="exibirPopup()">
        <label><b>Data da Auditoria</b></label>
        <input type="date" name="data_auditoria" min="<?php echo $auditoria ?>" max="<?php echo $auditoria ?>" value="<?php echo $auditoria ?>" required><br>
        <label><b>Salve os relatorios como: XLS Relatório Arquivo</b><br><br>
        <b>[1]</b> - Recepção->Hospedes na Casa [ <b>M:\Users\Vega\Downloads\inhouse.xls</b> ]<br>
        <b>[2]</b> - Caixa->Recebimentos e Pagamentos [ <b>M:\Users\Vega\Downloads\caixa.xls</b> ]<br>
        <br>
        </label>
        <input type="file" name="xlsFile[]" accept=".xls" multiple required><br><br>
        <input type="submit" value="Upload">
        </form>
<?php }else if($status_auditoria == 'Em Andamento Pre'){ ?>
    <h1>Anexe os Arquivos para Continuar a Conferencia da Auditoria Digital do dia <?php echo date('d/m/Y', strtotime($auditoria)); ?></h1><br>
        <form action="importar_excel_pos.php" method="POST" enctype="multipart/form-data" onsubmit="exibirPopup()">
        <label><b>Salve os relatorios como: XLS Relatório Arquivo</b><br><br>
        <b>[1]</b> - Gerenciais->Resumo Diario de Situação [ <b>M:\Users\Vega\Downloads\rds.xls</b> ]<br>
        <b>[2]</b> - Reservas->Reservas No-Shows no Periodo [ <b>M:\Users\Vega\Downloads\noshow.xls</b> ]<br>
        <br>
        </label>
        <input type="file" name="xlsFile[]" accept=".xls" multiple required><br><br>
        <input type="submit" value="Upload">
        </form>
<?php }else{ ?>
    <h1>Continue a Auditoria Digital do dia <?php echo date('d/m/Y', strtotime($auditoria)); ?></h1><br>
<?php } ?>
        <br><center><b>Auditorias Anteriores</b><br><br>
        <?php
        while($select = $query->fetch(PDO::FETCH_ASSOC)){
            $data_auditoria = $select['data_auditoria'];
        ?>
        <div class="botao-acao">
            <button onclick='redirecionar("<?php echo strtotime("$data_auditoria"); ?>")' class="botao"><?php echo date('d/m/Y', strtotime("$data_auditoria")); ?></button>
        </div>
        <br>
        <?php
        }
        ?></center>
    </div>
    <script>
    function redirecionar(dataAuditoria) {
        // Use window.location.href para redirecionar para a página desejada na mesma janela.
        window.location.href = "index.php?id=" + dataAuditoria;
    }
    </script>
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
