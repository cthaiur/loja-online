<?php
/**
 * TransactionAction Active Record
 * @author  <your-name-here>
 */
class TransactionAction extends TRecord
{
    const TABLENAME = 'eco_transaction_action';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}
    
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
        parent::addAttribute('transaction_id');
        parent::addAttribute('action_id');
        parent::addAttribute('process_time');
    }
}