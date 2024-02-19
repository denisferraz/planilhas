<?php
session_start();

require('../conexao.php');
require('../verifica_login.php');

echo "<script>
    window.location.replace('../painel.php')
    </script>";
    exit();

?>
