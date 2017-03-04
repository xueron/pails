<?php
/**
 * This file is part of the Supreme Shop Middleware.
 *
 * @author Supreme NewMedia GmbH <entwicklung@supreme.de>
 * @copyright 2012-2014 Supreme NewMedia GmbH
 * @license MIT License
 * @package Finance
 */

namespace Pails\Utils\Finance;

/**
 * ISO 4217 Currency
 */
class Currency
{

    const ISO_STATUS_ACTIVE = 'ISO_STATUS_ACTIVE';
    const ISO_STATUS_WITHOUT_CURRENCY_CODE = 'ISO_STATUS_WITHOUT_CURRENCY_CODE';
    const ISO_STATUS_UNOFFICIAL = 'ISO_STATUS_UNOFFICIAL';
    const ISO_STATUS_HISTORICAL = 'ISO_STATUS_HISTORICAL';

    /**
     * The three-letter currency code
     *
     * @var string the three-letter currency code
     */
    private $code;

    /**
     * One of 'ISO_STATUS_ACTIVE', 'ISO_STATUS_WITHOUT_CURRENCY_CODE', 'ISO_STATUS_UNOFFICIAL' or
     * 'ISO_STATUS_HISTORICAL'
     *
     * @var string one of 'ISO_STATUS_ACTIVE', 'ISO_STATUS_WITHOUT_CURRENCY_CODE', 'ISO_STATUS_UNOFFICIAL' or
     * 'ISO_STATUS_HISTORICAL'
     */
    private $isoStatus;

    /**
     * The number of decimal places used for this currency
     *
     * @var int the number of decimal places used for this currency
     */
    private $decimalDigits;

    /**
     * The name as specified in en.wikipedia.org
     *
     * @var string the name as specified in en.wikipedia.org
     */
    private $name;

    /**
     * The currency sign.
     * 
     * @var string
     */
    private $sign;

    /**
     * Creates a new instance.
     *
     * @param string $code      the three-letter currency code
     * @param string $isoStatus one of 'ISO_STATUS_ACTIVE', 'ISO_STATUS_WITHOUT_CURRENCY_CODE',
     *                              'ISO_STATUS_UNOFFICIAL' or 'ISO_STATUS_HISTORICAL'
     * @param int    $decimalDigits the number of decimal places used for this currency
     * @param string $name          the name as specified in en.wikipedia.org
     */
    private function __construct($code, $isoStatus, $decimalDigits, $name, $sign = null)
    {
        $this->code = $code;
        $this->isoStatus = $isoStatus;
        $this->decimalDigits = $decimalDigits;
        $this->name = $name;
        $this->sign = $sign ?: $code;
    }

    /**
     * Creates a new instance for the given code.
     *
     * @param string $code the three-letter currency code
     *
     * @return Currency
     *
     * @throws \InvalidArgumentException if the code is unknown
     */
    public static function valueOf($code = self::NONE)
    {
        if (self::isValidCode($code)) {
            $details = self::getDetails($code);
            $code = $details[ 'code' ];
            $isoStatus = $details[ 'isoStatus' ];
            $decimalDigits = $details[ 'decimalDigits' ];
            $name = $details[ 'name' ];
            $sign = isset($details['sign']) ? $details['sign'] : null;

            return new Currency($code, $isoStatus, $decimalDigits, $name, $sign);
        } else {
            throw new \InvalidArgumentException('Unknown currency code \''.$code.'\'.');
        }
    }
    
    public static function create($code = self::NONE)
    {
        return static::valueOf($code);
    }

