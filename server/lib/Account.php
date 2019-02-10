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
     * @var boolean Account has an icon
     */
    public $hasIcon;
    /**
     * @var string URL for account icon
     */
    public $iconUrl;
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
     * @param int $periodStart Start timestamp for requesting
     * @param int $periodEnd End timestamp for requesting
     *
     * @return array All account transactions
     */
    public function getTransactions($periodStart = 0, $periodEnd = null)
    {
        if ($periodEnd === null) {
            $periodEnd = time();
        }
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/DatabaseConnection.php';
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Transaction.php';
        $connection = new DatabaseConnection();
        $query = $connection->prepare('SELECT * FROM `transaction` WHERE `aid` = :aid AND `datePosted` > :periodStart AND `datePosted` < :periodEnd ORDER BY `datePosted` DESC;');
        $query->bindValue(':aid', $this->id, PDO::PARAM_INT);
        $query->bindValue(':periodStart', $periodStart, PDO::PARAM_INT);
        $query->bindValue(':periodEnd', $periodEnd, PDO::PARAM_INT);
        if ($query->execute()) {
            //return array of transactions
            return $query->fetchAll(PDO::FETCH_CLASS, 'Transaction');
        }
        //indicate there is a problem during querying
        return false;
    }

    /**
     * Store account icon.
     *
     * @param string $file Icon filename
     *
     * @return boolean True on success or false on failure
     */
    public function setIcon($file)
    {
        //get image type
        $type = exif_imagetype($file);
        switch ($type) {
            case IMAGETYPE_JPEG:
                $img = imagecreatefromjpeg($file);
            break;
            case IMAGETYPE_GIF:
                $img = imagecreatefromgif($file);
            break;
            case IMAGETYPE_PNG:
                $img = imagecreatefrompng($file);
            break;
            default:
                return false;
            break;
        }
        //resize icon to max allowed
        $iconMaxSize = 64;
        $x = imagesx($img);
        $y = imagesy($img);
        if ($x>$iconMaxSize or $y>$iconMaxSize) {
            if ($x>$y) {
                $nx = $iconMaxSize;
                $ny = $y/($x/$iconMaxSize);
            } else {
                $nx = $x/($y/$iconMaxSize);
                $ny = $iconMaxSize;
            }
        } else {
            $nx = $x;
            $ny = $y;
        }
        //create sampled image
        $img_sampled = imagecreatetruecolor($nx, $ny);
        //transparency
        if ($type === IMAGETYPE_PNG) {
            imagealphablending($img_sampled, false);
        }
        //progressive
        imageinterlace($img_sampled, true);
        imagecopyresampled($img_sampled, $img, 0, 0, 0, 0, $nx, $ny, $x, $y);
        imagedestroy($img);
        //output buffering
        ob_start();
        switch ($type) {
            case IMAGETYPE_JPEG:
                //output buffering jpeg in quality 92%
                $boo_return = imagejpeg($img_sampled, null, 92);
                break;
            case IMAGETYPE_GIF:
                //output buffering gif
                $boo_return = imagegif($img_sampled, null);
                break;
            case IMAGETYPE_PNG:
                //output buffering png in quality 0 (best)
                imagesavealpha($img_sampled, true);
                $boo_return = imagepng($img_sampled, null, 0);
                break;
            default:
                $boo_return = false;
                break;
        }
        $icon = ob_get_contents();
        ob_end_clean();
        if (!$boo_return) {
            return false;
        }
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/DatabaseConnection.php';
        $connection = new DatabaseConnection();
        $query = $connection->prepare('REPLACE INTO `account_icon` (`aid`, `mime_type`, `icon`) VALUES (:aid, :mime_type, :icon);');
        $query->bindValue(':aid', $this->id, PDO::PARAM_INT);
        $query->bindValue(':mime_type', $type, PDO::PARAM_STR);
        $query->bindValue(':icon', $icon, PDO::PARAM_STR);
        if ($query->execute()) {
            //set account has icon
            $this->hasIcon = true;
            $query = $connection->prepare('UPDATE `account` SET `hasIcon`=:hasIcon WHERE `id`=:id;');
            $query->bindValue(':id', $this->id, PDO::PARAM_INT);
            $query->bindValue(':hasIcon', $this->hasIcon, PDO::PARAM_BOOL);
            $query->execute();
            return true;
        }
        return false;
    }

    /**
     * Return account icon.
     *
     * @return string Account icon content or false on failure
     */
    public function getIcon()
    {
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/DatabaseConnection.php';
        $connection = new DatabaseConnection();
        $query = $connection->prepare('SELECT `icon`, `mime_type` FROM `account_icon` WHERE `aid` = :aid;');
        $query->bindValue(':aid', $this->id, PDO::PARAM_INT);
        if (!$query->execute()) {
            //indicate there is a problem during querying
            return false;
        }
        $icon = $query->fetch(PDO::FETCH_ASSOC);
        if (count($icon) === 0) {
            return false;
        }
        return $icon;
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
        if ($this->hasIcon) {
            $account->iconUrl = "accounts/$this->id/icons";
        }
        unset($account->hasIcon);
        unset($account->transactions);
        //return structured account
        return $account;
    }
}
