<?php

/**
 * Форма Цвет окантовки
 * PHP version 5.5
 * @category Yii
 * @author   Charnou Vitaliy <graffov87@gmail.com>
 */
class FormColorEdgingMC extends AbstractModelCalculation
{

    /**
     * Название модуля
     * 
     * @var string Название модуля
     */
    public $nameModule = 'FormColorEdgingMC';

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
     * Имя модуля
     * 
     * @return string возвращает имя модуля
     */
    public function getTitle()
    {
        return 'Форма Опции щита';
    }

}
