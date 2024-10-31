<?php
function orfw_sanitize_array( $arrayA )
{
    if ( !is_array($arrayA) )
        return array();
    
    $arrayB = array();

    foreach ( $arrayA as $key => $value )
    {
        $key = (is_numeric($key)) ? intval($key) : sanitize_key($key);

        switch ( gettype($value) )
        {
            case 'boolean':
                $value = boolval($value);
                break;
            
            case 'integer':
                $value = intval($value);
                break;
            
            case 'double':
                $value = doubleval($value);
                break;
            
            case 'string':
                $value = sanitize_text_field($value);
                break;
            
            case 'array':
                $value = orfw_sanitize_array($value);
                break;
            
            default:
                $value = '';
                break;
        }

        $arrayB[ $key ] = $value;
    }

    return $arrayB;
}

function time_elapsed_string( $datetime, $full = false )
{
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    foreach ($string as $k => &$v)
    {
        if ($diff->$k)
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        else
            unset($string[$k]);
    }

    if ( ! $full )
        $string = array_slice($string, 0, 1);
    
    return $string ? implode(', ', $string) . ' ago' : 'Just now';
}
