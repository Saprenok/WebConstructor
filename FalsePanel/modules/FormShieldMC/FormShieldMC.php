<?php

/**
 * Форма Щит
 * PHP version 5.5
 * @category Yii
 * @author   Charnou Vitaliy <graffov87@gmail.com>
 */
class FormShieldMC extends AbstractModelCalculation
{

    /**
     * Переменная хранит имя класса
     * 
     * @var string 
     */
    public $nameModule = 'FormShieldMC';

    /**
     * Алгоритм
     * 
     * @return bool
     */
    public function Algorithm()
    {
        //VariantUmodified определяется на форме Щита, но для него не сделан input. Чтобы значение не затиралось при переходе с формы - добавили следующую строчку
        if (!$this->PanoramicPanel) {
            Yii::app()->container->VariantUmodified = Yii::app()->container->ShieldWholePanels;
        }
			
        $this->ShieldOuterColorType = $this->Colout;
        $this->ShieldInnerColorType = $this->Colin;
        $this->ShieldOuterColor = $this->Colout_n;
        $this->ShieldInnerColor = $this->Colin_n;
        
        //Делаем перевод значений
        $newShieldOuterColorType = $this->ShieldOuterColorType;
        $newShieldInnerColorType = $this->ShieldInnerColorType;
        
        $this->ShieldOuterColorType = Yii::t('steps', $newShieldOuterColorType); 
        $this->ShieldInnerColorType = Yii::t('steps', $newShieldInnerColorType); 

        if (!$this->PanoramicPanel) {
            $this->PanelsDesign = PanelDesignModel::model()->findByPk($this->design)->title;    //  TypePanelsColorShieldMI
            if ($this->PanelsDesign == "Филенка" || $this->PanelsDesign == "Филенка с полосой" || $this->PanelsDesign == "Филенка с переменным шагом") {
                $this->PanelWithInfills = TRUE;
                $this->RegularSandwich = FALSE;
            } else {
                $this->PanelWithInfills = FALSE;
                $this->RegularSandwich = TRUE;
            }
        } else {
            $this->PanelWithInfills = FALSE;
            $this->RegularSandwich = TRUE;
        }

        $this->DontCutOnStrengthening = $this->MinCutsEnabledSelected;
        
        //=======================================================================================================
        
        return true;
    }

    /**
     * Название модуля
     * 
     * @return string
     */
    public function getTitle()
    {
        return 'Форма Щит';
    }

}
