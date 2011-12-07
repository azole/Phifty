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
 * $store = new FileJsonStore('ModelName');
 *
 * $store->add(array( .... ));
 * $store->add(array( .... ));
 * $store->add(array( .... ));
 *
 * $store->insert(0,array( .... ));
 *
 * $model = $store->get(1);
 *
 * $store->remove($id);
 * $store->remove($model);
 *
 * $store->save();
 *
 * $list = $store->load();
 *
 */

use Phifty\JsonStore\FileJsonModel;

class FileJsonStore
{
    public $name;
    public $rootDir;
    public $items;

    function __construct($name,$rootDir)
    {
        $this->name = $name;
        $this->rootDir = $rootDir;
        $this->items = array();
        if( ! file_exists($this->rootDir ) )
            mkdir( $this->rootDir , 0755 , true ); // recursive
    }

    function getStoreFile()
    {
        $path = $this->rootDir . DIRECTORY_SEPARATOR . $this->name . '.json';
        return $path;
    }

    function load()
    {
        $file = $this->getStoreFile();
        if( file_exists($file) ) {
            $data = json_decode(file_get_contents($file),true);
            if( isset($data['items']) ) {
                $this->items = $data['items'];
                return count($this->items);
            }
        }
    }

    function save()
    {
        $file = $this->getStoreFile();
        $string = json_encode( array( 'items' => $this->items ) );
        if( file_put_contents( $file, $string ) === false )
            return false;
        return true;
    }

    function add($record)
    {
        $keys = array_keys($this->items);
        sort($keys);
        $last_key = (int) end($keys);
        $last_key++;
        $record->id = $last_key;
        $this->items[$last_key] = $record->getData();
        return $last_key;
    }

    function update($record)
    {
        $id = $record->id;
        $data = get_object_vars($record);
        if( isset($this->items[$id]) ) {
            $this->items[$id] = $record;
            return true;
        }
        return false;
    }

    function get($id)
    {
        if( isset($this->items[$id]) ) 
            return $this->items[$id];
    }

    function items()
    {
        $that = $this;
        return array_map( function($e) use ($that) {
            return new FileJsonModel( $that->name, $that, $e );
        }, array_values($this->items) );
    }

    function remove($id)
    {
        unset($this->items[$id]);
    }

    function destroy()
    {
        $file = $this->getStoreFile();
        if( file_exists($file) ) {
            unlink( $file );
            $this->items = null;
            return true;
        }
    }

    function __destruct()
    {
        if( $this->items )
            $this->save();
    }
}



