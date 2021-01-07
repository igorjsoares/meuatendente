$(function () {
    //Money Euro
    $('#cpf').inputmask({
        mask: "999.999.999-99"
    });
})


$("#btnBuscar").click(function () {
    console.log("Entrou na função")
    //Inputs
    var email = $("#email").val()
    var cpf = $("#cpf").val()
    $.ajax({
        url: 'ajaxs/usuariosAjax.php',
        type: 'POST',
        dataType: 'html',
        data: {
            acao: "primeiroAcesso",
            dados: {
                email: email,
                cpf: cpf
            }
        },
        beforeSend: function () {
            console.log("Aguardando Cadastro")
            $("#overlay").removeData('hidden')
            $("#overlay").addClass('d-flex')
        },
        success: function (content) {
            console.log("Finalizou cadastro")
            console.log(content)
            $("#overlay").add('hidden')
            $("#overlay").removeClass('d-flex')

            if (content == "Primeiro acesso") {
                toastr.success('Crie uma senha para acesso à plataforma', '', {
                    timeOut: 2000,
                    positionClass: 'toast-bottom-right',
                    progressBar: true
                })
                document.getElementById('divSenhas').removeAttribute("hidden")
                document.getElementById("email").disabled = true
                document.getElementById("cpf").disabled = true
                document.getElementById('btnSalvar').removeAttribute("hidden")
                document.getElementById('btnBuscar').setAttribute("hidden", "true")
                document.getElementById("senha").focus()


            } else if (content == "Não é primeiro acesso") {
                $("#email").val() = ''
                $("#cpf").val() = ''
                toastr.warning('Este não é o primeiro acesso desse usuário, caso tenha esquecido a senha, acesse: Esqueci minha senha', '', {
                    timeOut: 3000,
                    positionClass: 'toast-bottom-right',
                    progressBar: true,
                    onHidden: function(){
                        window.location.href = "index.php"
                    }
                })
                $("#email").val() = ''
                $("#cpf").val() = ''
            } else {
                //!Colocar Alert de Não foi possível
                console.log(content)
                toastr.error('O e-mail e CPF informado não consta em nenhum usuário cadastrado', '', {
                    timeOut: 2000,
                    positionClass: 'toast-bottom-right',
                    progressBar: true,
                    onHidden: function(){
                        window.location.href = "index.php"
                    }
                })

            }
        }
    })
})


$("#btnSalvar").click(function () {
    console.log('Botão salvar')
    //Inputs
    var email = $("#email").val();
    var senha = $("#senha").val();

    firebase
        .auth()
        .createUserWithEmailAndPassword(email, senha)
        .then(function (result) {

            //var user = firebase.auth().currentUser;

            toastr.success('Cadastrado com sucesso. Faça login na plataforma.', '', {
                timeOut: 2000,
                positionClass: 'toast-bottom-right',
                progressBar: true,
                onHidden: function(){
                    window.location.href = "index.php"
                }
            })
            

            /* user.updateProfile({
                displayName: nome.value
            })
                .then(function () {
                    //console.log(result)
                    alert('Cadastrado ' + email.value)
                    window.location.href = "index.php";
                })
                .catch(function (error) {
                    console.log(error.code)
                    console.log(error.message)
                    toastr.error(error.message, '', {
                        timeOut: 2000,
                        positionClass: 'toast-bottom-right',
                        progressBar: true
                    })
                }); */
        })
        .catch(function (error) {
            console.log('Retornou erro')
            console.log(error.code)
            console.log(error.message)
            toastr.error(error.message, '', {
                timeOut: 2000,
                positionClass: 'toast-bottom-right',
                progressBar: true,
                onHidden: function() {
                    window.location.href = "index.php";
                }
            })
        })
})

