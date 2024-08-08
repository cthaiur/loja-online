<?php
/**
 * NotaFiscalForm
 *
 * @version    1.0
 * @package    notas
 * @subpackage control
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class NotaFiscalForm extends TPage
{
    protected $form; // form
    protected $fieldlist;
    
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct($param)
    {
        parent::__construct($param);
        parent::setTargetContainer('adianti_right_panel');
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_NotaFiscal');
        $this->form->setFormTitle('Nota Fiscal');
        $this->form->setColumnClasses(2, ['col-sm-3','col-sm-9']);
        
        // master fields
        $id = new TEntry('id');
        $cliente_id = new TDBUniqueSearch('cliente_id', 'notas', 'Pessoa', 'id', 'nome_fantasia');
        $dt_nota = new TDate('dt_nota');
        $natureza_operacao = new TDBUniqueSearch('natureza_operacao_id', 'notas', 'NaturezaOperacao', 'id', 'nome', 'codigo');
        $local_servico = new TCombo('local_servico');
        $chave = new TEntry('chave');
        
        // sizes
        $id->setSize('100%');
        $cliente_id->setSize('100%');
        $dt_nota->setSize('100%');
        $natureza_operacao->setSize('100%');
        $local_servico->setSize('100%');
        
        $cliente_id->addValidation('Cliente', new TRequiredValidator);
        $dt_nota->addValidation('Dt NotaFiscal', new TRequiredValidator);

        $id->setEditable(FALSE);
        $cliente_id->setMinLength(0);
        $dt_nota->setMask('dd/mm/yyyy');
        $dt_nota->setDatabaseMask('yyyy-mm-dd');
        $local_servico->addItems( ['L' => 'Tributado no município (prestador)', 'F' => 'Tributado fora do município (cliente)'] );
        $natureza_operacao->setMinLength(0);
        $natureza_operacao->setMask('{nome} ({codigo})');
        
        // add form fields to the form
        $this->form->addFields( [new TLabel('Id')], [$id] );
        $this->form->addFields( [new TLabel('Cliente')], [$cliente_id] );
        $this->form->addFields( [new TLabel('Dt NotaFiscal')], [$dt_nota] );
        $this->form->addFields( [new TLabel('Natureza Operação')], [$natureza_operacao] );
        $this->form->addFields( [ new TLabel('Local do serviço') ], [ $local_servico ] );
        $this->form->addFields( [ new TLabel('Chave') ], [ $chave ] );
        
        
        
        // detail fields
        $this->fieldlist = new TFieldList;
        $this->fieldlist-> width = '100%';
        
        
        $servico_id = new TDBUniqueSearch('list_servico_id[]', 'notas', 'Servico', 'id', 'nome', null, TCriteria::create( ['ativo' => 'Y'] ));
        $valor = new TNumeric('list_valor[]', 2, ',', '.');
        $quantidade = new TNumeric('list_quantidade[]', 2, ',', '.');
        
        $servico_id->setSize('100%');
        $valor->setSize('100%');
        $quantidade->setSize('100%');
        $servico_id->setMinLength(0);

        $this->fieldlist->addField( '<b>Serviço</b>', $servico_id, ['width' => '60%']);
        $this->fieldlist->addField( '<b>Valor</b>', $valor, ['width' => '20%']);
        $this->fieldlist->addField( '<b>Quantidade</b>', $quantidade, ['width' => '20%']);

        $this->form->addField($servico_id);
        $this->form->addField($valor);
        $this->form->addField($quantidade);
        
        $detail_wrapper = new TElement('div');
        $detail_wrapper->add($this->fieldlist);
        //$detail_wrapper->style = 'overflow-x:auto';
        
        $this->form->addContent( [ TElement::tag('h5', 'Itens da nota', [ 'style'=>'padding: 5px; margin-top: 10px'] ) ] );
        $this->form->addContent( [ $detail_wrapper ] );
        
        // create actions
        $this->form->addAction( _t('Save'),  new TAction( [$this, 'onSave'] ),  'fa:save green' );
        $this->form->addAction( _t('Clear'), new TAction( [$this, 'onClear'] ), 'fa:eraser red' );
        $this->form->addHeaderActionLink( _t('Close'), new TAction([$this, 'onClose']), 'fa:times red');
        
        // create the page container
        $container = new TVBox;
        $container->style = 'width: 100%';
        //$container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        parent::add($container);
    }
    
    /**
     * Executed whenever the user clicks at the edit button da datagrid
     */
    function onEdit($param)
    {
        try
        {
            TTransaction::open('notas');
            
            if (isset($param['key']))
            {
                $key = $param['key'];
                
                $object = new NotaFiscal($key);
                $this->form->setData($object);
                
                $items  = NotaFiscalItem::where('nota_fiscal_id', '=', $key)->load();
                
                if ($items)
                {
                    $this->fieldlist->addHeader();
                    foreach($items  as $item )
                    {
                        $detail = new stdClass;
                        $detail->list_servico_id = $item->servico_id;
                        $detail->list_valor = $item->valor;
                        $detail->list_quantidade = $item->quantidade;
                        
                        $this->fieldlist->addDetail($detail);
                    }
                    
                    //$this->fieldlist->addCloneAction();
                }
                else
                {
                    $this->onClear($param);
                }
                
                TTransaction::close(); // close transaction
	    }
	    else
            {
                $this->onClear($param);
            }
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
    
    /**
     * Clear form
     */
    public function onClear($param)
    {
        $this->fieldlist->addHeader();
        $this->fieldlist->addDetail( new stdClass );
        //$this->fieldlist->addCloneAction();
    }
    
    /**
     * Save the NotaFiscal and the NotaFiscalItem's
     */
    public static function onSave($param)
    {
        try
        {
            TTransaction::open('notas');
            
            $id = (int) $param['id'];
            $master = new NotaFiscal;
            $master->fromArray( $param);
            $master->dt_nota = TDateTime::convertToMask($param['dt_nota'], 'dd/mm/yyyy', 'yyyy-mm-dd');
            $master->mes = TDateTime::convertToMask($param['dt_nota'], 'dd/mm/yyyy', 'mm');
            $master->ano = TDateTime::convertToMask($param['dt_nota'], 'dd/mm/yyyy', 'yyyy');
            $master->store(); // save master object
            
            // delete details
            NotaFiscalItem::where('nota_fiscal_id', '=', $master->id)->delete();
            
            if( !empty($param['list_servico_id']) AND is_array($param['list_servico_id']) )
            {
                $total = 0;
                foreach( $param['list_servico_id'] as $row => $servico_id)
                {
                    if (!empty($servico_id))
                    {
                        $detail = new NotaFiscalItem;
                        $detail->nota_fiscal_id = $master->id;
                        $detail->servico_id = $param['list_servico_id'][$row];
                        $detail->valor =      (float) str_replace(['.',','], ['','.'], $param['list_valor'][$row]);
                        $detail->quantidade = (float) str_replace(['.',','], ['','.'], $param['list_quantidade'][$row]);
                        $detail->total = round($detail->valor * $detail->quantidade, 2);
                        $detail->store();
                        
                        $total += $detail->total;
                    }
                }
                $master->total = $total;
                $master->store(); // save master object
            }
            
            $data = new stdClass;
            $data->id = $master->id;
            TForm::sendData('form_NotaFiscal', $data);
            TTransaction::close(); // close the transaction
            
            $pos_action = new TAction(['NotaFiscalList', 'onReload']);
            new TMessage('info', AdiantiCoreTranslator::translate('Record saved'), $pos_action);
            TScript::create("Template.closeRightPanel()");
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
    
    /**
     * Close side panel
     */
    public static function onClose($param)
    {
        TScript::create("Template.closeRightPanel()");
    }
}
