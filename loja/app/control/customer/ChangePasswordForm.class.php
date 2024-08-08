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
class ChangePasswordForm extends TPage
{
    protected $form; // formulário
    
    /**
     * método construtor
     * Cria a página e o formulário de cadastro
     */
    public function __construct()
    {
        parent::__construct();
        
        // security check
        if (TSession::getValue('logged') !== TRUE)
        {
            throw new Exception(_t('Not logged'));
        }
        
        // cria os campos do formulário
        $pass1 = new TPassword('password1');
        $pass2 = new TPassword('password2');
        
        $pass1->addValidation( _t('New password'), new TRequiredValidator );
        $pass2->addValidation( _t('Confirm password'), new TRequiredValidator );
        
        $pass1->setSize('80%');
        $pass2->setSize('80%');
        
        // instancia um formulário
        $this->form = new BootstrapFormBuilder('form_login');
        $this->form->enableCSRFProtection();
        $this->form->setFormTitle(_t('Change password'));
        
        $this->form->addFields( [new TLabel(_t('New password'))], [$pass1]);
        $this->form->addFields( [new TLabel(_t('Confirm password'))], [$pass2]);
        $this->form->addAction( _t('Save'), new TAction(array($this, 'onChangePassword')), 'far:check-circle green');
        
        parent::add($this->form);
    }
    
    /**
     * Saves the new password
     */
    public function onChangePassword()
    {
        try
        {
            TTransaction::open('store');
            $data = $this->form->getData('StdClass');
            
            // validate form data
            $this->form->validate();
        
            $customer = Customer::newFromEmail( TSession::getValue('login') );
            if ($customer instanceof Customer)
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
                throw new Exception(_t('Invalid try'));
            }
            // finaliza a transação
            TTransaction::close();
        }
        catch (Exception $e) // em caso de exceção
        {
            // exibe a mensagem gerada pela exceção
            new TMessage('error', $e->getMessage());
        }
    }
}
