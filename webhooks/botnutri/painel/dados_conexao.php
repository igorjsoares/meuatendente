<?php
header("Content-Type: text/html; charset=UTF-8",true);

// Configurações de conexão
// ==============================
$conn['conectaServidor'] = true;    // Abre uma conex�o com o servidor MySQL?
$conn['abreSessao'] = true;         // Inicia a sess�o com um session_start()?
$conn['validaSempre'] = true;       // Deseja validar o usu�rio e a senha a cada carregamento de p�gina?
// Evita que, ao mudar os dados do usu�rio no banco de dados o mesmo contiue logado.

$conn['servidor'] = 'mysql669.umbler.com:41890';    // Servidor MySQL
$conn['usuario'] = 'prospect_admin';          // Usu�rio MySQL
$conn['senha'] = 'ROGImari1518';                // Senha MySQL
$conn['banco'] = 'newprospect';    			// Banco de dados MySQL

//Token API MessageFlow
$token_messageflow = 'Token c3adcffb-122f-4a71-9fae-b872bb0a6b67';

// ==============================

// Verifica se precisa fazer a conex�o com o MySQL
if ($conn['conectaServidor'] == true) {
$conn['link'] = mysqli_connect($conn['servidor'], $conn['usuario'], $conn['senha']) or die("MySQL: Não foi possível conectar-se ao servidor [".$conn['servidor']."].");
mysqli_select_db($conn['link'], $conn['banco']) or die("MySQL: Não foi possível conectar-se ao banco de dados [".$conn['banco']."].");
}


// Verifica se precisa iniciar a sess�o
if ($conn['abreSessao'] == true) {
	if(!isset($_SESSION)){ 
        session_start(); 
    } 
}
