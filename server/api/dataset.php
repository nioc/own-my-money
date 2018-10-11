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
        //call parser
        switch ($extension) {
            case '.ofx':
                require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/vendor/ofxparser/lib/OfxParser/Parser.php';
                require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/vendor/ofxparser/lib/OfxParser/Ofx.php';
                require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/vendor/ofxparser/lib/OfxParser/Entities/AbstractEntity.php';
                require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/vendor/ofxparser/lib/OfxParser/Entities/SignOn.php';
                require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/vendor/ofxparser/lib/OfxParser/Entities/Status.php';
                require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/vendor/ofxparser/lib/OfxParser/Entities/Institute.php';
                require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/vendor/ofxparser/lib/OfxParser/Entities/BankAccount.php';
                require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/vendor/ofxparser/lib/OfxParser/Entities/Statement.php';
                require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/vendor/ofxparser/lib/OfxParser/Entities/Transaction.php';
                $ofxParser = new \OfxParser\Parser();
                $ofx = $ofxParser->loadFromFile($file['tmp_name']);
                $bankAccount = reset($ofx->bankAccounts);
                break;
            default:
                $api->output(501, 'File extension "' . $extension . '" is not implemented');
                //upload failed, return an error
                return;
        }

        if (!$api->checkParameterExists('aid', $accountId)) {
            //account creation by dataset
            $user = new User($api->requesterId);
            if (!$result = $user->insertAccountFromDataset($bankAccount)) {
                $api->output(500, 'Error during process');
                //something gone wrong :(
                return;
            }
            $response = new stdClass();
            $response->code = 201;
            $response->message = $result['accountInserted'] . '/' . $result['accountprocessed'] . ' account created, ' . $result['inserted'] . '/' . $result['processed'] . ' transactions created';
            $api->output(201, $response);
            return;
        }

        //insert transactions from OFX into account
        $account = new Account($accountId);
        if (!$account->get()) {
            $api->output(404, 'Account not found');
            //indicate the account was not found
            return;
        }
        if ($account->user !== $api->requesterId) {
            $api->output(403, 'Account can be updated by owner only');
            //indicate the requester is not the account owner and is not allowed to update it
            return;
        }
        if (!$result = $account->insertTransactionsFromDataset($bankAccount->statement->transactions)) {
            $api->output(500, 'Error during process');
            //something gone wrong :(
            return;
        }
        $response = new stdClass();
        $response->code = 201;
        $response->message = $result['inserted'] . '/' . $result['processed'] . ' transactions created';
        $api->output(201, $response);
        return;
        break;
}
