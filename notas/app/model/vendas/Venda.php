<?php
/**
 * Venda Active Record
 * @author  <your-name-here>
 */
class Venda extends TRecord
{
    const TABLENAME = 'venda';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('cliente_id');
        parent::addAttribute('dt_venda');
        parent::addAttribute('total');
        parent::addAttribute('faturada');
        parent::addAttribute('cancelada');
        parent::addAttribute('nota_fiscal_id');
    }
    
    public function get_itens_string()
    {
        $output = [];
        $itens = VendaItem::where('venda_id', '=', $this->id)->load();
        if ($itens)
        {
            foreach ($itens as $item)
            {
                $output[] = $item->servico->nome;
            }
        }
        
        return implode('<br>', $output);
    }

    public function delete($id = null)
    {
        $id = isset($id) ? $id : $this->id;
        
        VendaItem::where('venda_id', '=', $this->id)->delete();
        parent::delete($id);
    }
    
    public function get_cliente()
    {
        return Pessoa::find($this->cliente_id);
    }
}
