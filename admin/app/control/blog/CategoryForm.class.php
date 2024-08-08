<?php
/**
 * CategoryForm Registration
 * @author  <your name here>
 */
class CategoryForm extends TStandardForm
{
    protected $form; // form
    protected $notebook;
    
    use Adianti\Base\AdiantiFileSaveTrait;
    
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct()
    {
        parent::__construct();
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_Category');
        $this->form->setFormTitle( _t('Category') );
        
        // defines the database
        parent::setDatabase('blog');
        
        // defines the active record
        parent::setActiveRecord('Category');

        // create the form fields
        $id    = new TEntry('id');
        $name  = new TEntry('name');
        $image  = new TFile('image');
        $link  = new TEntry('link');
        $position  = new TEntry('position');
        $icon      = new TEntry('icon');
        $description      = new TText('description');
        $show_menu = new TRadioGroup('show_menu');
        $show_menu->setUseButton();
        $show_menu->setLayout('horizontal');
        $show_menu->addItems( [ 'Y' => _t('Yes'), 'N' => _t('No') ] );

        $id->setEditable(FALSE);
        $image->enableFileHandling();
        $image->enableImageGallery();
        $image->setAllowedExtensions( ['jpg', 'png']);
        
        // add the fields
        $this->form->addFields([new TLabel('ID')], [$id]);
        $this->form->addFields([new TLabel(_t('Name'))], [$name]);
        $this->form->addFields([new TLabel(_t('Image'))], [$image]);
        $this->form->addFields([new TLabel(_t('Link'))], [$link]);
        $this->form->addFields([new TLabel(_t('Position'))], [$position]);
        $this->form->addFields([new TLabel(_t('Icon'))], [$icon]);
        $this->form->addFields([new TLabel(_t('Description'))], [$description]);
        $this->form->addFields([new TLabel(_t('Show menu'))], [$show_menu]);
        $id->setEditable(FALSE);
        $id->setSize('30%');
        $name->setSize('50%');
        
        // define the form action
        $btn = $this->form->addAction(_t('Save'), new TAction(array($this, 'onSave')), 'fa:save');
        $btn->class = 'btn btn-sm btn-primary';
        
        $this->form->addAction(_t('Clear'), new TAction(array($this, 'onClear')), 'fa:eraser red');
        
        // add the form to the page
        parent::add($this->form);
    }
    
    /**
     * method onSave()
     * Executed whenever the user clicks at the save button
     */
    public function onSave()
    {
        try
        {
            TTransaction::open($this->database);
            
            $data   = $this->form->getData();
            $object = $this->form->getData($this->activeRecord);
            $this->form->validate();
            $object->store();
            
            if ($object->image)
            {
                $this->saveFile($object, $data, 'image', 'app/images/categories/');
            }
            
            $this->form->setData($object);
            TTransaction::close();
            
            new TMessage('info', AdiantiCoreTranslator::translate('Record saved'));
            
            return $object;
        }
        catch (Exception $e) // in case of exception
        {
            $object = $this->form->getData($this->activeRecord);
            $this->form->setData($object);
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
}
