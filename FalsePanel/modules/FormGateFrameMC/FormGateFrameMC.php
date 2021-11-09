<?php

/**
 * Форма Проем
 * PHP version 5.5
 * @category Yii
 * @author   Charnou Vitaliy <graffov87@gmail.com>
 */
class FormGateFrameMC extends AbstractModelCalculation
{

    /**
     * Название модуля
     *
     * @var string Название модуля
     */
    public $nameModule = 'FormGateFrameMC';

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
        return 'Форма Проем';
    }

  

}
