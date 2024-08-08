<?php
/**
 * NotaFiscal Active Record
 * @author  <your-name-here>
 */
class NotaFiscal extends TRecord
{
    const TABLENAME = 'nota_fiscal';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('cliente_id');
        parent::addAttribute('dt_nota');
        parent::addAttribute('total');
        parent::addAttribute('transmitida');
        parent::addAttribute('cancelada');
        parent::addAttribute('emitida');
        parent::addAttribute('natureza_operacao_id');
        parent::addAttribute('local_servico');
        parent::addAttribute('chave');
    }
    
    public static function fromVenda($venda)
    {
        $nota = new NotaFiscal;
        $nota->cliente_id  = $venda->cliente_id;
        $nota->dt_nota     = date('Y-m-d H:i:s');
        $nota->total       = $venda->total;
        $nota->transmitida = 'N';
        $nota->cancelada   = 'N';
        
        // pega a natureza de operação padrão.
        $natureza = NaturezaOperacao::where('padrao', '=', 'Y')->first();
        if (!empty($natureza))
        {
            $nota->natureza_operacao_id = $natureza->id;
            $nota->local_servico = $natureza->local_servico;
        }
        
        $nota->store();
        
        $items = VendaItem::where('venda_id', '=', $venda->id)->load();
        
        if ($items)
        {
            foreach ($items as $item)
            {
                $nota_item = new NotaFiscalItem;
                $nota_item->nota_fiscal_id = $nota->id;
                $nota_item->servico_id = $item->servico_id;
                $nota_item->valor = $item->valor;
                $nota_item->quantidade = $item->quantidade;
                $nota_item->total = $item->total;
                $nota_item->store();
            }
        }
        
        return $nota;
    }

    public function get_itens()
    {
        $output = [];
        $itens = NotaFiscalItem::where('nota_fiscal_id', '=', $this->id)->load();
        return $itens;
    }
        
    public function get_itens_string()
    {
        $output = [];
        $itens = NotaFiscalItem::where('nota_fiscal_id', '=', $this->id)->load();
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
        
        NotafiscalItem::where('nota_fiscal_id', '=', $this->id)->delete();
        parent::delete($id);
    }
    
    public function get_cliente()
    {
        return Pessoa::find($this->cliente_id);
    }
    
    public function get_natureza_operacao()
    {
        return NaturezaOperacao::find($this->natureza_operacao_id);
    }
}
