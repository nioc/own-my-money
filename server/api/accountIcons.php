<?php

/**
 * account icon API.
 *
 * Provides account icons informations
 *
 * @version 1.0.0
 *
 * @api
 */
require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Api.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Account.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/User.php';
$api = new Api('json', ['GET', 'POST']);
switch ($api->method) {
    case 'GET':
        if (!$api->checkParameterExists('aid', $aid, Api::PARAM_INTEGER)) {
            header('HTTP/1.0 404 Not Found');
            //indicate the request is not valid
            return;
        }
        $account = new Account($aid);
        if (!$account->get()) {
            header('HTTP/1.0 404 Not Found');
            //indicate the account was not found
            return;
        }
        //returns the account icon
        $icon = $account->getIcon();
        if ($icon === false) {
            header('HTTP/1.0 500 Internal Server Error');
        }
        //manage cache browser
        header("Cache-Control: private, max-age=432000, pre-check=432000");
        header("Pragma: private");
        header("Expires: " . date('D, d M Y H:i:s', strtotime("5 day")).' GMT');
        header('Content-type: '.image_type_to_mime_type($icon['mime_type']));
        echo $icon['icon'];
        break;
    case 'POST':
        if (!$api->checkAuth()) {
            //User not authentified/authorized
            return;
        }
        if (!$api->checkParameterExists('aid', $aid, Api::PARAM_INTEGER)) {
            $api->output(400, $api->getMessage('accountIdMustBeProvided'));
            //indicate the request is not valid
            return;
        }
        $account = new Account($aid);
        if (!$account->get()) {
            $api->output(404, $api->getMessage('accountNotFound'));
            //indicate the account was not found
            return;
        }
        if ($account->user !== $api->requesterId) {
            $api->output(403, $api->getMessage('transactionsCanBeQueriedByAccountOwnerOnly'));
            //indicate the requester is not the account owner and is not allowed to query it
            return;
        }
        //account icon creation
        if (!$api->checkParameterExists('aid', $aid, Api::PARAM_INTEGER)) {
            $api->output(400, $api->getMessage('accountIdMustBeProvided'));
            //indicate the request is not valid
            return;
        }
        $file = $_FILES['file'];
        // check extension
        $allowedExtensions = array('.png', '.jpeg', '.jpg', '.gif');
        $extension = strtolower(strrchr($file['name'], '.'));
        if (!in_array($extension, $allowedExtensions)) {
            $api->output(400, $api->getMessage('fileExtensionMustBeIn') . implode(', ', $allowedExtensions));
            //invalid posted file, return an error
            return;
        }
        //check if there was an error on upload
        if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            $api->output(400, $api->getMessage('fileUploadError'));
            //upload failed, return an error
            return;
        }
        if ($account->setIcon($file['tmp_name'])) {
            $api->output(201, $account->structureData());
            return;
        }
        $api->output(500, $api->getMessage('somethingWentWrong'));
        break;
}
