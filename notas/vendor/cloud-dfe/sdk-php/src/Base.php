<?php

namespace CloudDfe\SdkPHP;

class Base
{
    /**
     * @var Client
     */
    protected $client;

    const AMBIENTE_PRODUCAO = 1;
    const AMBIENTE_HOMOLOGACAO = 2;

    /**
     * Base constructor.
     * @param array $params
     * @throws \Exception
     */
    public function __construct($params)
    {
        $this->client = new Client([
            'ambiente' => !empty($params['ambiente']) ? $params['ambiente'] : self::AMBIENTE_HOMOLOGACAO,
            'token' => $params['token'],
            'options' => $params['options']
        ], 'api');
    }

    /**
     * Verifica a chave
     * @param array $payload
     * @return array|string|string[]
     * @throws \Exception
     */
    protected static function checkKey($payload)
    {
        $key = preg_replace("/[^0-9]/", "", $payload['chave']);
        if (empty($key) || strlen($key) != 44) {
            throw new \Exception('A chave para gerar o PDF deve ter 44 digitos numericos');
        }
        return $key;
    }
}
