<?php
/**
 * TipoServicoForm
 *
 * @version    1.0
 * @package    notas
 * @subpackage control
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class TipoServicoForm extends TPage
{
    protected $form; // form
    
    use Adianti\Base\AdiantiStandardFormTrait; // Standard form methods
    
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct()
    {
        parent::__construct();
        
        parent::setTargetContainer('adianti_right_panel');

        $this->setDatabase('notas');              // defines the database
        $this->setActiveRecord('TipoServico');     // defines the active record
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_TipoServico');
        $this->form->setFormTitle('Tipo de Serviço');
        $this->form->setClientValidation(true);
        $this->form->setColumnClasses( 2, ['col-sm-5 col-lg-4', 'col-sm-7 col-lg-8'] );

        // create the form fields
        $id = new TEntry('id');
        $nome = new TEntry('nome');
        $lista_servico_id = new TDBUniqueSearch('lista_servico_id', 'notas', 'ListaServico', 'id', 'descricao,codigo');
        $lista_cnae_id = new TDBUniqueSearch('lista_cnae_id', 'notas', 'ListaCnae', 'id', 'descricao,codigo');

        // add the fields
        $this->form->addFields( [ new TLabel('Id') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Nome') ], [ $nome ] );
        $this->form->addFields( [ new TLabel('Lista serviço') ], [ $lista_servico_id ] );
        $this->form->addFields( [ new TLabel('Lista CNAE') ], [ $lista_cnae_id ] );

        $nome->addValidation('Nome', new TRequiredValidator);
        
        // set sizes
        $id->setSize('100%');
        $nome->setSize('100%');
        $lista_servico_id->setSize('100%');
        $lista_cnae_id->setSize('100%');
        $lista_servico_id->setMask('{codigo} - {descricao}');
        $lista_cnae_id->setMask('{codigo} - {descricao}');
        
        $id->setEditable(FALSE);
        
        
        
        // detail fields
        $this->fieldlist = new TFieldList;
        $this->fieldlist->width = '100%';
        $this->fieldlist->enableSorting();
        
        $imposto  = new TUniqueSearch('imposto[]');
        $aliquota = new TNumeric('aliquota[]', 2, ',', '.');
        $minimo   = new TNumeric('minimo[]', 2, ',', '.');
        
        $imposto->setMinLength(0);
        
        $impostos = [];
        $impostos['iss']    = 'ISSQN (Valor devido)';
        $impostos['pis']    = 'PIS (Retenção)';
        $impostos['cofins'] = 'COFINS (Retenção)';
        $impostos['inss']   = 'INSS (Retenção)';
        $impostos['ir']     = 'IRRF (Retenção)';
        $impostos['csll']   = 'CSLL (Retenção)';
        
        $imposto->addItems($impostos);
        
        $imposto->setSize('100%');
        $aliquota->setSize('100%');
        $minimo->setSize('100%');

        $this->fieldlist->addField( '<b>Imposto</b>',  $imposto, ['width' => '60%']);
        $this->fieldlist->addField( '<b>Alíquota (inteiro)</b>', $aliquota, ['width' => '20%']);
        $this->fieldlist->addField( '<b>Mínimo</b>',   $minimo, ['width' => '20%']);
        
        $this->form->addField($imposto);
        $this->form->addField($aliquota);
        $this->form->addField($minimo);
        
        $detail_wrapper = new TElement('div');
        $detail_wrapper->add($this->fieldlist);
        $detail_wrapper->style = 'overflow-x:auto';
        
        $this->form->addContent( [ TElement::tag('h5', 'Tributação', [ 'style'=>'padding: 5px; margin-top: 10px'] ) ] );
        $this->form->addContent( [ $detail_wrapper ] );
        
        
        
        // create the form actions
        $btn = $this->form->addAction(_t('Save'), new TAction([$this, 'onSave']), 'fa:save');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink(_t('New'),  new TAction([$this, 'onEdit']), 'fa:eraser red');
        $this->form->addHeaderActionLink( _t('Close'), new TAction([$this, 'onClose']), 'fa:times red');
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        
        parent::add($container);
    }
    
    /**
     * Clear form
     */
    public function onClear($param)
    {
        $this->fieldlist->addHeader();
        $this->fieldlist->addDetail( new stdClass );
        $this->fieldlist->addCloneAction();
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
                
                $object = new TipoServico($key);
                $this->form->setData($object);
                
                $items  = TipoServicoImposto::where('tipo_servico_id', '=', $key)->load();
                
                if ($items)
                {
                    $this->fieldlist->addHeader();
                    foreach($items  as $item )
                    {
                        $detail = new stdClass;
                        $detail->imposto  = $item->imposto;
                        $detail->aliquota = $item->aliquota;
                        $detail->minimo   = $item->minimo;
                        $this->fieldlist->addDetail($detail);
                    }
                    
                    $this->fieldlist->addCloneAction();
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
     * Save the Venda and the VendaItem's
     */
    public static function onSave($param)
    {
        try
        {
            TTransaction::open('notas');
            
            $id = (int) $param['id'];
            $master = new TipoServico;
            $master->fromArray( $param);
            $master->store(); // save master object
            
            // delete details
            TipoServicoImposto::where('tipo_servico_id', '=', $master->id)->delete();
            
            if( !empty($param['imposto']) AND is_array($param['imposto']) )
            {
                $total = 0;
                foreach( $param['imposto'] as $row => $imposto)
                {
                    if (!empty($imposto))
                    {
                        $detail = new TipoServicoImposto;
                        $detail->tipo_servico_id = $master->id;
                        $detail->imposto  = $imposto;
                        $detail->aliquota = (float) str_replace(['.',','], ['','.'], $param['aliquota'][$row]);
                        $detail->minimo   = (float) str_replace(['.',','], ['','.'], $param['minimo'][$row]);
                        $detail->store();
                    }
                }
            }
            
            $data = new stdClass;
            $data->id = $master->id;
            TForm::sendData('form_TipoServico', $data);
            TTransaction::close(); // close the transaction
            
            $pos_action = new TAction(['TipoServicoList', 'onReload'], ['register_state' => 'true']);
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
