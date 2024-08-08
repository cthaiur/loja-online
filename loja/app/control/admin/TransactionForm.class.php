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
class TransactionForm extends TPage
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
        
        // security check
        if (TSession::getValue('role') !== 'ADMINISTRATOR')
        {
            throw new Exception(_t('Permission denied'));
        }
        
        $id = new THidden('id');
        $paymentstatus = new TDBCombo('paymentstatus_id', 'store', 'PaymentStatus', 'id', 'description');
        $paymentstatus->setSize('100%');
        $value = new TNumeric('value', 2, ',', '.');
        $obs = new TText('obs');;
        
        $this->form = new BootstrapFormBuilder;
        $this->form->setFormTitle(_t('Update data'));
        $this->form->addFields( [$id] )->style="display:none";
        $this->form->addFields( [new TLabel(_t('Status'))], [$paymentstatus],  [new TLabel(_t('Price'))], [$value] );
        $this->form->addFields( [new TLabel(_t('Obs'))], [$obs] );
        $this->form->addAction(_t('Save'), new TAction(array($this, 'onSave')), 'far:save green');
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
            $this->form->setData($transaction); // só para passar id e status de pagamento
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
            $this->html->enableSection('main', $customer_array );
            $this->html->enableSection('status', array('paymentstatus_name' => $transaction->paymentstatus_name,
                                                       'paymenttype_name' => $transaction->paymenttype_name));
            $this->html->enableSection('transaction_id', array('transaction_id' => $transaction->external_id ? $transaction->external_id : '',
                                                               'id' => $transaction->id));
            $this->html->enableSection('product', array('description'=> $product->description,
                                                        'amount' => $transaction->quantity,
                                                        'price' => number_format($transaction->value, 2),
                                                        'shipping_cost' => number_format($transaction->shipping_cost, 2),
                                                        'currency' => $product->currency,
                                                        'total' => number_format( ($transaction->quantity * $transaction->value) + $transaction->shipping_cost, 2) ) );
            $this->html->enableSection('otherobject', array('object' => $this->form));
            parent::add($this->html);
            TTransaction::close();
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }
    
    /**
     *
     */
    public function onSave()
    {
        try
        {
            $data = $this->form->getData();
            $param = array();
            $param['key'] = $data->id;
            
            TTransaction::open('store');
            $transaction = new Transaction($data->id);
            $transaction->paymentstatus_id = $data->paymentstatus_id;
            $transaction->obs = $data->obs;
            if ((float) $transaction->value !== (float) $data->value)
            {
                $transaction->value = (float) $data->value;
                $transaction->total = ($transaction->quantity * $transaction->value) + $transaction->shipping_cost;
            }
            
            $transaction->store();
            $customer = $transaction->customer;
            $product_name = $transaction->product_name;
            TTransaction::close();
            
            $this->onLoad($param);
            new TMessage('info', TAdiantiCoreTranslator::translate('Record saved'));
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }
}
