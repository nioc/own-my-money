<?php

/**
 * account holders API.
 *
 * Provides account holders informations
 *
 * @version 1.0.0
 *
 * @api
 */
require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Api.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Account.php';
$api = new Api('json', ['GET', 'POST', 'DELETE', 'PUT']);
switch ($api->method) {
    case 'GET':
        //returns account holders
        if (!$api->checkAuth()) {
            //User not authentified/authorized
            return;
        }
        if (!$api->checkParameterExists('aid', $aid)) {
            $api->output(400, $api->getMessage('accountIdMustBeProvided'));
            //indicate the account id was not provided
            return;
        }
        //query all account holders
        $account = new Account($aid);
        if (!$account->get() || !$account->isHoldBy($api->requesterId, $error)) {
            $api->output(404, $api->getMessage('accountNotFound'));
            //indicate the requester is not an account holder or account is not found
            return;
        }
        $holders = $account->getHolders($error);
        if ($holders === false) {
            $api->output(500, $api->getMessage('somethingWentWrong') . $error);
            //indicate an error occurs
            return;
        }
        $api->output(200, $holders);
        //return account holders list
        return;
        break;
    case 'POST':
        //account holder creation
        if (!$api->checkAuth()) {
            //User not authentified/authorized
            return;
        }
        if (!$api->checkParameterExists('aid', $aid)) {
            $api->output(400, $api->getMessage('accountIdMustBeProvided'));
            //indicate the account id was not provided
            return;
        }
        $account = new Account($aid);
        if (!$account->get() || !$account->isHoldBy($api->requesterId, $error)) {
            $api->output(404, $api->getMessage('accountNotFound'));
            //indicate the requester is not an account holder or account is not found
            return;
        }
        if ($account->user !== $api->requesterId) {
            $api->output(403, $api->getMessage('accountCanBeUpdatedByOwnerOnly'));
            //indicate the requester is not the account owner and is not allowed to create holder
            return;
        }
        if (!$api->checkParameterExists('userId', $userId)) {
            $api->output(400, $api->getMessage('userIdMustBeProvided'));
            //indicate the user id was not provided
            return;
        }
        $api->checkParameterExists('isReadOnly', $isReadOnly);
        $result = $account->setHolder($userId, $isReadOnly, $error);
        if ($result === false) {
            $api->output(500, $api->getMessage('somethingWentWrong') . $error);
            //indicate an error occurs
            return;
        }
        $api->output(201, $result);
        break;
    case 'PUT':
        //update account holder permission
        if (!$api->checkAuth()) {
            //User not authentified/authorized
            return;
        }
        if (!$api->checkParameterExists('aid', $aid)) {
            $api->output(400, $api->getMessage('accountIdMustBeProvided'));
            //indicate the account id was not provided
            return;
        }
        $account = new Account($aid);
        if (!$account->get() || !$account->isHoldBy($api->requesterId, $error)) {
            $api->output(404, $api->getMessage('accountNotFound'));
            //indicate the requester is not an account holder or account is not found
            return;
        }
        if ($account->user !== $api->requesterId) {
            $api->output(403, $api->getMessage('accountCanBeUpdatedByOwnerOnly'));
            //indicate the requester is not the account owner and is not allowed to update holder
            return;
        }
        if (!$api->checkParameterExists('userId', $userId)) {
            $api->output(400, $api->getMessage('userIdMustBeProvidedInPath'));
            //indicate the user id was not provided
            return;
        }
        $userId = intval($userId);
        if (!$account->isHoldBy($userId, $error)) {
            $api->output(404, $api->getMessage('userNotHolder'));
            //indicate the user is not account holder
            return;
        }
        $api->checkParameterExists('isReadOnly', $isReadOnly);
        $result = $account->setHolder($userId, $isReadOnly, $error);
        if ($result === false) {
            $api->output(500, $api->getMessage('somethingWentWrong') . $error);
            //indicate an error occurs
            return;
        }
        $api->output(200, $result);
        break;
    case 'DELETE':
        //remove user from account holders
        if (!$api->checkAuth()) {
            //User not authentified/authorized
            return;
        }
        if (!$api->checkParameterExists('aid', $aid)) {
            $api->output(400, $api->getMessage('accountIdMustBeProvided'));
            //indicate the account id was not provided
            return;
        }
        $account = new Account($aid);
        if (!$account->get() || !$account->isHoldBy($api->requesterId, $error)) {
            $api->output(404, $api->getMessage('accountNotFound'));
            //indicate the requester is not an account holder or account is not found
            return;
        }
        if ($account->user !== $api->requesterId) {
            $api->output(403, $api->getMessage('accountCanBeUpdatedByOwnerOnly'));
            //indicate the requester is not the account owner and is not allowed to remove holder
            return;
        }
        if (!$api->checkParameterExists('userId', $userId)) {
            $api->output(400, $api->getMessage('userIdMustBeProvidedInPath'));
            //indicate the user id was not provided
            return;
        }
        $userId = intval($userId);
        if (!$account->isHoldBy($userId, $error)) {
            $api->output(404, $api->getMessage('userNotHolder'));
            //indicate the user is not an account holder
            return;
        }
        if ($account->user === $userId) {
            $api->output(400, $api->getMessage('ownerIsHolder'));
            //indicate owner can not be removed from holders
            return;
        }
        if ($account->removeHolder($userId, $error)) {
            $api->output(204);
            return;
        }
        $api->output(500, $api->getMessage('somethingWentWrong') . $error);
        break;
}
