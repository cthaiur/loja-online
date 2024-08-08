<?php
/**
 * BlogPreferenceForm
 *
 * @version    1.0
 * @package    control
 * @subpackage admin
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class BlogPreferenceForm extends TPage
{
    protected $form; // formulário
    
    /**
     * método construtor
     * Cria a página e o formulário de cadastro
     */
    function __construct()
    {
        parent::__construct();
        
        // cria o formulário
        $this->form = new BootstrapFormBuilder('form_preferences');
        $this->form->setFormTitle(_t('Preferences'));
        
        // cria os campos do formulário
        $site_title    = new TEntry('site_title');
        $site_subtitle = new TEntry('site_subtitle');
        $site_template = new TCombo('site_template');
        
        $options = [];
        $dirs = scandir('../templates');
        foreach ($dirs as $dir)
        {
            if (substr($dir,0,1) !== '.')
            {
                $options[ $dir ] = $dir;
            }
        }
        $site_template->addItems($options);
        $site_template->setDefaultOption(false);
        $this->form->addFields( [new TLabel(_t('Title'))],     [$site_title] );
        $this->form->addFields( [new TLabel(_t('Subtitle'))],  [$site_subtitle] );
        $this->form->addFields( [new TLabel(_t('Template'))],  [$site_template] );
        
        $site_title->setSize('70%');
        $site_subtitle->setSize('70%');
        $site_template->setSize('70%');
        
        $btn = $this->form->addAction(_t('Save'), new TAction(array($this, 'onSave')), 'far:save');
        $btn->class = 'btn btn-sm btn-primary';
        
        $container = new TVBox;
        $container->{'style'} = 'width: 100%;';
        $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        parent::add($container);
    }
    
    /**
     * Carrega o formulário de preferências
     */
    function onEdit($param)
    {
        try
        {
            $preferences = parse_ini_file('../config/config.ini');
            $this->form->setData((object) $preferences);
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage());
        }
    }
    
    /**
     * method onSave()
     * Executed whenever the user clicks at the save button
     */
    function onSave()
    {
        try
        {
            // get the form data
            $data = $this->form->getData();
            $data_array = (array) $data;
            
            if (!is_writable('../config/config.ini'))
            {
                throw new Exception( _t('Permission denied') . ': config/config.ini'  );
            }
            
            $output = '';
            foreach ($data_array as $property => $value)
            {
                $output.= "{$property} = \"{$value}\" \n";
            }
            file_put_contents('../config/config.ini', $output);
            
            // fill the form with the active record data
            $this->form->setData($data);
            
            // shows the success message
            new TMessage('info', AdiantiCoreTranslator::translate('Record saved'));
            // reload the listing
        }
        catch (Exception $e) // in case of exception
        {
            $object = $this->form->getData();
            $this->form->setData($object);
            new TMessage('error', $e->getMessage());
        }
    }
}
