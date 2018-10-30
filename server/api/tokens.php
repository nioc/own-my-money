<?php

/**
 * Authenticate user and create a token.
 *
 * Provides a token required for others API call
 *
 * @version 1.0.0
 *
 * @api
 */
require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Api.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/User.php';
$api = new Api('json', ['GET', 'POST']);
switch ($api->method) {
    case 'GET':
        if (!$api->checkAuth()) {
            //User not authentified/authorized
            return;
        }
        if (!$api->checkParameterExists('user', $id)) {
            $api->output(400, 'User id must be provided in path');
            //User id was not provided
            return;
        }
        //request a specific user
        if ($api->requesterId !== intval($id) && !$api->checkScope('admin')) {
            $api->output(403, 'Admin scope required');
            //indicate the requester is not the user and is not allowed to request
            return;
        }
        $user = new User($id);
        if (!$user->get()) {
            $api->output(404, 'User not found');
            //indicate the user was not found
            return;
        }
        $connections = $user->getLastConnections();
        if (!$connections) {
            $api->output(500, 'Error during request');
            //indicate something gone wrong
            return;
        }
        foreach ($connections as $connection) {
            unset($connection->user);
            unset($connection->expire);
            $connection->creation = date('c', $connection->creation);
        }
        $api->output(200, $connections);
        break;
    case 'POST':
        if (!$api->checkParameterExists('login', $login) || !$api->checkParameterExists('password', $password)) {
            $api->output(400, 'Both login and password must be provided');
            //login or password was not provided
            return;
        }
        $user = new User();
        if (!$user->checkCredentials($login, $password)) {
            error_log('Login attempt failed: '.$login);
            $user->increaseLoginAttemptFailed();
            $api->output(401, 'Invalid credentials');
            header('WWW-Authenticate: Bearer realm="money"');
            //invalid credentials
            return;
        }
        $user->clearLoginAttemptFailed();
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Mailer.php';
        $mailer = new Mailer($user->mail, '[Money] New connection in your account', "Hi,\r\n\r\nSomeone was recently authorized to access your account.\r\nYou can check this access from your profile.\r\n\r\nRegards.", null, null);
        $mailer->send();
        $api->output(201, $api->generateToken($user->getProfile()));
        break;
}
