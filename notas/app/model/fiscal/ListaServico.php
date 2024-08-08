<?php
/**
 * ListaServico Active Record
 * @author  <your-name-here>
 */
class ListaServico extends TRecord
{
    const TABLENAME = 'lista_servico';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('codigo');
        parent::addAttribute('descricao');
    }
}
