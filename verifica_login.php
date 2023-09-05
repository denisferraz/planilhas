<?php

ini_set('display_errors', 0 );
error_reporting(0);

if ( $_SERVER['REQUEST_METHOD']=='GET' && realpath(__FILE__) == realpath( $_SERVER['SCRIPT_FILENAME'] ) ) {
    echo "<script>
    alert('Você não tem permissão para acessar esta pagina.')
    window.location.replace('error.html')
    </script>";
    exit();
 }

session_start();

if(!$_SESSION['username']){
    header('Location: error.html');
    exit();
}

?>