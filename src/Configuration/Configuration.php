<?php

namespace JanSuchanek\DataGrid\Configuration;

use DataGrid\Components\DataGrid\Item\TextItem;
use DataGrid\Components\DataGrid\Item\NumberItem;

class Configuration extends \Nette\Object
{
    protected $primary = "id";
    protected $view = "default";
    protected $limits = [20];

	protected $columns = [];
    protected $buttons = [];
    protected $texts = [];
    protected $images = [];
    protected $relateds = [];
    protected $query = [];

    protected $id;
    protected $order;
    protected $sort;    

    protected $massActions = [];

    protected $items = [];

    public function setItems($items)
    {
        $this->items = $items;
    }

    public function getItems()
    {
        return $this->items;
    }

    public function setMassActions(array $massActions)
    {
        $this->massActions = $massActions;
    }

    public function getMassActions()
    {
        return $this->massActions;
    }

    public function setQuery($query)
    {
        $this->query = $query;
    }

    public function getQuery()
    {
        return $this->query;
    }

    public function setId($id)
    {
        $this->id = (int)$id;
    }

    public function getId()
    {
        return (int)$this->id;
    }        

    public function setOrder($order)
    {
        $this->order = $order;
    }

    public function getOrder()
    {
        if($this->order) return $this->order;
        return $this->primary;
    }

    public function setPrimary($col)
    {
        $this->primary = $col;
    }

    public function getPrimary()
    {
        return $this->primary;
    }

    public function setSort($sort)
    {
        $this->sort = $sort;
    }

    public function getSort()
    {
        if($this->sort != '') return 'DESC';
        return;
    }    

    public function addLimit($limit)
    {
        $this->limits[] = (int)$limit;
    }

    public function getView()
    {
        return __DIR__.'/../Components/DataGrid/templates/'.$this->view.'.latte';
    }

    public function setView($view)
    {
    	if(is_file(__DIR__.'/'.$view.'.latte')) {
    		$this->view = (string)$view;	
    	}
    }

    public function getTextItems()
    {
        return $this->texts;
    }

    /**
     * @param string $name
     * @return TextItem|NULL
     */
    public function getText($name)
    {
        if(isset($this->texts[$name])){
            return $this->texts[$name];
        }
        return NULL;
    }

    /**
     * @param TextItem $text
     * @return TextItem|NULL
     */
    public function setText(TextItem $text)
    {
        $this->texts[$text->name] = $text;
    }

    /**
     * @param $name
     * @return TextItem|NULL
     */
    public function addText($name)
    {
        if(!isset($this->texts[$name])){
            $textItem = new TextItem($name);
            $this->setText($textItem);
        }
        return $this->getText($name);
    }

    /**
     * @param string $name
     * @return TextItem|NULL
     */
    public function getNumber($name)
    {
        if(isset($this->texts[$name])){
            return $this->texts[$name];
        }
        return NULL;
    }

    /**
     * @param TextItem $text
     * @return TextItem|NULL
     */
    public function setNumber(NumberItem $text)
    {
        $this->texts[$text->name] = $text;
    }

    /**
     * @param $name
     * @return TextItem|NULL
     */
    public function addNumber($name)
    {
        if(!isset($this->texts[$name])){
            $textItem = new NumberItem($name);
            $this->setNumber($textItem);
        }
        return $this->getNumber($name);
    }
    
    public function getWhere()
    {
        $where = [];

        foreach($this->getQuery() as $key => $row){
            if($row && $item = $this->getText($key)){
                $where[] = [
                    $item->name . ($item->operator?$item->operator: '') => !$item instanceof NumberItem ? ($item->pattern[0] . $row .$item->pattern[1]): (int)$row,
                ];
            }
        }

        return $where;


    }    
}
