<?php
/**
 * CustomerList Listing
 * @author  <your name here>
 */
class CustomerList extends TPage
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
        $this->setActiveRecord('Customer');
        $this->addFilterField('name', 'like');
        $this->addFilterField('email', 'like');
        $this->setDefaultOrder('id', 'desc');
        $this->setLimit(20);
        
        $name  = new TEntry('name');
        $email = new TEntry('email');
        $name->setValue(TSession::getValue('Customer_name'));
        $email->setValue(TSession::getValue('Customer_email'));
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_Customer');
        $this->form->setFormTitle(_t('Customer'));
        
        $this->form->addFields( [_t('Name')], [$name] );
        $this->form->addFields( [_t('Email')], [$email] );
        
        $btn=$this->form->addAction( _t('Find'), new TAction(array($this, 'onSearch')), 'fa:search');
        $btn->class = 'btn btn-sm btn-success';
        $this->form->addAction( _t('New'),  new TAction(array('CustomerForm', 'onEdit')), 'fa:plus-square green');
        $name->setSize('100%');
        $email->setSize('100%');
        
        // creates a DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TQuickGrid);
        $this->datagrid->datatable = 'true';
        $this->datagrid->width = '100%';
        $this->datagrid->disableDefaultClick();
        $this->datagrid->enablePopover('Obs', '{obs}', 'auto', function($obj) { return $obj->obs; });
        
        $column_id = $this->datagrid->addQuickColumn('ID', 'id', 'right', 50, new TAction(array($this, 'onReload')), array('order', 'id'));
        $column_name = $this->datagrid->addQuickColumn(_t('Name'), 'name', 'left', NULL, new TAction(array($this, 'onReload')), array('order', 'name'));
        $column_email = $this->datagrid->addQuickColumn(_t('Email'), 'email', 'left', NULL);
        $column_phone = $this->datagrid->addQuickColumn(_t('Phone'), 'phone', 'left', NULL);
        $created_at = $this->datagrid->addQuickColumn(_t('Registered'), 'created_at', 'center', NULL);
        $column_purchases = $this->datagrid->addQuickColumn(_t('Purchases'), 'active', 'center', NULL);
        
        $column_id->setTransformer( function($value, $object) {
            if (!empty($object->obs))
            {
                return '<div style="display:inline-block;white-space:nowrap">' . $value . '&nbsp;<i class="fa fa-info-circle blue"></i> </div>';
            }
            return $value;
        });
        
        $created_at->setTransformer( function($value) {
            if ($value)
            {
                $date = new DateTime($value);
                return $date->format('d/m/Y H:i');
            }
        });
        
        $column_purchases->setTransformer( function($value, $object, $row) {
            $count = Transaction::where('customer_id','=',$object->id)->where('paymentstatus_id', 'IN', Transaction::APROVEDS)->count();
            $last  = Transaction::where('customer_id','=',$object->id)->last();
            
            if ($count > 0)
            {
                $class = 'success';
                $label = _t('Yes');
            }
            else
            {
                $label = _t('No');
                $class = 'danger';
                
                if ($last)
                {
                    $payment_status = new PaymentStatus($last->paymentstatus_id);
                    $class = $payment_status->description_label_class;
                }
            }
            
            $div = new TElement('a');
            $div->class="badge badge-{$class}";
            $div->style="text-shadow:none; font-size:12px; font-weight:lighter";
            
            if (!empty($last))
            {
                $div->generator = 'adianti';
                $div->href = 'view-order?key='.$last->id;
            }
            $div->add($label);
            return $div;
        });
        
        $column_email->setProperty('hiddable', 500);
        $column_email->setDataProperty('hiddable', 500);
        $column_phone->setProperty('hiddable', 900);
        $column_phone->setDataProperty('hiddable', 900);
        
        $this->datagrid->addQuickAction(_t('Edit'), new TDataGridAction(array('CustomerForm', 'onEdit')), 'id', 'far:edit blue');
        $this->datagrid->addQuickAction(_t('Delete'), new TDataGridAction(array($this, 'onDelete')), 'id', 'far:trash-alt red');
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
        
        // creates the page structure using a table
        $vbox = TVBox::pack($this->form, TPanelGroup::pack('', $this->datagrid), $this->pageNavigation);
        $vbox->style = 'width: 100%';
        parent::add($vbox);
    }
}
