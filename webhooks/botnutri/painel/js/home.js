$(function () {
  //Pede permissão para as notificações
  if (Notification.permission !== 'granted') {
    Notification.requestPermission()
  }

  activeMenu('#btnCampanhas')
  tela('campanhas.php')

  /* jQueryKnob */
  $('.knob').knob({
    /*change : function (value) {
     //console.log("change : " + value);
     },
     release : function (value) {
     console.log("release : " + value);
     },
     cancel : function () {
     console.log("cancel : " + this.value);
     },*/

  })
  /* END JQUERY KNOB */


  window.notificacaoPendente = 0
  window.notificacaoNegada = 0
  window.notificacaoAceita = 0
  window.totalNotificacoes = 0
  //Tempo de atualização
  window.tempoAtualizacao = 1000 * 60 //x*y onde y representa os segundos  ---- x=60000 caso queira em minutos

  verificarNotificacoes()

  //Initialize Select2 Elements
  $('.select2').select2()

  //Initialize Select2 Elements
  $('.select2bs4').select2({
    theme: 'bootstrap4'
  })

  if (window.tipoEmpresa == 1) { //) Anunciante 
    document.getElementById('divPendentesAnu').removeAttribute('hidden')
    document.getElementById('divOcupacaoAnu').removeAttribute('hidden')
    document.getElementById('divDisponiveisAnu').removeAttribute('hidden')
    document.getElementById('diReservadasAnu').removeAttribute('hidden')
    document.getElementById('divGrafAnu').removeAttribute('hidden')
    document.getElementById('divTabelasAnu').removeAttribute('hidden')
    consultaConsolidadoHomeAnunciante()

  } else {  //) Solicitante  

    document.getElementById('divPendentesSol').removeAttribute('hidden')
    document.getElementById('divNegadasSol').removeAttribute('hidden')
    document.getElementById('divTabelaSol').removeAttribute('hidden')
    consultaConsolidadoHomeSolicitante()

  }

  if (window.perfil_usuario == 'MASTER') {
    document.getElementById("divInfoTextoNomeEmpresa").style.visibility = "hidden"
    document.getElementById("divInfoTextoNomeEmpresa").style.display = "unset"
    document.getElementById("divInfoTextoNomeEmpresa").style.width = "0%"
    document.getElementById("divInfoSelectNomeEmpresa").style.visibility = "visible"
    document.getElementById("divInfoSelectNomeEmpresa").style.display = "block"
    document.getElementById("divInfoSelectNomeEmpresa").style.width = "100%"

    $.ajax({
      url: 'ajaxs/usuariosAjax.php',
      type: 'POST',
      dataType: 'json',
      data: {
        acao: 'consultaEmpresasMaster'
      },
      beforeSend: function () {
        console.log('Pesquisando empresas usuário MASTER')
      },
      success: function (content) {
        console.log('Pesquisado empresas usuário MASTER')
        console.log(content)

        var conteudo = '<option value="1">VAGAMED</option>'
        for (var i = 0; i < content.length; i++) {
          conteudo += '<option value="' + content[i]['id_empresa'] + '" data-tipo="' + content[i]['tipo'] + '">' + content[i]['nome'] + '</option>'
        }
        document.getElementById('selectNomeEmpresa').innerHTML = conteudo

        document.getElementById('selectNomeEmpresa').value = idEmpresa;

        document.getElementById("selectNomeEmpresa").disabled = false

        consultaStatusWhatsapp(idEmpresa)

      }
    });
  } else {
    consultaStatusWhatsapp(window.idEmpresa)
  }


})

