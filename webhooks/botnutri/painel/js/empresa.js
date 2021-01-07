$(function () {
    console.log("Tipo: " + window.tipoEmpresa)
    $('[data-mask]').inputmask()

})


$("#tipo").show(function () {
    selecionada = '<?php echo $tipo; ?>';
    $('#tipo option:contains(' + selecionada + ')').prop({
        selected: true
    });
});
$("#status").show(function () {
    selecionada = '<?php echo $statusNome; ?>';
    $('#status option:contains(' + selecionada + ')').prop({
        selected: true
    });
});
$("#formEmpresa").validate({
    rules: {
        nome: {
            required: true
        }
    },
    messages: {
        nome: {
            required: "Nome obrigatório"
        }
    },
    submitHandler: function (form) {
        $.ajax({
            url: 'ajaxs/usuariosAjax.php',
            type: 'POST',
            dataType: 'html',
            data: {
                acao: 'updateEmpresa',
                dados: {
                    idEmpresa: idEmpresa,
                    nome: $("#nome").val(),
                    razaoSocial: $("#razaoSocial").val(),
                    cnpj: $("#cnpj").val(),
                    cidade: $('#selectCidade :selected').text(),
                    endereco: $("#endereco").val(),
                    latitude: $("#latitude").val(),
                    longitude: $("#longitude").val(),
                    tipo: $("#tipo").val(),
                    status: $("#status").val()
                }
            },
            success: function (content) {
                $("#overlay").add('hidden')
                $("#overlay").removeClass('d-flex')

                if (content == "1") {
                    window.alteracao = 1
                    toastr.success("Dados alterados com sucesso", '', {
                        timeOut: 2000,
                        positionClass: 'toast-bottom-right',
                        progressBar: true
                    })
                    //Fecha o modal
                    $("#modalEmpresa").modal('hide')
                } else {
                    //!Colocar Alert de Não foi possível
                    console.log(content)
                    toastr.error(content, '', {
                        timeOut: 2000,
                        positionClass: 'toast-bottom-right',
                        progressBar: true
                    })

                }
            }
        })
    }
});

$('#modalEmpresa').on('shown.bs.modal', function (e) {
    console.log("Abriu modal")
    window.alteracao = 0
    $("#tipo").val(window.tipo);
    $("#status").val(window.status);

    //consulta  das cidades disponíveis
    $.ajax({
        url: 'ajaxs/usuariosAjax.php',
        type: 'POST',
        dataType: 'json',
        data: {
            acao: 'consultaCidades',
            dados: {
                idEmpresa: idEmpresa
            }
        },
        beforeSend: function () {
            console.log('Consultando as cidades')
        },
        success: function (content) {
            console.log(window.cidade)
            console.log('Finalizada a consulta das cidades')
            var select = document.getElementById("selectCidade")

            if (content != '') {
                for (var i = 0; i < content.length; i++) {

                    var conteudo = '<option value="' + content[i]['id_cidade'] + '">' + content[i]['nome'] + '</option>'

                    select.innerHTML += conteudo;
                }
            }
            document.getElementById("selectCidade").disabled = false

            var fCidade = document.getElementById("fCidade")
            console.log(fCidade.textContent)
            if (fCidade.textContent != '') {
                $('#selectCidade').text = fCidade.textContent
                $("#selectCidade").val($('option:contains("' + fCidade.textContent + '")').val());
            }


        }
    })
})

$('#modalEmpresa').on('hidden.bs.modal', function (e) {
    if (window.alteracao == 1) {
        activeMenu('#btn-empresa')
        tela('empresa.php')
    }

})

$(document).ready(function () {
    bsCustomFileInput.init();
});

$('#btn-imagem').on('click', function () {
    //console.log("Entrou no botão")
    var file_data = $('#imagemPerfil').prop('files')[0];
    var form_data = new FormData();
    form_data.append('file', file_data);
    $.ajax({
        url: 'uploadLogo.php', // point to server-side PHP script 
        dataType: 'text', // what to expect back from the PHP script, if anything
        cache: false,
        contentType: false,
        processData: false,
        data: form_data,
        type: 'POST',
        beforeSend: function () {
            $("#resultFoto").html("Carregando...");
        },
        success: function (php_script_response) {
            $("#resultFoto").html(php_script_response);
            window.alteracao = 0
            //document.getElementById('imgEmpresaMenu').src = 'assets/empresas/' + window.idEmpresa + '.png'
            document.getElementById('imgEmpresaMenu').src = 'assets/empresas/' + window.idEmpresa + '.jpg?random=' + new Date().getTime();
            document.getElementById('imgLogoEmpresa').src = 'assets/empresas/' + window.idEmpresa + '.jpg?random=' + new Date().getTime();
        }
    });
});