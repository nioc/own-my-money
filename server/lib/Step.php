<?php
/**
 * Step definition for processing application installation and upgrade.
 *
 * @version 1.0.0
 *
 * @internal
 */
class Step
{
    /**
     * @var string Step code
     */
    public $code;

    /**
     * @var string Step label
     */
    public $label;

    /**
     * @var string Step icon
     */
    public $icon;

    /**
     * @var string Step helper text (explain what step does)
     */
    public $help;

    /**
     * @var boolean Step status (only one should be active)
     */
    public $isActive;

    /**
     * @var Array Form fields to process the step
     */
    public $fields;

    /**
     * @var string Requested language
     */
    private $language;

    /**
     * Initializes a Step object with the provided informations.
     *
     * @param string $language   Language locale to output step informations
     * @param string $code       Code used for retrieving step
     * @param string $label      Label displayed in GUI
     * @param string $icon       Icon name displayed in GUI
     * @param string $help       Helper text to explain what step does
     * @param boolean $isActive  Indicate if step is the current one
     */
    public function __construct($language = 'en', $code = null, $label = null, $icon = null, $help = null, $isActive = false)
    {
        if ($code !== null) {
            $this->code = $code;
        }
        if ($label !== null) {
            $this->label = $label;
        }
        if ($icon !== null) {
            $this->icon = $icon;
        }
        if ($help !== null) {
            $this->help = $help;
        }
        $this->isActive = $isActive;
        $this->language = $language;
        //get step fields
        $this->getFields();
    }

    /**
     * Return all steps.
     *
     * @param string $language  Language locale to output steps informations
     *
     * @return array Steps
     */
    public static function getAll($language)
    {
        //check setup status
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Configuration.php';
        $configuration = new Configuration();
        if ($configuration->get('setup') === '1') {
            //initial setup is already done, check updates
            $distConfiguration = new Configuration(false);
            $installedVersion = $configuration->get('version');
            $distVersion = $distConfiguration->get('version');
            if ($installedVersion !== $distVersion) {
                //there are update to do
                return Step::getUpdateSteps($installedVersion, $language);
            }
            //nothing to do, return no step
            return [];
        }
        return Step::getInstallationSteps($language);
    }

    /**
     * Return all installation steps.
     *
     * @param string $language  Language locale to output steps informations
     *
     * @return array Steps
     */
    private static function getInstallationSteps($language)
    {
        //check database exists
        $databaseConfigured = true;
        try {
            require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/DatabaseConnection.php';
            $connection = new DatabaseConnection();
        } catch (PDOException $e) {
            $databaseConfigured = false;
        }
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Lang.php';
        $lang = new Lang($language);
        //set installation step
        $steps = [
          new Step($language, 'set-database-access', $lang->getMessage('stepSetupDataAccess'), 'fa-database', $lang->getMessage('stepSetupDataAccessLabel'), $databaseConfigured ? false : true),
          new Step($language, 'create-database', $lang->getMessage('stepCreateDatabase'), 'fa-table', $lang->getMessage('stepCreateDatabaseLabel'), false),
          new Step($language, 'set-mailer', $lang->getMessage('stepSetupmailer'), 'fa-envelope', $lang->getMessage('stepSetupmailerLabel'), $databaseConfigured ? true : false),
          new Step($language, 'set-security', $lang->getMessage('stepSetupSecurity'), 'fa-lock', $lang->getMessage('stepSetupSecurityLabel'), false),
          new Step($language, 'create-user', $lang->getMessage('stepCreateUser'), 'fa-user', $lang->getMessage('stepCreateUserLabel'), false),
          new Step($language, 'finalize-setup', $lang->getMessage('stepFinalizeSetup'), 'fa-cogs', $lang->getMessage('stepFinalizeSetupLabel'), false),
          new Step($language, 'confirmation', $lang->getMessage('stepConfirmation'), 'fa-check', $lang->getMessage('stepConfirmationLabel'), false)
        ];
        return $steps;
    }

