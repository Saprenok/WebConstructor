<?php

/**
 * Модуль расчета Проем
 * PHP version 5.5
 * @category Yii
 * @author   Charnou Vitaliy <graffov87@gmail.com>
 */
class ShieldMC extends AbstractModelCalculation
{

    /**
     * Переменная хранит имя класса
     * 
     * @var string 
     */
    public $nameModule = 'ShieldMC';

    /**
     * Алгоритм
     * 
     * @return bool
     */
    public function Algorithm()
    {
        if ($this->CurrentPanelType != 0 || $this->PanoramicPanel) {
            if (!$this->PanoramicPanel) {
                $model = ShieldModel::model()->find(array(
                    'condition' => 'id=:id',
                    'params' => array(
                        ':id' => $this->CurrentPanelType,
                    )
                ));
                $modelShieldGroup = ShieldGroupProductParametersModel::model()->find(array(
                    'with'=>array('shieldGroupManyProductParameters'=>array(
                        'alias'=>'m',
                        'condition' => 'm.product_id=:pid1 OR m.product_id=:pid2',
                    )),
                    'condition' => 't.shield_group_id=:shield_group_id',
                    'params' => array(
                        ':shield_group_id' => $model->shield_group_id,
                        ':pid1' => Yii::app()->container->productId,
                        ':pid2' => ProductModel::ID_ALL_PRODUCT,
                    )
                ));
                $modelSize = ShieldGroupSizesModel::model()->findAll(array(
                    'condition' => 'shiled_group_id = :id',
                    'params' => array(':id' => $model->id)
                ));
                $size = array();
                $weight = array();
                foreach ($modelSize as $key => $modelS) {
                    $size [] = $modelS->panelSize->size;
                    $weight [$modelS->panelSize->size] = $modelS->weight;
                }
                if ($model->panelType->title == 'С защитой от защемления' || $model->panelType->title == 'С защитой от защемления пальцев, тип GOLD') {
                    $this->ShieldWithAntiJamProtection = 1;
                } else {
                    $this->ShieldWithAntiJamProtection = 0;
                }
            } else {
                //Отбираем группу щитов "Панорамная панель" в справочнике щитов, ид 14 в таблице ShieldGroup
                $modelShieldGroup = ShieldGroupProductParametersModel::model()->find(array(
                    'with'=>array('shieldGroupManyProductParameters'=>array(
                        'alias'=>'m',
                        'condition' => 'm.product_id=:pid1 OR m.product_id=:pid2',
                    )),
                    'condition' => 't.shield_group_id=:shield_group_id',
                    'params' => array(
                        ':shield_group_id' => 14,
                        ':pid1' => Yii::app()->container->productId,
                        ':pid2' => ProductModel::ID_ALL_PRODUCT,
                    )
                ));
            }

            $this->ShieldIsVertical = 0;
            $this->ShieldWidth = $this->Bh;
            $this->ShieldHeight = $this->Hh;
            $this->ShieldPanelLength = $this->ShieldWidth - 4;
            
            //Определить тип профилей
            Yii::import('calculation.ProfileSelectionMC.ProfileSelectionMC');
            $profileS = new ProfileSelectionMC();
            $profileS->key = $this->key;
            $profileS->fillVariables();
            //установка атрибутов
            $profileS->Fill();
            $profileS->ShieldWithAntiJamProtection = $this->ShieldWithAntiJamProtection;
            $profileS->ShieldWidth = $this->ShieldWidth;
            
            $profileS->Algorithm();
            $profileS->Output();
            $this->ShieldTopProfile = $profileS->InitialShieldTopProfile;
            $this->ShieldBottomProfile = $profileS->InitialShieldBottomProfile;
            Yii::app()->container->ShieldTopProfile = $this->ShieldTopProfile;
            Yii::app()->container->ShieldBottomProfile = $this->ShieldBottomProfile;

            Yii::import('calculation.ProfileCalculationMC.ProfileCalculationMC');
            $profile = new ProfileCalculationMC();
            $profile->key = $this->key;
            $profile->fillVariables();
            $profile->Fill();
            $profile->ShieldTopProfile = $this->ShieldTopProfile;
            $profile->ShieldBottomProfile = $this->ShieldBottomProfile;
            $profile->ShieldWithAntiJamProtection = $this->ShieldWithAntiJamProtection;
            $profile->Algorithm();
            $profile->Output();
            
            $formula = new FormulaHelper();
            if (!$this->PanoramicPanel) {
                $this->ShieldSizesPanel = $size;
                $this->ShieldPanelWeights = $weight;
                $this->ShieldPanelName = $model->title;
                //Перевод на язык 
                $newShieldNameStr = $this->ShieldPanelName;
                $names = explode('/', $this->ShieldPanelName);
                if(count($names) > 0){
                    foreach($names as $key => $name){ $names[$key] = Yii::t('steps', trim($name)); }
                    $newShieldNameStr = implode('/', $names);
                }
                $this->ShieldPanelName = $newShieldNameStr;
                $this->ShieldMinTolerance = $formula->calculation($modelShieldGroup->lower_size_shield);
                $this->ShieldMaxTolerance = $formula->calculation($modelShieldGroup->upper_size_shield);
                $this->ShieldPanelPadding = $formula->calculation($modelShieldGroup->space_between_panels);
                $this->ShieldMinTop = $formula->calculation($modelShieldGroup->min_size_top_panel);
                $this->ShieldMinBottom = $formula->calculation($modelShieldGroup->min_size_bottom_panel);
                $this->ShieldMaxTop = $formula->calculation($modelShieldGroup->max_size_top_panel);
                $this->ShieldMaxBottom = $formula->calculation($modelShieldGroup->max_size_bottom_panel);
                $this->ShieldMinTopCut = $formula->calculation($modelShieldGroup->min_segment_top_panel);
                $this->ShieldMinBottomCut = $formula->calculation($modelShieldGroup->min_segment_bottom_panel);
                $this->ShieldTopCutDecrease = $formula->calculation($modelShieldGroup->decrease_cut_top_panel);
                $this->ShieldBottomCutDecrease = $formula->calculation($modelShieldGroup->decrease_cut_bottom_panel);
                $this->ShieldProcedureCuttingDefault = $formula->calculation($modelShieldGroup->procedure_cutting_default);//Порядок отреза по умолчанию

                /**
                * Результаты значения поля "Не резать по усилению* ": 
                *   2 - галка установлена и не доступна для изменений; 
                *   1 - галка установлена и доступна для измнений; 
                *   0 - галка не установлена и не доступна для изменений.
                */
                if (empty($model->not_cut_allowed) || FormulaHelper::getFormula($model->not_cut_allowed) === '') {          //проверяет на пустоту поля и пустую формулу
                    $this->MinCutsEnabled = $formula->calculation($modelShieldGroup->not_cut_allowed, 0);
                    if (!in_array($this->MinCutsEnabled, array(0,1,2,3))) {
                        $this->MinCutsEnabled = "Поле \"" . $model->getAttributeLabel('not_cut_allowed') . "\"(not_cut_allowed) из справочника щитов вернуло недопустимое значение!\nГруппа щитов:\n" .$model->shieldGroup->title;
                    }
                } else {
                    $this->MinCutsEnabled = $formula->calculation($model->not_cut_allowed, 0);
                    if (!in_array($this->MinCutsEnabled, array(0,1,2))) {
                        $this->MinCutsEnabled = "Поле \"" . $modelShieldGroup->getAttributeLabel('not_cut_allowed') . "\"(not_cut_allowed) из справочника группы щитов вернуло недопустимое значение!\nГруппа щитов:\n" .$model->shieldGroup->title;
                    }
                }
                
                $onlyOneSizeAllowed = empty($model->only_one_size_allowed) ? $modelShieldGroup->only_one_size_allowed : $model->only_one_size_allowed;
                $this->OnlyOneSizeAllowed = $formula->calculation($onlyOneSizeAllowed, 1);
                $this->OnlyOneSize = $formula->calculation($modelShieldGroup->only_one_size_default, 1);
                $this->ShieldAutoExtendEnabled = $formula->calculation($modelShieldGroup->autonadst_default);
                $this->ShieldAutoExtendAllowed = $formula->calculation($modelShieldGroup->autonadst_allowed);
                $this->ShieldAutoExtendCount = $formula->calculation($modelShieldGroup->autonadst_number);
                //Тип панели
                $this->ShieldPanelType = $model->panelType->title;
                //дизайн панелей
                $this->PanelDesign = $model->panelDesign->title;
                //2-ой дизайн панелей
                $this->PanelDesignAdd = $model->panelDesignAdd->title;
                $variation = explode(",", $formula->calculation($modelShieldGroup->autonadst_variation));
                $this->ShieldAutoExtendVariants = $variation;
                if ($model->panel_step_id != null) {
                    $this->ShieldPanelsWithInfill = 1;
                    $this->ShieldInfillStep = $model->panelStep->step;
                    if ($model->panel_width_id != null) {
                        $this->ShieldInfillWidth = $model->panelWidth->width;
                    } else {
                        $this->ShieldInfillWidth = $model->width_square_panel;
                    }
                    if ($model->panel_height_id != null) {
                        $this->ShieldInfillHeight = $model->panelHeight->height;
                    } else {
                        $this->ShieldInfillHeight = $model->height_square_panel;
                    }
                } else {
                    $this->ShieldPanelsWithInfill = 0;
                }
                $this->PanelOuterColor = $model->colorOutside->color;
                $this->PanelInnerColor = $model->colorInside->color;

                if (!$this->Colout) {
                    $this->Colout = $model->colorOutside->color;
                    if (strpos($this->Colout, "RAL") === 0) {
                        $this->Colout = "По RAL";
                    }
                }
                if (!$this->Colin) {
                    $this->Colin = $model->colorOutside->color;
                    if (strpos($this->Colin, "RAL") === 0) {
                        $this->Colin = "По RAL";
                    }
                }
            } else {
                $this->ShieldPanelName = Yii::app()->container->Loc(641);
                $this->ShieldMinTolerance = $formula->calculation($modelShieldGroup->lower_size_shield);
                $this->ShieldMaxTolerance = $formula->calculation($modelShieldGroup->upper_size_shield);
                $this->ShieldPanelPadding = $formula->calculation($modelShieldGroup->space_between_panels);
                $this->ShieldMinTop = $formula->calculation($modelShieldGroup->min_size_top_panel);
                $this->ShieldMinBottom = $formula->calculation($modelShieldGroup->min_size_bottom_panel);
                $this->ShieldMaxTop = $formula->calculation($modelShieldGroup->max_size_top_panel);
                $this->ShieldMaxBottom = $formula->calculation($modelShieldGroup->max_size_bottom_panel);
                $this->ShieldMinTopCut = $formula->calculation($modelShieldGroup->min_segment_top_panel);
                $this->ShieldMinBottomCut = $formula->calculation($modelShieldGroup->min_segment_bottom_panel);
                $this->ShieldTopCutDecrease = $formula->calculation($modelShieldGroup->decrease_cut_top_panel);
                $this->ShieldBottomCutDecrease = $formula->calculation($modelShieldGroup->decrease_cut_bottom_panel);
                $this->ShieldProcedureCuttingDefault = $formula->calculation($modelShieldGroup->procedure_cutting_default);//Порядок отреза по умолчанию
            }
        }
        
        if ($this->TypeF == "Алюминиевый") {
            $this->L = $this->Bh;
            $this->A = $this->Hh;
            
            //присваиваем занчения ширины и высоты щита значениям полученных ранее из FormShieldOptionsMC
            $this->ShieldWidth = $this->SandShieldWidth;
            $this->ShieldHeight = $this->SandShieldHeight;
            
            //Расчет количества заклепок
            if ($this->b_send == 1) {
                if ($this->b_resh == 1) {
                    $this->n_zakl = round(($this->L + $this->A - $this->resh) * 4 / 200 + ($this->L + $this->A - $this->resh) * 2 / 750);
                } else {
                    $this->n_zakl = round(($this->L + $this->A) * 4 / 200 + ($this->L + $this->A) * 2 / 750);
                }
            }
            //Дополнительный горизонтальный профиль
            $this->dop_gor = 0;
            if ($this->Index == "DHPF020" || $this->Index == "DHPF021" || $this->Index == "DHPF022" || $this->Index == "DHPF023" || $this->Index == "DHPF024" ||
                $this->Index == "DHPF030" || $this->Index == "DHPF031" || $this->Index == "DHPF032" || $this->Index == "DHPF033" || $this->Index == "DHPF034" ||
                $this->Index == "DHPF040" || $this->Index == "DHPF041" || $this->Index == "DHPF042" || $this->Index == "DHPF043" || $this->Index == "DHPF044" ||
                $this->Index == "DHPF050" || $this->Index == "DHPF051" || $this->Index == "DHPF052" || $this->Index == "DHPF053" || $this->Index == "DHPF054" ||
                $this->Index == "DHPF060" || $this->Index == "DHPF061" || $this->Index == "DHPF062" || $this->Index == "DHPF063" || $this->Index == "DHPF064" ||
                $this->Index == "DHPF070" || $this->Index == "DHPF071" || $this->Index == "DHPF072" || $this->Index == "DHPF073" || $this->Index == "DHPF074" ||
                $this->Index == "DHPF160" || $this->Index == "DHPF161" || $this->Index == "DHPF162" || $this->Index == "DHPF163" || $this->Index == "DHPF164" ||
                $this->Index == "DHPF170" || $this->Index == "DHPF171" || $this->Index == "DHPF172" || $this->Index == "DHPF173" ||
                $this->Index == "DHPF220" || $this->Index == "DHPF221" || $this->Index == "DHPF222" || $this->Index == "DHPF223" || $this->Index == "DHPF224" ||
                $this->Index == "DHPF230" || $this->Index == "DHPF232" || $this->Index == "DHPF233" || $this->Index == "DHPF234" ||
                $this->Index == "DHPF240" || $this->Index == "DHPF241" || $this->Index == "DHPF242" || $this->Index == "DHPF243" || $this->Index == "DHPF244" ||
                $this->Index == "DHPF250" || $this->Index == "DHPF251" || $this->Index == "DHPF252" || $this->Index == "DHPF253" || $this->Index == "DHPF254"
            ) {
                $this->dop_gor = 1;
            }
            //Профиль крышка
            $this->b_krsh = 0;
            if ($this->Index == "DHPF010" || $this->Index == "DHPF011" || $this->Index == "DHPF012" || $this->Index == "DHPF013" ||
                $this->Index == "DHPF110" || $this->Index == "DHPF111" || $this->Index == "DHPF112" || $this->Index == "DHPF113" ||
                $this->Index == "DHPF170" || $this->Index == "DHPF171" || $this->Index == "DHPF172" || $this->Index == "DHPF173" ||
                $this->Index == "DHPF210" || $this->Index == "DHPF211" || $this->Index == "DHPF212" || $this->Index == "DHPF213" ||
                $this->Index == "DHPF310" || $this->Index == "DHPF311" || $this->Index == "DHPF312"
            ) {
                $this->b_krsh = 1;
            }
            //Окантовка решетки
            $this->one_ok = 0;
            if ($this->Index == "DHPF020" || $this->Index == "DHPF021" || $this->Index == "DHPF022" || $this->Index == "DHPF023" || $this->Index == "DHPF024" ||
                $this->Index == "DHPF030" || $this->Index == "DHPF031" || $this->Index == "DHPF032" || $this->Index == "DHPF033" || $this->Index == "DHPF034" ||
                $this->Index == "DHPF040" || $this->Index == "DHPF041" || $this->Index == "DHPF042" || $this->Index == "DHPF043" || $this->Index == "DHPF044" ||
                $this->Index == "DHPF050" || $this->Index == "DHPF051" || $this->Index == "DHPF052" || $this->Index == "DHPF053" || $this->Index == "DHPF054" ||
                $this->Index == "DHPF060" || $this->Index == "DHPF061" || $this->Index == "DHPF062" || $this->Index == "DHPF063" || $this->Index == "DHPF064" ||
                $this->Index == "DHPF070" || $this->Index == "DHPF071" || $this->Index == "DHPF072" || $this->Index == "DHPF073" || $this->Index == "DHPF074"
            ) {
                $this->h_ok = $this->resh + 20;
            } else if ($this->Index == "DHPF160" || $this->Index == "DHPF161" || $this->Index == "DHPF162" || $this->Index == "DHPF163" || $this->Index == "DHPF164" ||
                $this->Index == "DHPF170" || $this->Index == "DHPF171" || $this->Index == "DHPF172" || $this->Index == "DHPF173"
            ) {
                $this->h_ok = $this->n_resh + 20;
            } else if ($this->Index == "DHPF220" || $this->Index == "DHPF221" || $this->Index == "DHPF222" || $this->Index == "DHPF223" || $this->Index == "DHPF224" ||
                $this->Index == "DHPF230" || $this->Index == "DHPF231" || $this->Index == "DHPF232" || $this->Index == "DHPF233" || $this->Index == "DHPF234" ||
                $this->Index == "DHPF240" || $this->Index == "DHPF241" || $this->Index == "DHPF242" || $this->Index == "DHPF243" || $this->Index == "DHPF244" ||
                $this->Index == "DHPF250" || $this->Index == "DHPF251" || $this->Index == "DHPF252" || $this->Index == "DHPF253" || $this->Index == "DHPF254"
            ) {
                $this->h_ok = $this->resh + $this->n_resh + 20;
                $this->one_ok = 1;
            } else if ($this->Index == "DHPF180"
            ) {
                $this->h_ok = $this->n_resh_m + 20;
            } else if ($this->Index == "DHPF260"
            ) {
                $this->h_ok = $this->n_resh + 20;
            }
            //Окантовка Профиль "Большая крышка", Профиль "Малая крышка", Профиль "Штапик"
            $this->ok_sht = 0;
            $this->ok_tr = 0;
            if ($this->Index == "DHPF020" || $this->Index == "DHPF021" || $this->Index == "DHPF022" || $this->Index == "DHPF023" || $this->Index == "DHPF024" ||
                $this->Index == "DHPF100" || $this->Index == "DHPF101" || $this->Index == "DHPF102" || $this->Index == "DHPF103" || $this->Index == "DHPF104" || $this->Index == "DHPF105" || $this->Index == "DHPF106" ||
                $this->Index == "DHPF110" || $this->Index == "DHPF111" || $this->Index == "DHPF112" || $this->Index == "DHPF113" ||
                $this->Index == "DHPF120" || $this->Index == "DHPF121" || $this->Index == "DHPF122" || $this->Index == "DHPF123" || $this->Index == "DHPF124" ||
                $this->Index == "DHPF200" || $this->Index == "DHPF201" || $this->Index == "DHPF210" || $this->Index == "DHPF211" || $this->Index == "DHPF212" || $this->Index == "DHPF213" ||
                $this->Index == "DHPF220" || $this->Index == "DHPF221" || $this->Index == "DHPF222" || $this->Index == "DHPF223" || $this->Index == "DHPF224" ||
                $this->Index == "DHPF320" || $this->Index == "DHPF321" || $this->Index == "DHPF300" || $this->Index == "DHPF301" || $this->Index == "DHPF310" || $this->Index == "DHPF311" || $this->Index == "DHPF312" || $this->Index == "DHPF313" ||
                $this->Index == "DHPF322" || $this->Index == "DHPF323" || $this->Index == "DHPF324" || $this->Index == "DHPF400" || $this->Index == "DHPF420" || $this->Index == "DHPF500" || $this->Index == "DHPF520"    
            ) {
                $this->ok_sht = 1;
            } else if ($this->Index == "DHPF030" || $this->Index == "DHPF031" || $this->Index == "DHPF032" || $this->Index == "DHPF033" || $this->Index == "DHPF034" ||
                $this->Index == "DHPF040" || $this->Index == "DHPF041" || $this->Index == "DHPF042" || $this->Index == "DHPF043" || $this->Index == "DHPF044" || 
                $this->Index == "DHPF050" || $this->Index == "DHPF051" || $this->Index == "DHPF052" || $this->Index == "DHPF053" || $this->Index == "DHPF054" ||
                $this->Index == "DHPF060" || $this->Index == "DHPF061" || $this->Index == "DHPF062" || $this->Index == "DHPF063" || $this->Index == "DHPF064" ||
                $this->Index == "DHPF070" || $this->Index == "DHPF071" || $this->Index == "DHPF072" || $this->Index == "DHPF073" || $this->Index == "DHPF074" ||
                $this->Index == "DHPF130" || $this->Index == "DHPF131" || $this->Index == "DHPF132" || $this->Index == "DHPF133" || $this->Index == "DHPF134" ||
                $this->Index == "DHPF140" || $this->Index == "DHPF141" || $this->Index == "DHPF142" || $this->Index == "DHPF143" || $this->Index == "DHPF144" ||
                $this->Index == "DHPF150" || $this->Index == "DHPF151" || $this->Index == "DHPF152" || $this->Index == "DHPF153" || $this->Index == "DHPF154" ||
                $this->Index == "DHPF160" || $this->Index == "DHPF161" || $this->Index == "DHPF162" || $this->Index == "DHPF163" || $this->Index == "DHPF164" ||
                $this->Index == "DHPF170" || $this->Index == "DHPF171" || $this->Index == "DHPF172" || $this->Index == "DHPF173" || $this->Index == "DHPF180" ||
                $this->Index == "DHPF330" || $this->Index == "DHPF331" || $this->Index == "DHPF332" || $this->Index == "DHPF333" || $this->Index == "DHPF334" ||
                $this->Index == "DHPF340" || $this->Index == "DHPF341" || $this->Index == "DHPF342" || $this->Index == "DHPF343" || $this->Index == "DHPF344" ||
                $this->Index == "DHPF430" || $this->Index == "DHPF440" || $this->Index == "DHPF450" || $this->Index == "DHPF530" || $this->Index == "DHPF540"

            ) {
                $this->ok_tr = 1;
            }
        }
        
        $this->PanelsWidth = $this->ShieldWidth - 4;
        if ($this->VerticalPanel) {
            $this->PanelsWidth = $this->ShieldHeight - 4;
        }
        
        if ($this->TypeF == "Алюминиевый") {
            if ($this->VidS == "Прямоугольный") {
                //количество саморезов
                $this->n_sam = 0;
                $this->n_sam_ug = 16;
                if ($this->Index == "DHPF002" || $this->Index == "DHPF022" || $this->Index == "DHPF032" || $this->Index == "DHPF042" || $this->Index == "DHPF052" || $this->Index == "DHPF062" || $this->Index == "DHPF072") {
                    $this->n_sam = $this->n_sam + floor($this->ShieldWidth/95) * 2;
                } else if ($this->Index == "DHPF003" || $this->Index == "DHPF023" || $this->Index == "DHPF033" || $this->Index == "DHPF043" || $this->Index == "DHPF053" || $this->Index == "DHPF063" || $this->Index == "DHPF073") {
                    $this->n_sam = $this->n_sam + floor($this->ShieldHeight/95) * 2;
                } else if ($this->Index == "DHPF004" || $this->Index == "DHPF024" || $this->Index == "DHPF034" || $this->Index == "DHPF044" || $this->Index == "DHPF054" || $this->Index == "DHPF064" || $this->Index == "DHPF074") {
                    $this->n_sam = $this->n_sam + ceil(($this->ShieldHeight + $this->ShieldWidth)/134.4) * 2;
                } else if ($this->Index == "DHPF010" || $this->Index == "DHPF011") {
                    $this->n_sam = $this->n_sam + floor($this->ShieldWidth/140) * 4;
                } else if ($this->Index == "DHPF012") {
                    $this->n_sam = $this->n_sam + floor($this->ShieldHeight/140) * 4;
                } else if ($this->Index == "DHPF013") {
                    $this->n_sam = $this->n_sam + floor(($this->ShieldHeight + $this->ShieldWidth)/226.3) * 4;
                }
                //Заполнение решетки
                if ($this->Index == "DHPF020" || $this->Index == "DHPF021" || $this->Index == "DHPF022" || $this->Index == "DHPF023" || $this->Index == "DHPF024") {
                    $this->n_sht = floor($this->ShieldWidth/140);
                    $this->n_sam = $this->n_sam + floor($this->ShieldWidth/140) * 4 + 8;
                } else if ($this->Index == "DHPF030" || $this->Index == "DHPF031" || $this->Index == "DHPF032" || $this->Index == "DHPF033" || $this->Index == "DHPF034") {
                    $this->n_sht = floor($this->ShieldWidth/100);
                    $this->n_sam = $this->n_sam + 8;
                } else if ($this->Index == "DHPF040" || $this->Index == "DHPF041" || $this->Index == "DHPF042" || $this->Index == "DHPF043" || $this->Index == "DHPF044" || $this->Index == "DHPF060" || $this->Index == "DHPF061" || $this->Index == "DHPF062" || $this->Index == "DHPF063" || $this->Index == "DHPF064") {
                    $this->n_sht = floor($this->ShieldWidth/100);
                    $this->n_sam = $this->n_sam + 8;
                } else if ($this->Index == "DHPF050" || $this->Index == "DHPF051" || $this->Index == "DHPF052" || $this->Index == "DHPF053" || $this->Index == "DHPF054" || $this->Index == "DHPF070" || $this->Index == "DHPF071" || $this->Index == "DHPF072" || $this->Index == "DHPF073" || $this->Index == "DHPF074") {
                    $this->n_sht = floor($this->ShieldWidth/100);
                    $this->n_sam = $this->n_sam + 8;
                }
            } else if ($this->VidS == "Арочный") {
                //Количество саморезов
                $this->n_sam = 4;
                $this->n_sam_ug = 16;
                //Расчет длины решетки и длины окантовки решетки
                $R = 0.5 * ((pow($this->resh, 2)) + (pow(0.5 * $this->Bh, 2))) / $this->resh;
                $this->l_resh = round(4* $R * asin(sqrt((pow($this->resh, 2)) + (pow(0.5 * $this->Bh, 2))) / (2 * $R)) - 70);
                $this->l_ok_resh = round(4* ($R - 18) * asin(sqrt((pow($this->resh, 2)) + (pow(0.5 * $this->Bh, 2))) / (2 * $R)) - 70);
                if ($this->Index == "DHPF102" || $this->Index == "DHPF122" || $this->Index == "DHPF132" || $this->Index == "DHPF142" || $this->Index == "DHPF152" || $this->Index == "DHPF162") {
                    $this->n_sam = $this->n_sam + floor($this->ShieldWidth/95) * 2;
                } else if ($this->Index == "DHPF103" || $this->Index == "DHPF123" || $this->Index == "DHPF133" || $this->Index == "DHPF143" || $this->Index == "DHPF153" || $this->Index == "DHPF163") {
                    $this->n_sam = $this->n_sam + floor($this->ShieldHeight/95) * 2;
                } else if ($this->Index == "DHPF104" || $this->Index == "DHPF124" || $this->Index == "DHPF134" || $this->Index == "DHPF144" || $this->Index == "DHPF154" || $this->Index == "DHPF164") {
                    $this->n_sam = $this->n_sam + ceil(($this->ShieldHeight + $this->ShieldWidth)/134.4) * 2;
                } else if ($this->Index == "DHPF110" || $this->Index == "DHPF111" || $this->Index == "DHPF170" || $this->Index == "DHPF171") {
                    $this->n_sam = $this->n_sam + floor($this->ShieldWidth/140) * 4;
                } else if ($this->Index == "DHPF112" || $this->Index == "DHPF172") {
                    $this->n_sam = $this->n_sam + floor($this->ShieldHeight/140) * 4;
                } else if ($this->Index == "DHPF113" || $this->Index == "DHPF173") {
                    $this->n_sam = $this->n_sam + floor(($this->ShieldHeight + $this->ShieldWidth)/226.3) * 4;
                } else if ($this->Index == "DHPF180") {
                    $this->n_sam = $this->n_sam + ceil($this->ShieldWidth/95) * 2 + 8;
                }
                //Заполнение решетки
                if ($this->Index == "DHPF100" || $this->Index == "DHPF101" || $this->Index == "DHPF102" || $this->Index == "DHPF103" || $this->Index == "DHPF104" || $this->Index == "DHPF105" || $this->Index == "DHPF106") {
                    if ($this->Index != "DHPF105" && $this->Index != "DHPF106") {
                        $this->n_sht = ceil(($this->Bh + 100 - 209)/95);
                        $this->n_sam = $this->n_sam + floor(($this->Bh + 100 - 209)/95) * 2;
                    }    
                } else if ($this->Index == "DHPF110" || $this->Index == "DHPF111" || $this->Index == "DHPF112" || $this->Index == "DHPF113" || $this->Index == "DHPF120" || $this->Index == "DHPF121" || $this->Index == "DHPF122" || $this->Index == "DHPF123" || $this->Index == "DHPF124") {
                    $this->n_sht = (floor(($this->Bh + 100 - 209)/140)/2) * 2;
                    $this->n_sam = $this->n_sam + ((floor(($this->Bh + 100 - 209)/140)/2) * 2) * 4;
                } else if ($this->Index == "DHPF160" || $this->Index == "DHPF161" || $this->Index == "DHPF162" || $this->Index == "DHPF163" || $this->Index == "DHPF164" || $this->Index == "DHPF170" || $this->Index == "DHPF171" || $this->Index == "DHPF172" || $this->Index == "DHPF173") {
                    $this->n_sam = $this->n_sam + 8;
                }
            } else if ($this->VidS == "Вогнутый") {
                //Количество саморезов
                $this->n_sam = 4;
                $this->n_sam_ug = 8;
                //Расчет длины решетки и длины окантовки решетки
                $R = 0.5 * ((pow($this->resh, 2)) + (pow(0.5 * $this->Bh, 2))) / $this->resh;
                $this->l_resh = round(4* $R * asin(sqrt((pow($this->resh, 2)) + (pow(0.5 * $this->Bh, 2))) / (2 * $R)) - 70);
                $this->l_ok_resh = round(4* ($R - 18) * asin(sqrt((pow($this->resh, 2)) + (pow(0.5 * $this->Bh, 2))) / (2 * $R)) - 70);
                if ($this->Index == "DHPF200") {
                    $this->n_sam = $this->n_sam + floor($this->ShieldWidth/95) * 2;
                } else if ($this->Index == "DHPF201") {
                    $this->n_sam = $this->n_sam + ceil(($this->ShieldHeight + $this->ShieldWidth)/134.4) * 2;
                } else if ($this->Index == "DHPF210" || $this->Index == "DHPF211") {
                    $this->n_sam = $this->n_sam + floor($this->ShieldWidth/140) * 4;
                } else if ($this->Index == "DHPF212") {
                    $this->n_sam = $this->n_sam + floor($this->ShieldHeight/140) * 4;
                } else if ($this->Index == "DHPF213") {
                    $this->n_sam = $this->n_sam + floor(($this->ShieldHeight + $this->ShieldWidth)/226.3) * 4;
                } else if ($this->Index == "DHPF222" || $this->Index == "DHPF223" || $this->Index == "DHPF232" || $this->Index == "DHPF233" || $this->Index == "DHPF242" || $this->Index == "DHPF243" || $this->Index == "DHPF252" || $this->Index == "DHPF253") {
                    if ($this->Index == "DHPF222" || $this->Index == "DHPF232" || $this->Index == "DHPF242" || $this->Index == "DHPF252") {
                        $this->n_sam = $this->n_sam + floor($this->ShieldWidth/95) * 2;
                    } else {
                        $this->n_sam = $this->n_sam + floor($this->ShieldHeight/95) * 2;
                    }
                } else if ($this->Index == "DHPF224" || $this->Index == "DHPF234" || $this->Index == "DHPF244" || $this->Index == "DHPF254") {
                    $this->n_sam = $this->n_sam + ceil(($this->ShieldHeight + $this->ShieldWidth)/134.4) * 2;
                } else if ($this->Index == "DHPF260") {
                    $this->n_sam = $this->n_sam + floor($this->ShieldWidth/95) * 2 + 8;
                }
                //Заполнение решетки
                if ($this->Index == "DHPF220" || $this->Index == "DHPF221" || $this->Index == "DHPF222" || $this->Index == "DHPF223" || $this->Index == "DHPF224") {
                    $this->n_sam = $this->n_sam + floor(($this->Bh - 64)/140) * 4;
                }
            } else if ($this->VidS == "Волна") {
                //Количество саморезов
                $this->n_sam = 4;
                $this->n_sam_ug = 16;
                //Расчет длины решетки и длины окантовки решетки
                $R = 0.5 * ((pow($this->resh, 2)) + (pow(0.5 * $this->Bh, 2))) / $this->resh;
                $this->l_resh = round(4* $R * asin(sqrt((pow($this->resh, 2)) + (pow(0.5 * $this->Bh, 2))) / (2 * $R)) - 70);
                $this->l_ok_resh = round(4* ($R - 18) * asin(sqrt((pow($this->resh, 2)) + (pow(0.5 * $this->Bh, 2))) / (2 * $R)) - 70);
                if ($this->Index == "DHPF310" || $this->Index == "DHPF311") {
                    $this->n_sam = $this->n_sam + floor($this->ShieldWidth/140) * 4;
                } else if ($this->Index == "DHPF312") {
                    $this->n_sam = $this->n_sam + floor(($this->ShieldHeight + $this->ShieldWidth)/226.3) * 4;
                } else if ($this->Index == "DHPF300" || $this->Index == "DHPF322" || $this->Index == "DHPF332" || $this->Index == "DHPF342") {
                    $this->n_sam = $this->n_sam + floor($this->ShieldWidth/95) * 2;
                } else if ($this->Index == "DHPF301" || $this->Index == "DHPF323" || $this->Index == "DHPF333" || $this->Index == "DHPF343") {
                    $this->n_sam = $this->n_sam + floor($this->ShieldHeight/95) * 2;
                } else if ($this->Index == "DHPF302" || $this->Index == "DHPF324" || $this->Index == "DHPF334" || $this->Index == "DHPF344") {
                    $this->n_sam = $this->n_sam + ceil(($this->ShieldHeight + $this->ShieldWidth)/134.4) * 2;
                } 
                //Заполнение решетки
                if ($this->Index == "DHPF300" || $this->Index == "DHPF301" || $this->Index == "DHPF302") {
                    $this->n_sht = ceil(($this->Bh + 100 - 209)/95);
                    $this->n_sam = $this->n_sam + floor(($this->Bh + 100 - 209)/95) * 2;
                } else if ($this->Index == "DHPF310" || $this->Index == "DHPF311" || $this->Index == "DHPF312" || $this->Index == "DHPF320" || $this->Index == "DHPF321" || $this->Index == "DHPF322" || $this->Index == "DHPF323" || $this->Index == "DHPF324") {
                    $this->n_sht = floor(($this->Bh + 100 - 209)/140);
                    $this->n_sam = $this->n_sam + floor(($this->Bh + 100 - 209)/140) * 4;
                }
            } else if ($this->VidS == "Верх арки") {
                //Щит-верх
                $this->b_verch = 1;
                //Количество саморезов
                $this->n_sam = 4;
                //Расчет длины решетки и длины окантовки решетки
                $R = 0.5 * ((pow($this->Hh, 2)) + (pow(0.5 * $this->Bh, 2))) / $this->Hh;
                $this->l_resh = round(4* $R * asin(sqrt((pow($this->Hh, 2)) + (pow(0.5 * $this->Bh, 2))) / (2 * $R)) - 70);
                $this->l_ok_resh = round(4* ($R - 18) * asin(sqrt((pow($this->Hh, 2)) + (pow(0.5 * $this->Bh, 2))) / (2 * $R)) - 70);
                if ($this->Index == "DHPF400") {
                    $this->n_sht = ceil(($this->Bh + 100 - 209)/95);
                    $this->n_sam = $this->n_sam + floor(($this->Bh + 100 - 209)/95) * 2;
                } else if ($this->Index == "DHPF420") {
                    $this->n_sht = floor((($this->Bh + 100 - 209)/140)/2);
                    $this->n_sam = $this->n_sam + floor((($this->Bh + 100 - 209)/140)/2) * 4;
                }
            } else if ($this->VidS == "Верх волны") {
                //Щит-верх
                $this->b_verch = 1;
                //Количество саморезов
                $this->n_sam = 4;
                //Расчет длины решетки и длины окантовки решетки
                $R = 0.5 * ((pow($this->Hh, 2)) + (pow(0.5 * $this->Bh, 2))) / $this->resh;
                $this->l_resh = round(4* $R * asin(sqrt((pow($this->Hh, 2)) + (pow(0.5 * $this->Bh, 2))) / (2 * $R)) - 70);
                $this->l_ok_resh = round(4* ($R - 18) * asin(sqrt((pow($this->Hh, 2)) + (pow(0.5 * $this->Bh, 2))) / (2 * $R)) - 70);
                if ($this->Index == "DHPF500") {
                    $this->n_sht = floor(($this->Bh + 100 - 209)/95);
                    $this->n_sam = $this->n_sam + ceil(($this->Bh + 100 - 209)/95) * 2;
                } else if ($this->Index == "DHPF520") {
                    $this->n_sht = floor(($this->Bh + 100 - 209)/140);
                    $this->n_sam = $this->n_sam + floor(($this->Bh + 100 - 209)/140) * 4;
                }
            }
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
        return 'Щит';
    }

}
