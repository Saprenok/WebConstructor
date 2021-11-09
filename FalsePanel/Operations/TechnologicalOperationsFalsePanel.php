<?php

/**
 * Технологические операции
 * @example  здесь текст примера использования класса (необязательно)
 * PHP version 5.5
 * @category Yii
 * @author   Shcherbakov Pavel <pavel24071988@gmail.com>
 */

class TechnologicalOperationsFalsePanel
{
    public $container;
    
    /*
     * Переменные со значениями, изначально.
     */
    private $ShaftCount = 1;
    private $SpringCount = 1;
    private $CProfileCount = 1;
    private $AdjustingProfileCount = 1;
    private $OmegaCount = 1;
    private $ElementCount = 0;
    private $Operations = array();
    private $Elements = array();
    private $SpringsPackExists = false;
    private $ElementName = '';


    /*
     * Служебные переменные общие
     */
    private $productId;
    private $orderProductId;
    private $Region;
    private $RegionMoscow;
    private $RegionEurope;
    private $title_lang;
    private $PanelExists;
    private $ElementsExists;
    private $FalsePanelExists;
    private $nomenclatureElementsByGroups;
    private $nomenclatureElementsBySubGroups;
    private $nomenclatureLinks;
    private $tovGroups;
    private $editedElementsByGroups;

