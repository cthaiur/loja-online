<?php
/**
 * Route translator
 *
 * @version    3.0
 * @package    core
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006-2014 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class AdiantiRouteTranslator
{
    public static function translate($url, $format = TRUE)
    {
        /*
	    $routes = array();
        $routes['class=ConfirmationForm&method=onLoad'] = 'buy-product';
        $routes['class=UserList&method=onDelete'] = 'user-ondelete';
        $routes['class=ProductPublicList'] = 'product-list';
        $routes['class=OrderList'] = 'my-orders';
        $routes['class=ChangePasswordForm'] = 'change-password';
        $routes['class=LoginForm&method=onLoad&action=ConfirmationForm'] = 'login-confirmation';
        $routes['class=OrderView&method=onLoad'] = 'view-order';
        $routes['class=CustomerProfileForm&method=onLoad'] = 'profile-form';
        $routes['class=LoginForm'] = 'login';
        $routes['class=TEDInformationView'] = 'ted-info';
        */

        // automatic parse .htaccess
        $routes = self::parseHtAccess();
	    
        $keys = array_map('strlen', array_keys($routes));
        array_multisort($keys, SORT_DESC, $routes);
        
        foreach ($routes as $pattern => $short)
        {
            $new_url = self::replace($url, $pattern, $short);
            if ($url !== $new_url)
            {
                return $new_url;
            }
        }
        
        foreach ($routes as $pattern => $short)
        {
            // ignore default page loading methods
            $pattern = str_replace(['&method=onReload', '&method=onShow'], ['',''], $pattern);
            $new_url = self::replace($url, $pattern, $short);
            if ($url !== $new_url)
            {
                return $new_url;
            }
        }
        
        if ($format)
        {
            return 'index.php?'.$url;
        }
        
        return $url;
    }
    
    /**
     * Replace URL with pattern by short version
     * @param $url full original URL
     * @param $pattern pattern to be replaced
     * @param $short short version
     */
    private static function replace($url, $pattern, $short)
    {
        if (strpos($url, $pattern) !== FALSE)
        {
            $url = str_replace($pattern.'&', $short.'?', $url);
            if (strlen($url) == strlen($pattern))
            {
                $url = str_replace($pattern, $short, $url);
            }
        }
        return $url;
    }
    
    /**
     * Parse HTAccess routes
     * returns ARRAY[action] = route
     *     Ex: ARRAY["class=TipoProdutoList&method=onReload"] = "tipo-produto-list"
     */
    public static function parseHtAccess()
    {
        $rotas = [];
        if (file_exists('.htaccess'))
        {
            $rules = file('.htaccess');
            foreach ($rules as $rule)
            {
                $rule = preg_replace('/\s+/', ' ',$rule);
                $rule_parts = explode(' ', $rule);
                
                if ($rule_parts[0] == 'RewriteRule')
                {
                    $source = $rule_parts[1];
                    $target = $rule_parts[2];
                    $source = str_replace(['^', '$'], ['',''], $source);
                    $target = str_replace('&%{QUERY_STRING}', '', $target);
                    $target = str_replace(' [NC]', '', $target);
                    $target = str_replace('index.php?', '', $target);
                    $rotas[$target] = $source;
                }
            }
        }
        
        return $rotas;
    }
}
