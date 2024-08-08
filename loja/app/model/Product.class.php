<?php
/**
 * Product Active Record
 * @author  <your-name-here>
 */
class Product extends TRecord
{
    const TABLENAME = 'eco_product';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('description');
        parent::addAttribute('url');
        parent::addAttribute('amount');
        parent::addAttribute('currency');
        parent::addAttribute('price');
        parent::addAttribute('image');
        parent::addAttribute('languages');
        parent::addAttribute('paymenttypes');
        parent::addAttribute('has_shipping');
        parent::addAttribute('shipping_cost');
        parent::addAttribute('weight');
        parent::addAttribute('width');
        parent::addAttribute('height');
        parent::addAttribute('length');
        parent::addAttribute('details');
        parent::addAttribute('opinions');
        parent::addAttribute('active');
        parent::addAttribute('confirmation_mail');
        parent::addAttribute('tag');
    }
    
    public function get_description_formatted()
    {
        if ($this->active == 'Y')
        {
            return $this->description;
        }
        else
        {
            return "<span style='color:gray'>$this->description</span>";
        }
    }
    
    public function getPaymentTypes($language)
    {
        $paymenttypes = explode(',', $this->paymenttypes);
        
        $items = array();
        foreach ($paymenttypes as $paymenttype_code)
        {
            $paymenttype = new PaymentType($paymenttype_code);
            $languages = explode(',', $paymenttype->languages);
            if (in_array($language, $languages))
            {
                $items[] = $paymenttype;
            }
        }
        return $items;
    }
}
