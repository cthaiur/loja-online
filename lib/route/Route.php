<?php
/**
 * Route handler
 *
 * @version    5.0
 * @package    Web
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class Route
{
    public static $actions;
    public static $exception;
    
    /**
     * Register an action
     * 
     * @param $action entry point
     * @param $callback response callback
     */
    public static function on($action, $callback)
    {
        self::$actions[$action] = $callback;
    }
    
    /**
     * Execute an action
     * 
     * @param $action entry point
     * @param $args arguments
     */
    public static function exec($action, $args)
    {
        call_user_func(self::$actions[$action], $args);
    }
    
    /**
     * Register an exception
     * 
     * @param $callback response callback
     */
    public static function exception($callback)
    {
        self::$exception = $callback;
    }
    
    /**
     * Execute the current URL action
     */
    public static function run()
    {
        $args = $_REQUEST;
        if (isset($args['action']))
        {
            $callback = self::$actions[ $args['action'] ];
        }
        else
        {
            $callback = self::$actions[''];
        }
        
        if (is_callable($callback))
        {
            try
            {
                call_user_func($callback, $args);
            }
            catch (Exception $e)
            {
                if (is_callable(self::$exception))
                {
                    call_user_func(self::$exception, $e);
                }
            }
        }
    }
}
