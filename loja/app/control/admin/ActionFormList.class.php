<?php
/**
 * PaymentTypeFormList Registration
 * @author  <your name here>
 */
class ActionFormList extends TPage
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
    function __construct()
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
        
        // defines the database
        $this->setDatabase('store');
        
        // defines the active record
        $this->setActiveRecord('Action');
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_PaymentType');
        $this->form->setFormTitle(_t('Actions'));
        
        // create the form fields
        $id            = new TEntry('id');
        $name          = new TEntry('name');
        $remote_method = new TEntry('remote_method');
        
        $id->setEditable(FALSE);

        // add the fields
        $this->form->addFields( [new TLabel('ID')], [$id] );
        $this->form->addFields( [new TLabel('Name')], [$name] );
        $this->form->addFields( [new TLabel('Remote Method')], [$remote_method] );
        
        $id->setSize('50%');
        $name->setSize('70%');
        $remote_method->setSize('70%');
        
        // define the form action
        $btn=$this->form->addAction(_t('Save'), new TAction(array($this, 'onSave')), 'far:save');
        $btn->class = 'btn btn-sm btn-success';
        $this->form->addAction(_t('Clear'), new TAction(array($this, 'onEdit')), 'fa:eraser red');

        
        // creates a DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TQuickGrid);
        $this->datagrid->style='width: 100%';
        $this->datagrid->setHeight(320);
        

        // creates the datagrid columns
        $this->datagrid->addQuickColumn('ID', 'id', 'left', 50);
        $this->datagrid->addQuickColumn(_t('Name'), 'name', 'left');
        $method = $this->datagrid->addQuickColumn(_t('Method'), 'remote_method', 'left');
        $method->setProperty('hiddable', 500);
        $method->setDataProperty('hiddable', 500);
        
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
}
