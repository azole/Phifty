<?php
namespace Phifty;

// Upload Header is like:
//
//        (
//            [HOST] => phifty.local
//            [CONNECTION] => keep-alive
//            [REFERER] => http://phifty.local/bs/image
//            [CONTENT-LENGTH] => 96740
//            [ORIGIN] => http://phifty.local
//            [UPLOAD-TYPE] => image/png
//            [UPLOAD-FILENAME] => Screen shot 2011-08-17 at 10.25.58 AM.png
//            [UPLOAD-SIZE] => 72555
//            [USER-AGENT] => Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_8) AppleWebKit/535.1 (KHTML, like Gecko) Chrome/13.0.782.218 Safari/535.1
//            [CONTENT-TYPE] => application/xml
//            [ACCEPT] => */*
//            [ACCEPT-ENCODING] => gzip,deflate,sdch
//            [ACCEPT-LANGUAGE] => en-US,en;q=0.8
//            [ACCEPT-CHARSET] => UTF-8,*;q=0.5
//            [COOKIE] => PHPSESSID=6dqs40ngvldtjrg9iim3uafnl3; locale=zh_TW
//        )

class Html5UploadHandler 
{
    public $content;
    public $headers;
    public $uploadDir;

    function __construct() 
    {
        $this->content = $this->decodeContent();
        $headers = getallheaders();
        $this->headers = array_change_key_case($headers, CASE_UPPER);
    }

    function supportSendAsBinary() 
    {
        return count($_FILES) > 0;
    }

    function getFileName()
    {
        return $this->headers[ 'UPLOAD-FILENAME' ];
    }

    function getFileType()
    {
        return $this->headers[ 'UPLOAD-TYPE' ];
    }

    function getFileSize()
    {
        return $this->headers[ 'UPLOAD-SIZE' ];
    }

    function getContent()
    {
        return $this->content;
    }

    function getHeaders()
    {
        return $this->headers;
    }

    function setUploadDir( $dir )
    {
        $this->uploadDir = $dir;
    }

    function decodeContent()
    {
        $content = file_get_contents('php://input');
        if(isset($_GET['base64'])) {
            $content = base64_decode( $content );
        } 
        return $content;
    }

    function hasFile()
    {
        if( count($_FILES) > 0 )
            return true;

        if( $this->content )
            return true;

        return false;
    }

    function move( $newFileName = null )
    {
        if( $this->supportSendAsBinary() ) {

            /* process with $_FILES */
            // $_FILES['upload']['tmp_name'];
            $filename = $newFileName ? $newFileName : $_FILES['upload']['name'];
            $path = $this->uploadDir . DIRECTORY_SEPARATOR . $filename;
            if( move_uploaded_file( $_FILES['upload']['tmp_name'] , $path ) ) {
                return $path;
            }
            return false;
        } else {
            $content = $this->getContent();
            $filename = $newFileName ? $newFileName : $this->getFileName();
            $path = $this->uploadDir . DIRECTORY_SEPARATOR . $filename;
            if( file_put_contents( $path , $content ) )
                return $path;
            return false;
        }
    }
}

