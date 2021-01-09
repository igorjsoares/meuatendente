$(function () {

    window.idContatoAtivo = 0;

    console.log("Horário atual local: " + moment().format('DD/MM/YYY HH:mm'))

    window.inicio = moment().format('YYYY-MM-DD HH:mm')
    console.log(window.inicio)
    console.log("Iniciando atendimento.js")

    window.previsao = moment()
    window.tempoAtualizacao = 1000 * 30 //x*y onde y representa os segundos

    window.intervalo = window.setInterval(atualizacaoPeriodica, window.tempoAtualizacao)

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

            var conteudo = ''

            for (var i = 0; i < content.length; i++) {
                window.ultimaRecebida = consultaUltimaRecebida()
                console.log('Return da Ultima recebida: ' + window.ultimaRecebida)
                if (content[i]['nome'] != '') {
                    var nome = content[i]['nome']
                } else {
                    var nome = content[i]['numero']
                }
                var nomeComAspas = "'" + nome + "'"
                conteudo += '<li class="nav-item" onclick="fctClickMenu(' + content[i]['idContato'] + ', ' + nomeComAspas + ', ' + content[i]['quant'] + ')" style="cursor:pointer">'
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
                    conteudo += '<span class="float-right badge bg-success" id="span' + content[i]['idContato'] + '">' + content[i]['quant'] + '</span>'
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

function consultaUltimaRecebida(idContato) {
    $.ajax({
        url: 'ajaxs/atendimentoAjax.php',
        type: 'POST',
        dataType: 'json',
        data: {
            acao: 'consultaUltimaRecebida',
            dados: {
                idContato: idContato
            }
        },
        beforeSend: function () {
            console.log('Consultando ultima recebida')
        },
        success: function (content) {
            console.log('Consultado ultima recebida')

            console.log(content)

            var conteudo = ''

            return content[0]['ultimo_envio']
        }
    })
}

//* FUNÇÃO Seleção da conversa
function fctClickMenu(idContato, nome, quant) {
    console.log('Id contato é: ' + idContato)
    document.getElementById("fConversaNome").innerHTML = nome
    document.getElementById("imgConversaAvatar").src = 'assets/empresas/avatar.png?random=' + new Date().getTime();

    if (quant != 0) {
        document.getElementById('span' + idContato).style.visibility = 'hidden'
    }

    $.ajax({
        url: 'ajaxs/atendimentoAjax.php',
        type: 'POST',
        dataType: 'json',
        data: {
            acao: 'consultaConversaAtendimento',
            dados: {
                idContato: idContato
            }
        },
        beforeSend: function () {
            console.log('Consultando conversa de atendimento')
        },
        success: function (content) {
            console.log('Consultado conversa')

            //$("#overlayTabela").add('hidden')
            //$("#overlayTabela").removeClass('d-flex')

            console.log(content)

            var conteudo = ''

            for (var i = 0; i < content.length; i++) {
                if (content[i]['direcao'] == 0) { //recebida

                    conteudo += '<div class="direct-chat-msg" style="padding-right: 10%;">'
                    conteudo += '<div class="direct-chat-text" style="margin-left: 0px; margin-right: 0px; width: 100%; background-color: #FFF;">'
                    conteudo += content[i]['mensagem']
                    conteudo += '</div>'
                    conteudo += '<div style="color: #C1C1C1; font-size: 8px; text-align: left; margin-left: 10px">' + content[i]['dataEnvio'] + '</div>'
                    conteudo += '</div>'

                } else { //enviada    

                    conteudo += '<div class="direct-chat-msg right" style="padding-left: 10%;">'
                    conteudo += '<div class="direct-chat-text" style="margin-left: 0px; margin-right: 0px; width: 100%; background-color: #DBF7C6;">'
                    conteudo += content[i]['mensagem']
                    conteudo += '</div>'
                    conteudo += '<div style="color: #C1C1C1; font-size: 8px; text-align: right; margin-right: 10px">' + content[i]['dataEnvio'] + '</div>'
                    conteudo += '</div>'
                }

                if (i == (content.length - 1)) {
                    window.ultimaRecebidaAtiva = content[i]['dataEnvioPadrao']
                    console.log("Window última Retorno Ativo: ".window.ultimaRecebidaAtiva)
                }

            }

            document.getElementById('divMensagens').innerHTML = conteudo

            alterarStatusChat(idContato)
        }
    })
}

//* FUNÇÃO Alterar Status statusCHAT
function alterarStatusChat(idContato) {

    $.ajax({
        url: 'ajaxs/atendimentoAjax.php',
        type: 'POST',
        dataType: 'json',
        data: {
            acao: 'atualizarStatusChat',
            dados: {
                idContato: idContato
            }
        },
        beforeSend: function () {
            console.log('Atualizando Status Chat. IdContato: ' + idContato)
        },
        success: function (content) {
            console.log('Atualização concluída')

            //$("#overlayTabela").add('hidden')
            //$("#overlayTabela").removeClass('d-flex')

            console.log(content)

            if (content != 0) {
                console.log("Atualizou o StatusChat")
            } else {
                console.log("Não atualizou o StatusChat")
            }

        }
    })
}

//* Atualização periódica
function atualizacaoPeriodica() {
    clearInterval(window.tempoAtualizacao)
    console.log("Atualização periódica")

    var ultimaRecebida = consultaUltimaRecebida()
    console.log('VAR dentro da atualização periódica. Ultima recebida: ' + ultimaRecebida)


    if (window.ultimaRecebida != ultimaRecebida) {
        window.ultimaRecebida = ultimaRecebida

        consultaMenu()
        if (window.idContatoAtivo != 0) {
            var ultimaRecebidaAtiva = consultaUltimaRecebida(window.idContatoAtivo)
    console.log('VAR dentro da atualização periódica. Ultima recebida Ativa: ' + ultimaRecebidaAtiva)

            if (window.ultimaRecebidaAtiva != ultimaRecebidaAtiva) {
                consultaConversaAtiva(window.idContatoAtivo)
            }
        }
    }

}

//* FUNÇÃO de consulta do Menu
function consultaMenu() {
    console.log("FCT consulta Menu")
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
            console.log('Consultando menu periódico de atendimento')
        },
        success: function (content) {
            console.log('Consultado menu periódico')

            //$("#overlayTabela").add('hidden')
            //$("#overlayTabela").removeClass('d-flex')

            console.log(content)

            var conteudo = ''

            for (var i = 0; i < content.length; i++) {
                if (content[i]['nome'] != '') {
                    var nome = content[i]['nome']
                } else {
                    var nome = content[i]['numero']
                }
                var nomeComAspas = "'" + nome + "'"
                conteudo += '<li class="nav-item" onclick="fctClickMenu(' + content[i]['idContato'] + ', ' + nomeComAspas + ', ' + content[i]['quant'] + ')" style="cursor:pointer">'
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
                    conteudo += '<span class="float-right badge bg-success" id="span' + content[i]['idContato'] + '">' + content[i]['quant'] + '</span>'
                }
                conteudo += '</div>'
                conteudo += '</div>'
                conteudo += '</li>'
                conteudo += '</div>'
            }

            document.getElementById('ulMenuConversas').innerHTML += conteudo
        }
    })
}

