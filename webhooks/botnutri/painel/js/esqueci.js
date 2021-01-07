
//btns
var btnEnviar = document.getElementById('btn-enviar');

//Inputs
var nome = document.getElementById('nome');
var email = document.getElementById('email');
var senha = document.getElementById('senha');

btnEnviar.addEventListener('click', function () {
    firebase
        .auth()
        .sendPasswordResetEmail(email.value)

        .then(function (result) {
            console.log(result)
            alert('E-mail para senha enviado')
        })
        .catch(function (error) {
            console.log(error.code)
            console.log(error.message)
            alert('Falha na solicitação')
        })
})

