<?php
header("Content-Type: text/html; charset=UTF-8", true);
//include ("dados_conexao.php");



//
// ─── BOTAO CADASTRAR ────────────────────────────────────────────────────────────
//  
if (isset($_POST['btn-cadastrar'])) {
    
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
                <p>Para fornecer o melhor da plataforma, o cadastro de novas empresas é controlado. <br>Deixe abaixo os dados da sua empresa, nossa equipe comercial entrará em contato o mais rápido possível.</p>
                <form role="form" data-toggle="validator" method="POST">
                    <div class="form-group mb-3">
                        <input type="text" class="form-control" name="nomeEmpresa" id="nomeEmpresa" placeholder="Empresa" style="text-transform: uppercase;" required>
                    </div>
                    <div class="form-group mb-3">
                        <input type="text" class="form-control" name="nome" id="nome" placeholder="Seu nome" style="text-transform: uppercase;" required>
                    </div>
                    <div class="form-group mb-3">
                        <input type="email" class="form-control" name="email" id="email" placeholder="E-mail" required>
                    </div>
                    <div class="form-group">
                        <input type="text" class="form-control" name="telefone" id="telefone" placeholder="Telefone" value="">
                    </div>
                </form>
                <div class="row">
                    <div class="col-7">
                    </div>
                    <!-- /.col -->
                    <div class="col-5">
                        <button style="background-color: #000; border-color: #000;" type="submit" class="btn btn-primary btn-block" name="btn-salvar" id="btnSalvarSolicitacao"><b>SALVAR</b></button>
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

    <script>
        $(function() {
            //Money Euro
            $('#telefone').inputmask({
                mask: "+55 (99) 9 9999-9999"
            });
        })


        $("#btnSalvarSolicitacao").click(function() {
            console.log("Entrou na função")
            //Inputs
            var nomeEmpresa = $("#nomeEmpresa").val()
            var nome = $("#nome").val()
            var email = $("#email").val()
            var telefone = $("#telefone").val()
            console.log(nomeEmpresa)
            $.ajax({
                url: 'ajaxs/usuariosAjax.php',
                type: 'POST',
                dataType: 'html',
                data: {
                    acao: "createNovo",
                    dados: {
                        nomeEmpresa: nomeEmpresa,
                        nome: nome,
                        email: email,
                        telefone: telefone
                    }
                },
                beforeSend: function() {
                    console.log("Aguardando Cadastro")
                    $("#overlay").removeData('hidden')
                    $("#overlay").addClass('d-flex')
                },
                success: function(content) {
                    console.log("Finalizou cadastro")
                    console.log(content)
                    $("#overlay").add('hidden')
                    //$("#overlay").removeClass('d-flex')

                    if (content == "1") {
                        toastr.success('Solicitação salva com sucesso! Em breve entraremos em contato.', '', {
                            timeOut: 2000,
                            positionClass: 'toast-bottom-right',
                            progressBar: true
                        })
                        
                    } else {
                        //!Colocar Alert de Não foi possível
                        console.log(content)
                        toastr.error('Não foi possível possível realizar a ação.', '', {
                            timeOut: 2000,
                            positionClass: 'toast-bottom-right',
                            progressBar: true
                        })

                    }
                }
            })
        })
        toastr.options.onHidden = function() { 
            window.location.href = "index.php"
         }
         toastr.options.onCloseClick = function() { 
            window.location.href = "index.php"
          }
    </script>
</body>

</html>