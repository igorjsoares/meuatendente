$(function () {

    window.inicio = moment().format('YYYY-MM-DD HH:mm')
    console.log(window.inicio)

    consultarCreditos()

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

    //Initialize Select2 Elements
    $('.select2').select2({
        placeholder: "Selecione uma campanha",
        allowClear: true
    })

    $('input[name="inicioEnvios"]').daterangepicker({
        "singleDatePicker": true,
        "timePicker": true,
        "timePicker24Hour": true,
        "locale": {
            "format": "DD/MM/YYYY HH:mm",
            "separator": " - ",
            "applyLabel": "Aplicar",
            "cancelLabel": "Cancelar",
            "fromLabel": "De",
            "toLabel": "Para",
            "customRangeLabel": "Custom",
            "weekLabel": "S",
            "daysOfWeek": [
                "D",
                "S",
                "T",
                "Q",
                "Q",
                "S",
                "S"
            ],
            "monthNames": [
                "Janeiro",
                "Fevereiro",
                "Março",
                "Abril",
                "Maio",
                "Junho",
                "Julho",
                "Agosto",
                "Setembro",
                "Outubro",
                "Novembro",
                "Dezembro"
            ],
            "firstDay": 0
        },
        "start": moment().add(30, 'm').format("DD/MM/AAA HH:mm"),
        "minDate": moment().add(30, 'm'), //30 minutos a frente
        "isInvalidDate": function (date) {
            //Isso aqui inabilita as datas especificadas abaixo
            /* var disabled_start = moment('11/01/2020', 'MM/DD/YYYY');
            var disabled_end = moment('11/15/2020', 'MM/DD/YYYY');
            return date.isAfter(disabled_start) && date.isBefore(disabled_end); */

            var data = new Date(date.format('YYYY-MM-DD HH:mm'));
            var diaSemana = data.getDay()

            if (diaSemana == 6 || diaSemana == 0) {
                return true;
            }
        }

    }, function (start, end, label) {

        console.log('New date range selected: ' + start.format('DD-MM-YYYY HH:mm') + ' to ' + end.format('DD-MM-YYYY HH:mm') + ' (predefined range: ' + label + ')');
        window.inicio = start.format('YYYY-MM-DD HH:mm')
    })



    $.ajax({
        url: 'ajaxs/campanhasAjax.php',
        type: 'POST',
        dataType: 'json',
        data: {
            acao: 'consultaCampanhas',
            dados: {
                idEmpresa: window.idEmpresa
            }
        },
        beforeSend: function () {
            console.log('Consultando campanhas')
            $("#overlayTabela").removeData('hidden');
            $("#overlayTabela").addClass('d-flex');
        },
        success: function (content) {
            console.log('Consultado campanhas')

            $("#overlayTabela").add('hidden')
            $("#overlayTabela").removeClass('d-flex')

            console.log(content)

           

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
            document.getElementById('tbodyCampanhas').innerHTML = $conteudo
        }
    })




    //Formatação  da tabela
    $("#tableContatos").DataTable({
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

})

/* $("button").click(function () {
    var emoji = $(this).text();
    $('#taMensagem').val($('#taMensagem').val() + emoji);
}); */



function emojis(emoji) {
    //var emoji = $(this).text();
    $('#taMensagem').val($('#taMensagem').val() + emoji)
    document.getElementById('taMensagem').focus()
}

$('#btnEmojis').on('click', function () {
    document.getElementById('cardEmojis').style.visibility = 'visible'
    document.getElementById('cardEmojis').style.display = 'unset'
    document.getElementById('taMensagem').focus()

})

$('#selectTipo').on('change', function () {
    console.log($('#selectTipo').val())
    window.tipo = $('#selectTipo').val()
    if ($('#selectTipo').val() == 1) {
        document.getElementById('fgMensagem').style.visibility = 'visible'
        document.getElementById('fgMensagem').style.display = 'unset'

        document.getElementById('rowArquivo').style.visibility = 'hidden'
        document.getElementById('rowArquivo').style.display = 'none'

    } else if ($('#selectTipo').val() == 2) {
        document.getElementById('fgMensagem').style.visibility = 'visible'
        document.getElementById('fgMensagem').style.display = 'unset'

        document.getElementById('rowArquivo').style.visibility = 'visible'
        document.getElementById('rowArquivo').style.display = 'unset'

        $('#arquivoMensagem').attr("accept", ".jpg, .gif, .png")

    } else if ($('#selectTipo').val() == 3) {
        document.getElementById('fgMensagem').style.visibility = 'hidden'
        document.getElementById('fgMensagem').style.display = 'none'

        document.getElementById('rowArquivo').style.visibility = 'visible'
        document.getElementById('rowArquivo').style.display = 'unset'

        $('#arquivoMensagem').attr("accept", ".mp4")

    } else if ($('#selectTipo').val() == 4) {
        document.getElementById('fgMensagem').style.visibility = 'hidden'
        document.getElementById('fgMensagem').style.display = 'none'

        document.getElementById('rowArquivo').style.visibility = 'visible'
        document.getElementById('rowArquivo').style.display = 'unset'

        $('#arquivoMensagem').attr("accept", ".mp3")

    } else if ($('#selectTipo').val() == 5) {
        document.getElementById('fgMensagem').style.visibility = 'hidden'
        document.getElementById('fgMensagem').style.display = 'none'

        document.getElementById('rowArquivo').style.visibility = 'visible'
        document.getElementById('rowArquivo').style.display = 'unset'

        $('#arquivoMensagem').attr("accept", ".pdf")

    }
})


$('#btnCloseEmojis').on('click', function () {
    document.getElementById('cardEmojis').style.visibility = 'hidden'
    document.getElementById('cardEmojis').style.display = 'none'

    document.getElementById('taMensagem').focus()
})

$('#btnSalvar').on('click', function () {

    var data = new Date(window.inicio)
    var hora = data.getHours()
    var minutos = data.getMinutes()

    if (hora < 7 || hora >= 22) {
        //$('#inicioEnvios').val('')
        notify('error', 'O horário permitido para inicio de campanha é das 7:00 às 21:59.\nEscolha outro horário.')
    } else {

        var nomeCampanha = $('#nomeCampanha').val()
        var mensagem = $('#taMensagem').val()
        var lista = $('#selectListas').val()

        if (lista == 0) {  //Verifica se a lista foi preenchida
            notify('error', 'Escolha uma lista válida.')
        } else if (nomeCampanha == "") { //Verifica se o nome da campanha foi preenchido
            notify('error', 'Digite um nome para a campanha.')
        } else {
            if (window.tipo == 1) { //Verifica se o tipo é 1
                if (mensagem == "") {
                    notify('error', 'Digite uma mensagem para o envio.')
                } else {
                    salvarCampanha()
                }
            } else {
                if ($('#arquivoMensagem').val() == '') {
                    notify('error', 'É necessário escolher um arquivo para esse tipo de envio.')
                } else {
                    salvarCampanha()
                }
            }
        }
    }
})

$(document).ready(function () {
    bsCustomFileInput.init();
});

$('#arquivoMensagem').change(function (event) {
    console.log('Entrou na seleção do arquivo')
    window.form = new FormData();
    window.form.append('fileUpload', event.target.files[0]); // para apenas 1 arquivo
    //var name = event.target.files[0].content.name; // para capturar o nome do arquivo com sua extenção
    console.log(window.form)

});

$('#selectListas').change(function (event) {
    var idLista = $('#selectListas').val()

    $.ajax({
        url: 'ajaxs/campanhasAjax.php',
        type: 'POST',
        dataType: 'html',
        data: {
            acao: 'consultaTotalLista',
            dados: {
                idLista: idLista
            }
        },
        beforeSend: function () {
            console.log('Consultando total lista')
            document.getElementById('btnSalvar').disabled = true;

            document.getElementById('fTotalContatos').innerHTML = 'Consultando total de contatos...'
        },
        success: function (resultado) {
            console.log('Consultado total lista')
            console.log(resultado)
            if (resultado > 0) {
                window.totalContatos = resultado
                document.getElementById('fTotalContatos').innerHTML = 'Esta lista tem um total de ' + resultado + ' contatos.'
                document.getElementById('btnSalvar').disabled = false;
            } else {
                document.getElementById('fTotalContatos').innerHTML = 'Erro ao carregar a lista.'
                notify('error', 'Escolha uma outra lista.')
            }
        }
    })

})

function consultarCreditos() {
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



function salvarCampanha() {
    console.log("Entrou em salvar campanha")
    var file_data = $('#arquivoMensagem').prop('files')[0];
    var form_data = new FormData();
    form_data.append('file', file_data);
    form_data.append('acao', 'salvarCampanha');
    form_data.append('idEmpresa', window.idEmpresa);
    form_data.append('idUsuario', window.idUsuario);
    form_data.append('caminhoImagemPerfil', window.caminhoImagemPerfil);
    form_data.append('nomePerfil', window.nomePerfil);
    form_data.append('nomeCampanha', $('#nomeCampanha').val());
    form_data.append('lista', $('#selectListas').val());
    form_data.append('inicio', window.inicio);
    form_data.append('mensagem', $('#taMensagem').val());
    form_data.append('nomeEmpresa', window.nomeEmpresa);
    form_data.append('tipo', window.tipo);
    form_data.append('url', '');
    form_data.append('totalContatos', window.totalContatos);
    form_data.append('idConta', window.idConta);




    /* data: {
        acao: 'salvarCampanha',
        data: form_data,
        dados: {
            idEmpresa: window.idEmpresa,
            idUsuario: window.idUsuario,
            nomeEmpresa: window.nomeEmpresa,
            nomeCampanha: $('#nomeCampanha').val(),
            lista: $('#selectListas').val(),
            inicio: window.inicio,
            mensagem: $('#taMensagem').val(),
            nomeEmpresa: window.nomeEmpresa,
            tipo: 1,
            url: '',
            form: window.form
        }
    }, */

    $.ajax({
        url: 'ajaxs/campanhasAjax.php',
        dataType: 'json', // what to expect back from the PHP script, if anything
        cache: false,
        contentType: false,
        processData: false,
        type: 'POST',
        data: form_data,
        beforeSend: function () {
            $("#overlay").removeData('hidden')
            $("#overlay").addClass('d-flex')

            console.log('Carregando')
        },
        success: function (content) {
            $("#overlay").add('hidden')
            $("#overlay").removeClass('d-flex')
            console.log('Finalizado carregamento')
            console.log(content)
            if (content[0]['result'] == 1) {
                notify('success', 'Campanha inserida com sucesso')
                window.alteracao = 1
                $("#modalNova").modal('hide')
            } else if (content[0]['result'] == 3) { //tamanho
                notify('error', 'O tamanho do arquivo excede o limite.\nImagem: 490kb\nAudio: 1Mb\nVídeo: 3Mb')
            } else if (content[0]['result'] == 2) { //Saldo
                /* var saldoDisponivel = content[0]['saldoDisponivel']
                var saldoReal = content[0]['saldoReal']
                var saldoPendente = content[0]['saldoPendente']
                var totalContatos = content[0]['totalContatos'] */


                notify('error', 'Saldo insuficiente. Insira mais créditos para poder criar essa campanha.')
            } else {
                //@ Tem que fazer aqui um código para varrer os erros e mostrar na notificação, independente do erro que apresentar. 
                //Problema com imagem muito pequena
                if (content[0]['resultadoAPI']['profile']['photo'][0] != "") {
                    $mensagemAPI = '\n' + content[0]['resultadoAPI']['profile']['photo'][0]
                } else {
                    $mensagemAPI = ''
                }

                notify('error', 'Não foi possível inserir a campanha' + $mensagemAPI)
            }
        }
    });
}

$('#modalNova').on('show.bs.modal', function (e) {
    window.alteracao = 0
    window.tipo = 1
    window.totalContatos = 0
    window.idConta = 0

    //Consulta as contas
    $.ajax({
        url: 'ajaxs/campanhasAjax.php',
        type: 'POST',
        dataType: 'json',
        data: {
            acao: 'consultaContas',
            dados: {
                idEmpresa: window.idEmpresa
            }
        },
        beforeSend: function () {
            console.log('Consultando contas')
            $("#overlay").removeData('hidden');
            $("#overlay").addClass('d-flex');
        },
        success: function (content) {
            console.log('Consultado contas')

            $("#overlay").add('hidden')
            $("#overlay").removeClass('d-flex')

            console.log(content)

            $conteudo = '<option value="' + window.idEmpresa + '" selected>' + window.nomeEmpresa + '</option>'
            for (var i = 0; i < content.length; i++) {
                $conteudo += '<option value="' + content[i]['idConta'] + '">' + content[i]['nome'] + '</option>'
            }
            document.getElementById('selectConta').innerHTML = $conteudo

            document.getElementById('imgFotoCampanha').src = 'assets/empresas/' + window.idEmpresa + '.jpg?random=' + new Date().getTime();
            document.getElementById('fNomePerfil').innerHTML = window.nomeEmpresa
            window.caminhoImagemPerfil = 'assets/empresas/' + window.idEmpresa + '.jpg'
            window.nomePerfil = window.nomeEmpresa
        }
    })

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
        },
        success: function (content) {
            console.log('Consultado listas')
            console.log(content)

            var conteudo = '<option value="0">Selecione uma lista</option>'
            for (var i = 0; i < content.length; i++) {
                conteudo += '<option value="' + content[i]['id_messageflow'] + '">' + content[i]['nome'] + '</option>'
            }
            document.getElementById('selectListas').innerHTML = conteudo
            document.getElementById('selectListas').removeAttribute('disabled')

        }
    })
})


