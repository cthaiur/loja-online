<?php
/**
 * Customer Registration Form
 * @author  <your name here>
 */
class TrackingStatusWindow extends TWindow
{
    protected $form; // form
    protected $notebook;
    
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    public function __construct()
    {
        parent::__construct();
        
        // security check
        if (TSession::getValue('logged') !== TRUE)
        {
            throw new Exception(_t('Not logged'));
        }
        parent::setTitle('Rastreamento');
        parent::setSize(800,400);
    }
    
    public function onLoad($param)
    {
        require_once 'app/lib/phpquery/phpQuery-onefile.php';
        
        $POST_FORM['objetos'] = $param['shipping_code'];
        $request = curl_init('https://www2.correios.com.br/sistemas/rastreamento/ctrl/ctrlRastreamento.cfm');
        curl_setopt($request, CURLOPT_POST, true);
        curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($request, CURLOPT_POSTFIELDS, $POST_FORM);
        $html = curl_exec($request);
        //var_dump($html);
        $replace = array();
        phpQuery::newDocumentHTML($html, $charset = 'utf-8');
        $c = 0;
        foreach(pq('tr') as $tr)
        {
            $c++;
            if(count(pq($tr)->find('td')) == 3 && $c > 1)
            {
                $row['data']   = pq($tr)->find('td:eq(0)')->text();
                $row['local']  = pq($tr)->find('td:eq(1)')->text();
                $row['status'] = pq($tr)->find('td:eq(2)')->text();
                
                $replace[] = $row;
            }
        }
        
        $html = new THtmlRenderer('app/resources/tracking.html');
        $html->enableTranslation();
        $html->enableSection('main');
        $html->enableSection('places', $replace, TRUE);
        
        parent::add($html);
    }
}
