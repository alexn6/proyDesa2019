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
    const ROL_SOLCOORG = 'SOL-COORG';

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
    // ############################# ESTADO_INVITACION ############################
    const ESTADO_ALTA = 'ALTA';
    const ESTADO_BAJA = 'BAJA';
    const ESTADO_NO_DEFINIDO = 'N/D';

    // #############################################################################
    // ############################ PARAMETROS SWIFMAIL ############################

    // IMPORTANTE: para terminar de configurar el envio de los mails se debe permitir el envio
    // de mjes desde la cuenta de gmail y permitir el acceso de app de poca confianza a la cuenta

    const SWFMAILER_SERVER_SMTP = 'smtp.gmail.com';
    const SWFMAILER_SERVER_SMTP_PORT = 587;
    const SWFMAILER_SERVER_SMTP_SECURE = 'tls';
    const SWFMAILER_SERVER_SMTP_USER = 'alex6tc90@gmail.com';
    const SWFMAILER_SERVER_SMTP_USER_PASS = 'G1m2a3i4l';

    // #############################################################################
    // ################################# SERVICIOS #################################

    // servicios propios
    const SERVICES_REST_CAMPO = 'http://132.255.7.152:20203/api/campus';
}