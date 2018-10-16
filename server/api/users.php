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
$api = new Api('json', ['GET', 'PUT']);
switch ($api->method) {
  case 'GET':
      //returns a specific user or all
      if (!$api->checkAuth()) {
          //User not authentified/authorized
          return;
      }
      if ($api->checkParameterExists('id', $id) && $id !== '') {
          //request a specific user
          $user = new User($id);
          if (!$user->get()) {
              $api->output(404, 'User not found');
              //indicate the user was not found
              return;
          }
          $api->output(200, $user->getProfile());
          //return requested user
          return;
      }
      //request all users
      $rawUsers = User::getAll();
      $users =  array();
      foreach ($rawUsers as $user) {
          array_push($users, $user->getProfile());
      }
      $api->output(200, $users);
      break;
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
