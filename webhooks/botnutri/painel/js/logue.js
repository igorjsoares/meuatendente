
//btns
var btnEntrar = document.getElementById('btn-entrar');

//Inputs
var email = document.getElementById('email');
var senha = document.getElementById('senha');

btnEntrar.addEventListener('click', function () {
    autenticarFirebase()
})
email.addEventListener('keypress', function (e) {
    if (e.key === 'Enter') {
      autenticarFirebase()
    }
});
senha.addEventListener('keypress', function (e) {
    if (e.key === 'Enter') {
      autenticarFirebase()
    }
});
function autenticarFirebase(){
    firebase
        .auth()
        .signInWithEmailAndPassword(email.value, senha.value)

        .then(function (result) {
            //console.log(result)
            notify('success', 'Autenticado: ' + email.value);

            //window.location.href = "home.php";

            var user = firebase.auth().currentUser;

            /* console.log(user)*/

            $.ajax({
                url: 'logue.php',
                type: 'POST',
                dataType: 'html',
                data: {
                    email: user.email,
                    uid: user.uid
                },
                beforeSend: function () {
                    //$("#overlay").removeData('hidden');
                    //$("#overlay").addClass('d-flex');
                },
                success: function (content) {
                    //$("#overlay").add('hidden')
                    //$("#overlay").removeClass('d-flex')

                    if (content == "Novo usuario" || content == "Usuario ativo") {
                        window.location.href = "home.php"
                        //maximizar()
                    } else {

                        toastr.error(content, '', {
                            timeOut: 2000,
                            positionClass: 'toast-bottom-right',
                            progressBar: true,
                            onHidden: function(){
                                window.location.href = "index.php"
                            }
                        })
                        console.log(content)

                    }
                }
            })


        })


        .catch(function (error) {
            console.log(error.code)
            console.log(error.message)
            notify('error', 'Falha ao autenticar');
        })
}

function notify(alert, alert_message) {
    if (alert == 'success') {
        toastr.success(alert_message, '', { timeOut: 2000, positionClass: 'toast-top-full-width', progressBar: true })
    }
    if (alert == 'error') {
        toastr.error(alert_message, '', { timeOut: 2000, positionClass: 'toast-top-full-width', progressBar: true })
    }
}

//!Não funciona, cada página aberta precisa de uma ação do usuário
function maximizar() {
    console.log("Tentando colocar em tela cheia")
    if (!document.fullscreenElement && // alternative standard method
        !document.mozFullScreenElement && !document.webkitFullscreenElement && !document.msFullscreenElement) { // current working methods
        if (document.documentElement.requestFullscreen) {
            document.documentElement.requestFullscreen();
        } else if (document.documentElement.msRequestFullscreen) {
            document.documentElement.msRequestFullscreen();
        } else if (document.documentElement.mozRequestFullScreen) {
            document.documentElement.mozRequestFullScreen();
        } else if (document.documentElement.webkitRequestFullscreen) {
            document.documentElement.webkitRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT);
        }
    } else {
        console.log("Já está maximizado")

        /* if (document.exitFullscreen) {
            document.exitFullscreen();
        } else if (document.msExitFullscreen) {
            document.msExitFullscreen();
        } else if (document.mozCancelFullScreen) {
            document.mozCancelFullScreen();
        } else if (document.webkitExitFullscreen) {
            document.webkitExitFullscreen();
        } */
    }
}