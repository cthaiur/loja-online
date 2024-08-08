<?php
/**
 * Customer Registration Form
 * @author  <your name here>
 */
class RegisterCustomerForm extends TPage
{
    protected $form; // form
    protected $notebook;
    
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct()
    {
        parent::__construct();
        
        $this->form = new BootstrapFormBuilder('registration_form');
        $this->form->setFormTitle( _t('Register new account') );
        $this->form->setFieldSizes('100%');
        
        $states = array();
        $states['AC'] = 'Acre';
        $states['AL'] = 'Alagoas';
        $states['AM'] = 'Amazonas';
        $states['AP'] = 'Amapá';
        $states['BA'] = 'Bahia';
        $states['CE'] = 'Ceará';
        $states['DF'] = 'Distrito Federal';
        $states['ES'] = 'Espírito Santo';
        $states['GO'] = 'Goiás';
        $states['MA'] = 'Maranhão';
        $states['MG'] = 'Minas Gerais';
        $states['MS'] = 'Mato Grosso do Sul';
        $states['MT'] = 'Mato Grosso';
        $states['PA'] = 'Pará';
        $states['PB'] = 'Paraíba';
        $states['PE'] = 'Pernambuco';
        $states['PI'] = 'Piauí';
        $states['PR'] = 'Paraná';
        $states['RJ'] = 'Rio de Janeiro';
        $states['RN'] = 'Rio Grande do Norte';
        $states['RO'] = 'Rondônia';
        $states['RR'] = 'Roraima';
        $states['RS'] = 'Rio Grande do Sul';
        $states['SC'] = 'Santa Catarina';
        $states['SE'] = 'Sergipe';
        $states['SP'] = 'São Paulo';
        $states['TO'] = 'Tocantins';

        // create the form fields
        $name           = new TEntry('name');
        $email          = new TEntry('email');
        $password       = new TPassword('password');
        $repassword     = new TPassword('repassword');
        $document       = new TEntry('document');
        $phone          = new TEntry('phone');
        $address        = new TEntry('address');
        $number         = new TEntry('number');
        $complement     = new TEntry('complement');
        $neighborhood   = new TEntry('neighborhood');
        $postal         = new TEntry('postal');
        $city           = new TEntry('city');
        $country_id     = new TDBCombo('country_id', 'store', 'Country', 'id', 'name');
        $country_id->enableSearch();
        $action         = new THidden('action');
        $parameter      = new THidden('parameter');

        if (TSession::getValue('language') == 'pt')
        {
            $state = new TCombo('state');
            $state->addItems($states);
            $postal->setMask('99999-999');
            $postal->addValidation(_t('Postal'), new TMinLengthValidator, array(9));
            $phone->addValidation(_t('Phone'), new TMinLengthValidator, array(13));
            $phone->setMask('(99)9999-99999');
            $state->enableSearch();
            
            $postal->setExitAction(new TAction(array($this, 'onExitPostal')));
        }
        else
        {
            $state = new TEntry('state');
        }
        
        $name->setMaxLength(50);
        
        $name->addValidation(_t('Name'), new TFullNameValidator);
        $address->addValidation(_t('Address'), new TMinLengthValidator, array(4));
        $name->addValidation(_t('Name'), new TMinLengthValidator, array(4));
        $city->addValidation(_t('City'), new TMinLengthValidator, array(2));
        
        // define the sizes
        $name->setSize('100%');
        $email->setSize('100%');
        $password->setSize('100%');
        $repassword->setSize('100%');
        $document->setSize('100%');
        $phone->setSize('100%');
        $address->setSize('100%');
        $number->setSize('100%');
        $complement->setSize('100%');
        $neighborhood->setSize('100%');
        $postal->setSize('100%');
        $city->setSize('100%');
        $state->setSize('100%');
        $country_id->setSize('100%');
        
        $name->addValidation(_t('Name'), new TRequiredValidator);
        $email->addValidation(_t('Email'), new TRequiredValidator);
        $password->addValidation(_t('Password'), new TRequiredValidator);
        $repassword->addValidation(_t('Confirm password'), new TRequiredValidator);
        $address->addValidation(_t('Address'), new TRequiredValidator);
        $number->addValidation(_t('Number'), new TRequiredValidator);
        $neighborhood->addValidation(_t('Neighborhood'), new TRequiredValidator);
        $postal->addValidation(_t('Postal'), new TRequiredValidator);
        $city->addValidation(_t('City'), new TRequiredValidator);
        $state->addValidation(_t('State'), new TRequiredValidator);
        $country_id->addValidation(_t('Country'), new TRequiredValidator);
        
        $row = $this->form->addFields( [ new TLabel(_t('Name')),     $name ],
                                       [ new TLabel(_t('Email')),    $email ] );
        $row->layout = ['col-sm-6', 'col-sm-6' ];
        
        $row = $this->form->addFields( [ new TLabel(_t('Password')),     $password ],
                                       [ new TLabel(_t('Confirm password')),    $repassword ] );
        $row->layout = ['col-sm-6', 'col-sm-6' ];
        
        $row = $this->form->addFields( [ new TLabel(_t('Document')), $document ],
                                       [ new TLabel(_t('Phone')),    $phone ] );
        $row->layout = ['col-sm-6', 'col-sm-6' ];
        
        if (TSession::getValue('language') == 'pt')
        {
            $document->addValidation(_t('Document'), new TRequiredValidator);
            $phone->addValidation(_t('Phone'), new TRequiredValidator);
            $country_id->setValue(85);
        }
        
        $row = $this->form->addFields( [ new TLabel(_t('Postal')), $postal ],
                                       [ new TLabel(_t('City')),    $city ],
                                       [ new TLabel(_t('Neighborhood')),    $neighborhood ] );
        $row->layout = ['col-sm-4', 'col-sm-4', 'col-sm-4' ];
        
        $row = $this->form->addFields( [ new TLabel(_t('Address')), $address ],
                                       [ new TLabel(_t('Number')),    $number ],
                                       [ new TLabel(_t('Complement')), $complement ] );
        $row->layout = ['col-sm-4', 'col-sm-4', 'col-sm-4' ];
        
        
        $row = $this->form->addFields( [ new TLabel(_t('State')), $state ],
                                       [ new TLabel(_t('Country')),  $country_id ] );
        $row->layout = ['col-sm-6', 'col-sm-6' ];
        
        $this->form->addFields( [$action], [$parameter] )->style="display:none";

        $btn = $this->form->addAction( _t('Continue'), new TAction(array($this, 'onSave')), 'far:check-circle');
        $btn->class = 'btn btn-success';
        
        // add the form to the page
        parent::add($this->form);
    }
    
