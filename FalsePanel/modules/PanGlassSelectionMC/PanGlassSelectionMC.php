<?php

/**
 * Модуль Выбор остекления для панорамных панелей
 * 
 * PHP version 5.5
 * @category Yii
 * @author   Charnou Vitaliy <graffov87@gmail.com>
 */
class PanGlassSelectionMC extends AbstractModelCalculation
{

    /**
     * Переменная хранит имя класса
     * 
     * @var string 
     */
    public $nameModule = 'PanGlassSelectionMC';

    /*public $listGlassingType = array(
        'Обычное' => 102600,
        'Ударопрочное' => 125269,
        'Сотовое (прозрачное)' => 126126,
        'Сотовое (прозр. текстур.)' => 126128,
        'Сотовое (матовое)' => 126342,
        'Сотовое (мат. теплоизолир)' => 126127,
        'Одинарный поликарбонат' => 102600,
        'Решетка' => 'CB000151730',
    );*/
    public $listGlassingIndex = array(
        'Обычное' => 0,
        'Ударопрочное' => 1,
        'Сотовое (прозрачное)' => 2,
        'Сотовое (прозр. текстур.)' => 3,
        'Сотовое (матовое)' => 4,
        'Сотовое (мат. теплоизолир)' => 5,
        'Одинарный поликарбонат' => 6,
        'Решетка' => 7,
    );
    /**
     * Алгоритм
     * 
     * @return bool
     */
    public function Algorithm()
    {
        if ($this->RegionMoscow) {
            $listGlassingType = array(
                'Обычное' => 'CB000246307',
                'Ударопрочное' => 'CB000246307',
                'Сотовое (прозрачное)' => 126126,
                'Сотовое (прозр. текстур.)' => 126128,
                'Сотовое (матовое)' => 126342,
                'Сотовое (мат. теплоизолир)' => 126127,
                'Одинарный поликарбонат' => 'CB000246307',
                'Решетка' => 'CB000151730',
            );
        } else {
            $listGlassingType = array(
                'Обычное' => 102600,
                'Ударопрочное' => 125269,
                'Сотовое (прозрачное)' => 126126,
                'Сотовое (прозр. текстур.)' => 126128,
                'Сотовое (матовое)' => 126342,
                'Сотовое (мат. теплоизолир)' => 126127,
                'Одинарный поликарбонат' => 102600,
                'Решетка' => 'CB000151730',
            );
        }
        
        
        if (!$this->PanoramicPanelInstalled) {
            $Type = $this->PanGlassingType;
        } else {
            $Type = $this->PanoramicPanelGlassingType;
        }
        if ($this->RegionMoscow) {
            $this->PanGlassingCode = "CB000246307";
        } else {
            $this->PanGlassingCode = "102600";
        }
        if (isset($listGlassingType[$Type])) {
            $this->PanGlassingCode = $listGlassingType[$Type];
        }            
        if (isset($this->listGlassingIndex[$Type])) {
            $this->PanGlassingIndex = $this->listGlassingIndex[$Type];
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
        return 'Выбор остекления для панорамных панелей';
    }

}