    /**
     * Is the given code valid?
     *
     * @param string $code the three-letter currency code
     *
     * @return boolean
     */
    public static function isValidCode($code)
    {
        if (array_key_exists($code, self::getInfoForCurrencies())) {
            return true;
        } elseif (array_key_exists($code, self::getInfoForCurrenciesWithoutCurrencyCode())) {
            return true;
        } elseif (array_key_exists($code, self::getInfoForCurrenciesWithUnofficialCode())) {
            return true;
        } elseif (array_key_exists($code, self::getInfoForCurrenciesWithHistoricalCode())) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get details about a given ISO Currency.
     *
     * @param string $code the three-letter currency code
     *
     * @return array info about ISO Currency
     *
     * @throws \InvalidArgumentException if currency code is unknown
     */
    public static function getDetails($code)
    {
        $infos = self::getInfoForCurrencies();
        if (array_key_exists($code, $infos)) {
            return $infos[ $code ];
        }
        $infos = self::getInfoForCurrenciesWithoutCurrencyCode();
        if (array_key_exists($code, $infos)) {
            return $infos[ $code ];
        }
        $infos = self::getInfoForCurrenciesWithUnofficialCode();
        if (array_key_exists($code, $infos)) {
            return $infos[ $code ];
        }
        $infos = self::getInfoForCurrenciesWithHistoricalCode();
        if (array_key_exists($code, $infos)) {
            return $infos[ $code ];
        }
        throw new \InvalidArgumentException('Unknown \$code: '.$code);
    }

    /**
     * Returns a suitable string representation of the currency.
     *
     * @see __toString()
     *
     * @return string the currency code
     */
    public function __toString()
    {
        return $this->sign;
    }

    /**
     * Get the Iso Code.
     *
     * @return string the ISO Code
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Get the ISO Status.
     *
     * @return string one of 'ISO_STATUS_ACTIVE', 'ISO_STATUS_WITHOUT_CURRENCY_CODE', 'ISO_STATUS_UNOFFICIAL' or
     *                'ISO_STATUS_HISTORICAL'
     */
    public function getIsoStatus()
    {
        return $this->isoStatus;
    }

    /**
     * Whether the ISO Status is 'ISO_STATUS_ACTIVE'.
     *
     * @return boolean if the status is 'ISO_STATUS_ACTIVE'
     */
    public function isActive()
    {
        return $this->isoStatus == self::ISO_STATUS_ACTIVE;
    }

    /**
     * Whether the ISO Status is 'ISO_STATUS_WITHOUT_CURRENCY_CODE'.
     *
     * @return boolean if the status is 'ISO_STATUS_WITHOUT_CURRENCY_CODE'
     */
    public function isWithoutCurrencyCode()
    {
        return $this->isoStatus == self::ISO_STATUS_WITHOUT_CURRENCY_CODE;
    }

    /**
     * Whether the ISO Status is 'ISO_STATUS_UNOFFICIAL'.
     *
     * @return boolean if the status is 'ISO_STATUS_UNOFFICIAL'
     */
    public function isUnofficial()
    {
        return $this->isoStatus == self::ISO_STATUS_UNOFFICIAL;
    }

    /**
     * Whether the ISO Status is 'ISO_STATUS_HISTORICAL'.
     *
     * @return boolean if the status is 'ISO_STATUS_HISTORICAL'
     */
    public function isHistorical()
    {
        return $this->isoStatus == self::ISO_STATUS_HISTORICAL;
    }

    /**
     * Get the number of decimal places used.
     *
     * @return int the number of decimal places used
     */
    public function getDecimalDigits()
    {
        return $this->decimalDigits;
    }

    /**
     * Get the name as used on en.wikipedia.org
     *
     * @return string the name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Whether this and another currency are the same. Uses only the code to determine identity.
     *
     * @param Currency $currency the currency to compare to
     *
     * @return boolean true if the currency codes match, false otherwise
     */
    public function equals(Currency $currency)
    {
        return $this->code == $currency->getCode();
    }

    // Built from http://en.wikipedia.org/wiki/ISO_4217 on 2012-07-19.

    /**
     * United Arab Emirates dirham
     */
    const CODE_AED = 'AED';

    /**
     * Afghan afghani
     */
    const CODE_AFN = 'AFN';

    /**
     * Albanian lek
     */
    const CODE_ALL = 'ALL';

    /**
     * Armenian dram
     */
    const CODE_AMD = 'AMD';

    /**
     * Netherlands Antillean guilder
     */
    const CODE_ANG = 'ANG';

    /**
     * Angolan kwanza
     */
    const CODE_AOA = 'AOA';

    /**
     * Argentine peso
     */
    const CODE_ARS = 'ARS';

    /**
     * Australian dollar
     */
    const CODE_AUD = 'AUD';

    /**
     * Aruban florin
     */
    const CODE_AWG = 'AWG';

    /**
     * Azerbaijani manat
     */
    const CODE_AZN = 'AZN';

    /**
     * Bosnia and Herzegovina convertible mark
     */
    const CODE_BAM = 'BAM';

    /**
     * Barbados dollar
     */
    const CODE_BBD = 'BBD';

    /**
     * Bangladeshi taka
     */
    const CODE_BDT = 'BDT';

    /**
     * Bulgarian lev
     */
    const CODE_BGN = 'BGN';

    /**
     * Bahraini dinar
     */
    const CODE_BHD = 'BHD';

    /**
     * Burundian franc
     */
    const CODE_BIF = 'BIF';

    /**
     * Bermudian dollar (customarily known as Bermuda dollar)
     */
    const CODE_BMD = 'BMD';

    /**
     * Brunei dollar
     */
    const CODE_BND = 'BND';

    /**
     * Boliviano
     */
    const CODE_BOB = 'BOB';

    /**
     * Bolivian Mvdol (funds code)
     */
    const CODE_BOV = 'BOV';

    /**
     * Brazilian real
     */
    const CODE_BRL = 'BRL';

    /**
     * Bahamian dollar
     */
    const CODE_BSD = 'BSD';

    /**
     * Bhutanese ngultrum
     */
    const CODE_BTN = 'BTN';

    /**
     * Botswana pula
     */
    const CODE_BWP = 'BWP';

    /**
     * Belarusian ruble
     */
    const CODE_BYR = 'BYR';

    /**
     * Belize dollar
     */
    const CODE_BZD = 'BZD';

    /**
     * Canadian dollar
     */
    const CODE_CAD = 'CAD';

    /**
     * Congolese franc
     */
    const CODE_CDF = 'CDF';

    /**
     * WIR Euro (complementary currency)
     */
    const CODE_CHE = 'CHE';

    /**
     * Swiss franc
     */
    const CODE_CHF = 'CHF';

    /**
     * WIR Franc (complementary currency)
     */
    const CODE_CHW = 'CHW';

    /**
     * Unidad de Fomento (funds code)
     */
    const CODE_CLF = 'CLF';

    /**
     * Chilean peso
     */
    const CODE_CLP = 'CLP';

    /**
     * Chinese yuan
     */
    const CODE_CNY = 'CNY';

    /**
     * Colombian peso
     */
    const CODE_COP = 'COP';

    /**
     * Unidad de Valor Real
     */
    const CODE_COU = 'COU';

    /**
     * Costa Rican colon
     */
    const CODE_CRC = 'CRC';

    /**
     * Cuban convertible peso
     */
    const CODE_CUC = 'CUC';

    /**
     * Cuban peso
     */
    const CODE_CUP = 'CUP';

    /**
     * Cape Verde escudo
     */
    const CODE_CVE = 'CVE';

    /**
     * Czech koruna
     */
    const CODE_CZK = 'CZK';

    /**
     * Djiboutian franc
     */
    const CODE_DJF = 'DJF';

    /**
     * Danish krone
     */
    const CODE_DKK = 'DKK';

    /**
     * Dominican peso
     */
    const CODE_DOP = 'DOP';

    /**
     * Algerian dinar
     */
    const CODE_DZD = 'DZD';

    /**
     * Egyptian pound
     */
    const CODE_EGP = 'EGP';

    /**
     * Eritrean nakfa
     */
    const CODE_ERN = 'ERN';

    /**
     * Ethiopian birr
     */
    const CODE_ETB = 'ETB';

    /**
     * Euro
     */
    const CODE_EUR = 'EUR';

    /**
     * Fiji dollar
     */
    const CODE_FJD = 'FJD';

    /**
     * Falkland Islands pound
     */
    const CODE_FKP = 'FKP';

    /**
     * Pound sterling
     */
    const CODE_GBP = 'GBP';

    /**
     * Georgian lari
     */
    const CODE_GEL = 'GEL';

    /**
     * Ghanaian cedi
     */
    const CODE_GHS = 'GHS';

    /**
     * Gibraltar pound
     */
    const CODE_GIP = 'GIP';

    /**
     * Gambian dalasi
     */
    const CODE_GMD = 'GMD';

    /**
     * Guinean franc
     */
    const CODE_GNF = 'GNF';

    /**
     * Guatemalan quetzal
     */
    const CODE_GTQ = 'GTQ';

    /**
     * Guyanese dollar
     */
    const CODE_GYD = 'GYD';

    /**
     * Hong Kong dollar
     */
    const CODE_HKD = 'HKD';

    /**
     * Honduran lempira
     */
    const CODE_HNL = 'HNL';

    /**
     * Croatian kuna
     */
    const CODE_HRK = 'HRK';

    /**
     * Haitian gourde
     */
    const CODE_HTG = 'HTG';

    /**
     * Hungarian forint
     */
    const CODE_HUF = 'HUF';

    /**
     * Indonesian rupiah
     */
    const CODE_IDR = 'IDR';

    /**
     * Israeli new sheqel
     */
    const CODE_ILS = 'ILS';

    /**
     * Indian rupee
     */
    const CODE_INR = 'INR';

    /**
     * Iraqi dinar
     */
    const CODE_IQD = 'IQD';

    /**
     * Iranian rial
     */
    const CODE_IRR = 'IRR';

    /**
     * Icelandic króna
     */
    const CODE_ISK = 'ISK';

    /**
     * Jamaican dollar
     */
    const CODE_JMD = 'JMD';

    /**
     * Jordanian dinar
     */
    const CODE_JOD = 'JOD';

    /**
     * Japanese yen
     */
    const CODE_JPY = 'JPY';

    /**
     * Kenyan shilling
     */
    const CODE_KES = 'KES';

    /**
     * Kyrgyzstani som
     */
    const CODE_KGS = 'KGS';

    /**
     * Cambodian riel
     */
    const CODE_KHR = 'KHR';

    /**
     * Comoro franc
     */
    const CODE_KMF = 'KMF';

    /**
     * North Korean won
     */
    const CODE_KPW = 'KPW';

    /**
     * South Korean won
     */
    const CODE_KRW = 'KRW';

    /**
     * Kuwaiti dinar
     */
    const CODE_KWD = 'KWD';

    /**
     * Cayman Islands dollar
     */
    const CODE_KYD = 'KYD';

    /**
     * Kazakhstani tenge
     */
    const CODE_KZT = 'KZT';

    /**
     * Lao kip
     */
    const CODE_LAK = 'LAK';

    /**
     * Lebanese pound
     */
    const CODE_LBP = 'LBP';

    /**
     * Sri Lankan rupee
     */
    const CODE_LKR = 'LKR';

    /**
     * Liberian dollar
     */
    const CODE_LRD = 'LRD';

    /**
     * Lesotho loti
     */
    const CODE_LSL = 'LSL';

    /**
     * Lithuanian litas
     */
    const CODE_LTL = 'LTL';

    /**
     * Latvian lats
     */
    const CODE_LVL = 'LVL';

    /**
     * Libyan dinar
     */
    const CODE_LYD = 'LYD';

    /**
     * Moroccan dirham
     */
    const CODE_MAD = 'MAD';

    /**
     * Moldovan leu
     */
    const CODE_MDL = 'MDL';

    /**
     * Malagasy ariary
     */
    const CODE_MGA = 'MGA';

    /**
     * Macedonian denar
     */
    const CODE_MKD = 'MKD';

    /**
     * Myanma kyat
     */
    const CODE_MMK = 'MMK';

    /**
     * Mongolian tugrik
     */
    const CODE_MNT = 'MNT';

    /**
     * Macanese pataca
     */
    const CODE_MOP = 'MOP';

    /**
     * Mauritanian ouguiya
     */
    const CODE_MRO = 'MRO';

    /**
     * Mauritian rupee
     */
    const CODE_MUR = 'MUR';

    /**
     * Maldivian rufiyaa
     */
    const CODE_MVR = 'MVR';

    /**
     * Malawian kwacha
     */
    const CODE_MWK = 'MWK';

    /**
     * Mexican peso
     */
    const CODE_MXN = 'MXN';

    /**
     * Mexican Unidad de Inversion (UDI) (funds code)
     */
    const CODE_MXV = 'MXV';

    /**
     * Malaysian ringgit
     */
    const CODE_MYR = 'MYR';

    /**
     * Mozambican metical
     */
    const CODE_MZN = 'MZN';

    /**
     * Namibian dollar
     */
    const CODE_NAD = 'NAD';

    /**
     * Nigerian naira
     */
    const CODE_NGN = 'NGN';

    /**
     * Nicaraguan córdoba
     */
    const CODE_NIO = 'NIO';

    /**
     * Norwegian krone
     */
    const CODE_NOK = 'NOK';

    /**
     * Nepalese rupee
     */
    const CODE_NPR = 'NPR';

    /**
     * New Zealand dollar
     */
    const CODE_NZD = 'NZD';

    /**
     * Omani rial
     */
    const CODE_OMR = 'OMR';

    /**
     * Panamanian balboa
     */
    const CODE_PAB = 'PAB';

    /**
     * Peruvian nuevo sol
     */
    const CODE_PEN = 'PEN';

    /**
     * Papua New Guinean kina
     */
    const CODE_PGK = 'PGK';

    /**
     * Philippine peso
     */
    const CODE_PHP = 'PHP';

    /**
     * Pakistani rupee
     */
    const CODE_PKR = 'PKR';

    /**
     * Polish złoty
     */
    const CODE_PLN = 'PLN';

    /**
     * Paraguayan guaraní
     */
    const CODE_PYG = 'PYG';

    /**
     * Qatari riyal
     */
    const CODE_QAR = 'QAR';

    /**
     * Romanian new leu
     */
    const CODE_RON = 'RON';

    /**
     * Serbian dinar
     */
    const CODE_RSD = 'RSD';

    /**
     * Russian rouble
     */
    const CODE_RUB = 'RUB';

    /**
     * Rwandan franc
     */
    const CODE_RWF = 'RWF';

    /**
     * Saudi riyal
     */
    const CODE_SAR = 'SAR';

    /**
     * Solomon Islands dollar
     */
    const CODE_SBD = 'SBD';

    /**
     * Seychelles rupee
     */
    const CODE_SCR = 'SCR';

    /**
     * Sudanese pound
     */
    const CODE_SDG = 'SDG';

    /**
     * Swedish krona/kronor
     */
    const CODE_SEK = 'SEK';

    /**
     * Singapore dollar
     */
    const CODE_SGD = 'SGD';

    /**
     * Saint Helena pound
     */
    const CODE_SHP = 'SHP';

    /**
     * Sierra Leonean leone
     */
    const CODE_SLL = 'SLL';

    /**
     * Somali shilling
     */
    const CODE_SOS = 'SOS';

    /**
     * Surinamese dollar
     */
    const CODE_SRD = 'SRD';

    /**
     * South Sudanese pound
     */
    const CODE_SSP = 'SSP';

    /**
     * São Tomé and Príncipe dobra
     */
    const CODE_STD = 'STD';

    /**
     * Syrian pound
     */
    const CODE_SYP = 'SYP';

    /**
     * Swazi lilangeni
     */
    const CODE_SZL = 'SZL';

    /**
     * Thai baht
     */
    const CODE_THB = 'THB';

    /**
     * Tajikistani somoni
     */
    const CODE_TJS = 'TJS';

    /**
     * Turkmenistani manat
     */
    const CODE_TMT = 'TMT';

    /**
     * Tunisian dinar
     */
    const CODE_TND = 'TND';

    /**
     * Tongan paʻanga
     */
    const CODE_TOP = 'TOP';

    /**
     * Turkish lira
     */
    const CODE_TRY = 'TRY';

    /**
     * Trinidad and Tobago dollar
     */
    const CODE_TTD = 'TTD';

    /**
     * New Taiwan dollar
     */
    const CODE_TWD = 'TWD';

    /**
     * Tanzanian shilling
     */
    const CODE_TZS = 'TZS';

    /**
     * Ukrainian hryvnia
     */
    const CODE_UAH = 'UAH';

    /**
     * Ugandan shilling
     */
    const CODE_UGX = 'UGX';

    /**
     * United States dollar
     */
    const CODE_USD = 'USD';

    /**
     * United States dollar (next day) (funds code)
     */
    const CODE_USN = 'USN';

    /**
     * United States dollar (same day) (funds code) (one source[who?] claims it is no longer used, but it is still on
     * the ISO 4217-MA list)
     */
    const CODE_USS = 'USS';

    /**
     * Uruguay Peso en Unidades Indexadas (URUIURUI) (funds code)
     */
    const CODE_UYI = 'UYI';

    /**
     * Uruguayan peso
     */
    const CODE_UYU = 'UYU';

    /**
     * Uzbekistan som
     */
    const CODE_UZS = 'UZS';

    /**
     * Venezuelan bolívar fuerte
     */
    const CODE_VEF = 'VEF';

    /**
     * Vietnamese đồng
     */
    const CODE_VND = 'VND';

    /**
     * Vanuatu vatu
     */
    const CODE_VUV = 'VUV';

    /**
     * Samoan tala
     */
    const CODE_WST = 'WST';

    /**
     * CFA franc BEAC
     */
    const CODE_XAF = 'XAF';

    /**
     * Silver (one troy ounce)
     */
    const CODE_XAG = 'XAG';

    /**
     * Gold (one troy ounce)
     */
    const CODE_XAU = 'XAU';

    /**
     * European Composite Unit (EURCO) (bond market unit)
     */
    const CODE_XBA = 'XBA';

    /**
     * European Monetary Unit (E.M.U.-6) (bond market unit)
     */
    const CODE_XBB = 'XBB';

    /**
     * European Unit of Account 9 (E.U.A.-9) (bond market unit)
     */
    const CODE_XBC = 'XBC';

    /**
     * European Unit of Account 17 (E.U.A.-17) (bond market unit)
     */
    const CODE_XBD = 'XBD';

    /**
     * East Caribbean dollar
     */
    const CODE_XCD = 'XCD';

    /**
     * Special drawing rights
     */
    const CODE_XDR = 'XDR';

    /**
     * UIC franc (special settlement currency)
     */
    const CODE_XFU = 'XFU';

    /**
     * CFA Franc BCEAO
     */
    const CODE_XOF = 'XOF';

    /**
     * Palladium (one troy ounce)
     */
    const CODE_XPD = 'XPD';

    /**
     * CFP franc
     */
    const CODE_XPF = 'XPF';

    /**
     * Platinum (one troy ounce)
     */
    const CODE_XPT = 'XPT';

    /**
     * Code reserved for testing purposes
     */
    const CODE_XTS = 'XTS';

    /**
     * No currency
     */
    const CODE_XXX = 'XXX';

    /**
     * Yemeni rial
     */
    const CODE_YER = 'YER';

    /**
     * South African rand
     */
    const CODE_ZAR = 'ZAR';

    /**
     * Zambian kwacha
     */
    const CODE_ZMK = 'ZMK';

    /**
     * Zimbabwe dollar
     */
    const CODE_ZWL = 'ZWL';

    /**
     * Guernsey pound
     */
    const WITHOUT_CURRENCY_CODE_GGP = 'GGP';

    /**
     * Jersey pound
     */
    const WITHOUT_CURRENCY_CODE_JEP = 'JEP';

    /**
     * Isle of Man pound also Manx pound
     */
    const WITHOUT_CURRENCY_CODE_IMP = 'IMP';

    /**
     * Kiribati dollar
     */
    const WITHOUT_CURRENCY_CODE_KRI = 'KRI';

    /**
     * Somaliland shilling
     */
    const WITHOUT_CURRENCY_CODE_SLS = 'SLS';

    /**
     * Transnistrian ruble
     */
    const WITHOUT_CURRENCY_CODE_PRB = 'PRB';

    /**
     * Tuvalu dollar
     */
    const WITHOUT_CURRENCY_CODE_TVD = 'TVD';

    /**
     * Bitcoin
     */
    const UNOFFICIAL_BTC = 'BTC';

    /**
     * Andorran franc (1:1 peg to the French franc)
     */
    const HISTORICAL_ADF = 'ADF';

    /**
     * Andorran peseta (1:1 peg to the Spanish peseta)
     */
    const HISTORICAL_ADP = 'ADP';

    /**
     * Austrian schilling
     */
    const HISTORICAL_ATS = 'ATS';

    /**
     * Belgian franc (currency union with LUF)
     */
    const HISTORICAL_BEF = 'BEF';

    /**
     * Cypriot pound
     */
    const HISTORICAL_CYP = 'CYP';

    /**
     * German mark
     */
    const HISTORICAL_DEM = 'DEM';

    /**
     * Estonian kroon
     */
    const HISTORICAL_EEK = 'EEK';

    /**
     * Spanish peseta
     */
    const HISTORICAL_ESP = 'ESP';

    /**
     * Finnish markka
     */
    const HISTORICAL_FIM = 'FIM';

    /**
     * French franc
     */
    const HISTORICAL_FRF = 'FRF';

    /**
     * Greek drachma
     */
    const HISTORICAL_GRD = 'GRD';

    /**
     * Irish pound (punt in Irish language)
     */
    const HISTORICAL_IEP = 'IEP';

    /**
     * Italian lira
     */
    const HISTORICAL_ITL = 'ITL';

    /**
     * Luxembourg franc (currency union with BEF)
     */
    const HISTORICAL_LUF = 'LUF';

    /**
     * Monegasque franc (currency union with FRF)
     */
    const HISTORICAL_MCF = 'MCF';

    /**
     * Moroccan franc
     */
    const HISTORICAL_MAF = 'MAF';

    /**
     * Maltese lira
     */
    const HISTORICAL_MTL = 'MTL';

    /**
     * Netherlands guilder
     */
    const HISTORICAL_NLG = 'NLG';

    /**
     * Portuguese escudo
     */
    const HISTORICAL_PTE = 'PTE';

    /**
     * Slovenian tolar
     */
    const HISTORICAL_SIT = 'SIT';

    /**
     * Slovak koruna
     */
    const HISTORICAL_SKK = 'SKK';

    /**
     * San Marinese lira (currency union with ITL and VAL)
     */
    const HISTORICAL_SML = 'SML';

    /**
     * Vatican lira (currency union with ITL and SML)
     */
    const HISTORICAL_VAL = 'VAL';

    /**
     * European Currency Unit (1 XEU = 1 EUR)
     */
    const HISTORICAL_XEU = 'XEU';

    /**
     * Afghan afghani
     */
    const HISTORICAL_AFA = 'AFA';

    /**
     * Angolan new kwanza
     */
    const HISTORICAL_AON = 'AON';

    /**
     * Angolan kwanza readjustado
     */
    const HISTORICAL_AOR = 'AOR';

    /**
     * Argentine peso ley
     */
    const HISTORICAL_ARL = 'ARL';

    /**
     * Argentine peso argentino
     */
    const HISTORICAL_ARP = 'ARP';

    /**
     * Argentine austral
     */
    const HISTORICAL_ARA = 'ARA';

    /**
     * Azerbaijani manat
     */
    const HISTORICAL_AZM = 'AZM';

    /**
     * Bulgarian lev A/99
     */
    const HISTORICAL_BGL = 'BGL';

    /**
     * Bolivian peso
     */
    const HISTORICAL_BOP = 'BOP';

    /**
     * Brazilian cruzeiro novo
     */
    const HISTORICAL_BRB = 'BRB';

    /**
     * Brazilian cruzado
     */
    const HISTORICAL_BRC = 'BRC';

    /**
     * Brazilian cruzeiro
     */
    const HISTORICAL_BRE = 'BRE';

    /**
     * Brazilian cruzado novo
     */
    const HISTORICAL_BRN = 'BRN';

    /**
     * Brazilian cruzeiro real
     */
    const HISTORICAL_BRR = 'BRR';

    /**
     * Chilean escudo
     */
    const HISTORICAL_CLE = 'CLE';

    /**
     * Serbian dinar
     */
    const HISTORICAL_CSD = 'CSD';

    /**
     * Czechoslovak koruna
     */
    const HISTORICAL_CSK = 'CSK';

    /**
     * East German Mark of the GDR (East Germany)
     */
    const HISTORICAL_DDM = 'DDM';

    /**
     * Ecuadorian sucre
     */
    const HISTORICAL_ECS = 'ECS';

    /**
     * Ecuador Unidad de Valor Constante (funds code) (discontinued)
     */
    const HISTORICAL_ECV = 'ECV';

    /**
     * Equatorial Guinean ekwele
     */
    const HISTORICAL_GQE = 'GQE';

    /**
     * Spanish peseta (account A)
     */
    const HISTORICAL_ESA = 'ESA';

    /**
     * Spanish peseta (account B)
     */
    const HISTORICAL_ESB = 'ESB';

    /**
     * Guinean syli
     */
    const HISTORICAL_GNE = 'GNE';

    /**
     * Ghanaian cedi
     */
    const HISTORICAL_GHC = 'GHC';

    /**
     * Guinea-Bissau peso
     */
    const HISTORICAL_GWP = 'GWP';

    /**
     * Israeli lira
     */
    const HISTORICAL_ILP = 'ILP';

    /**
     * Israeli shekel
     */
    const HISTORICAL_ILR = 'ILR';

    /**
     * Icelandic old krona
     */
    const HISTORICAL_ISJ = 'ISJ';

    /**
     * Lao kip
     */
    const HISTORICAL_LAJ = 'LAJ';

    /**
     * Malagasy franc
     */
    const HISTORICAL_MGF = 'MGF';

    /**
     * Old Macedonian denar A/93
     */
    const HISTORICAL_MKN = 'MKN';

    /**
     * Mali franc
     */
    const HISTORICAL_MLF = 'MLF';

    /**
     * Maldivian rupee
     */
    const HISTORICAL_MVQ = 'MVQ';

    /**
     * Mexican peso
     */
    const HISTORICAL_MXP = 'MXP';

    /**
     * Mozambican metical
     */
    const HISTORICAL_MZM = 'MZM';

    /**
     * Newfoundland dollar
     */
    const HISTORICAL_NFD = 'NFD';

    /**
     * Peruvian sol
     */
    const HISTORICAL_PEH = 'PEH';

    /**
     * Peruvian inti
     */
    const HISTORICAL_PEI = 'PEI';

    /**
     * Polish zloty A/94
     */
    const HISTORICAL_PLZ = 'PLZ';

    /**
     * Romanian leu A/05
     */
    const HISTORICAL_ROL = 'ROL';

    /**
     * Russian rouble A/97
     */
    const HISTORICAL_RUR = 'RUR';

    /**
     * Sudanese dinar
     */
    const HISTORICAL_SDD = 'SDD';

    /**
     * Sudanese old pound
     */
    const HISTORICAL_SDP = 'SDP';

    /**
     * Suriname guilder
     */
    const HISTORICAL_SRG = 'SRG';

    /**
     * Soviet Union rouble
     */
    const HISTORICAL_SUR = 'SUR';

    /**
     * Salvadoran colón
     */
    const HISTORICAL_SVC = 'SVC';

    /**
     * Tajikistani ruble
     */
    const HISTORICAL_TJR = 'TJR';

    /**
     * Turkmenistani manat
     */
    const HISTORICAL_TMM = 'TMM';

    /**
     * Turkish lira A/05
     */
    const HISTORICAL_TRL = 'TRL';

    /**
     * Ukrainian karbovanets
     */
    const HISTORICAL_UAK = 'UAK';

    /**
     * Ugandan shilling A/87
     */
    const HISTORICAL_UGS = 'UGS';

    /**
     * Uruguay old peso
     */
    const HISTORICAL_UYN = 'UYN';

    /**
     * Venezuelan bolívar
     */
    const HISTORICAL_VEB = 'VEB';

    /**
     * Gold franc (special settlement currency)
     */
    const HISTORICAL_XFO = 'XFO';

    /**
     * South Yemeni dinar
     */
    const HISTORICAL_YDD = 'YDD';

    /**
     * Yugoslav dinar
     */
    const HISTORICAL_YUD = 'YUD';

    /**
     * Yugoslav dinar
     */
    const HISTORICAL_YUN = 'YUN';

    /**
     * Yugoslav dinar
     */
    const HISTORICAL_YUR = 'YUR';

    /**
     * Yugoslav dinar
     */
    const HISTORICAL_YUO = 'YUO';

    /**
     * Yugoslav dinar
     */
    const HISTORICAL_YUG = 'YUG';

    /**
     * Yugoslav dinar
     */
    const HISTORICAL_YUM = 'YUM';

    /**
     * South African financial rand (funds code) (discontinued)
     */
    const HISTORICAL_ZAL = 'ZAL';

    /**
     * Zaïrean new zaïre
     */
    const HISTORICAL_ZRN = 'ZRN';

    /**
     * Zaïrean zaïre
     */
    const HISTORICAL_ZRZ = 'ZRZ';

    /**
     * Rhodesian dollar
     */
    const HISTORICAL_ZWC = 'ZWC';

    /**
     * Zimbabwean dollar A/06
     */
    const HISTORICAL_ZWD = 'ZWD';

    /**
     * Zimbabwean dollar A/08
     */
    const HISTORICAL_ZWN = 'ZWN';

    /**
     * Zimbabwean dollar A/09
     */
    const HISTORICAL_ZWR = 'ZWR';

    /**
     * Get details about all active ISO Currencies.
     *
     * @return array info about active ISO Currencies
     */
    public static function getInfoForCurrencies()
    {
        return array(
            self::CODE_AED => array(
                'code' => self::CODE_AED,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'United Arab Emirates dirham',
            ),
            self::CODE_AFN => array(
                'code' => self::CODE_AFN,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Afghan afghani',
            ),
            self::CODE_ALL => array(
                'code' => self::CODE_ALL,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Albanian lek',
            ),
            self::CODE_AMD => array(
                'code' => self::CODE_AMD,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Armenian dram',
            ),
            self::CODE_ANG => array(
                'code' => self::CODE_ANG,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Netherlands Antillean guilder',
            ),
            self::CODE_AOA => array(
                'code' => self::CODE_AOA,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Angolan kwanza',
            ),
            self::CODE_ARS => array(
                'code' => self::CODE_ARS,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Argentine peso',
            ),
            self::CODE_AUD => array(
                'code' => self::CODE_AUD,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Australian dollar',
            ),
            self::CODE_AWG => array(
                'code' => self::CODE_AWG,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Aruban florin',
            ),
            self::CODE_AZN => array(
                'code' => self::CODE_AZN,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Azerbaijani manat',
            ),
            self::CODE_BAM => array(
                'code' => self::CODE_BAM,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Bosnia and Herzegovina convertible mark',
            ),
            self::CODE_BBD => array(
                'code' => self::CODE_BBD,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Barbados dollar',
            ),
            self::CODE_BDT => array(
                'code' => self::CODE_BDT,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Bangladeshi taka',
            ),
            self::CODE_BGN => array(
                'code' => self::CODE_BGN,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Bulgarian lev',
            ),
            self::CODE_BHD => array(
                'code' => self::CODE_BHD,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 3,
                'name' => 'Bahraini dinar',
            ),
            self::CODE_BIF => array(
                'code' => self::CODE_BIF,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 0,
                'name' => 'Burundian franc',
            ),
            self::CODE_BMD => array(
                'code' => self::CODE_BMD,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Bermudian dollar (customarily known as Bermuda dollar)',
            ),
            self::CODE_BND => array(
                'code' => self::CODE_BND,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Brunei dollar',
            ),
            self::CODE_BOB => array(
                'code' => self::CODE_BOB,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Boliviano',
            ),
            self::CODE_BOV => array(
                'code' => self::CODE_BOV,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Bolivian Mvdol (funds code)',
            ),
            self::CODE_BRL => array(
                'code' => self::CODE_BRL,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Brazilian real',
            ),
            self::CODE_BSD => array(
                'code' => self::CODE_BSD,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Bahamian dollar',
            ),
            self::CODE_BTN => array(
                'code' => self::CODE_BTN,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Bhutanese ngultrum',
            ),
            self::CODE_BWP => array(
                'code' => self::CODE_BWP,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Botswana pula',
            ),
            self::CODE_BYR => array(
                'code' => self::CODE_BYR,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 0,
                'name' => 'Belarusian ruble',
            ),
            self::CODE_BZD => array(
                'code' => self::CODE_BZD,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Belize dollar',
            ),
            self::CODE_CAD => array(
                'code' => self::CODE_CAD,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Canadian dollar',
            ),
            self::CODE_CDF => array(
                'code' => self::CODE_CDF,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Congolese franc',
            ),
            self::CODE_CHE => array(
                'code' => self::CODE_CHE,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'WIR Euro (complementary currency)',
            ),
            self::CODE_CHF => array(
                'code' => self::CODE_CHF,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Swiss franc',
            ),
            self::CODE_CHW => array(
                'code' => self::CODE_CHW,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'WIR Franc (complementary currency)',
            ),
            self::CODE_CLF => array(
                'code' => self::CODE_CLF,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 0,
                'name' => 'Unidad de Fomento (funds code)',
            ),
            self::CODE_CLP => array(
                'code' => self::CODE_CLP,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 0,
                'name' => 'Chilean peso',
            ),
            self::CODE_CNY => array(
                'code' => self::CODE_CNY,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Chinese yuan',
            ),
            self::CODE_COP => array(
                'code' => self::CODE_COP,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Colombian peso',
            ),
            self::CODE_COU => array(
                'code' => self::CODE_COU,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Unidad de Valor Real',
            ),
            self::CODE_CRC => array(
                'code' => self::CODE_CRC,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Costa Rican colon',
            ),
            self::CODE_CUC => array(
                'code' => self::CODE_CUC,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Cuban convertible peso',
            ),
            self::CODE_CUP => array(
                'code' => self::CODE_CUP,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Cuban peso',
            ),
            self::CODE_CVE => array(
                'code' => self::CODE_CVE,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 0,
                'name' => 'Cape Verde escudo',
            ),
            self::CODE_CZK => array(
                'code' => self::CODE_CZK,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Czech koruna',
            ),
            self::CODE_DJF => array(
                'code' => self::CODE_DJF,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 0,
                'name' => 'Djiboutian franc',
            ),
            self::CODE_DKK => array(
                'code' => self::CODE_DKK,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Danish krone',
            ),
            self::CODE_DOP => array(
                'code' => self::CODE_DOP,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Dominican peso',
            ),
            self::CODE_DZD => array(
                'code' => self::CODE_DZD,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Algerian dinar',
            ),
            self::CODE_EGP => array(
                'code' => self::CODE_EGP,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Egyptian pound',
            ),
            self::CODE_ERN => array(
                'code' => self::CODE_ERN,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Eritrean nakfa',
            ),
            self::CODE_ETB => array(
                'code' => self::CODE_ETB,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Ethiopian birr',
            ),
            self::CODE_EUR => array(
                'code' => self::CODE_EUR,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Euro',
            ),
            self::CODE_FJD => array(
                'code' => self::CODE_FJD,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Fiji dollar',
            ),
            self::CODE_FKP => array(
                'code' => self::CODE_FKP,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Falkland Islands pound',
            ),
            self::CODE_GBP => array(
                'code' => self::CODE_GBP,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Pound sterling',
            ),
            self::CODE_GEL => array(
                'code' => self::CODE_GEL,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Georgian lari',
            ),
            self::CODE_GHS => array(
                'code' => self::CODE_GHS,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Ghanaian cedi',
            ),
            self::CODE_GIP => array(
                'code' => self::CODE_GIP,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Gibraltar pound',
            ),
            self::CODE_GMD => array(
                'code' => self::CODE_GMD,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Gambian dalasi',
            ),
            self::CODE_GNF => array(
                'code' => self::CODE_GNF,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 0,
                'name' => 'Guinean franc',
            ),
            self::CODE_GTQ => array(
                'code' => self::CODE_GTQ,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Guatemalan quetzal',
            ),
            self::CODE_GYD => array(
                'code' => self::CODE_GYD,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Guyanese dollar',
            ),
            self::CODE_HKD => array(
                'code' => self::CODE_HKD,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Hong Kong dollar',
            ),
            self::CODE_HNL => array(
                'code' => self::CODE_HNL,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Honduran lempira',
            ),
            self::CODE_HRK => array(
                'code' => self::CODE_HRK,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Croatian kuna',
            ),
            self::CODE_HTG => array(
                'code' => self::CODE_HTG,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Haitian gourde',
            ),
            self::CODE_HUF => array(
                'code' => self::CODE_HUF,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Hungarian forint',
            ),
            self::CODE_IDR => array(
                'code' => self::CODE_IDR,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Indonesian rupiah',
            ),
            self::CODE_ILS => array(
                'code' => self::CODE_ILS,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Israeli new sheqel',
            ),
            self::CODE_INR => array(
                'code' => self::CODE_INR,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Indian rupee',
            ),
            self::CODE_IQD => array(
                'code' => self::CODE_IQD,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 3,
                'name' => 'Iraqi dinar',
            ),
            self::CODE_IRR => array(
                'code' => self::CODE_IRR,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 0,
                'name' => 'Iranian rial',
            ),
            self::CODE_ISK => array(
                'code' => self::CODE_ISK,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 0,
                'name' => 'Icelandic króna',
            ),
            self::CODE_JMD => array(
                'code' => self::CODE_JMD,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Jamaican dollar',
            ),
            self::CODE_JOD => array(
                'code' => self::CODE_JOD,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 3,
                'name' => 'Jordanian dinar',
            ),
            self::CODE_JPY => array(
                'code' => self::CODE_JPY,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 0,
                'name' => 'Japanese yen',
            ),
            self::CODE_KES => array(
                'code' => self::CODE_KES,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Kenyan shilling',
            ),
            self::CODE_KGS => array(
                'code' => self::CODE_KGS,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Kyrgyzstani som',
            ),
            self::CODE_KHR => array(
                'code' => self::CODE_KHR,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Cambodian riel',
            ),
            self::CODE_KMF => array(
                'code' => self::CODE_KMF,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 0,
                'name' => 'Comoro franc',
            ),
            self::CODE_KPW => array(
                'code' => self::CODE_KPW,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 0,
                'name' => 'North Korean won',
            ),
            self::CODE_KRW => array(
                'code' => self::CODE_KRW,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 0,
                'name' => 'South Korean won',
            ),
            self::CODE_KWD => array(
                'code' => self::CODE_KWD,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 3,
                'name' => 'Kuwaiti dinar',
            ),
            self::CODE_KYD => array(
                'code' => self::CODE_KYD,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Cayman Islands dollar',
            ),
            self::CODE_KZT => array(
                'code' => self::CODE_KZT,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Kazakhstani tenge',
            ),
            self::CODE_LAK => array(
                'code' => self::CODE_LAK,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 0,
                'name' => 'Lao kip',
            ),
            self::CODE_LBP => array(
                'code' => self::CODE_LBP,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 0,
                'name' => 'Lebanese pound',
            ),
            self::CODE_LKR => array(
                'code' => self::CODE_LKR,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Sri Lankan rupee',
            ),
            self::CODE_LRD => array(
                'code' => self::CODE_LRD,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Liberian dollar',
            ),
            self::CODE_LSL => array(
                'code' => self::CODE_LSL,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Lesotho loti',
            ),
            self::CODE_LTL => array(
                'code' => self::CODE_LTL,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Lithuanian litas',
            ),
            self::CODE_LVL => array(
                'code' => self::CODE_LVL,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Latvian lats',
            ),
            self::CODE_LYD => array(
                'code' => self::CODE_LYD,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 3,
                'name' => 'Libyan dinar',
            ),
            self::CODE_MAD => array(
                'code' => self::CODE_MAD,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Moroccan dirham',
            ),
            self::CODE_MDL => array(
                'code' => self::CODE_MDL,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Moldovan leu',
            ),
            self::CODE_MGA => array(
                'code' => self::CODE_MGA,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 0,
                'name' => 'Malagasy ariary',
            ),
            self::CODE_MKD => array(
                'code' => self::CODE_MKD,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Macedonian denar',
            ),
            self::CODE_MMK => array(
                'code' => self::CODE_MMK,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 0,
                'name' => 'Myanma kyat',
            ),
            self::CODE_MNT => array(
                'code' => self::CODE_MNT,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Mongolian tugrik',
            ),
            self::CODE_MOP => array(
                'code' => self::CODE_MOP,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Macanese pataca',
            ),
            self::CODE_MRO => array(
                'code' => self::CODE_MRO,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 0,
                'name' => 'Mauritanian ouguiya',
            ),
            self::CODE_MUR => array(
                'code' => self::CODE_MUR,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Mauritian rupee',
            ),
            self::CODE_MVR => array(
                'code' => self::CODE_MVR,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Maldivian rufiyaa',
            ),
            self::CODE_MWK => array(
                'code' => self::CODE_MWK,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Malawian kwacha',
            ),
            self::CODE_MXN => array(
                'code' => self::CODE_MXN,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Mexican peso',
            ),
            self::CODE_MXV => array(
                'code' => self::CODE_MXV,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Mexican Unidad de Inversion (UDI) (funds code)',
            ),
            self::CODE_MYR => array(
                'code' => self::CODE_MYR,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Malaysian ringgit',
            ),
            self::CODE_MZN => array(
                'code' => self::CODE_MZN,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Mozambican metical',
            ),
            self::CODE_NAD => array(
                'code' => self::CODE_NAD,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Namibian dollar',
            ),
            self::CODE_NGN => array(
                'code' => self::CODE_NGN,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Nigerian naira',
            ),
            self::CODE_NIO => array(
                'code' => self::CODE_NIO,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Nicaraguan córdoba',
            ),
            self::CODE_NOK => array(
                'code' => self::CODE_NOK,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Norwegian krone',
            ),
            self::CODE_NPR => array(
                'code' => self::CODE_NPR,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Nepalese rupee',
            ),
            self::CODE_NZD => array(
                'code' => self::CODE_NZD,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'New Zealand dollar',
            ),
            self::CODE_OMR => array(
                'code' => self::CODE_OMR,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 3,
                'name' => 'Omani rial',
            ),
            self::CODE_PAB => array(
                'code' => self::CODE_PAB,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Panamanian balboa',
            ),
            self::CODE_PEN => array(
                'code' => self::CODE_PEN,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Peruvian nuevo sol',
            ),
            self::CODE_PGK => array(
                'code' => self::CODE_PGK,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Papua New Guinean kina',
            ),
            self::CODE_PHP => array(
                'code' => self::CODE_PHP,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Philippine peso',
            ),
            self::CODE_PKR => array(
                'code' => self::CODE_PKR,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Pakistani rupee',
            ),
            self::CODE_PLN => array(
                'code' => self::CODE_PLN,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Polish złoty',
            ),
            self::CODE_PYG => array(
                'code' => self::CODE_PYG,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 0,
                'name' => 'Paraguayan guaraní',
            ),
            self::CODE_QAR => array(
                'code' => self::CODE_QAR,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Qatari riyal',
            ),
            self::CODE_RON => array(
                'code' => self::CODE_RON,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Romanian new leu',
            ),
            self::CODE_RSD => array(
                'code' => self::CODE_RSD,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Serbian dinar',
            ),
            self::CODE_RUB => array(
                'code' => self::CODE_RUB,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Russian rouble',
            ),
            self::CODE_RWF => array(
                'code' => self::CODE_RWF,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 0,
                'name' => 'Rwandan franc',
            ),
            self::CODE_SAR => array(
                'code' => self::CODE_SAR,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Saudi riyal',
            ),
            self::CODE_SBD => array(
                'code' => self::CODE_SBD,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Solomon Islands dollar',
            ),
            self::CODE_SCR => array(
                'code' => self::CODE_SCR,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Seychelles rupee',
            ),
            self::CODE_SDG => array(
                'code' => self::CODE_SDG,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Sudanese pound',
            ),
            self::CODE_SEK => array(
                'code' => self::CODE_SEK,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Swedish krona/kronor',
            ),
            self::CODE_SGD => array(
                'code' => self::CODE_SGD,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Singapore dollar',
            ),
            self::CODE_SHP => array(
                'code' => self::CODE_SHP,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Saint Helena pound',
            ),
            self::CODE_SLL => array(
                'code' => self::CODE_SLL,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 0,
                'name' => 'Sierra Leonean leone',
            ),
            self::CODE_SOS => array(
                'code' => self::CODE_SOS,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Somali shilling',
            ),
            self::CODE_SRD => array(
                'code' => self::CODE_SRD,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Surinamese dollar',
            ),
            self::CODE_SSP => array(
                'code' => self::CODE_SSP,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'South Sudanese pound',
            ),
            self::CODE_STD => array(
                'code' => self::CODE_STD,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 0,
                'name' => 'São Tomé and Príncipe dobra',
            ),
            self::CODE_SYP => array(
                'code' => self::CODE_SYP,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Syrian pound',
            ),
            self::CODE_SZL => array(
                'code' => self::CODE_SZL,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Swazi lilangeni',
            ),
            self::CODE_THB => array(
                'code' => self::CODE_THB,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Thai baht',
            ),
            self::CODE_TJS => array(
                'code' => self::CODE_TJS,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Tajikistani somoni',
            ),
            self::CODE_TMT => array(
                'code' => self::CODE_TMT,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Turkmenistani manat',
            ),
            self::CODE_TND => array(
                'code' => self::CODE_TND,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 3,
                'name' => 'Tunisian dinar',
            ),
            self::CODE_TOP => array(
                'code' => self::CODE_TOP,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Tongan paʻanga',
            ),
            self::CODE_TRY => array(
                'code' => self::CODE_TRY,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Turkish lira',
            ),
            self::CODE_TTD => array(
                'code' => self::CODE_TTD,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Trinidad and Tobago dollar',
            ),
            self::CODE_TWD => array(
                'code' => self::CODE_TWD,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'New Taiwan dollar',
            ),
            self::CODE_TZS => array(
                'code' => self::CODE_TZS,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Tanzanian shilling',
            ),
            self::CODE_UAH => array(
                'code' => self::CODE_UAH,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Ukrainian hryvnia',
            ),
            self::CODE_UGX => array(
                'code' => self::CODE_UGX,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Ugandan shilling',
            ),
            self::CODE_USD => array(
                'code' => self::CODE_USD,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'United States dollar',
                'sign' => '$',
            ),
            self::CODE_USN => array(
                'code' => self::CODE_USN,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'United States dollar (next day) (funds code)',
            ),
            self::CODE_USS => array(
                'code' => self::CODE_USS,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'United States dollar (same day) (funds code) (one source[who?] claims it is no longer used, '
                          .'but it is still on the ISO 4217-MA list)',
            ),
            self::CODE_UYI => array(
                'code' => self::CODE_UYI,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 0,
                'name' => 'Uruguay Peso en Unidades Indexadas (URUIURUI) (funds code)',
            ),
            self::CODE_UYU => array(
                'code' => self::CODE_UYU,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Uruguayan peso',
            ),
            self::CODE_UZS => array(
                'code' => self::CODE_UZS,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Uzbekistan som',
            ),
            self::CODE_VEF => array(
                'code' => self::CODE_VEF,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Venezuelan bolívar fuerte',
            ),
            self::CODE_VND => array(
                'code' => self::CODE_VND,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 0,
                'name' => 'Vietnamese đồng',
            ),
            self::CODE_VUV => array(
                'code' => self::CODE_VUV,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 0,
                'name' => 'Vanuatu vatu',
            ),
            self::CODE_WST => array(
                'code' => self::CODE_WST,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Samoan tala',
            ),
            self::CODE_XAF => array(
                'code' => self::CODE_XAF,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 0,
                'name' => 'CFA franc BEAC',
            ),
            self::CODE_XAG => array(
                'code' => self::CODE_XAG,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 0,
                'name' => 'Silver (one troy ounce)',
            ),
            self::CODE_XAU => array(
                'code' => self::CODE_XAU,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 0,
                'name' => 'Gold (one troy ounce)',
            ),
            self::CODE_XBA => array(
                'code' => self::CODE_XBA,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 0,
                'name' => 'European Composite Unit (EURCO) (bond market unit)',
            ),
            self::CODE_XBB => array(
                'code' => self::CODE_XBB,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 0,
                'name' => 'European Monetary Unit (E.M.U.-6) (bond market unit)',
            ),
            self::CODE_XBC => array(
                'code' => self::CODE_XBC,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 0,
                'name' => 'European Unit of Account 9 (E.U.A.-9) (bond market unit)',
            ),
            self::CODE_XBD => array(
                'code' => self::CODE_XBD,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 0,
                'name' => 'European Unit of Account 17 (E.U.A.-17) (bond market unit)',
            ),
            self::CODE_XCD => array(
                'code' => self::CODE_XCD,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'East Caribbean dollar',
            ),
            self::CODE_XDR => array(
                'code' => self::CODE_XDR,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 0,
                'name' => 'Special drawing rights',
            ),
            self::CODE_XFU => array(
                'code' => self::CODE_XFU,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 0,
                'name' => 'UIC franc (special settlement currency)',
            ),
            self::CODE_XOF => array(
                'code' => self::CODE_XOF,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 0,
                'name' => 'CFA Franc BCEAO',
            ),
            self::CODE_XPD => array(
                'code' => self::CODE_XPD,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 0,
                'name' => 'Palladium (one troy ounce)',
            ),
            self::CODE_XPF => array(
                'code' => self::CODE_XPF,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 0,
                'name' => 'CFP franc',
            ),
            self::CODE_XPT => array(
                'code' => self::CODE_XPT,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 0,
                'name' => 'Platinum (one troy ounce)',
            ),
            self::CODE_XTS => array(
                'code' => self::CODE_XTS,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 0,
                'name' => 'Code reserved for testing purposes',
            ),
            self::CODE_XXX => array(
                'code' => self::CODE_XXX,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 0,
                'name' => 'No currency',
            ),
            self::CODE_YER => array(
                'code' => self::CODE_YER,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Yemeni rial',
            ),
            self::CODE_ZAR => array(
                'code' => self::CODE_ZAR,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'South African rand',
            ),
            self::CODE_ZMK => array(
                'code' => self::CODE_ZMK,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Zambian kwacha',
            ),
            self::CODE_ZWL => array(
                'code' => self::CODE_ZWL,
                'isoStatus' => self::ISO_STATUS_ACTIVE,
                'decimalDigits' => 2,
                'name' => 'Zimbabwe dollar',
            )
        );
    }

    /**
     * Get details about all Currencies without ISO Code.
     *
     * @return array info about Currencies without ISO Code
     */
    public static function getInfoForCurrenciesWithoutCurrencyCode()
    {
        return array(
            self::WITHOUT_CURRENCY_CODE_GGP => array(
                'code' => self::WITHOUT_CURRENCY_CODE_GGP,
                'isoStatus' => self::ISO_STATUS_WITHOUT_CURRENCY_CODE,
                'decimalDigits' => 2,
                'name' => 'Guernsey pound',
            ),
            self::WITHOUT_CURRENCY_CODE_JEP => array(
                'code' => self::WITHOUT_CURRENCY_CODE_JEP,
                'isoStatus' => self::ISO_STATUS_WITHOUT_CURRENCY_CODE,
                'decimalDigits' => 2,
                'name' => 'Jersey pound',
            ),
            self::WITHOUT_CURRENCY_CODE_IMP => array(
                'code' => self::WITHOUT_CURRENCY_CODE_IMP,
                'isoStatus' => self::ISO_STATUS_WITHOUT_CURRENCY_CODE,
                'decimalDigits' => 2,
                'name' => 'Isle of Man pound also Manx pound',
            ),
            self::WITHOUT_CURRENCY_CODE_KRI => array(
                'code' => self::WITHOUT_CURRENCY_CODE_KRI,
                'isoStatus' => self::ISO_STATUS_WITHOUT_CURRENCY_CODE,
                'decimalDigits' => 2,
                'name' => 'Kiribati dollar',
            ),
            self::WITHOUT_CURRENCY_CODE_SLS => array(
                'code' => self::WITHOUT_CURRENCY_CODE_SLS,
                'isoStatus' => self::ISO_STATUS_WITHOUT_CURRENCY_CODE,
                'decimalDigits' => 2,
                'name' => 'Somaliland shilling',
            ),
            self::WITHOUT_CURRENCY_CODE_PRB => array(
                'code' => self::WITHOUT_CURRENCY_CODE_PRB,
                'isoStatus' => self::ISO_STATUS_WITHOUT_CURRENCY_CODE,
                'decimalDigits' => 2,
                'name' => 'Transnistrian ruble',
            ),
            self::WITHOUT_CURRENCY_CODE_TVD => array(
                'code' => self::WITHOUT_CURRENCY_CODE_TVD,
                'isoStatus' => self::ISO_STATUS_WITHOUT_CURRENCY_CODE,
                'decimalDigits' => 2,
                'name' => 'Tuvalu dollar',
            )
        );
    }

    /**
     * Get details about all unofficial Currencies.
     *
     * @return array info about unofficial Currencies
     */
    public static function getInfoForCurrenciesWithUnofficialCode()
    {
        return array(
            self::UNOFFICIAL_BTC => array(
                'code' => self::UNOFFICIAL_BTC,
                'isoStatus' => self::ISO_STATUS_UNOFFICIAL,
                'decimalDigits' => 0,
                'name' => 'Bitcoin',
            )
        );
    }

    /**
     * Get details about all historical ISO Currencies.
     *
     * @return array info about historical ISO Currencies
     */
    public static function getInfoForCurrenciesWithHistoricalCode()
    {
        return array(
            self::HISTORICAL_ADF => array(
                'code' => self::HISTORICAL_ADF,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 2,
                'name' => 'Andorran franc (1:1 peg to the French franc)',
            ),
            self::HISTORICAL_ADP => array(
                'code' => self::HISTORICAL_ADP,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 0,
                'name' => 'Andorran peseta (1:1 peg to the Spanish peseta)',
            ),
            self::HISTORICAL_ATS => array(
                'code' => self::HISTORICAL_ATS,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 2,
                'name' => 'Austrian schilling',
            ),
            self::HISTORICAL_BEF => array(
                'code' => self::HISTORICAL_BEF,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 1,
                'name' => 'Belgian franc (currency union with LUF)',
            ),
            self::HISTORICAL_CYP => array(
                'code' => self::HISTORICAL_CYP,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 2,
                'name' => 'Cypriot pound',
            ),
            self::HISTORICAL_DEM => array(
                'code' => self::HISTORICAL_DEM,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 2,
                'name' => 'German mark',
            ),
            self::HISTORICAL_EEK => array(
                'code' => self::HISTORICAL_EEK,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 2,
                'name' => 'Estonian kroon',
            ),
            self::HISTORICAL_ESP => array(
                'code' => self::HISTORICAL_ESP,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 0,
                'name' => 'Spanish peseta',
            ),
            self::HISTORICAL_FIM => array(
                'code' => self::HISTORICAL_FIM,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 2,
                'name' => 'Finnish markka',
            ),
            self::HISTORICAL_FRF => array(
                'code' => self::HISTORICAL_FRF,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 2,
                'name' => 'French franc',
            ),
            self::HISTORICAL_GRD => array(
                'code' => self::HISTORICAL_GRD,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 0,
                'name' => 'Greek drachma',
            ),
            self::HISTORICAL_IEP => array(
                'code' => self::HISTORICAL_IEP,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 2,
                'name' => 'Irish pound (punt in Irish language)',
            ),
            self::HISTORICAL_ITL => array(
                'code' => self::HISTORICAL_ITL,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 0,
                'name' => 'Italian lira',
            ),
            self::HISTORICAL_LUF => array(
                'code' => self::HISTORICAL_LUF,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 1,
                'name' => 'Luxembourg franc (currency union with BEF)',
            ),
            self::HISTORICAL_MCF => array(
                'code' => self::HISTORICAL_MCF,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 1,
                'name' => 'Monegasque franc (currency union with FRF)',
            ),
            self::HISTORICAL_MAF => array(
                'code' => self::HISTORICAL_MAF,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 0,
                'name' => 'Moroccan franc',
            ),
            self::HISTORICAL_MTL => array(
                'code' => self::HISTORICAL_MTL,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 2,
                'name' => 'Maltese lira',
            ),
            self::HISTORICAL_NLG => array(
                'code' => self::HISTORICAL_NLG,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 2,
                'name' => 'Netherlands guilder',
            ),
            self::HISTORICAL_PTE => array(
                'code' => self::HISTORICAL_PTE,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 0,
                'name' => 'Portuguese escudo',
            ),
            self::HISTORICAL_SIT => array(
                'code' => self::HISTORICAL_SIT,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 1,
                'name' => 'Slovenian tolar',
            ),
            self::HISTORICAL_SKK => array(
                'code' => self::HISTORICAL_SKK,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 1,
                'name' => 'Slovak koruna',
            ),
            self::HISTORICAL_SML => array(
                'code' => self::HISTORICAL_SML,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 0,
                'name' => 'San Marinese lira (currency union with ITL and VAL)',
            ),
            self::HISTORICAL_VAL => array(
                'code' => self::HISTORICAL_VAL,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 0,
                'name' => 'Vatican lira (currency union with ITL and SML)',
            ),
            self::HISTORICAL_XEU => array(
                'code' => self::HISTORICAL_XEU,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 0,
                'name' => 'European Currency Unit (1 XEU = 1 EUR)',
            ),
            self::HISTORICAL_AFA => array(
                'code' => self::HISTORICAL_AFA,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 0,
                'name' => 'Afghan afghani',
            ),
            self::HISTORICAL_AON => array(
                'code' => self::HISTORICAL_AON,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 0,
                'name' => 'Angolan new kwanza',
            ),
            self::HISTORICAL_AOR => array(
                'code' => self::HISTORICAL_AOR,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 0,
                'name' => 'Angolan kwanza readjustado',
            ),
            self::HISTORICAL_ARL => array(
                'code' => self::HISTORICAL_ARL,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 2,
                'name' => 'Argentine peso ley',
            ),
            self::HISTORICAL_ARP => array(
                'code' => self::HISTORICAL_ARP,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 2,
                'name' => 'Argentine peso argentino',
            ),
            self::HISTORICAL_ARA => array(
                'code' => self::HISTORICAL_ARA,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 2,
                'name' => 'Argentine austral',
            ),
            self::HISTORICAL_AZM => array(
                'code' => self::HISTORICAL_AZM,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 0,
                'name' => 'Azerbaijani manat',
            ),
            self::HISTORICAL_BGL => array(
                'code' => self::HISTORICAL_BGL,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 2,
                'name' => 'Bulgarian lev A/99',
            ),
            self::HISTORICAL_BOP => array(
                'code' => self::HISTORICAL_BOP,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 2,
                'name' => 'Bolivian peso',
            ),
            self::HISTORICAL_BRB => array(
                'code' => self::HISTORICAL_BRB,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 2,
                'name' => 'Brazilian cruzeiro novo',
            ),
            self::HISTORICAL_BRC => array(
                'code' => self::HISTORICAL_BRC,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 2,
                'name' => 'Brazilian cruzado',
            ),
            self::HISTORICAL_BRE => array(
                'code' => self::HISTORICAL_BRE,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 2,
                'name' => 'Brazilian cruzeiro',
            ),
            self::HISTORICAL_BRN => array(
                'code' => self::HISTORICAL_BRN,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 2,
                'name' => 'Brazilian cruzado novo',
            ),
            self::HISTORICAL_BRR => array(
                'code' => self::HISTORICAL_BRR,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 2,
                'name' => 'Brazilian cruzeiro real',
            ),
            self::HISTORICAL_CLE => array(
                'code' => self::HISTORICAL_CLE,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 0,
                'name' => 'Chilean escudo',
            ),
            self::HISTORICAL_CSD => array(
                'code' => self::HISTORICAL_CSD,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 2,
                'name' => 'Serbian dinar',
            ),
            self::HISTORICAL_CSK => array(
                'code' => self::HISTORICAL_CSK,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 0,
                'name' => 'Czechoslovak koruna',
            ),
            self::HISTORICAL_DDM => array(
                'code' => self::HISTORICAL_DDM,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 0,
                'name' => 'East German Mark of the GDR (East Germany)',
            ),
            self::HISTORICAL_ECS => array(
                'code' => self::HISTORICAL_ECS,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 0,
                'name' => 'Ecuadorian sucre',
            ),
            self::HISTORICAL_ECV => array(
                'code' => self::HISTORICAL_ECV,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 0,
                'name' => 'Ecuador Unidad de Valor Constante (funds code) (discontinued)',
            ),
            self::HISTORICAL_GQE => array(
                'code' => self::HISTORICAL_GQE,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 0,
                'name' => 'Equatorial Guinean ekwele',
            ),
            self::HISTORICAL_ESA => array(
                'code' => self::HISTORICAL_ESA,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 0,
                'name' => 'Spanish peseta (account A)',
            ),
            self::HISTORICAL_ESB => array(
                'code' => self::HISTORICAL_ESB,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 0,
                'name' => 'Spanish peseta (account B)',
            ),
            self::HISTORICAL_GNE => array(
                'code' => self::HISTORICAL_GNE,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 0,
                'name' => 'Guinean syli',
            ),
            self::HISTORICAL_GHC => array(
                'code' => self::HISTORICAL_GHC,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 0,
                'name' => 'Ghanaian cedi',
            ),
            self::HISTORICAL_GWP => array(
                'code' => self::HISTORICAL_GWP,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 0,
                'name' => 'Guinea-Bissau peso',
            ),
            self::HISTORICAL_ILP => array(
                'code' => self::HISTORICAL_ILP,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 3,
                'name' => 'Israeli lira',
            ),
            self::HISTORICAL_ILR => array(
                'code' => self::HISTORICAL_ILR,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 2,
                'name' => 'Israeli shekel',
            ),
            self::HISTORICAL_ISJ => array(
                'code' => self::HISTORICAL_ISJ,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 0,
                'name' => 'Icelandic old krona',
            ),
            self::HISTORICAL_LAJ => array(
                'code' => self::HISTORICAL_LAJ,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 0,
                'name' => 'Lao kip',
            ),
            self::HISTORICAL_MGF => array(
                'code' => self::HISTORICAL_MGF,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 0,
                'name' => 'Malagasy franc',
            ),
            self::HISTORICAL_MKN => array(
                'code' => self::HISTORICAL_MKN,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 0,
                'name' => 'Old Macedonian denar A/93',
            ),
            self::HISTORICAL_MLF => array(
                'code' => self::HISTORICAL_MLF,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 0,
                'name' => 'Mali franc',
            ),
            self::HISTORICAL_MVQ => array(
                'code' => self::HISTORICAL_MVQ,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 0,
                'name' => 'Maldivian rupee',
            ),
            self::HISTORICAL_MXP => array(
                'code' => self::HISTORICAL_MXP,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 0,
                'name' => 'Mexican peso',
            ),
            self::HISTORICAL_MZM => array(
                'code' => self::HISTORICAL_MZM,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 0,
                'name' => 'Mozambican metical',
            ),
            self::HISTORICAL_NFD => array(
                'code' => self::HISTORICAL_NFD,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 2,
                'name' => 'Newfoundland dollar',
            ),
            self::HISTORICAL_PEH => array(
                'code' => self::HISTORICAL_PEH,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 0,
                'name' => 'Peruvian sol',
            ),
            self::HISTORICAL_PEI => array(
                'code' => self::HISTORICAL_PEI,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 0,
                'name' => 'Peruvian inti',
            ),
            self::HISTORICAL_PLZ => array(
                'code' => self::HISTORICAL_PLZ,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 0,
                'name' => 'Polish zloty A/94',
            ),
            self::HISTORICAL_ROL => array(
                'code' => self::HISTORICAL_ROL,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 0,
                'name' => 'Romanian leu A/05',
            ),
            self::HISTORICAL_RUR => array(
                'code' => self::HISTORICAL_RUR,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 0,
                'name' => 'Russian rouble A/97',
            ),
            self::HISTORICAL_SDD => array(
                'code' => self::HISTORICAL_SDD,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 0,
                'name' => 'Sudanese dinar',
            ),
            self::HISTORICAL_SDP => array(
                'code' => self::HISTORICAL_SDP,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 0,
                'name' => 'Sudanese old pound',
            ),
            self::HISTORICAL_SRG => array(
                'code' => self::HISTORICAL_SRG,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 0,
                'name' => 'Suriname guilder',
            ),
            self::HISTORICAL_SUR => array(
                'code' => self::HISTORICAL_SUR,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 0,
                'name' => 'Soviet Union rouble',
            ),
            self::HISTORICAL_SVC => array(
                'code' => self::HISTORICAL_SVC,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 2,
                'name' => 'Salvadoran colón',
            ),
            self::HISTORICAL_TJR => array(
                'code' => self::HISTORICAL_TJR,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 0,
                'name' => 'Tajikistani ruble',
            ),
            self::HISTORICAL_TMM => array(
                'code' => self::HISTORICAL_TMM,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 0,
                'name' => 'Turkmenistani manat',
            ),
            self::HISTORICAL_TRL => array(
                'code' => self::HISTORICAL_TRL,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 0,
                'name' => 'Turkish lira A/05',
            ),
            self::HISTORICAL_UAK => array(
                'code' => self::HISTORICAL_UAK,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 0,
                'name' => 'Ukrainian karbovanets',
            ),
            self::HISTORICAL_UGS => array(
                'code' => self::HISTORICAL_UGS,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 0,
                'name' => 'Ugandan shilling A/87',
            ),
            self::HISTORICAL_UYN => array(
                'code' => self::HISTORICAL_UYN,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 0,
                'name' => 'Uruguay old peso',
            ),
            self::HISTORICAL_VEB => array(
                'code' => self::HISTORICAL_VEB,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 2,
                'name' => 'Venezuelan bolívar',
            ),
            self::HISTORICAL_XFO => array(
                'code' => self::HISTORICAL_XFO,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 0,
                'name' => 'Gold franc (special settlement currency)',
            ),
            self::HISTORICAL_YDD => array(
                'code' => self::HISTORICAL_YDD,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 0,
                'name' => 'South Yemeni dinar',
            ),
            self::HISTORICAL_YUD => array(
                'code' => self::HISTORICAL_YUD,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 2,
                'name' => 'Yugoslav dinar',
            ),
            self::HISTORICAL_YUN => array(
                'code' => self::HISTORICAL_YUN,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 2,
                'name' => 'Yugoslav dinar',
            ),
            self::HISTORICAL_YUR => array(
                'code' => self::HISTORICAL_YUR,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 2,
                'name' => 'Yugoslav dinar',
            ),
            self::HISTORICAL_YUO => array(
                'code' => self::HISTORICAL_YUO,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 2,
                'name' => 'Yugoslav dinar',
            ),
            self::HISTORICAL_YUG => array(
                'code' => self::HISTORICAL_YUG,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 2,
                'name' => 'Yugoslav dinar',
            ),
            self::HISTORICAL_YUM => array(
                'code' => self::HISTORICAL_YUM,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 2,
                'name' => 'Yugoslav dinar',
            ),
            self::HISTORICAL_ZAL => array(
                'code' => self::HISTORICAL_ZAL,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 0,
                'name' => 'South African financial rand (funds code) (discontinued)',
            ),
            self::HISTORICAL_ZRN => array(
                'code' => self::HISTORICAL_ZRN,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 2,
                'name' => 'Zaïrean new zaïre',
            ),
            self::HISTORICAL_ZRZ => array(
                'code' => self::HISTORICAL_ZRZ,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 3,
                'name' => 'Zaïrean zaïre',
            ),
            self::HISTORICAL_ZWC => array(
                'code' => self::HISTORICAL_ZWC,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 2,
                'name' => 'Rhodesian dollar',
            ),
            self::HISTORICAL_ZWD => array(
                'code' => self::HISTORICAL_ZWD,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 2,
                'name' => 'Zimbabwean dollar A/06',
            ),
            self::HISTORICAL_ZWN => array(
                'code' => self::HISTORICAL_ZWN,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 2,
                'name' => 'Zimbabwean dollar A/08',
            ),
            self::HISTORICAL_ZWR => array(
                'code' => self::HISTORICAL_ZWR,
                'isoStatus' => self::ISO_STATUS_HISTORICAL,
                'decimalDigits' => 2,
                'name' => 'Zimbabwean dollar A/09',
            )
        );
    }

    /**
     * Use this to express no currency.
     */
    const NONE = Currency::CODE_XXX;

    /**
     * Use this in all test cases.
     */
    const TEST = Currency::CODE_XTS;
}
