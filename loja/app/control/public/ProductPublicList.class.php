<?php
/**
 * ProductList Listing
 * @author  <your name here>
 */
class ProductPublicList extends TPage
{
    protected $datagrid; // listing
    protected $pageNavigation;
    protected $htmlRender;
    
    /**
     * Class constructor
     * Creates the page, the form and the listing
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->htmlRender = new THtmlRenderer('app/resources/products_list.html');
        $this->htmlRender->enableTranslation();
        $this->htmlRender->enableSection('main');
        
        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
        $this->pageNavigation->setWidth(400);
        
        // creates the page structure using a table
        $container = new TVBox;
        $container->style = 'width:100%';
        $container->add($this->htmlRender);
        $container->add($this->pageNavigation);
        // add the table inside the page
        parent::add($container);
    }
    
    /**
     * method onReload()
     * Load the datagrid with the database objects
     */
    public function onReload($param = NULL)
    {
        try
        {
            // open a transaction with database
            TTransaction::open('store');
            
            // instancia um repositório
            $repository = new TRepository('Product');
            $limit = isset($this->limit) ? $this->limit : 10;
            
            // creates a criteria
            $criteria = new TCriteria;
            $criteria->setProperties($param); // order, offset
            $criteria->setProperty('limit', $limit);
            $criteria->setProperty('order', 'price');
            $criteria->add(new TFilter('languages', 'like', '%'.TSession::getValue('language').'%'));
            $criteria->add(new TFilter('active', '=', 'Y'));
            
            // load the objects according to criteria
            $objects = $repository->load($criteria);
            
            $products = [];
            if ($objects)
            {
                // iterate the collection of active records
                foreach ($objects as $object)
                {
                    $product_array = $object->toArray();
                    
                    $product_array['price']      = number_format($product_array['price'], 2);
                    $product_array['price_part'] = number_format( ($product_array['price'] * 1.204808743) / 12, 2, ',', '.');
                    $product_array['item_style'] = isset($param['key']) && $param['key'] == $object->id ? 'border:14px solid #6421a3' : '';
                    $product_array['buyurl'] = "buy-product?key=".$object->id;
                    $product_array['target'] = "";
                    
                    $products[] = $product_array;
                }
                
            }
            $this->htmlRender->enableSection('products', $products, TRUE);
            
            // reset the criteria for record count
            $criteria->resetProperties();
            $count= $repository->count($criteria);
            
            if (isset($this->pageNavigation))
            {
                $this->pageNavigation->setCount($count); // count of records
                $this->pageNavigation->setProperties($param); // order, page
                $this->pageNavigation->setLimit($limit); // limit
            }
            
            // close the transaction
            TTransaction::close();
            $this->loaded = true;
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
    
    /**
     * method show()
     * Shows the page
     */
    public function show()
    {
        $this->onReload( (func_num_args() > 0) ? func_get_arg(0) : null);
        parent::show();
    }
}
