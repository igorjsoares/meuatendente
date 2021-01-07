
$(function () {
    //webkitURL is deprecated but nevertheless
    window.URL = window.URL || window.webkitURL;

    window.gumStream; 						//stream from getUserMedia()
    window.recorder; 						//WebAudioRecorder object
    window.input; 							//MediaStreamAudioSourceNode  we'll be recording
    window.encodingType; 					//holds selected encoding for resulting audio (file)
    window.encodeAfterRecord = true;       // when to encode

    // shim for AudioContext when it's not avb. 
    window.AudioContext = window.AudioContext || window.webkitAudioContext;
    window.audioContext; //new audio context to help us record

    window.recordButton = document.getElementById("recordButton");
    window.stopButton = document.getElementById("stopButton");


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


$('#aTabUpload').on('click', function () {
    window.tipoAudio = 'upload'
})

$('#aTabGravar').on('click', function () {
    window.tipoAudio = 'gravado'

})

$('#btnSalvar').on('click', function () {

    console.log("Entrou em salvar audio")
    if (window.tipoAudio == 'upload') {
        var file_data = $('#arquivoProfile').prop('files')[0]
    } else {
        var file_data = window.audioBase64
    }
    var form_data = new FormData();
    form_data.append('file', file_data);
    form_data.append('tipoAudio', window.tipoAudio);
    form_data.append('acao', 'salvarAudio');
    form_data.append('idEmpresa', window.idEmpresa);
    form_data.append('idUsuario', window.idUsuario);
    form_data.append('nome', $('#inputNome').val());

    $.ajax({
        url: 'ajaxs/audiosAjax.php',
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
                notify('success', 'Audio inserido com sucesso!')
                window.alteracao = 1
                $("#modalAudio").modal('hide')
            } else {

                notify('error', 'Não foi possível salvar o arquivo. ' + content[0]['mensagem'])
            }
        }
    });
})

/* $("#inputNome").keyup(function () {
    document.getElementById('fNome').innerHTML = $("#inputNome").val()
}); */



$('#modalAudio').on('show.bs.modal', function (e) {
    var button = $(e.relatedTarget) // Button that triggered the modal
    //Variáveis recebendo os dados do botão
    var idConta = button.data('idconta')
    window.varRec = 'Inicio'
    window.varBaseAudio = ''
    window.tipoAudio = 'upload'


})

$('#btnAudioRec').click(function () {
    if (window.varRec == 'Inicio') {
        console.log('Var REC: ' + window.varRec)
        console.log('IF Inicio')
        iniciarAudio()
        $(this).text('Parar')
        document.getElementById('imgGifRec').style.visibility = 'visible'
        document.getElementById('imgGifRec').style.display = 'unset'
        window.varRec = 1

    } else if (window.varRec == 0) {
        console.log('IF Start')
        // Removendo um nó a partir do pai
        var node = document.getElementById("playerAudio");
        if (node.parentNode) {
            node.parentNode.removeChild(node);
        }

        iniciarAudio()
        $(this).text('Parar')
        document.getElementById('imgGifRec').style.visibility = 'visible'
        document.getElementById('imgGifRec').style.display = 'unset'
        window.varRec = 1
    } else {
        console.log('IF Stop')
        finalizarAudio()

        //window.mediaRecorder.stop()
        $(this).text('Gravar')
        document.getElementById('imgGifRec').style.visibility = 'hidden'
        document.getElementById('imgGifRec').style.display = 'none'

        window.varRec = 0
    }
})

