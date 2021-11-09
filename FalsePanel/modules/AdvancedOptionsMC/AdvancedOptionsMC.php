<?php

/**
 * Модуль расчета дополнительных параметров
 * PHP version 5.5
 * @category Yii
 */
class AdvancedOptionsMC extends AbstractModelCalculation
{
    /**
    * Переменная хранит имя класса
    * 
    * @var string 
    */
    public $nameModule = 'AdvancedOptionsMC';
    
   
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
        $this->Nak         = $this->formNak;
        $this->Upak        = $this->formUpak;
        $this->GateParsing = $this->formGateParsing;
       
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
        return 'Расчет дополнительных параметров';
    }

    /**
    * Заполняет входные параметры модуля данными которые приходят с формы "Дополнительно"
    * и приходят в виде json в formAllAdvancedOptions.
    * 
    * @return
    */
    private function setFormData()
    {
        if ($data = json_decode($this->formAllAdvancedOptions, true)) {
            foreach ($data as $key => $val) {
                if (is_bool($val)) {
                    $val = $val ? 1 : 0;
                }
                $this->$key = $val;
            }
        }
    }
}