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

$status_auditoria = $_SESSION['status_auditoria'];

if($status_auditoria == 'Concluida'){
    echo "<script>
    alert('Auditoria não foi Iniciada!')
    top.location.replace('index.php')
    </script>";
    exit();
}

$dados_caixa = $_SESSION['dados_caixa'];

$dados_filtrados = array_filter($dados_caixa, function($item) {
    return $item['pgto_forma'] != 'A Faturar' && $item['pgto_forma'] != 'Dinheiro' && $item['pgto_forma'] != 'Deposito';
});


// Ordenar o array por 'room_number'
usort($dados_filtrados, function($a, $b) {
    return $a['pgto_forma'] <=> $b['pgto_forma'];
});

$quantidade_dados = count($dados_filtrados);

$mastercard = 0;
$maestro = 0;
$visa = 0;
$visaelectron = 0;
$elodebito = 0;
$elocredito = 0;
$amex = 0;
$pix = 0;

foreach ($dados_filtrados as $dados) {
    if ($dados['pgto_forma'] === 'Redecard - Mastercard') {
        $mastercard += $dados['pgto_valor'];
    }
    if ($dados['pgto_forma'] === 'Redecard - Maestro') {
        $maestro += $dados['pgto_valor'];
    }
    if ($dados['pgto_forma'] === 'Redecard - Visa') {
        $visa += $dados['pgto_valor'];
    }
    if ($dados['pgto_forma'] === 'Redecard - Visa Electron') {
        $visaelectron += $dados['pgto_valor'];
    }
    if ($dados['pgto_forma'] === 'Redecard - Elo Débito') {
        $elodebito += $dados['pgto_valor'];
    }
    if ($dados['pgto_forma'] === 'Redecard - Elo Crédito') {
        $elocredito += $dados['pgto_valor'];
    }
    if ($dados['pgto_forma'] === 'Redecard - American Express') {
        $amex += $dados['pgto_valor'];
    }
    if ($dados['pgto_forma'] === 'PIX - Redecard') {
        $pix += $dados['pgto_valor'];
    }
}

$mastercard = number_format($mastercard, 2, ',', '.');
$maestro = number_format($maestro, 2, ',', '.');
$visa = number_format($visa, 2, ',', '.');
$visaelectron = number_format($visaelectron, 2, ',', '.');
$elodebito = number_format($elodebito, 2, ',', '.');
$elocredito = number_format($elocredito, 2, ',', '.');
$amex = number_format($amex, 2, ',', '.');
$pix = number_format($pix, 2, ',', '.');

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
    <title>Auditoria Digital</title>
</head>
<body>

<div class="container">

<!-- Diarias -->
<fieldset>
<legend>Caixa</legend>
<table>
<th colspan="7">Recebimentos e Pagamentos [Todos]</th>
<tr>
    <td align="center" colspan="2"><div class="botao-topo"><a href="javascript:void(0)" onclick='window.open("caixa_n_conf.php","iframe")'><button>Pendentes</button></a></div></td>
    <td align="center" colspan="3"><div class="botao-topo-chegadas"><a href="javascript:void(0)" onclick='window.open("caixa.php","iframe")'><button>Todos</button></a></div></td>
    <td align="center" colspan="2"><div class="botao-topo"><a href="javascript:void(0)" onclick='window.open("caixa_conf.php","iframe")'><button>Conferidos</button></a></div></td>
</tr>
<form action="acao.php" method="POST" id="formulario_auditoria">
<tr><td style="background-color: black" colspan="7"></td></tr>
<tr><td align="center" colspan="7">Pendentes de Conferencia: <b><?php echo $quantidade_dados ?></b></td></tr>
<tr><td style="background-color: black" colspan="7"></td></tr>
<?php

$cartoes = [
    'Mastercard' => $mastercard,
    'Maestro' => $maestro,
    'Visa' => $visa,
    'Visa Electron' => $visaelectron,
    'Elo Credito' => $elocredito,
    'Elo Debito' => $elodebito,
    'American Express' => $amex,
    'PIX' => $pix
];

foreach ($cartoes as $nomeCartao => $valorCartao) {
    if ($valorCartao != '0,00') {
        echo "<tr><td colspan=\"7\"><b>$nomeCartao:</b> R$$valorCartao</td></tr>";
    }
}

?>
<tr><td style="background-color: black" colspan="7"></td></tr>
<tr style="background-color: grey">
    <td align="center"><b>Data</b></td>
    <td align="center"><b>Apto.</b></td>
    <td align="center"><b>Hospede</b></td>
    <td align="center"><b>Forma</b></td>
    <td align="center"><b>Valor</b></td>
    <td align="center"><b>Documento</b></td>
    <td align="center"><b>Conferido</b></td>
</tr>


<?php
$qtd = 0;
$quantidade = 0;
foreach ($dados_filtrados as $select) {
    $id = $select['id'];
    $reserva = $select['reserva'];
    $guest_name = $select['guest_name'];
    $data_lancamento = $select['data_lancamento'];
    $pgto_forma = $select['pgto_forma'];
    $pgto_valor = $select['pgto_valor'];
    $room_number = $select['room_number'];
    $documento = $select['documento'];
    $auditoria_forma = $select['auditoria_forma'];
    $auditoria_conferido = $select['auditoria_conferido'];

    $qtd++;
    $quantidade++;

    if($qtd % 2 == 0){
    $cor_tr = 'darkgrey';
    }else{
    $cor_tr = 'white';  
    }
    ?>
<tr style="background-color: <?php echo $cor_tr; ?>">
    <td align="center"><?php echo date('d/m/Y', strtotime("$data_lancamento")); ?></td>
    <td align="center"><?php echo $room_number; ?></td>
    <td><?php echo $guest_name; ?></td>
    <td><?php echo $pgto_forma; ?></td>
    <td>R$<?php echo number_format($pgto_valor, 2, ',', '.'); ?></td>
    <td><?php echo $documento; ?></td>
    <td align="center"><input type="checkbox" name="comentarios_<?php echo $quantidade ?>" value="Sim" <?php if($auditoria_conferido == 'Sim') echo 'checked'; ?>></td>
</tr>
<input type="hidden" name="id_<?php echo $quantidade ?>" value="<?php echo $id ?>">
<?php } ?>
</table>
<br><br>
<input type="hidden" name="quantidade" value="<?php echo $quantidade_dados ?>">
<input type="hidden" name="id_job" value="caixa">
<input type="submit" class="submit" value="Validar Dados">
</form>
</fieldset>
</div>
</body>
</html>