    public static function onExitPostal($param)
    {
        session_write_close();
        
        try
        {
            $cep = preg_replace('/[^0-9]/', '', $param['postal']);
            $url = 'https://viacep.com.br/ws/'.$cep.'/json/unicode/';
            
            $content = @file_get_contents($url);
            
            if ($content !== false)
            {
                $cep_data = json_decode($content);
                
                $data = new stdClass;
                if (is_object($cep_data) && empty($cep_data->erro))
                {
                    $data->city = $cep_data->localidade;
                    $data->neighborhood  = $cep_data->bairro;
                    $data->address = $cep_data->logradouro;
                    $data->state = $cep_data->uf;
                    
                    TForm::sendData('registration_form', $data, false, true);
                }
                else
                {
                    $data->city = '';
                    $data->neighborhood  = '';
                    $data->address = '';
                    $data->state = '';
                    
                    TForm::sendData('registration_form', $data, false, true);
                }
            }
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }
    
    public function onLoad($param)
    {
        $obj = new StdClass;
        $obj->email = $param['email'];
        //$obj->name  = $param['name'];
        $obj->action = $param['action'];
        $obj->parameter = $param['parameter'];
        $this->form->setData($obj);
        
        TTransaction::open('store');
        $customer = Customer::newFromEmail($obj->email);
        if ($customer instanceof Customer)
        {
            $action = new TAction(['LoginForm', 'onLoad']);
            new TMessage('info', _t('Email already registered'), $action);
        }
        TTransaction::close();
    }
    
    /**
     * method onSave()
     * Executed whenever the user clicks at the save button
     */
    public function onSave($param)
    {
        try
        {
            // open a transaction with database 'store'
            TTransaction::open('store');
            $object = $this->form->getData('Customer');
            $password = $object->password;
            
            // form validation
            $this->form->validate();
            
            if ($object->password !== $object->repassword)
            {
                throw new Exception(_t('The passwords must be equal'));
            }
            
            if (Customer::newFromEmail($object->email) instanceof Customer)
            {
                throw new Exception(_t('Email already registered'));
            }
            
            $object->password = password_hash($object->password, PASSWORD_DEFAULT);
            $object->active = 'Y';
            
            // stores the object
            $object->store();
            
            $object->password = $password;
            
            // fill the form with the active record data
            $this->form->setData($object);
            
            // close the transaction
            TTransaction::close();
            
            TSession::setValue('logged', TRUE );
            TSession::setValue('login', $object->email );
            TSession::setValue('role', 'CUSTOMER' );
            
            if (!empty($param['action']) && !empty($param['parameter']))
            {
                AdiantiCoreApplication::gotoPage($param['action'], 'onLoad', array('key'=>$param['parameter']));
            }
            else
            {
                TScript::create("__adianti_goto_page('product-list');");
            }
        }
        catch (Exception $e) // in case of exception
        {
            // shows the exception error message
            new TMessage('error', $e->getMessage());
            $object = $this->form->getData();
            $this->form->setData($object);
            
            // undo all pending operations
            TTransaction::rollback();
        }
    }
}
