<?php

/**
 * Модуль интерфейса Параметры панорамных панелей
 * 
 * PHP version 5.5
 * @category Yii
 * @author   Charnou Vitaliy <graffov87@gmail.com>
 */
class PanoramicParametersMI extends AbstractModelInterface {

    /**
     * Название модуля
     * 
     * @var string
     */
    public $nameModule = 'PanoramicParametersMI';

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
        return Yii::t('steps', 'Параметры панорамных панелей');
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
                'PanPanelSize',
                'required',
                'message' => Yii::t('steps', 'Пустое значение')
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
        $rememberFomrVisit = '';
        
        //запомним посещение формы
        if (Yii::app()->container->isNewProduct == 1 && !$this->isFormVisited()) {
            $visitedFormsList = Yii::app()->container->visitedFormsList;
            $visitedFormsList[$this->nameModule] = 1;
            $visitedFormsList['SandwichParametersMI'] = 1;
            $visitedFormsList = json_encode(array('PanoramicParametersMI' => array('visitedFormsList' => $visitedFormsList)));
            $url = Yii::app()->createUrl("/steps/default/ajaxdata");
            $rememberFomrVisit = <<<BLOCK
                var visitedFormsList = $.param({$visitedFormsList});
                post = "key=" + $("#key").val() + "&" + visitedFormsList;
                $.ajax({
                    type: "POST",
                    url: "{$url}",
                    data: post,
                    async: false,
                    success: function(data) {
                    }
                });
BLOCK;
        }
        
        //Отобразить форму "Параметры панорамных панелей" (FormPanoramicParameters)
        $query .= '
            
            if (typeof elems === "undefined") {
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
                open: function(event, ui) { 
                    $(".ui-dialog-titlebar-close").hide(); 
                    
                    //Подставить в форму сохраненные значения
                    if (!$.isEmptyObject(containerPanoramic)) {
                        for (var i in containerPanoramic) {
                            $(\'[name="\'+containerPanoramic[i].name+\'"]\').val(containerPanoramic[i].value);
                        }
                        containerPanoramic = {};
                    }
                    if ($("#run-change-event").length == 0) {
                        $("#PanoramicParametersMI_PanShieldType").css("float", "left").css("margin-top", "2px").parent().attr(\'style\', \'width:240px!important\');
                        $("#PanoramicParametersMI_PanShieldType").after("<input type=\'button\' id=\'run-change-event\' style=\'width:20px;height:20px;\' value=\'+\'>");
                        $("#run-change-event").click(function(){$("#PanoramicParametersMI_PanShieldType").change();return false;});
                    }
                },
                close: function( event, ui ) {
                },
                buttons: {
                    \''. Yii::t('steps', 'Отправить') .'\': function(){
                        //Переход к следующей форме
                        var PanWicketInstalled = afterForm();
                        
                        //Проверка ограничений на ширину проема для одного просвета
                        if(parseInt($("#'.$class.'_InitialPanNumberOfGaps").val()) == 1){
                            if(($("#'.$class.'_PanGlassingType").val() == "Сотовое(прозрачное)" && '.Yii::app()->container->Bh.' > 3600) || ($("#'.$class.'_PanGlassingType").val() != "Сотовое(прозрачное)" && '.Yii::app()->container->Bh.' > 3190)){
                            $("#TypePanelsColorShieldMI").append("<div id=\'modal-msg1\'>'.Yii::app()->container->Loc(1572).'</div>");
                            $("#TypePanelsColorShieldMI #modal-msg1").dialog({
                                modal: true,
                                resizable: false,
                                closeOnEscape: false,
                                draggable: false,
                                open: function(event, ui) { $(".ui-dialog-titlebar-close").hide(); },
                                close: function( event, ui ) {
                                },
                                buttons: {
                                    \''. Yii::t('steps', 'Да') .'\': function(){
                                        //сохранение формы
                                        saveForms();
                                        $(this).remove();
                                    },
                                    \''. Yii::t('steps', 'Нет') .'\': function(){
                                        jQuery("#modal-msg1").dialog("close");
                                        jQuery("#modal-'.$class.'").dialog("open");
                                        jQuery("#modal-msg1").dialog("destroy");
                                        jQuery("#modal-msg1").remove();
                                        $(this).remove();
                                    },
                                }
                            });
                            }
                            if($("#'.$class.'_PanGlassingType").val() == "Ударопрочное" && '.Yii::app()->container->Bh.' > 3020){
                            $("#TypePanelsColorShieldMI").append("<div id=\'modal-msg2\'>'.Yii::app()->container->Loc(1572).'<br>'.Yii::app()->container->Loc(1823).' 3230мм!</div>");
                            $("#TypePanelsColorShieldMI #modal-msg2").dialog({
                                modal: true,
                                resizable: false,
                                closeOnEscape: false,
                                draggable: false,
                                open: function(event, ui) { $(".ui-dialog-titlebar-close").hide(); },
                                close: function( event, ui ) {
                                },
                                buttons: {
                                    \''. Yii::t('steps', 'Да') .'\': function(){
                                        //сохранение формы
                                        saveForms();
                                        $(this).remove();
                                    },
                                    \''. Yii::t('steps', 'Нет') .'\': function(){
                                        jQuery("#modal-msg1").dialog("close");
                                        jQuery("#modal-'.$class.'").dialog("open");
                                        jQuery("#modal-msg1").dialog("destroy");
                                        jQuery("#modal-msg1").remove();
                                        $(this).remove();
                                    },
                                }
                            });
                            }
                        }
                        //если форма не удалена, значит значения еще небыли сохранены
                        if($("#modal-'.$class.'").length){
                            //сохранение формы
                            saveForms();
                        }
                        
