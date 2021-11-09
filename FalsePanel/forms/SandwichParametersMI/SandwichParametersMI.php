<?php

/**
 * Модуль интерфейса Параметры сэндвич панелей
 * 
 * PHP version 5.5
 * @category Yii
 * @author   Charnou Vitaliy <graffov87@gmail.com>
 */
class SandwichParametersMI extends AbstractModelInterface {

    /**
     * Название модуля
     * 
     * @var string
     */
    public $nameModule = 'SandwichParametersMI';

    /**
     * Список модулей, выполняемых до запуска формы
     * 
     * @var array
     */
    public $beforeCalculation = array(
    );

    /**
     * Список модулей, которые выполняются при переходе на следующую форму
     * 
     * @var array
     */
    public $moduleCalculation = array(
    );
    
    public $constants = array(
        'PanSandwichDesign' => array("Стандартная","С широкой центральной полосой"),
        'PanSandwichType' => array("Гладкая","Стукко","Под дерево"),
        'PanSettingType' => array("Кол-во панелей","Высоту из сэндвича"),
        'PanBottomSettingType' => array("Кол-во панелей","Высоту из сэндвича"),
        'titlePanSettingType' => array("Задать сверху","Задать"),
        'titlePanPanelType' => array("Тип панелей сверху","Тип панелей"),
        'titlePanPanelParameter' => array("Количество:","Высота:"),
        'titlePanBottomPanelParameter' => array("Количество:","Высота:"),
    );

    /**
     * Алгоритм
     * 
     * @return bool
     */
    public function Algorithm() {
        return true;
    }

    /**
     * Функция, которая возвращает имя формы
     * 
     * @return string
     */
    public function getTitle() {
        return Yii::t('steps', 'Параметры сэндвич панелей');
    }

    /**
     * Очищает список выбраных данных из сессии.
     * 
     * @return bool
     */
    public function clearStore() {

        return false;
    }

    /**
     * Метод отвечающий за графическое отображение раскроя щита. Возвращает
     * строку, содержащую javascript.
     * 
     * @return string
     */
    public function getSVG() {
        return '';
    }

    /**
     * Правила валидации для элементов формы
     * 
     * @return array правила валидации, которые будут применены во время вызова {@link validate()}.
     */
    public function rules() {
        return array(
            array(
                'PanPanelParameter,PanBottomPanelParameter',
                'required',
                'message' => Yii::t('steps', 'Пустое значение')
            ),
            array(
                'PanPanelParameter,PanBottomPanelParameter',
                'numerical',
                'min'=>1,
                'tooSmall'=>Yii::t('steps', 'Введите значение > 0 в поле <Количество>')
            ),
//            array(
//                'AlFacing,AlFacing2010',
//                'boolean'
//            )
        );
    }

