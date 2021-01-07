


/* var imported = document.createElement('script');
imported.src = 'js/app.js';
document.head.appendChild(imported);  */

//btns
var btnDeslogar = document.getElementById('btn-deslogar');

btnDeslogar.addEventListener('click', function () {
  console.log("Logout")
  firebase
    .auth()
    .signOut()

    .then(() => {
      console.log("Deslogado, chamando PHP")
      $.ajax({
        url: 'ajaxs/usuariosAjax.php',
        type: 'POST',
        dataType: 'html',
        data: {
            acao: 'logout'
        },
        beforeSend: function () {
          console.log('Aguardando')
        },
        success: function (content) {
          if(content == 1){
            console.log("Deslogou e voltou")
            window.location.href = "../index.php"
          }
        }
    })
      window.location.href = "index.php"
    }, function (error) {
      console.log("Deu erro")
      console.log(error)
    })
  })