<?php

/**
 * Встраиваемые объекты
 * @example  здесь текст примера использования класса (необязательно)
 * PHP version 5.5
 * @category Yii
 * @author   Charnou Vitaliy <graffov87@gmail.com>
 */
class EmbeddedObjectsMI extends AbstractModelInterface {

    /**
     * Название модуля
     * @var string
     */
    public $nameModule = 'EmbeddedObjectsMI';

    /**
     * Список модулей, выполняемых до запуска формы
     * @var array
     */
    public $beforeCalculation = array(
        //'ShieldWeightCalculationMC',//временно убран запуск формы
        //'EmbeddedObjectsAutoMC',//add for automatic add wicket
        //'WindowsCalculationMC',//add for automatic add window
        //'WicketCalculationMC',
    );

    /**
     * Список модулей, которые выполняются при переходе на следующую форму
     * @var array
     */
    public $moduleCalculation = array(
        'EdgingProfileMC',
        //'WicketLockPositionCalculationMC',
        //'HandleCalculationMC',
        //'FormEmbeddedObjectsMC',
        //'ShieldWeightCalculationMC' //вызывается, чтоб сохранить вес заданный вручную
    );

    /**
     * Алгоритм
     * @return bool
     */
    public function Algorithm() {
        return true;
    }

    /**
     * Функция, которая возвращает имя формы
     * @return string
     */
    public function getTitle() {
        return Yii::t('steps', 'Встраиваемые объекты');
    }

    /**
     * Очищает список выбраных данных из сессии.
     * @return bool
     */
    public function clearStore() {
        Yii::app()->container->setStore(0, "formEmbedded");

        return false;
    }

