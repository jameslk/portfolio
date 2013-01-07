<?php

Libs('template');

function HeadExtra($html, $priority = false) {
    if(!isset($GLOBALS['head_extra'])) {
        $GLOBALS['head_extra'] = array(
            'high' => array(),
            'normal' => array(),
            'low' => array()
        );
    }
    
    if(!$priority)
        $priority = 'normal';
    
    $GLOBALS['head_extra'][$priority][] = $html;
}

function Exit_404($template_file = 'errors/404.tpl') {
    $template = new Template;
    $content = $template->fetch($template_file);
    
    header("HTTP/1.1 404 Not Found");
    echo $content;
    exit();
}

/**
 * @credit http://www.phpcentral.com/206-php-script-duration-calculator.html
 */
function Duration($seconds, $discard_details = false) {
    $years = floor($seconds/(60*60*24*365));
    $seconds %= 60*60*24*365;
    
    $weeks = floor($seconds/(60*60*24*7));
    $seconds %= 60*60*24*7;
    
    $days = floor($seconds/(60*60*24));
    $seconds %= 60*60*24;
    
    $hours = floor($seconds/(60*60));
    $seconds %= 60*60;
    
    $minutes = floor($seconds/60);
    
    $seconds = $seconds % 60;
    
    $duration = '';
    
    if(!$discard_details) {
        if($years >= 1)
            $duration = $years.' years ';
        
        if($weeks >= 1)
            $duration .= $weeks.' weeks ';
        
        if($days >= 1)
            $duration .= $days.' days ';
        
        if($hours >= 1)
            $duration .= $hours.' hours ';
        
        if($minutes >= 1)
            $duration .= $minutes.' minutes ';
        
        if($seconds >= 1)
            $duration .= $seconds.' second'.($seconds != 1 ? 's' : '');
    }
    else {
        if($years >= 1)
            $duration = $years.' year'.($years != 1 ? 's' : '');
        else if($weeks >= 1)
            $duration = $weeks.' week'.($weeks != 1 ? 's' : '');
        else if($days >= 1)
            $duration = $days.' day'.($days != 1 ? 's' : '');
        else if($hours >= 1)
            $duration = $hours.' hour'.($hours != 1 ? 's' : '');
        else if($minutes >= 1)
            $duration = $minutes.' minute'.($minutes != 1 ? 's' : '');
        else if($seconds >= 1)
            $duration = $seconds.' second'.($seconds != 1 ? 's' : '');
    }
    
    return trim($duration);
}

function DurationArray($seconds) {
    $years = floor($seconds/(60*60*24*365));
    $seconds %= 60*60*24*365;
    
    $weeks = floor($seconds/(60*60*24*7));
    $seconds %= 60*60*24*7;
    
    $days = floor($seconds/(60*60*24));
    $seconds %= 60*60*24;
    
    $hours = floor($seconds/(60*60));
    $seconds %= 60*60;
    
    $minutes = floor($seconds/60);
    
    $seconds = $seconds % 60;
    
    $duration = array();
    
    if($years >= 1)
        $duration['years'] = $years;
    else
        $duration['years'] = 0;
    
    if($weeks >= 1)
        $duration['weeks'] = $weeks;
    else
        $duration['weeks'] = 0;
    
    if($days >= 1)
        $duration['days'] = $days;
    else
        $duration['days'] = 0;
    
    if($hours >= 1)
        $duration['hours'] = $hours;
    else
        $duration['hours'] = 0;
    
    if($minutes >= 1)
        $duration['minutes'] = $minutes;
    else
        $duration['minutes'] = 0;
    
    if($seconds >= 1)
        $duration['seconds'] = $seconds;
    else
        $duration['seconds'] = 0;
    
    return $duration;
}

