<?php
/**
 * PaymentStatus Active Record
 * @author  <your-name-here>
 */
class Action extends TRecord
{
    const TABLENAME = 'eco_action';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('name');
        parent::addAttribute('remote_method');
    }
}
