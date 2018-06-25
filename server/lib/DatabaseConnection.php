<?php

/**
 * Database connection wrapper.
 *
 * @version 1.0.0
 *
 * @internal
 */
class DatabaseConnection extends PDO
{
    /**
     * Initializes connection with database.
     */
    public function __construct()
    {
        //get configuration informations
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Configuration.php';
        $configuration = new Configuration();
        //create PDO constructor
        if ($configuration->get('dbEngine') === 'sqlite') {
            parent::__construct('sqlite:'.$_SERVER['DOCUMENT_ROOT'].$configuration->get('dbPath'));
            //add support for foreign keys at runtime
            $this->exec('PRAGMA foreign_keys = ON;');
            //return to the main thread
            return;
        }
        parent::__construct('mysql:host='.$configuration->get('dbHost').';port='.'3306'.';dbname='.$configuration->get('dbName'), $configuration->get('dbUser'), $configuration->get('dbPwd'), array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
    }
}
