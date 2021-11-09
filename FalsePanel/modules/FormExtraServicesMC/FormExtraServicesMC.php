<?php

/**
 * Форма дополнительных услуг
 * @example  здесь текст примера использования класса (необязательно)
 * PHP version 5.5
 * @category Yii
 * @author   Charnou Vitaliy <graffov87@gmail.com>
 */
class FormExtraServicesMC extends AbstractModelCalculation
{
    /**
    * Переменная хранит имя класса
    * 
    * @var string 
    */
    public $nameModule = 'FormExtraServicesMC';

    /**
    * Алгоритм
    * 
    * @return bool
    */
    public function Algorithm()
    {
        $this->ExtraServicesList  = $this->ServiceComplectation;
        $this->ExtraServicesCount = count($this->ServiceComplectation);

        return true;
    }

    /**
    * Название модуля
    * 
    * @return string
    */
    public function getTitle()
    {
        return 'Форма дополнительных услуг';
    }
}