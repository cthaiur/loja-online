<?php

namespace CloudDfe\SdkPHP;

use stdClass;

class Nfe extends Base
{
    /**
     * @param array $payload
     * @return stdClass
     */
    public function cria($payload)
    {
        return $this->client->send('POST', "/nfe", $payload);
    }

    /**
     * @param array $payload
     * @return stdClass
     */
    public function preview($payload)
    {
        return $this->client->send('POST', "/nfe/preview", $payload);
    }

    /**
     * @return stdClass
     */
    public function status()
    {
        return $this->client->send('GET', '/nfe/status', []);
    }

    /**
     * @param array $payload
     * @return stdClass
     * @throws \Exception
     */
    public function consulta($payload)
    {
        $key = self::checkKey($payload);
        return $this->client->send('GET', "/nfe/{$key}", []);
    }

    /**
     * @param array $payload
     * @return stdClass
     */
    public function busca($payload)
    {
        return $this->client->send('POST', "/nfe/busca", $payload);
    }

    /**
     * @param array $payload
     * @return stdClass
     */
    public function cancela($payload)
    {
        return $this->client->send('POST', "/nfe/cancela", $payload);
    }

    /**
     * @param array $payload
     * @return stdClass
     */
    public function correcao($payload)
    {
        return $this->client->send('POST', "/nfe/correcao", $payload);
    }

    /**
     * @param array $payload
     * @return stdClass
     */
    public function inutiliza($payload)
    {
        return $this->client->send('POST', "/nfe/inutiliza", $payload);
    }

    /**
     * @param array $payload
     * @return stdClass
     * @throws \Exception
     */
    public function pdf($payload)
    {
        $key = self::checkKey($payload);
        return $this->client->send('GET', "/nfe/pdf/{$key}", []);
    }

    /**
     * @param array $payload
     * @return stdClass
     */
    public function manifesta($payload)
    {
        return $this->client->send('POST', "/nfe/manifesta", $payload);
    }

    /**
     * @param array $payload
     * @return stdClass
     */
    public function backup($payload)
    {
        return $this->client->send('POST', "/nfe/backup", $payload);
    }

    /**
     * @param array $payload
     * @return stdClass
     * @throws \Exception
     */
    public function download($payload)
    {
        $key = self::checkKey($payload);
        return $this->client->send('GET', "/nfe/download/{$key}", []);
    }

    /**
     * @param array $payload
     * @return stdClass
     */
    public function recebidas($payload)
    {
        return $this->client->send('GET', "/nfe/recebidas", $payload);
    }
}
