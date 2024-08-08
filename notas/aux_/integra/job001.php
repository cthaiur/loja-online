<?php
if (PHP_SAPI !== 'cli')
{
    die ('Access denied');
}

date_default_timezone_set('America/Sao_Paulo');
require_once 'phar://adianti-db-5.7.phar.gz';
require_once 'request.php';

try
{
    $config = require_once 'app/config/config.php';
    $location = $config['loja_host'];
    
    $target = TTransaction::open('target');
    TTransaction::setLogger(new TLoggerSTD); // or TLoggerTXT('/tmp/log.txt');
    $result = TDatabase::execute($target, "SELECT max(id) from pessoa");
    foreach ($result as $row)
    {
        $max = $row[0];
    }
    
    if (is_null($max))
    {
        $max = 0;
    }
    
    $parameters = array();
    $parameters['class']  = 'CustomerService';
    $parameters['method'] = 'loadAll';
    $parameters['order']  = 'id';
    $parameters['filters'] = [ ['id', '>', $max] ];
    
    $data = request($location, 'POST', $parameters, 'Basic:'.$config['rest_key']);
    
    foreach ($data as $object)
    {
        $row = (array) $object;
        $avoid_pessoa = new TCriteria;
        $avoid_pessoa->add(new TFilter('id', '=', $row['id']));
        
        $dado_pessoa = [];
        $dado_pessoa['id'] = $row['id'];
        $dado_pessoa['nome'] = $row['name'];
        $dado_pessoa['nome_fantasia'] = $row['name'];
        $dado_pessoa['tipo'] = strlen(preg_replace('/[^0-9]/', '', $row['document'])) > 11 ? 'J' : 'F';
        $dado_pessoa['codigo_nacional'] = $row['document'];
        $dado_pessoa['fone'] = $row['phone'];
        $dado_pessoa['email'] = $row['email'];
        $dado_pessoa['created_at'] = date('Y-m-d');
        $dado_pessoa['updated_at'] = date('Y-m-d');
        $dado_pessoa['logradouro'] = $row['address'];
        $dado_pessoa['bairro'] = $row['neighborhood'];
        $dado_pessoa['numero'] = $row['number'];
        $dado_pessoa['complemento'] = $row['complement'];
        $dado_pessoa['cep'] = str_replace(['-', '.'], ['', ''], $row['postal']);
        
        if (!empty($row['postal']))
        {
            $inicio_cep = substr($row['postal'],0,5);
            $result = TDatabase::execute($target, "SELECT cod_cidade as ibge FROM cep where cep='{$inicio_cep}'");
            foreach ($result as $row_cep)
            {
                $dado_pessoa['cidade_id'] = $row_cep['ibge'];
            }
        }
        
        TDatabase::insertData($target, 'pessoa', $dado_pessoa, $avoid_pessoa);
    }
    
    TTransaction::close();
    
    print "JOB " . basename(__FILE__) . " ran successfully \n";
}
catch (Exception $e)
{
    echo $e->getMessage();
}