$('#selectConta').on('change', function (e) {
    var textoSelect = $('#selectConta :selected').text()
    var valor = $('#selectConta').val()
    var index = document.getElementById('selectConta').selectedIndex
    console.log("Index: " + index)

    if (index == 0) {
        var caminho = 'assets/empresas/' + valor + '.jpg'
        document.getElementById('imgFotoCampanha').src = caminho + '?random=' + new Date().getTime();
        document.getElementById('fNomePerfil').innerHTML = textoSelect
        window.idConta = 0
    } else {
        var caminho = 'assets/contas/' + valor + '.jpg'
        document.getElementById('imgFotoCampanha').src = caminho + '?random=' + new Date().getTime();
        document.getElementById('fNomePerfil').innerHTML = textoSelect
        window.idConta = valor
    }
    window.caminhoImagemPerfil = caminho
    window.nomePerfil = textoSelect

})

$('#modalRelatorio').on('show.bs.modal', function (e) {
    var button = $(e.relatedTarget) // Button that triggered the modal
    //Variáveis recebendo os dados do botão
    window.idMessageFlow = button.data('idcampanha')
    var nomeCampanha = button.data('nomecampanha')
    var nomeLista = button.data('nomelista')
    var totalContatos = button.data('totalcontatos')
    var inicio = button.data('inicio')
    var fim = button.data('fim')
    var textoStatus = button.data('textostatus')
    var corStatus = button.data('corstatus')
    var lidos = button.data('lidos')
    var entregues = button.data('entregues')
    var recebidas = lidos + entregues
    var inativos = button.data('inativos')
    var naoVerificados = button.data('naoverificados')
    var porcentagemLidos = ((lidos / totalContatos) * 100)
    var porcentagemEntregues = ((entregues / totalContatos) * 100)
    var porcentagemInativos = ((inativos / totalContatos) * 100)
    var porcentagemNaoVerificados = ((naoVerificados / totalContatos) * 100)
    var porcentagemRecebidas = porcentagemLidos + porcentagemEntregues




    document.getElementById('strongTitulo').innerHTML = window.idMessageFlow + ' - ' + nomeCampanha
    document.getElementById('fLista').innerHTML = nomeLista
    document.getElementById('fContatos').innerHTML = totalContatos
    document.getElementById('spanStatus').innerHTML = textoStatus
    $('#spanStatus').addClass("bg-" + corStatus)
    //document.getElementById('spanLidas').innerHTML = parseFloat(porcentagemLidos.toFixed(1)) + '<small>%</small>'
    //document.getElementById('spanNaoLidas').innerHTML = parseFloat(porcentagemEntregues.toFixed(1)) + '<small>%</small>'
    document.getElementById('spanRecebidas').innerHTML = parseFloat(porcentagemRecebidas.toFixed(1)) + '<small>%</small>'

    document.getElementById('spanInativos').innerHTML = parseFloat(porcentagemInativos.toFixed(1)) + '<small>%</small>'
    document.getElementById('spanNaoVerificado').innerHTML = parseFloat(porcentagemNaoVerificados.toFixed(1)) + '<small>%</small>'


    //-------------
    //- DONUT CHART -
    //-------------
    // Get context with jQuery - using jQuery's .get() method.
    var donutChartCanvas = $('#donutChart').get(0).getContext('2d')
    var donutData = {
        labels: [
            'Recebidas',
            'Inativos',
            'Não verificados'
        ],
        datasets: [
            {
                data: [recebidas, inativos, naoVerificados],
                backgroundColor: ['#28a745', '#f8f9fa', '#bababa'],
            }
        ]
    }
    var donutOptions = {
        maintainAspectRatio: false,
        responsive: true,
    }
    //Create pie or douhnut chart
    // You can switch between pie and douhnut using the method below.
    var donutChart = new Chart(donutChartCanvas, {
        type: 'doughnut',
        data: donutData,
        options: donutOptions
    })



})