    /**
     * Метод выполняемый после сохранения формы.
     * В нем можно добавлять/убирать шаги расчета конструкции.
     * 
     * @return array
     */
    public function unsetStep() {
        $unsetArray = array();
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
    function checkNextStep() {

        return true;
    }

    /**
     * Метод отвечает за основную логику формы. В этом методе вешаются обработчики,
     * которые отвечают за отображение/сокрытие, активацию/деактивацию, присвоение
     * значения и т.д., элементам формы
     * 
     * @return string
     */
    public function JavascriptExperssion() {
        $query = "";
        $class = __CLASS__;
        $europe = Yii::app()->container->RegionEurope;
        
        //Отобразить форму "Параметры панорамных панелей" (FormPanoramicParameters)
        $query .= '
            // Обьявление переменных, которые могуть быть скрыты
            var form_li_formAllGateFrameAdditional = $("#li_formAllGateFrameAdditional");
            form_li_formAllGateFrameAdditional.hide();
            var form_input_formAllSandwichParameters = $("#' . __CLASS__ . '_formAllSandwichParameters");
            if (typeof elems === "undefined" || typeof inputVariables === "undefined") {
                var elems = \''. Yii::t('steps', 'Ошибка! Элементы формы <<Параметры панорамных панелей>> ненайдены.') .'\';
            }
            //создаем модальное окно
            $("#TypePanelsColorShieldMI").append("<div id=\'modal-'.$class.'\'></div>");
            $("#TypePanelsColorShieldMI #modal-'.$class.'").append(elems).find("option[value=\'\']").remove();
            $("#TypePanelsColorShieldMI #modal-'.$class.'").dialog({
                modal: true,
                resizable: false,
                closeOnEscape: false,
                draggable: false,
                open: function(event, ui) { $(".ui-dialog-titlebar-close").hide(); 
                    if(inputVariables.PanShieldType == "Середина из панорамных"){
                        $("#li_PanBottomSettingType, #li_PanBottomPanelType, #li_PanBottomPanelParameter").show();
                        $("#li_PanBottomSettingType :input, #li_PanBottomPanelType :input, #li_PanBottomPanelParameter :input").prop("disabled", false);
                    } else {
                        $("#li_PanBottomSettingType, #li_PanBottomPanelType, #li_PanBottomPanelParameter").hide();
                        $("#li_PanBottomSettingType :input, #li_PanBottomPanelType :input, #li_PanBottomPanelParameter :input").prop("disabled", true);
                    }
                },
                close: function( event, ui ) {
                },
                buttons: {
                    \''. Yii::t('steps', 'Отправить') .'\': function(){
                        $("#SandwichParametersMI_PanPanelParameter:visible, #SandwichParametersMI_PanBottomPanelParameter:visible").blur();
                        if (
                            (parseInt($("#SandwichParametersMI_PanPanelParameter").val()) >= 1 && !$("#SandwichParametersMI_PanPanelParameter").prop("disabled") && $("#SandwichParametersMI_PanBottomPanelParameter").prop("disabled"))
                        ||
                            (parseInt($("#SandwichParametersMI_PanBottomPanelParameter").val()) >= 1 && $("#SandwichParametersMI_PanPanelParameter").prop("disabled") && !$("#SandwichParametersMI_PanBottomPanelParameter").prop("disabled"))
                        ||
                            (parseInt($("#SandwichParametersMI_PanPanelParameter").val()) >= 1 && parseInt($("#SandwichParametersMI_PanBottomPanelParameter").val()) >= 1 && !$("#SandwichParametersMI_PanPanelParameter").prop("disabled") && !$("#SandwichParametersMI_PanBottomPanelParameter").prop("disabled"))
                        ){
                            $(this).dialog("close");
                            //вызвать алгоритм  А. Обновление размеров калитки и панелей
                            saveForms2();
                            //открыть диалог
                            jQuery("#modal-PanoramicParametersMI").dialog("open");
                        }
                    },
                    \''. Yii::t('steps', 'Отменить') .'\': function(){
                        jQuery("#modal-'.$class.'").dialog("close");
                        jQuery("#modal-PanoramicParametersMI").dialog("open");
                    },
                }
            });
        ';
        
        //Инициализация формы
//        if (!$this->isFormVisited()) {
        $query .= "
            if (parseInt('{$europe}') > 0 || inputVariables.PanWithAntiJamProtection == 1) {
                var PanAvailableColors = 'RAL9010, RAL8014, RAL6005, RAL5005, RAL9006, RAL3000, RAL7016, GOLDEN OAK, MAHAGONY, WENGE, DARK OAK';

            } else {
                var PanAvailableColors = 'RAL9003, RAL8014, RAL8017, RAL3005, RAL6005, RAL5005, RAL1014, RAL9006, RAL7004, RAL3000, RAL7016, ZEBRA, GOLDEN OAK, MAHAGONY, WENGE, ALDER, FOREST WALNUT, НЕСТАНДАРТ';            
            }
            PanAvailableColors = PanAvailableColors.toUpperCase();
            PanAvailableColors = PanAvailableColors.split(', ');
            for(var i = 0; i < PanAvailableColors.length; i++){
                $('#{$class}_PanSandwichColor').append('<option value=\''+PanAvailableColors[i]+'\'>'+PanAvailableColors[i]+'</option>');
            }
        ";
//        if (!$this->isFormVisited()) {
//            //запомним посещение формы
//            $arr = Yii::app()->container->visitedFormsList;
//            $arr[$this->nameModule] = 1;
//            Yii::app()->container->visitedFormsList = $arr;
//        }
        
