<?php

/**
 * Фрезерование (не нужное в Индивидуальном заказе но в коде этот модуль хардом вызывается в OrderProductSpecificationModel (е))
 * PHP version 5.5
 * @category Yii
 * @author   Shcherbakov Pavel <pavel24071988@gmail.com>
 */
class MillingMCFalsePanel
{

    /**
     * XML объект
     * @var $object
     */
    private $dom;

    /**
     * Контейнер
     * @var object
     */
    public $container;

    /**
     * Формирования файла для фрезерования
     * @return bool
     */
    public function Algorithm()
    {
        $arr = array();
        return $arr;
    }

    /**
     * Создание исходного варианта файла для фрезерования
     */
    private function createDom()
    {}

    /**
     * Геттер к контейнеру
     *
     * @param $name
     *
     * @return null
     */
    public function __get($name)
    {
        if (isset($this->container[$name])) {
            return $this->container[$name];
        } else {
            return null;
        }
    }
}
