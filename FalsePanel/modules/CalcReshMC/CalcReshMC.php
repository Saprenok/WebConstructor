<?php

/**
 * Модуль расчета Расчет опций щита: решетки, наконечников
 * PHP version 5.4
 * @category Yii
 * @author   Kuznetsov Y. <kuznetsovyuriial@mail.ru>
 */
class CalcReshMC extends AbstractModelCalculation
{
    /**
    * Переменная хранит имя класса
    * 
    * @var string 
    */
    public $nameModule = 'CalcReshMC';
    

    /**
    * Алгоритм
    * 
    * @return bool
    */
    public function Algorithm()
    {
        
        $resh = 0;
        $piki = 0;
        $piki_min = 0;
        $n_resh = 0;
        $n_resh_m = 0;
        $Index = $this->Index;
        //Высота арки/решетки значение по умолчанию
        if ($Index == 'DHPF250' || $Index == 'DHPF251' || $Index == 'DHPF252' || $Index == 'DHPF253' || $Index == 'DHPF254') {
            $resh = round(0.2 * $this->Hh, 0, PHP_ROUND_HALF_EVEN);
        } else {
            $resh = round(0.25 * $this->Hh, 0, PHP_ROUND_HALF_EVEN);
        }

        //Высота наконечников значение по умолчанию
        if ($Index == 'DHPF050' || $Index == 'DHPF051' || $Index == 'DHPF052' || $Index == 'DHPF053' || $Index == 'DHPF054' ||
            $Index == 'DHPF070' || $Index == 'DHPF071' || $Index == 'DHPF072' || $Index == 'DHPF073' || $Index == 'DHPF074'
        ) {
            $piki = round(100 + (0.2 * $this->Hh), 0, PHP_ROUND_HALF_EVEN);
        } else if ($Index == 'DHPF250' || $Index == 'DHPF251' || $Index == 'DHPF252' || $Index == 'DHPF253' || $Index == 'DHPF254') {
            $piki = round(0.20 * $this->Hh, 0, PHP_ROUND_HALF_EVEN);
        } else {
            $piki = round(100 + (0.1 * $this->Hh), 0, PHP_ROUND_HALF_EVEN);
        }

        //Высота нижней решетки значение по умолчанию
        if ($Index == 'DHPF180') {
            $n_resh = round(0.4 * $this->Hh, 0, PHP_ROUND_HALF_EVEN);
        } else if ($Index == 'DHPF250' || $Index == 'DHPF251' || $Index == 'DHPF252' || $Index == 'DHPF253' || $Index == 'DHPF254') {
            $n_resh = round(0.125 * $this->Hh, 0, PHP_ROUND_HALF_EVEN);
        } else if ($Index == 'DHPF260') {
            $n_resh = round(0.5 * $this->Hh, 0, PHP_ROUND_HALF_EVEN);
        } else {
            $n_resh = round(0.25 * $this->Hh, 0, PHP_ROUND_HALF_EVEN);
        }

        if ((0.05 * $this->Hh) < 150) {
            $piki_min = 150;
        } else {
            $piki_min = round(0.05 * $this->Hh, 0, PHP_ROUND_HALF_EVEN);
        }
        
        if ($this->BhOld != $this->Bh || $this->HhOld != $this->Hh || $this->IndexOld != $this->Index) {
            $this->resh = $resh;
            $this->piki = $piki;
            $this->piki_min = $piki_min;
            $this->n_resh = $n_resh;
        }
        
        if (!$this->piki_min)   $this->piki_min     = $piki_min;
        if (!$this->n_resh)     $this->n_resh       = $n_resh;
       
        $this->BhOld = $this->Bh;
        $this->HhOld = $this->Hh;
        $this->IndexOld = $this->Index;
        
        return true;
    }

    /**
    * Название модуля
    * 
    * @return string
    */
    public function getTitle()
    {
        return ' Расчет опций щита';
    }
}