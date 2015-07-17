<?php

namespace JanSuchanek\DataGrid\Components\DataGrid;

use JanSuchanek\DataGrid\Components\VisualPaginator\VisualPaginatorControlFactoryInterface;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Bridges\ApplicationLatte\Template;
use JanSuchanek\DataGrid\Components\DataGrid\Item\TextItem;
use Nette\Forms\Controls\SubmitButton;

/**
 * @property-read Template $template
 */
class DataGridControl extends Control
{
    const DEFAULT_VIEW = 'default';
    const DEFAULT_PRIMARY = 'id';

    /* @var VisualPaginatorControlFactoryInterface $visualPaginatorFactoryInterface */
    protected $visualPaginatorFactoryInterface;


    protected $primary;
    protected $view;
    protected $limits = [20];
    protected $massActions = [];
    protected $cols = [];
    protected $items = [];

    /** @persistent */
    public $order;

    /** @persistent */
    public $sort;

    /** @persistent */
    public $query = [];

    /** @persistent */
    public $id;

    public $onFindItems = [];
    public $onQueryProcess = [];
    public $onCancelProcess = [];
    public $onSaveProcess = [];
    public $onSubmitProcess = [];
    public $onInlineEdit = [];

    public function __construct(VisualPaginatorControlFactoryInterface $visualPaginatorFactoryInterface)
    {
        $this->visualPaginatorFactoryInterface = $visualPaginatorFactoryInterface;
    }

    public function getVisualPaginator()
    {
        return $this->getComponent("vp");
    }

    public function createComponentListForm()
    {


        $form = new Form;
        $form->addSelect("select", "Výběr akce", $this->getMassActions());
        $primary = $this->getPrimary();

        $query = $this->getQuery();
        $where = $this->getWhere();
        $items = $this->getItems();
        $cols = $this->getCols();
        $id = $this->getId();
        $order = $this->getOrder();
        $sort = $this->getSort();



        $this->onFindItems($this);

        $this->onInlineEdit($form, $cols, $items, $id);

        $container = $form->addContainer('items');

        foreach ($items as $item) {
            $container->addCheckbox($item->$primary);
        }

        $container = $form->addContainer('query');
        foreach($cols as $key => $col){
            $input = $container->addText($col->name, $col->name);

            if(isset($query[$key])){
                $input->setDefaultValue($query[$key]);
            }
        }

        $form->addSubmit('cancel', 'Zrušit')->onClick[] = function (SubmitButton $button) {
            $this->query = [];
            $this->id = NULL;
            $this->onCancelProcess();
            $this->redirect('this');
        };

        $form->addSubmit('search', 'Hledej')->onClick[] = function (SubmitButton $button) {
            $this->query = $button->getForm()->getValues()->query;
            $this->onQueryProcess();
            $this->redirect('this');
        };

        $form->addSubmit('save', 'Uložit')->onClick[] = function (SubmitButton $button) {
            $values = $button->getForm()->getHttpData();
            $this->onSaveProcess($values);
            $this->redirect('this');
        };

        $form->addSubmit('submit', 'Prověď')->onClick[] = function (SubmitButton $button) {
            $form = $button->getForm();
            $values = $form->getValues();
            $this->onSubmitProcess($values);
            $this->redirect('this');
        };

        return $form;
    }

    public function handleEdit($id = NULL)
    {
        $this->id = (int)$id;
    }

    public function getItemCount()
    {
        return $this->getComponent("vp")->getPaginator()->getItemCount();
    }

    public function render()
    {
        $this->onFindItems($this);

        $this->template->setParameters(
            [
                'itemsCount' => $this->getItemCount(),
                'cols' => $this->getCols(),
                'items' => $this->getItems(),
                'primary' => $this->getPrimary(),
                'order' => $this->getOrder(),
                'sort' => $this->getSort(),
                'id' => $this->getId(),
            ]
        );

        $this->template->setFile($this->getView());
        $this->template->render();
    }

    protected function createComponentVp()
    {
        return $this->visualPaginatorFactoryInterface->create();
    }


    public function setItems($items)
    {
        $primary = $this->getPrimary();
        foreach($items as $item){
            $this->items[$item->$primary] = $item;
        }
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

    public function setPrimary($col)
    {
        $this->primary = $col;
    }

    public function getPrimary()
    {
        if(!$this->primary){
            return self::DEFAULT_PRIMARY;
        }
        return $this->primary;
    }

    public function getCols()
    {
        return $this->cols;
    }

    public function addItem($type, $col, $label, $selection = [])
    {
        $item = $this->getText($col);
        $item->setType($type);
        $item->setLabel($label);

        if($type == 'select') $item->setSelection($selection);

        if($type == 'text'){
            $item->operator = $col.' ILIKE ?';
            $item->pattern = [NULL, '%'];
        }

        if($type == 'int'){
            $item->operator = $col.' = ?';
            $item->pattern = [NULL, NULL];
        }

        if($type == 'date'){
            $item->operator = $col.' = ?';
            $item->pattern = [NULL, NULL];
        }

        if($type == 'select'){
            $item->operator = $col.' = ?';
            $item->pattern = [NULL, NULL];
        }

        return $item;
    }


    /**
     * @param string $name
     * @return TextItem|NULL
     */
    public function getText($name)
    {
        if(!isset($this->cols[$name])){
            $this->cols[$name] = new TextItem($name);
        }
        return $this->cols[$name];
    }

    /**
     * @param TextItem $text
     * @return TextItem|NULL
     */
    public function setText(TextItem $text)
    {
        $this->cols[$text->name] = $text;
    }

    /**
     * @param $name
     * @return TextItem|NULL
     */
    public function addText($name)
    {
        if(!isset($this->cols[$name])){
            $textItem = new TextItem($name);
            $this->setText($textItem);
        }
        return $this->getText($name);
    }

    public function getView()
    {
        $view = !$this->view ? self::DEFAULT_VIEW : $this->view;
        return __DIR__.'/templates/'.$view.'.latte';
    }

    public function setView($view)
    {
        if(is_file(__DIR__.'/templates/'.$view.'.latte')) {
            $this->view = (string)$view;
        }
    }

    public function setOrder($order)
    {
        $this->order = $order;
    }

    public function getOrder()
    {
        if($this->order && in_array($this->order, $this->cols)) return $this->order;
        return $this->getPrimary();
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

    public function setQuery($query)
    {
        $this->query[$key] = $query;
    }

    public function getQuery()
    {
        return $this->query;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getWhere()
    {
        $where = [];

        foreach($this->getQuery() as $key => $row){

            if($row && $item = $this->getText($key)){
                $type = $item->getType();
                if($type == 'date'){

                    $date = new \DateTime($row);


                    if($date){
                        $where[] = ['to_timestamp('.$key.')' .' >= ?' => $date->format('Y-m-d')];
                        $where[] = ['to_timestamp('.$key.')'. ' < ?' => $date->modify('+1 day')->format('Y-m-d')];
                    }

                } elseif($type == 'int') {
                    $where[] = [
                        $key . $item->operator => (int)$row,
                    ];
                } elseif($type == 'stav') {
                    $where[] = [
                        $key . $item->operator => (int)$row,
                    ];
                } else {
                    $where[] = [
                        $key . $item->operator => $item->pattern[0] . $row .$item->pattern[1],
                    ];
                }

            }
        }

        return $where;
    }
}