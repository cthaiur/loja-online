<?php
/**
 * PaymentStatus Active Record
 * @author  <your-name-here>
 */
class ProductRequirement extends TRecord
{
    const TABLENAME = 'eco_product_requirement';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('product_id');
        parent::addAttribute('requirement_id');
    }
}
