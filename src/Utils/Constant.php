<?php

namespace App\Utils;

class Constant{

    const ROL_ESPECTADOR = 'ESPECTADOR';
    const ROL_SEGUIDOR = 'SEGUIDOR';
    const ROL_SOLICITANTE = 'SOLICITANTE';
    const ROL_COMPETIDOR = 'COMPETIDOR';
    const ROL_ORGANIZADOR = 'ORGANIZADOR';
    const ROL_COORGANIZADOR = 'CO-ORGANIZADOR';

    const COD_TIPO_ELIMINATORIAS = 'ELIM';
    const COD_TIPO_LIGA_SINGLE = 'LIGSING';
    const COD_TIPO_LIGA_DOUBLE = 'LIGDOUB';
    const COD_TIPO_ELIMINATORIAS_DOUBLE = 'ELIMDOUB';
    const COD_TIPO_FASE_GRUPOS = 'FASEGRUP';

    const MIN_COMPETIDORES_LIGA = 3;

    const FASE_IDA = -1;
    const FASE_VUELTA = -2;
    const FASE_FINAL = 1;
    const FASE_SEMIFINAL = 2;
    const FASE_CUARTOS = 3;
    const FASE_OCTAVOS = 4;
    const FASE_16AVOS = 5;
    const FASE_32AVOS = 6;

    // servicios propios
    const SERVICES_REST_CAMPO = 'http://132.255.7.152:20203/api/campus';
}