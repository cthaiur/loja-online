<?php
/**
 * PaymentStatus Active Record
 * @author  <your-name-here>
 */
class PaymentStatus extends TRecord
{
    const TABLENAME = 'eco_payment_status';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('description');
        parent::addAttribute('isfinal');
        parent::addAttribute('color');
    }
    
    public function get_description_label_class()
    {
        $styles = [];
        $styles[1] = 'warning';
        $styles[2] = 'secondary';
        $styles[3] = 'info';
        $styles[4] = 'info';
        $styles[5] = 'danger';
        $styles[6] = 'danger';
        $styles[7] = 'danger';
        $styles[8] = 'success';
        $styles[99] = 'secondary';
        $styles[100] = 'primary';
        
        return $styles[$this->id];
    }
    
    public function get_description_formatted()
    {
        $label_class = $this->get_description_label_class();
        return "<span style=\"font-size:10pt;text-shadow:none\" class=\"badge badge-{$label_class}\">". _t($this-> description)."</span>";
    }
}
