<?php
/**
 * VendaItem Active Record
 * @author  <your-name-here>
 */
class VendaItem extends TRecord
{
    const TABLENAME = 'venda_item';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('servico_id');
        parent::addAttribute('venda_id');
        parent::addAttribute('valor');
        parent::addAttribute('quantidade');
        parent::addAttribute('total');
    }

    public function get_servico()
    {
        return Servico::find($this->servico_id);
    }

}
