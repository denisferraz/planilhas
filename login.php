<?php
session_start();

require('conexao.php');

if ( $_SERVER['REQUEST_METHOD']=='GET' && realpath(__FILE__) == realpath( $_SERVER['SCRIPT_FILENAME'] ) ) {
    echo "<script>
    alert('Você não tem permissão para acessar esta pagina.')
    window.location.replace('index.html')
    </script>";
    exit();
 }

if(empty($_POST['login']) || empty($_POST['senha'])){
    header('Location: index.html');
    exit();
}

$id_job = mysqli_real_escape_string($conn_mysqli, $_POST['id_job']);

$username = mysqli_real_escape_string($conn_mysqli, $_POST['login']);
$senha = mysqli_real_escape_string($conn_mysqli, $_POST['senha']);
$crip_senha = md5($senha);

if($id_job == 'login'){


$query = $conexao->prepare("SELECT * FROM excel_users WHERE username = :username AND userpassword = :senha AND userstatus = 'Ativo'");
$query->execute(array('username' => $username, 'senha' => $crip_senha));
$row = $query->rowCount();

if($row == 1){

    while($select = $query->fetch(PDO::FETCH_ASSOC)){
        $nome = $select['nome'];
        $hotel = explode(";", $select['hotel']);;
        $hierarquia = $select['hierarquia'];
    }

    if (is_array($hotel) && !empty($hotel)) {
        $primeiroHotel = $hotel[0];
    }else {
        $primeiroHotel = $hotel;
    }

$query = $conexao->prepare("SELECT * FROM excel_hotels WHERE hotel_rid = :hotel_rid");
$query->execute(array('hotel_rid' => $primeiroHotel));
while($select = $query->fetch(PDO::FETCH_ASSOC)){
    $hotel_name = $select['hotel_name'];
}

    $_SESSION['username'] = $username;
    $_SESSION['name'] = $nome;
    $_SESSION['hotels'] = $hotel;
    $_SESSION['hotel'] = $primeiroHotel;
    $_SESSION['hotel_name'] = $hotel_name;
    $_SESSION['hierarquia'] = $hierarquia;
    $_SESSION['entrada'] = '0';
    $_SESSION['status_auditoria'] = 'Concluida';
    $_SESSION['status_plano'] = 'Concluido';

    header('Location: painel.php');
    exit();
}else{
    echo "<script>
    alert('Dados Invalidos!')
    window.location.replace('index.html')
    </script>";
    exit();
}

}else if($id_job == 'alterar_senha'){

    $senha_nova = mysqli_real_escape_string($conn_mysqli, $_POST['senha_nova']);

    $query = $conexao->prepare("SELECT * FROM excel_users WHERE username = :username AND userpassword = :senha AND userstatus = 'Ativo'");
    $query->execute(array('username' => $username, 'senha' => $crip_senha));
    $row = $query->rowCount();

if($row == 1){

    $crip_senha = md5($senha_nova);

    $query = $conexao->prepare("UPDATE excel_users SET userpassword = :senha WHERE username = :username AND userstatus = 'Ativo'");
    $query->execute(array('username' => $username, 'senha' => $crip_senha));

    echo "<script>
    alert('Senha alterada com Sucesso')
    window.location.replace('painel.php')
    </script>";
    exit();

}else{
    echo "<script>
    alert('Senha antiga invalida!')
    window.location.replace('profile.php')
    </script>";
    exit();
}

}else if($id_job == 'profile_novo'){

$query = $conexao->prepare("SELECT * FROM excel_users WHERE username = :username");
$query->execute(array('username' => $username));
$row = $query->rowCount();

if($row == 1){

    echo "<script>
    alert('Este Login ja Existe!')
    window.location.replace('painel.php')
    </script>";
    exit();

}else{

    $nome = mysqli_real_escape_string($conn_mysqli, $_POST['nome']);
    $hoteisSelecionados = $_POST['hotel'];
    $hierarquia = mysqli_real_escape_string($conn_mysqli, $_POST['hierarquia']);

    $hotels = '';
    foreach ($hoteisSelecionados as $hotel) {
        $hotels .= $hotel.';';
    }

    $hotels = substr($hotels, 0, -1);

    $query = $conexao->prepare("INSERT INTO excel_users (username, userpassword, nome, hotel, hierarquia, userstatus) VALUES (:username, :userpassword, :nome, :hotel, :hierarquia, 'Ativo')");
    $query->execute(array('username' => $username, 'userpassword' => $crip_senha, 'nome' => $nome, 'hotel' => $hotels, 'hierarquia' => $hierarquia));

    echo "<script>
    alert('Login $username Cadastrado com Sucesso')
    window.location.replace('painel.php')
    </script>";
    exit();
}

}else if($id_job == 'profile_deletar'){

    $id = mysqli_real_escape_string($conn_mysqli, $_POST['id']);

    $query = $conexao->prepare("DELETE FROM excel_users WHERE id = :id AND username = :username");
    $query->execute(array('username' => $username, 'id' => $id));

    echo "<script>
    alert('Login $username Deletado com Sucesso')
    window.location.replace('profiles.php')
    </script>";
    exit();

}else if($id_job == 'profile_editar'){

    $id = mysqli_real_escape_string($conn_mysqli, $_POST['id']);
    $nome = mysqli_real_escape_string($conn_mysqli, $_POST['nome']);
    $hierarquia = mysqli_real_escape_string($conn_mysqli, $_POST['hierarquia']);
    $senha_confirmar = mysqli_real_escape_string($conn_mysqli, $_POST['senha_confirmar']);
    $hoteisSelecionados = $_POST['hotel'];

    $hotels = '';
    foreach ($hoteisSelecionados as $hotel) {
        $hotels .= $hotel.';';
    }

    $hotels = substr($hotels, 0, -1);

    if($senha_confirmar == 'Senha'){
    $query = $conexao->prepare("UPDATE excel_users SET nome = :nome, hierarquia = :hierarquia, hotel = :hotel WHERE id = :id");
    $query->execute(array('nome' => $nome, 'hierarquia' => $hierarquia,  'hotel' => $hotels, 'id' => $id));
    }else{
    $query = $conexao->prepare("UPDATE excel_users SET userpassword = :userpassword, nome = :nome, hierarquia = :hierarquia, hotel = :hotel WHERE id = :id");
    $query->execute(array('userpassword' => $crip_senha, 'nome' => $nome, 'hierarquia' => $hierarquia, 'hotel' => $hotels));
    }

    echo "<script>
    alert('Login $username Alterado com Sucesso')
    window.location.replace('profiles.php')
    </script>";
    exit();

}