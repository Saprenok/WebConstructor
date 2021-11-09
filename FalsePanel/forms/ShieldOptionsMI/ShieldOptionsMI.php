<?php

/**
 * Модуль интерфейса Опции щита
 *
 * PHP version 5.5
 * @category Yii
 */
class ShieldOptionsMI extends AbstractModelInterface
{

     /**
     * Список модулей, выполняемых до запуска формы
     *
     * @var array
     */
    public $beforeCalculation = array();

    /**
     * Название модуля
     *
     * @var string
     */
    public $nameModule = 'ShieldOptionsMI';

    /**
     * Список модулей, которые выполняются при переходе на следующую форму
     * @var array
     */
    public $moduleCalculation = array(
        'FormShieldOptionsMC',
        'ProfileSelectionMC',
        'ShieldMC',
        'AlumShieldMC',
        
    );

    /**
     * Алгоритм
     *
     * @return bool
     */
    public function Algorithm()
    {
        //определение логики работы при первом расчете
        if (Yii::app()->container->isNewProduct == 1 && !$this->isFormVisited()) {
            if (
                Yii::app()->container->TypeF == "Панорамная панель" &&
                Yii::app()->container->Region == 'Москва'
            ) {
                Yii::app()->container->ProfilesWideStrip = 1;
            }
            
            if (
                !Yii::app()->container->ShieldPanelsWithInfill &&
                in_array(
                    Yii::app()->container->Region, 
                    array("Новосибирск")
                )
            ) {
                if (in_array('Инженер Конструктор', Yii::app()->user->roleslist))
                    Yii::app()->container->PanelsNovosib = 1;
            }
            
        }
        return true;
    }

