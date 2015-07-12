<?php

namespace JanSuchanek\DataGrid\Components\DataGrid\Item;

/**
 * Class Item
 * @package TableList
 * @property string $name
 * @property string $class
 * @property string $label
 * @property string $icon
 * @property array $types
 * @property array $onClick
 */
class Item extends \Nette\Object
{
    /**
     * @var string $name
     */
    private $name;

    /**
     * @var string $class
     */
    private $class;

    /**
     * @var string $label
     */
    private $label;

    /**
     * @var string $icon
     */
    private $icon;

    /**
     * @var array $types
     */
    private $types = array();

    /**
     * @var array $onClick
     */
    public $onClick = array();

    /**
     * @param string $name
     */
    public function __construct($name) {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Item
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return string
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param string $class
     * @return Item
     */
    public function setClass($class)
    {
        $this->class = $class;
        return $this;
    }

    /**
     * @param string $class
     * @return Item
     */
    public function addClass($class)
    {
        $this->class .= " ".$class;
        return $this;
    }

    /**
     * @param string $label
     * @return Item
     */
    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @param string $icon
     * @return Item
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;
        return $this;
    }

    /**
     * @param string $name
     * @param string
     */
    public function setType($name, $type)
    {
        $this->types[$name] = $type;
    }

    /**
     * @return array
     */
    public function getTypes()
    {
        return $this->types;
    }


}