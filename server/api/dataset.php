<?php

/**
 * dataset API.
 *
 * Provides dataset informations
 *
 * @version 1.0.0
 *
 * @api
 */
require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Api.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Account.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/User.php';
$api = new Api('json', ['POST']);
switch ($api->method) {
    case 'POST':
        if (!$api->checkAuth()) {
            //user not authentified/authorized
            return;
        }
        //get file
        $file = $_FILES['file'];
        // check extension
        $allowedExtensions = array('.ofx');
        if ($api->checkParameterExists('aid', $accountId)) {
            $accountId = intval($accountId);
            //allow json files for dataset account
            array_push($allowedExtensions, '.json');
            //check provided account exists
            $account = new Account($accountId);
            if (!$account->get()) {
                $api->output(404, 'Account not found');
                //indicate the account was not found
                return;
            }
            //check provided account is owned by the requester
            if ($account->user !== $api->requesterId) {
                $api->output(403, 'Account can be updated by owner only');
                //indicate the requester is not the account owner and is not allowed to update it
                return;
            }
        }
        $extension = strtolower(strrchr($file['name'], '.'));
        if (!in_array($extension, $allowedExtensions)) {
            $api->output(400, 'File extension must be in ' . implode(', ', $allowedExtensions));
            //invalid posted file, return an error
            return;
        }
        //check if there was an error on upload
        if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            $api->output(400, 'Error during file upload');
            //upload failed, return an error
            return;
        }
        //call parser to produce transactions array
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Dataset.php';
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Transaction.php';
        $transactions = [];
        $result = [];
        $result['accountProcessed'] = 0;
        $result['accountInserted'] = 0;
        $result['inserted'] = 0;
        $result['processed'] = 0;
        switch ($extension) {
            case '.ofx':
                $dataset = new Dataset($file['tmp_name']);
                if (!$accounts = $dataset->parseAccountsFromOfx($api->requesterId)) {
                    $api->output(500, 'Error during OFX process');
                    //something gone wrong :(
                    return;
                }
                //iterate on each account
                foreach ($accounts as $currentAccount) {
                    //check if API is called without account id or account does not exists or if the existing account is the one provided and owned by requester
                    if (!$accountId || !$currentAccount->id || ($accountId === $currentAccount->id && $currentAccount->user === $api->requesterId)) {
                        $result['accountProcessed']++;
                        if (!$currentAccount->id) {
                            //account creation by dataset (not already known)
                            if ($currentAccount->insert()) {
                                $result['accountInserted']++;
                            }
                        } else {
                            //update timestamp
                            $currentAccount->update();
                        }
                        //process account transactions
                        $transactions = $dataset->parseTransactionsFromOfx($currentAccount);
                        foreach ($transactions as $transaction) {
                            $result['processed']++;
                            if ($transaction->insert()) {
                                $result['inserted']++;
                            }
                        }
                    }
                }
                break;
            case '.json':
                if (!$api->checkParameterExists('map', $mapCode)) {
                    $api->output(400, 'Map code must be provided in query string for JSON dataset');
                    //indicate the map code is not provided
                    return;
                }
                $dataset = new Dataset($file['tmp_name']);
                //process account transactions
                if (!$transactions = $dataset->parseTransactionsFromJson($accountId, $mapCode)) {
                    $api->output(500, 'Error during JSON process');
                    //something gone wrong :(
                    return;
                }
                foreach ($transactions as $transaction) {
                    $result['processed']++;
                    if ($transaction->insert()) {
                        $result['inserted']++;
                    }
                }
                break;
            default:
                $api->output(501, 'File extension "' . $extension . '" is not implemented');
                //upload failed, return an error
                return;
        }

        //produce API response
        $response = new stdClass();
        $response->code = 201;
        if (!$accountId) {
            $response->message = $result['accountInserted'] . '/' . $result['accountProcessed'] . ' account created, ' . $result['inserted'] . '/' . $result['processed'] . ' transactions created';
        } else {
            $response->message = $result['inserted'] . '/' . $result['processed'] . ' transactions created';
        }
        $api->output(201, $response);
        return;
        break;
}
