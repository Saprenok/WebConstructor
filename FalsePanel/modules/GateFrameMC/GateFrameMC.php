<?php

/**
 * Модуль расчета Проем
 * PHP version 5.5
 * @category Yii
 * @author   Charnou Vitaliy <graffov87@gmail.com>
 */
class GateFrameMC extends AbstractModelCalculation
{

    /**
     * Название модуля
     *
     * @var string Название модуля
     */
    public $nameModule = 'GateFrameMC';

    /**
     * Алгоритм
     *
     * @return bool
     */
    public function Algorithm()
    {
        $this->GiveOnlyMediumCaps = 1;
        $this->ExtremelyLow = 0;

        $ShieldPanelType = $this->ShieldPanelType;

        return true;
    }

    /**
     * Имя модуля
     *
     * @return string возвращает имя модуля
     */
    public function getTitle()
    {
        return 'Проем';
    }

}
