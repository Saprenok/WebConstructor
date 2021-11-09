<?php

/**
 * Удаление объектов
 * @example  здесь текст примера использования класса (необязательно)
 * PHP version 5.5
 * @category Yii
 * @author   Charnou Vitaliy <graffov87@gmail.com>
 */
class DeleteObjectsMC extends AbstractModelCalculation
{

    /**
    * Переменная хранит имя класса
    * 
    * @var string 
    */
    public $nameModule = 'DeleteObjectsMC';
    private $EmbeddedObjects;

    /**
    * Алгоритм
    * 
    * @return bool
    */
    public function Algorithm()
    {
        $this->EmbeddedObjects         = Yii::app()->container->EmbeddedObjects;
        $this->EmbeddedObjectsCount    = Yii::app()->container->EmbeddedObjectsCount;
        $this->EmbeddedObjectsCounts   = Yii::app()->container->EmbeddedObjectsCounts;
        $i                             = $this->Elements;
        $this->WI                      = Yii::app()->container->WI;
        $this->V1                      = Yii::app()->container->V1;
        $this->V2                      = Yii::app()->container->V2;
        $this->VR                      = Yii::app()->container->VR;
        $this->VV1                     = Yii::app()->container->VV1;
        $this->S1                      = Yii::app()->container->S1;
        $this->S2                      = Yii::app()->container->S2;
        $this->S3                      = Yii::app()->container->S3;
        $this->N1                      = Yii::app()->container->N1;
        $this->N2                      = Yii::app()->container->N2;
        $this->NV1                     = Yii::app()->container->NV1;
        $this->WindowSteps             = Yii::app()->container->WindowSteps;
        $this->WindowPanels            = Yii::app()->container->WindowPanels;
        $this->WindowPartNumbers       = Yii::app()->container->WindowPartNumbers;
        $this->WindowSizes             = Yii::app()->container->WindowSizes;
        $this->WindowCounts            = Yii::app()->container->WindowCounts;
        $this->WindowCount             = Yii::app()->container->WindowCount;
        $this->WindowIsRadius          = Yii::app()->container->WindowIsRadius;
        $this->WindowRadius            = Yii::app()->container->WindowRadius;
        $this->WindowPaddings          = Yii::app()->container->WindowPaddings;
        $this->WindowLocations         = Yii::app()->container->WindowLocations;
        $this->WindowDefaults          = Yii::app()->container->WindowDefaults;
        $this->WindowRecommendations   = Yii::app()->container->WindowRecommendations;
        $this->WindowAutoCalc          = Yii::app()->container->WindowAutoCalc;
        $this->WindowMin               = Yii::app()->container->WindowMin;
        $this->WindowMax               = Yii::app()->container->WindowMax;
        $this->WindowRecommended       = Yii::app()->container->WindowRecommended;
        $this->WindowXs                = Yii::app()->container->WindowXs;
        $this->WindowYs                = Yii::app()->container->WindowYs;
        $this->WindowNewPadding        = Yii::app()->container->WindowNewPadding;
        $this->WindowNewSteps          = Yii::app()->container->WindowNewSteps;
        $this->WindowName              = Yii::app()->container->WindowName;
        $this->WindowCardId            = Yii::app()->container->WindowCardId;
        $this->WindowMinDistance       = Yii::app()->container->WindowMinDistance;
        $this->WindowOrder             =  Yii::app()->container->WindowOrder;
        if (count($this->EmbeddedObjects)) {
            if ($this->EmbeddedObjects[$i]['type'] == "window") {
                $n                           = $this->EmbeddedObjects[$i]['number'];
                $this->WindowIsRadius        = $this->arrayDrive(Yii::app()->container->WindowIsRadius, $n);
                $this->WindowRadius          = $this->arrayDrive(Yii::app()->container->WindowRadius, $n);
                $this->WindowAutoCalc        = $this->arrayDrive(Yii::app()->container->WindowAutoCalc, $n);
                $this->WindowMin             = $this->arrayDrive(Yii::app()->container->WindowMin, $n);
                $this->WindowMax             = $this->arrayDrive(Yii::app()->container->WindowMax, $n);
                $this->WindowRecommended     = $this->arrayDrive(Yii::app()->container->WindowRecommended, $n);
                //$this->WindowIsRadius        = $this->arrayDrive(Yii::app()->container->WindowLocations, $n);
                //$this->WindowRadius          = $this->arrayDrive(Yii::app()->container->WindowLocations, $n);
                $this->WindowLocations       = $this->arrayDrive(Yii::app()->container->WindowLocations, $n);
                $this->WindowPaddings        = $this->arrayDrive(Yii::app()->container->WindowPaddings, $n);
                $this->WindowDefaults        = $this->arrayDrive(Yii::app()->container->WindowDefaults, $n);
                $this->WindowRecommendations = $this->arrayDrive(Yii::app()->container->WindowRecommendations, $n);
                $this->WindowSteps           = $this->arrayDrive(Yii::app()->container->WindowSteps, $n);
                $this->WindowPanels          = $this->arrayDrive(Yii::app()->container->WindowPanels, $n);
                $this->WindowPartNumbers     = $this->arrayDrive(Yii::app()->container->WindowPartNumbers, $n);
                $this->WindowSizes           = $this->arrayDrive(Yii::app()->container->WindowSizes, $n);
                $this->WindowCounts          = $this->arrayDrive(Yii::app()->container->WindowCounts, $n);
                $this->WindowCount           = Yii::app()->container->WindowCount - 1;
                $this->WindowXs              = $this->arrayDrive(Yii::app()->container->WindowXs, $n);
                $this->WindowYs              = $this->arrayDrive(Yii::app()->container->WindowYs, $n);
                $this->WindowNewPadding      = $this->arrayDrive(Yii::app()->container->WindowNewPadding, $n);
                $this->WindowNewSteps        = $this->arrayDrive(Yii::app()->container->WindowNewSteps, $n);
                $this->WindowName            = $this->arrayDrive(Yii::app()->container->WindowName, $n);
                $this->WindowCardId          = $this->arrayDrive(Yii::app()->container->WindowCardId, $n);
                $this->WindowMinDistance     = $this->arrayDrive(Yii::app()->container->WindowMinDistance, $n);
            }
        }
        if (array_key_exists($i, $this->EmbeddedObjects)) {
            unset($this->EmbeddedObjects[$i]);
            $this->EmbeddedObjectsCounts--;
            if ($this->EmbeddedObjectsCounts < 0) {
                $this->EmbeddedObjectsCounts = 0;
            };
            $this->EmbeddedObjectsCount--;
            if ($this->EmbeddedObjectsCount < 0) {
                $this->EmbeddedObjectsCount = 0;
            };
            Yii::app()->container->EmbeddedObjectsCount  = $this->EmbeddedObjectsCount;
            Yii::app()->container->EmbeddedObjects       = $this->EmbeddedObjects;
            Yii::app()->container->EmbeddedObjectsCounts = $this->EmbeddedObjectsCounts;
        }

        return true;
    }

    /**
    * Удаляет из массива $array значение хранимое по ключу $n.
    * Возвращает массив с обновленными ключами, начинающимися с 1.
    * 
    * @param mixed $array Неассоциативный массив
    * @param mixed $n Ключ к массиву(число)
    * 
    * @return array
    */
    private function arrayDrive($array, $n)
    {
        $newArray = array();
        unset($array[$n]);
        $k = 1;
        foreach ($array as $key => $value) {
            $newArray[$k] = $value;
            $k++;
        }

        return $array;
    }

    /**
    * Название модуля
    * 
    * @return string
    */
    public function getTitle()
    {
        return 'Удаление объектов';
    }
}