<?php
/**
 * PessoaFormView
 *
 * @version    1.0
 * @package    notas
 * @subpackage control
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class PessoaFormView extends TPage
{
    protected $form; // form
    protected $detail_list_vendas;
    
    /**
     * Page constructor
     */
    public function __construct($param)
    {
        parent::__construct();
        
        parent::setTargetContainer('adianti_right_panel');

        $this->form = new BootstrapFormBuilder('form_PessoaView');
        $this->form->setFormTitle('Pessoa');
        $this->form->setColumnClasses(2, ['col-sm-3', 'col-sm-9']);
        
        $bt1 = $this->form->addHeaderAction( 'Etiqueta', new TAction([$this, 'onGeraEtiqueta'], ['key'=>$param['key'], 'static' => '1']), 'far:envelope purple');
        $bt2 = $this->form->addHeaderAction( 'Editar', new TAction(['PessoaForm', 'onEdit'],['key'=>$param['key']]), 'far:edit blue');
        $bt3 = $this->form->addHeaderAction( 'Fechar', new TAction([$this, 'onClose']), 'fa:times red');
        
        $bt1->style .= ';box-shadow:none';
        $bt2->style .= ';box-shadow:none';
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%'; 
        // $container->add(new TXMLBreadCrumb('menu.xml', 'PessoaList'));
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
            $master_object = new Pessoa($param['key']);
            
            $label_id = new TLabel('Id:', '#333333', '12px', '');
            $label_nome_fantasia = new TLabel('Fantasia:', '#333333', '12px', '');
            $label_codigo_nacional = new TLabel('CPF/CNPJ:', '#333333', '12px', '');
            $label_fone = new TLabel('Fone:', '#333333', '12px', '');
            $label_email = new TLabel('Email:', '#333333', '12px', '');
            $label_cidade = new TLabel('Local:', '#333333', '12px', '');
            $label_created_at = new TLabel('Criado em:', '#333333', '12px', '');
            $label_updated_at = new TLabel('Alterado em:', '#333333', '12px', '');
            
            $text_id  = new TTextDisplay($master_object->id, '#333333', '12px', '');
            $text_nome_fantasia  = new TTextDisplay($master_object->nome_fantasia, '#333333', '12px', '');
            $text_codigo_nacional  = new TTextDisplay($master_object->codigo_nacional, '#333333', '12px', '');
            $text_fone  = new THyperLink('<i class="fab fa-whatsapp green"></i> '.$master_object->fone, 'https://api.whatsapp.com/send?phone=55'.$master_object->fone, '#007bff', '12px', '');
            $text_email  = new THyperLink('<i class="far fa-envelope red"></i> ' . $master_object->email, 'https://mail.google.com/mail/u/0/?view=cm&fs=1&to='.$master_object->email.'&tf=1', '#007bff', '12px', '');
            $link_maps = 'https://www.google.com/maps/place/' . $master_object->logradouro . ',' . 
                                                                $master_object->numero . ', ' .
                                                                $master_object->bairro . ', ' .
                                                                (isset($master_object->cidade_id) ? $master_object->cidade->nome . '+' . $master_object->cidade->estado->uf : '');
            $text_cidade  = new THyperLink('<i class="fa fa-map-marker-alt"></i> Link para google maps', $link_maps, '#007bff', '12px', '');
            $text_created_at  = new TTextDisplay(TDateTime::convertToMask($master_object->created_at, 'yyyy-mm-dd hh:ii:ss', 'dd/mm/yyyy hh:ii:ss'), '#333333', '12px', '');
            $text_updated_at  = new TTextDisplay(TDateTime::convertToMask($master_object->updated_at, 'yyyy-mm-dd hh:ii:ss', 'dd/mm/yyyy hh:ii:ss'), '#333333', '12px', '');
            
            $this->form->addFields([$label_id],[$text_id]);
            $this->form->addFields([$label_nome_fantasia],[$text_nome_fantasia]);
            $this->form->addFields([$label_codigo_nacional],[$text_codigo_nacional]);
            $this->form->addFields([$label_fone],[$text_fone]);
            $this->form->addFields([$label_email],[$text_email]);
            $this->form->addFields([$label_cidade],[$text_cidade]);
            $this->form->addFields([$label_created_at],[$text_created_at]);
            $this->form->addFields([$label_updated_at],[$text_updated_at]);

            $this->detail_list_vendas = new BootstrapDatagridWrapper( new TDataGrid );
            $this->detail_list_vendas->style = 'width:100%';
            $this->detail_list_vendas->disableDefaultClick();
            $this->detail_list_vendas->disableHtmlConversion();
            
            $column_dt_venda = $this->detail_list_vendas->addColumn( new TDataGridColumn('dt_venda', 'Data', 'left') );
            $column_itens = $this->detail_list_vendas->addColumn( new TDataGridColumn('itens_string', 'Itens', 'left') );
            $column_total = $this->detail_list_vendas->addColumn( new TDataGridColumn('total', 'Total', 'right') );
            
            $column_dt_venda->setTransformer( function($value, $object, $row) {
                if ($object->cancelada == 'Y')
                {
                    $row->style= 'color: silver';
                }
                return TDate::convertToMask($value, 'yyyy-mm-dd', 'dd/mm/yyyy');
            });
            
            $column_total->setTransformer( function($value) {
                if (is_numeric($value)) {
                    return 'R$&nbsp;'.number_format($value, 2, ',', '.');
                }
                return $value;
            });
            
            $this->detail_list_vendas->createModel();
            
            $items = Venda::where('cliente_id', '=', $master_object->id)->orderBy('id', 'desc')->load();
            $this->detail_list_vendas->addItems($items);
            
            $panel = new TPanelGroup('Vendas', '#f5f5f5');
            $panel->add($this->detail_list_vendas)->style = 'overflow-x:auto';
            $this->form->addContent([$panel]);
            
            TTransaction::close();
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }
    
    /**
     * Gera etiqueta
     */
    public function onGeraEtiqueta($param)
    {
        try
        {
            $this->onEdit($param);
            
            TTransaction::open('notas');
            $pessoa = new Pessoa($param['key']);
            
            $replaces = $pessoa->toArray();
            $replaces['cidade'] = $pessoa->cidade;
            $replaces['estado'] = $pessoa->cidade->estado;
            
            // string with HTML contents
            $html = new THtmlRenderer('app/resources/mail-label.html');
            $html->enableSection('main', $replaces);
            $contents = file_get_contents('app/resources/styles-print.html') . $html->getContents();
            
            $options = new \Dompdf\Options();
            $options->setChroot(getcwd());
            
            // converts the HTML template into PDF
            $dompdf = new \Dompdf\Dompdf($options);
            $dompdf->loadHtml($contents);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            
            $file = 'app/output/etiqueta.pdf';
            
            // write and open file
            file_put_contents($file, $dompdf->output());
            
            $window = TWindow::create('Export', 0.8, 0.8);
            $object = new TElement('object');
            $object->data  = $file.'?rndval='.uniqid();
            $object->type  = 'application/pdf';
            $object->style = "width: 100%; height:calc(100% - 10px)";
            $window->add($object);
            $window->show();
            
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
