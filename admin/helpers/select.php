<?php
/**
 * @package    JVoter
 * @copyright  Copyright (C) 2019 JVoter. All rights reserved.
 * @license    GNU General Public License version 3, or later
 */
defined('_JEXEC') or die('Restricted access');

/**
 * A helper class for drop-down selection boxes
 */
abstract class JVoterSelect
{

	/**
	 * Maps the two letter codes to country names (in English)
	 *
	 * @var array
	 */
	public static $countries = array(
			'' => '----',
			'AD' => 'Andorra',
			'AE' => 'United Arab Emirates',
			'AF' => 'Afghanistan',
			'AG' => 'Antigua and Barbuda',
			'AI' => 'Anguilla',
			'AL' => 'Albania',
			'AM' => 'Armenia',
			'AO' => 'Angola',
			'AQ' => 'Antarctica',
			'AR' => 'Argentina',
			'AS' => 'American Samoa',
			'AT' => 'Austria',
			'AU' => 'Australia',
			'AW' => 'Aruba',
			'AX' => 'Aland Islands',
			'AZ' => 'Azerbaijan',
			'BA' => 'Bosnia and Herzegovina',
			'BB' => 'Barbados',
			'BD' => 'Bangladesh',
			'BE' => 'Belgium',
			'BF' => 'Burkina Faso',
			'BG' => 'Bulgaria',
			'BH' => 'Bahrain',
			'BI' => 'Burundi',
			'BJ' => 'Benin',
			'BL' => 'Saint Barthélemy',
			'BM' => 'Bermuda',
			'BN' => 'Brunei Darussalam',
			'BO' => 'Bolivia, Plurinational State of',
			'BQ' => 'Bonaire, Saint Eustatius and Saba',
			'BR' => 'Brazil',
			'BS' => 'Bahamas',
			'BT' => 'Bhutan',
			'BV' => 'Bouvet Island',
			'BW' => 'Botswana',
			'BY' => 'Belarus',
			'BZ' => 'Belize',
			'CA' => 'Canada',
			'CC' => 'Cocos (Keeling) Islands',
			'CD' => 'Congo, the Democratic Republic of the',
			'CF' => 'Central African Republic',
			'CG' => 'Congo',
			'CH' => 'Switzerland',
			'CI' => 'Cote d\'Ivoire',
			'CK' => 'Cook Islands',
			'CL' => 'Chile',
			'CM' => 'Cameroon',
			'CN' => 'China',
			'CO' => 'Colombia',
			'CR' => 'Costa Rica',
			'CU' => 'Cuba',
			'CV' => 'Cape Verde',
			'CW' => 'Curaçao',
			'CX' => 'Christmas Island',
			'CY' => 'Cyprus',
			'CZ' => 'Czech Republic',
			'DE' => 'Germany',
			'DJ' => 'Djibouti',
			'DK' => 'Denmark',
			'DM' => 'Dominica',
			'DO' => 'Dominican Republic',
			'DZ' => 'Algeria',
			'EC' => 'Ecuador',
			'EE' => 'Estonia',
			'EG' => 'Egypt',
			'EH' => 'Western Sahara',
			'ER' => 'Eritrea',
			'ES' => 'Spain',
			'ET' => 'Ethiopia',
			'FI' => 'Finland',
			'FJ' => 'Fiji',
			'FK' => 'Falkland Islands (Malvinas)',
			'FM' => 'Micronesia, Federated States of',
			'FO' => 'Faroe Islands',
			'FR' => 'France',
			'GA' => 'Gabon',
			'GB' => 'United Kingdom',
			'GD' => 'Grenada',
			'GE' => 'Georgia',
			'GF' => 'French Guiana',
			'GG' => 'Guernsey',
			'GH' => 'Ghana',
			'GI' => 'Gibraltar',
			'GL' => 'Greenland',
			'GM' => 'Gambia',
			'GN' => 'Guinea',
			'GP' => 'Guadeloupe',
			'GQ' => 'Equatorial Guinea',
			'GR' => 'Greece',
			'GS' => 'South Georgia and the South Sandwich Islands',
			'GT' => 'Guatemala',
			'GU' => 'Guam',
			'GW' => 'Guinea-Bissau',
			'GY' => 'Guyana',
			'HK' => 'Hong Kong',
			'HM' => 'Heard Island and McDonald Islands',
			'HN' => 'Honduras',
			'HR' => 'Croatia',
			'HT' => 'Haiti',
			'HU' => 'Hungary',
			'ID' => 'Indonesia',
			'IE' => 'Ireland',
			'IL' => 'Israel',
			'IM' => 'Isle of Man',
			'IN' => 'India',
			'IO' => 'British Indian Ocean Territory',
			'IQ' => 'Iraq',
			'IR' => 'Iran, Islamic Republic of',
			'IS' => 'Iceland',
			'IT' => 'Italy',
			'JE' => 'Jersey',
			'JM' => 'Jamaica',
			'JO' => 'Jordan',
			'JP' => 'Japan',
			'KE' => 'Kenya',
			'KG' => 'Kyrgyzstan',
			'KH' => 'Cambodia',
			'KI' => 'Kiribati',
			'KM' => 'Comoros',
			'KN' => 'Saint Kitts and Nevis',
			'KP' => 'Korea, Democratic People\'s Republic of',
			'KR' => 'Korea, Republic of',
			'KW' => 'Kuwait',
			'KY' => 'Cayman Islands',
			'KZ' => 'Kazakhstan',
			'LA' => 'Lao People\'s Democratic Republic',
			'LB' => 'Lebanon',
			'LC' => 'Saint Lucia',
			'LI' => 'Liechtenstein',
			'LK' => 'Sri Lanka',
			'LR' => 'Liberia',
			'LS' => 'Lesotho',
			'LT' => 'Lithuania',
			'LU' => 'Luxembourg',
			'LV' => 'Latvia',
			'LY' => 'Libyan Arab Jamahiriya',
			'MA' => 'Morocco',
			'MC' => 'Monaco',
			'MD' => 'Moldova, Republic of',
			'ME' => 'Montenegro',
			'MF' => 'Saint Martin (French part)',
			'MG' => 'Madagascar',
			'MH' => 'Marshall Islands',
			'MK' => 'Macedonia, the former Yugoslav Republic of',
			'ML' => 'Mali',
			'MM' => 'Myanmar',
			'MN' => 'Mongolia',
			'MO' => 'Macao',
			'MP' => 'Northern Mariana Islands',
			'MQ' => 'Martinique',
			'MR' => 'Mauritania',
			'MS' => 'Montserrat',
			'MT' => 'Malta',
			'MU' => 'Mauritius',
			'MV' => 'Maldives',
			'MW' => 'Malawi',
			'MX' => 'Mexico',
			'MY' => 'Malaysia',
			'MZ' => 'Mozambique',
			'NA' => 'Namibia',
			'NC' => 'New Caledonia',
			'NE' => 'Niger',
			'NF' => 'Norfolk Island',
			'NG' => 'Nigeria',
			'NI' => 'Nicaragua',
			'NL' => 'Netherlands',
			'NO' => 'Norway',
			'NP' => 'Nepal',
			'NR' => 'Nauru',
			'NU' => 'Niue',
			'NZ' => 'New Zealand',
			'OM' => 'Oman',
			'PA' => 'Panama',
			'PE' => 'Peru',
			'PF' => 'French Polynesia',
			'PG' => 'Papua New Guinea',
			'PH' => 'Philippines',
			'PK' => 'Pakistan',
			'PL' => 'Poland',
			'PM' => 'Saint Pierre and Miquelon',
			'PN' => 'Pitcairn',
			'PR' => 'Puerto Rico',
			'PS' => 'Palestinian Territory, Occupied',
			'PT' => 'Portugal',
			'PW' => 'Palau',
			'PY' => 'Paraguay',
			'QA' => 'Qatar',
			'RE' => 'Reunion',
			'RO' => 'Romania',
			'RS' => 'Serbia',
			'RU' => 'Russian Federation',
			'RW' => 'Rwanda',
			'SA' => 'Saudi Arabia',
			'SB' => 'Solomon Islands',
			'SC' => 'Seychelles',
			'SD' => 'Sudan',
			'SE' => 'Sweden',
			'SG' => 'Singapore',
			'SH' => 'Saint Helena, Ascension and Tristan da Cunha',
			'SI' => 'Slovenia',
			'SJ' => 'Svalbard and Jan Mayen',
			'SK' => 'Slovakia',
			'SL' => 'Sierra Leone',
			'SM' => 'San Marino',
			'SN' => 'Senegal',
			'SO' => 'Somalia',
			'SR' => 'Suriname',
			'SS' => 'South Sudan',
			'ST' => 'Sao Tome and Principe',
			'SV' => 'El Salvador',
			'SX' => 'Sint Maarten',
			'SY' => 'Syrian Arab Republic',
			'SZ' => 'Swaziland',
			'TC' => 'Turks and Caicos Islands',
			'TD' => 'Chad',
			'TF' => 'French Southern Territories',
			'TG' => 'Togo',
			'TH' => 'Thailand',
			'TJ' => 'Tajikistan',
			'TK' => 'Tokelau',
			'TL' => 'Timor-Leste',
			'TM' => 'Turkmenistan',
			'TN' => 'Tunisia',
			'TO' => 'Tonga',
			'TR' => 'Turkey',
			'TT' => 'Trinidad and Tobago',
			'TV' => 'Tuvalu',
			'TW' => 'Taiwan',
			'TZ' => 'Tanzania, United Republic of',
			'UA' => 'Ukraine',
			'UG' => 'Uganda',
			'UM' => 'United States Minor Outlying Islands',
			'US' => 'United States',
			'UY' => 'Uruguay',
			'UZ' => 'Uzbekistan',
			'VA' => 'Holy See (Vatican City State)',
			'VC' => 'Saint Vincent and the Grenadines',
			'VE' => 'Venezuela, Bolivarian Republic of',
			'VG' => 'Virgin Islands, British',
			'VI' => 'Virgin Islands, U.S.',
			'VN' => 'Viet Nam',
			'VU' => 'Vanuatu',
			'WF' => 'Wallis and Futuna',
			'WS' => 'Samoa',
			'YE' => 'Yemen',
			'YT' => 'Mayotte',
			'ZA' => 'South Africa',
			'ZM' => 'Zambia',
			'ZW' => 'Zimbabwe'
	);

