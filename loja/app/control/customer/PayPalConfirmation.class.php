<?php
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\ProductionEnvironment;

class PayPalConfirmation extends TPage
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
        $ini  = require 'app/config/paypal.php';
        $host = 'http://'.$_SERVER['HTTP_HOST'];
        
        try
        {
            TTransaction::open('store');
            $customer    = Customer::newFromEmail(TSession::getValue('login'));
            $transaction = new Transaction( TSession::getValue('current-transaction-id') );
            $product     = $transaction->product;
            $currency    = ($product->currency == 'U$') ? 'USD' : 'BRL';
            
            $client  = new PayPalHttpClient(new ProductionEnvironment($ini['client_id'], $ini['client_secret']));
            $request = new OrdersCaptureRequest($transaction->token);
            
            $response = $client->execute($request);
            
            // print "Status Code: {$response->statusCode}\n";
            // print "Status: {$response->result->status}\n";
            // print "Order ID: {$response->result->id}\n";
            
            $transaction->external_id = $response->result->purchase_units[0]->payments->captures[0]->id;
            
            if ($response->result->status == 'COMPLETED')
            {
                $transaction->paymentstatus_id = 4;
                $enable_section = 'comment2';
            }
            else
            {
                $transaction->paymentstatus_id = 1;
                $enable_section = 'comment3';
            }
            
            $transaction->store();
            
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
            $this->html->enableSection($enable_section);
            parent::add($this->html);
        }
        catch (Exception $e)
        {
            TTransaction::rollback();
            new TMessage('error', $e->getMessage());
        }
    }
}
