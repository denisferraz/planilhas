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
    <div class="container">
        <h1>Backup Auditoria Digital - [<?php echo ucfirst($_SESSION['hotel']); ?>]</h1><br>
        <form action="importar_excel_backup.php" method="POST" enctype="multipart/form-data" onsubmit="exibirPopup()">
        <input type="file" name="csvFile[]" accept=".csv" required><br>
        <table>
        <tr><td style="background-color: black" colspan="1"></td></tr>
        <tr><td><label><input type="checkbox" name="freestay" value="freestay" checked><b>Free Stay</b></label></td></tr>
        <tr><td colspan="1"></td></tr>
        <tr><td><label><input type="checkbox" name="presentlist" value="presentlist" checked><b>Controle Garantias</b></label></td></tr>
        <tr><td colspan="1"></td></tr>
        <tr><td><label><input type="checkbox" name="caixa" value="caixa" checked><b>Caixa</b></label></td></tr>
        <tr><td colspan="1"></td></tr>
        <?php
            if($status_auditoria == 'Em Andamento Pos'){
        ?>
        <tr><td><label><input type="checkbox" name="gerencial" value="gerencial" checked><b>Gerencial</b></label></td></tr>
        <?php
            }
        ?>
        <tr><td style="background-color: black" colspan="1"></td></tr>
        </table>
        <br><br>
        <input type="submit" value="Upload">
        </form>
        <br>
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
