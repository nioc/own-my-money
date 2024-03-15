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
require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Account.php';
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
                if (!$api->checkParameterExists('timeUnit', $timeUnit)) {
                    //determine the best time unit according to requested duration
                    $duration = $periodEnd - $periodStart;
                    $timeUnit = 'D';
                    if ($duration > 15724800) {
                        $timeUnit = 'M';
                    } elseif ($duration > 2678400) {
                        $timeUnit = 'W';
                    }
                }
                if ($api->checkParameterExists('category', $category)) {
                    $response->category = $category;
                }
                if ($api->checkParameterExists('subcategory', $subcategory)) {
                    $response->subcategory = $subcategory;
                }
                $isRecurringOnly = false;
                if ($api->checkParameterExists('isRecurringOnly', $isRecurringOnly)) {
                    $isRecurringOnly = filter_var($isRecurringOnly, FILTER_VALIDATE_BOOLEAN);
                }
                //request transactions history
                if ($api->checkParameterExists('aid', $aid, Api::PARAM_INTEGER)) {
                    //specific account
                    $account = new Account($aid);
                    if (!$account->get()) {
                        $api->output(404, $api->getMessage('accountNotFound'));
                        //indicate the requester is not the account owner and is not allowed to query it
                        return;
                    }
                    if ($account->user !== $api->requesterId && !$account->isHoldBy($api->requesterId, $error)) {
                        $api->output(403, $api->getMessage('transactionsCanBeQueriedByAccountOwnerOnly'));
                        //indicate the requester is not the account owner and is not allowed to query it
                        return;
                    }
                    $values = $account->getTransactionsHistory($periodStart, $periodEnd, $timeUnit, false, $category, $subcategory, $isRecurringOnly);
                } else {
                    //all user accounts
                    $values = $user->getTransactionsHistory($periodStart, $periodEnd, $timeUnit, true, $category, $subcategory, $isRecurringOnly);
                }
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
                $isRecurringOnly = false;
                if ($api->checkParameterExists('isRecurringOnly', $isRecurringOnly)) {
                    $isRecurringOnly = filter_var($isRecurringOnly, FILTER_VALIDATE_BOOLEAN);
                }
                $api->checkParameterExists('value', $value);
                //request transactions distribution
                $values = $user->getTransactionsDistribution($periodStart, $periodEnd, $type, $key, $value, true, $isRecurringOnly);
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
