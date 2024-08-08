<?php
/**
 * ListaCnae Active Record
 * @author  <your-name-here>
 */
class ListaCnae extends TRecord
{
    const TABLENAME = 'lista_cnae';
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
        parent::addAttribute('versao');
        parent::addAttribute('ativo');
    }
}
