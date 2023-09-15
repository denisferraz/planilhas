<?php
session_start();

require('conexao.php');
require('verifica_login.php');

$id = explode(',', base64_decode(mysqli_real_escape_string($conn_mysqli, $_GET['id'])));

$id_job = $id[0];
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <title>Planilhas Hotelaria</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<span class="card-group-right"><a href="logout.php"><button>Clique aqui para Sair</button></a></span>
<span class="card-group-left"><a href="painel.php"><button>Voltar</button></a></span>
<div class="card">
<form class="form" action="login.php" method="POST">
<?php 
if($id_job == 'Senha'){
?>

            <div class="card-top">
                <h2 class="title">Bem Vindo(a) <?php echo $_SESSION['name']; ?></h2>
                <p>Para alterar sua senha, preencha abaixo</p><br>
            </div>

            <div class="card-group">
                <label>Senha Antiga</label>
                <input type="password" minlength="4" maxlength="30" name="senha" placeholder="Senha Antiga" required>
                <label>Nova Senha</label>
                <input type="password" minlength="4" maxlength="30" name="senha_nova" placeholder="Senha Nova" required>
                <label>Confirmar Nova Senha</label>
                <input type="password" minlength="4" maxlength="30" name="senha_nova_confirmar" placeholder="Confirmar Senha Nova" required>
                <input type="hidden" name="login" value="<?php echo $_SESSION['username']; ?>" required>
                <input type="hidden" name="id_job" value="alterar_senha">
                <div class="card-group btn"><button type="submit">Alterar</button></div>
            </div>
<?php 
}else if($id_job == 'Novo'){
?>
            <div class="card-top">
                <h2 class="title">Bem Vindo(a) <?php echo $_SESSION['name']; ?></h2>
                <p>Para criar uma conta, preencha abaixo</p><br>
            </div>

            <div class="card-group">
                <label>Login</label>
                <input type="text" minlength="6" maxlength="35" name="login" placeholder="Login" required>
                <label>Senha</label>
                <input type="password" minlength="4" maxlength="30" name="senha" placeholder="Senha" required>
                <label>Confirmar Nova Senha</label>
                <input type="password" minlength="4" maxlength="30" name="senha_confirmar" placeholder="Confirmar Senha" required>
                <label>Nome</label>
                <input type="text" minlength="6" maxlength="35" name="nome" placeholder="Nome Completo" required>
                <label id="hierarquia">Hierarquia</label>
                <select name="hierarquia" id="hierarquia">
                    <option value="Colaborador">Colaborador</option>
                <?php
                if($_SESSION['hierarquia'] == 'Coordenador' || $_SESSION['hierarquia'] == 'Gerente' || $_SESSION['hierarquia'] == 'Administrador'){
                ?>
                    <option value="Supervisor">Supervisor</option>
                <?php
                } if($_SESSION['hierarquia'] == 'Gerente' || $_SESSION['hierarquia'] == 'Administrador'){
                ?>
                    <option value="Coordenador">Coordenador</option>
                <?php
                } if($_SESSION['hierarquia'] == 'Administrador'){
                ?>
                    <option value="Gerente">Gerente</option>
                <?php } ?>
                </select><br><br>
                <label id="hotels">Hoteis</label><br>
                <?php
                    foreach ($_SESSION['hotels'] as $valor) {
                        $query = $conexao->prepare("SELECT * FROM excel_hotels WHERE hotel_rid = :hotel_rid");
                        $query->execute(array('hotel_rid' => $valor));
                        while($select = $query->fetch(PDO::FETCH_ASSOC)){
                            $hotel_name = $select['hotel_name'];
                        }
                        
                        echo '<label>';
                        echo '<input type="checkbox" name="hotel[]" value="' . $valor . '"checked>';
                        echo $hotel_name;
                        echo '</label><br>';
                    }
                    ?>
                    <br>
                <input type="hidden" name="id_job" value="profile_novo">
                <div class="card-group btn"><button type="submit">Cadastrar</button></div>
            </div>
<?php 
}else if($id_job == 'Deletar'){
?>

<div class="card-top">
                <h2 class="title">Bem Vindo(a) <?php echo $_SESSION['name']; ?></h2>
                <p>Confirme abaixo para Deletar o Usuario <?php echo $id[3]; ?></p><br>
            </div>

            <div class="card-group">
                <label><?php echo $id[2]; ?> [ <?php echo $id[3]; ?> ]</label><br>
                <input type="hidden" name="login" value="<?php echo $id[3]; ?>">
                <input type="hidden" name="senha" value="senha">
                <input type="hidden" name="id" value="<?php echo $id[1]; ?>">
                <input type="hidden" name="id_job" value="profile_deletar">
                <div class="card-group btn"><button type="submit">Deletar</button></div>
            </div>

<?php 
}else if($id_job == 'Editar'){
?>

<div class="card-top">
                <h2 class="title">Bem Vindo(a) <?php echo $_SESSION['name']; ?></h2>
                <p>Altere os Dados do Usuario Abaixo</p><br>
            </div>

            <div class="card-group">

    <div class="change-password">
        <input type="checkbox" id="changePassword" name="change_password">
        <label for="changePassword">Selecione para Alterar Senha</label>
    </div><br>

    <div class="password-fields" style="display: none;">
        <label>Senha</label>
        <input type="password" minlength="4" maxlength="30" name="senha" placeholder="Senha" value="Senha">

        <label>Confirmar Nova Senha</label>
        <input type="password" minlength="4" maxlength="30" name="senha_confirmar" placeholder="Confirmar Senha" value="Senha">
    </div>

    <label>[<?php echo $id[3]; ?>] Nome</label>
    <input type="text" minlength="6" maxlength="35" name="nome" value="<?php echo $id[2]; ?>" placeholder="Nome Completo" required>
<br>
    <label for="hierarquia">Hierarquia</label>
    <select name="hierarquia" id="hierarquia">
        <option value="Colaborador" <?php echo ($id[4] === 'Colaborador') ? 'selected' : ''; ?>>Colaborador</option>
        <?php
        if($_SESSION['hierarquia'] == 'Coordenador' || $_SESSION['hierarquia'] == 'Gerente' || $_SESSION['hierarquia'] == 'Administrador'){
        ?>
        <option value="Supervisor" <?php echo ($id[4] === 'Supervisor') ? 'selected' : ''; ?>>Supervisor</option>
        <?php
        } if($_SESSION['hierarquia'] == 'Gerente' || $_SESSION['hierarquia'] == 'Administrador'){
        ?>
        <option value="Coordenador" <?php echo ($id[4] === 'Coordenador') ? 'selected' : ''; ?>>Coordenador</option>
        <?php
        } if($_SESSION['hierarquia'] == 'Administrador'){
        ?>
        <option value="Gerente" <?php echo ($id[4] === 'Gerente') ? 'selected' : ''; ?>>Gerente</option>
        <?php } ?>
    </select><br><br>
    <label id="hotels">Hoteis</label><br>
                <?php
                    $hotels = explode(';', $id[5]);
                    foreach ($_SESSION['hotels'] as $valor) {
                        $query = $conexao->prepare("SELECT * FROM excel_hotels WHERE hotel_rid = :hotel_rid");
                        $query->execute(array('hotel_rid' => $valor));
                        while($select = $query->fetch(PDO::FETCH_ASSOC)){
                            $hotel_name = $select['hotel_name'];
                        }
                        
                        $checked = (in_array($valor, $hotels) ? 'checked' : '');

                        
                        echo '<label>';
                        echo '<input type="checkbox" name="hotel[]" value="' . $valor . '" ' . $checked . '>';
                        echo $hotel_name;
                        echo '</label><br>';
                    }
                    ?>
                    <br>

    <input type="hidden" name="id_job" value="profile_editar">
    <input type="hidden" name="id" value="<?php echo $id[1]; ?>">
    <input type="hidden" name="login" value="<?php echo $id[3]; ?>">
    <div class="card-group btn"><button type="submit">Cadastrar</button></div>
    </div>

<script>
    const changePasswordCheckbox = document.getElementById('changePassword');
    const passwordFields = document.querySelector('.password-fields');
    const senhaInput = document.querySelector('input[name="senha"]');
    const senhaConfirmarInput = document.querySelector('input[name="senha_confirmar"]');

        changePasswordCheckbox.addEventListener('change', function () {
        passwordFields.style.display = this.checked ? 'block' : 'none';

        if (this.checked) {
            senhaInput.setAttribute('required', 'required');
            senhaConfirmarInput.setAttribute('required', 'required');
        } else {
            senhaInput.removeAttribute('required');
            senhaConfirmarInput.removeAttribute('required');
        }
    });
</script>
<?php  } ?>
</form>
</div>
<script>
    document.querySelector('.form').addEventListener('submit', function(event) {
    var id_jobs = document.querySelector('input[name="id_job"]').value;

    if(id_jobs == 'alterar_senha'){
    var novaSenha = document.querySelector('input[name="senha_nova"]').value;
    var confirmarSenha = document.querySelector('input[name="senha_nova_confirmar"]').value;
    }else if(id_jobs == 'profile_novo' || id_jobs == 'profile_editar'){
    var novaSenha = document.querySelector('input[name="senha"]').value;
    var confirmarSenha = document.querySelector('input[name="senha_confirmar"]').value;
    }else{
    var novaSenha = 'senha';
    var confirmarSenha = 'senha';  
    }
        
    if (novaSenha !== confirmarSenha) {
        event.preventDefault(); // Impede o envio do formulário
            
        Swal.fire({
            icon: 'error',
            title: 'Senhas não Conferem',
            text: 'Suas senhas precisam ser iguais!',
            showCancelButton: false,
            showConfirmButton: true,
            allowOutsideClick: true
        });
    }
});
</script>

</body>
</html>