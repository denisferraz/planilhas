<?php
session_start();

require('conexao.php');
require('verifica_login.php');

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
error_reporting(0);

$id_acao = explode(',', base64_decode(mysqli_real_escape_string($conn_mysqli, $_GET['id'])));

$id_job = $id_acao[0];

$query = $conexao->prepare("SELECT * FROM excel_hotels WHERE id > 0");
$query->execute();
$query_qtd = $query->rowCount();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Planilhas Hotelaria</title>
    <link rel="icon" type="image/x-icon" href="images/favicon.ico">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php
$id = base64_encode('Ver,123');
?>
<span class="card-group-right"><a href="logout.php"><button>Clique aqui para Sair</button></a></span>
<span class="card-group-right"><a href="configuracoes.php?id=<?php echo $id; ?>"><button>Configurações</button></a></span>
<span class="card-group-left"><a href="painel.php"><button>Voltar</button></a></span>
<?php
$id = base64_encode('Cadastrar,123');
?>
<span class="card-group-left"><a href="configuracoes.php?id=<?php echo $id; ?>"><button>Cadastrar Novo Hotel</button></a></span>
<?php 
if($id_job == 'Ver'){
?>
            <div class="card">
            <div class="card-top">
                <h2 class="title">Bem Vindo(a) <?php echo $_SESSION['name']; ?></h2>
            </div>
            <center>
            <div class="card-group">
              <br>
              <table>
                <tr>
                    <td><b>RID</b></td>
                    <td><b>Hotel</b></td>
                    <td><b>Status</b></td>
                    <td><b>Validade</b></td>
                    <td><b>Deletar</b></td>
                </tr>
              <?php
                while($select = $query->fetch(PDO::FETCH_ASSOC)){
                $id = $select['id'];
                $hotel_rid = $select['hotel_rid'];
                $hotel_name = $select['hotel_name'];
                $hotel_status = $select['hotel_status'];
                $hotel_validade = $select['hotel_validade'];

                $id_del = base64_encode('Deletar,'.$id.','.$hotel_rid.','.$hotel_name);
                $id_edit = base64_encode('Editar,'.$id.','.$hotel_rid.','.$hotel_name.','.$hotel_status.','.$hotel_validade);
              ?>

                <tr>
                    <td><a href="configuracoes.php?id=<?php echo $id_edit; ?>"><button><?php echo ucfirst($hotel_rid); ?></button></a></td>
                    <td><?php echo $hotel_name; ?></td>
                    <td><?php echo $hotel_status; ?></td>
                    <td><?php echo date('d/m/Y', strtotime("$hotel_validade")); ?></td>
                    <td><a href="configuracoes.php?id=<?php echo $id_del; ?>"><button>Deletar</button></a></td>
                </tr>
                <tr>
                    <td colspan="5"><hr></td>
                </tr>

              <?php } ?>

              </table>
              </div>
            </center>
        </div>
<?php 
}else if($id_job == 'Cadastrar'){
$id_criar = base64_encode('Criar,123');
?>
<form class="form" action="configuracoes.php?id=<?php echo $id_criar; ?>" method="POST">
            <div class="card">
            <div class="card-top">
                <h2 class="title">Cadastrar Novo Hotel</h2>
            </div>
            <div class="card-group">
              <br>
                <label>RID</label>
                <input type="text" minlength="4" maxlength="5" name="hotel_rid" placeholder="H0000" required>
                <label>Nome Hotel</label>
                <input type="text" minlength="4" maxlength="100" name="hotel_name" placeholder="Hotel" required>
                <label>Validade</label>
                <input type="date" min="<?php echo $hoje; ?>" name="hotel_validade" required>
                <div class="card-group btn"><button type="submit">Cadastrar</button></div>
                </div>
        </div>
</form>
<?php 
}else if($id_job == 'Criar'){

  $hotel_rid = mysqli_real_escape_string($conn_mysqli, $_POST['hotel_rid']);
  $hotel_name = mysqli_real_escape_string($conn_mysqli, $_POST['hotel_name']);
  $hotel_validade = mysqli_real_escape_string($conn_mysqli, $_POST['hotel_validade']);

  $query = $conexao->prepare("SELECT * FROM excel_hotels WHERE hotel_rid = :hotel_rid OR hotel_name = :hotel_name");
  $query->execute(array('hotel_rid' => $hotel_rid, 'hotel_name' => $hotel_name));
  $query_count = $query->rowCount();

  if($query_count >= 1){

    echo "<script>
    alert('Hotel $hotel_name Ja Cadastrado!')
    window.location.replace('configuracoes.php?id=$id')
    </script>";
    exit();

  }else{

  $query = $conexao->prepare("SELECT * FROM excel_users WHERE username = :username");
  $query->execute(array('username' => $_SESSION['username']));
  while($select = $query->fetch(PDO::FETCH_ASSOC)){
      $hotel = $select['hotel'];
  }

  $hotel = $hotel.';'.strtolower($hotel_rid);
  $query = $conexao->prepare("UPDATE excel_users SET hotel = :hotel WHERE hierarquia = :hierarquia");
  $query->execute(array('hierarquia' => 'Administrador', 'hotel' => $hotel));

  $query = $conexao->prepare("INSERT INTO excel_hotels (hotel_rid, hotel_name, hotel_status, hotel_validade) VALUES (:hotel_rid, :hotel_name, :hotel_status, :hotel_validade)");
  $query->execute(array('hotel_rid' => $hotel_rid, 'hotel_name' => $hotel_name, 'hotel_status' => 'Ativo', 'hotel_validade' => $hotel_validade));

//Criar Tabelas

//Arrivals
$tabela_nome = "$hotel_rid"."_excel_gestaorecepcao_arrivals";
$query_create = $conexao->prepare("CREATE TABLE `$tabela_nome` (
    `id` int(11) NOT NULL,
    `data_importacao` datetime NOT NULL DEFAULT current_timestamp(),
    `dados_arrivals` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;");
$query_create->execute();

//Cashier
$tabela_nome = "$hotel_rid"."_excel_gestaorecepcao_cashier";
$query_create = $conexao->prepare("CREATE TABLE `$tabela_nome` (
  `id` int(11) NOT NULL,
  `username` varchar(35) NOT NULL,
  `tipo_lancamento` varchar(35) NOT NULL,
  `pagamento_tipo` varchar(55) NOT NULL,
  `pagamento_valor` varchar(55) NOT NULL,
  `reserva_id` int(11) NOT NULL,
  `origem` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;");
$query_create->execute();

//In House
$tabela_nome = "$hotel_rid"."_excel_gestaorecepcao_inhouse";
$query_create = $conexao->prepare("CREATE TABLE `$tabela_nome` (
  `id` int(11) NOT NULL,
  `data_importacao` datetime NOT NULL DEFAULT current_timestamp(),
  `dados_presentlist` mediumtext NOT NULL,
  `reserva_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;");
$query_create->execute();

//Room Status
$tabela_nome = "$hotel_rid"."_excel_gestaorecepcao_roomstatus";
$query_create = $conexao->prepare("CREATE TABLE `$tabela_nome` (
  `id` int(11) NOT NULL,
  `data_importacao` datetime NOT NULL DEFAULT current_timestamp(),
  `room_number` mediumtext NOT NULL,
  `room_status` mediumtext NOT NULL,
  `room_type` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;");
$query_create->execute();

//Room Types
$tabela_nome = "$hotel_rid"."_excel_gestaorecepcao_roomtypes";
$query_create = $conexao->prepare("CREATE TABLE `$tabela_nome` (
  `id` int(11) NOT NULL,
  `room_type` varchar(50) NOT NULL,
  `room_type_qtd` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
$query_create->execute();


//Criar Pastas
function recursiveCopy($source, $dest) {
  if (is_dir($source)) {
      if (!is_dir($dest)) {
          mkdir($dest, 0777, true);
      }
      $files = scandir($source);
      foreach ($files as $file) {
          if ($file != "." && $file != "..") {
              recursiveCopy("$source/$file", "$dest/$file");
          }
      }
  } else {
      copy($source, $dest);
  }
}

$diretorio = str_replace('\\', '/', __DIR__);
$rid = strtolower($hotel_rid);

$pastas = ['gestao', 'comissao', 'auditoria', 'all'];

foreach($pastas as $pasta){
$sourceDir = $diretorio . '/'.$pasta.'/h8181';
$destDir = $diretorio . '/'.$pasta.'/' . $rid;

// Copiar o diretório e seu conteúdo
recursiveCopy($sourceDir, $destDir);
}

  $id = base64_encode('Ver,123');

  echo "<script>
    alert('Hotel $hotel_name Cadastrado com Sucesso!')
    window.location.replace('configuracoes.php?id=$id')
    </script>";
    exit();

  }

}else if($id_job == 'Deletar'){

  $id_hotel = $id_acao[1];
  $hotel_rid = $id_acao[2];
  $hotel_name = $id_acao[3];

  $query = $conexao->prepare("DELETE FROM excel_hotels WHERE id = :id AND hotel_rid = :hotel_rid AND hotel_name = :hotel_name");
  $query->execute(array('id' => $id_hotel, 'hotel_rid' => $hotel_rid, 'hotel_name' => $hotel_name));

// Deletar Tabelas
$prefixo_tabela = "$hotel_rid"."_excel_gestaorecepcao";

$tabelas = ["arrivals", "cashier", "inhouse", "roomstatus", "roomtypes"];

foreach ($tabelas as $tabela) {
    $tabela_nome = $prefixo_tabela . "_" . $tabela;
    $query_delete = $conexao->prepare("DROP TABLE `$tabela_nome`");
    $query_delete->execute();
}

//Remover da Lista o RID
$query = $conexao->prepare("SELECT * FROM excel_users WHERE username = :username");
$query->execute(array('username' => $_SESSION['username']));

$hotels = '';
while ($select = $query->fetch(PDO::FETCH_ASSOC)) {
    $hoteis = $select['hotel'];
}

$rid = strtolower($hotel_rid);
$hoteis = explode(';', $hoteis);

$hoteis = array_filter($hoteis, function ($item) use ($rid) {
    return $item !== $rid;
});

$hotels = implode(';', $hoteis);

$query = $conexao->prepare("UPDATE excel_users SET hotel = :hotel WHERE hierarquia = :hierarquia");
$query->execute(array('hierarquia' => 'Administrador', 'hotel' => $hotels));

//Deletar Pastas
function recursiveDelete($dir) {
  if (is_dir($dir)) {
      $files = array_diff(scandir($dir), array('.', '..'));
      foreach ($files as $file) {
          $path = $dir . '/' . $file;
          is_dir($path) ? recursiveDelete($path) : unlink($path);
      }
      rmdir($dir);
  }
}

$diretorio = str_replace('\\', '/', __DIR__);

$pastas = ['gestao', 'comissao', 'auditoria', 'all'];

foreach ($pastas as $pasta) {
    $dirToDelete = $diretorio . '/' . $pasta . '/' . $rid;

    recursiveDelete($dirToDelete);
}

  $id = base64_encode('Ver,123');
  
  echo "<script>
    alert('Hotel [$hotel_rid] $hotel_name Excluido com Sucesso!')
    window.location.replace('configuracoes.php?id=$id')
    </script>";
    exit();

}else if($id_job == 'Editar'){
$id_editar = base64_encode('Editado,123');

  $id_hotel = $id_acao[1];
  $hotel_rid = $id_acao[2];
  $hotel_name = $id_acao[3];
  $hotel_validade = $id_acao[5];

  ?>
  <form class="form" action="configuracoes.php?id=<?php echo $id_editar; ?>" method="POST">
              <div class="card">
              <div class="card-top">
                  <h2 class="title">Alterar Hotel<br><br><?php echo $hotel_name; ?></h2>
              </div>
              <div class="card-group">
                <br>
                  <label>RID: <?php echo ucfirst($hotel_rid); ?></label><br>
                  <input type="hidden" name="hotel_rid" value="<?php echo $hotel_rid; ?>" required>
                  <label>Nome Hotel</label>
                  <input type="text" minlength="4" maxlength="100" name="hotel_name" placeholder="Hotel" value="<?php echo $hotel_name; ?>" required>
                  <label>Validade</label>
                  <input type="date" min="<?php echo $hoje; ?>" name="hotel_validade" value="<?php echo $hotel_validade; ?>" required><br>
                  <label>Status</label>
                  <select name="hotel_status">
                    <option value="Ativo">Ativo</option>
                    <option value="Inativo">Inativo</option>
                  </select><br><br>
                  <div class="card-group btn"><button type="submit">Alterar</button></div>
                  </div>
          </div>
  </form>
  <?php 
}else if($id_job == 'Editado'){

  $hotel_rid = mysqli_real_escape_string($conn_mysqli, $_POST['hotel_rid']);
  $hotel_name = mysqli_real_escape_string($conn_mysqli, $_POST['hotel_name']);
  $hotel_validade = mysqli_real_escape_string($conn_mysqli, $_POST['hotel_validade']);
  $hotel_status = mysqli_real_escape_string($conn_mysqli, $_POST['hotel_status']);

  $query = $conexao->prepare("UPDATE excel_hotels SET hotel_name = :hotel_name, hotel_validade = :hotel_validade, hotel_status = :hotel_status WHERE hotel_rid = :hotel_rid");
  $query->execute(array('hotel_rid' => $hotel_rid, 'hotel_name' => $hotel_name, 'hotel_validade' => $hotel_validade, 'hotel_status' => $hotel_status));

  $id = base64_encode('Ver,123');
  
  echo "<script>
    alert('Hotel $hotel_name Editado com Sucesso!')
    window.location.replace('configuracoes.php?id=$id')
    </script>";
    exit();

}
?>
</body>
</html>