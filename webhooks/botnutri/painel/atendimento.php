<?php
header("Content-Type: text/html; charset=UTF-8", true);
include("dados_conexao.php");

//Variável para JS
echo "<script>window.idEmpresa = " . $_SESSION['NP_id_empresa'] . "</script>";
echo "<script>window.idUsuario = " . $_SESSION['NP_id_usuario'] . "</script>";
echo "<script>window.tipoEmpresa = " . $_SESSION['NP_tipo_empresa'] . "</script>";
echo "<script>window.nomeEmpresa = '" . $_SESSION['NP_nome_empresa'] . "'</script>";

//variável para imagens
$aleatorio = rand(1000, 10000);
?>

<!-- Content Wrapper. Contains page content -->
<!-- Content Header (Page header) -->
<!--
-->

<!-- Main content -->
<section class="content" style="padding: 0px;">


    <div class="container-fluid">
        <div class="row align-items-top" style="height: 100vh">
            <div class="col-4 col-md-4" style="height: 100vh; background-image: url('mari_fita.png'); background-repeat: no-repeat;background-size: 100%; background-position: center; background-color: #fff; background-position: bottom;">

                <ul class="nav flex-column" id="ulMenuConversas">
                    <!--
                    <li class="nav-item">
                        <div style="padding: 10px" class="row align-items-center">
                            <div class="col-2">
                                <div style="padding: 0px;" class="image">
                                    <img style="width: 45px; height: 45px" id="imgEmpresaMenu" src="assets/empresas/<?= $_SESSION['NP_id_empresa'] ?>.jpg" class="img-circle elevation-2" alt="">
                                </div>
                            </div>
                            <div class="col-8">
                                <font style="font-size: 20px;">Igor Soares</font><br>
                                <font style="font-size: 13px; color: gray">Mensagem enviada</font>
                            </div>
                            <div class="col-2">
                                <span class="float-right badge bg-success">2</span>
                            </div>
                        </div>
                    </li>
                    -->
                </ul>
            </div>
            <div class="col-8 col-md-8" style="padding: 0px;">
                <div class="row align-items-center" style="margin-left: 20px; padding: 10px;">
                    <div class="col-8">
                        <div class="row  align-items-center">

                            <img style="width: 45px; height: 45px; margin-right: 10px" id="imgConversaAvatar" src="assets/empresas/<?= $_SESSION['NP_id_empresa'] ?>.jpg" class="img-circle elevation-2" alt="">
                            <font id="fConversaNome">Igor Soares</font>
                        </div>
                    </div>
                    <div-col class="col-4" style="text-align: right;">
                        <div class="card-tools">
                            <!-- <span title="3 New Messages" class="badge bg-primary">3</span> -->
                            <button type="button" class="btn btn-tool" title="Enviar para suporte" id="btnSuporte">
                                <i id="iSuporte" class="fas fa-headset"></i>
                            </button>
                            <button type="button" class="btn btn-tool" title="Bot liberado" id="btnBloqueio">
                                <i id="iBloqueio" class="fas fa-robot" style="color: #67e375"></i>
                            </button>
                        </div>
                    </div-col>
                </div>

                <div>
                    <!-- Conversations are loaded here -->
                    <div class="direct-chat-messages" id="divMensagens" style="height: 400px; background-color: #E5DDD5;">
                        

                    </div>
                    <!--/.direct-chat-messages-->

                    <div class="card-footer">
                        <div class="row">
                            <textarea class="form-control" rows="3" id="taMensagem" placeholder="Digite a mensagem..." style="width: 84%; margin-right: 1%"></textarea>
                            <button class="btn btn-primary" id="btnEnvio" style="width: 15%">Enviar</button>

                        </div>
                    </div>
                    <!-- /.card-footer-->

                    <!--/.direct-chat -->
                    <div class="row" style="padding: 20px;">

                    </div>

                </div>
                <!-- /.col -->
            </div>
        </div>
</section>
<!-- /.content -->

<!-- M O D A L  P R E V I E W -->
<div class="modal fade" id="modalPreview">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div id="overlayPreview" style="background-color: white;" class="overlay justify-content-center align-items-center" hidden>
                <!--d-flex-->
                <i style="color:#00A599" class="fas fa-2x fa-sync fa-spin"></i>
            </div>
            <div style="height: 70px;" class="modal-header">
                <div class="row">
                    <div class="col-2">
                        <img id="imgFoto" src="assets/empresas/<?= $_SESSION['NP_id_empresa'] ?>.jpg?var=<?= $aleatorio ?>" class="rounded-circle imgPerfil mr-2" style="height: 40px; object-fit: cover!important" alt="">
                    </div>
                    <div class="col-10">
                        <div style="margin-left: 15px; margin-top: 10px; height: 100px">
                            <font style="font-size: 15px" id="fNome"><?= $_SESSION['NP_nome_empresa'] ?></font>
                        </div>
                    </div>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div style="background-color: #f4f6f9;" class="modal-body">
                <div style="height: 400px; overflow-x: hidden;" class="container" id="bodyPreview">
                    <div class="row align-items-center">
                        <div style="border-radius: 10px; background-color: #fff" class="col-12">
                            <img src="" style="width: 250px; height: 225px; visibility: hidden; display: none" id="imgPreview" alt="">
                            <video style="width: 250px; height: 225px; visibility: hidden; display: none" id="videoPreview" src="" controls>
                                Seu navegador não suporta o elemento <code>video</code>.
                            </video>
                            <audio style="visibility: hidden; display: none; width: 250px; margin-top:20px" id="audioPreview" controls src="">
                                <p>Seu nevegador não suporta o elemento audio.</p>
                            </audio>
                            <pre style="padding: 5px; width: 250px; font-size: 12px; white-space: pre-wrap; word-wrap: break-word;" id="pMensagem"></pre>
                            <!-- <textarea id="taMensagemPreview" class="form-control" placeholder="" maxlength="1500"></textarea> -->

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->

<!-- Select2 -->
<script src="plugins/select2/js/select2.full.min.js"></script>

<script src="js/atendimento.js"></script>