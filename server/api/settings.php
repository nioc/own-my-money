<?php

/**
 * setting API.
 *
 * Provides setting informations
 *
 * @version 1.0.0
 *
 * @api
 */
require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Api.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Configuration.php';
$api = new Api('json', ['GET', 'PUT']);
switch ($api->method) {
    case 'GET':
        if (!$api->checkAuth()) {
            //User not authentified/authorized
            return;
        }
        if (!$api->checkScope('admin')) {
            $api->output(403, $api->getMessage('adminScopeRequired'));
            //indicate the requester is not allowed to update it
            return;
        }
        if (!$api->checkParameterExists('key', $key)) {
            $api->output(400, $api->getMessage('keyMustBeProvidedInPath'));
            //indicate the key is missing
            return;
        }
        $configuration = new Configuration(true);
        $value = $configuration->get($key);
        if ($value === false) {
            $api->output(404, $api->getMessage('getConfigurationFailed', [$key]));
            //indicate the key was not found
            return;
        }
        $setting = new stdClass;
        $setting->key = $key;
        $setting->value = $value;
        $api->output(200, $setting);
        //requested setting
        return;
        break;

    case 'PUT':
        if (!$api->checkAuth()) {
            //User not authentified/authorized
            return;
        }
        if (!$api->checkScope('admin')) {
            $api->output(403, $api->getMessage('adminScopeRequired'));
            //indicate the requester is not allowed to update it
            return;
        }
        if (!$api->checkParameterExists('key', $key)) {
            $api->output(400, $api->getMessage('keyMustBeProvidedInPath'));
            //indicate the key is missing
            return;
        }
        if (!$api->checkParameterExists('value', $value)) {
            $api->output(400, $api->getMessage('valueMustBeProvided'));
            //indicate the value is missing
            return;
        }
        $configuration = new Configuration(true);
        if ($configuration->get($key) === false) {
            $api->output(404, $api->getMessage('getConfigurationFailed', [$key]));
            //indicate the key was not found
            return;
        }
        if ($configuration->set($key, $value) === false) {
            $api->output(500, $api->getMessage('setConfigurationFailed', [$key]));
            //indicate the key was not updated with provided value
            return;
        }
        //refresh configuration for reading the value once again
        $configuration = new Configuration(true);
        $setting = new stdClass;
        $setting->key = $key;
        $setting->value = $configuration->get($key);
        $api->output(200, $setting);
        //provided setting is updated
        return;
        break;

}
