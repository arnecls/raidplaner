<?php
    $gItoa64 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

    // -----------------------------------------------------------------------------
    
    function getUTF8($aString)
    {
        $Encoding = mb_detect_encoding($aString);
        return (($Encoding == 'UTF-8') && mb_check_encoding($aString,'UTF-8'))
            ? $aString
            : mb_convert_encoding($aString, 'UTF-8', $Encoding);
    }

    // -----------------------------------------------------------------------------
    
    function xmlSpecialChar( $aChar )
    {
        $Encoding = mb_detect_encoding($aChar);
        
        $Char = ($Encoding === false)
            ? mb_convert_encoding($aChar, 'UTF-32')             // Try default encoding
            : mb_convert_encoding($aChar, 'UTF-32', $Encoding); // Use detected encoding
            
        if (($Char == '') && ($Encoding !== false))
            $Char = mb_convert_encoding($aChar, 'UTF-32');
            
        return ($Char != '')
            ? '&#'.hexdec(bin2hex($Char)).';'
            : '&#65533;';
    }
    
    // -----------------------------------------------------------------------------
    
    function xmlentities( $aString, $aCompat, $aCharset )
    {
        $ValidString = htmlentities(getUTF8($aString), $aCompat, $aCharset);
    
        // if the given charset did not work use fallback
    
        $Flags = (PHP_VERSION_ID >= 50300) ? $aCompat | ENT_IGNORE : $aCompat;
    
        if ( $ValidString == '' )
            $ValidString = htmlentities( $aString, $Flags, 'ISO8859-15' );
    
        $HtmlTranslationTable = get_html_translation_table( HTML_ENTITIES, $aCompat );
    
        $TranslationTable = array();
    
        $TranslationTable['@'] = xmlSpecialChar('@');
        $TranslationTable['['] = xmlSpecialChar('[');
        $TranslationTable[']'] = xmlSpecialChar(']');
        $TranslationTable['\''] = xmlSpecialChar('\'');
    
        foreach ( $HtmlTranslationTable as $Key => $Value )
        {
             $TranslationTable[$Value] = xmlSpecialChar($Key);
        }
    
        $Translated = strtr( $ValidString, $TranslationTable );
    
        if ($Translated === false)
            return $ValidString;
    
        return $Translated;
    }
    
    // -----------------------------------------------------------------------------
    
    function xmlToUTF8( $aString )
    {
        $Flags = (PHP_VERSION_ID >= 50400) ? ENT_QUOTES | ENT_XML1 : ENT_QUOTES;    
        return html_entity_decode($aString, $Flags, 'UTF-8');
    }
    
    // -----------------------------------------------------------------------------
    
    function requestToXML( $aString, $aCompat, $aCharset )
    {
        return xmlentities( stripcslashes(urldecode($aString)), $aCompat, $aCharset );
    }
    
    // -----------------------------------------------------------------------------
    
    function leadingZero10( $aValue )
    {
        $Number = intval($aValue,10);
    
        return ($Number < 10)
            ? '0'.$Number
            : $Number;
    }
    
    // -----------------------------------------------------------------------------
    
    function HTMLToBBCode($aString)
    {
        $Text = $aString;
        
        $Text = preg_replace('/<a href="(.*)"\\><\\/a\\>/', '[url]\\1[/url]', $Text);
        $Text = preg_replace('/<a href="(.*)"\\>(.*)<\\/a\\>/', '[url=\'\\1\']\\2[/url]', $Text);
        $Text = preg_replace('/<b>(.*)<\\/b\\>/', '[b]\\1[/b]', $Text);
        $Text = preg_replace('/<i>(.*)<\\/i\\>/', '[i]\\1[/i]', $Text);
        $Text = preg_replace('/<img src=\'(.*)\'\\/>/', '[img]\\1[/img]', $Text);
        $Text = preg_replace('/<br\\/>/', "\n", $Text);
        
        return xmlToUTF8($Text);
    }

    // -----------------------------------------------------------------------------
    
    function encode64( $aInput, $aCount )
    {
        global $gItoa64;
        
        $Output = '';
        $i = 0;

        do {
            $Value = ord($aInput[$i++]);
            $Output .= $gItoa64[$Value & 0x3f];

            if ($i < $aCount)
            {
               $Value |= ord($aInput[$i]) << 8;
            }

            $Output .= $gItoa64[($Value >> 6) & 0x3f];

            if ($i++ >= $aCount)
            {
               break;
            }

            if ($i < $aCount)
            {
               $Value |= ord($aInput[$i]) << 16;
            }

            $Output .= $gItoa64[($Value >> 12) & 0x3f];

            if ($i++ >= $aCount)
            {
               break;
            }

            $Output .= $gItoa64[($Value >> 18) & 0x3f];
        } while ($i < $aCount);

        return $Output;
    }
    
    // -----------------------------------------------------------------------------
    
    function mysqlToMbstringCharset($aCharset)
    {
        $Mapping = array(
            'big5'     => 'BIG-5',
            'koi8r'    => 'KOI8-R',
            'latin1'   => 'Windows-1252',
            'latin2'   => 'ISO-8859-2',
            'ascii'    => 'ASCII',
            'ujis'     => 'EUC-JP',
            'sjis'     => 'SJIS',
            'hebrew'   => 'ISO-8859-8',
            'euckr'    => 'EUC-KR',
            'greek'    => 'ISO-8859-7',
            'latin5'   => 'ISO-8859-9',
            'utf8'     => 'UTF-8',
            'ucs2'     => 'UCS-2',
            'cp866'    => 'CP866',
            'latin7'   => 'ISO-8859-13',
            'cp1251'   => 'Windows-1251',
            'cp932'    => 'CP932',
            'eucjpms'  => 'JIS-ms' );
            
        $CharsetLower = strtolower($aCharset);
            
        return (isset($Mapping[$CharsetLower]))
            ? $Mapping[$CharsetLower]
            : 'UTF-8';
    }

?>