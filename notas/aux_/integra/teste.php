<?php
if (PHP_SAPI !== 'cli')
{
    die ('Access denied');
}
require_once 'request.php';

try
{
    $config = require_once 'app/config/config.php';
    $location = $config['loja_host'];
    
    $parameters = array();
    $parameters['class']  = 'CustomerService';
    //$parameters['class']  = 'TransactionViewService';
    $parameters['method'] = 'loadAll';
    $parameters['order']  = 'id';
    
    $data = request($location, 'POST', $parameters, 'Basic:'.$config['rest_key']);
    
    print($location);
    echo "\n";
    print_r($data);
}
catch (Exception $e)
{
    echo $e->getMessage();
}
