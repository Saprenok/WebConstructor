<?php

/**
 * Выбор профиля
 * PHP version 5.5
 * @category Yii
 * @author   Charnou Vitaliy <graffov87@gmail.com>
 */
class ProfileSelectionMC extends AbstractModelCalculation
{

    /**
     * Название модуля
     * 
     * @var string Название модуля
     */
    public $nameModule = 'ProfileSelectionMC';

    /**
     * Алгоритм
     * 
     * @return bool
     */
    public function Algorithm()
    {
        $this->InitialShieldTopProfile = 0;
        $this->InitialShieldBottomProfile = 0;
        return true;
    }

    /**
     * Имя модуля
     * 
     * @return string возвращает имя модуля
     */
    public function getTitle()
    {
        return 'Выбор профиля';
    }

}
