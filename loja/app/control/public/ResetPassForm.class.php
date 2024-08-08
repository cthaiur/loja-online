<?php
/**
 * New password form
 *
 * @version    1.0
 * @package    samples
 * @subpackage library
 * @author     Pablo Dall'Oglio <framework@adianti.com.br>
 * @copyright  Copyright (c) 2006-2011 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework/license
 */
class ResetPassForm extends TPage
{
    protected $form; // formulário
    
    /**
     * método construtor
     * Cria a página e o formulário de cadastro
     */
    function __construct()
    {
        parent::__construct();
        
        // instancia um formulário
        $this->form = new BootstrapFormBuilder('form_login');
        $this->form->setFormTitle( _t('Reset password') );
        
        // cria os campos do formulário
        $email = new TEntry('email');
        $pass1 = new TPassword('password1');
        $pass2 = new TPassword('password2');
        $hash  = new THidden('hash');
        $email->setEditable(FALSE);
        
        $this->form->addFields( [new TLabel(_t('Email'))], [$email] );
        $this->form->addFields( [new TLabel(_t('New password'))], [$pass1] );
        $this->form->addFields( [new TLabel(_t('Confirm password'))], [$pass2] );
        $this->form->addFields( [$hash] )->style = 'display:none';
        
        $this->form->addAction( _t('Save'), new TAction(array($this, 'onChangePassword')), 'far:check-circle green');
        
        parent::add($this->form);
    }
    
    public function onLoad($param)
    {
        $obj = new StdClass;
        $obj->email = $param['email'];
        $obj->hash  = $param['hash'];
        $this->form->setData($obj);
    } 
    
    /**
     * Saves the new password
     */
    function onChangePassword()
    {
        try
        {
            $config = require 'app/config/config.php';
            
            TTransaction::open('store');
            $data = $this->form->getData('StdClass');
            
            // validate form data
            $this->form->validate();
        
            $customer = Customer::newFromEmail($data-> email);
            if ($customer instanceof Customer)
            {
                $customer_array = $customer->toArray();
                
                $serial_data = [];
                $serial_data['name']     = $customer_array['name'];
                $serial_data['email']    = $customer_array['email'];
                $serial_data['document'] = $customer_array['document'];
                
                $hash = hash('sha512', serialize($serial_data).date('Ymd').$config['seed']);
                
                if ($hash == $data-> hash)
                {
                    if ($data->password1 == $data->password2)
                    {
                        $customer->password = password_hash($data->password1, PASSWORD_DEFAULT);
                        $customer->store();
                        new TMessage('info', _t('Password defined'));
                    }
                    else
                    {
                        throw new Exception(_t('The passwords do not match'));
                    }
                }
                else
                {
                    throw new Exception(_t('This form has expired. Please, try again'));
                }
            }
            else
            {
                throw new Exception(_t('Invalid try'));
            }
            // finaliza a transação
            TTransaction::close();
        }
        catch (Exception $e) // em caso de exceção
        {
            TSession::setValue('logged', FALSE);
            
            // exibe a mensagem gerada pela exceção
            new TMessage('error', $e->getMessage());
        }
    }
    
    /**
     * método onLogout
     * Executado quando o usuário clicar no botão logout
     */
    function onLogout()
    {
        TSession::setValue('logged', FALSE);
        TApplication::executeMethod('LoginForm', '');
    }
}
