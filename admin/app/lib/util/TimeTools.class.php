<?php
class TimeTools
{
    public static function getShortPastTime($from, $add = 'atrás')
    {
        $to = date('Y-m-d H:i:s');
        $start_date = new DateTime($from);
        $since_start = $start_date->diff(new DateTime($to));
        if ($since_start->y > 0)
            return $since_start->y.' anos ' . $add;
        if ($since_start->m > 0)
            return $since_start->m.' meses ' . $add;
        if ($since_start->d > 0)
            return $since_start->d.' dias ' . $add;
        if ($since_start->h > 0)
            return $since_start->h.' horas ' . $add;
        if ($since_start->i > 0)
            return $since_start->i.' minutos ' . $add;
        if ($since_start->s > 0)
            return $since_start->s.' segundos ' . $add;
    }
}
