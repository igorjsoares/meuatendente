$(function () {
    window.jsonEnvios = ''
    console.log("Tipo: " + window.tipoEmpresa)
    $('[data-mask]').inputmask()

    $.ajax({
        url: 'ajaxs/enviosAjax.php',
        type: 'POST',
        dataType: 'json',
        data: {
            acao: 'consultaEnvios'
        },
        beforeSend: function () {
            $("#overlay").removeData('hidden');
            $("#overlay").addClass('d-flex');
            console.log("Consultando envios")
        },
        success: function (content) {
            if (content != 0) {
                console.log(content)
                window.jsonEnvios = content
                conteudo = ''
                for (var i = 0; i < content.length; i++) {
                    document.getElementById('tbodyEnvios').innerHTML = ""

                    var textoStatus = ''
                    var corStatus = 'light'

                    switch (content[i]['status']) {
                        case '0':
                            textoStatus = 'Cancelada'
                            corStatus = 'danger'
                            break;
                        case '1':
                            textoStatus = 'Pendente'
                            corStatus = 'light'
                            break;
                        case '2':
                            textoStatus = 'Enviando'
                            corStatus = 'warning'
                            break;
                        case '3':
                            textoStatus = 'Reenviando'
                            corStatus = 'orange'
                            break;
                        case '4':
                            textoStatus = 'Sem Whatsapp'
                            corStatus = 'secoundary'
                            break;
                        case '5':
                            textoStatus = 'Enviada'
                            corStatus = 'success'
                            break;
                        default:
                            break;
                    }
                    conteudo += '<tr><td>' + content[i]['nome'] + '</td>'
                    conteudo += '<td>' + content[i]['telefone'] + '</td>'
                    conteudo += '<td><span class="badge bg-' + corStatus + '">' + textoStatus + '</span></td></tr>'
                }
                document.getElementById('tbodyEnvios').innerHTML = conteudo

            } else {
                console.log("Não foi encontrada nenhuma envio")
            }
            $("#overlay").add('hidden')
            $("#overlay").removeClass('d-flex')
        }
    })

})

//* SHOW - Para o modal de envios
$('#modalEnviar').on('shown.bs.modal', function (e) {
    console.log('Show Enviar')
    console.log(window.jsonEnvios)
    window.enviando = true

    if ($("#inputQuantidade").val() == 0 || $("#areaMensagem").val() == '') {
        $("#modalEnviar").modal('hide')
        notify('error', 'Preencha corretamente os campos para envio.')
    } else {
        window.mensagem = $("#areaMensagem").val()

        /* var jsonTeste = window.jsonEnvios.filter(function (entry) {
            return entry.status === '1';
        }) */
        //) Convertendo o Json em array 
        arrayPreEnvios = JSON.parse(JSON.stringify(window.jsonEnvios))

        //) Filtrando o array para o status 
        //var data = { records: [{ "empid": 1, "fname": "X", "lname": "Y" }, { "empid": 2, "fname": "A", "lname": "Y" }, { "empid": 3, "fname": "B", "lname": "Y" }, { "empid": 4, "fname": "C", "lname": "Y" }, { "empid": 5, "fname": "C", "lname": "Y" }] }
        //var empIds = [1, 4, 5]
        var statusParaFiltrar = '1'
        var filteredArray = window.jsonEnvios.filter(i => statusParaFiltrar.includes(i.status))
        console.log(filteredArray)

        //) Traz somente a quantidade pretendida
        window.arrayEnvios = filteredArray.slice(0, $("#inputQuantidade").val());
        console.log(window.arrayEnvios)

        console.log('Total de números para envio: ' + window.arrayEnvios.length)

        if (window.arrayEnvios.length > 0) {
            document.getElementById('strongQuantidadeTotal').innerHTML = window.arrayEnvios.length
            window.quantidadeTotal = window.arrayEnvios.length
            envio()
        } else {
            $("#modalEnviar").modal('hide')
            notify('error', 'Não foram encontrados nenhum contato para envio.')
        }
    }
})

