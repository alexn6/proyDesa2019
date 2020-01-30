<?php

namespace App\Utils;

class Constant{

    // #############################################################################
    // ################################# ROLES #####################################

    const ROL_ESPECTADOR = 'ESPECTADOR';
    const ROL_SEGUIDOR = 'SEGUIDOR';
    const ROL_SOLICITANTE = 'SOLICITANTE';
    const ROL_COMPETIDOR = 'COMPETIDOR';
    const ROL_ORGANIZADOR = 'ORGANIZADOR';
    const ROL_COORGANIZADOR = 'CO-ORGANIZADOR';

    // #############################################################################
    // ################################# TIPO TORNEO ###############################

    const COD_TIPO_ELIMINATORIAS = 'ELIM';
    const COD_TIPO_LIGA_SINGLE = 'LIGSING';
    const COD_TIPO_LIGA_DOUBLE = 'LIGDOUB';
    const COD_TIPO_ELIMINATORIAS_DOUBLE = 'ELIMDOUB';
    const COD_TIPO_FASE_GRUPOS = 'FASEGRUP';

    const MIN_COMPETIDORES_LIGA = 3;

    // #############################################################################
    // ############################### FASES TORNEO ################################

    const FASE_IDA = -1;
    const FASE_VUELTA = -2;
    const FASE_FINAL = 1;
    const FASE_SEMIFINAL = 2;
    const FASE_CUARTOS = 3;
    const FASE_OCTAVOS = 4;
    const FASE_16AVOS = 5;
    const FASE_32AVOS = 6;

    const ELIM_IDA = 1;
    const ELIM_VUELTA = 2;

    // #############################################################################
    // ############################# ESTADO_COMPETENCIA ############################

    const GENERO_MASCULINO = 'MASCULINO';
    const GENERO_FEMENINO = 'FEMENINO';
    const GENERO_MIXTO = 'MIXTO';

    // #############################################################################
    // ############################# ESTADO_COMPETENCIA ############################

    const ESTADO_SIN_INSCRIPCION = 'COMPETENCIA_SIN_INSCRIPCION';
    const ESTADO_INSCRIPCION_ABIERTA = 'COMPETENCIA_INSCRIPCION_ABIERTA';
    const ESTADO_INICIADA = 'COMPETENCIA_INICIADA';
    const ESTADO_FINALIZADA = 'COMPETENCIA_FINALIZADA';

    // #############################################################################
    // ################################# SERVICIOS #################################

    // servicios propios
    const SERVICES_REST_CAMPO = 'http://132.255.7.152:20203/api/campus';
}