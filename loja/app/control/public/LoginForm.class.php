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
class LoginForm extends TPage
{
    protected $loginForm; // formulário
    protected $reminderForm;
    protected $registerForm;
    
    /**
     * método construtor
     * Cria a página e o formulário de cadastro
     */
    function __construct($param)
    {
        parent::__construct();
        
        // instancia um formulário
        $this->loginForm = new BootstrapFormBuilder('form_login');
        $this->reminderForm = new BootstrapFormBuilder('form_reminder');
        $this->registerForm = new BootstrapFormBuilder('form_register');
        
        $this->registerForm->setHeaderProperty('style', '; background:#56d07c');
        $this->reminderForm->setHeaderProperty('style', '; background:#deb158');
        
        $this->loginForm->setFormTitle(_t('Login'));
        $this->registerForm->setFormTitle(_t('Register new account'));
        $this->reminderForm->setFormTitle(_t('Forgot your password?'));
        
        // cria os campos do formulário
        $email = new TEntry('email');
        $pass = new TPassword('password');
        
        
        $this->loginForm->addFields( [new TLabel(_t('Email'))], [$email] );
        $this->loginForm->addFields( [new TLabel(_t('Password'))], [$pass] );
        $this->loginForm->addFields( [new THidden('action')] )->style="display:none";
        $this->loginForm->addFields( [new THidden('parameter')] )->style="display:none";
        $this->loginForm->addAction(_t('Login'), new TAction(array($this, 'onLogin')), 'fa:check-circle green');
        $email->setSize('90%');
        $pass->setSize('90%');
        
        
        $this->reminderForm->addFields( [new TLabel(_t('Email'))], [$email_remind=new TEntry('email')]);
        $this->reminderForm->addAction(_t('Remind me'), new TAction(array($this, 'onRemind')), 'fa:envelope blue');
        $email_remind->setSize('90%');
        
        
        $this->registerForm->addFields( [new TLabel(_t('Email'))], [$email_register=new TEntry('email')]);
        $this->registerForm->addFields( [new THidden('action')])->style="display:none";
        $this->registerForm->addFields( [new THidden('parameter')])->style="display:none";
        $this->registerForm->addAction(_t('Continue'), new TAction(array('RegisterCustomerForm', 'onLoad')), 'far:arrow-alt-circle-right blue');
        $email_register->setSize('90%');
        
        
        $html = new THtmlRenderer('app/resources/login_form.html');
        $html->enableTranslation();
        $html->enableSection('main', ['loginForm'=>$this->loginForm,
                                      'reminderForm'=>$this->reminderForm,
                                      'registerForm'=>$this->registerForm]);
        
        if (isset($param['parameter'])) // código do produto
        {
            $html->enableSection('steps');
        }
        
        parent::add($html);
    }
    
    public function onLoad($param)
    {
        $obj = new StdClass;
        $obj->action    = isset($param['action']) ? $param['action'] : NULL;
        $obj->parameter = isset($param['parameter']) ? $param['parameter'] : NULL;
        
        $this->loginForm->setData($obj);
        $this->registerForm->setData($obj);
    }
    
    /**
     * Validate the login
     */
    public static function onLogin($param)
    {
        try
        {
            TTransaction::open('store');
            
            $auth = Customer::authenticate( $param['email'], $param['password']);
            
            if ($auth)
            {
                TSession::setValue('logged', TRUE);
                TSession::setValue('login', $param['email'] );
                TSession::setValue('role', 'CUSTOMER');
                
                // reload page
                if ($param['action'])
                {
                    AdiantiCoreApplication::gotoPage($param['action'], 'onLoad', array('key'=>$param['parameter']));
                }
                else
                {
                    AdiantiCoreApplication::gotoPage('ProductPublicList', '');
                    TScript::create("__adianti_goto_page('product-list');");
                }
            }
            
            TTransaction::close();
            // finaliza a transação
        }
        catch (Exception $e) // em caso de exceção
        {
            TSession::setValue('logged', FALSE);
            new TMessage('error', $e->getMessage());
            sleep(2);
        }
    }
    
    /**
     * Password reminder
     */
    public function onRemind()
    {
        try
        {
            $config = require 'app/config/config.php';
            
            $data = $this->reminderForm->getData('StdClass');
            TTransaction::open('store');
            $customer = Customer::newFromEmail($data-> email);
            TTransaction::close();
            
            if ($customer instanceof Customer)
            {
                $customer_array = $customer->toArray();
                
                $serial_data = [];
                $serial_data['name']     = $customer_array['name'];
                $serial_data['email']    = $customer_array['email'];
                $serial_data['document'] = $customer_array['document'];
                
                $hash = hash('sha512', serialize($serial_data).date('Ymd').$config['seed']);
                
                $host = 'http://'.$_SERVER['HTTP_HOST'];
                
                $message = _t('Hello') . ' ' . $customer->name . "\n".
                           _t('Click here to reset your password'). ":\n" .
                           "{$host}/loja/index.php?class=ResetPassForm&method=onLoad&email={$customer->email}&hash={$hash}";
                
                MailService::send([$customer->email], _t('Reset password'), $message, 'text');
                
                new TMessage('info', _t('Password reminder sent'));
            }
            else
            {
                new TMessage('error', _t('User not found'));
            }
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }
    
    /**
     * método onLogout
     * Executado quando o usuário clicar no botão logout
     */
    public function onLogout()
    {
        TSession::setValue('logged', FALSE);
        TApplication::executeMethod('LoginForm', '');
    }
}
