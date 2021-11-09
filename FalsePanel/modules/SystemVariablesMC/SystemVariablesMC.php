<?php

/**
 * Системные параметры
 * @example  здесь текст примера использования класса (необязательно)
 * PHP version 5.5
 * @category Yii
 * @author   Charnou Vitaliy <graffov87@gmail.com>
 */
class SystemVariablesMC extends AbstractModelCalculation
{
    /**
     * Переменная хранит имя класса
     * @var string
     */
    public $nameModule = 'SystemVariablesMC';

    /**
     * Алгоритм
     * @return bool
     */
    public function Algorithm()
    {
        return true;
    }

    /**
     * Название модуля
     * @return string
     */
    public function getTitle()
    {
        return 'Системные параметры';
    }
}