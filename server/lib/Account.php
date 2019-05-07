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
     * Return account balance.
     *
     * @param int $period Timestamp of the requested balance
     * @param int $budgetedOnly Handle only transaction from budgeted categories
     *
     * @return float Balance at requested period
     */
    public function getBalance($period = null, $budgetedOnly = true)
    {
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/DatabaseConnection.php';
        $connection = new DatabaseConnection();
        $query = $connection->prepare('SELECT `balance` FROM `account` WHERE `account`.`id`=:id;');
        $query->bindValue(':id', $this->id, PDO::PARAM_INT);
        if (!$query->execute()) {
            return false;
        }
        $lastBalance = $query->fetch(PDO::FETCH_ASSOC);
        $balance = floatval($lastBalance['balance']);
        if ($period === null) {
            return $balance;
        }
        $query = $connection->prepare('SELECT SUM(`amount`) AS `balance` FROM `transaction` LEFT JOIN `category` ON `transaction`.`category`=`category`.`id` WHERE `transaction`.`aid`=:id AND `datePosted` > :period AND (`isBudgeted`=:isBudgeted OR `isBudgeted`=TRUE OR `isBudgeted` IS NULL);');
        $query->bindValue(':id', $this->id, PDO::PARAM_INT);
        $query->bindValue(':period', $period, PDO::PARAM_INT);
        $query->bindValue(':isBudgeted', $budgetedOnly, PDO::PARAM_BOOL);
        if (!$query->execute()) {
            return false;
        }
        $deltaBalance = $query->fetch(PDO::FETCH_ASSOC);
        $balance = $balance - floatval($deltaBalance['balance']);
        return $balance;
    }

    private function getTransactionsByType($type, $sqlFormat, $isRecurringOnly, $periodStart, $periodEnd, $budgetedOnly, $category, $subcategory)
    {
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/DatabaseConnection.php';
        $connection = new DatabaseConnection();
        if ($type === 'debit') {
            $countType = 'countDebit';
        } else {
            $countType = 'countCredit';
        }
        if ($category === null && $subcategory === null) {
            $query = $connection->prepare('SELECT FROM_UNIXTIME(`datePosted`, :format) AS `date`, SUM(`amount`) AS :type, COUNT(1) AS :countType FROM `transaction` LEFT JOIN `category` ON `transaction`.`category`=`category`.`id` WHERE `transaction`.`aid`=:id AND `datePosted` > :periodStart AND `datePosted` < :periodEnd AND `type`=:type AND (`isBudgeted`=:isBudgeted OR `isBudgeted`=TRUE OR `isBudgeted` IS NULL) AND (`isRecurring`=:isRecurring OR `isRecurring`=TRUE) GROUP BY FROM_UNIXTIME(`datePosted`, :format) ORDER BY `datePosted` ASC;');
        } elseif ($subcategory === null) {
            //query only transactions in the specific category
            $query = $connection->prepare('SELECT FROM_UNIXTIME(`datePosted`, :format) AS `date`, SUM(`amount`) AS :type, COUNT(1) AS :countType FROM `transaction` WHERE `transaction`.`aid`=:id AND `datePosted` > :periodStart AND `datePosted` < :periodEnd AND `type`=:type AND `category`=:category AND (`isRecurring`=:isRecurring OR `isRecurring`=TRUE) GROUP BY FROM_UNIXTIME(`datePosted`, :format) ORDER BY `datePosted` ASC;');
            $query->bindValue(':category', $category, PDO::PARAM_INT);
        } else {
            //query only transactions in the specific subcategory
            $query = $connection->prepare('SELECT FROM_UNIXTIME(`datePosted`, :format) AS `date`, SUM(`amount`) AS :type, COUNT(1) AS :countType FROM `transaction` WHERE `transaction`.`aid`=:id AND `datePosted` > :periodStart AND `datePosted` < :periodEnd AND `type`=:type AND `subcategory`=:subcategory AND (`isRecurring`=:isRecurring OR `isRecurring`=TRUE) GROUP BY FROM_UNIXTIME(`datePosted`, :format) ORDER BY `datePosted` ASC;');
            $query->bindValue(':subcategory', $subcategory, PDO::PARAM_INT);
        }
        $query->bindValue(':id', $this->id, PDO::PARAM_INT);
        $query->bindValue(':periodStart', $periodStart, PDO::PARAM_INT);
        $query->bindValue(':periodEnd', $periodEnd, PDO::PARAM_INT);
        $query->bindValue(':type', $type, PDO::PARAM_STR);
        $query->bindValue(':countType', $countType, PDO::PARAM_STR);
        $query->bindValue(':format', $sqlFormat, PDO::PARAM_STR);
        $query->bindValue(':isBudgeted', $budgetedOnly, PDO::PARAM_BOOL);
        $query->bindValue(':isRecurring', $isRecurringOnly, PDO::PARAM_BOOL);
        if (!$query->execute()) {
            return false;
        }
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Return account transactions history.
     *
     * @param int $periodStart Start timestamp for requesting
     * @param int $periodEnd End timestamp for requesting
     * @param int $timeUnit Time unit (M for monthly, W for weekly, D for daily which is default value)
     * @param int $budgetedOnly Request only transaction from budgeted categories
     * @param int $category Category identifier
     * @param int $subcategory Subcategory identifier
     * @param boolean $isRecurringOnly Request only recurring transactions
     *
     * @return array User transactions history
     */
    public function getTransactionsHistory($periodStart, $periodEnd, $timeUnit = 'D', $budgetedOnly = true, $category = null, $subcategory = null, $isRecurringOnly = false)
    {
        switch ($timeUnit) {
          case 'M':
            $sqlFormat = '%Y-%m';
            $dateInterval = 'P1M';
            $resultFormat = 'Y-m';
            break;
          case 'W':
            $sqlFormat = '%xW%v';
            $dateInterval = 'P1W';
            $resultFormat = 'o\WW';
            break;
          case 'D':
          default:
            $sqlFormat = '%Y-%m-%d';
            $dateInterval = 'P1D';
            $resultFormat = 'Y-m-d';
            break;
        }

        //get debits
        if (!$isRecurringOnly) {
            $debits = $this->getTransactionsByType('debit', $sqlFormat, false, $periodStart, $periodEnd, $budgetedOnly, $category, $subcategory);
        }
        $debitsRecurring = $this->getTransactionsByType('debit', $sqlFormat, true, $periodStart, $periodEnd, $budgetedOnly, $category, $subcategory);

        //get credits
        if (!$isRecurringOnly) {
            $credits = $this->getTransactionsByType('credit', $sqlFormat, false, $periodStart, $periodEnd, $budgetedOnly, $category, $subcategory);
        }
        $creditsRecurring = $this->getTransactionsByType('credit', $sqlFormat, true, $periodStart, $periodEnd, $budgetedOnly, $category, $subcategory);

        //create calendar for returning each days
        $calendar = [];
        $dateTime = new DateTime();
        $dateTime->setTimestamp($periodStart);
        do {
            $date = $dateTime->format($resultFormat);
            $calendar[$date]['debit'] = 0;
            $calendar[$date]['credit'] = 0;
            $calendar[$date]['countCredit'] = 0;
            $calendar[$date]['countDebit'] = 0;
            $dateTime->add(new DateInterval($dateInterval));
        } while ($dateTime->getTimestamp() <= $periodEnd);

        //put amount in calendar
        if (!$isRecurringOnly) {
            foreach ($debits as $point) {
                $calendar[$point['date']]['debit'] = floatval($point['debit']);
                $calendar[$point['date']]['countDebit'] = intval($point['countDebit']);
            }
            foreach ($credits as $point) {
                $calendar[$point['date']]['credit'] = floatval($point['credit']);
                $calendar[$point['date']]['countCredit'] = intval($point['countCredit']);
            }
        }
        foreach ($debitsRecurring as $point) {
            $calendar[$point['date']]['debitRecurring'] = floatval($point['debit']);
            $calendar[$point['date']]['countDebitRecurring'] = intval($point['countDebit']);
        }
        foreach ($creditsRecurring as $point) {
            $calendar[$point['date']]['creditRecurring'] = floatval($point['credit']);
            $calendar[$point['date']]['countCreditRecurring'] = intval($point['countCredit']);
        }

        //get balances (get balance at end period, then remove transactions sum for each date)
        if ($category === null && $subcategory === null) {
            $balance = $this->getBalance($periodEnd, $budgetedOnly);
            $calendar = array_reverse($calendar);
            foreach ($calendar as $date => $value) {
                $calendar[$date]['balance'] = $balance;
                $balance = $balance - $value['debit'] - $value['credit'];
            }
            $calendar = array_reverse($calendar);
        }

        //format in array
        $points = [];
        foreach ($calendar as $date => $value) {
            $point = new stdClass();
            $point->date = $date;
            if (!$isRecurringOnly) {
                $point->debit = key_exists('debit', $value) ? $value['debit'] : 0;
                $point->credit = key_exists('credit', $value) ? $value['credit'] : 0;
                $point->countDebit = key_exists('countDebit', $value) ? $value['countDebit'] : 0;
                $point->countCredit = key_exists('countCredit', $value) ? $value['countCredit'] : 0;
            }
            $point->debitRecurring = key_exists('debitRecurring', $value) ? $value['debitRecurring'] : 0;
            $point->creditRecurring = key_exists('creditRecurring', $value) ? $value['creditRecurring'] : 0;
            $point->countDebitRecurring = key_exists('countDebitRecurring', $value) ? $value['countDebitRecurring'] : 0;
            $point->countCreditRecurring = key_exists('countCreditRecurring', $value) ? $value['countCreditRecurring'] : 0;
            if ($category === null && $subcategory === null) {
                $point->balance = $value['balance'];
            }
            array_push($points, $point);
        }
        //order by date
        function dateCompare($a, $b)
        {
            return strcmp($a->date, $b->date);
        }
        usort($points, 'dateCompare');

        return $points;
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
