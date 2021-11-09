<?php

/**
 * Модуль интерфейса Проем
 * PHP version 5.5
 * @category Yii
 * @author   Charnou Vitaliy <graffov87@gmail.com>
 */
class TypePanelsColorShieldMI extends AbstractModelInterface
{

    /**
     * Название модуля
     *
     * @var string
     */
    public $nameModule = 'TypePanelsColorShieldMI';

    /**
     * Список модулей, выполняемых до запуска формы
     *
     * @var array
     */
    public $beforeCalculation = array(
        'ShieldMC',
        'OptimalPanelsCuttingMC',
        'PanelsCuttingWithInfillsMC',
    );

    /**
     * Список модулей, которые выполняются при переходе на следующую форму
     *
     * @var array
     */
    public $moduleCalculation = array(
        'FormShieldMC',
        'ShieldColorMC',
    );

    /**
     * Алгоритм
     *
     * @return bool
     */
    public function Algorithm()
    {
        return true;
    }

    /**
     * Функция, которая возвращает имя формы
     *
     * @return string
     */
    public function getTitle()
    {
        return Yii::t('steps', 'Тип панелей и цвет щита');
    }

    /**
     * Очищает список выбраных данных из сессии.
     *
     * @return bool
     */
    public function clearStore()
    {
        Yii::app()->container->setStore(0, 'formPanels');
        Yii::app()->container->setStore(0, 'formPanelsAl');
        Yii::app()->container->setStore(0, 'formPanelsAl2010');

        return false;
    }

