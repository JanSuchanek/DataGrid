<?php

namespace JanSuchanek\DataGrid\Components\DataGrid;

use JanSuchanek\DataGrid\Components\VisualPaginator\VisualPaginatorControlFactoryInterface;
use JanSuchanek\DataGrid\Configuration\Configuration;
use Nette\Application\UI;
use Nette\Bridges\ApplicationLatte\Template;

/**
 * @property-read Template $template
 */
class DataGridControl extends UI\Control
{
    protected $configuration;

    protected $visualPaginator;

    /** @persistent */
    public $order;

    /** @persistent */
    public $sort;

    /** @persistent */
    public $query = [];

    public $id;    

    public function __construct(VisualPaginatorControlFactoryInterface $visualPaginator, Configuration $configuration)
    {
        $this->visualPaginator = $visualPaginator;
        $this->configuration = $configuration;
    }

    public function getConfiguration()
    {
        return $this->configuration;
    }

    public function setItems($items)
    {
        $this->items = $items;
    }

    public function render()
    {        

        $this->template->itemsCount = $this["vp"]->getPaginator()->itemCount;

        $this->template->setParameters(
            [
                'query' => $this->query,
                'items' => $this->configuration->getItems(),
                'primary' => $this->configuration->getPrimary(),
                'textItems' => $this->configuration->getTextItems(),
                'order' => $this->configuration->getOrder(),
                'sort' => $this->configuration->getSort(),
                'massActions' => $this->configuration->getMassActions(),
                'config' => $this->configuration,
            ]
        );

        $this->template->setFile($this->configuration->getView());
        $this->template->render();       
    }

    public function handleEdit($id = 0)
    {
        $this->id = $id;
        $this->configuration->setId($id);
        //$this->redirect("this");
    }

    /**
     * Loads state informations.
     * @param  array
     * @return void
     */
    public function loadState(array $params)
    {
        parent::loadState($params);

        $this->configuration->setId($this->id);
        $this->configuration->setQuery($this->query);
        $this->configuration->setOrder($this->order);
        $this->configuration->setSort($this->sort);
    }

    /**
     * Save params
     * @param  array
     * @return void
     */
    public function saveState(array & $params)
    {
        if(isset($params->query)){
            die;
        }
        if(isset($params["query"])){
            $query = [];
            foreach($params["query"] as $key => $row){
                if(!empty($row)) $query[$key] = $row;
            }
            $params["query"] = $query; 
            dump($query);
            die;
        }

        parent::saveState($params);
    }        

    public function getVisualPaginator()
    {
        return $this->getComponent("vp");
    }

    public function createComponentListForm()
    {
        $form = new UI\Form;
        $form->addSubmit('cancel', 'Zrušit')->onClick[] = function (\Nette\Forms\Controls\SubmitButton $button) {
            $this->query = [];
            //$this->config->query = 
            $this->redirect("this");
        };

        $form->addSubmit('save', 'Uložit')->onClick[] = function (\Nette\Forms\Controls\SubmitButton $button) {
            $form = $button->getForm();
            $values = $form->getHttpData();
            $this->redirect("this", ['id' => $values['item']['obid']]);
        };        

        $form->addSubmit('search', 'Odeslat')->onClick[] = function (\Nette\Forms\Controls\SubmitButton $button) {
            $form = $button->getForm();
            $values = $form->getHttpData();
            $this->query = $values['query'];
            $this->configuration->setQuery($this->query);
            //$this->redirect("this");
        };

        $form->addSubmit('submit', 'Odeslat')->onClick[] = function (\Nette\Forms\Controls\SubmitButton $button) {
            $form = $button->getForm();
            $select = $form->getHttpData($form::DATA_LINE, 'select');
            $values = $form->getHttpData($form::DATA_LINE, 'items[]');
            dump($select);
            dump($values);
            die;
        };
        return $form;
    }

    protected function createComponentVp()
    {
        return $this->visualPaginator->create();
    }
}