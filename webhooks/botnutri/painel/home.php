<?php
header("Content-Type: text/html; charset=UTF-8", true);
if (!isset($_SESSION)) {
  session_start();
}

if ($_SESSION['NP_Autenticacao'] != true) {
  session_destroy();
  header('Location: index.php');
  exit;
}

echo "<script>window.perfil_usuario = '" . $_SESSION['NP_perfil_usuario'] . "'</script>";
echo "<script>window.idEmpresa = '" . $_SESSION['NP_id_empresa'] . "'</script>";
echo "<script>window.idUsuario = '" . $_SESSION['NP_id_usuario'] . "'</script>";
echo "<script>window.numeroUsuario = '" . $_SESSION['NP_telefone_usuario'] . "'</script>";
echo "<script>window.nomeUsuarioAbreviado = '" . $_SESSION['NP_nome_usuario_abreviado'] . "'</script>";
echo "<script>window.tipoEmpresa = '" . $_SESSION['NP_tipo_empresa'] . "'</script>";

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>BOT NUTRI MARI MARTINS</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
  <!-- overlayScrollbars -->
  <link rel="stylesheet" href="plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
  <!-- Toastr -->
  <link href="plugins/toastr/build/toastr.css" rel="stylesheet" />
  <!-- Select2 -->
  <link rel="stylesheet" href="plugins/select2/css/select2.min.css">
  <link rel="stylesheet" href="plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="dist/css/adminlte.min.css">
  <!-- daterange picker -->
  <link rel="stylesheet" href="plugins/daterangepicker/daterangepicker.css">
  <!-- Tempusdominus Bootstrap 4 -->
  <link rel="stylesheet" href="plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
  <!-- overlayScrollbars -->
  <link rel="stylesheet" href="plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
</head>

<body class="sidebar-mini layout-fixed sidebar-collapse hold-transition layout-fixed">
  <!-- Site wrapper -->
  <div class="wrapper">
    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">

      <!-- Left navbar links -->
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i style="color: #a5f0ea" class="fas fa-bars"></i></a>
        </li>

      </ul>
      <img src="assets/logo_padrao_hor.png" alt="">

      <!-- Right navbar links -->
      <ul class="navbar-nav ml-auto">
        <!-- Notifications Dropdown Menu -->
        <li class="nav-item dropdown" hidden>
          <a class="nav-link" data-toggle="dropdown" href="#">
            <i style="color: #017f92" class="far fa-bell"></i>
            <span style="visibility: hidden" id="spanNotificacao" class="badge badge-danger navbar-badge"></span>
          </a>
          <div id="painelNotificacoes" style="min-width: 230px;" class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
            <!-- <span class="dropdown-item dropdown-header">2 Notificações</span>
            <div class="dropdown-divider"></div> -->
            <!--  <a href="#" class="dropdown-item">
              <i style="color: #017f92" class="fas fa-folder-plus mr-2"></i> Pedido pendente
              <span class="float-right text-muted text-sm">1m</span>
            </a> -->

            <!-- <div class="dropdown-divider"></div>
            <a href="#" class="dropdown-item dropdown-footer">Ver todas as notificações</a> -->
          </div>
        </li>
        <li class="nav-item">
          <a class="nav-link" id="btn-deslogar">
            <i style="color: #017f92" class="fas fa-sign-out-alt"></i>
          </a>
        </li>
      </ul>
    </nav>
    <!-- /.navbar -->

    <?php
    include "menu.php";
    ?>
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper" id="main">
      <!-- Content Header (Page header) -->
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1>Tela inicial</h1>
            </div>
          </div>
        </div><!-- /.container-fluid -->
      </section>

      <!-- Main content -->
      <section class="content">


      </section>
      <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->



    <footer style="height: 10px; padding: 3px" class="main-footer text-sm">
      <div class="float-right d-none d-sm-block">
        <b>Versão</b> 1.0.027
      </div>
      <strong>Copyright &copy; 2020-2021 <a href="https://nutrimarimartins.com.br">Nutri Mari Martins</a>.</strong> Todos os direitos reservados.
    </footer>

    <!-- Control Sidebar -->
    <aside class="control-sidebar control-sidebar-dark">
      <!-- Control sidebar content goes here -->
    </aside>
    <!-- /.control-sidebar -->
  </div>
  <!-- ./wrapper -->

  <!-- jQuery -->
  <script src="plugins/jquery/jquery.min.js"></script>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.0/jquery.min.js"></script>

  <!-- Bootstrap 4 -->
  <script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
  <!-- Select2 -->
  <script src="plugins/select2/js/select2.full.min.js"></script>
  <!-- The core Firebase JS SDK is always required and must be listed first -->
  <script src="https://www.gstatic.com/firebasejs/7.14.5/firebase-app.js"></script>
  <script src="https://www.gstatic.com/firebasejs/7.14.5/firebase-auth.js"></script>
  <!-- overlayScrollbars -->
  <script src="plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
  <!-- AdminLTE App -->
  <script src="dist/js/adminlte.min.js"></script>
  <!-- AdminLTE for demo purposes -->
  <script src="dist/js/demo.js"></script>
  <!-- InputMask -->
  <script src="plugins/moment/moment.min.js"></script>
  <script src="plugins/inputmask/jquery.inputmask.min.js"></script>
  <!-- Validador -->
  <script src="dist/js/validator.min.js"></script>
  <!-- JQuery Validator -->
  <script src="plugins/jquery-validation/dist/jquery.validate.min.js"></script>
  <!-- bs-custom-file-input -->
  <script src="plugins/bs-custom-file-input/bs-custom-file-input.min.js"></script>
  <!-- Toastr -->
  <script src="plugins/toastr/toastr.js"></script>
  <!-- jQuery Knob -->
  <script src="plugins/jquery-knob/jquery.knob.min.js"></script>
  <!-- Sparkline -->
  <script src="plugins/sparklines/sparkline.js"></script>
  <!-- date-range-picker -->
  <script src="plugins/daterangepicker/daterangepicker.js"></script>
  <!-- Moment -->
  <script src="plugins/daterangepicker/moment.min.js"></script>
  <!-- ChartJS -->
  <script src="plugins/chart.js/Chart.min.js"></script>

  <!-- overlayScrollbars -->
  <script src="plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
  <!-- DataTables -->
  <script src="plugins/datatables/jquery.dataTables.min.js"></script>
  <script src="plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
  <script src="plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
  <script src="plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>


  <!-- Conexão Firebase -->
  <script src="js/app.js"></script>

  <script type="text/javascript" src="js/logout.js" charset="utf-8"></script>

  <script src="js/home.js"></script>
</body>

</html>