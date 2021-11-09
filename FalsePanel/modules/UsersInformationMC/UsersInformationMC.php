<?php

/**
 * Инофрмация о пользователе
 * PHP version 5.5
 * @category Yii
 * @author   Charnou Vitaliy <graffov87@gmail.com>
 */
class UsersInformationMC extends AbstractModelCalculation
{
    /**
    * Название модуля
    * 
    * @var string Название модуля
    */
    public $nameModule = 'UsersInformationMC';


    /**
    * Алгоритм
    * 
    * @return bool
    */
    public function Algorithm()
    {
        $this->DilerVersion = 0;
        $this->Country = "Россия";
        if ($this->Region == "Киев" || $this->Region == "Днепропетровск") {
            $this->Country = "Украина";
        }
        if ($this->Region == "Kadan") {
            $this->Country = "Чехия";
        }
        if ($this->Region == "Suzhou") {
            $this->Country = "Китай";
        }
        if ($this->Region == "Mumbai") {
            $this->Country = "Индия";
        }
        if ($this->Region == "Астана" || $this->Region == "Алматы") {
            $this->Country = "Казахстан";
        }

        return true;
    }

    /**
    * Имя модуля
    * 
    * @return string возвращает имя модуля
    */
    public function getTitle()
    {
        return 'Инофрмация о пользователе';
    }
}