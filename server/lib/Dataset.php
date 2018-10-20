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
                    $account->balance = floatval($bankAccount->balance);
                    $account->lastUpdate = time();
                }
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
        foreach ($account->transactions as $currentTransaction) {
            $transaction = new Transaction();
            $transaction->datePosted = $currentTransaction->date->getTimestamp();
            $transaction->dateUser = $transaction->datePosted;
            $transaction->aid = $account->id;
            $transaction->amount = $currentTransaction->amount;
            $transaction->fitid = $currentTransaction->uniqueId;
            $transaction->name = $currentTransaction->name;
            $transaction->memo = $currentTransaction->memo;
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
        //get map
        $map = new Map($mapCode);
        if (!$map->get()) {
            //map not found return empty list
            return [];
        }
        $mapAttributes = $map->getAttributes();
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
