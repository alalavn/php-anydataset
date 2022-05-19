<?php

namespace ByJG\AnyDataset\Core;

use ByJG\Serializer\BinderObject;
use ByJG\Serializer\DumpToArrayInterface;

class Row extends BinderObject implements DumpToArrayInterface
{

    /**
     * \DOMNode represents a Row
     * @var \DOMElement
     */
    private $node = null;
    private $row = null;
    private $originalRow = null;

    protected $fieldNameCaseSensitive = true;

    /**
     * Row constructor
     *
     * @param array()
     * @throws \ByJG\Serializer\Exception\InvalidArgumentException
     */
    public function __construct($instance = null)
    {
        if (is_null($instance)) {
            $this->row = array();
        } elseif (is_array($instance)) {
            $this->row = $instance;
        } else {
            $this->row = array();
            $this->bind($instance);
        }

        $this->acceptChanges();
    }

    /**
     * Add a string field to row
     * @param string $name
     * @param string $value
     */
    public function addField($name, $value)
    {
        $name = $this->getHydratedFieldName($name);

        if (!array_key_exists($name, $this->row)) {
            $this->row[$name] = $value;
        } elseif (is_array($this->row[$name])) {
            $this->row[$name][] = $value;
        } else {
            $this->row[$name] = array($this->row[$name], $value);
        }
        $this->informChanges();
    }

    /**
     * @param string $name - Field name
     * @return string
     * @desc et the string value from a field name
     */
    public function get($name)
    {
        $name = $this->getHydratedFieldName($name);

        if (!array_key_exists($name, $this->row)) {
            return null;
        }

        $result = $this->row[$name];
        if (is_array($result)) {
            return array_shift($result);
        } else {
            return $result;
        }
    }

    /**
     * Get array from a single field
     *
     * @param string $fieldName
     * @return array
     */
    public function getAsArray($fieldName)
    {
        $fieldName = $this->getHydratedFieldName($fieldName);

        if (!array_key_exists($fieldName, $this->row)) {
            return [];
        }

        $result = $this->row[$fieldName];

        if (empty($result)) {
            return [];
        }

        return (array)$result;
    }

    /**
     * Return all Field Names from current Row
     * @return array
     */
    public function getFieldNames()
    {
        return array_keys($this->row);
    }

    /**
     * Set a string value to existing field name
     * @param string $name
     * @param string $value
     */
    public function set($name, $value)
    {
        $name = $this->getHydratedFieldName($name);

        if (!array_key_exists($name, $this->row)) {
            $this->addField($name, $value);
        } else {
            $this->row[$name] = $value;
        }
        $this->informChanges();
    }

    /**
     * Remove specified field name from row.
     *
     * @param string $fieldName
     */
    public function removeField($fieldName)
    {
        $fieldName = $this->getHydratedFieldName($fieldName);

        if (array_key_exists($fieldName, $this->row)) {
            unset($this->row[$fieldName]);
            $this->informChanges();
        }
    }

    /**
     * Remove specified field name with specified value name from row.
     *
     * @param string $fieldName
     * @param $value
     */
    public function removeValue($fieldName, $value)
    {
        $fieldName = $this->getHydratedFieldName($fieldName);

        $result = $this->row[$fieldName];
        if (!is_array($result)) {
            if ($value == $result) {
                unset($this->row[$fieldName]);
                $this->informChanges();
            }
        } else {
            $qty = count($result);
            for ($i = 0; $i < $qty; $i++) {
                if ($result[$i] == $value) {
                    unset($result[$i]);
                    $this->informChanges();
                }
            }
            $this->row[$fieldName] = array_values($result);
        }
    }

    /**
     * Update a specific field and specific value with new value
     *
     * @param String $fieldName
     * @param String $oldvalue
     * @param String $newvalue
     */
    public function replaceValue($fieldName, $oldvalue, $newvalue)
    {
        $fieldName = $this->getHydratedFieldName($fieldName);

        $result = $this->row[$fieldName];
        if (!is_array($result)) {
            if ($oldvalue == $result) {
                $this->row[$fieldName] = $newvalue;
                $this->informChanges();
            }
        } else {
            for ($i = count($result) - 1; $i >= 0; $i--) {
                if ($result[$i] == $oldvalue) {
                    $this->row[$fieldName][$i] = $newvalue;
                    $this->informChanges();
                }
            }
        }
    }

    public function toArray()
    {
        return $this->row;
    }

    /**
     * @return array
     */
    public function getAsRaw()
    {
        return $this->originalRow;
    }

    /**
     *
     * @return bool
     */
    public function hasChanges()
    {
        return ($this->row != $this->originalRow);
    }

    /**
     *
     */
    public function acceptChanges()
    {
        $this->originalRow = $this->row;
    }

    /**
     *
     */
    public function rejectChanges()
    {
        $this->row = $this->originalRow;
    }

    protected function informChanges()
    {
        $this->node = null;
    }

    /**
     * Override Specific implementation of setPropValue to Row
     *
     * @param Row $obj
     * @param string $propName
     * @param string $value
     */
    protected function setPropValue($obj, $propName, $value)
    {
        $obj->set($propName, $value);
    }

    /**
     * @return bool
     */
    public function fieldExists($name)
    {
        return isset($this->row[$this->getHydratedFieldName($name)]);
    }

    /**
     * @return void
     */
    public function enableFieldNameCaseInSensitive() 
    {
        $this->row = array_change_key_case($this->row, CASE_LOWER);
        $this->originalRow = array_change_key_case($this->originalRow, CASE_LOWER);
        $this->fieldNameCaseSensitive = false;
    }

    /**
     * @return bool
     */
    public function isFieldNameCaseSensitive()
    {
        return $this->fieldNameCaseSensitive;
    }

    /**
     * @params name Fieldname
     * @return string
     */
    protected function getHydratedFieldName($name)
    {
        if (!$this->isFieldNameCaseSensitive()) {
            return strtolower($name);
        }

        return $name;
    }
}
