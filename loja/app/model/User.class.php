<?php
/**
 * User Active Record
 * @author  <your-name-here>
 */
class User extends TRecord
{
    const TABLENAME = 'eco_user';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('name');
        parent::addAttribute('email');
        parent::addAttribute('password');
        parent::addAttribute('role');
    }
    
    /**
     * Static loader
     * Returns the user from email
     * @param $login
     */
    static public function newFromEmail($email)
    {
        $repos = new TRepository('User');
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
        $user = self::newFromEmail($email);
        
        if ($user instanceof User)
        {
            if (isset($user-> password) AND password_verify($password, $user-> password) )
            {
                return TRUE;
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
