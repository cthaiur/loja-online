<?php
/**
 * TipoServicoImpostos Active Record
 * @author  <your-name-here>
 */
class TipoServicoImposto extends TRecord
{
    const TABLENAME = 'tipo_servico_impostos';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('tipo_servico_id');
        parent::addAttribute('imposto');
        parent::addAttribute('aliquota');
        parent::addAttribute('minimo');
    }


}
