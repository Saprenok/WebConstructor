<?php

/**
 * Модуль расчета материалов
 * @category Yii
 */
class ExtraMaterialsMC extends AbstractModelCalculation
{
    /**
    * Переменная хранит имя класса
    * 
    * @var string 
    */
    public $nameModule = 'ExtraMaterialsMC';
    private $SheildComplectationCode = array();

    /**
    * Алгоритм
    * 
    * @return bool
    */
    public function Algorithm()
    {
        $complectaction = $this->MaterialsComplectation;
        $complectaction = $this->preparedShieldComplectationData($complectaction, $this->formViewAllMaterials);
        $this->MaterialsComplectation      = $complectaction;
        $complectationCount                = $this->MaterialsComplectationCount;
        $complectationCount                = $this->preparedShieldComplectationDataСount($complectationCount, $this->formViewAllMaterials);
        $this->MaterialsComplectationCount = $complectationCount;
        $this->ExtraMaterialsList          = $this->MaterialsComplectation;
        $this->ExtraMaterialsCount         = count($this->ExtraMaterialsList);

        return true;
    }

    /**
    * Соединяет два массива с данными о комплектации щита
    * 
    * @param array $array
    * @param array $data
    * 
    * @return array Объединенный массив
    */
    private function preparedShieldComplectationData($array, $data)
    {
        if (!is_array($array)) {
            $preparedData = array();
        } else {
            $preparedData = $array;
        }
        $data = $data ? json_decode($data, true) : array();
        foreach ($data as $row) {
            $preparedData[] = (is_numeric($row['code'])) ? (int)$row['code'] : $row['code'];
        }

        return $preparedData;
    }

    /**
    * Соединяет два массива с данными о количестве комплектации щита
    * 
    * @param array $array
    * @param array $data
    * 
    * @return array Объединенный массив
    */
    private function preparedShieldComplectationDataСount($array, $data)
    {
        if (!is_array($array)) {
            $preparedData = array();
        } else {
            $preparedData = $array;
        }
        $data = $data ? json_decode($data, true) : array();
        foreach ($data as $row) {
            $preparedData[(is_numeric($row['code'])) ? (int)$row['code'] : $row['code']] = $row['count'];
        }

        return $preparedData;
    }

    /**
    * Название модуля
    * 
    * @return string
    */
    public function getTitle()
    {
        return 'Расчет дополнительной материалов';
    }
}