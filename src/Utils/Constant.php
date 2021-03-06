<?php

namespace App\Utils;

class Constant{

    // #############################################################################
    // ################################ APP ANDROID ###############################
    const APP_MOVIL_NAME = 'Proyecto Torneos';

    const CANT_MAX_NOTICIAS = 3;
    const CANT_MAX_NOTICIAS_CLOUD = 5;

    // #############################################################################
    // ################################ FORMATO HORA ###############################
    const FORMAT_DATE_CREATE = 'Y-m-d';
    const FORMAT_DATE = 'd/m/Y';
    const FORMAT_DATE_HOUR = 'H:i:s';
    const FORMAT_DATE_TIME_HOUR = 'H:i:s d/m/Y';

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

    const FASE_DEFAULT_LIGA = -1;
    const FASE_DEFAULT_GRUPOS = 0;

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
    // ############################# GENERO_COMPETENCIA ############################

    const GENERO_MASCULINO = 'MASCULINO';
    const GENERO_FEMENINO = 'FEMENINO';
    const GENERO_MIXTO = 'MIXTO';

    // #############################################################################
    // ############################# ESTADO_COMPETENCIA ############################

    const ESTADO_COMP_SIN_INSCRIPCION = 'COMPETENCIA_SIN_INSCRIPCION';
    const ESTADO_COMP_CON_INSCRIPCION = 'COMPETENCIA_CON_INSCRIPCION';
    const ESTADO_COMP_INSCRIPCION_ABIERTA = 'COMPETENCIA_INSCRIPCION_ABIERTA';
    const ESTADO_COMP_INSCRIPCION_CERRADA = 'COMPETENCIA_INSCRIPCION_CERRADA';
    const ESTADO_COMP_INICIADA = 'COMPETENCIA_INICIADA';
    const ESTADO_COMP_PAUSADA = 'COMPETENCIA_PAUSADA';
    const ESTADO_COMP_SUSPENDIDA = 'COMPETENCIA_SUSPENDIDA';
    const ESTADO_COMP_FINALIZADA = 'COMPETENCIA_FINALIZADA';

    // #############################################################################
    // ############################# ESTADO_INVITACION ############################
    const ESTADO_INV_ALTA = 'ALTA';
    const ESTADO_INV_BAJA = 'BAJA';
    const ESTADO_INV_NO_DEFINIDO = 'N/D';

    // #############################################################################
    // ########################## Config DbCloudFirestore ###########################

    const PROJECT_FIREBASE = 'proyectotorneosfcm';

    // #############################################################################
    // ########################## PARAMETROS Verif-Email ###########################

    // NOTA: las peticiones deben ser con comillas dobles, en caso contrario se pueden agregar
    // caracteres no deseados(amp; por ej) a la hora de armar el string de la peticion
    const URL_API_EMAIL_VERIFICACTION = "https://emailverification.whoisxmlapi.com/api/v1?";
    const API_KEY_EMAIL_VERIFICATION = "at_mxqfktROoumpgCzRktSeRjBBtzxaU";

    // #############################################################################
    // ################################# SERVICIOS #################################

    // servicios propios
    const SERVICES_REST_CAMPO = 'http://132.255.7.152:20203/api/campus';
}