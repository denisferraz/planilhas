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

$min_dia = date('Y-m-d', strtotime("$hoje") -3600);
$max_dia = date('Y-m-d', strtotime("$hoje") +3600);

$query = $conexao->prepare("SELECT * FROM $dir"."_excel_plano_quartos WHERE id > 0 GROUP BY data_plano LIMIT 7");
$query->execute();

if (isset($_GET['id'])) {
$_SESSION['id'] = mysqli_real_escape_string($conn_mysqli, $_GET['id']);
}

if($_SESSION['id'] != 0){
    echo "<script>
    window.location.replace('plano.php')
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
    <title>Plano de Quartos</title>
</head>
<body>
<h1><?php echo $_SESSION['hotel_name']; ?> - Plano de Quartos</h1>
<span class="card-group-right"><a href="../../logout.php"><button>Clique aqui para Sair</button></a></span>
<span class="card-group-left"><a href="../../painel.php"><button>Voltar</button></a></span>

    <div class="container">
        <h1>Anexe o Room Status (Simplified) para Gerar o Plano de Quartos Digital</h1><br>
        <form action="importar_excel.php" method="POST" enctype="multipart/form-data" onsubmit="exibirPopup()">
        <label><b>Data do Plano</b></label>
        <input type="date" name="data_plano" min="<?php echo $min_dia ?>" max="<?php echo $max_dia ?>" value="<?php echo $hoje ?>" required><br>
        <label><b>Camareiras</b></label>
        <input type="number" name="camareiras" min="0" max="100" value="10" required><br><br>
        <label><b>Room Status (Simplified).[CSV]</b></label>
        <input type="file" name="csvFile[]" accept=".csv" multiple required><br><br>
        <input type="submit" value="Gerar Plano">
        </form>
        <br><center><b>Planos Gerados</b><br><br>
        <?php
        while($select = $query->fetch(PDO::FETCH_ASSOC)){
            $data_plano = $select['data_plano'];
        ?>
        <div class="botao-acao">
            <button onclick='redirecionar("<?php echo strtotime("$data_plano"); ?>")' class="botao"><?php echo date('d/m/Y', strtotime("$data_plano")); ?></button>
        </div>
        <br>
        <?php
        }
        ?></center>
    </div>
    <script>
    function redirecionar(dataPlano) {
        // Use window.location.href para redirecionar para a página desejada na mesma janela.
        window.location.href = "index.php?id=" + dataPlano;
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
