<?php

/**
 * Генерация технологических операций
 * @example  здесь текст примера использования класса (необязательно)
 * PHP version 5.5
 * @category Yii
 * @author   Charnou Vitaliy <graffov87@gmail.com>
 */
class GenerateTechnologicalOperationsFalsePanel
{

    /**
     * Контейнер
     * @var object
     */
    public $container;

    /**
     * Выполнение алгоритма - генерации технологических операций
     */
    public function Algorihtm()
    {
        $operations = "";
        if (!$this->RegionEurope) {
//            $panels = $this->getLink("Панели", "tag", $this->orderProductId);
//            CVarDumper::dump($panels, 10, TRUE);
//            foreach ($panels as $p) {
//                #CVarDumper::dump($p->nomenclature_id, 10, TRUE);
//                #CVarDumper::dump($p->nomenclature->code, 10, TRUE);
//                CVarDumper::dump($p->nomenclature->code_group, 10, TRUE);
//            }
//            die();
            $panels = $this->getNomenclatureCodeGroup(array('45', '138', 'CB0001834', '99'), $this->orderProductId);
            $count = count($panels);
            if ($count > 0) {
                /** @var $value OrderProductSpecificationModel */
                foreach ($panels as $key => $value) {
                    //  Данный элемент товарной группы является панелью
                    //  Если Element.ProductGroup = ("45" или "138) или (Element.ProductGroup = "CB0001834" и Element.Name начинается с "Панель") или Element.Code = ("CB000151794" или "CB000151795")
                    if (in_array($value->nomenclature->code_group, array(45, 138)) || (in_array($value->nomenclature->code_group, array('CB0001834', '99')) && strpos($value->nomenclature->title_ru, 'Панель') === 0)){
                        $panelOperations = array();
                        
                        $found = false;
                        if (in_array($value->technology_code, array("CB0000024", "CB0000005"))) $found = true;
                        if (!$found) $panelOperations[] = 'CB0000024';
                        
                        $found = false;
                        if (in_array($value->technology_code, array("CB0000117"))) $found = true;
                        if (!$found) $panelOperations[] = 'CB0000117';
                        
                        /*
                        * Щербаков
                        * можем и две операции повесить на элемент
                        */
                        if(!empty($panelOperations)) Yii::app()->db->createCommand()->update('order_product_specification', array('technology_code' => implode(',', $panelOperations), 'technology_count' => count($panelOperations)), 'id=' . $value->id);
                    }
                }
            }
            $operations = "";
            $okant = $this->getTitle("Упаковка.Окантовка", $this->orderProductId);
            $count = count($okant);
            if (is_array($okant) && $count > 0) {
                foreach ($okant as $key => $value) {
                    if (
                        $value->nomenclature_id != 'CB000166587' && 
                        $value->nomenclature_id != '00000013826'
                    ) {
                        $found = false;
                        if (in_array($value->technology_code, array("CB0000043"))) {
                            $found = true;
                        }
                        if (!$found) {
                            $operations = 'CB0000043';
                        } else {
                            $operations = $value->technology_code;
                        }
                        Yii::app()->db->createCommand()->update('order_product_specification', array('technology_code' => $operations, 'technology_count' => 1), 'id=' . $value->id);
                    }
                }
            }
            $operations = "";
            $tube = $this->getTitle("Упаковка.Труба", $this->orderProductId);
            $count = count($tube);
            if (is_array($tube) && $count > 0) {
                foreach ($tube as $key => $value) {
                    if ($value->nomenclature_id != 'CB000166587') {
                        $found = false;
                        if (in_array($value->technology_code, array("CB0000043"))) {
                            $found = true;
                        }
                        if (!$found) {
                            $operations = 'CB0000043';
                        } else {
                            $operations = $value->technology_code;
                        }
                        Yii::app()->db->createCommand()->update('order_product_specification', array('technology_code' => $operations, 'technology_count' => 1), 'id=' . $value->id);
                    }
                }
            }
        } else {
            $panels = $this->getLink("Панели", "tag", $this->orderProductId);
            //$panels = $this->getNomenclatureCodeGroup(array('43', '138', 'CB0001834'), $this->orderProductId);
            $count = count($panels);
            if (is_array($panels) && $count > 0) {
                foreach ($panels as $key => $value) {
                    $found = false;
                    if (in_array($value->technology_code, array("CB0000005"))) {
                        $found = true;
                    }
                    if (!$found) {
                        $operations = 'CB0000005';
                    } else {
                        $operations = $value->technology_code;
                    }
                    Yii::app()->db->createCommand()->update('order_product_specification', array('technology_code' => $operations, 'technology_count' => 1), 'id=' . $value->id);
                }
            }
            $operations = "";
            //$corob = $this->getTitle("Упаковка.Коробка", $this->orderProductId);
            $corob = $this->getTitleWithoutLink("Упаковка.Коробка", $this->orderProductId);
            $count = count($corob);
            if (is_array($corob) && $count > 0) {
                foreach ($corob as $key => $value) {
                    //if ($value->nomenclature_id != 'CB000166587') {
                        $found = false;
                        if (in_array($value->technology_code, array("CB0000014"))) {
                            $found = true;
                        }
                        if (!$found) {
                            $operations = 'CB0000014';
                        } else {
                            $operations = $value->technology_code;
                        }
                        Yii::app()->db->createCommand()->update('order_product_specification', array('technology_code' => $operations, 'technology_count' => 1), 'id=' . $value->id);
                    //}
                }
            }
        }
    }

//    /**
//     * Получить элементы по полю из таблицы(ярлык, группа ...)
//     *
//     * @param $link
//     *
//     * @return array
//     */
//    public function getLink($link)
//    {
//        $model = new OrderProductSpecificationModel();
//        $dataReader = Yii::app()->db->createCommand()
//                ->select('t.*')
//                ->from($model->tableName() . ' t')
//                ->where('tag="' . $link . '" and order_product_id=' . $this->container['orderProductId'])
//                ->query();
//        //        ->readObject('SpecificationItemModel', $this->getAttributes())
//        $items = array();
//        while ($item = $dataReader->readObject('OrderProductSpecificationModel', $model->getAttributes())) {
//            $items[] = $item;
//        }
//
//        return $items;
//    }
//
//    /**
//     * Получить записи по названию специфкиации
//     *
//     * @param $link
//     *
//     * @return mixed
//     */
//    public function getTitle($link)
//    {
//        $model = new OrderProductSpecificationModel();
//        $dataReader = Yii::app()->db->createCommand()->select('t.*,specification.title')->from($model->tableName() . ' t')->leftJoin('specification', '`specification`.`id`=`t`.`specification_id`')->where('`specification`.`title`="' . $link . '" and order_product_id=' . $this->container['orderProductId'])->query();
//        //        ->readObject('SpecificationItemModel', $this->getAttributes())
//        $items = array();
//        while ($item = $dataReader->readObject('OrderProductSpecificationModel', $model->getAttributes())) {
//            $items[] = $item;
//        }
//
//        return $item;
//    }

