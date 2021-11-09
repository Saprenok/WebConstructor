<?php

/**
 * Модуль расчета цвета Щита
 * PHP version 5.5
 * @category Yii
 * @author   Charnou Vitaliy <graffov87@gmail.com>
 */
class ShieldColorMC extends AbstractModelCalculation
{
    /**
    * Переменная хранит имя класса
    * 
    * @var string 
    */
    public $nameModule = 'ShieldColorMC';

    /**
    * Алгоритм
    * 
    * @return bool
    */
    public function Algorithm()
    {
        if (!Yii::app()->container->PanoramicPanel) {
            $PanelOuterColor = ColorsModel::model()->findByPk($this->colorOutside);
            $PanelInnerColor = ColorsModel::model()->findByPk($this->colorInside);

            if ($this->isColorDefault){
                $this->Pokout = 0;
                $this->Pokin = 0;
                if (strpos($PanelOuterColor->color, "RAL") === 0) {
                    $this->Colout = "По RAL";
                    $this->Colout_n = substr($PanelOuterColor->color, 3);
                } else {
                    $this->Colout = $PanelOuterColor->color;
                    $this->Colout_n = "";
                }
                if (strpos($PanelInnerColor->color, "RAL") === 0) {
                    $this->Colin = "По RAL";
                    $this->Colin_n = substr($PanelInnerColor->color, 3);
                } else {
                    $this->Colin = $PanelInnerColor->color;
                    $this->Colin_n = "";
                }
            } else {
                if (($this->Colout == "По RAL" && "RAL{$this->Colout_n}" == $PanelOuterColor->color) || $this->Colout == $PanelOuterColor->color) {
                    $this->Pokout = 0;
                } else {
                    $this->Pokout = 1;
                }            
                if (($this->Colin == "По RAL" && "RAL{$this->Colin_n}" == $PanelInnerColor->color) || $this->Colin == $PanelInnerColor->color) {
                    $this->Pokin = 0;
                } else {
                    $this->Pokin = 1;
                }
            }
        }
        
        $this->ShieldOuterColor = $this->Colout_n;
        $this->ShieldInnerColor = $this->Colin_n;
        $this->ShieldOuterPaintingRequired = $this->Pokout;
        $this->ShieldInnerPaintingRequired = $this->Pokin;
        $this->ShieldOuterColorType = $this->Colout;
        $this->ShieldInnerColorType = $this->Colin;

        $this->ShieldColorsAsString = ($this->ShieldOuterColor ? $this->ShieldOuterColorType . " " . $this->ShieldOuterColor : $this->ShieldOuterColorType) . "/" . ($this->ShieldInnerColor ? $this->ShieldInnerColorType . " " . $this->ShieldInnerColor : $this->ShieldInnerColorType);

        $this->ShieldColorsAsString = preg_replace('/По /', '', $this->ShieldColorsAsString);
        
        //Цвет панели
        if ($this->RegionEurope && $this->PanoramicPanel) {
            $this->ShieldPanelColor = "9006";
        } else {
            if (in_array($this->ShieldOuterColor, array(3005, 5005, 6005, 8014, 9003, 9006, 1000, 1014))) {
                if (!$this->RegionMoscow) {
                    $this->ShieldPanelColor = $this->ShieldOuterColor;
                } else {
                    if ((Yii::app()->container->PanoramicPanel && in_array($this->ShieldOuterColor, array(9003, 8014, 5005, 6005, 3005, 1000, 9006, 1014, 9010))) || in_array($this->ShieldOuterColor, array(9003, 9006))) {
                        $this->ShieldPanelColor = $this->ShieldOuterColor;
                    } else {
                        $this->ShieldPanelColor = "неокрашенный";
                    }
                }
            } else {
                if ($this->ShieldOuterColorType == "белый") {
                    $this->ShieldPanelColor = "9003";
                } elseif ($this->ShieldOuterColorType == "коричневый") {
                    $this->ShieldPanelColor = "8014";
                } else {
                    $this->ShieldPanelColor = "неокрашенный";
                }
            }
        }
        
        if (is_numeric($this->ShieldOuterColor) && $this->ShieldOuterColor !== "0") {
            $this->FinalOuterColor = $this->ShieldOuterColor;
        } else {
            if ($this->ShieldOuterColorType == "белый") {
                $this->FinalOuterColor = "9003";
            } elseif ($this->ShieldOuterColorType == "коричневый") {
                $this->FinalOuterColor = "8014";
            } else {
                $this->FinalOuterColor = "8014";
            }
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
        return 'Цвет щита';
    }
}