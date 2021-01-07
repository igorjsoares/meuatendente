<?php
header("Content-Type: text/html; charset=UTF-8", true);
include("dados_conexao.php");

if (!isset($_SESSION)) {
    session_start();
  }
  
  if ($_SESSION['NP_Autenticacao'] != true) {
    session_destroy();
    header('Location: index.php');
    exit;
  }

$id_empresa = $_SESSION['NP_id_empresa'];

//Variável para JS
echo "<script>window.idEmpresa = " . $id_empresa . "</script>";
echo "<script>window.tipoEmpresa = " . $_SESSION['NP_tipo_empresa'] . "</script>";

//Verifica se a logo existe
$filename = 'assets/empresas/' . $id_empresa . '.png';
if (file_exists($filename)) {
    $logo = $id_empresa;
} else {
    $logo = 'null';
}
//variável para imagens
$aleatorio = rand(1000, 10000);


//?Busca informações principais do banco de dados
$sql = "SELECT * FROM tbl_empresas WHERE id_empresa = $id_empresa";
$query = mysqli_query($conn['link'], $sql);
$consultaEmpresa = mysqli_fetch_array($query, MYSQLI_ASSOC);
$numRow = mysqli_num_rows($query);
if (!$query) {
    echo 'Não é possível acessar no momento';
}
if ($numRow == 0) { //Não foi encontrado nada
    echo "Nenhuma empresa esta vinculada a este usuário, favor entrar em contato com o suporte.";
} else {
    $nome = $consultaEmpresa['nome'];
    $razaoSocial = $consultaEmpresa['razao_social'];
    $cnpj = $consultaEmpresa['cnpj'];
    $cnpj = str_replace('.', '', $cnpj);
    $cnpj = str_replace('/', '', $cnpj);
    $cnpj = str_replace('-', '', $cnpj);
    $tipo = $consultaEmpresa['tipo'];
    echo "<script>window.tipo = " . $tipo . "</script>";
    if ($tipo == 1) {
        $tipo = "Política";
    } elseif ($tipo == 2) {
        $tipo = "Comercial";
    }
    $status = $consultaEmpresa['status'];
    echo "<script>window.status = " . $status . "</script>";

    if ($status == 1) {
        $statusNome = "Ativa";
        $statusCor = "success";
    } elseif ($status == 0) {
        $statusNome = "Inativa";
        $statusCor = "danger";
    }
    $cidade = @$consultaEmpresa['cidade'];

    $endereco = @$consultaEmpresa['endereco'];
    $enderecoLatLong = @$consultaEmpresa['endereco'] . " " . @$consultaEmpresa['latlong'];
    $arrayLatLong = explode(', ', $consultaEmpresa['latlong']);
    $latitude = @$arrayLatLong[0];
    $longitude = @$arrayLatLong[1];
}
//echo $sql;

?>

<!-- Content Wrapper. Contains page content -->
<!-- Content Header (Page header) -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Empresa</h1>
            </div>
        </div>
    </div><!-- /.container-fluid -->
</section>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12 col-lg-6">
                <!-- Profile Image -->
                <div class="card card-primary card-outline">
                    <div class="card-body box-profile">
                        <div style="background-color: #f4f6f9;height: 80px; padding: 10px;" class="row">
                            <div class="col-1">
                                <img id="imgFotoCampanha" src="assets/empresas/<?= $_SESSION['NP_id_empresa'] ?>.jpg?var=<?= $aleatorio ?>" class="rounded-circle imgPerfil mr-2" style="height: 60px; object-fit: cover!important" alt="">
                            </div>
                            <div class="col-11">
                                <div style="margin-left: 15px; margin-top: 20px; height: 100px">
                                    <h4><?= $_SESSION['NP_nome_empresa'] ?></h4>
                                </div>
                            </div>
                        </div>
                        <p>
                            <font style="font-size: 14px; color: #bababa">Esta é a imagem e o nome que aparecerá nas mensagens.</font>
                        </p>

                        <ul class="list-group list-group-unbordered mb-3">
                            <li class="list-group-item">
                                <b>Razão social</b> <a class="float-right"><?= $razaoSocial ?></a>
                            </li>
                            <li class="list-group-item">
                                <b>CNPJ</b> <a class="float-right"><?= $cnpj ?></a>
                            </li>
                            <li class="list-group-item">
                                <b>Tipo</b> <a class="float-right"><?= $tipo ?></a>
                            </li>
                            <li class="list-group-item">
                                <b>Cidade</b> <a class="float-right">
                                    <font id="fCidade"><?= $cidade ?></font>
                                </a>
                            </li>
                            <li class="list-group-item">
                                <b>Endereço</b> <a class="float-right"><?= $enderecoLatLong ?></a>
                            </li>
                            <li class="list-group-item">
                                <b>Status</b><a class="float-right"><span style="font-size: 15px" class="badge badge-<?= $statusCor ?>"><?= $statusNome ?></span></a>
                            </li>
                        </ul>

                        <a href="#" class="btn btn-primary btn-block" data-toggle="modal" data-target="#modalEmpresa"><b>EDITAR</b></a>
                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->
            </div>
        </div>
    </div>
