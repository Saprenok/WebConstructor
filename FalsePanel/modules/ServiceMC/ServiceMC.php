<?php

/**
 * Услуги
 * @example  здесь текст примера использования класса (необязательно)
 * PHP version 5.5
 * @category Yii
 * @author   Charnou Vitaliy <graffov87@gmail.com>
 */
class ServiceMC extends AbstractModelCalculation
{
    /**
    * Переменная хранит имя класса
    * 
    * @var string 
    */
    public $nameModule = 'ServiceMC';
    
    /**
    * Коды комплектации
    * 
    * @var array
    */
    private $SheildComplectationCode = array();

    /**
    * Алгоритм
    * 
    * @return bool
    */
    public function Algorithm()
    {
        $complectaction                  = $this->ServiceComplectation;
        $complectaction = $this->preparedShieldComplectationData($complectaction, $this->formViewAllService);
        $this->ServiceComplectation      = $complectaction;
        $complectationCount              = $this->ServiceComplectationCount;
        $complectationCount = $this->preparedShieldComplectationDataСount($complectationCount, $this->formViewAllService);
        $this->ServiceComplectationCount = $complectationCount;

        return true;
    }

    /**
    * Соединяет два массива с данными об услугах
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
            $preparedData[] = $row['code'];
        }

        return $preparedData;
    }

    /**
    * Соединяет два массива с данными о количестве услуг
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
            $preparedData[$row['code']] = $row['count'];
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
        return 'Расчет услугов';
    }
}