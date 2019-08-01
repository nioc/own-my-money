<?php

/**
 * Transactions API.
 *
 * @version 1.0.0
 *
 * @api
 */
require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Api.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Account.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Transaction.php';
$api = new Api('json', ['GET', 'PUT']);
switch ($api->method) {
    case 'GET':
        //returns a specific transaction or all transactions
        if (!$api->checkAuth()) {
            //User not authentified/authorized
            return;
        }
        $api->checkParameterExists('periodStart', $periodStart);
        $api->checkParameterExists('periodEnd', $periodEnd);
        if (!$api->checkParameterExists('aid', $aid)) {
            require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/User.php';
            $user = new User($api->requesterId);
            if ($api->checkParameterExists('pattern', $pattern)) {
                //return user transactions matching pattern
                $pattern = str_replace('*', '%', $pattern);
                if (false === $transactionsList = $user->getTransactionsByPattern($pattern, $errorMessage)) {
                    $api->output(500, $api->getMessage('transactionQueryError') . $errorMessage);
                    //something gone wrong :(
                    return;
                }
            } else {
                //return all user transactions (in date interval)
                $transactionsList = $user->getTransactions($periodStart, $periodEnd);
            }
            $transactions = array();
            foreach ($transactionsList as $transaction) {
                array_push($transactions, $transaction->structureData());
            }
            $api->output(200, $transactions);
            //return transactions list
            return;
        }
        //check requestor is the account owner
        $account = new Account($aid);
        $account->get();
        if ($account->user !== $api->requesterId) {
            $api->output(403, $api->getMessage('transactionsCanBeQueriedByAccountOwnerOnly'));
            //indicate the requester is not the account owner and is not allowed to query it
            return;
        }
        if ($api->checkParameterExists('id', $id) && $id !== '') {
            //request for a specific transaction
            $transaction = new Transaction($id);
            if (!$transaction->get()) {
                $api->output(404, $api->getMessage('transactionNotFound'));
                //indicate the account was not found
                return;
            }
            $api->output(200, $transaction->structureData());
            //return requested transaction
            return;
        }
        //Request all transactions of the account (in date interval)
        $transactionsList = $account->getTransactions($periodStart, $periodEnd);
        $transactions = array();
        //get last fetch date in request header
        $lastFetch = null;
        if ($api->checkHeaderExists('Omm-Last-Fetch', $lastFetchTimestamp) && $lastFetchTimestamp !== '') {
            foreach ($transactionsList as $transaction) {
                if (isset($transaction->insertedTimestamp) && ($transaction->insertedTimestamp >= $lastFetchTimestamp)) {
                    $transaction->isNew = true;
                }
            }
        }
        //clean data
        foreach ($transactionsList as $transaction) {
            array_push($transactions, $transaction->structureData());
        }
        $api->output(200, $transactions);
        //return transactions list
        return;
        break;
    case 'PUT':
        //update operation
        if (!$api->checkAuth()) {
            //User not authentified/authorized
            return;
        }
        $api->checkParameterExists('aid', $aid);
        if (!$api->checkParameterExists('id', $id) || $id === '') {
            $api->output(400, $api->getMessage('transactionIdMustBeProvided'));
            //Transaction was not provided, return an error
            return;
        }
        $transaction = new Transaction($id);
        if (!$transaction->get()) {
            $api->output(404, $api->getMessage('transactionNotFound'));
            //indicate the transaction was not found
            return;
        }
        //check requestor is the account owner
        if ($aid && $aid !== $transaction->aid) {
            $api->output(400, $api->getMessage('transactionIsNotValid') . $api->getMessage('inconsistentAccountId'));
            //provided transaction is not valid
            return;
        }
        if (!$aid) {
            $aid = $transaction->aid;
        }
        $account = new Account($aid);
        $account->get();
        if ($account->user !== $api->requesterId) {
            $api->output(403, $api->getMessage('transactionsCanBeUpdatedByAccountOwnerOnly'));
            //indicate the requester is not the account owner and is not allowed to update it
            return;
        }
        $updatedTransaction = $api->query['body'];
        if (!$transaction->validateModel($updatedTransaction, $errorMessage)) {
            $api->output(400, $api->getMessage('transactionIsNotValid') . $errorMessage);
            //provided transaction is not valid
            return;
        }
        if (!$transaction->update($errorMessage)) {
            $api->output(500, $api->getMessage('transactionUpdateError') . $errorMessage);
            //something gone wrong :(
            return;
        }
        $transaction->get();
        $api->output(200, $transaction->structureData());
        //return transaction
        return;
        break;
}
