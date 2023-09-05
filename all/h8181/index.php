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
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel='stylesheet' type='text/css' media='screen' href='../../css/style.css'>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Conciliaçao ALL</title>
</head>
<body>
<h1><?php echo $_SESSION['hotel_name']; ?> - Conciliação ALL</h1>
<span class="card-group-right"><a href="../../logout.php"><button>Clique aqui para Sair</button></a></span>
<span class="card-group-left"><a href="../../painel.php"><button>Voltar</button></a></span>

    <div class="container">
        <h1>Anexe os Arquivos para realizar a Conferencia Automatica</h1><br>
        <form action="importar_excel.php" method="POST" enctype="multipart/form-data" onsubmit="exibirPopup()">
        <label><b>Loyalt Reports</b></label>
        <input type="file" name="csvFile[]" accept=".csv" multiple required><br><br>
        <label><b>Hotel Link</b></label>
        <input type="file" name="excelFile" accept=".csv" required><br>
        <input type="submit" value="Upload">
        </form>
        <br>
        <?php
        if($_SESSION['hierarquia'] != 'Colaborador'){
        ?>
        <br>
        <a href="assinatura.php"><button>Assinatura N + 1</button></a>
        <?php } ?>
    </div>
<script>
    function exibirPopup() {
        Swal.fire({
            icon: 'warning',
            title: 'Estamos Ajustando suas Comissões...',
            text: 'Assim que a planilha baixar, clique em Finalizar!',
            showCancelButton: false,
            showConfirmButton: false,
            allowOutsideClick: false,
            willOpen: () => {
                Swal.showLoading();

                // Definir um temporizador de 15 segundos
                setTimeout(() => {
                    Swal.update({
                        showConfirmButton: true,
                        confirmButtonText: 'Finalizar'
                    });
                    Swal.hideLoading();
                }, 5000); // 10000 milissegundos = 10 segundos
            },
            footer: '<div style="text-align: center;">Clique em Finalizar após a conclusão.</div>' // Adicionar um texto informativo abaixo do spinner
        });
    }
</script>
</body>
</html>
