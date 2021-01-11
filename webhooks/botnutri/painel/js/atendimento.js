$(function () {

    window.idContatoAtivo = 0
    window.bloqueio = 0

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
                consultaUltimaRecebida()

                if (content[i]['nome'] != '') {
                    var nome = content[i]['nome']
                } else {
                    var nome = content[i]['numero']
                }
                var nomeComAspas = "'" + nome + "'"

                conteudo += '<li class="nav-item" id="li' + content[i]['idContato'] + '" onclick="fctClickMenu(' + content[i]['idContato'] + ', ' + nomeComAspas + ', ' + content[i]['quant'] + ', ' + content[i]['bloqueio_bot'] + ', ' + content[i]['numero'] + ')" style="cursor:pointer">'
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
                } else {
                    conteudo += '<span class="float-right badge bg-success" id="span' + content[i]['idContato'] + '" style="display: none">' + 0 + '</span>'
                }
                conteudo += '</div>'
                conteudo += '</div>'
                conteudo += '</li>'

            }

            document.getElementById('ulMenuConversas').innerHTML = conteudo

            //$("#ulMenuConversas li").sort(ordenarDecrescente).appendTo('#ulMenuConversas');



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

            window.ultimaRecebida = content[0]['ultimo_envio']
            console.log('Return da Ultima recebida: ' + window.ultimaRecebida)

        }
    })
}

//* FUNÇÃO Seleção da conversa
function fctClickMenu(idContato, nome, quant, bloqueio_bot, numeroContato) {
    console.log('Id contato é: ' + idContato)
    window.idContatoAtivo = idContato
    window.numeroContato = numeroContato
    document.getElementById("fConversaNome").innerHTML = nome
    document.getElementById("imgConversaAvatar").src = 'assets/empresas/avatar.png?random=' + new Date().getTime();

    if (quant != 0) {
        document.getElementById('span' + idContato).style.visibility = 'hidden'
    }

    if (bloqueio_bot == 1) {
        document.getElementById("iBloqueio").style.color = "#ff8181"
        document.getElementById("btnBloqueio").title = "Bot bloqueado"
        window.bloqueio = 1
    } else {
        document.getElementById("iBloqueio").style.color = "#67e375"
        document.getElementById("btnBloqueio").title = "Bot liberado"
        window.bloqueio = 0

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
                    console.log("Window última Retorno Ativo: " + window.ultimaRecebidaAtiva)
                }

            }

            document.getElementById('divMensagens').innerHTML = conteudo

            var objDiv = document.getElementById("divMensagens");
            objDiv.scrollTop = objDiv.scrollHeight;

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
    consultaStatusContato()
}

function consultaStatusContato() {
    //& ======================
    //& ======================
    //& ====================== Colocar aqui as consultas dos status do contato
    //& ======================
    //& ======================
}

//* Atualização periódica
function atualizacaoPeriodica() {
    clearInterval(window.tempoAtualizacao)
    console.log("Atualização periódica")

    //( Consulta a última mensagem recebida no geral pra comparar com a última pesquisa dessa
    $.ajax({
        url: 'ajaxs/atendimentoAjax.php',
        type: 'POST',
        dataType: 'json',
        data: {
            acao: 'consultaUltimaRecebida',
            dados: {
                idContato: ''
            }
        },
        beforeSend: function () {
            console.log('Consultando ultima recebida. Window.ultimaRecebida: ' + window.ultimaRecebida)
        },
        success: function (content) {

            var ultimaRecebidaBanco = content[0]['ultimo_envio']
            console.log('Fim da consulta. ultimaRecebida do banco: ' + ultimaRecebidaBanco)

            if (window.ultimaRecebida != ultimaRecebidaBanco) {
                var ultimaAnterior = window.ultimaRecebida
                window.ultimaRecebida = ultimaRecebidaBanco

                if (window.idContatoAtivo != 0) {

                    //( Consulta a última mensagem recebida do contato ativo pra ver se teve atualização desse contato
                    $.ajax({
                        url: 'ajaxs/atendimentoAjax.php',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            acao: 'consultaUltimaRecebida',
                            dados: {
                                idContato: window.idContatoAtivo
                            }
                        },
                        beforeSend: function () {
                            console.log('Consultando ultima recebida Ativa. window.ultimaRecebidaAtiva: ' + window.ultimaRecebidaAtiva)
                        },
                        success: function (content) {
                            var ultimaRecebidaAtiva = content[0]['ultimo_envio']
                            console.log('Fim da consulta. ultimaRecebidaAtiva do banco: ' + ultimaRecebidaAtiva)

                            if (window.ultimaRecebidaAtiva != ultimaRecebidaAtiva) {
                                consultaConversaAtiva(window.idContatoAtivo, window.ultimaRecebidaAtiva, ultimaAnterior)
                                window.ultimaRecebidaAtiva = ultimaRecebidaAtiva

                            } else {
                                console.log('Não precisou atualizar a conversa ativa')
                                consultaMenu(ultimaAnterior)

                            }
                        }
                    })
                } else {
                    consultaMenu(ultimaAnterior)
                }
            } else {
                console.log('Não atualiza o Menu. Última mensagem recebida geral em: ' + window.ultimaRecebida)
            }


        }
    })




}

