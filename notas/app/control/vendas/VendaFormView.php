<?php
/**
 * VendaFormView
 *
 * @version    1.0
 * @package    notas
 * @subpackage control
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class VendaFormView extends TPage
{
    protected $form; // form
    protected $detail_list_itens;
    
    /**
     * Page constructor
     */
    public function __construct($param)
    {
        parent::__construct();
        
        parent::setTargetContainer('adianti_right_panel');

        $this->form = new BootstrapFormBuilder('form_VendaView');
        $this->form->setFormTitle('Venda');
        $this->form->setColumnClasses(2, ['col-sm-3', 'col-sm-9']);
        
        $this->form->addHeaderAction('Fechar', new TAction([$this, 'onClose']), 'fa:times red');
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%'; 
        // $container->add(new TXMLBreadCrumb('menu.xml', 'VendaList'));
        $container->add($this->form);

        parent::add($container);
    }
    
    /**
     * onEdit
     */
    public function onEdit($param)
    {
        try
        {
            TTransaction::open('notas');
            $master_object = new Venda($param['key']);
            
            $label_id = new TLabel('Id:', '#333333', '12px', '');
            $label_cliente = new TLabel('Cliente:', '#333333', '12px', '');
            $label_data = new TLabel('Data:', '#333333', '12px', '');
            $label_total = new TLabel('Total:', '#333333', '12px', '');
            
            $text_id  = new TTextDisplay($master_object->id, '#333333', '12px', '');
            $text_cliente  = new TTextDisplay($master_object->cliente->nome, '#333333', '12px', '');
            $text_data  = new TTextDisplay(TDate::convertToMask($master_object->dt_venda, 'yyyy-mm-dd', 'dd/mm/yyyy'), '#333333', '12px', '');
            $text_total  = new TTextDisplay(number_format($master_object->total, 2, ',', '.'), '#333333', '12px', '');
            
            $this->form->addFields([$label_id],[$text_id]);
            $this->form->addFields([$label_cliente],[$text_cliente]);
            $this->form->addFields([$label_data],[$text_data]);
            $this->form->addFields([$label_total],[$text_total]);

            $this->detail_list_itens = new BootstrapDatagridWrapper( new TDataGrid );
            $this->detail_list_itens->style = 'width:100%';
            $this->detail_list_itens->disableDefaultClick();
            
            $column_servico = $this->detail_list_itens->addColumn( new TDataGridColumn('servico->nome', 'Serviço', 'left') );
            $column_valor = $this->detail_list_itens->addColumn( new TDataGridColumn('valor', 'Valor', 'right') );
            $column_qtde = $this->detail_list_itens->addColumn( new TDataGridColumn('quantidade', 'Quantidade', 'center') );
            $column_total = $this->detail_list_itens->addColumn( new TDataGridColumn('total', 'Total', 'right') );
            
            $transformer = function($value) {
                if (is_numeric($value)) {
                    return 'R$&nbsp;'.number_format($value, 2, ',', '.');
                }
                return $value;
            };
            
            $column_valor->setTransformer( $transformer );
            //$column_qtde->setTransformer( $transformer );
            $column_total->setTransformer( $transformer );
            
            $this->detail_list_itens->createModel();
            
            $items = VendaItem::where('venda_id', '=', $master_object->id)->orderBy('id', 'desc')->load();
            $this->detail_list_itens->addItems($items);
            
            $panel = new TPanelGroup('Itens', '#f5f5f5');
            $panel->add($this->detail_list_itens)->style = 'overflow-x:auto';
            $this->form->addContent([$panel]);
            
            TTransaction::close();
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
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
