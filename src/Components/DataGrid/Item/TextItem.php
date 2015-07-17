<?php

namespace JanSuchanek\DataGrid\Components\DataGrid\Item;

/**
 * Class TextItem
 * @package TableList
 */
class TextItem extends Item
{

    public $operator = ' ILIKE ?';
    public $pattern = [NULL, '%'];

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
     * @return TextItem
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
     * @return TextItem
     */
    public function setPrefix($s)
    {
        $this->prefix = $s;
        return $this;
    }

    /**
     * @param string $s
     * @return TextItem
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
     * @return TextItem
     */
    public function setSufix($s)
    {
        $this->sufix = $s;
        return $this;
    }

    /**
     * @param string $s
     * @return TextItem
     */
    public function addSufix($s)
    {
        $this->sufix .= " ".$s;
        return $this;
    }

    public function render($data){
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
