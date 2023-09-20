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

if($_SESSION['hierarquia'] == 'Colaborador'){
    echo "<script>
    alert('Você não tem permissão para acessar esta pagina!')
    window.location.replace('index.php')
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
    <title>Conciliaçao ALL</title>
</head>
<body>
<h1>Conciliação ALL</h1>

    <div class="container">
        <h1>Anexe a Planilha do ALL para Assinar como N + 1</h1><br>
        <form action="assinar_excel.php" method="POST" enctype="multipart/form-data" onsubmit="exibirPopup()">
        <label><b>Importar Planilha de Conciliação Finalizada</b></label>
        <input type="file" name="excelFile" accept=".xls" required><br>
        <input type="submit" value="Upload">
        </form>
        <br><br>
        <a href="index.php"><button>Voltar</button></a>
    </div>
<script>
    function exibirPopup() {
        Swal.fire({
            icon: 'warning',
            title: 'Estamos Assinando a Planilha...',
            text: 'Assim que o PDF baixar, clique em Finalizar!',
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