function ConvertNameStyle($name, $func_style = true) {
    if($func_style) {
        /* Function Style: hello_world -> HelloWorld */
        $len = strlen($name);
        for($i = 0;$i < $len;++$i) {
            if(!$i) {
                /* Capitalize first char */
                $name[$i] = strtoupper($name[$i]);
            }
            else if(($name[$i] == '_') && ($i+1 < $len)) {
                /* Capitalize char after _ */
                $name = substr_replace($name, strtoupper($name[$i+1]), $i, 2);
                --$len;
            }
        }
    }
    else {
        /* Variable Style: HelloWorld -> hello_world or HELLOWorld -> hello_world */
        $len = strlen($name);
        for($i = 0;$i < $len;++$i) {
            if(($name[$i] >= 'A') && ($name[$i] <= 'Z')) {
                if(($name[$i+1] >= 'A') && ($name[$i+1] <= 'Z')) {
                    /* Lowercase and prepend or append _ to uppercase string */
                    $offset = $i;
                    while(($name[$offset] >= 'A') && ($name[$offset] <= 'Z')) {
                        $name[$offset] = strtolower($name[$offset]);
                        ++$offset;
                    }
                    
                    if($i) //prepend
                        $name = substr_replace($name, '_'.$name[$i], $i, 1);
                    else //append
                        $name = substr_replace($name, '_'.$name[$offset-1], $offset-1, 1);
                }
                else {
                    /* Lowercase and prepend _ to uppercase char */
                    
                    if($i)
                        $name = substr_replace($name, '_'.strtolower($name[$i]), $i, 1);
                    else
                        $name[0] = strtolower($name[0]);
                }
                
                ++$len;
            }
        }
        
        $name = trim(str_replace('__', '_', $name), '_');
    }
    
    return $name;
}

function GetTimeZones() {
    return array(
        '-12'	=> '[UTC - 12] Baker Island Time',
		'-11'	=> '[UTC - 11] Niue Time, Samoa Standard Time',
		'-10'	=> '[UTC - 10] Hawaii-Aleutian Standard Time, Cook Island Time',
		'-9.5'	=> '[UTC - 9:30] Marquesas Islands Time',
		'-9'	=> '[UTC - 9] Alaska Standard Time, Gambier Island Time',
		'-8'	=> '[UTC - 8] Pacific Standard Time',
		'-7'	=> '[UTC - 7] Mountain Standard Time',
		'-6'	=> '[UTC - 6] Central Standard Time',
		'-5'	=> '[UTC - 5] Eastern Standard Time',
		'-4.5'	=> '[UTC - 4:30] Venezuelan Standard Time',
		'-4'	=> '[UTC - 4] Atlantic Standard Time',
		'-3.5'	=> '[UTC - 3:30] Newfoundland Standard Time',
		'-3'	=> '[UTC - 3] Amazon Standard Time, Central Greenland Time',
		'-2'	=> '[UTC - 2] Fernando de Noronha Time, South Georgia &amp; the South Sandwich Islands Time',
		'-1'	=> '[UTC - 1] Azores Standard Time, Cape Verde Time, Eastern Greenland Time',
		'0'		=> '[UTC] Western European Time, Greenwich Mean Time',
		'1'		=> '[UTC + 1] Central European Time, West African Time',
		'2'		=> '[UTC + 2] Eastern European Time, Central African Time',
		'3'		=> '[UTC + 3] Moscow Standard Time, Eastern African Time',
		'3.5'	=> '[UTC + 3:30] Iran Standard Time',
		'4'		=> '[UTC + 4] Gulf Standard Time, Samara Standard Time',
		'4.5'	=> '[UTC + 4:30] Afghanistan Time',
		'5'		=> '[UTC + 5] Pakistan Standard Time, Yekaterinburg Standard Time',
		'5.5'	=> '[UTC + 5:30] Indian Standard Time, Sri Lanka Time',
		'5.75'	=> '[UTC + 5:45] Nepal Time',
		'6'		=> '[UTC + 6] Bangladesh Time, Bhutan Time, Novosibirsk Standard Time',
		'6.5'	=> '[UTC + 6:30] Cocos Islands Time, Myanmar Time',
		'7'		=> '[UTC + 7] Indochina Time, Krasnoyarsk Standard Time',
		'8'		=> '[UTC + 8] Chinese Standard Time, Australian Western Standard Time, Irkutsk Standard Time',
		'8.75'	=> '[UTC + 8:45] Southeastern Western Australia Standard Time',
		'9'		=> '[UTC + 9] Japan Standard Time, Korea Standard Time, Chita Standard Time',
		'9.5'	=> '[UTC + 9:30] Australian Central Standard Time',
		'10'	=> '[UTC + 10] Australian Eastern Standard Time, Vladivostok Standard Time',
		'10.5'	=> '[UTC + 10:30] Lord Howe Standard Time',
		'11'	=> '[UTC + 11] Solomon Island Time, Magadan Standard Time',
		'11.5'	=> '[UTC + 11:30] Norfolk Island Time',
		'12'	=> '[UTC + 12] New Zealand Time, Fiji Time, Kamchatka Standard Time',
		'12.75'	=> '[UTC + 12:45] Chatham Islands Time',
		'13'	=> '[UTC + 13] Tonga Time, Phoenix Islands Time',
		'14'	=> '[UTC + 14] Line Island Time',
	);
}

