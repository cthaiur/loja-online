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
class OrderView extends TPage
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
        try
        {
            $key = $param['key'];
            
            TTransaction::open('store');
            $transaction = new Transaction($key);
            $product  = $transaction->product;
            $customer = $transaction->customer;
            $logged   = Customer::newFromEmail(TSession::getValue('login'));
            
            if (TSession::getValue('login') !== 'admin')
            {
                if ($transaction->customer->email !== $logged->email)
                {
                    throw new Exception(_t('Invalid try'));
                }
            }
            
            $customer_array = $customer->toArray();
            $customer_array['complement'] = isset($customer_array['complement']) ? $customer_array['complement'] : '';
            
            $this->html = new THtmlRenderer('app/resources/transaction.html');
            $this->html->enableTranslation();
            $this->html->disableHtmlConversion();
            
            if (TSession::getValue('login') == 'admin')
            {
                $customer_array['customer_name'] = $customer_array['name'];
            }
            
            $this->html->enableSection('main', $customer_array);
            $this->html->enableSection('status', array('paymentstatus_name' => $transaction->paymentstatus_name,
                                                       'paymenttype_name' => $transaction->paymenttype_name ? $transaction->paymenttype_name : ''));
            if ($transaction->shipping_code)
            {
                $this->html->enableSection('tracking', array('shipping_code' => $transaction->shipping_code));
            }
            
            $this->html->enableSection('transaction_id', array('transaction_id' => $transaction->external_id ? $transaction->external_id : '',
                                                               'id' => $transaction->id));
            $this->html->enableSection('product', array('description'=> $product->description,
                                                        'amount' => $transaction->quantity,
                                                        'price' => number_format($transaction->value, 2, ',', '.'),
                                                        'shipping_cost' => number_format( (float) $transaction->shipping_cost, 2, ',', '.'),
                                                        'currency' => $product->currency,
                                                        'total' => number_format($transaction->total, 2, ',', '.') ) );
            
            
            if (TSession::getValue('login') == 'admin')
            {
                $actions = TransactionAction::where('transaction_id', '=', $transaction->id)->orderBy('process_time', 'asc')->load();
                
                $actions_array = [];
                $i = 0;
                if ($actions)
                {
                    foreach ($actions as $action)
                    {
                        $actions_array[] = ['time' => $action->process_time,
                                            'color' => ($i%2)==0 ? 'whitesmoke' : 'white',
                                            'action' => Action::find($action->action_id)->name];
                        $i ++;
                    }
                }
                
                if (count($actions)>0)
                {
                    $this->html->enableSection('actions', TRUE);
                    $this->html->enableSection('actions-detail', $actions_array, TRUE);
                }
            }
            
            
            
            
            $transactions = $transaction->getByCustomer( $customer );
            if (!empty($transactions))
            {
                $purchases = array();
                $total = 0;
                $i = 0;
                
                foreach ($transactions as $other_transaction)
                {
                    $purchase = $other_transaction->toArray();
                    $purchase['product_description'] = $other_transaction->product->description;
                    $purchase['color'] = ($i%2)==0 ? 'whitesmoke' : 'white';
                    $purchase['bordercolor'] = $other_transaction->id == $transaction->id ? '#4474B8' : '#ABABAB';
                    $purchase['borderwidth'] = $other_transaction->id == $transaction->id ? '4' : '0';
                    $purchase['status_description'] = _t($other_transaction->paymentstatus->description);
                    $purchase['status_info'] = $other_transaction->get_paymentstatus_info(false);
                    $purchase['total'] = number_format($purchase['total'], 2, ',', '.');
                    $purchases[] = $purchase;
                    
                    if (in_array($other_transaction->paymentstatus->id, Transaction::APROVEDS ))
                    {
                        $total += $other_transaction->total;
                    }
                    $i ++;
                }
                $this->html->enableSection('purchases', TRUE);
                $this->html->enableSection('purchases-detail', $purchases, TRUE);
                
                if (TSession::getValue('login') == 'admin')
                {
                    $this->html->enableSection('purchases-total', ['total'=> number_format($total,2, ',', '.')]);
                }
            }
            
            parent::add($this->html);
            TTransaction::close();
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }
}
