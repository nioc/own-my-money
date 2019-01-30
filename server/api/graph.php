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
                //determine the best time unit according to requested duration
                $duration = $periodEnd - $periodStart;
                $timeUnit = 'D';
                if ($duration > 15724800) {
                    $timeUnit = 'M';
                } elseif ($duration > 2678400) {
                    $timeUnit = 'W';
                }
                if ($api->checkParameterExists('category', $category)) {
                    $response->category = $category;
                }
                if ($api->checkParameterExists('subcategory', $subcategory)) {
                    $response->subcategory = $subcategory;
                }
                //request transactions history
                $values = $user->getTransactionsHistory($periodStart, $periodEnd, $timeUnit, true, $category, $subcategory);
                if (!$values) {
                    $api->output(500, '');
                    //something go wrong
                    return;
                }
                $response->values = $values;
                $response->timeUnit = $timeUnit;
                break;
            case 'distribution':
                if (!$api->checkParameterExists('key', $key)) {
                    $api->output(400, $api->getMessage('keyMustBeProvidedInPath'));
                    //no distribution key provided, return error
                    return;
                }
                if (!$api->checkParameterExists('type', $type)) {
                    $api->output(400, $api->getMessage('typeMustBeProvidedInQuery'));
                    //no transaction type provided, return error
                    return;
                }
                if (!in_array($type, ['debit', 'credit'])) {
                    $api->output(400, $api->getMessage('typeMustBeInEnum'));
                    //no graph type provided, return error
                    return;
                }
                $api->checkParameterExists('value', $value);
                //request transactions distribution
                $values = $user->getTransactionsDistribution($periodStart, $periodEnd, $type, $key, $value, true);
                if ($values === false) {
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
