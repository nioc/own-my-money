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
$api = new Api('json', ['POST']);
switch ($api->method) {
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
        $api->output(201, $api->generateToken($user->getProfile()));
        break;
}
