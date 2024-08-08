<?php
/**
 * PostList Listing
 * @author  <your name here>
 */
class PostList extends TPage
{
    protected $form;     // registration form
    protected $datagrid; // listing
    protected $pageNavigation;
    
    use Adianti\Base\AdiantiStandardListTrait;
    
    /**
     * Class constructor
     * Creates the page, the form and the listing
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->setDatabase('blog'); // defines the database
        $this->setActiveRecord('Post'); // defines the active record
        $this->setDefaultOrder('id', 'desc');
        
        $this->addFilterField('title', 'like', 'title'); // filterField, operator, formField
        $this->addFilterField('category_id', '=', 'category_id'); // filterField, operator, formField
        
        if (TSession::getValue('login') !== 'admin')
        {
            $criteria = TCriteria::create( ['login' => TSession::getValue('login') ] );
            $this->setCriteria($criteria);
        }
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_Post');
        $this->form->setFormTitle(_t('Posts'));
        
        // create the form fields
        $title = new TEntry('title');
        $category_id = new TDBCombo('category_id', 'blog', 'Category', 'id', 'name');
        
        $this->form->addFields( [new TLabel(_t('Title'))], [$title]);
        $this->form->addFields( [new TLabel(_t('Category'))], [$category_id]);
        
        $this->form->setData( TSession::getValue('PostList_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addAction(_t('Find'), new TAction(array($this, 'onSearch')), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addAction(_t('New'),  new TAction(array('PostForm', 'onClear')), 'bs:plus-sign green');
        
        // creates a DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TQuickGrid);
        $this->datagrid->style = "width: 100%";
        
        // creates the datagrid columns
        $this->datagrid->addColumn( new TDataGridColumn('id', 'ID', 'center', '10%') );
        $this->datagrid->addColumn( new TDataGridColumn('title', _t('Title'), 'left', '40%') );
        $this->datagrid->addColumn( new TDataGridColumn('user_name', _t('User'), 'center', '10%') );
        $this->datagrid->addColumn( new TDataGridColumn('category_name', _t('Category'), 'center', '20%') );
        $column_date = $this->datagrid->addColumn( new TDataGridColumn('post_date', _t('Date'), 'center', '20%') );
        
        $column_date->setTransformer(function($value)
        {
            if ($value)
            {
                try
                {
                    $date = new DateTime($value);
                    return $date->format('d/m/Y');
                }
                catch (Exception $e)
                {
                    return $value;
                }
            }
            return $value;
        });
        
        // add the actions to the datagrid
        $action_edit   = new TDataGridAction(['PostForm', 'onEdit'],   ['key' => '{id}'] );
        $action_delete = new TDataGridAction([$this, 'onDelete'],   ['key' => '{id}'] );
        
        $this->datagrid->addAction($action_edit, 'Edit',   'far:edit blue fa-fw');
        $this->datagrid->addAction($action_delete, 'Delete', 'far:trash-alt red fa-fw');

        // create the datagrid model
        $this->datagrid->createModel();
        
        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
        
        $panel = new TPanelGroup;
        $panel->add($this->datagrid);
        $panel->addFooter($this->pageNavigation);
        
        // creates the page container
        $vbox = new TVBox;
        $vbox->style = "width: 100%";
        $vbox->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $vbox->add($this->form);
        $vbox->add($panel);

        // add the container inside the page
        parent::add($vbox);
    }
}
