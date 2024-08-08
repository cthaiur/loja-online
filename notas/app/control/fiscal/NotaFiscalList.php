<?php
/**
 * NotaFiscalList
 *
 * @version    1.0
 * @package    notas
 * @subpackage control
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class NotaFiscalList extends TPage
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
        $this->setActiveRecord('NotaFiscal');   // defines the active record
        $this->setDefaultOrder('id', 'desc');         // defines the default order
        $this->setLimit(10);
        // $this->setCriteria($criteria) // define a standard filter

        $this->addFilterField('id', '=', 'id'); // filterField, operator, formField
        $this->addFilterField('cliente_id', 'like', 'cliente_id'); // filterField, operator, formField
        $this->addFilterField('transmitida', '=', 'transmitida'); // filterField, operator, formField
        $this->addFilterField('cancelada', '=', 'cancelada'); // filterField, operator, formField
        $this->setOrderCommand('cliente->nome_fantasia', '(SELECT nome_fantasia FROM pessoa WHERE pessoa.id=fatura.cliente_id)');
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_NotaFiscal');
        $this->form->setFormTitle('Nota Fiscal');
        

        // create the form fields
        $id = new TEntry('id');
        $cliente_id = new TDBUniqueSearch('cliente_id', 'notas', 'Pessoa', 'id', 'nome_fantasia');
        $transmitida = new TRadioGroup('transmitida');
        $cancelada = new TRadioGroup('cancelada');
        
        $current = (int) date('Y');
        
        $cliente_id->setMinLength(0);
        
        $transmitida->addItems( ['Y' => 'Sim', 'N' => 'Não', '' => 'Ambos'] );
        $cancelada->addItems( ['Y' => 'Sim', 'N' => 'Não', '' => 'Ambos'] );
        $transmitida->setLayout('horizontal');
        $cancelada->setLayout('horizontal');
        
        // add the fields
        $this->form->addFields( [ new TLabel('Id') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Cliente') ], [ $cliente_id ] );
        $this->form->addFields( [ new TLabel('Transmitida') ], [ $transmitida ] );
        $this->form->addFields( [ new TLabel('Cancelada') ], [ $cancelada ] );


        // set sizes
        $id->setSize('100%');
        $cliente_id->setSize('100%');
        $transmitida->setSize('100%');
        $cancelada->setSize('100%');

        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue(__CLASS__.'_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink(_t('New'), new TAction(['NotaFiscalForm', 'onEdit']), 'fa:plus green');
        
        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->disableHtmlConversion();
        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'center');
        $column_cliente_id = new TDataGridColumn('cliente->nome_fantasia', 'Cliente', 'left');
        $column_dt_nota = new TDataGridColumn('dt_nota', 'Data', 'center');
        $column_itens = new TDataGridColumn('itens_string', 'Itens', 'left');
        $column_total = new TDataGridColumn('total', 'Total', 'right');
        $column_status = new TDataGridColumn('status', 'Status', 'center');
        
        $column_dt_nota->enableAutoHide(500);
        $column_total->enableAutoHide(500);
        $column_status->enableAutoHide(500);
        
        $column_id->setTransformer( function ($value, $object, $row) {
            if ($object->cancelada == 'Y')
            {
                $row->style= 'color: silver';
            }
            
            return $value;
        });
        
        $column_dt_nota->setTransformer( function($value) {
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
            else if ($object->emitida == 'Y')
            {
                $value = 'Emitida';
                $label = 'success';
            }
            else if ($object->transmitida == 'Y')
            {
                $value = 'Transmitida';
                $label = 'primary';
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
        $this->datagrid->addColumn($column_dt_nota);
        $this->datagrid->addColumn($column_itens);
        $this->datagrid->addColumn($column_status);
        $this->datagrid->addColumn($column_total);


        // creates the datagrid column actions
        $column_id->setAction(new TAction([$this, 'onReload']), ['order' => 'id']);
        $column_cliente_id->setAction(new TAction([$this, 'onReload']), ['order' => 'cliente->nome_fantasia']);
        $column_dt_nota->setAction(new TAction([$this, 'onReload']), ['order' => 'dt_nota']);
        
        $action_edit     = new TDataGridAction(['NotaFiscalForm', 'onEdit'], ['id'=>'{id}']);
        $action_delete   = new TDataGridAction([$this, 'onDelete'], ['id'=>'{id}']);
        $action_transmit = new TDataGridAction([$this, 'onTransmite'], ['id'=>'{id}']);
        $action_imprime  = new TDataGridAction([$this, 'onImprime'], ['id'=>'{id}']);
        $action_cancel   = new TDataGridAction([$this, 'onCancel'], ['id'=>'{id}']);
        
        $this->datagrid->addAction($action_edit,    _t('Edit'),   'far:edit blue');
        $this->datagrid->addAction($action_delete , _t('Delete'), 'fa:trash-alt red');
        $this->datagrid->addAction($action_transmit, 'Transmite', 'fa:rocket green');
        $this->datagrid->addAction($action_imprime,  'Imprime', 'fa:file-pdf red');
        $this->datagrid->addAction($action_cancel, _t('Cancel'), 'fa:power-off red');
        
        $action_edit->setDisplayCondition( function ($object) {
            return $object->emitida !== 'Y';
        });
        
        $action_cancel->setDisplayCondition( function ($object) {
            return (!empty($object->chave) && $object->cancelada == 'N' && $object->emitida == 'Y');
        });
        
        $action_delete->setDisplayCondition( function ($object) {
            return ( empty($object->chave) && $object->cancelada !== 'Y');
        });
        
        $action_transmit->setDisplayCondition( function ($object) {
            return (empty($object->chave) && $object->cancelada == 'N');
        });
        
        $action_imprime->setDisplayCondition( function ($object) {
            return !empty($object->chave);
        });
        
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
     * Transmite a Nota
     */
    public function onTransmite($param)
    {
        try
        {
            TTransaction::open('notas');
            
            $ini = AdiantiApplicationConfig::get();
            $gateway = $ini['general']['gateway'];
            
            if ($gateway == 'clouddfe')
            {
                $client = new NotaFiscalServicoClient(new NotaFiscalCloudDFEGateway);
            }
            
            if ($client instanceof NotaFiscalServicoClient)
            {
                $nota = new NotaFiscal($param['id']);
                $response = $client->transmite($nota);
                
                if (!empty($response['message']))
                {
                    new TMessage( (($response['status'] == 'success') ? 'info' : 'error'), $response['message']);
                }
                
                AdiantiCoreApplication::loadPage('NotaFiscalList', 'onReload');
            }
            else
            {
                throw new Exception('Gateway não encontrado');
            }
            
            TTransaction::close();
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }
    
    /**
     * Gera o PDF da Nota
     */
    public function onImprime($param)
    {
        try
        {
            TTransaction::open('notas');
            
            $ini = AdiantiApplicationConfig::get();
            $gateway = $ini['general']['gateway'];
            
            if ($gateway == 'clouddfe')
            {
                $client = new NotaFiscalServicoClient(new NotaFiscalCloudDFEGateway);
            }
            
            if ($client instanceof NotaFiscalServicoClient)
            {
                $nota = new NotaFiscal($param['id']);
                $response = $client->imprime($nota);
                
                if ($response['status'] == 'success')
                {
                    $nota->emitida = 'Y';
                    $nota->store();
                    
                    file_put_contents('files/notas/pdf/'.$nota->id.'.pdf', base64_decode($response['pdf']));
                    file_put_contents('files/notas/xml/'.$nota->id.'.xml', base64_decode($response['xml']));
                    
                    $window = TWindow::create('Nota Fiscal', 0.8, 0.8);
                    $object = new TElement('object');
                    $object->data  = 'download.php?file=files/notas/pdf/'.$nota->id.'.pdf';
                    $object->type  = 'application/pdf';
                    $object->style = "width: 100%; height:calc(100% - 10px)";
                    $object->add('O navegador não suporta a exibição deste conteúdo, <a style="color:#007bff;" target=_newwindow href="'.$object->data.'"> clique aqui para baixar</a>...');
                    
                    $window->add($object);
                    $window->show();
                }
                
                if (!empty($response['message']))
                {
                    new TMessage( (($response['status'] == 'success') ? 'info' : 'error'), $response['message']);
                }
            }
            else
            {
                throw new Exception('Gateway não encontrado');
            }
            
            TTransaction::close();
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }
    
    /**
     * Ask before cancel
     */
    public static function onCancel($param)
    {
        // define the cancel action
        $action = new TAction(array(__CLASS__, 'cancel'));
        $action->setParameters($param); // pass the key parameter ahead
        
        $id = $param['id'];
        
        TTransaction::open('erphouse');
        $nota = new NotaFiscal($id);
        $valor = number_format($nota->total, 2, ',', '.');
        $data = TDate::date2br($nota->dt_nota);
        TTransaction::close();
        
        // shows a dialog to the user
        new TQuestion("Deseja cancelar a Nota {$id} de R$ {$valor} emitida em {$data}?", $action);
    }
    /**
     * Cancela a Nota 
     */
    public function cancel($param)
    {
        try
        {
            TTransaction::open('notas');
            
            $ini = AdiantiApplicationConfig::get();
            $gateway = $ini['general']['gateway'];
            
            if ($gateway == 'clouddfe')
            {
                $client = new NotaFiscalServicoClient(new NotaFiscalCloudDFEGateway);
            }
            
            if ($client instanceof NotaFiscalServicoClient)
            {
                $nota = new NotaFiscal($param['id']);
                $response = $client->cancela($nota);
                
                if ($response['status'] == 'success')
                {
                    $nota->cancelada = 'Y';
                    $nota->store();
                }
                
                $this->onReload($param);
                
                if (!empty($response['message']))
                {
                    new TMessage( (($response['status'] == 'success') ? 'info' : 'error'), $response['message']);
                }
            }
            else
            {
                throw new Exception('Gateway não encontrado');
            }
            
            TTransaction::close();
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }
    
    /**
     * Exclui a nota se não foi transmitida ou cancelada.
     */
    public function Delete($param)
    {
        try
        {
            $key = $param['key'];
            TTransaction::open($this->database);
            
            $nota = new NotaFiscal($key, FALSE);
            
            if (!empty($nota->chave) || $nota->cancelada == 'Y')
            {
                throw new Exception('Operação não permitida');
            }
            
            // permite novamente aquela venda gerar a nota fiscal.
            $venda = Venda::where('nota_fiscal_id', '=', $nota->id)->set('faturada', 'N')->set('nota_fiscal_id', null)->update();
            
            $nota->delete();
            TTransaction::close();
            
            $this->onReload( $param );
            new TMessage('info', AdiantiCoreTranslator::translate('Record deleted'));
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
}
