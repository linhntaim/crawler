<?php

namespace App\Utils;

trait TransactionTrait
{
    protected function transactionHelper()
    {
        return TransactionHelper::getInstance();
    }

    protected function transactionStart($connection = null, $isolationLevel = null)
    {
        $this->transactionHelper()->start($connection, $isolationLevel);
    }

    protected function transactionComplete()
    {
        $this->transactionHelper()->complete();
    }

    protected function transactionStop()
    {
        $this->transactionHelper()->stop();
    }
}