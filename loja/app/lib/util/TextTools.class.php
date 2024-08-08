<?php
class TextTools
{
    public static function assureUnicode($content)
    {
        $enc_in = mb_detect_encoding($content, ['UTF-8', 'ISO-8859-1', 'ASCII'], true);
        if ($enc_in !== 'UTF-8')
        {
            return iconv($enc_in, "UTF-8", $content);
        }
        return $content;
    }
    
    public static function slug($content)
    {
        $content = self::assureUnicode($content);
        
        $table = array(
            'Š'=>'S', 'š'=>'s', 'Đ'=>'Dj', 'đ'=>'dj', 'Ž'=>'Z', 'ž'=>'z', 'Č'=>'C', 'č'=>'c', 'Ć'=>'C', 'ć'=>'c',
            'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
            'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O',
            'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss',
            'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e',
            'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o',
            'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b',
            'ÿ'=>'y', 'Ŕ'=>'R', 'ŕ'=>'r',
        );

        $content = strtr($content, $table);

        $content = strtolower($content);
        //Strip any unwanted characters
        $content = preg_replace("/[^a-z0-9_\s-]/", "", $content);
        //Clean multiple dashes or whitespaces
        $content = preg_replace("/[\s-]+/", " ", $content);
        //Convert whitespaces and underscore to dash
        $content = preg_replace("/[\s_]/", "-", $content);
        
        return $content;
    }
    
    /**
     * Replace text between
     * @param $str Text to be replaced
     * @param $needle_start Start mark
     * @param $needle_end End mark
     * @param $replacement Text to be inserted
     * @param $include_limits if the mark limits will be replaced
     */
    public static function replaceBetween($str, $needle_start, $needle_end, $replacement, $include_limits = true)
    {
        $pos = strpos($str, $needle_start);
        $start = $pos === false ? 0 : $pos + ($include_limits ? strlen($needle_start) : 0);

        $pos = strpos($str, $needle_end, $start);
        $end = $pos === false ? strlen($str) : ($include_limits ? $pos : $pos + strlen($needle_end));

        return substr_replace($str, $replacement, $start, $end - $start);
    }
}