        //изненение наименований контролов
        $query .= "
            if(inputVariables.PanShieldType == 'Середина из панорамных'){
                $('#li_PanSettingType label').text('{$this->constants['titlePanSettingType'][0]}');
                $('#li_PanPanelType label').text('{$this->constants['titlePanPanelType'][0]}');
            }else{
                $('#li_PanSettingType label').text('{$this->constants['titlePanSettingType'][1]}');
                $('#li_PanPanelType label').text('{$this->constants['titlePanPanelType'][1]}');
            }
            if($('#{$class}_PanSettingType').val() == 'Кол-во панелей'){
                $('#li_PanPanelParameter label').text('{$this->constants['titlePanPanelParameter'][0]}');
            }else{
                $('#li_PanPanelParameter label').text('{$this->constants['titlePanPanelParameter'][1]}');
            }
            if($('#{$class}_PanBottomSettingType').val() == 'Кол-во панелей'){
                $('#li_PanBottomPanelParameter label').text('{$this->constants['titlePanBottomPanelParameter'][0]}');
            }else{
                $('#li_PanBottomPanelParameter label').text('{$this->constants['titlePanBottomPanelParameter'][1]}');
            }
        ";
        
        //Условие видимости
        $query .= "
            if(inputVariables.PanShieldType == 'Середина из панорамных'){
                $('#li_PanBottomSettingType, #li_PanBottomPanelType, #li_PanBottomPanelParameter').show();
                $('#li_PanBottomSettingType :input, #li_PanBottomPanelType :input, #li_PanBottomPanelParameter :input').prop('disabled', false);
            } else {
                $('#li_PanBottomSettingType, #li_PanBottomPanelType, #li_PanBottomPanelParameter').hide();
                $('#li_PanBottomSettingType :input, #li_PanBottomPanelType :input, #li_PanBottomPanelParameter :input').prop('disabled', true);
            }
        ";
//           $('#{$class}_')
//           $('#{$class}_PanGlassingType option[value=\'\']')
//simpleAlertDialog(objDataSimpleAlert, '".json_encode(Yii::app()->container->Loc('690'))."');

        //Изменение выбранного значения
        $query .= "
            //изненение наименований контролов
            $('#{$class}_PanSettingType').change(function(){
                if($('#{$class}_PanSettingType').val() == 'Кол-во панелей'){
                    $('#li_PanPanelParameter label').text('{$this->constants['titlePanPanelParameter'][0]}');
                }else{
                    $('#li_PanPanelParameter label').text('{$this->constants['titlePanPanelParameter'][1]}');
                }
            });
            $('#{$class}_PanBottomSettingType').change(function(){
                if($('#{$class}_PanBottomSettingType').val() == 'Кол-во панелей'){
                    $('#li_PanBottomPanelParameter label').text('{$this->constants['titlePanBottomPanelParameter'][0]}');
                }else{
                    $('#li_PanBottomPanelParameter label').text('{$this->constants['titlePanBottomPanelParameter'][1]}');
                }
            });
            
            //событие элемента PanSettingType
            $('#{$class}_PanSettingType').change(function(){
                PanPanelType = '" . Yii::app()->container->PanPanelType . "';
                //Доступные типы панелей
                if($('#{$class}_PanSettingType').val() == 'Кол-во панелей'){
                    if(inputVariables.PanWithAntiJamProtection == 1){
                        var PanAvailableTypes = '500, 530, 562, 610';
                    }else{
                        if(parseInt('{$europe}') > 0){
                            var PanAvailableTypes = '500, 610';
                        }else{
                            var PanAvailableTypes = '475, 500, 525, 550, 575';
                        }
                    }
                }else{
                    if(inputVariables.PanWithAntiJamProtection == 1){
                        //var PanAvailableTypes = '500, 530, 562, 610, 500+610, 500+562, 530+562, 500+530+562, 500+530+610, 500+530+562+610';
                        var PanAvailableTypes = '500, 530, 562, 610';
                    }else{
                        if(parseInt('{$europe}') > 0){
                            var PanAvailableTypes = '500, 610, 500+610';
                        }else{
                            var PanAvailableTypes = '475, 500, 525, 550, 575, 475+575, 525+550, 525+575, 475+500+575, 500+525+550, 525+550+575, 475+550+575, 475+500+550+575, 475+525+550+575, 475+525+550+575, 475+500+525+550+575';
                        }
                    }
                }
                
                //обновление контрола
                PanAvailableTypes = PanAvailableTypes.split(', ');
                $('#{$class}_PanPanelType option').remove();
                for(var i = 0; i < PanAvailableTypes.length; i++){
                    $('#{$class}_PanPanelType').append('<option value=\''+PanAvailableTypes[i]+'\'>'+PanAvailableTypes[i]+'</option>');
                }
                
                if (PanPanelType) {
                    $('#" . __CLASS__ . "_PanPanelType [value=\'" . Yii::app()->container->PanPanelType . "\']').attr('selected','selected');
                }
            });
            
