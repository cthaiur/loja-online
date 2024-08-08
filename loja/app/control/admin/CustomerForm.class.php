<?php
/**
 * CustomerForm Registration
 * @author  <your name here>
 */
class CustomerForm extends TPage
{
    protected $form; // form
    
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
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_Customer');
        $this->form->setFormTitle( _t('Customer') );

        // create the form fields
        $id             = new TEntry('id');
        $name           = new TEntry('name');
        $email          = new TEntry('email');
        $password       = new TPassword('password');
        $document       = new TEntry('document');
        $phone          = new TEntry('phone');
        $address        = new TEntry('address');
        $number         = new TEntry('number');
        $complement     = new TEntry('complement');
        $neighborhood   = new TEntry('neighborhood');
        $postal         = new TEntry('postal');
        $city           = new TEntry('city');
        $state          = new TEntry('state');
        $country_id     = new TDBCombo('country_id', 'store', 'Country', 'id', 'name');
        $obs            = new TText('obs');

        // define the sizes
        $id->setSize('50%');
        $name->setSize('100%');
        $email->setSize('100%');
        $password->setSize('100%');
        $document->setSize('100%');
        $phone->setSize('100%');
        $address->setSize('100%');
        $number->setSize('50%');
        $complement->setSize('100%');
        $neighborhood->setSize('100%');
        $postal->setSize('100%');
        $city->setSize('100%');
        $state->setSize('100%');
        $country_id->setSize('100%');

        $id->setEditable(FALSE);

        $this->form->addFields( [new TLabel('ID')], [$id], [new TLabel(_t('Name'))], [$name] );
        $this->form->addFields( [new TLabel(_t('Email'))], [$email], [new TLabel(_t('Password'))], [$password] );
        $this->form->addFields( [new TLabel(_t('Document'))], [$document], [new TLabel(_t('Phone'))], [$phone] );
        $this->form->addFields( [new TLabel(_t('Address'))], [$address], [new TLabel(_t('Number'))], [$number] );
        $this->form->addFields( [new TLabel(_t('Complement'))], [$complement], [new TLabel(_t('Neighborhood'))], [$neighborhood] );
        $this->form->addFields( [new TLabel(_t('Postal'))], [$postal], [new TLabel(_t('City'))], [$city] );
        $this->form->addFields( [new TLabel(_t('State'))], [$state], [new TLabel(_t('Country'))], [$country_id] );
        $this->form->addFields( [new TLabel(_t('Obs'))], [$obs] );
        
        $this->form->addAction( _t('Save'), new TAction(array($this, 'onSave')), 'far:save');
        
        // add the form to the page
        parent::add($this->form);
    }

    /**
     * method onSave()
     * Executed whenever the user clicks at the save button
     */
    public function onSave()
    {
        try
        {
            // open a transaction with database 'store'
            TTransaction::open('store');
            
            // get the form data into an active record Customer
            $object = $this->form->getData('Customer');
            $password = $object->password;
            
            if (empty($object->password))
            {
                unset($object->password);
            }
            else
            {
                $object->password = password_hash($object->password, PASSWORD_DEFAULT);
            }
            
            // form validation
            $this->form->validate();
            
            // stores the object
            $object->store();
            
            $object->password = $password;
            
            // fill the form with the active record data
            $this->form->setData($object);
            
            // close the transaction
            TTransaction::close();
            
            // shows the success message
            new TMessage('info', TAdiantiCoreTranslator::translate('Record saved'));
            // reload the listing
        }
        catch (Exception $e) // in case of exception
        {
            // shows the exception error message
            new TMessage('error', $e->getMessage());
            
            // undo all pending operations
            TTransaction::rollback();
        }
    }
    
    /**
     * method onEdit()
     * Executed whenever the user clicks at the edit button da datagrid
     */
    public function onEdit($param)
    {
        try
        {
            if (isset($param['key']))
            {
                // get the parameter $key
                $key=$param['key'];
                
                // open a transaction with database 'store'
                TTransaction::open('store');
                
                // instantiates object Customer
                $object = new Customer($key);
                
                $object->password='';
                
                // fill the form with the active record data
                $this->form->setData($object);
                
                // close the transaction
                TTransaction::close();
            }
            else
            {
                $this->form->clear();
            }
        }
        catch (Exception $e) // in case of exception
        {
            // shows the exception error message
            new TMessage('error', $e->getMessage());
            
            // undo all pending operations
            TTransaction::rollback();
        }
    }
}
