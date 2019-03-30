<?php
/**
 * @package    JVoter
 * @copyright  Copyright (C) 2019 JVoter. All rights reserved.
 * @license    GNU General Public License version 3, or later
 */
defined('_JEXEC') or die('Restricted access');

abstract class OTBPayment extends JPlugin
{

    /**
     * Name of the plugin, returned to the component
     *
     * @var string
     */
    protected $ppName = 'abstract';

    /**
     * Translation key of the plugin's title, returned to the component
     *
     * @var string
     */
    protected $ppKey = 'PLG_OTBPAYMENT_ABSTRACT_TITLE';

    /**
     * Image path, returned to the component
     *
     * @var string
     */
    protected $ppImage = '';

    /**
     * Public constructor for the plugin
     *
     * @param object $subject
     *            The object to observe
     * @param array $config
     *            An optional associative array of configuration settings.
     */
    public function __construct(&$subject, $config = array())
    {
        if(! is_object($config['params']))
        {
            $config['params'] = new JRegistry($config['params']);
        }
        
        parent::__construct($subject, $config);
        
        if(array_key_exists('ppName', $config))
        {
            $this->ppName = $config['ppName'];
        }
        
        if(array_key_exists('ppImage', $config))
        {
            $this->ppImage = $config['ppImage'];
        }
        
        $name = $this->ppName;
        
        if(array_key_exists('ppKey', $config))
        {
            $this->ppKey = $config['ppKey'];
        } else
        {
            $this->ppKey = "PLG_OTBPAYMENT_{$name}_TITLE";
        }
        
        // Load the language files
        $jlang = JFactory::getLanguage();
        $jlang->load('plg_otbpayment_' . $name, JPATH_ADMINISTRATOR, 'en-GB', true);
        $jlang->load('plg_otbpayment_' . $name, JPATH_ADMINISTRATOR, $jlang->getDefault(), true);
        $jlang->load('plg_otbpayment_' . $name, JPATH_ADMINISTRATOR, null, true);
    }

    /**
     * Plugin event which returns the identity information of this payment
     * method.
     * The result is an array containing one or more associative arrays.
     * If the plugin only provides a single payment method you should only
     * return an array containing just one associative array. The assoc array
     * has the keys 'name' (the name of the payment method), 'title'
     * (translation key for the payment method's name) and 'image' (the URL to
     * the image used for this payment method).
     *
     * @return array
     */
    public function onOTBPaymentGetIdentity()
    {
        $title = $this->params->get('title', '');
        $image = trim($this->params->get('ppimage', ''));
        
        if(empty($title))
        {
            $title = JText::_($this->ppKey);
        }
        
        if(empty($image))
        {
            $image = $this->ppImage;
        }
        
        $ret = array(
            $this->ppName => (object) array(
                'name' => $this->ppName,
                'title' => $title,
                'image' => $image
            )
        );
        
        return $ret;
    }

    /**
     * Returns the payment form to be submitted by the user's browser.
     * The form must have an ID of
     * "paymentForm" and a visible submit button.
     *
     * @param string $paymentmethod
     *            The currently used payment method. Check it against
     *            $this->ppName.
     * @param $campaign_id campaign
     *            id
     * @param array $data
     *            Input (request) data
     *            
     * @return string The payment form to render on the page. Use the special id
     *         'paymentForm' to have it
     *         automatically submitted after 5 seconds.
     */
    abstract public function onOTBPaymentNew($paymentmethod, $campaign_id, $data);

    /**
     * Processes a callback from the payment processor
     *
     * @param string $paymentmethod
     *            The currently used payment method. Check it against
     *            $this->ppName
     * @param array $data
     *            Input (request) data
     *            
     * @return boolean True if the callback was handled, false otherwise
     */
    abstract public function onOTBPaymentCallback($paymentmethod, $data);

    /**
     * Get the path to a layout file
     *
     * @param string $layout
     *            The name of the plugin layout file
     * @return string The path to the plugin layout file
     * @access protected
     */
    protected function getLayoutPath($layout = 'default')
    {
        $app = \JFactory::getApplication();
        // get the template and default paths for the layout
        $path = JPATH_SITE . '/templates/' . $app->getTemplate() . '/html/plugins/' . $this->_type . '/' . $this->_name . '/' . $layout . '.php';
        
        // if the site template has a layout override, use it
        jimport('joomla.filesystem.file');
        if(! JFile::exists($path))
        {
            $path = JPATH_SITE . '/plugins/' . $this->_type . '/' . $this->_name . '/' . $this->_name . '/tmpl/' . $layout . '.php';
        }
        
        return $path;
    }

