<?php
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\ProductionEnvironment;

class PaymentFacade
{
    public static function processPayment(Transaction $transaction)
    {
        $host     = 'http://'.$_SERVER['HTTP_HOST'];
        $product  = $transaction->product;
        $customer = $transaction->customer;
        
        TSession::setValue('current-transaction-id', $transaction->id);
        
        if ($transaction->paymenttype_id == 1) // PAGSEGURO
        {
            $ini = require 'app/config/pagseguro.php';
            \PagSeguro\Library::initialize();
            
            $document = str_replace(['.', '/'], ['', ''], $customer->document);
            $doc_type = strlen($document) >= 14 ? 'CNPJ' : 'CPF';
            
            $payment = new \PagSeguro\Domains\Requests\Payment();
            $payment->addItems()->withParameters( $product->id, $product->description, $transaction->quantity, $transaction->value );
            $payment->setCurrency("BRL");
            $payment->setReference(APPLICATION_NAME . '_' . $transaction->id);
            $payment->setRedirectUrl("$host/loja/transaction_pagseguro_end.html");
            $payment->setSender()->setName( trim(str_replace('  ', ' ', $customer->name )) );
            $payment->setSender()->setEmail( trim($customer->email) );
            $payment->setSender()->setPhone()->withParameters( substr(preg_replace("/[^0-9]/","", $customer->phone), 0, 2), trim(substr(preg_replace("/[^0-9]/","", $customer->phone),2) ) );
            $payment->setSender()->setDocument()->withParameters( $doc_type, $document );
            
            /*
            $payment->setShipping()->setAddress()->withParameters(
                $customer->address,
                $customer->number,
                $customer->neighborhood,
                $customer->postal,
                $customer->city,
                $customer->state,
                $customer->country->iso3,
                $customer->complement
            );
            */
            
            /*
            $payment->acceptPaymentMethod()->groups(
                \PagSeguro\Enum\PaymentMethod\Group::CREDIT_CARD,
                \PagSeguro\Enum\PaymentMethod\Group::BALANCE
            );
            */
            \PagSeguro\Configuration\Configure::setAccountCredentials($ini['account'], $ini['token']);
            
            $url = $payment->register( \PagSeguro\Configuration\Configure::getAccountCredentials() );
            
            $info = new stdClass;
            $info->url = $url;
            
            return $info;
        }
        else if ($transaction->paymenttype_id == 2) // PAYPAL
        {
            $ini = require 'app/config/paypal.php';
            
            $payload = [
                'intent' => 'CAPTURE',
                'application_context' =>
                    [
                        'return_url' => "$host/loja/transaction_paypal_end.html",
                        'cancel_url' => "$host/loja/transaction_paypal_cancel.html",
                        'brand_name' => $host,
                        'locale' => 'pt-BR',
                        'landing_page' => 'BILLING',
                        'user_action' => 'PAY_NOW',
                    ],
                'purchase_units' =>
                    [
                        [
                            'reference_id' => APPLICATION_NAME . '_' . $transaction->id,
                            'description' => $product->description,
                            'amount' =>
                                [
                                    'currency_code' => ($product->currency == 'U$') ? 'USD' : 'BRL',
                                    'value' => $transaction->total
                                ]
                        ]
                    ]
            ];
            
            $client  = new PayPalHttpClient(new ProductionEnvironment($ini['client_id'], $ini['client_secret']));
            $request = new OrdersCreateRequest();
            $request->headers["prefer"] = "return=representation";
            $request->body = $payload;
            
            $response = $client->execute($request);
            
            // print "Status Code: {$response->statusCode}\n";
            // print "Status: {$response->result->status}\n";
            // print "Order ID: {$response->result->id}\n";
            // print "Intent: {$response->result->intent}\n";
            
            foreach($response->result->links as $link)
            {
                if ($link->rel == 'approve')
                {
                    $url = $link->href;
                }
            }
            
            $transaction->token = $response->result->id;
            $transaction->store();
            
            $info = new stdClass;
            $info->url = $url;
            return $info;
        }
        else if ($transaction->paymenttype_id == 3) // TED 
        {
            $transaction->paymentstatus_id = 1;
            $transaction->store();
            
            $info = new stdClass;
            $info->url = 'ted-info';           
            return $info;
        }
    }
}
