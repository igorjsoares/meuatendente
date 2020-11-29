<?php

// Arquivo PHP para realizar funções da consulta e marcação de horários
// By Igor Soares

//* Consulta os meses disponíveis 
function fctConsultaMeses()
{
    logSis('DEB', 'Entrou no fctConsultaMeses');
    include_once("servicos.php");

    $resultado = fctConsultaParaArray(
        'ConsultaMês',
        "SELECT MONTH(horario) AS mes FROM tbl_horarios WHERE status = 1 GROUP BY MONTH(horario)"
    );

    if ($resultado == false) {
        return false;
    } else {
        //return $resultado;
        $arrayResultado = [];
        while ($linha = $resultado) {
            /* array_push($arrayResultado, array(
                'mes' => $linha['mes'],
                'nome_mes' => fctNomeMes($linha['mes'])
            )); */
            logSis('DEB', 'Encontrado mês: ' . $linha['mes']);
        } 
        return $arrayResultado;
       
    }
}

//* Consulta os dias com horários disponíveis apartir do mês 
function fctConsultaDias($mes)
{
    include_once("servicos.php");

    $resultado = fctConsultaParaArray(
        'ConsultaDias',
        "SELECT day(horario) AS dia FROM tbl_horarios WHERE status = 1 AND month(horario) = $mes GROUP BY day(horario)"
    );

    if ($resultado == false) {
        return false;
    } else {
        $arrayResultado = [];
        while ($linha = $resultado) {
            array_push($arrayResultado, array(
                'dia' => $linha['dia'],
                'nome_dia' => fctNomeSemana($linha['dia'])
            ));
        }
        return $arrayResultado;
    }
}

//* Consulta os horários disponíveis a partir do dia e do mês 
function fctConsultaHorarios($dia, $mes)
{
    include_once("servicos.php");

    $resultado = fctConsultaParaArray(
        'ConsultaHorarios',
        "SELECT id_horario, day(horario) AS dia, WEEKDAY(horario) AS dia_semana, DATE_FORMAT(horario, '%H:%i') AS hora FROM tbl_horarios WHERE status = 1 AND month(horario) = $mes AND day(horario) = $dia"
    );

    if ($resultado == false) {
        return false;
    } else {
        $arrayResultado = [];
        while ($linha = $resultado) {
            array_push($arrayResultado, array(
                'id_horario' => $linha['dia'],
                'dia' => $linha['dia'],
                'dia_semana' => fctNomeSemana($linha['dia_semana']),
                'hora' => $linha['hora']
            ));
        }
        return $arrayResultado;
    }
}

//* Consulta os horários pendentes do cliente 
function fctConsultaMeusHorarios($idContato)
{
    include_once("servicos.php");

    $resultado = fctConsultaParaArray(
        'ConsultaMeusHorarios',
        "SELECT id_horario, day(horario) AS dia, WEEKDAY(horario) AS dia_semana, DATE_FORMAT(horario, '%H:%i') AS hora FROM tbl_horarios WHERE status = 1 AND id_contato = $idContato"
    );

    if ($resultado == false) {
        return false;
    } else {
        $arrayResultado = [];
        while ($linha = $resultado) {
            array_push($arrayResultado, array(
                'id_horario' => $linha['dia'],
                'dia' => $linha['dia'],
                'dia_semana' => fctNomeSemana($linha['dia_semana']),
                'hora' => $linha['hora']
            ));
        }
        return $arrayResultado;
    }
}
