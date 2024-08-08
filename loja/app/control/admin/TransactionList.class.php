<?php
/**
 * OrderList Listing
 * @author  <your name here>
 */
class TransactionList extends TPage
{
    private $form;     // registration form
    private $datagrid; // listing
    private $pageNavigation;
    private $loaded;
    
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
        
        $product_id = new TDBUniqueSearch('product_id', 'store', 'Product', 'id', 'description', 'active desc, description');
        $paymentstatus_id = new TDBUniqueSearch('paymentstatus_id', 'store', 'PaymentStatus', 'id', 'description', 'id');
        $customer = new TEntry('customer');
        
        $this->form = new BootstrapFormBuilder();
        $this->form->setFormTitle(_t('Transactions'));
        
        $row = $this->form->addFields([$l1=new TLabel(_t('Product'))], [$product_id], [$l2=new TLabel(_t('Status'))], [$paymentstatus_id], [$l3=new TLabel(_t('Customer'))], [$customer]);
        $row->layout = [ 'col-sm-1', 'col-sm-3', 'col-sm-1', 'col-sm-3', 'col-sm-1', 'col-sm-3'];
        $l1->setSize(100);
        $l2->setSize(60);
        $l3->setSize(80);
        $btn = $this->form->addAction(_t('Search'), new TAction(array($this, 'onSearch')), 'fa:search');
        $btn->class = 'btn btn-sm btn-success';
        
        $product_id->setValue(TSession::getValue('TransactionList_Product'));
        $product_id->setMask('{description_formatted}');
        $paymentstatus_id->setMask('{description_formatted}');
        $paymentstatus_id->setValue(TSession::getValue('TransactionList_PaymentStatus'));
        $customer->setValue(TSession::getValue('TransactionList_Customer'));
        
        $product_id->setSize('100%');
        $product_id->setMinLength('0');
        $paymentstatus_id->setMinLength('0');
        $paymentstatus_id->setSize('100%');
        $customer->setSize('100%');
        
        // creates a DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->width = '100%';
        $this->datagrid->setHeight(320);
        $this->datagrid->disableHtmlConversion();
        $this->datagrid->enablePopover('Obs', '<b>Obs cliente</b>: {customer->obs} <br> <b>Obs pedido</b>: {obs} ', 'auto', function($obj) { return !empty($obj->obs) || !empty($obj->customer->obs); });
        
        // creates the datagrid columns
        $id               = new TDataGridColumn('id', 'id', 'center', 50);
        $operation_date   = new TDataGridColumn('operation_date', _t('Date'), 'center', 90 );
        $quantity         = new TDataGridColumn('quantity', _t('Qty'), 'center' );
        $total            = new TDataGridColumn('total', _t('Total'), 'right' );
        $product_id       = new TDataGridColumn('<span style="font-weight:bold;color:#6E6E6E">{customer_name}</span><br>{product_name}', _t('Product'), 'left' );
        $customer_name    = new TDataGridColumn('customer_name', _t('Customer'), 'left' );
        $paymentstatus_id = new TDataGridColumn('paymentstatus_info', _t('Status'), 'left' );
        
        $id->setTransformer( function($value, $object) {
            if (!empty($object->obs) || !empty($object->customer->obs))
            {
                return $value . '<br><i class="fa fa-info-circle blue"></i>';
            }
            return $value;
        });
        
        $id->setProperty('hiddable', 500);
        $id->setDataProperty('hiddable', 500);
        $quantity->setProperty('hiddable', 900);
        $quantity->setDataProperty('hiddable', 900);
        $total->setProperty('hiddable', 900);
        $total->setDataProperty('hiddable', 900);
        
        $total->setTransformer( function($value) { return number_format($value,2, ',', '.'); } );
        
        // add the columns to the DataGrid
        $this->datagrid->addColumn($id);
        $this->datagrid->addColumn($operation_date);
        $this->datagrid->addColumn($quantity);
        $this->datagrid->addColumn($total);
        $this->datagrid->addColumn($product_id);
        $this->datagrid->addColumn($paymentstatus_id);

        
        // creates two datagrid actions
        $action1 = new TDataGridAction(array('OrderView', 'onLoad'));
        $action1->setLabel(_t('View'));
        $action1->setImage('fa:search');
        $action1->setField('id');
        
