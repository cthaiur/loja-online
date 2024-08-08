<?php
/**
 * Customer Registration Form
 * @author  <your name here>
 */
class CustomerProfileForm extends TWindow
{
    protected $form; // form
    protected $notebook;
    
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    public function __construct()
    {
        parent::__construct();
        parent::setProperty('class', 'window_modal');
        parent::setDialogClass('no-title');
        
        // security check
        if (TSession::getValue('logged') !== TRUE)
        {
            throw new Exception(_t('Not logged'));
        }
        
        parent::setSize(0.7, null);
        parent::setTitle( _t('Customer') );
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_Customer');
        $this->form->setFormTitle( _t('Update profile data') );
        $this->form->setHeaderProperty('style', '; background:#5291d1');
        
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
        $context        = new THidden('context');
        $name           = new TEntry('name');
        $email          = new TEntry('email');
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
        $this->context = $context;
        
        if (TSession::getValue('language') == 'pt')
        {
            $state = new TCombo('state');
            $state->addItems($states);
            $postal->setMask('99999-999');
            $postal->addValidation(_t('Postal'), new TMinLengthValidator, array(9));
            $phone->addValidation(_t('Phone'), new TMinLengthValidator, array(13));
            $phone->setMask('(99)9999-99999');
            $state->enableSearch();
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
        $email->setEditable(FALSE);
        
        $name->addValidation(_t('Name'), new TRequiredValidator);
        $email->addValidation(_t('Email'), new TRequiredValidator);
        $phone->addValidation(_t('Phone'), new TRequiredValidator);
        $address->addValidation(_t('Address'), new TRequiredValidator);
        $document->addValidation(_t('Document'), new TRequiredValidator);
        $number->addValidation(_t('Number'), new TRequiredValidator);
        $neighborhood->addValidation(_t('Neighborhood'), new TRequiredValidator);
        $postal->addValidation(_t('Postal'), new TRequiredValidator);
        $city->addValidation(_t('City'), new TRequiredValidator);
        $state->addValidation(_t('State'), new TRequiredValidator);
        $country_id->addValidation(_t('Country'), new TRequiredValidator);
        
        $this->form->addFields( [ $context ] );
        
        $row = $this->form->addFields( [ new TLabel(_t('Name')),     $name ],
                                       [ new TLabel(_t('Email')),    $email ] );
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

        $btn = $this->form->addAction( _t('Ok, these data are correct'), new TAction(array($this, 'onSave')), 'far:check-circle');
        $btn->class = 'btn btn-success';
        $btn->style = 'font-size: 12pt !important';
        
        // add the form to the page
        parent::add($this->form);
    }
    
    public function onLoad($param)
    {
        try
        {
            TTransaction::open('store');
            $customer = Customer::newFromEmail(TSession::getValue('login'));
            if ($customer instanceof Customer)
            {
                $this->form->setData($customer);
            }
            TTransaction::close();
            
            if (!empty($param['context']))
            {
                $this->context->setValue($param['context']);
            }
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }
    
    /**
     * method onSave()
     * Executed whenever the user clicks at the save button
     */
    public function onSave()
    {
        try
        {
            // open a transaction with database 'store'
            TTransaction::open('store');
            
            // form validation
            $this->form->validate();
            
            $customer = Customer::newFromEmail(TSession::getValue('login'));
            if ($customer instanceof Customer)
            {
                $data = $this->form->getData();
                
                $customer->name = $data->name;
                $customer->document = $data->document;
                $customer->phone = $data->phone;
                $customer->address = $data->address;
                $customer->number = $data->number;
                $customer->complement = $data->complement;
                $customer->neighborhood = $data->neighborhood;
                $customer->postal = $data->postal;
                $customer->city = $data->city;
                $customer->state = $data->state;
                $customer->country_id = $data->country_id;
                $customer->store();
            }
            TTransaction::close();
            
            // fill the form with the active record data
            $this->form->setData($customer);
            
            if ($data->context == 'confirmation-form')
            {
                TScript::create("__adianti_post_data('form_confirm_transaction', 'class=ConfirmationForm&method=onSaveTransaction');");
            }
            else
            {
                TScript::create('window.location.reload();');
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
