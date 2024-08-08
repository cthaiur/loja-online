<?php
/**
 * BlockForm Registration
 * @author  <your name here>
 */
class BlockForm extends TStandardForm
{
    protected $form; // form
    protected $notebook;
    
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct()
    {
        parent::__construct();
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_Block');
        $this->form->setFormTitle( _t('Block') );
        
        // defines the database
        parent::setDatabase('blog');
        
        // defines the active record
        parent::setActiveRecord('Block');

        // create the form fields
        $id           = new TEntry('id');
        $name         = new TEntry('name');
        $title        = new TEntry('title');
        $content      = new THtmlEditor('content');

        $id->setEditable(FALSE);
        
        // add the fields
        $this->form->addFields([new TLabel('ID')],           [$id]);
        $this->form->addFields([new TLabel(_t('Key'))],      [$name]);
        $this->form->addFields([new TLabel(_t('Title'))],    [$title]);
        $this->form->addFields([new TLabel('<b>'._t('Content').'</b><hr>')]);
        $this->form->addFields([$content]);
        
        $content->setSize('100%', 300);
        
        // define the form action
        $btn = $this->form->addAction(_t('Save'), new TAction(array($this, 'onSave')), 'fa:save');
        $btn->class = 'btn btn-sm btn-primary';
        
        $this->form->addAction(_t('Clear'), new TAction(array($this, 'onClear')), 'fa:eraser red');
        
        // add the form to the page
        parent::add($this->form);
    }
}