    /**
     * Return required update steps.
     *
     * @param string $installedVersion  Current version
     * @param string $language          Language locale to output steps informations
     *
     * @return array Steps
     */
    private static function getUpdateSteps($installedVersion, $language)
    {
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Lang.php';
        $lang = new Lang($language);
        $hasDatabaseAlter = false;
        switch ($installedVersion) {
            //check if there is some database change
            case '0.1.0':
            case '0.2.0':
            case '0.3.0':
            case '0.3.1':
            case '0.3.2':
            case '0.3.3':
            case '0.4.0':
            case '0.5.0':
            case '0.5.1':
            case '0.5.2':
            case '0.6.0':
            case '0.7.0':
            case '0.8.0':
            case '0.9.0':
            case '0.9.1':
            case '0.10.0':
            case '0.10.1':
            case '0.10.2':
                $hasDatabaseAlter = true;
                break;
            case '0.11.0':
            case '0.11.1':
            case '0.11.2':
            case '0.12.0':
            case '0.12.1':
                break;
            default:
                return $lang->getMessage('unknownInstalledVersion');
                break;
        }
        $steps = [];
        if ($hasDatabaseAlter) {
            array_push(
                $steps,
                new Step($language, 'backup-database', $lang->getMessage('stepCreateBackup'), 'fa-life-ring', $lang->getMessage('stepCreateBackupLabel'), false),
                new Step($language, 'update-database', $lang->getMessage('stepUpdateDatabase'), 'fa-table', $lang->getMessage('stepUpdateDatabaseLabel'), false)
            );
        }
        array_push(
            $steps,
            new Step($language, 'finalize-setup', $lang->getMessage('stepFinalizeUpdate'), 'fa-cogs', $lang->getMessage('stepFinalizeUpdateLabel'), false),
            new Step($language, 'confirmation', $lang->getMessage('stepConfirmation'), 'fa-check', $lang->getMessage('stepUpdateConfirmationLabel'), false)
        );
        return $steps;
    }

    /**
     * Return the requested step field value
     *
     * @param string $name Name of the requested field
     *
     * @return string The field value
     */
    private function getFieldValue($name)
    {
        foreach ($this->fields as $field) {
            if ($field->name === $name) {
                //return the value of the field
                return $field->value;
            }
        }
        //field is not in the step fields
        return false;
    }

    /**
     * Return step fields or false if the step is not kownw (even if step has no field to process, it must be declared here)
     *
     * @return array Fields
     */
    private function getFields()
    {
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Field.php';
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Configuration.php';
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Lang.php';
        $configuration = new Configuration();
        $lang = new Lang($this->language);
        switch ($this->code) {

            case 'set-database-access':
                $this->fields = [
                    new Field('host', $lang->getMessage('fieldHost'), 'text', 'required', $lang->getMessage('fieldHostLabel'), $configuration->get('dbHost')),
                    new Field('admin-login', $lang->getMessage('fieldLogin'), 'text', 'required', $lang->getMessage('fieldLoginMySqlRootLabel'), 'root'),
                    new Field('admin-password', $lang->getMessage('fieldPassword'), 'password', 'required', $lang->getMessage('fieldPasswordMySqlRootLabel')),
                    new Field('db-login', $lang->getMessage('fieldLogin'), 'text', 'required', $lang->getMessage('fieldLoginMySqlLabel'), $configuration->get('dbUser')),
                    new Field('db-password', $lang->getMessage('fieldPassword'), 'password', 'required', $lang->getMessage('fieldPasswordMySqlLabel'), $configuration->get('dbPwd')),
                    new Field('db-name', $lang->getMessage('fieldDatabase'), 'text', 'required', $lang->getMessage('fieldDatabaseLabel'), $configuration->get('dbName')),
                ];
                break;

            case 'create-database':
                $this->fields = [
                ];
                break;

            case 'set-mailer':
                $this->fields = [
                  new Field('mailer', $lang->getMessage('fieldSendMail'), 'text', 'required|between:0,1', $lang->getMessage('fieldSendMailLabel'), $configuration->get('mailer')),
                  new Field('mail-sender', $lang->getMessage('fieldFromAddress'), 'email', 'required|email', $lang->getMessage('fieldFromAddressLabel'), $configuration->get('mailSender')),
                ];
                break;

            case 'set-security':
                $this->fields = [
                    new Field('hash-key', $lang->getMessage('fieldHashKey'), 'text', 'required|min:5', $lang->getMessage('fieldHashKeyLabel'), $configuration->get('hashKey')),
                ];
                break;

            case 'create-user':
                $this->fields = [
                    new Field('login', $lang->getMessage('fieldLogin'), 'text', 'required|min:3|alpha_num', $lang->getMessage('fieldLoginLabel'), 'JohnDoe'),
                    new Field('password', $lang->getMessage('fieldPassword'), 'password', 'required|min:5|alpha_num', $lang->getMessage('fieldPasswordLabel')),
                ];
                break;

            case 'backup-database':
            case 'update-database':
            case 'finalize-setup':
            case 'confirmation':
                $this->fields = [
                ];
                break;

            default:
                //step is not declared, return false
                $this->fields = false;
                break;
        }
    }

