<?php
header("Content-Type: text/html; charset=UTF-8", true);

?>

<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <title>BOT NUTRI</title>
  <link rel="shortcut icon" href="assets/fav.ico" />
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
  <!-- DataTables -->
  <link rel="stylesheet" href="plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
</head>

<body style="background: #246056" class="hold-transition login-page">
  <div class="login-box">
    <div class="login-logo">
      <img src="assets/LogoTextoBranco.png" alt=""><br>
    </div>
    <!-- /.login-logo -->
    <div class="card">
      <div style="border-radius: 10px;" class="card-body login-card-body">
        <form>
          <div class="input-group mb-3">
            <input type="email" class="form-control" placeholder="E-mail" name="email" id="email" required>
            <div class="input-group-append">
              <div class="input-group-text">
                <span style="color: #f1a583" class="fas fa-envelope"></span>
              </div>
            </div>
          </div>
          <div class="input-group mb-3">
            <input type="password" class="form-control" placeholder="Senha" name="senha" id="senha" required>
            <div class="input-group-append">
              <div class="input-group-text">
                <span style="color: #f1a583" class="fas fa-lock"></span>
              </div>
            </div>
          </div>
        </form>
        <div class="row">
          <div class="col-8">
          </div>
          <!-- /.col -->
          <div class="col-4">
            <button style="color: white; background-color: #f1a583; border: none;" type="submit"
              class="btn btn-primary btn-block" name="btn-entrar" onclick="" id='btn-entrar'><b>ENTRAR</b></button>
          </div>
          <!-- /.col -->
        </div>

        <p class="mb-1">
          <a href="esqueci.php">Esqueci minha senha</a>
        </p>
      </div>
      <!-- /.login-card-body -->
    </div>
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
  <script src="js/logue.js"></script>
  <!-- JQuery Validator -->
  <script src="plugins/jquery-validation/dist/jquery.validate.min.js"></script>

 

</body>

</html>