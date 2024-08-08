<?php
/**
 * Country Active Record
 * @author  <your-name-here>
 */
class Country extends TRecord
{
    const TABLENAME = 'eco_country';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('name');
        parent::addAttribute('iso');
        parent::addAttribute('iso3');
    }
}
?>