//* CONSULTA para alimentar dados da HOME do ANUNCIANTE 
function consultaConsolidadoHomeAnunciante() {

  consultaSolicitacoesStatus(window.idEmpresa, window.tipoEmpresa)


  //) AJAX - Consulta consolidado Vagas
  $.ajax({
    url: 'ajaxs/vagasAjax.php', //) Em vagasAjax.php
    type: 'POST',
    dataType: 'json',
    data: {
      acao: 'consultaConsolidadoVagas',
      dados: {
        idUsuario: window.idUsuario,
        idEmpresa: window.idEmpresa
      }
    },
    beforeSend: function () {
      $("#overlayConsolidadoOcupacaoAnu").removeData('hidden');
      $("#overlayConsolidadoOcupacaoAnu").addClass('d-flex');
      $("#overlayConsolidadoDisponiveisAnu").removeData('hidden');
      $("#overlayConsolidadoDisponiveisAnu").addClass('d-flex');
      $("#overlayGrafSol").removeData('hidden');
      $("#overlayGrafSol").addClass('d-flex');
      console.log("Consultando consolidado vagas")
    },
    success: function (content) {
      console.log(content)

      var leitosLivres = parseInt(content[0]['livres']) + parseInt(content[1]['livres']) + parseInt(content[2]['livres'])
      var leitosbloqueados = parseInt(content[0]['bloqueadas']) + parseInt(content[1]['bloqueadas']) + parseInt(content[2]['bloqueadas'])
      var leitosTotais = parseInt(content[0]['total']) + parseInt(content[1]['total']) + parseInt(content[2]['total'])
      var leitosOcupados = leitosTotais - (leitosLivres + leitosbloqueados)

      document.getElementById('hOcupacaoAnu').innerHTML = Math.trunc((leitosOcupados / leitosTotais) * 100) + '<sup style="font-size: 20px">%</sup>'
      document.getElementById('hDisponiveisAnu').innerHTML = leitosLivres
      //console.log('Livres: ' + leitosLivres + '<br>Totais: ' + leitosTotais + '<br>Ocupados: ' + leitosOcupados)

      var percentualUti = Math.trunc((((content[0]['total'] - (content[0]['bloqueadas'] + content[0]['livres'])) / content[0]['total']) * 100))
      percentualUti = Number.isNaN(percentualUti) ? 0 : percentualUti;
      document.getElementById('inputGrafUti').value = percentualUti
      document.getElementById('fUtiReservadas').innerHTML = (content[0]['total'] - (content[0]['bloqueadas'] + content[0]['livres'])) + '/' + content[0]['total']

      var percentualEnf = Math.trunc((((content[1]['total'] - (content[1]['bloqueadas'] + content[1]['livres'])) / content[1]['total']) * 100))
      percentualEnf = Number.isNaN(percentualEnf) ? 0 : percentualEnf;
      document.getElementById('inputGrafEnf').value = percentualEnf
      document.getElementById('fEnfReservadas').innerHTML = (content[1]['total'] - (content[1]['bloqueadas'] + content[1]['livres'])) + '/' + content[1]['total']

      var percentualPso = Math.trunc((((content[2]['total'] - (content[2]['bloqueadas'] + content[2]['livres'])) / content[2]['total']) * 100))
      percentualPso = Number.isNaN(percentualPso) ? 0 : percentualPso;
      document.getElementById('inputGrafPso').value = percentualPso
      document.getElementById('fPsoReservadas').innerHTML = (content[2]['total'] - (content[2]['bloqueadas'] + content[2]['livres'])) + '/' + content[2]['total']

      $("input.knob").trigger('change');

      $("#overlayConsolidadoOcupacaoAnu").add('hidden')
      $("#overlayConsolidadoOcupacaoAnu").removeClass('d-flex')
      $("#overlayConsolidadoDisponiveisAnu").add('hidden')
      $("#overlayConsolidadoDisponiveisAnu").removeClass('d-flex')
      $("#overlayGrafSol").add('hidden')
      $("#overlayGrafSol").removeClass('d-flex')
    }

  })
  ultimasTabela(2, 5, window.idEmpresa, window.tipoEmpresa, 'tbodySolicitacoesAceitasAnu')
  ultimasTabela(3, 5, window.idEmpresa, window.tipoEmpresa, 'tbodySolicitacoesNegadasAnu')

}

