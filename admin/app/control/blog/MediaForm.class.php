<?php
/**
 * MediaForm Registration
 * @author  <your name here>
 */
class MediaForm extends TStandardForm
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
        $this->form = new BootstrapFormBuilder('form_Media');
        $this->form->setFormTitle( _t('Media') );
        
        // defines the database
        parent::setDatabase('blog');
        
        // defines the active record
        parent::setActiveRecord('Media');

        // create the form fields
        $id           = new TEntry('id');
        $name         = new TEntry('name');
        $type         = new TCombo('type');
        $code         = new TEntry('code');
        $url          = new TEntry('url');
        $author       = new TEntry('author');
        
        $id->setEditable(FALSE);
        $type->addItems(['youtube' => 'YouTube', 'vimeo' => 'Vimeo', 'slideshare' => 'Slide share', 'flickr' => 'Flickr']);
        
        // add the fields
        $this->form->addFields([new TLabel('ID')],          [$id]);
        $this->form->addFields([new TLabel(_t('Name'))],    [$name]);
        $this->form->addFields([new TLabel(_t('Type'))],    [$type]);
        $this->form->addFields([new TLabel(_t('Code'))],    [$code]);
        $this->form->addFields([new TLabel('URL')],         [$url]);
        $this->form->addFields([new TLabel(_t('Author'))],  [$author]);
        
        // define the form action
        $btn = $this->form->addAction(_t('Save'), new TAction(array($this, 'onSave')), 'fa:save');
        $btn->class = 'btn btn-sm btn-primary';
        
        $this->form->addAction(_t('Clear'), new TAction(array($this, 'onClear')), 'fa:eraser red');
        
        // add the form to the page
        parent::add($this->form);
    }
}
