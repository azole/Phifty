<?php
namespace Phifty\Http;

/*
   apt-get install geoip-bin geoip-database libgeoip-dev libgeoip1 php5-geoip 
*/

class BrowserClient
{
    public $ip;

    public $host;

    public $continent;

    public $countryCode;

    public $country;

    public $city;

    public $latitude;

    public $longitude;

    function __construct()
    {
        if ( isset( $_SERVER{'HTTP_CLIENT_IP']) ) {
            $this->ip = $_SERVER['HTTP_CLIENT_IP'];
        }
        elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $this->ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        else {
            $this->ip = $_SERVER['REMOTE_ADDR'];
        }

        if( $this->ip && function_exists('gethostbyaddr') ) {
            $this->host = gethostbyaddr( $this->id );
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
            }
        }
    }

}