//* CONSULTA para alimentar dados da HOME do SOLICITANTE 
function consultaConsolidadoHomeSolicitante() {

  //) AJAX - Negadas últimas 24h 
  $.ajax({
    url: 'ajaxs/solicitacoesAjax.php',  //) Dentro do PHP de Solicitações
    type: 'POST',
    dataType: 'json',
    data: {
      acao: 'consultaNegadas24h',
      dados: {
        idUsuario: window.idUsuario,
        idEmpresa: window.idEmpresa,
        tipoEmpresa: window.tipoEmpresa,
        notificacoes: 1
      }
    },
    beforeSend: function () {
      $("#overlayConsolidadoNegadasSol").removeData('hidden');
      $("#overlayConsolidadoNegadasSol").addClass('d-flex');
      console.log("Consultando consolidado Negadas 24h")
    },
    success: function (content) {
      if (content != 0) {
        console.log(content)
        for (var i = 0; i < content.length; i++) {
          if (content[i]['status'] == 3) {
            document.getElementById('hNegadasSol').innerHTML = content[i]['total']
          }
        }

      } else {
        console.log("Não foi encontrada nenhuma vaga")
      }
      $("#overlayConsolidadoNegadasSol").add('hidden')
      $("#overlayConsolidadoNegadasSol").removeClass('d-flex')
    }
  })

  consultaSolicitacoesStatus(window.idEmpresa, window.tipoEmpresa)
  ultimasTabela(2, 10, window.idEmpresa, window.tipoEmpresa, 'tbodySolicitacoesAceitasSol', 'overlayTabelaSolicitacoesAceitasSol')
}

function consultaSolicitacoesStatus(idEmpresa, tipoEmpresa) {
  //) AJAX - Para consultar o consolidado de solicitações 
  $.ajax({
    url: 'ajaxs/solicitacoesAjax.php',  //) Dentro do PHP de Solicitações
    type: 'POST',
    dataType: 'json',
    data: {
      acao: 'consultaConsolidadoVagas',
      dados: {
        idUsuario: window.idUsuario,
        idEmpresa: idEmpresa,
        tipoEmpresa: tipoEmpresa,
        notificacoes: 1
      }
    },
    beforeSend: function () {
      if (tipoEmpresa == 1) {
        $("#overlayConsolidadoPendentesAnu").removeData('hidden');
        $("#overlayConsolidadoPendentesAnu").addClass('d-flex');
        $("#overlayConsolidadoReservadasAnu").removeData('hidden');
        $("#overlayConsolidadoReservadasAnu").addClass('d-flex');
      } else {
        $("#overlayConsolidadoPendentesSol").removeData('hidden');
        $("#overlayConsolidadoPendentesSol").addClass('d-flex');
      }

      console.log("Consultando consolidado Solicitações")
    },
    success: function (content) {
      if (content != 0) {
        console.log(content)
        for (var i = 0; i < content.length; i++) {
          if (content[i]['status'] == 1) {
            if (tipoEmpresa == 1) {
              document.getElementById('hPendentesAnu').innerHTML = content[i]['total']
            } else {
              document.getElementById('hPendentesSol').innerHTML = content[i]['total']
            }
          }

          if (content[i]['status'] == 2) {
            if (tipoEmpresa == 1) {
              document.getElementById('hReservadasAnu').innerHTML = content[i]['total']
            }
          }

          if (content[i]['status'] == 3) {
            if (tipoEmpresa == 1) {
              //nada
            } else {
              //nada
            }
          }

        }

      } else {
        console.log("Não foi encontrada nenhuma vaga")
      }

      if (tipoEmpresa == 1) {
        $("#overlayConsolidadoPendentesAnu").add('hidden')
        $("#overlayConsolidadoPendentesAnu").removeClass('d-flex')
        $("#overlayConsolidadoReservadasAnu").add('hidden')
        $("#overlayConsolidadoReservadasAnu").removeClass('d-flex')
      } else {
        $("#overlayConsolidadoPendentesSol").add('hidden')
        $("#overlayConsolidadoPendentesSol").removeClass('d-flex')
      }

    }
  })
}

//* CONSULTA  para trazer os últimos registros de solicitações de acordo com as variáveis e salvar na tabela indicada
function ultimasTabela(status, quantidade, idEmpresa, tipoEmpresa, tabela, overlay) {
  //) AJAX - Para consultar os dados para a tabelas 
  $.ajax({
    url: 'ajaxs/solicitacoesAjax.php',  //) Dentro do PHP de Solicitações
    type: 'POST',
    dataType: 'json',
    data: {
      acao: 'consultaUltimasTabelas',
      dados: {
        idEmpresa: idEmpresa,
        tipoEmpresa: tipoEmpresa,
        quantidade: quantidade,
        status: status
      }
    },
    beforeSend: function () {
      if (overlay != '') {
        $("#" + overlay).removeData('hidden');
        $("#" + overlay).addClass('d-flex');
      }
      console.log("Consultando consolidado Solicitações")
    },
    success: function (content) {
      if (content != 0) {
        console.log(content)
        conteudo = ''
        for (var i = 0; i < content.length; i++) {
          console.log(content[i]['status'])
          if (content[i]['categoria'] == 1) {
            var categoria = 'UTI'
          } else if (content[i]['categoria'] == 2) {
            var categoria = 'ENF'
          } else if (content[i]['categoria'] == 3) {
            var categoria = 'PSO'
          }
          conteudo += '<tr><td>' + content[i]['idSolicitacao'] + '</td>'
          conteudo += '<td>' + categoria + '</td>'
          conteudo += '<td>' + content[i]['nomeEmpresa'] + '</td>'
          conteudo += '<td>' + content[i]['nomePaciente'] + '</td></tr>'
        }
        document.getElementById(tabela).innerHTML = conteudo

      } else {
        console.log("Não foi encontrada nenhuma vaga")
      }
      if (overlay != '') {
        $("#" + overlay).add('hidden')
        $("#" + overlay).removeClass('d-flex')
      }
    }
  })
}

