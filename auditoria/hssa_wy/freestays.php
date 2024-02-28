<?php

session_start();

require('../../conexao.php');
require('../../verifica_login.php');

$dir = substr(__DIR__, -7);

if($dir != $_SESSION['hotel']){
    echo "<script>
    alert('Você não tem permissão para acessar esta pagina!')
    top.location.replace('../../index.html')
    </script>";
    exit();
}

error_reporting(0);

$id = mysqli_real_escape_string($conn_mysqli, $_GET['id']);

$data_auditoria = date('Y-m-d', $id);

$mes = date('m', strtotime($data_auditoria));

$chave = $_SESSION['hotel'].$chave;

//$_SESSION['dados_presentlist']
$query = $conexao->prepare("SELECT * FROM {$dir}_excel_auditoria WHERE MONTH(data_auditoria) = :mes");
$query->bindParam(':mes', $mes, PDO::PARAM_INT);
$query->execute();

$dados_presentlist = [];
while($select = $query->fetch(PDO::FETCH_ASSOC)){
    $dados_auditoria = $select['dados_auditoria'];
    $id = $select['id'];

// Para descriptografar os dados
$dados = base64_decode($dados_auditoria);
$dados_decifrados = openssl_decrypt($dados, $metodo, $chave, 0, $iv);

$dados_array = explode(';', $dados_decifrados);

if($dados_array[0] == 'inhouse'){
$dados_presentlist[] = [
  'id' => $id,
  'reserva' => $dados_array[2],
  'room_number' => $dados_array[3],
  'guest_name' => $dados_array[4],
  'checkin' => $dados_array[5],
  'checkout' => $dados_array[6],
  'room_rate' => $dados_array[7],
  'comentario_checkins' => $dados_array[8],
  'comentario_freestay' => $dados_array[9],
  'auditoria_diarias' => $dados_array[10],
  'auditoria_garantia' => $dados_array[11]
];
}}

$reservas = array_filter($dados_presentlist, function($item) {
    return $item['room_rate'] == 0;
});

$dados_filtrados = [];

foreach ($reservas as $select) {
    $reserva = $select['reserva'];
    
    // Verificar se já existe uma entrada para essa reserva
    if (!isset($dados_filtrados[$reserva])) {
        // Se não existir, adiciona a entrada
        $dados_filtrados[$reserva] = $select;
    }
}

// Ordenar o array por 'room_number'
usort($dados_filtrados, function($a, $b) {
    return $a['room_number'] <=> $b['room_number'];
});

$quantidade_dados = count($dados_filtrados);

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../../images/favicon.ico">
    <link rel="shortcut icon" href="../../images/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="../../css/style_tabela.css">
    <title>Free Stays</title>
</head>
<body>

<div class="container">
<!-- Diarias -->
<fieldset>
<legend>Free Stays</legend>
<form action="acao.php" method="POST" id="formulario_auditoria">
<table>
<th colspan="8">Cortesias e Uso da Casa</th>
<tr><td style="background-color: black" colspan="8"></td></tr>
<tr><td align="center" colspan="8">Periodo de Conferencia: <b><?php $mes_completo = (new DateTime($data_auditoria))->format('F Y'); echo $mes_completo; ?></b></td>
<tr><td align="center" colspan="8">Quantidade Total: <b><?php echo $quantidade_dados; ?></b></td>
<tr><td style="background-color: black" colspan="8"></td></tr>
<tr style="background-color: grey">
    <td align="center"><b>Qtd</b></td>
    <td align="center"><b>Reserva</b></td>
    <td align="center"><b>Apto.</b></td>
    <td align="center"><b>Hospede</b></td>
    <td align="center"><b>Checkin</b></td>
    <td align="center"><b>Checkout</b></td>
    <td align="center"><b>Comentario</b></td>
    <td align="center"><b>Documento</b></td>
</tr>


<?php
$qtd = 0;
$quantidade = 0;
foreach ($dados_filtrados as $select) {
    $id = $select['id'];
    $guest_name = $select['guest_name'];
    $reserva = $select['reserva'];
    $checkin = $select['checkin'];
    $checkout = $select['checkout'];
    $room_number = $select['room_number'];
    $comentario = $select['comentario_freestay'];

    $qtd++;
    $quantidade++;

    if($qtd % 2 == 0){
    $cor_tr = 'darkgrey';
    }else{
    $cor_tr = 'white';  
    }
    ?>
<tr style="background-color: <?php echo $cor_tr; ?>">
    <td align="center"><b><?php echo $qtd; ?></b></td>
    <td align="center"><?php echo $reserva; ?></td>
    <td align="center"><?php echo $room_number; ?></td>
    <td><?php echo $guest_name; ?></td>
    <td align="center"><?php echo date('d/m/Y', strtotime("$checkin")); ?></td>
    <td align="center"><?php echo date('d/m/Y', strtotime("$checkout")); ?></td>
    <td><input class="input-field" type="text" name="comentarios_<?php echo $quantidade ?>" value="<?php echo $comentario ?>"></td>
    <td align="center">
        
        <!-- ARQUIVOS !-->
        <?php
        $diretorio = 'arquivos/'.$reserva;
        $files = glob($diretorio . '.pdf');
        $numFiles = count($files);

        if($numFiles < 1){

            ?>
            <a href="javascript:void(0)" onclick='window.open("arquivos.php?id=<?php echo $reserva ?>","iframe-home"); return false'><button class="botao-topo">Enviar PDF</button></a>
            <?php

        }else{

            foreach ($files as $file) {
                $fileName = basename($file);
                echo '<a href="javascript:void(0);" onclick="window.open(\'' . $file . '\',\'_blank\'); return false"><button class="botao">Ver PDF</button></a>';
            }

        }
        ?>

    </td>
</tr>
<input type="hidden" name="id_<?php echo $quantidade ?>" value="<?php echo $id ?>">
<?php } ?>
</table>
<br><br>
<input type="hidden" name="quantidade" value="<?php echo $quantidade_dados ?>">
<input type="hidden" name="data_auditoria" value="<?php echo strtotime("$data_auditoria") ?>">
<input type="hidden" name="id_job" value="freestays">
<input type="submit" class="submit" value="Validar Dados">
</form>
</fieldset>
</div>
</body>
</html>
