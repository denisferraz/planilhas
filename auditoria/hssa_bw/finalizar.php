<?php

session_start();

require('../../conexao.php');
require('../../verifica_login.php');

$dir = substr(__DIR__, -7);

if($dir != $_SESSION['hotel']){
    echo "<script>
    alert('Você não tem permissão para acessar esta pagina!')
    window.location.replace('../../index.html')
    </script>";
    exit();
}

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
error_reporting(0);

$data_auditoria = $_SESSION['data_auditoria'];

$query_status = $conexao->prepare("SELECT * FROM $dir"."_excel_auditoria_auditorias WHERE id > 0 AND data_auditoria = '{$data_auditoria}'");
$query_status->execute();
while($select_status = $query_status->fetch(PDO::FETCH_ASSOC)){
    $status_auditoria = $select_status['auditoria_status'];
}

if($status_auditoria != 'Em Andamento Pos'){
    echo "<script>
    window.location.replace('auditoria.php')
    </script>";
    exit();
}

//Valida se toda a auditoria foi preenchida
if($_SESSION['freestay'] == 0){
    echo "<script>
    alert('Free Stay não foi Validado')
    window.location.replace('auditoria.php')
    </script>";
    exit();
}
if($_SESSION['gerencial'] == 0){
    echo "<script>
    alert('Gerencial não foi Validado')
    window.location.replace('auditoria.php')
    </script>";
    exit();
}
if($_SESSION['Garantias'] == 0){
    echo "<script>
    alert('Controle de Garantias não foi Validado')
    window.location.replace('auditoria.php')
    </script>";
    exit();
}
if($_SESSION['caixa'] == 0){
    echo "<script>
    alert('Caixa não foi Validado')
    window.location.replace('auditoria.php')
    </script>";
    exit();
}
if($_SESSION['noshow'] == 0){
    echo "<script>
    alert('No Show não foi Validado')
    window.location.replace('auditoria.php')
    </script>";
    exit();
}

$colaborador = $_SESSION['name'];
$data_finalizada = date('Y-m-d H:i:s');

$query = $conexao->prepare("UPDATE $dir"."_excel_auditoria_auditorias SET colaborador = '{$colaborador}', auditoria_status = 'Finalizada', data_finalizada = '{$data_finalizada}' WHERE data_auditoria = '{$data_auditoria}'");
$query->execute();

$proxima_auditoria = date('Y-m-d', strtotime("$data_auditoria +1 day"));

$query = $conexao->prepare("INSERT INTO $dir"."_excel_auditoria_auditorias (data_auditoria, auditoria_status) VALUES ('{$proxima_auditoria}', 'Pendente')");
$query->execute();

echo "<script>
    alert('Auditoria Finalizada! Clique para Imprimir a mesma')
    top.location.replace('auditoria.php')
    </script>";
    exit();

$conn_mysqli->close();

?>