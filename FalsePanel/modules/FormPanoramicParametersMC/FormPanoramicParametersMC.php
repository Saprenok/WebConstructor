<?php

/**
 * Форма Параметры панорамных панелей
 * 
 * PHP version 5.5
 * @category Yii
 * @author   Charnou Vitaliy <graffov87@gmail.com>
 */
class FormPanoramicParametersMC extends AbstractModelCalculation
{

    /**
     * Переменная хранит имя класса
     * 
     * @var string 
     */
    public $nameModule = 'FormPanoramicParametersMC';

    /**
     * Алгоритм
     * 
     * @return bool
     */
    public function Algorithm()
    {
        if (Yii::app()->container->PanoramicPanelGlassingType)
            $this->PanGlassingType = Yii::app()->container->PanoramicPanelGlassingType;
        return true;
    }

    /**
     * Название модуля
     * 
     * @return string
     */
    public function getTitle()
    {
        return 'Форма Параметры панорамных панелей';
    }

}
