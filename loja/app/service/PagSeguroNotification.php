<?php
/**
 * Customer Registration Form
 * @author  <your name here>
 */
class PagSeguroNotification
{
    public static function receive($param)
    {
        if (PHP_SAPI !== 'cli')
        {
            return;
        }
        
        /**
            http://www.site.com.br/loja/pagseguro-notification
                ?notificationType=transaction
                &notificationCode=AB52E1-3F4BFD4BFDD1-D234A07FB59E-039312
        */
        
        if (isset($param['notificationType']) AND $param['notificationType'] == 'transaction')
        {
            $ini     = require 'app/config/pagseguro.php';
            $account = $ini['account'];
            $token   = $ini['token'];
            $code    = $param['notificationCode'];
            $url     = "https://ws.pagseguro.uol.com.br/v2/transactions/notifications/{$code}?email={$account}&token={$token}";
            
            try
            {
                $content = file_get_contents($url);
                $xml = simplexml_load_string($content);
                
                $transaction = (string) $xml->code;
                $id          = str_replace( APPLICATION_NAME . '_', '', (string) $xml->reference );
                $status      = (string) $xml->status;
                
                TTransaction::open('store');
                $object = Transaction::find($id);
                
            	if ( ((int) $object->paymentstatus_id !== (int) $status) AND ($object->paymentstatus->isfinal !== 'Y' ) )
            	{
            	    $object->paymentstatus_id = $status;
            	    $object->external_id      = $transaction;
            	    $object->store();
            	}
                TTransaction::close();
            }
            catch (Exception $e) // in case of exception
            {
                new TMessage('error', $e->getMessage());
                TTransaction::rollback();
            }
        }
    }
}
