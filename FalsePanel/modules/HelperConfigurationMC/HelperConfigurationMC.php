<?php

/**
 * Конфигурация помощника
 * @example  здесь текст примера использования класса (необязательно)
 * PHP version 5.5
 * @category Yii
 * @author   Charnou Vitaliy <graffov87@gmail.com>
 */
class HelperConfigurationMC extends AbstractModelCalculation
{
    /**
     * Название модуля
     *
     * @var string Название модуля
     */
    public $nameModule = 'HelperConfigurationMC';

    private $order = null;

    /**
     * Алгоритм
     *
     * @return bool
     */
    public function Algorithm()
    {
        $this->setOrderInfo();

        $orderModel = $this->order->with('region');
        $orderRegionModel = $orderModel->region;

        $this->Change14020 = 0;
        $this->BreakingAngle = 45;
        $this->Retail = $this->InstallationInOrder;
        $this->Region = $orderRegionModel->title;
        if ($this->Region == "Москва") {
            $this->RegionMoscow = 1;
        } else {
            $this->RegionMoscow = 0;
        }
        if ($this->Region == "Kadan") {
            $this->RegionEurope = 1;
        } else {
            $this->RegionEurope = 0;
        }
        if ($this->Region == "Suzhou") {
            $this->RegionChina = 1;
        } else {
            $this->RegionChina = 0;
        }
        if (($this->RegionMoscow == 0) && ($this->RegionEurope == 0) && ($this->RegionChina) == 0) {
            $this->RegionRussiaAndCIS = 1;
        } else {
            $this->RegionRussiaAndCIS = 0;
        }

        $Estimate = array();
        if (Yii::app()->container->checkParam("options")) {
            foreach (Yii::app()->container->options as $key => $value) {
                $Estimate[$key] = array("count" => $value);
            }
            $this->Estimate = $Estimate;
            $Codes = array();
            foreach ($this->Estimate as $key => $value) {
                $Codes[] = $key;
            }

            if ($this->isNewProduct == 1 && !$this->isFormVisited()) {
                $this->RubberInstalled = 0;
                $this->formGateFrameSealing = 0;
                if (in_array("137446", $Codes)) {
                    $this->formGateFrameSealing = 1;
                }
                if (in_array("120844", $Codes) || in_array("CB000252505", $Codes)) {
                    $this->RubberInstalled = 1;
                }
            }
        }
        //конец блока инициализации

        if (is_string(Yii::app()->container->orderProductId) || is_numeric(Yii::app()->container->orderProductId)) {
            $model = OrderProductModel::model()->findByPk(Yii::app()->container->orderProductId);
            $this->ConstructionNumber = $model->position;
        } else {
            $dataReader = Yii::app()->db->createCommand()->select('MAX(t.position) maximal')->from('order_product t')->where('t.order_id=' . Yii::app()->container->orderId)->query();
            $groups = $dataReader->readAll();
            $this->ConstructionNumber = $groups[0]['maximal'] + 1;
        }

        if (isset($this->order->customer->language)) {
            switch ($this->order->customer->language) {
                case "c8b7a452-5835-4bfa-89e3-1beb7332ef3d":
                    $this->CustomerLanguage = 2;
                    break;
                case "66a04654-5e4a-47b9-99e6-fac5547a24f7":
                    $this->CustomerLanguage = 4;
                    break;
                case "5208288c-5978-11e2-be0f-18a9053f8f18":
                    $this->CustomerLanguage = 8;
                    break;
                case "3bcab96a-97a3-49cf-984b-04be1718d372":
                    $this->CustomerLanguage = 9;
                    break;
                default:
                    $this->CustomerLanguage = -1;
                    break;
            }
        } else {
            $this->CustomerLanguage = -1;
        }

        //вычисление необходимости проверять остатки пружин для модуля SpringsCalculationMC
        if ($this->order && $this->order->guid) {       //$order->guid != null -> заказ из 1С
            $WarehouseID = $this->order->warehouse_id;
            //Yii::app()->user->setState('warehouseId',$WarehouseID);
        } else {
            $WarehouseID = null;
        }

        if ($WarehouseID != null) {
            $this->RemainsCheckingEnabled = 1;
        } else {
            $this->RemainsCheckingEnabled = 0;
        }
        $this->PanelsExtensionAllowed = 1;

        $this->StorageCode = $orderRegionModel->storage_code;
    }

