<?php

namespace JanSuchanek\DataGrid\Components\DataGrid\Item;

/**
 * Class NumberItem
 * @package TableList
 */
class NumberItem extends Item
{
    public $operator = ' = ?';
    public $pattern = [NULL, NULL];

    /**
     * @var string
     */
    private $prefix = "";

    /**
     * @var string
     */
    private $sufix = "";

    /**
     * @var string
     */
    private $unknown = "";

    /**
     * @var string
     */
    private $col;

    public function __construct($name) {
        parent::__construct($name);
    }

    /**
     * @return string
     */
    public function getUnknown()
    {
        return $this->unknown;
    }

    /**
     * @param string $s
     * @return NumberItem
     */
    public function setUnknown($s)
    {
        $this->unknown = $s;
        return $this;
    }

    /**
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix. " ";
    }

    /**
     * @param string $s
     * @return NumberItem
     */
    public function setPrefix($s)
    {
        $this->prefix = $s;
        return $this;
    }

    /**
     * @param string $s
     * @return NumberItem
     */
    public function addPrefix($s)
    {
        $this->prefix .= " ".$s;
        return $this;
    }

    /**
     * @return string
     */
    public function getSufix()
    {
        return $this->sufix;
    }

    /**
     * @param string $s
     * @return NumberItem
     */
    public function setSufix($s)
    {
        $this->sufix = $s;
        return $this;
    }

    /**
     * @param string $s
     * @return NumberItem
     */
    public function addSufix($s)
    {
        $this->sufix .= " ".$s;
        return $this;
    }

    public function render($data, $control){
        $name = $this->name;
        $r = \Nette\Utils\Html::el('span');
        $r->add($this->prefix);
        if(isset($data->$name)) {
            $r->add($data->$name);
        } else {
            $r->add($this->unknown);
        }
        $r->add($this->sufix);
        return $r;
    }
}