</section>
<!-- /.content -->

<div class="modal fade" id="modalEmpresa">
    <div class="modal-dialog">
        <div class="modal-content">
            <div id="overlay" style="background-color: white;" class="overlay justify-content-center align-items-center" hidden>
                <i style="color:blueviolet" class="fas fa-2x fa-sync fa-spin"></i>
            </div>
            <div class="modal-header">
                <h4 class="modal-title">Editar Perfil</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

                <label for="exampleInputFile">Alterar logomarca</label>
                <div class="row">
                    <div class="col-9">
                        <div class="form-group">
                            <div class="input-group">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" accept=".jpg" id="imagemPerfil">
                                    <label class="custom-file-label" for="imagemPerfil">Selecione um arquivo</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-3">
                        <button class="btn btn-primary btn-block" id="btn-imagem"><b>SALVAR</b></button>
                    </div>
                </div>

                <div id="resultFoto">
                </div>
                <form role="form" id="formEmpresa">
                    <div class="form-group">
                        <label for="telefone">Nome da empresa</label>
                        <input type="text" class="form-control" name="nome" id="nome" placeholder="" value="<?= $nome ?>" maxlength="22" disabled>
                    </div>
                    <div class="form-group">
                        <label for="telefone">Razão social</label>
                        <input type="text" class="form-control" name="razao_social" id="razaoSocial" placeholder="" value="<?= $razaoSocial ?>">
                    </div>
                    <div class="form-group">
                        <label for="cnpj">CNPJ</label>
                        <input type="text" class="form-control" name="cnpj" id="cnpj" placeholder="" value="<?= $cnpj ?>" data-inputmask='"mask": "99.999.999/9999-99"' data-mask maxlength="18">
                    </div>
                    <div class="form-group">
                        <label>Cidade</label>
                        <select class="form-control" name="selectCidade" id="selectCidade" disabled>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Endereço</label>
                        <textarea name="endereco" id="endereco" class="form-control" rows="2" placeholder="" maxlength="80"><?= $endereco ?></textarea>
                    </div>

                    <div style="margin-left: 15px; margin-bottom: 10px; align-items: center;" class="row" hidden>
                        Latitude <input name="latitude" id="latitude" style="height: 25px; width: 100px; margin-left: 10px; margin-right: 5px" type="text" class="form-control" value="<?= $latitude ?>" placeholder="">, Longitude<input name="longitude" id="longitude" type="text" style="height: 25px; width: 100px; margin-left: 5px; margin-right: 5px" class="form-control" value="<?= $longitude ?>" placeholder="">
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <!-- select -->
                            <div class="form-group">
                                <label>Tipo</label>
                                <select class="form-control" name="tipo" id="tipo" disabled>
                                    <option value="1">Política</option>
                                    <option value="2">Comercial</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label>Status</label>
                                <select class="form-control" name="status" id="status" disabled>
                                    <option value="1">Ativa</option>
                                    <option value="0">Inativa</option>
                                </select>
                            </div>
                        </div>
                    </div>



                    <div class="modal-footer text-right">
                        <button type="submit" class="btn btn-primary btn-block" id="btn-salvar"><b>SALVAR</b></button>
                    </div>
                </form>
            </div>
            <!-- /.card-body -->
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->
<!-- /.content -->

<script src="js/empresa.js"></script>