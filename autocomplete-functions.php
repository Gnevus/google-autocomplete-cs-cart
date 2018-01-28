<?php

function fn_a_cities_google_langs($lang_code)
{
    $supported_langs = array ('en', 'eu', 'ca', 'da', 'nl', 'fi', 'fr', 'gl', 'de', 'el', 'it', 'ja', 'no', 'nn', 'ru' , 'es', 'sv', 'th');

    if (in_array($lang_code, $supported_langs)) {
        return $lang_code;
    }

    return '';
}
