<?php

/**
 * account dispatch API.
 *
 * Provides account dispatch by categories and users
 *
 * @version 1.0.0
 *
 * @api
 */
require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Api.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Account.php';
$api = new Api('json', ['GET']);
switch ($api->method) {
    case 'GET':
        if (!$api->checkAuth()) {
            //User not authentified/authorized
            return;
        }
        if (!$api->checkParameterExists('aid', $id, Api::PARAM_INTEGER)) {
            $api->output(400, $api->getMessage('accountIdMustBeProvided'));
            //indicate the account id was not found in query
            return;
        }
        $account = new Account($id);
        if (!$account->get()) {
            $api->output(404, $api->getMessage('accountNotFound'));
            //indicate the account was not found
            return;
        }
        if ($account->user !== $api->requesterId && !$account->isHoldBy($api->requesterId, $error)) {
            $api->output(403, $api->getMessage('transactionsCanBeQueriedByAccountOwnerOnly'));
            //indicate the requester is not the account owner and is not allowed to query it
            return;
        }
        $currentTimestamp = time();
        if (!$api->checkParameterExists('periodStart', $periodStart)) {
            $periodStart = $currentTimestamp - 90*24*60*60;
        }
        if (!$api->checkParameterExists('periodEnd', $periodEnd)) {
            $periodEnd = $currentTimestamp;
        }
        $data = $account->getTransactionsDispatch($periodStart, $periodEnd);
        $api->output(200, $data);
    }
