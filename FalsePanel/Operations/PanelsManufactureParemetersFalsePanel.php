<?php

/**
 * Параметры производства панелей
 * @example  здесь текст примера использования класса (необязательно)
 * PHP version 5.5
 * @category Yii
 * @author   Charnou Vitaliy <graffov87@gmail.com>
 */
class PanelsManufactureParemetersFalsePanel
{
    public $container;

    public function Algorihtm()
    {
        $Specification      = SpecificationModel::model()->findAll('product_id=' . $this->container['productId']);
        $specification_item = array();
        foreach ($Specification as $key => $value) {
            $specification_item[] = $value->id;
        }
        $formula  = new FormulaHelper();
        $criteria = new CDbCriteria();
        $criteria->addInCondition('specification_id', $specification_item);
        $criteria->addCondition('order_product_id=' . $this->container['orderProductId']);
        $Elements   = OrderProductSpecificationModel::model()->findAll($criteria);
        $Operations = array();
        $model      = ShieldModel::model()->find(array(
            'condition' => 'id=:id',
            'params'    => array(
                ':id' => $this->CurrentPanelType,
            )
        ));
        if (stripos($this->ProductName, "YETT") == true) {
            $IsYett = true;
        } else {
            $IsYett = false;
        }
        $panels = $this->getLink($Elements, "Панели");
        $count  = count($panels);
        if ($count > 0) {
            foreach ($panels as $key => $value) {
                $size        = 0;
                $TopHoles    = array();
                $BottomHoles = array();
                $TopCount    = 0;
                $BottomCount = 0;
                if (strpos($model->title, "DS30") == false) {
                    foreach ($this->Hinges as $keys => $values) {
                        $Hinge = $values;
                        $Flag  = 0;
                        if ($Hinge) {
                            if ($this->WicketInstalled) {
                                if (in_array($this->WicketType, array(
                                    "Калитка v4",
                                    "Калитка v5"
                                ))
                                ) {
                                    $Offset = 54;
                                } else {
                                    $Offset = 0;
                                }
                                if (($Hinge['X'] >= $this->WicketX - $Offset) && ($Hinge['X'] < $this->WicketX + $this->WicketWidth + $Offset) && ($Hinge['Y'] > $this->ShieldDrillingHeight - $this->WicketY) && ($Hinge['Y'] < $this->ShieldDrillingHeight - ($this->WicketY - $this->WicketHeight))) {
                                    $Flag = 0;
                                } else {
                                    $Flag = 1;
                                }
                            } else {
                                $Flag = 0;
                            }
                        }
                        if ($Flag) {
                            if (($Hinge['Y'] < $size + 50) && ($Hinge['Y'] > $size)) {
                                if ($TopCount < 10) {
                                    $TopHoles[] = $Hinge['X'];
                                    $TopCount++;
                                }
                            } else {
                                if ($BottomCount < 10) {
                                    $BottomHoles[] = $Hinge['X'];
                                    $BottomCount++;
                                }
                            }
                        }
                    }
                    if (($TopCount > 0) && ($BottomCount > 0)) {
                        asort($TopHoles);
                        asort($BottomHoles);
                        if ($key == count($panels) - 1) {
                            $DrillType = 2;
                            $Holes     = $TopHoles;
                            $HoleCount = $TopCount;
                        } elseif ($key == 0) {
                            $DrillType = 1;
                            $Holes     = $BottomHoles;
                            $HoleCount = $BottomCount;
                        } else {
                            $Flag = 1;
                            if ($TopCount == $BottomCount) {
                                for ($k = 0; $k <= $TopCount; $k++) {
                                    if ($TopHoles[$k] != $BottomHoles[$k]) {
                                        $Flag = 0;
                                    }
                                }
                            } else {
                                $Flag = 0;
                            }
                            if ($Flag) {
                                $DrillType = 0;
                            } else {
                                $DrillType = 2;
                            }
                            $Holes     = $TopHoles;
                            $HoleCount = $TopCount;
                        }
                    } else {
                        $DrillType = 0;
                    }
                    $CurrentX = $Holes[0];
                    $Index    = 1;
                    while ($Index <= $HoleCount) {
                        if ($Holes[$Index] - $CurrentX < 500) {
                            for ($l = $Index; $l <= $HoleCount - 1; $l++) {
                                $Holes[$l] = $Holes[$l + 1];
                            }
                            unset($Holes[$HoleCount]);
                            $HoleCount--;
                        } else {
                            $OldX          = $Holes[$Index];
                            $Holes[$Index] = $Holes[$Index] - $CurrentX;
                            $CurrentX      = $OldX;
                            $Index++;
                        }
                    }
                    $NewHoles   = array();
                    $HingeWidth = 45;
                    $OldX       = 0;
                    for ($k = $HoleCount; $k >= 0; $k--) {
                        $CurrentX = 0;
                        for ($l = 0; $l <= $k; $l++) {
                            $CurrentX                      = $CurrentX + $Holes[$l];
                            $NewHoles[$HoleCount - $k + 1] = $this->ShieldWidth - $CurrentX - $OldX - $this->HingeWidth;
                            $OldX                          = $OldX + $NewHoles[$HoleCount - $k + 1];
                        }
                    }
                    if (!$this->RegionEurope) {
                        $Operations = array();
                        $Elements   = array();
                        if ($DrillType == 0) {
                            $Name = "tech_storony_obe";
                        }
                        if ($DrillType == 1) {
                            $Name = "tech_storony_sverhu";
                        }
                        if ($DrillType == 2) {
                            $Name = "tech_storony_snizu";
                        }
                        $Elements[] = array("tech_storony" => $Name);
                        for ($k = 0; $k <= $HoleCount; $k++) {
                            $Elements[] = array("СверлениеОтверстий_Шаг" . $k => $NewHoles[$k]);
                            $Elements[] = array("СверлениеОтверстий_Стороны" . $k => $DrillType);
                        }
                        $Operations[] = array(
                            "Code"     => "000000008",
                            "Elements" => $Elements
                        );
                        if ($value->panel_size != $value->width) {
                            $Elements = array();
                            if ($key == count($panels) - 1) {
                                $PanelWidth = $value->panel_size - $value->width;
                            } else {
                                $PanelWidth = $value->width;
                            }
                            $Elements[]   = array("tech_prodol_rez" => $PanelWidth);
                            $Operations[] = array(
                                "Code"     => "000000003",
                                "Elements" => $Elements
                            );
                        }
                        $Operations[] = array(
                            "Code"     => "CB0000024",
                            "Elements" => ""
                        );
                    } else {
                        $Operations = array();
                        $Elements   = array();
                        for ($k = 0; $k <= $HoleCount; $k++) {
                            $Elements[] = array("tech_sverlenie_" . $k => $NewHoles[$k]);
                        }
                        $Operations[]  = array(
                            "Code"     => "CB0000019",
                            "Elements" => $Elements
                        );
                        $ProfileExists = 0;
                        if ($key == count($panels) - 1) {
                            if ($IsYett) {
                                $CapType = 115;
                            } else {
                                $CapType = 125;
                            }
                            if ($this->ShieldBottomPanelIsCut) {
                                $CapType = 0;
                            }
                            $profiles = $this->getLink($Elements, "ПрофильСнизуЩита");
                            foreach ($profiles as $ki => $val) {
                                if ($val) {
                                    $ProfileExists = 1;
                                    if (in_array($val->nomenclature_id, array(
                                        "DH80043",
                                        "DHSW-0006"
                                    ))
                                    ) {
                                        $CapType = 0;
                                    }
                                }
                            }
                        } elseif ($key == 0) {
                            if ($IsYett) {
                                $CapType = 214;
                            } else {
                                $CapType = 114;
                            }
                            $CapType = 124;
                            if ($this->ShieldBottomPanelIsCut) {
                                $CapType = 0;
                            }
                            $profiles = $this->getLink($Elements, "ПрофильСверхуЩита");
                            foreach ($profiles as $ki => $val) {
                                if ($val) {
                                    $ProfileExists = 1;
                                }
                            }
                        } else {
                            if ($IsYett) {
                                $CapType = 116;
                            } else {
                                $CapType = 126;
                            }
                        }
                        $Elements     = array();
                        $Elements[]   = array("УстановкаКрышек_ТипЛевойКрышки" => $CapType);
                        $Elements[]   = array("УстановкаКрышек_ТипПравойКрышки" => $CapType);
                        $Elements[]   = array("УстановкаКрышек_ДлинаКрышек" => $value->width);
                        $Elements[]   = array("УстановкаКрышек_Сверление" => 1);
                        $Operations[] = array(
                            "Code"     => "CB0000016",
                            "Elements" => $Elements
                        );
                        if ($ProfileExists) {
                            if ($key == count($panels) - 1) {
                                $panelPosition = 2;
                            } elseif ($key == 0) {
                                $panelPosition = 1;
                            } else {
                                $panelPosition = 0;
                            }
                        } else {
                            $panelPosition = 0;
                        }
                        if ($this->ShieldWithAntiJamProtection) {
                            $panelType = 1;
                        } else {
                            $panelType = 0;
                        }
                        $Elements     = array();
                        $Elements[]   = array(
                            "СборкаПанели_Пачка",
                            1
                        );
                        $Elements[]   = array("СборкаПанели_Положение" => $panelPosition);
                        $Elements[]   = array("СборкаПанели_Номер" => count($panels) - $key + 1);
                        $Elements[]   = array("СборкаПанели_Тип" => $panelType);
                        $Operations[] = array(
                            "Code"     => "CB0000017",
                            "Elements" => $Elements
                        );
                        $PanelWidth   = $value->width;
                        if ($key == count($panels) - 1) {
                            if ($this->ShieldBottomPanelIsCut) {
                                if ($value->width == $value->panel_size) {
                                    $value->width = $value->width - 1;
                                }
                            }
                        } elseif ($key == 0) {
                            if ($this->ShieldTopPanelIsCut) {
                                if ($value->width == $value->panel_size) {
                                    $value->width = $value->width - 1;
                                } else {
                                    $value->width = $value->width + 30;
                                }
                            }
                        } else {
                            $value->width = $value->width;
                            $Elements     = array();
                            $Elements[]   = array(
                                "ОбрезкаПанели_РезультирующаяШирина",
                                $PanelWidth
                            );
                            $Elements[]   = array("ОбрезкаПанели_ИсходнаяШирина" => $value->panel_size);
                            $Elements[]   = array("ОбрезкаПанели_ИсходнаяДлина" => $value->length);
                            $Elements[]   = array("ОбрезкаПанели_РезультирующаяДлина" => $value->length);
                            $Operations[] = array(
                                "Code"     => "CB0000018",
                                "Elements" => $Elements
                            );
                            $Elements     = array();
                            if ($key == count($panels) - 1) {
                                $profiles = $this->getLink($Elements, "ПрофильСнизуЩита");
                                foreach ($profiles as $ki => $val) {
                                    if ($val) {
                                        $Type = 0;
                                        if (strpos($val->nomenclature_id, "80041")) {
                                            $Type = 1;
                                        }
                                        if (strpos($val->nomenclature_id, "DH80042")) {
                                            $Type = 2;
                                        }
                                        if (strpos($val->nomenclature_id, "DH80043")) {
                                            $Type = 3;
                                        }
                                        if (strpos($val->nomenclature_id, "DHSK-2015")) {
                                            $Type = 4;
                                        }
                                        if (strpos($val->nomenclature_id, "DHSK-2016")) {
                                            $Type = 5;
                                        }
                                        if (strpos($val->nomenclature_id, "DHSW-0006")) {
                                            $Type = 6;
                                        }
                                        if (strpos($val->nomenclature_id, "DHSK-2022")) {
                                            $Type = 7;
                                        }
                                        if (strpos($val->nomenclature_id, "DHSK-2018")) {
                                            $Type = 8;
                                        }
                                        if (strpos($val->nomenclature_id, "DHSK-2017")) {
                                            $Type = 9;
                                        }
                                        $Elements[]    = array("УстановкаПрофиля_ТипПрофиля" => $Type);
                                        $RivetCOunt    = ceil(($value->length - 300) / 450) + 1;
                                        $RivetDistance = ceil(($value->length - 300) / $RivetCOunt);
                                        $i             = 1;
                                        for ($k = 1; $k <= $RivetCOunt; $k++) {
                                            $Elements[] = array("УстановкаПрофиля_Сторона" . $i => 0);
                                            if ($k == 1) {
                                                $Elements[] = array("УстановкаПрофиля_Клепка" . $i => 150);
                                            } else {
                                                $Elements[] = array("УстановкаПрофиля_Клепка" . $i => $RivetDistance);
                                            }
                                            $i++;
                                        }
                                        $Elements[] = array("УстановкаПрофиля_Сторона" . $i => 1);
                                        $Elements[] = array("УстановкаПрофиля_Клепка" . $i => 15);
                                        $i++;
                                        if ($value->length <= 3050) {
                                            $Elements[] = array("УстановкаПрофиля_Сторона" . $i => 1);
                                            $Elements[] = array("УстановкаПрофиля_Клепка" . $i => $value->length - 30 - 2 * ceil($value->length / 1000 + 1));
                                            $i++;
                                        } else {
                                            $Elements[] = array("УстановкаПрофиля_Сторона" . $i => 1);
                                            $Elements[] = array("УстановкаПрофиля_Клепка" . $i => $RivetDistance + 150 - 15);
                                            $i++;
                                            for ($k = 1; $k <= $RivetCOunt; $k++) {
                                                $Elements[] = array("УстановкаПрофиля_Сторона" . $i => 0);
                                                $Elements[] = array("УстановкаПрофиля_Сторона" . $i => 1);
                                                $Elements[] = array("УстановкаПрофиля_Клепка" . $i => $RivetDistance);
                                                $i++;
                                            }
                                            $Elements[] = array("УстановкаПрофиля_Сторона" . $i => 1);
                                            $Elements[] = array("УстановкаПрофиля_Клепка" . $i => $RivetDistance + 150 - 15);
                                            $i++;
                                        }
                                    }
                                }
                            } elseif ($key == 0) {
                                $profiles = $this->getLink($Elements, "ПрофильСверхуЩита");
                                foreach ($profiles as $ki => $val) {
                                    if ($val) {
                                        $Type = 0;
                                        if (strpos($val->nomenclature_id, "80041")) {
                                            $Type = 1;
                                        }
                                        if (strpos($val->nomenclature_id, "DH80042")) {
                                            $Type = 2;
                                        }
                                        if (strpos($val->nomenclature_id, "DH80043")) {
                                            $Type = 3;
                                        }
                                        if (strpos($val->nomenclature_id, "DHSK-2015")) {
                                            $Type = 4;
                                        }
                                        if (strpos($val->nomenclature_id, "DHSK-2016")) {
                                            $Type = 5;
                                        }
                                        if (strpos($val->nomenclature_id, "DHSW-0006")) {
                                            $Type = 6;
                                        }
                                        if (strpos($val->nomenclature_id, "DHSK-2022")) {
                                            $Type = 7;
                                        }
                                        if (strpos($val->nomenclature_id, "DHSK-2018")) {
                                            $Type = 8;
                                        }
                                        if (strpos($val->nomenclature_id, "DHSK-2017")) {
                                            $Type = 9;
                                        }
                                        $Elements[]     = array("УстановкаПрофиля_ТипПрофиля" => $Type);
                                        $i              = 1;
                                        $PanelLength    = $value->length;
                                        $StartDistance  = 150;
                                        $CenterDistance = 200;
                                        $Side           = 0;
                                        $RivetCOunt     = $this->kII($value->length / 450);
                                        if ($RivetCOunt > 2) {
                                            $RivetDistance = ceil(($value->length - $StartDistance * 2 - $CenterDistance) / ($RivetCOunt - 2));
                                        } else {
                                            $RivetDistance = 0;
                                        }
                                        $CenterIndex = ceil($RivetCOunt / 2) + 1;
                                        for ($kl = 1; $kl <= $RivetCOunt; $kl++) {
                                            $Elements[] = array("УстановкаПрофиля_Сторона" . $i => $Side);
                                            if ($kl == 1) {
                                                $Elements[] = array("УстановкаПрофиля_Клепка" . $i => $StartDistance);
                                            } elseif ($k == $CenterIndex) {
                                                $Elements[] = array("УстановкаПрофиля_Клепка" . $i => $CenterDistance);
                                            } else {
                                                $Elements[] = array("УстановкаПрофиля_Клепка" . $i => $RivetDistance);
                                            }
                                            $i++;
                                        }
                                        $PanelLength    = $value->length;
                                        $StartDistance  = 15;
                                        $CenterDistance = 300;
                                        $Side           = 1;
                                        $RivetCOunt     = $this->kII($value->length / 450);
                                        if ($RivetCOunt > 2) {
                                            $RivetDistance = ceil(($value->length - $StartDistance * 2 - $CenterDistance) / ($RivetCOunt - 2));
                                        } else {
                                            $RivetDistance = 0;
                                        }
                                        $CenterIndex = ceil($RivetCOunt / 2) + 1;
                                        for ($kl = 1; $kl <= $RivetCOunt; $kl++) {
                                            $Elements[] = array("УстановкаПрофиля_Сторона" . $i => $Side);
                                            if ($kl == 1) {
                                                $Elements[] = array("УстановкаПрофиля_Клепка" . $i => $StartDistance);
                                            } elseif ($k == $CenterIndex) {
                                                $Elements[] = array("УстановкаПрофиля_Клепка" . $i => $CenterDistance);
                                            } else {
                                                $Elements[] = array("УстановкаПрофиля_Клепка" . $i => $RivetDistance);
                                            }
                                            $i++;
                                        }
                                    }
                                }
                            }
                            $Operations[] = array(
                                "Code"     => "CB0000015",
                                "Elements" => $Elements
                            );
                            //for ($j = 1; $j <= $this->WindowCount; $j++) {
                            $WindowYs = $this->WindowYs;
                            foreach ($WindowYs as $j => $valWindowYs) {
                                if (($this->WindowYs[$j] > $sizeTop) && ($this->WindowYs[$j] < $sizeTop + $value->width)) {
                                    if ($this->PanelsWithInfill) {
                                        $RealWidth = $value->panel_size;
                                        if ($key == count($panels) - 1) {
                                            $BottomPosition = round(($RealWidth - $this->WindowSizes[$j]['Y']) / 2, 0, PHP_ROUND_HALF_EVEN);
                                        } else {
                                            $BottomPosition = round(($value->width - $this->WindowSizes[$j]['Y']) / 2, 0, PHP_ROUND_HALF_EVEN);
                                        }
                                    } else {
                                        $BottomPosition = round(($value->width - $this->WindowSizes[$j]['Y']) / 2, 0, PHP_ROUND_HALF_EVEN);
                                    }
                                    $leftPostion = $this->WindowPaddings[$j];
                                    for ($l = 1; $l <= $this->WindowCounts[$j]; $l++) {
                                        $Elements[] = array("ФрезерованиеПанелей_Слева" . $l => $leftPostion);
                                        $Elements[] = array("ФрезерованиеПанелей_Снизу" . $l => $BottomPosition);
                                        $Elements[] = array("ФрезерованиеПанелей_Слева" . $l => $this->WindowPosition);
                                        $leftPostion += $this->WindowNewSteps[$j] + $this->WindowSizes[$j]['X'];
                                    }
                                }
                            }
                            $Operations[] = array(
                                "Code"     => "CB0000020",
                                "Elements" => $Elements
                            );
                        }
                    }
                }
                $size += $value->width;
            }
        }

        return $Operations;
    }

    public function getLink($Elements, $link)
    {
        $array = array();
        foreach ($Elements as $key => $value) {
            if ($value->specificationItem) {
                if ($value->specificationItem->tag == $link) {
                    $array[] = $value;
                }
            }
        }

        return $array;
    }

    public function __get($name)
    {
        if (isset($this->container[$name])) {
            return $this->container[$name];
        } else {
            return null;
        }
    }

    public function getTitle($Elements, $link)
    {
        $array = array();
        foreach ($Elements as $key => $value) {
            if ($value->specification->title == $link) {
                $array[] = $value;
            }
        }

        return $array;
    }


    public function kII($value)
    {
        $f = floor($value / 7);
        if ($f % 2 != 0) {
            $f++;
        }

        return $f;
    }
}