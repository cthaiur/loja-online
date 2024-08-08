<?php
require_once 'init.php';

$site_config    = parse_ini_file('../config/config.ini');
$site_template  = $site_config['site_template'];
$admin_template = 'theme1';

$class      = isset($_REQUEST['class']) ? $_REQUEST['class'] : '';
$method     = isset($_REQUEST['method']) ? $_REQUEST['method'] : NULL;
$parameters = $_REQUEST;

// obtém template e injeta o núcleo da loja

$content   = file_get_contents("../templates/{$site_template}/home_store.html");
$content   = str_replace('{page_content}', file_get_contents('app/resources/nucleo.html'), $content);
$content   = str_replace('{theme}', $site_template, $content);

// $content   = file_get_contents("app/templates/{$admin_template}/layout.html");

$protocol   = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
$blog_host  = $protocol.$_SERVER['SERVER_NAME'].dirname(dirname( $_SERVER['PHP_SELF']));
$categories = str_replace('./', '../', file_get_contents($blog_host . '/index.php?action=render_categories'));
$content    = str_replace('{categories}', $categories, $content); // ajustar links para raíz do site

if (TSession::getValue('logged'))
{
    TTransaction::open('store');
    $user = User::newFromEmail(TSession::getValue('login'));
    
    if (TSession::getValue('role') == 'CUSTOMER')
    {
        $menu = file_get_contents("app/resources/menu_customer.html");
    }
    else if (TSession::getValue('role') == 'ADMINISTRATOR')
    {
        $menu = file_get_contents("app/resources/menu_admin.html");
    }
    
    $content = str_replace('{navigation}', $menu, $content);
    
    TTransaction::close();
}
else
{
    $menu = file_get_contents("app/resources/menu_public.html");
    $content = str_replace('{navigation}', $menu, $content);
    
    if (isset($_REQUEST['class']))
    {
        if (!in_array($_REQUEST['class'], ['ConfirmationView', 'ResetPassForm', 'RegisterCustomerForm', 'ProductPublicList', 'PagSeguroNotification', 'AdminLoginForm'] ) )
        {
            // este passo só é necessário por que o LoginForm recebe parâmetros
            if (isset($parameters['class']) AND $parameters['class'] !== 'LoginForm')
            {
                $parameters['action']    = $parameters['class'];
                $parameters['parameter'] = isset($parameters['key']) ? $parameters['key'] : NULL;
                $method    = 'onLoad';
                unset($parameters['key']);
            }
            $class  = 'LoginForm';
        }
    }
    else
    {
        $class  = 'ProductPublicList';
    }
}

// define o tradutor de rotas
AdiantiCoreApplication::setRouter( ['AdiantiRouteTranslator', 'translate'] );

$content  = ApplicationTranslator::translateTemplate($content);
$content  = str_replace('{LIBRARIES}', file_get_contents("app/templates/{$admin_template}/libraries.html"), $content);
$content  = str_replace('{site_title}', $site_config['site_title'], $content);
$content  = str_replace('{class}', isset($_REQUEST['class']) ? $_REQUEST['class'] : '', $content);
$content  = str_replace('{template}', $admin_template, $content);
$content  = str_replace('{lang}', LANG, $content);

print $content;

AdiantiCoreApplication::loadPage($class, $method, $parameters);
