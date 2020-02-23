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
     * @var int Date transaction was posted to account
     */
    public $datePosted;
    /**
     * @var int Date user initiated transaction, if known
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
     * @var string Financial Institution Transaction ID
     */
    public $fitid;
    /**
     * @var string Optional memo from Financial Institution
     */
    public $memo;
    /**
     * @var int Category identifier
     */
    public $category;
    /**
     * @var int Subcategory identifier
     */
    public $subcategory;
    /**
     * @var string User note about the transaction
     */
    public $note;
    /**
     * @var boolean Indicate if transaction is reccuring or single shot
     */
    public $isRecurring;
    /**
     * @var int Date transaction was inserted
     */
    public $insertedTimestamp;

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
        if ($this->isRecurring === null) {
            $this->isRecurring = false;
        }
        $connection = new DatabaseConnection();
        $query = $connection->prepare('INSERT INTO `transaction` (`aid`, `fitid`, `type`, `datePosted`, `dateUser`, `amount`, `name`, `memo`, `category`, `subcategory`, `note`, `isRecurring`, `insertedTimestamp`) VALUES ( :aid, :fitid, :type, :datePosted, :dateUser, :amount, :name, :memo, :category, :subcategory, :note, :isRecurring, :insertedTimestamp);');
        $query->bindValue(':aid', $this->aid, PDO::PARAM_INT);
        $query->bindValue(':fitid', $this->fitid, PDO::PARAM_STR);
        $query->bindValue(':type', $this->type, PDO::PARAM_STR);
        $query->bindValue(':datePosted', $this->datePosted, PDO::PARAM_INT);
        $query->bindValue(':dateUser', $this->dateUser, PDO::PARAM_INT);
        //there is no PARAM_FLOAT in PDO so use PARAM_STR instead...
        $query->bindValue(':amount', $this->amount, PDO::PARAM_STR);
        $query->bindValue(':name', $this->name, PDO::PARAM_STR);
        $query->bindValue(':memo', $this->memo, PDO::PARAM_STR);
        $query->bindValue(':category', $this->category, PDO::PARAM_STR);
        $query->bindValue(':subcategory', $this->subcategory, PDO::PARAM_STR);
        $query->bindValue(':note', $this->note, PDO::PARAM_STR);
        $query->bindValue(':isRecurring', $this->isRecurring, PDO::PARAM_BOOL);
        $query->bindValue(':insertedTimestamp', time(), PDO::PARAM_INT);
        if ($query->execute()) {
            $this->id = (int) $connection->lastInsertId();
            //returns insertion was successfully processed
            return true;
        }
        //returns insertion has encountered an error
        if ($query->errorInfo()[1] !== 1062) {
            error_log('Transaction insertion error ' . $query->errorInfo()[1]. ': '. $query->errorInfo()[2]);
        }
        return false;
    }

    /**
     * Get shares of a Transaction.
     *
     * @return mixed True on success or false on failure
     */
    private function getShares()
    {
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/DatabaseConnection.php';
        $connection = new DatabaseConnection();
        $query = $connection->prepare('SELECT `user`, `share` FROM `transaction_user_dispatch` WHERE `transaction_user_dispatch`.`transaction` = :id;');
        $query->bindValue(':id', $this->id, PDO::PARAM_INT);
        $this->shares = [];
        if ($query->execute()) {
            $this->shares = $query->fetchAll(PDO::FETCH_OBJ);
            foreach ($this->shares as &$share) {
                $share->user = (int) $share->user;
                $share->share = (int) $share->share;
            }
            //returns the shares was successfully fetched
            return true;
        }
        //returns an error occurs when fetching shares
        return false;
    }

    /**
     * Populates a Transaction.
     *
     * @param int $userId User identifier to get transaction share
     *
     * @return mixed True on success or false on failure
     */
    public function get($userId)
    {
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/DatabaseConnection.php';
        $connection = new DatabaseConnection();
        $query = $connection->prepare('SELECT `transaction`.*, IFNULL(`share`, 100) AS `share` FROM `transaction` LEFT JOIN `transaction_user_dispatch` ON `user` = :user AND `transaction_user_dispatch`.`transaction` = `transaction`.`id` WHERE `id`=:id LIMIT 1;');
        $query->bindValue(':id', $this->id, PDO::PARAM_INT);
        $query->bindValue(':user', $userId, PDO::PARAM_INT);
        $query->setFetchMode(PDO::FETCH_INTO, $this);
        if ($query->execute() && $query->fetch()) {
            //get shares dispatch
            $this->getShares();
            //returns the transaction object was successfully fetched
            return true;
        }
        //returns the transaction is not known or database was not reachable
        return false;
    }

    /**
     * Validate a transaction object with provided informations.
     *
     * @param object $transaction Transaction object to validate
     * @param string $error The returned error message
     *
     * @return bool True if the transaction object provided is correct
     */
    public function validateModel($transaction, &$error)
    {
        $error = '';
        if ($transaction === null) {
            $error = 'invalid resource';
            //return false and detailed error message
            return false;
        }
        //iterate on each object attributes to set object
        foreach ($this as $key => $value) {
            if (property_exists($transaction, $key)) {
                //get provided attribute
                $this->$key = $transaction->$key;
            }
        }
        //check mandatory attributes
        if (!is_int($this->id)) {
            $error = 'integer must be provided in id attribute';
            //return false and detailed error message
            return false;
        }
        if (!is_string($this->name) || $this->name === '') {
            $error = 'string must be provided in name attribute';
            //return false and detailed error message
            return false;
        }
        if (!isset($this->amount) || $this->amount === '') {
            $error = 'float must be provided in amount attribute';
            //return false and detailed error message
            return false;
        }
        if (!is_string($this->fitid) || $this->fitid === '') {
            $error = 'string must be provided in fitid attribute';
            //return false and detailed error message
            return false;
        }
        if (!is_string($this->type) || ($this->type !== 'DEBIT') && ($this->type !== 'CREDIT')) {
            $error = 'either DEBIT or CREDIT must be provided in fitid attribute';
            //return false and detailed error message
            return false;
        }
        if ($this->shares) {
            if (array_sum(array_column($this->shares, 'share')) !== 100) {
                $error = 'total dispatch must be equal to 100%';
                //return false and detailed error message
                return false;
            }
            $shareUsers = array_column($this->shares, 'user');
            if (count(array_unique($shareUsers)) !== count($shareUsers)) {
                $error = 'there is duplication in dispatch';
                //return false and detailed error message
                return false;
            }
        }
        //Convert datetime from ISO8601 to Unix timestamp
        if (isset($this->datePosted)) {
            $this->datePosted = strtotime($this->datePosted);
        }
        if (isset($this->dateUser)) {
            $this->dateUser = strtotime($this->dateUser);
        }
        if ($this->category === '') {
            $this->category = null;
        }
        if ($this->subcategory === '') {
            $this->subcategory = null;
        }
        $this->amount = floatval($this->amount);
        //Transaction is valid
        return true;
    }

    /**
     * Update transaction with provided informations.
     *
     * @param string $error The returned error message
     *
     * @return bool True if the transaction is updated
     */
    public function update(&$error)
    {
        $error = '';
        if (is_int($this->id)) {
            require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/DatabaseConnection.php';
            $connection = new DatabaseConnection();
            $query = $connection->prepare('UPDATE `transaction` SET `name`=:name, `memo`=:memo, `category`=:category, `subcategory`=:subcategory, `note`=:note, `isRecurring`=:isRecurring WHERE `id`=:id;');
            $query->bindValue(':id', $this->id, PDO::PARAM_INT);
            $query->bindValue(':name', $this->name, PDO::PARAM_STR);
            $query->bindValue(':memo', $this->memo, PDO::PARAM_STR);
            $query->bindValue(':category', $this->category, PDO::PARAM_INT);
            $query->bindValue(':subcategory', $this->subcategory, PDO::PARAM_INT);
            $query->bindValue(':note', $this->note, PDO::PARAM_STR);
            $query->bindValue(':isRecurring', $this->isRecurring, PDO::PARAM_BOOL);
            if ($query->execute()) {
                //return true to indicate a successful transaction update
                return true;
            }
            $error = $query->errorInfo()[2];
        }
        //return false to indicate an error occurred while reading the transaction
        return false;
    }

    /**
     * Update transaction shares dispatch with provided informations.
     *
     * @param int $userId User identifier to set transaction share
     * @param string $error The returned error message
     *
     * @return bool True if shares are updated
     */
    public function updateShares($userId, &$error)
    {
        $error = '';
        if (!is_int($this->id)) {
            $error = 'transaction id is not a valid integer';
            //return false to indicate operation is not done
            return false;
        }
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/DatabaseConnection.php';
        $connection = new DatabaseConnection();
        $query = $connection->prepare('DELETE FROM `transaction_user_dispatch` WHERE `transaction` = :id');
        $query->bindValue(':id', $this->id, PDO::PARAM_INT);
        if (!$query->execute()) {
            $error = $query->errorInfo()[2];
            //return false to indicate an error occurs during operation
            return false;
        }
        $query = $connection->prepare('INSERT INTO `transaction_user_dispatch` (`transaction`, `user`, `share`) VALUES (:id, :user, :share);');
        foreach ($this->shares as $share) {
            if (!is_null($share->user) && !is_null($share->share)) {
                $query->bindValue(':id', $this->id, PDO::PARAM_INT);
                $query->bindValue(':user', $share->user, PDO::PARAM_INT);
                $query->bindValue(':share', $share->share, PDO::PARAM_INT);
                if (!$query->execute()) {
                    $error = $query->errorInfo()[2];
                    //return false to indicate an error occurs during operation
                    return false;
                }
                if ($userId === $share->user) {
                    //this is the requester share, update transaction
                    $this->share = $share->share;
                }
            }
        }
        //return true to indicate operation is successful
        return true;
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
     * Check if a Transaction matches to a pattern and apply it.
     *
     * @param string $userId User identifier to get patterns
     */
    public function applyPatterns($userId)
    {
        $transactionLabel = $this->name;
        if ($this->memo) {
            $transactionLabel = $this->memo . ' ' . $this->name;
        }
        // get user patterns
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Pattern.php';
        $patterns = Pattern::getAll($userId);
        //try to find a matching pattern
        foreach ($patterns as $pattern) {
            //escape regular expression characters in pattern
            $pattern->label = preg_quote($pattern->label, '/');
            if (preg_match("/^$pattern->label$/i", $transactionLabel)) {
                // apply pattern and end the loop
                $this->category = $pattern->category;
                $this->subcategory = $pattern->subcategory;
                $this->isRecurring = $pattern->isRecurring;
                if ($pattern->shares) {
                    $this->shares = $pattern->shares;
                }
                break;
            }
        }
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
        if (isset($transaction->category)) {
            $transaction->category = (int) $transaction->category;
        }
        if (isset($transaction->subcategory)) {
            $transaction->subcategory= (int) $transaction->subcategory;
        }
        if (isset($transaction->hasIcon)) {
            if ($transaction->hasIcon) {
                $transaction->iconUrl= "accounts/$transaction->aid/icons";
            }
            unset($transaction->hasIcon);
        }
        if (isset($transaction->isRecurring)) {
            $transaction->isRecurring = (bool) $transaction->isRecurring;
        }
        if (isset($transaction->share)) {
            $transaction->share = (int) $transaction->share;
        }
        unset($transaction->aid);
        unset($transaction->insertedTimestamp);
        //return structured transaction
        return $transaction;
    }

    /**
     * Update transactions category following an update on subcategory parent id
     *
     * @param string $oldCategory Previous transactions category
     * @param string $newCategory New transactions category
     * @param string $subcategory Transactions subcategory
     * @param string $error The returned error message
     *
     * @return bool True on success or false on failure
     */
    public static function updateFollowingParentSubategoryChange($oldCategory, $newCategory, $subcategory, &$error)
    {
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/DatabaseConnection.php';
        $connection = new DatabaseConnection();
        $query = $connection->prepare('UPDATE `transaction` SET `category`=:newCategory WHERE `category`=:oldCategory AND `subcategory`=:subcategory;');
        $query->bindValue(':oldCategory', $oldCategory, PDO::PARAM_INT);
        $query->bindValue(':newCategory', $newCategory, PDO::PARAM_INT);
        $query->bindValue(':subcategory', $subcategory, PDO::PARAM_INT);
        if ($query->execute()) {
            //returns udpate was successfully processed
            return true;
        }
        $error = $query->errorInfo()[2];
        //returns update has encountered an error
        return false;
    }
}
