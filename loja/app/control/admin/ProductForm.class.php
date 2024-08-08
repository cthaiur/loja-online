<?php
/**
 * ProductForm Registration
 * @author  <your name here>
 */
class ProductForm extends TPage
{
    protected $form; // form
    protected $notebook;
    
    /**
     * Class constructor
     * Creates the page and the registration form
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
        
        $this->form = new BootstrapFormBuilder('form_Product');
        $this->form->setFormTitle(_t('Product'));
        
        // create the form fields
        $id                             = new TEntry('id');
        $description                    = new TEntry('description');
        $url                            = new TEntry('url');
        $amount                         = new TEntry('amount');
        $currency                       = new TEntry('currency');
        $price                          = new TEntry('price');
        $image                          = new TEntry('image');
        $languages                      = new TCheckGroup('languages');
        $paymenttypes                   = new TDBCheckGroup('paymenttypes', 'store', 'PaymentType', 'id', 'description');
        $actions                        = new TDBCheckGroup('actions', 'store', 'Action', 'id', 'name');
        $requirements                   = new TDBMultiSearch('requirements', 'store', 'Product', 'id', 'description');
        $active                         = new TRadioGroup('active');
        $details                        = new THtmlEditor('details');
        $opinions                       = new THtmlEditor('opinions');
        $confirmation_mail              = new THtmlEditor('confirmation_mail');
        $tag                            = new TEntry('tag');
        
        $items = array('pt'=>'Português', 'en'=>'Inglês');
        $yesno = array('Y'=>_t('Yes'), 'N'=>_t('No'));
        
        $id->setEditable(FALSE);
        $languages->addItems($items);
        $active->addItems($yesno);
        $active->setLayout('horizontal');
        $active->setUseButton();
        $languages->setLayout('horizontal');
        $paymenttypes->setLayout('horizontal');
        $details->setSize('100%', '400');
        $opinions->setSize('100%', '400');
        $confirmation_mail->setSize('100%', '400');
        
        // add the fields
        $this->form->appendPage( _t('Data') );
        $this->form->addFields( [new TLabel('ID')], [$id] );
        $this->form->addFields( [new TLabel(_t('Description'))], [$description] );
        $this->form->addFields( [new TLabel('URL')], [$url] );
        $this->form->addFields( [new TLabel(_t('Currency'))], [$currency], [new TLabel(_t('Price'))], [$price] );
        $this->form->addFields( [new TLabel(_t('Image'))], [$image] );
        $this->form->addFields( [new TLabel(_t('Languages'))], [$languages] );
        $this->form->addFields( [new TLabel(_t('Methods'))], [$paymenttypes] );
        $this->form->addFields( [new TLabel(_t('Active'))], [$active] );
        $this->form->addFields( [new TLabel(_t('Tag'))], [$tag] );
        $this->form->addFields( [new TLabel(_t('Actions'))], [$actions]);
        $this->form->addFields( [new TLabel(_t('Requirements'))],  [$requirements]);
        
        $this->form->appendPage( _t('Details') );
        $this->form->addFields( [$details] );
        
        $this->form->appendPage( _t('Opinions') );
        $this->form->addFields( [$opinions] );
        
        $this->form->appendPage( _t('Confirmation e-mail') );
        $this->form->addFields( [$confirmation_mail] );
        
        $id->setSize('30%');
        $description->setSize('100%');
        $url->setSize('100%');
        $amount->setSize('100%');
        $currency->setSize('100%');
        $price->setSize('100%');
        $image->setSize('100%');
        $languages->setUseButton();
        $paymenttypes->setUseButton();
        $requirements->setSize('100%');
        
        // validations
        $description->addValidation(_t('Description'), new TRequiredValidator);
        $url->addValidation('URL', new TRequiredValidator);
        
        $this->form->addAction( _t('Save'), new TAction(array($this, 'onSave')), 'far:save');
        $this->form->addAction( _t('New'), new TAction(array($this, 'onEdit')), 'fa:plus-square green');
        
        parent::add($this->form);
    }
    
    /**
     * method onSave()
     * Executed whenever the user clicks at the save button
     */
    public static function onSave($param)
    {
        try
        {
            TTransaction::open('store');
            $data = (object) $param;
            
            $object = new Product;
            $object->fromArray($param);
            $object->languages    = isset($data->languages) ? implode(',', $data->languages) : null;
            $object->paymenttypes = isset($data->paymenttypes) ? implode(',', $data->paymenttypes) : null;
            $object->store();
            
            ProductAction::where('product_id', '=', $object->id)->delete();
            if (!empty($data->actions))
            {
                foreach ($data->actions as $action_id)
                {
                    $product_action = new ProductAction;
                    $product_action->product_id = $object->id;
                    $product_action->action_id = $action_id;
                    $product_action->store();
                }
            }
            
            ProductRequirement::where('product_id', '=', $object->id)->delete();
            if (!empty($data->requirements))
            {
                foreach ($data->requirements as $requirement_id)
                {
                    $product_req = new ProductRequirement;
                    $product_req->product_id = $object->id;
                    $product_req->requirement_id = $requirement_id;
                    $product_req->store();
                }
            }
            
            TTransaction::close();
            
            new TMessage('info', TAdiantiCoreTranslator::translate('Record saved'));
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
    
    /**
     * method onEdit()
     * Executed whenever the user clicks at the edit button da datagrid
     * @param  $param An array containing the GET ($_GET) parameters
     */
    public function onEdit($param)
    {
        try
        {
            if (isset($param['key']))
            {
                $key=$param['key'];
                TTransaction::open('store');
                
                $class = 'Product';
                $object = new $class($key);
                $object->languages    = explode(',', $object->languages);
                $object->paymenttypes = explode(',', $object->paymenttypes);
                $object->actions      = ProductAction::where('product_id', '=', $object->id)->getIndexedArray('action_id', 'action_id');
                $object->requirements = ProductRequirement::where('product_id', '=', $object->id)->getIndexedArray('requirement_id', 'requirement_id');
                $this->form->setData($object);
                TTransaction::close();
            }
            else
            {
                $this->form->clear();
            }
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
}
