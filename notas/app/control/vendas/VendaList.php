<?php
/**
 * VendaList
 *
 * @version    1.0
 * @package    notas
 * @subpackage control
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class VendaList extends TPage
{
    protected $form;     // registration form
    protected $datagrid; // listing
    protected $pageNavigation;
    protected $formgrid;
    protected $deleteButton;
    
    use Adianti\base\AdiantiStandardListTrait;
    
    /**
     * Page constructor
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->setDatabase('notas');            // defines the database
        $this->setActiveRecord('Venda');   // defines the active record
        $this->setDefaultOrder('id', 'desc');         // defines the default order
        $this->setLimit(10);
        // $this->setCriteria($criteria) // define a standard filter

        $this->addFilterField('id', '=', 'id'); // filterField, operator, formField
        $this->addFilterField('cliente_id', 'like', 'cliente_id'); // filterField, operator, formField
        $this->addFilterField('faturada', '=', 'faturada'); // filterField, operator, formField
        $this->addFilterField('cancelada', '=', 'cancelada'); // filterField, operator, formField
        $this->setOrderCommand('cliente->nome_fantasia', '(SELECT nome_fantasia FROM pessoa WHERE pessoa.id=venda.cliente_id)');
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_Venda');
        $this->form->setFormTitle('Venda');
        

        // create the form fields
        $id = new TEntry('id');
        $cliente_id = new TDBUniqueSearch('cliente_id', 'notas', 'Pessoa', 'id', 'nome_fantasia');
        $faturada  = new TRadioGroup('faturada');
        $cancelada = new TRadioGroup('cancelada');
        
        $cliente_id->setMinLength(0);
        
        $faturada->addItems( ['Y' => 'Sim', 'N' => 'Não', '' => 'Ambos'] );
        $cancelada->addItems( ['Y' => 'Sim', 'N' => 'Não', '' => 'Ambos'] );
        $faturada->setLayout('horizontal');
        $cancelada->setLayout('horizontal');
        
        // add the fields
        $this->form->addFields( [ new TLabel('Id') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Cliente') ], [ $cliente_id ] );
        $this->form->addFields( [ new TLabel('Faturada') ], [ $faturada ] );
        $this->form->addFields( [ new TLabel('Cancelada') ], [ $cancelada ] );


        // set sizes
        $id->setSize('100%');
        $cliente_id->setSize('100%');
        $faturada->setSize('100%');
        $cancelada->setSize('100%');

        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue(__CLASS__.'_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        
        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->disableHtmlConversion();
        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'center');
        $column_cliente_id = new TDataGridColumn('cliente->nome_fantasia', 'Cliente', 'left');
        $column_dt_venda = new TDataGridColumn('dt_venda', 'Dt Venda', 'center');
        $column_itens = new TDataGridColumn('itens_string', 'Itens', 'left');
        $column_total = new TDataGridColumn('total', 'Total', 'right');
        $column_status = new TDataGridColumn('status', 'Status', 'center');
        
        
        $column_dt_venda->enableAutoHide(500);
        $column_total->enableAutoHide(500);
        $column_status->enableAutoHide(500);
        
        $column_dt_venda->setTransformer( function($value) {
            return TDate::convertToMask($value, 'yyyy-mm-dd', 'dd/mm/yyyy');
        });
        
        $column_total->setTransformer( function($value) {
            if (is_numeric($value)) {
                return 'R$&nbsp;'.number_format($value, 2, ',', '.');
            }
            return $value;
        });
        
        $column_status->setTransformer( function($value, $object) {
            if ($object->cancelada == 'Y')
            {
                $value = 'Cancelada';
                $label = 'danger';
            }
            else if ($object->faturada == 'Y')
            {
                $value = 'Faturada';
                $label = 'success';
            }
            else
            {
                $value = 'Aguardando';
                $label = 'warning';
            }
            
            $div = new TElement('span');
            $div->class="label label-" . $label;
            $div->style="text-shadow:none; font-size:12px";
            $div->add( $value );
            return $div;
        });
        
        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_cliente_id);
        $this->datagrid->addColumn($column_dt_venda);
        $this->datagrid->addColumn($column_itens);
        $this->datagrid->addColumn($column_status);
        $this->datagrid->addColumn($column_total);


        // creates the datagrid column actions
        $column_id->setAction(new TAction([$this, 'onReload']), ['order' => 'id']);
        $column_cliente_id->setAction(new TAction([$this, 'onReload']), ['order' => 'cliente->nome_fantasia']);
        $column_dt_venda->setAction(new TAction([$this, 'onReload']), ['order' => 'dt_venda']);

        
        $action1 = new TDataGridAction(['VendaFormView', 'onEdit'], ['id'=>'{id}', 'register_state' => 'false']);
        $action2 = new TDataGridAction(['VendaForm', 'onEdit'], ['id'=>'{id}', 'register_state' => 'false']);
        $action3 = new TDataGridAction([$this, 'onCancel'], ['id'=>'{id}']);
        $action4 = new TDataGridAction([$this, 'onGeraNota'], ['id'=>'{id}']);
        
        $action2->setDisplayCondition( [$this, 'onDisplayGera'] );
        $action3->setDisplayCondition( [$this, 'onDisplayGera'] );
        $action4->setDisplayCondition( [$this, 'onDisplayGera'] );
        
        $this->datagrid->addAction($action1, _t('View'),   'fa:search gray');
        
        if (TSession::getValue('login') == 'admin')
        {
            $this->datagrid->addAction($action2, _t('Edit'),   'far:edit blue');
        }
        $this->datagrid->addAction($action3 ,_t('Cancel'), 'fa:power-off red');
        $this->datagrid->addAction($action4 ,'Gera nota', 'fa:file-import green');
        
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
    
    /**
     *
     */
    public function onGeraNota($param)
    {
        // define the delete action
        $action = new TAction(array($this, 'geraNota'));
        $action->setParameters($param); // pass the key parameter ahead
        
        // shows a dialog to the user
        new TQuestion('Gerar Nota Fiscal ?', $action);
    }
    
    public function onDisplayGera($object)
    {
        return $object->faturada !== 'Y';
    }
    
    public function geraNota($param)
    {
        try
        {
            TTransaction::open('notas');
            $venda = new Venda($param['id']);
            
            $nota = NotaFiscal::fromVenda($venda);
            
            $venda->faturada = 'Y';
            $venda->nota_fiscal_id = $nota->id;
            $venda->store();
            
            TTransaction::close();
            
            new TMessage('info', 'Nota criada');
            $this->onReload($param);
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }
    
    /**
     * Cancela venda
     */
    public function onCancel($param)
    {
        try
        {
            TTransaction::open('notas');
            $venda = new Venda($param['id']);
            
            if ($venda->faturada == 'Y')
            {
                return;
            }
            
            if ($venda->cancelada !== 'Y')
            {
                $venda->cancelada = 'Y';
                $venda->store();
                
                new TMessage('info', 'Venda cancelada');
            }
            else
            {
                $venda->cancelada = 'N';
                $venda->store();
                
                new TMessage('info', 'Venda descancelada');
            }
            
            TTransaction::close();
            
            $this->onReload($param);
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }
    
    public function Delete($param)
    {
        new TMessage('error', 'Operação não permitida');
    }
}
