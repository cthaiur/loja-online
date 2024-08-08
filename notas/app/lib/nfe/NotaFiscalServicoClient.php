<?php
/**
 * Nota Fiscal Servico Client
 *
 * @version    1.0
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class NotaFiscalServicoClient
{
    private $gateway;
    
    /**
     * Cria o client
     * @param $gateway Objeto gateway de transmissão de NFSE
     */
    public function __construct(NotaFiscalServicoInterface $gateway)
    {
        $this->gateway = $gateway;
    }
    
    /**
     * Transmite a NFE
     * @param $nf Objeto NotaFiscal
     */
    public function transmite(NotaFiscal $nota_fiscal_obj)
    {
        $tomador_obj   = $nota_fiscal_obj->cliente;
        $tomador       = $tomador_obj->toArray();
        $prestador_obj = Pessoa::find(1);
        $prestador     = $prestador_obj->toArray();
        $nota_fiscal   = $nota_fiscal_obj->toArray();
        
        // colunas virtuais adicionas ao vetor
        $tomador['codigo_ibge']   = $tomador_obj->cidade->codigo_ibge;
        $tomador['uf']            = $tomador_obj->cidade->estado->uf;
        $prestador['codigo_ibge'] = $prestador_obj->cidade->codigo_ibge;
        $prestador['uf']          = $prestador_obj->cidade->estado->uf;
        $nota_fiscal['natureza']  = $nota_fiscal_obj->natureza_operacao->codigo;
        
        $itens = $nota_fiscal_obj->itens;
        if ($itens)
        {
            $servico = $itens[0]->toArray();
            $servico['descricao'] = $itens[0]->servico->nome;
            $servico['cnae']      = $itens[0]->servico->tipo_servico->lista_cnae->codigo;
            $servico['codigo']    = $itens[0]->servico->tipo_servico->lista_servico->codigo;  // LC 116/2003
            $servico['nome']      = substr($itens[0]->servico->tipo_servico->lista_servico->descricao,0,60);  // LC 116/2003
            $servico['total']     = $nota_fiscal_obj->total;
            
            $impostos = $itens[0]->servico->tipo_servico->impostos;
            
            $valor_retencoes = 0;
            
            if ($impostos)
            {
                foreach ($impostos as $imposto)
                {
                    if ($imposto->aliquota > 0 && (empty($imposto->minimo) || $imposto->minimo > $nota_fiscal_obj->total))
                    {
                        $retencao = $nota_fiscal_obj->total * ($imposto->aliquota/100);
                        $servico['valor_'.$imposto->imposto] = $retencao;
                        
                        if ($imposto->imposto !== 'iss') // ISS não desconta, pois não é retenção.
                        {
                            $valor_retencoes += $retencao;
                        }
                    }
                    
                    if ($imposto->imposto == 'iss')
                    {
                        $servico['valor_aliquota'] = $imposto->aliquota;
                    }
                }
            }
            
            $servico['valor_liquido'] = $nota_fiscal_obj->total;
            $servico['valor_base_calculo'] = $nota_fiscal_obj->total;
            
            if ($valor_retencoes > 0)
            {
                $servico['valor_liquido'] = $nota_fiscal_obj->total - $valor_retencoes;
            }
            
            $response = $this->gateway->transmite($nota_fiscal, $tomador, $servico, $prestador);
            
            if ($response['status'] == 'success')
            {
                $nota_fiscal_obj->chave = $response['data'];
                $nota_fiscal_obj->transmitida = 'Y';
                $nota_fiscal_obj->store();
            }
            
            return $response;
        }
    }
    
    /**
     * Visualiza a NFE
     */
    public function imprime(NotaFiscal $nota_fiscal_obj)
    {
        return $this->gateway->imprime($nota_fiscal_obj->chave);
    }
    
    /**
     * Cancela a NFE
     */
    public function cancela(NotaFiscal $nota_fiscal_obj)
    {
        return $this->gateway->cancela($nota_fiscal_obj->chave);
    }
    
    /**
     * Visualiza a NFE
     */
    public function visualiza()
    {
        $this->gateway->visualiza();
    }
}
