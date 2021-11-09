<?php

/**
 * Модуль расчета Цвет окантовки
 * PHP version 5.5
 * @category Yii
 */
class ColorEdgingMC extends AbstractModelCalculation
{
    /**
    * Переменная хранит имя класса
    * 
    * @var string 
    */
    public $nameModule = 'ColorEdgingMC';
    

    /**
    * Алгоритм
    * 
    * @return bool
    */
    public function Algorithm()
    {
        $this->infoArray = array();
        $this->setFormData();
        
        //алгоритм формы
        $this->ColorEdging                       = $this->formColorEdging;
        $this->OtherColorEdging                  = $this->formOtherColorEdging;
        $this->ColorPost                         = $this->formColorPost;
        $this->OtherColorPost                    = $this->formOtherColorPost;
        $this->ColorGrid                         = $this->formColorGrid;
        $this->OtherColorGrid                    = $this->formOtherColorGrid;
        $this->ColorTips                         = $this->formColorTips;
        $this->OtherColorTips                    = $this->formOtherColorTips;
        $this->ColorVenzel                       = $this->formColorVenzel;
        $this->OtherColorVenzel                  = $this->formOtherColorVenzel;
        
        $this->OtherColorEdgingWithoutRal = "НЕОКРАШЕННЫЙ";
        $CEdgWithoutRal = substr($this->OtherColorEdging, -4);
        if (    is_numeric($CEdgWithoutRal) && 
                (
                    $CEdgWithoutRal == 9003 || 
                    $CEdgWithoutRal == 8014 ||
                    $CEdgWithoutRal == 5005 ||
                    $CEdgWithoutRal == 6005 ||
                    $CEdgWithoutRal == 3005 ||
                    $CEdgWithoutRal == 7004 ||
                    $CEdgWithoutRal == 1014 ||
                    $CEdgWithoutRal == 9006 ||
                    $CEdgWithoutRal == 8017
                )
            )
        {
            $this->OtherColorEdgingWithoutRal = $CEdgWithoutRal;
        }
        
        
        //Переход к следующей форме(из интерфейса)

        return true;
    }

    /**
    * Название модуля
    * 
    * @return string
    */
    public function getTitle()
    {
        return 'Расчет цвета окантовки';
    }

    /**
    * Заполняет входные параметры модуля данными которые приходят с формы "Цвет окантовки"
    * и приходят в виде json в formAllColorEdging.
    * 
    * @return
    */
    private function setFormData()
    {
        if ($data = json_decode($this->formAllColorEdging, true)) {
            foreach ($data as $key => $val) {
                if (is_bool($val)) {
                    $val = $val ? 1 : 0;
                }
                $this->$key = $val;
            }
        }
    }
}