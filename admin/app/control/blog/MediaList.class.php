<?php
/**
 * MediaList Listing
 * @author  <your name here>
 */
class MediaList extends TPage
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
        $this->setActiveRecord('Media'); // defines the active record
        $this->setDefaultOrder('id', 'desc');
        
        $this->addFilterField('name', 'like', 'name'); // filterField, operator, formField
        $this->addFilterField('type', '=', 'type'); // filterField, operator, formField
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_Media');
        $this->form->setFormTitle(_t('Medias'));
        
        // create the form fields
        $name = new TEntry('name');
        $type = new TCombo('type');
        
        $type->addItems(['youtube' => 'YouTube', 'vimeo' => 'Vimeo', 'slideshare' => 'Slide share', 'flickr' => 'Flickr']);
        
        $this->form->addFields( [new TLabel(_t('Name'))], [$name]);
        $this->form->addFields( [new TLabel(_t('Type'))], [$type]);
        
        $this->form->setData( TSession::getValue('MediaList_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addAction(_t('Find'), new TAction(array($this, 'onSearch')), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addAction(_t('New'),  new TAction(array('MediaForm', 'onEdit')), 'bs:plus-sign green');
        
        // creates a DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TQuickGrid);
        $this->datagrid->style = "width: 100%";
        
        // creates the datagrid columns
        $this->datagrid->addColumn( new TDataGridColumn('id', 'ID', 'center', '10%') );
        $this->datagrid->addColumn( new TDataGridColumn('name', _t('Name'), 'left', '50%') );
        $this->datagrid->addColumn( new TDataGridColumn('type', _t('Type'), 'center', '40%') );
        
        // add the actions to the datagrid
        $action_edit   = new TDataGridAction(['MediaForm', 'onEdit'],   ['key' => '{id}'] );
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
