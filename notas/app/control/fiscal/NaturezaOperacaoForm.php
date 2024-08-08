<?php
/**
 * NaturezaOperacaoForm
 *
 * @version    1.0
 * @package    notas
 * @subpackage control
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class NaturezaOperacaoForm extends TPage
{
    protected $form; // form
    
    use Adianti\Base\AdiantiStandardFormTrait {
        onSave as onSaveTrait;
    }
    
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct()
    {
        parent::__construct();
        
        parent::setTargetContainer('adianti_right_panel');
        $this->setAfterSaveAction( new TAction(['NaturezaOperacaoList', 'onReload'], ['register_state' => 'true']) );

        $this->setDatabase('notas');              // defines the database
        $this->setActiveRecord('NaturezaOperacao');     // defines the active record
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_NaturezaOperacao');
        $this->form->setFormTitle('Natureza de Operação');
        $this->form->setClientValidation(true);
        $this->form->setColumnClasses( 2, ['col-sm-5 col-lg-4', 'col-sm-7 col-lg-8'] );
        

        // create the form fields
        $id = new TEntry('id');
        $codigo = new TEntry('codigo');
        $nome = new TEntry('nome');
        $local_servico = new TCombo('local_servico');
        $padrao = new TRadioGroup('padrao');
        
        $local_servico->addItems( ['L' => 'Tributado no município (prestador)', 'F' => 'Tributado fora do município (cliente)'] );
        
        // add the fields
        $this->form->addFields( [ new TLabel('Id') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Código') ], [ $codigo ] );
        $this->form->addFields( [ new TLabel('Nome') ], [ $nome ] );
        $this->form->addFields( [ new TLabel('Local do serviço') ], [ $local_servico ] );
        $this->form->addFields( [ new TLabel('Padrão') ], [ $padrao ] );
        
        $nome->addValidation('Nome', new TRequiredValidator);
        $codigo->addValidation('Ativo', new TRequiredValidator);
        $padrao->addValidation('Padrão', new TRequiredValidator);
        
        // set sizes
        $id->setSize('100%');
        $codigo->setSize('100%');
        $nome->setSize('100%');
        $local_servico->setSize('100%');
        $padrao->setSize('100%');
        $padrao->addItems( ['Y' => 'Sim', 'N' => 'Não'] );
        $padrao->setLayout('horizontal');
        $padrao->setValue('Y');
        
        $id->setEditable(FALSE);
        
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
     * Close side panel
     */
    public static function onClose($param)
    {
        TScript::create("Template.closeRightPanel()");
    }
    
    /**
     * Salva os dados
     */
    public function onSave($param)
    {
        $object = $this->onSaveTrait();
        
        if ($object && $object->padrao == 'Y')
        {
            try
            {
                TTransaction::open('notas');
                NaturezaOperacao::where('padrao','=', 'Y')->where('id', '<>', $object->id)->set('padrao', 'N')->update();
                TTransaction::close();
            }
            catch (Exception $e) // in case of exception
            {
                new TMessage('error', $e->getMessage());
                TTransaction::rollback();
            }
        }
    }
}