    public function Algorihtm($dataArr)
    {
        $Operations = array();
        
        //Контроль качества
        $Operations[] = array(
            'Code' => "CB0000242",
            'SectionName' => "",
            'Elements' => array()
        );
        
        $this->arrayOfCodesPosition = $dataArr['arrayOfCodesPosition'];
        $preTitleShield = $dataArr['type'] === 'regionpanels' ? 'Упаковка.Нарезка панелей' : 'Упаковка.Щит';
        $this->Operations[] = array(
            'Code' => "CB0000010",
            'SectionName' => "",
            'Elements' => array()
        );
        //  Операция "Производство вместо брака"
        $this->Operations[] = array(
            'Code' => "CB0000150",
            'SectionName' => "",
            'Elements' => array()
        );
        
        $this->productId = Yii::app()->container->productId;
        $this->orderProductId = Yii::app()->container->orderProductId;
        $this->Region = Yii::app()->container->Region;
        $this->RegionEurope = Yii::app()->container->RegionEurope;
        $this->RegionMoscow = Yii::app()->container->RegionMoscow;
        $lang = explode('_', Yii::app()->getLanguage());
        $this->title_lang = 'title_'. $lang[0];
        
        $this->getTitleElements($dataArr);
        $this->getEditedElements();
        $this->nomenclatureLinks = $this->getLinks();
        
        /*
        foreach($this->nomenclatureElementsByGroups as $groups){
            foreach($groups as $element){
                $this->algorithmOfAddingGeneralOperations("Упаковка.Направляющие", $element);
            }
        }
        */
        
        if(!empty($this->nomenclatureElementsByGroups)){
            if(!empty($this->nomenclatureElementsByGroups['Упаковка.Направляющие'])){
                $this->packLeadingTask($this->nomenclatureElementsByGroups['Упаковка.Направляющие']);
            }
            if(!empty($this->nomenclatureElementsByGroups['Упаковка.Омега'])){
                $this->omegaTask($this->nomenclatureElementsByGroups['Упаковка.Омега']);
            }
            if(!empty($this->nomenclatureElementsByGroups['Упаковка.Пружины'])){
                $this->springsTask($this->nomenclatureElementsByGroups['Упаковка.Пружины']);
            }
            if(!empty($this->nomenclatureElementsByGroups['Упаковка.Торсионный механизм'])){
                $this->torsionTask($this->nomenclatureElementsByGroups['Упаковка.Торсионный механизм']);
            }
            
            //  элементы существуют
            if($this->ElementsExists === true){
                $folder_edit = false;
                if($this->SpringsPackExists === true){
                    $sectionName = 'Упаковка.Пружины';
                    $folder = SpecificationModel::model()->find('title="Упаковка.Пружины" AND product_id='. $this->productId);
                    if ($folder) {
                        if (!empty($this->editedElementsByGroups["Упаковка.Пружины"])) {
                            $this->Elements[] = array("ИзмененияВручную" => 1);
                        }
                    }
                }else{
                    $sectionName = 'Упаковка.Торсионный механизм';
                    $folder = SpecificationModel::model()->find('title="Упаковка.Торсионный механизм" AND product_id='. $this->productId);
                    if ($folder) {
                        if (!empty($this->editedElementsByGroups["Упаковка.Торсионный механизм"])) {
                            $this->Elements[] = array("ИзмененияВручную" => 1);
                        }
                    }
                }
                
                if(!empty($this->Elements)){
                    $this->Operations[] = array(
                        'Code' => "CB0000003",
                        'SectionName' => $sectionName,
                        'Elements' => $this->Elements
                    );
                    
                    $Panels = $this->getLink("Панели", "tag", Yii::app()->container->orderProductId);
                    //Число панелей
                    $PanelCount = is_array($Panels) ? count($Panels) : 0;
                    //В списке есть панели
                    if ($PanelCount > 0 && $dataArr['type'] !== 'regiontorsion') {//'allregion'
                        //Операция "Взвешивание щита"
                        $Operations[] = array(
                            'Code' => "CB0000178",
                            'SectionName' => "",
                            'Elements' => array()
                        );
                    }
                }
            }
            //===================//
            
            $groups = array();
            if(isset($this->tovGroups['45']))
                $groups['45'] = $this->tovGroups['45'];
            if(isset($this->tovGroups['138']))
                $groups['138'] = $this->tovGroups['138'];
            if(isset($this->tovGroups['CB0001834']))
                $groups['CB0001834'] = $this->tovGroups['CB0001834'];
            $falsePanels = array();
            if(isset($this->nomenclatureElementsByGroups['Упаковка.Фальшпанель']))
                $falsePanels = $this->nomenclatureElementsByGroups['Упаковка.Фальшпанель'];
            
            /*
             * Смотрим Европа/не Европа
             */
            $this->ElementName  = '';
            $this->ElementCount = 0;
            $this->PanelExists = false;
            $this->ElementsExists = false;
            $this->FalsePanelExists = false;
            $this->Elements = array();
            
            /*
                private $nomenclatureElementsByGroups;
                private $tovGroups;
                private $editedElementsByGroups;
             */
            
            if (!$this->RegionEurope) {
                $this->noEuropeTasks($groups, $falsePanels);
            }else{
                $this->EuropeTasks($groups, $falsePanels);
            }
            
            /*
             * Панели или фальшпанель существует
             */
            
            if($this->ElementsExists === true){
                $this->Elements[] = array("Сборка_КолВоПанелей", $this->ElementCount);
                
                if($this->ElementName !== '' || $this->ElementName !== '-'){
                    $name_ = str_replace("Панель", "", $this->ElementName);
                    $name = str_replace("/", "\\", $name_);
                    $this->Elements[] = array("Сборка_Цвет", $name);
                }
                
                if($this->PanelExists){
                    $packProtect = array();
                    if(isset($this->nomenclatureElementsByGroups['Упаковка.Щит']))
                        $packProtect = $this->nomenclatureElementsByGroups['Упаковка.Щит'];
                    if(!empty($packProtect)){
                        if (!empty($this->editedElementsByGroups["Упаковка.Щит"])) {
                            $this->Elements[] = array("ИзмененияВручную" => 1);
                        }
                    }
                    
                    if(!empty($this->Elements)){
                        $this->Operations[] = array(
                            'Code' => "CB0000002",
                            'SectionName' => "Упаковка.Щит",
                            'Elements' => $this->Elements
                        );
                    }

                    //  Если регион не Европа и ElementCount > 0
                    if(!$this->RegionEurope && $this->ElementCount > 0){
                        //  Операция "Изготовление панелей"
                        $this->Operations[] = array(
                            'Code' => "CB0000025",
                            'SectionName' => "",
                            'Elements' => array()
                        );
                        //  Операция "Складирование панелей"
                        $this->Operations[] = array(
                            'Code' => "CB0000026",
                            'SectionName' => "",
                            'Elements' => array()
                        );
                        //  Операция "Передача панелей"
                        $this->Operations[] = array(
                            'Code' => "CB0000027",
                            'SectionName' => "",
                            'Elements' => array()
                        );
                        //  Операция "Распределение панелей"
                        $this->Operations[] = array(
                            'Code' => "CB0000100",
                            'SectionName' => "",
                            'Elements' => array()
                        );
                        //  Операция "Распил панелей"
                        $this->Operations[] = array(
                            'Code' => "CB0000099",
                            'SectionName' => "",
                            'Elements' => array()
                        );
                    }
                }
            
                //  Была найдена ФальшПанель
                if($this->FalsePanelExists){
                    $this->Elements = array();
                    $packProtect = array();
                    if(!empty($this->nomenclatureElementsByGroups['Упаковка.Щит'])){
                        $packProtect = $this->nomenclatureElementsByGroups['Упаковка.Щит'];
                        if (!empty($this->editedElementsByGroups["Упаковка.Щит"])) {
                            $this->Elements[] = array("ИзмененияВручную" => 1);
                        }
                        if(!empty($this->Elements)){
                            $this->Operations[] = array(
                                'Code' => "CB0000002",
                                'SectionName' => "Упаковка.Фальшпанель",
                                'Elements' => $this->Elements
                            );
                        }
                    }
                    //  Добавить дополнительные операции, если мы их еще не добавляли
                    //  Если не PanelExists и не RegionEurope и ElementCount > 0
                    if(!$this->PanelExists && (!$this->RegionEurope && $this->ElementCount > 0)){
                        //  Операция "Изготовление панелей"
                        $this->Operations[] = array(
                            'Code' => "CB0000025",
                            'SectionName' => "",
                            'Elements' => array()
                        );
                        //  Операция "Складирование панелей"
                        $this->Operations[] = array(
                            'Code' => "CB0000026",
                            'SectionName' => "",
                            'Elements' => array()
                        );
                        //  Операция "Передача панелей"
                        $this->Operations[] = array(
                            'Code' => "CB0000027",
                            'SectionName' => "",
                            'Elements' => array()
                        );
                        //  Операция "Распределение панелей"
                        $this->Operations[] = array(
                            'Code' => "CB0000100",
                            'SectionName' => "",
                            'Elements' => array()
                        );
                        //  Операция "Распил панелей"
                        $this->Operations[] = array(
                            'Code' => "CB0000099",
                            'SectionName' => "",
                            'Elements' => array()
                        );
                    }
                }
            }
            
            /*
             * Если есть разделы, но в них нет элементов.
             */
            if(!$this->ElementsExists || !$this->PanelExists){
                if(!empty($this->nomenclatureElementsByGroups['Упаковка.Щит'])){
                    $this->Elements = array();
                    $this->Elements[] = array("ИзмененияВручную" => 1);
                    $this->Operations[] = array(
                        'Code' => "CB0000002",
                        'SectionName' => "Упаковка.Щит",
                        'Elements' => $this->Elements
                    );
                }
                
                //  Добавляем эту операцию для раздела "Изготовление щита", если он существует
                if(!empty($this->nomenclatureElementsByGroups['Изготовление щита'])){
                    $packProtect = $this->nomenclatureElementsByGroups['Изготовление щита'];
                    $this->Elements = array();
                    $this->Elements[] = array("ИзмененияВручную" => 1);
                    $this->Operations[] = array(
                        'Code' => "CB0000002",
                        'SectionName' => "Изготовление щита",
                        'Elements' => $this->Elements
                    );
                }
            }
            
            /*
             * Проверяем Европу - нужна именно такая последовательность
             */            
            if(!$this->RegionEurope){
                $this->ElementCount = 1;
                if(!empty($this->nomenclatureElementsByGroups['Упаковка.Окантовка'])){
                    $packProtect = $this->nomenclatureElementsByGroups['Упаковка.Окантовка'];
                    $this->ElementCount = count($packProtect);
                    $this->Elements = array();
                    if(!empty($this->editedElementsByGroups['Упаковка.Окантовка'])){
                        $this->Elements[] = array("ИзмененияВручную" => 1);
                    };
                    $FoundAny = false;
                    $FoundedSpecifid = false;
                    
                    foreach($packProtect as $group){
                        foreach($group as $element){
                            if(empty($element->nomenclature)) continue;
                            $this->Elements[] = array("чермет_". $element->nomenclature->article .'/'. $element->length .'/'. $element->count);
                            if ($element->nomenclature_id == 'CB000166587') {
                                $FoundedSpecifid = true;
                            }else{
                                $FoundAny = true;
                            }
                            $this->ElementCount++;
                        }
                    }
                    if($FoundAny === true){
                        $this->Operations[] = array(
                            'Code' => "CB0000013",
                            'SectionName' => "Упаковка.Труба",
                            'Elements' => $this->Elements
                        );
                    }
                    // данная операция добавляется если в спецификации есть белая труба - CB000166587
                    // Щербаков - выключил вообще это условие так как флаг FoundSpecified нигде в изделии не работает и не определяется
                    /*elseif($this->FoundSpecified === true){
                        $this->Operations[] = array(
                            'Code' => "CB0000089",
                            'SectionName' => "Упаковка.Труба",
                            'Elements' => $this->Elements
                        );
                    }*/
                }
            }
            //  Получить массив Element из списка Specification по разделу "Упаковка.Коробка"
            if(!empty($this->nomenclatureElementsByGroups['Упаковка.Коробка'])){
                //$this->ElementCount = count($packProtect);
                $this->Elements = array();
                if(!empty($this->nomenclatureElementsBySubGroups['Упаковка.Коробка/Комплектация для сборки щита'])){
                    $this->Elements[] = array("Страница_1_Комплектация" => "CB0000028");
                }
                if(!empty($this->nomenclatureElementsBySubGroups['Упаковка.Коробка/Метизы для сборки щита'])){
                    $this->Elements[] = array("Страница_1_Комплектация" => "CB0000032");
                }
                if(!empty($this->nomenclatureElementsBySubGroups['Упаковка.Коробка/Метизы'])){
                    $this->Elements[] = array("Страница_1_Комплектация" => "CB0000031");
                }
                if(!empty($this->nomenclatureElementsBySubGroups['Упаковка.Коробка/Комплектация торс. механизма'])){
                    $this->Elements[] = array("Страница_2_Комплектация" => "CB0000029");
                }
                if(!empty($this->nomenclatureElementsBySubGroups['Упаковка.Коробка/Комплектация'])){
                    $this->Elements[] = array("Страница_2_Комплектация" => "CB0000027");
                }
                if(!empty($this->nomenclatureElementsBySubGroups['Упаковка.Коробка/Метизы торс. механизма'])){
                    $this->Elements[] = array("Страница_2_Комплектация" => "CB0000036");
                }
                if(!empty($this->nomenclatureElementsBySubGroups['Упаковка.Коробка/Комплектация для сборки направляющих'])){
                    $this->Elements[] = array("Страница_3_Комплектация" => "CB0000030");
                }
                if(!empty($this->nomenclatureElementsBySubGroups['Упаковка.Коробка/Метизы для сборки направляющих'])){
                    $this->Elements[] = array("Страница_3_Комплектация" => "CB0000033");
                }
                if(!empty($this->nomenclatureElementsBySubGroups['Упаковка.Коробка/Метизы для крепления направляющих'])){
                    $this->Elements[] = array("Страница_3_Комплектация" => "CB0000034");
                }
                if(!empty($this->nomenclatureElementsBySubGroups['Упаковка.Коробка/Метизы для крепления к проему'])){
                    $this->Elements[] = array("Страница_3_Комплектация" => "CB0000035");
                }
                if(!empty($this->nomenclatureElementsByGroups['Упаковка.Коробка'])){
                    if(!empty($this->editedElementsByGroups['Упаковка.Коробка'])){
                        $this->Elements[] = array("ИзмененияВручную" => "1");
                    }
                }
                if($dataArr['type'] !== 'regionguides'){
                    $this->Operations[] = array(
                        'Code' => "CB0000004",
                        'SectionName' => "Упаковка.Коробка",
                        'Elements' => $this->Elements
                    );
                }
            }
            
            if(!$this->RegionEurope){
                $this->Operations[] = array(
                    'Code' => "CB0000028",
                    'SectionName' => "",
                    'Elements' => array()
                );
            }
        }
        
        // если есть региональные панели - то запускаем первый раз, а значит тех операции не нужны
        if($dataArr['type'] === 'regionpanels'){
            $Operations = array();
            //  Операция "Изготовление панелей"
            $Operations[] = array(
                'Code' => "CB0000025",
                'SectionName' => "",
                'Elements' => array()
            );
            //  Операция "Складирование панелей"
            $Operations[] = array(
                'Code' => "CB0000026",
                'SectionName' => "",
                'Elements' => array()
            );
            //  Операция "Передача панелей"
            $Operations[] = array(
                'Code' => "CB0000027",
                'SectionName' => "",
                'Elements' => array()
            );
            //  Операция "Распределение панелей"
            $Operations[] = array(
                'Code' => "CB0000100",
                'SectionName' => "",
                'Elements' => array()
            );
            //  Операция "Распил панелей"
            $Operations[] = array(
                'Code' => "CB0000099",
                'SectionName' => "",
                'Elements' => array()
            );
            if ($this->FalsePanelEnabled) {
                //  Операция "Упаковка панелей"
                $Operations[] = array(
                    'Code' => "CB0000145",
                    'SectionName' => "Упаковка.Фальшпанель",
                    'Elements' => array()
                );
            }
            //  Операция "Заказ сырья"
            $Operations[] = array(
                'Code' => "CB0000010",
                'SectionName' => "",
                'Elements' => $Elements
            );
            //  Операция "Производство вместо брака"
            $Operations[] = array(
                'Code' => "CB0000150",
                'SectionName' => "",
                'Elements' => array()
            );
            //  Операция "Передача на СГП"
            $Operations[] = array(
                'Code' => "CB0000028",
                'SectionName' => "",
                'Elements' => array()
            );
            $Elements = array();
            $Elements[] = array("СборкаПанели_НаЭтикетку" => str_replace('По ', '', str_replace('/', '\\', $this->ShieldColorsAsString)));
            $Elements[] = array("Сборка_КолВоПанелей" => $this->ShieldPanelCount);
            //  Операция "Упаковка панелей"
            $Operations[] = array(
                'Code' => "CB0000145",
                'SectionName' => "Упаковка.Нарезка панелей",
                'Elements' => $Elements
            );
        }

        
        //  Добавление тех.операций от спецификации
        //  Выбираем все элементы спецификации
        $specification_item_technology = Yii::app()->db->createCommand()
                ->select("ops.id, ops.technology_code, ops.nomenclature_id, spec.title")
                ->from("order_product_specification ops")
                ->leftJoin("specification spec", "spec.id = findSpecificationRootId(ops.specification_id)")
                ->where("(ops.is_deleted=0 AND ops.technology_code!='' and ops.technology_code IS NOT NULL) AND (ops.technology_count>0) AND ops.order_product_id=" . $this->orderProductId)
                ->order("ops.id")
                ->queryAll();
        foreach ($specification_item_technology as $sit) {
            /*
             * Щербаков
             * 15.09.2015 - день начала встраивания двух операций к элементу нуменклатуры
             * поэтому парсим technology_code по разделителю ','
             */
            $curOperations = explode(',', $sit['technology_code']);
            foreach($curOperations as $_sit){
                $this->Operations[] = array(
                    "Code" => $_sit,
                    "SectionName" => '',
                    "Elements" => array(),
                    "KeyLinksNomenclature" => $sit['id'],
                );
            }
        }
        
        return $this->Operations;
    }
    
