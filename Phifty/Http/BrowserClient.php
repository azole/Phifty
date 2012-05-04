<?php
namespace Phifty\Http;
use Phifty\Http\Browscap;

/**
 * Debian system:
 *
 * $ apt-get install geoip-bin geoip-database libgeoip-dev libgeoip1 php5-geoip 
 */
class BrowserClient
{
    public $ip;

    public $host;


    /**
     * AS for Asia
     * EU for Europe
     * SA for South America
     * AF for Africa
     * AN for µAntartica
     * OC for Oceania
     * NA for North America
     */
    public $continent;

    public $countryCode;

    public $country;

    public $city;

    public $latitude;

    public $longitude;

    public $geoipSupports = false;

    public $userAgent;

    public $browser = array();

    function __construct($ip = null)
    {
        if( $ip ) {
            $this->ip = $ip;
        }
        elseif ( isset( $_SERVER['HTTP_CLIENT_IP']) ) {
            $this->ip = $_SERVER['HTTP_CLIENT_IP'];
        }
        elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $this->ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        else {
            $this->ip = $_SERVER['REMOTE_ADDR'];
        }

        if( isset($_SERVER['HTTP_USER_AGENT']) ) {
            $this->userAgent = $_SERVER['HTTP_USER_AGENT'];
        }

        if( $this->ip && function_exists('gethostbyaddr') ) {
            $this->host = gethostbyaddr( $this->ip );
        }

        // get extended informations
        if( $this->ip && extension_loaded('geoip') ) {
            if( $record = geoip_record_by_name($this->ip) ) {
                $this->continent = $record['continent_code'];
                $this->countryCode = $record['country_code'];
                $this->country = $record['country_name'];
                $this->city = $record['city'];
                $this->latitude = $record['latitude'];
                $this->longitude = $record['longitude'];
                $this->geoipSupports = true;
            }
        }

        // if browscap string is set in php.ini, we can use get_browser function
        if( $browscapStr = ini_get('browscap') ) {
            $this->browser = get_browser( 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.7) Gecko/20040803 Firefox/0.9.3', true);
        }
        else {
            $browscap = new Phifty\Http\Browscap('cache');
        $info = $browscap->getBrowser('Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.7) Gecko/20040803 Firefox/0.9.3', true);
        ok( $info['Platform'] );


        }

    }
}