    /**
     * Метод отвечающий за графическое отображение раскроя щита. Возвращает
     * строку, содержащую javascript.
     *
     * @return string
     */
    public function getSVG() {
        $path = Yii::getPathOfAlias(Yii::app()->params['uploadModuleSVG']) . DIRECTORY_SEPARATOR . 'ShieldPanels.svg';
        if (is_file($path)) {
            $svg = new SVG($path);
            $str = $svg->getXML();
            $event = 0;
            //Только ShieldMI!
            if (Yii::app()->container->ShieldWidth > 0) {
                $width = Yii::app()->container->ShieldWidth;
            } else {
                $width = Yii::app()->container->Bh;
            }
            if (Yii::app()->container->ShieldRealHeight > 0) {
                $Height = Yii::app()->container->ShieldRealHeight;
            } elseif (Yii::app()->container->ShieldHeight > 0) {
                $Height = Yii::app()->container->ShieldHeight;
            } else {
                $Height = Yii::app()->container->Hh;
            }
            //Конец только ShieldMI!
            $str .= '<script>';
            //Только ShieldMI!
            $str .= '
                //высота части щита сендвич панелей с учетом пропорциональности
                var heightPanels = ' . $Height . ';
                svgInfo("Shield",  "aperture", heightPanels);
                svgInfo("Bh","aperture",' . $width . ');
                var SandPanel = ' . Yii::app()->container->SandPanel . ';
            ';
            //Конец только ShieldMI!
            if (Yii::app()->container->PanoramicPanel) {
                $strTemp = '
                    var heightShield = ' . (Yii::app()->container->ShieldRealHeight /*+ Yii::app()->container->SandTopHeight + Yii::app()->container->SandBottomHeight*/) . ';
                    var widthShield = ' . Yii::app()->container->ShieldWidth . ';
                    var counts =' . (Yii::app()->container->ShieldPanelCount + Yii::app()->container->SandTopCount + Yii::app()->container->SandBottomCount) . ';
                    var panelCount = 0;
                    var panels = {};
                    var SandTopPanels = ' . json_encode(array_reverse(Yii::app()->container->SandTopPanels)) . ';
                    var ShieldPanels = ' . json_encode(Yii::app()->container->ShieldPanels) . ';
                    var SandBottomPanels = ' . json_encode(array_reverse(Yii::app()->container->SandBottomPanels)) . ';
                    for (var i in SandTopPanels) {
                        panelCount++;
                        panels[panelCount] = SandTopPanels[i];
                    }
                    for (var i in ShieldPanels) {
                        panelCount++;
                        panels[panelCount] = parseInt(ShieldPanels[i]);
                    }
                    for (var i in SandBottomPanels) {
                        panelCount++;
                        panels[panelCount] = SandBottomPanels[i];
                    }
                    var panelsCutting = panels;
                    isCut = ' . json_encode(array(
                                    Yii::app()->container->SandTopPanelIsCut,
                                    Yii::app()->container->SandBottomPanelIsCut
                            )) . ';
                ';
            } else {
                $strTemp = '
                    var heightShield = ' . Yii::app()->container->ShieldRealHeight . ';
                    var widthShield = ' . Yii::app()->container->ShieldWidth . ';
                    var counts =' . Yii::app()->container->ShieldPanelCount . ';
                    panels = ' . json_encode(Yii::app()->container->ShieldWholePanels) . ';
                    panelsCutting =' . json_encode(Yii::app()->container->ShieldPanels) . ';
                    isCut = ' . json_encode(array(
                                    Yii::app()->container->ShieldTopPanelIsCut,
                                    Yii::app()->container->ShieldBottomPanelIsCut
                            )) . ';
                ';
            }
            $str .= '

            var svgM = jQuery("#apertureSVG svg g#Shield");
            '.$strTemp.'
            //Закоментировано только для ShieldMI!
            //svgInfo("ShieldWidth","aperture",widthShield);
            //svgInfo("ShieldHeight","aperture",heightShield);
            //реальная высота верхних алюминиевых профилей
            var HeightTopProfiles = ' . Yii::app()->container->HeightTopProfiles . ';
            //реальная высота верхних алюминиевых профилей пик
            var HeightTopProfilesPiki = ' . Yii::app()->container->HeightTopProfilesPiki . ';
            //реальная высота самых верхних труб/вензелей
            var HeightTopTube = ' . Yii::app()->container->HeightTopTube . ';
            //реальная высота основной части алюминиевых профилей щита
            var HeightAlumProfilesShield = ' . Yii::app()->container->HeightAlumProfilesShield . ';
            if (counts > 0 || HeightAlumProfilesShield > 0) {
                var TypeF = "' . Yii::app()->container->TypeF . '";
                //Здесь будет хранится высота алюм. профилей с учетом поправочного коэф. на пропорциональность
                var Height = ' . Yii::app()->container->HeightTopProfiles . ';
                if  (TypeF !== "Алюминиевый") { 
                    HeightTopProfiles = 0;
                    HeightTopProfilesPiki = 0;
                    HeightTopTube = 0;
                    Height = 0;
                }
                var heightShield = ' . Yii::app()->container->ShieldRealHeight . ';
                //высота части щита сендвич панелей с учетом пропорциональности
                var heightPanels = ' . $Height . ';
                var k = 0;
                if (SandPanel) {
                    k = (heightShield + HeightTopProfiles + HeightTopProfilesPiki + HeightTopTube)/900;
                    heightPanels = heightShield/k;
                } else {
                    k = (HeightTopProfiles + HeightTopProfilesPiki + HeightTopTube + HeightAlumProfilesShield)/900;
                    heightPanels = 0;
                    heightShield = 0;
                }

                svgInfo("Shield",  "aperture", heightPanels);
                
                var svgM = jQuery("#apertureSVG svg g#Shield");
                clearElements(svgM,".panels");
                clearElements(svgM,".views");
                var SandTopCount = ' . Yii::app()->container->SandTopCount . ';
                var SandBottomCount = ' . Yii::app()->container->SandBottomCount . ';
                var PanPanelCount = ' . Yii::app()->container->PanPanelCount . ';
                var EdgingProfiles = ' . json_encode(Yii::app()->container->EdgingProfiles) . ';
                var VerticalPanel = ' . Yii::app()->container->VerticalPanel . ';
                var profile = ' . json_encode(Yii::app()->container->AddDetal) . ';
                
                if  (TypeF == "Алюминиевый") { 
                    if (SandPanel)
                        createPanels_FalsePanel(svgM, counts, panels, panelsCutting, heightShield, widthShield, isCut, SandTopCount, SandBottomCount, PanPanelCount, 1, VerticalPanel);
                    if (HeightTopProfiles > 0 || HeightTopProfilesPiki > 0 || HeightAlumProfilesShield > 0 || HeightTopTube > 0)
                        createProfilesAlum(svgM, profile, Height, HeightTopProfiles, HeightTopProfilesPiki, HeightAlumProfilesShield, HeightTopTube, heightShield, widthShield);
                } else {
                    createPanels_FalsePanel(svgM, counts, panels, panelsCutting, heightShield, widthShield, isCut, SandTopCount, SandBottomCount, PanPanelCount, 1, VerticalPanel);
                }
                if  (TypeF == "Для секционных ворот")
                    createProfiles(svgM, EdgingProfiles, heightShield, widthShield);


                var fillsCount = ' . Yii::app()->container->ShieldInfillCount . ';
                if (fillsCount > 0 ) {
                    var fills = ' . json_encode(Yii::app()->container->ShieldInfills) . ';
                    createFills(svgM, fills, heightShield, widthShield);
                }
            ';
            if (Yii::app()->container->EmbeddedObjectsCount > 0) {
                $windowObjects = array();
                $windowParams = array();
                if (Yii::app()->container->WindowCount > 0) {
                    foreach (Yii::app()->container->WindowCounts as $i => $element) {
                        if (Yii::app()->container->WindowCounts[$i]) {
                            $windowObjects[$i]['ObjectType'] = "window";
                            $windowObjects[$i]['RemoveRestrictions'] = 0;
                            $windowObjects[$i]['LocationTypeY'] = Yii::app()->container->WindowLocations[$i][1]['Y'];
                            $windowObjects[$i]['LocationTypeX'] = Yii::app()->container->WindowLocations[$i][1]['X'];
                            $windowObjects[$i]['ObjectDefaultWidth'] = Yii::app()->container->WindowSizes[$i][1]['X'];
                            $windowObjects[$i]['ObjectDefaultHeight'] = Yii::app()->container->WindowSizes[$i][1]['Y'];
                            $windowObjects[$i]['ObjectPaddingX'] = Yii::app()->container->WindowPaddings[$i][1]['X'];
                            $windowObjects[$i]['ObjectPaddingY'] = Yii::app()->container->WindowPaddings[$i][1]['Y'];
                            $windowObjects[$i]['ObjectCount'] = Yii::app()->container->WindowCounts[$i];
                            $windowObjects[$i]['AutoCalc'] = Yii::app()->container->WindowAutoCalc[$i];
                            $windowObjects[$i]['ObjectMinLeft'] = Yii::app()->container->WindowMin[$i]['Left'];
                            $windowObjects[$i]['ObjectMinRight'] = Yii::app()->container->WindowMin[$i]['Right'];
                            $windowObjects[$i]['ObjectMinTop'] = Yii::app()->container->WindowMin[$i]['Top'];
                            $windowObjects[$i]['ObjectMinBottom'] = Yii::app()->container->WindowMin[$i]['Bottom'];
                            $windowObjects[$i]['ObjectMinWidth'] = Yii::app()->container->WindowMin[$i]['Width'];
                            $windowObjects[$i]['ObjectMinHeight'] = Yii::app()->container->WindowMin[$i]['Height'];
                            $windowObjects[$i]['ObjectMaxWidth'] = Yii::app()->container->WindowMax[$i]['Width'];
                            $windowObjects[$i]['ObjectMaxHeight'] = Yii::app()->container->WindowMax[$i]['Height'];
                            $windowObjects[$i]['ObjectStep'] = Yii::app()->container->WindowSteps[$i][1];
                            $windowObjects[$i]['ObjectPartNumber'] = Yii::app()->container->WindowPartNumbers[$i][1];
                            $windowObjects[$i]['ObjectPanels'] = Yii::app()->container->WindowPanels[$i][1];
                            $windowObjects[$i]['ObjectRecommendedLeft'] = Yii::app()->container->WindowRecommended[$i]['Left'];
                            $windowObjects[$i]['ObjectRecommendedRight'] = Yii::app()->container->WindowRecommended[$i]['Right'];
                            $windowObjects[$i]['ObjectRecommendedTop'] = Yii::app()->container->WindowRecommended[$i]['Top'];
                            $windowObjects[$i]['ObjectRecommendedBottom'] = Yii::app()->container->WindowRecommended[$i]['Bottom'];
                            $windowObjects[$i]['ObjectPaddingY'] = Yii::app()->container->WindowPaddings[$i][1]['Y'];
                            $windowObjects[$i]['ObjectRadius'] = Yii::app()->container->WindowRadius[$i];
                            $windowObjects[$i]['ObjectIsRadius'] = Yii::app()->container->WindowIsRadius[$i];
                            $windowObjects[$i]['ObjectMinDistance'] = Yii::app()->container->WindowMinDistance[$i];
                            $windowParams['WindowCounts'][$i] = Yii::app()->container->WindowCounts[$i];
                            $windowParams['WindowPanels'][$i] = Yii::app()->container->WindowPanels[$i];
                            $windowParams['WindowSizes'][$i] = Yii::app()->container->WindowSizes[$i];
                            $windowParams['WindowNewSteps'][$i] = Yii::app()->container->WindowNewSteps[$i];
                            $windowParams['WindowNewPadding'][$i] = Yii::app()->container->WindowNewPadding[$i];
                            $windowParams['WindowXs'][$i] = Yii::app()->container->WindowXs[$i];
                            $windowParams['WindowYs'][$i] = Yii::app()->container->WindowYs[$i];
                            $windowParams['WindowRadius'][$i] = Yii::app()->container->WindowRadius[$i];
                            $windowParams['WindowIsRadius'][$i] = Yii::app()->container->WindowIsRadius[$i];
                            $windowParams['WindowName'][$i] = Yii::app()->container->WindowName[$i];
                        }
                    }
                }
                $str .= '
                    embeddedObjects =' . json_encode(Yii::app()->container->EmbeddedObjects) . ';
                    EmbeddedObjectsCount = ' . Yii::app()->container->EmbeddedObjectsCount . ';
                    EmbeddedObjectsCounts = ' . Yii::app()->container->EmbeddedObjectsCounts . ';
                    textNamePanoramicPanel = "' . Yii::app()->container->textNamePanoramicPanel . '";
                    var windowObjects = ' . json_encode($windowObjects) . ';
                    var ParamObjects = ' . json_encode($windowParams) . ';
                    if(EmbeddedObjectsCount > 0) {
                        var newCounts = EmbeddedObjectsCount;
                        var newCollection = {};
                        var newCollectionObject = {};
                        var newCollectionNomen = {};
                        var ObjectW = {};
                        var coord = {};
                        for(var noviObject in embeddedObjects) {
                            ObjectS = {};
                            ObjectW = {};
                            coord = {};
                            if (embeddedObjects[noviObject].type == "window") {
                                if(windowObjects.length != 0) {
                                    n = embeddedObjects[noviObject].number;
                                    ObjectS.ObjectType = "window";
                                    ObjectS.RemoveRestrictions = 0;
                                    ObjectS.ObjectDefaultWidth = parseInt(windowObjects[n].ObjectDefaultWidth);
                                    ObjectS.ObjectDefaultHeight = parseInt(windowObjects[n].ObjectDefaultHeight);
                                    ObjectS.LocationTypeX = parseInt(windowObjects[n].LocationTypeX);
                                    ObjectS.LocationTypeY = parseInt(windowObjects[n].LocationTypeY);
                                    ObjectS.ObjectPaddingX = parseInt(windowObjects[n].ObjectPaddingX);
                                    ObjectS.ObjectPaddingY = parseInt(windowObjects[n].ObjectPaddingY);
                                    ObjectS.ObjectCount =parseInt(windowObjects[n].ObjectCount);
                                    ObjectS.AutoCalc = parseInt(windowObjects[n].AutoCalc);
                                    ObjectS.ObjectMinLeft = parseInt(windowObjects[n].ObjectMinLeft);
                                    ObjectS.ObjectMinRight = parseInt(windowObjects[n].ObjectMinRight);
                                    ObjectS.ObjectMinTop = parseInt(windowObjects[n].ObjectMinTop);
                                    ObjectS.ObjectMinBottom = parseInt(windowObjects[n].ObjectMinBottom);
                                    ObjectS.ObjectMinWidth = parseInt(windowObjects[n].ObjectMinWidth);
                                    ObjectS.ObjectMinHeight = parseInt(windowObjects[n].ObjectMinHeight);
                                    ObjectS.ObjectMaxWidth = parseInt(windowObjects[n].ObjectMaxWidth);
                                    ObjectS.ObjectMaxHeight = parseInt(windowObjects[n].ObjectMaxHeight);
                                    ObjectS.ObjectStep = parseInt(windowObjects[n].ObjectStep);
                                    ObjectS.ObjectPartNumber = windowObjects[n].ObjectPartNumber;
                                    ObjectS.ObjectPanels = parseInt(windowObjects[n].ObjectPanels);
                                    ObjectS.ObjectRecommendedLeft = parseInt(windowObjects[n].ObjectRecommendedLeft);
                                    ObjectS.ObjectRecommendedRight = parseInt(windowObjects[n].ObjectRecommendedRight);
                                    ObjectS.ObjectRecommendedTop = parseInt(windowObjects[n].ObjectRecommendedTop);
                                    ObjectS.ObjectRecommendedBottom =parseInt(windowObjects[n].ObjectRecommendedBottom);
                                    ObjectS.ObjectPaddingY = parseInt(windowObjects[n].ObjectPaddingY);
                                    ObjectS.ObjectRadius = parseInt(windowObjects[n].ObjectRadius);
                                    ObjectS.ObjectIsRadius = parseInt(windowObjects[n].ObjectIsRadius);
                                    ObjectS.ObjectMinDistance = parseInt(windowObjects[n].ObjectMinDistance);
                                    ObjectW.WindowOrder = n;
                                    ObjectW.WindowCounts = ParamObjects.WindowCounts;
                                    ObjectW.WindowPanels = ParamObjects.WindowPanels;
                                    ObjectW.WindowSizes = ParamObjects.WindowSizes;
                                    ObjectW.WindowRadius = ParamObjects.WindowRadius;
                                    ObjectW.WindowIsRadius = ParamObjects.WindowIsRadius;
                                    ObjectW.WindowName = ParamObjects.WindowName;
                                    coord.WindowNewSteps = ParamObjects.WindowNewSteps;
                                    coord.WindowXs = ParamObjects.WindowXs;
                                    coord.WindowYs = ParamObjects.WindowYs;
                                    coord.WindowNewPadding = ParamObjects.WindowNewPadding;
                                }
                            }
                            var newfill = {};
                            newfill.ShieldPanelsWithInfill = ' . Yii::app()->container->ShieldPanelsWithInfill . ';
                            newfill.ShieldInfillStep = ' . Yii::app()->container->ShieldInfillStep . ';
                            newfill.ShieldInfillWidth = ' . Yii::app()->container->ShieldInfillWidth . ';
                            panels = ' . json_encode(Yii::app()->container->ShieldPanels) . ';
                            var Panel =  parseInt(jQuery("#' . __CLASS__ . '_ObjectPanels").val());
                            var Shield = {};
                            Shield.Height = ' . Yii::app()->container->ShieldRealHeight . ';
                            Shield.Width = ' . Yii::app()->container->ShieldWidth . ';
                            if (jQuery("#' . __CLASS__ . '_RemoveRestrictions").attr("checked") == "checked") {
                                RemoveRestrictions = 1;
                            } else {
                                RemoveRestrictions = 0;
                            }
                            if (jQuery("#' . __CLASS__ . '_AutoCalc").attr("checked") == "checked") {
                                autoCalc = 1;
                            } else {
                                autoCalc = 0;
                            };

                            var x1 = $(svgM).find("rect#Shields").attr("x") * 1;
                            var y1 = $(svgM).find("rect#Shields").attr("y") * 1;
                            var x2 = parseInt(x1) + $(svgM).find("rect#Shields").attr("width") * 1;
                            var y2 = parseInt(y1) + $(svgM).find("rect#Shields").attr("height") * 1;
                            deltaH = Shield.Width / (x2 - x1);
                            deltaV = Shield.Height / (y2 - y1);

                            var newObject = {};
                            if (ObjectS.ObjectType == "window") {
                                newObject = checkWindows(svgM,ObjectS.ObjectPanels,panels,ObjectS,Shield,ObjectS.AutoCalc,ObjectS.RemoveRestrictions,deltaH, deltaV, newCollection,newfill);

                                if (typeof newObject  == "string") {
                                    //simpleAlertDialog(objDataSimpleAlert, newObject);
                                    //break;
                                }
                            }
                            newCollection[noviObject] = newObject;
                            newCollectionObject[noviObject] = ObjectS;
                            newCollectionNomen[noviObject] = {"ObjectTypeSelected": embeddedObjects[noviObject].ObjectTypeSelected,"PartTypeSelected":embeddedObjects[noviObject].PartTypeSelected};
                            if (ObjectS.ObjectType == "window") {
                                if (typeof newObject  != "string") {
                                    if (windowObjects.length != 0) {
                                        addWindow(svgM,heightShield,widthShield,ObjectW,coord,noviObject,0);
                                    }
                                }
                            }
                        }
                    }
                ';
            }
            $str .= '}</script>';

            return $str;
        }
    }

    /**
     * Правила валидации для элементов формы
     *
     * @return array правила валидации, которые будут применены во время вызова {@link validate()}.
     */
    public function rules()
    {
        if (Yii::app()->container->PanoramicPanel) {
            return array();
        } else {
            return array(
                array(
                    'typePanel,design,design,design2,structure,colorOutside,colorInside,typeSize',
                    'required',
                    'message' => Yii::t('steps', 'Пустое значение')
                ),
                array(
                    'AlFacing,AlFacing2010',
                    'boolean'
                )
            );
        }
    }

    /**
     * Метод выполняемый после сохранения формы.
     * В нем можно добавлять/убирать шаги расчета конструкции.
     *
     * @return array
     */
    public function unsetStep()
    {
        $unsetArray   = array();
        $unsetArray[] = array(
            0,
            'false'
        );

        return $unsetArray;
    }

    /**
     * Метод определяет наличие следующего шага.
     *
     * @return bool
     */
    function checkNextStep()
    {
        if (Yii::app()->container->ShieldPanelCount == 0) {
            return Yii::t('steps', 'Оптимальный раскрой не был произведен!');
        }
        if (Yii::app()->container->PanoramicPanel) {
            if (Yii::app()->container->PanGlassingIndex == 6 && Yii::app()->container->WithBeadings != 1) {
                return Yii::t('steps', 'Внимание! Панорамное заполнение "Одинарный поликарбонат" возможно только в панорамных панелях со штапиком!');
            }
            if (Yii::app()->container->PanGlassingIndex == 7 && Yii::app()->container->WithBeadings != 1) {
                return Yii::t('steps', 'Внимание! Панорамное заполнение "Решетка" возможно только в панорамных панелях со штапиком!');
            }
            if (Yii::app()->container->PanGlassingIndex == 6 && (Yii::app()->container->PanWithAntiJamProtection || Yii::app()->container->ShieldWithAntiJamProtection)) {
                return Yii::t('steps', 'Внимание! Панорамное заполнение "Одинарный поликарбонат" возможно только в комплектации без защиты от защемления пальцев!');
            }
        }
        if (Yii::app()->container->ErrCheckSendPanel == 1) {
            $loc1 = Yii::app()->container->Loc(110098);
            $loc2 = Yii::app()->container->Loc(464004);
            return "{$loc1}. {$loc2}.";;
        }
        return true;
    }

    /**
     * Метод отвечает за основную логику формы. В этом методе вешаются обработчики,
     * которые отвечают за отображение/сокрытие, активацию/деактивацию, присвоение
     * значения и т.д., элементам формы
     *
     * @return string
     */
    public function JavascriptExperssion()
    {
        $query = '';
        $this->formPanels = Yii::app()->container->getStore('formPanels');
        $this->formPanelsAl = Yii::app()->container->getStore('formPanelsAl');
        $this->formPanelsAl2010 = Yii::app()->container->getStore('formPanelsAl2010');
        if (($this->formPanels == 0) && ($this->formPanelsAl == 0) && ($this->formPanelsAl2010 == 0)) {
            $productId = Yii::app()->container->productId;
            $group = array();
            $groupAl = array();
            $groupAl2010 = array();
            $polElements = array();
            $polElementsAl = array();
            $polElementsAl2010 = array();
            $formula = new FormulaHelper();
            if (Yii::app()->container->DilerVersion) {
                $version = "FIND_IN_SET(\"diller\",s.version)>0";
            } else {
                $version = "FIND_IN_SET(\"inside\",s.version)>0 or s.version=\"\"";
            }
            list($regionNames) = AbstractDirectoryModel::getRegionsName(Yii::app()->container->Region);
            $dataReader = Yii::app()->db->createCommand()->selectDistinct('g.shield_group_id, g.condition_issuing, s.title')
                            ->from('shield_group_product_parameters g')
                            ->leftJoin('shield_group_many_product_parameter m', 'm.shield_group_product_parameters_id = g.id')
                            ->leftJoin('shield_group s', 's.id = g.shield_group_id')
                            ->leftJoin('shield_group_product_regions t', 't.shield_group_product_id = g.id')
                            ->leftJoin('region', 'region.id = t.region_id')
                            ->where('region.title IN (' . $regionNames . ') and (m.product_id=' . $productId . ' or m.product_id=' . ProductModel::ID_ALL_PRODUCT . ') and(' . $version . ')')->query();
            $groups = $dataReader->readAll();
            foreach ($groups as $key => $value) {
                if ($formula->calculation($value['condition_issuing'], 1)) {
                    if (($value['title'] != "Панели с алюминиевой облицовкой") && ($value['title'] != "Панели с алюминиевой облицовкой 2010")) {
                        $group[] = $value['shield_group_id'];
                    } else {
                        if ($value['title'] == "Панели с алюминиевой облицовкой") {
                            $groupAl[] = $value['shield_group_id'];
                        } else {
                            if ($value['title'] == "Панели с алюминиевой облицовкой 2010") {
                                $groupAl2010[] = $value['shield_group_id'];
                            }
                        }
                    }
                }
            }
            if (count($group) > 0) {
                $this->formPanels = ProductHelper::getPanels($group, Yii::app()->container->DilerVersion, Yii::app()->container->Region);
            } else {
                $this->formPanels = 0;
            }
            if (count($groupAl) > 0) {
                $this->formPanelsAl = ProductHelper::getPanels($groupAl, Yii::app()->container->DilerVersion, Yii::app()->container->Region);
            } else {
                $this->formPanelsAl = 0;
            }
            if (count($groupAl2010) > 0) {
                $this->formPanelsAl2010 = ProductHelper::getPanels($groupAl2010, Yii::app()->container->DilerVersion, Yii::app()->container->Region);
            } else {
                $this->formPanelsAl2010 = 0;
            }

            Yii::app()->container->setStore($this->formPanels, 'formPanels');
            Yii::app()->container->setStore($this->formPanelsAl, 'formPanelsAl');
            Yii::app()->container->setStore($this->formPanelsAl2010, 'formPanelsAl2010');
        }
        $json = CJavaScript::encode($this->formPanels);
        $jsonAl = CJavaScript::encode($this->formPanelsAl);
        $jsonAl2010 = CJavaScript::encode($this->formPanelsAl2010);
        $experssions = array();
        foreach ($this->elementsVariables as $key => $value) {
            $keyup = '';
            $focus = '';
            $click = '';
            $blur = '';
            $change = '';
            $dbclick = '';
            if ($value['type'] == 'integer') {
                $expressions = $this->commonJSFunctions();
                $keyup .= $expressions['integerCheck'];
            }
            if ($value['svg'] == 'true') {
                $keyup .= 'svgInfo("' . $key . '","aperture",this);';
                $focus .= 'svgAlloc("' . $key . '","aperture");';
                $blur .= 'svgUnAlloc("' . $key . '","aperture");';
            }
            if ($key == 'AlFacing') {
                $click .= '
                    isTypePanelChanged = 1;
                    if(jQuery("#' . __CLASS__ . '_AlFacing").attr("checked") == "checked") {
                        if (jQuery("#' . __CLASS__ . '_AlFacing2010").attr("checked") == "checked") {
                            jQuery("#' . __CLASS__ . '_AlFacing2010").removeAttr("checked");
                        };
                        content = {};
                        content = clone(contentAl);
                        createOption(content,"panel_type_id","typePanel","' . __CLASS__ . '_typePanel",0);
                    } else {
                        content = {};
                        content = clone(contentPanels);
                        createOption(content,"panel_type_id","typePanel","' . __CLASS__ . '_typePanel",0);
                    };
                ';
            }
            if ($key == 'AlFacing2010') {
                $click .= '
                    isTypePanelChanged = 1;
                    if(jQuery("#' . __CLASS__ . '_AlFacing2010").attr("checked") == "checked") {
                        if (jQuery("#' . __CLASS__ . '_AlFacing").attr("checked") == "checked") {
                            jQuery("#' . __CLASS__ . '_AlFacing").removeAttr("checked");
                        };
                        content = {};
                        content = clone(contentAl2010);
                        createOption(content,"panel_type_id","typePanel","' . __CLASS__ . '_typePanel",0);
                    } else {
                        content = {};
                        content = clone(contentPanels);
                        createOption(content,"panel_type_id","typePanel","' . __CLASS__ . '_typePanel",0);
                    };
                ';
            }
            if ($key == 'PanoramicPanel') {
                list($formPanoramicParameters, $funcPanoramicParameters) = $this->forms('PanoramicParametersMI');
                $query .= '
                    function panoramicHandler(){
                        if(jQuery("#' . __CLASS__ . '_PanoramicPanel").prop("checked")) {

                            //вызов формы Параметры панорамных панелей в модальном окне
                            var functionPanoramicParametersMI = new Function("elems", '.$funcPanoramicParameters.');
                            var elems = '.$formPanoramicParameters.';
                            functionPanoramicParametersMI(elems);
                            disableElements();
                        } else {
                            disableElements();
                        };
                    }
                ';
                $click .= '
                    panoramicHandler();
                ';
            }
            if ($key == 'typePanel') {
                $change .= '
                    typePanel = this.value;
                    if (typeof(typePanel) != "undefined") {
                        createOption(content["panel_type_id"][this.value],"panel_design_id","design", this.id,0);
                    }
                ';
            }
            if ($key == 'design') {
                $change .= '
                    design = this.value;
                    if (typeof(design) != "undefined") {
                        createOption(content["panel_type_id"][jQuery("#' . __CLASS__ . '_typePanel").val()]["panel_design_id"][this.value],"panel_design_add_id","design2", this.id,0);
                    }
                ';
            }
            if ($key == 'design2') {
                $change .= '
                    design2 = this.value;
                    if (typeof(design2) != "undefined") {
                        createOption(content["panel_type_id"][jQuery("#' . __CLASS__ . '_typePanel").val()]["panel_design_id"][jQuery("#' . __CLASS__ . '_design").val()]["panel_design_add_id"][this.value],"panel_structure_id","structure", this.id,0);
                    }
                ';
            }
            if ($key == 'structure') {
                $change .= '
                    structure = this.value;
                    if (typeof(structure) != "undefined") {
                        createOption(content["panel_type_id"][jQuery("#' . __CLASS__ . '_typePanel").val()]["panel_design_id"][jQuery("#' . __CLASS__ . '_design").val()]["panel_design_add_id"][jQuery("#' . __CLASS__ . '_design2").val()]["panel_structure_id"][this.value],"color_outside_id","colorOutside", this.id,0);
                    }
                ';

            }
            if ($key == 'colorOutside') {
                $change .= '
                        bStart = 0;
                        colorOutside = this.value;
                        if (typeof(colorOutside) != "undefined") {
                            createOption(content["panel_type_id"][jQuery("#' . __CLASS__ . '_typePanel").val()]["panel_design_id"][jQuery("#' . __CLASS__ . '_design").val()]["panel_design_add_id"][jQuery("#' . __CLASS__ . '_design2").val()]["panel_structure_id"][jQuery("#' . __CLASS__ . '_structure").val()]["color_outside_id"][this.value],"color_inside_id","colorInside", this.id,0);
                        }
                    ';
            }
            if ($key == 'colorInside') {
                $change .= '
                        bStart = 0;
                        colorInside = this.value;
                        if (typeof(colorInside) != "undefined") {
                            createOption(content["panel_type_id"][jQuery("#' . __CLASS__ . '_typePanel").val()]["panel_design_id"][jQuery("#' . __CLASS__ . '_design").val()]["panel_design_add_id"][jQuery("#' . __CLASS__ . '_design2").val()]["panel_structure_id"][jQuery("#' . __CLASS__ . '_structure").val()]["color_outside_id"][jQuery("#' . __CLASS__ . '_colorOutside").val()]["color_inside_id"][this.value],"typeSize","typeSize", this.id,0);
                        }
                    ';
            }
            if ($key == 'typeSize') {
                $change .= '
                    bStart = 0;
                    $("#apertureSVG svg g#Shield").die("click");
                    showPanelStep();

                    var post =jQuery("#' . __CLASS__ . '_typeSize").val();
                    if(post != "") {
                        currentTypes =jQuery("#' . __CLASS__ . '_typeSize").val();
                        $("#apertureSVG svg g#Shield").live("click",function(){
                            bStart = 0;
                            jQuery("#' . __CLASS__ . '_CurrentPanelType").val(prevTypes);
                            ajaxs(1);
                        });
                        if(currentTypes==prevTypes) {
                            jQuery("#' . __CLASS__ . '_CurrentPanelType").val(post); ajaxs(1);
                        } else {
                            jQuery("#' . __CLASS__ . '_CurrentPanelType").val(post); ajaxs(0);
                        }
                    }
                    //функция по удалению установленных объектов на щит (окна и калитки)
                    var container_WicketInstalled       = ' . (int) Yii::app()->container->WicketInstalled . ';
                    var container_ObjectTypeWindow      = "' . (string) Yii::app()->container->ObjectTypeWindow . '";
                    var container_EmbeddedObjectsCounts = 0;
                    if ((container_WicketInstalled || container_ObjectTypeWindow == "window") && container_EmbeddedObjectsCounts) {
                        delInstallObject();
                    }
                ';
                //    $click .= 'simpleAlertDialog(objDataSimpleAlert, "da")';
                // $click .= '   jQuery("#' . __CLASS__ . '_typeSize option[value=\'\']").prop("selected", true);';
                $blur .= '   jQuery("#' . __CLASS__ . '_typeSize option[value=\'"+prevTypes+"\']").prop("selected", true);';
            }
            if ($key == 'VariantCutting') {
                if (Yii::app()->container->ShieldWidth > 0) {
                    $width = Yii::app()->container->ShieldWidth;
                } else {
                    $width = Yii::app()->container->Bh;
                }
                $change .= 'var post = jQuery("#cutting").serialize();
                            post += "&"+jQuery("#aperture-form").serialize();
                            post += getVariantUmodified(0);
                            post +="&calc=OptimalPanelsCuttingMC";
                            jQuery.ajax({type: "POST",
                                url:"' . Yii::app()->createUrl("/steps/default/ajaxdata") . '",
                                data:post,
                                async: false,
                                success: function(data) {
                                    contentJSON = $.parseJSON(data);
                                    $("#TypePanelsColorShieldMI_VariantCutting").val(contentJSON.VariantCutting);       //Модуль OptimalPanelsCuttingMC возвращает номер варианта раскроя
                                    if (contentJSON.ShieldPanelCount > 0 ) {
                                        var heightShield = contentJSON.ShieldRealHeight;
                                        svgInfo("ShieldHeight","aperture",heightShield);
                                        var widthShield = "' . $width . '";
                                        var counts = contentJSON.ShieldPanelCount;
                                        panels = contentJSON.ShieldWholePanels;
                                        maximal = panels[1];
                                        for (var kis in panels) {
                                            panels[kis] = parseInt( panels[kis]);
                                            if (panels[kis] > maximal) {
                                                maximal = panels[kis];
                                            }
                                        }
                                        panelsCutting = contentJSON.ShieldPanels;
                                        isCut = [contentJSON.ShieldTopPanelIsCut,contentJSON.ShieldBottomPanelIsCut];
                                        svg = jQuery("#apertureSVG svg g#Shield");
                                        clearElements(svg,".panels");
                                        sortablesEl(panels);
                                        //createPanels(svg,counts, panels, panelsCutting,heightShield, widthShield, isCut);
                                        var VerticalPanel = ' . Yii::app()->container->VerticalPanel . ';
                                        createPanels_FalsePanel(svg, counts, panels, panelsCutting, heightShield, widthShield, isCut, 0, 0, 0, 0, VerticalPanel);
                                        
                                        post ="key="+ jQuery("#key").val()+"&calc=PanelsCuttingWithInfillsMC";
                                        jQuery.ajax({type: "POST",
                                            url:"' . Yii::app()->createUrl("/steps/default/ajaxdata") . '",
                                            data:post,
                                            async: false,
                                            success: function(data) {
                                                fillsJSON = $.parseJSON(data);
                                                var heightShield = contentJSON.ShieldRealHeight;
                                                var widthShield = "' . $width . '";
                                                var fills =fillsJSON.ShieldInfills;
                                                var PhilMessage = fillsJSON.PhilMessage;
                                                if (PhilMessage != 0)
                                                    simpleAlertDialog(objDataSimpleAlert, PhilMessage);
                                                svg = jQuery("#apertureSVG svg g#Shield");
                                                if (fillsJSON.ShieldInfillCount >0 ) {
                                                    createFills(svg,fills,heightShield, widthShield);
                                                }
                                            }
                                        });
                                        deletePanoramicWicket();
                                    } else {
                                        simpleAlertDialog({ firstTr: objDataSimpleAlert.firstTr, dialogClass: "errorMsg" }, "' . OrderModel::translitatorForPhantomJS(Yii::t('steps', 'Раскрой не удался!'), !Yii::app()->container->autoCalc).'");
                                        console.log("1");
                                    }
                                },
                                error:function() {
                                    $("body #full-loading").remove();
                                    simpleAlertDialog({ firstTr: objDataSimpleAlert.firstTr, dialogClass: "errorMsg" }, "' . OrderModel::translitatorForPhantomJS(Yii::t('steps', 'Раскрой не удался!'), !Yii::app()->container->autoCalc).'");
                                    console.log("2");
                                }
                            });
                    //функция по удалению установленных объектов на щит (окна и калитки)
                    var container_WicketInstalled       = ' . (int) Yii::app()->container->WicketInstalled . ';
                    var container_ObjectTypeWindow      = "' . (string) Yii::app()->container->ObjectTypeWindow . '";
                    var container_EmbeddedObjectsCounts = 0;
                    if ((container_WicketInstalled || container_ObjectTypeWindow == "window") && container_EmbeddedObjectsCounts) {
                        delInstallObject();
                    }
                ';
            }
            if ($key == 'PanelCuttingMode') {
                if (Yii::app()->container->ShieldWidth > 0) {
                    $width = Yii::app()->container->ShieldWidth;
                } else {
                    $width = Yii::app()->container->Bh;
                }
                $change .= 'var errorPr=0;
                            if((this.value == 1) || (this.value == 2)){
                                jQuery("#' . __CLASS__ . '_PanelSizeForCutting").removeAttr("disabled");
                                if (this.value == 1 && minimalB) {
                                    jQuery("#' . __CLASS__ . '_PanelSizeForCutting").val(minimalB);
                                }
                                if (this.value == 2 && minimalT) {
                                    jQuery("#' . __CLASS__ . '_PanelSizeForCutting").val(minimalT);
                                }
                            } else {
                                jQuery("#' . __CLASS__ . '_PanelSizeForCutting").attr("disabled", "disabled");
                            }
                            if (errorPr == 0) {
                                var post = jQuery("#cutting").serialize();
                                post += "&"+jQuery("#aperture-form").serialize();
                                post += getVariantUmodified();
                                post +="&calc=OptimalPanelsCuttingMC";

                                jQuery.ajax({type: "POST",
                                        url:"' . Yii::app()->createUrl("/steps/default/ajaxdata") . '",
                                        data:post,
                                        async: false,
                                        success: function(data) {
                                            contentJSON = $.parseJSON(data);
                                            if (contentJSON.ShieldPanelCount > 0 ) {
                                                var heightShield = contentJSON.ShieldRealHeight;
                                                svgInfo("ShieldHeight","aperture",heightShield);
                                                var widthShield = "' . $width . '";
                                                var counts = contentJSON.ShieldPanelCount;
                                                panels = contentJSON.ShieldWholePanels;
                                                panelsCutting = contentJSON.ShieldPanels;
                                                maximal = panels[1];
                                                for (var kis in panels) {
                                                    panels[kis] = parseInt( panels[kis]);
                                                    if (panels[kis] > maximal) {
                                                        maximal = panels[kis];
                                                    }
                                                }
                                                isCut = [contentJSON.ShieldTopPanelIsCut,contentJSON.ShieldBottomPanelIsCut];
                                                svg = jQuery("#apertureSVG svg g#Shield");
                                                clearElements(svg,".panels"); 
                                                //createPanels(svg,counts, panels, panelsCutting,heightShield, widthShield, isCut);
                                                var VerticalPanel = ' . Yii::app()->container->VerticalPanel . ';
                                                createPanels_FalsePanel(svg, counts, panels, panelsCutting, heightShield, widthShield, isCut, 0, 0, 0, 0, VerticalPanel);
                                                post ="key="+ jQuery("#key").val()+"&calc=PanelsCuttingWithInfillsMC";
                                                jQuery.ajax({type: "POST",
                                                    url:"' . Yii::app()->createUrl("/steps/default/ajaxdata") . '",
                                                    data:post,
                                                    async: false,
                                                    success: function(data) {
                                                        fillsJSON = $.parseJSON(data);
                                                        var heightShield = contentJSON.ShieldRealHeight;
                                                        var widthShield = "' . $width . '";
                                                        var fills =fillsJSON.ShieldInfills;
                                                        var PhilMessage = fillsJSON.PhilMessage;
                                                        if (PhilMessage != 0)
                                                            simpleAlertDialog(objDataSimpleAlert, PhilMessage);
                                                        svg = jQuery("#apertureSVG svg g#Shield");
                                                        if (fillsJSON.ShieldInfillCount >0 ) {
                                                            createFills(svg,fills,heightShield, widthShield);
                                                        }
                                                    }
                                                });
                                                deletePanoramicWicket();
                                            } else {
                                                simpleAlertDialog({ firstTr: objDataSimpleAlert.firstTr, dialogClass: "errorMsg" }, "' . OrderModel::translitatorForPhantomJS(Yii::t('steps', 'Раскрой не удался!'), !Yii::app()->container->autoCalc).'");
                                                console.log("3");
                                            }
                                        },
                                        error:function() {
                                            $("body #full-loading").remove();
                                            simpleAlertDialog({ firstTr: objDataSimpleAlert.firstTr, dialogClass: "errorMsg" }, "' . OrderModel::translitatorForPhantomJS(Yii::t('steps', 'Раскрой не удался!'), !Yii::app()->container->autoCalc).'");
                                            console.log("4");
                                        }
                                    })
                                }
                            //функция по удалению установленных объектов на щит (окна и калитки)
                            var container_WicketInstalled       = ' . (int) Yii::app()->container->WicketInstalled . ';
                            var container_ObjectTypeWindow      = "' . (string) Yii::app()->container->ObjectTypeWindow . '";
                            var container_EmbeddedObjectsCounts = 0;
                            if ((container_WicketInstalled || container_ObjectTypeWindow == "window") && container_EmbeddedObjectsCounts) {
                                delInstallObject();
                            }
                        ;';
            }
            if ($key == 'PanelSizeForCutting') {
                if (Yii::app()->container->ShieldWidth > 0) {
                    $width = Yii::app()->container->ShieldWidth;
                } else {
                    $width = Yii::app()->container->Bh;
                }
                $change .= '
                var errorPr = 0;
                console.log( minimalT);
                console.log(minimalB);
                console.log(jQuery("#' . __CLASS__ . '_PanelCuttingMode option:selected").val());
                if ( jQuery("#' . __CLASS__ . '_PanelCuttingMode option:selected").val() == 1) {
                    if((jQuery("#' . __CLASS__ . '_PanelSizeForCutting").val() < minimalB) || (jQuery("#' . __CLASS__ . '_PanelSizeForCutting").val() > maximal)) {
                        errorPr = 1;
                        if (jQuery("#' . __CLASS__ . '_PanelSizeForCutting").val() < minimalB) {
                            jQuery("#' . __CLASS__ . '_PanelSizeForCutting_em_").text(" Вы ввели меньше "+minimalB);
                            jQuery("#' . __CLASS__ . '_PanelSizeForCutting_em_").show();
                        };
                        if (jQuery("#' . __CLASS__ . '_PanelSizeForCutting").val() > maximal) {
                            jQuery("#' . __CLASS__ . '_PanelSizeForCutting_em_").text(" Вы ввели больше "+maximal);
                            jQuery("#' . __CLASS__ . '_PanelSizeForCutting_em_").show();
                        };
                    } else {
                        errorPr = 0;
                        jQuery("#' . __CLASS__ . '_PanelSizeForCutting_em_").text("");
                        jQuery("#' . __CLASS__ . '_PanelSizeForCutting_em_").hide();
                    }
                }
                if ( jQuery("#' . __CLASS__ . '_PanelCuttingMode option:selected").val() == 2) {
                    if((jQuery("#' . __CLASS__ . '_PanelSizeForCutting").val() < minimalT) || (jQuery("#' . __CLASS__ . '_PanelSizeForCutting").val() > maximal)) {
                        errorPr = 1;
                        if (jQuery("#' . __CLASS__ . '_PanelSizeForCutting").val() < minimalT) {
                            jQuery("#' . __CLASS__ . '_PanelSizeForCutting_em_").text(" Вы ввели меньше "+minimalT);
                            jQuery("#' . __CLASS__ . '_PanelSizeForCutting_em_").show();
                        };
                        if (jQuery("#' . __CLASS__ . '_PanelSizeForCutting").val() > maximal) {
                            jQuery("#' . __CLASS__ . '_PanelSizeForCutting_em_").text(" Вы ввели больше "+maximal);
                            jQuery("#' . __CLASS__ . '_PanelSizeForCutting_em_").show();
                        };
                    } else {
                        errorPr = 0;
                        jQuery("#' . __CLASS__ . '_PanelSizeForCutting_em_").text("");
                        jQuery("#' . __CLASS__ . '_PanelSizeForCutting_em_").hide();
                    }
                }
                if (errorPr == 0 ) {
                    var post = jQuery("#cutting").serialize();
                    post += "&"+jQuery("#aperture-form").serialize();
                    post +="&calc=OptimalPanelsCuttingMC";
                    jQuery.ajax({type: "POST",
                                url:"' . Yii::app()->createUrl("/steps/default/ajaxdata") . '",
                                data:post,
                                async: false,
                                success: function(data) {
                                    contentJSON = $.parseJSON(data);
                                    if (contentJSON.ShieldPanelCount > 0 ) {
                                        var heightShield = contentJSON.ShieldRealHeight;
                                        svgInfo("ShieldHeight","aperture",heightShield);
                                        var widthShield = "' . $width . '";
                                        var counts = contentJSON.ShieldPanelCount;
                                        panels = contentJSON.ShieldWholePanels;
                                        maximal = panels[1];
                                        for (var kis in panels) {
                                            panels[kis] = parseInt( panels[kis]);
                                            if (panels[kis] > maximal) {
                                                maximal = panels[kis];
                                            }
                                        }
                                        panelsCutting = contentJSON.ShieldPanels;
                                        isCut = [contentJSON.ShieldTopPanelIsCut,contentJSON.ShieldBottomPanelIsCut];
                                        svg = jQuery("#apertureSVG svg g#Shield");
                                        clearElements(svg,".panels");
                                        //createPanels(svg,counts, panels, panelsCutting,heightShield, widthShield, isCut);
                                        var VerticalPanel = ' . Yii::app()->container->VerticalPanel . ';
                                        createPanels_FalsePanel(svg, counts, panels, panelsCutting, heightShield, widthShield, isCut, 0, 0, 0, 0, VerticalPanel);
                                        post ="key="+ jQuery("#key").val()+"&calc=PanelsCuttingWithInfillsMC";
                                        jQuery.ajax({type: "POST",
                                                    url:"' . Yii::app()->createUrl("/steps/default/ajaxdata") . '",
                                                    data:post,
                                                    async: false,
                                                    success: function(data) {
                                                        fillsJSON = $.parseJSON(data);
                                                        var heightShield = contentJSON.ShieldRealHeight;
                                                        var widthShield = "' . $width . '";
                                                        var fills =fillsJSON.ShieldInfills;
                                                        var PhilMessage = fillsJSON.PhilMessage;
                                                        if (PhilMessage != 0)
                                                            simpleAlertDialog(objDataSimpleAlert, PhilMessage);
                                                        svg = jQuery("#apertureSVG svg g#Shield");
                                                        if (fillsJSON.ShieldInfillCount >0 ) {
                                                            createFills(svg,fills,heightShield, widthShield);
                                                        }
                                                    }
                                        });
                                    } else {
                                        simpleAlertDialog({ firstTr: objDataSimpleAlert.firstTr, dialogClass: "errorMsg" }, "' . OrderModel::translitatorForPhantomJS(Yii::t('steps', 'Раскрой не удался!'), !Yii::app()->container->autoCalc).'");
                                        console.log("5");
                                    }
                                },
                                error:function() {
                                    $("body #full-loading").remove();
                                    simpleAlertDialog({ firstTr: objDataSimpleAlert.firstTr, dialogClass: "errorMsg" }, "' . OrderModel::translitatorForPhantomJS(Yii::t('steps', 'Раскрой не удался!'), !Yii::app()->container->autoCalc).'");
                                    console.log("6");
                                }
                    })
                ;};';
            }
            if ($key == 'Colout') {
                $change .= '
                    if ((this.value == "По RAL") || (this.value == "вручную")) {
                        jQuery("#' . __CLASS__ . '_Colout_n").removeAttr("disabled");
                    } else {
                        jQuery("#' . __CLASS__ . '_Colout_n").attr("disabled", "disabled");
                    };
                    if (this.value == "По RAL" && ("RAL" + jQuery("#' . __CLASS__ . '_Colout_n").val()) == jQuery("#' . __CLASS__ . '_colorOutside option:selected").text()) {
                        jQuery("#' . __CLASS__ . '_Pokout").removeAttr("checked");
                    } else if (jQuery("#' . __CLASS__ . '_Colout option:selected").val() == jQuery("#' . __CLASS__ . '_colorOutside option:selected").text()) {
                        jQuery("#' . __CLASS__ . '_Pokout").removeAttr("checked");
                    } else {
                        jQuery("#' . __CLASS__ . '_Pokout").attr("checked", "checked");
                    };
                ';
            }
            if ($key == 'Colin') {
                $expressions = $this->commonJSFunctions();
                $integerCheck = $expressions['integerCheck'];
                $change .= '
                    if ((this.value == "По RAL") || (this.value == "вручную")) {
                        jQuery("#' . __CLASS__ . '_Colin_n").removeAttr("disabled");
                    } else {
                        jQuery("#' . __CLASS__ . '_Colin_n").attr("disabled", "disabled");
                    };
                    if (this.value == "По RAL" && ("RAL" + jQuery("#' . __CLASS__ . '_Colin_n").val()) == jQuery("#' . __CLASS__ . '_colorInside option:selected").text()) {
                        jQuery("#' . __CLASS__ . '_Pokin").removeAttr("checked");
                    } else if (jQuery("#' . __CLASS__ . '_Colin option:selected").val() == jQuery("#' . __CLASS__ . '_colorInside option:selected").text()) {
                        jQuery("#' . __CLASS__ . '_Pokin").removeAttr("checked");
                    } else {
                        jQuery("#' . __CLASS__ . '_Pokin").attr("checked", "checked");
                    };
                    if(this.value == "вручную") jQuery("#' . __CLASS__ . '_Colin_n").die("keyup");
                    else jQuery("#' . __CLASS__ . '_Colin_n").live("keyup", function(){'. $integerCheck .'});
                ';
            }
            if ($key == 'Colout_n') {
                $keyup .= '
                    var outsidePanelColor = jQuery("#' . __CLASS__ . '_colorOutside option:selected").text();
                    if (outsidePanelColor.indexOf("RAL") != -1 && jQuery("#' . __CLASS__ . '_Colout option:selected").val() == "По RAL") {
                        if (outsidePanelColor.substr(3) != $.trim(this.value)) {
                            jQuery("#' . __CLASS__ . '_Pokout").prop("checked", true);
                        } else {
                            jQuery("#' . __CLASS__ . '_Pokout").prop("checked", false);
                        }
                    }
                ';
            }
            if ($key == 'Colin_n') {
                $keyup .= '
                    var insidePanelColor = jQuery("#' . __CLASS__ . '_colorInside option:selected").text();
                    if (insidePanelColor.indexOf("RAL") != -1 && jQuery("#' . __CLASS__ . '_Colin option:selected").val() == "По RAL") {
                        if (insidePanelColor.substr(3) != $.trim(this.value)) {
                            jQuery("#' . __CLASS__ . '_Pokin").prop("checked", true);
                        } else {
                            jQuery("#' . __CLASS__ . '_Pokin").prop("checked", false);
                        }
                    }
                ';
            }
            $experssions[$key] = array(
                'keyup' => $keyup,
                'focus' => $focus,
                'blur' => $blur,
                'change' => $change,
                'click' => $click
            );
        }
        if (Yii::app()->container->VerticalPanel) {
            $cuttings = array(
                "cuttings" => array(
                    1 => array("value" => Yii::t('steps', "Задать размер правой панели")),
                    2 => array("value" => Yii::t('steps', "Задать размер левой панели")),
                    3 => array("value" => Yii::t('steps', "Обрезаем панель справа")),
                    4 => array("value" => Yii::t('steps', "Обрезаем панель слева")),
                    5 => array("value" => Yii::t('steps', "Обрезаем панели симметрично"))
                )
            );
        } else {
            $cuttings = array(
                "cuttings" => array(
                    1 => array("value" => Yii::t('steps', "Задать размер нижней панели")),
                    2 => array("value" => Yii::t('steps', "Задать размер верхней панели")),
                    3 => array("value" => Yii::t('steps', "Обрезаем нижнюю панель")),
                    4 => array("value" => Yii::t('steps', "Обрезаем верхнюю панель")),
                    5 => array("value" => Yii::t('steps', "Обрезаем панели симметрично"))
                )
            );
        }

        $query .= '
            var yesStr = \''. Yii::t('steps', 'да') .'\';
            var noStr  = \''. Yii::t('steps', 'нет') .'\';
            cutting = ' . json_encode($cuttings) . ';
            var minimalT;
            var minimalB;
            var maxiaml;
        ';
        foreach ($experssions as $key => $value) {
            foreach ($value as $event => $function) {
                if (!empty($function)) {
                    $query .= '
                        jQuery("#' . __CLASS__ . '_' . $key . '").die("' . $event . '");
                    ';
                    $query .= '
                        jQuery("#' . __CLASS__ . '_' . $key . '").live("' . $event . '", function(){' . $function . '});
                    ';
                    //   if ($this->elementsVariables[$key]['typeForm'] == 'array') {
                    //      $query .= 'jQuery("#' . __CLASS__ . '_' . $key . ' option").live("click", function(){ ' . $function . '});';
                    // }
                }
            }
        }
        $query .= '
            contentPanels  = ' . $json . ';
            contentAl  = ' . $jsonAl . ';
            contentAl2010  = ' . $jsonAl2010 . ';
            var form_input_VerticalLocation = $("#' . __CLASS__ . '_VerticalLocation");
            form_input_VerticalLocation.prop("disabled", true);
            
            var VerticalPanel = ' . Yii::app()->container->VerticalPanel . ';
            if (VerticalPanel) {
                $("#' . __CLASS__ . '_VerticalLocation").prop("checked", true);
            } else {
                $("#' . __CLASS__ . '_VerticalLocation").prop("checked", false);
            }
        ';

        // для незаполненой формы выбирать автоматически параметры(если 1 параметр в выпадаюем списке)
        $runEvents = '';
        if (Yii::app()->container->CurrentPanelType == 0) {
            $runEvents = '
                if (countLoops === 1) {
                    var elemSelected = jQuery("#' . __CLASS__ . '_"+form).find("option:first").next("option").prop("selected", true);
                    elemSelected = jQuery("#' . __CLASS__ . '_"+form).trigger("change");
                }
            ';
        } else {
            $runEvents = '
                if (isTypePanelChanged === 0) {
                    $("#' . __CLASS__ . '_"+form).change(function(){            //событие "Й"
                        console.log("Значение поменяли: #' . __CLASS__ . '_"+form);
                        isTypePanelChanged = 1;
                    });
                }
                if (countLoops === 1 && isTypePanelChanged === 1) {
                    var elemSelected = jQuery("#' . __CLASS__ . '_"+form).find("option:first").next("option").prop("selected", true);
                    elemSelected = jQuery("#' . __CLASS__ . '_"+form).trigger("change");
                }
            ';
        }
        $query .= '
            var currentTypes;
            var prevTypes = "' . Yii::app()->container->typeSize . '";
            if (prevTypes != undefined && prevTypes != 0 || prevTypes == "") {
                $("#apertureSVG svg g#Shield").die("click");
                $("#apertureSVG svg g#Shield").live("click",function(){
                    bStart = 0;
                    currentTypes = prevTypes;
                    jQuery("#' . __CLASS__ . '_CurrentPanelType").val(prevTypes);
                    ajaxs(1);
                });
            } else if ($("#TypePanelsColorShieldMI_PanoramicPanel").prop("checked")) {
                $("#apertureSVG svg g#Shield").die("click");
                $("#apertureSVG svg g#Shield").live("click",function(){
                    bStart = 0;
                    panoramicHandler();
                });
            }
        ';
        $query .= ' var numberSizes = ' . json_encode(Yii::app()->container->SelectedSizes) . ';';
        $query .= ' var numberonlyOne = ' . Yii::app()->container->SelectedOnlyOne . ';';
        $query .= ' var numberauto = ' . Yii::app()->container->SelectedAutoExtend . ';';
        $query .= ' var numberMinCuts = ' . Yii::app()->container->SelectedMinCuts . ';';
        $query .= ' var isTypePanelChanged = 0;';
        $query .= '
            function createOption(content, name, form, parents, selected)
            {
                if(parents !=0 ) {
                    emptys(parents);
                }
                var str = "";
                if (name =="cutting") {
                    str = str+ "<option value=\'\'>'.Yii::t('steps', 'Выберите').'</option>";

                    for (var key in content)
                    {
                        strArray = "";
                        length = content[key].cutting;
                        for (var key2 in content[key].cutting) {
                            strArray  = strArray + content[key].cutting[key2]
                            if(key2 != length -1) {
                                strArray = strArray +",";
                            }
                        }
                        str = str + "<option value =\'" + key + "\'>" + strArray+"</option>"
                        jQuery("#' . __CLASS__ . '_"+form).empty();
                        jQuery("#' . __CLASS__ . '_"+form).append("<option value=\"\">'.Yii::t('steps', 'Выберите').'</option>");
                    }
                    jQuery("#' . __CLASS__ . '_"+form).html(str);
                } else {
                    str = str+ "<option value=\"\">'.Yii::t('steps', 'Выберите').'</option>";
                    var countLoops = 0;

                    if (typeof(content) == "undefined") {
                        console.log("find undefined content name are: " + name);
                        //str = str + "<option value =\'" + key + "\'>'.Yii::t('steps', 'Выберите').'</option>";
                        jQuery("#' . __CLASS__ . '_"+form).empty();
                        jQuery("#' . __CLASS__ . '_"+form).append("<option value=\"\">'.Yii::t('steps', 'Выберите').'</option>");
                    } else {
                        for (var key in content[name])
                        {
                            str = str + "<option value =\'" + key + "\'>" + content[name][key]["value"]+"</option>";
                            jQuery("#' . __CLASS__ . '_"+form).empty();
                            jQuery("#' . __CLASS__ . '_"+form).append("<option value=\"\">'.Yii::t('steps', 'Выберите').'</option>");
                            countLoops++;
                        }
                    }
                    jQuery("#' . __CLASS__ . '_"+form).html(str);
                    // если 1 пункт в списке, автоматически его выбрать
                    ' . $runEvents . '
                    if (selected != 0 ) {
                        jQuery("#' . __CLASS__ . '_"+form+" [value="+selected+"]").attr("selected","selected");
                    }
                }
            };
        ';
        $tempArray = array();
        foreach ($this->elementsVariables as $key => $value) {
            if (($value['typeForm'] == 'array') && (!in_array($key, array(
                        'Pokout',
                        'Colout',
                        'Colout_n',
                        'Pokin',
                        'Colin',
                        'Colin_n',
                        'PanelCuttingMode',
                    )))
            ) {
                $tempArray[] = $key;
            }
        }
        $query .= '
            function emptys(element){

                el = element.substring(element.indexOf("_")+1);
                elements = ' . json_encode($tempArray) . ';
                n = elements.indexOf(el);

                for(var i=n+1; i<= elements.length;i++){
                    jQuery("#' . __CLASS__ . '_"+elements[i]).empty();
                    jQuery("#' . __CLASS__ . '_"+elements[i]).append("<option value=\"\">'.Yii::t('steps', 'Выберите').'</option>");
                }
            }
        ';
        $query .= '
            $("#' . __CLASS__ . '_isColorDefault").val(0);
            //костыль для избегания ошибки повторного вызова окна с выбором типоразмеров панелей
            var bStart = 0;
            function ajaxs(id) {
                var post = jQuery("#aperture-form").serialize();
                post +="&calc=ShieldMC";
                jQuery.ajax({
                    type: "POST",
                    url:"' . Yii::app()->createUrl("/steps/default/ajaxdata") . '",
                    data:post,
                    async: false,
                    success: function(data) {
                        shieldJSON = $.parseJSON(data);

                        minimalT = parseInt(shieldJSON.ShieldMinTop);
                        minimalB = parseInt(shieldJSON.ShieldMinBottom);

                        //цвет
                        $("#' . __CLASS__ . '_isColorDefault").val(1);
                        var post = jQuery("#aperture-form").serialize();
                        post +="&calc=ShieldColorMC";
                        jQuery.ajax({
                            type: "POST",
                            url:"' . Yii::app()->createUrl("/steps/default/ajaxdata") . '",
                            data:post,
                            async: false,
                            success: function(data) {
                                shieldJSON = $.parseJSON(data);

                                jQuery("#' . __CLASS__ . '_Colout").removeAttr("disabled");
                                jQuery("#' . __CLASS__ . '_Colin").removeAttr("disabled");

                                switchColorShield(shieldJSON);
                            }
                        });
                        $("#' . __CLASS__ . '_isColorDefault").val(0);

                        if (bStart == 0) {
                            typeDialog(data,id);
                        }
                    }
                });
            };
        ';
        if (Yii::app()->container->ShieldWidth > 0) {
            $width = Yii::app()->container->ShieldWidth;
        } else {
            $width = Yii::app()->container->Bh;
        }
        $query .= '
            function typeDialog(data,id){
                bStart = 1;
                data = $.parseJSON(data);
                var str = "<div id=\"sizes\"><form id=\"cutting\">";
                typesArray = data.ShieldSizesPanel;

                typesArray.forEach(
                    function(element, index, array){
                        checksize = "";
                        if(id==1) {
                            for(var i=0;i<numberSizes.length;i++) {
                                if (element == numberSizes[i]) {
                                    checksize = "checked=\'checked\'";
                                    break;
                                }
                            }
                        } else {
                            checksize="checked=\'checked\'";
                        }
                        str += "<input type=\'checkbox\'"+ checksize +" value=\'"+element+"\' id=\'TypePanelsColorShieldMI_ShieldPanelSizes[]\' name=\'TypePanelsColorShieldMI[ShieldPanelSizes][]\'/>"+element+"<br/>";
                });

            str += "<p><br/><br/></p>";

            //чекбокс Только один типоразмер
            onlySizeAllow="";
            onlySize = "";
            if (data.OnlyOneSizeAllowed == 1 ) {
                onlySize = " checked=\"checked\" ";
            } else if (data.OnlyOneSizeAllowed == 2 ) {
                onlySize = " checked=\"checked\" ";
                onlySizeAllow=" disabled=\"disabled\" ";
            } else if (data.OnlyOneSizeAllowed == 0 ) {
            }

            if (data.ShieldAutoExtendAllowed == 0)
            {
                autoExAllow = "disabled=\"disabled\"";
            } else {
                autoExAllow = "";
            }
            if(data.ShieldAutoExtendEnabled == 1) {
                autoEx = "checked=\"checked\"";
            } else {
                autoEx = "";
            }
//            if (data.MinCutsAllowed ==0) {
//                minCutsAllow = "disabled=\"disabled\"";
//            } else {
//                 minCutsAllow = "";
//            }
            minCuts = "";
            minCutsAllow = "";
            if (data.MinCutsEnabled == 1 ) {
                minCuts = "checked=\"checked\"";
            } else if (data.MinCutsEnabled == 2 ) {
                minCuts = "checked=\"checked\"";
                minCutsAllow = "disabled=\"disabled\"";
            } else if (data.MinCutsEnabled == 0 ) {
                minCutsAllow = "disabled=\"disabled\"";
            } else if (data.MinCutsEnabled == 3 ) {
                minCuts = "";
                minCutsAllow = "";
            } else {
                //должно вернуть сообщение об ошибке, если не = 0, 1, 2
                simpleAlertDialog(objDataSimpleAlert, data.MinCutsEnabled);
            }
            if(id==1 && numberonlyOne ==1) {
              onlySize = "checked=\"checked\"";
            }
             if(id==1 && numberauto ==1) {
            autoEx = "checked=\"checked\"";
            }
//             if(id==1 && numberMinCuts ==1) {
//             minCuts = "checked=\"checked\"";
//            }

            var ShieldProcedureCuttingDefault = Number(data.ShieldProcedureCuttingDefault);
            switch(ShieldProcedureCuttingDefault) {
                case 0:
                    ShieldProcedureCuttingDefault = 4;
                    break
                case 1:
                    ShieldProcedureCuttingDefault = 3;
                    break
                case 2:
                    ShieldProcedureCuttingDefault = 5;
                    break
                case 3:
                    ShieldProcedureCuttingDefault = 2;
                    break
                case 4:
                    ShieldProcedureCuttingDefault = 1;
                    break
                default:
                    ShieldProcedureCuttingDefault = $("#TypePanelsColorShieldMI_PanelCuttingMode").val();
            }

            str += "<input type=\'checkbox\' value=\'1\' id=\'TypePanelsColorShieldMI_OnlyOneSizeSelected\' name=\'TypePanelsColorShieldMI[OnlyOneSizeSelected]\' "+ onlySizeAllow + onlySize +"/>' . Yii::t('steps', 'Только один типоразмер') . '<br/>";
            str += "<input type=\'checkbox\' value=\'1\' id=\'TypePanelsColorShieldMI_ShieldAutoExtendEnabledSelected\' name=\'TypePanelsColorShieldMI[ShieldAutoExtendEnabledSelected]\' " + autoExAllow + autoEx +"/>' . Yii::t('steps', 'Надставка над профилем') . '<br/>";
            str += "<input type=\'checkbox\' value=\'1\' id=\'TypePanelsColorShieldMI_MinCutsEnabledSelected\' name=\'TypePanelsColorShieldMI[MinCutsEnabledSelected]\' "+minCutsAllow+minCuts +"/>' . Yii::t('steps', 'Не резать по усилению') . '<br/>";
            str+= "<input type=\'hidden\' value=\'0\' id==\'TypePanelsColorShieldMI_VariantUmodified\' name=\'TypePanelsColorShieldMI[VariantUmodified]\' />";
            str+= "<input type=\'hidden\' value=\'"+ShieldProcedureCuttingDefault+"\' id==\'TypePanelsColorShieldMI_PanelCuttingMode\' name=\'TypePanelsColorShieldMI[PanelCuttingMode]\' />";
            str += "</form></div>";
            jQuery("#aperture-form").parent().append(str);
            jQuery("#sizes").dialog({

                modal: true,
                resizable: false,
                close: function( event, ui ) {
                    //удаляем диалог и элементы
                    jQuery("#sizes").dialog("destroy");
                    jQuery("#sizes").remove();
                },
                buttons: {
                    \''. Yii::t('steps', 'Отправить') .'\': function() {
                        if (data.ShieldProcedureCuttingDefault >= 0) {
                            jQuery("#' . __CLASS__ . '_PanelCuttingMode [value="+ShieldProcedureCuttingDefault+"]").attr("selected","selected");
                        }
                        jQuery("#aperture-form #sizes").remove();
                        var post = jQuery("form#cutting").serialize();
                        post +="&key="+ jQuery("#key").val()+"&calc=OptimalPanelsCuttingMC";
                        if(!jQuery("form#cutting #' . __CLASS__ . '_OnlyOneSizeSelected").prop("checked")) {
                            post += "&' . __CLASS__ . '%5BOnlyOneSizeSelected%5D=0";
                        } else {
                            post += "&' . __CLASS__ . '%5BOnlyOneSizeSelected%5D=1";
                        }
                        if(!jQuery("form#cutting #' . __CLASS__ . '_ShieldAutoExtendEnabledSelected").prop("checked")) {
                            post += "&' . __CLASS__ . '%5BShieldAutoExtendEnabledSelected%5D=0";
                        }
                        if(!jQuery("form#cutting #' . __CLASS__ . '_MinCutsEnabledSelected").prop("checked")) {
                            post += "&' . __CLASS__ . '%5BMinCutsEnabledSelected%5D=0";
                        } else {
                            post += "&' . __CLASS__ . '%5BMinCutsEnabledSelected%5D=1";
                        }
                        jQuery.ajax({type: "POST",
                                    url:"' . Yii::app()->createUrl("/steps/default/ajaxdata") . '",
                                    data:post,
                                    async: false,
                                    success: function(data) {
                                        contentJSON = $.parseJSON(data);
                                        if (!contentJSON.ShieldCuttingComplete) {
                                            simpleAlertDialog({ firstTr: objDataSimpleAlert.firstTr, dialogClass: "errorMsg" }, "' . OrderModel::translitatorForPhantomJS(Yii::t('steps', 'Раскрой не удался!'), !Yii::app()->container->autoCalc).'");
                                            console.log("7");
                                            return true;
                                        }
                                        if (contentJSON.ShieldPanelCount > 0 ) {
                                            numberSizes = contentJSON.SelectedSizes;
                                            numberonlyOne = contentJSON.SelectedOnlyOne;
                                            numberauto =contentJSON.SelectedAutoExtend;;
                                            numberMinCuts = contentJSON.SelectedMinCuts;
                                            var heightShield = contentJSON.ShieldRealHeight;
                                            svgInfo("ShieldHeight","aperture",heightShield);
                                            var widthShield = "' . $width . '";
                                            var counts =contentJSON.ShieldPanelCount;
                                            panels = contentJSON.ShieldWholePanels;
                                            maximal = panels[1];
                                            for (var kis in panels) {
                                                panels[kis] = parseInt( panels[kis]);
                                                if (panels[kis] > maximal) {
                                                    maximal = panels[kis];
                                                }
                                            }


                                            panelsCutting = contentJSON.ShieldPanels;
                                            isCut = [contentJSON.ShieldTopPanelIsCut,contentJSON.ShieldBottomPanelIsCut];
                                            svg = jQuery("#apertureSVG svg g#Shield");
                                            clearElements(svg,".panels"); 
                                            //createPanels(svg,counts, panels, panelsCutting,heightShield, widthShield, isCut);
                                            var VerticalPanel = ' . Yii::app()->container->VerticalPanel . ';
                                            createPanels_FalsePanel(svg, counts, panels, panelsCutting, heightShield, widthShield, isCut, 0, 0, 0, 0, VerticalPanel);
                                            sortablesEl(panels);
                                            for (var tmp in contentJSON.CuttingVariantsUnmodifieds) {
                                                a = contentJSON.CuttingVariantsUnmodifieds[tmp].cutting;
                                                var b = [];
                                                for( key in a) {
                                                    b.push(a[key]);
                                                }
                                                c = b.reverse();
                                                contentJSON.CuttingVariantsUnmodifieds[tmp].cutting = c;
                                            }

                                            createOption(contentJSON.CuttingVariantsUnmodifieds,"cutting","VariantCutting", jQuery("#' . __CLASS__ . '_typeSize").attr("id"),0,1);
                                            $("#TypePanelsColorShieldMI_VariantCutting").val(contentJSON.VariantCutting);
                                            var sizesN = "";
                                            for(var temp in contentJSON.SelectedSizes){
                                                sizesN += contentJSON.SelectedSizes[temp];
                                                if(temp != contentJSON.SelectedSizes.length - 1){
                                                    sizesN +=",";
                                                }
                                            }
                                            if(contentJSON.SelectedOnlyOne == 1) {
                                                OneN = yesStr;
                                            } else {
                                                OneN = noStr;
                                            }
                                            if(contentJSON.SelectedAutoExtend == 1) {
                                                AutoN = yesStr;
                                            } else {
                                                AutoN = noStr;
                                            }
                                            if(contentJSON.SelectedMinCuts == 1) {
                                                MinN = yesStr;
                                            } else {
                                                MinN = noStr;
                                            }
                                            strSize = "<li id=\"li_selectedSize\"><div class=\"grid_3 alpha\">' . Yii::t('steps', 'Выбранные типоразмеры') . ':</div><div class=\"grid_2 new-step\">"+sizesN+"</div></li>";
                                            strOnly = "<li id=\"li_SelectedOnlyOne\"><div class=\"grid_3 alpha\">' . Yii::t('steps', 'Выбран только один типоразмер') . ':</div><div class=\"grid_2 new-step\">"+OneN+"</div></li>";
                                            strAuto = "<li id=\"li_SelectedAutoExtend\"><div class=\"grid_3 alpha\">' . Yii::t('steps', 'Выбрана автонадставка над профилем') . ':</div><div class=\"grid_2 new-step\">"+AutoN+"</div></li>";
                                            strMin = "<li id=\"li_SelectedMinCuts\"><div class=\"grid_3 alpha\">' . Yii::t('steps', 'Выбрано не резать по усилению') . ':</div><div class=\"grid_2 new-step\">"+MinN+"</div></li>";
                                            if($("li#li_selectedSize")){
                                                $("li#li_selectedSize").remove();
                                            }
                                            if($("li#li_SelectedOnlyOne")){
                                                $("li#li_SelectedOnlyOne").remove();
                                            }
                                            if($("li#li_SelectedAutoExtend")){
                                                $("li#li_SelectedAutoExtend").remove();
                                            }
                                            if($("li#li_SelectedMinCuts")){
                                                $("li#li_SelectedMinCuts").remove();
                                            }
                                            $("#' . __CLASS__ . ' ul.clearfix").append(strSize);
                                            $("#' . __CLASS__ . ' ul.clearfix").append(strOnly);
                                            $("#' . __CLASS__ . ' ul.clearfix").append(strAuto);
                                            $("#' . __CLASS__ . ' ul.clearfix").append(strMin);
                                        } else {
                                            simpleAlertDialog({ firstTr: objDataSimpleAlert.firstTr, dialogClass: "errorMsg" }, "' . OrderModel::translitatorForPhantomJS(Yii::t('steps', 'Раскрой не удался!'), !Yii::app()->container->autoCalc).'");
                                            console.log("8");
                                        }
                                    },
                                    error:function() {
                                        $("body #full-loading").remove();
                                        simpleAlertDialog({ firstTr: objDataSimpleAlert.firstTr, dialogClass: "errorMsg" }, "' . OrderModel::translitatorForPhantomJS(Yii::t('steps', 'Раскрой не удался!'), !Yii::app()->container->autoCalc).'");
                                        console.log("9");
                                    }
                        });
                        post ="key="+ jQuery("#key").val()+"&calc=PanelsCuttingWithInfillsMC";
                        jQuery.ajax({type: "POST",
                                    url:"' . Yii::app()->createUrl("/steps/default/ajaxdata") . '",
                                    data:post,
                                    async: false,
                                    success: function(data) {
                                        fillsJSON = $.parseJSON(data);
                                        var heightShield = contentJSON.ShieldRealHeight;
                                        var widthShield = "' . $width . '";
                                        var fills =fillsJSON.ShieldInfills;
                                        var PhilMessage = fillsJSON.PhilMessage;
                                        if (PhilMessage != 0)
                                            simpleAlertDialog(objDataSimpleAlert, PhilMessage);
                                        svg = jQuery("#apertureSVG svg g#Shield");
                                        if (fillsJSON.ShieldInfillCount >0 ) {
                                            createFills(svg,fills,heightShield, widthShield);
                                        }
                                    }
                        });
                        jQuery("#' . __CLASS__ . '_typeSize option[value=\'"+currentTypes+"\']").prop("selected", true);
                        prevTypes = currentTypes;
                        $("#TypePanelsColorShieldMI_typeSize_em_").hide();
                        $(this).dialog("close");
                        $("#sizes").remove();
                        $(this).remove();
                        //функция по удалению установленных объектов на щит (окна и калитки)
                        var container_WicketInstalled       = ' . (int) Yii::app()->container->WicketInstalled . ';
                        var container_ObjectTypeWindow      = "' . (string) Yii::app()->container->ObjectTypeWindow . '";
                        var container_EmbeddedObjectsCounts = 0;
                        if ((container_WicketInstalled || container_ObjectTypeWindow == "window") && container_EmbeddedObjectsCounts) {
                            delInstallObject();
                        }

                        deletePanoramicWicket();

                    },
	    	}
	    });
        };';
        $query .= '
            function switchColorShield(data) {
                //внешний цвет щита
                var str = jQuery("#' . __CLASS__ . '_colorOutside :selected").text();
                if (str.indexOf("RAL") != -1 || str.indexOf("Нестандарт") != -1) {
                    createСolorOptions(str, "#' . __CLASS__ . '_Colout");
                    jQuery("#' . __CLASS__ . '_Colout [value=\"По RAL\"]").attr("selected", "selected");
                    jQuery("#' . __CLASS__ . '_Colout_n").removeAttr("disabled");
                    //в случае нестандартного цвета переобпределяем покарску
                    if (str.indexOf("Нестандарт") != -1) {
                        jQuery("#' . __CLASS__ . '_Colout_n").val(jQuery("#' . __CLASS__ . '_Colout_n").val());
                        jQuery("#' . __CLASS__ . '_Pokout").attr("checked","checked");
                    } else {
                        jQuery("#' . __CLASS__ . '_Colout_n").val(str.substr(3));
                        jQuery("#' . __CLASS__ . '_Pokout").removeAttr("checked");
                    }
                } else {
                    createСolorOptions(str, "#' . __CLASS__ . '_Colout");
                    jQuery("#' . __CLASS__ . '_Colout [value=\""+str+"\"]").attr("selected", "selected");
                    jQuery("#' . __CLASS__ . '_Colout_n").attr("disabled", "disabled");
                    jQuery("#' . __CLASS__ . '_Colout_n").val(0);
                    jQuery("#' . __CLASS__ . '_Pokout").removeAttr("checked");
                }
                
                //внутренний цвет щита
                var str2 = jQuery("#' . __CLASS__ . '_colorInside :selected").text();
                if (str2.indexOf("RAL") != -1 || str2.indexOf("Нестандарт") != -1) {
                    createСolorOptions(str2, "#' . __CLASS__ . '_Colin");
                    jQuery("#' . __CLASS__ . '_Colin [value=\"По RAL\"]").attr("selected", "selected");
                    jQuery("#' . __CLASS__ . '_Colin_n").removeAttr("disabled");
                    //в случае нестандартного цвета переобпределяем покарску
                    if (str2.indexOf("Нестандарт") != -1) {
                        jQuery("#' . __CLASS__ . '_Colin_n").val(jQuery("#' . __CLASS__ . '_Colin_n").val());
                        jQuery("#' . __CLASS__ . '_Pokin").attr("checked","checked");
                    } else {
                        jQuery("#' . __CLASS__ . '_Colin_n").val(str2.substr(3));
                        jQuery("#' . __CLASS__ . '_Pokin").removeAttr("checked");
                    }
                } else {
                    createСolorOptions(str2, "#' . __CLASS__ . '_Colin");
                    jQuery("#' . __CLASS__ . '_Colin [value=\""+str2+"\"]").attr("selected", "selected");
                    jQuery("#' . __CLASS__ . '_Colin_n").attr("disabled", "disabled");
                    jQuery("#' . __CLASS__ . '_Colin_n").val(0);
                    jQuery("#' . __CLASS__ . '_Pokin").removeAttr("checked");
                }
            }

            //обновление списка цветов
            function createСolorOptions(value, elemId){
                var colorList = [
                    \''. Yii::t('steps', 'белый') .'\',
                    \''. Yii::t('steps', 'коричневый') .'\',
                    \''. Yii::t('steps', 'По образцу') .'\',
                    \''. Yii::t('steps', 'По RAL') .'\',
                    \''. Yii::t('steps', 'вручную') .'\'
                ];
                jQuery(elemId + " option").not(":first").remove();
                for (var i = 0; i < colorList.length; i++) {
                    jQuery(elemId).append( jQuery("<option value=\""+colorList[i]+"\">"+colorList[i]+"</option>"));
                }
                if (value.indexOf("RAL") == -1) {
                    jQuery(elemId).append( jQuery("<option value=\""+value+"\">"+value+"</option>"));
                }
            }
        ';
        if (Yii::app()->container->ShieldWidth > 0) {
            $width = Yii::app()->container->ShieldWidth;
        } else {
            $width = Yii::app()->container->Bh;
        }
        $query .=   'function sortablesEl(object) {
                        if( $("#sortablePanels")) {
                            $("#sortablePanels").remove();
                        }
                        str = "<ul id=\"sortablePanels\">";
                        for (pan in object) {
                            str +="<li>"+object[pan]+"</li>"
                        }

                        str+="</ul>";
                        $("li#li_rearranged .grid_2").append(str);
                        $("#sortablePanels").sortable({ stop: function(event, ui) {
	                    var post = jQuery("#cutting").serialize();
                            post += "&"+jQuery("#aperture-form").serialize();
                            post += getVariantUmodified();
                            post +="&key="+ jQuery("#key").val()+"&calc=OptimalPanelsCuttingMC";

                            jQuery.ajax({type: "POST",
                                url:"' . Yii::app()->createUrl("/steps/default/ajaxdata") . '",
                                data:post,
                                async: false,
                                success: function(data) {
                                    contentJSON = $.parseJSON(data);
                                    if (contentJSON.ShieldPanelCount > 0 ) {
                                        var heightShield = contentJSON.ShieldRealHeight;
                                        svgInfo("ShieldHeight","aperture",heightShield);
                                        var widthShield = "' . $width . '";
                                        var counts = contentJSON.ShieldPanelCount;
                                        panels = contentJSON.ShieldWholePanels;
                                        maximal = panels[1];
                                        for (var kis in panels) {
                                            panels[kis] = parseInt( panels[kis]);
                                            if (panels[kis] > maximal) {
                                                maximal = panels[kis];
                                            }
                                        }
                                        panelsCutting = contentJSON.ShieldPanels;
                                        isCut = [contentJSON.ShieldTopPanelIsCut,contentJSON.ShieldBottomPanelIsCut];

                                        svg = jQuery("#apertureSVG svg g#Shield");
                                        clearElements(svg,".panels");
                                        //createPanels(svg,counts, panels, panelsCutting,heightShield, widthShield, isCut);
                                        var VerticalPanel = ' . Yii::app()->container->VerticalPanel . ';
                                        createPanels_FalsePanel(svg, counts, panels, panelsCutting, heightShield, widthShield, isCut, 0, 0, 0, 0, VerticalPanel);
                                        post ="key="+ jQuery("#key").val()+"&calc=PanelsCuttingWithInfillsMC";
                                        jQuery.ajax({type: "POST",
                                            url:"' . Yii::app()->createUrl("/steps/default/ajaxdata") . '",
                                            data:post,
                                            async: false,
                                            success: function(data) {
                                                fillsJSON = $.parseJSON(data);
                                                var heightShield = contentJSON.ShieldRealHeight;
                                                var widthShield = "' . $width . '";
                                                var fills =fillsJSON.ShieldInfills;
                                                var PhilMessage = fillsJSON.PhilMessage;
                                                if (PhilMessage != 0)
                                                    simpleAlertDialog(objDataSimpleAlert, PhilMessage);
                                                svg = jQuery("#apertureSVG svg g#Shield");
                                                if (fillsJSON.ShieldInfillCount >0 ) {
                                                    createFills(svg,fills,heightShield, widthShield);
                                                }
                                            }
                                        });
                                    } else {
                                        simpleAlertDialog({ firstTr: objDataSimpleAlert.firstTr, dialogClass: "errorMsg" }, "' . OrderModel::translitatorForPhantomJS(Yii::t('steps', 'Раскрой не удался!'), !Yii::app()->container->autoCalc).'");
                                        console.log("10");
                                    }
                                },
                                error:function() {
                                    $("body #full-loading").remove();
                                    simpleAlertDialog({ firstTr: objDataSimpleAlert.firstTr, dialogClass: "errorMsg" }, "' . OrderModel::translitatorForPhantomJS(Yii::t('steps', 'Раскрой не удался!'), !Yii::app()->container->autoCalc).'");
                                    console.log("11");
                                }
                                });
                            }
                        });
                    }
        ';
        $query .= ' jQuery("#' . __CLASS__ . '_Pokout").live("click",function(){return false});';
        //$query .= ' jQuery("#' . __CLASS__ . '_Colout").attr("disabled", "disabled");';
        $query .= ' jQuery("#' . __CLASS__ . '_Colout_n").attr("disabled", "disabled");';
        $query .= ' jQuery("#' . __CLASS__ . '_Pokin").live("click",function(){return false});';
        //$query .= ' jQuery("#' . __CLASS__ . '_Colin").attr("disabled", "disabled");';
        $query .= ' jQuery("#' . __CLASS__ . '_Colin_n").attr("disabled", "disabled");';
        $query .= '
            //закрываем видимость и возможность выбора алюминиевой облицовки
            var form_li_AlFacing = $("#li_AlFacing");
            form_li_AlFacing.hide();
            //закрываем видимость и возможность выбора алюминиевой облицовки 2010
            var form_li_AlFacing2010 = $("#li_AlFacing2010");
            form_li_AlFacing2010.hide();
            //закрываем видимость и возможность выбора Панорамной панели
            var form_li_PanoramicPanel = $("#li_PanoramicPanel");
            var container_PanoramicPanel       = ' . (int) Yii::app()->container->PanoramicPanel . ';
            if (container_PanoramicPanel == 0 && (container_TypeF == "Для секционных ворот".toLowerCase() || container_TypeF == "Алюминиевый".toLowerCase())) {
                form_li_PanoramicPanel.hide();
            }
            if($.isEmptyObject(contentAl)) {
                jQuery("#' . __CLASS__ . '_AlFacing").attr("disabled", "disabled");
            };
            if($.isEmptyObject(contentAl2010)) {
                jQuery("#' . __CLASS__ . '_AlFacing2010").attr("disabled", "disabled");
            };
        ';
        $query .= '
            content = {};
            content = clone(contentPanels);
        ';
        $query .= '
            createOption(content,"panel_type_id","typePanel",0,0);
            jQuery("#' . __CLASS__ . '_PanelCuttingMode").empty();
            jQuery("#' . __CLASS__ . '_PanelCuttingMode").append("<option value=\"\">'.Yii::t('steps', 'Выберите').'</option>");
            jQuery("#' . __CLASS__ . '_PanelSizeForCutting").attr("disabled","disabled");
            //ошибка наличия паармтера панеля
            var err = 0;
        ';
        if (Yii::app()->container->AlFacing == 1) {
            $query .= '
                jQuery("#' . __CLASS__ . '_AlFacing").attr("checked","checked");
                content = {};
                content = clone(contentAl);
                createOption(content,"panel_type_id","typePanel","' . __CLASS__ . '_typePanel",0);
            ';
        }
        if (Yii::app()->container->AlFacing2010 == 1) {
            $query .= '
                jQuery("#' . __CLASS__ . '_AlFacing2010").attr("checked","checked");
                content = {};
                content = clone(contentAl2010);
                createOption(content,"panel_type_id","typePanel","' . __CLASS__ . '_typePanel",0);
            ';
        }
        if (Yii::app()->container->typePanel > 0 && (Yii::app()->container->TypeF == "Для секционных ворот" || Yii::app()->container->TypeF == "Алюминиевый")) {
            $query .= '
                if (err == 0) {
                    jQuery("#' . __CLASS__ . '_typePanel [value=' . Yii::app()->container->typePanel . ']").attr("selected","selected");
                    console.log("typePanel_1");
                    typePanel = content["panel_type_id"][' . Yii::app()->container->typePanel . '];
                    if (typeof(typePanel) != "undefined") {
                        createOption(content["panel_type_id"][' . Yii::app()->container->typePanel . '],"panel_design_id","design", "' . __CLASS__ . '_typePanel",0);
                    }   else {
                        err = err + 1;
                    }
                }
            ';
        }
        if (Yii::app()->container->design > 0 && (Yii::app()->container->TypeF == "Для секционных ворот" || Yii::app()->container->TypeF == "Алюминиевый")) {
            $query .= '
                if (err == 0) {
                    jQuery("#' . __CLASS__ . '_design [value=' . Yii::app()->container->design . ']").attr("selected","selected");

                    //проверяем определены ли панели с выбранным типом панелей
                    design = content["panel_type_id"][' . Yii::app()->container->typePanel . ']["panel_design_id"][' . Yii::app()->container->design . '];
                    if (typeof(design) != "undefined") {
                        console.log("design");
                        createOption(content["panel_type_id"][' . Yii::app()->container->typePanel . ']["panel_design_id"][' . Yii::app()->container->design . '],"panel_design_add_id","design2","' . __CLASS__ . '_design",0);
                    }   else {
                        err = err + 1;
                    }
                }
            ';
        }
        if (Yii::app()->container->design2 > 0 && (Yii::app()->container->TypeF == "Для секционных ворот" || Yii::app()->container->TypeF == "Алюминиевый")) {
            $query .= '
                if (err == 0) {
                    jQuery("#' . __CLASS__ . '_design2 [value=' . Yii::app()->container->design2 . ']").attr("selected","selected");

                    //проверяем определены ли панели с выбранным дизайном
                    design2 = content["panel_type_id"][' . Yii::app()->container->typePanel . ']["panel_design_id"][' . Yii::app()->container->design . ']["panel_design_add_id"][' . Yii::app()->container->design2 . '];
                    if (typeof(design2) != "undefined") {
                        console.log("design2");
                        createOption(content["panel_type_id"][' . Yii::app()->container->typePanel . ']["panel_design_id"][' . Yii::app()->container->design . ']["panel_design_add_id"][' . Yii::app()->container->design2 . '],"panel_structure_id","structure", "' . __CLASS__ . '_design2",0);
                    }   else {
                        err = err + 1;
                    }
                }
            ';
        }
        if (Yii::app()->container->structure > 0 && (Yii::app()->container->TypeF == "Для секционных ворот" || Yii::app()->container->TypeF == "Алюминиевый")) {
            $query .= '
                if (err == 0) {
                    jQuery("#' . __CLASS__ . '_structure [value=' . Yii::app()->container->structure . ']").attr("selected","selected");

                    //проверяем определены ли панели с выбранным дизайном 2
                    structure = content["panel_type_id"][' . Yii::app()->container->typePanel . ']["panel_design_id"][' . Yii::app()->container->design . ']["panel_design_add_id"][' . Yii::app()->container->design2 . ']["panel_structure_id"][' . Yii::app()->container->structure . '];

                    if (typeof(structure) != "undefined") {
                        console.log("structure");
                        createOption(content["panel_type_id"][' . Yii::app()->container->typePanel . ']["panel_design_id"][' . Yii::app()->container->design . ']["panel_design_add_id"][' . Yii::app()->container->design2 . ']["panel_structure_id"][' . Yii::app()->container->structure . '],"color_outside_id","colorOutside", "' . __CLASS__ . '_structure",0);
                    } else {
                        err = err + 1;
                    }
                }
            ';
        }
        if (Yii::app()->container->colorOutside > 0 && (Yii::app()->container->TypeF == "Для секционных ворот" || Yii::app()->container->TypeF == "Алюминиевый")) {
            $query .= '
                if (err == 0) {
                    var colorOutsideSelected = jQuery("#' . __CLASS__ . '_colorOutside [value=' . Yii::app()->container->colorOutside . ']").attr("selected","selected").text();
                    jQuery("#' . __CLASS__ . '_Colout").append( jQuery("<option value=\"" + colorOutsideSelected + "\">" + colorOutsideSelected + "</option>"));
                    //проверяем определены ли панели с выбранной структурой
                    colorOutside = content["panel_type_id"][' . Yii::app()->container->typePanel . ']["panel_design_id"][' . Yii::app()->container->design . ']["panel_design_add_id"][' . Yii::app()->container->design2 . ']["panel_structure_id"][' . Yii::app()->container->structure . ']["color_outside_id"][' . Yii::app()->container->colorOutside . '];
                    if (typeof(colorOutside) != "undefined") {
                        console.log("colorOutside");
                        createOption(content["panel_type_id"][' . Yii::app()->container->typePanel . ']["panel_design_id"][' . Yii::app()->container->design . ']["panel_design_add_id"][' . Yii::app()->container->design2 . ']["panel_structure_id"][' . Yii::app()->container->structure . ']["color_outside_id"][' . Yii::app()->container->colorOutside . '],"color_inside_id","colorInside", "' . __CLASS__ . '_colorOutside",0);
                    } else {
                        err = err + 1;
                    }
                }
            ';
        }
        if (Yii::app()->container->colorInside > 0 && (Yii::app()->container->TypeF == "Для секционных ворот" || Yii::app()->container->TypeF == "Алюминиевый")) {
            $query .= '
                var colorInsideSelected = jQuery("#' . __CLASS__ . '_colorInside [value=' . Yii::app()->container->colorInside . ']").attr("selected","selected").text();
                jQuery("#' . __CLASS__ . '_Colin").append( jQuery("<option value=\"" + colorInsideSelected + "\">" + colorInsideSelected + "</option>"));

                if (err == 0) {
                    //проверяем определены ли панели с выбранным внешним цветом панелей
                    colorInside = content["panel_type_id"][' . Yii::app()->container->typePanel . ']["panel_design_id"][' . Yii::app()->container->design . ']["panel_design_add_id"][' . Yii::app()->container->design2 . ']["panel_structure_id"][' . Yii::app()->container->structure . ']["color_outside_id"][' . Yii::app()->container->colorOutside . ']["color_inside_id"][' . Yii::app()->container->colorInside . '];
                    if (typeof(colorInside) != "undefined") {
                        typeSize = "' . Yii::app()->container->typeSize . '";
                        if (bStart == 0) {
                            createOption(content["panel_type_id"][' . Yii::app()->container->typePanel . ']["panel_design_id"][' . Yii::app()->container->design . ']["panel_design_add_id"][' . Yii::app()->container->design2 . ']["panel_structure_id"][' . Yii::app()->container->structure . ']["color_outside_id"][' . Yii::app()->container->colorOutside . ']["color_inside_id"][' . Yii::app()->container->colorInside . '],"typeSize","typeSize", "' . __CLASS__ . '_colorInside",0);
                        }
                    } else {
                        err = err + 1;
                    }
                }
            ';
        }
        if (Yii::app()->container->typeSize > 0 && (Yii::app()->container->TypeF == "Для секционных ворот" || Yii::app()->container->TypeF == "Алюминиевый")) {
            $query .= 'jQuery("#' . __CLASS__ . '_typeSize [value=' . Yii::app()->container->typeSize . ']").attr("selected","selected");';
        }
        $cuttingJSON = json_encode(Yii::app()->container->CuttingVariantsUnmodifieds);
        $query .= '
            createOption(' . $cuttingJSON . ',"cutting","VariantCutting", jQuery("#' . __CLASS__ . '_typeSize").attr("id"),0,1);
        ';
        if (Yii::app()->container->VariantCutting > 0 && (Yii::app()->container->TypeF == "Для секционных ворот" || Yii::app()->container->TypeF == "Алюминиевый")) {
            $query .= 'jQuery("#' . __CLASS__ . '_VariantCutting [value=' . Yii::app()->container->VariantCutting . ']").attr("selected","selected");';
        }

        $PanelCuttingMode = Yii::app()->container->PanelCuttingMode;
        //if (Yii::app()->container->PanelCuttingMode > 0) {
        if (empty($PanelCuttingMode)) {
            if(Yii::app()->container->ShieldProcedureCuttingDefault > 0) {
                Yii::app()->container->PanelCuttingMode = 3;
        } else {
                Yii::app()->container->PanelCuttingMode = 4;
        }

            $query .= 'jQuery("#' . __CLASS__ . '_PanelCuttingMode [value=' . Yii::app()->container->PanelCuttingMode . ']").attr("selected","selected");';
        }
        $selected = 3;
        $query .= 'createOption(cutting, "cuttings", "PanelCuttingMode", 0, '. Yii::app()->container->PanelCuttingMode .');';

        if ((Yii::app()->container->PanelCuttingMode == 1) || (Yii::app()->container->PanelCuttingMode == 2)) {
            $query .= '
            jQuery("#' . __CLASS__ . '_PanelSizeForCutting").removeAttr("disabled");';
        }
        if (Yii::app()->container->PanelSizeForCutting > 0) {
            $query .= '
            jQuery("#' . __CLASS__ . '_PanelSizeForCutting").val(' . Yii::app()->container->PanelSizeForCutting . ');';
        }
        if (is_array(Yii::app()->container->VariantUmodified)) {
            $query .= ' sortablesEl(' . json_encode(Yii::app()->container->ShieldWholePanels) . ');';
        }
        if (Yii::app()->container->Pokout > 1) {
            $query .= ' jQuery("#' . __CLASS__ . '_Pokout").attr("checked","checked");';
        }
        if (Yii::app()->container->Pokin > 1) {
            $query .= ' jQuery("#' . __CLASS__ . '_Pokin").attr("checked","checked");';
        }
        if ((gettype(Yii::app()->container->Colout) == "string") && Yii::app()->container->Colout) {
            $query .= '
                jQuery("#' . __CLASS__ . '_Colout").removeAttr("disabled");
                options = jQuery("#' . __CLASS__ . '_Colout option");
                var bools = 0;
                for(var key in options){
                    if(options[key].value == "' . Yii::app()->container->Colout . '") {
                    bools = 1;
                    }
                }
                if (bools == 0) {
                    jQuery("#' . __CLASS__ . '_Colout").append( jQuery("<option value=\"' . Yii::app()->container->Colout . '\">' . Yii::app()->container->Colout . '</option>"));
                }
                    jQuery("#' . __CLASS__ . '_Colout [value=\"' . Yii::app()->container->Colout . '\"]").attr("selected", "selected");
            ';
            if ((Yii::app()->container->Colout == "По RAL")) {
                $query .= 'jQuery("#' . __CLASS__ . '_Colout_n").removeAttr("disabled");';
            }
        }
        if ((gettype(Yii::app()->container->Colin) == "string") && Yii::app()->container->Colin) {
            $query .= '
                jQuery("#' . __CLASS__ . '_Colin").removeAttr("disabled");
                options = jQuery("#' . __CLASS__ . '_Colin option");
                var bools = 0;
                for(var key in options){
                    if(options[key].value == "' . Yii::app()->container->Colin . '") {
                    bools = 1;
                    }
                }
                if (bools == 0) {
                    jQuery("#' . __CLASS__ . '_Colin").append( jQuery("<option value=\"' . Yii::app()->container->Colin . '\">' . Yii::app()->container->Colin . '</option>"));
                }
                jQuery("#' . __CLASS__ . '_Colin [value=\"' . Yii::app()->container->Colin . '\"]").attr("selected","selected");
            ';
            if ((Yii::app()->container->Colin == "По RAL")) {
                $query .= 'jQuery("#' . __CLASS__ . '_Colin_n").removeAttr("disabled");';
            }
        }
        if (Yii::app()->container->Colout_n != 0) {
            $query .= 'jQuery("#' . __CLASS__ . '_Colout_n").removeAttr("disabled");
                       jQuery("#' . __CLASS__ . '_Colout_n").val("' . Yii::app()->container->Colout_n . '");
            ';
        }
        if (Yii::app()->container->Colin_n != 0) {
            $query .= 'jQuery("#' . __CLASS__ . '_Colin_n").removeAttr("disabled");
                       jQuery("#' . __CLASS__ . '_Colin_n").val("' . Yii::app()->container->Colin_n . '");
            ';
        }
        if (Yii::app()->container->ShieldMinTop > 0) {
            $query .= 'minmalT =' . Yii::app()->container->ShieldMinTop . ';';
        }
        if (Yii::app()->container->ShieldMinBottom > 0) {
            $query .= 'minmalT =' . Yii::app()->container->ShieldMinBottom . ';';
        }
        if (count(Yii::app()->container->ShieldWholePanels) > 0) {
            $wholeArray = Yii::app()->container->ShieldWholePanels;
            $query .= 'maximal =' . max($wholeArray) . ';';
        }
        if (count(Yii::app()->container->SelectedSizes) > 0) {
            $query .= '
                showPanelStep();
                var SelectedSizes = ' . json_encode(Yii::app()->container->SelectedSizes) . ';
                var SelectedOnlyOne = ' . Yii::app()->container->SelectedOnlyOne . ';
                SelectedAutoExtend =' . Yii::app()->container->SelectedAutoExtend . ';
                SelectedMinCuts = ' . Yii::app()->container->SelectedMinCuts . ';
                var sizesN = "";
                for(var temp in SelectedSizes){
                    sizesN += SelectedSizes[temp];
                    if(temp != SelectedSizes.length - 1){
                        sizesN +=",";
                    }
                }
                if(SelectedOnlyOne == 1) {
                    OneN = yesStr;
                } else {
                    OneN = noStr;
                }
                if(SelectedAutoExtend == 1) {
                    AutoN = yesStr;
                } else {
                    AutoN = noStr;
                }
                if(SelectedMinCuts == 1) {
                    MinN = yesStr;
                } else {
                    MinN = noStr;
                }
                strSize = "<li id=\"li_selectedSize\"><div class=\"grid_3 alpha\">' . Yii::t('steps', 'Выбранные типоразмеры') . ':</div><div class=\"grid_2 new-step\">"+sizesN+"</div></li>";
                strOnly = "<li id=\"li_SelectedOnlyOne\"><div class=\"grid_3 alpha\">' . Yii::t('steps', 'Выбран только один типоразмер') . ':</div><div class=\"grid_2 new-step\">"+OneN+"</div></li>";
                strAuto = "<li id=\"li_SelectedAutoExtend\"><div class=\"grid_3 alpha\">' . Yii::t('steps', 'Выбрана автонадставка над профилем') . ':</div><div class=\"grid_2 new-step\">"+AutoN+"</div></li>";
                strMin = "<li id=\"li_SelectedMinCuts\"><div class=\"grid_3 alpha\">' . Yii::t('steps', 'Выбрано не резать по усилению') . ':</div><div class=\"grid_2 new-step\">"+MinN+"</div></li>";
                if($("li#li_selectedSize")){
                    $("li#li_selectedSize").remove();
                }
                if($("li#li_SelectedOnlyOne")){
                    $("li#li_SelectedOnlyOne").remove();
                }
                if($("li#li_SelectedAutoExtend")){
                    $("li#li_SelectedAutoExtend").remove();
                }
                if($("li#li_SelectedMinCuts")){
                    $("li#li_SelectedMinCuts").remove();
                }
                $("#' . __CLASS__ . ' ul.clearfix").append(strSize);
                $("#' . __CLASS__ . ' ul.clearfix").append(strOnly);
                $("#' . __CLASS__ . ' ul.clearfix").append(strAuto);
                $("#' . __CLASS__ . ' ul.clearfix").append(strMin);
            ';
        }
        $query .= '
            //Шаг филенки
            function showPanelStep(){
                if($("li#li_PanelStep")){
                    $("li#li_PanelStep").remove();
                }
                var selectedTypeSize = jQuery("#' . __CLASS__ . '_typeSize option:selected").text();
                var arr = selectedTypeSize.split("/");
                if (arr.length !== 1 ) {
                    var panelStep = arr.pop();
                    $("li#li_Colin_n").after("<li id=\"li_PanelStep\"><div class=\"grid_3 alpha\">'.Yii::t('steps', 'Шаг филенки').':</div><div class=\"grid_2 new-step\">"+panelStep+"</div></li>");
                } else {
                    jQuery("li#li_PanelStep").remove();
                }
            }

            //Формирование данных по VariantUmodified для передачи в POST
            //Если передать параметр VariantUmodified расчитается из VariantCutting, если не передавать из sortablePanels
            function getVariantUmodified(){
                var result = "";
                if (arguments[0] === undefined) {
                    var liPanger = $("#sortablePanels li");
                    for (i = 0; i < liPanger.length; i++) {
                        result +="&' . __CLASS__ . '%5BVariantUmodified%5D%5B%5D="+liPanger[i].innerHTML;
                    }
                } else {
                    var arr = jQuery("#TypePanelsColorShieldMI_VariantCutting option:selected").text().split(",");
                    for (i = 0; i < arr.length; i++) {
                        if (arr[i]) {
                            result +="&' . __CLASS__ . '%5BVariantUmodified%5D%5B%5D="+arr[i];
                        }
                    }
                }

                return result;
            }
        ';
        // выбор значения по умолчанию для Типа Панелей ("Без защиты от защемления")
        if (Yii::app()->container->typePanel == 0) {
            $query .= '
                var defaultTypePanelA;
                var defaultTypePanelB;
                if ($("#' . __CLASS__ . '_typePanel option").length < 3) {
//                    defaultTypePanelA = $("#' . __CLASS__ . '_typePanel option:first").next("option");
//                    createOption(content["panel_type_id"][defaultTypePanelA.val()],"panel_design_id","design", defaultTypePanelA.parent().attr("id"),0);
//                    defaultTypePanelA.prop("selected", true);
                } else {
                    $("#' . __CLASS__ . '_typePanel option").each(function(idx, elem){
                        if ($(elem).text() === \''. Yii::t('steps', "Без защиты от защемления") .'\') {
                            defaultTypePanelB = $(elem);
                        }
                    });
                    if (defaultTypePanelB !== undefined) {
                        defaultTypePanelB.prop("selected", true);
                        createOption(content["panel_type_id"][defaultTypePanelB.val()],"panel_design_id","design", defaultTypePanelB.parent().attr("id"),0);
                    }
                }
            ';
        }

        //активирует/деактивирует элементы формы
        $query .= '
            function disableElements(){
                var elems = [\'typePanel\',\'design\',\'design2\',\'structure\',\'colorOutside\',\'colorInside\',\'typeSize\',\'VariantCutting\',\'PanelCuttingMode\',\'PanelSizeForCutting\'];
                var checked = $(\'#TypePanelsColorShieldMI_PanoramicPanel\').prop(\'checked\');

                for (var i in elems) {
                    $(\'#TypePanelsColorShieldMI_\' + elems[i]).prop(\'disabled\', checked);
                }
            }

            disableElements();

            var  containerPanoramic = {};
            var  containerSandwich = {};

            function deletePanoramicWicket(){
                //удаление панорамной калитки с рисунка(svg)
                if ('. Yii::app()->container->WicketInstalled .' == 0) {
                    $(\'g[id^=gWicket]\').remove();
                }
                //обнулить переменные с формы TypePanelsColorShieldMI
                jQuery.ajax({
                    type: \'POST\',
                    url:\'' . Yii::app()->createUrl('/steps/default/ajaxdata') . '\',
                    data: $.param({
                        key: $(\'#key\').val(),
                        TypePanelsColorShieldMI: {
                            PanWicketInstalled:0,
                        }
                    }),
                    async: false,
                    success: function(data) {}
                });
            }

            // в случае если у нас филенка и к заказу еще нет раскроя то вызываем методы ниже
            var checkShieldPanels = $.parseJSON(\''. json_encode(Yii::app()->container->ShieldPanels) .'\'),
                checkShieldInfillStep = parseInt(\''. Yii::app()->container->ShieldInfillStep .'\');
            if(typeof(checkShieldPanels[1]) === \'undefined\' && checkShieldInfillStep > 0){
                jQuery("#' . __CLASS__ . '_typeSize option:contains(\''. Yii::app()->container->ShieldInfillStep .'\')").attr(\'selected\', \'selected\');
                showPanelStep();

                var post =jQuery("#' . __CLASS__ . '_typeSize").val();
                if(post != "") {
                    currentTypes =jQuery("#' . __CLASS__ . '_typeSize").val();
                    if(currentTypes==prevTypes) {
                        jQuery("#' . __CLASS__ . '_CurrentPanelType").val(post); ajaxs(1);
                    } else {
                        jQuery("#' . __CLASS__ . '_CurrentPanelType").val(post); ajaxs(0);
                    }
                }
            }
        ';
        $query .= '
            function delInstallObject() {
                //var container_ChangeShieldPanels          = ' . (int) Yii::app()->container->ChangeShieldPanels . ';
                var container_ChangeShieldPanels          = 1;
                console.log("container_ChangeShieldPanels = " + container_ChangeShieldPanels);

                if (container_ChangeShieldPanels){
                    var svgM = jQuery("#apertureSVG svg g#Shield");
                    //удяаляем объекты
                    //объекты для удаления на мастере отрисовки щита
                    var object = ' . json_encode(Yii::app()->container->EmbeddedObjects) . ';
                    //какой именно объект удаляем
                    var number = ' . Yii::app()->container->ObjectCount. ';
                    console.log("number = " + number);
                    console.log('. json_encode(Yii::app()->container->EmbeddedObjects) .');

                    var container_WicketInstalled          = ' . (int) Yii::app()->container->WicketInstalled . ';
                    var container_ObjectTypeWindow          = "' . (string) Yii::app()->container->ObjectTypeWindow . '";

                    for(var i in object){
                        number = i;
                        console.log("number(" + i + ") = " + number);
                        var post2 = "TypePanelsColorShieldMI%5BObjectType%5D="+object[number].type+"&TypePanelsColorShieldMI%5BElements%5D=" + number + "&key="+ jQuery("#key").val()+"&calc=DeleteObjectsMC";
                        jQuery.ajax({type: "POST",
                            url:"' . Yii::app()->createUrl("/steps/default/ajaxdata") . '",
                            data: post2,
                            async: false,
                            error: function(jqXHR, textStatus, errorThrown) {
                            },
                            success: function (data) {
                                //delete Collection[number];
                                //delete CollectionObject[number];
                                //delete CollectionNomen[number];
                            }
                        });

                        if (container_WicketInstalled) {
                            //удаляем калитку
                            $("g#gWicket"+ number).remove();
                            //удаляем замок в калитке
                            $("rect#rlock").remove();
                        }
                        if (container_ObjectTypeWindow == "window") {
                            //удалаяем окна
                            $("g#gWindow"+number).remove();
                        }
                    }
                }
            };
        ';

        $query.= '
            var typeSize =jQuery("#' . __CLASS__ . '_typeSize").val();
            if(typeSize == "" && '. json_encode(Yii::app()->container->autoCalc) .')
                simpleAlertDialog({ firstTr: objDataSimpleAlert.firstTr, dialogClass: "errorMsg" }, \''. OrderModel::translitatorForPhantomJS(Yii::t('steps', 'Раскрой не удался!'), false) .'\');
        ';

        $query.=$this->getTooltip('TypePanelsColorShieldMI_PanelSizeForCutting', '15');
        $query.=$this->getTooltip('TypePanelsColorShieldMI_Colout_n', '16');
        $query.=$this->getTooltip('TypePanelsColorShieldMI_Colin_n', '17');
        $query.=$this->getTooltip('TypePanelsColorShieldMI_AlFacing', '162');
        $query.=$this->getTooltip('TypePanelsColorShieldMI_AlFacing2010', '163');
        $query.=$this->getTooltip('bPrev', '178');
        $query.=$this->getTooltip('bNext', '177');
        return $query;
    }

    /**
     *
     * Проверка версии на дилера
     *
     *
     * @return bool
     */
    function checkVersion($value)
    {
        $bool = false;
        $inside = 0;
        $diler = 0;
        if (count($value) > 0) {
            if ($value[0] == "inside") {
                $inside = 1;
            } else {
                $diler = 1;
            }
            if (isset($value[1])) {
                if ($value[1] == "inside") {
                    $inside = 1;
                } else {
                    $diler = 1;
                }
            }
            if ($inside) {
                $bool = true;
            }
            if ((Yii::app()->container->DilerVersion == 1) && ($diler)) {
                $bool = true;
            }
        }

        return $bool;
    }

    public function forms($formName)
    {
        $result = '';
        $product = ProductModel::model()->findByPk(Yii::app()->container->productId);
        $dir = $product->dir_product;
        Yii::setPathOfAlias('interface', Yii::app()->basePath . '/../product/' . $dir . '/forms/');
        $model = Yii::app()->container->getForms($formName);
        $model->fill();
//        foreach ($model->elementsVariables as $key => $val) {
//            if ($val['typeForm'] == 'array'){
//                $result .= "<select name='{$key}' id='{$key}' title='{$val['name']}'>";
//                if (isset($model->constantVariables[$key])) {
//                    foreach ($model->constantVariables[$key] as $i) {
//                        $result .= "<option value='{$i}'>{$i}</option>";
//                    }
//                }
//                $result .= "</select>";
//            }
//            if ($val['typeForm'] == 'text'){
//                $result .= "<input name='{$key}' id='{$key}' type='text' maxlength='12' value='' title='{$val['name']}'>";
//            }
//        }
        //тест прошел
//        $str = CHtml::dropDownList('dddddd', '', [1,2,3]);
//        $result=json_encode($str);
        $str = Yii::app()->controller->renderPartial('form', array('models'=>$model), true);
//        $str .= $model->JavascriptExperssion();
        $result1=json_encode($str);
        $result2=json_encode($model->JavascriptExperssion());

        return array($result1,$result2);
    }

    private function commonJSFunctions(){
        $expressions['integerCheck'] = '
            if (this.value.match(/[^0-9]/g)) {
                this.value = this.value.replace(/[^0-9]/g, "");
            };
            if(this.value.length > 1) {
                if (this.value.match(/^0/g)) {
                    this.value = this.value.replace(/^0/g, "");
                }
            }';
        return $expressions;
    }
}