<?php
use CloudDfe\SdkPHP\Nfse;

/**
 * CloudDFE Gateway
 *
 * @version    1.0
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class NotaFiscalCloudDFEGateway implements NotaFiscalServicoInterface
{
    public function configure()
    {
        $ini = require 'app/config/clouddfe.php';
        
        if (strtolower($ini['ambiente']) == 'producao')
        {
            $ambiente = Nfse::AMBIENTE_PRODUCAO;
        }
        else if (strtolower($ini['ambiente']) == 'homologacao')
        {
            $ambiente = Nfse::AMBIENTE_HOMOLOGACAO;
        }
        
        $params = [
            'token' => $ini['token'],
            'ambiente' => $ambiente,
            'options' => [
                'debug' => false,
                'timeout' => 60,
                'port' => 443,
                'http_version' => CURL_HTTP_VERSION_NONE
            ]
        ];
        
        return $params;
    }
    
    /**
     * Transmite a NFE
     * @param $tomador Tomador do serviço
     * @param $servico Serviço prestado
     */
    public function transmite(array $nota_fiscal, array $tomador, array $servico, array $prestador)
    {
        $ini = require 'app/config/clouddfe.php';
        $nfse = new Nfse($this->configure());
        
        $payload = [
            "numero"  => (string) $nota_fiscal['id'],
            "serie"   => $ini['serie'],
            "tipo"    => "1", // 1 = RPS
            "status"  => "1", // 1 = Normal
            "data_emissao"      => date('Y-m-d').'T'.date('H:i:sP'),
            "natureza_operacao" => $nota_fiscal['natureza'],
            "incentivador_cultural" => false,
            "tomador" => [
                "cnpj"         => ($tomador['tipo'] == 'J') ? preg_replace('/[^0-9]/', '', $tomador['codigo_nacional']) : null,
                "cpf"          => ($tomador['tipo'] == 'F') ? preg_replace('/[^0-9]/', '', $tomador['codigo_nacional']) : null,
                "im"           => !empty($tomador['codigo_municipal']) ? preg_replace('/[^0-9]/', '', $tomador['codigo_municipal']) : null,
                "razao_social" => $tomador['nome'],
                "telefone"     => preg_replace('/[^0-9]/', '', $tomador['fone']),
                "email"        => $tomador['email'],
                "endereco"     => [
                    "logradouro"       => $tomador['logradouro'],
                    "numero"           => $tomador['numero'],
                    "complemento"      => $tomador['complemento'],
                    "bairro"           => $tomador['bairro'],
                    "codigo_municipio" => $tomador['codigo_ibge'],
                    "uf"               => $tomador['uf'],
                    "cep"              => preg_replace('/[^0-9]/', '', $tomador['cep'])
                ]
            ],
            "servico" => [
                "iss_retido"         => false,
                "codigo"             => $servico['codigo'], // LC 116/2003
                "descricao"          => $servico['nome'],   // LC 116/2003
                "discriminacao"      => $servico['descricao'],
                "codigo_cnae"        => $servico['cnae'],
                "codigo_municipio"   => ($nota_fiscal['local_servico'] == 'F') ? $tomador['codigo_ibge'] : $prestador['codigo_ibge'],
                "valor_servicos"     => $servico['total'],
                "valor_pis"          => ($servico['valor_pis'] ?? null),
                "valor_cofins"       => ($servico['valor_cofins'] ?? null),
                "valor_inss"         => ($servico['valor_inss'] ?? null),
                "valor_ir"           => ($servico['valor_ir'] ?? null),
                "valor_csll"         => ($servico['valor_csll'] ?? null),
                "valor_iss"          => ($servico['valor_iss'] ?? null),
                "valor_liquido"      => $servico['valor_liquido'],
                "valor_base_calculo" => ($servico['valor_base_calculo'] ?? null),
                "valor_aliquota"     => ($servico['valor_aliquota'] ?? null)
            ]
        ];
        
        $ret = $nfse->cria($payload);
        
        $response = [];
        $response['status'] = 'success';
        
        if ($ret->sucesso)
        {
            $response['data'] = $ret->chave;
            $response['message'] = $ret->mensagem;
            
            if ($ret->codigo == 5023)
            {
                /**
                 * Existem alguns provedores assincronos, necesse cenario a api
                 * sempre ira devolver o codigo 5023, após esse retorno
                 * é necessario buscar a NFSe pela chave de acesso
                 */
                $payload = [
                    'chave' => $ret->chave
                ];
                $ret = $nfse->consulta($payload);
                
                $response['message'] = $ret->mensagem;
            }
        }
        
        if (!$ret->sucesso)
        {
            $response['status']  = 'error';
            $response['data']    = $ret->codigo;
            $response['message'] = $ret->mensagem . '<br>' . TTable::fromData( json_decode(json_encode(array_values($ret->erros)),true), ['class' => 'table'], [ 'style'=>'font-weight:bold'] );
        }
        
        return $response;
    }
    
    /**
     * Cancela NFE
     */
    public function cancela($chave)
    {
        $nfse = new Nfse($this->configure());
        
        $payload = [
            'chave' => $chave,
            'justificativa' => 'Cancelamento por erro de digitação' //minimo de 15 caracteres
        ];
        
        $ret = $nfse->cancela($payload);
        
        $response = [];
        
        if ($ret->sucesso)
        {
            $response['status']  = 'success';
            $response['data']    = $chave;
            $response['message'] = $ret->mensagem;
        }
        else
        {
            $response['status']  = 'error';
            $response['data']    = $ret->codigo;
            $response['message'] = $ret->mensagem . '<br>' . TTable::fromData( json_decode(json_encode(array_values($ret->erros)),true), ['class' => 'table'], [ 'style'=>'font-weight:bold'] );
        }
        
        return $response;
    }
    
    /**
     * Previsualiza NFE
     */
    public function preview($chave)
    {
    }
    
    /**
     * Gera PDF da NFE
     */
    public function imprime($chave)
    {
        $nfse = new Nfse($this->configure());
        
        $payload = [
            'chave' => $chave
        ];
        $ret = $nfse->consulta($payload);
        
        $response = [];
        
        if ($ret->sucesso && !empty($ret->pdf))
        {
            $response['status']  = 'success';
            $response['data']    = $ret->codigo;
            $response['pdf']     = $ret->pdf;
            $response['xml']     = $ret->xml;
            $response['message'] = $ret->mensagem;
        }
        else
        {
            $response['status'] = 'error';
            $response['message'] = $ret->mensagem . '<br>' . ( !empty($ret->erros) ? TTable::fromData( json_decode(json_encode(array_values($ret->erros)),true), ['class' => 'table'], [ 'style'=>'font-weight:bold'] ) : '' );
        }
        
        return $response;
    }
}
