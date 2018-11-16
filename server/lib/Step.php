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
     * Initializes a Step object with the provided informations.
     *
     * @param string $code       Code used for retrieving step
     * @param string $label      Label displayed in GUI
     * @param string $icon       Icon name displayed in GUI
     * @param string $help       Helper text to explain what step does
     * @param boolean $isActive  Indicate if step is the current one
     */
    public function __construct($code = null, $label = null, $icon = null, $help = null, $isActive = false)
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
        //get step fields
        $this->getFields();
    }

    /**
     * Return all steps.
     *
     * @return array Steps
     */
    public static function getAll()
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
                return Step::getUpdateSteps($installedVersion);
            }
            //nothing to do, return no step
            return [];
        }
        return Step::getInstallationSteps();
    }

    private static function getInstallationSteps()
    {
        //check database exists
        $databaseConfigured = true;
        try {
            require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/DatabaseConnection.php';
            $connection = new DatabaseConnection();
        } catch (PDOException $e) {
            $databaseConfigured = false;
        }
        //set installation step
        $steps = [
          new Step('set-database-access', 'Setup database access', 'fa-database', 'Application will create MySQL user and database used to store your data', $databaseConfigured ? false : true),
          new Step('create-database', 'Create database', 'fa-table', 'Now, application will create MySQL tables', false),
          new Step('set-mailer', 'Setup mailer', 'fa-envelope', 'The application can send emails if your host has a SMTP server, in this step we configure the mail system', $databaseConfigured ? true : false),
          new Step('set-security', 'Setup security', 'fa-lock', 'Authorization process uses signed JWT, it requires you set your own secret key for generating the HMAC', false),
          new Step('create-user', 'Create user', 'fa-user', 'This step will create your (super) user account', false),
          new Step('finalize-setup', 'Finalize setup', 'fa-cogs', 'This step will complete your setup', false),
          new Step('confirmation', 'Confirmation', 'fa-check', 'That\'s all, installation process has been completed, you can now <a href="/">signin</a> into the application', false)
        ];
        return $steps;
    }

    private static function getUpdateSteps($installedVersion)
    {
        $hasDatabaseAlter = false;
        switch ($installedVersion) {
            //check if there is some database change
            case '0.1.0':
                $hasDatabaseAlter = true;
                break;
            case '0.2.0':
            case '0.3.0':
            case '0.3.1':
                break;
            default:
                return 'Unknown installed version';
                break;
        }
        $steps = [];
        if ($hasDatabaseAlter) {
            array_push(
                $steps,
                new Step('backup-database', 'Create backup', 'fa-life-ring', 'Your data matters, application will create a local backup of your database', false),
                new Step('update-database', 'Update database', 'fa-table', 'Now, application will update MySQL tables', false)
            );
        }
        array_push(
            $steps,
            new Step('finalize-setup', 'Finalize update', 'fa-cogs', 'This step will complete this update', false),
            new Step('confirmation', 'Confirmation', 'fa-check', 'That\'s all, update process has been completed, you can now go back to the <a href="/">home</a> and discover the new features', false)
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
        $configuration = new Configuration();
        switch ($this->code) {

            case 'set-database-access':
                $this->fields = [
                    new Field('host', 'Host', 'text', 'required', 'MySQL server host, can be `localhost`, an ip address, ...', $configuration->get('dbHost')),
                    new Field('admin-login', 'Login', 'text', 'required', 'Existing MySQL admin login used for create the `money` user (require the `CREATE USER` privilege)', 'root'),
                    new Field('admin-password', 'Password', 'password', 'required', 'Existing MySQL admin password (used only for this step, it will not be stored)'),
                    new Field('db-login', 'Login', 'text', 'required', 'MySQL `money` user login to create', $configuration->get('dbUser')),
                    new Field('db-password', 'Password', 'password', 'required', 'MySQL `money` user password', $configuration->get('dbPwd')),
                    new Field('db-name', 'Database', 'text', 'required', 'MySQL database name to create', $configuration->get('dbName')),
                ];
                break;

            case 'create-database':
                $this->fields = [
                ];
                break;

            case 'set-mailer':
                $this->fields = [
                  new Field('mailer', 'Send mail', 'text', 'required|between:0,1', 'Does the application send out emails (for user connection) ; type 1 for sending emails, 0 for not', $configuration->get('mailer')),
                  new Field('mail-sender', 'From address', 'email', 'required|email', 'Sender email address', $configuration->get('mailSender')),
                ];
                break;

            case 'set-security':
                $this->fields = [
                    new Field('hash-key', 'Hash key', 'text', 'required|min:5', 'Hash used to sign user tokens', $configuration->get('hashKey')),
                ];
                break;

            case 'create-user':
                $this->fields = [
                    new Field('login', 'Login', 'text', 'required|min:3|alpha_num', 'Your login in the application (alphanumeric only, more than 3 characters)', 'JohnDoe'),
                    new Field('password', 'Password', 'password', 'required|min:5|alpha_num', 'Your password in the application (alphanumeric only, more than 5 characters)'),
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
        $configuration = new Configuration();
        switch ($this->code) {
            case 'set-database-access':
                if (!$host = $this->getFieldValue('host')) {
                    return 'Failed to get `host` value';
                }
                if (!$adminLogin = $this->getFieldValue('admin-login')) {
                    return 'Failed to get `admin-login` value';
                }
                if (!$adminPassword = $this->getFieldValue('admin-password')) {
                    return 'Failed to get `admin-password` value';
                }
                if (!$dbLogin = $this->getFieldValue('db-login')) {
                    return 'Failed to get `db-login` value';
                }
                if (!$dbPassword = $this->getFieldValue('db-password')) {
                    return 'Failed to get `db-password` value';
                }
                if (!$dbName = $this->getFieldValue('db-name')) {
                    return 'Failed to get `db-password` value';
                }
                //store host
                if (!$configuration->set('dbHost', $host)) {
                    return 'Failed to set configuration `dbHost`';
                }
                //create MySQL user
                try {
                    $connection = new PDO('mysql:host='.$host.';port='.'3306', $adminLogin, $adminPassword, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
                } catch (PDOException $e) {
                    return $e->getMessage();
                }
                $query = $connection->prepare('CREATE USER :login@:client IDENTIFIED BY :password;');
                $query->bindValue(':login', $dbLogin, PDO::PARAM_STR);
                $query->bindValue(':client', 'localhost', PDO::PARAM_STR);
                $query->bindValue(':password', $dbPassword, PDO::PARAM_STR);
                if (!$query->execute()) {
                    //returns error
                    return $query->errorInfo()[2];
                }
                //store MySQL user credentials
                if (!$configuration->set('dbUser', $dbLogin)) {
                    return 'Failed to set configuration `dbUser`';
                }
                if (!$configuration->set('dbPwd', $dbPassword)) {
                    return 'Failed to set configuration `dbPwd`';
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
                $query->bindValue(':client', 'localhost', PDO::PARAM_STR);
                if (!$query->execute()) {
                    //returns error
                    return $query->errorInfo()[2];
                }
                //store MySQL database
                if (!$configuration->set('dbName', $dbName)) {
                    return 'Failed to set configuration `dbName`';
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
                    return 'Failed to get `mailer` value';
                }
                if (!$mailSender = $this->getFieldValue('mail-sender')) {
                    return 'Failed to get `mail-sender` value';
                }
                if (!$configuration->set('mailer', $mailer)) {
                    return 'Failed to set configuration `mailer`';
                }
                if (!$configuration->set('mailSender', $mailSender)) {
                    return 'Failed to set configuration `mailSender`';
                }
                return true;
                break;

            case 'set-security':
                if (!$hashKey = $this->getFieldValue('hash-key')) {
                    return 'Failed to get `hash-key` value';
                }
                if (!$configuration->set('hashKey', $hashKey)) {
                    return 'Failed to set configuration `hashKey`';
                }
                return true;
                break;

            case 'create-user':
                if (!$login = $this->getFieldValue('login')) {
                    return 'Failed to get `login` value';
                }
                if (!$password = $this->getFieldValue('password')) {
                    return 'Failed to get `password` value';
                }
                //create user
                require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/User.php';
                $user = new User();
                $user->login = $login;
                $user->password = $password;
                $user->scope = 'user admin';
                $user->status = true;
                if (!$user->insert($error)) {
                    return 'Error during user creation'.$error;
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
                    return 'Error during backup creation'.join(' ', $output);
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
                        return 'Failed to get configuration `version`';
                    }
                    $databaseVersion = $query->fetchColumn();
                    if ($distVersion === $databaseVersion) {
                        return true;
                    }
                    return "Error during database update (current $databaseVersion ≠ target $distVersion)";
                }
                return 'Error during database update (can not read database version)';
                break;

            case 'finalize-setup':
                //set the version installed
                $distConfiguration = new Configuration(false);
                if (!$version = $distConfiguration->get('version')) {
                    return 'Failed to get configuration `version`';
                }
                if (!$configuration->set('version', $version)) {
                    return 'Failed to set configuration `version`';
                }
                //set the installation steps are completed
                if (!$configuration->set('setup', '1')) {
                    return 'Failed to set configuration `setup`';
                }
                return true;
                break;

            case 'confirmation':
                return true;
                break;

            default:
                return 'Step unknown';
                break;
        }
    }
}