    /**
     * Метод отвечающий за графическое отображение раскроя щита. Возвращает
     * строку, содержащую javascript.
     * @return string
     */
    public function getSVG() {
        $path = Yii::getPathOfAlias(Yii::app()->params['uploadModuleSVG']) . DIRECTORY_SEPARATOR . 'ShieldPanels.svg';
        if (is_file($path)) {
            $svg = new SVG($path);
            $str = $svg->getXML();
            $event = 0;
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
                
                Height = Height/k;

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
     * @return array правила валидации, которые будут применены во время вызова {@link validate()}.
     */
    public function rules() {
        return array(
            array(
                'ObjectDefaultWidth,ObjectDefaultHeight,ObjectPaddingX,ObjectCount',
                'required',
                'message' => Yii::t('steps', 'Пустое значение')
            ),
            array(
                'ObjectCount,ObjectStep',
                'numerical',
                'integerOnly' => true,
//                'message' => Yii::t('steps', 'Нечисловое значение')
            ),
        );
    }

    /**
     * Метод выполняемый после сохранения формы.
     * В нем можно добавлять/убирать шаги расчета конструкции.
     * @return array
     */
    public function unsetStep() {
        $unsetArray = array();
        if (Yii::app()->container->PanoramicPanel == 0) {
            $unsetArray[] = array(
                1,
                'ColorEdgingMI'
            );
        } else {
            $unsetArray[] = array(
                0,
                'ColorEdgingMI'
            );
        }

        return $unsetArray;
    }

    /**
     * Метод определяет наличие следующего шага.
     * @return bool
     */
    function checkNextStep() {
        
        return true;
    }

    /**
     * Метод отвечает за основную логику формы. В этом методе вешаются обработчики,
     * которые отвечают за отображение/сокрытие, активацию/деактивацию, присвоение
     * значения и т.д., элементам формы
     * @return string
     */
    public function JavascriptExperssion() {
        if (!Yii::app()->container->WicketFringingColor) {
            Yii::app()->container->WicketFringingColor = "МЕТАЛЛИК";
        }
        $this->formEmbedded = Yii::app()->container->getStore("formEmbedded");
        if ($this->formEmbedded == 0) {
            $temp = array();
            $temp2 = array();
            $formula = new FormulaHelper();
            $condition = "\"Окно\"";
            list($regionNames) = AbstractDirectoryModel::getRegionsName(Yii::app()->container->Region);
            $dataReader = Yii::app()->db->createCommand()->selectDistinct('e.id,e.title,el.id elId,el.title elTitle,n.article,el.type,el.default_width,el.default_height,el.min_height,el.min_width,el.max_width,el.max_height,pr.min_left,pr.min_top,pr.min_right,pr.min_bottom,pr.recommended_left,pr.recommended_top,pr.recommended_right,pr.recommended_bottom,el.weight,el.distance_object,el.is_inner,el.object,el.is_round,el.radius, el.WindowMillingWidth,el.WindowMillingHeight, pr.WicketHingesLocation,pr.WicketFringingColor, pr.WicketDirection, pr.WicketPusher, pr.WicketPeephole, pr.condition_issuing')->from('nomenclature_embeddeds e')->leftJoin('nomenclature_embedded_elements el', 'el.nomenclature_embedded_id = e.id')->leftJoin('nomenclature_embedded_elements_products pr', 'pr.element_id = el.id')->leftJoin('embedded_elements_many_products mpr', 'pr.id = mpr.nomenclature_embedded_elements_products_id')->leftJoin('nomenclature n', 'n.code = el.nomenclature_id')->leftJoin('nomenclature_embeded_element_product_region r', 'r.product_id = pr.id')->leftJoin('region reg', 'reg.id = r.region_id')->where('reg.title IN (' . $regionNames. ') and el.type IN(' . $condition . ') and (mpr.product_id=:product_id or mpr.product_id=:all_product_id)')->query(array(':product_id' => Yii::app()->container->productId, ':all_product_id' => ProductModel::ID_ALL_PRODUCT));
            $groups = $dataReader->readAll();
            foreach ($groups as $key => $value) {
                // проверим сначала по условиям выдачи
                if(!$formula->calculation($value['condition_issuing'], 1)) continue;

                $temp2['group'][$value['id']]['value'] = Yii::t('steps', $value['title']);
                $temp2['group'][$value['id']][$value['elId']]['value'] = Yii::t('steps', $value['elTitle']);
                $temp2['group'][$value['id']][$value['elId']]['article'] = $value['article'];
                $temp2['group'][$value['id']][$value['elId']]['type'] = $value['type'];
                $temp2['group'][$value['id']][$value['elId']]['default_width'] = $formula->calculation($value['default_width']);
                $temp2['group'][$value['id']][$value['elId']]['default_height'] = $formula->calculation($value['default_height']);
                $temp2['group'][$value['id']][$value['elId']]['min_width'] = $formula->calculation($value['min_width']);
                $temp2['group'][$value['id']][$value['elId']]['min_height'] = $formula->calculation($value['min_height']);
                $temp2['group'][$value['id']][$value['elId']]['max_width'] = $formula->calculation($value['max_width']);
                $temp2['group'][$value['id']][$value['elId']]['max_height'] = $formula->calculation($value['max_height']);
                $temp2['group'][$value['id']][$value['elId']]['min_left'] = $formula->calculation($value['min_left']);
                $temp2['group'][$value['id']][$value['elId']]['min_top'] = $formula->calculation($value['min_top']);
                $temp2['group'][$value['id']][$value['elId']]['min_right'] = $formula->calculation($value['min_right']);
                $temp2['group'][$value['id']][$value['elId']]['min_bottom'] = $formula->calculation($value['min_bottom']);
                $temp2['group'][$value['id']][$value['elId']]['recommended_left'] = $formula->calculation($value['recommended_left']);
                $temp2['group'][$value['id']][$value['elId']]['recommended_top'] = $formula->calculation($value['recommended_top']);
                $temp2['group'][$value['id']][$value['elId']]['recommended_right'] = $formula->calculation($value['recommended_right']);
                $temp2['group'][$value['id']][$value['elId']]['recommended_bottom'] = $formula->calculation($value['recommended_bottom']);
                $temp2['group'][$value['id']][$value['elId']]['weight'] = $value['weight'];
                $temp2['group'][$value['id']][$value['elId']]['distance_object'] = $formula->calculation($value['distance_object']);
                $temp2['group'][$value['id']][$value['elId']]['is_inner'] = $value['is_inner'];
                $temp2['group'][$value['id']][$value['elId']]['object'] = $value['object'];
                $temp2['group'][$value['id']][$value['elId']]['Isradius'] = $value['is_round'];
                $temp2['group'][$value['id']][$value['elId']]['radius'] = $value['radius'];
                $temp2['group'][$value['id']][$value['elId']]['WindowMillingWidth'] = $formula->calculation($value['WindowMillingWidth']);
                $temp2['group'][$value['id']][$value['elId']]['WindowMillingHeight'] = $formula->calculation($value['WindowMillingHeight']);
                $temp2['group'][$value['id']][$value['elId']]['is_inner'] = $value['is_inner'];
                $temp2['group'][$value['id']][$value['elId']]['WicketHingesLocation'] = $value['WicketHingesLocation'];
                $temp2['group'][$value['id']][$value['elId']]['WicketFringingColor'] = $value['WicketFringingColor'];
                $temp2['group'][$value['id']][$value['elId']]['WicketDirection'] = $value['WicketDirection'];
                $temp2['group'][$value['id']][$value['elId']]['WicketPusher'] = $value['WicketPusher'];
                $temp2['group'][$value['id']][$value['elId']]['WicketPeephole'] = $value['WicketPeephole'];
            }
            $this->formEmbedded = $temp2;
        }
        Yii::app()->container->setStore($this->formEmbedded, "formEmbedded");
        $json = CJavaScript::encode($this->formEmbedded);
        $experssions = array();
        //описание событий элементов формы 
        foreach ($this->elementsVariables as $key => $value) {
            $keyup = '';
            $focus = '';
            $click = '';
            $blur = '';
            $change = '';

            if ($key == 'ObjectTypeSelected') {
                $change .= '
                createOptionEm(content.group[this.value],"PartTypeSelected",0,0);
                svgUnEvents(svgE, countPanel);';
            }
            if ($key == 'Viewdistance') {
                $change .= '
                    var svgW = jQuery("#apertureSVG svg g#Shield");
                    var heightShield = "' . Yii::app()->container->ShieldRealHeight . '";
                    var widthShield = "' . Yii::app()->container->ShieldWidth . '";

                    if (jQuery(this).prop("checked")) {
                        viewDistance(svgW,widthShield,heightShield,Collection);
                    } else {
                        clearElements(svgW,".views");
                    }
                ';
            }
            if ($key == 'EmbeddedShieldWeightType') {
                $change .= '
                if (jQuery(this).prop("checked")) {
                    jQuery("#' . __CLASS__ . '_EmbeddedShieldWeight").removeAttr("disabled");
                    jQuery("#' . __CLASS__ . '_EmbeddedShieldBottomPanelWeight").removeAttr("disabled");
                    
                    //скрыть инфо о весе
                    $("div.info div.weight div.hrefs").hide();
                    $("div.info div.weight div.block").hide();
                } else {
                    jQuery("#' . __CLASS__ . '_EmbeddedShieldWeight").attr("disabled","disabled");
                    jQuery("#' . __CLASS__ . '_EmbeddedShieldBottomPanelWeight").attr("disabled","disabled");
                    
                    var post = "EmbeddeObjectsMI%5BEmbeddedShieldWeightType%5D=0&EmbeddeObjectsMI%5BEmbeddedShieldWeight%5D="+    jQuery("#' . __CLASS__ . '_EmbeddedShieldWeight").val() + "&EmbeddeObjectsMI%5BEmbeddedShieldBottomPanelWeight%5D=" + jQuery("#' . __CLASS__ . '_EmbeddedShieldBottomPanelWeight").val();
                    post +="&key="+ jQuery("#key").val()+"&calc=ShieldWeightCalculationMC";
                    
                    jQuery.ajax({type: "POST",
                        url: "' . Yii::app()->createUrl("/steps/default/ajaxdata") . '",
                        data: post,
                        async: false,
                        success: function(data) {
                            weight = $.parseJSON(data);
                            jQuery("#' . __CLASS__ . '_EmbeddedShieldWeight").val(weight.ShieldWeight);
                            jQuery("#' . __CLASS__ . '_EmbeddedShieldBottomPanelWeight").val(parseFloat(weight.ShieldBottomPanelWeight) / 2);
                            
                            showWeight(weight.ArrayWeight);
                        }
                    });
                            
                    //показать инфо о весе
                    $("div.info div.weight div.hrefs").show();
                    $("div.info div.weight div.block").hide();
                }';
            }
            $str = Yii::app()->container->Loc(110181);
            if ($key == 'PartTypeSelected') {
                $change .= '
                if (this.value != "") {
                    wicket = content.group[jQuery("#' . __CLASS__ . '_ObjectTypeSelected").val()][jQuery("#' . __CLASS__ . '_PartTypeSelected").val()];
                    
                    svgUnEvents(svgE, countPanel);
                    svgEvents(svgE, countPanel, ajax, wicket.object);
                    jQuery("#' . __CLASS__ . '_ObjectArticle").val(this.value);
                    
                    //для калитки Калитка v4 стандарт закрыта возможность изменения ширины калитки
                    ObjectTypeSelected = jQuery("#' . __CLASS__ . '_ObjectTypeSelected").val();
                    
                    if (this.value == 33 || this.value == 36 || ObjectTypeSelected == 1) {
                        jQuery("#' . __CLASS__ . '_ObjectDefaultWidth").live("keydown",function(){return false});
                    } else {
                        jQuery("#' . __CLASS__ . '_ObjectDefaultWidth").die("keydown");
                    }
                    
                    //вызовем событие, если выбраный тип обьекта Калитка
                    if ($(this).find("option:selected").text().indexOf("'. Yii::app()->container->Loc(509) .'") != -1) {
                        var profileSelected = jQuery("#' . __CLASS__ . '_WicketFutureInstallation").val();
                        if (wicket.type != profileSelected) {
                            simpleAlertDialog(objDataSimpleAlert, "'. $str .'".replace(\'%s1\', " "+ Yii.t("steps", wicket.type)).replace(\'%s2\', " "+ Yii.t("steps", profileSelected)));
                        }
                        $("#apertureSVG g#Shield #rpanel1").click();
                    }
                } else {
                    svgUnEvents(svgE, countPanel);
                    jQuery("#' . __CLASS__ . '_ObjectArticle").val("");
                }
                ';
            }
            if ($key == 'RemoveRestrictions') {
                $click .= '
                if (jQuery(this).attr("checked") == "checked") {
                    jQuery("#' . __CLASS__ . '_LocationTypeX").attr("disabled","disabled");
                    jQuery("#' . __CLASS__ . '_ObjectPaddingX").attr("disabled","disabled");
                    var Shield = {};
                    Shield.Height = ' . Yii::app()->container->ShieldRealHeight . ';
                    Shield.Width = ' . Yii::app()->container->ShieldWidth . ';

                    var x1 = $(svgM).find("rect#Shields").attr("x") * 1;
                    var y1 = $(svgM).find("rect#Shields").attr("y") * 1;
                    var x2 = parseInt(x1) + $(svgM).find("rect#Shields").attr("width") * 1;
                    var y2 = parseInt(y1) + $(svgM).find("rect#Shields").attr("height") * 1;
                    deltaH = Shield.Width / (x2 - x1);
                    deltaV = Shield.Height / (y2 - y1);
                    paddingX = deltaH * GlobalDx;
                    paddingY = deltaV * GlobalDy;

                    jQuery("#' . __CLASS__ . '_ObjectPaddingX").val(paddingX);
                    jQuery("#' . __CLASS__ . '_ObjectPaddingY").val(paddingY);

                } else {
                    jQuery("#' . __CLASS__ . '_LocationTypeX").removeAttr("disabled");
                    
                    if (jQuery("#' . __CLASS__ . '_LocationTypeX").val() == 4 ) {
                        jQuery("#' . __CLASS__ . '_ObjectPaddingX").removeAttr("disabled");
                    }
                }
                ';
            }
            if ($key == "LocationTypeX") {
                $change .= '
                    if ($(this).val() == 4) {
                        jQuery("#' . __CLASS__ . '_ObjectPaddingX").removeAttr("disabled");
                    } else {
                        jQuery("#' . __CLASS__ . '_ObjectPaddingX").attr("disabled","disabled");
                    }
                ';
            }
            if ($key == "AutoCalc") {
                $click .= '
                if (jQuery(this).attr("checked") == "checked") {
                    jQuery("#' . __CLASS__ . '_ObjectStep").live("keydown",function(){return false});
                    jQuery("#' . __CLASS__ . '_ObjectStep").val("-1");
                } else {
                    jQuery("#' . __CLASS__ . '_ObjectStep").die("keydown");
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
        
        $query = "
            
            //зануляем изменения по встраиваем объектам для флага расчета веса щита
            jQuery('#" . __CLASS__ . "_CheckChangeWeight').val(0);
                
            //Расположение объекта (слева/справа/по центру)
            var form_input_LocationTypeX = $('#" . __CLASS__ . "_LocationTypeX');
            //Задать положение слева (координата x до объекта)
            var form_input_ObjectPaddingX = $('#" . __CLASS__ . "_LocationTypeX');
            
            $('#EmbeddedObjectsMI_EmbeddedShieldWeight, #EmbeddedObjectsMI_EmbeddedShieldBottomPanelWeight').numberMask({type:'float',afterPoint:6,defaultValueInput:'0',decimalMark:'.'});    
            svgE = jQuery('#apertureSVG svg g#Shield');

            $('#apertureSVG').append('<div id=\"comment\"></div>');
            $('#comment').css('position','absolute');
            $('#comment').css('z-index','100');
            $('#comment').css('margin-left','350px');
            $('#comment').css('top','500px');
            
            //отображение координат курсора
            $(svgE).mousemove(function(e){
                Shield = document.getElementById('Shields');
                var ShieldS = {};
                ShieldS.Height = " . Yii::app()->container->ShieldRealHeight . ";
                ShieldS.Width = " . Yii::app()->container->ShieldWidth . ";
                general = getOffset(Shield);
                nom = fixEvent(e);
                dx = nom.pageX - general.left;
                //сверху вниз
                dy = nom.pageY - general.top;
                //снизу вверх
                dy = Math.abs(nom.pageY - 389);
                
                var x1 = $(this).find('rect#Shields').attr('x') * 1;
                var y1 = 0;//$(svgE).find('rect#Shields').attr('y') * 1;
                var x2 = parseInt(x1) + $(this).find('rect#Shields').attr('width') * 1;
                var y2 = parseInt(y1) + $(this).find('rect#Shields').attr('height') * 1;
                deltaH = (x2 - x1) / 342;
                deltaV =(y2 - y1) / 206;

                delta2H =  ShieldS.Width / (x2 - x1);
                delta2V = ShieldS.Height / (y2 - y1);
                padX = deltaH * delta2H* dx;
                padY = deltaV * delta2V* dy;
                $('#comment').text('X: ' +  parseInt(padX) + '; Y: ' + parseInt(padY)  + ';');
            });


            countPanel = " . Yii::app()->container->ShieldPanelCount . ";
            svgUnEvents(svgE, countPanel);
            
            if (typeof newCollection !== 'undefined'){
                Collection = newCollection;
            } else {
                Collection = {};
            }
            if (typeof newCounts !== 'undefined') {
                countCollection = newCounts;
            } else {
                countCollection = 0;
            }
            if (typeof newCollectionObject !== 'undefined'){
                CollectionObject = newCollectionObject;
            } else {
                CollectionObject = {};
            };
            if (typeof newCollectionNomen !== 'undefined'){
                CollectionNomen = newCollectionNomen;
            } else {
                CollectionNomen = {};
            };
            var GlobalDx = 0;
            var GlobalDy = 0;
            var fill = {};
            fill.ShieldPanelsWithInfill = " . Yii::app()->container->ShieldPanelsWithInfill . ";
            fill.ShieldInfillStep = " . Yii::app()->container->ShieldInfillStep . ";
            fill.ShieldInfillWidth = " . Yii::app()->container->ShieldInfillWidth . ";
        ";
        foreach ($experssions as $key => $value) {
            foreach ($value as $event => $function) {
                if (!empty($function)) {
                    $query .= '
                    jQuery("#' . __CLASS__ . '_' . $key . '").die("' . $event . '");
                    ';
                    $query .= '
                    jQuery("#' . __CLASS__ . '_' . $key . '").live("' . $event . '", function(){ ' . $function . '});
                    ';
                }
            }
        }
        $query .= '
            content  = ' . $json . ';
            function createOptionEm(content, form, parents, selected)
            {
                if(parents !=0 ) {
                    emptys(parents);
                }
                var str = "";
                str = str+ "<option value=\"\">'. Yii::app()->container->Loc(330201) .'</option>";
                for (var key in content)
                {
                    if(key !="value") {
                        str = str + "<option value =\'" + key + "\'>" + content[key].value+"</option>";
                        jQuery("#' . __CLASS__ . '_"+form).empty();
                    }
                }
                jQuery("#' . __CLASS__ . '_"+form).html(str);
                if (selected != 0 ) {
                    jQuery("#' . __CLASS__ . '_"+form+" [value="+selected+"]").attr("selected","selected");
                }

            };
        ';
        $query .= '
                ajax = function(el, dx, dy) {
                    pId = el.id;
                    GlobalDx = dx ? dx : 0;
                    GlobalDy = dy ? dy : 0;
                    typeNewDialogObject();
                };
        ';
        if (true) {
        $query .= '
            function allCheck(Collection, status, id, add, massiv, fill, event)
            {
                var Object = {};
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
                if (jQuery("#' . __CLASS__ . '_ObjectPusher").attr("checked") == "checked") {
                    pusher = 1;
                } else {
                    pusher = 0;
                };
                if (jQuery("#' . __CLASS__ . '_ObjectPeephole").attr("checked") == "checked") {
                    Peephole = 1;
                } else {
                    Peephole = 0;
                };
                
                var svg = jQuery("#apertureSVG svg g#Shield");
                var x1 = $(svg).find("rect#Shields").attr("x") * 1;
                var y1 = $(svg).find("rect#Shields").attr("y") * 1;
                var x2 = parseInt(x1) + $(svg).find("rect#Shields").attr("width") * 1;
                var y2 = parseInt(y1) + $(svg).find("rect#Shields").attr("height") * 1;
                deltaH = Shield.Width / (x2 - x1);
                deltaV = Shield.Height / (y2 - y1);

                Object.ObjectType = jQuery("#' . __CLASS__ . '_ObjectType").val();
                Object.ObjectArticle = jQuery("#' . __CLASS__ . '_ObjectArticle").val();
                Object.RemoveRestrictions = RemoveRestrictions;
                Object.ObjectDefaultWidth = parseInt(jQuery("#' . __CLASS__ . '_ObjectDefaultWidth").val());
                Object.ObjectDefaultHeight = parseInt( jQuery("#' . __CLASS__ . '_ObjectDefaultHeight").val());
                Object.LocationTypeX = parseInt(jQuery("#' . __CLASS__ . '_LocationTypeX").val());
                Object.ObjectPaddingX = parseInt(jQuery("#' . __CLASS__ . '_ObjectPaddingX").val());
                Object.ObjectHingesLocation  = jQuery("#' . __CLASS__ . '_ObjectHingesLocation").val();
                Object.ObjectFringingColor = jQuery("#' . __CLASS__ . '_ObjectFringingColor").val();
                Object.ObjectDirection =  jQuery("#' . __CLASS__ . '_ObjectDirection").val();
                Object.ObjectPusher = pusher;
                Object.ObjectPeephole = Peephole;
                Object.ObjectCount = parseInt(jQuery("#' . __CLASS__ . '_ObjectCount").val());
                Object.AutoCalc = autoCalc;
                Object.ObjectMinLeft = parseInt(jQuery("#' . __CLASS__ . '_ObjectMinLeft").val());
                Object.ObjectMinRight = parseInt( jQuery("#' . __CLASS__ . '_ObjectMinRight").val());
                Object.ObjectMinTop = parseInt(jQuery("#' . __CLASS__ . '_ObjectMinTop").val());
                Object.ObjectMinBottom = parseInt(jQuery("#' . __CLASS__ . '_ObjectMinBottom").val());
                Object.ObjectMinWidth = parseInt(jQuery("#' . __CLASS__ . '_ObjectMinWidth").val());
                Object.ObjectMaxWidth = parseInt( jQuery("#' . __CLASS__ . '_ObjectMaxWidth").val());
                Object.ObjectMinHeight = parseInt(jQuery("#' . __CLASS__ . '_ObjectMinHeight").val());
                Object.ObjectMaxHeight = parseInt(jQuery("#' . __CLASS__ . '_ObjectMaxHeight").val());
                Object.ObjectStep = parseInt(jQuery("#' . __CLASS__ . '_ObjectStep").val());
                Object.ObjectPartNumber = jQuery("#' . __CLASS__ . '_ObjectPartNumber").val();
                Object.ObjectFringingColor = jQuery("#' . __CLASS__ . '_ObjectFringingColor").val();
                Object.ObjectPanels = parseInt(jQuery("#' . __CLASS__ . '_ObjectPanels").val());
                panels = ' . json_encode(Yii::app()->container->ShieldPanels) . ';
                Object.ObjectRecommendedLeft = parseInt(jQuery("#' . __CLASS__ . '_ObjectRecommendedLeft").val());
                Object.ObjectRecommendedRight = parseInt(jQuery("#' . __CLASS__ . '_ObjectRecommendedRight").val());
                Object.ObjectRecommendedTop = parseInt(jQuery("#' . __CLASS__ . '_ObjectRecommendedTop").val());
                Object.ObjectRecommendedBottom = parseInt(jQuery("#' . __CLASS__ . '_ObjectRecommendedBottom").val());
                Object.ObjectPaddingY = parseInt(jQuery("#' . __CLASS__ . '_ObjectPaddingY").val());
                Object.ObjectIsInner = parseInt(jQuery("#' . __CLASS__ . '_ObjectIsInner").val());
                Object.ObjectIsRadius = parseInt(jQuery("#' . __CLASS__ . '_ObjectIsRadius").val());
                Object.ObjectRadius = parseInt(jQuery("#' . __CLASS__ . '_ObjectRadius").val());
                Object.ObjectMinDistance = parseInt(jQuery("#' . __CLASS__ . '_ObjectMinDistance").val());
                
                if(Object.ObjectRecommendedLeft < Object.ObjectMinLeft) {
                    Object.ObjectRecommendedLeft = Object.ObjectMinLeft;
                }
                if(Object.ObjectRecommendedRight < Object.ObjectMinRight) {
                    Object.ObjectRecommendedRight = Object.ObjectMinRight;
                }
                if(Object.ObjectRecommendedTop < Object.ObjectMinTop) {
                    Object.ObjectRecommendedTop = Object.ObjectMinTop;
                }
                if(Object.ObjectRecommendedBottom < Object.ObjectMinBottom) {
                    Object.ObjectRecommendedBottom = Object.ObjectMinBottom;
                }
                
                otstupmez = parseInt(' . Yii::app()->container->ShieldPanelPadding . ');
                ShieldTopPanel = parseInt(' . Yii::app()->container->ShieldTopPanel . ');
                Object.LocationTypeY     = 3;
                //А. Описание расчета размеров объекта
                result = checkPaddingElements(Shield,Panel,RemoveRestrictions,Object,deltaV);
                if (typeof result == "string") {
                    return result;
                } else {
                    jQuery("#' . __CLASS__ . '_ObjectDefaultWidth").val(result.DefaultWidth);
                    jQuery("#' . __CLASS__ . '_ObjectDefaultHeight").val(result.DefaultHeight);
                    jQuery("#' . __CLASS__ . '_ObjectMaxHeight").val(result.MaxHeight);
                    Object.ObjectDefaultWidth = result.DefaultWidth;
                    Object.ObjectDefaultHeight = result.DefaultHeight;
                    //Object.ObjectDefaultHeight = calcHeight;
                    Object.ObjectMaxHeight = result.MaxHeight;
                }
                
                var newObject = {};
                if (Object.ObjectType == "window") {
                    newObject = checkWindows(svg,Panel,panels,Object,Shield,autoCalc,RemoveRestrictions,deltaH, deltaV, Collection,fill);
                    if (typeof newObject  == "string") {
                        return  newObject;
                    }
                }

                tempObject=clone(newObject);
                if (status == true) {
                    result = TryToInstallAt(tempObject.X,tempObject.Y,tempObject, Shield,Panel,panels,otstupmez,Collection,deltaH,deltaV,RemoveRestrictions,id);
                } else {
                    result = TryToInstallAt(tempObject.X,tempObject.Y,tempObject, Shield,Panel,panels,otstupmez,Collection,deltaH,deltaV,RemoveRestrictions,-1);
                }

                if(result!="true")
                {
                    return result;
                } else {
                    if (status == true){
                        delObject(Collection, id);
                        $("g#gWindow"+id).remove();
                    }
                    
                    if (event == "Добавить")
                        countCollection++;
                    
                    Collection[countCollection] = newObject;
                    CollectionObject[countCollection] = Object;
                    
                    if (add == true) {
                        CollectionNomen[countCollection] = {"ObjectTypeSelected": jQuery("#' . __CLASS__ . '_ObjectTypeSelected").val(),"PartTypeSelected":jQuery("#' . __CLASS__ . '_PartTypeSelected").val()};
                    } else {
                        CollectionNomen[countCollection] = {"ObjectTypeSelected": massiv.ObjectTypeSelected,"PartTypeSelected":massiv.PartTypeSelected};
                    }
                }
                return "true";
            };
        ';
        }
        $query .= '
            //удаление 
            function delObject(object, number) {
                
                //были изменения с объектами на щите, флаг для расчета веса щита
                jQuery("#' . __CLASS__ . '_CheckChangeWeight").val(1);

                var post2 = "EmbeddeObjectsMI%5BObjectType%5D="+object[number].type+"&EmbeddeObjectsMI%5BElements%5D=" + number + "&key="+ jQuery("#key").val()+"&calc=DeleteObjectsMC";
                jQuery.ajax({type: "POST",
                    url:"' . Yii::app()->createUrl("/steps/default/ajaxdata") . '",
                    data: post2,
                    async: false,
                    error: function(jqXHR, textStatus, errorThrown) {
                    },
                    success: function (data) {
                        delete Collection[number];
                        delete CollectionObject[number];
                        delete CollectionNomen[number];
                    }
                });
                
                var heightShield = "' . Yii::app()->container->ShieldRealHeight . '";
                var widthShield = "' . Yii::app()->container->ShieldWidth . '";
                
                clearElements(svgM,".hinges");
            }
        ';
        $query .= '
        //создание нового диалогового окна
        function typeNewDialogObject(){
                jQuery("#aperture-form").parent().append("<div id=\"paramObject\"><form method=\"post\" action=\"/steps\" id=\"dopObjectform\">     <ul class=\"clearfix\"></ul></form></div>");
                jQuery("#aperture-form #' . __CLASS__ . ' li#li_RemoveRestrictions").appendTo("#paramObject ul");
                jQuery("#aperture-form #' . __CLASS__ . ' li#li_ObjectDefaultWidth").appendTo("#paramObject ul");
                jQuery("#aperture-form #' . __CLASS__ . ' li#li_ObjectDefaultHeight").appendTo("#paramObject ul");
                jQuery("#aperture-form #' . __CLASS__ . ' li#li_LocationTypeX").appendTo("#paramObject ul");
                jQuery("#aperture-form #' . __CLASS__ . ' li#li_ObjectPaddingX").appendTo("#paramObject ul");
                jQuery("#aperture-form #' . __CLASS__ . ' li#li_ObjectHingesLocation").appendTo("#paramObject ul");
                jQuery("#aperture-form #' . __CLASS__ . ' li#li_ObjectFringingColor").appendTo("#paramObject ul");
                jQuery("#aperture-form #' . __CLASS__ . ' li#li_ObjectDirection").appendTo("#paramObject ul");
                jQuery("#aperture-form #' . __CLASS__ . ' li#li_ObjectPusher").appendTo("#paramObject ul");
                jQuery("#aperture-form #' . __CLASS__ . ' li#li_ObjectPeephole").appendTo("#paramObject ul");
                jQuery("#aperture-form #' . __CLASS__ . ' li#li_ObjectCount").appendTo("#paramObject ul");
                jQuery("#aperture-form #' . __CLASS__ . ' li#li_AutoCalc").appendTo("#paramObject ul");
                jQuery("#aperture-form #' . __CLASS__ . ' li#li_ObjectStep").appendTo("#paramObject ul");
                jQuery("#aperture-form #' . __CLASS__ . ' li#li_ObjectPartNumber").appendTo("#paramObject ul");
                jQuery("#aperture-form #' . __CLASS__ . ' li#li_ObjectPaddingY").appendTo("#paramObject ul");
                jQuery("#aperture-form #' . __CLASS__ . ' li#li_ObjectMinLeft").appendTo("#paramObject ul");
                jQuery("#aperture-form #' . __CLASS__ . ' li#li_ObjectMinWidth").appendTo("#paramObject ul");
                jQuery("#aperture-form #' . __CLASS__ . ' li#li_ObjectMaxWidth").appendTo("#paramObject ul");
                jQuery("#aperture-form #' . __CLASS__ . ' li#li_ObjectMinHeight").appendTo("#paramObject ul");
                jQuery("#aperture-form #' . __CLASS__ . ' li#li_ObjectMaxHeight").appendTo("#paramObject ul");
                jQuery("#aperture-form #' . __CLASS__ . ' li#li_ObjectMinTop").appendTo("#paramObject ul");
                jQuery("#aperture-form #' . __CLASS__ . ' li#li_ObjectMinRight").appendTo("#paramObject ul");
                jQuery("#aperture-form #' . __CLASS__ . ' li#li_ObjectMinBottom").appendTo("#paramObject ul");
                jQuery("#aperture-form #' . __CLASS__ . ' li#li_ObjectRecommendedLeft").appendTo("#paramObject ul");
                jQuery("#aperture-form #' . __CLASS__ . ' li#li_ObjectRecommendedTop").appendTo("#paramObject ul");
                jQuery("#aperture-form #' . __CLASS__ . ' li#li_ObjectRecommendedRight").appendTo("#paramObject ul");
                jQuery("#aperture-form #' . __CLASS__ . ' li#li_ObjectRecommendedBottom").appendTo("#paramObject ul");             
                jQuery("#aperture-form #' . __CLASS__ . ' li#li_ObjectType").appendTo("#paramObject ul");
                jQuery("#aperture-form #' . __CLASS__ . ' li#li_ObjectIsRadius").appendTo("#paramObject ul");
                jQuery("#aperture-form #' . __CLASS__ . ' li#li_ObjectRadius").appendTo("#paramObject ul");
                jQuery("#aperture-form #' . __CLASS__ . ' li#li_ObjectPanels").appendTo("#paramObject ul");
                jQuery("#aperture-form #' . __CLASS__ . ' li#li_ObjectIsInner").appendTo("#paramObject ul");
                jQuery("#aperture-form #' . __CLASS__ . ' li#li_ObjectMinDistance").appendTo("#paramObject ul");
                jQuery("#aperture-form #' . __CLASS__ . ' li#li_ObjectArticle").appendTo("#paramObject ul");
                jQuery("#' . __CLASS__ . '_ObjectDefaultWidth").val(wicket.default_width);
                jQuery("#' . __CLASS__ . '_ObjectDefaultHeight").val(wicket.default_height);
                jQuery("#' . __CLASS__ . '_ObjectPaddingX").val(wicket.min_left);
                jQuery("#' . __CLASS__ . '_ObjectPartNumber").val(wicket.article);
                jQuery("#' . __CLASS__ . '_ObjectMinLeft").val(wicket.min_left);
                jQuery("#' . __CLASS__ . '_ObjectMinWidth").val(wicket.min_width);
                jQuery("#' . __CLASS__ . '_ObjectMaxWidth").val(wicket.max_width);
                jQuery("#' . __CLASS__ . '_ObjectMinHeight").val(wicket.min_height);
                jQuery("#' . __CLASS__ . '_ObjectMaxHeight").val(wicket.max_height);
                jQuery("#' . __CLASS__ . '_ObjectMinTop").val(wicket.min_top);
                jQuery("#' . __CLASS__ . '_ObjectMinRight").val(wicket.min_right);
                jQuery("#' . __CLASS__ . '_ObjectMinBottom").val(wicket.min_bottom);
                
                jQuery("#' . __CLASS__ . '_ObjectType").val(wicket.object);
                jQuery("#' . __CLASS__ . '_ObjectIsRadius").val(wicket.Isradius);
                jQuery("#' . __CLASS__ . '_ObjectRadius").val(wicket.radius);
                jQuery("#' . __CLASS__ . '_ObjectIsInner").val(wicket.is_inner);
                jQuery("#' . __CLASS__ . '_ObjectMinDistance").val(wicket.distance_object);
                //значения по умолчанию
                //jQuery("#' . __CLASS__ . '_RemoveRestrictions").prop("checked", false);
                jQuery("#' . __CLASS__ . '_ObjectCount").val(1);
                jQuery("#' . __CLASS__ . '_ObjectStep").val(-1);
                jQuery("#' . __CLASS__ . '_AutoCalc").prop("checked", true);

                if(wicket.WicketHingesLocation == "left") {
                    jQuery("#' . __CLASS__ . '_ObjectHingesLocation [value=\"Слева\"]").attr("selected","selected");
                    jQuery("#' . __CLASS__ . '_ObjectHingesLocation [value!=\"Слева\"]").remove();
                    jQuery("#' . __CLASS__ . '_ObjectHingesLocation").live("change",function(){return false});
                } else if(wicket.WicketHingesLocation == "right"){
                    jQuery("#' . __CLASS__ . '_ObjectHingesLocation [value=\"Справа\"]").attr("selected","selected");
                    jQuery("#' . __CLASS__ . '_ObjectHingesLocation [value!=\"Справа\"]").remove();
                    jQuery("#' . __CLASS__ . '_ObjectHingesLocation").live("change",function(){return false});
                } else {
                    jQuery("#' . __CLASS__ . '_ObjectHingesLocation [value=\"Слева\"]").attr("selected","selected");
                    jQuery("#' . __CLASS__ . '_ObjectHingesLocation").die("change");
                }
                if(wicket.WicketFringingColor == "МЕТАЛЛИК") {
                    jQuery("#' . __CLASS__ . '_ObjectFringingColor [value=\"МЕТАЛЛИК\"]").attr("selected","selected");
                    jQuery("#' . __CLASS__ . '_ObjectFringingColor [value!=\"МЕТАЛЛИК\"]").remove();
                    jQuery("#' . __CLASS__ . '_ObjectFringingColor").live("change",function(){return false});
                } else if(wicket.WicketFringingColor == "RAL9003"){
                    jQuery("#' . __CLASS__ . '_ObjectFringingColor [value=\"RAL9003\"]").attr("selected","selected");
                    jQuery("#' . __CLASS__ . '_ObjectFringingColor [value!=\"RAL9003\"]").remove();
                    jQuery("#' . __CLASS__ . '_ObjectFringingColor").live("change",function(){return false});
                } else if(wicket.WicketFringingColor == "RAL8014"){
                    jQuery("#' . __CLASS__ . '_ObjectFringingColor [value=\"RAL8014\"]").attr("selected","selected");
                    jQuery("#' . __CLASS__ . '_ObjectFringingColor [value!=\"RAL8014\"]").remove();
                    jQuery("#' . __CLASS__ . '_ObjectFringingColor").live("change",function(){return false});
                } else {
                    jQuery("#' . __CLASS__ . '_ObjectFringingColor [value=\"МЕТАЛЛИК\"]").attr("selected","selected");
                    jQuery("#' . __CLASS__ . '_ObjectFringingColor").die("change");
                }
                if(wicket.WicketDirection == "inside") {
                    jQuery("#' . __CLASS__ . '_ObjectDirection [value=\"Внутрь\"]").attr("selected","selected");
                    jQuery("#' . __CLASS__ . '_ObjectDirection [value!=\"Внутрь\"]").remove();
                    jQuery("#' . __CLASS__ . '_ObjectDirection").live("change",function(){return false});
                } else if(wicket.WicketDirection == "outside"){
                    jQuery("#' . __CLASS__ . '_ObjectDirection [value=\"Наружу\"]").attr("selected","selected");
                    jQuery("#' . __CLASS__ . '_ObjectDirection [value!=\"Наружу\"]").remove();
                    jQuery("#' . __CLASS__ . '_ObjectDirection").live("change",function(){return false});
                } else {
                    jQuery("#' . __CLASS__ . '_ObjectDirection [value=\"\"]").attr("selected","selected");
                    jQuery("#' . __CLASS__ . '_ObjectDirection").die("change");
                }
               
                if(wicket.WicketPusher == "yes") {
                    jQuery("#' . __CLASS__ . '_ObjectPusher").attr("checked","checked");
                    jQuery("#' . __CLASS__ . '_ObjectPusher").live("click",function(){return false});
                } else if(wicket.WicketPusher == "no"){
                    jQuery("#' . __CLASS__ . '_ObjectPusher").removeAttr("checked");
                    jQuery("#' . __CLASS__ . '_ObjectPusher").prop("disabled", true);
                    jQuery("#' . __CLASS__ . '_ObjectPusher").live("click",function(){return false});
                } else {
                    jQuery("#' . __CLASS__ . '_ObjectPusher").removeAttr("checked");
                    jQuery("#' . __CLASS__ . '_ObjectPusher").die("click");
                }
                if(wicket.WicketPeephole == "yes") {
                    jQuery("#' . __CLASS__ . '_ObjectPeephole").attr("checked","checked");
                    jQuery("#' . __CLASS__ . '_ObjectPeephole").live("click",function(){return false});
                } else if(wicket.WicketPeephole == "no"){
                    jQuery("#' . __CLASS__ . '_ObjectPeephole").removeAttr("checked");
                    jQuery("#' . __CLASS__ . '_ObjectPeephole").prop("disabled", true);
                    jQuery("#' . __CLASS__ . '_ObjectPeephole").live("click",function(){return false});
                } else {
                    jQuery("#' . __CLASS__ . '_ObjectPeephole").removeAttr("checked");
                    jQuery("#' . __CLASS__ . '_ObjectPeephole").die("click");
                }

                jQuery("#paramObject #' . __CLASS__ . '_RemoveRestrictions").removeAttr("checked","checked");
                jQuery("#paramObject #' . __CLASS__ . '_LocationTypeX").val(1);
          
                if (wicket.object == "window") {
                    jQuery("#paramObject li#li_ObjectDefaultWidth").show();
                    jQuery("#paramObject li#li_ObjectDefaultHeight").show();
                    jQuery("#paramObject li#li_LocationTypeX").show();
                    jQuery("#paramObject li#li_ObjectPaddingX").show();
                    jQuery("#paramObject li#li_ObjectCount").show();
                    jQuery("#paramObject li#li_LocationTypeX").show();
                    jQuery("#paramObject li#li_ObjectPaddingX").show();
                    jQuery("#paramObject li#li_AutoCalc").show();
                    //if (jQuery("#paramObject #' . __CLASS__ . '_AutoCalc").prop("checked")) {
                    //jQuery("#paramObject li#li_ObjectStep").hide();
                    //} else {
                    jQuery("#paramObject li#li_ObjectStep").show();
                    //}
                    jQuery("#paramObject #' . __CLASS__ . '_ObjectDefaultWidth").live("keydown",function(){return false});
                    jQuery("#paramObject #' . __CLASS__ . '_ObjectDefaultHeight").live("keydown",function(){return false});
                }
                
                jQuery("#' . __CLASS__ . '_ObjectDefaultWidth").live("keydown",function(){return false});
                jQuery("#' . __CLASS__ . '_ObjectDefaultHeight").live("keydown",function(){return false});

                jQuery("#paramObject").dialog({
                modal: true,
                resizable: false,
                open: function(event, ui) {
                    $(this).parent().children().children(".ui-dialog-titlebar-close").hide();
                },

                buttons: {
                    "' . Yii::t('steps', 'Добавить') . '": function() {

                    jQuery("#paramObject li#li_RemoveRestrictions").appendTo("#aperture-form li#' . __CLASS__ . ' ul").hide();
                    jQuery("#paramObject li#li_ObjectDefaultWidth").appendTo("#aperture-form li#' . __CLASS__ . ' ul").hide();
                    jQuery("#paramObject li#li_ObjectDefaultHeight").appendTo("#aperture-form li#' . __CLASS__ . ' ul").hide();
                    jQuery("#paramObject li#li_LocationTypeX").appendTo("#aperture-form li#' . __CLASS__ . ' ul").hide();
                    jQuery("#paramObject li#li_ObjectPaddingX").appendTo("#aperture-form li#' . __CLASS__ . ' ul").hide();
                    jQuery("#paramObject li#li_ObjectHingesLocation").appendTo("#aperture-form li#' . __CLASS__ . ' ul").hide();
                    jQuery("#paramObject li#li_ObjectFringingColor").appendTo("#aperture-form li#' . __CLASS__ . ' ul").hide();
                    jQuery("#paramObject li#li_ObjectDirection").appendTo("#aperture-form li#' . __CLASS__ . ' ul").hide();
                    jQuery("#paramObject li#li_ObjectPusher").appendTo("#aperture-form li#' . __CLASS__ . ' ul").hide();
                    jQuery("#paramObject li#li_ObjectPeephole").appendTo("#aperture-form li#' . __CLASS__ . ' ul").hide();

                    jQuery("#paramObject li#li_ObjectCount").appendTo("#aperture-form li#' . __CLASS__ . ' ul").hide();
                    jQuery("#paramObject li#li_AutoCalc").appendTo("#aperture-form li#' . __CLASS__ . ' ul").hide();
                    jQuery("#paramObject li#li_ObjectStep").appendTo("#aperture-form li#' . __CLASS__ . ' ul").hide();
                    jQuery("#paramObject li#li_ObjectPartNumber").appendTo("#aperture-form li#' . __CLASS__ . ' ul").hide();
                    jQuery("#paramObject li#li_ObjectPaddingY").appendTo("#aperture-form li#' . __CLASS__ . ' ul").hide();
                    jQuery("#paramObject li#li_ObjectMinLeft").appendTo("#aperture-form li#' . __CLASS__ . ' ul").hide();
                    jQuery("#paramObject li#li_ObjectMinWidth").appendTo("#aperture-form li#' . __CLASS__ . ' ul").hide();
                    jQuery("#paramObject li#li_ObjectMaxWidth").appendTo("#aperture-form li#' . __CLASS__ . ' ul").hide();
                    jQuery("#paramObject li#li_ObjectMinHeight").appendTo("#aperture-form li#' . __CLASS__ . ' ul").hide();
                    jQuery("#paramObject li#li_ObjectMaxHeight").appendTo("#aperture-form li#' . __CLASS__ . ' ul").hide();
                    jQuery("#paramObject li#li_ObjectMinTop").appendTo("#aperture-form li#' . __CLASS__ . ' ul").hide();
                    jQuery("#paramObject li#li_ObjectMinRight").appendTo("#aperture-form li#' . __CLASS__ . ' ul").hide();
                    jQuery("#paramObject li#li_ObjectMinBottom").appendTo("#aperture-form li#' . __CLASS__ . ' ul").hide();
                    jQuery("#paramObject li#li_ObjectRecommendedLeft").appendTo("#aperture-form li#' . __CLASS__ . ' ul").hide();
                    jQuery("#paramObject li#li_ObjectRecommendedTop").appendTo("#aperture-form li#' . __CLASS__ . ' ul").hide();
                    jQuery("#paramObject li#li_ObjectRecommendedRight").appendTo("#aperture-form li#' . __CLASS__ . ' ul").hide();
                    jQuery("#paramObject li#li_ObjectRecommendedBottom").appendTo("#aperture-form li#' . __CLASS__ . ' ul").hide();              
                    jQuery("#paramObject li#li_ObjectType").appendTo("#aperture-form li#' . __CLASS__ . ' ul").hide();
                    jQuery("#paramObject li#li_ObjectIsRadius").appendTo("#aperture-form li#' . __CLASS__ . ' ul").hide();
                    jQuery("#paramObject li#li_ObjectRadius").appendTo("#aperture-form li#' . __CLASS__ . ' ul").hide();
                    jQuery("#paramObject li#li_ObjectPanels").appendTo("#aperture-form li#' . __CLASS__ . ' ul").hide();
                    jQuery("#paramObject li#li_ObjectIsInner").appendTo("#aperture-form li#' . __CLASS__ . ' ul").hide();
                    jQuery("#paramObject li#li_ObjectMinDistance").appendTo("#aperture-form li#' . __CLASS__ . ' ul").hide();
                    jQuery("#paramObject li#li_ObjectArticle").appendTo("#aperture-form li#' . __CLASS__ . ' ul").hide();

                    jQuery("#' . __CLASS__ . '_LocationTypeX").removeAttr("disabled","disabled");
                    jQuery("#' . __CLASS__ . '_ObjectPaddingX").removeAttr("disabled","disabled");
                    jQuery("#aperture-form #' . __CLASS__ . '_ObjectPanels").val(pId.substring(6));
                        
                    //были изменения с объектами на щите, флаг для расчета веса щита
                    jQuery("#' . __CLASS__ . '_CheckChangeWeight").val(1);

                    var post = jQuery("#aperture-form").serialize();
                    post += "&' . __CLASS__ . '%5BElements%5D=0";
                    post +="&calc=EmbeddedObjectsMC";
                    
                    result = allCheck(Collection,false,0,true,0,fill, "Добавить");
                    
                    if (result == "true") {
                        jQuery.ajax({type: "POST",
                            url:"' . Yii::app()->createUrl("/steps/default/ajaxdata") . '",
                            data:post,
                            async: false,
                            success: function(data) {
                                contentJSON = $.parseJSON(data);
                                if (contentJSON.error) {
                                    simpleAlertDialog(objDataSimpleAlert, \''. Yii::t('steps', 'Установить этот объект нельзя!') .'\');
                                } else {
                                    if (jQuery("#' . __CLASS__ . '_ObjectType").val() == "wicket") {

                                        var post2 ="key="+ jQuery("#key").val()+"&calc=WicketCalculationMC";
                                        jQuery.ajax({type: "POST",
                                            url:"' . Yii::app()->createUrl("/steps/default/ajaxdata") . '",
                                            data:post2,
                                            async: false,
                                            success: function(data) {
                                                heightW = contentJSON.WicketHeight;
                                                widthW = contentJSON.WicketWidth;
                                                wicketJSON = $.parseJSON(data);
                                                console.log( wicketJSON);
                                                //wickets = {"X": wicketJSON.WicketX,"Y":wicketJSON.WicketY,"height":heightW,"width":widthW,"name":contentJSON.WicketName};
                                                var svgW = jQuery("#apertureSVG svg g#Shield");
                                                var heightShield = "' . Yii::app()->container->ShieldRealHeight . '";
                                                var widthShield = "' . Yii::app()->container->ShieldWidth . '";

                                                if (jQuery("#' . __CLASS__ . '_RemoveRestrictions").attr("checked") == "checked") {
                                                    addWicket(svgW,heightShield, widthShield,wickets,1,countCollection/*-1*/,1);
                                                } else {
                                                    addWicket(svgW,heightShield, widthShield,wickets,0,countCollection/*-1*/,1);
                                                }
                                                if (jQuery("#EmbeddedObjectsMI_Viewdistance").prop("checked")) {
                                                    clearElements(svgW,".views");
                                                    viewDistance(svgW,widthShield,heightShield,Collection);
                                                }
                                                clearElements(svgM,".hinges");
                                                
                                                if (jQuery("#' . __CLASS__ . '_RemoveRestrictions").attr("checked") == "checked") {
                                                    var s = Snap("#apertureSVG svg");

                                                    number = countCollection/*-1*/;
                                                    g = s.select("#gWicket" + number);
                                                    Shield = s.select("#Shields");
                                                    g.parentSelect(Shield);
                                                    g.drag();
                                                }
                                            }
                                        });
                                    } else if (jQuery("#' . __CLASS__ . '_ObjectType").val() == "window") {

                                        var post2 ="key="+ jQuery("#key").val()+"&calc=WindowsCalculationMC";
                                        jQuery.ajax({type: "POST",
                                            url:"' . Yii::app()->createUrl("/steps/default/ajaxdata") . '",
                                            data:post2,
                                            async: false,
                                            success: function(data) {
                                                if (!contentJSON.error) {
                                                    coord = $.parseJSON(data);
                                                    var svgWin = jQuery("#apertureSVG svg g#Shield");
                                                    var heightShield = "' . Yii::app()->container->ShieldRealHeight . '";
                                                    var widthShield = "' . Yii::app()->container->ShieldWidth . '";

                                                    addWindow(svgWin,heightShield,widthShield,contentJSON,coord,countCollection/*-1*/,1);
                                                    if (jQuery("#EmbeddedObjectsMI_Viewdistance").prop("checked")) {
                                                        clearElements(svgWin,".views");
                                                        viewDistance(svgWin,widthShield,heightShield,Collection);
                                                    }
                                                } else {simpleAlertDialog(objDataSimpleAlert, \''. Yii::t('steps', 'нельзя поставить окна!') .'\');}
                                            }
                                        });
                                    }
                                }
                            }
                        });
                                      
                    } else {
                        simpleAlertDialog(objDataSimpleAlert, result);
                    }
                    if(result == "true") {
                        $(this).dialog("close");
                        $("#paramObject").remove();
          
                        resetEmbeddedObject();
                    } else {
                        jQuery("#aperture-form #' . __CLASS__ . ' li#li_RemoveRestrictions").appendTo("#paramObject ul");
                        jQuery("#aperture-form #' . __CLASS__ . ' li#li_ObjectDefaultWidth").appendTo("#paramObject ul");
                        jQuery("#aperture-form #' . __CLASS__ . ' li#li_ObjectDefaultHeight").appendTo("#paramObject ul");
                        jQuery("#aperture-form #' . __CLASS__ . ' li#li_LocationTypeX").appendTo("#paramObject ul");
                        jQuery("#aperture-form #' . __CLASS__ . ' li#li_ObjectPaddingX").appendTo("#paramObject ul");
                        jQuery("#aperture-form #' . __CLASS__ . ' li#li_ObjectHingesLocation").appendTo("#paramObject ul");
                        jQuery("#aperture-form #' . __CLASS__ . ' li#li_ObjectFringingColor").appendTo("#paramObject ul");
                        jQuery("#aperture-form #' . __CLASS__ . ' li#li_ObjectDirection").appendTo("#paramObject ul");
                        jQuery("#aperture-form #' . __CLASS__ . ' li#li_ObjectPusher").appendTo("#paramObject ul");
                        jQuery("#aperture-form #' . __CLASS__ . ' li#li_ObjectPeephole").appendTo("#paramObject ul");
                        jQuery("#aperture-form #' . __CLASS__ . ' li#li_ObjectCount").appendTo("#paramObject ul");
                        jQuery("#aperture-form #' . __CLASS__ . ' li#li_AutoCalc").appendTo("#paramObject ul");
                        jQuery("#aperture-form #' . __CLASS__ . ' li#li_ObjectStep").appendTo("#paramObject ul");
                        jQuery("#aperture-form #' . __CLASS__ . ' li#li_ObjectPartNumber").appendTo("#paramObject ul");
                        jQuery("#aperture-form #' . __CLASS__ . ' li#li_ObjectPaddingY").appendTo("#paramObject ul");
                        jQuery("#aperture-form #' . __CLASS__ . ' li#li_ObjectMinLeft").appendTo("#paramObject ul");
                        jQuery("#aperture-form #' . __CLASS__ . ' li#li_ObjectMinWidth").appendTo("#paramObject ul");
                        jQuery("#aperture-form #' . __CLASS__ . ' li#li_ObjectMaxWidth").appendTo("#paramObject ul");
                        jQuery("#aperture-form #' . __CLASS__ . ' li#li_ObjectMinHeight").appendTo("#paramObject ul");
                        jQuery("#aperture-form #' . __CLASS__ . ' li#li_ObjectMaxHeight").appendTo("#paramObject ul");
                        jQuery("#aperture-form #' . __CLASS__ . ' li#li_ObjectMinTop").appendTo("#paramObject ul");
                        jQuery("#aperture-form #' . __CLASS__ . ' li#li_ObjectMinRight").appendTo("#paramObject ul");
                        jQuery("#aperture-form #' . __CLASS__ . ' li#li_ObjectMinBottom").appendTo("#paramObject ul");
                        jQuery("#aperture-form #' . __CLASS__ . ' li#li_ObjectRecommendedLeft").appendTo("#paramObject ul");
                        jQuery("#aperture-form #' . __CLASS__ . ' li#li_ObjectRecommendedTop").appendTo("#paramObject ul");
                        jQuery("#aperture-form #' . __CLASS__ . ' li#li_ObjectRecommendedRight").appendTo("#paramObject ul");
                        jQuery("#aperture-form #' . __CLASS__ . ' li#li_ObjectRecommendedBottom").appendTo("#paramObject ul");

                        jQuery("#aperture-form #' . __CLASS__ . ' li#li_ObjectType").appendTo("#paramObject ul");
                        jQuery("#aperture-form #' . __CLASS__ . ' li#li_ObjectIsRadius").appendTo("#paramObject ul");
                        jQuery("#aperture-form #' . __CLASS__ . ' li#li_ObjectRadius").appendTo("#paramObject ul");
                        jQuery("#aperture-form #' . __CLASS__ . ' li#li_ObjectPanels").appendTo("#paramObject ul");
                        jQuery("#aperture-form #' . __CLASS__ . ' li#li_ObjectIsInner").appendTo("#paramObject ul");
                        jQuery("#aperture-form #' . __CLASS__ . ' li#li_ObjectMinDistance").appendTo("#paramObject ul");
                        jQuery("#aperture-form #' . __CLASS__ . ' li#li_ObjectArticle").appendTo("#paramObject ul");


                        jQuery("#paramObject li#li_ObjectDefaultWidth").show();
                        jQuery("#paramObject li#li_ObjectDefaultHeight").show();
                        jQuery("#paramObject li#li_LocationTypeX").show();
                        jQuery("#paramObject li#li_ObjectPaddingX").show();
                        if (wicket.object == "window") {
                            jQuery("#paramObject li#li_ObjectCount").show();
                            jQuery("#paramObject li#li_LocationTypeX").show();
                            jQuery("#paramObject li#li_ObjectPaddingX").show();
                            jQuery("#paramObject li#li_AutoCalc").show();
//                          if (jQuery("#paramObject #' . __CLASS__ . '_AutoCalc").prop("checked")) {
//                              jQuery("#paramObject li#li_ObjectStep").hide();
//                          } else {
                                jQuery("#paramObject li#li_ObjectStep").show();
//                          }
                            jQuery("#paramObject #' . __CLASS__ . '_ObjectDefaultWidth").live("keydown",function(){return false});
                            jQuery("#paramObject #' . __CLASS__ . '_ObjectDefaultHeight").live("keydown",function(){return false});
                        }
                    }
                },
                "' . Yii::t('steps', 'Закрыть') . '": function(){
                    jQuery("#paramObject li#li_RemoveRestrictions").appendTo("#aperture-form li#' . __CLASS__ . ' ul").hide();
                    jQuery("#paramObject li#li_ObjectDefaultWidth").appendTo("#aperture-form li#' . __CLASS__ . ' ul").hide();
                    jQuery("#paramObject li#li_ObjectDefaultHeight").appendTo("#aperture-form li#' . __CLASS__ . ' ul").hide();
                    jQuery("#paramObject li#li_LocationTypeX").appendTo("#aperture-form li#' . __CLASS__ . ' ul").hide();
                    jQuery("#paramObject li#li_ObjectPaddingX").appendTo("#aperture-form li#' . __CLASS__ . ' ul").hide();
                    jQuery("#paramObject li#li_ObjectHingesLocation").appendTo("#aperture-form li#' . __CLASS__ . ' ul").hide();
                    jQuery("#paramObject li#li_ObjectFringingColor").appendTo("#aperture-form li#' . __CLASS__ . ' ul").hide();
                    jQuery("#paramObject li#li_ObjectDirection").appendTo("#aperture-form li#' . __CLASS__ . ' ul").hide();
                    jQuery("#paramObject li#li_ObjectPusher").appendTo("#aperture-form li#' . __CLASS__ . ' ul").hide();
                    jQuery("#paramObject li#li_ObjectPeephole").appendTo("#aperture-form li#' . __CLASS__ . ' ul").hide();
                    jQuery("#paramObject li#li_ObjectCount").appendTo("#aperture-form li#' . __CLASS__ . ' ul").hide();
                    jQuery("#paramObject li#li_AutoCalc").appendTo("#aperture-form li#' . __CLASS__ . ' ul").hide();
                    jQuery("#paramObject li#li_ObjectStep").appendTo("#aperture-form li#' . __CLASS__ . ' ul").hide();
                    jQuery("#paramObject li#li_ObjectPartNumber").appendTo("#aperture-form li#' . __CLASS__ . ' ul").hide();
                    jQuery("#paramObject li#li_ObjectPaddingY").appendTo("#aperture-form li#' . __CLASS__ . ' ul").hide();
                    jQuery("#paramObject li#li_ObjectMinLeft").appendTo("#aperture-form li#' . __CLASS__ . ' ul").hide();
                    jQuery("#paramObject li#li_ObjectMinWidth").appendTo("#aperture-form li#' . __CLASS__ . ' ul").hide();
                    jQuery("#paramObject li#li_ObjectMaxWidth").appendTo("#aperture-form li#' . __CLASS__ . ' ul").hide();
                    jQuery("#paramObject li#li_ObjectMinHeight").appendTo("#aperture-form li#' . __CLASS__ . ' ul").hide();
                    jQuery("#paramObject li#li_ObjectMaxHeight").appendTo("#aperture-form li#' . __CLASS__ . ' ul").hide();
                    jQuery("#paramObject li#li_ObjectMinTop").appendTo("#aperture-form li#' . __CLASS__ . ' ul").hide();
                    jQuery("#paramObject li#li_ObjectMinRight").appendTo("#aperture-form li#' . __CLASS__ . ' ul").hide();
                    jQuery("#paramObject li#li_ObjectMinBottom").appendTo("#aperture-form li#' . __CLASS__ . ' ul").hide();
                    jQuery("#paramObject li#li_ObjectRecommendedLeft").appendTo("#aperture-form li#' . __CLASS__ . ' ul").hide();
                    jQuery("#paramObject li#li_ObjectRecommendedTop").appendTo("#aperture-form li#' . __CLASS__ . ' ul").hide();
                    jQuery("#paramObject li#li_ObjectRecommendedRight").appendTo("#aperture-form li#' . __CLASS__ . ' ul").hide();
                    jQuery("#paramObject li#li_ObjectRecommendedBottom").appendTo("#aperture-form li#' . __CLASS__ . ' ul").hide();                          
                    jQuery("#paramObject li#li_ObjectType").appendTo("#aperture-form li#' . __CLASS__ . ' ul").hide();
                    jQuery("#paramObject li#li_ObjectIsRadius").appendTo("#aperture-form li#' . __CLASS__ . ' ul").hide();
                    jQuery("#paramObject li#li_ObjectRadius").appendTo("#aperture-form li#' . __CLASS__ . ' ul").hide();
                    jQuery("#paramObject li#li_ObjectPanels").appendTo("#aperture-form li#' . __CLASS__ . ' ul").hide();
                    jQuery("#paramObject li#li_ObjectIsInner").appendTo("#aperture-form li#' . __CLASS__ . ' ul").hide();
                    jQuery("#paramObject li#li_ObjectMinDistance").appendTo("#aperture-form li#' . __CLASS__ . ' ul").hide();
                    jQuery("#paramObject li#li_ObjectArticle").appendTo("#aperture-form li#' . __CLASS__ . ' ul").hide();

                    $(this).dialog("close");
                    $("#paramObject").remove();

                    resetEmbeddedObject();
                }
	    }
	 });
        }
        ';
        
        $query .= '
            //перемещение объекта
            function movedElement(id,dx1,dy1) {
                id = parseInt(id);
                objectTempes = clone(CollectionObject[id]);
                for(var opl in CollectionObject[id]){
                    jQuery("#' . __CLASS__ . '_"+opl).val(CollectionObject[id][opl]);
                    if (jQuery("#' . __CLASS__ . '_"+opl).attr("type") == "checkbox") {
                        if (CollectionObject[id][opl] == 1) {
                            jQuery("#' . __CLASS__ . '_"+opl).attr("checked", "checked");
                        }else {
                            jQuery("#' . __CLASS__ . '_"+opl).removeAttr("checked");
                        }
                        jQuery("#' . __CLASS__ . '_"+opl).val(1);
                    }
                }
                var Shield = {};
                Shield.Height = ' . Yii::app()->container->ShieldRealHeight . ';
                Shield.Width = ' . Yii::app()->container->ShieldWidth . ';
                var svgS = jQuery("#apertureSVG svg g#Shield");
                var x1 = $(svgS).find("rect#Shields").attr("x") * 1;
                var y1 = $(svgS).find("rect#Shields").attr("y") * 1;
                var x2 = parseInt(x1) + $(svgS).find("rect#Shields").attr("width") * 1;
                var y2 = parseInt(y1) + $(svgS).find("rect#Shields").attr("height") * 1;
                deltaH = Shield.Width / (x2 - x1);
                deltaV = Shield.Height / (y2 - y1);
                dy1 = dy1 - 330;
                paddingX = deltaH * dx1;
                paddingY = deltaV * dy1;


                jQuery("#' . __CLASS__ . '_ObjectPaddingX").val(paddingX);
                    

                jQuery("#' . __CLASS__ . '_ObjectPaddingY").val(paddingY);
                for(var opl in CollectionObject[id]){
                    CollectionObject[id][opl] = jQuery("#' . __CLASS__ . '_"+opl).val();
                    if (jQuery("#' . __CLASS__ . '_"+opl).attr("type") == "checkbox") {
                        if (jQuery("#' . __CLASS__ . '_"+opl).attr("checked", "checked")) {
                            CollectionObject[id][opl] = 1;
                        }else {
                            CollectionObject[id][opl] = 0;
                        }
                    }
                }
                colls = clone(CollectionNomen[id]);
                resultMoved = allCheck(Collection,true,id, false, CollectionNomen[id], "Изменить");
                if (resultMoved == "true") {
                    var post =                 jQuery("#aperture-form").serialize();
                    post += "&' . __CLASS__ . '%5BObjectTypeSelected%5D=" +colls.ObjectTypeSelected+"&' . __CLASS__ . '%5BPartTypeSelected%5D="+colls.PartTypeSelected;
                    post +="&calc=EmbeddedObjectsMC";
                    jQuery.ajax({type: "POST",
                    url:"' . Yii::app()->createUrl("/steps/default/ajaxdata") . '",
                    data:post,
                    async: false,
                    success: function(data) {
                        contentJSON = $.parseJSON(data);
                        if (contentJSON.error) {
                            simpleAlertDialog(objDataSimpleAlert, \''. Yii::t('steps', 'Установить этот объект нельзя!') .'\');
                        } else {
                            if (jQuery("#' . __CLASS__ . '_ObjectType").val() == "wicket") {
                                var post2 ="key="+ jQuery("#key").val()+"&calc=WicketCalculationMC";
                                jQuery.ajax({type: "POST",
                                    url:"' . Yii::app()->createUrl("/steps/default/ajaxdata") . '",
                                    data:post2,
                                    async: false,
                                    success: function(data) {
                                        heightW = contentJSON.WicketHeight;
                                        widthW = contentJSON.WicketWidth;
                                        wicketJSON = $.parseJSON(data);
                                        //wickets = {"X": wicketJSON.WicketX,"Y":wicketJSON.WicketY,"height":heightW,"width":widthW,"name":contentJSON.WicketName};
                                        var svgW = jQuery("#apertureSVG svg g#Shield");
                                        var heightShield = "' . Yii::app()->container->ShieldRealHeight . '";
                                        var widthShield = "' . Yii::app()->container->ShieldWidth . '";

                                        if (jQuery("#' . __CLASS__ . '_RemoveRestrictions").attr("checked") == "checked") {
                                            addWicket(svgW,heightShield, widthShield,wickets,1,countCollection/*-1*/,1);
                                        } else {
                                            addWicket(svgW,heightShield, widthShield,wickets,0,countCollection/*-1*/,1);
                                        }
                                        if (jQuery("#EmbeddedObjectsMI_Viewdistance").prop("checked")) {
                                            clearElements(svgW,".views");
                                            viewDistance(svgW,widthShield,heightShield,Collection);
                                        }
                                        clearElements(svgM,".hinges");
                                        var s = Snap("#apertureSVG svg");
                                        number = countCollection/*-1*/;
                                        g = s.select("#gWicket" + number);
                                        Shield = s.select("#Shields");
                                        g.parentSelect(Shield);
                                        g.drag();
                                    }
                                });
                            } else if (jQuery("#' . __CLASS__ . '_ObjectType").val() == "window") {
                                var post2 ="key="+ jQuery("#key").val()+"&calc=WindowsCalculationMC";
                                jQuery.ajax({type: "POST",
                                    url:"' . Yii::app()->createUrl("/steps/default/ajaxdata") . '",
                                    data:post2,
                                    async: false,
                                    success: function(data) {
                                        if (!contentJSON.error) {
                                            coord = $.parseJSON(data);
                                            var svgWin = jQuery("#apertureSVG svg g#Shield");
                                            var heightShield = "' . Yii::app()->container->ShieldRealHeight . '";
                                            var widthShield = "' . Yii::app()->container->ShieldWidth . '";

                                            addWindow(svgWin,heightShield,widthShield,contentJSON,coord,countCollection/*-1*/,1);
                                            if (jQuery("#EmbeddedObjectsMI_Viewdistance").prop("checked")) {
                                                clearElements(svgWin,".views");
                                                viewDistance(svgWin,widthShield,heightShield,Collection);
                                            }
                                        } else {
                                            simpleAlertDialog(objDataSimpleAlert, \''. Yii::t('steps', 'нельзя поставить окна!') .'\');
                                        }
                                    }
                                });
                            }
                        }
                    }
                });
            } else {

                jQuery("#' . __CLASS__ . '_ObjectPaddingX").val(objectTempes.ObjectPaddingX);
                    
                jQuery("#' . __CLASS__ . '_ObjectPaddingY").val(objectTempes.ObjectPaddingY);
                result2 = allCheck(Collection,true,id, false, CollectionNomen[id],fill, "Изменить");
                var post =                 jQuery("#aperture-form").serialize();
                post += "&' . __CLASS__ . '%5BObjectTypeSelected%5D=" +colls.ObjectTypeSelected+"&' . __CLASS__ . '%5BPartTypeSelected%5D="+colls.PartTypeSelected;
                post +="&calc=EmbeddedObjectsMC";
                jQuery.ajax({type: "POST",
                    url:"' . Yii::app()->createUrl("/steps/default/ajaxdata") . '",
                    data:post,
                    async: false,
                    success: function(data) {
                        contentJSON = $.parseJSON(data);
                        if (contentJSON.error) {
                            simpleAlertDialog(objDataSimpleAlert, \''. Yii::t('steps', 'Установить этот объект нельзя!') .'\');
                        } else {
                            if (jQuery("#' . __CLASS__ . '_ObjectType").val() == "wicket") {

                                var post2 ="key="+ jQuery("#key").val()+"&calc=WicketCalculationMC";
                                jQuery.ajax({type: "POST",
                                    url:"' . Yii::app()->createUrl("/steps/default/ajaxdata") . '",
                                    data:post2,
                                    async: false,
                                    success: function(data) {
                                    heightW = contentJSON.WicketHeight;
                                    widthW = contentJSON.WicketWidth;
                                    wicketJSON = $.parseJSON(data);
                                    //wickets = {"X": wicketJSON.WicketX,"Y":wicketJSON.WicketY,"height":heightW,"width":widthW,"name":contentJSON.WicketName};
                                    var svgW = jQuery("#apertureSVG svg g#Shield");
                                    var heightShield = "' . Yii::app()->container->ShieldRealHeight . '";
                                    var widthShield = "' . Yii::app()->container->ShieldWidth . '";

                                    if (jQuery("#' . __CLASS__ . '_RemoveRestrictions").attr("checked") == "checked") {
                                        addWicket(svgW,heightShield, widthShield,wickets,1,countCollection/*-1*/,1);
                                    } else {
                                        addWicket(svgW,heightShield, widthShield,wickets,0,countCollection/*-1*/,1);
                                    }
                                    if (jQuery("#EmbeddedObjectsMI_Viewdistance").prop("checked")) {
                                        clearElements(svgW,".views");
                                        viewDistance(svgW,widthShield,heightShield,Collection);
                                    }
                                    clearElements(svgM,".hinges");
                                    var s = Snap("#apertureSVG svg");
                                    number = countCollection/*-1*/;
                                    g = s.select("#gWicket" + number);
                                    Shield = s.select("#Shields");
                                    g.parentSelect(Shield);
                                    g.drag();
                                }
                            });
                        } else if (jQuery("#' . __CLASS__ . '_ObjectType").val() == "window") {
                            var post2 ="key="+ jQuery("#key").val()+"&calc=WindowsCalculationMC";
                            jQuery.ajax({type: "POST",
                                url:"' . Yii::app()->createUrl("/steps/default/ajaxdata") . '",
                                data:post2,
                                async: false,
                                success: function(data) {
                                    if (!contentJSON.error) {
                                        coord = $.parseJSON(data);
                                        var svgWin = jQuery("#apertureSVG svg g#Shield");
                                        var heightShield = "' . Yii::app()->container->ShieldRealHeight . '";
                                        var widthShield = "' . Yii::app()->container->ShieldWidth . '";

                                        addWindow(svgWin,heightShield,widthShield,contentJSON,coord,countCollection/*-1*/,1);
                                        if (jQuery("#EmbeddedObjectsMI_Viewdistance").prop("checked")) {
                                            clearElements(svgWin,".views");
                                            viewDistance(svgWin,widthShield,heightShield,Collection);
                                        }
                                    } else {
                                        simpleAlertDialog(objDataSimpleAlert, \''. Yii::t('steps', 'нельзя поставить окна!') .'\');
                                    }
                                }
                            });
                        }
                    }
                }
            });
                simpleAlertDialog(objDataSimpleAlert, resultMoved);
            }



            if((jQuery("#' . __CLASS__ . '_ObjectTypeSelected").val() != "") &&(jQuery("#' . __CLASS__ . '_PartTypeSelected").val()!="")) {
                wicket = content.group[jQuery("#' . __CLASS__ . '_ObjectTypeSelected").val()][jQuery("#' . __CLASS__ . '_PartTypeSelected").val()];

                jQuery("#' . __CLASS__ . '_ObjectDefaultWidth").val(wicket.default_width);
                jQuery("#' . __CLASS__ . '_ObjectDefaultHeight").val(wicket.default_height);
                jQuery("#' . __CLASS__ . '_ObjectPaddingX").val(wicket.min_left);

                jQuery("#' . __CLASS__ . '_ObjectPartNumber").val(wicket.article);
                jQuery("#' . __CLASS__ . '_ObjectMinLeft").val(wicket.min_left);
                jQuery("#' . __CLASS__ . '_ObjectMinWidth").val(wicket.min_width);
                jQuery("#' . __CLASS__ . '_ObjectMaxWidth").val(wicket.max_width);
                jQuery("#' . __CLASS__ . '_ObjectMinHeight").val(wicket.min_height);
                jQuery("#' . __CLASS__ . '_ObjectMaxHeight").val(wicket.max_height);
                jQuery("#' . __CLASS__ . '_ObjectMinTop").val(wicket.min_top);
                jQuery("#' . __CLASS__ . '_ObjectMinRight").val(wicket.min_right);
                jQuery("#' . __CLASS__ . '_ObjectMinBottom").val(wicket.min_bottom);
                jQuery("#' . __CLASS__ . '_ObjectRecommendedLeft").val(wicket.recommended_left);
                jQuery("#' . __CLASS__ . '_ObjectRecommendedTop").val(wicket.recommended_top);
                jQuery("#' . __CLASS__ . '_ObjectRecommendedRight").val(wicket.recommended_right);
                jQuery("#' . __CLASS__ . '_ObjectRecommendedBottom").val(wicket.recommended_bottom);                        
                jQuery("#' . __CLASS__ . '_ObjectType").val(wicket.object);
                jQuery("#' . __CLASS__ . '_ObjectIsRadius").val(wicket.Isradius);
                jQuery("#' . __CLASS__ . '_ObjectRadius").val(wicket.radius);
                jQuery("#' . __CLASS__ . '_ObjectIsInner").val(wicket.is_inner);
                jQuery("#' . __CLASS__ . '_ObjectMinDistance").val(wicket.distance_object);
            }

        }
        ';
        $query .= '
        //создание диалогового окна по ID объекта
        function typeDialogObject(id){
            id = parseInt(id);
            
            //выбрать текущий встраеваемый обьект
            $("#EmbeddedObjectsMI_ObjectTypeSelected option[value=" + CollectionNomen[id].ObjectTypeSelected + "]").prop("selected", true);
            $("#EmbeddedObjectsMI_ObjectTypeSelected").change(); //вызов события, чтоб сформировался выпадающий список PartTypeSelected
            $("#EmbeddedObjectsMI_PartTypeSelected option[value=" + CollectionNomen[id].PartTypeSelected + "]").prop("selected", true);
            
            
            objectTempes = CollectionObject[id];
            jQuery("#aperture-form").parent().append("<div id=\"paramObject\"><form method=\"post\" action=\"/steps\" id=\"dopObjectform\">     <ul class=\"clearfix\"></ul></form></div>");
            for(var opl in CollectionObject[id]){
                $("#aperture-form #' . __CLASS__ . ' li#li_"+opl).appendTo("#paramObject ul");
                jQuery("#paramObject #' . __CLASS__ . '_"+opl).val(CollectionObject[id][opl]);

             if (jQuery("#paramObject #' . __CLASS__ . '_"+opl).attr("type") == "checkbox") {
                    if (CollectionObject[id][opl] == 1) {

                        jQuery("#paramObject #' . __CLASS__ . '_"+opl).attr("checked", "checked");
                    }
                    else {

                        jQuery("#paramObject #' . __CLASS__ . '_"+opl).removeAttr("checked");
                    }
                    jQuery("#paramObject #' . __CLASS__ . '_"+opl).val(1);
                }
                jQuery("#paramObject li#li_"+opl).hide();
            }

            if (CollectionObject[id].ObjectType == "wicket") {
                var WicketX  = parseInt(' . Yii::app()->container->WicketX . ');
                var postWicket = "key="+ jQuery("#key").val()+"&calc=WicketCalculationMC";
                jQuery.ajax({type: "POST",
                    url:"' . Yii::app()->createUrl("/steps/default/ajaxdata") . '",
                    data:postWicket,
                    async: false,
                    success: function(data) {
                        contentJSON = $.parseJSON(data);
                        WicketX = contentJSON.WicketX;
                        //WicketX = parseInt(' . Yii::app()->container->WicketX . ');
                    }
                });
                jQuery("#paramObject li#li_RemoveRestrictions").show();
                jQuery("#paramObject li#li_ObjectDefaultWidth").show();
                jQuery("#paramObject li#li_ObjectDefaultHeight").show();
                jQuery("#paramObject li#li_LocationTypeX").show();
                jQuery("#paramObject li#li_ObjectPaddingX").show();
                jQuery("#paramObject li#li_ObjectHingesLocation").show();
                jQuery("#paramObject li#li_ObjectFringingColor").show();
                jQuery("#paramObject li#li_ObjectDirection").show();
                jQuery("#paramObject li#li_ObjectPusher").show();
                jQuery("#paramObject li#li_ObjectPeephole").show();
                
                jQuery("#' . __CLASS__ . '_ObjectPaddingX").val(WicketX);
                
            } else if (CollectionObject[id].ObjectType == "window") {
                jQuery("#paramObject li#li_ObjectDefaultWidth").show();
                jQuery("#paramObject li#li_ObjectDefaultHeight").show();
                jQuery("#paramObject li#li_LocationTypeX").show();
                jQuery("#paramObject li#li_ObjectPaddingX").show();
                jQuery("#paramObject li#li_ObjectCount").show();
                jQuery("#paramObject li#li_LocationTypeX").show();
                jQuery("#paramObject li#li_ObjectPaddingX").show();
                jQuery("#paramObject li#li_AutoCalc").show();
                // if (jQuery("#paramObject #' . __CLASS__ . '_AutoCalc").prop("checked")) {
                // jQuery("#paramObject li#li_ObjectStep").hide();
                //  } else {
                jQuery("#paramObject li#li_ObjectStep").show();
                //  }
            }
            jQuery("#paramObject").dialog({
            modal: true,
            resizable: false,
            open: function(event, ui) {
            $(this).parent().children().children(".ui-dialog-titlebar-close").hide();
        },
	buttons: {
	    \''. Yii::t('steps', 'Изменить') .'\': function() {

            for(var opl in CollectionObject[id]){
                CollectionObject[id][opl] = jQuery("#paramObject #' . __CLASS__ . '_"+opl).val();
                if (jQuery("#paramObject #' . __CLASS__ . '_"+opl).attr("type") == "checkbox") {
                    if (jQuery("#paramObject #' . __CLASS__ . '_"+opl).attr("checked") == "checked") {
                        CollectionObject[id][opl] = 1;
                    } else {
                        CollectionObject[id][opl] = 0;
                    }
                }
            }

            for(var opl in CollectionObject[id]){
                if (jQuery("#' . __CLASS__ . '_" + opl).val() != "NaN") {
                    $("#paramObject li#li_"+opl).appendTo("#aperture-form li#' . __CLASS__ . ' ul").hide();
                }
            }

            var post = jQuery("#aperture-form").serialize();

            post += "&' . __CLASS__ . '%5BObjectTypeSelected%5D=" +CollectionNomen[id].ObjectTypeSelected;
            post += "&' . __CLASS__ . '%5BPartTypeSelected%5D="+CollectionNomen[id].PartTypeSelected;
            post +="&calc=EmbeddedObjectsMC";
            result = allCheck(Collection,true,id, false, CollectionNomen[id],fill, "Изменить");
            

            if (result == "true") {
                jQuery.ajax({type: "POST",
                url:"' . Yii::app()->createUrl("/steps/default/ajaxdata") . '",
                data:post,
                async: false,
                success: function(data) {
                    contentJSON = $.parseJSON(data);
                    if (contentJSON.error) {
                        simpleAlertDialog(objDataSimpleAlert, \''. Yii::t('steps', 'Установить этот объект нельзя!') .'\');
                    } else {
                        if (jQuery("#' . __CLASS__ . '_ObjectType").val() == "wicket") {

                            var post2 ="key="+ jQuery("#key").val()+"&calc=WicketCalculationMC";
                            jQuery.ajax({type: "POST",
                                url:"' . Yii::app()->createUrl("/steps/default/ajaxdata") . '",
                                data:post2,
                                async: false,
                                success: function(data) {
                                    heightW = contentJSON.WicketHeight;
                                    widthW = contentJSON.WicketWidth;
                                    wicketJSON = $.parseJSON(data);
                                        //wickets = {"X": wicketJSON.WicketX,"Y":wicketJSON.WicketY,"height":heightW,"width":widthW,"name":contentJSON.WicketName};
                                        var svgW = jQuery("#apertureSVG svg g#Shield");
                                        var heightShield = "' . Yii::app()->container->ShieldRealHeight . '";
                                        var widthShield = "' . Yii::app()->container->ShieldWidth . '";

                                        if (jQuery("#' . __CLASS__ . '_RemoveRestrictions").attr("checked") == "checked") {
                                            addWicket(svgW,heightShield, widthShield,wickets,1,countCollection/*-1*/,1);
                                        } else {
                                            addWicket(svgW,heightShield, widthShield,wickets,0,countCollection/*-1*/,1);
                                        }
                                        if (jQuery("#EmbeddedObjectsMI_Viewdistance").prop("checked")) {
                                            clearElements(svgW,".views");
                                            viewDistance(svgW,widthShield,heightShield,Collection);
                                        }
                                        clearElements(svgM,".hinges");
                                        var s = Snap("#apertureSVG svg");
                                        number = countCollection/*-1*/;
                                        g = s.select("#gWicket" + number);
                                        Shield = s.select("#Shields");
                                        g.parentSelect(Shield);
                                        g.drag();
                                    }
                                });
                            } else if (jQuery("#' . __CLASS__ . '_ObjectType").val() == "window") {

                                var post2 ="key="+ jQuery("#key").val()+"&calc=WindowsCalculationMC";
                                jQuery.ajax({type: "POST",
                                    url:"' . Yii::app()->createUrl("/steps/default/ajaxdata") . '",
                                    data:post2,
                                    async: false,
                                    success: function(data) {
                                        if (!contentJSON.error) {
                                            coord = $.parseJSON(data);
                                            var svgWin = jQuery("#apertureSVG svg g#Shield");
                                            var heightShield = "' . Yii::app()->container->ShieldRealHeight . '";
                                            var widthShield = "' . Yii::app()->container->ShieldWidth . '";

                                            addWindow(svgWin,heightShield,widthShield,contentJSON,coord,countCollection/*-1*/,1);
                                            if (jQuery("#EmbeddedObjectsMI_Viewdistance").prop("checked")) {
                                                clearElements(svgWin,".views");
                                                viewDistance(svgWin,widthShield,heightShield,Collection);
                                            }
                                        } else {simpleAlertDialog(objDataSimpleAlert, \''. Yii::t('steps', 'нельзя поставить окна!') .'\');}
                                    }
                                });
                            }
                        }
                    }
                });
            } else {
                simpleAlertDialog(objDataSimpleAlert, result);
            }



            if(result == "true") {
                    if ((jQuery("#' . __CLASS__ . '_ObjectTypeSelected").val() != "") &&(jQuery("#' . __CLASS__ . '_PartTypeSelected").val()!="")) {
                        wicket = content.group[jQuery("#' . __CLASS__ . '_ObjectTypeSelected").val()][jQuery("#' . __CLASS__ . '_PartTypeSelected").val()];

                        jQuery("#' . __CLASS__ . '_ObjectDefaultWidth").val(wicket.default_width);
                        jQuery("#' . __CLASS__ . '_ObjectDefaultHeight").val(wicket.default_height);
                        jQuery("#' . __CLASS__ . '_ObjectPaddingX").val(wicket.min_left);

                        jQuery("#' . __CLASS__ . '_ObjectPartNumber").val(wicket.article);
                        jQuery("#' . __CLASS__ . '_ObjectMinLeft").val(wicket.min_left);
                        jQuery("#' . __CLASS__ . '_ObjectMinWidth").val(wicket.min_width);
                        jQuery("#' . __CLASS__ . '_ObjectMaxWidth").val(wicket.max_width);
                        jQuery("#' . __CLASS__ . '_ObjectMinHeight").val(wicket.min_height);
                        jQuery("#' . __CLASS__ . '_ObjectMaxHeight").val(wicket.max_height);
                        jQuery("#' . __CLASS__ . '_ObjectMinTop").val(wicket.min_top);
                        jQuery("#' . __CLASS__ . '_ObjectMinRight").val(wicket.min_right);
                        jQuery("#' . __CLASS__ . '_ObjectMinBottom").val(wicket.min_bottom);
                        jQuery("#' . __CLASS__ . '_ObjectRecommendedLeft").val(wicket.recommended_left);
                        jQuery("#' . __CLASS__ . '_ObjectRecommendedTop").val(wicket.recommended_top);
                        jQuery("#' . __CLASS__ . '_ObjectRecommendedRight").val(wicket.recommended_right);
                        jQuery("#' . __CLASS__ . '_ObjectRecommendedBottom").val(wicket.recommended_bottom);
                        jQuery("#' . __CLASS__ . '_ObjectType").val(wicket.object);
                        jQuery("#' . __CLASS__ . '_ObjectIsRadius").val(wicket.Isradius);
                        jQuery("#' . __CLASS__ . '_ObjectRadius").val(wicket.radius);
                        jQuery("#' . __CLASS__ . '_ObjectIsInner").val(wicket.is_inner);
                        jQuery("#' . __CLASS__ . '_ObjectMinDistance").val(wicket.distance_object);
                    }
                    //закрыть диолог
                    $(this).dialog("close");
                    $("#paramObject").remove();
    
                    resetEmbeddedObject();
                } else {
                    for(var opl in CollectionObject[id]){
                        $("#aperture-form #' . __CLASS__ . ' li#li_"+opl).appendTo("#paramObject");
                        jQuery("#paramObject #' . __CLASS__ . '_"+opl).val(CollectionObject[id][opl]);

                        if (jQuery("#paramObject #' . __CLASS__ . '_"+opl).attr("type") == "checkbox") {
                            if (CollectionObject[id][opl] == 1) {

                                jQuery("#paramObject #' . __CLASS__ . '_"+opl).attr("checked", "checked");
                            }
                            else {

                                jQuery("#paramObject #' . __CLASS__ . '_"+opl).removeAttr("checked");
                            }
                            jQuery("#paramObject #' . __CLASS__ . '_"+opl).val(1);
                        }
                        jQuery("#paramObject li#li_"+opl).hide();
                    }
                    jQuery("#paramObject li#li_RemoveRestrictions").show();
                    jQuery("#paramObject li#li_ObjectDefaultWidth").show();
                    jQuery("#paramObject li#li_ObjectDefaultHeight").show();
                    jQuery("#paramObject li#li_LocationTypeX").show();
                    jQuery("#paramObject li#li_ObjectPaddingX").show();

                    if (CollectionObject[id].ObjectType == "window") {
                        jQuery("#paramObject li#li_ObjectCount").show();
                        jQuery("#paramObject li#li_LocationTypeX").show();
                        jQuery("#paramObject li#li_ObjectPaddingX").show();
                        jQuery("#paramObject li#li_AutoCalc").show();
                
//                  if (jQuery("#paramObject #' . __CLASS__ . '_AutoCalc").prop("checked")) {
//                      jQuery("#paramObject li#li_ObjectStep").hide();
//                  } else {
                        jQuery("#paramObject li#li_ObjectStep").show();
//                  }
                }
            }
        },
	\''. Yii::t('steps', 'Закрыть') .'\': function(){
	    for(var opl in objectTempes){
                if (jQuery("#' . __CLASS__ . '_" + opl).val() != "NaN") {
                    $("#paramObject li#li_"+opl).appendTo("#aperture-form li#' . __CLASS__ . ' ul").hide();
                }
            }
            if((jQuery("#' . __CLASS__ . '_ObjectTypeSelected").val() != "") &&(jQuery("#' . __CLASS__ . '_PartTypeSelected").val()!="")) {
                wicket = content.group[jQuery("#' . __CLASS__ . '_ObjectTypeSelected").val()][jQuery("#' . __CLASS__ . '_PartTypeSelected").val()];
                jQuery("#' . __CLASS__ . '_ObjectDefaultWidth").val(wicket.default_width);
                jQuery("#' . __CLASS__ . '_ObjectDefaultHeight").val(wicket.default_height);
                jQuery("#' . __CLASS__ . '_ObjectPaddingX").val(wicket.min_left);

                jQuery("#' . __CLASS__ . '_ObjectPartNumber").val(wicket.article);
                jQuery("#' . __CLASS__ . '_ObjectMinLeft").val(wicket.min_left);
                jQuery("#' . __CLASS__ . '_ObjectMinWidth").val(wicket.min_width);
                jQuery("#' . __CLASS__ . '_ObjectMaxWidth").val(wicket.max_width);
                jQuery("#' . __CLASS__ . '_ObjectMinHeight").val(wicket.min_height);
                jQuery("#' . __CLASS__ . '_ObjectMaxHeight").val(wicket.max_height);
                jQuery("#' . __CLASS__ . '_ObjectMinTop").val(wicket.min_top);
                jQuery("#' . __CLASS__ . '_ObjectMinRight").val(wicket.min_right);
                jQuery("#' . __CLASS__ . '_ObjectMinBottom").val(wicket.min_bottom);
                jQuery("#' . __CLASS__ . '_ObjectRecommendedLeft").val(wicket.recommended_left);
                jQuery("#' . __CLASS__ . '_ObjectRecommendedTop").val(wicket.recommended_top);
                jQuery("#' . __CLASS__ . '_ObjectRecommendedRight").val(wicket.recommended_right);
                jQuery("#' . __CLASS__ . '_ObjectRecommendedBottom").val(wicket.recommended_bottom);    
                jQuery("#' . __CLASS__ . '_ObjectType").val(wicket.object);
                jQuery("#' . __CLASS__ . '_ObjectIsRadius").val(wicket.Isradius);
                jQuery("#' . __CLASS__ . '_ObjectRadius").val(wicket.radius);
                jQuery("#' . __CLASS__ . '_ObjectIsInner").val(wicket.is_inner);
                jQuery("#' . __CLASS__ . '_ObjectMinDistance").val(wicket.distance_object);
            }
            $(this).dialog("close");
            $("#paramObject").remove();
            $(this).remove();
             
            resetEmbeddedObject();
	}
    }
	});
        };
        ';
        $query .= '
            createOptionEm(content.group,"ObjectTypeSelected",0,0);
            jQuery("li#li_RemoveRestrictions").hide();
            jQuery("li#li_ObjectDefaultWidth").hide();
            jQuery("li#li_ObjectDefaultHeight").hide();
            //jQuery("li#li_LocationTypeX [value=1]").attr("selected","selected");
            jQuery("li#li_LocationTypeX").hide();
            //jQuery("#' . __CLASS__ . '_ObjectPaddingX").attr("disabled","disabled");
            jQuery("li#li_ObjectPaddingX").hide();
            jQuery("li#li_ObjectHingesLocation").hide();
            jQuery("li#li_ObjectFringingColor").hide();
            jQuery("li#li_ObjectDirection").hide();
            jQuery("li#li_ObjectPusher").hide();
            jQuery("li#li_ObjectPeephole").hide();
            jQuery("li#li_ObjectCount").hide();
            jQuery("#' . __CLASS__ . '_ObjectCount").val(1);
            jQuery("li#li_ObjectStep").hide();
            jQuery("#' . __CLASS__ . '_AutoCalc").attr("checked","checked");
            jQuery("#' . __CLASS__ . '_ObjectStep").live("keydown",function(){return false});
            jQuery("#' . __CLASS__ . '_ObjectStep").val(-1);

            jQuery("li#li_AutoCalc").hide();
        ';
        //вешаем обработчик, чтобы активировать/деактивировать зависимые элементы
        $query .= '
            //reailtime обработчик контролов
            form_input_LocationTypeX.click(function(){
                if (form_input_LocationTypeX.val() == "Задать расстояние слева") {
                    form_input_ObjectPaddingX.prop("disabled", true);
                } else {
                    form_input_ObjectPaddingX.prop("disabled", false);
                }
            });
                        
        ';
        $query .= '
            jQuery("#' . __CLASS__ . '_EmbeddedShieldWeight").val(' . Yii::app()->container->ShieldWeight . ');
            jQuery("#' . __CLASS__ . '_EmbeddedShieldBottomPanelWeight").val(' . Yii::app()->container->ShieldBottomPanelWeight / 2 . ');
            jQuery("#' . __CLASS__ . '_EmbeddedShieldWeight").attr("disabled","disabled");
            jQuery("#' . __CLASS__ . '_EmbeddedShieldBottomPanelWeight").attr("disabled","disabled");
        ';
        if (Yii::app()->container->EmbeddedShieldWeightType == 1) {
            $query .= '

            jQuery("#' . __CLASS__ . '_EmbeddedShieldWeightType").attr("checked","checked");
            jQuery("#' . __CLASS__ . '_EmbeddedShieldWeight").removeAttr("disabled");
            jQuery("#' . __CLASS__ . '_EmbeddedShieldBottomPanelWeight").removeAttr("disabled");
            ';
        }
        $query .= '
            $("#EmbeddedObjectsMI_Viewdistance").removeAttr("checked");
            
            function resetEmbeddedObject(){
                //сбросить выбраный тип объекта
                $("#EmbeddedObjectsMI_ObjectTypeSelected").val("");
                $("#EmbeddedObjectsMI_PartTypeSelected").val("");
                $("#EmbeddedObjectsMI_ObjectTypeSelected").change();  //убирает события с рисунка
            }
        ';
        
        $query .= $this->getTooltip('EmbeddedObjectsMI_ObjectTypeSelected', '24');
        $query .= $this->getTooltip('EmbeddedObjectsMI_PartTypeSelected', '25');
        $query .= $this->getTooltip('EmbeddedObjectsMI_EmbeddedShieldWeight', '26');
        $query .= $this->getTooltip('EmbeddedObjectsMI_EmbeddedShieldBottomPanelWeight', '30');
        $query .= $this->getTooltip('EmbeddedObjectsMI_Viewdistance', '172');
        $query .= $this->getTooltip('EmbeddedObjectsMI_EmbeddedShieldWeightType', '173');
        $query .= $this->getTooltip('bPrev', '178');
        $query .= $this->getTooltip('bNext', '177');

        return $query;
    }

}
