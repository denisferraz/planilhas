<?php

if ( $_SERVER['REQUEST_METHOD']=='GET' && realpath(__FILE__) == realpath( $_SERVER['SCRIPT_FILENAME'] ) ) {
    echo "<script>
    window.location.replace('index.php')
    </script>";
    exit();    
 }

date_default_timezone_set('America/Sao_Paulo');

// Chave de criptografia
$chave = 'Accor@123';
$metodo = 'AES-256-CBC';
//$iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($metodo));
$iv = '8246508246508246';

//Local de Configuração
$local_configuracao = 'Casa';

//Configuração DB
$config_host = 'localhost';

if($local_configuracao == 'Casa'){
$config_db = 'app_denisferraz';
$config_user = 'root';
$config_password = '';
}else{
$config_db = 'u661915792_app_checkos';
$config_user = 'u661915792_app_checkos';
$config_password = 'Much@ch0';
}

$hoje = date('Y-m-d');

$config_dsn = "mysql:host=$config_host;dbname=$config_db";
try {
    $conexao = new PDO($config_dsn, $config_user, $config_password);
    $conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    echo $e->getMessage();
}

define('Host', "$config_host");
define('Usuario', "$config_user");
define('Senha', "$config_password");
define('DB', "$config_db");
$conn_mysqli = mysqli_connect(Host, Usuario, Senha, DB) or die ('Não foi possivel conectar');

    $config_empresa = 'Denis Ferraz - Jogo 22';
    $config_email = 'contato@denisferraz.com.br';
    $config_telefone = '71992604877';
?>