        // creates two datagrid actions
        $action2 = new TDataGridAction(array('TransactionForm', 'onLoad'));
        $action2->setLabel(_t('Edit'));
        $action2->setImage('far:edit blue');
        $action2->setField('id');
        
        // add the actions to the datagrid
        $this->datagrid->addAction($action1);
        $this->datagrid->addAction($action2);
        
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
     * method onSearch()
     */
    public function onSearch()
    {
        // get the search form data
        $data = $this->form->getData();
        
        $session_filters = TSession::getValue('TransactionListFilters');
        
        if ($data->product_id)
        {
            $filter = new TFilter('product_id', '=', $data->product_id);
            $session_filters['product'] = $filter;
            TSession::setValue('TransactionList_Product', $data->product_id);
        }
        else
        {
            unset($session_filters['product']);
            TSession::setValue('TransactionList_Product', $data->product_id);
        }
        
        if ($data->paymentstatus_id)
        {
            $filter = new TFilter('paymentstatus_id', '=', $data->paymentstatus_id);
            $session_filters['status'] = $filter;
            TSession::setValue('TransactionList_PaymentStatus', $data->paymentstatus_id);
        }
        else
        {
            unset($session_filters['status']);
            TSession::setValue('TransactionList_PaymentStatus', $data->paymentstatus_id);
        }
        
        if ($data->customer)
        {
            $filter1 = new TFilter('customer_id', 'in', "(SELECT id from eco_customer WHERE name like '%{$data->customer}%')");
            $filter2 = new TFilter('customer_id', 'in', "(SELECT id from eco_customer WHERE email like '%{$data->customer}%')");
            
            $subcriteria = new TCriteria;
            $subcriteria->add($filter1, TExpression::OR_OPERATOR);
            $subcriteria->add($filter2, TExpression::OR_OPERATOR);
            $session_filters['customer'] = $subcriteria;
            TSession::setValue('TransactionList_Customer', $data->customer);
        }
        else
        {
            unset($session_filters['customer']);
            TSession::setValue('TransactionList_Customer', $data->customer);
        }
        
        $this->form->setData($data);
        TSession::setValue('TransactionListFilters', $session_filters);
        
        $param=array();
        $param['offset']    =0;
        $param['first_page']=1;
        $this->onReload($param);
    }
    
    /**
     * method onReload()
     * Load the datagrid with the database objects
     */
    public function onReload($param = NULL)
    {
        try
        {
            // open a transaction with database 'store'
            TTransaction::open('store');
            
            // creates a repository for Transaction
            $repository = new TRepository('Transaction');
            $limit = 20;
            // creates a criteria
            $criteria = new TCriteria;
            $criteria->setProperties($param); // order, offset
            $criteria->setProperty('order', 'id');
            $criteria->setProperty('direction', 'desc');
            $criteria->setProperty('limit', $limit);
            
            $session_filters = TSession::getValue('TransactionListFilters');
            if ($session_filters)
            {
                foreach($session_filters as $filter)
                {
                    $criteria->add($filter);
                }
            }
            
            // load the objects according to criteria
            $objects = $repository->load($criteria);
            
            $this->datagrid->clear();
            if ($objects)
            {
                // iterate the collection of active records
                foreach ($objects as $object)
                {
                    // add the object inside the datagrid
                    $this->datagrid->addItem($object);
                }
            }
            
            // reset the criteria for record count
            $criteria->resetProperties();
            $count= $repository->count($criteria);
            $this->pageNavigation->enableCounters();
            $this->pageNavigation->setCount($count); // count of records
            $this->pageNavigation->setProperties($param); // order, page
            $this->pageNavigation->setLimit($limit); // limit
            
            // close the transaction
            TTransaction::close();
            $this->loaded = true;
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
     * method show()
     * Shows the page
     */
    public function show()
    {
        // check if the datagrid is already loaded
        if (!$this->loaded)
        {
            $this->onReload();
        }
        parent::show();
    }
}
