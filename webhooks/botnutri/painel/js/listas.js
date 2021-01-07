$(function () {
    $.ajax({
        url: 'ajaxs/listasAjax.php',
        type: 'POST',
        dataType: 'json',
        data: {
            acao: 'consultaListas',
            dados: {
                idEmpresa: window.idEmpresa
            }
        },
        beforeSend: function () {
            console.log('Consultando listas')
            $("#overlayTabela").removeData('hidden');
            $("#overlayTabela").addClass('d-flex');
        },
        success: function (content) {
            console.log('Consultado listas')

            $("#overlayTabela").add('hidden')
            $("#overlayTabela").removeClass('d-flex')

            console.log(content)

            $conteudo = ''
            for (var i = 0; i < content.length; i++) {
                switch (content[i]['processing_status']) {
                    case 'finished':
                        var textoStatus = 'Finalizado'
                        var corStatus = 'success'
                        break;

                    //@ Identificar aqui os CASES com os retornos de status

                    default:
                        break;
                }
                moment.locale('pt-br')
                var atualizacao = moment(content[i]['upload_date']).format('DD/MM/YYYY H:mm')

                $conteudo += '<tr><td>' + content[i]['id_messageflow'] + '</td>'
                $conteudo += '<td>' + content[i]['nome'] + '</td>'
                $conteudo += '<td>' + content[i]['total_contacts'] + '</td>'
                $conteudo += '<td>' + atualizacao + '</td>'
                $conteudo += '<td><span class="badge badge-' + corStatus + '">' + textoStatus + '</span></td>'
                $conteudo += '<td>'
                //$conteudo += '<a style="margin: 2px" href="https://wa.me/" class="btn btn-info btn-sm"><i class="fas fa-users"></i></a>'
                //$conteudo += '<a style="margin: 2px" href="https://wa.me/" class="btn btn-info btn-sm"><i class="far fa-copy"></i></a>'
                //$conteudo += '<a style="margin: 2px" href="https://wa.me/" class="btn btn-info btn-sm"><i class="fas fa-pencil-alt"></i></a>'
                //$conteudo += '<a style="margin: 2px" href="" class="btn btn-danger btn-sm"><i class="far fa-trash-alt"></i></a></td>'
                $conteudo += "</td></tr>"


            }
            document.getElementById('tbodyListas').innerHTML = $conteudo
        }
    })
})

$('#btnSalvar').on('click', function () {
    var nomeLista = $('#nomeLista').val()
    var numerosDigitados = $('#areaContatos').val()

    if (nomeLista == '') {
        notify('error', 'O preenchimento do campo nome da lista é obrigatório')
    } else {
        salvarLista()
    }
})

$(document).ready(function () {

    bsCustomFileInput.init();
});
function salvarLista() {
    //@ Colocar a partir daqui para salvar a lista tanto no MESSAGEFlow quanto na VELIP
    console.log("Entrou em salvar lista")
    var file_data = $('#arquivoContatos').prop('files')[0];
    var form_data = new FormData();
    form_data.append('file', file_data);
    form_data.append('acao', 'salvarLista');
    form_data.append('idEmpresa', window.idEmpresa);
    form_data.append('idUsuario', window.idUsuario);
    form_data.append('nomeLista', $('#nomeLista').val());
    form_data.append('areaContatos', $('#areaContatos').val());

    //form_data.append('nomeLista', $('#nomeLista').val());
    //form_data.append('file', file_data);

    $.ajax({
        url: 'ajaxs/listasAjax.php', // point to server-side PHP script 
        dataType: 'html', // what to expect back from the PHP script, if anything
        cache: false,
        contentType: false,
        processData: false,
        type: 'POST',
        data: form_data,
        beforeSend: function () {
            console.log('Carregando')
            $("#overlay").removeData('hidden')
            $("#overlay").addClass('d-flex')
        },
        success: function (content) {
            $("#overlay").add('hidden')
            $("#overlay").removeClass('d-flex')
            console.log('Finalizado carregamento')
            console.log(content)
            if (content == 1) {
                notify('success', 'Lista inserida com sucesso')
                window.alteracao = 1
                $("#modalNova").modal('hide')

            } else {
                notify('error', 'Não foi possível inserir a lista')
            }
        }
    });
}

$('#modalNova').on('show.bs.modal', function (e) {
    window.alteracao = 0
})

$('#modalNova').on('hidden.bs.modal', function (e) {
    if (window.alteracao == 1) {
        activeMenu('#btnListas')
        tela('listas.php')
    }
})

//* FUNÇÃO de notificação
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