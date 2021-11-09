<?php

/**
 * Переменные (вызываются последними, после всех операций, почему то 0_o)
 * PHP version 5.5
 * @category Yii
 * @author   Shcherbakov Pavel <pavel24071988@gmail.com>
 */

/**
 * Class SpecificationVariables
 */
class SpecificationVariablesFalsePanel
{
    private $order = null;
    
    public function Algorihtm()
    {
        $this->res = array();
        // Установлена калитка
        /*$this->setWicket();
        // Устанавливаем число панелей
        $this->setSand();*/
        return $this->res;
    }
    /**
     * sect_priority

     */
    private function setWicket()
    {
        $orderId = Yii::app()->container->orderId;
        $order = OrderModel::model()->findByPk($orderId);
        //заказ из 1С
        if ($order && $order->guid) {
            $typesOperationsOrder = $order->typesOperationsOrder;
        } else {
            $typesOperationsOrder = null;
        }
        
        //WicketInstalled - значение из расчета изделия
        //typesOperationsOrder - параметр из заказа покупателя ВидОперации->EnumRef.ВидыОперацийЗаказПокупателя
        if ($typesOperationsOrder == "ЗаказНаРемонт") {
            $this->res['sect_priority'] = 1;
        }
    }
     /**
     * Назначит элемент  spec_sect_sand_count
     */
    private function setSand()
    {
        $panels = $this->getNomenclatureCodeGroup(array('45', '138', 'CB0001834', '99'), $this->orderProductId);
        $count = count($panels);
        //Элемент существует
        if (!empty($count)) {
            //	Добавить элемент "spec_sect_sand_count=" + СТРОКА(Count) в массив VariablesList
            $this->res['spec_sect_sand_count'] = (string)$count;
        }
    }
    
    /**
     * 
     * @param array $codes
     * @param int $orderProductId
     */
    public function getNomenclatureCodeGroup($codes, $orderProductId)
    {
        $dataReader = Yii::app()->db->createCommand()
                ->select('ops.*')
                ->from('order_product_specification ops')
                ->join('nomenclature n', ' n.code=ops.nomenclature_id')
                ->where("ops.is_deleted=0 AND ops.order_product_id=" . $orderProductId . " AND n.code_group IN ('" . implode("','", $codes) . "')")
                ->query();
        $items = array();
        while ($item = $dataReader->readObject('OrderProductSpecificationModel', OrderProductSpecificationModel::model()->getAttributes())) {
            $items[] = $item;
        }

        return $items;
    }
    
    /**
     * Поиск элементов по группе
     *
     * @param $link
     *
     * @return array OrderProductSpecificationModel[]
     */
    public function getGroup($link)
    {
        $Specification = SpecificationModel::model()->findAll('product_id=' . $this->container['productId']);
        $res = array();
        /** @var OrderProductSpecificationModel $value */
        foreach ($Specification as $value) {
            if (!empty($value->group_name) && $value->group_name == $link) {
                $res[] = $value;
            }
        }

        return $res;
    }
}