function consultaStatusWhatsapp(idEmpresa) {
  $.ajax({
    url: 'ajaxs/usuariosAjax.php',
    type: 'POST',
    dataType: 'json',
    data: {
      acao: 'consultaStatusWhatsapp',
      dados: {
        idEmpresa: idEmpresa
      }

    },
    beforeSend: function () {
      $("#overlayWhatsapp").removeData('hidden');
      $("#overlayWhatsapp").addClass('d-flex');
      console.log("Consultando vagas")
    },
    success: function (content) {
      $("#overlayWhatsapp").add('hidden')
      $("#overlayWhatsapp").removeClass('d-flex')
      console.log('Pesquisado status Whatsapp')
      console.log(content)

      if (content[0]['id_contato'] != 0) { //Alguém logado
        if (content[0]['id_contato'] == window.idUsuario) { //Usuário logado é o usuário de contato
          document.getElementById('iWhatsapp').style.color = '#28a745'
          document.getElementById('aWhatsapp').style.backgroundColor = '#28a745'
          document.getElementById('pWhatsapp').innerHTML = 'Notificando a: <strong>' + content[0]['nome_contato'] + '</strong>'
          document.getElementById('aWhatsapp').onclick = function () { alterarStatusWhatsapp('deslogar') }
          document.getElementById('pWhatsapp').style.color = '#28a745'
          document.getElementById('aWhatsapp').innerHTML = 'Deslogar das notificações <i class="fas fa-times-circle"></i>'
        } else { //Usuário de contato é outro usuário
          document.getElementById('iWhatsapp').style.color = '#bababa'
          document.getElementById('aWhatsapp').style.backgroundColor = '#28a745'
          document.getElementById('pWhatsapp').innerHTML = 'Notificando a: <strong>' + content[0]['nome_contato'] + '</strong>'
          document.getElementById('aWhatsapp').onclick = function () { alterarStatusWhatsapp('logar') }
          document.getElementById('pWhatsapp').style.color = '#bababa'
          document.getElementById('aWhatsapp').innerHTML = 'Trocar para o seu usuário <i class="fas fa-sync-alt"></i>'
        }
      } else {
        document.getElementById('iWhatsapp').style.color = '#dc3545'
        document.getElementById('aWhatsapp').style.backgroundColor = '#dc3545'
        document.getElementById('aWhatsapp').innerHTML = 'Se colocar disponível <i class="fas fa-check-circle"></i>'
        document.getElementById('aWhatsapp').onclick = function () { alterarStatusWhatsapp('logar') }
        document.getElementById('pWhatsapp').style.color = '#dc3545'
        document.getElementById('pWhatsapp').innerHTML = '<strong>NINGUÉM ESTÁ SENDO NOTIFICADO</strong>'
      }
    }
  });
}

