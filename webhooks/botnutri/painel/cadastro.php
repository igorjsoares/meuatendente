<?php
header("Content-Type: text/html; charset=UTF-8", true);
//include ("dados_conexao.php");



//
// ─── BOTAO CADASTRAR ────────────────────────────────────────────────────────────
//  
if (isset($_POST['btn-cadastrar'])) {

  /* //Verifica as variáveis
  //$_POST  = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING); //FILTER_SANITIZE_NUMBER_INT //FILTER_SANITIZE_NUMBER_FLOAT
  $nome = filter_var($_POST['nome'], FILTER_SANITIZE_STRING);
  $email = filter_var($_POST['email'], FILTER_SANITIZE_STRING);
  $senha = filter_var($_POST['senha'], FILTER_SANITIZE_STRING);
  $senha = md5($senha);
  
  //
  // ─── INSERE O USUARIO NO BANCO DE DADOS ─────────────────────────────────────────
  //
  $sql = "INSERT INTO tbl_usuarios(nome, email, perfil, status, senha) VALUES ('$nome', '$email', 1, 1, '$senha')";
  $resultado = mysqli_query($conn['link'], $sql);
  $id_usuario = mysqli_insert_id($conn['link']);
  if($resultado != '1'){
    $_SESSION['alert'] = 'error';
    $_SESSION['alert_message'] = 'Não foi possível realizar o cadastro.';
    file_put_contents('log.txt',"> ERR ".date('d/m/Y h:i:s').' Insert usuarios. Erro: '.$resultado. mysqli_connect_error().PHP_EOL,FILE_APPEND);
  }else{
    $_SESSION['alert'] = 'success';
    $_SESSION['alert_message'] = 'Cadastro realizado com sucesso!';

    header('Location: index.php');
  } */
}
?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>VAGAMED</title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <!-- icheck bootstrap -->
  <link rel="stylesheet" href="plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="dist/css/adminlte.min.css">
  <!-- Google Font: Source Sans Pro -->
  <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
  <!-- Toastr -->
  <link href="plugins/toastr/build/toastr.css" rel="stylesheet" />
</head>

<body style="background: #017f92" class="hold-transition login-page">
  <div class="login-box">
    <div class="login-logo">
      <img src="assets/LogoTextoBranco.png" alt="">
    </div>
    <!-- /.login-logo -->
    <div class="card">
      <div id="overlay" style="background-color: white;" class="overlay justify-content-center align-items-center" hidden>
        <!--d-flex-->
        <i style="color:#017f92" class="fas fa-2x fa-sync fa-spin"></i>
      </div>
      <div style="border-radius: 10px;" class="card-body login-card-body">
        <p>Para o primeiro acesso é necessário que o administrador da sua empresa cadastre seu usuário na plataforma.</p>
        <form role="form" data-toggle="validator" method="POST">
          <div class="form-group mb-3">
            <input type="email" class="form-control" name="email" id="email" placeholder="Seu e-mail" required>
          </div>
          <div class="form-group mb-3">
            <input type="text" class="form-control" name="cpf" id="cpf" placeholder="CPF" required>
          </div>
          <div id="divSenhas" hidden>
            <div class="form-group mb-3">
              <input type="password" class="form-control" name="senha" id="senha" placeholder="Senha" data-minlength="6" data-minlength-error="Mínimo de seis (6) digitos" required>
              <div class="help-block with-errors"></div>
            </div>
            <div class="form-group mb-3">
              <input type="password" class="form-control" id="resenha" placeholder="Repita a Senha" data-match="#senha" data-match-error="Atenção! As senhas não estão iguais." required>
              <div class="help-block with-errors"></div>
            </div>
          </div>
        </form>
        <div class="row">
          <div class="col-7">
          </div>
          <!-- /.col -->
          <div class=" col-5">
            <button style="background-color: #000; border-color: #000;" type="submit" class="btn btn-primary btn-block" name="btnBuscar" id="btnBuscar"><b>BUSCAR</b></button>
            <button style="background-color: #000; border-color: #000;" type="submit" class="btn btn-primary btn-block" name="btnSalvar" id="btnSalvar" hidden><b>SALVAR</b></button>

          </div>
          <!-- /.col -->
        </div>
      </div>
      <!-- /.login-card-body -->
    </div>
    <p class="mb-0">
      <a class="btn" style="background-color: #5c5c5c; color: white; width: 100%; padding: 15px;" href="index.php"><b>VOLTAR</b></a>
    </p>
  </div>
  <!-- /.login-box -->

  <!-- jQuery -->
  <script src="plugins/jquery/jquery.min.js"></script>
  <!-- Bootstrap 4 -->
  <script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
  <!-- AdminLTE App -->
  <script src="dist/js/adminlte.min.js"></script>
  <!-- Toastr -->
  <script src="plugins/toastr/toastr.js"></script>
  <!-- Validador -->
  <script src="dist/js/validator.min.js"></script>
  <!-- InputMask -->
  <script src="plugins/moment/moment.min.js"></script>
  <script src="plugins/inputmask/jquery.inputmask.min.js"></script>

  <!-- The core Firebase JS SDK is always required and must be listed first -->
  <script src="https://www.gstatic.com/firebasejs/7.14.5/firebase-app.js"></script>
  <script src="https://www.gstatic.com/firebasejs/7.14.5/firebase-auth.js"></script>
  <!-- Conexão Firebase -->
  <script src="js/app.js"></script>
  <!-- Arquivo de logue -->
  <script src="js/cadastro.js"></script>
  
</body>

</html>