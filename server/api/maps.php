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
$api = new Api('json', ['GET', 'POST', 'PUT', 'DELETE']);
switch ($api->method) {
    case 'GET':
        if (!$api->checkAuth()) {
            //User not authentified/authorized
            return;
        }
        if ($api->checkParameterExists('code', $code)) {
            $map = new Map($code);
            //query a specific map
            if (!$map->get()) {
                $api->output(404, 'Map not found');
                //indicate the map was not found
                return;
            }
            $map->getAttributes();
            $api->output(200, $map);
            //return map
            return;
        }
        //query all maps
        $maps = Map::getAll();
        $api->output(200, $maps);
        //return maps list
        return;
        break;

    case 'POST':
        if (!$api->checkAuth()) {
            //User not authentified/authorized
            return;
        }
        $map = new Map();
        $requestedMap = $api->query['body'];
        if (!$map->validateModel($requestedMap, $errorMessage)) {
            $api->output(400, 'Map is not valid: '.$errorMessage);
            //provided map is not valid
            return;
        }
        if (!$map->insert($error)) {
            $api->output(500, 'Error during map creation'.$error);
            //something gone wrong :(
            return;
        }
        if (!$map->setAttributes()) {
          $api->output(500, 'Error during attributes creation');
          //something gone wrong :(
          return;
        }
        $map->get();
        $api->output(201, $map);
        //provided map is created
        return;
        break;

    case 'PUT':
        if (!$api->checkAuth()) {
            //User not authentified/authorized
            return;
        }
        if ($api->checkParameterExists('code', $code)) {
            $map = new Map($code);
            //query a specific map
            if (!$map->get()) {
                $api->output(404, 'Map not found');
                //indicate the map was not found
                return;
            }
        }
        $map = new Map();
        $requestedMap = $api->query['body'];
        if (!$map->validateModel($requestedMap, $errorMessage)) {
            $api->output(400, 'Map is not valid: '.$errorMessage);
            //provided map is not valid
            return;
        }
        if ($code !== $map->code) {
            $api->output(400, 'Code can not be changed');
            //something gone wrong :(
            return;
        }
        if (!$map->update($error)) {
            $api->output(500, 'Error during map update'.$error);
            //something gone wrong :(
            return;
        }
        if (!$map->setAttributes()) {
          $api->output(500, 'Error during attributes update');
          //something gone wrong :(
          return;
        }
        $map->get();
        $api->output(200, $map);
        //provided map is updated
        return;
        break;

    case 'DELETE':
        if (!$api->checkAuth()) {
            //User not authentified/authorized
            return;
        }
        if ($api->checkParameterExists('code', $code)) {
            $map = new Map($code);
            //query a specific map
            if (!$map->get()) {
                $api->output(404, 'Map not found');
                //indicate the map was not found
                return;
            }
        }
        $map = new Map($code);
        if (!$map->delete($error)) {
            $api->output(500, 'Error during map update'.$error);
            //something gone wrong :(
            return;
        }
        $api->output(204);
        //provided map is deleted
        return;
        break;
}