	/**
	 * Maps countries to state short codes and names
	 *
	 * @var array
	 */
	public static $states = array();

	/**
	 * Returns a list of all countries except the empty option (no country)
	 *
	 * @return array
	 */
	public static function getCountriesForHeader ()
	{
		static $countries = array();
		
		if (empty($countries))
		{
			$countries = self::$countries;
			unset($countries['']);
		}
		
		return $countries;
	}

	/**
	 * Returns a list of all countries including the empty option (no country)
	 *
	 * @return array
	 */
	public static function getCountries ()
	{
		return self::$countries;
	}

	/**
	 * Returns a list of all states
	 *
	 * @return array
	 */
	public static function getStates ()
	{
		static $states = array();
		
		if (empty($states))
		{
			$states = array();
			
			foreach (self::$states as $country => $s)
			{
				$states = array_merge($states, $s);
			}
		}
		
		return $states;
	}

	/**
	 * Translate a two letter country code into the country name (in English).
	 * If the country is unknown the country
	 * code itself is returned.
	 *
	 * @param string $cCode
	 *        	The country code
	 *        	
	 * @return string The name of the country or, of it's not known, the country
	 *         code itself.
	 */
	public static function decodeCountry ($cCode)
	{
		if (array_key_exists($cCode, self::$countries))
		{
			return self::$countries[$cCode];
		}
		else
		{
			return $cCode;
		}
	}

