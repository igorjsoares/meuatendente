<!-- Main Sidebar Container -->
<aside id="asideBar" style="background-color: #246056" class="main-sidebar sidebar-light-primary elevation-4 border-bottom-0">
  <!-- Brand Logo -->
  <a style="height: 60px" href="home.php" class="brand-link">
    <img style="margin-top: 5px" src="assets/logo_sozinha_branca.png" class="brand-image">
    <span style="color: white" class="brand-text">BOT Nutri</span>
  </a>

  <!-- Sidebar -->
  <div id="divSideBar" class="sidebar">
    <!-- Sidebar user (optional) -->
    <div class="user-panel mt-3 pb-3 mb-3 d-flex">
      <div style="padding: 0px;" class="image">
        <img style="width: 58px; height: 58px" id="imgEmpresaMenu" src="assets/empresas/<?= $_SESSION['NP_id_empresa'] ?>.jpg" class="img-circle elevation-2" alt="">
      </div>
      <div id="divInfoTextoNomeEmpresa" class="info">
        <p>
          <font style="color: white" href="#" class="d-block"><?= $_SESSION['NP_nome_empresa'] ?></font>
        </p>
      </div>
      <div style="visibility: hidden; display: unset; width: 0%; padding-top: 3px" id="divInfoSelectNomeEmpresa" class="info">
        <select style="font-size: 12px; height: 30px" class="form-control" name="perfil" id="selectNomeEmpresa" disabled>
          <option value="1">VAGAMED</option>
        </select>
      </div>

    </div>

    <!-- Sidebar Menu -->
    <nav class="mt-2">
      <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
        <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->

        


        <li class="nav-item">
          <a id="btnListas" style="color: white;" href="#" class="nav-link">
            <i class="fas fa-columns nav-icon"></i>
            <p>Painel</p>
          </a>
        </li>
        <li class="nav-item">
          <a id="btnListas" style="color: white;" href="#" class="nav-link">
            <i class="fab fa-whatsapp nav-icon"></i>
            <p>Atendimento</p>
          </a>
        </li>
        <?php
        if ($_SESSION['NP_perfil_usuario'] == 'MASTER' || $_SESSION['NP_perfil_usuario'] == 'Administrador') {
        ?>



          <li class="nav-item">
            <a id="btnUsuarios" style="color: white" href="#" class="nav-link">
              <i class="fas fa-users nav-icon"></i>
              <p>
                Usuários
              </p>
            </a>
          </li>

          <li class="nav-item">
            <a id="btnEmpresa" style="color: white" href="#" class="nav-link">
              <i class="fas fa-hospital nav-icon"></i>
              <p>
                Empresa
              </p>
            </a>
          </li>

          <li class="nav-item" hidden>
            <a id="btnRelatorios" style="color: white" href="#" class="nav-link">
              <i class="fas fa-chart-line nav-icon"></i>
              <p>
                Relatórios
              </p>
            </a>
          </li>


        <?php
        }
        ?>

        <?php
        if ($_SESSION['NP_perfil_usuario'] == 'MASTER') {
        ?>
          <li class="nav-item">
            <a id="btnClientes" style="color: white" href="#" class="nav-link">
              <i class="fas fa-address-book nav-icon"></i>
              <p>
                Clientes
              </p>
            </a>
          </li>

          <li class="nav-item">
            <a id="btnProspectos" style="color: white" href="#" class="nav-link">
              <i class="fas fa-plus-square nav-icon"></i>
              <p>
                Prospectos
              </p>
            </a>
          </li>

          <li class="nav-item" hidden>
            <a id="btnEnvios" style="color: white" href="#" class="nav-link">
              <i class="fab fa-whatsapp nav-icon"></i>
              <p>
                BOOT NP
              </p>

            </a>
          </li>
        <?php
        }
        ?>
      </ul>
    </nav>
    <!-- /.sidebar-menu -->
  </div>
  <!-- /.sidebar -->
</aside>