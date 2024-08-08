<?php
/**
 * PaymentStatus Active Record
 * @author  <your-name-here>
 */
class ProductAction extends TRecord
{
    const TABLENAME = 'eco_product_action';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('product_id');
        parent::addAttribute('action_id');
    }
}
