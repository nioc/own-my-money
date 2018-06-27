<?php

/**
 * User API.
 *
 * Provides user informations
 *
 * @version 1.0.0
 *
 * @api
 */
require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Api.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/User.php';
$api = new Api('json', ['PUT']);
switch ($api->method) {
    case 'PUT':
        //update user
        if (!$api->checkAuth()) {
            //User not authentified/authorized
            return;
        }
        $user = new User();
        if (!$api->checkParameterExists('id', $user->id) || $user->id == '') {
            $api->output(400, 'PUT method must be called on a specific resource');
            //indicate the request is not valid, id must be provided in query path
            return;
        }
        $user->id = intval($user->id);
        if ($user->id !== $api->requesterId) {
            $api->output(403, 'User can be updated by himself only');
            //indicate the requester is not the user and is not allowed to update it
            return;
        }
        if (!$api->checkParameterExists('login', $user->login) || $user->login == '') {
            $api->output(400, 'Login must be provided');
            //indicate the request is not valid, login must be provided
            return;
        }
        if (!$api->checkParameterExists('password', $user->password) || $user->password == '') {
            $api->output(400, 'Password must be provided');
            //indicate the request is not valid, password must be provided
            return;
        }
        if (!$user->update($error)) {
            $api->output(500, 'Error during profile update'.$error);
            //something gone wrong :(
            return;
        }
        $api->output(200, $user->getProfile());
        break;
}
