<?php

/**
 * Account definition.
 *
 * @version 1.0.0
 *
 * @internal
 */
class Account
{
    /**
     * @var int Internal account identifier
     */
    public $id;
    /**
     * @var int Identifier of the user who owns the account
     */
    public $user;
    /**
     * @var string Bank identifier
     */
    public $bankId;
    /**
     * @var string Branch identifier
     */
    public $branchId;
    /**
     * @var string Account identifier
     */
    public $accountId;
    /**
     * @var string Account label
     */
    public $label;
    /**
     * @var string Transactions query default duration
     */
    public $duration;
    /**
     * @var float Current account balance
     */
    public $balance;
    /**
     * @var string Timestamp representing the last update
     */
    public $lastUpdate;
    /**
     * @var array Transactions
     */
    public $transactions;

    /**
     * Initializes an Account  object with his identifier.
     *
     * @param int $id Internal account identifier
     */
    public function __construct($id = null)
    {
        if ($id !== null) {
            $this->id = intval($id);
        }
    }

    /**
     * Inserts an account in database.
     *
     * @return bool True on success or false on failure
     */
    public function insert()
    {
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/DatabaseConnection.php';
        $connection = new DatabaseConnection();
        $query = $connection->prepare('INSERT INTO `account` (`user`, `bankId`, `branchId`, `accountId`, `label`, `balance`, `lastUpdate`) VALUES ( :user, :bankId, :branchId, :accountId, :label, :balance, :lastUpdate);');
        $query->bindValue(':user', $this->user, PDO::PARAM_INT);
        $query->bindValue(':bankId', $this->bankId, PDO::PARAM_STR);
        $query->bindValue(':branchId', $this->branchId, PDO::PARAM_STR);
        $query->bindValue(':accountId', $this->accountId, PDO::PARAM_STR);
        $query->bindValue(':label', $this->label, PDO::PARAM_STR);
        $query->bindValue(':balance', $this->balance, PDO::PARAM_INT);
        $query->bindValue(':lastUpdate', $this->lastUpdate, PDO::PARAM_INT);
        if ($query->execute()) {
            $this->id = $connection->lastInsertId();
            //returns insertion was successfully processed
            return true;
        }
        //returns insertion has encountered an error
        return false;
    }

    /**
     * Update an account in database.
     *
     * @return bool True on success or false on failure
     */
    public function update()
    {
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/DatabaseConnection.php';
        $connection = new DatabaseConnection();
        $query = $connection->prepare('UPDATE `account` SET `bankId`=:bankId, `branchId`=:branchId, `accountId`=:accountId, `label`=:label, `duration`=:duration, `balance`=:balance, `lastUpdate`=UNIX_TIMESTAMP() WHERE `id`=:id;');
        $query->bindValue(':id', $this->id, PDO::PARAM_INT);
        $query->bindValue(':bankId', $this->bankId, PDO::PARAM_STR);
        $query->bindValue(':branchId', $this->branchId, PDO::PARAM_STR);
        $query->bindValue(':accountId', $this->accountId, PDO::PARAM_STR);
        $query->bindValue(':label', $this->label, PDO::PARAM_STR);
        $query->bindValue(':duration', $this->duration, PDO::PARAM_STR);
        $query->bindValue(':balance', $this->balance, PDO::PARAM_INT);
        if ($query->execute()) {
            //returns update was successfully processed
            return true;
        }
        //returns update has encountered an error
        return false;
    }

    /**
     * Populates an account.
     *
     * @return bool True on success or false on failure
     */
    public function get()
    {
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/DatabaseConnection.php';
        $connection = new DatabaseConnection();
        $query = $connection->prepare('SELECT * FROM `account` WHERE `id`=:id LIMIT 1;');
        $query->bindValue(':id', $this->id, PDO::PARAM_INT);
        $query->setFetchMode(PDO::FETCH_INTO, $this);
        if ($query->execute() && $query->fetch()) {
            $this->id = intval($this->id);
            $this->user = intval($this->user);
            //returns the account object was successfully fetched
            return true;
        }
        //returns the account is not known or database was not reachable
        return false;
    }

    /**
     * Search and populates an account by public keys
     *
     * @param string $userId Owner
     * @param string $bankId
     * @param string $branchId
     * @param string $accountId
     * @return boolean Account identifier or false on failure
     */
    public function getByPublicId($userId, $bankId, $branchId, $accountId)
    {
        if (!isset($userId, $bankId, $branchId, $accountId)) {
            //returns public keys are not set
            return false;
        }
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/DatabaseConnection.php';
        $connection = new DatabaseConnection();
        $query = $connection->prepare('SELECT * FROM `account` WHERE `user`=:user AND `bankId`=:bankId AND `branchId`=:branchId AND `accountId`=:accountId LIMIT 1;');
        $query->bindValue(':user', $userId, PDO::PARAM_INT);
        $query->bindValue(':bankId', $bankId, PDO::PARAM_STR);
        $query->bindValue(':branchId', $branchId, PDO::PARAM_STR);
        $query->bindValue(':accountId', $accountId, PDO::PARAM_STR);
        $query->setFetchMode(PDO::FETCH_INTO, $this);
        if ($query->execute() && $query->fetch()) {
            $this->id = intval($this->id);
            $this->user = intval($this->user);
            //returns the account object was successfully fetched
            return true;
        }
        //returns the account is not known or database was not reachable
        return false;
    }

    /**
     * Return all account transactions.
     *
     * @return array All account transactions
     */
    public function getTransactions()
    {
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/DatabaseConnection.php';
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Transaction.php';
        $connection = new DatabaseConnection();
        $query = $connection->prepare('SELECT * FROM `transaction` WHERE `aid` = :aid ORDER BY `datePosted` DESC;');
        $query->bindValue(':aid', $this->id, PDO::PARAM_INT);
        if ($query->execute()) {
            //return array of transactions
            return $query->fetchAll(PDO::FETCH_CLASS, 'Transaction');
        }
        //indicate there is a problem during querying
        return false;
    }

    /**
     * Delete an account from database.
     *
     * @return bool True on success or false on failure
     */
    public function delete()
    {
        if (is_int($this->id)) {
            require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/DatabaseConnection.php';
            $connection = new DatabaseConnection();
            $query = $connection->prepare('DELETE FROM `account` WHERE `id` = :id;');
            $query->bindValue(':id', $this->id, PDO::PARAM_INT);
            //returns deletion result
            return $query->execute();
        }
        //returns an error if no identifier was provided
        return false;
    }

    /**
     * Return structured account.
     *
     * @return object A public version of account
     */
    public function structureData()
    {
        //create account structure
        $account = $this;
        $account->id = (int) $account->id;
        if (isset($account->balance)) {
            $account->balance = (float) $account->balance;
        }
        if (isset($account->lastUpdate)) {
            $account->lastUpdate= date('c', $account->lastUpdate);
        }
        unset($account->transactions);
        //return structured account
        return $account;
    }
}
