<?php
/**
 * OrderList Listing
 * @author  <your name here>
 */
class OrderList extends TPage
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
        
        // creates a DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->disableHtmlConversion();
        $this->datagrid->width = '100%';
        
        // creates the datagrid columns
        $id               = new TDataGridColumn('id', 'id', 'right', 50);
        $operation_date   = new TDataGridColumn('operation_date', _t('Date'), 'center', 100);
        $quantity         = new TDataGridColumn('quantity', _t('Qty'), 'center' );
        $total            = new TDataGridColumn('total', _t('Total'), 'right' );
        $product_id       = new TDataGridColumn('product_name', _t('Product'), 'left' );
        $paymentstatus_id = new TDataGridColumn('paymentstatus_info', _t('Status'), 'center' );

        $id->setProperty('hiddable', 500);
        $id->setDataProperty('hiddable', 500);
        $quantity->setProperty('hiddable', 900);
        $quantity->setDataProperty('hiddable', 900);
        $total->setProperty('hiddable', 900);
        $total->setDataProperty('hiddable', 900);
        $operation_date->setTransformer( function($value) {
            return TDate::date2br($value);
        });
        
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
        
        // add the actions to the datagrid
        $this->datagrid->addAction($action1);
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
        
        // creates the page structure using a table
        $vbox = TVBox::pack($this->form, TPanelGroup::pack(_t('My orders'), $this->datagrid), $this->pageNavigation);
        $vbox->style = 'width: 100%';
        parent::add($vbox);
    }
    
    /**
     * method onReload()
     * Load the datagrid with the database objects
     */
    function onReload($param = NULL)
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
            
            $customer = Customer::newFromEmail(TSession::getValue('login'));
            $criteria->add(new TFilter('customer_id' , '=', $customer->id));
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
    function show()
    {
        // check if the datagrid is already loaded
        if (!$this->loaded)
        {
            $this->onReload();
        }
        parent::show();
    }
}
