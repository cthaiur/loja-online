<?php
/**
 * ProductList Listing
 * @author  <your name here>
 */
class ProductList extends TPage
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
        $this->setActiveRecord('Product');
        $this->addFilterField('description', 'like');
        $this->setDefaultOrder('id');
        $this->setLimit(100);
        
        $filter = new TEntry('description');
        $filter->setValue(TSession::getValue('Product_description'));
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_Product');
        $this->form->setFormTitle(_t('Product'));
        
        $this->form->addFields( [new TLabel(_t('Description'))], [$filter] );
        $filter->setSize('100%');
        
        $btn=$this->form->addAction( _t('Find'), new TAction(array($this, 'onSearch')), 'fa:search');
        $btn->class = 'btn btn-sm btn-success';
        $this->form->addAction( _t('New'), new TAction(array('ProductForm', 'onEdit')), 'fa:plus-square green');
        
        // creates a DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TQuickGrid);
        $this->datagrid->datatable = 'true';
        $this->datagrid->style = 'width: 100%';
        
        // creates the datagrid columns
        $this->datagrid->addQuickColumn('ID', 'id', 'right', 50, new TAction(array($this, 'onReload')), array('order', 'id'));
        $this->datagrid->addQuickColumn(_t('Description'), 'description', 'left', NULL, new TAction(array($this, 'onReload')), array('order', 'description'));
        $column_active = $this->datagrid->addQuickColumn(_t('Active'), 'active', 'center', NULL);
        $price = $this->datagrid->addQuickColumn(_t('Price'), 'price', 'right', NULL);
        
        $price->setTransformer( function($value, $object, $row) {
            return $object->currency . ' ' . $value;
        });
        
        $column_active->setTransformer( function($value, $object, $row) {
            $class = ($value=='N') ? 'danger' : 'success';
            $label = ($value=='N') ? _t('No') : _t('Yes');
            $div = new TElement('span');
            $div->class="badge badge-{$class}";
            $div->style="text-shadow:none; font-size:12px; font-weight:lighter";
            $div->add($label);
            return $div;
        });
        
        $price->setProperty('hiddable', 500);
        $price->setDataProperty('hiddable', 500);
        
        // add the actions to the datagrid
        $this->datagrid->addQuickAction(_t('Edit'), new TDataGridAction(array('ProductForm', 'onEdit')), 'id', 'far:edit blue');
        $this->datagrid->addQuickAction(_t('Delete'), new TDataGridAction(array($this, 'onDelete')), 'id', 'far:trash-alt red');
        
        // create ONOFF action
        $action_onoff = new TDataGridAction(array($this, 'onTurnOnOff'));
        $action_onoff->setButtonClass('btn btn-default');
        $action_onoff->setLabel(_t('Activate/Deactivate'));
        $action_onoff->setImage('fa:power-off orange');
        $action_onoff->setField('id');
        $this->datagrid->addAction($action_onoff);
        
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
     * Turn on/off an user
     */
    public function onTurnOnOff($param)
    {
        try
        {
            TTransaction::open('store');
            $product = Product::find($param['id']);
            if ($product instanceof Product)
            {
                $product->active = $product->active == 'Y' ? 'N' : 'Y';
                $product->store();
            }
            
            TTransaction::close();
            
            $this->onReload($param);
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
}
