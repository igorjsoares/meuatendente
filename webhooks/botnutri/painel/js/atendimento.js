$(function () {

    window.inicio = moment().format('YYYY-MM-DD HH:mm')
    console.log(window.inicio)
    console.log("Iniciando atendimento.js")

    $("#bodyPreview").overlayScrollbars({
        overflowBehavior: {
            x: "hide",
            y: "scroll"
        },
        scrollbars: {
            visibility: "auto",
            autoHide: "never"
        }
    }) //overflow-y: scroll;

    $("#contentModalNova").overlayScrollbars({
        overflowBehavior: {
            x: "hide",
            y: "scroll"
        },
        scrollbars: {
            visibility: "auto",
            autoHide: "never"
        }
    }) //overflow-y: scroll;

    $.ajax({
        url: 'ajaxs/atendimentoAjax.php',
        type: 'POST',
        dataType: 'json',
        data: {
            acao: 'consultaMenuAtendimento',
            dados: {
            }
        },
        beforeSend: function () {
            console.log('Consultando menu de atendimento')
        },
        success: function (content) {
            console.log('Consultado menu')

            $("#overlayTabela").add('hidden')
            $("#overlayTabela").removeClass('d-flex')

            console.log(content)

            /*
 
             $conteudo = ''
             for (var i = 0; i < content.length; i++) {
                 switch (content[i]['status']) {
                     case 'processing':
                         var textoStatus = 'Processando'
                         var corStatus = 'light'
                         break;
                     case 'waiting':
                         var textoStatus = 'Aguardando'
                         var corStatus = 'light'
                         break;
                     case 'running':
                         var textoStatus = 'Rodando'
                         var corStatus = 'info'
                         break;
                     case 'finished':
                         var textoStatus = 'Finalizada'
                         var corStatus = 'success'
                         break;
                     case 'closed':
                         var textoStatus = 'Fechada'
                         var corStatus = 'secondary'
                         break;
                     case 'error':
                         var textoStatus = 'Erro'
                         var corStatus = 'danger'
                         break;
                     case 'validating':
                         var textoStatus = 'Validando'
                         var corStatus = 'warning'
                         break;
 
                     default:
                         break;
                 }
                 moment.locale('pt-br')
                 var inicio = moment(content[i]['running_date']).format('DD/MM/YYYY H:mm')
                 if (inicio == 'Invalid date') {
                     inicio = "";
                 }
                 var fim = moment(content[i]['finished_date']).format('DD/MM/YYYY H:mm')
                 if (fim == 'Invalid date') {
                     fim = "";
                 }
                 var totalContatos = parseInt(content[i]['delivered']) + parseInt(content[i]['inactive_whatsapp']) + parseInt(content[i]['read']) + parseInt(content[i]['unverified'])
                 var idConta = content[i]['id_conta']
                 var nomeConta = content[i]['nome_conta']
 
                 $conteudo += '<tr><td>' + content[i]['idMessageFlow'] + '</td>'
                 $conteudo += '<td>' + content[i]['nome'] + '</td>'
                 $conteudo += '<td>' + totalContatos + '</td>'
                 $conteudo += '<td>' + inicio + '</td>'
                 $conteudo += '<td>' + fim + '</td>'
                 $conteudo += '<td><span class="badge badge-' + corStatus + '">' + textoStatus + '</span></td>'
                 $conteudo += '<td>'
                 $conteudo += '<a style="margin: 2px" href="#" class="btn btn-info btn-sm" data-toggle="modal" data-target="#modalPreview" data-idcampanha="' + content[i]['idMessageFlow'] + '" data-nomecampanha="' + content[i]['nome'] + '" data-nomelista="' + content[i]['nome_lista'] + '" data-idconta="' + idConta + '" data-nomeconta="' + nomeConta + '" data-totalcontatos="' + totalContatos + '" data-totalcontatos="' + totalContatos + '" data-inicio="' + inicio + '" data-fim="' + fim + '" data-textostatus="' + textoStatus + '" data-corstatus="' + corStatus + '" data-lidos="' + parseInt(content[i]['read']) + '" data-entregues="' + parseInt(content[i]['delivered']) + '" data-inativos="' + parseInt(content[i]['inactive_whatsapp']) + '" data-naoverificados="' + parseInt(content[i]['unverified']) + '" data-tipo="' + content[i]['tipo'] + '" data-mensagem="' + content[i]['mensagem'] + '" data-url="' + content[i]['urlArquivo'] + '"><i class="fas fa-mobile-alt"></i></a>'
                 $conteudo += '<a style="margin: 2px" href="#" class="btn btn-info btn-sm" data-toggle="modal" data-target="#modalRelatorio" data-idcampanha="' + content[i]['idMessageFlow'] + '" data-nomecampanha="' + content[i]['nome'] + '" data-nomelista="' + content[i]['nome_lista'] + '" data-totalcontatos="' + totalContatos + '" data-inicio="' + inicio + '" data-fim="' + fim + '" data-textostatus="' + textoStatus + '" data-corstatus="' + corStatus + '" data-lidos="' + parseInt(content[i]['read']) + '" data-entregues="' + parseInt(content[i]['delivered']) + '" data-inativos="' + parseInt(content[i]['inactive_whatsapp']) + '" data-naoverificados="' + parseInt(content[i]['unverified']) + '"><i class="fas fa-chart-pie"></i></a>'
                 //$conteudo += '<a style="margin: 2px" href="https://wa.me/" class="btn btn-info btn-sm"><i class="far fa-copy"></i></a>'
                 //$conteudo += '<a style="margin: 2px" href="https://wa.me/" class="btn btn-info btn-sm"><i class="fas fa-pencil-alt"></i></a>'
                 //$conteudo += '<a style="margin: 2px" href="" class="btn btn-danger btn-sm"><i class="far fa-trash-alt"></i></a></td>'
                 $conteudo += "</tr>"
                }
 */
            var conteudo = ''

            for (var i = 0; i < content.length; i++) {
                if (content[i]['nome'] != '') {
                    var nome = content[i]['nome']
                } else {
                    var nome = content[i]['numero']
                }
                conteudo += '<li class="nav-item" click="fctClickMenu(' + content[i]['id_contato'] + ')">'
                conteudo += '<div style="padding: 10px" class="row align-items-center">'
                conteudo += '<div class="col-2">'
                conteudo += '<div style="padding: 0px;" class="image">'
                conteudo += '<img style="width: 45px; height: 45px" id="imgEmpresaMenu" src="assets/empresas/avatar.png" class="img-circle elevation-2" alt="">'
                conteudo += '</div>'
                conteudo += '</div>'
                conteudo += '<div class="col-8">'
                conteudo += '<font style="font-size: 20px;">' + nome + '</font><br>'
                conteudo += '<font style="font-size: 13px; color: gray">Mensagem enviada</font>'
                conteudo += '</div>'
                conteudo += '<div class="col-2">'
                if (content[i]['quant'] > 0) {
                    conteudo += '<span class="float-right badge bg-success">' + content[i]['quant'] + '</span>'
                }
                conteudo += '</div>'
                conteudo += '</div>'
                conteudo += '</li>'
                conteudo += '</div>'
            }

            document.getElementById('ulMenuConversas').innerHTML = conteudo
        }
    })
})

//* FUNÇÃO de notificação
function fctClickMenu(idContato) {
    console.log('Id contato é: ' + idContato)
}

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