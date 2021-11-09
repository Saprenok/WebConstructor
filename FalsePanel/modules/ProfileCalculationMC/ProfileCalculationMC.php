<?php

/**
 * Расчет профилей
 * PHP version 5.5
 * @category Yii
 * @author   Charnou Vitaliy <graffov87@gmail.com>
 */
class ProfileCalculationMC extends AbstractModelCalculation
{
    /**
    * Переменная хранит имя класса
    * 
    * @var string 
    */
    public $nameModule = 'ProfileCalculationMC';

    /**
    * Алгоритм
    * 
    * @return bool
    */
    public function Algorithm()
    {
        if (
            $this->TypeF == "Алюминиевый" || 
            $this->TypeF == "Для секционных ворот"
        ) {
            $this->ShieldTopProfileSize = 2;
            $this->DrawingTopProfileSize = 2;
            $this->ShieldBottomProfileSize = 2;
            $this->DrawingBottomProfileSize = 2;
        }
        return true;
    }

    /**
    * Название модуля
    * 
    * @return string
    */
    public function getTitle()
    {
        return 'Расчет профилей';
    }
}