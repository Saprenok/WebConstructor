<?php

/**
 * Модуль расчета положения ручки DHF09
 * PHP version 5.5
 * @category Yii
 */
class HandleCalculationMC extends AbstractModelCalculation
{
    /**
    * Переменная хранит имя класса
    * 
    * @var string 
    */
    public $nameModule = 'HandleCalculationMC';

    /**
    * Алгоритм
    * 
    * @return bool
    */
    public function Algorithm()
    {
        //Репликация переменных
        $CalcWicketInstalled = $this->WicketInstalled;
        $CalcWicketX = $this->WicketX;
        $CalcWicketWidth = $this->WicketWidth;

        //Калитка установлена
        if ($CalcWicketInstalled) {
            //Есть место для установки ручки справа
            if ($this->ShieldWidth - ($CalcWicketX + $CalcWicketWidth) > 350 + 25 + 230 + 100) {
                $this->WicketDHF09 = "Справа";
                //Есть место для установки ручки слева
            } elseif ($CalcWicketX > 350 + 25 + 230 + 100) {
                $this->WicketDHF09 = "Слева";
                //Нет места для установки ручки
            } else {
                $this->WicketDHF09 = 0;
            }
            //Калитка не установлена
        } else {
            $this->WicketDHF09 = "Справа";
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
        return 'Расчет положения ручки DHF09';
    }
}