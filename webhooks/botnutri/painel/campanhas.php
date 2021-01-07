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
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Campanhas - Whatsapp</h1>
            </div>
            <div class="col-sm-6 text-right">
                <a id="btnNovoUsuario" style="padding: 10px;" class="btn btn-warning" data-toggle="modal" data-target="#modalNova">
                    <i class="fas fa-plus">
                    </i>
                    Nova campanha
                </a>
            </div>
        </div>
    </div><!-- /.container-fluid -->
</section>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12 col-lg-12">
                <!-- Profile Image -->
                <div class="card">
                    <div id="overlayTabela" style="background-color: white;" class="overlay justify-content-center align-items-center" hidden>
                        <!--d-flex-->
                        <i style="color:#00A599" class="fas fa-2x fa-sync fa-spin"></i>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <table id="tableCampanhas" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>
                                        Código
                                    </th>
                                    <th>
                                        Nome da campanha
                                    </th>
                                    <th>
                                        Número de contatos
                                    </th>
                                    <th>
                                        Início do envio
                                    </th>
                                    <th>
                                        Encerramento
                                    </th>
                                    <th>
                                        Status
                                    </th>
                                    <th>
                                        Ações
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="tbodyCampanhas">

                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- /.card -->
            </div>
        </div>
    </div>
</section>
<!-- /.content -->


