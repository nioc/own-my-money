<?php

/**
 * pattern API.
 *
 * Provides patterns informations
 *
 * @version 1.0.0
 *
 * @api
 */
require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Api.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Pattern.php';
$api = new Api('json', ['GET', 'POST', 'PUT', 'DELETE']);
switch ($api->method) {
    case 'GET':
        if (!$api->checkAuth()) {
            //User not authentified/authorized
            return;
        }
        if ($api->checkParameterExists('id', $id) && !isset($id)) {
            $pattern = new Pattern($api->requesterId, $id);
            // //query a specific pattern
            if (!$pattern->get()) {
                $api->output(404, 'Pattern not found');
                //indicate the pattern was not found
                return;
            }
            $api->output(200, $pattern->structureData());
            //return pattern
            return;
        }
        //query all patterns
        $patterns = Pattern::getAll($api->requesterId);
        foreach ($patterns as $pattern) {
            $pattern->structureData();
        }
        $api->output(200, $patterns);
        //return patterns list
        return;
        break;

    case 'POST':
        if (!$api->checkAuth()) {
            //User not authentified/authorized
            return;
        }
        $pattern = new Pattern($api->requesterId);
        $requestedPattern = $api->query['body'];
        if (!$pattern->validateModel($requestedPattern, $errorMessage)) {
            $api->output(400, 'Pattern is not valid: '.$errorMessage);
            //provided pattern is not valid
            return;
        }
        if (!$pattern->insert($error)) {
            $api->output(500, 'Error during pattern creation'.$error);
            //something gone wrong :(
            return;
        }
        $pattern->get();
        $api->output(201, $pattern->structureData());
        //provided pattern is created
        return;
        break;

    case 'PUT':
        if (!$api->checkAuth()) {
            //User not authentified/authorized
            return;
        }
        if (!$api->checkParameterExists('id', $id) || $id == '') {
            $api->output(400, 'PUT method must be called on a specific resource');
            //indicate the request is not valid, id must be provided in query path
            return;
        }
        $pattern = new Pattern($api->requesterId, $id);
        //query a specific pattern
        if (!$pattern->get()) {
            $api->output(404, 'Pattern not found');
            //indicate the pattern was not found
            return;
        }
        $requestedPattern = $api->query['body'];
        if (!$pattern->validateModel($requestedPattern, $errorMessage)) {
            $api->output(400, 'Pattern is not valid: '.$errorMessage);
            //provided pattern is not valid
            return;
        }
        if (intval($id) !== $pattern->id) {
            $api->output(400, 'Id can not be changed');
            //something gone wrong :(
            return;
        }
        if (!$pattern->update($error)) {
            $api->output(500, 'Error during pattern update'.$error);
            //something gone wrong :(
            return;
        }
        $pattern->get();
        $api->output(200, $pattern->structureData());
        //provided pattern is updated
        return;
        break;

    case 'DELETE':
        if (!$api->checkAuth()) {
            //User not authentified/authorized
            return;
        }
        if (!$api->checkParameterExists('id', $id) || $id == '') {
            $api->output(400, 'DELETE method must be called on a specific resource');
            //indicate the request is not valid, id must be provided in query path
            return;
        }
        $pattern = new Pattern($api->requesterId, $id);
        //query a specific pattern
        if (!$pattern->get()) {
            $api->output(404, 'Pattern not found');
            //indicate the pattern was not found
            return;
        }
        if (!$pattern->delete($error)) {
            $api->output(500, 'Error during pattern update'.$error);
            //something gone wrong :(
            return;
        }
        $api->output(204);
        //provided pattern is deleted
        return;
        break;
}
