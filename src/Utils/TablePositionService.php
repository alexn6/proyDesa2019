<?php

namespace App\Utils;

class TablePositionService{

    // ###########################################################################
    // ########################## CONFIG FIREBASE FCM ############################
    // const URL_API_FCM = "https://fcm.googleapis.com/fcm/send";
    // const TOKEN_ACCES_SERVER_FIREBASE = 'AAAAIv70EkE:APA91bGetKCqMPfkVuRLn7nT9qqMgUdzc9mN5lB-ny9_XX1gQjdITjfcFE2NxPC_3I3c43XzwcVb8Y6RvT5I55hhScGpT8zDfkWKdFcXdlTCDhTHJo42ahHF-PI4bhrOcfxUqGrRz-lT';

    // ###########################################################################
    // ###########################################################################

    public function getTablePosition($resultCompetitors, $ptsByResult){
        // recuperamos los puntos segun el deporte
        $ptsGanado = $ptsByResult["ganado"];
        $ptsEmpatado = $ptsByResult["empatado"];
        $ptsPerdido = $ptsByResult["perdido"];

        // calculamos los puntos de cada competidor
        for ($i=0; $i < count($resultCompetitors) ; $i++) {
            $pg = $resultCompetitors[$i]['PG'];
            $pe = $resultCompetitors[$i]['PE'];
            $pp = $resultCompetitors[$i]['PP'];
            $ptsTotal = $pg*$ptsGanado + $pe*$ptsEmpatado + $pp*$ptsPerdido;
            $resultCompetitors[$i]['Pts'] = $ptsTotal;
        }

        // ordenamos los resultados por puntos
        usort($resultCompetitors, function($a, $b) {
            return strnatcmp($a['Pts'], $b['Pts']);
            }
        );

        return $resultCompetitors;
    }
}