function iniciarAudio() {
    //& Tentar
    //& https://medium.com/jeremy-gottfrieds-tech-blog/javascript-tutorial-record-audio-and-encode-it-to-mp3-2eedcd466e78 
    //& https://www.phpclasses.org/blog/post/543-Notable-PHP-package-Media-Converter.html
    //& http://www.allwebdevhelp.com/php/help-tutorials.php?i=4555
    //& https://www.youtube.com/watch?v=80giIJkO5V8&ab_channel=DevPleno

    //& Consegui fazer funcionar com o pedaço de código do endereço abaixo, só tenho que organizar ou no código que tenho
    //& ou ajustar esse novo código
    //& https://blog.addpipe.com/using-webaudiorecorder-js-to-record-audio-on-your-website/
    //& https://github.com/addpipe/simple-web-audio-recorder-demo/blob/master/index.html
    //& https://addpipe.com/simple-web-audio-recorder-demo/

    let mediaRecorder

    console.log("startRecording() caßlled");

    var constraints = { audio: true }

    navigator
        .mediaDevices
        .getUserMedia(constraints)
        .then(function (stream) {

            window.audioContext = new AudioContext();

            //assign to gumStream for later use
            window.gumStream = stream;

            window.input = window.audioContext.createMediaStreamSource(stream);



            window.recorder = new WebAudioRecorder(window.input, {
                workerDir: "plugins/web-audio-recorder/", // must end with slash
                encoding: 'mp3',
                numChannels: 2, //2 is the default, mp3 encoding supports only 2
                onEncoderLoading: function (recorder, encoding) {
                    // show "loading encoder..." display
                    console.log("Carregando " + encoding + " encoder...");
                },
                onEncoderLoaded: function (recorder, encoding) {
                    // hide "loading encoder..." display
                    console.log(encoding + " encoder carregado");
                }
            });

            window.recorder.onComplete = function (recorder, blob) {
                console.log("Encoding completo");
                createDownloadLink(blob, 'mp3');
            }

            window.recorder.setOptions({
                timeLimit: 120,
                encodeAfterRecord: window.encodeAfterRecord,
                ogg: { quality: 0.5 },
                mp3: { bitRate: 160 }
            });

            //start the recording process
            window.recorder.startRecording();

            console.log("Gravação iniciada");

        }).catch(function (err) {
            //enable the record button if getUSerMedia() fails
            console.log('Erro de getUSerMedia')

        });





    /*  let mediaRecorder
 
 
 
     console.log("startRecording() called");
 
     
 
     navigator
         .mediaDevices
         .getUserMedia({ audio: true })
         .then(stream => {
             //console.log(stream)
             window.mediaRecorder = new MediaRecorder(stream)
             let chunks = []
             window.mediaRecorder.ondataavailable = data => {
                 chunks.push(data.data)
             }
 
             window.mediaRecorder.onstop = () => {
                 //const blob = new Blob(chunks, { type: 'audio/ogg;codec=opus' })
                 const blob = new Blob(chunks, { type: 'audio/ogg' })
                 const reader = new window.FileReader()
                 reader.readAsDataURL(blob)
                 reader.onloadend = () => {
                     console.log(reader.result)
                     window.audioBase64 = reader.result
                     const audio = document.createElement('audio')
                     audio.src = reader.result
                     audio.controls = true
                     audio.id = 'playerAudio'
                     $('#divPlayer').append(audio)
 
                     stream.getTracks() // get all tracks from the MediaStream
                         .forEach(track => track.stop()); // stop each of them
                 }
             }
             window.mediaRecorder.start()
 
         }, err => {
             document.getElementById('pStatusAudio').innerHTML = "Para gravar, é necessário permitir no navegador."
         })  */

}

function finalizarAudio() {
    console.log("stopRecording() called");

    //stop microphone access
    window.gumStream.getAudioTracks()[0].stop();

    //tell the recorder to finish the recording (stop recording + encode the recorded audio)
    window.recorder.finishRecording();

    console.log('Recording stopped');

}

function createDownloadLink(blob, encoding) {

    const reader = new window.FileReader()
    reader.readAsDataURL(blob)
    reader.onloadend = () => {
        console.log(reader.result)
        window.audioBase64 = reader.result

        var url = window.URL.createObjectURL(blob);

        console.log(url)

        const audio = document.createElement('audio')
        audio.src = url
        audio.controls = true
        audio.id = 'playerAudio'
        $('#divPlayer').append(audio)
    }

}


$('#modalAudio').on('hidden.bs.modal', function (e) {
    if (window.alteracao == 1) {
        activeMenu('#btnAudio')
        tela('audios.php')
    }

    if (document.querySelector('#playerAudio')) {

        // Removendo um nó a partir do pai
        var node = document.getElementById("playerAudio");
        if (node.parentNode) {
            node.parentNode.removeChild(node);
        }
    }
})

$('#modalPreview').on('show.bs.modal', function (e) {
    var button = $(e.relatedTarget) // Button that triggered the modal
    //Variáveis recebendo os dados do botão
    //@ Precisa colocar alguma segurança na disponibilização dos áudios 
    var idAudio = button.data('idaudio')
    var idVelip = button.data('idvelip')

    //document.getElementById('audioPreview').src = 'assets/audios/' + idAudio + '.ogg?random=' + new Date().getTime()
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
    //Variáveis recebendo os dados do botão
    window.idAudio = button.data('idaudio')
    console.log(window.idConta)
    window.alteracao = 0

})

$('#btnSimAlert').on('click', function () {
    $.ajax({
        url: 'ajaxs/audiosAjax.php',
        type: 'POST',
        dataType: 'html',
        data: {
            acao: 'excluirAudio',
            dados: {
                idAudio: window.idAudio
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
                notify('success', 'Audio excluído com sucesso!')
            } else {
                notify('error', 'Não foi possível excluir.')
            }
        }
    })
})

$('#modalAlert').on('hidden.bs.modal', function (e) {
    if (window.alteracao == 1) {
        activeMenu('#btnAudio')
        tela('audios.php')
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

