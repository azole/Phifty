<?php


namespace Phifty\Request;

/*

    $f = new UploadFile( 'ufile' , 0 );   // name and index   , $_FILES['ufile'][...][0]
    $f->move_to_dir( "file_dirs" );
    $f->move( "target.file" );


    $f->

 */
class File {

    // file data object
    var $column;  // file column name
    var $name;
    var $type;
    var $size;
    var $tmp_name;
    var $error;

    public function __construct( $name , $index = null )  {
        $this->column = $name;

        $hasFile = (bool) @$_FILES[$name]['tmp_name'];
        if( $hasFile ) {
            $keys = array_keys( @$_FILES[ $name ] );
            if( $index ) {
                foreach( $keys as $key )
                    $this->$key = $_FILES[ $name ][ $key ][ $index ];
            } else {
                foreach( $keys as $key )
                    $this->$key = $_FILES[ $name ][ $key ];
            }
        }
    }

    public function __destruct() {

    }

    public function get_kbytes() {
        return (int) $this->size / 1024;
    }

    private function join_paths() {
        $args = func_get_args();
        $paths = array();
        foreach ($args as $arg)
            $paths = array_merge($paths, (array)$arg);

        $paths2 = array();
        foreach ($paths as $i=>$path)
        {   $path = trim($path, '/');
            if (strlen($path))
                $paths2[]= $path;
        }
        $result = join('/', $paths2); // If first element of old path was absolute, make this one absolute also
        if (strlen($paths[0]) && substr($paths[0], 0, 1) == '/')
            return '/'.$result;
        return $result;
    }

    public function get_name() {
        return $this->name;
    }

    public function get_extension() {
        $parts = explode('.',$this->name);
        return end($parts);
    }

    public function get_size() {
        return $this->size;
    }

    public function move_to_dir( $dir , $src_file = null ) {
        $file = $this->tmp_name;
        $src_dir = dirname( $file );

        if( ! $src_file )
            $src_file = basename( $file );

        if( ! file_exists( $dir ) ) {
            mkdir( $dir );
            chmod( $dir , 0777 );
        }

        $new_path = $this->join_paths( $dir , $src_file );
        $ret = $this->move( $new_path );
        if( $ret )
            return $new_path;
        return false;
    }

    public function move( $filepath ) {
#          echo $this->tmp_name;
#          echo $_FILES['file']['tmp_name'];
        if(move_uploaded_file( $this->tmp_name , $filepath)) {
            # echo "File is valid, and was successfully uploaded.\n";
            return true;
        }
        return false;
    }

    public function delete_tmp() {
        unlink( $this->tmp_name );
    }

    public function found() {
        return $this->name ? true : false;
    }

    public function has_error() {
        return (bool) $this->error;
    }

    public function error_message() {
        $error = $this->error;

        // error messages for normal users.
        switch( $error ) {
            case UPLOAD_ERR_OK:
                return _("No Error");
            case UPLOAD_ERR_INI_SIZE || UPLOAD_ERR_FORM_SIZE:
                return _("The upload file exceeds the limit.");
            case UPLOAD_ERR_PARTIAL:
                return _("The uploaded file was only partially uploaded.");
            case UPLOAD_ERR_NO_FILE:
                return _("No file was uploaded.");
            case UPLOAD_ERR_CANT_WRITE:
                return _("Failed to write file to disk.");
            case UPLOAD_ERR_EXTENSION:
                return _("A PHP extension stopped the file upload.");
            default:
                return _("Unknown Error.");
        }

        // built-in php error description
        switch( $error ) {
            case UPLOAD_ERR_OK:
                return _("There is no error, the file uploaded with success.");
            case UPLOAD_ERR_INI_SIZE:
                return _("The uploaded file exceeds the upload_max_filesize directive in php.ini.");
            case UPLOAD_ERR_FORM_SIZE:
                return _("The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.");
            case UPLOAD_ERR_PARTIAL:
                return _("The uploaded file was only partially uploaded.");
            case UPLOAD_ERR_NO_FILE:
                return _("No file was uploaded.");
            case UPLOAD_ERR_NO_TMP_DIR:
                return _("Missing a temporary folder. Introduced in PHP 4.3.10 and PHP 5.0.3.");
            case UPLOAD_ERR_CANT_WRITE:
                return _("Failed to write file to disk. Introduced in PHP 5.1.0.");
            case UPLOAD_ERR_EXTENSION:
                return _("A PHP extension stopped the file upload. PHP does not provide a way to ascertain which extension caused the file upload to stop; examining the list of loaded extensions with phpinfo() may help. Introduced in PHP 5.2.0.");
            default:
                return _("Unknown Error.");
        }
    }

}


?>
