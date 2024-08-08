<?php
/**
 * NotaFiscal Active Record
 * @author  <your-name-here>
 */
class NaturezaOperacao extends TRecord
{
    const TABLENAME = 'natureza_operacao';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('codigo');
        parent::addAttribute('nome');
        parent::addAttribute('local_servico');
        parent::addAttribute('padrao');
    }
}