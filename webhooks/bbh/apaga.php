<?php
include("dados_conexao.php");

$sql = "TRUNCATE tbl_contatos";
$resultado = mysqli_query($conn['link'], $sql);
if ($resultado == '1') {
    echo 'APAGADOS DADOS DA TBL_CONTATOS';
}

$sql = "TRUNCATE tbl_interacoes";
$resultado = mysqli_query($conn['link'], $sql);
if ($resultado == '1') {
    echo '<BR>APAGADOS DADOS DA TBL_INTERAÇÕES';
}

unlink("log.txt");