//* FUNÇÃO de conversa Ativa
function consultaConversaAtiva(idContato, ultimaRecebida) {
console.log("FCT consultaConversaAtiva")
    $.ajax({
        url: 'ajaxs/atendimentoAjax.php',
        type: 'POST',
        dataType: 'json',
        data: {
            acao: 'consultaConversaAtendimento',
            dados: {
                idContato: idContato,
                ultimaRecebida: ultimaRecebida
            }
        },
        beforeSend: function () {
            console.log('Consultando conversa ativa periódico de atendimento')
        },
        success: function (content) {
            console.log('Consultado menu periódico')

            //$("#overlayTabela").add('hidden')
            //$("#overlayTabela").removeClass('d-flex')

            console.log(content)

            var conteudo = ''

            for (var i = 0; i < content.length; i++) {
                if (content[i]['nome'] != '') {
                    var nome = content[i]['nome']
                } else {
                    var nome = content[i]['numero']
                }
                var nomeComAspas = "'" + nome + "'"
                conteudo += '<li class="nav-item" onclick="fctClickMenu(' + content[i]['idContato'] + ', ' + nomeComAspas + ', ' + content[i]['quant'] + ')" style="cursor:pointer">'
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
                    conteudo += '<span class="float-right badge bg-success" id="span' + content[i]['idContato'] + '">' + content[i]['quant'] + '</span>'
                }
                conteudo += '</div>'
                conteudo += '</div>'
                conteudo += '</li>'
                conteudo += '</div>'
            }

            document.getElementById('ulMenuConversas').innerHTML += conteudo
        }
    })
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