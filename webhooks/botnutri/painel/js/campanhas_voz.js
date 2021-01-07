$(function () {

    window.inicio = moment().format('YYYY-MM-DD HH:mm')
    console.log(window.inicio)
    window.fim = moment().format('YYYY-MM-DD HH:mm')
    console.log(window.fim)

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
        placeholder: "Selecione uma opção",
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

    $('input[name="fimEnvios"]').daterangepicker({
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
        "start": moment().add(60, 'm').format("DD/MM/AAA HH:mm"),
        "minDate": moment().add(60, 'm'), //30 minutos a frente
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
        window.fim = start.format('YYYY-MM-DD HH:mm')
    })



    $.ajax({
        url: 'ajaxs/campanhasVozAjax.php',
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

                moment.locale('pt-br')
                var inicio = moment(content[i]['inicio']).format('DD/MM/YYYY H:mm')
                if (inicio == 'Invalid date') {
                    inicio = "";
                }
                var fim = moment(content[i]['fim']).format('DD/MM/YYYY H:mm')
                if (fim == 'Invalid date') {
                    fim = "";
                }

                var status = content[i]['cp_active']
                if (status == 1) {
                    var textoStatus = 'Ativo'
                    var corStatus = 'success'
                    var botaoStatus = '<a style="margin: 2px" href="" title="Desativar campanha" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#modalAlert"  data-idcampanhafornecedor="' + content[i]['id_campanha_fornecedor'] + '" data-idcampanha="' + content[i]['id_campanha'] + '" data-acao="desativar"><i class="fas fa-power-off"></i></a></td>'
                } else {
                    var textoStatus = 'Inativo'
                    var corStatus = 'danger'
                    var botaoStatus = '<a style="margin: 2px" href="" title="Reativar campanha" class="btn btn-info btn-sm" data-toggle="modal" data-target="#modalAlert"  data-idcampanhafornecedor="' + content[i]['id_campanha_fornecedor'] + '" data-idcampanha="' + content[i]['id_campanha'] + '" data-acao="ativar"><i class="fas fa-power-off"></i></a></td>'
                }

                $conteudo += '<tr><td>' + content[i]['id_campanha_fornecedor'] + '</td>'
                $conteudo += '<td>' + content[i]['nome'] + '</td>'
                $conteudo += '<td>' + content[i]['nome_lista'] + '</td>'
                $conteudo += '<td>' + content[i]['cp_made'] + '</td>'
                $conteudo += '<td>' + inicio + '</td>'
                $conteudo += '<td>' + fim + '</td>'
                $conteudo += '<td><span class="badge badge-' + corStatus + '">' + textoStatus + '</span></td>'
                //$conteudo += '<td><span class="badge badge-' + corStatus + '">' + textoStatus + '</span></td>'
                $conteudo += '<td>'

                $conteudo += '<a style="margin: 2px" href="#" class="btn btn-info btn-sm" data-toggle="modal" data-target="#modalPreview" title="Ouvir áudio" data-idaudio="' + content[i]['id_audio_db'] + '" data-idvelip="' + content[i]['id_audio'] + '"><i class="fas fa-play"></i></a>'
                //$conteudo += '<a style="margin: 2px" href="#" class="btn btn-info btn-sm" data-toggle="modal" data-target="#modalPreview" data-idcampanha="' + content[i]['idMessageFlow'] + '" data-nomecampanha="' + content[i]['nome'] + '" data-nomelista="' + content[i]['nome_lista'] + '" data-idconta="' + idConta + '" data-nomeconta="' + nomeConta + '" data-totalcontatos="' + totalContatos + '" data-totalcontatos="' + totalContatos + '" data-inicio="' + inicio + '" data-fim="' + fim + '" data-textostatus="' + textoStatus + '" data-corstatus="' + corStatus + '" data-lidos="' + parseInt(content[i]['read']) + '" data-entregues="' + parseInt(content[i]['delivered']) + '" data-inativos="' + parseInt(content[i]['inactive_whatsapp']) + '" data-naoverificados="' + parseInt(content[i]['unverified']) + '" data-tipo="' + content[i]['tipo'] + '" data-mensagem="' + content[i]['mensagem'] + '" data-url="' + content[i]['urlArquivo'] + '"><i class="fas fa-mobile-alt"></i></a>'
                //$conteudo += '<a style="margin: 2px" href="#" class="btn btn-info btn-sm" data-toggle="modal" data-target="#modalRelatorio" data-idcampanha="' + content[i]['idMessageFlow'] + '" data-nomecampanha="' + content[i]['nome'] + '" data-nomelista="' + content[i]['nome_lista'] + '" data-totalcontatos="' + totalContatos + '" data-inicio="' + inicio + '" data-fim="' + fim + '" data-textostatus="' + textoStatus + '" data-corstatus="' + corStatus + '" data-lidos="' + parseInt(content[i]['read']) + '" data-entregues="' + parseInt(content[i]['delivered']) + '" data-inativos="' + parseInt(content[i]['inactive_whatsapp']) + '" data-naoverificados="' + parseInt(content[i]['unverified']) + '"><i class="fas fa-chart-pie"></i></a>'
                //$conteudo += '<a style="margin: 2px" href="https://wa.me/" class="btn btn-info btn-sm"><i class="far fa-copy"></i></a>'
                //$conteudo += '<a style="margin: 2px" href="https://wa.me/" class="btn btn-info btn-sm"><i class="fas fa-pencil-alt"></i></a>'
                $conteudo += botaoStatus
                $conteudo += "</td></tr>"



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

$('#btnSalvar').on('click', function () {

    var dataInicio = new Date(window.inicio)
    var horaInicio = dataInicio.getHours()
    var minutosInicio = dataInicio.getMinutes()

    var dataFim = new Date(window.fim)
    var horaFim = dataFim.getHours()
    var minutosFim = dataFim.getMinutes()

    if ((horaInicio < 7 || horaInicio >= 22) || (horaFim < 7 || horaFim >= 22)) {
        notify('error', 'O horário permitido para inicio de campanha é das 7:00 às 21:59.\nEscolha outro horário.')
    } else {

        var nomeCampanha = $('#nomeCampanha').val()
        var lista = $('#selectListas').val()
        var audio = $('#selectAudios').val()

        if (lista == 0) {  //Verifica se a lista foi preenchida
            notify('error', 'Escolha uma lista válida.')
        } else if (audio == 0) { //Verifica se o nome da campanha foi preenchido
            notify('error', 'Escolha um áudio válido.')
        } else if (nomeCampanha == "") { //Verifica se o nome da campanha foi preenchido
            notify('error', 'Digite um nome para a campanha.')
        } else {
            salvarCampanha()
        }
    }
})

$(document).ready(function () {
    bsCustomFileInput.init();
});

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
    var form_data = new FormData();
    form_data.append('acao', 'salvarCampanha');
    form_data.append('idEmpresa', window.idEmpresa);
    form_data.append('idUsuario', window.idUsuario);
    form_data.append('nomeCampanha', $('#nomeCampanha').val());
    form_data.append('lista', $('#selectListas').val());
    form_data.append('audio', $('#selectAudios').val());
    form_data.append('inicio', window.inicio);
    form_data.append('fim', window.fim);

    $.ajax({
        url: 'ajaxs/campanhasVozAjax.php',
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
            } else if (content[0]['result'] == 2) { //Saldo
                /* var saldoDisponivel = content[0]['saldoDisponivel']
                var saldoReal = content[0]['saldoReal']
                var saldoPendente = content[0]['saldoPendente']
                var totalContatos = content[0]['totalContatos'] */


                notify('error', 'Saldo insuficiente. Insira mais créditos para poder criar essa campanha.')
            } else {
                

                notify('error', content[0]['mensagem'] + ' | ' + content[0]['resultadoAPI'])
            }
        }
    });
}

$('#modalNova').on('show.bs.modal', function (e) {
    window.alteracao = 0
    window.tipo = 1
    window.totalContatos = 0
    window.idConta = 0

    $.ajax({
        url: 'ajaxs/listasAjax.php',
        type: 'POST',
        dataType: 'json',
        data: {
            acao: 'consultaListasVoz',
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
                var id_velip = content[i]['id_velip']
                var num_velip = content[i]['num_velip']
                var dadosValue = "{'id' : '"+id_velip+"', 'num': '"+num_velip+"'}"
                conteudo += '<option value="' + dadosValue + '">' + content[i]['nome'] + '</option>'
            }
            document.getElementById('selectListas').innerHTML = conteudo
            document.getElementById('selectListas').removeAttribute('disabled')
        }
    })
    $.ajax({
        url: 'ajaxs/audiosAjax.php',
        type: 'POST',
        dataType: 'json',
        data: {
            acao: 'consultaAudios',
            dados: {
                idEmpresa: window.idEmpresa
            }
        },
        beforeSend: function () {
            console.log('Consultando audios')
        },
        success: function (content) {
            console.log('Consultado audios')
            console.log(content)

            var conteudo = '<option value="0">Selecione um áudio</option>'
            for (var i = 0; i < content.length; i++) {
                conteudo += '<option value="' + content[i]['id_fornecedor'] + '">' + content[i]['nome'] + '</option>'
            }
            document.getElementById('selectAudios').innerHTML = conteudo
            document.getElementById('selectAudios').removeAttribute('disabled')

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
        activeMenu('#btnCampanhasVoz')
        tela('campanhasvoz.php')
    }
})

$('#modalPreview').on('show.bs.modal', function (e) {
    var button = $(e.relatedTarget) // Button that triggered the modal
    //Variáveis recebendo os dados do botão
    //@ Precisa colocar alguma segurança na disponibilização dos áudios 
    var idAudio = button.data('idaudio')
    var idVelip = button.data('idvelip')

    document.getElementById('audioPreview').src = 'assets/audios/' + idAudio + '.mp3?random=' + new Date().getTime()
    document.getElementById('audioPreview').style.visibility = 'visible'
    document.getElementById('audioPreview').style.display = 'unset'

})

$('#modalPreview').on('hidden.bs.modal', function (e) {
    document.getElementById('audioPreview').src = ""
})

//* GERENCIAMENTO DO modalAlert
$('#modalAlert').on('show.bs.modal', function (e) {
    var button = $(e.relatedTarget) // Button that triggered the modal

    window.idCampanha = button.data('idcampanha')
    window.idCampanhaFornecedor = button.data('idcampanhafornecedor')
    window.acaoStatus = button.data('acao')
    window.alteracao = 0

    if (acaoStatus == 'ativar') {
        document.getElementById('pMensagemAlert').innerHTML = 'Ter certeza que deseja ATIVAR essa campanha?'
    } else {
        document.getElementById('pMensagemAlert').innerHTML = 'Ter certeza que deseja DESATIVAR essa campanha?'
    }
})

$('#btnSimAlert').on('click', function () {
    $.ajax({
        url: 'ajaxs/campanhasVozAjax.php',
        type: 'POST',
        dataType: 'html',
        data: {
            acao: 'alterarStatusCampanha',
            dados: {
                idCampanha: window.idCampanha,
                idCampanhaFornecedor: window.idCampanhaFornecedor,
                acaoStatus: window.acaoStatus
            }
        },
        beforeSend: function () {
            $("#overlayAlert").removeData('hidden');
            $("#overlayAlert").addClass('d-flex');

            console.log('Alterando status campanha')
        },
        success: function (resultado) {
            $("#overlayAlert").add('hidden')
            $("#overlayAlert").removeClass('d-flex')
            console.log('Alterado status campanha')

            if (resultado == 1) {
                window.alteracao = 1
                $("#modalAlert").modal('hide')
                notify('success', 'Audio excluído com sucesso!')
            } else {
                notify('error', 'Não foi possível excluir.')
            }
        }
    })
})

$('#modalAlert').on('hidden.bs.modal', function (e) {
    if (window.alteracao == 1) {
        activeMenu('#btnCampanhasVoz')
        tela('campanhasvoz.php')
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