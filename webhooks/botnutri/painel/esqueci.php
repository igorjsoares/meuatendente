<?php
header("Content-Type: text/html; charset=UTF-8",true);
//include ("dados_conexao.php");

//
// ─── BOTAO ENTRAR ───────────────────────────────────────────────────────────────
//
if(isset($_POST['btn-enviar'])){
  /* $email = filter_var($_POST['email'], FILTER_SANITIZE_STRING);
  
  //?Busca informações principais do banco de dados
  $sql = "SELECT * FROM tbl_usuarios WHERE email = '$email'";
  $query = mysqli_query($conn['link'], $sql);
  $resultadoSenha = mysqli_fetch_array($query, MYSQLI_ASSOC);
  $numRow = mysqli_num_rows($query);
  if( ! $query ){
    $_SESSION['alert'] = 'error';
    $_SESSION['alert_message'] = 'Não é possível acessar no momento';
    file_put_contents('log.txt',"> ERR ".date('d/m/Y h:i:s')." Mysql Connect: " . mysqli_connect_error().PHP_EOL,FILE_APPEND);
  }if($numRow == 0){ //Não foi encontrado nada
    $_SESSION['alert'] = 'error';
    $_SESSION['alert_message'] = 'Esse e-mail não foi encontrado.';
  }else{     
    
    //
    // ─── CRIAR SENHA ─────────────────────────────────────────────────
    //
    //Letras minúsculas embaralhadas
    $letrasMinusculas = str_shuffle('abcdefghijklmnopqrstuvwxyz');
    //Letras maiúsculas embaralhadas
    $letrasMaiusculas = str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ');
    //Números aleatórios
    $numeros = (((date('Ymd') / 12) * 24) + mt_rand(800, 9999));
    $numeros .= 1234567890;
    //Junta tudo
    $caracteres = $letrasMinusculas.$letrasMaiusculas.$numeros;
    //Embaralha e pega apenas 6
    $senha = substr(str_shuffle($caracteres), 0, 6);

    $sql = "UPDATE tbl_usuarios SET senha = MD5('$senha') WHERE email = '$email'";
    $resultadoSenha = mysqli_query($conn['link'], $sql);  
    if($resultadoSenha == true){
      $_SESSION['alert'] = 'success';
      $_SESSION['alert_message'] = 'Foi enviada uma nova senha para o seu e-mail. '.$senha;
      header('Location: index.php');
    }else{
      $_SESSION['alert'] = 'error';
      $_SESSION['alert_message'] = 'Não foi possível gerar sua senha.';
    }
    
  } */
  
}

?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Agendado - Senha</title>
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
  <link href="plugins/toastr/build/toastr.css" rel="stylesheet"/>
</head>
<body style="background: #017f92" class="hold-transition login-page">
  <div class="login-box">
    <div class="login-logo">
      <img src="assets/LogoBranca.png" alt="">
    </div>
    <!-- /.login-logo -->
    <div class="card">
      <div style="border-radius: 10px;" class="card-body login-card-body">
        <p style="color: #017f92" class="login-box-msg">
          Digite seu e-mail, enviaremos um link para redefinir sua senha
        </p>
        <form role="form" method="post">
          <div class="form-group mb-3">
            <input type="email" class="form-control" id="email" placeholder="E-mail" name="email">
          </div>
        </form>
            <div class="row">
              <div class="col-4">
              </div>
              <!-- /.col -->
              <div class="col-8">
                <button style="background-color: #000; border-color: #000;" type="submit" class="btn btn-primary btn-block" name="btn-enviar" id="btn-enviar"><b>REDEFINIR SENHA</b></button>
              </div>
              <!-- /.col -->
            </div>
      </div>
      <!-- /.login-card-body -->
    </div>
    <p class="mb-0">
      <a  class="btn" style="background-color: #5C5C5C; color: white; width: 100%; padding: 15px;" href="index.php"><b>VOLTAR</b></a>
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

  <!-- The core Firebase JS SDK is always required and must be listed first -->
    <script src="https://www.gstatic.com/firebasejs/7.14.5/firebase-app.js"></script>
    <script src="https://www.gstatic.com/firebasejs/7.14.5/firebase-auth.js"></script>
    <!-- Conexão Firebase -->
    <script src="js/app.js"></script>
    <!-- Arquivo de logue -->
    <script src="js/esqueci.js"></script>

  <script type="text/javascript">



    function notify(alert, alert_message) {
        if(alert == 'success'){
          toastr.success(alert_message, '', {timeOut: 2000, positionClass: 'toast-top-full-width', progressBar: true})
        }
        if(alert == 'error'){
          toastr.error(alert_message, '', {timeOut: 2000, positionClass: 'toast-top-full-width', progressBar: true})
        }
      }
  </script>  
</body>
</html>