<!-- M O D A L  N O V A  C A M P A N H A -->
<div class="modal fade" id="modalNova">
    <div id="contentModalNova" class="modal-dialog modal modal-dialog-centered">
        <div class="modal-content">
            <div id="overlay" style="background-color: white;" class="overlay justify-content-center align-items-center" hidden>
                <!--d-flex-->
                <i style="color:#00A599" class="fas fa-2x fa-sync fa-spin"></i>
            </div>
            <div class="modal-body">

                <h3>Nova campanha</h3>

                <div class="form-group">
                    <label>Conta de perfil</label>
                    <select class="form-control" name="selectConta" id="selectConta">
                    </select>
                </div>
                <div style="background-color: #f4f6f9;height: 50px; padding: 5px;" class="row">
                    <div class="col-1">
                        <img id="imgFotoCampanha" src="" class="rounded-circle imgPerfil mr-2" style="height: 40px; object-fit: cover!important" alt="">
                    </div>
                    <div class="col-11">
                        <div style="margin-left: 10px; margin-top: 10px; height: 100px">
                            <font style="font-size: 15px" id="fNomePerfil"></font>
                        </div>
                    </div>
                </div>

                <p>
                    <font style="font-size: 14px; color: #bababa; margin-left: 10px">Esta é a imagem e o nome que aparecerá na mensagem.</font>
                </p>

                <div class="form-group">
                    <label for="nomeCampanha">Nome da campanha</label>
                    <input type="text" class="form-control" name="nomeCampanha" id="nomeCampanha" placeholder="" maxlength="30" value="">
                </div>
                <div class="row">
                    <div class="col-8">
                        <div class="select2-info">
                            <label for="inicioEnvios">Listas de contato</label>
                            <select style="font-size: 12px" disabled id="selectListas" class="select2" data-placeholder="" data-dropdown-css-class="select2-info" style="width: 100%; font-size: 14px">

                            </select>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="form-group">
                            <label for="inicioEnvios">Início do envio</label>
                            <input class="form-control float-right" type="text" name="inicioEnvios" id="inicioEnvios" value="" />
                        </div>
                    </div>
                </div>
                <font id="fTotalContatos" style="color: #bababa; font-size: 12px"></font>

                <div style="margin-top: 15px" class="form-group">
                    <label>Tipo de mensagem</label>
                    <select id="selectTipo" class="custom-select">
                        <option value="1">Somente texto</option>
                        <option value="2">Imagem com texto</option>
                        <option value="3">Vídeo</option>
                        <option value="4">Áudio</option>
                        <!-- <option value="5">PDF</option> -->
                    </select>
                </div>

                <div style="visibility: hidden; display: none" id="rowArquivo" class="row">
                    <div class="col-12">
                        <div class="form-group">
                            <div class="input-group">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" accept="image/*" id="arquivoMensagem">
                                    <label class="custom-file-label" for="imagemPerfil">Selecione um arquivo</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <div id="cardEmojis" style="padding: 10px; margin: 10px; border-radius: 10px; visibility: hidden; display: none">
                    <!-- /.card-header -->
                    <div class="card card-primary card-outline card-tabs">
                        <div class="card-header p-0 pt-1 border-bottom-0">
                            <div class="row">
                                <div class="col-11">
                                    <ul class="nav nav-tabs" id="custom-tabs-three-tab" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link active" id="custom-tabs-three-home-tab" data-toggle="pill" href="#custom-tabs-three-home" role="tab" aria-controls="custom-tabs-three-home" aria-selected="true">😃</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="custom-tabs-three-profile-tab" data-toggle="pill" href="#custom-tabs-three-profile" role="tab" aria-controls="custom-tabs-three-profile" aria-selected="false">🚚</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="custom-tabs-three-messages-tab" data-toggle="pill" href="#custom-tabs-three-messages" role="tab" aria-controls="custom-tabs-three-messages" aria-selected="false">🌏</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="custom-tabs-three-settings-tab" data-toggle="pill" href="#custom-tabs-three-settings" role="tab" aria-controls="custom-tabs-three-settings" aria-selected="false">🍔</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="custom-tabs-four-settings-tab" data-toggle="pill" href="#custom-tabs-four-settings" role="tab" aria-controls="custom-tabs-four-settings" aria-selected="false">📢</a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="col-1" style="padding-left: 0px;padding-top: 20px;">
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool" id="btnCloseEmojis"><i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <!-- /.card-tools -->
                        </div>


                        <div class="card-body">
                            <div class="tab-content" id="custom-tabs-three-tabContent">
                                <div class="tab-pane fade show active" id="custom-tabs-three-home" role="tabpanel" aria-labelledby="custom-tabs-three-home-tab">
                                    <div class="row">
                                        <!--
                            Fonte: https://apps.timwhitlock.info/emoji/tables/unicode
                            IMPORTANTE! Copie os emojis da coluna NATIVE        
                        -->
                                        <button type="button" onclick="emojis('😁')" class="btn btn-sm">😁</button>
                                        <button type="button" onclick="emojis('😂')" class="btn btn-sm">😂</button>
                                        <button type="button" onclick="emojis('😃')" class="btn btn-sm">😃</button>
                                        <button type="button" onclick="emojis('😄')" class="btn btn-sm">😄</button>
                                        <button type="button" onclick="emojis('😅')" class="btn btn-sm">😅</button>
                                        <button type="button" onclick="emojis('😆')" class="btn btn-sm">😆</button>
                                        <button type="button" onclick="emojis('😉')" class="btn btn-sm">😉</button>
                                        <button type="button" onclick="emojis('😊')" class="btn btn-sm">😊</button>
                                        <button type="button" onclick="emojis('😋')" class="btn btn-sm">😋</button>
                                        <button type="button" onclick="emojis('😌')" class="btn btn-sm">😌</button>
                                        <button type="button" onclick="emojis('😍')" class="btn btn-sm">😍</button>
                                        <button type="button" onclick="emojis('😏')" class="btn btn-sm">😏</button>
                                        <button type="button" onclick="emojis('😒')" class="btn btn-sm">😒</button>
                                        <button type="button" onclick="emojis('😓')" class="btn btn-sm">😓</button>
                                        <button type="button" onclick="emojis('😔')" class="btn btn-sm">😔</button>
                                        <button type="button" onclick="emojis('😖')" class="btn btn-sm">😖</button>
                                        <button type="button" onclick="emojis('😘')" class="btn btn-sm">😘</button>
                                        <button type="button" onclick="emojis('😚')" class="btn btn-sm">😚</button>
                                        <button type="button" onclick="emojis('😜')" class="btn btn-sm">😜</button>
                                        <button type="button" onclick="emojis('😝')" class="btn btn-sm">😝</button>
                                        <button type="button" onclick="emojis('😞')" class="btn btn-sm">😞</button>
                                        <button type="button" onclick="emojis('😠')" class="btn btn-sm">😠</button>
                                        <button type="button" onclick="emojis('😢')" class="btn btn-sm">😢</button>
                                        <button type="button" onclick="emojis('😤')" class="btn btn-sm">😤</button>
                                        <button type="button" onclick="emojis('😥')" class="btn btn-sm">😥</button>
                                        <button type="button" onclick="emojis('😨')" class="btn btn-sm">😨</button>
                                        <button type="button" onclick="emojis('😩')" class="btn btn-sm">😩</button>
                                        <button type="button" onclick="emojis('😪')" class="btn btn-sm">😪</button>
                                        <button type="button" onclick="emojis('😫')" class="btn btn-sm">😫</button>
                                        <button type="button" onclick="emojis('😭')" class="btn btn-sm">😭</button>
                                        <button type="button" onclick="emojis('😰')" class="btn btn-sm">😰</button>
                                        <button type="button" onclick="emojis('😱')" class="btn btn-sm">😱</button>
                                        <button type="button" onclick="emojis('😲')" class="btn btn-sm">😲</button>
                                        <button type="button" onclick="emojis('😳')" class="btn btn-sm">😳</button>
                                        <button type="button" onclick="emojis('😵')" class="btn btn-sm">😵</button>
                                        <button type="button" onclick="emojis('😷')" class="btn btn-sm">😷</button>
                                        <button type="button" onclick="emojis('😸')" class="btn btn-sm">😸</button>
                                        <button type="button" onclick="emojis('😹')" class="btn btn-sm">😹</button>
                                        <button type="button" onclick="emojis('😺')" class="btn btn-sm">😺</button>
                                        <button type="button" onclick="emojis('😻')" class="btn btn-sm">😻</button>
                                        <button type="button" onclick="emojis('😼')" class="btn btn-sm">😼</button>
                                        <button type="button" onclick="emojis('😽')" class="btn btn-sm">😽</button>
                                        <button type="button" onclick="emojis('😾')" class="btn btn-sm">😾</button>
                                        <button type="button" onclick="emojis('😿')" class="btn btn-sm">😿</button>
                                        <button type="button" onclick="emojis('🙀')" class="btn btn-sm">🙀</button>
                                        <button type="button" onclick="emojis('🙅')" class="btn btn-sm">🙅</button>
                                        <button type="button" onclick="emojis('🙆')" class="btn btn-sm">🙆</button>
                                        <button type="button" onclick="emojis('🙇')" class="btn btn-sm">🙇</button>
                                        <button type="button" onclick="emojis('🙈')" class="btn btn-sm">🙈</button>
                                        <button type="button" onclick="emojis('🙉')" class="btn btn-sm">🙉</button>
                                        <button type="button" onclick="emojis('🙊')" class="btn btn-sm">🙊</button>
                                        <button type="button" onclick="emojis('🙋')" class="btn btn-sm">🙋</button>
                                        <button type="button" onclick="emojis('🙌')" class="btn btn-sm">🙌</button>
                                        <button type="button" onclick="emojis('🙍')" class="btn btn-sm">🙍</button>
                                        <button type="button" onclick="emojis('🙎')" class="btn btn-sm">🙎</button>
                                        <button type="button" onclick="emojis('🙏')" class="btn btn-sm">🙏</button>
                                        <button type="button" onclick="emojis('🤝')" class="btn btn-sm">🤝</button>
                                        <button type="button" onclick="emojis('👀')" class="btn btn-sm">👀</button>
                                        <button type="button" onclick="emojis('👆')" class="btn btn-sm">👆</button>
                                        <button type="button" onclick="emojis('👇')" class="btn btn-sm">👇</button>
                                        <button type="button" onclick="emojis('👈')" class="btn btn-sm">👈</button>
                                        <button type="button" onclick="emojis('👉')" class="btn btn-sm">👉</button>
                                        <button type="button" onclick="emojis('👊')" class="btn btn-sm">👊</button>
                                        <button type="button" onclick="emojis('👋')" class="btn btn-sm">👋</button>
                                        <button type="button" onclick="emojis('👌')" class="btn btn-sm">👌</button>
                                        <button type="button" onclick="emojis('👍')" class="btn btn-sm">👍</button>
                                        <button type="button" onclick="emojis('👎')" class="btn btn-sm">👎</button>
                                        <button type="button" onclick="emojis('👏')" class="btn btn-sm">👏</button>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="custom-tabs-three-profile" role="tabpanel" aria-labelledby="custom-tabs-three-profile-tab">
                                    <button type="button" onclick="emojis('🚀')" class="btn btn-sm">🚀</button>
                                    <button type="button" onclick="emojis('🚃')" class="btn btn-sm">🚃</button>
                                    <button type="button" onclick="emojis('🚄')" class="btn btn-sm">🚄</button>
                                    <button type="button" onclick="emojis('🚅')" class="btn btn-sm">🚅</button>
                                    <button type="button" onclick="emojis('🚇')" class="btn btn-sm">🚇</button>
                                    <button type="button" onclick="emojis('🚉')" class="btn btn-sm">🚉</button>
                                    <button type="button" onclick="emojis('🚌')" class="btn btn-sm">🚌</button>
                                    <button type="button" onclick="emojis('🚏')" class="btn btn-sm">🚏</button>
                                    <button type="button" onclick="emojis('🚑')" class="btn btn-sm">🚑</button>
                                    <button type="button" onclick="emojis('🚒')" class="btn btn-sm">🚒</button>
                                    <button type="button" onclick="emojis('🚓')" class="btn btn-sm">🚓</button>
                                    <button type="button" onclick="emojis('🚕')" class="btn btn-sm">🚕</button>
                                    <button type="button" onclick="emojis('🚗')" class="btn btn-sm">🚗</button>
                                    <button type="button" onclick="emojis('🚙')" class="btn btn-sm">🚙</button>
                                    <button type="button" onclick="emojis('🚚')" class="btn btn-sm">🚚</button>
                                    <button type="button" onclick="emojis('🚤')" class="btn btn-sm">🚤</button>
                                    <button type="button" onclick="emojis('🚥')" class="btn btn-sm">🚥</button>
                                    <button type="button" onclick="emojis('🚧')" class="btn btn-sm">🚧</button>
                                    <button type="button" onclick="emojis('🚨')" class="btn btn-sm">🚨</button>
                                    <button type="button" onclick="emojis('🚩')" class="btn btn-sm">🚩</button>
                                    <button type="button" onclick="emojis('🚪')" class="btn btn-sm">🚪</button>
                                    <button type="button" onclick="emojis('🚫')" class="btn btn-sm">🚫</button>
                                    <button type="button" onclick="emojis('🚬')" class="btn btn-sm">🚬</button>
                                    <button type="button" onclick="emojis('🚭')" class="btn btn-sm">🚭</button>
                                    <button type="button" onclick="emojis('🚲')" class="btn btn-sm">🚲</button>
                                    <button type="button" onclick="emojis('🚶')" class="btn btn-sm">🚶</button>
                                    <button type="button" onclick="emojis('🚹')" class="btn btn-sm">🚹</button>
                                    <button type="button" onclick="emojis('🚺')" class="btn btn-sm">🚺</button>
                                    <button type="button" onclick="emojis('🚻')" class="btn btn-sm">🚻</button>
                                    <button type="button" onclick="emojis('🚼')" class="btn btn-sm">🚼</button>
                                    <button type="button" onclick="emojis('🚽')" class="btn btn-sm">🚽</button>
                                    <button type="button" onclick="emojis('🚾')" class="btn btn-sm">🚾</button>
                                    <button type="button" onclick="emojis('🛀')" class="btn btn-sm">🛀</button>
                                </div>
                                <div class="tab-pane fade" id="custom-tabs-three-messages" role="tabpanel" aria-labelledby="custom-tabs-three-messages-tab">
                                    <button type="button" onclick="emojis('🌊')" class="btn btn-sm">🌊</button>
                                    <button type="button" onclick="emojis('🌋')" class="btn btn-sm">🌋</button>
                                    <button type="button" onclick="emojis('🌌')" class="btn btn-sm">🌌</button>
                                    <button type="button" onclick="emojis('🌏')" class="btn btn-sm">🌏</button>
                                    <button type="button" onclick="emojis('🌑')" class="btn btn-sm">🌑</button>
                                    <button type="button" onclick="emojis('🌓')" class="btn btn-sm">🌓</button>
                                    <button type="button" onclick="emojis('🌔')" class="btn btn-sm">🌔</button>
                                    <button type="button" onclick="emojis('🌕')" class="btn btn-sm">🌕</button>
                                    <button type="button" onclick="emojis('🌙')" class="btn btn-sm">🌙</button>
                                    <button type="button" onclick="emojis('🌛')" class="btn btn-sm">🌛</button>
                                    <button type="button" onclick="emojis('🌟')" class="btn btn-sm">🌟</button>
                                    <button type="button" onclick="emojis('🌠')" class="btn btn-sm">🌠</button>
                                    <button type="button" onclick="emojis('🌰')" class="btn btn-sm">🌰</button>
                                    <button type="button" onclick="emojis('🌱')" class="btn btn-sm">🌱</button>
                                    <button type="button" onclick="emojis('🌴')" class="btn btn-sm">🌴</button>
                                    <button type="button" onclick="emojis('🌵')" class="btn btn-sm">🌵</button>
                                    <button type="button" onclick="emojis('🌷')" class="btn btn-sm">🌷</button>
                                    <button type="button" onclick="emojis('🌸')" class="btn btn-sm">🌸</button>
                                    <button type="button" onclick="emojis('🌹')" class="btn btn-sm">🌹</button>
                                    <button type="button" onclick="emojis('🌺')" class="btn btn-sm">🌺</button>
                                    <button type="button" onclick="emojis('🌻')" class="btn btn-sm">🌻</button>
                                    <button type="button" onclick="emojis('🌼')" class="btn btn-sm">🌼</button>
                                    <button type="button" onclick="emojis('🌽')" class="btn btn-sm">🌽</button>
                                    <button type="button" onclick="emojis('🌾')" class="btn btn-sm">🌾</button>
                                    <button type="button" onclick="emojis('🌿')" class="btn btn-sm">🌿</button>
                                    <button type="button" onclick="emojis('🍀')" class="btn btn-sm">🍀</button>
                                    <button type="button" onclick="emojis('🍁')" class="btn btn-sm">🍁</button>
                                    <button type="button" onclick="emojis('🍂')" class="btn btn-sm">🍂</button>
                                    <button type="button" onclick="emojis('🍃')" class="btn btn-sm">🍃</button>
                                    <button type="button" onclick="emojis('🍄')" class="btn btn-sm">🍄</button>
                                </div>
                                <div class="tab-pane fade" id="custom-tabs-three-settings" role="tabpanel" aria-labelledby="custom-tabs-three-settings-tab">
                                    <button type="button" onclick="emojis('🍅')" class="btn btn-sm">🍅</button>
                                    <button type="button" onclick="emojis('🍆')" class="btn btn-sm">🍆</button>
                                    <button type="button" onclick="emojis('🍇')" class="btn btn-sm">🍇</button>
                                    <button type="button" onclick="emojis('🍈')" class="btn btn-sm">🍈</button>
                                    <button type="button" onclick="emojis('🍉')" class="btn btn-sm">🍉</button>
                                    <button type="button" onclick="emojis('🍊')" class="btn btn-sm">🍊</button>
                                    <button type="button" onclick="emojis('🍌')" class="btn btn-sm">🍌</button>
                                    <button type="button" onclick="emojis('🍍')" class="btn btn-sm">🍍</button>
                                    <button type="button" onclick="emojis('🍎')" class="btn btn-sm">🍎</button>
                                    <button type="button" onclick="emojis('🍏')" class="btn btn-sm">🍏</button>
                                    <button type="button" onclick="emojis('🍑')" class="btn btn-sm">🍑</button>
                                    <button type="button" onclick="emojis('🍒')" class="btn btn-sm">🍒</button>
                                    <button type="button" onclick="emojis('🍓')" class="btn btn-sm">🍓</button>
                                    <button type="button" onclick="emojis('🍔')" class="btn btn-sm">🍔</button>
                                    <button type="button" onclick="emojis('🍕')" class="btn btn-sm">🍕</button>
                                    <button type="button" onclick="emojis('🍖')" class="btn btn-sm">🍖</button>
                                    <button type="button" onclick="emojis('🍗')" class="btn btn-sm">🍗</button>
                                    <button type="button" onclick="emojis('🍘')" class="btn btn-sm">🍘</button>
                                    <button type="button" onclick="emojis('🍙')" class="btn btn-sm">🍙</button>
                                    <button type="button" onclick="emojis('🍚')" class="btn btn-sm">🍚</button>
                                    <button type="button" onclick="emojis('🍛')" class="btn btn-sm">🍛</button>
                                    <button type="button" onclick="emojis('🍜')" class="btn btn-sm">🍜</button>
                                    <button type="button" onclick="emojis('🍝')" class="btn btn-sm">🍝</button>
                                    <button type="button" onclick="emojis('🍞')" class="btn btn-sm">🍞</button>
                                    <button type="button" onclick="emojis('🍟')" class="btn btn-sm">🍟</button>
                                    <button type="button" onclick="emojis('🍠')" class="btn btn-sm">🍠</button>
                                    <button type="button" onclick="emojis('🍡')" class="btn btn-sm">🍡</button>
                                    <button type="button" onclick="emojis('🍢')" class="btn btn-sm">🍢</button>
                                    <button type="button" onclick="emojis('🍺')" class="btn btn-sm">🍣</button>
                                    <button type="button" onclick="emojis('🍤')" class="btn btn-sm">🍤</button>
                                    <button type="button" onclick="emojis('🍥')" class="btn btn-sm">🍥</button>
                                    <button type="button" onclick="emojis('🍦')" class="btn btn-sm">🍦</button>
                                    <button type="button" onclick="emojis('🍧')" class="btn btn-sm">🍧</button>
                                    <button type="button" onclick="emojis('🍨')" class="btn btn-sm">🍨</button>
                                    <button type="button" onclick="emojis('🍩')" class="btn btn-sm">🍩</button>
                                    <button type="button" onclick="emojis('🍪')" class="btn btn-sm">🍪</button>
                                    <button type="button" onclick="emojis('🍫')" class="btn btn-sm">🍫</button>
                                    <button type="button" onclick="emojis('🍬')" class="btn btn-sm">🍬</button>
                                    <button type="button" onclick="emojis('🍭')" class="btn btn-sm">🍭</button>
                                    <button type="button" onclick="emojis('🍮')" class="btn btn-sm">🍮</button>
                                    <button type="button" onclick="emojis('🍯')" class="btn btn-sm">🍯</button>
                                    <button type="button" onclick="emojis('🍰')" class="btn btn-sm">🍰</button>
                                    <button type="button" onclick="emojis('🍱')" class="btn btn-sm">🍱</button>
                                    <button type="button" onclick="emojis('🍲')" class="btn btn-sm">🍲</button>
                                    <button type="button" onclick="emojis('🍳')" class="btn btn-sm">🍳</button>
                                    <button type="button" onclick="emojis('🍴')" class="btn btn-sm">🍴</button>
                                    <button type="button" onclick="emojis('🍵')" class="btn btn-sm">🍵</button>
                                    <button type="button" onclick="emojis('🍶')" class="btn btn-sm">🍶</button>
                                    <button type="button" onclick="emojis('🍷')" class="btn btn-sm">🍷</button>
                                    <button type="button" onclick="emojis('🍸')" class="btn btn-sm">🍸</button>
                                    <button type="button" onclick="emojis('🍹')" class="btn btn-sm">🍹</button>
                                    <button type="button" onclick="emojis('🍺')" class="btn btn-sm">🍺</button>
                                    <button type="button" onclick="emojis('🍻')" class="btn btn-sm">🍻</button>
                                </div>
                                <div class="tab-pane fade" id="custom-tabs-four-settings" role="tabpanel" aria-labelledby="custom-tabs-three-settings-tab">
                                    <button type="button" onclick="emojis('🎯')" class="btn btn-sm">🎯</button>
                                    <button type="button" onclick="emojis('📢')" class="btn btn-sm">📢</button>
                                    <button type="button" onclick="emojis('💡')" class="btn btn-sm">💡</button>
                                    <button type="button" onclick="emojis('💣')" class="btn btn-sm">💣</button>
                                    <button type="button" onclick="emojis('💬')" class="btn btn-sm">💬</button>
                                    <button type="button" onclick="emojis('💻')" class="btn btn-sm">💻</button>
                                    <button type="button" onclick="emojis('📌')" class="btn btn-sm">📌</button>
                                </div>
                            </div>
                        </div>
                        <!-- /.card -->

                    </div>
                </div>
                <div id="fgMensagem">
                    <div class="form-group">
                        <div class="row">
                            <div class="col-6">
                                <label style="margin-top:5px;">Mensagem</label>
                            </div>
                            <div style="text-align: right; padding-top: 15px" class="col-6">
                                <button id="btnEmojis" class="btn btn-sm">😃</button>
                            </div>
                        </div>
                        <textarea id="taMensagem" class="form-control" rows="5" placeholder="Digite aqui a mensagem que deseja enviar nessa campanha." maxlength="1000"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="row">
                    <div class="col-12 text-left">
                        <button style="visibility:visible; margin:5px" type="button" class="btn btn-light" name="btnCancelar" id="btnCancelar" data-dismiss="modal">CANCELAR</button>
                        <button disabled style="visibility:visible; margin:5px" type="button" class="btn btn-warning" name="btnSalvar" id="btnSalvar">SALVAR</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->

<!-- M O D A L  R E L A T O R I O -->
<div class="modal fade" id="modalRelatorio">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div id="overlayRelatorio" style="background-color: white;" class="overlay justify-content-center align-items-center" hidden>
                <!--d-flex-->
                <i style="color:#00A599" class="fas fa-2x fa-sync fa-spin"></i>
            </div>
            <div class="modal-header">
                <h5 style="margin-left: 10px"><strong id="strongTitulo"></strong></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div style="background-color: #f4f6f9;" class="modal-body">
                <div class="card">
                    <div class="card-body">
                        <h7><strong>Lista: </strong>
                            <font id="fLista"></font>
                        </h7><br>
                        <h7><strong>Contatos: </strong>
                            <font id="fContatos"></font><strong style="margin-left: 5px;">Status: </strong><span id="spanStatus" style="font-size: 12px; border-radius: 5px; font-weight: 100; font-weight: 700" class="badge"></span>
                        </h7>
                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->
                <div class="row">
                    <div class="col-12 col-sm-12 col-md-4">
                        <div class="info-box">
                            <span style="max-width: 50px;" class="info-box-icon bg-success elevation-1"><i class="fas fa-eye"></i></span>

                            <div class="info-box-content">
                                <span class="info-box-text">Recebidas</span>
                                <span id="spanRecebidas" class="info-box-number">

                                </span>
                            </div>
                            <!-- /.info-box-content -->
                        </div>
                        <!-- /.info-box -->
                    </div>
                    <div class="col-12 col-sm-12 col-md-4">
                        <div class="info-box">
                            <span style="max-width: 50px;" class="info-box-icon bg-light elevation-1"><i class="fas fa-comment-slash"></i></span>

                            <div class="info-box-content">
                                <span class="info-box-text">Inativo</span>
                                <span id="spanInativos" class="info-box-number">
                                </span>
                            </div>
                            <!-- /.info-box-content -->
                        </div>
                        <!-- /.info-box -->
                    </div>
                    <div class="col-12 col-sm-12 col-md-4">
                        <div class="info-box">
                            <span style="max-width: 50px;" class="info-box-icon bg-secondary elevation-1"><i class="fas fa-comment-slash"></i></span>

                            <div class="info-box-content">
                                <span class="info-box-text">Não verificado</span>
                                <span id="spanNaoVerificado" class="info-box-number">
                                </span>
                            </div>
                            <!-- /.info-box-content -->
                        </div>
                        <!-- /.info-box -->
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Resumo de envio</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="donutChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->
                <div id="divTabela" class="col-sm-12" hidden>
                    <div style="width: 100%;" class="card">
                        <div id="overlayContatos" style="background-color: white;" class="overlay justify-content-center align-items-center" hidden>
                            <!--d-flex-->
                            <i style="color:#00A599" class="fas fa-2x fa-sync fa-spin"></i>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body">
                            <table id="tableContatos" class="table table-bordered table-striped">
                                <thead style="font-size:small;">
                                    <tr>
                                        <th>

                                        </th>
                                        <th>
                                            Telefone
                                        </th>
                                        <th>
                                            Data
                                        </th>
                                        <th>
                                            Status
                                        </th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12 text-center">
                    <a id="btnVisualizarContatos" style="padding: 10px;" class="btn btn-warning">
                        Visualizar detalhamento por contatos
                    </a>
                </div>

            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->

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

<script src="js/campanhas.js"></script>