    /**
     * Logs the received IPN information to file
     *
     * @param array $data
     *            Request data
     * @param boolean $isValid
     *            Is it a valid payment?
     *            
     * @return void
     */
    protected function logIPN($data, $isValid)
    {
        $logpath = \JFactory::getApplication()->get('log_path');
        
        $logFilenameBase = $logpath . '/otbpayment_' . strtolower($this->ppName) . '_ipn';
        
        $logFile = $logFilenameBase . '.php';
        
        JLoader::import('joomla.filesystem.file');
        
        if(! JFile::exists($logFile))
        {
            $dummy = "<?php die(); ?>\n";
            JFile::write($logFile, $dummy);
        } else
        {
            if(@filesize($logFile) > 1048756)
            {
                $altLog = $logFilenameBase . '-1.php';
                
                if(JFile::exists($altLog))
                {
                    JFile::delete($altLog);
                }
                
                JFile::copy($logFile, $altLog);
                JFile::delete($logFile);
                
                $dummy = "<?php die(); ?>\n";
                
                JFile::write($logFile, $dummy);
            }
        }
        
        $logData = @file_get_contents($logFile);
        
        if($logData === false)
        {
            $logData = '';
        }
        
        $logData .= "\n" . str_repeat('-', 80);
        $pluginName = strtoupper($this->ppName);
        $logData .= $isValid ? 'VALID ' . $pluginName . ' IPN' : 'INVALID ' . $pluginName . ' IPN *** FRAUD ATTEMPT OR INVALID NOTIFICATION ***';
        $logData .= "\nDate/time : " . gmdate('Y-m-d H:i:s') . " GMT\n\n";
        
        foreach($data as $key => $value)
        {
            $logData .= '  ' . str_pad($key, 30, ' ') . $value . "\n";
        }
        
        $logData .= "\n";
        
        JFile::write($logFile, $logData);
    }