	/**
	 * Translate a two letter country code into the country name (in English).
	 * If the country is unknown three em-dashes
	 * are returned. This is different to decode country which returns the
	 * country code in this case.
	 *
	 * @param string $cCode
	 *        	The country code
	 *        	
	 * @return string The name of the country or, of it's not known, the country
	 *         code itself.
	 */
	public static function formatCountry ($cCode = '')
	{
		$name = self::decodeCountry($cCode);
		
		if ($name == $cCode)
		{
			$name = '&mdash;';
		}
		
		return $name;
	}

	/**
	 * Translate the short state code into the full, human-readable state name.
	 * If the state is unknown three em-dashes
	 * are returned instead.
	 *
	 * @param string $state
	 *        	The state code
	 *        	
	 * @return string The human readable state name
	 */
	public static function formatState ($state)
	{
		$name = '&mdash;';
		
		foreach (self::$states as $country => $states)
		{
			if (array_key_exists($state, $states))
			{
				$name = $states[$state];
			}
		}
		
		return $name;
	}

	/**
	 * Return a generic drop-down list
	 *
	 * @param array $list
	 *        	An array of objects, arrays, or scalars.
	 * @param string $name
	 *        	The value of the HTML name attribute.
	 * @param mixed $attribs
	 *        	Additional HTML attributes for the <select> tag. This
	 *        	can be an array of attributes, or an array of options. Treated
	 *        	as options
	 *        	if it is the last argument passed. Valid options are:
	 *        	Format options, see {@see JHtml::$formatOptions}.
	 *        	Selection options, see {@see JHtmlSelect::options()}.
	 *        	list.attr, string|array: Additional attributes for the select
	 *        	element.
	 *        	id, string: Value to use as the select element id attribute.
	 *        	Defaults to the same as the name.
	 *        	list.select, string|array: Identifies one or more option
	 *        	elements
	 *        	to be selected, based on the option key values.
	 * @param mixed $selected
	 *        	The key that is selected (accepts an array or a string).
	 * @param string $idTag
	 *        	Value of the field id or null by default
	 *        	
	 * @return string HTML for the select list
	 */
	protected static function genericlist ($list, $name, $attribs = null, $selected = null, $idTag = null)
	{
		if (empty($attribs))
		{
			$attribs = null;
		}
		else
		{
			$temp = '';
			
			foreach ($attribs as $key => $value)
			{
				$temp .= ' ' . $key . '="' . $value . '"';
			}
			
			$attribs = $temp;
		}
		
		return JHtml::_('select.genericlist', $list, $name, $attribs, 'value', 'text', $selected, $idTag);
	}