$('#btnVisualizarContatos').on('click', function () {
    $("#overlayContatos").removeData('hidden');
    $("#overlayContatos").addClass('d-flex');

    $("#divTabela").removeData('hidden');
    $("#divTabela").addClass('d-flex');

    document.getElementById('btnVisualizarContatos').style.visibility = 'hidden'

    $.ajax({
        url: 'ajaxs/campanhasAjax.php',
        type: 'POST',
        dataType: 'json',
        data: {
            acao: 'consultaContatos',
            dados: {
                idMessageFlow: window.idMessageFlow
            }
        },
        beforeSend: function () {
            console.log('Consultando contatos')
        },
        success: function (content) {
            console.log('Consultado contatos')

            $("#overlayContatos").add('hidden')
            $("#overlayContatos").removeClass('d-flex')

            console.log(content)

            $conteudo = ''
            var table = $('#tableContatos').DataTable()
            for (var i = 0; i < content['recipients'].length; i++) {
                switch (content['recipients'][i]['status']) {
                    case 'unverified':
                        var textoStatus = 'Não verificado'
                        var corStatus = 'light'
                        break;
                    case 'inactive_whatsapp':
                        var textoStatus = 'Inativo'
                        var corStatus = 'secondary'
                        break;
                    case 'read':
                        var textoStatus = 'Recebido'
                        var corStatus = 'success'
                        break;
                    case 'delivered':
                        var textoStatus = 'Recebido'
                        var corStatus = 'success'
                        break;

                    default:
                        break;
                }
                moment.locale('pt-br')
                var data = moment(content['recipients'][i]['date']).format('DD/MM/YY H:mm')
                if (data == 'Invalid date') {
                    data = "";
                }
                var telefone = content['recipients'][i]['phone']

                table.row.add([
                    (i + 1), telefone.substring(2), data, '<span class="badge badge-' + corStatus + '">' + textoStatus + '</span>'
                ]).draw(false)

                /* $conteudo += '<tr><td>' + (i + 1) + '</td>'
                $conteudo += '<td>' + content['recipients'][i]['phone'] + '</td>'
                $conteudo += '<td>' + data + '</td>'
                $conteudo += '<td><span class="badge badge-' + corStatus + '">' + textoStatus + '</span></td>'
                $conteudo += "</tr>" */
            }
            //document.getElementById('tbodyContatos').innerHTML = $conteudo
        }
    })

})

