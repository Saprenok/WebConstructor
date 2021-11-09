<?php

/**
 * Модуль интерфейса Цвет окантовки
 *
 * PHP version 5.5
 * @category Yii
 * @author   Charnou Vitaliy <graffov87@gmail.com>
 */
class ColorEdgingMI extends AbstractModelInterface
{

     /**
     * Список модулей, выполняемых до запуска формы
     *
     * @var array
     */
    public $beforeCalculation = array(
        'FormColorEdgingMC',
    );

    /**
     * Название модуля
     *
     * @var string
     */
    public $nameModule = 'ColorEdgingMI';

    /**
     * Список модулей, которые выполняются при переходе на следующую форму
     * @var array
     */
    public $moduleCalculation = array(
        //'EmbeddedObjectsMC',
        'ColorEdgingMC'
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
        return Yii::t('steps', 'Цвет окантовки');
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
            //1 для EmbeddedObjectsMI!
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
        return array();
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
        $query = "";
        $query .= "
            $('select option[value=\"\"]').remove();
        ";
        // Активность элемента
        $query = "
            //список входных элементов
            var container_DilerVersion      = parseInt('" . Yii::app()->container->DilerVersion . "');
            var container_SpecialEdition    = parseInt('" . Yii::app()->container->SpecialEdition . "');
            var container_RegionChina       = parseInt('" . Yii::app()->container->RegionChina . "');
            var container_RegionMoscow      = parseInt('" . Yii::app()->container->RegionMoscow . "');
            var container_RegionEurope      = parseInt('" . Yii::app()->container->RegionEurope . "');
            var container_Colout_n   = '" . (string) Yii::app()->container->Colout_n . "'.toLowerCase();
            var container_Colout     = '" . (string) Yii::app()->container->Colout . "';
            var container_TypeF      = '" . (string) Yii::app()->container->TypeF . "'.toLowerCase();
            var container_Index      = '" . (string) Yii::app()->container->Index . "'.toLowerCase();
            var container_VidS       = '" . (string) Yii::app()->container->VidS . "'.toLowerCase();

            // Обьявление переменных, которые могуть быть скрыты
            var form_li_formOtherColorEdging = $('#li_formOtherColorEdging');
            var form_li_formColorEdging = $('#li_formColorEdging');
            var form_li_formOtherColorPost = $('#li_formOtherColorPost');
            var form_li_formColorPost = $('#li_formColorPost');
            var form_li_formOtherColorGrid = $('#li_formOtherColorGrid');
            var form_li_formColorGrid = $('#li_formColorGrid');
            var form_li_formOtherColorTips = $('#li_formOtherColorTips');
            var form_li_formColorTips = $('#li_formColorTips');
            var form_li_formOtherColorVenzel = $('#li_formOtherColorVenzel');
            var form_li_formColorVenzel = $('#li_formColorVenzel');

            form_li_formOtherColorEdging.hide();
            form_li_formColorEdging.hide();
            form_li_formOtherColorPost.hide();
            form_li_formColorPost.hide();
            form_li_formOtherColorGrid.hide();
            form_li_formColorGrid.hide();
            form_li_formOtherColorTips.hide();
            form_li_formColorTips.hide();
            form_li_formOtherColorVenzel.hide();
            form_li_formColorVenzel.hide();

            //Список элементов управления
            var form_input_formColorEdging = $('#" . __CLASS__ . "_formColorEdging');
            var form_input_formOtherColorEdging = $('#" . __CLASS__ . "_formOtherColorEdging');
            var form_input_formColorPost = $('#" . __CLASS__ . "_formColorPost');
            var form_input_formOtherColorPost = $('#" . __CLASS__ . "_formOtherColorPost');

            var form_input_formColorGrid = $('#" . __CLASS__ . "_formColorGrid');
            $('#" . __CLASS__ . "_formColorGrid option:first').remove();
            var form_input_formOtherColorGrid = $('#" . __CLASS__ . "_formOtherColorGrid');

            var form_input_formColorTips = $('#" . __CLASS__ . "_formColorTips');
            $('#" . __CLASS__ . "_formColorTips option:first').remove();
            var form_input_formOtherColorTips = $('#" . __CLASS__ . "_formOtherColorTips');

            var form_input_formColorVenzel = $('#" . __CLASS__ . "_formColorVenzel');
            $('#" . __CLASS__ . "_formColorVenzel option:first').remove();
            var form_input_formOtherColorVenzel = $('#" . __CLASS__ . "_formOtherColorVenzel');

            // Обьявление переменных, которые могуть быть скрыты
            var form_li_formAllColorEdging = $('#li_formAllColorEdging');
            form_li_formAllColorEdging.hide();
            var form_input_formAllColorEdging = $('#" . __CLASS__ . "_formAllColorEdging');

        ";
        //Присвоение значений из контейнера
        if (Yii::app()->container->formAllColorEdging) {
            if ((Yii::app()->container->OtherColorEdging != '0') && is_string(Yii::app()->container->OtherColorEdging)) {
                $query .= '$("#' . __CLASS__ . '_formOtherColorEdging").val("' . (string) Yii::app()->container->OtherColorEdging . '");';
            }
            if ((Yii::app()->container->ColorEdging != '0') && is_string(Yii::app()->container->ColorEdging)) {
                $query .= '$("#' . __CLASS__ . '_formColorEdging [value=\"' . Yii::app()->container->ColorEdging . '\"]").attr("selected","selected");';
            }
            if ((Yii::app()->container->ColorPost != '0') && is_string(Yii::app()->container->ColorPost)) {
                $query .= '$("#' . __CLASS__ . '_formColorPost [value=\"' . Yii::app()->container->ColorPost . '\"]").attr("selected","selected");';
            }
            if ((Yii::app()->container->ColorGrid != '0') && is_string(Yii::app()->container->ColorGrid)) {
                $query .= '$("#' . __CLASS__ . '_formColorGrid [value=\"' . Yii::app()->container->ColorGrid . '\"]").attr("selected","selected");';
            }
            if ((Yii::app()->container->ColorTips != '0') && is_string(Yii::app()->container->ColorTips)) {
                $query .= '$("#' . __CLASS__ . '_formColorTips [value=\"' . Yii::app()->container->ColorTips . '\"]").attr("selected","selected");';
            }
            if ((Yii::app()->container->ColorVenzel != '0') && is_string(Yii::app()->container->ColorVenzel)) {
                $query .= '$("#' . __CLASS__ . '_formColorVenzel [value=\"' . Yii::app()->container->ColorVenzel . '\"]").attr("selected","selected");';
            }
        }

        // Алгоритмы элемента
        $query .= "
            all = $('input[id^=\"" . __CLASS__ . "_form\"], select[id^=\"" . __CLASS__ . "_form\"]');
            all.each (function(index, elem) {
                $(elem).on('click', function() {
                    checkColor();
                    //записать данные в хранилище
                    getData();
                });
            });

            function getData(){
                var array = [];
                var obj = {};
                var elems = $('#aperture-form li#" . __CLASS__ . " ul').find('select, input[type=\'checkbox\'], input[type=\'text\']');
                elems.each(function(indx, elem){
                    if ($(elem).prop('tagName') === 'SELECT') {
                        var name = $(elem).attr('name');
                        var selected = $(elem).find('option:selected').val();
                        name = getName(name);
                        obj[name] = selected;
                    } else {
                        if ($(elem).prop('tagName') === 'INPUT' && $(elem).attr('type').toLowerCase() === 'checkbox'){
                            var name = $(elem).attr('name');
                            var isChecked = $(elem).prop('checked');
                            name = getName(name);
                            obj[name] = isChecked;
                        }
                        if ($(elem).prop('tagName') === 'INPUT' && $(elem).attr('type').toLowerCase() === 'text'){
                            var name = $(elem).attr('name');
                            var value = $(elem).val();
                            name = getName(name);
                            obj[name] = value;
                        }
                    }
                });

                $('#" . __CLASS__ . "_formAllColorEdging').val(JSON.stringify(obj));
            }

            function getName(name) {
                var length = '" . __CLASS__ . "'.length;
                var fullLength = name.length;
                return name.substring(length + 1 , fullLength -1);
            }
        ";

        //Инициализация конструкции
        if (Yii::app()->container->isNewProduct == 1 && !$this->isFormVisited()) {
            $query .= "
               form_input_formOtherColorGrid.val(form_input_formColorGrid.val());
               form_input_formOtherColorTips.val(form_input_formColorTips.val());
               form_input_formOtherColorVenzel.val(form_input_formColorVenzel.val());
            ";   
            /*$query .= "
                console.log('container_RegionMoscow = ' + container_RegionMoscow);
                if (!container_RegionMoscow) {
                    console.log('1');
                    form_input_formColorEdging.find(\"option[value='Другой']\").prop('selected', true);
                    form_input_formOtherColorEdging.val('RAL 9006');
                } else {
                    console.log('2');
                    //смотрим выбранный внешний цвет панели
                    if (container_Colout_n == '3005'.toLowerCase()) {
                        form_input_formColorEdging.find(\"option[value='RAL 3005']\").prop('selected', true);
                        form_input_formColorPost.find(\"option[value='RAL 3005']\").prop('selected', true);
                    } else if (container_Colout_n == '5005'.toLowerCase()) {
                        form_input_formColorEdging.find(\"option[value='RAL 5005']\").prop('selected', true);
                        form_input_formColorPost.find(\"option[value='RAL 5005']\").prop('selected', true);
                    } else if (container_Colout_n == '6005'.toLowerCase()) {
                        form_input_formColorEdging.find(\"option[value='RAL 6005']\").prop('selected', true);
                        form_input_formColorPost.find(\"option[value='RAL 6005']\").prop('selected', true);
                    } else if (container_Colout_n == '8014'.toLowerCase()) {
                        form_input_formColorEdging.find(\"option[value='RAL 8014']\").prop('selected', true);
                        form_input_formColorPost.find(\"option[value='RAL 8014']\").prop('selected', true);
                    } else if (container_Colout_n == '9003'.toLowerCase()) {
                        form_input_formColorEdging.find(\"option[value='RAL 9003']\").prop('selected', true);
                        form_input_formColorPost.find(\"option[value='RAL 9003']\").prop('selected', true);
                    } else {
                        form_input_formColorEdging.find(\"option[value='Другой']\").prop('selected', true);
                        form_input_formColorPost.find(\"option[value='Другой']\").prop('selected', true);
                    }

                    if (container_Colout_n != 0 ) {
                        jQuery('#" . __CLASS__ . "_formOtherColorEdging').val('RAL ' + container_Colout_n);
                        jQuery('#" . __CLASS__ . "_formOtherColorPost').val('RAL ' + container_Colout_n);
                    } else {
                        jQuery('#" . __CLASS__ . "_formOtherColorEdging').val(container_Colout);
                        jQuery('#" . __CLASS__ . "_formOtherColorPost').val(container_Colout);
                    }
                }
            ";*/
        }

        // Инициализация формы
        if (!$this->isFormVisited()) {
            $query .= "
                if (!container_RegionMoscow) {
                    console.log('11');
                    form_input_formColorEdging.find(\"option[value='Другой']\").prop('selected', true);
                    form_input_formOtherColorEdging.val('RAL 9006');
                } else {
                    if (container_Colout_n == '3005'.toLowerCase()) {
                        form_input_formColorEdging.find(\"option[value='RAL 3005']\").prop('selected', true);
                        form_input_formColorPost.find(\"option[value='RAL 3005']\").prop('selected', true);
                    } else if (container_Colout_n == '5005'.toLowerCase()) {
                        form_input_formColorEdging.find(\"option[value='RAL 5005']\").prop('selected', true);
                        form_input_formColorPost.find(\"option[value='RAL 5005']\").prop('selected', true);
                    } else if (container_Colout_n == '6005'.toLowerCase()) {
                        form_input_formColorEdging.find(\"option[value='RAL 6005']\").prop('selected', true);
                        form_input_formColorPost.find(\"option[value='RAL 6005']\").prop('selected', true);
                    } else if (container_Colout_n == '8014'.toLowerCase()) {
                        form_input_formColorEdging.find(\"option[value='RAL 8014']\").prop('selected', true);
                        form_input_formColorPost.find(\"option[value='RAL 8014']\").prop('selected', true);
                    } else if (container_Colout_n == '9003'.toLowerCase()) {
                        form_input_formColorEdging.find(\"option[value='RAL 9003']\").prop('selected', true);
                        form_input_formColorPost.find(\"option[value='RAL 9003']\").prop('selected', true);
                    } else {
                        form_input_formColorEdging.find(\"option[value='Другой']\").prop('selected', true);
                        form_input_formColorPost.find(\"option[value='Другой']\").prop('selected', true);
                    }

                    if (container_Colout_n != 0 ) {
                        jQuery('#" . __CLASS__ . "_formOtherColorEdging').val('RAL ' + container_Colout_n);
                        jQuery('#" . __CLASS__ . "_formOtherColorPost').val('RAL ' + container_Colout_n);
                    } else {
                        jQuery('#" . __CLASS__ . "_formOtherColorEdging').val(container_Colout);
                        jQuery('#" . __CLASS__ . "_formOtherColorPost').val(container_Colout);
                    }
                }
            ";
        }

        // Условие видимости
        $query .= "
            if (container_TypeF != 'Панорамная панель'.toLowerCase()) {
                form_li_formOtherColorEdging.show();
                form_li_formColorEdging.show();
            }

            if (container_TypeF == 'Алюминиевый'.toLowerCase()) {
                if (
                        container_Index == 'DHPF020'.toLowerCase() || container_Index == 'DHPF021'.toLowerCase() || container_Index == 'DHPF022'.toLowerCase() || container_Index == 'DHPF023'.toLowerCase() || container_Index == 'DHPF024'.toLowerCase() ||
                        container_Index == 'DHPF030'.toLowerCase() || container_Index == 'DHPF031'.toLowerCase() || container_Index == 'DHPF032'.toLowerCase() || container_Index == 'DHPF033'.toLowerCase() || container_Index == 'DHPF034'.toLowerCase() ||
                        container_Index == 'DHPF040'.toLowerCase() || container_Index == 'DHPF041'.toLowerCase() || container_Index == 'DHPF042'.toLowerCase() || container_Index == 'DHPF043'.toLowerCase() || container_Index == 'DHPF044'.toLowerCase() ||
                        container_Index == 'DHPF050'.toLowerCase() || container_Index == 'DHPF051'.toLowerCase() || container_Index == 'DHPF052'.toLowerCase() || container_Index == 'DHPF053'.toLowerCase() || container_Index == 'DHPF054'.toLowerCase() ||
                        container_Index == 'DHPF060'.toLowerCase() || container_Index == 'DHPF061'.toLowerCase() || container_Index == 'DHPF062'.toLowerCase() || container_Index == 'DHPF063'.toLowerCase() || container_Index == 'DHPF064'.toLowerCase() ||
                        container_Index == 'DHPF070'.toLowerCase() || container_Index == 'DHPF071'.toLowerCase() || container_Index == 'DHPF072'.toLowerCase() || container_Index == 'DHPF073'.toLowerCase() || container_Index == 'DHPF074'.toLowerCase() ||
                        container_Index == 'DHPF100'.toLowerCase() || container_Index == 'DHPF101'.toLowerCase() || container_Index == 'DHPF102'.toLowerCase() || container_Index == 'DHPF103'.toLowerCase() || container_Index == 'DHPF104'.toLowerCase() || container_Index == 'DHPF105'.toLowerCase() || container_Index == 'DHPF106'.toLowerCase() ||
                        container_Index == 'DHPF110'.toLowerCase() || container_Index == 'DHPF111'.toLowerCase() || container_Index == 'DHPF112'.toLowerCase() || container_Index == 'DHPF113'.toLowerCase() ||
                        container_Index == 'DHPF120'.toLowerCase() || container_Index == 'DHPF121'.toLowerCase() || container_Index == 'DHPF122'.toLowerCase() || container_Index == 'DHPF123'.toLowerCase() || container_Index == 'DHPF124'.toLowerCase() ||
                        container_Index == 'DHPF130'.toLowerCase() || container_Index == 'DHPF131'.toLowerCase() || container_Index == 'DHPF132'.toLowerCase() || container_Index == 'DHPF133'.toLowerCase() || container_Index == 'DHPF134'.toLowerCase() ||
                        container_Index == 'DHPF140'.toLowerCase() || container_Index == 'DHPF141'.toLowerCase() || container_Index == 'DHPF142'.toLowerCase() || container_Index == 'DHPF143'.toLowerCase() || container_Index == 'DHPF144'.toLowerCase() ||
                        container_Index == 'DHPF150'.toLowerCase() || container_Index == 'DHPF151'.toLowerCase() || container_Index == 'DHPF152'.toLowerCase() || container_Index == 'DHPF153'.toLowerCase() || container_Index == 'DHPF154'.toLowerCase() ||
                        container_Index == 'DHPF160'.toLowerCase() || container_Index == 'DHPF161'.toLowerCase() || container_Index == 'DHPF162'.toLowerCase() || container_Index == 'DHPF163'.toLowerCase() || container_Index == 'DHPF164'.toLowerCase() ||
                        container_Index == 'DHPF170'.toLowerCase() || container_Index == 'DHPF171'.toLowerCase() || container_Index == 'DHPF172'.toLowerCase() || container_Index == 'DHPF173'.toLowerCase() || container_Index == 'DHPF180'.toLowerCase() ||
                        container_Index == 'DHPF200'.toLowerCase() || container_Index == 'DHPF201'.toLowerCase() || container_Index == 'DHPF210'.toLowerCase() || container_Index == 'DHPF211'.toLowerCase() || container_Index == 'DHPF212'.toLowerCase() || container_Index == 'DHPF213'.toLowerCase() ||
                        container_Index == 'DHPF220'.toLowerCase() || container_Index == 'DHPF221'.toLowerCase() || container_Index == 'DHPF222'.toLowerCase() || container_Index == 'DHPF223'.toLowerCase() || container_Index == 'DHPF224'.toLowerCase() ||
                        container_Index == 'DHPF230'.toLowerCase() || container_Index == 'DHPF231'.toLowerCase() || container_Index == 'DHPF232'.toLowerCase() || container_Index == 'DHPF233'.toLowerCase() || container_Index == 'DHPF234'.toLowerCase() ||
                        container_Index == 'DHPF240'.toLowerCase() || container_Index == 'DHPF241'.toLowerCase() || container_Index == 'DHPF242'.toLowerCase() || container_Index == 'DHPF243'.toLowerCase() || container_Index == 'DHPF244'.toLowerCase() ||
                        container_Index == 'DHPF250'.toLowerCase() || container_Index == 'DHPF251'.toLowerCase() || container_Index == 'DHPF252'.toLowerCase() || container_Index == 'DHPF253'.toLowerCase() || container_Index == 'DHPF254'.toLowerCase() ||
                        container_Index == 'DHPF260'.toLowerCase() || container_Index == 'DHPF300'.toLowerCase() || container_Index == 'DHPF301'.toLowerCase() ||
                        container_Index == 'DHPF310'.toLowerCase() || container_Index == 'DHPF311'.toLowerCase() || container_Index == 'DHPF312'.toLowerCase() ||
                        container_Index == 'DHPF320'.toLowerCase() || container_Index == 'DHPF321'.toLowerCase() || container_Index == 'DHPF322'.toLowerCase() || container_Index == 'DHPF323'.toLowerCase() || container_Index == 'DHPF324'.toLowerCase() ||
                        container_Index == 'DHPF330'.toLowerCase() || container_Index == 'DHPF331'.toLowerCase() || container_Index == 'DHPF332'.toLowerCase() || container_Index == 'DHPF333'.toLowerCase() || container_Index == 'DHPF334'.toLowerCase() ||
                        container_Index == 'DHPF340'.toLowerCase() || container_Index == 'DHPF341'.toLowerCase() || container_Index == 'DHPF342'.toLowerCase() || container_Index == 'DHPF343'.toLowerCase() || container_Index == 'DHPF344'.toLowerCase()
                ) {
                    if (container_VidS != 'Верх арки'.toLowerCase() && container_VidS != 'Верх волны'.toLowerCase()) {
                        form_li_formColorGrid.show();
                        form_li_formOtherColorGrid.show();
                    } else {
                        form_li_formColorGrid.hide();
                        form_li_formOtherColorGrid.hide();
                    }
                } else {
                    form_li_formColorGrid.hide();
                    form_li_formOtherColorGrid.hide();
                }

                if (
                    container_Index == 'DHPF040'.toLowerCase() || container_Index == 'DHPF041'.toLowerCase() || container_Index == 'DHPF042'.toLowerCase() || container_Index == 'DHPF043'.toLowerCase() || container_Index == 'DHPF044'.toLowerCase() ||
                    container_Index == 'DHPF050'.toLowerCase() || container_Index == 'DHPF051'.toLowerCase() || container_Index == 'DHPF052'.toLowerCase() || container_Index == 'DHPF053'.toLowerCase() || container_Index == 'DHPF054'.toLowerCase() ||
                    container_Index == 'DHPF060'.toLowerCase() || container_Index == 'DHPF061'.toLowerCase() || container_Index == 'DHPF062'.toLowerCase() || container_Index == 'DHPF063'.toLowerCase() || container_Index == 'DHPF064'.toLowerCase() ||
                    container_Index == 'DHPF070'.toLowerCase() || container_Index == 'DHPF071'.toLowerCase() || container_Index == 'DHPF072'.toLowerCase() || container_Index == 'DHPF073'.toLowerCase() || container_Index == 'DHPF074'.toLowerCase() ||
                    container_Index == 'DHPF140'.toLowerCase() || container_Index == 'DHPF141'.toLowerCase() || container_Index == 'DHPF142'.toLowerCase() || container_Index == 'DHPF143'.toLowerCase() || container_Index == 'DHPF144'.toLowerCase() ||
                    container_Index == 'DHPF150'.toLowerCase() || container_Index == 'DHPF151'.toLowerCase() || container_Index == 'DHPF152'.toLowerCase() || container_Index == 'DHPF153'.toLowerCase() || container_Index == 'DHPF154'.toLowerCase() ||
                    container_Index == 'DHPF160'.toLowerCase() || container_Index == 'DHPF161'.toLowerCase() || container_Index == 'DHPF162'.toLowerCase() || container_Index == 'DHPF163'.toLowerCase() || container_Index == 'DHPF164'.toLowerCase() ||
                    container_Index == 'DHPF170'.toLowerCase() || container_Index == 'DHPF171'.toLowerCase() || container_Index == 'DHPF172'.toLowerCase() || container_Index == 'DHPF173'.toLowerCase() || container_Index == 'DHPF180'.toLowerCase() ||
                    container_Index == 'DHPF240'.toLowerCase() || container_Index == 'DHPF241'.toLowerCase() || container_Index == 'DHPF242'.toLowerCase() || container_Index == 'DHPF243'.toLowerCase() || container_Index == 'DHPF244'.toLowerCase() ||
                    container_Index == 'DHPF250'.toLowerCase() || container_Index == 'DHPF251'.toLowerCase() || container_Index == 'DHPF252'.toLowerCase() || container_Index == 'DHPF253'.toLowerCase() || container_Index == 'DHPF254'.toLowerCase() || container_Index == 'DHPF260'.toLowerCase() ||
                    container_Index == 'DHPF340'.toLowerCase() || container_Index == 'DHPF341'.toLowerCase() || container_Index == 'DHPF342'.toLowerCase() || container_Index == 'DHPF343'.toLowerCase() || container_Index == 'DHPF344'.toLowerCase() ||
                    container_Index == 'DHPF440'.toLowerCase() || container_Index == 'DHPF450'.toLowerCase() || container_Index == 'DHPF540'.toLowerCase()
                ) {
                    form_li_formColorTips.show();
                    form_li_formOtherColorTips.show();
                } else {
                    form_li_formColorTips.hide();
                    form_li_formOtherColorTips.hide();
                }

                if (
                    container_Index == 'DHPF060'.toLowerCase() || container_Index == 'DHPF061'.toLowerCase() || container_Index == 'DHPF062'.toLowerCase() || container_Index == 'DHPF063'.toLowerCase() || container_Index == 'DHPF064'.toLowerCase() ||
                    container_Index == 'DHPF070'.toLowerCase() || container_Index == 'DHPF071'.toLowerCase() || container_Index == 'DHPF072'.toLowerCase() || container_Index == 'DHPF073'.toLowerCase() || container_Index == 'DHPF074'.toLowerCase() ||
                    container_Index == 'DHPF150'.toLowerCase() || container_Index == 'DHPF151'.toLowerCase() || container_Index == 'DHPF152'.toLowerCase() || container_Index == 'DHPF153'.toLowerCase() || container_Index == 'DHPF154'.toLowerCase() ||
                    container_Index == 'DHPF160'.toLowerCase() || container_Index == 'DHPF161'.toLowerCase() || container_Index == 'DHPF162'.toLowerCase() || container_Index == 'DHPF163'.toLowerCase() || container_Index == 'DHPF164'.toLowerCase() ||
                    container_Index == 'DHPF170'.toLowerCase() || container_Index == 'DHPF171'.toLowerCase() || container_Index == 'DHPF172'.toLowerCase() || container_Index == 'DHPF173'.toLowerCase() ||
                    container_Index == 'DHPF180'.toLowerCase() || container_Index == 'DHPF450'.toLowerCase()
                ) {
                    form_li_formColorVenzel.show();
                    form_li_formOtherColorVenzel.show();
                } else {
                    form_li_formColorVenzel.hide();
                    form_li_formOtherColorVenzel.hide();
                }
            }
        ";

        //вешаем обработчик, чтобы активировать/деактивировать зависимые элементы
        $query .= "
            function checkColor(){
                if (!container_RegionMoscow) {
                    $('#ColorEdgingMI_formOtherColorEdging').attr('readonly', true);
                    form_input_formColorEdging.prop('disabled', true);
                } else {
                    //Поле для ввода цвета будет активно только когда цвет выбран 'Другой'
                    if (form_input_formColorEdging.val() == 'Другой') {
                        $('#ColorEdgingMI_formOtherColorEdging').attr('readonly', false);
                    } else {
                        $('#ColorEdgingMI_formOtherColorEdging').attr('readonly', true);
                    }
                    if (form_input_formColorPost.val() == 'Другой') {
                        $('#ColorEdgingMI_formOtherColorPost').attr('readonly', false);
                    } else {
                        $('#ColorEdgingMI_formOtherColorPost').attr('readonly', true);
                    }
                    if (form_input_formColorGrid.val() == 'Другой') {
                        $('#ColorEdgingMI_formOtherColorGrid').attr('readonly', false);
                    } else {
                        $('#ColorEdgingMI_formOtherColorGrid').attr('readonly', true);
                    }
                    if (form_input_formColorTips.val() == 'Другой') {
                        $('#ColorEdgingMI_formOtherColorTips').attr('readonly', false);
                    } else {
                        $('#ColorEdgingMI_formOtherColorTips').attr('readonly', true);
                    }
                    if (form_input_formColorVenzel.val() == 'Другой') {
                        $('#ColorEdgingMI_formOtherColorVenzel').attr('readonly', false);
                    } else {
                        $('#ColorEdgingMI_formOtherColorVenzel').attr('readonly', true);
                    }
                }
            }
        ";
        //вешаем обработчик, чтобы активировать/деактивировать зависимые элементы
        $query .= "
            //reailtime обработчик контролов
            form_input_formColorEdging.change(function(){
                if (form_input_formColorEdging.val() != 'Другой') {
                    form_input_formOtherColorEdging.val(form_input_formColorEdging.val());
                }
            });
            //reailtime обработчик контролов
            form_input_formColorPost.change(function(){
                if (form_input_formColorPost.val() != 'Другой') {
                    form_input_formOtherColorPost.val(form_input_formColorPost.val());
                }
            });
            //reailtime обработчик контролов
            form_input_formColorGrid.change(function(){
                if (form_input_formColorGrid.val() != 'Другой') {
                    form_input_formOtherColorGrid.val(form_input_formColorGrid.val());
                }
            });
            //reailtime обработчик контролов
            form_input_formColorTips.change(function(){
                if (form_input_formColorTips.val() != 'Другой') {
                    form_input_formOtherColorTips.val(form_input_formColorTips.val());
                }
            });
            //reailtime обработчик контролов
            form_input_formColorVenzel.change(function(){
                if (form_input_formColorVenzel.val() != 'Другой') {
                    form_input_formOtherColorVenzel.val(form_input_formColorVenzel.val());
                }
            });
            //активируем/деактивируем контролы
            checkColor();
        ";

        $query .= $this->getTooltip('bPrev', '178');
        $query .= $this->getTooltip('bNext', '177');

        // шаг прогрузился
        $query .= "$('#isLoadStep').attr('data-curstep', '". $this->nameModule ."').text('true');";
        $query .= "console.log('". $this->nameModule ." isLoadStep is true');";

        return $query;
    }

}
