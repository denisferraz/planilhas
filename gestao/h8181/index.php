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

$query = $conexao->prepare("SELECT * FROM $dir"."_excel_gestaorecepcao_arrivals WHERE id > 0");
$query->execute();
while($select = $query->fetch(PDO::FETCH_ASSOC)){
    $data_importacao = $select['data_importacao'];
}

$ultima_importacao = date('d/m/Y - H:i:s\h', strtotime("$data_importacao") - 3 * 3600);
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
    <title>Gestão Recepção - Downtime</title>
</head>
<body>
<h1><?php echo $_SESSION['hotel_name']; ?> - Gestão Recepção (Downtime)</h1>
<span class="card-group-right"><a href="../../logout.php"><button>Clique aqui para Sair</button></a></span>
<span class="card-group-left"><a href="../../painel.php"><button>Voltar</button></a></span>

    <div class="container">
        <h1>Ultima Importação: <?php echo $ultima_importacao; ?></h1><br>
        <form action="importar_excel.php" method="POST" enctype="multipart/form-data" onsubmit="exibirPopup()">
        <label><b>List of In Hous Guest + Expected Arrivals + Room Status (simplified)</b></label>
        <input type="file" name="csvFile[]" accept=".csv" multiple required><br>
        <input type="submit" value="Upload">
        </form>
        <br><br>
        Caso ja tenha importado anterior <a href="gestao.php"><button>Acesse o Painel Aqui</button></a>
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
