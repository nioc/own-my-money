<?php

/**
 * map API.
 *
 * Provides map informations
 *
 * @version 1.0.0
 *
 * @api
 */
require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Api.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Map.php';
$api = new Api('json', ['GET']);
switch ($api->method) {
    case 'GET':
        if (!$api->checkAuth()) {
            //User not authentified/authorized
            return;
        }
        //query all maps
        $maps = Map::getAll();
        $api->output(200, $maps);
        //return maps list
        return;
        break;
}
