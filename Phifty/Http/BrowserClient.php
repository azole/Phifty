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

    public $refer;

    public $browser = array();

    function __construct($ip = null, $userAgentStr = null)
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

        if( $userAgentStr ) {
            $this->userAgent = $userAgent;
        }
        elseif( isset($_SERVER['HTTP_USER_AGENT']) ) {
            $this->userAgent = $_SERVER['HTTP_USER_AGENT'];
        }

        if( $this->ip && function_exists('gethostbyaddr') ) {
            $this->host = gethostbyaddr( $this->ip );
        }

        if( isset($_SERVER['HTTP_REFERER']) ) {
            $this->refer = $_SERVER['HTTP_REFERER'];
        }

        // get extended informations
        if( extension_loaded('geoip') )
            $this->geoipSupports = true;
        if( $this->ip && $this->geoipSupports ) {
            if( $record = @geoip_record_by_name($this->ip) ) {
                $this->continent     = $record['continent_code'];
                $this->countryCode   = $record['country_code'];
                $this->country       = $record['country_name'];
                $this->city          = $record['city'];
                $this->latitude      = $record['latitude'];
                $this->longitude     = $record['longitude'];
            }
        }

        // if browscap string is set in php.ini, we can use get_browser function
        if( $browscapStr = ini_get('browscap') ) {
            $this->browser = (object) get_browser( $userAgentStr , true);
        }
        else {
            $browscap = new Browscap( kernel()->cacheDir );
            $this->browser = (object) $browscap->getBrowser( $userAgentStr , true);
        }
    }
}

