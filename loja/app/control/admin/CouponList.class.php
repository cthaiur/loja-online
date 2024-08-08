<?php
/**
 * CouponList Listing
 * @author  <your name here>
 */
class CouponList extends TPage
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
        $this->setActiveRecord('Coupon');
        $this->addFilterField('email', 'like');
        
        // create the form fields
        $filter = new TEntry('email');
        $filter->setValue(TSession::getValue('Coupon_email'));
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_Coupon');
        $this->form->setFormTitle(_t('Coupon'));
        
        $this->form->addFields( [new TLabel(_t('Email'))], [$filter] );
        $btn=$this->form->addAction( _t('Find'), new TAction(array($this, 'onSearch')), 'fa:search');
        $btn->class = 'btn btn-sm btn-success';
        $this->form->addAction( _t('New'), new TAction(array('CouponForm', 'onEdit')), 'fa:plus-square green');
        
        $filter->setSize('100%');
        
        // creates a DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TQuickGrid);
        $this->datagrid->width = '100%';
        $this->datagrid->setHeight(320);
        
        // creates the datagrid columns
        $this->datagrid->addQuickColumn('ID', 'id', 'right', 50, new TAction(array($this, 'onReload')), array('order', 'id'));
        $this->datagrid->addQuickColumn(_t('Email'), 'email', 'left', NULL);
        $this->datagrid->addQuickColumn(_t('Product'), 'product_name', 'left', NULL);
        $this->datagrid->addQuickColumn(_t('Expiration'), 'expiration', 'left', NULL);
        $this->datagrid->addQuickColumn(_t('Discount'), 'discount', 'center', NULL);
        $c_used = $this->datagrid->addQuickColumn(_t('Used'), 'used', 'center', NULL);
        
        $c_used->setTransformer(function($value) {
            $div = new TElement('span');
            $div->class="badge badge-" . ($value == 'Y' ? 'success' : 'danger');
            $div->style="text-shadow:none; font-size:12px";
            $div->add($value == 'Y' ? _t('Yes') : _t('No'));
            return $div;
        });
        
        // add the actions to the datagrid
        $this->datagrid->addQuickAction(_t('Edit'), new TDataGridAction(array('CouponForm', 'onEdit')), 'id', 'far:edit blue');
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
