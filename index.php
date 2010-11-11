<?php

include_once dirname(__FILE__) . '/nette.php';

NDebug::enable();

$app = new FakturoidPairing('config.ini');

$app->run();



// autoload
function __autoload($class)
{
    include_once dirname(__FILE__) . '/app/' . $class . '.php';
}

// helpers
if(!function_exists('dump'))
{
    function dump($var)
    {
        echo "<pre>";
        var_dump($var);
        echo "</pre>";
    }
}

/**
* Multibyte safe version of trim()
* Always strips whitespace characters (those equal to \s)
*
* @author Peter Johnson
* @email phpnet@rcpt.at
* @param $string The string to trim
* @param $chars Optional list of chars to remove from the string ( as per trim() )
* @param $chars_array Optional array of preg_quote'd chars to be removed
* @return string
*/
function mb_trim( $string, $chars = "", $chars_array = array() )
{
    for( $x=0; $x<iconv_strlen( $chars ); $x++ ) $chars_array[] = preg_quote( iconv_substr( $chars, $x, 1 ) );
    $encoded_char_list = implode( "|", array_merge( array( "\s","\t","\n","\r", "\0", "\x0B" ), $chars_array ) );

    $string = mb_ereg_replace( "^($encoded_char_list)*", "", $string );
    $string = mb_ereg_replace( "($encoded_char_list)*$", "", $string );
    return $string;
}