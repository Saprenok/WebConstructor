<?php

/**
 * Модуль Расчет панорамных секционных ворот
 * 
 * PHP version 5.5
 * @category Yii
 * @author   Charnou Vitaliy <graffov87@gmail.com>
 */
class PanoramicCalculationMC extends AbstractModelCalculation
{

    /**
     * Переменная хранит имя класса
     * 
     * @var string 
     */
    public $nameModule = 'PanoramicCalculationMC';
    
    private function IncArray(&$array, $index, $increment)
    {
        if (array_key_exists($index, $array)) {
            $array[$index] += $increment;
        } else {
            $array[$index] = $increment;
        }
    }

    /**
     * Алгоритм
     * 
     * @return bool
     */
    public function Algorithm()
    {
        $modal = array();
        //1. Расчет параметров панорамного щита
        //Ширина панорамных панелей  
        $this->PanPanelWidth = $this->ShieldWidth - 3;
        //Значения по умолчанию
        $this->PanTopPanelCut = 0;
        $this->PanBottomPanelCut = 0;
            
        $this->PanWicketAllSandwich = 0;    
        $this->PanWicketTopIsShieldTop = 0;    
        $this->PanWicketTopIsPanoramic = 0;    
        $this->PanWicketTopIsSandwich = 0;    
        $this->PanWicketTopHasSandwich = 0;    
        $this->PanWicketTopAddedSandwich = 0;    
        $this->PanWicketNumberOfPanoramic = 0;
        $this->PanWicketNumberOfSandwich = 0;
        $this->PanWicketLarge = 0;    
        $this->PanWicketHeightLimit = 0;
        $this->PanWicketTopAddedManually = 0;    
        $this->PanShowMiddlePanel = 0;    
        $this->PanTopPanel = 0;
        $this->PanTopCount = 0;
        $this->PanBottomPanel = 0;
        $this->PanBottomCount = 0;
        $this->PanMiddlePanel = 0;
        $this->PanMiddleCount = 0;
        $this->PanRegularPanel = $this->Hh;
        
        if ($this->ShieldWidth >= 4500) {
            $this->PanStrengthenedRegularCount = $this->NPanpramic;
        } else {
            $this->PanRegularCount = $this->NPanpramic;
        }
        
        $this->PanVariableGapPanel = 0;
        $this->PanVariableGapCount = 0;
        $this->PanStrengthenedVariableGapCount = 0;
        $this->PanScrapsHeight = 0;
        $this->PanScrapsCount = 0;
        $this->PanVariableGapScrapsHeight = 0;
        $this->PanVariableGapScrapsCount = 0;
        $this->PanTopAdditionalHeight = 0;
        $this->PanBottomAdditionalHeight = 0;
        $this->PanWicketL = 0;
        $this->PanWicketL1 = 0;
        $this->PanWicketLeft = 0;
        $this->PanWicketTop = 0;
        $this->PanWicketWidth = 0;
        $this->PanWicketHeight = 0;
        $this->PanWicketNumberOfGaps = 0;
        $PanWicketRealHeight = 0;
        
        //Запуск модулей подбора кодов панелей и остекления
        //Запустить модуль "Выбор остекления для панорамных панелей" (PanGlassingSelection)    
        Yii::import('calculation.PanGlassSelectionMC.PanGlassSelectionMC');
        $profileS = new PanGlassSelectionMC();
        $profileS->key = $this->key;
        $profileS->fillVariables();
        $profileS->Fill();
        $profileS->Algorithm();
        $profileS->Output();
        
        //Запустить модуль "Выбор панорамных панелей" (PanPanelsSelection)
        Yii::import('calculation.PanPanelsSelectionMC.PanPanelsSelectionMC');
        $profileS = new PanPanelsSelectionMC();
        $profileS->key = $this->key;
        $profileS->fillVariables();
        $profileS->Fill();
        $profileS->Algorithm();
        $profileS->Output();
        
        $this->PanPanelSizes = $profileS->PanPanelSizes;
        $PanWicketNumberOfPanels = array();

        //Для каждого значения PanelSize из PanPanelSizes выполнить следующие действия    
        foreach ($this->PanPanelSizes as $PanelSize) {
            $PanWicketNumberOfPanels[$PanelSize] = 0;
        }
        
        if ($this->PanoramicPanelInstalled) {
            $this->PanNumberOfGaps = $this->PanoramicPanelNumberOfGaps;
        }
        
        //2. Расчет значений для отображения на чертежах
        //Для теплых профилей    
        if ($this->WarmProfiles == 1) {
            if ($this->PanNumberOfGaps != 0) {
                $this->PanL2 = round(($this->PanPanelWidth - 175) / $this->PanNumberOfGaps, 0, PHP_ROUND_HALF_EVEN);
            } else {
                $this->PanL2 = 0;
            }
            $this->PanL1 = $this->PanL2 + 70;
            if ($this->PanNumberOfGaps)
                $this->PanL1Warm = 105 + 70 / 2 + ($this->PanPanelWidth - 105 * 2 - 70 * ($this->PanNumberOfGaps - 1)) / $this->PanNumberOfGaps;
        //Для профилей со штапиками    
        } elseif ($this->WithBeadings == 1) {
            if ($this->PanNumberOfGaps != 0) {
                $this->PanL2 = round(($this->PanPanelWidth - 150) / $this->PanNumberOfGaps, 0, PHP_ROUND_HALF_EVEN);
            } else {
                $this->PanL2 = 0;
            }
            $this->PanL1 = $this->PanL2 + 75 + 2;
            $this->PanL1Warm = 0;
        //Для обычных профилей
        } else {
            if ($this->PanNumberOfGaps != 0) {
                $this->PanL2 = round(($this->PanPanelWidth - 150) / $this->PanNumberOfGaps, 0, PHP_ROUND_HALF_EVEN);
            } else {
                $this->PanL2 = 0;
            }
            $this->PanL1 = $this->PanL2 + 75;
            $this->PanL1Warm = 0;
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
        return 'Расчет панорамных секционных ворот';
    }

}
