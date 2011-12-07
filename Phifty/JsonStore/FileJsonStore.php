<?php
/*
 * This file is part of the Phifty package.
 *
 * (c) Yo-An Lin <cornelius.howl@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
namespace Phifty\JsonStore;

/**
 * Store Json in File.
 *
 * $store = new FileJsonStore;
 * $store->setRootDir( 'path/to/dir' );
 *
 * save object in path/to/dir/1.json
 *
 *      $store->store( $id , array( ...  ) );
 *
 * load object
 *
 *      $store->load( $id );
 * 
 * update object
 *
 *      $store->update( $id , array( ... ) );
 *
 */
class FileJsonStore 
{
    public $rootDir;

    function setRootDir($dir)
    {
        $this->rootDir = $dir;
    }

    function getFilePath($id)
    {
        return $this->rootDir . DIRECTORY_SEPARATOR . $id
    }

    function store($data)
    {
        $id = $data['id'];
        $file = $this->getFilePath($id);
        if( file_put_contents( $file , json_encode($data) ) === false )
            return false;
        return true;
    }

    function load($id)
    {
        $file = $this->getFilePath($id);
        if( file_exists($file) ) {
            return json_decode($file);
        }
        return false;
    }

    function update($data)
    {
        $id = $data['id'];
        $orig_data = $this->load($id);
        if( $orig_data ) {
            $data = array_merge( $orig_data, $data );
            $this->store($data);
        }
    }
}
