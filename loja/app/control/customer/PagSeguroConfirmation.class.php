<?php
/**
 * Template View pattern implementation
 *
 * @version    1.0
 * @package    samples
 * @subpackage tutor
 * @author     Pablo Dall'Oglio <framework@adianti.com.br>
 * @copyright  Copyright (c) 2006-2011 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework/license
 */
class PagSeguroConfirmation extends TPage
{
    private $customerCode;
    private $form;
    private $description;
    private $amount;
    private $price;
    private $product_id;
    
    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct();
        
        // security check
        if (TSession::getValue('logged') !== TRUE)
        {
            throw new Exception(_t('Not logged'));
        }
    }
    
    /**
     * Carrega um produto na transação
     */
    public function onLoad($param)
    {
        $ini = require 'app/config/pagseguro.php';
        
        try
        {
            \PagSeguro\Library::initialize();
            
            $transaction_id = TSession::getValue('current-transaction-id');
            
            \PagSeguro\Configuration\Configure::setAccountCredentials($ini['account'], $ini['token']);
            
            $response = \PagSeguro\Services\Transactions\Search\Reference::search(
                \PagSeguro\Configuration\Configure::getAccountCredentials(),
                APPLICATION_NAME . '_' . $transaction_id,
                []
            );
            
            $transactions = $response->getTransactions();
            
            if ($transactions)
            {
                $pagseg_transaction = $transactions[0];
            }
            
            TTransaction::open('store');
            $customer    = Customer::newFromEmail( TSession::getValue('login') );
            $transaction = new Transaction($transaction_id);
            $transaction->external_id = $param['transaction_id'];
            $transaction->paymentstatus_id = $pagseg_transaction->getStatus();
            $transaction->store();
            
            $product = $transaction->product;
            TTransaction::close();
            
            $customer_array = $customer->toArray();
            $customer_array['complement'] = isset($customer_array['complement']) ? $customer_array['complement'] : '';
                        
            $this->html = new THtmlRenderer('app/resources/transaction.html');
            $this->html->enableTranslation();
            $this->html->enableSection('main', $customer_array );
            $this->html->enableSection('steps');
            $this->html->enableSection('transaction_id', array('transaction_id' => $transaction->external_id,
                                                               'id' => $transaction->id) );
            
            $this->html->enableSection('product', array('description'=> $product->description,
                                                        'amount' => $transaction->quantity,
                                                        'price' => number_format($transaction->value, 2),
                                                        'shipping_cost' => number_format($transaction->shipping_cost, 2),
                                                        'currency' => $product->currency,
                                                        'total' => number_format($transaction->quantity * $transaction->value, 2) ) );
            $this->html->enableSection('comment');
            parent::add($this->html);
            
        }
        catch (Exception $e)
        {
            TTransaction::rollback();
            new TMessage('error', $e->getMessage());
        }
    }
}