$('#modalNova').on('hidden.bs.modal', function (e) {
    if (window.alteracao == 1) {
        activeMenu('#btnCampanhas')
        tela('campanhas.php')
    }
})

$('#modalPreview').on('show.bs.modal', function (e) {
    var button = $(e.relatedTarget) // Button that triggered the modal
    //Variáveis recebendo os dados do botão
    window.idMessageFlow = button.data('idcampanha')
    var tipo = button.data('tipo')
    var mensagem = button.data('mensagem')
    window.idContaPreview = button.data('idconta')
    window.nomeContaPreview = button.data('nomeconta')

    //document.getElementById('imgFoto').src = 'assets/empresas/' + window.idEmpresa + '.jpg?random=' + new Date().getTime()
    //document.getElementById('fNome').innerHTML = window.nomeEmpresa
    if(window.idContaPreview == 0){
        document.getElementById('imgFoto').src = 'assets/empresas/' + window.idEmpresa + '.jpg?random=' + new Date().getTime();
        document.getElementById('fNome').innerHTML = window.nomeEmpresa
    }else{
        document.getElementById('imgFoto').src = 'assets/contas/' + window.idContaPreview + '.jpg?random=' + new Date().getTime();
        document.getElementById('fNome').innerHTML = window.nomeContaPreview
    }

    if (tipo == 2) { //Mostra  ou não a IMAGEM no Preview
        document.getElementById('imgPreview').src = 'assets/midias/' + window.idMessageFlow + '.jpg?random=' + new Date().getTime()
        document.getElementById('imgPreview').style.visibility = 'visible'
        document.getElementById('imgPreview').style.display = 'unset'
    } else {
        document.getElementById('imgPreview').style.visibility = 'hidden'
        document.getElementById('imgPreview').style.display = 'none'
    }
    if (tipo == 3) { //Mostra  ou não o VÍDEO no Preview
        document.getElementById('videoPreview').src = 'assets/midias/' + window.idMessageFlow + '.mp4?random=' + new Date().getTime()
        document.getElementById('videoPreview').style.visibility = 'visible'
        document.getElementById('videoPreview').style.display = 'unset'
    } else {
        document.getElementById('videoPreview').style.visibility = 'hidden'
        document.getElementById('videoPreview').style.display = 'none'
    }
    if (tipo == 4) { //Mostra  ou não o AUDIO no Preview
        document.getElementById('audioPreview').src = 'assets/midias/' + window.idMessageFlow + '.mp3?random=' + new Date().getTime()
        document.getElementById('audioPreview').style.visibility = 'visible'
        document.getElementById('audioPreview').style.display = 'unset'
    } else {
        document.getElementById('audioPreview').style.visibility = 'hidden'
        document.getElementById('audioPreview').style.display = 'none'
    }
    document.getElementById('pMensagem').innerHTML = mensagem
    //var taMensagem = document.getElementById("taMensagemPreview")
    //taMensagem.style.height = txtarea.scrollHeight + "px"
})

$('#modalPreview').on('hidden.bs.modal', function (e) {
    document.getElementById('imgPreview').src = ""
    document.getElementById('videoPreview').src = ""
    document.getElementById('audioPreview').src = ""
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