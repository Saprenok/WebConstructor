<?php

/**
 * Чертежи
 * PHP version 5.5
 * @category Yii
 */
class DrawingsMC extends AbstractModelCalculation
{

    /**
     * Переменная хранит имя класса
     * @var string
     */
    public $nameModule = 'DrawingsMC';
    public $error = '';

    /**
     * Алгоритм
     * @return bool
     */
    public function Algorithm()
    {
        $NameProductForCards = array();
        //$NameProductForCards[] = array('loc' => 532);
        $NameProductForCards[] = array('str' => ' ');
        //$typePan = (Yii::app()->container->AlFacing || Yii::app()->container->AlFacing2010) ? ', ' . Yii::app()->container->Loc(262) : '';
        $typePan = (Yii::app()->container->AlFacing) ? ', ' . 'Premium' : '';
        $typePan = (Yii::app()->container->AlFacing2010) ? ', ' . 'Premium 2010' : '';
        $productModel = ProductModel::model()->findByPk(Yii::app()->container->productId);
        $productTitle = $productModel ? $productModel->title : '';
        $NameProductForCards[] = array('str' => $productTitle);
        /*if (Yii::app()->container->AlFacing || Yii::app()->container->AlFacing2010) {
            $NameProductForCards[] = array('str' => ', ');
            $NameProductForCards[] = array('loc' => 262);
        }*/
        if (Yii::app()->container->AlFacing) {
            $NameProductForCards[] = array('str' => ', Premium');
        }
        if (Yii::app()->container->AlFacing2010) {
            $NameProductForCards[] = array('str' => ', Premium 2010');
        }
        if (Yii::app()->container->TypeF == "Панорамная панель") {
            $NameProductForCards[] = array('str' => ': Панорамная панель');
        }
        if (Yii::app()->container->TypeF == "Алюминиевый") {
            $Index = Yii::t('steps',Yii::app()->container->Index);
            $NameProductForCards[] = array('str' => ' ');
            $NameProductForCards[] = array('str' => 'Щит: ');
            $NameProductForCards[] = array('str' => array('source' => 'orders', 'word' => $Index));
        }
        $this->NameProductForCards = $NameProductForCards;
        
        if (($this->DriveD600KITInstalled) || ($this->DriveD1000Installed && $this->GuidesSK3600Installed)) {
            $this->DriveGuides = 3780;
            $this->DriveLength1 = 360;
            $this->DriveLength2 = 145;
        } elseif (($this->DriveD1000KITInstalled) || (($this->DriveD600Installed) && ($this->GuidesSK4600Installed))) {
            $this->DriveGuides = 4780;
            $this->DriveLength1 = 360;
            $this->DriveLength2 = 145;
        } elseif ($this->DriveSE750KITInstalled) {
            $this->DriveGuides = 3775;
            $this->DriveLength1 = 324;
            $this->DriveLength2 = 115;
        } elseif ($this->DriveSE1200KITInstalled) {
            $this->DriveGuides = 4775;
            $this->DriveLength1 = 324;
            $this->DriveLength2 = 115;
        } elseif (($this->DriveISE500KITInstalled) || ($this->DriveSE500KITInstalled)) {
            $this->DriveGuides = 3100;
            $this->DriveLength1 = 310;
            $this->DriveLength2 = 112;
        } else {
            $this->DriveGuides = null;
            $this->DriveLength1 = null;
            $this->DriveLength2 = null;
        }
        $this->OKT1Installed = 0;
        $this->OKT2Installed = 0;
        $this->OKTName = "";
        $Specification = SpecificationModel::model()->findAll('product_id=' . Yii::app()->container->productId);
        $specification_item = array();
        foreach ($Specification as $key => $value) {
            $specification_item[] = $value->id;
        }

        $specItem = new SpecificationItemModel();
        $specItem->setKey($this->key);
        $Element1 = $specItem->getSpecificationItemByTag("OKT72,КОК", $this->key, Yii::app()->container->productId);
        if (count($Element1) > 0) {
            $this->OKTName = "DH-OKT72";
            if ($Element1[0]['position'] == 1) {
                $this->OKT1Installed = 1;
            } else if ($Element1[0]['position'] == 2) {
                $this->OKT2Installed = 1;
            }
        }
        $Element2 = $specItem->getSpecificationItemByTag("OKT73,КОК", $this->key, Yii::app()->container->productId);
        if (count($Element2) > 0) {
            $this->OKTName = "DH-OKT73";
            if ($Element2[0]['position'] == 1) {
                $this->OKT1Installed = 1;
            } else if ($Element2[0]['position'] == 2) {
                $this->OKT2Installed = 1;
            }
        }
        $Wb = 70;
        $this->WicketParameter1 = $this->ShieldWidth - $Wb * 2;
        $Lc = $this->WicketX - ($Wb + 70) - 50;
        $Nn = round($Lc / 270);
        $this->WicketParameter2 = round($Lc / $Nn);
        $Lc2 = $this->ShieldWidth - ($this->WicketX + $this->WicketWidth + $Wb + 70 + 50);
        $Nn2 = round($Lc2 / $this->WicketParameter2);
        if ($Nn2 <= 0) $Nn2 = 1;
        $this->WicketParameter3 = round($Lc2 / $Nn2);

        //        Направляющие профили
        $ElementCodes = array(15558, 103917, 15559, 103924, 15560, 103929, 138719, 138720, 'CB000003472', 'CB000222690', 'CB000208176');
        $this->GuideProfilesAsString = $this->algorithmA($ElementCodes);
        //        Угловые стойки
        $ElementCodes = array(15561, 103934, 137902, 137914, 138729, 138726, 138727, 138728);
        $this->CornerRacksAsString = $this->algorithmA($ElementCodes);
        //        Уголки
        $ElementCodes = array(15562);
        $this->CornersAsString = $this->algorithmA($ElementCodes);
        //        С-профили
        $ElementCodes = array(15557, 103605, 138718, 100374);
        $this->CProfilesAsString = $this->algorithmA($ElementCodes);
        //        Валы
        $ElementCodes = array('CB000004291', 100378, 84, 85, 86, 11951, 11956, 14123, 16593, 104915, 106462, 111373, 111817, 111818, 119945, 120161, 123394, 123395, 130264, 131692, 131693, 134217, 134778, 134856, 136856, 11353, 11952, 16681, 104871, 118377, 141849, 141850, 141581, 141852, 141853);
        $this->ShaftsAsString = $this->algorithmA($ElementCodes);

        $iteration = $this->drawings();

        //  Примечание логотипа для карты "Раскрой панелей"
        if ($this->RegularSandwich) {
            $this->LogoString1 = Yii::app()->container->Loc('460020');
        } else {
            $this->LogoString1 = Yii::app()->container->Loc('460021');
        }

        //  Примечание логотипа для карты "Монтажник"
        if ($this->RegularSandwich) {
            $this->LogoString2 = Yii::app()->container->Loc('460022');
        } else {
            if ($this->AluminiumHandleInstalled && !$this->WicketInstalled) {
                $this->LogoString2 = Yii::app()->container->Loc('460024');
            } else {
                $this->LogoString2 = Yii::app()->container->Loc('460023');
            }
        }
        
        if (Yii::app()->container->RegionEurope) $this->drawingsRigelCProfile($iteration);
        
        
        return true;
    }

