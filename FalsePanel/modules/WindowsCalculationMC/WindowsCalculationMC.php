<?php

/**
 * Модуль расчета окон
 * PHP version 5.5
 * @category Yii
 */
class WindowsCalculationMC extends AbstractModelCalculation
{

    /**
     * Переменная хранит имя класса
     * 
     * @var string 
     */
    public $nameModule = 'WindowsCalculationMC';
    private $counter;

    /**
     * Алгоритм
     * 
     * @return bool
     */
    public function Algorithm()
    {
//        if ($this->WindowCount > 0) {
        if (count($this->WindowCounts) > 0) {
            $this->counter = $this->WindowOrder;
            if (!is_array(Yii::app()->container->WindowNewPadding) || (count(Yii::app()->container->WindowNewPadding) == 0)) {
                $windowPadding = array();
            } else {
                $windowPadding = Yii::app()->container->WindowNewPadding;
            }
            if (!is_array(Yii::app()->container->WindowNewSteps) || (count(Yii::app()->container->WindowNewSteps) == 0)) {
                $windowSteps = array();
            } else {
                $windowSteps = Yii::app()->container->WindowNewSteps;
            }
            if (!is_array(Yii::app()->container->WindowXs) || (count(Yii::app()->container->WindowXs) == 0)) {
                $WindowXs = array();
            } else {
                $WindowXs = Yii::app()->container->WindowXs;
            }
            if (!is_array(Yii::app()->container->WindowYs) || (count(Yii::app()->container->WindowYs) == 0)) {
                $WindowYs = array();
            } else {
                $WindowYs = Yii::app()->container->WindowYs;
            }
            $ObjectPaddingY = $this->ObjectPaddingY;
            if ($ObjectPaddingY == 0) {
                $ObjectPaddingY = $this->ObjectMinTopWindows;
            }
            if (!is_array(Yii::app()->container->WindowPaddings) || (count(Yii::app()->container->WindowPaddings) == 0)) {
                $WindowPaddings = array();
            } else {
                $WindowPaddings = Yii::app()->container->WindowPaddings;
            }
            if (!is_array(Yii::app()->container->WindowSteps) || (count(Yii::app()->container->WindowSteps) == 0)) {
                $WindowSteps = array();
            } else {
                $WindowSteps = Yii::app()->container->WindowSteps;
            }
            if (array_key_exists($this->WindowOrder, $this->WindowCounts)) {
            
                for ($i = 1; $i <= $this->WindowCounts[$this->WindowOrder]; $i++) {
                    $Location = $this->WindowLocations[$this->counter][$i]; // for inner method
                    $PaddingX = $this->WindowPaddings[$this->counter][$i]['X']; // $WindowPadding['X']
                    $PaddingY = $this->WindowPaddings[$this->counter][$i]['Y']; // $WindowPadding['Y']
                    $Left = $this->WindowDefaults[$this->counter][$i]['Left']; // $WindowDefault['Left']
                    $Top = $this->WindowDefaults[$this->counter][$i]['Top']; // $WindowDefault['Top']
                    $Right = $this->WindowDefaults[$this->counter][$i]['Right']; // $WindowDefault['Right']
                    $Bottom = $this->WindowDefaults[$this->counter][$i]['Bottom']; // $WindowDefault['Bottom']
                    $WindowDefault = $this->WindowDefaults[$this->counter][$i]; // for inner method
                    $Recommended = $this->WindowRecommendations[$this->counter][$i];
                    $MinDistance = $this->WindowMinDistance[$this->counter];
                    $Step = $this->WindowSteps[$this->counter][$i];
                    $Panel = $this->WindowPanels[$this->counter][$i];
                    $Panels = $this->ShieldPanels[$Panel];
                    $PartNumber = $this->WindowPartNumbers[$this->counter][$i];
                    $Width = $this->WindowSizes[$this->counter][$i]['X']; // $WindowSize['X']
                    $Height = $this->WindowSizes[$this->counter][$i]['Y']; // $WindowSize['Y']
                    $WindowSize = $this->WindowSizes[$this->counter][$i]; // for inner method
                    //  $Count         = $this->WindowCounts[$counter][$i];
                    $Count = $this->WindowCounts[$this->WindowOrder];
                    //        Рассчитать окно
                    switch ($Location['X']) {
                        case "1" :
                            $X = $Recommended['Left'];
                            break;
                        case "2" :
                            $X = $Recommended['Right'];
                            break;
                        case "3" :
                            $X = $this->bround(($this->ShieldWidth - ($Count * $WindowSize['X'])) / 2);
                            break;
                        case "4" :
                            $X = $PaddingX;
                            break;
                    }
                    switch ($Location['Y']) {
                        case "1" :
                            $Y = $Recommended['Bottom'];
                            break;
                        case "2" :
                            $Y = $Recommended['Top'];
                            break;
                        case "3" :
                            $Y = $this->bround(($Panels - $WindowSize['Y']) / 2);
                            break;
                        case "4" :
                            $Y = $PaddingY;
                            break;
                    }
                    $WicketInstalled = $this->CheckWicket($Y, $Height, $Panel);
                    if ($this->WindowAutoCalc[$this->counter] == 0) {
                        $NewStep = $Step;
                        if ($this->ShieldInfillCount > 0) {
                            $Remainder = $this->bround(($NewStep - $this->ShieldInfillStep) % ($this->ShieldInfillWidth + $this->ShieldInfillStep));
                            $NewStep = ($Remainder >= $NewStep / 2) ? $NewStep + $NewStep - $Remainder : $NewStep - $Remainder;
                            $NewStep += $this->ShieldInfillWidth - $WindowSize['X'];
                        }
                        if ($WindowSize['X'] * $Count + $NewStep * ($Count - 1) > $this->ShieldWidth) {
                            //    echo "Нельзя поставить столько окон!";
                            $this->DeleteWindow();

                            return "031";
                        }
                        if (!Yii::app()->container->RemoveRestrictions) {
                            if ($NewStep < $Step || ($NewStep * ($Count - 1) + $WindowSize['X'] * $Count + $WindowDefault['Left'] > $WindowDefault['Right'] + $WindowSize['X'])) {
                                //          echo "Нельзя поставить столько окон с таким расстоянием между ними!";
                                $this->DeleteWindow();

                                return "032";
                            }
                        }
                    } else {
                        $NewPadding = -1;
                        $NewStep = $this->autoCalcStep($WindowDefault, $WindowSize, $NewPadding, $Count, $MinDistance, $WicketInstalled);
                        if (!$NewStep) {
                            $this->DeleteWindow();

                            return "033";
                        }
                    }
                    switch ($Location['X']) {
                        case "1" :
                            list($NewPadding, $NewStep) = $this->calculateLocationXeqLeft($WindowDefault, $WindowSize, $PaddingX, $Count, $MinDistance, $NewStep, $WicketInstalled);
                            break;
                        case "2" :
                            list($NewPadding, $NewStep) = $this->calculateLocationXeqRight($WindowDefault, $WindowSize, $PaddingX, $Count, $MinDistance, $NewStep, $WicketInstalled);
                            break;
                        case "3" :
                            list($NewPadding, $NewStep) = $this->calculateLocationXeqCenter($WindowDefault, $WindowSize, $PaddingX, $Count, $MinDistance, $NewStep, $WicketInstalled);
                            break;
                        case "4" :
                            list($NewPadding, $NewStep) = $this->calculateLocationXmarginLeft($WindowDefault, $WindowSize, $PaddingX, $Count, $MinDistance, $NewStep, $WicketInstalled);
                            break;
                    }
                    if (($NewPadding < $Left) || ($NewPadding - 1 > $Right - ($Count - 1) * ($Width + $NewStep))) {
                        //    echo "Отступ слева вне допустимых интервалов!";
                        //$this->DeleteWindow();

                        //return "034";
                    }
                    $windowPadding[$this->counter][$i] = $NewPadding;
                    //$windowSteps[$this->counter][$i] = $NewStep;
                    $WindowXs[$this->counter][$i] = $X;
                    $WindowYs[$this->counter][$i] = $Y;
                    $WindowPaddings[$this->counter][$i]['X'] = $NewPadding;
                    if (!empty($ObjectPaddingY[$this->WindowCount])) {
                        $WindowPaddings[$this->counter][$i]['Y'] = $ObjectPaddingY[$this->WindowCount];
                    } else {
                        $WindowPaddings[$this->counter][$i]['Y'] = $this->ObjectMinTop;
                    }
                    $WindowSteps[$this->counter][$i] = $NewStep;
                }
            }
            $this->WindowNewPadding = $windowPadding;
            $this->WindowPaddings = $WindowPaddings;
            $this->WindowNewSteps = $WindowSteps;
            $this->WindowSteps = $WindowSteps;
            $this->WindowXs = $WindowXs;
            $this->WindowYs = $WindowYs;
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
        return 'Расчет окон';
    }

    /**
     * Алгоритм расчета для Location.X = "Слева"
     *
     * @param array   $WindowDefault
     * @param array   $WindowSize
     * @param array   $WindowPadding
     * @param integer $Count
     * @param integer $MinDistance
     *
     * @return bool|array
     */
    private function calculateLocationXeqLeft($WindowDefault, $WindowSize, $PaddingX, $Count, $MinDistance, $NewStep, $WicketInstalled)
    {
        $Left = $WindowDefault['Left'];
        $Width = $WindowSize['X'];
        if ($this->ShieldInfillCount > 0) {
            $NewPadding = $this->bround((($this->ShieldWidth + $this->ShieldInfillStep) % ($this->ShieldInfillWidth + $this->ShieldInfillStep)) / 2 + ($this->ShieldInfillWidth - $Width) / 2);
        } else {
            if (!$WicketInstalled) {
                $NewPadding = $Left;
            } elseif (($this->WicketX + $this->WicketWidth > $Left) && ($Left + $Width > $this->WicketX)) {
                $NewPadding = $this->bround($this->WicketX + ($this->WicketWidth - $Width) / 2);
            } else {
                $NewPadding = $Left;
            }
        }
        if ($this->WindowAutoCalc[$this->counter] == 1) {
            // @todo:	Вызвать алгоритм автоматического расчета отступа. Смотри пункт З.
            $NewStep = $this->autoCalcStep($WindowDefault, $WindowSize, $NewPadding, $Count, $MinDistance, $WicketInstalled);
            if (empty($NewStep)) {
                $this->DeleteWindow();

                return "035";
            }
        }

        return array(
            $NewPadding,
            $NewStep
        );
    }

    /**
     * Алгоритм расчета для Location.X = "Справа"
     *
     * @param array   $WindowDefault
     * @param array   $WindowSize
     * @param array   $WindowPadding
     * @param integer $Count
     * @param integer $MinDistance
     *
     * @return bool|array
     */
    private function calculateLocationXeqRight($WindowDefault, $WindowSize, $PaddingX, $Count, $MinDistance, $NewStep, $WicketInstalled)
    {
        $Left = $WindowDefault['Left'];
        $Width = $WindowSize['X'];
        $Right = $WindowDefault['Right'];
        if ($this->ShieldInfillCount > 0) {
            $NewPadding = $this->bround(($this->ShieldWidth - ((($this->ShieldWidth + $this->ShieldInfillStep) % ($this->ShieldInfillWidth + $this->ShieldInfillStep)) / 2 + ($this->ShieldInfillWidth - $Width) / 2) - $Width));
        } else {
            if (!$WicketInstalled) {
                $NewPadding = $Right;
            } elseif ($this->WicketX + $this->WicketWidth > $Right && $Right + $Width > $this->WicketX) {
                $NewPadding = $this->bround($this->WicketX + ($this->WicketWidth - $Width) / 2);
            } else {
                $NewPadding = $Right;
            }
        }
        if ($this->WindowAutoCalc[$this->counter] == 1) {
            // @todo:	Вызвать алгоритм автоматического расчета отступа. Смотри пункт З.
            $NewStep = $this->autoCalcStep($WindowDefault, $WindowSize, $NewPadding, $Count, $MinDistance, $WicketInstalled);
            if (empty($NewStep)) {
                $this->DeleteWindow();

                return "036";
            }
        }
        $NewPadding -= ($Count - 1) * ($Width + $NewStep);

        return array(
            $NewPadding,
            $NewStep
        );
    }

    /**
     * Алгоритм расчета для Location.X = "По центру"
     *
     * @param array   $WindowDefault
     * @param array   $WindowSize
     * @param array   $WindowPadding
     * @param integer $Count
     * @param integer $MinDistance
     *
     * @return bool|array
     */
    private function calculateLocationXeqCenter($WindowDefault, $WindowSize, $PaddingX, $Count, $MinDistance, $NewStep, $WicketInstalled)
    {
        $Width = $WindowSize['X'];
        if ($this->ShieldInfillCount > 0) {
            $NewPadding = $this->bround((($this->ShieldWidth + $this->ShieldInfillStep) % ($this->ShieldInfillWidth + $this->ShieldInfillStep)) / 2 + ($this->ShieldInfillWidth - $Width) / 2);
            $NewPadding += $this->bround(($this->ShieldInfillWidth + $this->ShieldInfillStep) * floor((($this->ShieldWidth + $this->ShieldInfillStep) / ($this->ShieldInfillWidth + $this->ShieldInfillStep) - $Count) / 2));
            if ($this->WindowAutoCalc[$this->counter] == 1) {
                $NewStep = $this->autoCalcStep($WindowDefault, $WindowSize, $NewPadding, $Count, $MinDistance, $WicketInstalled);
                if (empty($NewStep)) {
                    $this->DeleteWindow();

                    return "037";
                }
            }
        } else {
            if (!$WicketInstalled || $Count == 1) {
                $NewPadding = $this->bround(($this->ShieldWidth - $Width * $Count - $NewStep * ($Count - 1)) / 2);
            } else {
                $NewPadding = $this->bround(min(($this->WicketX + ($this->WicketWidth - $Width) / 2), ($this->ShieldWidth - ($this->WicketX + ($this->WicketWidth - $Width) / 2 + $Width))));
            }
        }
        if ($this->WindowAutoCalc[$this->counter] == 1) {
            $NewStep = $this->autoCalcStep($WindowDefault, $WindowSize, $NewPadding, $Count, $MinDistance, $WicketInstalled);
            if (empty($NewStep)) {
                $this->DeleteWindow();

                return "038";
            }
        }
        $NewPadding = $this->bround(($this->ShieldWidth - ($Count * $Width + ($Count - 1) * $NewStep)) / 2);

        return array(
            $NewPadding,
            $NewStep
        );
    }

    /**
     * Алгоритм расчета для Location.X = "Задать отступ слева"
     *
     * @param array   $WindowDefault
     * @param array   $WindowSize
     * @param array   $WindowPadding
     * @param integer $Count
     * @param integer $MinDistance
     *
     * @return bool|array
     */
    private function calculateLocationXmarginLeft($WindowDefault, $WindowSize, $PaddingX, $Count, $MinDistance, $NewStep, $WicketInstalled)
    {
        $NewPadding = $PaddingX;
        if ($this->WindowAutoCalc[$this->counter] == 1) {
            $NewStep = $this->autoCalcStep($WindowDefault, $WindowSize, $NewPadding, $Count, $MinDistance, $WicketInstalled);
            if (empty($NewStep)) {
                $this->DeleteWindow();

                return "039";
            }
        }

        return array(
            $NewPadding,
            $NewStep
        );
    }

    /**
     * Автоматический подбор отступа между окнами
     *
     * @param array   $WindowDefault
     * @param array   $WindowSize
     * @param integer $NewPadding
     * @param integer $Count
     * @param integer $MinDistance
     *
     * @return bool|float|int|number
     */
    private function autoCalcStep($WindowDefault, $WindowSize, $NewPadding, $Count, $MinDistance, $WicketInstalled)
    {
        $Left = $WindowDefault['Left'];
        $Right = $WindowDefault['Right'];
        $Width = $WindowSize['X'];
        if ($Count <= 1) {
            $NewStep = -1;

            return $NewStep;
        }
        if ($NewPadding == -1) {
            $NewStep = $this->bround(($this->ShieldWidth - $Width * $Count) / ($Count + 1));
        } else {
            if (!$WicketInstalled || $Count < 2) {
                $NewStep = $this->bround(($this->ShieldWidth - $Width * $Count - $NewPadding * 2) / ($Count - 1));
            } else {
                $NewStep = $this->bround(abs($NewPadding - ($this->WicketX + ($this->WicketWidth - $Width) / 2)));
                $Remainder = $this->bround($NewStep / ($Width + $MinDistance));
                if (empty($Remainder)) {
                    $NewStep = $this->bround(abs(($this->ShieldWidth - $NewPadding) - $Width - ($this->WicketX + ($this->WicketWidth - $Width) / 2)));
                    if (empty($NewStep)) {
                        if (floor($Count / 2) == $Count / 2) {
                            $NewStep = $this->bround(($this->ShieldWidth - $Width * $Count - $this->WicketWidth) / $Count + $this->WicketWidth);
                        } else {
                            $NewStep = $this->bround(($this->ShieldWidth - $Width * $Count) / ($Count + 1));
                        }
                    } else {
                        $NewStep = $this->bround($NewStep / ($Count - 1) - $Width);
                    }
                } elseif ($Remainder > $Count) {
                    $NewStep = $this->bround($NewStep / ($Count - 1) - $Width);
                } elseif ($Remainder == 1) {
                    $NewStep = $this->bround($NewStep - $Width);
                } else {
                    $NewStep = $this->bround($NewStep / ($Remainder - 1) - $Width);
                }
            }
        }
        if ($this->ShieldInfillCount > 0) {
            $Remainder = $this->bround(($NewStep - $this->ShieldInfillStep) % ($this->ShieldInfillStep + $this->ShieldInfillWidth));
            $NewStep = $this->bround($NewStep - ($Remainder - ($this->ShieldInfillWidth - $Width)));
        }
        if ($Width * $Count + $NewStep * ($Count - 1) > $this->ShieldWidth) {
            //  echo "Нельзя поставить столько окон!";
            //$this->DeleteWindow();

            //return "040";
        }
        if ($Count > 1 && ($NewStep < $MinDistance || $NewStep + 25 < $Left || $this->ShieldWidth - ($NewStep + 25) > $Right + $Width)) {
            $NewStep = $this->bround(($this->ShieldWidth - $Width * $Count - 2 * max($Left, ($this->ShieldWidth - ($Right + $Width)))) / ($Count - 1));
            if (!Yii::app()->container->RemoveRestrictions) {
                if ($NewStep < $MinDistance) {
                    //    echo "Нельзя поставить столько окон!";
                    $this->DeleteWindow();

                    return "041";
                }
            }
        }

        return $NewStep;
    }
    
    private function CheckWicket($WindowY, $WindowHeight, $Panel)
    {
        $WindowPanel = $this->ShieldPanelCount - $Panel + 1;
        if (!$this->WicketInstalled || !in_array($WindowPanel, $this->WicketPanels)) {
            return false;
        }
        $WicketTop = $this->WicketY;
        $WicketBottom = $WicketTop + $this->WicketHeight;
        $WindowTop = $WindowY;
        $WindowBottom = $WindowTop + $WindowHeight;
        for ($N = 1;$N <= $this->ShieldPanelCount;$N++) {
            if ($N == $Panel && $WindowBottom > $WicketTop && $WindowTop < $WicketBottom) {
                return true;
            }
            $WindowTop+=$this->ShieldPanels[$N] + $this->ShieldPanelPadding;
            $WindowBottom+=$this->ShieldPanels[$N] + $this->ShieldPanelPadding;           
        }
        return false;
    }

    private function DeleteWindow()
    {
        $counter = $this->counter;
        $WindowPaddings = Yii::app()->container->WindowPaddings;
        $WindowLocations = Yii::app()->container->WindowLocations;
        $WindowDefaults = Yii::app()->container->WindowDefaults;
        $WindowRecommendations = Yii::app()->container->WindowRecommendations;
        $WindowSteps = Yii::app()->container->WindowSteps;
        $WindowPanels = Yii::app()->container->WindowPanels;
        $WindowPartNumbers = Yii::app()->container->WindowPartNumbers;
        $WindowSizes = Yii::app()->container->WindowSizes;
        $WindowCounts = Yii::app()->container->WindowCounts;
        unset($WindowPaddings[$counter]);
        unset($WindowLocations[$counter]);
        unset($WindowDefaults[$counter]);
        unset($WindowRecommendations[$counter]);
        unset($WindowSteps[$counter]);
        unset($WindowPanels[$counter]);
        unset($WindowPartNumbers[$counter]);
        unset($WindowSizes[$counter]);
        unset($WindowCounts[$counter]);
        Yii::app()->container->WindowPaddings = $WindowPaddings;
        Yii::app()->container->WindowLocations = $WindowLocations;
        Yii::app()->container->WindowDefaults = $WindowDefaults;
        Yii::app()->container->WindowRecommendations = $WindowRecommendations;
        Yii::app()->container->WindowSteps = $WindowSteps;
        Yii::app()->container->WindowPanels = $WindowPanels;
        Yii::app()->container->WindowPartNumbers = $WindowPartNumbers;
        Yii::app()->container->WindowSizes = $WindowSizes;
        Yii::app()->container->WindowCounts = $WindowCounts;
        Yii::app()->container->WindowCount = $counter - 1;
    }

}
