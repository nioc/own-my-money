<?php

/**
 * Transaction graph API.
 *
 * Provides transactions history and distribution informations
 *
 * @version 1.0.0
 *
 * @api
 */
require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Api.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/User.php';
$api = new Api('json', ['GET']);
switch ($api->method) {
    case 'GET':
        if (!$api->checkAuth()) {
            //User not authentified/authorized
            return;
        }
        if (!$api->checkParameterExists('request', $request)) {
            $api->output(404, '');
            //no graph type provided, return error
            return;
        }
        $user = new User($api->requesterId);
        //get requested period
        if (!$api->checkParameterExists('periodStart', $periodStart) || $periodStart === '') {
            $periodStart = time() - (90 * 24 * 60 * 60);
        }
        $periodStart = intval($periodStart);
        if (!$api->checkParameterExists('periodEnd', $periodEnd) || $periodEnd === '') {
            $periodEnd = time();
        }
        $periodEnd = intval($periodEnd);
        $response = new stdClass();
        switch ($request) {
            case 'history':
                //request transactions history
                $values = $user->getTransactionsHistory($periodStart, $periodEnd);
                if (!$values) {
                    $api->output(500, '');
                    //something go wrong
                    return;
                }
                $response->values = $values;
                break;
            case 'distribution':
                if (!$api->checkParameterExists('key', $key)) {
                    $api->output(400, 'Key attribute must be provided in path');
                    //no distribution key provided, return error
                    return;
                }
                if (!$api->checkParameterExists('type', $type)) {
                    $api->output(400, 'Type attribute must be provided in query string');
                    //no transaction type provided, return error
                    return;
                }
                if (!in_array($type, ['debit', 'credit'])) {
                    $api->output(400, 'Type attribute must be `debit` or `credit`');
                    //no graph type provided, return error
                    return;
                }
                $api->checkParameterExists('value', $value);
                //request transactions distribution
                $values = $user->getTransactionsDistribution($periodStart, $periodEnd, $type, $key, $value);
                if (!$values) {
                    $api->output(500, '');
                    //something go wrong
                    return;
                }
                $response->values = $values;
                $response->type = $type;
                $response->key = $key;
                break;

            default:
                $api->output(404, '');
                //no request type provided, return error
                return;
                break;
        }
        $response->periodStart = date('c', $periodStart);
        $response->periodEnd = date('c', $periodEnd);
        $api->output(200, $response);
        break;
}