    /**
     * Поиск по ярылку
     *
     * @param $Elements
     * @param $link
     *
     * @return array
     */
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

    /**
     * Название модуля
     * @return string
     */
    public function getTitle()
    {
        return 'Чертежи';
    }

    /**
     * А. Формирование строки с длинами элементов
     * @param array $ElementCodes Список кодов элементов номенклатуры
     * @return string Строка с длинами элементов
     */
    private function algorithmA($ElementCodes)
    {
        array_walk($ElementCodes, function(&$value, $index) {
            $value = str_pad($value, 11, '0', STR_PAD_LEFT);
        });
        //  Получить элемент номенклатуры
        $Elements = Yii::app()->db->createCommand()
                ->select('ops.id, ops.nomenclature_id, ops.length, ops.count')
                ->from('order_product_specification ops')
                ->leftJoin('nomenclature n', 'n.code = ops.nomenclature_id')
                ->where('ops.is_deleted=0 AND ops.amount>0 AND ops.order_product_id=' . Yii::app()->container->orderProductId . ' AND (n.code IN ("' . implode('","', $ElementCodes) . '") OR n.parent_code IN ("' . implode('","', $ElementCodes) . '"))')
                ->queryAll();
        //  Значения по умолчанию
        $ElementsAsString = "";
        $ElementCount = 0;
        if (count($Elements)) { //  Элемент существует
            $ElementCount = count($Elements);
            //  Перебираем все элементы
            foreach ($Elements as $N => $Element) {
                //  Добавить точку с запятой
                if ($N > 0) {
                    $ElementsAsString = $ElementsAsString . "; ";
                }
                //  Получить элемент
                $Element = $Elements[$N];
                //  Добавить элемент
                $ElementsAsString .= $Element['count'] . " x " . $Element['length'] . "  ± 2 " . Yii::app()->container->Loc(1773);
            }
        }
        //  Нет элементов
        if ($ElementCount == 0) {
            $ElementsAsString = $ElementsAsString . "  ± 2 " . Yii::app()->container->Loc(1773);
        }
        return $ElementsAsString;
    }