function alterarStatusWhatsapp(status) {
  if (status == 'logar') {
    var idContato = window.idUsuario
    var numeroContato = window.numeroUsuario
  } else {
    var idContato = 0
    var numeroContato = ''
  }
  $.ajax({
    url: 'ajaxs/usuariosAjax.php',
    type: 'POST',
    dataType: 'html',
    data: {
      acao: 'alterarStatusWhatsapp',
      dados: {
        idEmpresa: window.idEmpresa,
        idContato: idContato,
        numeroContato: numeroContato
      }
    },
    beforeSend: function () {
      $("#overlayWhatsapp").removeData('hidden');
      $("#overlayWhatsapp").addClass('d-flex');
      console.log("Mudando status whatsapp")
    },
    success: function (content) {
      $("#overlayWhatsapp").add('hidden')
      $("#overlayWhatsapp").removeClass('d-flex')
      console.log('Mudado status whatsapp')
      console.log(content)

      if (content == 1) { //Alguém logado
        if (status == 'logar') {
          document.getElementById('iWhatsapp').style.color = '#28a745'
          document.getElementById('aWhatsapp').style.backgroundColor = '#28a745'
          document.getElementById('pWhatsapp').innerHTML = 'Notificando a: <strong>' + window.nomeUsuarioAbreviado + '</strong>'
          document.getElementById('aWhatsapp').href = "alterarStatusWhatsapp('deslogar')"
          document.getElementById('pWhatsapp').style.color = '#28a745'
          document.getElementById('aWhatsapp').innerHTML = 'Deslogar das notificações <i class="fas fa-times-circle"></i>'
        } else {
          document.getElementById('iWhatsapp').style.color = '#dc3545'
          document.getElementById('aWhatsapp').style.backgroundColor = '#dc3545'
          document.getElementById('aWhatsapp').innerHTML = 'Se colocar disponível <i class="fas fa-check-circle"></i>'
          document.getElementById('aWhatsapp').href = "alterarStatusWhatsapp('logar')"
          document.getElementById('pWhatsapp').style.color = '#dc3545'
          document.getElementById('pWhatsapp').innerHTML = '<strong>NINGUÉM ESTÁ SENDO NOTIFICADO</strong>'
        }
      }
    }
  })
}

document.getElementById("selectNomeEmpresa").onchange = function () {
  var comboCidades = document.getElementById("selectNomeEmpresa")
  console.log("O indice é: " + comboCidades.selectedIndex)
  console.log("O texto é: " + comboCidades.options[comboCidades.selectedIndex].text)
  console.log("A chave é: " + comboCidades.options[comboCidades.selectedIndex].value)
  console.log(comboCidades.options[comboCidades.selectedIndex].attributes['data-tipo'].value)

  $.ajax({
    url: 'ajaxs/usuariosAjax.php',
    type: 'POST',
    dataType: 'html',
    data: {
      acao: 'selecaoEmpresasMaster',
      dados: {
        idEmpresa: comboCidades.options[comboCidades.selectedIndex].value,
        nomeEmpresa: comboCidades.options[comboCidades.selectedIndex].text,
        tipoEmpresa: comboCidades.options[comboCidades.selectedIndex].attributes['data-tipo'].value
      }
    },
    beforeSend: function () {
      console.log('Escolhendo empresa usuário MASTER')
    },
    success: function (content) {
      console.log('Escolhida empresa usuário MASTER')

      document.getElementById('imgEmpresaMenu').src = 'assets/empresas/' + comboCidades.options[comboCidades.selectedIndex].value + '.png?random=' + new Date().getTime();

      window.location.href = "home.php"
    }
  })
}

function tela(url) {
  //$("#main").load(url);
  $.ajax({
    type: 'GET',
    dataType: 'html',
    contentType: "charset=utf-8",
    url: url,
    beforeSend: function () {
      $("#main").html("<div id='overlay' style='background-color: white; height: 100%;' class='overlay justify-content-center align-items-center d-flex'><i style='color:#00A599' class='fas fa-2x fa-sync fa-spin'></i></div>");
    },
    success: function (content) {
      $('html,body').scrollTop(0);
      $("#main").load(url);
      $("#asideBar").removeClass('sidebar-focused')
      //$('[data-widget="pushmenu"]').PushMenu('toggle')

    }
  });
}
function verificarNotificacoes() {
  clearInterval(window.intervalo);

  $.ajax({
    type: 'POST',
    dataType: 'json',
    url: 'ajaxs/notificacoesAjax.php',
    data: {
      acao: 'verificarNotificacao',
      dados: {
        idUsuario: window.idUsuario,
        idEmpresa: window.idEmpresa,
      }
    },
    beforeSend: function () {
      console.log('Verificando Notificações')
    },
    success: function (content) {
      console.log('Verificadas Notificações')
      console.log(content)
      var temNotificacao = 0
      for (var i = 0; i < content.length; i++) {
        switch (content[i]['acao']) {
          case "1":
            temNotificacao = 1
            window.notificacaoPendente = content[i]['quant']
            console.log("Aumentou o 1 para " + window.notificacaoPendente)
            break;
          case "2":
            temNotificacao = 1
            window.notificacaoAceita = content[i]['quant']
            console.log("Aumentou o 2 para " + window.notificacaoAceita)
            break;
          case "3":
            temNotificacao = 1
            window.notificacaoNegada = content[i]['quant']
            console.log("Aumentou o 3 para " + window.notificacaoNegada)
            break;
        }
      }
      if (temNotificacao != 0) {
        notificar()
      }
    }
  })
}

