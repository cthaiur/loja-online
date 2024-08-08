<?php
/**
 * Media Active Record
 * @author  <your-name-here>
 */
class Media extends TRecord
{
    const TABLENAME = 'media';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    use SystemChangeLogTrait;
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('name');
        parent::addAttribute('type');
        parent::addAttribute('code');
        parent::addAttribute('url');
        parent::addAttribute('author');
    }
}
