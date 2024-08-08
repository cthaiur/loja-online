<?php
class PagSeguroCollectService
{
    public static function process()
    {
        if (PHP_SAPI !== 'cli')
        {
            return;
        }
        
        $ini = require 'app/config/pagseguro.php';
        
        \PagSeguro\Library::initialize();
        \PagSeguro\Configuration\Configure::setAccountCredentials($ini['account'], $ini['token']);
        
        try
        {
            // open a transaction with database 'store'
            TTransaction::open('store');
            
            // creates a repository for Transaction
            $repository = new TRepository('Transaction');
            
            // creates a criteria
            $criteria = new TCriteria;
            $criteria->setProperty('order', 'id');
            $criteria->setProperty('direction', 'desc');
            
            $criteria->add(new TFilter('paymenttype_id', '=', 1));
            $criteria->add(new TFilter('paymentstatus_id', 'IN', PaymentStatus::where('isfinal', '=', 'N')->getIndexedArray('id')));
            $criteria->add(new TFilter('operation_date', '>=', date('Y-m-d', strtotime('-30 days'))));
            
            // load the objects according to criteria
            $objects = $repository->load($criteria);
            
            if ($objects)
            {
                $rows = [];
                
                // iterate the collection of active records
                foreach ($objects as $object)
                {
                    $response = \PagSeguro\Services\Transactions\Search\Reference::search(
                        \PagSeguro\Configuration\Configure::getAccountCredentials(),
                        APPLICATION_NAME . '_' . $object->id,
                        []
                    );
                    
                    $transactions = $response->getTransactions();
                    
                    if ($transactions)
                    {
                        foreach ($transactions as $transaction)
                        {
                            if ((int) $object->paymentstatus_id !== (int) $transaction->getStatus())
                            {
                                $object->paymentstatus_id = $transaction->getStatus();
                                
                                if (empty($object->external_id))
                                {
                                    $object->external_id = $transaction->getCode();
                                }
                                
                                $object->store();
                                
                                $status = new PaymentStatus($object-> paymentstatus_id);
                                $rows[] = "{$object->id} - {$object->product->description} - {$object->customer->name} - (\$ {$object->value}) - [{$status->description}] \n";
                            }
                        }
                    }
                }
                if ($rows)
                {
                    $config = require 'app/config/config.php';
                    MailService::send([$config['admin']], 'PagSeguro', implode("\n", $rows), 'text');
                }
            }
            
            // close the transaction
            TTransaction::close();
        }
        catch (Exception $e) // in case of exception
        {
            echo 'ERROR: ' . $e->getMessage();
            TTransaction::rollback();
        }
    }
}
