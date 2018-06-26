<?php

/**
 * Transaction definition.
 *
 * @version 1.1.0
 *
 * @internal
 */
class Transaction
{
    /**
     * @var int Transaction identifier
     */
    public $id;
    /**
     * @var int Account id for this transaction
     */
    public $aid;
    /**
     * @var string Enum : DEBIT or CREDIT
     */
    public $type;
    /**
     * @var int Posted date
     */
    public $datePosted;
    /**
     * @var int Value date for customer
     */
    public $dateUser;
    /**
     * @var int Amount
     */
    public $amount;
    /**
     * @var string Label of the transaction
     */
    public $name;
    /**
     * @var string Fitid
     */
    public $fitid;
    /**
     * @var int Category identifier
     */
    public $category;
    /**
     * @var int Subcategory identifier
     */
    public $subcategory;

    /**
     * Initializes a Transaction object with his identifier.
     *
     * @param int $id Transaction identifier
     */
    public function __construct($id = null)
    {
        if ($id !== null) {
            $this->id = intval($id);
        }
    }

    /**
     * Inserts an transaction in database.
     *
     * @return bool True on success or false on failure
     */
    public function insert()
    {
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/DatabaseConnection.php';
        $connection = new DatabaseConnection();
        $query = $connection->prepare('INSERT INTO `transaction` (`aid`, `fitid`, `type`, `datePosted`, `dateUser`, `amount`, `name`, `category`) VALUES ( :aid, :fitid, :type, :datePosted, :dateUser, :amount, :name, :category);');
        $query->bindValue(':aid', $this->aid, PDO::PARAM_INT);
        $query->bindValue(':fitid', $this->fitid, PDO::PARAM_STR);
        $query->bindValue(':type', $this->type, PDO::PARAM_STR);
        $query->bindValue(':datePosted', $this->datePosted, PDO::PARAM_INT);
        $query->bindValue(':dateUser', $this->dateUser, PDO::PARAM_INT);
        //there is no PARAM_FLOAT in PDO so use PARAM_STR instead...
        $query->bindValue(':amount', $this->amount, PDO::PARAM_STR);
        $query->bindValue(':name', $this->name, PDO::PARAM_STR);
        $query->bindValue(':category', $this->category, PDO::PARAM_STR);
        if ($query->execute()) {
            $this->id = $connection->lastInsertId();
            //returns insertion was successfully processed
            return true;
        }
        //returns insertion has encountered an error
        return false;
    }

    /**
     * Populates a Transaction.
     *
     * @return mixed True on success or false on failure
     */
    public function get()
    {
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/DatabaseConnection.php';
        $connection = new DatabaseConnection();
        $query = $connection->prepare('SELECT * FROM `transaction` WHERE `id`=:id LIMIT 1;');
        $query->bindValue(':id', $this->id, PDO::PARAM_INT);
        $query->setFetchMode(PDO::FETCH_INTO, $this);
        if ($query->execute() && $query->fetch()) {
            //returns the transaction object was successfully fetched
            return true;
        }
        //returns the transaction is not known or database was not reachable
        return false;
    }

    /**
     * Delete a transaction from database.
     *
     * @return bool True on success or false on failure
     */
    public function delete()
    {
        if (is_int($this->id)) {
            require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/DatabaseConnection.php';
            $connection = new DatabaseConnection();
            $query = $connection->prepare('DELETE FROM `transaction` WHERE `id` = :id;');
            $query->bindValue(':id', $this->id, PDO::PARAM_INT);
            //returns deletion result
            return $query->execute();
        }
        //returns an error if no identifier was provided
        return false;
    }

    /**
     * Return structured transaction.
     *
     * @return object A public version of transaction
     */
    public function structureData()
    {
        //create transaction structure
        $transaction = $this;
        $transaction->id = (int) $transaction->id;
        if (isset($transaction->amount)) {
            $transaction->amount = floatval($transaction->amount);
        }
        if (isset($transaction->datePosted)) {
            $transaction->datePosted = date('c', $transaction->datePosted);
        }
        if (isset($transaction->dateUser)) {
            $transaction->dateUser = date('c', $transaction->dateUser);
        }
        $transaction->category = (int) $transaction->category;
        $transaction->subcategory= (int) $transaction->subcategory;
        unset($transaction->aid);
        //return structured transaction
        return $transaction;
    }
}
