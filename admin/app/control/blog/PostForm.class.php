<?php
/**
 * PostForm Registration
 * @author  <your name here>
 */
class PostForm extends TPage
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
        $this->form = new BootstrapFormBuilder('form_Post');
        $this->form->setFormTitle( _t('Post') );
        
        // create the form fields
        $id           = new TEntry('id');
        $title        = new TEntry('title');
        $subtitle     = new TEntry('subtitle');
        $content      = new THtmlEditor('content');
        $image        = new TFile('image');
        $tags         = new TMultiEntry('tags');
        $post_date    = new TDate('post_date');
        $media_id     = new TDBCombo('media_id', 'blog', 'Media', 'id', '{name} ({type})');
        $category_id  = new TDBCombo('category_id', 'blog', 'Category', 'id', 'name');

        $id->setEditable(FALSE);
        $tags->setValueSeparator(',');
        $image->enableFileHandling();
        $image->enableImageGallery();
        $image->setAllowedExtensions( ['jpg', 'png']);
        $media_id->enableSearch();
        
        // add the fields
        $this->form->addFields([new TLabel('ID')],           [$id]);
        $this->form->addFields([new TLabel(_t('Title'))],    [$title]);
        $this->form->addFields([new TLabel(_t('Subtitle'))], [$subtitle]);
        $this->form->addFields([new TLabel(_t('Date'))],     [$post_date]);
        $this->form->addFields([new TLabel(_t('Image'))],    [$image]);
        $this->form->addFields([new TLabel(_t('Tags'))],     [$tags]);
        $this->form->addFields([new TLabel(_t('Category'))], [$category_id]);
        $this->form->addFields([new TLabel(_t('Media'))],    [$media_id]);
        $this->form->addFields([new TLabel('<b>'._t('Post').'</b><hr>', null, null, null, '100%')]);
        $this->form->addFields([$content]);
        
        $content->setSize('100%', 400);
        $tags->setSize('100%');
        
        // define the form action
        $btn = $this->form->addAction(_t('Save'), new TAction(array($this, 'onSave')), 'fa:save');
        $btn->class = 'btn btn-sm btn-primary';
        
        $this->form->addAction(_t('Clear'), new TAction(array($this, 'onClear')), 'fa:eraser red');
        $this->form->addAction(_t('Return'), new TAction(array('PostList', 'onReload')), 'fa:arrow-left');

        // add the form to the page
        parent::add($this->form);
    }
    
    /**
     * method onSave()
     * Executed whenever the user clicks at the save button
     */
    public function onSave($param)
    {
        try
        {
            TTransaction::open('blog');
            $data   = $this->form->getData();
            $object = new Post;
            $object->fromArray( (array) $data);
            
            if (!empty($object->id))
            {
                $old = Post::find($object->id);
                
                if ($old->login !== TSession::getValue('login') && TSession::getValue('login') !== 'admin')
                {
                    throw new Exception(_t('Permission denied'));
                }
                
                $object->login = $old->login;
            }
            else
            {
                $object->login = TSession::getValue('login');
            }
            
            $this->form->validate();
            $object->store(); // garente que tenha id
            
            if ($data->image)
            {
                $this->saveFile($object, $data, 'image', 'app/images/posts/');
            }
            
            $this->form->setData($object);
            TTransaction::close();
            
            // shows the success message
            new TMessage('info', AdiantiCoreTranslator::translate('Record saved'));
            
            return $object;
        }
        catch (Exception $e) // in case of exception
        {
            $object = $this->form->getData();
            $this->form->setData($object);
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
    
    /**
     * Clear form
     */
    public function onClear($param)
    {
        $this->form->clear(true);
        // $this->varlist->addHeader();
        // $this->varlist->addDetail( new stdClass );
        // $this->varlist->addCloneAction();
    }
    
    /**
     * method onEdit()
     * Executed whenever the user clicks at the edit button da datagrid
     * @param  $param An array containing the GET ($_GET) parameters
     */
    public function onEdit($param)
    {
        try
        {
            if (isset($param['key']))
            {
                $key = $param['key'];
                TTransaction::open('blog');
                $object = Post::find($key);
                
                if ($object->login !== TSession::getValue('login') && TSession::getValue('login') !== 'admin')
                {
                    throw new Exception(_t('Permission denied'));
                }
                
                $this->form->setData($object);
                
                // close the transaction
                TTransaction::close();
                
                return $object;
            }
            else
            {
                $this->form->clear();
            }
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
}
