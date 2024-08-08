<?php
/**
 * PaymentType Active Record
 * @author  <your-name-here>
 */
class PaymentType extends TRecord
{
    const TABLENAME = 'eco_payment_type';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('description');
        parent::addAttribute('information');
        parent::addAttribute('languages');
        parent::addAttribute('icon');
        parent::addAttribute('url');
    }
}
?>