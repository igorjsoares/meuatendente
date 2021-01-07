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

  $id_usuario = $_SESSION['NP_id_usuario'];
$id_empresa = $_SESSION['NP_id_empresa'];
$perfil = $_SESSION['NP_perfil_usuario'];
echo "<script>window.idEmpresa = " . $id_empresa . "</script>";



//?Busca informações principais do banco de dados
if ($perfil == 'Administrador' || $perfil == 'MASTER') {
    $sql = "SELECT * FROM tbl_usuarios WHERE id_empresa = $id_empresa AND status != 9";
} else {
    $sql = "SELECT * FROM tbl_usuarios WHERE id_usuario = $id_usuario AND status != 9";
}
$query = mysqli_query($conn['link'], $sql);
$numRow = mysqli_num_rows($query);
if (!$query) {
    echo 'Não é possível acessar no momento';
}
if ($numRow == 0) { //Não foi encontrado nada
    echo "Nenhum usuário encontrado.";
} else {
?>

    <!-- Content Wrapper. Contains page content -->
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Usuários</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a id="btnNovoUsuario" style="padding: 10px;" class="btn btn-warning" data-toggle="modal" data-target="#modalUsuario">
                        <i class="fas fa-plus">
                        </i>
                        Novo
                    </a>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">

                    <table id="tableEmpresas" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th style="width: 29%">
                                    Nome
                                </th>
                                <th style="width: 20%">
                                    E-mail
                                </th>
                                <th class="text-center">
                                    Telefone
                                </th>
                                <th style="width: 11%" class="text-center">
                                    Perfil
                                </th>
                                <th style="width: 9%" class="text-center">
                                    Status
                                </th>
                                <th style="width: 8%">
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            while ($usuario = mysqli_fetch_array($query)) {
                                if ($usuario['status'] == 0) {
                                    $status = 'Inativo';
                                    $cor_status = 'danger';
                                } else {
                                    $status = 'Ativo';
                                    $cor_status = 'success';
                                }
                                if ($usuario['perfil'] == "Administrador") {
                                    $perfilIco = 'fas fa-user-tie';
                                } else if ($usuario['perfil'] == "Usuário") {
                                    $perfilIco = 'fas fa-user';
                                }

                            ?>
                                <tr>
                                    <td>
                                        <?= $usuario['nome'] ?>
                                    </td>
                                    <td>
                                        <?= $usuario['email'] ?>
                                    </td>
                                    <td class="text-center">
                                        <?= $usuario['telefone'] ?>
                                    </td>
                                    <td class="text-center">
                                        <i style="font-size: 25px; color: #00A599" class="<?= $perfilIco ?>" title="<?= $usuario['perfil'] ?>"></i>
                                    </td>
                                    <td class="project-state" class="text-center">
                                        <span class="badge badge-<?= $cor_status ?>"><?= $status ?></span>
                                    </td>
                                    <td class="project-actions text-right">
                                        <a class="btn btn-default btn-sm" data-toggle="modal" data-target="#modalUsuario" data-tipo="edit" data-idusuario="<?= $usuario['id_usuario'] ?>" data-nome="<?= $usuario['nome'] ?>" data-email="<?= $usuario['email'] ?>" data-telefone="<?= $usuario['telefone'] ?>" data-cpf="<?= $usuario['cpf'] ?>" data-status="<?= $usuario['status'] ?>" data-perfil="<?= $usuario['perfil'] ?>">
                                            <i class="fas fa-pencil-alt">
                                            </i>
                                            Editar
                                        </a>
                                    </td>
                                </tr>
                            <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
    <!-- /.content -->
<?php
}
?>


<!-- M O D A L  N O V O  U S U A R I O -->
<div class="modal fade" id="modalUsuario">
    <div class="modal-dialog">
        <div class="modal-content">

            <div id="overlay" style="background-color: white;" class="overlay justify-content-center align-items-center" hidden>
                <!--d-flex-->
                <i style="color:#00A599" class="fas fa-2x fa-sync fa-spin"></i>
            </div>

            <div class="modal-header">
                <h4 class="modal-title">Novo usuário</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

                <form role="form" id="formUsuarios">
                    <div class="card-body">
                        <div class="form-group">
                            <label for="telefone">Nome</label>
                            <input type="text" class="form-control" name="nome" id="nome" placeholder="" style="text-transform: uppercase;">
                        </div>
                        <div class="form-group">
                            <label for="cpf">CPF</label>
                            <input type="text" class="form-control" name="cpf" id="cpf" placeholder="" data-inputmask='"mask": "999.999.999-99"' data-mask maxlength="14">
                        </div>
                        <div class="form-group">
                            <label for="email">E-mail</label>
                            <input type="email" class="form-control" name="email" id="email" placeholder="" style="text-transform:lowercase;">
                        </div>
                        <div class="form-group">
                            <label for="telefone">Telefone <font style="font-size: 10px">(Com Whatsapp)</font></label>
                            <input type="text" class="form-control" name="telefone" id="telefone" placeholder="" value="">
                        </div>

                        <!-- select -->
                        <div class="form-group">
                            <label>Perfil</label>
                            <select class="form-control" name="perfil" id="perfil">
                                <option value="Administrador">Administrador</option>
                                <option value="Usuário">Usuário</option>
                            </select>
                        </div>



                    </div>
                    <!-- /.card-body -->
                    <div class="modal-footer">
                        <div class="row">
                            <div class="col-4 text-left">
                                <button style="visibility:hidden;" type="button" class="btn btn-danger" name="btn-excluir" id="btn-excluir">EXCLUIR</button>
                            </div>
                            <div class="col-4 text-center">
                                <button style="visibility:visible;" type="button" class="btn btn-default" name="btn-bloquear" id="btn-bloquear">BLOQUEAR</button>
                            </div>
                            <div class="col-4 text-right">
                                <button type="submit" class="btn btn-warning" name="btn-salvar" id="btn-salvar">SALVAR</button>
                            </div>
                        </div>
                    </div>
                </form>

            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->


<!-- M O D A L  A L E R T -->
<div class="modal fade" id="modalAlert">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body">
                <div id="contentAlert">
                </div>
            </div>
            <div class="modal-footer">
                <div class="row">
                    <div class="col-6 text-left">
                        <button style="visibility:visible;" type="button" class="btn btn-danger" name="btn-cancelar" id="btn-cancelar" data-dismiss="modal">CANCELAR</button>
                    </div>
                    <div class="col-6 text-right">
                        <button type="submit" class="btn btn-warning" name="btn-sim" id="btn-sim">SIM</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->

<!-- DataTables -->
<script src="plugins/datatables/jquery.dataTables.min.js"></script>
<script src="plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>

<script>
    $(function() {
        //Money Euro
        $('#telefone').inputmask({
            mask: "+55 (99) 9 9999-9999"
        });
        $('[data-mask]').inputmask()

        $("#tableEmpresas").DataTable({
            "paging": true,
            "lengthChange": false,
            "searching": true,
            "ordering": false,
            "info": true,
            "autoWidth": false,
            "responsive": true,
            //"dom": '<"top"f>rt<"bottom"ilp><"clear">',
            "dom": '<"LEITOS"f<t><"row"<"col-6"i><"col-6"p>>>',
            "language": {
                "decimal": "",
                "emptyTable": "Nenhum dado foi carregado",
                "info": "De _START_ a _END_ de _TOTAL_ registros",
                "infoEmpty": "De 0 a 0 de 0 registros",
                "infoFiltered": "(filtrado de _MAX_ totais de registros)",
                "infoPostFix": "",
                "thousands": ",",
                "lengthMenu": "Mostrar _MENU_ registros",
                "loadingRecords": "Carregando...",
                "processing": "Processando...",
                "search": "Buscar:",
                "zeroRecords": "Nenhum registro correspondente encontrado",
                "paginate": {
                    "first": "Primeiro",
                    "last": "Último",
                    "next": "Próximo",
                    "previous": "Anterior"
                },
                "aria": {
                    "sortAscending": ": ativar para classificar a coluna crescente",
                    "sortDescending": ": ativar para classificar a coluna descrescente"
                }
            }
        })
    })

    $("#formUsuarios").validate({
        rules: {
            nome: {
                required: true,
                minlength: 5
            },
            email: {
                required: true,
                minlength: 4
            },
            cpf: {
                required: true,
                minlength: 14
            }
        },
        messages: {
            nome: {
                required: "Obrigatório",
                minlength: "Mínimo de 5 letras"
            },
            email: {
                required: "Obrigatório",
                minlength: "Mínimo de 4 caracteres"
            },
            cpf: {
                required: "Obrigatório",
                minlength: "Não está completo"
            }
        },
        submitHandler: function(form) {

            if (window.tipo == "edit") {
                acaoAtual = 'updateUsuario'
                idUsuario = window.idUsuario
            } else {
                acaoAtual = 'createUsuario'
                idUsuario = ""
            }

            $.ajax({
                url: 'ajaxs/usuariosAjax.php',
                type: 'POST',
                dataType: 'html',
                data: {
                    acao: acaoAtual,
                    dados: {
                        idUsuario: idUsuario,
                        idEmpresa: idEmpresa,
                        nome: $("#nome").val(),
                        email: $("#email").val(),
                        telefone: $("#telefone").val(),
                        perfil: $("#perfil").val(),
                        cpf: $("#cpf").val()
                    }
                },
                beforeSend: function() {
                    $("#overlay").removeData('hidden');
                    $("#overlay").addClass('d-flex');
                },
                success: function(content) {
                    $("#overlay").add('hidden')
                    $("#overlay").removeClass('d-flex')

                    if (content == "1") {

                        window.alteracao = 1
                        $("#modalUsuario").modal('hide')
                        notify('success', 'Ação realizada com sucesso!')
                    } else {
                        console.log(content)
                        notify('error', 'Não foi possível possível realizar a ação.')

                    }
                }
            })
        }
    });

    $('#btn-bloquear').click(function() {
        window.acao = 'bloquearUsuario'
        //!Colocar o alert de confirmação
        $('#modalAlert').modal('show')
        if (window.status == 1) {
            nomeStatus = 'bloquear'
        } else {
            nomeStatus = 'desbloquear'
        }
        document.getElementById("contentAlert").innerHTML = "Você tem certeza que deseja " + nomeStatus + " esse usuário?"
    })
    $('#btn-excluir').click(function() {
        window.acao = 'excluirUsuario'
        //!Colocar o alert de confirmação
        $('#modalAlert').modal('show')
        document.getElementById("contentAlert").innerHTML = "Você tem certeza que deseja excluir esse usuário?"
    })

    $('#btn-sim').click(function() {
        $("#modalAlert").modal('hide')
        if (window.status == 1) {
            tipoBloqueio = 0
        } else {
            tipoBloqueio = 1
        }

        $.ajax({
            url: 'ajaxs/usuariosAjax.php',
            type: 'POST',
            dataType: 'html',
            data: {
                acao: window.acao,
                dados: {
                    idUsuario: idUsuario,
                    tipoBloqueio: tipoBloqueio
                }
            },
            beforeSend: function() {
                $("#overlay").removeData('hidden');
                $("#overlay").addClass('d-flex');
            },
            success: function(content) {
                $("#overlay").add('hidden')
                $("#overlay").removeClass('d-flex')

                if (content == "1") {
                    //Fecha o modal
                    window.alteracao = 1
                    $("#modalUsuario").modal('hide')

                    notify('success', 'Ação realizada com sucesso!')
                } else {
                    //!Colocar Alert de Não foi possível
                    console.log(content)
                    notify('error', 'Não foi possível possível realizar a ação.')

                }
            }
        })
    })



    $('#modalUsuario').on('show.bs.modal', function(e) {
        var button = $(e.relatedTarget) // Button that triggered the modal
        window.tipo = button.data('tipo')
        window.idUsuario = button.data('idusuario')
        var nome = button.data('nome')
        var email = button.data('email')
        var telefone = button.data('telefone')
        var perfil = button.data('perfil')
        var cpf = button.data('cpf')
        window.status = button.data('status')
        if (window.status == 0) {
            textoBloquear = "Desbloquear"
        } else {
            textoBloquear = "Bloquear"
        }

        if (window.tipo == "edit") {
            document.getElementById("nome").value = nome;
            document.getElementById("email").value = email;
            document.getElementById("telefone").value = telefone;
            document.getElementById("cpf").value = cpf;
            document.getElementById("nome").disabled = true;
            document.getElementById("email").disabled = true;
            document.getElementById("cpf").disabled = true;

            $('#perfil option:contains(' + perfil + ')').prop({
                selected: true
            });
            document.getElementById("btn-bloquear").innerHTML = textoBloquear
            document.getElementById("btn-bloquear").style.visibility = "visible"
            document.getElementById("btn-excluir").style.visibility = "visible"

        } else {
            document.getElementById("nome").value = ''
            document.getElementById("email").value = ''
            document.getElementById("telefone").value = ''
            document.getElementById("cpf").value = ''
            document.getElementById("nome").disabled = false;
            document.getElementById("email").disabled = false;
            document.getElementById("cpf").disabled = false;

            document.getElementById("btn-bloquear").style.visibility = "hidden"
            document.getElementById("btn-excluir").style.visibility = "hidden"
        }
    })

    $('#modalUsuario').on('shown.bs.modal', function(e) {
        var button = $(e.relatedTarget) // Button that triggered the modal
        var tipo = button.data('tipo')
        window.alteracao = 0

        if (tipo != "edit") {
            document.getElementById("nome").focus()
        }
    })

    $('#modalUsuario').on('hidden.bs.modal', function(e) {
        if (window.alteracao == 1) {
            activeMenu('#btn-usuarios')
            tela('usuarios.php')
        }
    })

    function notify(alert, alert_message) {
        if (alert == 'success') {
            toastr.success(alert_message, '', {
                timeOut: 2000,
                positionClass: 'toast-bottom-right',
                progressBar: true
            })
        }
        if (alert == 'error') {
            toastr.error(alert_message, '', {
                timeOut: 2000,
                positionClass: 'toast-bottom-right',
                progressBar: true
            })
        }
    }
</script>