//* FUNÇÃO de consulta do Menu
function consultaMenu(ultimaRecebida) {
    console.log("FCT consulta Menu. Teve diferença entre a ultimo envio e o registrado. ultimaRecebida: " + ultimaRecebida)
    $.ajax({
        url: 'ajaxs/atendimentoAjax.php',
        type: 'POST',
        dataType: 'json',
        data: {
            acao: 'consultaMenuAtendimento',
            dados: {
                ultimaRecebida: ultimaRecebida
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


            for (var i = 0; i < content.length; i++) {
                //( Verifica se o objeto já existe
                if (typeof 'li' + content[i]['idContato'] != "undefined") { //( Existe
                    var span = document.getElementById('span' + content[i]['idContato'])
                    span.innerHTML = parseFloat($('#span' + content[i]['idContato']).html()) + parseFloat(content[i]['quant'])
                    if (span.style.display == 'none' && window.idContatoAtivo != content[i]['idContato']) {
                        span.style.display = 'block'
                    }

                    var li = "li" + content[i]['idContato']
                    $("#" + li).parent().prepend(document.getElementById(li));

                } else { //( não existe
                    var conteudo = ''

                    if (content[i]['nome'] != '') {
                        var nome = content[i]['nome']
                    } else {
                        var nome = content[i]['numero']
                    }
                    var nomeComAspas = "'" + nome + "'"
                    conteudo += '<li id="li' + content[i]['idContato'] + '" class="nav-item" onclick="fctClickMenu(' + content[i]['idContato'] + ', ' + nomeComAspas + ', ' + content[i]['quant'] + ')" style="cursor:pointer">'
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
                    } else {
                        conteudo += '<span class="float-right badge bg-success" id="span' + content[i]['idContato'] + '" style="display: none">' + content[i]['quant'] + '</span>'
                    }
                    conteudo += '</div>'
                    conteudo += '</div>'
                    conteudo += '</li>'
                    conteudo += '</div>'

                    document.getElementById('ulMenuConversas').innerHTML += conteudo

                    $("#li'" + content[i]['idContato']).parent().prepend(document.getElementById("#li'" + content[i]['idContato']));

                }

            }

        }
    })
}


//* FUNÇÃO de conversa Ativa
function consultaConversaAtiva(idContato, ultimaRecebida, ultimaAnteriorGeral) {
    console.log("FCT consultaConversaAtiva - Precisou atualizar a conversa ativa")
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

            }

            document.getElementById('divMensagens').innerHTML += conteudo

            var objDiv = document.getElementById("divMensagens");
            objDiv.scrollTop = objDiv.scrollHeight;

            consultaMenu(ultimaAnteriorGeral)
        }
    })
}

$("#btnBloqueio").click(function () {
    console.log("Pedido Bloqueio/Desbloqueio")

    $.ajax({
        url: 'ajaxs/atendimentoAjax.php',
        type: 'POST',
        dataType: 'json',
        data: {
            acao: 'bloqueioBot',
            dados: {
                idContato: window.idContatoAtivo,
                bloqueio: window.bloqueio
            }
        },
        beforeSend: function () {
            console.log('Atualizando Bloqueio')
        },
        success: function (content) {
            console.log('Atualizado bloqueio')

            //$("#overlayTabela").add('hidden')
            //$("#overlayTabela").removeClass('d-flex')

            console.log(content)

            if (content == 1) {
                if (window.bloqueio == 1) { //Se era igual a 1
                    document.getElementById("iBloqueio").style.color = "#67e375"
                    document.getElementById("btnBloqueio").title = "Bot liberado"
                    window.bloqueio = 0
                } else {
                    document.getElementById("iBloqueio").style.color = "#ff8181"
                    document.getElementById("btnBloqueio").title = "Bot bloqueado"
                    window.bloqueio = 1

                }
                console.log("Bloqueio BOT Alterado com sucesso!")
            }
        }
    })
});

//* Ação de envio de mensagem
$("#btnEnvio").click(function () {
    console.log("Clicou no botão de envio")
    var numero = window.numeroContato
    var mensagem = $("#taMensagem").val()
    envioMensagem(numero, mensagem)
})

//* Envio de mensagem
function envioMensagem(numeroEnvio, mensagemEnvio) {
    console.log("Envio acionado. Número: " + numeroEnvio + " Mensagem: " + mensagemEnvio)

    $.ajax({
        url: 'ajaxs/atendimentoAjax.php',
        type: 'POST',
        dataType: 'json',
        data: {
            acao: 'envioMensagem',
            dados: {
                idContato: window.idContatoAtivo,
                numero: numeroEnvio,
                mensagem: mensagemEnvio
            }
        },
        beforeSend: function () {
            console.log('Enviando Mensagem')
            $('#taMensagem').attr('disabled', true)
            $('#btnEnvio').attr('disabled', true)

        },
        success: function (content) {
            console.log('Finalizado envio')

            console.log(content)

            if (content == 1) {
                console.log("Mensagem enviada com sucesso!")

                document.getElementById('taMensagem').value = ""

                conteudo = ""
                conteudo += '<div class="direct-chat-msg right" style="padding-left: 10%;">'
                conteudo += '<div class="direct-chat-text" style="margin-left: 0px; margin-right: 0px; width: 100%; background-color: #DBF7C6;">'
                conteudo += mensagemEnvio
                conteudo += '</div>'
                conteudo += '<div style="color: #C1C1C1; font-size: 8px; text-align: right; margin-right: 10px">Enviado pelo chat</div>'
                conteudo += '</div>'

                document.getElementById('divMensagens').innerHTML += conteudo

                var objDiv = document.getElementById("divMensagens");
                objDiv.scrollTop = objDiv.scrollHeight



            } else {
                console.log("Mensagem NÃO enviada")
                notify('error', 'A mensagem não foi enviada. Verifique a conexão com a API e tente novamente.')

                //& =========
                //& =========
                //& ========= VERIFICAR CONEXÃO COM A API AQUI
                //& =========
                //& =========

            }
            document.getElementById('taMensagem').removeAttribute('disabled')
            document.getElementById('btnEnvio').removeAttribute('disabled')
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

