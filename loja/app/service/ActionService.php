<?php
class ActionService
{
    public static function processActions()
    {
        if (PHP_SAPI !== 'cli')
        {
            return;
        }
        
        $output = '';
        TTransaction::open('store');
        
        $pending_actions = ViewPendingActions::all();
        
        foreach ($pending_actions as $pending_action)
        {
            $transaction_id = $pending_action->transaction_id;
            $action_id      = $pending_action->action_id;
            $remote_method  = $pending_action->remote_method;
            
            if (method_exists('ActionService', $remote_method))
            {
                try
                {
                    if (self::$remote_method( $pending_action ))
                    {
                        self::registerAction( $transaction_id, $action_id );
                    }
                }
                catch (Exception $e)
                {
                    $output .= "$transaction_id - $remote_method [FAIL:" . $e->getMessage() . "]\n";
                }
            }
            else
            {
                $output .= "$transaction_id - $remote_method [FAIL:METHOD NOT FOUND]\n";
            }
        }
        
        if (!empty($output))
        {
            $config = require 'app/config/config.php';
            MailService::send([$config['admin']], 'Notificação de falha na loja', $output, 'text');
        }
        
        print $output;
        
        TTransaction::close();
        return $output;
    }
    
    /**
     *
     */
    public static function registerAction( $transaction_id, $action_id )
    {
        $datetime = date('Y-m-d H:i:s');
        
        $conn = TTransaction::get();
        
        $transaction_action = new TransactionAction;
        $transaction_action->transaction_id = $transaction_id;
        $transaction_action->action_id      = $action_id;
        $transaction_action->process_time   = $datetime;
        $transaction_action->store();
        
        // conta quantas ações já foram processadas
        $processed_transactions = TransactionAction::where('transaction_id','=',$transaction_id)->count();
        
        // conta quantas ações foram planejadas
        $rows = TDatabase::getData($conn, "SELECT count(*) as prepared_transactions
                                             FROM eco_transaction, eco_product, eco_product_action
                                            WHERE eco_transaction.product_id =  eco_product.id AND
                                                  eco_product.id = eco_product_action.product_id AND
                                                  eco_transaction.id = ?", null, [$transaction_id] );
        
        $prepared_transactions = $rows[0]['prepared_transactions'];
        
        if ($prepared_transactions == $processed_transactions AND $processed_transactions > 0)
        {
            $transaction = new Transaction($transaction_id);
            $transaction->paymentstatus_id = 8; // DELIVERED
            $transaction->store();
        }
    }
    
    /**
     *
     */
    public static function sendConfirmationMail( $transaction )
    {
        $subject = $transaction->product_description;
        $message = $transaction->render( $transaction->confirmation_mail );
        
        MailService::send([$transaction->customer_email], $subject, $message, 'html');
        return TRUE;
    }
    
    public static function sendAnotherThing( $transaction )
    {
        // ...
        
        return TRUE;
    }
}
