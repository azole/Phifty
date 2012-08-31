<?php

/*

_('en')
_('ja')
_('zh_TW')
_('zh_CN')
_('en_US')
_('fr')

*/

namespace Phifty {
use Exception;
define( 'L10N_LOCALE_KEY' , 'locale' );

class Locale
{
    public $current;
    public $langList = array();
    public $localedir;
    public $domain;
    public $defaultLang;

    function setDefault( $lang )
    {
        $this->defaultLang = $lang;
        return $this;
    }

    function getDefault() {
        return $this->defaultLang;
    }

    function init( $force_lang = null  )
    {
        $lang = null;

        if( $force_lang )
            $lang = $force_lang;

        if( ! $lang && isset($_GET[ L10N_LOCALE_KEY ]) )
            $lang = $_GET[ L10N_LOCALE_KEY ];
        if( ! $lang && isset($_POST[ L10N_LOCALE_KEY ]) )
            $lang = $_POST[ L10N_LOCALE_KEY ];
        if( ! $lang && isset( $_SESSION[ L10N_LOCALE_KEY ] ) )
            $lang = @$_SESSION[ L10N_LOCALE_KEY ];
        
        if( ! $lang && isset( $_COOKIE['locale'] ) )
            $lang = @$_COOKIE['locale'];

        if( ! $lang )
            $lang = $this->defaultLang;
        if( ! $lang )
            throw new Exception( 'Locale: Language is not define.' );
        $this->speak( $lang );
        return $this;
    }


    function saveSession()
    {
        kernel()->session->set( L10N_LOCALE_KEY , $this->current );
    }

    function saveCookie()
    {
        $time = time() + 60 * 60 * 24 * 30;
        @setcookie( L10N_LOCALE_KEY , $this->current , $time , '/' );
    }

    function getCurrentLang()
    {
        return $this->current;
    }

    // set current language
    function speak( $lang )
    {
        $this->current = $lang;
        $this->saveCookie();
        $this->saveSession();
        $this->initGettext();
        return $this;
    }

    function isSpeaking( $lang )
    {
        return $this->current == $lang;
    }


    function current()
    {
        return $this->current;
    }

    function speaking()
    {
        return $this->current;
    }

    function available()
    {
        return $this->getLangList();
    }

    // get available language list
    function getLangList()
    {
        // update language Label
        foreach( $this->langList as $n => $v ) {
            $this->langList[ $n ] = _( $n );
        }
        return $this->langList;
    }

    function setLangList( $list )
    {
        $this->langList = $list;
    }

    function add( $lang , $name = null )
    {
        if( ! $name )
            $name = _( $lang );
        $this->langList[ $lang ] = $name;
        return $this;
    }

    function remove( $lang )
    {
        unset( $this->langList[ $lang ] );
        return $this;
    }

    // get language name from language hash
    function name( $lang )
    {
        return @$this->langList[ $lang ];
    }

    function domain( $domain )
    {
        $this->domain = $domain;
        return $this;
    }

    function localedir( $dir )
    {
        $this->localedir = $dir;
        return $this;
    }

    function setupEnv()
    {
        $lang = $this->current;
        putenv("LANG=$lang");
        putenv("LANGUAGE=$lang.UTF-8");
        setlocale(LC_MESSAGES, $lang );
        setlocale(LC_ALL,  "$lang.UTF-8" );
        setlocale(LC_TIME, "$lang.UTF-8");
    }

    function initGettext( $textdomain = null , $localedir = null )
    {
        if( ! $textdomain )
            $textdomain = $this->domain;

        if( ! $textdomain )
            throw new Exception( 'Locale: textdomain is not defined.' );

        if( ! $localedir )
            $localedir = $this->localedir;

        if( ! $localedir )
            throw new Exception( 'Locale: locale dir is not defined.' );

        if( ! file_exists( $localedir ) )
            throw new Exception( "Locale: locale dir does not exist: $localedir" );

        $this->setupEnv();

        bindtextdomain( $textdomain, $localedir );
        bind_textdomain_codeset( $textdomain, 'UTF-8');
        textdomain( $textdomain );
        return $this;
    }
}


/*
function current_language()
{
    return l10n()->speaking();
}

function current_lang()
{
    return l10n()->speaking();
}
*/

}

namespace {
    function __()
    {
        $args = func_get_args();
        $msg = _( array_shift( $args ) );
        $id = 1;
        foreach( $args as $arg ) {
            $msg = str_replace( "%$id" , $arg , $msg );
            $id++;
        }
        return $msg;
    }
}

