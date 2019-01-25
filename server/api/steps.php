<?php

/**
 * steps API.
 *
 * Provides process steps informations and allow to move forward by posting form to complete a step
 *
 * @version 1.0.0
 *
 * @api
 */
require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Api.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Step.php';
$api = new Api('json', ['GET', 'PUT']);

//check server is not already installed
require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Configuration.php';
$configuration = new Configuration();
if ($api->method !== 'OPTIONS' && $configuration->get('setup') === '1') {
    //server already installed, requester need to have an admin scope
    if (!$api->checkAuth()) {
        //User not authentified/authorized
        return;
    }
    if (!$api->checkScope('admin')) {
        $api->output(403, $api->getMessage('adminScopeRequired'));
        //indicate the requester is not allowed to update it
        return;
    }
}

switch ($api->method) {
    case 'GET':
        //query steps
        $steps = Step::getAll($api->language);
        if (is_string($steps)) {
            $api->output(500, $steps);
            //return steps list
            return;
        }
        $api->output(200, $steps);
        //return steps list
        return;
        break;
    case 'PUT':
        //complete step (by posting a PUT in fields sub-resource)
        if (!$api->checkParameterExists('code', $code)) {
        }
        $step = new Step($api->language, $code);
        if ($step->fields === false) {
            $api->output(404, $api->getMessage('stepNotFound'));
            //indicate the step has no fields to complete
            return;
        }
        //get fields (array of code and value) posted in request body
        $fields = $api->query['body'];
        //process the step with provided fields informations
        $result = $step->process($fields);
        if ($result !== true) {
            $api->output(500, $result);
            //indicate the step process had encountered an error
            return;
        }
        $api->output(200, $fields);
        //return the provided fields
        return;
        break;
}