function notificar() {
  console.log("Entrou no notificar")
  var notificacao = 0
  container = document.getElementById('painelNotificacoes')
  container.innerHTML = ""
  var conteudo = ""

  //TRATAMENTO PENDENTES
  if (window.notificacaoPendente != 0) {
    notificacao += parseInt(window.notificacaoPendente)
    if (window.notificacaoPendente == 1) {
      var textoNotificacao = 'solicitação pendente'
    } else {
      var textoNotificacao = 'solicitações pendentes'
    }
    conteudo = '<a onclick="aNotificacao()" href="#" class="dropdown-item">'
    conteudo += '<i style="color: #ffc107" class="far fa-clipboard nav-icon mr-2"></i> ' + window.notificacaoPendente + ' ' + textoNotificacao
    conteudo += '</a>'
    container.innerHTML += conteudo
  }

  //TRATAMENTO ACEITAS
  if (window.notificacaoAceita != 0) {
    notificacao += parseInt(window.notificacaoAceita)
    if (window.notificacaoAceita == 1) {
      var textoNotificacao = 'solicitação aceita'
    } else {
      var textoNotificacao = 'solicitações aceitas'
    }
    conteudo = '<a onclick="aNotificacao()" href="#" class="dropdown-item">'
    conteudo += '<i style="color: #28a745" class="far fa-clipboard nav-icon mr-2"></i> ' + window.notificacaoAceita + ' ' + textoNotificacao
    conteudo += '</a>'
    container.innerHTML += conteudo
  }

  //TRATAMENTO NEGADAS
  if (window.notificacaoNegada != 0) {
    notificacao += parseInt(window.notificacaoNegada)
    if (window.notificacaoNegada == 1) {
      var textoNotificacao = 'solicitação negada'
    } else {
      var textoNotificacao = 'solicitações negadas'
    }
    conteudo = '<a onclick="aNotificacao()" href="#" class="dropdown-item">'
    conteudo += '<i style="color: #dc3545" class="far fa-clipboard nav-icon mr-2"></i> ' + window.notificacaoNegada + ' ' + textoNotificacao + '<span class="float-right text-muted text-sm">1m</span>'
    conteudo += '</a>'
    container.innerHTML += conteudo
  }
  console.log(notificacao)

  if (notificacao > 0) {
    console.log("maior que 0")
    document.getElementById('spanNotificacao').innerHTML = notificacao
    document.getElementById('spanNotificacao').style.visibility = 'visible'
    if (notificacao > window.totalNotificacoes) {
      notificacaoNavegador('VAGAMED', 'Há solicitações alteradas, clique para acessar.')
    }
    window.totalNotificacoes = notificacao

  } else {
    document.getElementById('spanNotificacao').innerHTML = ''
    document.getElementById('spanNotificacao').style.visibility = 'hidden'
  }
  window.intervalo = window.setInterval(verificarNotificacoes, window.tempoAtualizacao)

}
function aNotificacao() {
  document.getElementById('painelNotificacoes').innerHTML = ""
  document.getElementById('spanNotificacao').innerHTML = ''
  document.getElementById('spanNotificacao').style.visibility = 'hidden'

  //console.log('Clicado na notificacao')
  window.totalNotificacoes = 0
  activeMenu('#btnSolicitacoes')
  tela('solicitacoes.php')
}

$("#btnTeste").click(function () {
  console.log('Clicou no botão de teste')

  $.ajax({
    type: 'POST',
    dataType: 'html',
    url: 'whatsapp.php',
    data: {
      acao: 'envio'
      /* ,
      dados: {
        idUsuario: window.idUsuario,
        idEmpresa: window.idEmpresa,
      } */
    },
    beforeSend: function () {
      console.log('Tentando envio')
    },
    success: function (content) {
      console.log('Tentativa finalizada')
      console.log(content)

    }
  })

})

