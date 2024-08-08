<?php
/**
 * CouponForm Registration
 * @author  <your name here>
 */
class CouponForm extends TPage
{
    protected $form; // form
    
    use Adianti\Base\AdiantiStandardFormTrait;
    
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->setDatabase('store');    // defines the database
        $this->setActiveRecord('Coupon');   // defines the active record
        
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
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_Coupon');
        $this->form->setFormTitle(_t('Coupon'));
        
        // create the form fields
        $id             = new TEntry('id');
        $email          = new TEntry('email');
        $product_id     = new TDBUniqueSearch('product_id', 'store', 'Product', 'id', 'description');
        $expiration     = new TDate('expiration');
        $discount       = new TEntry('discount');
        $used           = new TRadioGroup('used');

        $id->setEditable(FALSE);
        $used->addItems( ['Y' => _t('Yes'), 'N' => _t('No') ] );
        $product_id->setMinLength(0);
        $used->setUseButton(true);
        $used->setLayout('horizontal');
        
        // add the fields
        $this->form->addFields( [new TLabel('ID')], [$id] );
        $this->form->addFields( [new TLabel(_t('Email'))], [$email] );
        $this->form->addFields( [new TLabel(_t('Product'))], [$product_id] );
        $this->form->addFields( [new TLabel(_t('Expiration'))], [$expiration] );
        $this->form->addFields( [new TLabel(_t('Discount'))], [$discount] );
        $this->form->addFields( [new TLabel(_t('Used'))], [$used] );
        
        $id->setSize('30%');
        $email->setSize('70%');
        $product_id->setSize('70%');
        $expiration->setSize('70%');
        $discount->setSize('70%');
        
        $this->form->addAction( _t('Save'), new TAction(array($this, 'onSave')), 'far:save');
        $this->form->addAction( _t('Clear'), new TAction(array($this, 'onEdit')), 'fa:eraser red');
        $this->form->addAction( _t('Back to the listing'), new TAction(array('CouponList', 'onReload')), 'fa:table blue');
        
        // validations
        $email->addValidation(_t('Email'), new TRequiredValidator);
        $product_id->addValidation(_t('Product'), new TRequiredValidator);
        $expiration->addValidation(_t('Expiration'), new TRequiredValidator);

        // add the form to the page
        parent::add($this->form);
    }
}
