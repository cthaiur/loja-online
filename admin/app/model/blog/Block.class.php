<?php
/**
 * Block Active Record
 * @author  <your-name-here>
 */
class Block extends TRecord
{
    const TABLENAME = 'block';
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
        parent::addAttribute('title');
        parent::addAttribute('content');
    }
}