$("#btnConsultar").click(function () {
  activeMenu('#btnConsultar')
  tela('consulta.php')
});
$("#btnEmpresa").click(function () {
  //$('[data-widget="pushmenu"]').PushMenu('collapse')
  //https://adminlte.io/docs/3.0/javascript/push-menu.html
  activeMenu('#btnEmpresa')
  tela('empresa.php')
});
$("#btnEmpresaProfile").click(function () {
  acessoVagas()
});
$("#btn-empresa").click(function () {
  activeMenu('#btn-empresa')
  tela('empresa.php')
});
$("#btnUsuarios").click(function () {
  activeMenu('#btnUsuarios')
  tela('usuarios.php')
});
$("#btnCreditos").click(function () {
  activeMenu('#btnCreditos')
  tela('creditos.php')
});
$("#btnConfiguracoes").click(function () {
  activeMenu('#btnConfiguracoes')
  tela('configuracoes.php')
});
$("#btnClientes").click(function () {
  activeMenu('#btnClientes')
  tela('clientes.php')
});
$("#btnProspectos").click(function () {
  activeMenu('#btnProspectos')
  tela('prospectos.php')
});
$("#btnEnvios").click(function () {
  activeMenu('#btnEnvios')
  tela('envios.php')
});
$("#btnListas").click(function () {
  activeMenu('#btnListas')
  tela('listas.php')
});
$("#btnContas").click(function () {
  activeMenu('#btnContas')
  tela('contas.php')
});
$("#btnCampanhas").click(function () {
  activeMenu('#btnCampanhas')
  tela('campanhas.php')
});
$("#btnAudios").click(function () {
  activeMenu('#btnAudios')
  tela('audios.php')
});
$("#btnCampanhasVoz").click(function () {
  activeMenu('#btnCampanhasVoz')
  tela('campanhasvoz.php')
});

function activeMenu(item_ativo) {
  if (item_ativo == '#btnConsultar') {
    $("#btnConsultar").addClass('active')
  } else {
    $("#btnConsultar").removeClass('active')
  }
  if (item_ativo == '#btnEmpresa') {
    $("#btnEmpresa").addClass('active')
  } else {
    $("#btnEmpresa").removeClass('active')
  }
  if (item_ativo == '#btnUsuarios') {
    $("#btnUsuarios").addClass('active')
  } else {
    $("#btnUsuarios").removeClass('active')
  }
  if (item_ativo == '#btnRelatorios') {
    $("#btnRelatorios").addClass('active')
  } else {
    $("#btnRelatorios").removeClass('active')
  }
  if (item_ativo == '#btnCreditos') {
    $("#btnCreditos").addClass('active')
  } else {
    $("#btnCreditos").removeClass('active')
  }
  if (item_ativo == '#btnConfiguracoes') {
    $("#btnConfiguracoes").addClass('active')
  } else {
    $("#btnConfiguracoes").removeClass('active')
  }
  if (item_ativo == '#btnClientes') {
    $("#btnClientes").addClass('active')
  } else {
    $("#btnClientes").removeClass('active')
  }
  if (item_ativo == '#btnProspectos') {
    $("#btnProspectos").addClass('active')
  } else {
    $("#btnProspectos").removeClass('active')
  }
  if (item_ativo == '#btnEnvios') {
    $("#btnEnvios").addClass('active')
  } else {
    $("#btnEnvios").removeClass('active')
  }
  if (item_ativo == '#btnContas') {
    $("#btnContas").addClass('active')
  } else {
    $("#btnContas").removeClass('active')
  }
  if (item_ativo == '#btnListas') {
    $("#btnListas").addClass('active')
  } else {
    $("#btnListas").removeClass('active')
  }
  if (item_ativo == '#btnCampanhas') {
    $("#btnCampanhas").addClass('active')
  } else {
    $("#btnCampanhas").removeClass('active')
  }
}

function notificacaoNavegador(titulo, texto) {

  console.log("Chamou a notificação")
  var notification = new Notification(titulo, {
    icon: 'assets/logo.png',
    body: texto
  })
  notification.onclick = function () {
    window.focus()
  }
}
/*
document.getElementById('main').addEventListener("mouseover", function () {
  console.log('Saiu do menu')
}) */