//* FUNÇÃO de envio de mensagens
function envio() {
    console.log('Entrou na função envio')


    var progresso = 0

    //) Chama uma função pra criar recursividade
    funcaoFor(window.arrayEnvios)

    /* //) Percorre o array com os contatos selecionados 
    for (var i = 0; i < window.arrayEnvios.length; i++) {

        var numero = window.arrayEnvios[i]['telefone']
        alteraStatus('Iniciando envio para número: ' + numero)


        //) AJAX para 
        $.ajax({
            url: 'ajaxs/enviosAjax.php',
            type: 'POST',
            dataType: 'html',
            data: {
                acao: 'envio',
                dados: {
                    mensagem: window.mensagem,
                    numero: numero,
                    tentativa: 2
                }
            },
            beforeSend: function () {
                alteraStatus('Enviando mensagem para: ' + numero)
            },
            success: function (content) {
                if (content == 1) { //) Envio realizado com sucesso 
                    console.log(content)
                    alteraStatus('Mensagem enviada para: ' + numero)
                } else {
                    alteraStatus('Mensagem NÃO enviada para: ' + numero)
                }
            }
        })

        document.getElementById('strongQuantidade').innerHTML = (i + 1)
        progresso = (((i + 1) / window.quantidadeTotal) * 100) + "%"
        $("#progress-bar").css("width", progresso);
        console.log('Progresso: ' + progresso) 
    }


    alteraStatus('Concluído')
    //$('#modalEnviar').modal('hide')
    notify('success', 'Envio finalizado.')
    */
}

//* FUNÇÃO GAMBIARRA PRA FAZER O FOR ESPERAR PELO AJAX 
function funcaoFor(dados) {
    funcaoFor2(dados, 0, dados.length - 1)
}

//* FUNÇÃO GAMBIARRA PRA FAZER O FOR ESPERAR PELO AJAX 2 
function funcaoFor2(dados, i, max) {
    if (i <= max && window.enviando == true) {
        var numero = dados[i]['telefone']
        //) AJAX para 
        $.ajax({
            url: 'ajaxs/enviosAjax.php',
            type: 'POST',
            dataType: 'html',
            data: {
                acao: 'envio',
                dados: {
                    mensagem: window.mensagem,
                    numero: numero,
                    tentativa: 2
                }
            },
            beforeSend: function () {
                alteraStatus('Enviando mensagem para: ' + numero)
            },
            success: function (content) {
                if (content == 1) { //) Envio realizado com sucesso 
                    console.log(content)
                    alteraStatus('Mensagem enviada para: ' + numero)
                } else {
                    alteraStatus('Mensagem NÃO enviada para: ' + numero)
                }

                document.getElementById('strongQuantidade').innerHTML = (i + 1)
                progresso = (((i + 1) / window.quantidadeTotal) * 100) + "%"
                $("#progress-bar").css("width", progresso);
                console.log('Progresso: ' + progresso)
                funcaoFor2(dados, ++i, max)
            }
        })
    } else {
        alteraStatus('Concluído')
        $('#modalEnviar').modal('hide')
        notify('success', 'Envio finalizado.')
    }
}

//* FUNÇÃO de auxílio para alterar o status 
function alteraStatus(texto) {
    document.getElementById('fontStatus').innerHTML = texto
    console.log(texto)
}

//* AÇÃO CLICK - Cancelar envio (dentro do modal)
$('#btnCancelar').click(function () {
    $('#modalEnviar').modal('hide')
})

$('#modalEnviar').on('hidden.bs.modal', function (e) {
    window.enviando = false
        activeMenu('#btnEnvios')
        tela('envios.php')
})

//* AÇÃO CLICK - Cancelar envio (dentro do modal)
$('#btnTemp').click(function () {
    $.ajax({
        url: 'ajaxs/enviosAjax.php',
        type: 'POST',
        dataType: 'html',
        data: {
            acao: 'pendentesTodos'
        },
        beforeSend: function () {
            alteraStatus('Colocando todos pendentes')
        },
        success: function (content) {
            if (content == 1) { //) Envio realizado com sucesso 
                console.log(content)
                notify('success', 'Todos Pendentes')
            } else {
                notify('error', 'Todos Pendentes')
            }
        }
    })
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