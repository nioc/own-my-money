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
}