    /**
     * Функция, которая возвращает имя формы
     *
     * @return string
     */
    public function getTitle()
    {
        return Yii::t('steps', 'Опции щита');
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
                svgInfo("Bh",  "aperture", ' . $width  . ');
            ';
            foreach ($this->elementsVariables as $key => $value) {
                if ($value['svg'] == 'true') {
                    $str .= 'svgInfo("' . $key . '","aperture",  document.getElementById("' . __CLASS__ . '_' . $key . '"));
                    
                    ';
                }
            }
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
            $str .= '
                }</script>'
            ;

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
        $unsetArray = array();
        if (Yii::app()->container->TypeF == "Алюминиевый") {
            if (Yii::app()->container->SandPanel == 1) {
                $unsetArray[] = array(
                    1,
                    'TypePanelsColorShieldMI'
                );
            } else {
                $unsetArray[] = array(
                    0,
                    'TypePanelsColorShieldMI'
                );
            };
        } else {
            $unsetArray[] = array(
                1,
                'TypePanelsColorShieldMI'
            );
        }
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
        if (
            !in_array('Инженер Конструктор', Yii::app()->user->roleslist) &&
            $this->PanelsNovosib
        ) {
            return Yii::t('steps', 'Опция <Использовать панели из Новосибирска> находится на стадии разработки!');
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
        $class = __CLASS__;
        $query = "
            var RegionMoscow = parseInt('" . Yii::app()->container->RegionMoscow . "');
            var container_Hh                = parseInt('" . Yii::app()->container->Hh . "');
            var container_Bh                = parseInt('" . Yii::app()->container->Bh . "');
            var container_TypeF             = '" . (string) Yii::app()->container->TypeF . "'.toLowerCase();
            var container_ProfileType       = '" . (string) Yii::app()->container->ProfileType . "'.toLowerCase();
            var container_SandPanel                = parseInt('" . Yii::app()->container->SandPanel . "');
                
            var form_ProfileType = $('#" . __CLASS__ . "_ProfileType');
            $('#" . __CLASS__ . "_ProfileType option:first').remove();

            var form_naklon      = $('#" . __CLASS__ . "_naklon');
            $('#" . __CLASS__ . "_naklon option:first').remove();

            //Список элементов

            var form_ShieldOptionsMI_VidS = $('#" . __CLASS__ . "_VidS');
            $('#" . __CLASS__ . "_VidS option:first').remove();

            var form_ShieldOptionsMI_Index = $('#" . __CLASS__ . "_Index');
            $('#" . __CLASS__ . "_Index option:first').remove();

            var form_ShieldOptionsMI_resh     = $('#" . __CLASS__ . "_resh');
            var form_ShieldOptionsMI_n_resh   = $('#" . __CLASS__ . "_n_resh');
            var form_ShieldOptionsMI_piki     = $('#" . __CLASS__ . "_piki');
            var form_ShieldOptionsMI_piki_min = $('#" . __CLASS__ . "_piki_min');

            //переменные которые могут быть скрыты
            var form_li_ShieldOptionsMI_resh     = $('#li_resh');
            form_li_ShieldOptionsMI_resh.hide();
            var form_li_ShieldOptionsMI_piki     = $('#li_piki');
            form_li_ShieldOptionsMI_piki.hide();
            var form_li_ShieldOptionsMI_n_resh   = $('#li_n_resh');
            form_li_ShieldOptionsMI_n_resh.hide();
            var form_li_ShieldOptionsMI_piki_min = $('#li_piki_min');
            form_li_ShieldOptionsMI_piki_min.hide();
            var form_li_ShieldOptionsMI_naklon   = $('#li_naklon');
            form_li_ShieldOptionsMI_naklon.hide();

            var form_li_ProfileType = $('#li_ProfileType');
            
            var form_li_VidS        = $('#li_VidS');
            form_li_VidS.hide();
            
            var form_li_Index       = $('#li_Index');
            form_li_Index.hide();
            
            var form_li_ProfilesWideStrip = $('#li_ProfilesWideStrip');
            form_li_ProfilesWideStrip.show();
            var form_ProfilesWideStrip = $('#" . __CLASS__ . "_ProfilesWideStrip');
            if (container_TypeF != 'Панорамная панель'.toLowerCase()) {
                form_ProfilesWideStrip.prop('checked', false);
                form_li_ProfilesWideStrip.hide();
            }
            
            if  (container_TypeF == 'Для секционных ворот'.toLowerCase()) {
                $('#li_PanelsNovosib').show();
            } else {
                $('#li_PanelsNovosib').hide();
                $('#" . __CLASS__ . "_PanelsNovosib').prop('checked', false);
            }
            
            $('#li_PanelsNovosib').show();
            if (RegionMoscow) {
                $('#li_PanelsNovosib').hide();
            }
        ";
        $Controls = array('TurnOverShveller','UseEdging');

        foreach($Controls as $Control) {
            $query .= "
                var form_{$Control} = $('#" . __CLASS__ . "_{$Control}');
                var form_yt{$Control} = $('#yt" . __CLASS__ . "_{$Control}');
                var form_li_{$Control} = $('#li_{$Control}');
                form_{$Control}.change(function(){
                    set{$Control}();
                });
                function set{$Control}() {
                    if (form_{$Control}.prop('checked')) {
                        form_yt{$Control}.val(1);
                    } else {
                        form_yt{$Control}.val(0);
                    }
                }
                set{$Control}();
            ";
        }

        $query .= "
            if (container_TypeF != 'Панорамная панель'.toLowerCase()) {
                form_li_ProfileType.hide();
            }
            if (container_ProfileType == 0 && container_TypeF == 'Панорамная панель'.toLowerCase()) {
                form_ProfileType.val('Со штапиками');
            }
        ";

        //Инициализация конструкции
        if (Yii::app()->container->isNewProduct == 1 && !$this->isFormVisited()) {
            $query .= "
                form_TurnOverShveller.prop('checked', false);

                form_UseEdging.prop('checked', true);
            ";
        }
        //условие активности
        $query .= "
            if (container_TypeF == 'Для секционных ворот'.toLowerCase()) {
                form_TurnOverShveller.prop('disabled', true);
            } else {
                form_TurnOverShveller.prop('disabled', true);
            }
            if (container_TypeF == 'Для секционных ворот'.toLowerCase() || container_TypeF == 'Алюминиевый'.toLowerCase()) {
                form_UseEdging.prop('disabled', false);
            } else {
                form_UseEdging.prop('disabled', true);
            }
        ";

        //Инициализация формы
        if (!$this->isFormVisited()) {
            $query .="
                /*Дублирование
                if (container_TypeF == 'Для секционных ворот'.toLowerCase()) {
                    form_TurnOverShveller.prop('disabled', true);
                } else {
                    form_TurnOverShveller.prop('disabled', true);
                }
                if (container_TypeF == 'Для секционных ворот'.toLowerCase() || container_TypeF == 'Алюминиевый'.toLowerCase()) {
                    form_UseEdging.prop('disabled', false);
                } else {
                    form_UseEdging.prop('disabled', true);
                }*/
            ";
        }


        foreach($Controls as $Control) {
            $query .= "
                set{$Control}();
            ";
        }

        //Условие активности
        $query .= "
            if (container_TypeF != 'Для секционных ворот'.toLowerCase()) {
                form_TurnOverShveller.prop('disabled', true);
            }
            if (container_TypeF != 'Для секционных ворот'.toLowerCase() && container_TypeF != 'Алюминиевый'.toLowerCase()) {
                form_UseEdging.prop('disabled', true);
            }
        ";

        

        //вешаем обработчик, чтобы активировать/деактивировать зависимые элементы
        $query .= "
            //reailtime обработчик контролов
            form_ShieldOptionsMI_Index.change(function(){
                setOptions();
            });
        ";

        $query .= "
            function setOptions(){
                if (
                    form_ShieldOptionsMI_Index.val() == 'DHPF020' || form_ShieldOptionsMI_Index.val() == 'DHPF021' || form_ShieldOptionsMI_Index.val() == 'DHPF022' || form_ShieldOptionsMI_Index.val() == 'DHPF023' || form_ShieldOptionsMI_Index.val() == 'DHPF024' ||
                    form_ShieldOptionsMI_Index.val() == 'DHPF030' || form_ShieldOptionsMI_Index.val() == 'DHPF031' || form_ShieldOptionsMI_Index.val() == 'DHPF032' || form_ShieldOptionsMI_Index.val() == 'DHPF033' || form_ShieldOptionsMI_Index.val() == 'DHPF034' ||
                    form_ShieldOptionsMI_Index.val() == 'DHPF040' || form_ShieldOptionsMI_Index.val() == 'DHPF041' || form_ShieldOptionsMI_Index.val() == 'DHPF042' || form_ShieldOptionsMI_Index.val() == 'DHPF043' || form_ShieldOptionsMI_Index.val() == 'DHPF044' ||
                    form_ShieldOptionsMI_Index.val() == 'DHPF050' || form_ShieldOptionsMI_Index.val() == 'DHPF051' || form_ShieldOptionsMI_Index.val() == 'DHPF052' || form_ShieldOptionsMI_Index.val() == 'DHPF053' || form_ShieldOptionsMI_Index.val() == 'DHPF054' ||
                    form_ShieldOptionsMI_Index.val() == 'DHPF060' || form_ShieldOptionsMI_Index.val() == 'DHPF061' || form_ShieldOptionsMI_Index.val() == 'DHPF062' || form_ShieldOptionsMI_Index.val() == 'DHPF063' || form_ShieldOptionsMI_Index.val() == 'DHPF064' ||
                    form_ShieldOptionsMI_Index.val() == 'DHPF070' || form_ShieldOptionsMI_Index.val() == 'DHPF071' || form_ShieldOptionsMI_Index.val() == 'DHPF072' || form_ShieldOptionsMI_Index.val() == 'DHPF073' || form_ShieldOptionsMI_Index.val() == 'DHPF074' ||
                    form_ShieldOptionsMI_Index.val() == 'DHPF100' || form_ShieldOptionsMI_Index.val() == 'DHPF101' || form_ShieldOptionsMI_Index.val() == 'DHPF102' || form_ShieldOptionsMI_Index.val() == 'DHPF103' || form_ShieldOptionsMI_Index.val() == 'DHPF104' || form_ShieldOptionsMI_Index.val() == 'DHPF105' || form_ShieldOptionsMI_Index.val() == 'DHPF106' ||
                    form_ShieldOptionsMI_Index.val() == 'DHPF110' || form_ShieldOptionsMI_Index.val() == 'DHPF111' || form_ShieldOptionsMI_Index.val() == 'DHPF112' || form_ShieldOptionsMI_Index.val() == 'DHPF113' ||
                    form_ShieldOptionsMI_Index.val() == 'DHPF120' || form_ShieldOptionsMI_Index.val() == 'DHPF121' || form_ShieldOptionsMI_Index.val() == 'DHPF122' || form_ShieldOptionsMI_Index.val() == 'DHPF123' || form_ShieldOptionsMI_Index.val() == 'DHPF124' ||
                    form_ShieldOptionsMI_Index.val() == 'DHPF130' || form_ShieldOptionsMI_Index.val() == 'DHPF131' || form_ShieldOptionsMI_Index.val() == 'DHPF132' || form_ShieldOptionsMI_Index.val() == 'DHPF133' || form_ShieldOptionsMI_Index.val() == 'DHPF134' ||
                    form_ShieldOptionsMI_Index.val() == 'DHPF140' || form_ShieldOptionsMI_Index.val() == 'DHPF141' || form_ShieldOptionsMI_Index.val() == 'DHPF142' || form_ShieldOptionsMI_Index.val() == 'DHPF143' || form_ShieldOptionsMI_Index.val() == 'DHPF144' ||
                    form_ShieldOptionsMI_Index.val() == 'DHPF150' || form_ShieldOptionsMI_Index.val() == 'DHPF151' || form_ShieldOptionsMI_Index.val() == 'DHPF152' || form_ShieldOptionsMI_Index.val() == 'DHPF153' || form_ShieldOptionsMI_Index.val() == 'DHPF154' ||
                    form_ShieldOptionsMI_Index.val() == 'DHPF160' || form_ShieldOptionsMI_Index.val() == 'DHPF161' || form_ShieldOptionsMI_Index.val() == 'DHPF162' || form_ShieldOptionsMI_Index.val() == 'DHPF163' || form_ShieldOptionsMI_Index.val() == 'DHPF164' ||
                    form_ShieldOptionsMI_Index.val() == 'DHPF170' || form_ShieldOptionsMI_Index.val() == 'DHPF171' || form_ShieldOptionsMI_Index.val() == 'DHPF172' || form_ShieldOptionsMI_Index.val() == 'DHPF173' || form_ShieldOptionsMI_Index.val() == 'DHPF180' ||
                    form_ShieldOptionsMI_Index.val() == 'DHPF200' || form_ShieldOptionsMI_Index.val() == 'DHPF201' || form_ShieldOptionsMI_Index.val() == 'DHPF210' || form_ShieldOptionsMI_Index.val() == 'DHPF211' || form_ShieldOptionsMI_Index.val() == 'DHPF212' || form_ShieldOptionsMI_Index.val() == 'DHPF213' ||
                    form_ShieldOptionsMI_Index.val() == 'DHPF220' || form_ShieldOptionsMI_Index.val() == 'DHPF221' || form_ShieldOptionsMI_Index.val() == 'DHPF222' || form_ShieldOptionsMI_Index.val() == 'DHPF223' || form_ShieldOptionsMI_Index.val() == 'DHPF224' ||
                    form_ShieldOptionsMI_Index.val() == 'DHPF230' || form_ShieldOptionsMI_Index.val() == 'DHPF231' || form_ShieldOptionsMI_Index.val() == 'DHPF232' || form_ShieldOptionsMI_Index.val() == 'DHPF233' || form_ShieldOptionsMI_Index.val() == 'DHPF234' ||
                    form_ShieldOptionsMI_Index.val() == 'DHPF240' || form_ShieldOptionsMI_Index.val() == 'DHPF241' || form_ShieldOptionsMI_Index.val() == 'DHPF242' || form_ShieldOptionsMI_Index.val() == 'DHPF243' || form_ShieldOptionsMI_Index.val() == 'DHPF244' ||
                    form_ShieldOptionsMI_Index.val() == 'DHPF250' || form_ShieldOptionsMI_Index.val() == 'DHPF251' || form_ShieldOptionsMI_Index.val() == 'DHPF252' || form_ShieldOptionsMI_Index.val() == 'DHPF253' || form_ShieldOptionsMI_Index.val() == 'DHPF254' ||
                    form_ShieldOptionsMI_Index.val() == 'DHPF260' || form_ShieldOptionsMI_Index.val() == 'DHPF300' || form_ShieldOptionsMI_Index.val() == 'DHPF301' ||
                    form_ShieldOptionsMI_Index.val() == 'DHPF310' || form_ShieldOptionsMI_Index.val() == 'DHPF311' || form_ShieldOptionsMI_Index.val() == 'DHPF312' ||
                    form_ShieldOptionsMI_Index.val() == 'DHPF320' || form_ShieldOptionsMI_Index.val() == 'DHPF321' || form_ShieldOptionsMI_Index.val() == 'DHPF322' || form_ShieldOptionsMI_Index.val() == 'DHPF323' || form_ShieldOptionsMI_Index.val() == 'DHPF324' ||
                    form_ShieldOptionsMI_Index.val() == 'DHPF330' || form_ShieldOptionsMI_Index.val() == 'DHPF331' || form_ShieldOptionsMI_Index.val() == 'DHPF332' || form_ShieldOptionsMI_Index.val() == 'DHPF333' || form_ShieldOptionsMI_Index.val() == 'DHPF334' ||
                    form_ShieldOptionsMI_Index.val() == 'DHPF340' || form_ShieldOptionsMI_Index.val() == 'DHPF341' || form_ShieldOptionsMI_Index.val() == 'DHPF342' || form_ShieldOptionsMI_Index.val() == 'DHPF343' || form_ShieldOptionsMI_Index.val() == 'DHPF344'
                ) {
                    if (form_ShieldOptionsMI_VidS.val() != 'Верх арки' && form_ShieldOptionsMI_VidS.val() != 'Верх волны') {
                        form_li_ShieldOptionsMI_resh.show();
                    } else {
                        form_li_ShieldOptionsMI_resh.hide();
                    }
                } else {
                    form_li_ShieldOptionsMI_resh.hide();
                }
                
                if (
                    form_ShieldOptionsMI_Index.val() == 'DHPF040' || form_ShieldOptionsMI_Index.val() == 'DHPF041' || form_ShieldOptionsMI_Index.val() == 'DHPF042' || form_ShieldOptionsMI_Index.val() == 'DHPF043' || form_ShieldOptionsMI_Index.val() == 'DHPF044' ||
                    form_ShieldOptionsMI_Index.val() == 'DHPF050' || form_ShieldOptionsMI_Index.val() == 'DHPF051' || form_ShieldOptionsMI_Index.val() == 'DHPF052' || form_ShieldOptionsMI_Index.val() == 'DHPF053' || form_ShieldOptionsMI_Index.val() == 'DHPF054' ||
                    form_ShieldOptionsMI_Index.val() == 'DHPF060' || form_ShieldOptionsMI_Index.val() == 'DHPF061' || form_ShieldOptionsMI_Index.val() == 'DHPF062' || form_ShieldOptionsMI_Index.val() == 'DHPF063' || form_ShieldOptionsMI_Index.val() == 'DHPF064' ||
                    form_ShieldOptionsMI_Index.val() == 'DHPF070' || form_ShieldOptionsMI_Index.val() == 'DHPF071' || form_ShieldOptionsMI_Index.val() == 'DHPF072' || form_ShieldOptionsMI_Index.val() == 'DHPF073' || form_ShieldOptionsMI_Index.val() == 'DHPF074' ||
                    form_ShieldOptionsMI_Index.val() == 'DHPF140' || form_ShieldOptionsMI_Index.val() == 'DHPF141' || form_ShieldOptionsMI_Index.val() == 'DHPF142' || form_ShieldOptionsMI_Index.val() == 'DHPF143' || form_ShieldOptionsMI_Index.val() == 'DHPF144' ||
                    form_ShieldOptionsMI_Index.val() == 'DHPF150' || form_ShieldOptionsMI_Index.val() == 'DHPF151' || form_ShieldOptionsMI_Index.val() == 'DHPF152' || form_ShieldOptionsMI_Index.val() == 'DHPF153' || form_ShieldOptionsMI_Index.val() == 'DHPF154' ||
                    form_ShieldOptionsMI_Index.val() == 'DHPF160' || form_ShieldOptionsMI_Index.val() == 'DHPF161' || form_ShieldOptionsMI_Index.val() == 'DHPF162' || form_ShieldOptionsMI_Index.val() == 'DHPF163' || form_ShieldOptionsMI_Index.val() == 'DHPF164' ||
                    form_ShieldOptionsMI_Index.val() == 'DHPF170' || form_ShieldOptionsMI_Index.val() == 'DHPF171' || form_ShieldOptionsMI_Index.val() == 'DHPF172' || form_ShieldOptionsMI_Index.val() == 'DHPF173' || form_ShieldOptionsMI_Index.val() == 'DHPF180' ||
                    form_ShieldOptionsMI_Index.val() == 'DHPF240' || form_ShieldOptionsMI_Index.val() == 'DHPF241' || form_ShieldOptionsMI_Index.val() == 'DHPF242' || form_ShieldOptionsMI_Index.val() == 'DHPF243' || form_ShieldOptionsMI_Index.val() == 'DHPF244' ||
                    form_ShieldOptionsMI_Index.val() == 'DHPF250' || form_ShieldOptionsMI_Index.val() == 'DHPF251' || form_ShieldOptionsMI_Index.val() == 'DHPF252' || form_ShieldOptionsMI_Index.val() == 'DHPF253' || form_ShieldOptionsMI_Index.val() == 'DHPF254' || form_ShieldOptionsMI_Index.val() == 'DHPF260' ||
                    form_ShieldOptionsMI_Index.val() == 'DHPF340' || form_ShieldOptionsMI_Index.val() == 'DHPF341' || form_ShieldOptionsMI_Index.val() == 'DHPF342' || form_ShieldOptionsMI_Index.val() == 'DHPF343' || form_ShieldOptionsMI_Index.val() == 'DHPF344' ||
                    form_ShieldOptionsMI_Index.val() == 'DHPF440' || form_ShieldOptionsMI_Index.val() == 'DHPF450' || form_ShieldOptionsMI_Index.val() == 'DHPF540'
                ) {
                    if (form_ShieldOptionsMI_Index.val() == 'DHPF240' || form_ShieldOptionsMI_Index.val() == 'DHPF241' || form_ShieldOptionsMI_Index.val() == 'DHPF242' || form_ShieldOptionsMI_Index.val() == 'DHPF243' || form_ShieldOptionsMI_Index.val() == 'DHPF244') {
                       form_li_ShieldOptionsMI_piki.hide();
                    } else {
                        form_li_ShieldOptionsMI_piki.show();
                    }
                } else {
                    form_li_ShieldOptionsMI_piki.hide();
                }

                if (
                    form_ShieldOptionsMI_Index.val() == 'DHPF160' || form_ShieldOptionsMI_Index.val() == 'DHPF161' || form_ShieldOptionsMI_Index.val() == 'DHPF162' || form_ShieldOptionsMI_Index.val() == 'DHPF163' || form_ShieldOptionsMI_Index.val() == 'DHPF164' ||
                    form_ShieldOptionsMI_Index.val() == 'DHPF170' || form_ShieldOptionsMI_Index.val() == 'DHPF171' || form_ShieldOptionsMI_Index.val() == 'DHPF172' || form_ShieldOptionsMI_Index.val() == 'DHPF173' || form_ShieldOptionsMI_Index.val() == 'DHPF180' ||
                    form_ShieldOptionsMI_Index.val() == 'DHPF220' || form_ShieldOptionsMI_Index.val() == 'DHPF221' || form_ShieldOptionsMI_Index.val() == 'DHPF222' || form_ShieldOptionsMI_Index.val() == 'DHPF223' || form_ShieldOptionsMI_Index.val() == 'DHPF224' ||
                    form_ShieldOptionsMI_Index.val() == 'DHPF230' || form_ShieldOptionsMI_Index.val() == 'DHPF231' || form_ShieldOptionsMI_Index.val() == 'DHPF232' || form_ShieldOptionsMI_Index.val() == 'DHPF233' || form_ShieldOptionsMI_Index.val() == 'DHPF234' ||
                    form_ShieldOptionsMI_Index.val() == 'DHPF240' || form_ShieldOptionsMI_Index.val() == 'DHPF241' || form_ShieldOptionsMI_Index.val() == 'DHPF242' || form_ShieldOptionsMI_Index.val() == 'DHPF243' || form_ShieldOptionsMI_Index.val() == 'DHPF244' ||
                    form_ShieldOptionsMI_Index.val() == 'DHPF250' || form_ShieldOptionsMI_Index.val() == 'DHPF251' || form_ShieldOptionsMI_Index.val() == 'DHPF252' || form_ShieldOptionsMI_Index.val() == 'DHPF253' || form_ShieldOptionsMI_Index.val() == 'DHPF254'
                ) {
                    form_li_ShieldOptionsMI_n_resh.show();
                } else {
                    form_li_ShieldOptionsMI_n_resh.hide();
                }

                if (
                    form_ShieldOptionsMI_Index.val() == 'DHPF050' || form_ShieldOptionsMI_Index.val() == 'DHPF051' || form_ShieldOptionsMI_Index.val() == 'DHPF052' || form_ShieldOptionsMI_Index.val() == 'DHPF053' || form_ShieldOptionsMI_Index.val() == 'DHPF054' ||
                    form_ShieldOptionsMI_Index.val() == 'DHPF070' || form_ShieldOptionsMI_Index.val() == 'DHPF071' || form_ShieldOptionsMI_Index.val() == 'DHPF072' || form_ShieldOptionsMI_Index.val() == 'DHPF073' || form_ShieldOptionsMI_Index.val() == 'DHPF074'
                ) {
                    form_li_ShieldOptionsMI_piki_min.show();
                } else {
                    form_li_ShieldOptionsMI_piki_min.hide();
                }

                if (
                    form_ShieldOptionsMI_Index.val() == 'DHPF004' || form_ShieldOptionsMI_Index.val() == 'DHPF013' || form_ShieldOptionsMI_Index.val() == 'DHPF024' || form_ShieldOptionsMI_Index.val() == 'DHPF034' || form_ShieldOptionsMI_Index.val() == 'DHPF044' || form_ShieldOptionsMI_Index.val() == 'DHPF054' || form_ShieldOptionsMI_Index.val() == 'DHPF064' || form_ShieldOptionsMI_Index.val() == 'DHPF074' ||
                    form_ShieldOptionsMI_Index.val() == 'DHPF104' || form_ShieldOptionsMI_Index.val() == 'DHPF113' || form_ShieldOptionsMI_Index.val() == 'DHPF124' || form_ShieldOptionsMI_Index.val() == 'DHPF134' || form_ShieldOptionsMI_Index.val() == 'DHPF144' || form_ShieldOptionsMI_Index.val() == 'DHPF154' || form_ShieldOptionsMI_Index.val() == 'DHPF164' || form_ShieldOptionsMI_Index.val() == 'DHPF173' ||
                    form_ShieldOptionsMI_Index.val() == 'DHPF201' || form_ShieldOptionsMI_Index.val() == 'DHPF213' || form_ShieldOptionsMI_Index.val() == 'DHPF224' || form_ShieldOptionsMI_Index.val() == 'DHPF234' || form_ShieldOptionsMI_Index.val() == 'DHPF244' || form_ShieldOptionsMI_Index.val() == 'DHPF254' ||
                    form_ShieldOptionsMI_Index.val() == 'DHPF302' || form_ShieldOptionsMI_Index.val() == 'DHPF312' || form_ShieldOptionsMI_Index.val() == 'DHPF324' || form_ShieldOptionsMI_Index.val() == 'DHPF334' || form_ShieldOptionsMI_Index.val() == 'DHPF344'
                ) {
                    form_li_ShieldOptionsMI_naklon.show();
                } else {
                    form_li_ShieldOptionsMI_naklon.hide();
                }
            }
        ";

        //необходимо, чтобы при возврте на форму не сбрасывался выбранный индекс, при типе щита не прямоугольном
        $query .= "
            //Установить необходимый набор индексов щита по типу щита
            setIndex();

            var Index = '" . Yii::app()->container->Index . "';
            if (Index)
                $('#" . __CLASS__ . "_Index [value=\'" . Yii::app()->container->Index . "\']').attr('selected','selected');

        ";

        //вешаем обработчик, чтобы активировать/деактивировать зависимые элементы
        $query .= "
            //reailtime обработчик контрола 'Тип щита'
            form_ShieldOptionsMI_VidS.change(function(){
                //Установить необходимый набор индексов щита по типу щита
                setIndex();
                setOptions();
            });
        ";
        
        $query .= "
            //Устанавливает необходимый набор индексов щита по типу щита
            function setIndex(){
                var AvailableIndex = '';

                if ($('#{$class}_VidS').val() == 'Прямоугольный'){
                    var AvailableIndex = 'DHPF000, DHPF001, DHPF002, DHPF003, DHPF004, DHPF010, DHPF011, DHPF012, DHPF013, DHPF020, DHPF021, DHPF022, DHPF023, DHPF024, DHPF030, DHPF031, DHPF032, DHPF033, DHPF034, DHPF040, DHPF041, DHPF042, DHPF043, DHPF044, DHPF050, DHPF051, DHPF052, DHPF053, DHPF054, DHPF060, DHPF061, DHPF062, DHPF063, DHPF064, DHPF070, DHPF071, DHPF072, DHPF073, DHPF074, из ламелей';
                } else if ($('#{$class}_VidS').val() == 'Арочный'){
                    var AvailableIndex = 'DHPF100, DHPF101, DHPF102, DHPF103, DHPF104, DHPF105, DHPF106, DHPF110, DHPF111, DHPF112, DHPF113, DHPF120, DHPF121, DHPF122, DHPF123, DHPF124, DHPF130, DHPF131, DHPF132, DHPF133, DHPF134, DHPF140, DHPF141, DHPF142, DHPF143, DHPF144, DHPF150, DHPF151, DHPF152, DHPF153, DHPF154, DHPF160, DHPF161, DHPF162, DHPF163, DHPF164, DHPF170, DHPF171, DHPF172, DHPF173, DHPF180';
                } else if ($('#{$class}_VidS').val() == 'Вогнутый'){
                    var AvailableIndex = 'DHPF200, DHPF201, DHPF210, DHPF211, DHPF212, DHPF213, DHPF220, DHPF221, DHPF222, DHPF223, DHPF224, DHPF230, DHPF231, DHPF232, DHPF233, DHPF234, DHPF240, DHPF241, DHPF242, DHPF243, DHPF244, DHPF250, DHPF251, DHPF252, DHPF253, DHPF254, DHPF260';
                } else if ($('#{$class}_VidS').val() == 'Волна'){
                    var AvailableIndex = 'DHPF300, DHPF301, DHPF302, DHPF310, DHPF311, DHPF312, DHPF320, DHPF321, DHPF322, DHPF323, DHPF324, DHPF330, DHPF331, DHPF332, DHPF333, DHPF334, DHPF340, DHPF341, DHPF342, DHPF343, DHPF344';
                } else if ($('#{$class}_VidS').val() == 'Верх арки'){
                    var AvailableIndex = 'DHPF400, DHPF420, DHPF430, DHPF440, DHPF450';
                } else if ($('#{$class}_VidS').val() == 'Верх волны'){
                    var AvailableIndex = 'DHPF500, DHPF520, DHPF530, DHPF540';
                }

                AvailableIndex = AvailableIndex.toUpperCase();
                AvailableIndex = AvailableIndex.split(', ');

                $('#{$class}_Index option').remove();

                for(var i = 0; i < AvailableIndex.length; i++){
                    $('#{$class}_Index').append('<option value=\''+AvailableIndex[i]+'\'>'+AvailableIndex[i]+'</option>');
                }
            }
        ";
                    
        //Условие видимости
        $query .= "
            if (container_TypeF == 'Алюминиевый'.toLowerCase()) {
                form_li_VidS.show();
                form_li_Index.show();
                setOptions();
            }
        ";

        $query .= $this->getTooltip('bPrev', '178');
        $query .= $this->getTooltip('bNext', '177');

        // шаг прогрузился
        $query .= "$('#isLoadStep').attr('data-curstep', '". $this->nameModule ."').text('true');";
        $query .= "console.log('". $this->nameModule ." isLoadStep is true');";

        return $query;
    }

}
