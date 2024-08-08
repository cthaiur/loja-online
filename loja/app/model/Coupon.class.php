<?php
/**
 * PaymentStatus Active Record
 * @author  <your-name-here>
 */
class Coupon extends TRecord
{
    const TABLENAME = 'eco_coupon';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('email');
        parent::addAttribute('product_id');
        parent::addAttribute('expiration');
        parent::addAttribute('used');
        parent::addAttribute('discount');
    }
    
    public function get_product_name()
    {
        $p = new Product($this-> product_id);
        return $p-> description;
    }
    
    public static function getCoupons($email, $product_id)
    {
        $coupons = Coupon::where('email', '=', $email)
                         ->where('product_id', '=', $product_id)
                         ->where('expiration', '>=', date('Y-m-d'))
                         ->orderBy('used', 'asc')->load();
        
        if (count($coupons) > 0)
        {
            return $coupons[0];
        }
    }
}