                        function saveForms(){
                            var modalData = {};
                            //получение данных с формы
                            $("#modal-'.$class.'").find(":input:disabled").prop("disabled", false);
                            
                            var isSuccess = true;
                            function sendAjax() {
                                if($("#'.$class.'_PanShieldType").val() != "Полностью из панорамных"){
                                    var post = $("#modal-'.$class.' form, #modal-SandwichParametersMI form, #aperture-form #TypePanelsColorShieldMI_PanoramicPanel").serialize();
                                    containerPanoramic = $("#modal-'.$class.' form").serializeArray();
                                    containerSandwich = $("#modal-SandwichParametersMI form").serializeArray();
                                }else{
                                    var post = $("#modal-'.$class.' form, #aperture-form #TypePanelsColorShieldMI_PanoramicPanel").serialize();
                                    containerPanoramic = $("#modal-'.$class.' form").serializeArray();
                                }
                                
                                post +="&key="+$("#key").val()+"&calcList=ShieldMC,OptimalPanelsCuttingMC,PanoramicCalculationMC";
                            
                                var jqXHR = jQuery.ajax({
                                    type: "POST",
                                    url:"' . Yii::app()->createUrl("/steps/default/ajaxdata") . '",
                                    data:post,
                                    async: false,
                                    success: function(data) {
                                        var response = JSON.parse(data);
                                        //проверка на модальные окна(если массив errorAlert не пуст - вывести модальное окно)
                                        if (typeof response.errorAlert !== "undefined") {
                                            //выводим диалоговое окно и получаем ответ от пользователя
                                            showDialog(response, false, sendAjax);
                                            
                                            return true;
                                        }
                                        if (typeof response.infoMessage !== "undefined") {
                                            showDialog(response);
                                        }
                                        if (typeof response.errorArray !== "undefined") {
                                            
                                            isSuccess = false;
                                            simpleAlertDialog(objDataSimpleAlert, response.errorArray);
                                            
                                            return true;
                                        }
                                        var heightShield = +response.OptimalPanelsCuttingMC.ShieldRealHeight/* + response.OptimalPanelsCuttingMC.SandTopHeight + response.OptimalPanelsCuttingMC.SandBottomHeight*/;
                                        //var heightShield = +response.ShieldMC.ShieldHeight;
                                        svgInfo("ShieldHeight","aperture",heightShield);
                                        var widthShield = response.ShieldMC.ShieldWidth;
                                        var counts = parseInt(response.OptimalPanelsCuttingMC.ShieldPanelCount) + parseInt(response.OptimalPanelsCuttingMC.SandTopCount) + parseInt(response.OptimalPanelsCuttingMC.SandBottomCount);
                                        
                                        var panelCount = 0;
                                        var temp = 0;
                                        var panels = {};
                                        var SandBottomPanelsCount = 0;
                                        var panelsWhole = {};
                                        for (var i in response.OptimalPanelsCuttingMC.SandTopPanels) {
                                            panelCount++;
                                        }
                                        temp = panelCount;
                                        for (var i in response.OptimalPanelsCuttingMC.SandTopPanels) {
                                            panels[temp] = response.OptimalPanelsCuttingMC.SandTopPanels[i];
                                            panelsWhole[temp] = Number(response.OptimalPanelsCuttingMC.SandWholeTopPanel);
                                            temp--;
                                        }                                        
                                        for (var i in response.OptimalPanelsCuttingMC.ShieldPanels) {
                                            panelCount++;
                                            panels[panelCount] = parseInt(response.OptimalPanelsCuttingMC.ShieldPanels[i]);
                                            panelsWhole[panelCount] = parseInt(response.OptimalPanelsCuttingMC.ShieldPanels[i]);
                                        }
                                        for (var i in response.OptimalPanelsCuttingMC.SandBottomPanels) {
                                            panelCount++;
                                            SandBottomPanelsCount++;
                                        }
                                        temp = panelCount;
                                        //for (var i in response.OptimalPanelsCuttingMC.SandBottomPanels) {
                                        for (i = SandBottomPanelsCount; i>=1; i--) {
                                            temp--;
                                            panels[panelCount] = response.OptimalPanelsCuttingMC.SandBottomPanels[i];
                                            panelsWhole[panelCount] = Number(response.OptimalPanelsCuttingMC.SandWholeBottomPanel);
                                            panelCount++;
                                        }
                                        var panelsCutting = panels;
                                        panels = panelsWhole;
                                        var isCut = [response.OptimalPanelsCuttingMC.SandTopPanelIsCut, response.OptimalPanelsCuttingMC.SandBottomPanelIsCut];
                                        
                                        svg = jQuery("#apertureSVG svg g#Shield");
                                        clearElements(svg,".panels");
                                        createPanels(svg, counts, panels, panelsCutting, heightShield, widthShield, isCut, response.OptimalPanelsCuttingMC.SandTopCount, response.OptimalPanelsCuttingMC.SandBottomCount, response.OptimalPanelsCuttingMC.PanPanelCount, 1);
                                    }
                                });
                            }
                            sendAjax();
                            //удаление форм
                            if (isSuccess) {
                                //запоминание посещения данной формы
                                '.$rememberFomrVisit.'

                                jQuery("#modal-SandwichParametersMI").dialog("destroy");
                                jQuery("#modal-SandwichParametersMI").remove();
                                jQuery("#modal-'.$class.'").dialog("destroy");
                                jQuery("#modal-'.$class.'").remove();
                                
                                //установка цвета
                                if ($("#TypePanelsColorShieldMI_Colout").val() == 0)
                                    $("#TypePanelsColorShieldMI_Colout").val("белый").change();
                                if ($("#TypePanelsColorShieldMI_Colin").val() == 0)
                                    $("#TypePanelsColorShieldMI_Colin").val("белый").change();
                                if ($("#TypePanelsColorShieldMI_Colout_n").val() == 0)
                                    $("#TypePanelsColorShieldMI_Colout_n").val(0);
                                if ($("#TypePanelsColorShieldMI_Colin_n").val() == 0)
                                    $("#TypePanelsColorShieldMI_Colin_n").val(0);
                                
                                //убрать возможность перестанавливать панели
                                if( $("#sortablePanels")) {
                                    $("#sortablePanels").remove();
                                }
                            }
                            //вешаем событие на SVG щита
                            $("#apertureSVG svg g#Shield").die("click");
                            $("#apertureSVG svg g#Shield").live("click",function(){
                                panoramicHandler();
                            });
                        }
                    },
                    \''. Yii::t('steps', 'Отменить') .'\': function(){
                        jQuery("#modal-SandwichParametersMI").dialog("destroy");
                        jQuery("#modal-SandwichParametersMI").remove();
                        jQuery("#modal-'.$class.'").dialog("destroy");
                        jQuery("#modal-'.$class.'").remove();
                        $("#TypePanelsColorShieldMI_PanoramicPanel").prop("checked", false);
                        disableElements();
                    },
                }
            });
        ';
        
        $query .= '
            function afterForm() {
                //Переход к следующей форме
                var PanNumberOfGaps = parseInt($("#'.$class.'_InitialPanNumberOfGaps").val());
                
                //Найти индекс остекления по его типу
                if ($("#'.$class.'_PanGlassingType").val() == "Обычное") {
                    $("#'.$class.'_PanGlassingIndex").val(0);
                } else if ($("#'.$class.'_PanGlassingType").val() == "Ударопрочное") {
                    $("#'.$class.'_PanGlassingIndex").val(1);
                } else if ($("#'.$class.'_PanGlassingType").val() == "Сотовое (прозрачное)") {
                    $("#'.$class.'_PanGlassingIndex").val(2);
                } else if ($("#'.$class.'_PanGlassingType").val() == "Сотовое (прозр. текстур.)") {
                    $("#'.$class.'_PanGlassingIndex").val(3);
                } else if ($("#'.$class.'_PanGlassingType").val() == "Сотовое (матовое)") {
                    $("#'.$class.'_PanGlassingIndex").val(4);
                } else if ($("#'.$class.'_PanGlassingType").val() == "Сотовое (мат. теплоизолир)") {
                    $("#'.$class.'_PanGlassingIndex").val(5);
                } else if ($("#'.$class.'_PanGlassingType").val() == "Одинарный поликарбонат") {
                    $("#'.$class.'_PanGlassingIndex").val(6);
                } else if ($("#'.$class.'_PanGlassingType").val() == "Решетка") {
                    $("#'.$class.'_PanGlassingIndex").val(7);
                }
                
                $("#'.$class.'_PanNumberOfGaps").val(PanNumberOfGaps);
                
                //Не убрала данный кусок программы, так как если убрать, то PanNumberOfGaps всегда 0, позже надо будет разобраться
                var PanWicketLocation = $("#'.$class.'_PanWicketLocation").val();
                if($("#'.$class.'_InitialPanWicketInstalled").val() == "Есть"){
                    var PanWicketInstalled = true;
                    $("#'.$class.'_PanWicketInstalled").val(1);
                }else{
                    var PanWicketInstalled = false;
                    $("#'.$class.'_PanWicketInstalled").val(0);
                }
                
                if($("#'.$class.'_PanComplectationType").val() == "С защитой от защемления"){
                    $("#'.$class.'_PanWithAntiJamProtection").val(1);
                }else{
                    $("#'.$class.'_PanWithAntiJamProtection").val(0);
                } 
                
                return PanWicketInstalled;
            }
        ';

        //Инициализация конструкции
        if (Yii::app()->container->isNewProduct == 1 && !$this->isFormVisited()) {
        $query .= "
            //Количество просветов по умолчанию установим в 1
            //$('#{$class}_InitialPanNumberOfGaps').val(parseInt((".Yii::app()->container->Bh." + 1000) / 1000));
            
            if (parseInt('{$europe}') > 0) {
                $('#{$class}_PanComplectationType option[value=\'С защитой от защемления\']').prop('selected', true);
            }
        ";
        
        //запомним посещение формы
//        $arr = Yii::app()->container->visitedFormsList;
//        $arr[$this->nameModule] = 1;
//        Yii::app()->container->visitedFormsList = $arr;
        }else{
             $query .= "
                //Количество просветов
                $('#{$class}_InitialPanNumberOfGaps').val(parseInt(". $this->PanNumberOfGaps ."));
            ";
        }
