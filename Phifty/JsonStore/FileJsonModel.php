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



/*
 * Store data in json file.
 *
 * $record = new FileJsonModel('ModelName',$store);
 *
 * save object in path/to/dir/1.json
 *
 *      $record->column = value;
 *
 * load object
 *
 *      $record->load( $id );
 * 
 * update object
 *
 *      $record->update( $id , array( ... ) );
 *
 */
class FileJsonModel
{
    protected $name;
	protected $store;
	protected $data;

    function __construct($name,$store,$data = null)
    {
        $this->name = $name;
		$this->store = $store;
			$this->data = $data ? $data : array();
    }

	function __get($name)
	{
		if( isset($this->data[$name] ) )
			return $this->data[$name];
	}

	function __set($name,$value)
	{
		$this->data[$name] = $value;
	}

	function hasId()
	{
		return isset($this->data['id']) && $this->data['id'];
	}

	function getData()
	{
		return $this->data;
	}

    function save($data = null)
    {
		if( $data )
			$this->data = array_merge($this->data,$data);

		if( $this->hasId() )
			return $this->store->update($this);
		else
			return $this->store->add($this);
    }

    function load($id)
    {
		$data = $this->store->get($id);
		$this->data = $data;
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


