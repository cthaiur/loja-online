<?php
/**
 * PessoaList
 *
 * @version    1.0
 * @package    notas
 * @subpackage control
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class PessoaList extends TPage
{
    protected $form;     // registration form
    protected $datagrid; // listing
    protected $pageNavigation;
    protected $formgrid;
    
    use Adianti\base\AdiantiStandardListTrait;
    
    /**
     * Page constructor
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->setDatabase('notas');            // defines the database
        $this->setActiveRecord('Pessoa');   // defines the active record
        $this->setDefaultOrder('id', 'asc');         // defines the default order
        $this->setLimit(10);
        // $this->setCriteria($criteria) // define a standard filter

        $this->addFilterField('id', '=', 'id'); // filterField, operator, formField
        $this->addFilterField('nome_fantasia', 'like', 'nome_fantasia'); // filterField, operator, formField
        $this->addFilterField('fone', 'like', 'fone'); // filterField, operator, formField
        $this->addFilterField('email', 'like', 'email'); // filterField, operator, formField
        $this->addFilterField('cidade_id', '=', 'cidade_id'); // filterField, operator, formField
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_Pessoa');
        $this->form->setFormTitle('Pessoa');
        

        // create the form fields
        $id = new TEntry('id');
        $nome_fantasia = new TEntry('nome_fantasia');
        $fone = new TEntry('fone');
        $email = new TEntry('email');
        $cidade_id = new TDBUniqueSearch('cidade_id', 'notas', 'Cidade', 'id', 'nome');
        $cidade_id->setMask('{nome} / {estado->uf}');

        // add the fields
        $this->form->addFields( [ new TLabel('Id') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Nome Fantasia') ], [ $nome_fantasia ] );
        $this->form->addFields( [ new TLabel('Fone') ], [ $fone ] );
        $this->form->addFields( [ new TLabel('Email') ], [ $email ] );
        $this->form->addFields( [ new TLabel('Cidade') ], [ $cidade_id ] );

        // set sizes
        $id->setSize('100%');
        $nome_fantasia->setSize('100%');
        $fone->setSize('100%');
        $email->setSize('100%');

        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue(__CLASS__.'_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        //$this->form->addActionLink(_t('New'), new TAction(['PessoaForm', 'onEdit']), 'fa:plus green');
        
        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        //$this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'left');
        $column_nome_fantasia = new TDataGridColumn('nome_fantasia', 'Nome Fantasia', 'left');
        $column_fone = new TDataGridColumn('fone', 'Fone', 'left');
        $column_email = new TDataGridColumn('email', 'Email', 'left');
        $column_cidade_id = new TDataGridColumn('cidade->nome', 'Cidade', 'left');
        $column_estado_id = new TDataGridColumn('cidade->estado->nome', 'Estado', 'left');
        
        $column_cidade_id->setTransformer( function($value) {
            if (strpos($value, 'propriedade') !== false)
            {
                return '';
            }
            return $value;
        });
        
        $column_estado_id->setTransformer( function($value) {
            if (strpos($value, 'propriedade') !== false)
            {
                return '';
            }
            return $value;
        });
        
        $column_fone->enableAutoHide(500);
        $column_email->enableAutoHide(500);
        $column_cidade_id->enableAutoHide(500);
        $column_estado_id->enableAutoHide(500);
        
        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_nome_fantasia);
        $this->datagrid->addColumn($column_fone);
        $this->datagrid->addColumn($column_email);
        $this->datagrid->addColumn($column_cidade_id);
        $this->datagrid->addColumn($column_estado_id);
        
        $column_id->setAction(new TAction([$this, 'onReload']), ['order' => 'id']);
        $column_nome_fantasia->setAction(new TAction([$this, 'onReload']), ['order' => 'nome_fantasia']);

        
        $action1 = new TDataGridAction(['PessoaFormView', 'onEdit'], ['id'=>'{id}', 'register_state' => 'false']);
        $action2 = new TDataGridAction(['PessoaForm', 'onEdit'], ['id'=>'{id}']);
        //$action3 = new TDataGridAction([$this, 'onDelete'], ['id'=>'{id}', 'register_state' => 'false']);
        
        $this->datagrid->addAction($action1, _t('View'),   'fa:search gray');
        $this->datagrid->addAction($action2, _t('Edit'),   'far:edit blue');
        //$this->datagrid->addAction($action3 ,_t('Delete'), 'far:trash-alt red');
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));
        
        $panel = new TPanelGroup('', 'white');
        $panel->add($this->datagrid);
        $panel->addFooter($this->pageNavigation);
        
        // header actions
        $dropdown = new TDropDown(_t('Export'), 'fa:list');
        $dropdown->setPullSide('right');
        $dropdown->setButtonClass('btn btn-default waves-effect dropdown-toggle');
        $dropdown->addAction( _t('Save as CSV'), new TAction([$this, 'onExportCSV'], ['register_state' => 'false', 'static'=>'1']), 'fa:table blue' );
        $dropdown->addAction( _t('Save as PDF'), new TAction([$this, 'onExportPDF'], ['register_state' => 'false', 'static'=>'1']), 'far:file-pdf red' );
        $panel->addHeaderWidget( $dropdown );
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add($panel);
        
        parent::add($container);
    }
}
