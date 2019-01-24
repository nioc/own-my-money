<?php

/**
 * updates API.
 *
 * Provides version informations and update system
 *
 * @version 1.0.0
 *
 * @api
 */
require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Api.php';
$api = new Api('json', ['GET', 'POST']);
switch ($api->method) {
    case 'GET':
        //requester need to be authentified and have an admin scope
        if (!$api->checkAuth()) {
            //User not authentified/authorized
            return;
        }
        if (!$api->checkScope('admin')) {
            $api->output(403, $api->getMessage('adminScopeRequired'));
            //indicate the requester is not allowed to update it
            return;
        }
        //query version in repository
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Configuration.php';
        $configuration = new Configuration();
        $version = new stdClass();
        $version->installed = $configuration->get('version');
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Updater.php';
        $updater = new Updater();
        if (!$updater->getLastVersion()) {
            $api->output(500, $api->getMessage('requestError'));
            return;
        }
        $version->latest = $updater->version;
        $api->output(200, $version);
        //return version informations
        return;
        break;

    case 'POST':
        //requester need to be authentified and have an admin scope
        if (!$api->checkAuth()) {
            //User not authentified/authorized
            return;
        }
        if (!$api->checkScope('admin')) {
            $api->output(403, $api->getMessage('adminScopeRequired'));
            //indicate the requester is not allowed to update application
            return;
        }
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Updater.php';
        $updater = new Updater();
        if (!$updater->getLastVersion()) {
            $api->output(500, $updater->logs);
            return;
        }
        if (!$updater->downloadArchive()) {
            $api->output(500, $updater->logs);
            return;
        }
        if (!$updater->extractArchive()) {
            $api->output(500, $updater->logs);
            return;
        }
        $api->output(200, $updater->logs);
        return;
        break;
}
