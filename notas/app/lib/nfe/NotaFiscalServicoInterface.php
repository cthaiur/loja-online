<?php
/**
 * Nota Fiscal Servico Interface
 *
 * @version    1.0
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
interface NotaFiscalServicoInterface
{ 
    public function transmite(array $nota_fiscal, array $tomador, array $servico, array $prestador);
    public function cancela($chave);
    public function preview($chave);
    public function imprime($chave);
}