    /**
     * Геттер контейнера
     *
     * @param $name
     *
     * @return null
     */
    public function __get($name)
    {
        if (isset($this->container[$name])) {
            return $this->container[$name];
        } else {
            return null;
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
     * поиск в таблице элементов по полю
     *
     * @param $link
     * @param $field
     * @param $orderProductId
     *
     * @return array
     */
    public function getLink($link, $field, $orderProductId)
    {
        if ($field == "tag") {
            $where = "ops.{$field} LIKE '%" . $link . "%'";
        } else {
            $where = "ops.{$field}='" . $link . "'";
        }
        $dataReader = Yii::app()->db->createCommand()
                ->select('ops.*')
                ->from('order_product_specification ops')
                ->where('ops.is_deleted=0 AND ops.order_product_id=' . $orderProductId . ' AND ' . $where)
                ->query();
        $items = array();
        while ($item = $dataReader->readObject('OrderProductSpecificationModel', OrderProductSpecificationModel::model()->getAttributes())) {
            if ($item->amount > 0) {
                if ($field == "tag") {
                    $tags = array_map('trim', explode(',', $item->tag));
                    if (in_array($link, $tags)) {
                        $items[] = $item;
                    }
                } else {
                    $items[] = $item;
                }
            }
        }

        return $items;
    }

    /**
     * Поиск элементов по названию спецификации
     *
     * @param $link
     * @param $orderProductId
     *
     * @return array
     */
    public function getTitle($link, $orderProductId)
    {
        $dataReader = Yii::app()->db->createCommand()
                ->select('ops.*')
                ->from('order_product_specification ops')
//              ->leftJoin("specification spec", "spec.id = ops.specification_id")
//              Предположительно должно быть так, ибо панели невыбирались потому что они были в Упаковка.Щит-Панели
                ->leftJoin("specification spec", "(spec.id = ops.specification_id) OR (spec.id = findSpecificationRootId(ops.specification_id))")
                ->where('ops.is_deleted=0 AND spec.title="' . $link . '" and ops.order_product_id=' . $orderProductId)
                ->query();
        $items = array();
        while ($item = $dataReader->readObject('OrderProductSpecificationModel', OrderProductSpecificationModel::model()->getAttributes())) {
            $items[] = $item;
        }

        return $items;
    }
    public function getTitleWithoutLink($link, $orderProductId)
    {
        $field = "tag";
        $linkTag = "МатериалУпаковки";
        $where = "ops.{$field} NOT LIKE '%" . $linkTag . "%'";
        
        $dataReader = Yii::app()->db->createCommand()
                ->select('ops.*')
                ->from('order_product_specification ops')
//              ->leftJoin("specification spec", "spec.id = ops.specification_id")
//              Предположительно должно быть так, ибо панели невыбирались потому что они были в Упаковка.Щит-Панели
                ->leftJoin("specification spec", "(spec.id = ops.specification_id) OR (spec.id = findSpecificationRootId(ops.specification_id))")
                ->where('ops.is_deleted=0 AND
                         spec.title="' . $link . '" AND
                         ops.order_product_id=' . $orderProductId . ' AND
                         (ops.tag IS NULL OR ops.tag NOT LIKE \'%'. $linkTag .'%\')'
                )->query();
        $items = array();
        while ($item = $dataReader->readObject('OrderProductSpecificationModel', OrderProductSpecificationModel::model()->getAttributes())) {
            $items[] = $item;
        }

        return $items;
    }

}