    /**
     * Process the step with provided fields informations.
     *
     * @param object $fields Array of fields used to complete the step
     *
     * @return bool|string True if the step is successfully completed, a string whith the encountered error otherwise
     */
    public function process($fields)
    {
        $this->fields = $fields;
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Configuration.php';
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Lang.php';
        $configuration = new Configuration();
        $lang = new Lang($this->language);
        switch ($this->code) {
            case 'set-database-access':
                if (!$host = $this->getFieldValue('host')) {
                    return $lang->getMessage('getFieldFailed', ['host']);
                }
                if (!$adminLogin = $this->getFieldValue('admin-login')) {
                    return $lang->getMessage('getFieldFailed', ['admin-login']);
                }
                if (!$adminPassword = $this->getFieldValue('admin-password')) {
                    return $lang->getMessage('getFieldFailed', ['admin-password']);
                }
                if (!$dbLogin = $this->getFieldValue('db-login')) {
                    return $lang->getMessage('getFieldFailed', ['db-login']);
                }
                if (!$dbPassword = $this->getFieldValue('db-password')) {
                    return $lang->getMessage('getFieldFailed', ['db-password']);
                }
                if (!$dbName = $this->getFieldValue('db-name')) {
                    return $lang->getMessage('getFieldFailed', ['db-name']);
                }
                //store host
                if (!$configuration->set('dbHost', $host)) {
                    return $lang->getMessage('setConfigurationFailed', ['dbHost']);
                }
                //create MySQL user
                try {
                    $connection = new PDO('mysql:host='.$host.';port='.'3306', $adminLogin, $adminPassword, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
                } catch (PDOException $e) {
                    return $e->getMessage();
                }
                $query = $connection->prepare('CREATE USER :login@:client IDENTIFIED BY :password;');
                $query->bindValue(':login', $dbLogin, PDO::PARAM_STR);
                if ($host === 'localhost') {
                    $query->bindValue(':client', 'localhost', PDO::PARAM_STR);
                } else {
                    $query->bindValue(':client', '%', PDO::PARAM_STR);
                }
                $query->bindValue(':password', $dbPassword, PDO::PARAM_STR);
                if (!$query->execute()) {
                    //returns error
                    return $query->errorInfo()[2];
                }
                //store MySQL user credentials
                if (!$configuration->set('dbUser', $dbLogin)) {
                    return $lang->getMessage('setConfigurationFailed', ['dbUser']);
                }
                if (!$configuration->set('dbPwd', $dbPassword)) {
                    return $lang->getMessage('setConfigurationFailed', ['dbPwd']);
                }
                //create MySQL database
                $query = $connection->prepare('CREATE DATABASE IF NOT EXISTS '.$dbName.' DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;');
                if (!$query->execute()) {
                    //returns error
                    return $query->errorInfo()[2];
                }
                //grant created user privileges on created database
                $query = $connection->prepare('GRANT ALL PRIVILEGES ON '.$dbName.'.* TO :login@:client WITH GRANT OPTION;');
                $query->bindValue(':login', $dbLogin, PDO::PARAM_STR);
                if ($host === 'localhost') {
                    $query->bindValue(':client', 'localhost', PDO::PARAM_STR);
                } else {
                    $query->bindValue(':client', '%', PDO::PARAM_STR);
                }
                if (!$query->execute()) {
                    //returns error
                    return $query->errorInfo()[2];
                }
                //store MySQL database
                if (!$configuration->set('dbName', $dbName)) {
                    return $lang->getMessage('setConfigurationFailed', ['dbName']);
                }
                return true;
                break;

            case 'create-database':
                //connect to MySQL with application credentials
                try {
                    require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/DatabaseConnection.php';
                    $connection = new DatabaseConnection();
                } catch (PDOException $e) {
                    return $e->getMessage();
                }
                //create tables
                $sql = file_get_contents($_SERVER['DOCUMENT_ROOT'].'/server/configuration/create-database.sql');
                $connection->exec($sql);
                return true;
                break;

            case 'set-mailer':
                $mailer = $this->getFieldValue('mailer');
                if ($mailer === false) {
                    return $lang->getMessage('getFieldFailed', ['mailer']);
                }
                if (!$mailSender = $this->getFieldValue('mail-sender')) {
                    return $lang->getMessage('getFieldFailed', ['mail-sender']);
                }
                if (!$configuration->set('mailer', $mailer)) {
                    return $lang->getMessage('setConfigurationFailed', ['mailer']);
                }
                if (!$configuration->set('mailSender', $mailSender)) {
                    return $lang->getMessage('setConfigurationFailed', ['mailSender']);
                }
                return true;
                break;

            case 'set-security':
                if (!$hashKey = $this->getFieldValue('hash-key')) {
                    return $lang->getMessage('getFieldFailed', ['hash-key']);
                }
                if (!$configuration->set('hashKey', $hashKey)) {
                    return $lang->getMessage('setConfigurationFailed', ['hashKey']);
                }
                return true;
                break;

            case 'create-user':
                if (!$login = $this->getFieldValue('login')) {
                    return $lang->getMessage('getFieldFailed', ['login']);
                }
                if (!$password = $this->getFieldValue('password')) {
                    return $lang->getMessage('getFieldFailed', ['password']);
                }
                //create user
                require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/User.php';
                $user = new User();
                $user->login = $login;
                $user->password = $password;
                $user->scope = 'user admin';
                $user->status = true;
                $user->language = '';
                if (!$user->insert($error)) {
                    return $lang->getMessage('userCreationError') . $error;
                }
                return true;
                break;

            case 'backup-database':
                $filename = $_SERVER['DOCUMENT_ROOT'].'/money-db-backup-'.time().'.sql';
                $dbName = $configuration->get('dbName');
                $dbUser = $configuration->get('dbUser');
                $dbPwd = $configuration->get('dbPwd');
                exec("mysqldump $dbName --password=$dbPwd --user=$dbUser --single-transaction >$filename", $output);
                if (!file_exists($filename) || filesize($filename) === 0) {
                    return $lang->getMessage('backupError') . join(' ', $output);
                }
                return true;
                break;

            case 'update-database':
                //connect to MySQL with application credentials
                try {
                    require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/DatabaseConnection.php';
                    $connection = new DatabaseConnection();
                } catch (PDOException $e) {
                    return $e->getMessage();
                }
                //alter existing tables and create new ones according to the installed version
                $installedVersion = $configuration->get('version');
                switch ($installedVersion) {
                    case '0.1.0':
                        $sql = file_get_contents($_SERVER['DOCUMENT_ROOT'].'/server/configuration/alter-database-0.2.0.sql');
                        $connection->exec($sql);
                        // no break
                    case '0.2.0':
                    case '0.3.0':
                    case '0.3.1':
                    case '0.3.2':
                    case '0.3.3':
                    case '0.4.0':
                        $sql = file_get_contents($_SERVER['DOCUMENT_ROOT'].'/server/configuration/alter-database-0.5.0.sql');
                        $connection->exec($sql);
                        // no break
                    case '0.5.0':
                    case '0.5.1':
                    case '0.5.2':
                    case '0.6.0':
                        $sql = file_get_contents($_SERVER['DOCUMENT_ROOT'].'/server/configuration/alter-database-0.7.0.sql');
                        $connection->exec($sql);
                        // no break
                    case '0.7.0':
                    case '0.8.0':
                        $sql = file_get_contents($_SERVER['DOCUMENT_ROOT'].'/server/configuration/alter-database-0.9.0.sql');
                        $connection->exec($sql);
                        // no break
                    case '0.9.0':
                    case '0.9.1':
                        $sql = file_get_contents($_SERVER['DOCUMENT_ROOT'].'/server/configuration/alter-database-0.10.0.sql');
                        $connection->exec($sql);
                        // no break
                    case '0.10.0':
                    case '0.10.1':
                    case '0.10.2':
                        $sql = file_get_contents($_SERVER['DOCUMENT_ROOT'].'/server/configuration/alter-database-0.11.0.sql');
                        $connection->exec($sql);

                        //cumulative scripts, break will be shared
                        break;
                    default:
                        break;
                }
                //check the database has expected version
                $query = $connection->prepare('SELECT `version` FROM `version`;');
                if ($query->execute()) {
                    $distConfiguration = new Configuration(false);
                    if (!$distVersion = $distConfiguration->get('version')) {
                        return $lang->getMessage('getConfigurationFailed', ['version']);
                    }
                    $databaseVersion = $query->fetchColumn();
                    if ($distVersion === $databaseVersion) {
                        return true;
                    }
                    return $lang->getMessage('databaseUpdateErrorBadVersion', [$databaseVersion, $distVersion]);
                }
                return $lang->getMessage('databaseUpdateErrorRead');;
                break;

            case 'finalize-setup':
                //set the version installed
                $distConfiguration = new Configuration(false);
                if (!$version = $distConfiguration->get('version')) {
                    return $lang->getMessage('getConfigurationFailed', ['version']);
                }
                if (!$configuration->set('version', $version)) {
                    return $lang->getMessage('setConfigurationFailed', ['version']);
                }
                //set the installation steps are completed
                if (!$configuration->set('setup', '1')) {
                    return $lang->getMessage('setConfigurationFailed', ['setup']);
                }
                return true;
                break;

            case 'confirmation':
                return true;
                break;

            default:
                return $lang->getMessage('unknownStep');
                break;
        }
    }
}