function GetCountryCodes() {
    return array(
        'US' => 'United States',
        'GB' => 'United Kingdom (Great Britain)',
        'CA' => 'Canada',
        
        'AF' => 'Afghanistan',
        'AL' => 'Albania',
        'DZ' => 'Algeria',
        'AS' => 'American Samoa',
        'AD' => 'Andorra',
        'AO' => 'Angola',
        'AI' => 'Anguilla',
        'AQ' => 'Antarctica',
        'AG' => 'Antigua & Barbuda',
        'AR' => 'Argentina',
        'AM' => 'Armenia',
        'AW' => 'Aruba',
        'AU' => 'Australia',
        'AT' => 'Austria',
        'AZ' => 'Azerbaijan',
        'BS' => 'Bahama',
        'BH' => 'Bahrain',
        'BD' => 'Bangladesh',
        'BB' => 'Barbados',
        'BY' => 'Belarus',
        'BE' => 'Belgium',
        'BZ' => 'Belize',
        'BJ' => 'Benin',
        'BM' => 'Bermuda',
        'BT' => 'Bhutan',
        'BO' => 'Bolivia',
        'BA' => 'Bosnia and Herzegovina',
        'BW' => 'Botswana',
        'BV' => 'Bouvet Island',
        'BR' => 'Brazil',
        'IO' => 'British Indian Ocean Territory',
        'VG' => 'British Virgin Islands',
        'BN' => 'Brunei Darussalam',
        'BG' => 'Bulgaria',
        'BF' => 'Burkina Faso',
        'BI' => 'Burundi',
        'KH' => 'Cambodia',
        'CM' => 'Cameroon',
        'CV' => 'Cape Verde',
        'KY' => 'Cayman Islands',
        'CF' => 'Central African Republic',
        'TD' => 'Chad',
        'CL' => 'Chile',
        'CN' => 'China',
        'CX' => 'Christmas Island',
        'CC' => 'Cocos (Keeling) Islands',
        'CO' => 'Colombia',
        'KM' => 'Comoros',
        'CG' => 'Congo',
        'CK' => 'Cook Iislands',
        'CR' => 'Costa Rica',
        'CI' => 'Cote D\'ivoire (Ivory Coast)',
        'HR' => 'Croatia',
        'CU' => 'Cuba',
        'CY' => 'Cyprus',
        'CZ' => 'Czech Republic',
        'DK' => 'Denmark',
        'DJ' => 'Djibouti',
        'DM' => 'Dominica',
        'DO' => 'Dominican Republic',
        'TP' => 'East Timor',
        'EC' => 'Ecuador',
        'EG' => 'Egypt',
        'SV' => 'El Salvador',
        'GQ' => 'Equatorial Guinea',
        'ER' => 'Eritrea',
        'EE' => 'Estonia',
        'ET' => 'Ethiopia',
        'FK' => 'Falkland Islands (Malvinas)',
        'FO' => 'Faroe Islands',
        'FJ' => 'Fiji',
        'FI' => 'Finland',
        'FR' => 'France',
        'FX' => 'France, Metropolitan',
        'GF' => 'French Guiana',
        'PF' => 'French Polynesia',
        'TF' => 'French Southern Territories',
        'GA' => 'Gabon',
        'GM' => 'Gambia',
        'GE' => 'Georgia',
        'DE' => 'Germany',
        'GH' => 'Ghana',
        'GI' => 'Gibraltar',
        'GR' => 'Greece',
        'GL' => 'Greenland',
        'GD' => 'Grenada',
        'GP' => 'Guadeloupe',
        'GU' => 'Guam',
        'GT' => 'Guatemala',
        'GN' => 'Guinea',
        'GW' => 'Guinea-Bissau',
        'GY' => 'Guyana',
        'HT' => 'Haiti',
        'HM' => 'Heard & McDonald Islands',
        'HN' => 'Honduras',
        'HK' => 'Hong Kong',
        'HU' => 'Hungary',
        'IS' => 'Iceland',
        'IN' => 'India',
        'ID' => 'Indonesia',
        'IQ' => 'Iraq',
        'IE' => 'Ireland',
        'IR' => 'Islamic Republic of Iran',
        'IL' => 'Israel',
        'IT' => 'Italy',
        'JM' => 'Jamaica',
        'JP' => 'Japan',
        'JO' => 'Jordan',
        'KZ' => 'Kazakhstan',
        'KE' => 'Kenya',
        'KI' => 'Kiribati',
        'KR' => 'Korea, Republic of',
        'KW' => 'Kuwait',
        'KG' => 'Kyrgyzstan',
        'LA' => 'Laos',
        'LV' => 'Latvia',
        'LB' => 'Lebanon',
        'LS' => 'Lesotho',
        'LR' => 'Liberia',
        'LY' => 'Libyan Arab Jamahiriya',
        'LI' => 'Liechtenstein',
        'LT' => 'Lithuania',
        'LU' => 'Luxembourg',
        'MO' => 'Macau',
        'MG' => 'Madagascar',
        'MW' => 'Malawi',
        'MY' => 'Malaysia',
        'MV' => 'Maldives',
        'ML' => 'Mali',
        'MT' => 'Malta',
        'MH' => 'Marshall Islands',
        'MQ' => 'Martinique',
        'MR' => 'Mauritania',
        'MU' => 'Mauritius',
        'YT' => 'Mayotte',
        'MX' => 'Mexico',
        'FM' => 'Micronesia',
        'MD' => 'Moldova, Republic of ',
        'MC' => 'Monaco',
        'MN' => 'Mongolia',
        'MS' => 'Monserrat',
        'MA' => 'Morocco',
        'MZ' => 'Mozambique',
        'MM' => 'Myanmar',
        'NA' => 'Namibia',
        'NR' => 'Nauru',
        'NP' => 'Nepal',
        'NL' => 'Netherlands',
        'AN' => 'Netherlands Antilles',
        'NC' => 'New Caledonia',
        'NZ' => 'New Zealand',
        'NI' => 'Nicaragua',
        'NE' => 'Niger',
        'NG' => 'Nigeria',
        'NU' => 'Niue',
        'NF' => 'Norfolk Island',
        'MP' => 'Northern Mariana Islands',
        'NO' => 'Norway',
        'OM' => 'Oman',
        'PK' => 'Pakistan',
        'PW' => 'Palau',
        'PA' => 'Panama',
        'PG' => 'Papua New Guinea',
        'PY' => 'Paraguay',
        'PE' => 'Peru',
        'PH' => 'Philippines',
        'PN' => 'Pitcairn',
        'PL' => 'Poland',
        'PT' => 'Portugal',
        'PR' => 'Puerto Rico',
        'QA' => 'Qatar',
        'RE' => 'Reunion',
        'RO' => 'Romania',
        'RU' => 'Russian Federation',
        'RW' => 'Rwanda',
        'LC' => 'Saint Lucia',
        'WS' => 'Samoa',
        'SM' => 'San Marino',
        'ST' => 'Sao Tome & Principe',
        'SA' => 'Saudi Arabia',
        'SN' => 'Senegal',
        'SC' => 'Seychelles',
        'SL' => 'Sierra Leone',
        'SG' => 'Singapore',
        'SK' => 'Slovakia',
        'SI' => 'Slovenia',
        'SB' => 'Solomon Islands',
        'SO' => 'Somalia',
        'ZA' => 'South Africa',
        'ES' => 'Spain',
        'LK' => 'Sri Lanka',
        'SH' => 'St. Helena',
        'KN' => 'St. Kitts and Nevis',
        'PM' => 'St. Pierre & Miquelon',
        'VC' => 'St. Vincent & the Grenadines',
        'SD' => 'Sudan',
        'SR' => 'Suriname',
        'SJ' => 'Svalbard & Jan Mayen Islands',
        'SZ' => 'Swaziland',
        'SE' => 'Sweden',
        'CH' => 'Switzerland',
        'SY' => 'Syrian Arab Republic',
        'TW' => 'Taiwan',
        'TJ' => 'Tajikistan',
        'TZ' => 'Tanzania, United Republic of',
        'TH' => 'Thailand',
        'TG' => 'Togo',
        'TK' => 'Tokelau',
        'TO' => 'Tonga',
        'TT' => 'Trinidad & Tobago',
        'TN' => 'Tunisia',
        'TR' => 'Turkey',
        'TM' => 'Turkmenistan',
        'TC' => 'Turks & Caicos Islands',
        'TV' => 'Tuvalu',
        'UG' => 'Uganda',
        'UA' => 'Ukraine',
        'AE' => 'United Arab Emirates',
        'UM' => 'United States Minor Outlying',
        'VI' => 'United States Virgin Islands',
        'UY' => 'Uruguay',
        'UZ' => 'Uzbekistan',
        'VU' => 'Vanuatu',
        'VA' => 'Vatican City State (Holy See)',
        'VE' => 'Venezuela',
        'VN' => 'Viet Nam',
        'WF' => 'Wallis & Futuna Islands',
        'EH' => 'Western Sahara',
        'YE' => 'Yemen',
        'YU' => 'Yugoslavia',
        'ZR' => 'Zaire',
        'ZM' => 'Zambia',
        'ZW' => 'Zimbabwe'
    );
}