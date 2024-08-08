<?php
/**
 * PaymentStatusFormList Registration
 * @author  <your name here>
 */
class PaymentStatusFormList extends TPage
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
        $this->setActiveRecord('PaymentStatus');
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_PaymentStatus');
        $this->form->setFormTitle(_t('Payment Status'));
        
        $yesno = array('Y' => _t('Yes'), 'N' => _t('No'));

        // create the form fields
        $id           = new TEntry('id');
        $description  = new TEntry('description');
        $isfinal      = new TCombo('isfinal');
        $color        = new TColor('color');
        
        $id->setEditable(FALSE);
        $isfinal->addItems($yesno);

        // add the fields
        $this->form->addFields( [new TLabel('ID')], [$id], [new TLabel(_t('Description'))], [$description] );
        $this->form->addFields( [new TLabel(_t('Final?'))], [$isfinal], [new TLabel(_t('Color'))], [$color] );

        $id->setSize('30%');
        $description->setSize('100%');
        $isfinal->setSize('50%');
        $color->setSize('70%');
        
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
        $this->datagrid->addQuickColumn(_t('Description'), 'description', 'left');
        $this->datagrid->addQuickColumn(_t('Final?'), 'isfinal', 'left');
        $this->datagrid->addQuickColumn(_t('Color'), 'color', 'left');

        
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
