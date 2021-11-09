<?php

/**
 * Форма дополнительных материалов
 * PHP version 5.5
 * @category Yii
 * @author   Charnou Vitaliy <graffov87@gmail.com>
 */
class FormExtraMaterialsMC extends AbstractModelCalculation
{
    /**
    * Переменная хранит имя класса
    * 
    * @var string 
    */
    public $nameModule = 'FormExtraMaterialsMC';

    /**
    * Алгоритм
    * 
    * @return bool
    */
    public function Algorithm()
    {
        return true;
    }

    /**
    * Название модуля
    * 
    * @return string
    */
    public function getTitle()
    {
        return 'Форма дополнительных материалов';
    }
}