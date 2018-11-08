<?php

/**
 * transaction/pattern API.
 *
 * Provides patterns suggestions from user's transactions
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
        //suggest patterns from user's transactions
        $user = new User($api->requesterId);
        $patterns = $user->suggestPatterns();
        foreach ($patterns as $pattern) {
            $pattern->structureData();
            unset($pattern->id);
        }
        $api->output(200, $patterns);
        //return suggested patterns
        return;
        break;
}