	/**
	 * Generates an HTML radio list.
	 *
	 * @param array $list
	 *        	An array of objects
	 * @param string $name
	 *        	The value of the HTML name attribute
	 * @param string $attribs
	 *        	Additional HTML attributes for the <select> tag
	 * @param string $selected
	 *        	The name of the object variable for the option text
	 * @param boolean $idTag
	 *        	Value of the field id or null by default
	 *        	
	 * @return string HTML for the select list
	 */
	protected static function genericradiolist ($list, $name, $attribs = null, $selected = null, $idTag = null)
	{
		if (empty($attribs))
		{
			$attribs = null;
		}
		else
		{
			$temp = '';
			
			foreach ($attribs as $key => $value)
			{
				$temp .= $key . ' = "' . $value . '"';
			}
			
			$attribs = $temp;
		}
		
		return JHtml::_('select.radiolist', $list, $name, $attribs, 'value', 'text', $selected, $idTag);
	}

	/**
	 * Generates a yes/no drop-down list.
	 *
	 * @param string $name
	 *        	The value of the HTML name attribute
	 * @param array $attribs
	 *        	Additional HTML attributes for the <select> tag
	 * @param string $selected
	 *        	The key that is selected
	 *        	
	 * @return string HTML for the list
	 */
	public static function booleanlist ($name, $attribs = null, $selected = null)
	{
		$options = array(
				JHtml::_('select.option', '', '---'),
				JHtml::_('select.option', '0', JText::_('JNO')),
				JHtml::_('select.option', '1', JText::_('JYES'))
		);
		
		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	public static function getFilteredCountries ($force = false)
	{
		static $countries = null;
		
		if (is_null($countries) || $force)
		{
			$countries = array_merge(self::$countries);
		}
		
		return $countries;
	}

	/**
	 * Returns a drop-down selection box for countries.
	 * Some special attributes:
	 *
	 * show An array of country codes to display. Takes precedence over hide.
	 * hide An array of country codes to hide.
	 *
	 * @param string $selected
	 *        	Selected country code
	 * @param string $id
	 *        	Field name and ID
	 * @param array $attribs
	 *        	Field attributes
	 *        	
	 * @return string
	 */
	public static function countries ($selected = null, $id = 'country', $attribs = array())
	{
		// Get the raw list of countries
		$options = array();
		$countries = self::$countries;
		asort($countries);
		
		// Parse show / hide options
		
		// -- Initialisation
		$show = array();
		$hide = array();
		
		// -- Parse the show attribute
		if (isset($attribs['show']))
		{
			$show = trim($attribs['show']);
			
			if (! empty($show))
			{
				$show = explode(',', $show);
			}
			else
			{
				$show = array();
			}
			
			unset($attribs['show']);
		}
		
		// -- Parse the hide attribute
		if (isset($attribs['hide']))
		{
			$hide = trim($attribs['hide']);
			
			if (! empty($hide))
			{
				$hide = explode(',', $hide);
			}
			else
			{
				$hide = array();
			}
			
			unset($attribs['hide']);
		}
		
		// -- If $show is not empty, filter the countries
		if (count($show))
		{
			$temp = array();
			
			foreach ($show as $key)
			{
				if (array_key_exists($key, $countries))
				{
					$temp[$key] = $countries[$key];
				}
			}
			
			asort($temp);
			$countries = $temp;
		}
		
		// -- If $show is empty but $hide is not, filter the countries
		elseif (count($hide))
		{
			$temp = array();
			
			foreach ($countries as $key => $v)
			{
				if (! in_array($key, $hide))
				{
					$temp[$key] = $v;
				}
			}
			
			asort($temp);
			$countries = $temp;
		}
		
		foreach ($countries as $code => $name)
		{
			$options[] = JHtml::_('select.option', $code, $name);
		}
		
		return self::genericlist($options, $id, $attribs, $selected, $id);
	}

	/**
	 * Displays a list of the available user groups.
	 *
	 * @param string $name
	 *        	The form field name.
	 * @param string $selected
	 *        	The name of the selected section.
	 * @param array $attribs
	 *        	Additional attributes to add to the select field.
	 *        	
	 * @return string The HTML for the list
	 */
	public static function usergroups ($name = 'usergroups', $selected = '', $attribs = array())
	{
		return JHtml::_('access.usergroup', $name, $selected, $attribs, false);
	}

	/**
	 * Generates a drop-down list for the available languages of a
	 * multi-language site.
	 *
	 * @param string $selected
	 *        	The key that is selected
	 * @param string $id
	 *        	The value of the HTML name attribute
	 * @param array $attribs
	 *        	Additional HTML attributes for the <select> tag
	 *        	
	 * @return string HTML for the list
	 */
	public static function languages ($selected = null, $id = 'language', $attribs = array())
	{
		JLoader::import('joomla.language.helper');
		$languages = \JLanguageHelper::getLanguages('lang_code');
		$options = array();
		$options[] = JHtml::_('select.option', '*', JText::_('JALL_LANGUAGE'));
		
		if (! empty($languages))
		{
			foreach ($languages as $key => $lang)
			{
				$options[] = JHtml::_('select.option', $key, $lang->title);
			}
		}
		
		return self::genericlist($options, $id, $attribs, $selected, $id);
	}
}
