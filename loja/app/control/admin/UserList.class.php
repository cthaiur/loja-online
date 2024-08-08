<?php
/**
 * UserList Listing
 * @author  <your name here>
 */
class UserList extends TPage
{
    protected $form;     // registration form
    protected $datagrid; // listing
    protected $pageNavigation;
    
    // trait with onReload, onSearch, onDelete...
    use Adianti\Base\AdiantiStandardListTrait;
    
    /**
     * Class constructor
     * Creates the page, the form and the listing
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
        
        $this->setDatabase('store');
        $this->setActiveRecord('User');
        $this->addFilterField('name', 'like');
        
        // create the form fields
        $filter = new TEntry('name');
        $filter->setValue(TSession::getValue('User_name'));
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_User');
        $this->form->setFormTitle(_t('User'));
        
        $this->form->addFields( [new TLabel(_t('Name'))], [$filter] );
        $btn=$this->form->addAction( _t('Find'), new TAction(array($this, 'onSearch')), 'fa:search');
        $btn->class = 'btn btn-sm btn-success';
        $this->form->addAction( _t('New'), new TAction(array('UserForm', 'onEdit')), 'fa:plus-square green');
        
        $filter->setSize('100%');
        
        // creates a DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TQuickGrid);
        $this->datagrid->width = '100%';
        $this->datagrid->setHeight(320);
        

        // creates the datagrid columns
        $this->datagrid->addQuickColumn('ID', 'id', 'right', 50, new TAction(array($this, 'onReload')), array('order', 'id'));
        $this->datagrid->addQuickColumn(_t('Name'), 'name', 'left', NULL, new TAction(array($this, 'onReload')), array('order', 'name'));
        $this->datagrid->addQuickColumn(_t('Email'), 'email', 'left', NULL);
        $role = $this->datagrid->addQuickColumn(_t('Role'), 'role', 'left', NULL);
        $role->setProperty('hiddable', 500);
        $role->setDataProperty('hiddable', 500);
        
        // add the actions to the datagrid
        $this->datagrid->addQuickAction(_t('Edit'), new TDataGridAction(array('UserForm', 'onEdit')), 'id', 'far:edit blue');
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
