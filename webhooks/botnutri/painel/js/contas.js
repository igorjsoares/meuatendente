$(function () {

    $("#tableContas").DataTable({
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
})

$(document).ready(function () {
    bsCustomFileInput.init();
});

$('#btnSalvar').on('click', function () {

    console.log("Entrou em salvar conta")
    var file_data = $('#arquivoProfile').prop('files')[0];
    var form_data = new FormData();
    form_data.append('file', file_data);
    form_data.append('acao', 'salvarConta');
    form_data.append('idEmpresa', window.idEmpresa);
    form_data.append('idUsuario', window.idUsuario);
    form_data.append('nome', $('#inputNome').val());

    $.ajax({
        url: 'ajaxs/contasAjax.php',
        dataType: 'json', // what to expect back from the PHP script, if anything
        cache: false,
        contentType: false,
        processData: false,
        type: 'POST',
        data: form_data,
        beforeSend: function () {
            $("#overlayConta").removeData('hidden')
            $("#overlayConta").addClass('d-flex')

            console.log('Carregando')
        },
        success: function (content) {
            $("#overlayConta").add('hidden')
            $("#overlayConta").removeClass('d-flex')
            console.log('Finalizado carregamento')

            console.log(content)
            if (content[0]['result'] == 1) {
                notify('success', 'Conta inserida com sucesso!')
                window.alteracao = 1
                $("#modalConta").modal('hide')
            } else {
                notify('error', 'Não foi possível inserir a campanha')
            }
        }
    });
})

/* $("#inputNome").keyup(function () {
    document.getElementById('fNome').innerHTML = $("#inputNome").val()
}); */

$('#btnSimAlert').on('click', function () {
    console.log('Antes do ajax: ' + window.idConta)
    $.ajax({
        url: 'ajaxs/contasAjax.php',
        type: 'POST',
        dataType: 'html',
        data: {
            acao: 'excluirConta',
            dados: {
                idConta: window.idConta
            }
        },
        beforeSend: function () {
            $("#overlayAlert").removeData('hidden');
            $("#overlayAlert").addClass('d-flex');

            console.log('Excluindo conta')
        },
        success: function (resultado) {
            $("#overlayAlert").add('hidden')
            $("#overlayAlert").removeClass('d-flex')
            console.log('Finalizado exclusão')

            if (resultado == 1) {
                window.alteracao = 1
                $("#modalAlert").modal('hide')
                notify('success', 'Conta de perfil excluída com sucesso!')
            } else {
                notify('error', 'Não foi possível excluir.')
            }
        }
    })
})

$('#modalConta').on('show.bs.modal', function (e) {
    var button = $(e.relatedTarget) // Button that triggered the modal
    //Variáveis recebendo os dados do botão
    var idConta = button.data('idconta')

})

$('#modalConta').on('hidden.bs.modal', function (e) {
    if (window.alteracao == 1) {
        activeMenu('#btnContas')
        tela('contas.php')
    }
})

$('#modalAlert').on('show.bs.modal', function (e) {
    var button = $(e.relatedTarget) // Button that triggered the modal
    //Variáveis recebendo os dados do botão
    window.idConta = button.data('idconta')
    console.log(window.idConta)
    window.alteracao = 0

})

$('#modalAlert').on('hidden.bs.modal', function (e) {
    if (window.alteracao == 1) {
        activeMenu('#btnContas')
        tela('contas.php')
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