    /**
     * Отбор чертежей по циклу
     */
    private function drawings()
    {
        $Drawing = array();
        $iteration = 0;

        /**
         * Раскрой для линии
         */
        if (!$this->RegionEurope && !$this->RegionMoscow && !$this->RegionChina) {
            $Drawing[++$iteration]['map'] = 'Раскрой для линии';
            $Drawing[$iteration]['orientation'] = 'Портрентная';
            $Drawing[$iteration]['Loc'] = 'Раскрой для линии';
            $Drawing[$iteration]['Container'] = array(
                'X' => 100,
                'Y' => 850
            );
            $X = 0;
            $Y = 0;
            $svgs = array();
            for ($i = $this->ShieldPanelCount; $i >= 1; $i--) {
                //если больще 6-ти панелей то таблицы в соседний ряд отрисовываем
                //if ($this->ShieldPanelCount - $i == 6) {
                //переход на новый ряд по координате Y
                if ($Y > 600) {
                    $X = 250;
                    $Y = 0;
                }
                $svgs[] = array(
                    'name' => '',
                    'type' => 'text',
                    'X' => $X,
                    'Y' => $Y,
                    'Width' => 2000,
                    'Font' => 5,
                    'Style' => 'Полужирный',
                    'color' => "#000064",
                    'text' => Yii::app()->container->Loc('110183') . " №" . $i
                );
                $Y+=24;
                $svgs[] = array(
                    'name' => '',
                    'type' => 'table',
                    'X' => $X,
                    'Y' => $Y,
                    'heightline' => 127,
                    'Font' => 5,
                    'Style' => "Обычный",
                    'color' => "#000064",
                    'countline' => count($this->OperationsCounts[$i]),
                    'countrows' => 5,
                    'class' => 'DrawingsMC_Cutting', //  Класс для доп. оформления HTML
                    'text' => $this->OperationsTables[$i]
                );
                $Y+= 15 * count($this->OperationsTables[$i]);
            }
            $Drawing[$iteration]['svgs'] = $svgs;
        }

        /**
         * Фрезерование
         */
        if ($this->RegionMoscow && ((($this->WicketInstalled && $this->WicketType <> "Калитка v2") || $this->WindowCount > 0) || (($this->StepHandleInstalled || $this->StepHandleWithLogoInstalled) && $this->WicketDHF09 !== 0))) {
            $Drawing[++$iteration]['map'] = 'Фрезерование';
            $Drawing[$iteration]['orientation'] = 'Ландшафтная';
            $Drawing[$iteration]['Loc'] = Yii::app()->container->Loc('600');
            $Drawing[$iteration]['Container'] = array(
                'X' => 100,
                'Y' => 100
            );
            $X = 20;
            $Y = 20;
            $svgs = array();
            foreach ($this->WindowPanels as $i => $panel) {
                $WindowPanelNumber = $panel[1];
                $panelWidth = $this->ShieldWidth;
                if ($this->PanoramicPanel) {
                    $ShieldPanels = array();
                    $ShieldPanels = array_merge(array_reverse($this->SandTopPanels), $this->ShieldPanels, array_reverse($this->SandBottomPanels));
                    array_unshift($ShieldPanels, "forDel");
                    unset($ShieldPanels[0]);
                    $PanelHeight = $ShieldPanels[$WindowPanelNumber];
                } else {
                    $PanelHeight = $this->ShieldPanels[$WindowPanelNumber];
                }
                $ProgramName = $PanelHeight . "-win";
                if ($this->WindowCounts[$i] > 1) {
                    if ($this->ShieldWithAntiJamProtection) {
                        $ProgramName .= "-new";
                    } elseif ($this->RegularSandwich) {
                        $ProgramName .= "-alt";
                    } elseif ($this->PanelWithInfills) {
                        $ProgramName .= "-f";
                    }
                }
                if ($this->WindowCounts[$i] == 1) {
                    $svgs[] = array(
                        'name' => '',
                        'type' => 'table',
                        'X' => $X,
                        'Y' => $Y,
                        'heightline' => '',
                        'Font' => '',
                        'Style' => 'Полужирный',
                        'color' => '#000000',
                        'countline' => 6,
                        'countrows' => 2,
                        'class' => 'DrawingsMC_Milling', //  Класс для доп. оформления HTML
                        'head' => 'Программа: ' . $ProgramName,
                        'text' => array(
                            "Панель:" => $PanelHeight,
                            "Длина L, мм:" => $panelWidth,
                            "Ширина A, мм:" => $this->WindowSizes[$i][1]['X'],
                            "Высота B, мм" => $this->WindowSizes[$i][1]['Y'],
                            "Расстояние C, мм:" => $this->WindowNewPadding[$i][1],
                            "Кол-во окон N:" => $this->WindowCounts[$i]
                        )
                    );
                } else {
                    $svgs[] = array(
                        'name' => '',
                        'type' => 'table',
                        'X' => $X,
                        'Y' => $Y,
                        'heightline' => '',
                        'Font' => '',
                        'Style' => 'Полужирный',
                        'color' => '#000000',
                        'countline' => 7,
                        'countrows' => 2,
                        'class' => 'DrawingsMC_Milling', //  Класс для доп. оформления HTML
                        'head' => 'Программа: ' . $ProgramName,
                        'text' => array(
                            "Панель:" => $PanelHeight,
                            "Длина L, мм:" => $panelWidth,
                            "Ширина A, мм:" => $this->WindowSizes[$i][1]['X'],
                            "Высота B, мм" => $this->WindowSizes[$i][1]['Y'],
                            "Расстояние C, мм:" => $this->WindowNewPadding[$i][1],
                            "Шаг C, мм:" => $this->WindowNewSteps[$i][1],
                            "Кол-во окон N:" => $this->WindowCounts[$i]
                        )
                    );
                }
                $Y = $Y + 120;
                if ($Y >= 120 * 4) {
                    $X = $X + 140;
                    $Y = 20;
                }
            }
            if ($this->WicketInstalled && !in_array($this->WicketType, array("Калитка v3", "Калитка v4", "Калитка v4 стандарт", "Калитка v4 стандарт CZ", "Калитка v5", "Калитка v5 cz"))) {
                $svgs[] = array(
                    'name' => '',
                    'type' => 'table',
                    'X' => $X,
                    'Y' => $Y,
                    'heightline' => '',
                    'Font' => '',
                    'Style' => 'Полужирный',
                    'color' => '#000000',
                    'countline' => 3,
                    'countrows' => 2,
                    'class' => 'DrawingsMC_Milling', //  Класс для доп. оформления HTML
                    'head' => 'Программа: kalit1(нижняя)',
                    'text' => array(
                        "Расстояние C, мм:" => $this->WicketX,
                        "Глубина паза H, мм:" => $this->NV1,
                        "Ширина паза L, мм" => $this->WicketWidth,
                    )
                );
            }
            $Y = $Y + 120;
            if ($Y > 120 * 4) {
                $X = $X + 140;
                $Y = 20;
            }
            if ($this->WicketInstalled) {
                $svgs[] = array(
                    'name' => '',
                    'type' => 'table',
                    'X' => $X,
                    'Y' => $Y,
                    'heightline' => '',
                    'Font' => '',
                    'Style' => 'Полужирный',
                    'color' => '#000000',
                    'countline' => 3,
                    'countrows' => 2,
                    'class' => 'DrawingsMC_Milling', //  Класс для доп. оформления HTML
                    'head' => 'Программа: kalit2(верхняя)',
                    'text' => array(
                        "Расстояние C, мм:" => $this->WicketX,
                        "Глубина паза H, мм:" => $this->VV1,
                        "Ширина паза L, мм" => $this->WicketWidth,
                    )
                );
            }
            $Drawing[$iteration]['svgs'] = $svgs;
        }
        
        $this->Drawings = $Drawing;
        return $iteration;
    }
    
