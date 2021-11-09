<?php

/**
 * Пост-проверка правильности раскроя
 * PHP version 5.5
 * @category Yii
 * @author   Charnou Vitaliy <graffov87@gmail.com>
 */
class CuttingPostCheckMC extends AbstractModelCalculation
{

    /**
    * Название модуля
    * 
    * @return string
    */
    public $nameModule = 'CuttingPostCheckMC';

    /**
    * Алгоритм
    * 
    * @return bool
    */
    public function Algorithm()
    {
        $RealMin = $this->ShieldMinBottom - 350;
        
        if ($this->ShieldBottomPanel < $RealMin) {
            $this->PostCheckResult = Yii::app()->container->Loc('951', array('%s1'=>$RealMin));
        } else {
            $this->PostCheckResult = "";
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
        return 'Пост-проверка правильности раскроя';
    }
}