    /**
     * Имя модуля
     *
     * @return string возвращает имя модуля
     */
    public function getTitle()
    {
        return 'Конфигурация помощника';
    }

    /**
     * Метод извлекает информацию о заказе из БД
     *
     */
    private function setOrderInfo()
    {
        $orderId = Yii::app()->container->orderId;
        // $mode = Yii::app()->container->mode;
        $order = $this->order = OrderModel::model()->with('customer')->findByPk($orderId);
        if ($order) {
            $this->CustomerInOrder = (string) ((!empty($order->customer_id)) ? $order->customer->name : '');
            $this->CustomerLanguage = (string) ((!empty($order->customer->language)) ? $order->customer->language : 0);
            //тип должен быть строка, иначе: 'string'  == 0  -> true
            $this->InstallationInOrder = (string) ((!empty($order->with_installation)) ? $order->with_installation : 0);
            //Если значение false, то галочка "Размещение панелей в другой регион" должна быть в true
            if (Yii::app()->container->autoCalc) {
                $this->LocProductionPanels = 1;
            } else {
                $this->LocProductionPanels = (string) ((!empty($order->loc_production_panels)) ? $order->loc_production_panels : 0);
            }
            
            $orderTypes = $order->getOrderTypes();
            if (isset($orderTypes[$order->type])) {
                $this->OrderType = $orderTypes[$order->type];
            }
            $this->OrderNumber = $orderNumber = (!empty($order->number)) ? $order->number : 0;
            //убрать нули из номера заказа
            if ($orderNumber !== 0 && $orderNumber[0] === '0') {
                $this->OrderNumberTrimmed = (int) $orderNumber;
            } else {
                $this->OrderNumberTrimmed = $orderNumber;
            }
            $this->OrderYear = (!empty($order->year)) ? $order->year : 0;
            $this->InstallationDate = (!empty($order->installationDate)) ? $order->installationDate : 0;
            $this->MeasurementDate = (!empty($order->measurementDate)) ? $order->measurementDate : 0;

            //вычисление создается ли изделие или редактируется
            if(Yii::app()->container->orderProductId instanceof ForNull)
                $checkOPSExist = [];
            else
                $checkOPSExist = Yii::app()->db->createCommand('SELECT id FROM order_product_specification WHERE order_product_id = '. Yii::app()->container->orderProductId .' LIMIT 1')->queryAll();
            $this->isNewProduct = empty($checkOPSExist) ? 1 : 0;
            /*if ($mode == "read" && !$order->calculationDate || $mode == "add") {
                $this->isNewProduct = 1;
            }*/

            $this->CustomerCountry = "";
            $this->CustomerCountryID = "";
            $this->CustomerCountryFromID = "";
            $customer_country = $order->customer->customer_country;
            $customer_country_ID = $order->customer->customer_country_ID;
            if (!empty($customer_country)) {
                $this->CustomerCountry = $customer_country;
            }
            if (!empty($customer_country_ID)) {
                $this->CustomerCountryID = $customer_country_ID;
                if ($this->CustomerCountryID == "040") $this->CustomerCountryFromID = "Австрия";
                if ($this->CustomerCountryID == "276") $this->CustomerCountryFromID = "Германия";
                if ($this->CustomerCountryID == "756") $this->CustomerCountryFromID = "Швейцария";
            }
        }
    }

    /**
     * Проверяет, была ли посещена форма "Проем"
     *
     * @return bool
     */
    public function isFormVisited()
    {
        $arr = Yii::app()->container->visitedFormsList;
        return is_array($arr) && isset($arr['GateFrameMI']);
    }

}
