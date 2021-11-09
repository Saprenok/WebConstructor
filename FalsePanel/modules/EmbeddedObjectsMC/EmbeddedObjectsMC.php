<?php

/**
 * Встраиваемые объекты
 * PHP version 5.5
 * @category Yii
 * @author   Charnou Vitaliy <graffov87@gmail.com>
 */
class EmbeddedObjectsMC extends AbstractModelCalculation
{

    /**
     * Переменная хранит имя класса
     * 
     * @var string 
     */
    public $nameModule = 'EmbeddedObjectsMC';

    /**
     * Алгоритм
     * 
     * @return bool
     */
    public function Algorithm()
    {
        if ($this->ObjectType == 'wicket') {
            $this->DestinationWidth = $this->ShieldWidth;
            $this->DestinationHeight = $this->ShieldHeight;
            if ($this->RemoveRestrictions == 1) {
                $this->LocationTypeY = 4;
                $this->LocationTypeX = 4;
            } else {
                $this->LocationTypeY = 1;
            }
        } elseif ($this->ObjectType == 'window') {
            $this->DestinationWidth = $this->ShieldWidth;
            if (array_key_exists($this->ObjectPanels, $this->ShieldPanels))
                $this->DestinationHeight = $this->ShieldPanels[$this->ObjectPanels];
            else
                return "01";
            $this->LocationTypeY = 3;
        }
        if ($this->ObjectRecommendedLeft <= $this->ObjectMinLeft) {
            $this->ObjectRecommendedLeft = $this->ObjectMinLeft;
        }
        if ($this->ObjectRecommendedRight < $this->ObjectMinRight) {
            $this->ObjectRecommendedRight = $this->ObjectMinRight;
        }
        if ($this->ObjectRecommendedTop < $this->ObjectMinTop) {
            $this->ObjectRecommendedTop = $this->ObjectMinTop;
        }
        if ($this->ObjectRecommendedBottom < $this->ObjectMinBottom) {
            $this->ObjectRecommendedBottom = $this->ObjectMinBottom;
        }
        if ($this->ObjectType == 'wicket') {
            switch ($this->LocationTypeX) {
                case "1" :
                    $this->ObjectPaddingX = $this->ObjectMinLeft;
                    break;
                case "2" :
                    $this->ObjectPaddingX = $this->DestinationWidth - $this->ObjectMinLeft;
                    break;
                case "3" :
                    $this->ObjectPaddingX = ($this->DestinationWidth - $this->ObjectMinLeft + $this->ObjectDefaultWidth) / 2;
                    break;
                case "4" :
                    $this->ObjectPaddingX = $this->ObjectPaddingX;
                    break;
            }
        }
        $CalculatedLeft = null;
        $CalculatedBottom = null;
        $CalculatedWidth = null;
        $CalculatedHeight = null;
        $CalculatedRecommendedLeft = null;
        $CalculatedRecommendedRight = null;
        if ($this->RemoveRestrictions == 0) {
            if ($this->ObjectType == 'wicket') {
                $this->WicketInstalled = 1;
                $emModel = NomenclatureModel::model()->find(array(
                    'condition' => 'article=:article',
                    'params' => array(':article' => $this->ObjectPartNumber)
                ));
                $model = NomenclatureEmbeddedElementsModel::model()->find(array(
//                    'condition' => 'nomenclature_id=:article',        //@todo: существует 2 калитки с одним кодом номенклатуры CB000149052 , переделал на выбор по id 
//                    'params' => array(':article' => $emModel->code)
                    'condition' => 'id=:article',
                    'params' => array(':article' => $this->PartTypeSelected)
                ));
                $this->WicketType = $model->type;
                $Height = 0;
                if ($this->WicketType == "Калитка") {
                    for ($i = $this->ShieldPanelCount; $i >= 1; $i--) {
                        if ($i == $this->ShieldPanelCount) {
                            $Height = $Height + $this->ShieldPanels[$i] - 130;
                        } elseif ($i == 1) {
                            if ($Height + $this->ShieldPanels[$i] - 150 > $this->ObjectDefaultHeight) {
                                $Height = $Height + max($this->ObjectDefaultHeight - $Height, 200);
                            } else {
                                $Height = $Height + $this->ShieldPanels[$i] - 150;
                            }
                        } else {
                            if ($Height + $this->ShieldPanels[$i] - 150 > $this->ObjectDefaultHeight) {
                                $Height = $Height + max($this->ObjectDefaultHeight - $Height, 200);
                                break;
                            } elseif ((($Height + $this->ShieldPanels[$i] - 150 >= $this->ObjectDefaultHeight - 80) && ($Height + $this->ShieldPanels[$i] - 150 <= $this->ObjectDefaultHeight)) || (($i == 2) && ($this->ShieldTopPanel < 440))) {
                                $Height = $Height + $this->ShieldPanels[$i] - 150;
                                break;
                            } else {
                                $Height = $Height + $this->ShieldPanels[$i];
                            }
                        }
                    }
                    $CalculatedBottom = 135;
                    $CalculatedHeight = $Height;
                } else {
                    if ($this->WicketType == "Калитка v2") {
                        for ($i = $this->ShieldPanelCount; $i >= 1; $i--) {
                            if ($i == $this->ShieldPanelCount) {
                                $Height = $Height + $this->ShieldPanels[$i] - 75;
                            } elseif ($i == 1) {
                                $Height = $Height + $this->ShieldPanels[$i] - 75;
                            } else {
                                if (($Height + $this->ShieldPanels[$i] - 100 >= $this->ObjectDefaultHeight - 115) || ($i == 2)) {
                                    $Height = $Height + $this->ShieldPanels[$i] - 100;
                                    break;
                                } else {
                                    $Height = $Height + $this->ShieldPanels[$i];
                                }
                            }
                        }
                        $CalculatedBottom = 75;
                        $CalculatedHeight = $Height;
                    } elseif (in_array($this->WicketType, array(
                                "Калитка v3",
                                "Калитка v4"
                            ))
                    ) {
                        for ($i = $this->ShieldPanelCount; $i >= 1; $i--) {
                            if ($i == $this->ShieldPanelCount) {
                                $Height = $Height + $this->ShieldPanels[$i] - 75;
                            } elseif ($i == 1) {
                                if ($Height + $this->ShieldPanels[$i] - 150 > $this->ObjectDefaultHeight) {
                                    $Height = $Height + max($this->ObjectDefaultHeight - $Height, 200);
                                } else {
                                    $Height = $Height + $this->ShieldPanels[$i] - 150;
                                }
                            } else {
                                if ($Height + $this->ShieldPanels[$i] - 150 > $this->ObjectDefaultHeight) {
                                    $Height = $Height + max($this->ObjectDefaultHeight - $Height, 200);
                                    break;
                                } elseif ((($Height + $this->ShieldPanels[$i] - 150 >= $this->ObjectDefaultHeight - 80) && ($Height + $this->ShieldPanels[$i] - 150 <= $this->ObjectDefaultHeight)) || (($i == 2) && ($this->ShieldTopPanel < 440))) {
                                    $Height = $Height + $this->ShieldPanels[$i] - 150;
                                    break;
                                } else {
                                    $Height = $Height + $this->ShieldPanels[$i];
                                }
                            }
                        }
                        $CalculatedBottom = 75;
                        $CalculatedHeight = $Height;
                    } elseif (in_array($this->WicketType, array(
                                "Калитка v5",
                                "Калитка v5cz"
                            ))
                    ) {
                        for ($i = $this->ShieldPanelCount; $i >= 1; $i--) {
                            if ($i == $this->ShieldPanelCount) {
                                $Height = $Height + $this->ShieldPanels[$i] - 0;
                            } elseif ($i == 1) {
                                if ($Height + $this->ShieldPanels[$i] - 150 > $this->ObjectDefaultHeight) {
                                    $Height = $Height + max($this->ObjectDefaultHeight - $Height, 200);
                                } else {
                                    $Height = $Height + $this->ShieldPanels[$i] - 150;
                                }
                            } else {
                                if ($Height + $this->ShieldPanels[$i] - 150 > $this->ObjectDefaultHeight) {
                                    $Height = $Height + max($this->ObjectDefaultHeight - $Height, 200);
                                    break;
                                } elseif ((($Height + $this->ShieldPanels[$i] - 150 >= $this->ObjectDefaultHeight - 80) && ($Height + $this->ShieldPanels[$i] - 150 <= $this->ObjectDefaultHeight)) || (($i == 2) && ($this->ShieldTopPanel < 440))) {
                                    $Height = $Height + $this->ShieldPanels[$i] - 150;
                                    break;
                                } else {
                                    $Height = $Height + $this->ShieldPanels[$i];
                                }
                            }
                        }
                        $CalculatedBottom = 20;
                        $CalculatedHeight = $Height;  
                    } elseif ($this->WicketType = "Калитка v4 стандарт") {
                        $Height += 2;
                        for ($i = $this->ShieldPanelCount; $i >= 1; $i--) {
                            if ($i == $this->ShieldPanelCount) {
                                $Height = $Height + $this->ShieldPanels[$i] - 75;
                            } elseif ($i == 1) {
                                $Height = $Height + 310;
                                break;
                            } else {
                                if ($Height + 310 > $this->ObjectDefaultHeight) {
                                    $Height = $Height + 310;
                                    break;
                                } elseif ((($Height + 310 >= $this->ObjectDefaultHeight - 80) && ($Height + 310 <= $this->ObjectDefaultHeight)) || (($i == 2) && ($this->ShieldTopPanel < 440))) {
                                    $Height = $Height + 310;
                                    break;
                                } else {
                                    $Height = $Height + $this->ShieldPanels[$i] + $this->ShieldPanelPadding;
                                }
                            }
                        }
                        $CalculatedBottom = 75;
                        $CalculatedHeight = $Height;
                    }
                }
            }
        }
        if ($CalculatedLeft != null) {
            $this->ObjectMinLeft = $CalculatedLeft;
        }
        if ($CalculatedWidth != null) {
            $this->ObjectDefaultWidth = $CalculatedWidth;
        }
        if ($CalculatedHeight != null) {
            $this->ObjectDefaultHeight = $CalculatedHeight;
        }
        if ($CalculatedRecommendedLeft != null) {
            $this->ObjectRecommenededLeft = $CalculatedRecommendedLeft;
        }
        if ($this->CalculatedRecommendedRight != null) {
            $this->ObjectRecommenededRight = $CalculatedRecommendedRight;
        }
        if ($CalculatedBottom != null) {
            $UseExactBottom = 1;
            $EmbeddedExactBottom = $CalculatedBottom + $this->ObjectDefaultHeight;
        } else {
            $UseExactBottom = 0;
            $EmbeddedExactBottom = 0;
        }
        if ($this->ObjectPaddingY == 0) {
            $this->ObjectPaddingY = $this->ObjectMinTop;
        }
        if ($this->RemoveRestrictions == 1) {
            if ($this->ObjectMinWidth > 0) {
                if ($this->DestinationWidth < $this->ObjectMinWidth) {
                    return "01";
                } elseif ($this->DestinationWidth < $this->ObjectDefaultWidth) {
                    $this->ObjectDefaultWidth = round($this->DestinationWidth - ($this->ObjectMinLeft + $this->ObjectMinRight), 1);
                }
            } else {
                if ($this->DestinationWidth < $this->ObjectDefaultWidth) {
                    return "01";
                }
            }
            if (($this->ObjectMaxWidth == 0) || ($this->ObjectMaxWidth > $this->DestinationWidth)) {
                $this->ObjectMaxWidth = $this->DestinationWidth;
            }
            if ($this->ObjectMinHeight > 0) {
                if ($this->DestinationHeight < $this->ObjectMinHeight) {
                    return "01";
                } elseif ($this->DestinationHeight < $this->ObjectDefaultHeight) {
                    $this->ObjectDefaultHeight = round($this->DestinationHeight - ($this->ObjectMinTop + $this->ObjectMinBottom), 1);
                }
            } else {
                if ($this->DestinationHeight < $this->ObjectDefaultHeight) {
                    return "01";
                }
            }
            if (($this->ObjectMaxHeight == 0) || ($this->ObjectMaxHeight > $this->DestinationHeight)) {
                $this->ObjectMaxHeight = $this->DestinationHeight;
            }
        } else {
            if ($this->ObjectMinWidth > 0) {
                if ($this->DestinationWidth < ($this->ObjectMinWidth + $this->ObjectMinLeft + $this->ObjectMinRight)) {
                    return "01";
                } elseif ($this->DestinationWidth < $this->ObjectDefaultWidth + $this->ObjectMinLeft + $this->ObjectMinRight) {
                    $this->ObjectDefaultWidth = round($this->DestinationWidth - ($this->ObjectMinLeft + $this->ObjectMinRight), 1);
                }
            } else {
                if ($this->DestinationWidth < $this->ObjectDefaultWidth + $this->ObjectMinLeft + $this->ObjectMinRight) {
                    return "01";
                }
            }
            if (($this->ObjectMaxWidth == 0) || ($this->ObjectMaxWidth > $this->DestinationWidth - ($this->ObjectMinLeft + $this->ObjectMinRight))) {
                $this->ObjectMaxWidth = $this->DestinationWidth - ($this->ObjectMinLeft + $this->ObjectMinRight);
            }
            if ($this->ObjectMinHeight > 0) {
                if ($this->DestinationHeight < $this->ObjectMinHeight + $this->ObjectMinTop + $this->ObjectMinBottom) {
                    return "01";
                } elseif ($this->DestinationHeight < $this->ObjectDefaultHeight + $this->ObjectMinTop + $this->ObjectMinBottom) {
                    $this->ObjectDefaultHeight = round($this->DestinationHeight - ($this->ObjectMinTop + $this->ObjectMinBottom), 1);
                }
            } else {
                if ($this->DestinationHeight < $this->ObjectDefaultHeight + $this->ObjectMinTop + $this->ObjectMinBottom) {
                    return "01";
                }
            }
            if (($this->ObjectMaxHeight == 0) || ($this->ObjectMaxHeight > $this->DestinationHeight - ($this->ObjectMinTop + $this->ObjectMinBottom))) {
                $this->ObjectMaxHeight = $this->DestinationHeight - ($this->ObjectMinTop + $this->ObjectMinBottom);
            }
        }
        if ($this->ObjectIsRadius > 0) {
            $ObjectWidth = $this->ObjectRadius;
            $ObjectHeight = $this->ObjectRadius;
        } else {
            $ObjectWidth = $this->ObjectDefaultWidth;
            $ObjectHeight = $this->ObjectDefaultHeight;
        }
        if (!is_array(Yii::app()->container->EmbeddedObjects) || (count(Yii::app()->container->EmbeddedObjects) == 0)) {
            $EmbeddedObjects = array();
        } else {
            $EmbeddedObjects = Yii::app()->container->EmbeddedObjects;
        }
        if (Yii::app()->container->EmbeddedObjectsCount == 0) {
            $EmbeddedObjectsCount = 0;
        } else {
            $EmbeddedObjectsCount = (Yii::app()->container->EmbeddedObjectsCount);
        }
        if (Yii::app()->container->EmbeddedObjectsCounts == 0) {
            $EmbeddedObjectsCounts = 0;
            $EmbeddedObjects = array();
        } else {
            $EmbeddedObjectsCounts = (Yii::app()->container->EmbeddedObjectsCounts);
        }
        $this->WicketInstalled = Yii::app()->container->WicketInstalled;
        $this->WicketType = Yii::app()->container->WicketType;
        $this->Wicket1Installed = Yii::app()->container->Wicket1Installed;
        $this->Wicket2Installed = Yii::app()->container->Wicket2Installed;
        $this->Wicket3Installed = Yii::app()->container->Wicket3Installed;
        $this->Wicket4Installed = Yii::app()->container->Wicket4Installed;
        $this->Wicket4StInstalled = Yii::app()->container->Wicket4StInstalled;
        $this->Wicket5Installed = Yii::app()->container->Wicket5Installed;
        $this->Wicket5czInstalled = Yii::app()->container->Wicket5czInstalled;
        $this->WicketPadding = Yii::app()->container->WicketPadding;
        $this->WicketLocation = Yii::app()->container->WicketLocation;
        $this->WicketWidth = Yii::app()->container->WicketWidth;
        $this->WicketHeight = Yii::app()->container->WicketHeight;
        $this->WicketMinLeft = Yii::app()->container->WicketMinLeft;
        $this->WicketMinRight = Yii::app()->container->WicketMinRight;
        $this->WicketMinTop = Yii::app()->container->WicketMinTop;
        $this->WicketMinBottom = Yii::app()->container->WicketMinBottom;
        $this->WicketMinWidth = Yii::app()->container->WicketMinWidth;
        $this->WicketMinHeight = Yii::app()->container->WicketMinHeight;
        $this->WicketMaxWidth = Yii::app()->container->WicketMaxWidth;
        $this->WicketMaxHeight = Yii::app()->container->WicketMaxHeight;
        $this->WicketIsInner = Yii::app()->container->WicketIsInner;
        $this->WicketName = Yii::app()->container->WicketName;
        $this->WicketRecommendedLeft = Yii::app()->container->WicketRecommendedLeft;
        $this->WicketRecommendedTop = Yii::app()->container->WicketRecommendedTop;
        $this->WicketRecommendedRight = Yii::app()->container->WicketRecommendedRight;
        $this->WicketRecommendedBottom = Yii::app()->container->WicketRecommendedBottom;
        $this->WicketDefault = Yii::app()->container->WicketDefault;
        $this->WicketRecommended = Yii::app()->container->WicketRecommended;
        $this->WicketHingesLocation = Yii::app()->container->WicketHingesLocation;
        $this->WicketHingesLocationTranslate = Yii::t("steps", $this->WicketHingesLocation);
        $this->WicketFringingColor = Yii::app()->container->WicketFringingColor;
        $this->WicketDirection = Yii::app()->container->WicketDirection;
        $this->WicketPusher = Yii::app()->container->WicketPusher;
        $this->WicketPeephole = Yii::app()->container->WicketPeephole;
        $this->WicketPartNumbers = Yii::app()->container->WicketPartNumbers;
        $this->WicketMinDistance = Yii::app()->container->WicketMinDistance;
        $this->WindowCount = Yii::app()->container->WindowCount;
        $this->WindowPaddings = Yii::app()->container->WindowPaddings;
        $this->WindowLocations = Yii::app()->container->WindowLocations;
        $this->WindowDefaults = Yii::app()->container->WindowDefaults;
        $this->WindowRecommendations = Yii::app()->container->WindowRecommendations;
        $this->WindowSteps = Yii::app()->container->WindowSteps;
        $this->WindowPanels = Yii::app()->container->WindowPanels;
        $this->WindowPartNumbers = Yii::app()->container->WindowPartNumbers;
        $this->WindowSizes = Yii::app()->container->WindowSizes;
        $this->WindowCounts = Yii::app()->container->WindowCounts;
        $this->WindowAutoCalc = Yii::app()->container->WindowAutoCalc;
        $this->WindowMin = Yii::app()->container->WindowMin;
        $this->WindowMax = Yii::app()->container->WindowMax;
        $this->WindowRecommended = Yii::app()->container->WindowRecommended;
        $this->WindowIsRadius = Yii::app()->container->WindowIsRadius;
        $this->WindowRadius = Yii::app()->container->WindowRadius;
        $this->WindowCount = Yii::app()->container->WindowCount;
        $this->WindowCardId = Yii::app()->container->WindowCardId;
        $this->WindowName = Yii::app()->container->WindowName;
        $this->WindowMinDistance = Yii::app()->container->WindowMinDistance;
        $object = NomenclatureEmbeddedElementsModel::model()->findByPk($this->ObjectArticle);
        if (is_object($object)) {
            $ObjectArticle = $object->title;
        } else {
            $ObjectArticle = "";
        }
        
        $this->WindowOrder = Yii::app()->container->WindowOrder;
        
        if ($this->ObjectType == "window") {
            if ($this->ObjectCount > 0) {
                if (Yii::app()->container->EmbeddedObjectsCount == 0) {
                    $counter = 1;
                } else {
                    $counter = (Yii::app()->container->EmbeddedObjectsCount) + 1;
                }
                if ($this->Elements) {
                    $counter = $this->Elements;
                }
                if (!is_array(Yii::app()->container->WindowPaddings) || (count(Yii::app()->container->WindowPaddings) == 0)) {
                    $Padding = array();
                } else {
                    $Padding = Yii::app()->container->WindowPaddings;
                }
                if (!is_array(Yii::app()->container->WindowLocations) || (count(Yii::app()->container->WindowLocations) == 0)) {
                    $Location = array();
                } else {
                    $Location = Yii::app()->container->WindowLocations;
                }
                if (!is_array(Yii::app()->container->WindowDefaults) || (count(Yii::app()->container->WindowDefaults) == 0)) {
                    $Default = array();
                } else {
                    $Default = Yii::app()->container->WindowDefaults;
                }
                if (!is_array(Yii::app()->container->WindowRecommendations) || (count(Yii::app()->container->WindowRecommendations) == 0)) {
                    $Recommended = array();
                } else {
                    $Recommended = Yii::app()->container->WindowRecommendations;
                }
                if (!is_array(Yii::app()->container->WindowSteps) || (count(Yii::app()->container->WindowSteps) == 0)) {
                    $Step = array();
                } else {
                    $Step = Yii::app()->container->WindowSteps;
                }
                if (!is_array(Yii::app()->container->WindowPanels) || (count(Yii::app()->container->WindowPanels) == 0)) {
                    $panels = array();
                } else {
                    $panels = Yii::app()->container->WindowPanels;
                }
                if (!is_array(Yii::app()->container->WindowPartNumbers) || (count(Yii::app()->container->WindowPartNumbers) == 0)) {
                    $numbers = array();
                } else {
                    $numbers = Yii::app()->container->WindowPartNumbers;
                }
                if (!is_array(Yii::app()->container->WindowSizes) || (count(Yii::app()->container->WindowSizes) == 0)) {
                    $Size = array();
                } else {
                    $Size = Yii::app()->container->WindowSizes;
                }
                if (!is_array(Yii::app()->container->WindowCounts) || (count(Yii::app()->container->WindowCounts) == 0)) {
                    $counts = array();
                } else {
                    $counts = Yii::app()->container->WindowCounts;
                }
                if (!is_array(Yii::app()->container->WindowAutoCalc) || (count(Yii::app()->container->WindowAutoCalc) == 0)) {
                    $autocalc = array();
                } else {
                    $autocalc = Yii::app()->container->WindowAutoCalc;
                }
                if (!is_array(Yii::app()->container->WindowMin) || (count(Yii::app()->container->WindowMin) == 0)) {
                    $WindowMin = array();
                } else {
                    $WindowMin = Yii::app()->container->WindowMin;
                }
                if (!is_array(Yii::app()->container->WindowMax) || (count(Yii::app()->container->WindowMax) == 0)) {
                    $WindowMax = array();
                } else {
                    $WindowMax = Yii::app()->container->WindowMax;
                }
                if (!is_array(Yii::app()->container->WindowRecommended) || (count(Yii::app()->container->WindowRecommended) == 0)) {
                    $WindowRecommended = array();
                } else {
                    $WindowRecommended = Yii::app()->container->WindowRecommended;
                }
                if (!is_array(Yii::app()->container->WindowRadius) || (count(Yii::app()->container->WindowRadius) == 0)) {
                    $WindowRadius = array();
                } else {
                    $WindowRadius = Yii::app()->container->WindowRadius;
                }
                if (!is_array(Yii::app()->container->WindowRadius) || (count(Yii::app()->container->WindowIsRadius) == 0)) {
                    $WindowIsRadius = array();
                } else {
                    $WindowIsRadius = Yii::app()->container->WindowIsRadius;
                }
                if (!is_array(Yii::app()->container->WindowName) || (count(Yii::app()->container->WindowName) == 0)) {
                    $WindowName = array();
                } else {
                    $WindowName = Yii::app()->container->WindowName;
                }
                if (!is_array(Yii::app()->container->WindowCardId) || (count(Yii::app()->container->WindowCardId) == 0)) {
                    $WindowCardId = array();
                } else {
                    $WindowCardId = Yii::app()->container->WindowCardId;
                }
                if (!is_array(Yii::app()->container->WindowMinDistance) || (count(Yii::app()->container->WindowMinDistance) == 0)) {
                    $WindowMinDistance = array();
                } else {
                    $WindowMinDistance = Yii::app()->container->WindowMinDistance;
                }
                for ($i = 1; $i <= $this->ObjectCount; $i++) {
                    $Location[$counter][$i]['X'] = $this->LocationTypeX;
                    $Location[$counter][$i]['Y'] = $this->LocationTypeY;
                    $Padding[$counter][$i]['X'] = $this->ObjectPaddingX;
                    $Padding[$counter][$i]['Y'] = $this->ObjectPaddingY;
                    $Default[$counter][$i]['Left'] = $this->ObjectMinLeft;
                    $Default[$counter][$i]['Top'] = $this->DestinationHeight - $this->ObjectMinTop;
                    $Default[$counter][$i]['Right'] = $this->DestinationWidth - $this->ObjectMinRight - $ObjectWidth;
                    $Default[$counter][$i]['Bottom'] = $this->ObjectDefaultHeight + $this->ObjectMinBottom;
                    $Recommended[$counter][$i]['Left'] = $this->ObjectRecommendedLeftWindow[$i];
                    $Recommended[$counter][$i]['Top'] = $this->DestinationHeight - $this->ObjectRecommendedTopWindow[$i];
                    $Recommended[$counter][$i]['Right'] = $this->DestinationWidth - $this->ObjectRecommendedRightWindow[$i] - $ObjectWidth;
                    $Recommended[$counter][$i]['Bottom'] = $this->ObjectDefaultHeight + $this->ObjectRecommendedBottomWindow[$i];
                    $Step[$counter][$i] = $this->ObjectStep;
                    $panels[$counter][$i] = $this->ObjectPanels;
                    $numbers[$counter][$i] = $this->ObjectPartNumber;
                    $Size[$counter][$i]['X'] = $ObjectWidth;
                    $Size[$counter][$i]['Y'] = $ObjectHeight;
                }
                $WindowMin[$counter]['Left'] = $this->ObjectMinLeft;
                $WindowMin[$counter]['Right'] = $this->ObjectMinRight;
                $WindowMin[$counter]['Top'] = $this->ObjectMinTop;
                $WindowMin[$counter]['Bottom'] = $this->ObjectMinBottom;
                $WindowMin[$counter]['Width'] = $this->ObjectMinWidth;
                $WindowMin[$counter]['Height'] = $this->ObjectMinHeight;
                $WindowMax[$counter]['Width'] = $this->ObjectMaxWidth;
                $WindowMax[$counter]['Height'] = $this->ObjectMaxHeight;
                $WindowMin[$counter]['Left'] = $this->ObjectMinLeft;
                $WindowMin[$counter]['Right'] = $this->ObjectMinRight;
                $WindowMin[$counter]['Top'] = $this->ObjectMinTop;
                $WindowMin[$counter]['Bottom'] = $this->ObjectMinBottom;
                $WindowRecommended[$counter]['Left'] = $this->ObjectRecommendedLeftWindow;
                $WindowRecommended[$counter]['Right'] = $this->ObjectRecommendedRightWindow;
                $WindowRecommended[$counter]['Top'] = $this->ObjectRecommendedTopWindow;
                $WindowRecommended[$counter]['Bottom'] = $this->ObjectRecommendedBottomWindow;
                $ObjectIsRadius = (int)$this->ObjectIsRadius;
                if ($ObjectIsRadius != 1) {
                    $ObjectIsRadius = 0;
                } 
                $ObjectRadius = (int)$this->ObjectRadius;
                if (!is_int($ObjectRadius)) {
                    $ObjectRadius = 0;
                } 
                
                $WindowIsRadius[$counter] = $ObjectIsRadius;
                $WindowRadius[$counter] = $ObjectRadius;
                $WindowName[$counter] = $ObjectArticle;
                $WindowMinDistance[$counter] = $this->ObjectMinDistance;
                $this->WindowMin = $WindowMin;
                $this->WindowMax = $WindowMax;
                $this->WindowPaddings = $Padding;
                $this->WindowLocations = $Location;
                $this->WindowDefaults = $Default;
                $this->WindowRecommendations = $Recommended;
                $this->WindowSteps = $Step;
                $this->WindowPanels = $panels;
                $this->WindowPartNumbers = $numbers;
                $this->WindowSizes = $Size;
                $this->WindowName = $WindowName;
                $counts[$counter] = $this->ObjectCount;
                $autocalc[$counter] = $this->AutoCalc;
                $this->WindowRecommended = $WindowRecommended;
                $this->WindowAutoCalc = $autocalc;
                $this->WindowCounts = $counts;
                $this->WindowCount = Yii::app()->container->WindowCount + 1;
                $this->WindowOrder = $counter;
                $this->WindowRadius = $WindowRadius;
                $this->WindowIsRadius = $WindowIsRadius;
                $this->WindowMinDistance = $WindowMinDistance;
                $emModel = NomenclatureModel::model()->find(array(
                    'condition' => 'article=:article',
                    'params' => array(':article' => $this->ObjectPartNumber)
                ));
                $dataReader = Yii::app()->db->createCommand()->select('card_id')->from('nomenclature nom')->leftJoin('nomenclature_embedded_elements nee', 'nee.nomenclature_id = nom.code')->leftJoin('nomenclature_embedded_elements_products neep', 'neep.element_id = nee.id')->leftJoin('nomenclature_embeded_element_product_card neepc', 'neepc.product_id = neep.id')->where('nom.article="' . $this->ObjectPartNumber . '"')->query();
                $groups = $dataReader->readAll();
                foreach ($groups as $value) {
                    $WindowCardId[$counter][] = $value['card_id'];
                }
                $this->WindowCardId = $WindowCardId;
            }
            $EmbeddedObjectsCount++;
            $EmbeddedObjectsCounts++;
            $EmbeddedObjects[$counter]['number'] = $counter;
            $EmbeddedObjects[$counter]['type'] = "window";
            $EmbeddedObjects[$counter]['ObjectTypeSelected'] = $this->ObjectTypeSelected;
            $EmbeddedObjects[$counter]['PartTypeSelected'] = $this->PartTypeSelected;
        }
        $this->EmbeddedObjects = $EmbeddedObjects;
        $this->EmbeddedObjectsCount = $EmbeddedObjectsCount;
        $this->EmbeddedObjectsCounts = $EmbeddedObjectsCounts;
        
        return true;
    }

    /**
     * Название модуля
     * 
     * @return string
     */
    public function getTitle()
    {
        return 'Встраиваемые объекты';
    }

}
