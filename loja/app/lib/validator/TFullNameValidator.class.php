<?php
/**
 * Required field validation
 *
 * @version    1.0
 * @package    validator
 * @author     Pablo Dall'Oglio <framework@adianti.com.br>
 * @copyright  Copyright (c) 2006-2011 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework/license
 */
class TFullNameValidator extends TFieldValidator
{
    /**
     * Validate a given value
     * @param $label Identifies the value to be validated in case of exception
     * @param $value Value to be validated
     * @param $parameters aditional parameters for validation
     */
    public function validate($label, $value, $parameters = NULL)
    {
        $parts = explode(' ', trim($value));
        
        if (count($parts) < 2)
        {
            throw new Exception(_t('The field ^1 must have a full name', $label));
        }
    }
}
?>