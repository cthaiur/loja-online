<?php
/**
 * UserForm Registration
 * @author  <your name here>
 */
class UserForm extends TPage
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
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_User');
        $this->form->setFormTitle(_t('User'));
        
        // create the form fields
        $id             = new TEntry('id');
        $name           = new TEntry('name');
        $email          = new TEntry('email');
        $password       = new TPassword('password');
        $role           = new TCombo('role');

        $id->setEditable(FALSE);
        $items=array('ADMINISTRATOR'=>_t('Administrator'));
        $role->addItems($items);
        
        // add the fields
        $this->form->addFields( [new TLabel('ID')], [$id] );
        $this->form->addFields( [new TLabel(_t('Name'))], [$name] );
        $this->form->addFields( [new TLabel(_t('Email'))], [$email] );
        $this->form->addFields( [new TLabel(_t('Password'))], [$password] );
        $this->form->addFields( [new TLabel(_t('Role'))], [$role] );
        
        $id->setSize('30%');
        $name->setSize('70%');
        $email->setSize('70%');
        $password->setSize('70%');
        $role->setSize('70%');
        
        $this->form->addAction( _t('Save'), new TAction(array($this, 'onSave')), 'far:save');
        $this->form->addAction( _t('Clear'), new TAction(array($this, 'onEdit')), 'fa:eraser red');
        $this->form->addAction( _t('Back to the listing'), new TAction(array('UserList', 'onReload')), 'fa:table blue');
        
        // validations
        $name->addValidation(_t('Name'), new TRequiredValidator);
        $email->addValidation(_t('Email'), new TRequiredValidator);
        $role->addValidation(_t('Role'), new TRequiredValidator);

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
            // open a transaction with database
            TTransaction::open('store');
            
            $object = $this->form->getData('User');
            $password = $object->password;
            
            if (empty($object->password))
            {
                unset($object->password);
            }
            else
            {
                $object->password = password_hash($object->password, PASSWORD_DEFAULT);
            }
            
            $this->form->validate();
            
            $object->store();
            $object->password = $password;
            $this->form->setData($object);
            TTransaction::close();
            new TMessage('info', TAdiantiCoreTranslator::translate('Record saved'));
        }
        catch (Exception $e) // in case of exception
        {
            $object = $this->form->getData('User');
            $this->form->setData($object);
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
                $class = 'User';
                $object = new $class($key);
                $object->password='';
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