            $('#{$class}_PanBottomSettingType').change(function(){
                PanBottomPanelType = '" . Yii::app()->container->PanBottomPanelType . "';
                if($('#{$class}_PanBottomSettingType').val() == 'Кол-во панелей'){
                    if(inputVariables.PanWithAntiJamProtection == 1){
                        var PanAvailableBottomTypes = '500, 530, 562, 610';
                    }else{
                        if(parseInt('{$europe}') > 0){
                            var PanAvailableBottomTypes = '500, 610';
                        }else{
                            var PanAvailableBottomTypes = '475, 500, 525, 550, 575';
                        }
                    }
                }else{
                    if(inputVariables.PanWithAntiJamProtection == 1){
                        var PanAvailableBottomTypes = '500, 530, 562, 610, 500+610, 500+562, 530+562, 500+530+562, 500+530+610, 500+530+562+610';
                    }else{
                        if(parseInt('{$europe}') > 0){
                            var PanAvailableBottomTypes = '500, 610, 500+610';
                        }else{
                            var PanAvailableBottomTypes = '475, 500, 525, 550, 575, 475+575, 525+550, 525+575, 475+500+575, 500+525+550, 525+550+575, 475+550+575, 475+500+550+575, 475+525+550+575, 475+525+550+575, 475+500+525+550+575';
                        }
                    }
                }
                
                
                //обновление контрола
                PanAvailableBottomTypes = PanAvailableBottomTypes.split(', ');
                $('#{$class}_PanBottomPanelType option').remove();
                for(var i = 0; i < PanAvailableBottomTypes.length; i++){
                    $('#{$class}_PanBottomPanelType').append('<option value=\''+PanAvailableBottomTypes[i]+'\'>'+PanAvailableBottomTypes[i]+'</option>');
                }
                if (PanBottomPanelType) {
                    $('#" . __CLASS__ . "_PanBottomPanelType [value=\'" . Yii::app()->container->PanBottomPanelType . "\']').attr('selected','selected');
                }
            });
        ";
        //Присвоение значений из контейнера
        if (Yii::app()->container->formAllSandwichParameters) {
            if ((Yii::app()->container->PanSandwichColor != '0') && is_string(Yii::app()->container->PanSandwichColor)) {
                $query .= '$("#' . __CLASS__ . '_PanSandwichColor [value=\"' . Yii::app()->container->PanSandwichColor . '\"]").attr("selected","selected");';
            }
            $PanPanelType = Yii::app()->container->PanPanelType;
            if (!empty($PanPanelType)) {
                $query .= '$("#' . __CLASS__ . '_PanPanelType [value=\"' . Yii::app()->container->PanPanelType . '\"]").attr("selected","selected");';
            }
            $PanBottomPanelType = Yii::app()->container->PanBottomPanelType;
            if (!empty($PanBottomPanelType)) {
                $query .= '$("#' . __CLASS__ . '_PanBottomPanelType [value=\"' . Yii::app()->container->PanBottomPanelType . '\"]").attr("selected","selected");';
            }
        }
        //запустить события элементов 1 раз в момент инициализации формы
        $query .= "
            $('#{$class}_PanSettingType').change();
            $('#{$class}_PanBottomSettingType').change();            
            $('#{$class}_PanSettingType').change();            
            $('#{$class}_PanBottomSettingType').change();
        ";
        
        return $query;
    }
}
