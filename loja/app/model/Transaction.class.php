<?php
/**
 * Transaction Active Record
 * @author  <your-name-here>
 */
class Transaction extends TRecord
{
    const TABLENAME = 'eco_transaction';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    const APROVEDS = [3,4,8];
    
    private $customer;
    private $product;
    private $paymentstatus;
    private $paymenttype;
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('operation_date');
        parent::addAttribute('operation_time');
        parent::addAttribute('external_id');
        parent::addAttribute('quantity');
        parent::addAttribute('value');
        parent::addAttribute('customer_id');
        parent::addAttribute('product_id');
        parent::addAttribute('paymentstatus_id');
        parent::addAttribute('paymenttype_id');
        parent::addAttribute('token');
        parent::addAttribute('shipping_cost');
        parent::addAttribute('shipping_code');
        parent::addAttribute('total');
        parent::addAttribute('obs');
    }

    public function getByCustomer(Customer $customer, $exclude = NULL)
    {
        $repo = new TRepository('Transaction');
        $cri = new TCriteria;
        $cri->add(new TFilter('customer_id', '=', $customer->id));
        $cri->setProperty('order', 'id');
        $cri->setProperty('direction', 'desc');
        return $repo->load($cri);
    }
    
    /**
     * Retorna a última transação de um cliente
     */
    public static function getLastTransactionFor(Customer $customer)
    {
        $repo = new TRepository('Transaction');
        $cri = new TCriteria;
        $cri->add(new TFilter('customer_id', '=', $customer->id));
        $cri->setProperty('order', 'id');
        $cri->setProperty('direction', 'desc');
        $objects = $repo->load($cri);
        return $objects[0];
    }
    
    /**
     * Retorna a transação baseada no transaction id
     */
    public static function getTransactionByExternalCode($code)
    {
        $repo = new TRepository('Transaction');
        $cri = new TCriteria;
        $cri->add(new TFilter('external_id', '=', $code));
        $objects = $repo->load($cri);
        if ($objects)
        {
            return $objects[0];
        }
    }
    
    /**
     * Retorna a transação baseada no token
     */
    public static function getTransactionByToken($code)
    {
        $repo = new TRepository('Transaction');
        $cri = new TCriteria;
        $cri->add(new TFilter('token', '=', $code));
        $objects = $repo->load($cri);
        if ($objects)
        {
            return $objects[0];
        }
    }
    
    /**
     * Method set_customer
     * Sample of usage: $transaction->customer = $object;
     * @param $object Instance of Customer
     */
    public function set_customer(Customer $object)
    {
        $this->customer = $object;
        $this->customer_id = $object->id;
    }
    
    /**
     * Method get_customer
     * Sample of usage: $transaction->customer->attribute;
     * @returns Customer instance
     */
    public function get_customer()
    {
        // loads the associated object
        if (empty($this->customer))
            $this->customer = new Customer($this->customer_id);
    
        // returns the associated object
        return $this->customer;
    }
    
    /**
     * Method set_product
     * Sample of usage: $transaction->product = $object;
     * @param $object Instance of Product
     */
    public function set_product(Product $object)
    {
        $this->product = $object;
        $this->product_id = $object->id;
    }
    
    /**
     * Method get_product
     * Sample of usage: $transaction->product->attribute;
     * @returns Product instance
     */
    public function get_product()
    {
        // loads the associated object
        if (empty($this->product))
            $this->product = new Product($this->product_id);
    
        // returns the associated object
        return $this->product;
    }
    
    /**
     * Method set_paymentstatus
     * Sample of usage: $transaction->paymentstatus = $object;
     * @param $object Instance of PaymentStatus
     */
    public function set_paymentstatus(PaymentStatus $object)
    {
        $this->paymentstatus = $object;
        $this->paymentstatus_id = $object->id;
    }
    
    /**
     * Method get_paymentstatus
     * Sample of usage: $transaction->paymentstatus->attribute;
     * @returns PaymentStatus instance
     */
    public function get_paymentstatus()
    {
        // loads the associated object
        if (empty($this->paymentstatus))
            $this->paymentstatus = new PaymentStatus($this->paymentstatus_id);
    
        // returns the associated object
        return $this->paymentstatus;
    }
    
    /**
     * Method set_paymenttype
     * Sample of usage: $transaction->paymenttype = $object;
     * @param $object Instance of PaymentType
     */
    public function set_paymenttype(PaymentType $object)
    {
        $this->paymenttype = $object;
        $this->paymenttype_id = $object->id;
    }
    
    /**
     * Method get_paymenttype
     * Sample of usage: $transaction->paymenttype->attribute;
     * @returns PaymentType instance
     */
    public function get_paymenttype()
    {
        // loads the associated object
        if (empty($this->paymenttype))
            $this->paymenttype = new PaymentType($this->paymenttype_id);
    
        // returns the associated object
        return $this->paymenttype;
    }

    public function get_product_name()
    {
        $object = new Product($this-> product_id);
        return $object-> description;
    }
    
    public function get_paymentstatus_name()
    {
        $object = new PaymentStatus($this-> paymentstatus_id);
        return _t($object-> description);
    }
    
    public function get_paymenttype_name()
    {
        $object = new PaymentType($this-> paymenttype_id);
        return $object-> description;
    }
    
    public function get_paymentstatus_info( $complete = true )
    {
        $object = new PaymentStatus($this-> paymentstatus_id);
        
        $type = $this->get_paymenttype();
        $type_icon = "<i class='{$type->icon}' aria-hidden='true'></i> ";
        
        $label_class = $object->description_label_class;
        
        $label = "<span style=\"font-size:10pt;text-shadow:none;padding:5px;font-weight:500\" class=\"badge badge-{$label_class}\">". $type_icon . _t($object-> description)."</span>";
        
        if ($complete)
        {
            $label .= '<br>'. "<span style=\"font-size:8pt\">".$this-> external_id."<span>";
        }
        
        return $label;
    }
    
    public function get_customer_name()
    {
        $object = new Customer($this-> customer_id);
        return $object-> name;
    }
}
