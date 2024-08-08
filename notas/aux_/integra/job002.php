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
    $result = TDatabase::execute($target, "SELECT max(id) from venda");
    
    // pego novamente as ultimas X, pq alguma pode ter mudado o status de pgto (wating payment ->paid)
    foreach ($result as $row)
    {
        $max = (int) $row[0] - 200;
    }
    
    $product_map = [];
    $product_map[1] = 1;
    $product_map[2] = 2;
    
    $parameters = array();
    $parameters['class']  = 'TransactionViewService';
    $parameters['method'] = 'loadAll';
    $parameters['order']  = 'id';
    $parameters['filters'] = [ ['paymentstatus_id', 'IN', [3,4,8]], ['id', '>', $max], [ 'product_id', 'IN', array_keys($product_map) ] ];
    
    $data = request($location, 'POST', $parameters, 'Basic:'.$config['rest_key']);
    
    foreach ($data as $transaction)
    {
        $venda = [];
        $venda['id']              = $transaction->id;
        $venda['cliente_id']      = $transaction->customer_id;
        $venda['dt_venda']        = $transaction->operation_date;
        $venda['total']           = $transaction->total - $transaction->shipping_cost;
        
        if ($venda['total'] == 0)
        {
            $venda['cancelada'] = 'Y';
        }
        
        $criteria_avoid = new TCriteria;
        $criteria_avoid->add(new TFilter('id', '=', $transaction->id));
        
        $inserted = TDatabase::insertData($target, 'venda', $venda, $criteria_avoid);
        if (!is_null($inserted))
        {
            $new_item = [];
            $new_item['id']         = $transaction->id;
            $new_item['quantidade'] = $transaction->quantity;
            $new_item['valor']      = $transaction->value;
            $new_item['total']      = $transaction->quantity * $transaction->value;
            $new_item['servico_id'] = $product_map[$transaction->product_id];
            $new_item['venda_id']   = $venda['id'];
            TDatabase::insertData($target, 'venda_item', $new_item);
        }
    }
    
    // close the transaction
    TTransaction::close();

    print "JOB " . basename(__FILE__) . " ran successfully \n";
}
catch (Exception $e)
{
    echo $e->getMessage();
}
