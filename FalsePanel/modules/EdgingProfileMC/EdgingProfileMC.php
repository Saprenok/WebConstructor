<?php

/**
 * Модуль расчета Окантовка профилем
 * @category Yii
 */
class EdgingProfileMC extends AbstractModelCalculation
{
    /**
    * Переменная хранит имя класса
    * 
    * @var string 
    */
    public $nameModule = 'EdgingProfileMC';
    
    /**
    * Алгоритм
    * 
    * @return bool
    */
    public function Algorithm()
    {
        $this->defaultInit();
        $this->gainEdgingProfileAlgorithm();

        return true;
    }

    /**
    * Название модуля
    * 
    * @return string
    */
    public function getTitle()
    {
        return 'Окантовка профилем';
    }

    private function defaultInit()
    {
        $this->EdgingProfiles  = array();
        $this->setEdgingCount();
        $this->setEdgingLength();
    }

    /**
    * Отрисовка окантовки профилем
    * 
    * @return
    */
    private function gainEdgingProfileAlgorithm()
    {
        if ($this->TypeF == "Для секционных ворот") {
            if ($this->UseEdging) { 
                $deltaWidth = $this->ShieldWidth;
                //снизу щита
                $x = 0;
                $y = $this->ShieldHeight - 40;
                $this->setEdgingProfiles(array(
                    $x,
                    $y,
                    $deltaWidth,
                    40
                ));
                //сверху щита
                $x = 0;
                $y = 0;//сверху щита
                $this->setEdgingProfiles(array(
                    $x,
                    $y,
                    $deltaWidth,
                    40
                ));
                //слева щита
                $x = 0;
                $y = 40;
                $this->setEdgingProfiles(array(
                    $x,
                    $y,
                    40,
                    $this->ShieldHeight - 80
                ));
                //справа щита
                $x = $this->ShieldWidth - 40;
                $y = 40;
                $this->setEdgingProfiles(array(
                    $x,
                    $y,
                    40,
                    $this->ShieldHeight - 80
                ));
            }
        } 
    }

    /**
    * Добавляет элементы в массив EdgingProfiles
    * 
    * @param mixed $value
    * 
    * @return
    */
    private function setEdgingProfiles($value)
    {
        $tempArray           = $this->EdgingProfiles;
        $tempArray[]         = $value;
        $this->EdgingProfiles = $tempArray;
    }

    private function setEdgingCount($value = 0)
    {
        $this->EdgingCount = $value;
    }

    private function setEdgingLength($value = 0)
    {
        $this->EdgingLength = $value;
    }
}