    public function getTextDataTable()
    {
        $result = array();
        
        $cnt = Yii::app()->container;
        $cprofil = "X";
        $result[] = array(
            "Rigel",
            "X",
        );
        
        $result[] = array(
            "c-profil",
            $cprofil,
        );
        return $result;
    }
    /**
     * Формирование таблички 2х2 по отображению Ригеля и С-Профиля
     */
    private function drawingsRigelCProfile($iteration)
    {
        if (is_array($this->Drawings)) {
            $Drawing = $this->Drawings;
        } else {
            $Drawing = array();
        }
        if (empty($iteration)) $iteration = 0;
        $textDataTable = $this->getTextDataTable();
        
        $X = 0;
        $Y = 470;
        $Drawing[++$iteration]['map'] = 'Раскрой панелей';
        //$Drawing[$iteration]['orientation'] = 'Портретная';
        //$Drawing[$iteration]['Loc'] = Yii::app()->container->Loc('591') . ' 1';
        $Drawing[$iteration]['Container'] = array(
            'X' => 0,
            'Y' => 0
        );
        $Drawing[$iteration]['svgs'][] = array(
            'name' => '',
            'type' => 'table',
            'X' => $X,
            'Y' => $Y,
            'heightline' => '',
            'Font' => '',
            'Style' => 'Полужирный',
            'color' => '#000000',
            'countline' => count($textDataTable),
            'countrows' => 2,
            'class' => 'DrawingsMCRigelCProfile_Control', //  Класс для доп. оформления HTML
            'head' => '',
            'text' => $textDataTable
        );
        
        $this->Drawings = $Drawing;
    }
}
