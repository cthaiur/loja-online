<?php
/**
 * TipoServico Active Record
 * @author  <your-name-here>
 */
class TipoServico extends TRecord
{
    const TABLENAME = 'tipo_servico';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('nome');
        parent::addAttribute('lista_cnae_id');
        parent::addAttribute('lista_servico_id');
    }
    
    public function get_lista_cnae()
    {
        return ListaCnae::find($this->lista_cnae_id);
    }
    
    public function get_lista_servico()
    {
        return ListaServico::find($this->lista_servico_id);
    }
    
    public function get_impostos()
    {
        return TipoServicoImposto::where('tipo_servico_id', '=', $this->id)->load();
    }
    
    public function delete($id = null)
    {
        $id = isset($id) ? $id : $this->id;
        
        TipoServicoImposto::where('tipo_servico_id', '=', $this->id)->delete();
        parent::delete($id);
    }
}
