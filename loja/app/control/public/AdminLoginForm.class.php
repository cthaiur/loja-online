<?php
/**
 * Login form
 *
 * @version    1.0
 * @package    samples
 * @subpackage library
 * @author     Pablo Dall'Oglio <framework@adianti.com.br>
 * @copyright  Copyright (c) 2006-2011 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework/license
 */
class AdminLoginForm extends TPage
{
    protected $form; // formulário
    
    /**
     * método construtor
     * Cria a página e o formulário de cadastro
     */
    function __construct($param)
    {
        parent::__construct();
        
        // instancia um formulário
        $this->form = new BootstrapFormBuilder('form_login');
        $this->form->setFormTitle(_t('Login'));
        
        // cria os campos do formulário
        $email = new TEntry('email');
        $pass = new TPassword('password');
        
        $this->form->addFields( [new TLabel(_t('Email'))], [$email] );
        $this->form->addFields( [new TLabel(_t('Password'))], [$pass] );
        
        $email->setSize('90%');
        $pass->setSize('90%');
        
        $this->form->addAction(_t('Login'), new TAction(array($this, 'onLogin')), 'fa:check-circle green');
        
        $html = new THtmlRenderer('app/resources/admin_form.html');
        $html->enableTranslation();
        $html->enableSection('main', ['form'=> $this->form]);
        
        parent::add($html);
    }
    
    /**
     * Validate the login
     */
    public static function onLogin($param)
    {
        try
        {
            TTransaction::open('store');
            
            $auth = User::authenticate($param['email'], $param['password']);
            
            if ($auth)
            {
                TSession::setValue('logged', TRUE);
                TSession::setValue('login', $param['email'] );
                
                $user = User::newFromEmail($param['email']);
                
                if ($user->role == 'ADMINISTRATOR')
                {
                    TSession::setValue('role', 'ADMINISTRATOR');
                    AdiantiCoreApplication::gotoPage('TransactionList', '');
                }
            }
            TTransaction::close();
        }
        catch (Exception $e) // em caso de exceção
        {
            TSession::setValue('logged', FALSE);
            new TMessage('error', $e->getMessage());
            sleep(2);
        }
    }
}
