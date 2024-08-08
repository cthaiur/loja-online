<?php
require_once 'init.php';

class TApplication extends AdiantiCoreApplication
{
    public static function run($debug = FALSE)
    {
        new TSession;
        
        $lang = TSession::getValue('language') ? TSession::getValue('language') : 'en';
        AdiantiCoreTranslator::setLanguage($lang);
        ApplicationTranslator::setLanguage($lang);
        
        if ($_REQUEST)
        {
            $class = isset($_REQUEST['class']) ? $_REQUEST['class'] : '';
            AdiantiCoreApplication::setRouter(array('AdiantiRouteTranslator', 'translate'));
            
            if (!TSession::getValue('logged') AND !in_array($class, array('LoginForm', 'ConfirmationView', 'ResetPassForm', 'RegisterCustomerForm', 'ProductPublicList', 'PagSeguroNotification', 'AdminLoginForm') ) )
            {
                echo TPage::getLoadedCSS();
                echo TPage::getLoadedJS();
                new TMessage('error', 'Not logged');
                return;
            }
            parent::run($debug);
        }
    }
}

TApplication::run();