    /**
     * Translates the given 2-digit country code into the 3-digit country code.
     *
     * @param string $country
     *            The 2 digit country code
     *            
     * @return string The 3 digit country code
     */
    protected function translateCountry($country)
    {
        $countryMap = array(
            'AX' => 'ALA',
            'AF' => 'AFG',
            'AL' => 'ALB',
            'DZ' => 'DZA',
            'AS' => 'ASM',
            'AD' => 'AND',
            'AO' => 'AGO',
            'AI' => 'AIA',
            'AQ' => 'ATA',
            'AG' => 'ATG',
            'AR' => 'ARG',
            'AM' => 'ARM',
            'AW' => 'ABW',
            'AU' => 'AUS',
            'AT' => 'AUT',
            'AZ' => 'AZE',
            'BS' => 'BHS',
            'BH' => 'BHR',
            'BD' => 'BGD',
            'BB' => 'BRB',
            'BY' => 'BLR',
            'BE' => 'BEL',
            'BZ' => 'BLZ',
            'BJ' => 'BEN',
            'BM' => 'BMU',
            'BT' => 'BTN',
            'BO' => 'BOL',
            'BA' => 'BIH',
            'BW' => 'BWA',
            'BV' => 'BVT',
            'BR' => 'BRA',
            'IO' => 'IOT',
            'BN' => 'BRN',
            'BG' => 'BGR',
            'BF' => 'BFA',
            'BI' => 'BDI',
            'KH' => 'KHM',
            'CM' => 'CMR',
            'CA' => 'CAN',
            'CV' => 'CPV',
            'KY' => 'CYM',
            'CF' => 'CAF',
            'TD' => 'TCD',
            'CL' => 'CHL',
            'CN' => 'CHN',
            'CX' => 'CXR',
            'CC' => 'CCK',
            'CO' => 'COL',
            'KM' => 'COM',
            'CD' => 'COD',
            'CG' => 'COG',
            'CK' => 'COK',
            'CR' => 'CRI',
            'CI' => 'CIV',
            'HR' => 'HRV',
            'CU' => 'CUB',
            'CY' => 'CYP',
            'CZ' => 'CZE',
            'DK' => 'DNK',
            'DJ' => 'DJI',
            'DM' => 'DMA',
            'DO' => 'DOM',
            'EC' => 'ECU',
            'EG' => 'EGY',
            'SV' => 'SLV',
            'GQ' => 'GNQ',
            'ER' => 'ERI',
            'EE' => 'EST',
            'ET' => 'ETH',
            'FK' => 'FLK',
            'FO' => 'FRO',
            'FJ' => 'FJI',
            'FI' => 'FIN',
            'FR' => 'FRA',
            'GF' => 'GUF',
            'PF' => 'PYF',
            'TF' => 'ATF',
            'GA' => 'GAB',
            'GM' => 'GMB',
            'GE' => 'GEO',
            'DE' => 'DEU',
            'GH' => 'GHA',
            'GI' => 'GIB',
            'GR' => 'GRC',
            'GL' => 'GRL',
            'GD' => 'GRD',
            'GP' => 'GLP',
            'GU' => 'GUM',
            'GT' => 'GTM',
            'GN' => 'GIN',
            'GW' => 'GNB',
            'GY' => 'GUY',
            'HT' => 'HTI',
            'HM' => 'HMD',
            'HN' => 'HND',
            'HK' => 'HKG',
            'HU' => 'HUN',
            'IS' => 'ISL',
            'IN' => 'IND',
            'ID' => 'IDN',
            'IR' => 'IRN',
            'IQ' => 'IRQ',
            'IE' => 'IRL',
            'IL' => 'ISR',
            'IT' => 'ITA',
            'JM' => 'JAM',
            'JP' => 'JPN',
            'JO' => 'JOR',
            'KZ' => 'KAZ',
            'KE' => 'KEN',
            'KI' => 'KIR',
            'KP' => 'PRK',
            'KR' => 'KOR',
            'KW' => 'KWT',
            'KG' => 'KGZ',
            'LA' => 'LAO',
            'LV' => 'LVA',
            'LB' => 'LBN',
            'LS' => 'LSO',
            'LR' => 'LBR',
            'LY' => 'LBY',
            'LI' => 'LIE',
            'LT' => 'LTU',
            'LU' => 'LUX',
            'MO' => 'MAC',
            'MK' => 'MKD',
            'MG' => 'MDG',
            'MW' => 'MWI',
            'MY' => 'MYS',
            'MV' => 'MDV',
            'ML' => 'MLI',
            'MT' => 'MLT',
            'MH' => 'MHL',
            'MQ' => 'MTQ',
            'MR' => 'MRT',
            'MU' => 'MUS',
            'YT' => 'MYT',
            'MX' => 'MEX',
            'FM' => 'FSM',
            'MD' => 'MDA',
            'MC' => 'MCO',
            'MN' => 'MNG',
            'MS' => 'MSR',
            'MA' => 'MAR',
            'MZ' => 'MOZ',
            'MM' => 'MMR',
            'NA' => 'NAM',
            'NR' => 'NRU',
            'NP' => 'NPL',
            'NL' => 'NLD',
            'AN' => 'ANT',
            'NC' => 'NCL',
            'NZ' => 'NZL',
            'NI' => 'NIC',
            'NE' => 'NER',
            'NG' => 'NGA',
            'NU' => 'NIU',
            'NF' => 'NFK',
            'MP' => 'MNP',
            'NO' => 'NOR',
            'OM' => 'OMN',
            'PK' => 'PAK',
            'PW' => 'PLW',
            'PS' => 'PSE',
            'PA' => 'PAN',
            'PG' => 'PNG',
            'PY' => 'PRY',
            'PE' => 'PER',
            'PH' => 'PHL',
            'PN' => 'PCN',
            'PL' => 'POL',
            'PT' => 'PRT',
            'PR' => 'PRI',
            'QA' => 'QAT',
            'RE' => 'REU',
            'RO' => 'ROU',
            'RU' => 'RUS',
            'RW' => 'RWA',
            'SH' => 'SHN',
            'KN' => 'KNA',
            'LC' => 'LCA',
            'PM' => 'SPM',
            'VC' => 'VCT',
            'WS' => 'WSM',
            'SM' => 'SMR',
            'ST' => 'STP',
            'SA' => 'SAU',
            'SN' => 'SEN',
            'CS' => 'SCG',
            'SC' => 'SYC',
            'SL' => 'SLE',
            'SG' => 'SGP',
            'SK' => 'SVK',
            'SI' => 'SVN',
            'SB' => 'SLB',
            'SO' => 'SOM',
            'ZA' => 'ZAF',
            'GS' => 'SGS',
            'ES' => 'ESP',
            'LK' => 'LKA',
            'SD' => 'SDN',
            'SR' => 'SUR',
            'SJ' => 'SJM',
            'SZ' => 'SWZ',
            'SE' => 'SWE',
            'CH' => 'CHE',
            'SY' => 'SYR',
            'TW' => 'TWN',
            'TJ' => 'TJK',
            'TZ' => 'TZA',
            'TH' => 'THA',
            'TL' => 'TLS',
            'TG' => 'TGO',
            'TK' => 'TKL',
            'TO' => 'TON',
            'TT' => 'TTO',
            'TN' => 'TUN',
            'TR' => 'TUR',
            'TM' => 'TKM',
            'TC' => 'TCA',
            'TV' => 'TUV',
            'UG' => 'UGA',
            'UA' => 'UKR',
            'AE' => 'ARE',
            'GB' => 'GBR',
            'US' => 'USA',
            'UM' => 'UMI',
            'UY' => 'URY',
            'UZ' => 'UZB',
            'VU' => 'VUT',
            'VA' => 'VAT',
            'VE' => 'VEN',
            'VN' => 'VNM',
            'VG' => 'VGB',
            'VI' => 'VIR',
            'WF' => 'WLF',
            'EH' => 'ESH',
            'YE' => 'YEM',
            'ZM' => 'ZMB',
            'ZW' => 'ZWE'
        );
        
        if(array_key_exists($country, $countryMap))
        {
            return $countryMap[$country];
        } else
        {
            return '';
        }
    }

    protected function debug($string)
    {
        static $logDir = '';
        
        if(empty($logDir))
        {
            $defLogDir = (version_compare(JVERSION, '3.5.999', 'le') ? JPATH_ROOT : JPATH_ADMINISTRATOR) . '/logs';
            $logDir = \JFactory::getApplication()->get('log_path', $defLogDir);
            $logDir = rtrim($logDir, '/' . DIRECTORY_SEPARATOR);
        }
        
        $handle = fopen($logDir . '/log.txt', 'a+');
        fwrite($handle, date('Y-m-d H:i:s') . ' --- ' . $string . PHP_EOL);
        fclose($handle);
    }
}