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
class ConfirmationForm extends TPage
{
    private $form;
    private $description;
    private $price;
    private $product_id;
    private $paymenttype_id;
    private $notebook;
    
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
        
        // create the HTML Renderer
        $this->html = new THtmlRenderer('app/resources/preorder.html');
        $this->html->enableTranslation();
        $this->html->enableSection('main');
        
        $this->description    = new TLabel('description');
        $this->price          = new TLabel('price');
        $this->product_id     = new THidden('product_id');
        $this->paymenttype_id = new TRadioGroup('paymenttype_id');
        $this->details        = new TLabel('details');
        $this->opinions       = new TLabel('opinions');
        
        $this->paymenttype_id->setLayout('horizontal');
        $this->paymenttype_id->setUseButton();
        
        $this->notebook = new BootstrapFormBuilder( '100%', '100');
        $this->notebook->setProperty('style', 'border:none; box-shadow:none; margin:0');
        
        // create a quickform
        $this->form = new BootstrapFormBuilder('form_confirm_transaction');
        $this->form->setFormTitle(_t('Order data'));
        $this->form->addFields( [new TLabel(_t('Name'), null, null, 'B')], [$this->description] );
        $row= $this->form->addFields( [new TLabel(_t('Price'), null, null, 'B')], [$this->price], [new TLabel(_t('Payment'), null, null, 'B')], [$this->paymenttype_id] );
        $row->layout = ['col-sm-2 control-label', 'col-sm-2', 'col-sm-2 control-label', 'col-sm-6'];
        
        $this->form->addContent( [$this->notebook] );
        
        $this->notebook->appendPage( '<i class="far fa-file-alt" aria-hidden="true"></i> ' . _t('Details') );
        $this->notebook->addFields( [$this->details] );
        
        $this->details->setProperty('style', 'padding: 10px; user-select:none;');
        $this->opinions->setProperty('style', 'padding: 10px; user-select:none;');
        
        $this->form->addFields( [$this->product_id])->style='display:none';
        
        $this->description->setSize('100%');
        $this->price->setSize('100%');
        $this->description->style = 'float:left;text-align:left';
        $this->price->style = 'float:left;text-align:left';
        
        $btn1 = $this->form->addAction(_t('Change product'), new TAction(array('ProductPublicList', 'onReload')), 'fa:shopping-cart orange');
        $btn2 = $this->form->addAction(_t('Continue'), new TAction(array($this, 'onPreSaveTransaction'), ['static'=>'1']), 'far:arrow-alt-circle-right');
        