    public function getTitleElements($dataArr)
    {
        $model = new OrderProductSpecificationModel();
        $where = [
            'order_product_id='. $this->container['orderProductId'],
            't.is_deleted=0',
            't.is_can_delete=0'
        ];
        if(!empty($dataArr['notForExportCodes']))
            $where[] = 't.nomenclature_id NOT IN (\''. implode('\', \'', $dataArr['notForExportCodes']) .'\')';

        $dataReader = Yii::app()->db->createCommand()
                ->select('
                    t.*,
                    IF(s_second.title IS NULL, s_first.title, s_second.title) title,
                    IF(s_second.title IS NOT NULL, CONCAT(s_second.title, \'/\', s_first.title), NULL) subtitle,
                    ng.title group_name
                ')
                ->from($model->tableName() . ' t')
                ->leftJoin('specification s_first', '`s_first`.`id`=`t`.`specification_id`')
                ->leftJoin('specification s_second', '`s_first`.`parent_id`=`s_second`.`id`')
                ->leftJoin('nomenclature_group_elements nge', '`t`.`nomenclature_id`=`nge`.`nomenclature_id`')
                ->leftJoin('nomenclature_groups ng', '`nge`.`nomenclature_group_id`=`ng`.`id`')
                ->where(implode(' AND ', $where))
                ->group('t.id')
                ->query();
        /*
         * Формируем группы и делаем объект
         */
        while ($item = $dataReader->readObject('OrderProductSpecificationModel', $model->getAttributes())) {
            $item['group_name'] = $item->group_name;
            $this->nomenclatureElementsByGroups[$item->title][] = $item;
            $this->nomenclatureElementsBySubGroups[$item->subtitle][] = $item;
            if(!empty($item->nomenclature)){
                $code_group = $item->nomenclature->code_group;
                $this->tovGroups[$code_group][] = $item;
            }
        }
    }
    
    /**
     * Получение всех элементов, которые редактируются.
     * Щербаков
     * @return array
     */
    public function getEditedElements()
    {
        $dataReader = Yii::app()->db->createCommand()
                ->select('ops.*, spec.title')
                ->from('order_product_specification ops')
                ->leftJoin("specification spec", "(spec.id = ops.specification_id) or (spec.id = findSpecificationRootId(ops.specification_id))")
                ->where('ops.is_new=1 and ops.is_deleted=0 and ops.order_product_id=' . $this->orderProductId)
                ->query();
        /*
         * Формируем редактируемые группы и делаем объект
         */
        while ($item = $dataReader->readObject('OrderProductSpecificationModel', OrderProductSpecificationModel::model()->getAttributes())) {
            $this->editedElementsByGroups[$item->title][] = $item;
        }
    }
    
    /*
     * Получение всех линков, в разрезе по типам линков
     * Щербаков
     * @return array;
     */
    private function getLinks(){
        $dataReader = Yii::app()->db->createCommand()
                ->select('ops.*')
                ->from('order_product_specification ops')
                ->where('ops.is_deleted=0 AND ops.order_product_id='. $this->orderProductId)
                ->query();
        /*
         * Формируем link(и)
         */
        $tags = array("tag", "group_name", "nomenclature_id");
        $return = array();
        while ($item = $dataReader->readObject('OrderProductSpecificationModel', OrderProductSpecificationModel::model()->getAttributes())) {
            if ($item->amount > 0) {
                foreach($tags as $tag){
                    if(!empty($item->{$tag})){
                        $return[$tag][$item->{$tag}][] = $item->id;
                    }
                }
            }
        }
        return $return;
    }
    
    /**
     * Проверка на редактирование элемента
     *
     * @param $str
     * @param $orderProductId
     *
     * @return bool
     */
    public function getEdited($str, $orderProductId)
    {
        $bools = false;
        $dataReader = Yii::app()->db->createCommand()
                ->select('ops.*')
                ->from('order_product_specification ops')
                ->leftJoin("specification spec", "(spec.id = ops.specification_id) OR (spec.id = findSpecificationRootId(ops.specification_id))")
                ->where('ops.is_deleted=0 AND spec.title ="' . $str . '" and ops.is_new=1 and ops.order_product_id=' . $orderProductId)
                ->query();
        $items = array();
        while ($item = $dataReader->readObject('OrderProductSpecificationModel', OrderProductSpecificationModel::model()->getAttributes())) {
            $items[] = $item;
        }
        if (count($items) > 0) {
            $bools = true;
        }

        return $bools;
    }
        
    private function complectTask($elements){
        /*
        $result = $this->algorithmA($group, $element);
        $this->Operations[] = $result;
         * 
         */
    }
    
    private function shieldComplectTask($elements){
        /*
        $result = $this->algorithmA($group, $element);
        $this->Operations[] = $result;
         * 
         */
    }
    
    /*
     * Работаем с элементами из Упаковка.Направляющие
     */
    private function packLeadingTask($elements){
        
        $this->Elements = array();
        $this->ElementCount = count($elements);
        
        //  добавление общих операций
        $result = array();
        foreach($elements as $element){
            $result = $this->algorithmOfAddingGeneralOperations("Упаковка.Направляющие", $element);
            if(!empty($result))
                array_push($this->Elements, $result);
        }
        //=========================//
        
        $folder = SpecificationModel::model()->find('title="Упаковка.Направляющие" AND product_id='. $this->productId);
        if ($folder) {
            if (!empty($this->editedElementsByGroups["Упаковка.Направляющие"])) {
                $this->Elements[] = array("ИзмененияВручную" => 1);
            }
        }
        
        if(!empty($this->Elements)){
            $this->Operations[] = array(
                'Code' => "CB0000001",
                'SectionName' => "Упаковка.Направляющие",
                'Elements' => $this->Elements
            );
        }
        
        /*
        $result = $this->algorithmA($group, $element);
        $this->Operations[] = $result;
         * 
         */
    }
    
    /*
     * Работаем с элементами из Упаковка.Омега
     */
    private function omegaTask($elements){
        
        $this->Elements = array();
        $this->ElementCount = count($elements);
        
        //  добавление общих операций
        $result = array();
        foreach($elements as $element){
            $result = $this->algorithmOfAddingGeneralOperations("Упаковка.Омега", $element);
            if(!empty($result))
                array_push($this->Elements, $result);
        }
        //=========================//
        
        $folder = SpecificationModel::model()->find('title="Упаковка.Омега" AND product_id='. $this->productId);
        if ($folder) {
            if (!empty($this->editedElementsByGroups["Упаковка.Омега"])) {
                $this->Elements[] = array("ИзмененияВручную" => 1);
            }
        }
        
        if(!empty($this->Elements)){
            $this->Operations[] = array(
                'Code' => "CB0000001",
                'SectionName' => "Упаковка.Омега",
                'Elements' => $this->Elements
            );
        }
        
        /*
        $result = $this->algorithmA($group, $element);
        $this->Operations[] = $result;
         * 
         */
    }
    
    /*
     * Работаем с элементами из Упаковка.Пружины
     */
    private function springsTask($elements){
        
        $this->Elements = array();
        $this->ElementsExists = true;
        $this->SpringsPackExists = false;
        $this->ElementCount = count($elements);
        $this->SpringsPackExists = true;
        
        //  добавление общих операций
        $result = array();
        foreach($elements as $element){
            $result = $this->algorithmOfAddingGeneralOperations("Упаковка.Пружины", $element);
            if(!empty($result))
                $this->Elements[] = $result;
        }
    }
    
    private function torsionTask($elements){
        $this->ElementCount = count($elements);
        $this->ElementsExists = true;
        
        //  добавление общих операций
        $result = array();
        foreach($elements as $element){
            $result = $this->algorithmOfAddingGeneralOperations("Упаковка.Пружины", $element);
            if(!empty($result))
                array_push($this->Elements, $result);
        }
        //==========================//
    }
    
    /*
     * Функции EuropeTasks и noEuropeTasks работают с элементами принадлежащими группам 45, 138, CB0001834 (массив в массиве)
     */
    private function noEuropeTasks($elementsbygroup, $falsePanels){
        
        $formula = new FormulaHelper();
        
        if(count($elementsbygroup) > 0){
            $this->PanelExists = true;
            $this->ElementsExists = true;
        }
        foreach($elementsbygroup as $key => $groups){
            //  не нужно дублировать операции
            $repeatOperation = true;
            //==============================//
            foreach($groups as $element){
                $nomenclature = $element->nomenclature;
                if($element['title'] !== 'Упаковка.Фальшпанель'){
                    $checkPanel = preg_match('/^Панель/i', $nomenclature->title_ru);
                    if(($key === 45 || $key === 138) || ($key === 'CB0001834' && $checkPanel === 1)){
                        if(empty($this->ElementName)){
                            $title = $this->title_lang;
                            $this->ElementName = $nomenclature->$title;
                        } else {
                            if($this->ElementName !== $nomenclature->$title)
                                $this->ElementName = '-';
                        }
                        
                        $this->ElementCount = $this->ElementCount + $element->count;
                        $operations = array();                        
                        $operations[] = $element->technology_code;
                        $found = false;
                        
                        foreach($operations as $operation){
                            if($operation === 'CB0000024'){
                                $found = true;
                                //break;
                            }elseif($operation === 'CB0000005'){
                                //break;
                            }
                        }
                        
                        if($found === false && $repeatOperation === true){
                            $this->Operations[] = array(
                                'Code' => "CB0000024",
                                'SectionName' => '',
                                'Elements' => array(),
                                "KeyLinksNomenclature" => $element->id
                            );
                            $repeatOperation = false;
                        }
                    }
                    /*//  в справочнике номенклатуры данный элемент содержится в данной папке или дочерней по отношению к ней
                    $found = false;
                    if (in_array($element->technology_code, array("CB0000117"))) {
                        $found = true;
                    }
                    if (!$found) {
                        $this->Operations[] = array(
                            'Code' => "CB0000117",
                            'SectionName' => '',
                            'Elements' => array()
                            //"KeyLinksNomenclature" => $element->id
                        );
                    }*/
                }
            }
        }
        
        if(count($falsePanels) > 0){
            $this->FalsePanelExists = true;
            $this->ElementsExists = true;
        }
        
        foreach($falsePanels as $element){
            $nomenclature = $element->nomenclature;
            if($nomenclature->code_group === 45 || $nomenclature->code_group === 138){
                if(empty($this->ElementName)){
                    $title = $this->title_lang;
                    $this->ElementName = $nomenclature->$title;
                } else {
                    if($this->ElementName !== $nomenclature->$title)
                        $this->ElementName = '-';
                }
                
                $this->ElementCount = $this->ElementCount + $element->count;
                $operations = array();                        
                $operations[] = $element->technology_code;
                $found = false;

                foreach($operations as $operation){
                    if($operation === 'CB0000024'){
                        $found = true;
                        //break;
                    }elseif($operation === 'CB0000005'){
                        //break;
                    }
                }

                if($found === false){
                    $this->Operations[] = array(
                        'Code' => "CB0000024",
                        'SectionName' => '',
                        'Elements' => array(),
                        "KeyLinksNomenclature" => $element->id
                    );
                }
            }
            /*//  в справочнике номенклатуры данный элемент содержится в данной папке или дочерней по отношению к ней
            $found = false;
            if (in_array($element->technology_code, array("CB0000117"))) {
                $found = true;
            }
            if (!$found) {
                $this->Operations[] = array(
                    'Code' => "CB0000117",
                    'SectionName' => '',
                    'Elements' => array()
                    //"KeyLinksNomenclature" => $element->id
                );
            }*/
        }
        
    }
    
    private function EuropeTasks($elementsbygroup, $falsePanels){
        
        $formula = new FormulaHelper();
        
        if(count($elementsbygroup) > 0){
            $this->PanelExists = true;
            $this->ElementsExists = true;
        }
        
        foreach($elementsbygroup as $key => $groups){
            foreach($groups as $element){
                $nomenclature = $element->nomenclature;
                if($element['title'] !== 'Упаковка.Фальшпанель'){
                    $title = $this->title_lang;
                    if(empty($this->ElementName)){
                        $this->ElementName = $nomenclature->$title;
                    } else {
                        if($this->ElementName !== $nomenclature->$title)
                            $this->ElementName = '-';
                    }

                    $this->ElementCount = $this->ElementCount + $element->count;
                    $operations = array();                        
                    $operations[] = $element->technology_code;
                    $found = false;

                    foreach($operations as $operation){
                        if($operation === 'CB0000005'){
                            $found = true;
                            //break;
                        }
                    }

                    if($found === false){
                        $this->Operations[] = array(
                            'Code' => "CB0000005",
                            'SectionName' => '',
                            'Elements' => array()
                        );
                    }
                }
            }
        }
        
        if(count($falsePanels) > 0){
            $this->FalsePanelExists = true;
            $this->ElementsExists = true;
        }
        
        foreach($falsePanels as $element){
            $nomenclature = $element->nomenclature;
            if($nomenclature->code_group === 45 || $nomenclature->code_group === 138){
                $title = $this->title_lang;
                if(empty($this->ElementName)){
                    $this->ElementName = $nomenclature->$title;
                } else {
                    if($this->ElementName !== $nomenclature->$title)
                        $this->ElementName = '-';
                }
                
                $this->ElementCount = $this->ElementCount + $element->count;
                $operations = array();                        
                $operations[] = $element->technology_code;
                $found = false;

                foreach($operations as $operation){
                    if($operation === 'CB0000005'){
                        $found = true;
                        //break;
                    }
                }

                if($found === false){
                    $this->Operations[] = array(
                        'Code' => "CB0000005",
                        'SectionName' => '',
                        'Elements' => array()
                    );
                }
            }
        }
    }


    private function algorithmOfAddingGeneralOperations($section, $element){
        
        if(empty($section)) return false;
        if(empty($element)) return false;
        $result = null;
        $nomenclature = $element->nomenclature;
        $group_name = $element->group_name;
        
        if($section === 'Упаковка.Направляющие'){
            switch ($group_name){
                case 'СтойкаУгловаяМодернизированная':
                    $result = array("Направляющие_A" => $element->length);
                    break;
                case 'СтойкаУгловаяМодернизированнаяОблегченная':
                    $result = array("Направляющие_A(1.5)" => $element->length);
                    break;
                case 'НаправляющаяЛег':
                    $result = array("Направляющие_C" => $element->length);
                    break;
                case 'УплотнительБоковойУвеличенный':
                    $result = array("Направляющие_УплотнительБоковой" => $element->length);
                    break;
                case '145203':
                    $result = array("Направляющие_УплотнительБоковой" => $element->length);
                    break;
                case '14177':
                    $result = array("Направляющие_УплотнительБоковой" => $element->length);
                    break;
                case 'Вал25516':
                    $result = array("Направляющие_Вал_". $this->ShaftCount => 'Полнотелый/'. $element->count .'/'. $element->length);
                    $this->ShaftCount++;
                    break;
                case 'Вал25018':
                    $result = array("Направляющие_Вал_". $this->ShaftCount => 'Пуст. с пазом/'. $element->count .'/'. $element->length);
                    $this->ShaftCount++;
                    break;
                case 'Вал25015':
                    $result = array("Направляющие_Вал_". $this->ShaftCount => 'Пустотелый/'. $element->count .'/'. $element->length);
                    $this->ShaftCount++;
                    break;
                case 'Вал25076':
                    $result = array("Направляющие_Вал_". $this->ShaftCount => 'Вал 1.25/'. $element->count .'/'. $element->length);
                    $this->ShaftCount++;
                    break;
                case 'Профиль С':
                    $result = array("Направляющие_ПрофильС_". $this->CProfileCount => $nomenclature['article'] .'/'. $element->count .'/'. $element->length);
                    $this->CProfileCount++;
                    break;
                case '171':
                    $result = array("Направляющие_УстановочныйПрофиль_". $this->AdjustingProfileCount => $nomenclature['article'] .'/'. $element->count .'/'. $element->length);
                    $this->AdjustingProfileCount++;
                    break;
            }
        }elseif($section === 'Упаковка.Омега'){
            if($group_name === 'Omega'){
                $result = array("Направляющие_Omega_". $this->OmegaCount => $nomenclature['article'] .'/'. $element->count .'/'. $element->length);
                $this->OmegaCount++;
            }
        }elseif($section === 'Упаковка.Пружины'){
            if($group_name === 'Пружина45x5Левая' || $group_name === 'Пружина45x5Правая' || 
               $group_name === 'Пружина45x5,5Левая' || $group_name === 'Пружина45x5,5Правая' || 
               $group_name === 'Пружина45x6Левая' || $group_name === 'Пружина45x6Правая' ||
               $group_name === 'Пружина50x5Левая' || $group_name === 'Пружина50x5Правая' ||
               $group_name === 'Пружина50x5,5Левая' || $group_name === 'Пружина50x5,5Правая' ||
               $group_name === 'Пружина50x6Левая' || $group_name === 'Пружина50x6Правая' || 
               $group_name === 'Пружина50x6,5Левая' || $group_name === 'Пружина50x6,5Правая' ||
               $group_name === 'Пружина67x6Левая' || $group_name === 'Пружина67x6Правая' ||
               $group_name === 'Пружина67x6,5Левая' || $group_name === 'Пружина67x6,5Правая' ||
               $group_name === 'Пружина67x7Левая' || $group_name === 'Пружина67x7Правая' ||
               $group_name === 'Пружина67x7,5Левая' || $group_name === 'Пружина67x7,5Правая' ||
               $group_name === 'Пружина95x6,5Левая' || $group_name === 'Пружина95x6,5Правая' ||
               $group_name === 'Пружина95x7Левая' || $group_name === 'Пружина95x7Правая' ||
               $group_name === 'Пружина95x7,5Левая' || $group_name === 'Пружина95x7,5Правая' ||
               $group_name === 'Пружина95x8Левая' || $group_name === 'Пружина95x8Правая' ||
               $group_name === 'Пружина95x8,5Левая' || $group_name === 'Пружина95x8,5Правая' ||
               $group_name === 'Пружина95x9Левая' || $group_name === 'Пружина95x9Правая' ||
               $group_name === 'Пружина95x9,5Левая' || $group_name === 'Пружина95x9,5Правая' ||
               $group_name === 'Пружина152x8,5Левая' || $group_name === 'Пружина152x8,5Правая' ||
               $group_name === 'Пружина152x9Левая' || $group_name === 'Пружина152x9Правая' ||
               $group_name === 'Пружина152x9,5Левая' || $group_name === 'Пружина152x9,5Правая' ||
               $group_name === 'Пружина152x10Левая' || $group_name === 'Пружина152x10Правая' ||
               $group_name === 'Пружина152x10,5Левая' || $group_name === 'Пружина152x10,5Правая' ||
               $group_name === 'Пружина152x11Левая' || $group_name === 'Пружина152x11Правая' ||
               $group_name === 'Пружина152x11,5Левая' || $group_name === 'Пружина152x11,5Правая'){
                $new_article_ = str_replace('Пружина', '', $nomenclature->title_ru);
                $new_article = trim(str_replace('оцинкованная', '', $new_article_));
                $result = array("Пружины_ПараметрыПружины_". $this->SpringCount => $new_article .'/'. $element->count .'/'. $element->length);
                $this->SpringCount++;
            }else{
                switch ($group_name){
                    case 'Вал25516':
                        $result = array("Направляющие_Вал_". $this->ShaftCount => 'Полнотелый/'. $element->count .'/'. $element->length);
                        $this->ShaftCount++;
                        break;
                    case 'Вал25018':
                        $result = array("Направляющие_Вал_". $this->ShaftCount => 'Пуст. с пазом/'. $element->count .'/'. $element->length);
                        $this->ShaftCount++;
                        break;
                    case 'Вал25015':
                        $result = array("Направляющие_Вал_". $this->ShaftCount => 'Пустотелый/'. $element->count .'/'. $element->length);
                        $this->ShaftCount++;
                        break;
                    case 'Вал25076':
                        $result = array("Направляющие_Вал_". $this->ShaftCount => 'Вал 1.25/'. $element->count .'/'. $element->length);
                        $this->ShaftCount++;
                        break;
                }
            }
            
            if($this->RegionMoscow && !$this->RegionEurope){
                $found = false;
                if($group_name === 'Пружина152x8,5Левая' || $group_name === 'Пружина152x8,5Правая' ||
                   $group_name === 'Пружина152x9Левая' || $group_name === 'Пружина152x9Правая' ||
                   $group_name === 'Пружина152x9,5Левая' || $group_name === 'Пружина152x9,5Правая' ||
                   $group_name === 'Пружина152x10Левая' || $group_name === 'Пружина152x10Правая' ||
                   $group_name === 'Пружина152x10,5Левая' || $group_name === 'Пружина152x10,5Правая' ||
                   $group_name === 'Пружина152x11Левая' || $group_name === 'Пружина152x11Правая' ||
                   $group_name === 'Пружина152x11,5Левая' || $group_name === 'Пружина152x11,5Правая'){
                    $operations = array($element->technology_code);
                    foreach($operations as $operation){
                        if($operation === 'CB0000006'){
                            $found = true;
                        }
                    }
                    
                    if($found === false){
                        $this->Operations[] = array(
                            'Code' => "CB0000006",
                            'SectionName' => '',
                            'Elements' => array()
                        );
                    }
                }
            }
        }
        
        return $result;
    }
}