<?php
/**
 * Dataset handler.
 *
 * @version 1.0.0
 *
 * @internal
 */
class Dataset
{
    /**
     * @var string Filepath
     */
    private $filepath;

    public function __construct($filepath = null)
    {
        if ($filepath !== null) {
            $this->filepath = $filepath;
        }
    }

    /**
     * Parse accounts from OFX file.
     *
     * @param int User identifier posting dataset
     *
     * @return array Accounts
     */
    public function parseAccountsFromOfx($userId)
    {
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Account.php';
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/vendor/ofxparser/lib/OfxParser/Parser.php';
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/vendor/ofxparser/lib/OfxParser/Ofx.php';
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/vendor/ofxparser/lib/OfxParser/Entities/AbstractEntity.php';
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/vendor/ofxparser/lib/OfxParser/Entities/SignOn.php';
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/vendor/ofxparser/lib/OfxParser/Entities/Status.php';
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/vendor/ofxparser/lib/OfxParser/Entities/Institute.php';
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/vendor/ofxparser/lib/OfxParser/Entities/BankAccount.php';
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/vendor/ofxparser/lib/OfxParser/Entities/Statement.php';
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/vendor/ofxparser/lib/OfxParser/Entities/Transaction.php';
        $ofxParser = new \OfxParser\Parser();
        $ofx = $ofxParser->loadFromFile($this->filepath);

        $accounts = [];
        foreach ($ofx->bankAccounts as $bankAccount) {
            if (isset($bankAccount->routingNumber, $bankAccount->agencyNumber, $bankAccount->accountNumber)) {
                $account = new Account();
                if (!$account->getByPublicId($userId, $bankAccount->routingNumber, $bankAccount->agencyNumber, $bankAccount->accountNumber)) {
                    //account does not exists, creating it
                    $account->user = $userId;
                    $account->bankId = $bankAccount->routingNumber;
                    $account->branchId = $bankAccount->agencyNumber;
                    $account->accountId = $bankAccount->accountNumber;
                    $account->lastUpdate = time();
                }
                $account->balance = floatval($bankAccount->balance);
                $account->transactions = $bankAccount->statement->transactions;
                array_push($accounts, $account);
            }
        }
        //return all accounts read from OFX file
        return $accounts;
    }

    /**
     * Parse account transactions from OFX file
     *
     * @param Account $Account Account read from OFX file
     *
     * @return array Transactions
     */
    public function parseTransactionsFromOfx($account)
    {
        $transactions = [];
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Configuration.php';
        $configuration = new Configuration();
        $regexp = $configuration->get('ofxRemoveRegex');
        foreach ($account->transactions as $currentTransaction) {
            $transaction = new Transaction();
            $transaction->datePosted = $currentTransaction->date->getTimestamp();
            $transaction->dateUser = $transaction->datePosted;
            $transaction->aid = $account->id;
            $transaction->amount = $currentTransaction->amount;
            $transaction->fitid = $currentTransaction->uniqueId;
            $transaction->name = $currentTransaction->name;
            $transaction->memo = ($regexp !== '') ? preg_replace($regexp, '', $currentTransaction->memo) : $currentTransaction->memo;
            $transaction->type = 'DEBIT';
            if ($transaction->amount > 0) {
                $transaction->type = 'CREDIT';
            }
            array_push($transactions, $transaction);
        }
        //return all transactions of the provided account
        return $transactions;
    }

    /**
     * Parse account transactions from QIF file
     *
     * @param int $accountId Account identifier
     *
     * @return array Transactions
     */
    public function parseTransactionsFromQif($accountId)
    {
        $configuration = new Configuration();
        $dateFormat = $configuration->get('qifDateFormat');
        $uniqueFitId = $configuration->get('qifUniqueFitId');
        $qif = file_get_contents($this->filepath);
        $lines = explode(PHP_EOL, $qif);
        $transactions = [];
        $transaction = new Transaction();
        foreach ($lines as $line) {
            $field = substr($line, 0, 1);
            $value = trim(substr($line, 1));
            switch ($field) {
                case '!':
                    // Type, not used
                    break;
                case 'D':
                    // Date, parse using system configuration
                    if ($dateTime = DateTime::createFromFormat($dateFormat, $value)) {
                        $dateTime->setTime(12, 0, 0, 0);
                        $transaction->datePosted = $dateTime->getTimestamp();
                        $transaction->dateUser = $dateTime->getTimestamp();
                    }
                    break;
                case 'T':
                    // Amount, remove comma and get float
                    $transaction->amount = floatval(str_replace(",", "", $value));
                    break;
                case 'C':
                    // Status, not used
                    break;
                case 'N':
                    // Numero (check or reference number), not used
                    break;
                case 'P':
                    // Payee, use it as label
                    $transaction->name = $value;
                    break;
                case 'M':
                case 'E':
                    // Memo
                    $transaction->memo = $value;
                    break;
                case 'A':
                    // Address, not used
                    break;
                case 'L':
                case 'S':
                    // Category, not used
                    break;
                case 'O':
                    // Commission, not used
                    break;
                case "^":
                    // End of entry
                    if ($transaction->datePosted !== null && $transaction->amount !== null) {
                        // generate a pseudo fitid
                        $transaction->fitid = "omm-$accountId-".hash('md5', $transaction->datePosted.$transaction->amount.$transaction->name.$transaction->memo);
                        if ($uniqueFitId === '1') {
                            $transaction->fitid .= '-'.time().'-'.count($transactions);
                        }
                        $transaction->aid = $accountId;
                        $transaction->type = ($transaction->amount > 0) ? 'CREDIT' : 'DEBIT';
                        array_push($transactions, $transaction);
                    }
                    $transaction = new Transaction();
                    break;
            }
        }
        return $transactions;
    }

    /**
     * Parse account transactions from JSON file
     *
     * @param int $accountId Account identifier
     * @param string $mapCode Map to use for parsing JSON
     *
     * @return array Transactions
     */
    public function parseTransactionsFromJson($accountId, $mapCode)
    {
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Map.php';
        //get file content and decode JSON
        $json = file_get_contents($this->filepath);
        $jsTransactions = json_decode($json, true);
        if ($jsTransactions === null) {
            error_log(json_last_error_msg());
            //JSON invalid return empty list
            return [];
        }
        //get map
        $map = new Map($mapCode);
        if (!$map->get()) {
            //map not found return empty list
            return [];
        }
        $mapAttributes = array_flip($map->getAttributes());
        $transactions = [];
        foreach ($jsTransactions as $currentTransaction) {
            $transaction = new Transaction();
            foreach ($currentTransaction as $key => $value) {
                //parse provided transaction with Map to feed Transaction object
                if (array_key_exists($key, $mapAttributes)) {
                    //attribute in provided transaction is found in map, feed the related Transaction attribute with the value
                    $attribute = $mapAttributes[$key];
                    if ($attribute === 'dateUser' || $attribute === 'datePosted') {
                        //handle format for date attributes
                        if ($dateTime = DateTime::createFromFormat($map->dateFormat, $value)) {
                            $value = $dateTime->getTimestamp();
                        }
                    }
                    $transaction->$attribute = $value;
                }
            }
            $transaction->aid = $accountId;
            $transaction->type = 'DEBIT';
            if ($transaction->amount > 0) {
                $transaction->type = 'CREDIT';
            }
            array_push($transactions, $transaction);
        }
        return $transactions;
    }
}
