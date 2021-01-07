$(function () {

    consultarCreditos()

    $("#tableCreditos").DataTable({
        "paging": true,
        "lengthChange": false,
        "searching": true,
        "ordering": false,
        "info": true,
        "autoWidth": false,
        "responsive": true,
        //"dom": '<"top"f>rt<"bottom"ilp><"clear">',
        "dom": '<f<t><"row"<"col-6"i><"col-6"p>>>',
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

    $("#tableExtrato").DataTable({
        "paging": true,
        "lengthChange": false,
        "scrollY": 300,
        "searching": false,
        "ordering": false,
        "info": false,
        "autoWidth": true,
        "responsive": true,
        //"dom": '<"top"f>rt<"bottom"ilp><"clear">',
        //"dom": '<f<t><"row"<"col-6"i><"col-12"p>>>',
        "dom": '<<t><"row"<"col-12"p>>>',
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

    //consultarCreditosEmpresas()
    //Formatação  da tabela
})

function consultarCreditos(){
    $.ajax({
        url: 'ajaxs/creditosAjax.php',
        type: 'POST',
        dataType: 'json',
        data: {
            acao: 'consultaExtrato',
            dados: {
                idEmpresa: idEmpresa
            }
        },
        beforeSend: function () {
            console.log('Consultando créditos')
            $("#overlayExtrato").removeData('hidden');
            $("#overlayExtrato").addClass('d-flex');
        },
        success: function (content) {
            console.log('Consultado créditos')

            $("#overlayExtrato").add('hidden')
            $("#overlayExtrato").removeClass('d-flex')

            console.log(content)

            
            document.getElementById('strongCreditos').innerHTML = content['consolidado'][0]['disponivel']
        }
    })
}


$('#btnSalvar').on('click', function () {

    if ($('#inputCreditos').val() == '') {
        notify('error', 'É necessário inserir uma quantidade de créditos.')
    } else if ($('#taDescricao').val() == '') {
        notify('error', 'É necessário inserir uma descrição para efetuar a recarga.')
    } else {
        $.ajax({
            url: 'ajaxs/creditosAjax.php',
            type: 'POST',
            dataType: 'json',
            data: {
                acao: 'salvarCredito',
                dados: {
                    idEmpresa: window.idEmpresa,
                    idUsuario: window.idUsuario,
                    valor: $('#inputCreditos').val(),
                    descricao: $('#taDescricao').val()
                }
            },
            beforeSend: function () {
                console.log('Fazendo recarga')
                $("#overlayRecarga").removeData('hidden');
                $("#overlayRecarga").addClass('d-flex');
            },
            success: function (content) {
                console.log('Finalizada recarga')

                $("#overlayRecarga").add('hidden')
                $("#overlayRecarga").removeClass('d-flex')

                console.log(content)

                if (content == 1) {
                    notify('success', 'Recarga realizada com sucesso!')
                    window.alteracao = 1
                    $("#modalAdicionar").modal('hide')

                } else {
                    notify('error', 'Não foi possível realizar a recarga')
                }
            }
        })
    }
})

$('#modalExtrato').on('show.bs.modal', function (e) {
    var button = $(e.relatedTarget) // Button that triggered the modal
    //Variáveis recebendo os dados do botão
    var idEmpresa = button.data('idempresa')

    consultarExtrato(idEmpresa)

})

$('#modalExtrato').on('hidden.bs.modal', function (e) {
    var table = $('#tableExtrato').DataTable()
    table.clear()

})

$('#modalAdicionar').on('show.bs.modal', function (e) {
    var button = $(e.relatedTarget) // Button that triggered the modal
    //Variáveis recebendo os dados do botão
    window.idEmpresa = button.data('idempresa')

    window.alteracao = 0
    consultarExtrato(idEmpresa)
})

$('#modalAdicionar').on('hidden.bs.modal', function (e) {
    if (window.alteracao == 1) {
        activeMenu('#btnCreditos')
        tela('creditos.php')
    }

})

function consultarExtrato(idEmpresa) {
    $.ajax({
        url: 'ajaxs/creditosAjax.php',
        type: 'POST',
        dataType: 'json',
        data: {
            acao: 'consultaExtrato',
            dados: {
                idEmpresa: idEmpresa
            }
        },
        beforeSend: function () {
            console.log('Consultando extrato')
            $("#overlayExtrato").removeData('hidden');
            $("#overlayExtrato").addClass('d-flex');
        },
        success: function (content) {
            console.log('Consultado extrato')

            $("#overlayExtrato").add('hidden')
            $("#overlayExtrato").removeClass('d-flex')

            console.log(content)

            $conteudo = ''
            for (var i = 0; i < content['dados'].length; i++) {
                switch (content['dados'][i]['status']) {
                    case '1':
                        var textoStatus = 'Processado'
                        var corStatus = 'info'
                        break;
                    case '2':
                        var textoStatus = 'Pendente'
                        var corStatus = 'light'
                        break;
                    default:
                        break;
                }
                moment.locale('pt-br')
                var data = moment(content['dados'][i]['create_at']).format('DD/MM/YYYY H:mm')
                if (data == 'Invalid date') {
                    data = "";
                }
                var creditos = content['dados'][i]['valor']
                if (parseInt(creditos) < 0) {
                    if (content['dados'][i]['status'] == 2) {
                        var corLinha = '#bababa'
                    } else {
                        var corLinha = '#dc3545'
                    }
                } else {
                    var corLinha = '#28a745'
                }

                if (content['dados'][i]['referencia'] == 0) {
                    $referencia = 'Recarga'
                } else {
                    $referencia = content['dados'][i]['referencia']
                }

                var table = $('#tableExtrato').DataTable()

                table.row.add([
                    $referencia, data, '<strong style="color: ' + corLinha + '">' + creditos + '</strong>'
                ]).draw(false)

                table.page(0).draw('page')
            }

            document.getElementById('spanReal').innerHTML = content['consolidado'][0]['real']
            document.getElementById('spanPendentes').innerHTML = content['consolidado'][0]['pendente']
            document.getElementById('spanDisponivel').innerHTML = content['consolidado'][0]['disponivel']
        }
    })
}

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