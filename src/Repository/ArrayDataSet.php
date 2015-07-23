<?php

namespace ByJG\AnyDataset\Repository;

use UnexpectedValueException;
use ByJG\AnyDataset\Repository\ArrayDataSet;
use ByJG\AnyDataset\Repository\ArrayIIterator;
use ByJG\AnyDataset\Repository\IIterator;

class ArrayDataSet
{
	/**
	 * @var Array
	 */
	protected $_array;

	/**
	 * Constructor Method
	 *
	 * @param Array $array
	 * @return ArrayDataSet
	 */
	public function __construct($array, $fieldName="value")
	{

		$this->_array = array();

		if (!$array)
			return;
		
		if (is_array($array))
		{
			foreach ($array as $key => $value) 
			{
				if (is_array($value))
				{
					$this->_array[$key] = $value;
				}
				elseif (!is_object($value))
				{
					$this->_array[$key] = array($fieldName => $value);
				}
				else 
				{
					$result = array("__class" => get_class($value));
					$methods = get_class_methods($value);
					foreach ($methods as $method)
					{
						if (strpos($method, "get") === 0)
						{
							$result[substr($method,3)] = $value->{$method}();
						}
					}
					$this->_array[$key] = $result;
					$props = get_object_vars($value);
					$this->_array[$key] += $props;
				}
			}
		}
		else
		{
			throw new UnexpectedValueException("You need to pass an array");
		}
	}

	/**
	 * Return a IIterator
	 *
	 * @return IIterator
	 */
	public function getIterator()
	{
		return new ArrayIIterator($this->_array);
	}
	
}
?>
