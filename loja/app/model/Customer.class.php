<?php
/**
 * Customer Active Record
 * @author  <your-name-here>
 */
class Customer extends TRecord
{
    const TABLENAME = 'eco_customer';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    const CREATEDAT = 'created_at';
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('name');
        parent::addAttribute('email');
        parent::addAttribute('password');
        parent::addAttribute('document');
        parent::addAttribute('phone');
        parent::addAttribute('address');
        parent::addAttribute('number');
        parent::addAttribute('complement');
        parent::addAttribute('neighborhood');
        parent::addAttribute('postal');
        parent::addAttribute('city');
        parent::addAttribute('state');
        parent::addAttribute('country_id');
        parent::addAttribute('active');
        parent::addAttribute('created_at');
        parent::addAttribute('obs');
    }
    
    public function get_country_name()
    {
        $country = new Country($this-> country_id);
        return $country-> name;
    }
    
    public function get_country()
    {
        return new Country($this-> country_id);
    }
    
    /**
     * Static loader
     * Returns the customer from email
     * @param $login
     */
    public static function newFromEmail($email)
    {
        $repos = new TRepository('Customer');
        $criteria = new TCriteria;
        $criteria->add(new TFilter('email', '=', $email));
        $objects = $repos->load($criteria);
        if (isset($objects[0]))
        {
            return $objects[0];
        }
    }
    
    /**
     * Authenticates the user
     * @param $login
     * @param $password
     */
    public static function authenticate($email, $password)
    {
        $customer = self::newFromEmail($email);
        
        if ($customer instanceof Customer)
        {
            if (isset($customer-> password) AND password_verify($password, $customer-> password) )
            {
                if ($customer-> active == 'Y')
                {
                    return TRUE;
                }
                else
                {
                    throw new Exception(_t('Not active account'));
                }
            }
            else
            {
                throw new Exception(_t('Incorrect password'));
            }
        }
        else
        {
            throw new Exception(_t('User not found'));
        }
    }
}
