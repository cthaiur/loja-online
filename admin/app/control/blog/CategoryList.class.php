<?php
/**
 * CategoryList Listing
 * @author  <your name here>
 */
class CategoryList extends TPage
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
        $this->setActiveRecord('Category'); // defines the active record
        $this->setDefaultOrder('id', 'desc');
        
        $this->addFilterField('name', 'like', 'name'); // filterField, operator, formField
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_Category');
        $this->form->setFormTitle(_t('Categories'));
        
        // create the form fields
        $name = new TEntry('name');
        
        $this->form->addFields( [new TLabel(_t('Name'))], [$name]);
        
        $this->form->setData( TSession::getValue('CategoryList_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addAction(_t('Find'), new TAction(array($this, 'onSearch')), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addAction(_t('New'),  new TAction(array('CategoryForm', 'onEdit')), 'bs:plus-sign green');
        
        // creates a DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TQuickGrid);
        $this->datagrid->style = "width: 100%";
        
        // creates the datagrid columns
        $this->datagrid->addColumn( new TDataGridColumn('id', 'ID', 'center', '10%') );
        $this->datagrid->addColumn( new TDataGridColumn('name', _t('Name'), 'left', '70%') );
        $this->datagrid->addColumn( new TDataGridColumn('position', _t('Position'), 'center', '10%') );
        $colum_menu = $this->datagrid->addColumn( new TDataGridColumn('show_menu', _t('Menu'), 'center', '10%') );
        
        $action_edit   = new TDataGridAction(['CategoryForm', 'onEdit'],   ['key' => '{id}'] );
        $action_delete = new TDataGridAction([$this, 'onDelete'],   ['key' => '{id}'] );
        
        $this->datagrid->addAction($action_edit, 'Edit',   'far:edit blue fa-fw');
        $this->datagrid->addAction($action_delete, 'Delete', 'far:trash-alt red fa-fw');
        
        $colum_menu->setTransformer(function($value) {
            if ($value=='Y')
            {
                $label = _t('Yes');
                $color = 'green';
            }
            else
            {
                $label = _t('No');
                $color = 'orange';
            }
            
            $div = new TElement('span');
            $div->style = "text-shadow:none; font-size:12px; background: $color; border-radius:4px; padding:3px; color: white;";
            $div->add($label);
            return $div;
        });
        
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
