<?php
/**
 * ApplicationTranslator
 *
 * @version    7.0
 * @package    util
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class ApplicationTranslator
{
    private static $instance; // singleton instance
    private $lang;            // target language
    private $messages;
    private $sourceMessages;
    
    /**
     * Class Constructor
     */
    private function __construct()
    {
        $this->messages = [];
        $this->messages['en'][] = 'Options';
        $this->messages['en'][] = 'Change password';
        $this->messages['en'][] = 'New Issue';
        $this->messages['en'][] = 'Documents';
        $this->messages['en'][] = 'Permission denied';
        $this->messages['en'][] = 'File not found';
        $this->messages['en'][] = 'Search';
        $this->messages['en'][] = 'Register';
        $this->messages['en'][] = 'Record saved';
        $this->messages['en'][] = 'Do you really want to delete ?';
        $this->messages['en'][] = 'Record deleted';
        $this->messages['en'][] = 'Function';
        $this->messages['en'][] = 'Table';
        $this->messages['en'][] = 'Tool';
        $this->messages['en'][] = 'Data';
        $this->messages['en'][] = 'New';
        $this->messages['en'][] = 'Open';
        $this->messages['en'][] = 'Save';
        $this->messages['en'][] = 'Edit';
        $this->messages['en'][] = 'Delete';
        $this->messages['en'][] = 'Find';
        $this->messages['en'][] = 'Cancel';
        $this->messages['en'][] = 'Yes';
        $this->messages['en'][] = 'No';
        $this->messages['en'][] = 'January';
        $this->messages['en'][] = 'February';
        $this->messages['en'][] = 'March';
        $this->messages['en'][] = 'April';
        $this->messages['en'][] = 'May';
        $this->messages['en'][] = 'June';
        $this->messages['en'][] = 'July';
        $this->messages['en'][] = 'August';
        $this->messages['en'][] = 'September';
        $this->messages['en'][] = 'October';
        $this->messages['en'][] = 'November';
        $this->messages['en'][] = 'December';
        $this->messages['en'][] = 'Today';
        $this->messages['en'][] = 'Close';
        $this->messages['en'][] = 'The field ^1 can not be less than ^2 characters';
        $this->messages['en'][] = 'The field ^1 can not be greater than ^2 characters';
        $this->messages['en'][] = 'The field ^1 can not be less than ^2';
        $this->messages['en'][] = 'The field ^1 can not be greater than ^2';
        $this->messages['en'][] = 'The field ^1 is required';
        $this->messages['en'][] = 'The field ^1 has not a valid CNPJ';
        $this->messages['en'][] = 'The field ^1 has not a valid CPF';
        $this->messages['en'][] = 'The field ^1 contains an invalid e-mail';
        $this->messages['en'][] = 'Code';
        $this->messages['en'][] = 'Description';
        $this->messages['en'][] = 'Name';
        $this->messages['en'][] = 'Phone';
        $this->messages['en'][] = 'Email';
        $this->messages['en'][] = 'Address';
        $this->messages['en'][] = 'City';
        $this->messages['en'][] = 'Category';
        $this->messages['en'][] = 'Birthdate';
        $this->messages['en'][] = 'Login';
        $this->messages['en'][] = 'Logout';
        $this->messages['en'][] = 'Password';
        $this->messages['en'][] = 'Registration';
        $this->messages['en'][] = 'Expiration';
        $this->messages['en'][] = 'User not found';
        $this->messages['en'][] = 'Incorrect password';
        $this->messages['en'][] = 'News';
        $this->messages['en'][] = 'Tools';
        $this->messages['en'][] = 'Services';
        $this->messages['en'][] = 'Language';
        $this->messages['en'][] = 'Languages';
        $this->messages['en'][] = 'Tickets';
        $this->messages['en'][] = 'Contact';
        $this->messages['en'][] = 'Português';
        $this->messages['en'][] = 'Versão português';
        $this->messages['en'][] = 'Payment Status';
        $this->messages['en'][] = 'Payment Type';
        $this->messages['en'][] = 'Customer';
        $this->messages['en'][] = 'Product';
        $this->messages['en'][] = 'Products';
        $this->messages['en'][] = 'Transaction';
        $this->messages['en'][] = 'Transactions';
        $this->messages['en'][] = 'All transactions';
        $this->messages['en'][] = 'Pending';
        $this->messages['en'][] = 'Confirmed';
        $this->messages['en'][] = 'Amount';
        $this->messages['en'][] = 'Price';
        $this->messages['en'][] = 'Currency';
        $this->messages['en'][] = 'User';
        $this->messages['en'][] = 'Role';
        $this->messages['en'][] = 'Administrator';
        $this->messages['en'][] = 'Country';
        $this->messages['en'][] = 'Document';
        $this->messages['en'][] = 'Number';
        $this->messages['en'][] = 'Complement';
        $this->messages['en'][] = 'Neighborhood';
        $this->messages['en'][] = 'Postal';
        $this->messages['en'][] = 'State';
        $this->messages['en'][] = 'Forgot your password?';
        $this->messages['en'][] = 'Remind me';
        $this->messages['en'][] = 'Password reminder';
        $this->messages['en'][] = 'Hello';
        $this->messages['en'][] = 'Your password is';
        $this->messages['en'][] = 'Password reminder sent';
        $this->messages['en'][] = 'Confirm password';
        $this->messages['en'][] = 'New password';
        $this->messages['en'][] = 'Reset password';
        $this->messages['en'][] = 'Click here to reset your password';
        $this->messages['en'][] = 'This form has expired. Please, try again';
        $this->messages['en'][] = 'The passwords do not match';
        $this->messages['en'][] = 'Password defined';
        $this->messages['en'][] = 'Invalid try';
        $this->messages['en'][] = 'Register new account';
        $this->messages['en'][] = 'Continue';
        $this->messages['en'][] = 'Email already registered';
        $this->messages['en'][] = 'Virtual store';
        $this->messages['en'][] = 'Customer data';
        $this->messages['en'][] = 'Click here to edit';
        $this->messages['en'][] = 'Product data';
        $this->messages['en'][] = 'Buy';
        $this->messages['en'][] = 'Price per unit';
        $this->messages['en'][] = 'Account created. Check yout emails to enable this account and proceed with the purchase';
        $this->messages['en'][] = 'Account verification';
        $this->messages['en'][] = 'Click here to activate your account';
        $this->messages['en'][] = 'Not active account';
        $this->messages['en'][] = 'Account activated';
        $this->messages['en'][] = 'You may proceed to login';
        $this->messages['en'][] = 'Image';
        $this->messages['en'][] = 'Next';
        $this->messages['en'][] = 'My profile';
        $this->messages['en'][] = 'My orders';
        $this->messages['en'][] = 'Change product';
        $this->messages['en'][] = 'Selection';
        $this->messages['en'][] = 'Identification';
        $this->messages['en'][] = 'Confirm transaction';
        $this->messages['en'][] = 'Payment';
        $this->messages['en'][] = 'Delivery';
        $this->messages['en'][] = 'Date';
        $this->messages['en'][] = 'Qty';
        $this->messages['en'][] = 'Value';
        $this->messages['en'][] = 'Status';
        $this->messages['en'][] = 'Order data';
        $this->messages['en'][] = 'Total';
        $this->messages['en'][] = 'The field ^1 must have a full name';
        $this->messages['en'][] = 'Final?';
        $this->messages['en'][] = 'Basic registers';
        $this->messages['en'][] = 'Processes';
        $this->messages['en'][] = 'Generate';
        $this->messages['en'][] = 'Year';
        $this->messages['en'][] = 'Month';
        $this->messages['en'][] = 'Type';
        $this->messages['en'][] = 'Community';
        $this->messages['en'][] = 'Store';
        $this->messages['en'][] = 'Icon';
        $this->messages['en'][] = 'Add to cart';
        $this->messages['en'][] = 'Pay with';
        $this->messages['en'][] = 'In analysis';
        $this->messages['en'][] = 'Waiting payment';
        $this->messages['en'][] = 'Canceled';
        $this->messages['en'][] = 'Returned';
        $this->messages['en'][] = 'Available';
        $this->messages['en'][] = 'Paid';
        $this->messages['en'][] = 'In dispute';
        $this->messages['en'][] = 'Delivered';
        $this->messages['en'][] = 'Started';
        $this->messages['en'][] = 'Shipping';
        $this->messages['en'][] = 'Has shipping';
        $this->messages['en'][] = 'Shipping cost';
        $this->messages['en'][] = 'Weight';
        $this->messages['en'][] = 'Width';
        $this->messages['en'][] = 'Height';
        $this->messages['en'][] = 'Length';
        $this->messages['en'][] = 'Tracking number';
        $this->messages['en'][] = 'The tracking number for your order is';
        $this->messages['en'][] = 'Check';
        $this->messages['en'][] = 'License';
        $this->messages['en'][] = 'Level';
        $this->messages['en'][] = 'View';
        $this->messages['en'][] = 'Color';
        $this->messages['en'][] = 'Clear';
        $this->messages['en'][] = 'Back to the listing';
        $this->messages['en'][] = 'Methods';
        $this->messages['en'][] = 'Already user';
        $this->messages['en'][] = 'New user';
        $this->messages['en'][] = 'My account';
        $this->messages['en'][] = 'List products';
        $this->messages['en'][] = 'The passwords must be equal';
        $this->messages['en'][] = 'Method';
        $this->messages['en'][] = 'Actions';
        $this->messages['en'][] = 'Requirements';
        $this->messages['en'][] = 'Prerequisites for the purchase of this product are not complete. Please contact us for more information';
        $this->messages['en'][] = 'Gift';
        $this->messages['en'][] = 'Update data';
        $this->messages['en'][] = 'Select';
        $this->messages['en'][] = 'Information';
        $this->messages['en'][] = 'Details';
        $this->messages['en'][] = 'Active';
        $this->messages['en'][] = 'Activate/Deactivate';
        $this->messages['en'][] = 'This product is not active currently';
        $this->messages['en'][] = 'Choose the payment method';
        $this->messages['en'][] = 'Opinions';
        $this->messages['en'][] = 'Confirmation e-mail';
        $this->messages['en'][] = 'Update profile data';
        $this->messages['en'][] = 'Ok, these data are correct';
        $this->messages['en'][] = 'Tag';
        $this->messages['en'][] = 'Coupon';
        $this->messages['en'][] = 'Used';
        $this->messages['en'][] = 'Discount';
        $this->messages['en'][] = 'Discount coupon';
        $this->messages['en'][] = 'Action';
        $this->messages['en'][] = 'Action log';
        $this->messages['en'][] = 'Account created';
        $this->messages['en'][] = 'Registered';
        $this->messages['en'][] = 'Purchases';
        $this->messages['en'][] = 'Obs';
        $this->messages['en'][] = 'Contact data';
        //fim
        
        $this->messages['pt'][] = 'Opções';
        $this->messages['pt'][] = 'Alterar senha';
        $this->messages['pt'][] = 'Novo chamado';
        $this->messages['pt'][] = 'Documentos';
        $this->messages['pt'][] = 'Permissão negada';
        $this->messages['pt'][] = 'Arquivo não encontrado';
        $this->messages['pt'][] = 'Buscar';
        $this->messages['pt'][] = 'Cadastrar';
        $this->messages['pt'][] = 'Registro salvo';
        $this->messages['pt'][] = 'Deseja realmente excluir ?';
        $this->messages['pt'][] = 'Registro excluído';
        $this->messages['pt'][] = 'Função';
        $this->messages['pt'][] = 'Tabela';
        $this->messages['pt'][] = 'Ferramenta';
        $this->messages['pt'][] = 'Dados';
        $this->messages['pt'][] = 'Novo';
        $this->messages['pt'][] = 'Abrir';
        $this->messages['pt'][] = 'Salvar';
        $this->messages['pt'][] = 'Editar';
        $this->messages['pt'][] = 'Excluir';
        $this->messages['pt'][] = 'Buscar';
        $this->messages['pt'][] = 'Cancelar';
        $this->messages['pt'][] = 'Sim';
        $this->messages['pt'][] = 'Não';
        $this->messages['pt'][] = 'Janeiro';
        $this->messages['pt'][] = 'Fevereiro';
        $this->messages['pt'][] = 'Março';
        $this->messages['pt'][] = 'Abril';
        $this->messages['pt'][] = 'Maio';
        $this->messages['pt'][] = 'Junho';
        $this->messages['pt'][] = 'Julho';
        $this->messages['pt'][] = 'Agosto';
        $this->messages['pt'][] = 'Setembro';
        $this->messages['pt'][] = 'Outubro';
        $this->messages['pt'][] = 'Novembro';
        $this->messages['pt'][] = 'Dezembro';
        $this->messages['pt'][] = 'Hoje';
        $this->messages['pt'][] = 'Fechar';
        $this->messages['pt'][] = 'O campo ^1 não pode ter menos de ^2 caracteres';
        $this->messages['pt'][] = 'O campo ^1 não pode ter mais de ^2 caracteres';
        $this->messages['pt'][] = 'O campo ^1 não pode ser menor que ^2';
        $this->messages['pt'][] = 'O campo ^1 não pode ser maior que ^2';
        $this->messages['pt'][] = 'O campo ^1 é obrigatório';
        $this->messages['pt'][] = 'O campo ^1 não contém um CNPJ válido';
        $this->messages['pt'][] = 'O campo ^1 não contém um CPF válido';
        $this->messages['pt'][] = 'O campo ^1 contém um e-mail inválido';
        $this->messages['pt'][] = 'Código';
        $this->messages['pt'][] = 'Descrição';
        $this->messages['pt'][] = 'Nome';
        $this->messages['pt'][] = 'Fone';
        $this->messages['pt'][] = 'Email';
        $this->messages['pt'][] = 'Endereço';
        $this->messages['pt'][] = 'Cidade';
        $this->messages['pt'][] = 'Categoria';
        $this->messages['pt'][] = 'Nascimento';
        $this->messages['pt'][] = 'Login';
        $this->messages['pt'][] = 'Logout';
        $this->messages['pt'][] = 'Senha';
        $this->messages['pt'][] = 'Registro';
        $this->messages['pt'][] = 'Expiração';
        $this->messages['pt'][] = 'Usuário não encontrado';
        $this->messages['pt'][] = 'Senha incorreta';
        $this->messages['pt'][] = 'Notícias';
        $this->messages['pt'][] = 'Ferramentas';
        $this->messages['pt'][] = 'Serviços';
        $this->messages['pt'][] = 'Linguagem';
        $this->messages['pt'][] = 'Linguagens';
        $this->messages['pt'][] = 'Chamados';
        $this->messages['pt'][] = 'Contato';
        $this->messages['pt'][] = 'English';
        $this->messages['pt'][] = 'English version';
        $this->messages['pt'][] = 'Estado de pagamento';
        $this->messages['pt'][] = 'Tipo de pagamento';
        $this->messages['pt'][] = 'Cliente';
        $this->messages['pt'][] = 'Produto';
        $this->messages['pt'][] = 'Produtos';
        $this->messages['pt'][] = 'Transação';
        $this->messages['pt'][] = 'Transações';
        $this->messages['pt'][] = 'Todas transações';
        $this->messages['pt'][] = 'Pendente';
        $this->messages['pt'][] = 'Confirmado';
        $this->messages['pt'][] = 'Quantidade';
        $this->messages['pt'][] = 'Preço';
        $this->messages['pt'][] = 'Moeda';
        $this->messages['pt'][] = 'Usuário';
        $this->messages['pt'][] = 'Papel';
        $this->messages['pt'][] = 'Administrador';
        $this->messages['pt'][] = 'País';
        $this->messages['pt'][] = 'CPF/CNPJ';
        $this->messages['pt'][] = 'Número';
        $this->messages['pt'][] = 'Complemento';
        $this->messages['pt'][] = 'Bairro';
        $this->messages['pt'][] = 'CEP';
        $this->messages['pt'][] = 'Estado';
        $this->messages['pt'][] = 'Esqueceu a senha?';
        $this->messages['pt'][] = 'Lembre-me';
        $this->messages['pt'][] = 'Lembrete de senha';
        $this->messages['pt'][] = 'Olá';
        $this->messages['pt'][] = 'Sua senha é';
        $this->messages['pt'][] = 'Lembrete de senha enviado';
        $this->messages['pt'][] = 'Confirme a senha';
        $this->messages['pt'][] = 'Nova senha';
        $this->messages['pt'][] = 'Redefinir senha';
        $this->messages['pt'][] = 'Clique aqui para redefinir sua senha';
        $this->messages['pt'][] = 'Este formulário expirou. Por favor, tente novamente';
        $this->messages['pt'][] = 'As senhas não conferem';
        $this->messages['pt'][] = 'Senha definida';
        $this->messages['pt'][] = 'Tentativa inválida';
        $this->messages['pt'][] = 'Registrar conta nova';
        $this->messages['pt'][] = 'Prosseguir';
        $this->messages['pt'][] = 'Email já registrado';
        $this->messages['pt'][] = 'Loja virtual';
        $this->messages['pt'][] = 'Dados do cliente';
        $this->messages['pt'][] = 'Clique aqui para editar';
        $this->messages['pt'][] = 'Dados do produto';
        $this->messages['pt'][] = 'Comprar';
        $this->messages['pt'][] = 'Preço unitário';
        $this->messages['pt'][] = 'Conta criada. Verifique seus emails para habilitar a conta e prosseguir com a compra';
        $this->messages['pt'][] = 'Verificação de conta';
        $this->messages['pt'][] = 'Clique aqui para ativar sua conta';
        $this->messages['pt'][] = 'Conta não ativa';
        $this->messages['pt'][] = 'Conta ativa';
        $this->messages['pt'][] = 'Você pode prosseguir com o login';
        $this->messages['pt'][] = 'Imagem';
        $this->messages['pt'][] = 'Próximo';
        $this->messages['pt'][] = 'Meu perfil';
        $this->messages['pt'][] = 'Meus pedidos';
        $this->messages['pt'][] = 'Alterar produto';
        $this->messages['pt'][] = 'Seleção';
        $this->messages['pt'][] = 'Identificação';
        $this->messages['pt'][] = 'Confirmação';
        $this->messages['pt'][] = 'Pagamento';
        $this->messages['pt'][] = 'Entrega';
        $this->messages['pt'][] = 'Data';
        $this->messages['pt'][] = 'Qtde';
        $this->messages['pt'][] = 'Valor';
        $this->messages['pt'][] = 'Situação';
        $this->messages['pt'][] = 'Dados do pedido';
        $this->messages['pt'][] = 'Total';
        $this->messages['pt'][] = 'O campo ^1 deve ter um nome completo';
        $this->messages['pt'][] = 'Final?';
        $this->messages['pt'][] = 'Cadastros básicos';
        $this->messages['pt'][] = 'Processos';
        $this->messages['pt'][] = 'Gerar';
        $this->messages['pt'][] = 'Ano';
        $this->messages['pt'][] = 'Mês';
        $this->messages['pt'][] = 'Tipo';
        $this->messages['pt'][] = 'Comunidade';
        $this->messages['pt'][] = 'Loja';
        $this->messages['pt'][] = 'Ícone';
        $this->messages['pt'][] = 'Adicionar ao carrinho';
        $this->messages['pt'][] = 'Pague com';
        $this->messages['pt'][] = 'Em análise';
        $this->messages['pt'][] = 'Aguardando pgto';
        $this->messages['pt'][] = 'Cancelado';
        $this->messages['pt'][] = 'Devolvido';
        $this->messages['pt'][] = 'Disponível';
        $this->messages['pt'][] = 'Pago';
        $this->messages['pt'][] = 'Em disputa';
        $this->messages['pt'][] = 'Enviado';
        $this->messages['pt'][] = 'Iniciado';
        $this->messages['pt'][] = 'Frete';
        $this->messages['pt'][] = 'Tem frete';
        $this->messages['pt'][] = 'Valor do frete';
        $this->messages['pt'][] = 'Peso';
        $this->messages['pt'][] = 'Largura';
        $this->messages['pt'][] = 'Altura';
        $this->messages['pt'][] = 'Comprimento';
        $this->messages['pt'][] = 'Código rastreio';
        $this->messages['pt'][] = 'O código de rastreio para seu pedido é';
        $this->messages['pt'][] = 'Verificar';
        $this->messages['pt'][] = 'Licença';
        $this->messages['pt'][] = 'Nível';
        $this->messages['pt'][] = 'Visualizar';
        $this->messages['pt'][] = 'Cor';
        $this->messages['pt'][] = 'Limpar';
        $this->messages['pt'][] = 'Voltar para a listagem';
        $this->messages['pt'][] = 'Métodos';
        $this->messages['pt'][] = 'Já sou usuário';
        $this->messages['pt'][] = 'Novo usuário';
        $this->messages['pt'][] = 'Minha conta';
        $this->messages['pt'][] = 'Listar produtos';
        $this->messages['pt'][] = 'As senhas devem ser iguais';
        $this->messages['pt'][] = 'Método';
        $this->messages['pt'][] = 'Ações';
        $this->messages['pt'][] = 'Requisitos';
        $this->messages['pt'][] = 'Pré-requisitos para compra deste produto não completos. Entre em contato conosco para maiores informações';
        $this->messages['pt'][] = 'Brinde';
        $this->messages['pt'][] = 'Alterar dados';
        $this->messages['pt'][] = 'Selecionar';
        $this->messages['pt'][] = 'Informações';
        $this->messages['pt'][] = 'Detalhes';
        $this->messages['pt'][] = 'Ativo';
        $this->messages['pt'][] = 'Ativar/Desativar';
        $this->messages['pt'][] = 'Esta produto não está ativo no momento';
        $this->messages['pt'][] = 'Escolha o método de pagamento';
        $this->messages['pt'][] = 'Opiniões';
        $this->messages['pt'][] = 'E-mail de confirmação';
        $this->messages['pt'][] = 'Atualizar dados de perfil';
        $this->messages['pt'][] = 'Ok, estes dados estão corretos';
        $this->messages['pt'][] = 'Tag';
        $this->messages['pt'][] = 'Cupom';
        $this->messages['pt'][] = 'Utilizado';
        $this->messages['pt'][] = 'Desconto';
        $this->messages['pt'][] = 'Cupom de desconto';
        $this->messages['pt'][] = 'Ação';
        $this->messages['pt'][] = 'Log de ações';
        $this->messages['pt'][] = 'Conta criada';
        $this->messages['pt'][] = 'Cadastrado';
        $this->messages['pt'][] = 'Compras';
        $this->messages['pt'][] = 'Obs';
        $this->messages['pt'][] = 'Dados de contato';
        //fim
        foreach ($this->messages as $lang => $messages)
        {
            $this->sourceMessages[$lang] = array_flip( $this->messages[ $lang ] );
        }
    }
    
    /**
     * Returns the singleton instance
     * @return  Instance of self
     */
    public static function getInstance()
    {
        // if there's no instance
        if (empty(self::$instance))
        {
            // creates a new object
            self::$instance = new self;
        }
        // returns the created instance
        return self::$instance;
    }
    
    /**
     * Define the target language
     * @param $lang     Target language index
     */
    public static function setLanguage($lang, $global = true)
    {
        $instance = self::getInstance();
        if (in_array($lang, array_keys($instance->messages)))
        {
            $instance->lang = $lang;
        }
        
        if ($global)
        {
            AdiantiCoreTranslator::setLanguage( $lang );
        }
    }
    
    /**
     * Returns the target language
     * @return Target language index
     */
    public static function getLanguage()
    {
        $instance = self::getInstance();
        return $instance->lang;
    }
    
    /**
     * Translate a word to the target language
     * @param $word     Word to be translated
     * @return          Translated word
     */
    public static function translate($word, $source_language, $param1 = NULL, $param2 = NULL, $param3 = NULL)
    {
        // get the self unique instance
        $instance = self::getInstance();
        // search by the numeric index of the word
        
        if (isset($instance->sourceMessages[$source_language][$word]) and !is_null($instance->sourceMessages[$source_language][$word]))
        {
            $key = $instance->sourceMessages[$source_language][$word]; //$key = array_search($word, $instance->messages['en']);
            
            // get the target language
            $language = self::getLanguage();
            // returns the translated word
            $message = $instance->messages[$language][$key];
            
            if (isset($param1))
            {
                $message = str_replace('^1', $param1, $message);
            }
            if (isset($param2))
            {
                $message = str_replace('^2', $param2, $message);
            }
            if (isset($param3))
            {
                $message = str_replace('^3', $param3, $message);
            }
            return $message;
        }
        else
        {
            return 'Message not found: '. $word;
        }
    }
    
    /**
     * Translate a template file
     */
    public static function translateTemplate($template)
    {
        // search by translated words
        if(preg_match_all( '!_t\{(.*?)\}!i', $template, $match ) > 0)
        {
            foreach($match[1] as $word)
            {
                $translated = _t($word);
                $template = str_replace('_t{'.$word.'}', $translated, $template);
            }
        }
        
        if(preg_match_all( '!_tf\{(.*?), (.*?)\}!i', $template, $matches ) > 0)
        {
            foreach($matches[0] as $key => $match)
            {
                $raw        = $matches[0][$key];
                $word       = $matches[1][$key];
                $from       = $matches[2][$key];
                $translated = _tf($word, $from);
                $template = str_replace($raw, $translated, $template);
            }
        }
        return $template;
    }
}

/**
 * Facade to translate words from english
 * @param $word  Word to be translated
 * @param $param1 optional ^1
 * @param $param2 optional ^2
 * @param $param3 optional ^3
 * @return Translated word
 */
function _t($msg, $param1 = null, $param2 = null, $param3 = null)
{
    return ApplicationTranslator::translate($msg, 'en', $param1, $param2, $param3);
}

/**
 * Facade to translate words from specified language
 * @param $word  Word to be translated
 * @param $source_language  Source language
 * @param $param1 optional ^1
 * @param $param2 optional ^2
 * @param $param3 optional ^3
 * @return Translated word
 */
function _tf($msg, $source_language = 'en', $param1 = null, $param2 = null, $param3 = null)
{
    return ApplicationTranslator::translate($msg, $source_language, $param1, $param2, $param3);
}