//           $('#{$class}_')
//           $('#{$class}_PanGlassingType option[value=\'\']')
//simpleAlertDialog(objDataSimpleAlert, '".json_encode(Yii::app()->container->Loc('690'))."');
//  $('#modal-PanoramicParametersMI').dialog('')
//  $('#modal-SandwichParametersMI').dialog('')
        list($formSandwichParameters, $funcSandwichParameters) = $this->forms('SandwichParametersMI');
        //Изменение выбранного значения
        $query .= "
            function formsEvents(event){
                //3. Изменение выбранного значения
                if (typeof event !== 'undefined' && event.target.id == 'PanoramicParametersMI_PanShieldType') {     //проверка на каком элементе событие
                    //Только если в щите есть сэндвич панели
                    if($('#{$class}_PanShieldType').val() != 'Полностью из панорамных'){
                        if($('#{$class}_PanComplectationType').val() == 'С защитой от защемления'){
                            $('#{$class}_PanWithAntiJamProtection').val(1);
                        }else{
                            $('#{$class}_PanWithAntiJamProtection').val(0);
                        }

                        //Отобразить форму 'Параметры сэндвич панелей' (FormSandwichParameters)
                        if ($('#modal-{$class}').dialog('isOpen')) {
                            $('#modal-{$class}').dialog('close');
                        }
                        
                        //получение данных с формы
                        $('#modal-{$class}').find(':input:disabled').prop('disabled', false);
                        var data = $('#modal-{$class} form').serializeArray();
                        var outputVariables = {};       //тут будем хранить данные введеные на форме
                        for(var i in data){
                            outputVariables[data[i].name.slice(22, -1)] = data[i].value;
                        }

                        if (jQuery('#modal-SandwichParametersMI').length) {
                            jQuery('#modal-SandwichParametersMI').dialog('destroy');
                            jQuery('#modal-SandwichParametersMI').remove();
                        }
                        var functionPanoramicParametersMI = new Function('elems,inputVariables,saveForms2', {$funcSandwichParameters});
                        var elems = {$formSandwichParameters};
                        functionPanoramicParametersMI(elems, outputVariables, saveForms2);
                        
                        return;
                    }
                }
                
                //5. Изменение выбранного значения
                if (typeof event !== 'undefined' && event.target.id == 'PanoramicParametersMI_InitialPanSetPanelSize') {     //проверка на каком элементе событие
                    //Проверка ограничений на высоту панели
                    if($('#{$class}_InitialPanSetPanelSize').val() == 'Да'){
                        if($('#{$class}_PanPanelSize').val() >= 350 && $('#{$class}_PanPanelSize').val() >= 750){
                            if(('{$europe}' > 0 && $('#{$class}_PanPanelSize').val() > 670) || ('{$europe}' == 0 && $('#{$class}_PanPanelSize').val() > 610)){
                                simpleAlertDialog(objDataSimpleAlert, '".json_encode(Yii::app()->container->Loc('690'))."');
                            }
                        }
                    }
                }
            }
        ";
        
        //вешаем сабытия на форму
        $query .= "
            all = $('#modal-{$class} input[id^=\"{$class}_\"], #modal-{$class} select[id^=\"{$class}_\"]');
            all.each (function(index, elem) {
                $(elem).on('change', function(event) {
                    //что выполнять по событию                    
                    formsEvents(event);
                });
            });

            formsEvents();
        ";
        
        //вспомогательные функции
        $query .= "
            //активирует/деактивирует элементы формы 
            function disableElements(){
                var elems = ['typePanel','design','design2','structure','colorOutside','colorInside','typeSize','VariantCutting','PanelCuttingMode','PanelSizeForCutting'];
                var checked = $('#TypePanelsColorShieldMI_PanoramicPanel').prop('checked');

                for (var i in elems) {
                    $('#TypePanelsColorShieldMI_' + elems[i]).prop('disabled', checked);
                }
            }
            
            //аджакс вызов модулей раскроя щита
            //также тут реализован алгоритм - А.Обновление размеров калитки и панелей
            function saveForms2(){
                var result = true;
                //клонирование формы
                if($('#".$class."_PanShieldType').val() != 'Полностью из панорамных'){
                    var originalForm = $('#modal-".$class." form, #modal-SandwichParametersMI form, #aperture-form #TypePanelsColorShieldMI_PanoramicPanel');
                    var clonedForm = $('#modal-".$class." form, #modal-SandwichParametersMI form, #aperture-form #TypePanelsColorShieldMI_PanoramicPanel').clone();
                }else{
                    var originalForm = $('#modal-".$class." form, #aperture-form #TypePanelsColorShieldMI_PanoramicPanel');
                    var clonedForm = $('#modal-".$class." form, #aperture-form #TypePanelsColorShieldMI_PanoramicPanel').clone();
                }
                
                //установка у клона выбраных значений
                clonedForm.find(':input').each(function(i){
                    var selector = '#' + $(this).attr('id');
                    if ($(this).prop('tagName') == 'SELECT') {
                        $(this).val(originalForm.find(selector).find('option:selected').val());
                        //вывод в консоль для проверки
/*                            console.log('Клон ' + selector + ' = ' + $(this).find('option:selected').val() + '\n');
                        console.log('Ориг ' + selector + ' = ' + originalForm.find(selector).find('option:selected').val() + '\n');
                        console.log('--------------------------------------------------');*/
                    } else { 
                        $(this).val(originalForm.find(selector).val());
/*                            console.log('Клон ' + selector + ' = ' + $(this).val() + '\n');
                        console.log('Ориг ' + selector + ' = ' + originalForm.find(selector).val() + '\n');
                        console.log('--------------------------------------------------');*/
                    }
                });
                
                //получение данных с формы
                clonedForm.find(':input:disabled').prop('disabled', false);
                var post = clonedForm.serialize();
                
                post +='&key='+$('#key').val()+'&calcList=ShieldMC,OptimalPanelsCuttingMC,PanoramicCalculationMC';
            
                var jqXHR = jQuery.ajax({
                    type: 'POST',
                    url:'" . Yii::app()->createUrl('/steps/default/ajaxdata') . "',
                    data:post,
                    async: false,
                    success: function(data) {
                        var response = JSON.parse(data);
                        //проверка на модальные окна(если массив errorAlert не пуст - вывести модальное окно)
                        if (typeof response.errorAlert !== 'undefined') {
                            //выводим диалоговое окно и получаем ответ от пользователя
                            showDialog(response, false, saveForms2);
                            
                            return true;
                        }
                        if (typeof response.infoMessage !== 'undefined') {
                            showDialog(response);
                        }
                        if (typeof response.errorArray !== 'undefined') {
                            
                            result = false;
                            simpleAlertDialog(objDataSimpleAlert, response.errorArray);
                            
                            return true;
                        }

                        //Обновляем высоту панели, если она не задается вручную
                        if ($('#PanoramicParametersMI_InitialPanSetPanelSize').val() == 'Нет'){
                            $('#PanoramicParametersMI_PanPanelSize').val(response.OptimalPanelsCuttingMC.PanFinalPanelSize);
                        }                               
                    }
                });
                
                return result;
            }
        ";
        
        return $query;
    }
    
    public function forms($formName)
    {
        $result = '';
        $product = ProductModel::model()->findByPk(Yii::app()->container->productId);
        $dir = $product->dir_product;
        Yii::setPathOfAlias('interface', Yii::app()->basePath . '/../product/' . $dir . '/forms/');
        $model = Yii::app()->container->getForms($formName);
        $model->fill();

        Yii::app()->clientScript->scriptMap = array(
            'jquery.js'=>false,
            'jquery.yiiactiveform.js'=>false,
        );
        $str = Yii::app()->controller->renderPartial('form', array('models'=>$model, 'formName'=>'modal-sandwich-from'), true, true);
        $result1=json_encode($str);
        $result2=json_encode($model->JavascriptExperssion());

        return array($result1,$result2);
    }
}
