<?php

/**
 * Модуль расчета веса щита
 * PHP version 5.5
 * @category Yii
 */
class ShieldWeightCalculationMC extends AbstractModelCalculation
{

    /**
     * Переменная хранит имя класса
     *
     * @var string
     */
    public $nameModule = 'ShieldWeightCalculationMC';

    /**
     * Алгоритм
     *
     * @return bool
     */
    public function Algorithm()
    {
        /*
         * А. Расчет веса панелей
         */
        $ArrayWeight = array();
        Yii::import('webroot.protected.modules.constructor.components.*');
        if ($this->EmbeddedShieldWeightType) {
            $this->ShieldWeight = $this->EmbeddedShieldWeight;
            $this->ShieldBottomPanelWeight = $this->EmbeddedShieldBottomPanelWeight * 2; //
        } else {
            $model = ShieldModel::model()->find(array(
                'condition' => 'id=:id',
                'params' => array(
                    ':id' => $this->CurrentPanelType,
                )
            ));
            $sizes = ShieldGroupSizesModel::model()->findAll('shiled_group_id=' . $model->id);
            //Значение по умолчанию
            $this->ShieldWeight = 0;
            $Weight = 0;
            //Для модификации этих параметров сделаем их копии
            $CurrentPanelSizes = $this->ShieldPanelSizes;
            $CurrentPanelWeights = $this->ShieldPanelWeights;
            $nomenclature_for = [];
            $panel_code = 0;
            //Должны быть уже отсортированы, но гарантий нет - сортируем сами
            for ($i = 1; $i <= $this->ShieldPanelCount; $i++) {
                //Количество доступных типоразмеров
                for ($j = 0; $j < count($CurrentPanelSizes); $j++) {
                    if ($this->ShieldWholePanels[$i] == $CurrentPanelSizes[$j]) {
                        // Есть информация о подборе панелей
                        $panel_code = 0;
                        // Для всех панелей
                        foreach ($sizes as $key => $value) {
                            if ($value->panelSize->size == $CurrentPanelSizes[$j]) {
                                $panel_code = $value->panel_code;
                            }
                        }
                        if(empty($nomenclature_for[$panel_code])) $nomenclature_for[$panel_code] = NomenclatureModel::model()->find('code = \''. $panel_code .'\'');
                        $nomenclature = $nomenclature_for[$panel_code];
                        $CurrentWeight = $nomenclature->weight * 1000 / $CurrentPanelSizes[$j];

                        $Weight = ($this->ShieldPanels[$i] * $this->ShieldPanelLength * $CurrentWeight) / 1000000;
                        $ArrayWeight[] = array(
                            "code" => $panel_code,
                            "name" => $nomenclature->title_ru,
                            "kol" => $this->ShieldPanels[$i] * $this->ShieldPanelLength/1000000,
                            "weight_length" => $CurrentWeight,
                            "weight" => $Weight
                        );
                        //Добавляем к общему весу
                        $this->ShieldWeight = $this->ShieldWeight + $Weight;
                    }
                }
            }
            $nomenclature_for = NomenclatureModel::model()->find('code = \''. $panel_code .'\'');
            for ($j = count($CurrentPanelSizes) - 1; $j >= 0; $j--) {
                if ($CurrentPanelSizes[$j] == $this->ShieldWholePanels[count($this->ShieldWholePanels)]) {
                    $CurrentWeight = $nomenclature_for->weight * 1000 / $CurrentPanelSizes[$j];
                    $Weight = ($this->ShieldPanels[count($this->ShieldPanels)] * $this->ShieldPanelLength * $CurrentWeight) / 1000000;
                    // в поле записывается весь вес нижней панели
                    $this->ShieldBottomPanelWeight = $Weight;
                    break;
                }
            }

            $SpecificationItem = new SpecificationItemModel();
            $SpecificationItem->setKey($this->key);
            list($errors, $result) = $SpecificationItem->createSpecificationForCalcWeight(Yii::app()->container->productId, $this->key);
            $nomenclature_for = [];
            foreach ($result as $key => $value) {
                // Значение по умолчанию
                $weight = 0;
                // Получить информацию об элементах номенклатуры
                if ($value['is_calc_weight_shield'] == 1) {
                    if(empty($nomenclature_for[$value['nomenclature_id']])) $nomenclature_for[$value['nomenclature_id']] = NomenclatureModel::model()->find('code = "' . $value['nomenclature_id'] . '"');
                    $nomenclature = $nomenclature_for[$value['nomenclature_id']];
                    // Считаем вес каждого элемента
                    $valueW = !$value['is_additional'] && $value['type'] != OrderProductSpecificationModel::TYPE_COMMENT ? OrderSpecificationHelper::calculationforWeight($value, $nomenclature) : $value['amount'];
                    // Формула вычисления веса
                    $this->ShieldWeight += $valueW * $nomenclature->weight;
                    // Формула вычисления кличества
                    $tempWeight = (isset($value['weight']) ? (($value['weight'] > 0) ? $value['weight'] : $nomenclature->weight) : $nomenclature->weight);
                    //Формула вычисления длины в погонных метрах
                    $weight = $valueW * $nomenclature->weight;
                    $ArrayWeight[] = array(
                        "code" => $value['nomenclature_id'],
                        "name" => $nomenclature->title_ru,
                        "kol" => $valueW,
                        "weight_length" => $nomenclature->weight,
                        "weight" => $weight
                    );
                }
            }
            $this->ShieldWeight = round($this->ShieldWeight);
        }        
        $this->ShieldWeight = round($this->ShieldWeight, 2);
        $this->ShieldBottomPanelWeight = round($this->ShieldBottomPanelWeight, 2);
        $this->ArrayWeight = $ArrayWeight;

        return true;
    }

    /**
     * Название модуля
     *
     * @return string
     */
    public function getTitle()
    {
        return 'Расчет веса щита';
    }

    /**
     * @param $arrayMain
     * @param $arraySecond
     * return array ($arrayMain, $arraySecond)
     */
    private function _array_multisort($arrayMain, $arraySecond)
    {
        array_multisort($arrayMain, SORT_DESC, $arraySecond);
        array_unshift($arrayMain, null);
        unset($arrayMain[0]);
        array_unshift($arraySecond, null);
        unset($arraySecond[0]);

        return array(
            $arrayMain,
            $arraySecond
        );
    }

}
