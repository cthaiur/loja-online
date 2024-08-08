<?php
/**
 * PaymentTypeFormList Registration
 * @author  <your name here>
 */
class PaymentTypeFormList extends TPage
{
    protected $form; // form
    protected $datagrid; // datagrid
    protected $pageNavigation;
    
    // trait with onSave, onEdit, onDelete, onReload, onSearch...
    use Adianti\Base\AdiantiStandardFormListTrait;
    
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    public function __construct()
    {
        parent::__construct();
        $this->setDefaultOrder('id', 'asc');
        
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
        
        // defines the database
        $this->setDatabase('store');
        
        // defines the active record
        $this->setActiveRecord('PaymentType');
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_PaymentType');
        $this->form->setFormTitle(_t('Payment Type'));
        
        // create the form fields
        $id           = new TEntry('id');
        $description  = new TEntry('description');
        $information  = new TEntry('information');
        $languages    = new TCheckGroup('languages');
        $icon         = new TEntry('icon');
        $url          = new TEntry('url');
        
        $id->setEditable(FALSE);
        $items = array('pt'=>'Português', 'en'=>'Inglês');
        $languages->addItems($items);
        $languages->setLayout('horizontal');

        // add the fields
        $this->form->addFields( [new TLabel('ID')], [$id], [new TLabel(_t('Languages'))], [$languages] );
        $this->form->addFields( [new TLabel(_t('Description'))], [$description] );
        $this->form->addFields( [new TLabel(_t('Information'))], [$information] );
        $this->form->addFields( [new TLabel(_t('Icon'))], [$icon], [new TLabel('URL')], [$url] );
        
        $id->setSize('50%');
        $description->setSize('100%');
        $icon->setSize('100%');
        $url->setSize('100%');
        
        $languages->setUseButton();
        
        // define the form action
        $btn=$this->form->addAction(_t('Save'), new TAction(array($this, 'onSave')), 'far:save');
        $btn->class = 'btn btn-sm btn-success';
        $this->form->addAction(_t('Clear'), new TAction(array($this, 'onEdit')), 'fa:eraser red');

        
        // creates a DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TQuickGrid);
        $this->datagrid->style='width: 100%';
        $this->datagrid->setHeight(320);
        

        // creates the datagrid columns
        $this->datagrid->addQuickColumn('ID', 'id', 'left', '10%');
        $this->datagrid->addQuickColumn(_t('Description'), 'description', 'left', '90%');

        
        // add the actions to the datagrid
        $this->datagrid->addQuickAction(_t('Edit'), new TDataGridAction(array($this, 'onEdit')), 'id', 'far:edit blue');
        $this->datagrid->addQuickAction(_t('Delete'), new TDataGridAction(array($this, 'onDelete')), 'id', 'far:trash-alt red');
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
        
        
        $vbox = TVBox::pack($this->form, TPanelGroup::pack('', $this->datagrid), $this->pageNavigation);
        $vbox->style = 'width: 100%';
        parent::add($vbox);
    }
    
    /**
     * method onSave()
     * Executed whenever the user clicks at the save button
     */
    public function onSave()
    {
        try
        {
            TTransaction::open($this->database);
            $object = $this->form->getData($this->activeRecord);
            $this->form->validate();
            
            $languages    = $object->languages;
            
            $object->languages    = implode(',', $languages);
            $object->store();
            $object->languages    = $languages;
            
            $this->form->setData($object);
            TTransaction::close();
            new TMessage('info', TAdiantiCoreTranslator::translate('Record saved'));
            $this->onReload();
        }
        catch (Exception $e) // in case of exception
        {
            $object = $this->form->getData($this->activeRecord);
            $this->form->setData($object);
            new TMessage('error', $e->getMessage());
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
                $key=$param['key'];
                TTransaction::open($this->database);
                $class = $this->activeRecord;
                
                $object = new $class($key);
                $object->languages    = explode(',', $object->languages);
                
                $this->form->setData($object);
                TTransaction::close();
                $this->onReload();
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
