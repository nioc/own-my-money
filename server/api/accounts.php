<?php

/**
 * account API.
 *
 * Provides account informations
 *
 * @version 1.0.0
 *
 * @api
 */
require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Api.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Account.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/User.php';
$api = new Api('json', ['GET', 'POST', 'DELETE', 'PUT']);
switch ($api->method) {
    case 'GET':
        //returns the account
        if (!$api->checkAuth()) {
            //User not authentified/authorized
            return;
        }
        if (!$api->checkParameterExists('id', $id)) {
            //query all user's account
            $user = new User($api->requesterId);
            $accounts = array();
            foreach ($user->getAccounts() as $account) {
                array_push($accounts, $account->structureData());
            }
            $api->output(200, $accounts);
            //return user accounts list
            return;
        }
        $account = new Account($id);
        //query a specific account
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
        $account->isOwned = $account->user === $api->requesterId;
        $api->output(200, $account->structureData());
        break;
    case 'POST':
        //account creation
        if (!$api->checkAuth()) {
            //User not authentified/authorized
            return;
        }
        //@TODO add controls
        $account = new Account();
        $account->user = $api->requesterId;
        $api->checkParameterExists('bankId', $account->bankId);
        $api->checkParameterExists('branchId', $account->branchId);
        $api->checkParameterExists('accountId', $account->accountId);
        $api->checkParameterExists('label', $account->label);
        if ($account->getByPublicId($account->user, $account->bankId, $account->branchId, $account->accountId)) {
            $api->output(400, $api->getMessage('accountAlreadyExists'));
            return;
        }
        if ($account->insert()) {
            $api->output(201, $account->structureData());
            return;
        }
        $api->output(500, $api->getMessage('somethingWentWrong'));
        break;
    case 'PUT':
        //returns the account
        if (!$api->checkAuth()) {
            //User not authentified/authorized
            return;
        }
        if (!$api->checkParameterExists('id', $id)) {
            $api->output(400, $api->getMessage('accountIdMustBeProvided'));
            //indicate the request is not valid
            return;
        }
        $account = new Account($id);
        if (!$account->get()) {
            $api->output(404, $api->getMessage('accountNotFound'));
            //indicate the account was not found
            return;
        }
        if ($account->user !== $api->requesterId) {
            $api->output(403, $api->getMessage('accountCanBeUpdatedByOwnerOnly'));
            //indicate the requester is not the account owner and is not allowed to update it
            return;
        }
        if ($api->checkParameterExists('bankId', $bankId)) {
            $account->bankId = $bankId;
        }
        if ($api->checkParameterExists('branchId', $branchId)) {
            $account->branchId = $branchId;
        }
        if ($api->checkParameterExists('accountId', $accountId)) {
            $account->accountId = $accountId;
        }
        if ($api->checkParameterExists('label', $label)) {
            $account->label = $label;
        }
        if ($api->checkParameterExists('duration', $duration)) {
            try {
                new DateInterval($duration);
                $account->duration = $duration;
            } catch (Exception $e) {
                $api->output(400, $api->getMessage('invalidDuration'));
                return;
            }
        }
        if ($api->checkParameterExists('balance', $balance)) {
            $account->balance = $balance;
        }
        if ($account->update()) {
            $api->output(200, $account->structureData());
            return;
        }
        $api->output(500, $api->getMessage('somethingWentWrong'));
        break;
    case 'DELETE':
        //account deletion
        if (!$api->checkAuth()) {
            //User not authentified/authorized
            return;
        }
        if (!$api->checkParameterExists('id', $id)) {
            $api->output(400, $api->getMessage('accountIdMustBeProvided'));
            //indicate the request is not valid
            return;
        }
        $account = new Account($id);
        if (!$account->get()) {
            $api->output(404, $api->getMessage('accountNotFound'));
            //indicate the account was not found
            return;
        }
        if ($account->user !== $api->requesterId) {
            $api->output(403, $api->getMessage('accountCanBeDeletedByOwnerOnly'));
            //indicate the requester is not the account owner and is not allowed to delete it
            return;
        }
        if ($account->delete()) {
            $api->output(204);
            return;
        }
        $api->output(500, $api->getMessage('somethingWentWrong'));
        break;
}