        $btn1->style = 'padding: 8px 10px !important; font-size: 12pt !important';
        $btn2->style = 'padding: 8px 10px !important;font-weight: bold; font-size: 12pt !important';
        $btn2->class = 'btn btn-success';
    }
    
    /**
     * Carrega um produto na transação
     */
    public function onLoad($param)
    {
        try
        {
            TTransaction::open('store');
            $customer = Customer::newFromEmail(TSession::getValue('login'));
            $product = new Product($param['key']);
            
            if ($product->active == 'N')
            {
                throw new Exception( _t('This product is not active currently') );
            }
            
            $requirements = ProductRequirement::where('product_id', '=', $product->id)->getIndexedArray('requirement_id', 'requirement_id');
            if (!empty($requirements))
            {
                $pre_transacitons = Transaction::where('customer_id', '=', $customer->id)->where('paymentstatus_id', 'IN', [3,4,8])->where('product_id','in', $requirements)->count();
                if ($pre_transacitons == 0)
                {
                    throw new Exception(_t('Prerequisites for the purchase of this product are not complete. Please contact us for more information'));
                }
            }
            
            $this->product_id->setValue($product->id);
            $this->description->setValue("<a target='newwindow' href='$product->url'><b> {$product->description} </b> <i class='fas fa-external-link-alt' aria-hidden='true'></i></a>");
            
            $coupon = Coupon::getCoupons($customer->email, $product->id);
            
            if ($coupon instanceof Coupon)
            {
                $info = new TImage('fa:info-circle blue');
                $info->title = _t('Discount coupon');
                
                $price = $product->price * (1 - ($coupon->discount/100));
                $this->price->setValue('<span class="strike red">'.$product->currency . ' ' . number_format($product->price, 2) . '</span> ' .
                                       '<span class="blue">'.$product->currency . ' ' . number_format($price, 2) . ' ' . $info );
            }
            else
            {
                $this->price->setValue($product->currency . ' ' . number_format($product->price, 2));
            }
            
            $this->details->setValue(str_replace(['{name}'],[$product->description], $product->details));
            
            if (!empty($product->opinions))
            {
                $this->notebook->appendPage( '<i class="far fa-comments" aria-hidden="true"></i> ' . _t('Opinions') );
                $this->notebook->addFields( [$this->opinions] );
                $this->opinions->setValue(str_replace(['{name}'],[$product->description], $product->opinions));
            }
            
            $paymenttypes = $product->getPaymentTypes(TSession::getValue('language'));
            $items   = [];
            $details = [];
            
            if ($paymenttypes)
            {
                foreach ($paymenttypes as $paymenttype)
                {
                    $type_icon = str_replace('fa:', 'fa fa-', $paymenttype->icon);
                    $items[$paymenttype->id] = "<i class='{$type_icon}' aria-hidden='true'></i>  " . $paymenttype->description;
                    $details[$paymenttype->id] = $paymenttype->information;
                }
            }
            
            TTransaction::close();
            
            $allowed_languages = array_keys($items);
            $this->paymenttype_id->addItems($items);
            //$this->paymenttype_id->setValue(array_shift($allowed_languages));
            
            foreach ($this->paymenttype_id->getLabels() as $key => $label)
            {
                $label->setTip( $details[$key] );
            }
            
            $i = 0;
            foreach ($this->paymenttype_id->getButtons() as $key => $button)
            {
                if ($i == 0)
                {
                    $button->id = 'button_popover';
                }
                $i++;
            }
            
            $msg = _t('Choose the payment method');
            TScript::create("setTimeout( function() { __adianti_show_popover('#button_popover', '', '{$msg}', 'bottom') }, 100)");
            
            $replace2 = array();
            $replace2['object']  = $this->form;
            
            // replace the otherobject section variables
            $this->html->enableSection('otherobject', $replace2);
            parent::add($this->html);
            
        }
        catch (Exception $e)
        {
            $action = new TAction(['ProductPublicList', 'onReload']);
            new TMessage('error', $e->getMessage(), $action);
        }
    }
    
    /**
     * Salva a transação
     */
    public static function onPreSaveTransaction($param)
    {
        try
        {
            if (empty($param['paymenttype_id']))
            {
                throw new Exception(_t('Choose the payment method'));
            }
            
            $new_param = [];
            $new_param['context'] = 'confirmation-form';
            AdiantiCoreApplication::loadPage('CustomerProfileForm', 'onLoad', $new_param);
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }
    
    /**
     * Salva a transação
     */
    public function onSaveTransaction($param)
    {
        try
        {
            TTransaction::open('store');
            
            $data = $this->form->getData();
            
            if (empty($data->paymenttype_id))
            {
                throw new Exception(_t('Choose the payment method'));
            }
            
            $customer = Customer::newFromEmail( TSession::getValue('login') );
            $product  = Product::find($data->product_id);
            
            if ($customer instanceof Customer AND $product instanceof Product)
            {
                $transaction = new Transaction;
                $transaction->customer = $customer;
                $transaction->product = $product;
                $transaction->operation_date = date('Y-m-d');
                $transaction->operation_time = date('Y-m-d H:i:s');
                $transaction->quantity = 1;
                $transaction->value = $product->price;
                $transaction->paymentstatus_id = 99;
                $transaction->paymenttype_id = $data->paymenttype_id;
                
                $coupon = Coupon::getCoupons($customer->email, $product->id);
                
                if ($coupon instanceof Coupon)
                {
                    $transaction->value = $transaction->value * (1 - ($coupon->discount/100));
                    $coupon->used = 'Y';
                    $coupon->store();
                }
                
                $transaction->total = ($transaction->quantity * $transaction->value);
                $transaction->store();
                
                // process payment
                $info = PaymentFacade::processPayment($transaction);
                $url  = $info->url;
                
                $customer_array = $customer->toArray();
                $customer_array['complement'] = isset($customer_array['complement']) ? $customer_array['complement'] : '';
                
                $this->html = new THtmlRenderer('app/resources/transaction.html');
                $this->html->enableTranslation();
                $this->html->enableSection('main', $customer_array );
                
                $this->html->enableSection('steps');
                $this->html->enableSection('product', array('description'=> $product->description,
                                                            'amount' => 1,
                                                            'price' => number_format($transaction->value, 2),
                                                            'currency' => $product->currency,
                                                            'total' => number_format( $transaction->total, 2) ) );
                if ($data->paymenttype_id == 1) // PAGSEGURO
                {
                    $this->html->enableSection('pagbutton', array( 'url' => $url) );
                }
                else if ($data->paymenttype_id == 2) // PAYPAL
                {
                    $this->html->enableSection('paybutton', array( 'url' => $url) );
                }
                else if ($data->paymenttype_id == 3) // TED
                {
                    $this->html->enableSection('tedbutton', array( 'url' => $url) );
                }
                parent::add($this->html);
            }
            TTransaction::close();
        }
        catch (Exception $e)
        {
            $posaction = new TAction(['ConfirmationForm', 'onLoad']);
            $posaction->setParameter('key', $param['product_id']);
            
            new TMessage('error', $e->getMessage(), $posaction);
            TTransaction::rollback();
        }
    }
}
