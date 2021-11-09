<?php

/**
 * Услуги
 * PHP version 5.5
 * @category Yii
 * @author   Charnou Vitaliy <graffov87@gmail.com>
 */
class ServiceMI extends AbstractModelInterface
{

    /**
     * Название модуля
     * @var string
     */
    public $nameModule = 'ServiceMI';

    /**
     * Список модулей, выполняемых до запуска формы
     * @var array
     */
    public $beforeCalculation = array();

    /**
     * Список модулей, которые выполняются при переходе на следующую форму
     * @var array
     */
    public $moduleCalculation = array(
        'ServiceMC',
        'FormExtraServicesMC'
    );

    /**
     * Алгоритм
     * @return bool
     */
    public function Algorithm()
    {
        return true;
    }

    /**
     * Функция, которая возвращает имя формы
     * @return string
     */
    public function getTitle()
    {
        return Yii::t('steps', 'Услуги');
    }

    /**
     * Очищает список выбраных данных из сессии.
     * @return bool
     */
    public function clearStore()
    {
        Yii::app()->container->setStore(0, "formService");
        Yii::app()->container->setStore(0, "formServices");

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
                svgInfo("Hh","aperture",' . $Height . ');
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
     * @return array правила валидации, которые будут применены во время вызова {@link validate()}.
     */
    public function rules()
    {
        return array();
    }

    /**
     * Метод выполняемый после сохранения формы.
     * В нем можно добавлять/убирать шаги расчета конструкции.
     * @return array
     */
    public function unsetStep()
    {
        $unsetArray = array();
        $unsetArray[] = array(
            0,
            'false'
        );

        return $unsetArray;
    }

    /**
     * Метод определяет наличие следующего шага.
     * @return bool
     */
    function checkNextStep()
    {
        if (count(Yii::app()->container->emptyCodes) > 0) {
            $str = join(",", Yii::app()->container->emptyCodes);

            return Yii::t("steps", "Отсуствуют коды"). " " . $str;
        }

        return true;
    }

    /**
     * Метод отвечает за основную логику формы. В этом методе вешаются обработчики,
     * которые отвечают за отображение/сокрытие, активацию/деактивацию, присвоение
     * значения и т.д., элементам формы
     * @return string
     */
    public function JavascriptExperssion()
    {
        $query = "";
        $query = "var CollectionService = [];";
        $initData = $this->inputVariables['formViewAllService'] ? json_decode($this->inputVariables['formViewAllService'], true) : array();
//        $isFirstVisit = is_array(json_decode($this->inputVariables['formViewAllService'], true)) ? false : true;
        $formVisited = Yii::app()->container->getStore('formExtraVisited');
        Yii::app()->container->getStore(1, 'formExtraVisited');
        //        Yii::app()->container->formViewAllService       = null;
        //        Yii::app()->container->formServiceComplectation = null;
        $this->formService = json_decode(Yii::app()->container->getStore('formService'), true);
        $this->formServices = json_decode(Yii::app()->container->getStore('formServices'), true);
        $productId = Yii::app()->container->productId;
        //        $isFirstVisit      = $this->formService ? false : true;
        if ($this->formService == 0) {
            $elements = ServiceProductModel::model()->findAll(array(
                'condition' => 'product_id = :id1 OR product_id = :id2',
                'params' => array(
                    ':id1' => $productId,
                    ':id2' => ProductModel::ID_ALL_PRODUCT,
                //id для "Все изделия"
                ),
            ));
            $complect = [];
            foreach ($elements As $key => $element) {
                if ($element = $this->getShownElement($element)) {
                    $complect[] = $element;
                }
            }
            $this->formService = $complect;
            Yii::app()->container->setStore(json_encode($this->formService), 'formService');
        }
        if ($this->formServices == 0) {
            $groups = $this->getShowFolder($elements);
            $this->formServices = $groups;
            Yii::app()->container->setStore(json_encode($this->formServices), 'formServices');
        }
        //create init data
        $query .= "var _checkBoxComplectation = '<div class=\"group-delete\"><div class=\"group-settings\">" . CHtml::link(Yii::t('main', 'Редактировать'), '#', array('class' => 'edit_service',)) . "</div>';
            _checkBoxComplectation += '<div class=\"group-del\">" . CHtml::link(Yii::t('main', 'Удалить'), '#', array('class' => 'delete_service',)) . "</div></div>';
            ";

        $version = explode(".", phpversion());
        $query .= " var strFolders = '<ul id=\"trreviews\" class=\"treeview\">';";
        $query .= "strFolders += '<li id=\"0\" class=\"collapsable\"> <div  class=\"hitarea hasChildren-hitarea collapsable-hitarea\"></div><span><a  class=\"treenode selected\" id=\"0\" onclick=\"nomenclatureCalc.viewRoots(rootService, \'elementsService\');$(\'a.selected\').removeClass(\'selected\');$(this).addClass(\'selected\');\" href=\"javascript:void(0);\">/</a></span><ul style=\"display: none;\"><li class=\"last\"><span class=\"placeholder\">&nbsp;</span></li></ul> </li>';";
        foreach ($this->formServices as $key => $value) {
            $title = addslashes($value['title']);
            $query .= "strFolders += '<li id=\"" . $value['code'] . "\"  class=\"hasChildren expandable\"> <div onclick=\"nomenclatureCalc.viewFoldersService(\'" . $value['code'] . "\',\'" . $value['specificationId'] . "\',\' " . $value['cardId'] . "\',\'" . $value['count'] . "\',\'" . $value['folder'] . "\');\" class=\"hitarea hasChildren-hitarea expandable-hitarea\"></div><span><a  class=\"treenode\" id=\"" . $value['code'] . "\" onclick=\"nomenclatureCalc.viewChildrensService(\'" . $value['code'] . "\',\'" . $value['specificationId'] . "\',\' " . $value['cardId'] . "\',\'" . $value['count'] . "\',\'" . $value['folder'] . "\');$(\'a.selected\').removeClass(\'selected\');$(this).addClass(\'selected\');\" href=\"javascript:void(0);\">" . $title . "</a></span><ul style=\"display: none;\"><li class=\"last\"><span class=\"placeholder\">&nbsp;</span></li></ul> </li>';";
        }
        $query .= "strFolders += '</ul>';";
        // create dialog with source drivers directory
        $query .= " var strBuilder = '<div id=\"dialog_service_list\"><div class=\"left-column\">' + strFolders +'</div><div class=\"right-column\"><div class=\"table popup-nomenclature-table-container\" id=\"elementsService\" style=\"position:relative\">';";
        $query .= "var rootService = '<table id=\"list_all_service\" class=\"tbl\"> \
                <thead><tr><th>" . Yii::t('steps', 'Код'). "</th><th>" . Yii::t('steps', 'Артикул'). "</th><th>" . Yii::t('steps', 'Наименование'). "</th><th>" . Yii::t('steps', 'Ед.изм.'). "</th><th>&nbsp;</th></tr></thead> \
                <tbody>';";
        if (count($this->formService) > 0) {
            foreach ($this->formService As $service) {
                $displayStyle = "";
                if (!$formVisited) {
                    if ($service['default'] && !$this->existsInList($service['id'], $initData)) {
                        $initData[] = array(
                            'id' => $service['id'],
                            'code' => $service['code'],
                            'article' => $service['article'],
                            'title' => $service['title'],
                            'count' => 1,
                            'unit' => $service['unit'],
                            'specification_id' => $service['specificationId'],
                            'cards_id' => $service['cardId']
                        );
                        $displayStyle = "style=\"display:none\"";
                    }
                } else {
                    if ($this->existsInList($service['id'], $initData)) {
                        $displayStyle = "style=\"display:none\"";
                    }
                }
                foreach ($initData as $value) {
                    $query .= "CollectionService.push(\"" . $value['code'] . "\");";
                }
                $query .= "rootService += '<tr " . $displayStyle . " data-parent=\"" . $service['id'] . "\"> \
                                                <td class=\"source_service_code\">" . htmlspecialchars($service['code']) . "</td> \
                                                <td class=\"source_service_article\">" . htmlspecialchars($service['article']) . "</td> \
                                                <td class=\"source_service_title\">" . htmlspecialchars($service['title']) . "</td> \
                                                <td class=\"source_service_unit\" data-unit-ru=\"{$service['unit']}\">" . Yii::t('tableHeader', $service['unit']) . "</td> \
                                                <td style=\"display:none;\" class=\"source_service_specification\">" . htmlspecialchars($service['specificationId']) . "</td> \
                                                <td style=\"display:none;\" class=\"source_service_cards\">" . htmlspecialchars($service['cardId']) . "</td> \
                                                <td style=\"display:none;\" class=\"source_service_count\">" . htmlspecialchars($service['count']) . "</td> \
                                                <td style=\"display:none;\" class=\"source_service_folder\">0</td> \
                                                <td>" . CHtml::link(Yii::t('steps', 'Выбрать'), '#', array(
                                                'class' => 'select',
                                                'onclick' => 'clickEventComplectationSelect(this); return false;'
                                                )) . "</td> \
                                            </tr>';";
            }
        }
        $query .= "rootService += '</tbody> \
            </table> ';";
        $query .= "strBuilder += rootService+'</div></div></div>';";
        $query .= "
            $(\"#aperture-form li#" . __CLASS__ . "\").append(strBuilder);
            $(\"#dialog_service_list\").hide();
        ";
        // create dialog for add drive in use list
        $query .= "
            $('#" . __CLASS__ . "_ViewDirectoryService').on('click',function(event) {
                event.preventDefault();

                $(\"#dialog_service_list\").dialog({
                    modal: true,
                    autoOpen: false,
                    title: '". Yii::t('steps', 'Добавить услугу') . "',
                    width: '90%',
                    height: $(window).height()*0.8,
                    resizable: true,
                    close: function( event, ui ) {
                        $(\"#dialog_service_list\").dialog('destroy');
                    }
                });

                $(\"#dialog_service_list\").dialog('open');
            });
        ";
        $query .= "
            var clickEventComplectationSelect = function(select){
//            $('#list_all_service .select').on('click',function(event) {
//                event.preventDefault();
                var row = $(select).closest('tr');

                var code = row.find('.source_service_code').text(),
                article = row.find('.source_service_article').text(),
                title = row.find('.source_service_title').text(),
                type = row.find('.source_service_unit').data('unitRu'),
                specification = row.find('.source_service_specification').text(),
                cards = row.find('.source_service_cards').text(),
                count = row.find('.source_service_count').text()

                var parent_id = row.attr('data-parent');
                CollectionService.push(code);
                var _rowUseComplectation = '<tr data-parent=\"' + parent_id + '\"> \
                                                <td class=\"use_service_code\">' + code + '</td> \
                                                <td class=\"use_service_article\">' + article + '</td> \
                                                <td class=\"use_service_title\">' + title + '</td> \
                                                <td class=\"use_service_count\">' + count + '</td> \
                                                <td style=\"display:none;\" class=\"use_service_unit\">' + type + '</td> \
                                                <td style=\"display:none;\" class=\"use_service_specification\">' + specification + '</td> \
                                                <td style=\"display:none;\" class=\"use_service_cards\">' + cards + '</td> \
                                                <td>' + _checkBoxComplectation + '</td> \
                                            </tr>';
                $('#list_service_elements > tbody').append(_rowUseComplectation);

                if (typeof parent_id != \"undefined\"){
                    var trs = $('#list_all_service tr[data-child=' + parent_id + ']');
                    trs.each (function(index, elem) {
                        var code = $(elem).find('.source_service_code').text(),
                        article = $(elem).find('.source_service_article').text(),
                        title = $(elem).find('.source_service_title').text(),
                        specification = row.find('.source_service_specification').text(),
                        cards = row.find('.source_service_cards').text(),
                        count = row.find('.source_service_count').text();

                        var _rowUseComplectation = '<tr data-child=\"' + parent_id + '\" style=\"display:none;\" > \
                                                        <td class=\"use_service_code\">' + code + '</td> \
                                                        <td class=\"use_service_article\">' + article + '</td> \
                                                        <td class=\"use_service_title\">' + title + '</td> \
                                                        <td class=\"use_service_count\">' + count + '</td> \
                                                        <td style=\"display:none;\" >&nbsp;</td> \
                                                        <td style=\"display:none;\" class=\"use_service_specification\">' + specification + '</td> \
                                                        <td style=\"display:none;\" class=\"use_service_cards\">' + cards + '</td> \
                                                        <td>&nbsp;</td> \
                                                    </tr>';
                        $('#list_service_elements > tbody').append(_rowUseComplectation);
                    });
                }

                // add use data in output array
                createDataListComplectation();

                row.hide();
                $(\"#dialog_service_list\").dialog('close');
            //});
            }
            ";
        // block listComplectationElements - table with use drivers directory
        $query .= "var listComplectationElements = $('<table id=\"list_service_elements\" class=\"tbl\"/>');\n";
        $query .= "var _initComplectationList = '<thead><tr> \
                                        <th>" . Yii::t('steps', 'Код'). "</th> \
                                        <th>" . Yii::t('steps', 'Артикул'). "</th> \
                                        <th>" . Yii::t('steps', 'Наименование'). "</th> \
                                        <th>" . Yii::t('steps', 'Кол-во'). "</th> \
                                        <th style=\"display:none;\" >" . Yii::t('steps', 'Тип'). "</th> \
                                        <th style=\"display:none;\" >" . Yii::t('steps', 'Спецификация'). "</th> \
                                        <th style=\"display:none;\" >" . Yii::t('steps', 'Карты'). "</th> \
                                        <th>" . Yii::t('steps', 'Управл.'). "</th> \
                                    </tr></thead><tbody>';";
        foreach ($initData as $row) {
            if (is_array($row['cards_id'])) {
                $card = join(",", $row['cards_id']);
            } else {
                $card = $row['cards_id'];
            }
            $query .= "_initComplectationList += '<tr data-parent=\"' + {$row['id']} + '\"> \
                                                    <td class=\"use_service_code\">{$row['code']}</td> \
                                                    <td class=\"use_service_article\">{$row['article']}</td> \
                                                    <td class=\"use_service_title\">{$row['title']}</td> \
                                                    <td class=\"use_service_count\">{$row['count']}</td> \
                                                    <td style=\"display:none;\" class=\"use_service_unit\">{$row['unit']}</td> \
                                                    <td style=\"display:none;\" class=\"use_service_specification\">{$row['specification_id']}</td> \
                                                    <td style=\"display:none;\" class=\"use_service_cards\">{$card}</td> \
                                                    <td>' + _checkBoxComplectation + '</td> \
                                                </tr>';
        ";
        }
        $query .= "_initComplectationList += '</tbody>'; ";
        $query .= "listComplectationElements.append( _initComplectationList );\n";
        $query .= "$('#" . __CLASS__ . " ul.clearfix').append(listComplectationElements);\n";
        // create dialog with edit adding driver data
        $query .= " var divComplectationEdit = '<div id=\"dialog_service_edit\"> \
            <div class=\"row\" id=\"tabExampleComplectation\"> \
                <ul> \
                    <li><a href=\"#tab_1\" title=\"tab_1\">" . Yii::t('steps', 'Параметры расчета'). "</a></li> \
                    <!-- li><a href=\"#tab_2\" title=\"tab_2\">" . Yii::t('steps', 'Параметры вывода'). "</a></li //--> \
                </ul> \
                <div id=\"tab_1\"> \
                    <p class=\"tab_1_msg fixCodePos\">" . Yii::t('steps', 'Код'). ":</p> " . CHtml::textField('code', '', array('disabled' => 'disabled')) . " <br/> \
                    <p class=\"tab_1_msg fixArticlePos\">" . Yii::t('steps', 'Артикул'). ":</p> " . CHtml::textField('article', '', array('disabled' => 'disabled')) . " <br/> \
                    <p class=\"tab_1_msg fixNamePos\">" . Yii::t('steps', 'Наименование'). ":</p> " . CHtml::textField('title', '', array('disabled' => 'disabled')) . " <br/> \
                    <p class=\"tab_1_msg fixOnePos\">" . Yii::t('steps', 'Ед.изм.'). ":</p> " . CHtml::textField('type', '', array('disabled' => 'disabled')) . " <br/> \
                    <p class=\"tab_1_msg fixCountPos\">" . Yii::t('steps', 'Количество'). ":</p> " . CHtml::textField('count', 1 /* , array('onkeyup' => 'this.value = this.value.replace(/[^0-9\.]/g, \'\')') */) . " \
                </div> \
                <!-- div id=\"tab_2\"> \
                </div //--> \
            </div> \
            <div class=\"row\"> \
                <button type=\"button\" title=\"" . Yii::t('steps', 'Сохранить'). "\" id=\"save_dialog_service_edit\">" . Yii::t('steps', 'Сохранить'). "</button> \
            </div> \
        </div>';";
        $query .= " $(\"#aperture-form\").parent().append(divComplectationEdit);";
        $cs = Yii::app()->getClientScript();
        $cs->registerScript('#tabExampleComplectation', "jQuery('#tabExampleComplectation').tabs({'collapsible':true});");
        $query .= "
            $(\"#dialog_service_edit\").dialog({
                modal: true,
                autoOpen: false,
                title: '" . Yii::t('steps', 'Редактировать параметры комплектации'). "',
                width: '400',
                height: 'auto',
                resizable: true
            });

            $('#list_service_elements').on('click', '.edit_service', function() {
                var row = $(this).closest('tr');
                var dialogForm = $(\"#dialog_service_edit\");

                dialogForm.find('#code').val(row.find('.use_service_code').text());
                dialogForm.find('#article').val(row.find('.use_service_article').text());
                dialogForm.find('#title').val(row.find('.use_service_title').text());
                dialogForm.find('#count').val(row.find('.use_service_count').text());
                dialogForm.find('#type').val(Yii.t('tableHeader', row.find('.use_service_unit').text()));
                dialogForm.find('#specification').val(row.find('.use_service_specification').text());
                dialogForm.find('#cards').val(row.find('.use_service_cards').text());
                dialogForm.dialog('open');

                //вешаем  маску для вводимых велечин (дробные или целые)
                if (row.find('.use_service_unit').text() == 'п/м' || row.find('.use_complectation_unit').text() == 'м') {
                    $('#dialog_service_edit #count').unbind();
                    $('#dialog_service_edit #count').numberMask({type:'float',afterPoint:4,decimalMark:'.'});
                } else {
                    $('#dialog_service_edit #count').unbind();
                    $('#dialog_service_edit #count').numberMask({pattern:/^\d*$/});
                }

                //delete previous handlers
                dialogForm.unbind('click');

                dialogForm.on('click', '#save_dialog_service_edit', function(event) {
                    event.preventDefault();
                    row.find('.use_service_count').text(dialogForm.find('#count').val());
                    // add use data in output array
                    createDataListComplectation();

                    dialogForm.dialog('close');
                });

                // add use data in output array
                createDataListComplectation();

                return false;
            });

            $('#list_service_elements').on('click', '.delete_service', function() {
                var row = $(this).closest('tr');
                row.remove();
                var parent_id = row.attr('data-parent');
                if (typeof parent_id != \"undefined\"){
                    var trs = $('#list_service_elements tr[data-child=' + parent_id + ']');
                    trs.each (function(index, elem) {
                        elem.remove();
                    });
                }

                $('#list_all_service tr[data-parent=' + parent_id + ']').show();

                // add use data in output array
                createDataListComplectation();

                return false;
            });
        ";
        $query .= "
        getDataList = function(){
            var items = [],
            codes = [];
            $('#list_service_elements tbody tr').each(function(){
                var item = {
                    'code': $(this).find('.use_service_code').text(),
                    'article': $(this).find('.use_service_article').text(),
                    'title': $(this).find('.use_service_title').text(),
                    'unit': $(this).find('.use_service_unit').text(),
                    'count': $(this).find('.use_service_count').text(),
                    'specification_id': $(this).find('.use_service_specification').text(),
                    'cards_id': $(this).find('.use_service_cards').text(),
                    'id': $(this).attr('data-parent')
                };
                codes.push($(this).find('.use_service_code').text());
                items.push(item);
            });

            return {'items': items, 'codes': codes};
        }
        ";
        $query .= "
        createDataListComplectation = function(){
            var dataList = getDataList();
            $('#" . __CLASS__ . "_formServiceComplectation').val(JSON.stringify(dataList.codes));
            $('#" . __CLASS__ . "_formViewAllService').val(JSON.stringify(dataList.items));
        }
        ";
        $query .= "createDataListComplectation();\n";
        $query .= $this->getTooltip('bPrev', '178');
        $query .= $this->getTooltip('bNext', '177');

        // шаг прогрузился
        $query .= "$('#isLoadStep').attr('data-curstep', '". $this->nameModule ."').text('true');";
        $query .= "console.log('". $this->nameModule ." isLoadStep is true');";

        return $query;
    }

    /**
     * Метод проверяет условия хранимые в полях is_valid("Подходит") и default("По умолчанию").
     * В которых хранятся формулы, вычисляемые с помощью метода calculate() класса FormulaHelper.
     * Если вычисление формулы для поля is_valid возвращает число отличное от 0, то метод возвращает
     * массив с характеристиками элемента номенклатыры.
     *
     * @param ServiceProductModel $element Модель услуг
     *
     * @return array Массив атрибутов услуг
     */
    public function getShownElement($element)
    {
        $formula = new FormulaHelper();
        $_fieldTitle = 'title_' . Yii::app()->session['shortLanguage'];
        if (empty($element->service)) {
            return false;
        }
        if (!is_object($element->service->nomenclature)) {
            $codes = Yii::app()->container->emptyCodes;
            if (!in_array($element->service->nomenclature_code, $codes)) {
                $codes[] = $element->service->nomenclature_code;
            }
            Yii::app()->container->emptyCodes = $codes;

            return false;
        }
        $boolsFormula = 0;
        $condition = json_decode($element->service->condition_issuing);
        if (empty($condition[0]->formula)) {
            $boolsFormula = 1;
        }
        if ((!empty($element->service->condition_issuing))) {
            $formula->calculation($element->service->condition_issuing, 1);
        }
        if (!(empty($element->service->condition_issuing)) && (!$boolsFormula) && ($formula->isError() || !$formula->calculation($element->service->condition_issuing, 1))) {
            return false;
        }
        if (!$this->isNeededRegion($element)) {
            return false;
        }
        $boolsFormula2 = 0;
        $condition = json_decode($element->is_valid);
        if (empty($condition[0]->formula)) {
            $boolsFormula2 = 1;
        }
        if ((!empty($element->is_valid))) {
            $formula->calculation($element->is_valid);
        }
        if (isset($element->is_valid) && (!$boolsFormula2) && ($formula->isError() || !$formula->calculation($element->is_valid, 1))) {
            return false;
        }
        if (isset($element->is_valid) && !$formula->calculation($element->is_valid)) {
            return false;
        }
        if ($element->service->nomenclature->have_children) {
            return false;
        }

        return array(
            'id' => $element->element_id,
            'code' => $element->service->nomenclature->code,
            'article' => $element->service->nomenclature->article,
            'title' => $element->service->nomenclature->{$_fieldTitle},
            'count' => 1,
            'unit' => $element->service->nomenclature->unit_nomenclature,
            'specificationId' => $element->specification_id,
            'cardId' => $this->getCardsId($element->cards),
            'default' => $element->default ? $formula->calculation($element->default) : 0
        );
    }

    /**
     * Метод получения папок
     *
     * @param $ids
     *
     * @return array
     */
    private function getShowFolder($ids)
    {
        $array = array();
        $formula = new FormulaHelper();
        $_fieldTitle = 'title_' . Yii::app()->session['shortLanguage'];
        $conditions = array();
        foreach ($ids as $key => $element) {
            if (empty($element->service)) {
                continue;
            }
            if (!is_object($element->service->nomenclature)) {
                $codes = Yii::app()->container->emptyCodes;
                if (!in_array($element->service->nomenclature_code, $codes)) {
                    $codes[] = $element->service->nomenclature_code;
                }
                Yii::app()->container->emptyCodes = $codes;

                continue;
            }
            $boolsFormula = 0;
            $condition = json_decode($element->service->condition_issuing);
            if (empty($condition[0]->formula)) {
                $boolsFormula = 1;
            }
            if ((!empty($element->service->condition_issuing))) {
                $formula->calculation($element->service->condition_issuing, 1);
            }
            if (!(empty($element->service->condition_issuing)) && (!$boolsFormula) && ($formula->isError() || !$formula->calculation($element->service->condition_issuing, 1))) {
                continue;
            }
            if (!$this->isNeededRegion($element)) {
                continue;
            }
            $boolsFormula2 = 0;
            $condition = json_decode($element->is_valid);
            if (empty($condition[0]->formula)) {
                $boolsFormula2 = 1;
            }
            if ((!empty($element->is_valid))) {
                $formula->calculation($element->is_valid);
            }
            if (isset($element->is_valid) && (!$boolsFormula2) && ($formula->isError() || !$formula->calculation($element->is_valid, 1))) {
                continue;
            }
            if (isset($element->is_valid) && !$formula->calculation($element->is_valid)) {
                continue;
            }
            if ($element->service->nomenclature->have_children) {
                $array[] = array(
                    'id' => $element->element_id,
                    'code' => $element->service->nomenclature->code,
                    'article' => $element->service->nomenclature->article,
                    'title' => $element->service->nomenclature->{$_fieldTitle},
                    'count' => 1,
                    'unit' => $element->service->nomenclature->unit_nomenclature,
                    'specificationId' => $element->specification_id,
                    'cardId' => $this->getCardsId($element->cards),
                    'default' => $element->default ? $formula->calculation($element->default) : 0,
                    'folder' => $element->service->nomenclature->code
                );
            }
        }

        return $array;
    }

    /**
     * Метод проверяет регион передаваемого элемента справочника
     *
     * @param NomenclatureAdditionalProductModel $element
     *
     * @return bool
     */
    private function isNeededRegion($element)
    {
        $needed = false;
        if (!$element->service->region) {
            return true;
        }
        if ($element->service->region) {
            if (!is_array($this->regionArray) || !count($this->regionArray)) {
                list($r, $regionArray) = AbstractDirectoryModel::getRegionsName(Yii::app()->container->Region);
                $this->regionArray = $regionArray;
            }
            foreach ($element->service->region as $city) {
                if (in_array($city->title, $this->regionArray)) {
                    $needed = true;
                    break;
                }
            }

            return $needed;
        }
    }

    /**
     * Метод проверяет существует ли переданый $id в массиве $list
     *
     * @param mixed $id
     * @param mixed $list
     *
     * @return bool
     */
    private function existsInList($id, $list)
    {
        foreach ($list as $row) {
            if ($row['id'] == $id) {
                return true;
            }
        }

        return false;
    }

    /**
     * Метод преобразует массив в строку, содержащую все id графических карт
     *
     * @param mixed $cardsList Массив графических карт данного изделия
     *
     * @return string Список id карт через запятую
     */
    private function getCardsId($cardsList)
    {
        $cardsId = array();
        foreach ($cardsList as $row) {
            $cardsId[] = $row->id;
        }

        return implode(',', $cardsId);
    }

}
