<?php
date_default_timezone_set('America/Sao_Paulo');
require_once 'request.php';
chdir ('/var/www/html/php');
require_once 'init.php';

try
{
    $target = TTransaction::open('scubi');
    TTransaction::setLogger(new TLoggerSTD);
    $result = TDatabase::execute($target, "SELECT max(id) from venda");
    // pego novamente as ultimas 100, pq alguma pode ter mudado o status de pgto (wating payment ->paid)
    foreach ($result as $row)
    {
        $max = (int) $row[0] - 200;
    }

    $location = 'https://php.com.br/store/rest.php';
    $parameters = array();
    $parameters['class']  = 'TransactionService';
    $parameters['method'] = 'loadAll';
    $parameters['order']  = 'id';
    $parameters['filters'] = [ ['paymentstatus_id', 'IN', [3,4,8]], ['id', '>', $max] ];
    $parameters['token']  = 'fas98dfb6asoid7fbnc36he.gpsx;2hd3e.srigntd456124clp';
    $data = request($location, 'POST', $parameters);

    $product_map = [];
    $product_map[1]  = [new Produto(3, false)]; // curso

    $obs_software  = [0.0365, 'Sem tributação de ICMS com base no Livro V, artigo 35 do RICMS/RS. Valor aproximado dos tributos R$ {imposto_reais} ({imposto_percentual}%)'];
    $obs_curso_dig = [0.0365, 'Sem tributação de ICMS com base no Livro V, artigo 35 do RICMS/RS. Valor aproximado dos tributos R$ {imposto_reais} ({imposto_percentual}%)'];
    $obs_livro_fis = [0.3209, 'Alíquota de PIS e COFINS conforme Lei 10.865/2004, art.28, inc.VI. Valor aproximado dos tributos R$ {imposto_reais} ({imposto_percentual}%) Fonte: IBPT'];
    $obs_livro_dig = [0.0365, 'Não incidência do ICMS conforme Livro I, art. 11, II do RICMS/RS. Valor aproximado dos tributos R$ {imposto_reais} ({imposto_percentual}%)']; 

    $obs_map = [];
    $obs_map[1]  = $obs_curso_dig;

    foreach ($data as $transaction)
    {
        $venda = [];
        $venda['id']              = $transaction->id;
        $venda['cliente_id']      = $transaction->customer_id;
        $venda['dt_venda']        = $transaction->operation_date;
        $venda['hora']            = '12:00';
        $venda['id_origem']       = $transaction->id;
        $venda['origem']          = 'Venda';
        $venda['estado_venda_id'] = '1';
        $venda['tipo_venda_id']   = '1';
        $venda['obs_fiscal']      = $obs_map[$transaction->product_id][1];
        $venda['endereco_id']     = $transaction->customer_id;
        $valor_total_sem_frete    = $transaction->total - $transaction->shipping_cost;
        $percentual_imposto       = $obs_map[$transaction->product_id][0];

        $venda['obs_fiscal'] = str_replace('{imposto_percentual}', round($percentual_imposto*100,2), $venda['obs_fiscal']);
        $venda['obs_fiscal'] = str_replace('{imposto_reais}', round($percentual_imposto*$valor_total_sem_frete,2), $venda['obs_fiscal']);

        if ($transaction->shipping_cost)
        {
            $venda['frete'] = $transaction->shipping_cost;
            $venda['frete_conta'] = '0';
        }
        else
        {
            $venda['frete_conta'] = '9';
        }

        $criteria_avoid = new TCriteria;
        $criteria_avoid->add(new TFilter('id', '=', $transaction->id));
        $inserted = TDatabase::insertData($target, 'venda', $venda, $criteria_avoid);
        if (!is_null($inserted))
        {
            $total = $transaction->total;
            
            if (count($product_map[$transaction->product_id]) == 1)
            {
                $produto = $product_map[$transaction->product_id][0];
                
                $new_item = [];
                $new_item['qtde'] = $transaction->quantity;
                $new_item['valor'] = $transaction->value;
                $new_item['produto_id'] = $produto->id;
                $new_item['venda_id'] = $venda['id'];
                TDatabase::insertData($target, 'item_venda', $new_item);
            }
            else
            {
                $total_produtos = 0;
                foreach ($product_map[$transaction->product_id] as $site_product_id => $produto)
                {
                    $total_produtos += $produto->preco_venda * $transaction->quantity;
                }
               
                $proporcao = $total / $total_produtos;
                $count = 0;
                $subtotal = 0;
                foreach ($product_map[$transaction->product_id] as $site_product_id => $produto)
                {
                    $new_item = [];
                    $new_item['qtde'] = $transaction->quantity;
                    $new_item['valor'] = round(($produto->preco_venda * $proporcao), 2);
                    $new_item['produto_id'] = $produto->id;
                    $new_item['venda_id'] = $venda['id'];
                    
                    
                    $subtotal += $new_item['valor'];
                    $count ++;
                    
                    // calcula diferença de arredondamento
                    if ($count == count($product_map[$transaction->product_id]))
                    {
                        $new_item['valor'] += ($total - $subtotal);
                    }
                    
                    TDatabase::insertData($target, 'item_venda', $new_item);
                }
            }
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

