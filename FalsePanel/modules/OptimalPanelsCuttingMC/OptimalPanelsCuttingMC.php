<?php

/**
 * Модуль расчета Проем
 *
 * PHP version 5.5
 * @category Yii
 * @author   Charnou Vitaliy <graffov87@gmail.com>
 */
class OptimalPanelsCuttingMC extends AbstractModelCalculation
{

    /**
     * Переменная хранит имя класса
     *
     * @var string
     */
    public $nameModule = 'OptimalPanelsCuttingMC';

    /**
    * Подсчитывае количество итераций цикла, при подсчете раскроя щита.
    * 
    * @var int
    */
    public $countIteration = 0;
    
    /**
     * Список не модифицированных вариантов раскроя
     *
     * @var array
     */
    protected $CuttingVariantsUnmodified = array();
    protected $Result = 0;
    protected $PanPanelPadding = 0;

    /**
     * Алгоритм
     *
     * @return bool
     */
    public function Algorithm()
    {
        $this->ShieldPanelsLength = array();
        $ShieldHeight = $this->ShieldHeight;
        if ($this->VerticalPanel)
            $ShieldHeight = $this->ShieldWidth;

        //выполнять раскрой, если есть панели
        if (is_array($this->ShieldPanelSizes)) {
            //Разрешено резать по усилению
            if ($this->MinCutsEnabledSelected == 0) {
                //Убираем ограничения
                $this->ShieldMinTopCut = 0;
                $this->ShieldMinBottomCut = 0;
            };
            $this->SelectedSizes = $this->ShieldPanelSizes;
            $this->SelectedOnlyOne = $this->OnlyOneSizeSelected;
            $this->SelectedAutoExtend = $this->ShieldAutoExtendEnabledSelected;
            $this->SelectedMinCuts = $this->MinCutsEnabledSelected;
            //Приоритет выбора типоразмеров панелей - от большего к меньшему
            $sizer = $this->ShieldPanelSizes;
            rsort($sizer);
            $this->ShieldPanelSizes = $sizer;
            
            //Значения по умолчанию                                                                     
            $this->PanPanels = array();
            $this->PanPanelCount = 0;                                                                          
            $this->SandTopPanels = array();
            $this->SandTopCount = 0;
            $this->SandBottomPanels = array();
            $this->SandBottomCount = 0;
            $PanoramicPanelSizes = array(385, 475, 500, 525, 530, 550, 562, 575, 610);
            $SandNumberOfPanels = array();
            foreach ($PanoramicPanelSizes as $PanelSize) {
                $SandNumberOfPanels[$PanelSize] = 0;
            }
            $this->SandNumberOfPanels = $SandNumberOfPanels;
            $this->SandTopHeight = 0;
            $this->SandBottomHeight = 0;
            $this->SandTopRealHeight = 0;
            $this->SandBottomRealHeight = 0;
            $this->SandTopPanel = 0;
            $this->SandBottomPanel = 0;
            $this->SandWholeTopPanel = 0;
            $this->SandWholeBottomPanel = 0;
            $this->SandTopPanelIsCut = 0;
            $this->SandBottomPanelIsCut = 0;
            $this->SandTopPanelCut = 0;
            $this->SandBottomPanelCut = 0;
            $CuttingVariants = array();
            
            //Для обычных панелей
            if ($this->PanoramicPanel == 0) {
            //Смотри алгоритм 1
            $this->autoExtend();
            
            //Начало сортировки вариантов раскроя
            $tempCutting = $this->CuttingVariantsUnmodified;
            $deltaA = array();
            $ratingA = array();
            $cuttingA = array();
            foreach ($tempCutting as $key => $value) {
                $deltaA[$key] = abs($value['delta']);
                $ratingA[$key] = $value['rating'];
                $cuttingA[$key] = count($value['cutting']);
            }
            array_multisort($deltaA, SORT_ASC, $ratingA, SORT_ASC, $cuttingA, SORT_ASC, $tempCutting);
            $count = count($tempCutting);
            $tempToCuttings = $tempCutting;
            $tempCutting = array();
            for ($i = 1; $i <= $count; $i++) {
                $tempCutting[$i] = $tempToCuttings[$i - 1];
            }
            $this->CuttingVariantsUnmodified = $tempCutting;
            $CuttingVariants = array();
            $this->VariantCutting = 0;
            if (is_array($this->VariantUmodified)) {
                foreach ($this->CuttingVariantsUnmodified as $key => $val) {
                    $arr1 = $val['cutting'];
                    $arr2 = $this->VariantUmodified;                    
                    sort($arr1);
                    sort($arr2);
                    for ($i = 0; $i < count($arr1); $i++) {
                        if (!isset($arr2[$i]) || (isset($arr2[$i]) && ($arr2[$i] != $arr1[$i]))) {
                            continue 2;
                        }
                    }
                    $this->VariantCutting = $key;
                    
                    //если меняли панели местами вручную на форме
                    $count = 1;
                    foreach ($this->VariantUmodified as $size) {
                        $this->CuttingVariantsUnmodified[$key]['cutting'][$count] = $size;
                        $count++;
                    }
                    
                    break;
                }
            }
            //Номер оптимального варианта                    
            $CuttingOptimalVariant = 1;
            $this->ShieldCuttingComplete = 0;
            //Необходимо выполнить обрезку нижней и/или верхний панелей
            foreach ($this->CuttingVariantsUnmodified as $key => $value) {
                $DeltaUnmodified = $this->CuttingVariantsUnmodified[$key]['delta'];
                //Параметры для вызова алгоритма
                $VariantUnmodified = $this->CuttingVariantsUnmodified[$key]['cutting'];
                //$DeltaUnmodified = $this->getDelta($VariantUnmodified, $ShieldHeight, $this->ShieldPanelPadding);
                //list($variant, $delta, $PanelIsCut) = $this->pruningPanels($VariantUnmodified, $DeltaUnmodified);                                  
                
                list($variant1, $delta1, $PanelIsCut1, $Result1) = $this->pruningPanels($VariantUnmodified, $DeltaUnmodified, 0);
                foreach ($PanelIsCut1 as $keyPanelIsCut1 => $valuePanelIsCut1) {
                    $PanelIsCutTop1 = $PanelIsCut1[0];
                    $PanelIsCutBottom1 = $PanelIsCut1[1];
                }
                
                $variant = $variant1;
                $delta = $delta1;
                $PanelIsCut = $PanelIsCut1;
                
                if (!$this->Result) {
                    //Не удалось выполнить обрезку для текущего оптимального варианта 
                    if ($key == $CuttingOptimalVariant) {
                        //Сделать следующий вариант оптимальным, еслии такой вариант есть в списке доступных
                        if (array_key_exists($CuttingOptimalVariant + 1, $this->CuttingVariantsUnmodified))
                            $CuttingOptimalVariant = $CuttingOptimalVariant + 1;
                    }
                    //Не удалось произвести обрезку для выбранного пользователем варианта
                    if ($key == $this->VariantCutting) {
                        //Сбросить выбранный пользователем вариант
                        $this->VariantCutting = 0;
                    }
                } else {
                    $this->ShieldCuttingComplete = 1;
                }
                $CuttingVariants[$key]['delta'] = $delta;
                $CuttingVariants[$key]['rating'] = $this->CuttingVariantsUnmodified[$key]['rating'];
                $CuttingVariants[$key]['cutting'] = $variant;
                $CuttingVariants[$key]['PanelIsCut'] = $PanelIsCut;
            }
            //завершить выполнение модуля, если не найдены варианты раскроя
            if (!$this->ShieldCuttingComplete) {
                //$this->errorArray = "Раскрой не возможен";
                //return true;
            }
            $this->PanelCuttingMode = 0;
            $this->CuttingVariantsUnmodifieds = $this->CuttingVariantsUnmodified;
            if ($this->VariantCutting == 0) {
                $number = $this->findVariantCuttingFromDESC($CuttingOptimalVariant);
            } else {
                if ($this->VariantCutting < $CuttingOptimalVariant) {
                    $number = $CuttingOptimalVariant;
                } else {
                    $number = $this->VariantCutting;
                }
            }
            $this->VariantCutting = $number;
            //Для панорамных панелей
            } else {
                $this->panoramicCutting();
                
            }
            //В. Расчет выходных параметров
            if (sizeof($CuttingVariants) > 0 || $this->PanoramicPanel) {
                $typeSize = array();
                if (!$this->PanoramicPanel) {
                    $this->ShieldPanels = $CuttingVariants[$number]['cutting'];
                    $this->ShieldWholePanels = $this->CuttingVariantsUnmodified[$number]['cutting'];
                    $this->ShieldPanelCount = count($this->ShieldPanels);

                    /**
                     * Функционал расчёта ShieldNumberOfPacks перенесен из ShieldMC
                     */
                    $model = ShieldModel::model()->find(array(
                        'condition' => 'id=:id',
                        'params' => array(
                            ':id' => $this->CurrentPanelType,
                        )
                    ));
                    $modelShieldGroup = ShieldGroupProductParametersModel::model()->find(array(
                        'with' => array('shieldGroupManyProductParameters' => array(
                                'alias' => 'm',
                                'condition' => 'm.product_id=:pid1 OR m.product_id=:pid2',
                            )),
                        'condition' => 't.shield_group_id=:shield_group_id',
                        'params' => array(
                            ':shield_group_id' => $model->shield_group_id,
                            ':pid1' => Yii::app()->container->productId,
                            ':pid2' => ProductModel::ID_ALL_PRODUCT,
                        )
                    ));
                    $formula = new FormulaHelper();
                    Yii::app()->container->ShieldPanelCount = $this->ShieldPanelCount;
                    $this->ShieldNumberOfPacks = $formula->calculation($modelShieldGroup->packaging_place, 1);
                    $this->ShieldTopPanelIsCut = $CuttingVariants[$number]['PanelIsCut'][0];
                    $this->ShieldBottomPanelIsCut = $CuttingVariants[$number]['PanelIsCut'][1];
                    
                    //Начальные значения элементов массива
                    foreach ($this->ShieldPanelSizes as $key => $value) {
                        $typeSize[$this->ShieldPanelSizes[$key]] = 0;
                    }                    
                } else {
                    //Необходимо для правильной работы использующих данные переменные алгоритмов                                  
                    $this->ShieldPanels = $this->PanPanels;
                    $this->ShieldWholePanels = $this->PanPanels;
                    $this->ShieldPanelCount = $this->PanPanelCount;
                    
                    $ShieldWidth = Yii::app()->container->ShieldWidth;
                    if ($ShieldWidth >= 4000) {
                        $this->ShieldNumberOfPacks = ceil(($this->ShieldPanelCount + $this->SandBottomCount + $this->SandTopCount )/5);
                    }else{
                        $this->ShieldNumberOfPacks = ceil(($this->ShieldPanelCount + $this->SandBottomCount + $this->SandTopCount )/6);
                    }
                    if (!count($this->ShieldWholePanels)) {
                        $this->errorArray = Yii::t('steps', "Раскрой не удался!");
                        return true;
                    }
                    $typeSize = array();
                    //Считаем количество панелей с данным типоразмером, кроме первой и последней
                    for ($in = 1; $in <= $this->ShieldPanelCount; $in++) {
                        $typeSize[$this->ShieldWholePanels[$in]] = 0;
                    }
                    
                }
                
                //Считаем количество панелей с данным типоразмером, кроме первой и последней
                for ($in = 2; $in < $this->ShieldPanelCount; $in++) {
                    $typeSize[$this->ShieldWholePanels[$in]] = $typeSize[$this->ShieldWholePanels[$in]] + 1;
                }
                $this->ShieldNumberOfPanels = $typeSize;    
                
                $this->ShieldTopPanel = $this->ShieldPanels[1];
                $this->ShieldBottomPanel = $this->ShieldPanels[$this->ShieldPanelCount];
                
                
                $this->ShieldWholeTopPanel = $this->ShieldWholePanels[1];
                $this->ShieldWholeBottomPanel = $this->ShieldWholePanels[$this->ShieldPanelCount];
                

                //Г. Расчет физической и реальной высоты щита
                //Значние по умолчанию, на случай если тип панелей не установлен
                $this->ShieldRealHeight = $this->ShieldHeight;
                //Начальное значение переменной
                $this->ShieldPhysicalHeight = 0;
                //Находим сумму высот панелей и стыков
                for ($in = 1; $in <= $this->ShieldPanelCount; $in++) {
                    //Высота панели плюс добавочная высота на стык
                    $this->ShieldPhysicalHeight = $this->ShieldPhysicalHeight + $this->ShieldPanels[$in];
                }
                //Стыков на один меньше, чем панелей
                $this->ShieldPhysicalHeight = $this->ShieldPhysicalHeight + $this->ShieldPanelPadding * ($this->ShieldPanelCount - 1);
                //Размер щита без учета профилей
                $this->ShieldDrillingHeight = $this->ShieldPhysicalHeight;
                //Для панорамной панели ShieldPanels содержит только высоту панорамных - нужно добавить сэнвичи
                if ($this->PanoramicPanel) {
                    $this->ShieldDrillingHeight = $this->ShieldDrillingHeight + $this->SandTopHeight + $this->SandBottomHeight;
                }
                $TempDrillingHeight = $this->ShieldDrillingHeight;
                //Перерасчет профилей с учетом данных об обрезке панелей    H226    
                //Вызвать модуль "Расчет профилей" (ProfileCalculation)
                Yii::import("calculation.ProfileCalculationMC.ProfileCalculationMC");
                $Module = new ProfileCalculationMC();
                $Module->key = $this->key;
                $Module->fillVariables();
                $Module->Fill();
                $Module->ShieldTopPanelIsCut = $this->ShieldTopPanelIsCut;
                $Module->ShieldBottomPanelIsCut = $this->ShieldBottomPanelIsCut;
                $Module->Algorithm();
                $Module->Output();
                $this->ShieldTopProfileSize = $Module->ShieldTopProfileSize;
                $this->ShieldBottomProfileSize = $Module->ShieldBottomProfileSize; 
                $this->DrawingTopProfileSize = $Module->DrawingTopProfileSize; 
                $this->DrawingBottomProfileSize = $Module->DrawingBottomProfileSize; 
                
                //Добавляем высоту профилей к высоте щита
                $this->ShieldPhysicalHeight = $this->ShieldPhysicalHeight + $this->DrawingTopProfileSize + $this->DrawingBottomProfileSize;
                if ($this->PanoramicPanel) {
                    $this->ShieldPhysicalHeight += $this->SandTopHeight + $this->SandBottomHeight;
                }
                //Реальная ширина не задана
                $this->ShieldRealWidth = null;
                //Учитываем только стыки
                //$this->ShieldRealHeight = $this->ShieldPhysicalHeight;
                $this->ShieldRealHeight = $this->ShieldHeight;


                //Д. Формирование строки раскроя
                //Изначально пустая строка
                $this->ShieldCuttingAsString = '';
                $this->ShieldCuttingAsStringPanelsOnly = '';
                //Необходимо для группировки панелей с одинаковыми размерами
                $previousPanel = 0;
                $count = 0;
////////////////////////////////////////////////////////////////////////////////
                //Сначала перечисляем все панели
                for ($N = 1; $N <= $this->ShieldPanelCount; $N++) {
                    //Типоразмер этой панели тот же, что и у предыдущих
                    if ($previousPanel == $this->ShieldPanels[$N]) {
                        //Просто увеличиваем счетчик панелей
                        $count = $count + 1;
                        //Это первая панель с таким типоразмером
                    } else {
                        //Это не самая первая панель - самую первую панель необходимо просто запомнить
                        if ($previousPanel > 0) {
                            //Добавить типоразмер к строке
                            $this->ShieldCuttingAsString = $this->ShieldCuttingAsString . $previousPanel;
                            //В группе было несколько панелей
                            if ($count > 1) {
                                //Добавить информацию о числе панелей в группе
                                $this->ShieldCuttingAsString = $this->ShieldCuttingAsString . 'x' . $count;
                            }
                            //Элементы разделены знаком "+"
                            $this->ShieldCuttingAsString = $this->ShieldCuttingAsString . '+';
                        }
                        //Сбрасываем счетчик
                        $count = 1;
                        //Запоминаем новый типоразмер панели
                        $previousPanel = $this->ShieldPanels[$N];
                    }
                }
                //Для комбинированного панорамного щита проверяем наличие сендвич панелей сверху
                for ($N = 1; $N <= $this->SandTopCount; $N++) {
                    //Типоразмер этой панели тот же, что и у предыдущих
                    if ($previousPanel == $this->SandTopPanels[$N]) {
                        //Просто увеличиваем счетчик панелей
                        $count = $count + 1;
                        //Это первая панель с таким типоразмером
                    } else {
                        //Это не самая первая панель - самую первую панель необходимо просто запомнить
                        if ($previousPanel > 0) {
                            //Добавить типоразмер к строке
                            $this->ShieldCuttingAsString = $this->ShieldCuttingAsString . $previousPanel;
                            //В группе было несколько панелей
                            if ($count > 1) {
                                //Добавить информацию о числе панелей в группе
                                $this->ShieldCuttingAsString = $this->ShieldCuttingAsString . 'x' . $count;
                            }
                            //Элементы разделены знаком "+"
                            $this->ShieldCuttingAsString = $this->ShieldCuttingAsString . '+';
                        }
                        //Сбрасываем счетчик
                        $count = 1;
                        //Запоминаем новый типоразмер панели
                        $previousPanel = $this->SandTopPanels[$N];
                    }
                }
                //Для комбинированного панорамного щита проверяем наличие сендвич панелей снизу
                for ($N = 1; $N <= $this->SandBottomCount; $N++) {
                    //Типоразмер этой панели тот же, что и у предыдущих
                    if ($previousPanel == $this->SandBottomPanels[$N]) {
                        //Просто увеличиваем счетчик панелей
                        $count = $count + 1;
                        //Это первая панель с таким типоразмером
                    } else {
                        //Это не самая первая панель - самую первую панель необходимо просто запомнить
                        if ($previousPanel > 0) {
                            //Добавить типоразмер к строке
                            $this->ShieldCuttingAsString = $this->ShieldCuttingAsString . $previousPanel;
                            //В группе было несколько панелей
                            if ($count > 1) {
                                //Добавить информацию о числе панелей в группе
                                $this->ShieldCuttingAsString = $this->ShieldCuttingAsString . 'x' . $count;
                            }
                            //Элементы разделены знаком "+"
                            $this->ShieldCuttingAsString = $this->ShieldCuttingAsString . '+';
                        }
                        //Сбрасываем счетчик
                        $count = 1;
                        //Запоминаем новый типоразмер панели
                        $previousPanel = $this->SandBottomPanels[$N];
                    }
                }
                //Повторяем все тоже самое для последней группы панелей, которая не отработана в цикле
                if ($previousPanel > 0) {
                    //Добавить типоразмер к строке
                    $this->ShieldCuttingAsString = $this->ShieldCuttingAsString . $previousPanel;
                    //В группе было несколько панелей
                    if ($count > 1) {
                        //Добавить информацию о числе панелей в группе
                        $this->ShieldCuttingAsString = $this->ShieldCuttingAsString . 'x' . $count;
                    }
                    //Элементы разделены знаком "+"
                    $this->ShieldCuttingAsString = $this->ShieldCuttingAsString . '+';
                }
                //Необходимо для группировки панелей с одинаковыми размерами
                $previousPanel = 0;
                $count = 0;
////////////////////////////////////////////////////////////////////////////////
                //Сначала перечисляем все панели
                for ($N = $this->ShieldPanelCount; $N >= 1; $N--) {
                    //Типоразмер этой панели тот же, что и у предыдущих
                    if ($previousPanel == $this->ShieldPanels[$N]) {
                        //Просто увеличиваем счетчик панелей
                        $count = $count + 1;
                        //Это первая панель с таким типоразмером
                    } else {
                        //Это не самая первая панель - самую первую панель необходимо просто запомнить
                        if ($previousPanel > 0) {
                            //Добавить типоразмер к строке
                            $this->ShieldCuttingAsStringPanelsOnly = $this->ShieldCuttingAsStringPanelsOnly . $previousPanel;
                            //В группе было несколько панелей
                            if ($count > 1) {
                                //Добавить информацию о числе панелей в группе
                                $this->ShieldCuttingAsStringPanelsOnly = $this->ShieldCuttingAsStringPanelsOnly . 'x' . $count;
                            }
                            //Элементы разделены запятыми
                            $this->ShieldCuttingAsStringPanelsOnly = $this->ShieldCuttingAsStringPanelsOnly . ',';
                        }
                        //Сбрасываем счетчик
                        $count = 1;
                        //Запоминаем новый типоразмер панели
                        $previousPanel = $this->ShieldPanels[$N];
                    }
                }
                //Для комбинированного панорамного щита проверяем наличие сендвич панелей сверху
                for ($N = $this->SandTopCount; $N >= 1; $N--) {
                    //Типоразмер этой панели тот же, что и у предыдущих
                    if ($previousPanel == $this->SandTopPanels[$N]) {
                        //Просто увеличиваем счетчик панелей
                        $count = $count + 1;
                        //Это первая панель с таким типоразмером
                    } else {
                        //Это не самая первая панель - самую первую панель необходимо просто запомнить
                        if ($previousPanel > 0) {
                            //Добавить типоразмер к строке
                            $this->ShieldCuttingAsStringPanelsOnly = $this->ShieldCuttingAsStringPanelsOnly . $previousPanel;
                            //В группе было несколько панелей
                            if ($count > 1) {
                                //Добавить информацию о числе панелей в группе
                                $this->ShieldCuttingAsStringPanelsOnly = $this->ShieldCuttingAsStringPanelsOnly . 'x' . $count;
                            }
                            //Элементы разделены запятыми
                            $this->ShieldCuttingAsStringPanelsOnly = $this->ShieldCuttingAsStringPanelsOnly . ',';
                        }
                        //Сбрасываем счетчик
                        $count = 1;
                        //Запоминаем новый типоразмер панели
                        $previousPanel = $this->SandTopPanels[$N];
                    }
                }
                //Для комбинированного панорамного щита проверяем наличие сендвич панелей снизу
                for ($N = $this->SandBottomCount; $N >= 1; $N--) {
                    //Типоразмер этой панели тот же, что и у предыдущих
                    if ($previousPanel == $this->SandBottomPanels[$N]) {
                        //Просто увеличиваем счетчик панелей
                        $count = $count + 1;
                        //Это первая панель с таким типоразмером
                    } else {
                        //Это не самая первая панель - самую первую панель необходимо просто запомнить
                        if ($previousPanel > 0) {
                            //Добавить типоразмер к строке
                            $this->ShieldCuttingAsStringPanelsOnly = $this->ShieldCuttingAsStringPanelsOnly . $previousPanel;
                            //В группе было несколько панелей
                            if ($count > 1) {
                                //Добавить информацию о числе панелей в группе
                                $this->ShieldCuttingAsStringPanelsOnly = $this->ShieldCuttingAsStringPanelsOnly . 'x' . $count;
                            }
                            //Элементы разделены запятыми
                            $this->ShieldCuttingAsStringPanelsOnly = $this->ShieldCuttingAsStringPanelsOnly . ',';
                        }
                        //Сбрасываем счетчик
                        $count = 1;
                        //Запоминаем новый типоразмер панели
                        $previousPanel = $this->SandBottomPanels[$N];
                    }
                }
                //Повторяем все тоже самое для последней группы панелей, которая не отработана в цикле
                if ($previousPanel > 0) {
                    //Добавить типоразмер к строке
                    $this->ShieldCuttingAsStringPanelsOnly = $this->ShieldCuttingAsStringPanelsOnly . $previousPanel;
                    //В группе было несколько панелей
                    if ($count > 1) {
                        //Добавить информацию о числе панелей в группе
                        $this->ShieldCuttingAsStringPanelsOnly = $this->ShieldCuttingAsStringPanelsOnly . 'x' . $count;
                    }
                }
////////////////////////////////////////////////////////////////////////////////
//                $tempArray = array();
//                for ($in = $this->ShieldPanelCount; $in >= 1; $in--) {
//                    if (isset($tempArray[$this->ShieldPanels[$in]])) {
//                        $tempArray[$this->ShieldPanels[$in]] += 1;
//                    } else {
//                        $tempArray[$this->ShieldPanels[$in]] = 1;
//                    }
//                }
//                foreach ($tempArray as $keyN => $valueN) {
//                    if ($valueN == 1) {
//                        $this->ShieldCuttingAsString .= $keyN . ",";
//                    } else {
//                        $this->ShieldCuttingAsString .= $keyN . "x" . $valueN . ",";
//                    }
//                }
////////////////////////////////////////////////////////////////////////////////
                //Есть зазор между панелями
                if ($this->ShieldPanelPadding > 0) {
                    //Добавляем информацию о ширине и числе зазоров
                    $this->ShieldCuttingAsString = $this->ShieldCuttingAsString . $this->ShieldPanelPadding . '*' . ($this->ShieldPanelCount - 1) . '+';
                }
                //Есть зазор между панелями (рассматриваем комбинированный панорамный щит)
                if ($this->PanPanelPadding > 0 && ($this->SandTopCount > 0 || $this->SandBottomCount > 0)) {
                   //Добавляем информацию о ширине и числе зазоров
                    $this->ShieldCuttingAsString = $this->ShieldCuttingAsString . $this->PanPanelPadding . '*' . ($this->SandTopCount + $this->SandBottomCount) . '+'; 
                }
                //Добавить информацию о ширине верхнего профиля
                $this->ShieldCuttingAsString = $this->ShieldCuttingAsString . $this->DrawingTopProfileSize . "+";
                //Добавить информацию о ширине нижнего профиля
                $this->ShieldCuttingAsString = $this->ShieldCuttingAsString . $this->DrawingBottomProfileSize;
                //Минимальная высота щита
                $ShieldMinHeight = $this->Hh + $this->ShieldMinTolerance + $this->DrawingTopProfileSize + $this->DrawingBottomProfileSize;
                if ($this->ShieldTopPanelIsCut == 1) {
                    $ShieldMinHeight = $ShieldMinHeight + $this->ShieldTopCutDecrease;
                }
                if ($this->ShieldBottomPanelIsCut == 1) {
                    $ShieldMinHeight = $ShieldMinHeight + $this->ShieldBottomCutDecrease;
                }
                //Максимальная высота щита
                $ShieldMaxHeight = $this->Hh + $this->ShieldMaxTolerance + $this->DrawingTopProfileSize + $this->DrawingBottomProfileSize;
                if ($this->ShieldTopPanelIsCut == 1) {
                    $ShieldMaxHeight = $ShieldMaxHeight + $this->ShieldTopCutDecrease;
                }
                if ($this->ShieldBottomPanelIsCut == 1) {
                    $ShieldMaxHeight = $ShieldMaxHeight + $this->ShieldBottomCutDecrease;
                }
                //Отклонение высоты щита от минимально и максимально допустимой
                $ShieldMinDelta = $ShieldMinHeight - $this->ShieldRealHeight;
                $ShieldMaxDelta = $ShieldMaxHeight - $this->ShieldRealHeight;
                //Отклонение не может быть больше +-5мм
                if ($ShieldMinDelta > -5) {
                    $ShieldMinDelta = -5;
                }
                if ($ShieldMaxDelta < 5) {
                    $ShieldMaxDelta = 5;
                }
                //Добавим информацию об отклонении высоты щита от предельных значений
                $this->ShieldCuttingAsString = $this->ShieldCuttingAsString . '(' . $ShieldMinDelta . ';' . $ShieldMaxDelta . ')';

                //E. Расчет координат стыков панелей
                //Координата первого стыка, скорректированная с учетом реальной высоты щита (с учетом профилей) и размера петли
                $Y = $this->ShieldTopPanel + $ShieldHeight - $TempDrillingHeight;
                $coordinates = array();
                for ($n = 1; $n <= $this->ShieldPanelCount - 1; $n++) {
                    $coordinates[$n] = $n;
                }
                //Стыков на один меньше, чем панелей
                $this->ShieldPaddingCoordinates = $coordinates;
                $coordinates = $this->ShieldPaddingCoordinates;
                //Мы уже вычислили координату первого стыка
                for ($n = 2; $n <= $this->ShieldPanelCount; $n++) {
                    //Запомним предыдущее значение координаты стыка
                    $coordinates[$n - 1] = $Y + 5;
                    //Вычислим новое - добавим высоту панели и зазор между панелями
                    $Y = $Y + $this->ShieldPanels[$n] + $this->ShieldPanelPadding;
                }
                
                for ($n = 1; $n <= $this->ShieldPanelCount; $n++) {
                    $ShieldPanelsLength[$n] = $this->ShieldWidth - 4;
                    if ($this->VerticalPanel) {
                        $ShieldPanelsLength[$n] = $this->ShieldHeight - 4;
                    }
                }
                
                $this->ShieldPanelsLength = $ShieldPanelsLength;
                $this->ShieldPaddingCoordinates = $coordinates;
            }
        }
        $this->ShieldPanelSizen = $this->ShieldPanelSizes;
        $this->ChangeShieldPanels = 0;
        if ($this->ShieldPanels != Yii::app()->container->ShieldPanels) {
            $this->ChangeShieldPanels = 1;
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
        return 'Оптимальный раскрой панелей';
    }

    /**
     * функция автоподстановки
     *
     * @return bool
     */
    protected function autoExtend()
    {
        $ShieldHeight = $this->ShieldHeight;
        if ($this->VerticalPanel)
            $ShieldHeight = $this->ShieldWidth;
        //вычисление количества вариантов раскроя
        if (($this->ShieldAutoExtendEnabledSelected == 0) || ($this->ShieldAutoExtendCount == 0)) {
            $NumberOfVariants = 1;
        } else {
            $NumberOfVariants = $this->ShieldAutoExtendCount;
        }
        //    $strExtend = $this->ShieldTopProfile . '/' . $this->ShieldBottomProfile;
        //    array_unshift($ShieldAutoExtendVariants,$strExtend);
        for ($count = 1; $count <= $NumberOfVariants; $count++) {
            //Подготовка к первому вызову алгоритма оптимального раскроя
            $this->CuttingVariantsUnmodified = array();
            if ($this->ShieldAutoExtendEnabledSelected == 1) {
                
            }
            $CompletedHeight = 0;
            $CurrentVariant = array();
            $CuttingTopEnabled = 1;
            $CuttingBottomEnabled = 1;
            $PrefferedSize = 0;
            $counts = count($this->ShieldPanelSizes) - 1;
            if ($this->OnlyOneSizeSelected) {
                for ($i = $counts; $i >= 0; $i--) {
                    $PrefferedSize = $i;
                    //А. Добавление нового элемента в вариант раскроя
                    //$this->optimalCutting($CompletedHeight, $CurrentVariant, $PrefferedSize, $CuttingTopEnabled, $CuttingBottomEnabled);
                    $this->optimalCutting($CompletedHeight, $CurrentVariant, $PrefferedSize, $CuttingTopEnabled, $CuttingBottomEnabled, $this->maxexcess, $ShieldHeight);
                }
            } else {
                //А. Добавление нового элемента в вариант раскроя
                //$this->optimalCutting($CompletedHeight, $CurrentVariant, $PrefferedSize, $CuttingTopEnabled, $CuttingBottomEnabled);
                $this->optimalCutting($CompletedHeight, $CurrentVariant, $PrefferedSize, $CuttingTopEnabled, $CuttingBottomEnabled, $this->maxexcess, $ShieldHeight);
                if (count($this->CuttingVariantsUnmodified) > 0) {
                    return true;
                }
            }
        }
    }

    /**
     * функия оптимального раскроя
     *
     * @param $CompletedHeight
     * @param $CurrentVariant
     * @param $PrefferedSizer
     * @param $CuttingTopEnabled
     * @param $CuttingBottomEnabled
     */
    protected function optimalCutting($CompletedHeight, $CurrentVariant, $PrefferedSizer, $CuttingTopEnabled, $CuttingBottomEnabled, $MaxExceedingShield, $Hh)
    {
        $ShieldHeight = $this->ShieldHeight;
        if ($this->VerticalPanel)
            $ShieldHeight = $this->ShieldWidth;
        $this->countIteration++;   //для дебага(подсчета количества вызова ф-и)
        
        //Это первая итерация рекурсии?
        if ((count($CurrentVariant)) == 0) {
            //Добавить первый элемент
            $CurrentVariant[1] = 0;
            //Мы пока не набрали никакой высоты щита. На случай, если это не сделала вызывающая процедура
            $CompletedHeight = 0;
        } else {
            //Добавляем следующий элемента
            $CurrentVariant[] = 0;
        }
        //Глубина рекурсии - число элементов в текущем варианте раскроя
        $Depth = count($CurrentVariant);

        //Б. Описание цикла проверки типоразмеров
        //Проверяем все типоразмеры по порядку
        foreach ($this->ShieldPanelSizes as $key => $value) {
            if ((($this->OnlyOneSizeSelected == 1) && ($Depth > 1)) || ($this->OnlyOneSizeSelected == 0) || ($Depth == 1)) {
                /**
                * @todo Убрал перебор всех панелей для случая, когда выбран "Один типоразмер".
                *       Это позволило снизить количество циклов перебора на 2 порядка.
                *       Также когда не выбран "Один типоразмер"
                *       Уточнить правильность логики.
                */
                if ($this->OnlyOneSizeSelected 
                    && $value != $this->ShieldPanelSizes[$PrefferedSizer]
                ) {
                    continue;
                } else if (isset($CurrentVariant[$Depth - 1]) && $value > $CurrentVariant[$Depth - 1]) {
                    continue;
                } else {
                    $panSize = $key;
                }
//                if ($this->OnlyOneSizeSelected) {
//                    $panSize = $PrefferedSizer;
//                }  else {
//                    $panSize = $key;
//                }
                //Выбрать N-й типоразмер в качестве текущего
                $CurrentVariant[$Depth] = $this->ShieldPanelSizes[$panSize];
                //Это первая итерация(работаем с нижней панелью) и текущий типоразмер превышает допустимую величину
                if (($Depth == 1) && ($this->ShieldMaxBottom > 0) && ($CurrentVariant[$Depth] > $this->ShieldMaxBottom)) {
                    //Уменьшить текущий типоразмер до допустимой величины
                    $CurrentVariant[$Depth] = $this->ShieldMaxBottom;
                }

                //В. Описание алгоритма проверки типоразмера
                //Минимально и максимально допустимые высота щита
                $ShieldMinHeight = $ShieldHeight + $this->ShieldMinTolerance;
                $ShieldMaxHeight = $ShieldHeight + $this->ShieldMaxTolerance;
                //Есть ограничение на размер верхней панели
                if ($this->ShieldMaxTop != 0) {
                    //Выбираем его в качестве типоразмера текущей панели
                    $CurrentVariant[$Depth] = $this->ShieldMaxTop;
                    //Превышение минимально допустимого размера щита. Нужно для списка вариантов раскроя.
                    $Delta = $CompletedHeight + $CurrentVariant[$Depth] - $ShieldMinHeight - $this->ShieldBottomCutDecrease - $this->ShieldTopCutDecrease;
                    //Размер верхней панели попадает в диапазон от минимально до максимально допустимого
                    if (($ShieldMinHeight <= $CompletedHeight + $this->ShieldMaxTop) && ($ShieldMaxHeight >= $CompletedHeight + $this->ShieldMaxTop)) {
                        //Без подрезки
                        $this->Result = 1;
                        //Смотри пункт Д. (Вариант, Дэльта, Оценка). Обрезов не было.
                        
                        //$this->addVariant($CurrentVariant, $Delta, 0);
                        $this->addVariant($CurrentVariant, $ShieldMinHeight - $Hh, 0);
                        
                        //Еще остается свободное место - пробуем добавить еще одну панель
                    //} elseif ($CompletedHeight + $this->ShieldMaxTop - $this->ShieldBottomCutDecrease - $this->ShieldTopCutDecrease < $ShieldMinHeight) {
                    //    //Рекурсивное добавление еще одной панели. Увеличиваем высоту щита на текущий типоразмер + отступ.
                    //    $this->optimalCutting($CompletedHeight + $CurrentVariant[$Depth] + $this->ShieldPanelPadding, $CurrentVariant, $PrefferedSizer, $CuttingTopEnabled, $CuttingBottomEnabled, $this->maxexcess, $this->Hh);
                    //    //Разрешена отрезка паза и при этом высота той части нижней панели, которую необходимо отрезать, попадает в диапазон от минимально(отрезаем ShieldMinBottomCut) до максимально возможной(оставляем от панели только ShieldMinBottom)величины отреза снизу.
                    } elseif (($CuttingBottomEnabled == 1) && (($CompletedHeight - $CurrentVariant[1] + $this->ShieldMinBottom - $this->ShieldBottomCutDecrease - $this->ShieldTopCutDecrease) + $this->ShieldMaxTop <= $ShieldMaxHeight) && ($CompletedHeight - $this->ShieldBottomCutDecrease - $this->ShieldTopCutDecrease - $this->ShieldMinBottomCut + $this->ShieldMaxTop >= $ShieldMaxHeight)) {
                        //Смотри пункт Д. (Вариант, Дэльта, Оценка). Был один обрез.
                        //$this->addVariant($CurrentVariant, $Delta, 1);
                        $this->addVariant($CurrentVariant, $CompletedHeight + $CurrentVariant[$Depth] - $Hh, 2);
                        //Разрешена отрезка шипа и при этом высота той части верхней панели, которую необходимо отрезать, попадает в диапазон от минимально(отрезаем ShieldMinTopCut) до максимально возможной(оставляем от панели только ShieldMinTop)величины отреза сверху.
                    } elseif (($CuttingTopEnabled == 1) && ($CompletedHeight - $this->ShieldBottomCutDecrease - $this->ShieldTopCutDecrease + $this->ShieldMinTop <= $ShieldMaxHeight) && ($CompletedHeight - $this->ShieldBottomCutDecrease - $this->ShieldTopCutDecrease - $this->ShieldMinTopCut + $this->ShieldMaxTop >= $ShieldMaxHeight)) {
                        //Смотри пункт Д. (Вариант, Дэльта, Оценка). Был один обрез.
                        //$this->addVariant($CurrentVariant, $Delta, 1);
                        $this->addVariant($CurrentVariant, $CompletedHeight + $CurrentVariant[$Depth] - $Hh, 2);
                        //Разрешена отрезка паза и шипа и при этом суммарная высота тех частей нижней и верхней панелей, которую необходимо отрезать, попадает в диапазон от минимально(отрезаем ShieldMinBottomCut и ShieldMinTopCut) до максимально возможной(оставляем от панелей только ShieldMinBottom и ShieldMinTop)величины отреза снизу и сверху.
                    } elseif (($CuttingBottomEnabled == 1) && ($CuttingTopEnabled == 1) && ($CompletedHeight - $this->ShieldBottomCutDecrease - $this->ShieldTopCutDecrease - $CurrentVariant[1] + $this->ShieldMinBottom + $this->ShieldMinTop <= $ShieldMaxHeight) && ($CompletedHeight - $this->ShieldBottomCutDecrease - $this->ShieldTopCutDecrease - $this->ShieldMinTopCut + $this->ShieldMaxTop - $this->ShieldMinBottomCut >= $ShieldMaxHeight)) {
                        //Смотри пункт Д. (Вариант, Дэльта, Оценка). Было два обреза.
                        //$this->addVariant($CurrentVariant, $Delta, 2);
                        $this->addVariant($CurrentVariant, $CompletedHeight + $CurrentVariant[$Depth] - $Hh, 3);
                    } elseif ($CompletedHeight + $this->ShieldMaxTop - $this->ShieldBottomCutDecrease - $this->ShieldTopCutDecrease < $ShieldMinHeight) {
                        //Рекурсивное добавление еще одной панели. Увеличиваем высоту щита на текущий типоразмер + отступ.
                        $this->optimalCutting($CompletedHeight + $CurrentVariant[$Depth] + $this->ShieldPanelPadding, $CurrentVariant, $PrefferedSizer, $CuttingTopEnabled, $CuttingBottomEnabled, $this->maxexcess, $Hh);
                        //Разрешена отрезка паза и при этом высота той части нижней панели, которую необходимо отрезать, попадает в диапазон от минимально(отрезаем ShieldMinBottomCut) до максимально возможной(оставляем от панели только ShieldMinBottom)величины отреза снизу.
                    }
                } else {
                    //Размер верхней панели попадает в диапазон от минимально до максимально допустимого
                    if (($ShieldMinHeight <= $CompletedHeight + $CurrentVariant[$Depth]) && ($ShieldMaxHeight + $MaxExceedingShield >= $CompletedHeight + $CurrentVariant[$Depth])) {
                        //Без подрезки
                        $this->Result = 1;
                        $Delta = $ShieldMinHeight - $Hh;
                        $this->addVariant($CurrentVariant, $ShieldMinHeight - $Hh, 0);
                    //Еще остается свободное место - пробуем добавить еще одну панель
                    //ПазРежется    
                    } elseif (($CuttingBottomEnabled == 1) && (($CompletedHeight - $this->ShieldBottomCutDecrease - $CurrentVariant[1] + $this->ShieldMinBottom) + $CurrentVariant[$Depth] <= $ShieldMaxHeight) && ($CompletedHeight - $this->ShieldBottomCutDecrease - $this->ShieldMinBottomCut + $CurrentVariant[$Depth] > $ShieldMinHeight)) {
                        //Смотри пункт Д. (Вариант, Дэльта, Оценка). Был один обрез.
                        $this->addVariant($CurrentVariant, $CompletedHeight + $CurrentVariant[$Depth] - $Hh, 2);
                        if (
                                $this->ShieldPanelsWithInfill && 
                                $this->TypeF == "Алюминиевый" &&
                                ($CompletedHeight + $CurrentVariant[$Depth] - $this->ShieldTopCutDecrease + $this->ShieldMinTop <= $ShieldMaxHeight)
                        ) {
                            $this->optimalCutting($CompletedHeight + $CurrentVariant[$Depth] + $this->ShieldPanelPadding, $CurrentVariant, $PrefferedSizer, $CuttingTopEnabled, $CuttingBottomEnabled, $this->maxexcess, $Hh);
                        }
                    //Разрешена отрезка шипа и при этом высота той части верхней панели, которую необходимо отрезать, попадает в диапазон от минимально(отрезаем ShieldMinTopCut) до максимально возможной(оставляем от панели только ShieldMinTop)величины отреза сверху.
                    //ШипРежется
                    } elseif (($CuttingTopEnabled == 1) && ($CompletedHeight - $this->ShieldTopCutDecrease + $this->ShieldMinTop <= $ShieldMaxHeight) && ($CompletedHeight - $this->ShieldTopCutDecrease - $this->ShieldMinTopCut + $CurrentVariant[$Depth] > $ShieldMinHeight)) {
                        //Смотри пункт Д. (Вариант, Дэльта, Оценка). Был один обрез.
                        $this->addVariant($CurrentVariant, $CompletedHeight + $CurrentVariant[$Depth] - $Hh, 2);
                    //Разрешена отрезка паза и шипа и при этом суммарная высота тех частей нижней и верхней панелей, которую необходимо отрезать, попадает в диапазон от минимально(отрезаем ShieldMinBottomCut и ShieldMinTopCut) до максимально возможной(оставляем от панелей только ShieldMinBottom и ShieldMinTop)величины отреза снизу и сверху.
                    //ПазРежется
                    } elseif (($CuttingBottomEnabled == 1) && ($CuttingTopEnabled == 1) && (($CompletedHeight - $this->ShieldBottomCutDecrease - $this->ShieldTopCutDecrease - $CurrentVariant[1] + $this->ShieldMinBottom + $this->ShieldMinTop) <= $ShieldMaxHeight) && ($CompletedHeight - $this->ShieldBottomCutDecrease - $this->ShieldTopCutDecrease - $this->ShieldMinTopCut + $CurrentVariant[$Depth] - $this->ShieldMinBottomCut > $ShieldMinHeight)) {
                        //Смотри пункт Д. (Вариант, Дэльта, Оценка). Было два обреза.
                        $this->addVariant($CurrentVariant, $CompletedHeight +  $CurrentVariant[$Depth] - $Hh, 3);
                    //ПазРежется    
                    } elseif (($CuttingBottomEnabled == 1) && (($CompletedHeight - $this->ShieldBottomCutDecrease - $CurrentVariant[1] + $this->ShieldMinBottom) + $CurrentVariant[$Depth] <= $ShieldMaxHeight + $MaxExceedingShield) && ($CompletedHeight - $this->ShieldBottomCutDecrease - $this->ShieldMinBottomCut + $CurrentVariant[$Depth] > $ShieldMinHeight)) {
                        //Смотри пункт Д. (Вариант, Дэльта, Оценка). Был один обрез.
                        $this->addVariant($CurrentVariant, $CurrentVariant[1] - $this->ShieldMinBottom + ($ShieldMaxHeight - $Hh), 1);
                    //Разрешена отрезка шипа и при этом высота той части верхней панели, которую необходимо отрезать, попадает в диапазон от минимально(отрезаем ShieldMinTopCut) до максимально возможной(оставляем от панели только ShieldMinTop)величины отреза сверху.    
                    //ШипРежется
                    } elseif (($CuttingTopEnabled == 1) && ($CompletedHeight - $this->ShieldTopCutDecrease + $this->ShieldMinTop <= $ShieldMaxHeight + $MaxExceedingShield) && ($CompletedHeight - $this->ShieldTopCutDecrease - $this->ShieldMinTopCut + $CurrentVariant[$Depth] > $ShieldMinHeight)) {
                        //Смотри пункт Д. (Вариант, Дэльта, Оценка). Был один обрез.
                        $Delta = $CurrentVariant[$Depth] - $this->ShieldMinTop + ($ShieldMaxHeight - $Hh);
                        $this->addVariant($CurrentVariant, $CurrentVariant[$Depth] - $this->ShieldMinTop + ($ShieldMaxHeight - $Hh), 1);
                    //Разрешена отрезка паза и шипа и при этом суммарная высота тех частей нижней и верхней панелей, которую необходимо отрезать, попадает в диапазон от минимально(отрезаем ShieldMinBottomCut и ShieldMinTopCut) до максимально возможной(оставляем от панелей только ShieldMinBottom и ShieldMinTop)величины отреза снизу и сверху.
                    //ШипРежется    
                    /*
                    } elseif (($CuttingBottomEnabled == 1) && ($CuttingTopEnabled == 1) && (($CompletedHeight - $this->ShieldBottomCutDecrease - $this->ShieldTopCutDecrease - $CurrentVariant[1] + $this->ShieldMinBottom + $this->ShieldMinTop) <= $ShieldMaxHeight) && ($CompletedHeight - $this->ShieldBottomCutDecrease - $this->ShieldTopCutDecrease - $this->ShieldMinTopCut + $CurrentVariant[$Depth] - $this->ShieldMinBottomCut >= $ShieldMinHeight)) {
                        //Смотри пункт Д. (Вариант, Дэльта, Оценка). Было два обреза.
                        $this->addVariant($CurrentVariant, $CompletedHeight +  $CurrentVariant[$Depth] - $Hh, 2);
                    */
                    } elseif ($CompletedHeight + $CurrentVariant[$Depth] < $ShieldMinHeight) {
                        //Рекурсивное добавление еще одной панели. Увеличиваем высоту щита на текущий типоразмер + отступ.
                        $this->optimalCutting($CompletedHeight + $CurrentVariant[$Depth] + $this->ShieldPanelPadding, $CurrentVariant, $PrefferedSizer, $CuttingTopEnabled, $CuttingBottomEnabled, $this->maxexcess, $Hh);
                        //Разрешена отрезка паза и при этом высота той части нижней панели, которую необходимо отрезать, попадает в диапазон от минимально(отрезаем ShieldMinBottomCut) до максимально возможной(оставляем от панели только ShieldMinBottom)величины отреза снизу.
                    }
                }
            }
            //Г. Удаление последнего элемента из варианта раскроя
            //Приводим массив в исходное состояние. Он состоит всего из одного элемента?
            if (count($CurrentVariant) == 1) {
                //Присваиваем высоте щита исходное значение. Для вызывающей процедуры.
                $CompletedHeight = 0;
                //Удаляем последний элемент вместе с массивом.
                $CurrentVariant = array();
            } else {
                //Удаляем последний элемент массива.
                unset($CurrentVariant[count($CurrentVariant)]);
            }
        }
    }

    /**
     * Д. Добавление нового варианта раскроя в список
     *
     * @param mixed $cuttingVariant
     * @param mixed $deltaVariant
     * @param mixed $ratingVariant
     *
     * @return bool
     */
    protected function addVariant($cuttingVariant, $deltaVariant, $ratingVariant)
    {
        //Это первый вариант - дополнительные проверки не требуются
        if (count($this->CuttingVariantsUnmodified) == 0) {
            //Добавляем первый элемент
            $this->CuttingVariantsUnmodified[1]['delta'] = 0;
            $this->CuttingVariantsUnmodified[1]['rating'] = 0;
            $this->CuttingVariantsUnmodified[1]['cutting'] = array();
        } else {
            //Проверяем все варианты раскроя
            for ($count = 1; $count <= count($this->CuttingVariantsUnmodified); $count++) {
                //Текущий вариант раскроя
                $variant = $this->CuttingVariantsUnmodified[$count];
                //Это тот же самый вариант, просто с другим порядком типоразмеров?
                if (($variant['delta'] == $deltaVariant) && ($variant['rating'] == $ratingVariant) && (count($variant['cutting']) == count($cuttingVariant))) {
                    //Просто не будем его добавлять
                    return true;
                }
            }
            //Если предыдущая проверка завершилась успешно, то добавляем новый элемент
            $countNew = $сountVariant = count($this->CuttingVariantsUnmodified) + 1;
            //Увеличить размер массива CurrentVariantUnmodified на 1
            $this->CuttingVariantsUnmodified[$countNew]['delta'] = 0;
            $this->CuttingVariantsUnmodified[$countNew]['rating'] = 0;
            $this->CuttingVariantsUnmodified[$countNew]['cutting'] = array();
        }
        //Индекс последнего элемента
        $сountVariant = count($this->CuttingVariantsUnmodified);
        //Копируем данные текущего варианта расчета в только что добавленный элемент массива ВариантыРаскроя
        $this->CuttingVariantsUnmodified[$сountVariant]['delta'] = $deltaVariant;
        $this->CuttingVariantsUnmodified[$сountVariant]['rating'] = $ratingVariant;
        if ($this->RegionEurope) {
            $this->CuttingVariantsUnmodified[$сountVariant]['cutting'] = $this->reverseArray($cuttingVariant);
        } else {
            $this->CuttingVariantsUnmodified[$сountVariant]['cutting'] = $cuttingVariant;    
        }

        return true;
    }

    /**
     * Функция обрезки панелей
     *
     * 2. Алгоритм "Обрезка панелей"
     *
     * @param $variantM
     * @param $deltaM
     *
     * @return array
     */
    private function pruningPanels($variantM, $deltaM, $maxexcess)
    {
        $ShieldHeight = $this->ShieldHeight;
        if ($this->VerticalPanel)
            $ShieldHeight = $this->ShieldWidth;
        
        if ($this->PanelCuttingMode == 0) {
            $this->PanelCuttingMode = 3;
        }

        //А. Вычисление дэльты
        //Количество панелей
        $counts = count($variantM);
        //Начальное значение переменной
        $delta = 0;
        //Перебираем все панели
        for ($i = 1; $i <= $counts; $i++) {
            //Добавляем следующую панель
            $delta += $variantM[$i] + $this->ShieldPanelPadding;
        }
        //Находим разницу между набраной и необходимой высотой щита
        $delta = $delta - $ShieldHeight - $this->ShieldPanelPadding;
        $delta = $deltaM;

        //Б. Подготовка к расчету
        $tempPrunning = array();
        //Если обрезка на жестко заданное значение не удалась, то будет произведен откат на обычные режимы
        $this->Result = 0;
        $panelMode = $this->PanelCuttingMode;
        //Минимально и максимально допустимые высота щита
        $ShieldMinHeight = $ShieldHeight + $this->ShieldMinTolerance;
        $ShieldMaxHeight = $ShieldHeight + $this->ShieldMaxTolerance;
        //Изначально панели не обрезаны
        $ShieldTopPanelIsCut = 0;
        $ShieldBottomPanelIsCut = 0;
        //Флаги, указывающие на то, что панель уже была обрезана
        $TopPanelAlreadyCut = 0;
        $BottomPanelAlreadyCut = 0;
        //Для работы с вариантом скопируем его в результирующую переменную
        $variant = $variantM;
        //    $delta                  = $deltaM;
        //Понадобится для модификации последней панели
        $count = count($variant);
        //Если максимальные размеры не заданы, то заместим их этим значением
        $MaxPanelSize = max($variant);
        //Необходимо, чтобы избежать постоянных проверок на нулевое значение
        if ($this->ShieldMaxBottom == 0) {
            $CuttingMaxBottom = $MaxPanelSize;
        } else {
            $CuttingMaxBottom = $this->ShieldMaxBottom;
        }
        $CuttingMinBottom = $this->ShieldMinBottom;
        if ($this->ShieldMaxTop == 0) {
            $CuttingMaxTop = $MaxPanelSize;
        } else {
            $CuttingMaxTop = $this->ShieldMaxTop;
        }
        $CuttingMinTop = $this->ShieldMinTop;
        //Проверка на соответствие условиям по максимально допустимым размерам панелей
        if ($variant[1] > $CuttingMaxTop) {
            //Подрезаем панель до максимально допустимого размера
            $delta = $delta - $variant[1] + $CuttingMaxTop;
            $variant[1] = $CuttingMaxTop;
            $ShieldTopPanelIsCut = 1;
            $TopPanelAlreadyCut = 1;
        }
        //Проверка на соответствие условиям по максимально допустимым размерам панелей
        if ($variant[$count] > $CuttingMaxBottom) {
            //Подрезаем панель до максимально допустимого размера
            $delta = $delta - $variant[$count] + $CuttingMaxBottom;
            $variant[$count] = $CuttingMaxBottom;
            $ShieldBottomPanelIsCut = 1;
            $BottomPanelAlreadyCut = 1;
        }
        $cuttingDelta = 0;
        $s1 = $variant[1];
        $s2 = $variant[$count];
        //Г. Режим обрезки "Задать размер нижней панели"
        if ($panelMode == 1) {
            $cuttingDelta = $delta;
            //Указанный пользователем размер должен находится в допустимых пределах
            if ($this->PanelSizeForCutting < $CuttingMinBottom) {
                $cuttingPanelSize = $CuttingMinBottom;
            } else {
                $cuttingPanelSize = $this->PanelSizeForCutting;
            }
            if ($this->PanelSizeForCutting > $CuttingMaxTop) {
                $cuttingPanelSize = $CuttingMaxBottom;
            } else {
                $cuttingPanelSize = $this->PanelSizeForCutting;
            }
            //Отрез не должен быть меньше минимально допустимого
            if (($this->ShieldMinBottomCut != 0) && ($cuttingPanelSize > $variant[$count] - $this->ShieldMinBottomCut)) {
                $cuttingPanelSize = $variant[$count] - $this->ShieldMinBottomCut;
            }
            //Профиль меняется только если мы действительно что то отрезаем
            if ($variant[$count] != $cuttingPanelSize) {
                $cuttingDelta = $cuttingDelta - $variant[$count] + $cuttingPanelSize - $this->ShieldBottomCutDecrease;
            }
            //Разница попадает в допустимый диапазон
            if (($cuttingDelta >= $this->ShieldMinTolerance) && ($cuttingDelta <= $this->ShieldMaxTolerance + $maxexcess)) {
                //Размер панели не совпадает с размером под резку
                if ($variant[$count] != $cuttingPanelSize) {
                    //Просто заменить размер панели на указаный
                    $variant[$count] = $cuttingPanelSize;
                    $ShieldBottomPanelIsCut = 1;
                    //Размеры совпадают
                } else {
                    if (!$BottomPanelAlreadyCut) {
                        $ShieldBottomPanelIsCut = 0;
                    }
                }
                if (!$TopPanelAlreadyCut) {
                    $ShieldTopPanelIsCut = 0;
                }
                $variant[$count] = $cuttingPanelSize;
                $this->Result = 1;
                //Не удается подрезать нижнюю - пробуем подрезать еще и верхнюю
            } else {
                //Учитываем замену профиля
                $cuttingDelta = $cuttingDelta - $this->ShieldTopCutDecrease;
                //Будем уменьшать размер верхней панели с шагом 10мм
                for ($n = $this->ShieldMaxTolerance + $maxexcess; $n >= $this->ShieldMinTolerance; $n--) {
                    //Нашли, корректируем разницу
                    if ((($cuttingDelta - $n) % 10 == 0) && ($cuttingDelta - $n >= 0)) {
                        $cuttingDelta = $cuttingDelta - $n;
                        $this->Result = 1;
                        break;
                    }
                }
                //По прежнему не удалось сделать отрез - пытаемся сделать максимально допустимый
                if ($this->Result == 0) {
                    $cuttingDelta = $cuttingDelta - $this->ShieldMinTolerance;
                }
                //Удалось обрезать
                if (($variant[1] - $cuttingDelta >= $CuttingMinTop) && ($variant[1] - $cuttingDelta <= $CuttingMaxTop)) {
                    //Корректируем размер верхней панели
                    $variant[$count] = $cuttingPanelSize;
                    //Корректируем размер нижней панели
                    $variant[1] = $variant[1] - $cuttingDelta;
                    $this->Result = 1;
                    $ShieldTopPanelIsCut = 1;
                    $ShieldBottomPanelIsCut = 1;
                    //Не удалось, но можно удалить верхнюю панель и надставить следующую за ней
                } elseif (($variant[1] - $cuttingDelta < 0) && ($count >= 2) && ($variant[2] - $variant[1] + $cuttingDelta >= $CuttingMinTop) && ($variant[2] - $variant[1] + $cuttingDelta <= $CuttingMaxTop)) {
                    //Корректируем размер верхней панели
                    $variant[$count] = $cuttingPanelSize;
                    //Запомнить новый размер панели
                    $panel = $variant[2] + $variant[1] - $cuttingDelta;
                    //Удалить верхнюю панель
                    unset($variant[1]);
                    $count--;
                    $variant = $this->range($variant);
                    //Корректируем размер нижней панели
                    $variant[1] = $panel;
                    $ShieldTopPanelIsCut = 1;
                    $ShieldBottomPanelIsCut = 1;
                    $this->Result = 1;
                    //Нельзя обрезать нижнюю панель при таком заданном размере - откат к режиму "Обрезаем верхнюю панель"
                } else {
                    $panelMode = 4;
                    $this->Result = 0;
                }
            }
        }
        //В. Режим обрезки "Задать размер верхней панели"
        if ($panelMode == 2) {
            //Исходное значение дэльта может понадобится позже
            $cuttingDelta = $delta;
            //Указанный пользователем размер должен находится в допустимых пределах
            if ($this->PanelSizeForCutting < $CuttingMinTop) {
                $cuttingPanelSize = $CuttingMinTop;
            } else {
                $cuttingPanelSize = $this->PanelSizeForCutting;
            }
            if ($this->PanelSizeForCutting > $CuttingMaxTop) {
                $cuttingPanelSize = $CuttingMaxTop;
            } else {
                $cuttingPanelSize = $this->PanelSizeForCutting;
            }
            //Отрез не должен быть меньше минимально допустимого
            if (($this->ShieldMinTopCut != 0) && ($cuttingPanelSize > $variant[1] - $this->ShieldMinTopCut)) {
                $cuttingPanelSize = $variant[1] - $this->ShieldMinTopCut;
            }
            //Профиль меняется только если мы действительно что то отрезаем
            if ($variant[1] != $cuttingPanelSize) {
                $cuttingDelta = $cuttingDelta - $variant[1] + $cuttingPanelSize - $this->ShieldTopCutDecrease;
            }
            //Разница попадает в допустимый диапазон
            if (($cuttingDelta >= $this->ShieldMinTolerance) && ($cuttingDelta <= $this->ShieldMaxTolerance + $maxexcess)) {
                //Размер панели не совпадает с размером под резку
                if ($variant[1] != $cuttingPanelSize) {
                    //Просто заменить размер панели на указаный
                    $variant[1] = $cuttingPanelSize;
                    $ShieldTopPanelIsCut = 1;
                    //Размеры совпадают
                } else {
                    if (!$TopPanelAlreadyCut) {
                        $ShieldTopPanelIsCut = 0;
                    }
                }
                if (!$BottomPanelAlreadyCut) {
                    $ShieldBottomPanelIsCut = 0;
                }
                $this->Result = 1;
                //Не удается подрезать верхнюю - пробуем подрезать еще и нижнюю
            } else {
                //Учитываем замену профиля
                $cuttingDelta = $cuttingDelta - $this->ShieldBottomCutDecrease;
                //Будем уменьшать размер нижней панели с шагом 10мм
                for ($n = $this->ShieldMaxTolerance + $maxexcess; $n >= $this->ShieldMinTolerance; $n--) {
                    //Нашли, корректируем разницу
                    if ((($cuttingDelta - $n) % 10 == 0) && ($cuttingDelta - $n >= 0)) {
                        $cuttingDelta = $cuttingDelta - $n;
                        $this->Result = 1;
                        break;
                    }
                }
                //По прежнему не удалось сделать отрез - пытаемся сделать максимально допустимый
                if ($this->Result = 0) {
                    $cuttingDelta = $cuttingDelta - $this->ShieldMinTolerance;
                }
                //Удалось обрезать
                if (($variant[$count] - $cuttingDelta >= $CuttingMinBottom) && ($variant[$count] - $cuttingDelta <= $CuttingMaxBottom)) {
                    //Корректируем размер верхней панели
                    $variant[1] = $cuttingPanelSize;
                    //Корректируем размер нижней панели
                    $variant[$count] = $variant[$count] - $cuttingDelta;
                    $ShieldTopPanelIsCut = 1;
                    if ($cuttingDelta == 0) {
                        $ShieldBottomPanelIsCut = 0;
                    } else {
                        $ShieldBottomPanelIsCut = 1;
                    }
                    $this->Result = 1;
                    //Не удалось, но можно удалить нижнюю панель и надставить следующую за ней
                } elseif (($variant[$count] - $cuttingDelta < 0) && ($count >= 2) && ($variant[$count - 1] - $variant[$count] + $cuttingDelta >= $CuttingMinBottom) && ($variant[$count - 1] - $variant[$count] + $cuttingDelta <= $CuttingMaxTop)) {
                    //Корректируем размер верхней панели
                    $variant[1] = $cuttingPanelSize;
                    //Запомнить новый размер панели
                    $panel = $variant[$count - 1] + $variant[$count] - $cuttingDelta;
                    //Удалить нижнюю панель
                    unset($variant[$count]);
                    $count--;
                    $variant = $this->range($variant);
                    //Корректируем размер нижней панели
                    $variant[$count] = $panel;
                    $ShieldTopPanelIsCut = 1;
                    $ShieldBottomPanelIsCut = 1;
                    $this->Result = 1;
                    //Нельзя обрезать верхнюю панель при таком заданном размере - откат к режиму "Обрезаем нижнюю панель"
                } else {
                    $panelMode = 3;
                    $this->Result = 0;
                }
            }
        }

        //Д. Проверка на необходимость обрезки
        if ($this->Result == 0) {
            if ($delta >= $this->ShieldMinTolerance && $delta <= $this->ShieldMaxTolerance + $maxexcess) {
                if (!$TopPanelAlreadyCut) {
                    $ShieldTopPanelIsCut = 0;
                }
                if (!$BottomPanelAlreadyCut) {
                    $ShieldBottomPanelIsCut = 0;
                }
                $this->Result = 1;
            }
        }

//        while ((array_diff($arrayPrunning, $tempPrunning) && ($this->Result == 0))) {
        $cuttingDelta = 0;
        //Е. Режим обрезки "Обрезаем верхнюю панель"
        if ($panelMode == 4) {
            if (($this->Result == 0)) {
                //Учитывем замену профиля при обрезе верхней панели
                $cuttingDelta = $delta - $this->ShieldTopCutDecrease;
                //Будем уменьшать размер верхней панели с шагом 10мм
                for ($n = $this->ShieldMaxTolerance + $maxexcess; $n >= $this->ShieldMinTolerance; $n--) {
                    //Нашли, корректируем разницу
                    if ((($cuttingDelta - $n) % 10 == 0) && ($cuttingDelta - $n >= 0)) {
                        $cuttingDelta = $cuttingDelta - $n;
                        $this->Result = 1;
                        break;
                    }
                }
                //По прежнему не удалось сделать отрез - пытаемся сделать максимально допустимый
                if ($this->Result == 0) {
                    $cuttingDelta = $cuttingDelta - $this->ShieldMinTolerance;
                }
                //Обрез верхней панели разрешен и удовлетворяет всем условиям
                if (($variant[1] - $cuttingDelta >= $CuttingMinTop) && ($variant[1] - $cuttingDelta <= $CuttingMaxTop) && (($this->ShieldMinTopCut == 0) || ($this->ShieldMinTopCut != 0) && ($this->ShieldMinTopCut <= $cuttingDelta))) {
                    //Просто подрезать панель
                    $variant[1] = $variant[1] - $cuttingDelta;
                    if (!$BottomPanelAlreadyCut) {
                        $ShieldBottomPanelIsCut = 0;
                    }
                    $ShieldTopPanelIsCut = 1;
                    $this->Result = 1;
                    //Не удалось, но можно удалить верхнюю панель и надставить следующую за ней
                } elseif (($variant[1] - $cuttingDelta < 0) && ($count >= 2) && ($variant[2] - $variant[1] + $cuttingDelta >= $CuttingMinTop) && ($variant[2] - $variant[1] + $cuttingDelta >= $CuttingMaxTop) && (($this->ShieldMinTopCut == 0) || (($this->ShieldMinTopCut != 0) && (-($variant[1] - $cuttingDelta) > $this->ShieldMinTopCut)))) {
                    //Запомнить новый размер панели
                    $panel = $variant[2] - $variant[1] + $cuttingDelta;
                    //Удалить верхнюю панель
                    unset($variant[1]);
                    $count--;
                    $variant = $this->range($variant);
                    //Корректируем размер верхней панели
                    $variant[1] = $panel;
                    if (!$BottomPanelAlreadyCut) {
                        $ShieldBottomPanelIsCut = 0;
                    }
                    $ShieldTopPanelIsCut = 1;
                    $this->Result = 1;
                    //Пробуем отрезать и нижнюю тоже
                } elseif ($count >= 2 && ($variant[$count] + $variant[1] - $cuttingDelta >= ($CuttingMinTop + $CuttingMinBottom)) && ($variant[$count] + $variant[1] - $cuttingDelta <= ($CuttingMaxTop + $CuttingMaxBottom)) && ($cuttingDelta > min($this->ShieldMinBottomCut, $this->ShieldMinTopCut))) {
                    //Учитываем замену профиля при обрезе обоих панелей
                    $cuttingDelta = $delta - $this->ShieldTopCutDecrease - $this->ShieldBottomCutDecrease;
                    //Нужно для нового поиска
                    $this->Result = 0;
                    //Будем уменьшать размер нижней панели с шагом 10мм
                    for ($n = $this->ShieldMaxTolerance; $n >= $this->ShieldMinTolerance; $n--) {
                        //Нашли, корректируем разницу
                        if ((($cuttingDelta - $n) % 10 == 0) && ($cuttingDelta - $n >= 0)) {
                            $cuttingDelta = $cuttingDelta - $n;
                            $this->Result = 1;
                            break;
                        }
                    }
                    //По прежнему не удалось сделать отрез - пытаемся сделать максимально допустимый
                    if ($this->Result == 0) {
                        $cuttingDelta = $cuttingDelta - $this->ShieldMinTolerance;
                    }
                    $ShieldTopPanelIsCut = 1;
                    $ShieldBottomPanelIsCut = 1;
                    //Размер верхней панели можно округлить до десятков мм
                    if ($variant[1] + $variant[$count] - $cuttingDelta - ($CuttingMinTop + (10 - $CuttingMinTop % 10)) >= $CuttingMinBottom) {
                        //Режем верхнюю по максимуму, нижнюю - на сколько останется
                        $variant[$count] = $variant[1] + $variant[$count] - $cuttingDelta - ($CuttingMinTop + (10 - $CuttingMinTop % 10));
                        $variant[1] = ($CuttingMinTop + (10 - $CuttingMinTop % 10));
                    } else {
                        $variant[$count] = $variant[1] + $variant[$count] - $cuttingDelta - $CuttingMinTop;
                        $variant[1] = $CuttingMinTop;
                    }
                    //Отрез снизу меньше минимально допустимого - корректируем его
                    if ($variant[$count] > $variantM[$count] - $this->ShieldMinBottomCut) {
                        //$variant[1] = $variant[1] + $variant[$count] - $variantM[$count] + $this->ShieldMinBottomCut;
                        //$variant[$count] = $variantM[$count] + $this->ShieldMinBottomCut;
                        $variant_new1 = $variant[1] + $variant[$count] - $variantM[$count] + $this->ShieldMinBottomCut;
                        $variant_old1 = $variant[1];
                        $variant[1] = $variant_new1;
                        $variant_newCOUNT = $variantM[$count] + $this->ShieldMinBottomCut;
                        $variant[$count] = $variant[$count] - ($variant_new1 - $variant_old1);
                    }
                    $this->Result = 1;
                } else {
                    $this->Result = 0;
                }
            }
        }
        $cuttingDelta = 0;
        //Д. Режим обрезки "Обрезаем нижнюю панель"
        if ($panelMode == 3 || $panelMode == 4) {
            // режим обрезки "Нижняя панель"
            if (($this->Result == 0)) {
                //Учитывем замену профиля при обрезе нижней панели
                $cuttingDelta = $delta - $this->ShieldBottomCutDecrease;
                //Будем уменьшать размер нижней панели с шагом 10мм
                for ($n = $this->ShieldMaxTolerance + $maxexcess; $n >= $this->ShieldMinTolerance; $n--) {
                    //Нашли, корректируем разницу
                    if ((($cuttingDelta - $n) % 10 == 0) && ($cuttingDelta - $n >= 0)) {
                        $cuttingDelta = $cuttingDelta - $n;
                        $this->Result = 1;
                        break;
                    }
                }
                //По прежнему не удалось сделать отрез - пытаемся сделать максимально допустимый
                if ($this->Result == 0) {
                    $cuttingDelta = $cuttingDelta - $this->ShieldMinTolerance;
                }
                //Обрез нижней панели разрешен и удовлетворяет всем условиям
                if (($variant[$count] - $cuttingDelta >= $CuttingMinBottom) && ($variant[$count] - $cuttingDelta <= $CuttingMaxBottom) && (($this->ShieldMinBottomCut == 0) || (($this->ShieldMinBottomCut != 0) && ($this->ShieldMinBottomCut <= $cuttingDelta)))
                ) {
                    //Просто подрезать панель
                    $variant[$count] = $variant[$count] - $cuttingDelta;
                    if ($cuttingDelta == 0) {
                        $ShieldBottomPanelIsCut = 0;
                    } else {
                        $ShieldBottomPanelIsCut = 1;
                    }
                    if (!$TopPanelAlreadyCut) {
                        $ShieldTopPanelIsCut = 0;
                    }
                    $this->Result = 1;
                    //Не удалось, но можно удалить нижнюю панель и надставить следующую за ней
                } elseif (($variant[$count] - $cuttingDelta < 0) && ($count >= 2) && ($variant[$count - 1] - $variant[$count] + $cuttingDelta >= $CuttingMinBottom) && ($variant[$count - 1] - $variant[$count] + $cuttingDelta <= $CuttingMaxBottom) && (($this->ShieldMinBottomCut == 0) || (($this->ShieldMinBottomCut != 0) && (-($variant[$count] - $cuttingDelta) > $this->ShieldMinBottomCut)))) {
                    //Запомнить новый размер панели
                    $panel = $variant[$count - 1] + $variant[$count] - $cuttingDelta;
                    //Удалить нижнюю панель
                    unset($variant[$count]);
                    $count--;
                    $variant = $this->range($variant);
                    //Корректируем размер нижней панели
                    $variant[$count] = $panel;
                    if (!$TopPanelAlreadyCut) {
                        $ShieldTopPanelIsCut = 0;
                    }
                    $ShieldBottomPanelIsCut = 1;
                    $this->Result = 1;
                    //Пробуем отрезать и верхнюю тоже
                } elseif (($count >= 2) && ($variant[$count] + $variant[1] - $cuttingDelta >= ($CuttingMinTop + $CuttingMinBottom)) && ($variant[$count] + $variant[1] - $cuttingDelta <= ($CuttingMaxTop + $CuttingMaxBottom)) && (($this->ShieldMinBottomCut == 0) || (($this->ShieldMinBottomCut != 0) && ($cuttingDelta > $this->ShieldMinBottomCut)))) {
                    //Учитываем замену профиля при обрезе обоих панелей
                    $cuttingDelta = $delta - $this->ShieldTopCutDecrease - $this->ShieldBottomCutDecrease;
                    //Нужно для нового поиска
                    $this->Result = 0;
                    //Будем уменьшать размер нижней панели с шагом 10мм
                    for ($n = $this->ShieldMaxTolerance; $n >= $this->ShieldMinTolerance; $n--) {
                        //Нашли, корректируем разницу
                        if ((($cuttingDelta - $n) % 10 == 0) && ($cuttingDelta - $n >= 0)) {
                            $cuttingDelta = $cuttingDelta - $n;
                            $this->Result = 1;
                            break;
                        }
                    }
                    //По прежнему не удалось сделать отрез - пытаемся сделать максимально допустимый
                    if ($this->Result == 0) {
                        $cuttingDelta = $cuttingDelta - $this->ShieldMinTolerance;
                    }
                    $ShieldTopPanelIsCut = 1;
                    $ShieldBottomPanelIsCut = 1;
                    //Размер нижней панели можно округлить до десятков мм
                    if ($variant[$count] + $variant[1] - $cuttingDelta - ($CuttingMinBottom + (10 - $CuttingMinBottom % 10)) >= $CuttingMinTop) {
                        //Режем нижнюю по максимуму, верхнюю - на сколько останется
                        $variant[1] = $variant[$count] + $variant[1] - $cuttingDelta - ($CuttingMinBottom + (10 - $CuttingMinBottom % 10));
                        $variant[$count] = ($CuttingMinBottom + (10 - $CuttingMinBottom % 10));
                        //Нельзя округлить до десятков мм
                    } else {
                        //Режем нижнюю по максимуму, верхнюю - на сколько останется
                        $variant[1] = $variant[1] + $variant[$count] - $cuttingDelta - $CuttingMinTop;
                        $variant[$count] = $CuttingMinBottom;
                    }
                    //Отрез сверху меньше минимально допустимого - корректируем его
                    if ($variant[1] > $variantM[1] - $this->ShieldMinTopCut) {
                        $variant[$count] = $variant[$count] + $variant[1] - $variantM[1] + $this->ShieldMinTopCut;
                        $variant[1] = $variantM[1] - $this->ShieldMinTopCut;
                    }
                    $this->Result = 1;
                } else {
                    $this->Result = 0;
                }
            }
        }
        $cuttingDelta = 0;
        //Ж. Режим обрезки "Обрезаем обе панели"
        if (true) {
            if (($this->Result == 0)) {
                //Учитываем замену профиля при обрезе обоих панелей
                $cuttingDelta = $delta - $this->ShieldTopCutDecrease - $this->ShieldBottomCutDecrease;
                //Будем уменьшать размер нижней панели с шагом 10мм
                for ($n = $this->ShieldMaxTolerance; $n >= $this->ShieldMinTolerance; $n--) {
                    //Нашли, корректируем разницу
                    if ((($cuttingDelta - $n) % 10 == 0) && ($cuttingDelta - $n >= 0)) {
                        $cuttingDelta = $cuttingDelta - $n;
                        $this->Result = 1;
                        break;
                    }
                }
                //По прежнему не удалось сделать отрез - пытаемся сделать максимально допустимый
                if ($this->Result == 0) {
                    $cuttingDelta = $cuttingDelta - $this->ShieldMinTolerance;
                }
                //Сбрасываем значение, чтобы в случаее неудачи возвращалась ЛОЖЬ
                $this->Result = 0;
                //У нас только одна панель
                if ($count == 1) {
                    //Ее можно обрезать с обеих сторон
                    if (($variant[$count] - $cuttingDelta > $CuttingMinBottom) && ($variant[1] - $cuttingDelta > $CuttingMinTop)) {
                        //Просто обрезаем с обеих сторон
                        $variant[$count] = $variant[$count] - $cuttingDelta;
                        $ShieldTopPanelIsCut = 1;
                        if ($cuttingDelta == 0) {
                            $ShieldBottomPanelIsCut = 0;
                        } else {
                            $ShieldBottomPanelIsCut = 1;
                        }
                        $this->Result = 1;
                    }
                    //Можно обрезать обе панели
                } elseif (($variant[1] + $variant[$count] - $cuttingDelta >= ($CuttingMinTop + $CuttingMinBottom)) && ($variant[1] + $variant[$count] - $cuttingDelta <= ($CuttingMaxTop + $CuttingMaxBottom)) && ((($this->ShieldMinBottomCut == 0) || (($this->ShieldMinBottomCut != 0) && ($cuttingDelta > $this->ShieldMinBottomCut))) && ($this->ShieldMinTopCut == 0 || (($this->ShieldMinTopCut != 0) && ($cuttingDelta > $this->ShieldMinTopCut))))) {
                    $ShieldTopPanelIsCut = 1;
                    $ShieldBottomPanelIsCut = 1;
                    //Стараемся сделать верхнюю и нижнюю панели равного размера
                    $panel = round(($variant[1] + $variant[$count] - $cuttingDelta) / 2, 0, PHP_ROUND_HALF_EVEN);
                    //Новый размер верхней панели меньше минимально допустимого, но его можно увеличить за счет нижней
                    if (($panel < $CuttingMinTop) && ($variant[$count] + $variant[1] - $cuttingDelta - $CuttingMinTop < $variant[$count] - $this->ShieldMinBottomCut)) {
                        //Размер верхней панели можно округлить до десятков мм
                        if ($variant[1] + $variant[$count] - $cuttingDelta - ($CuttingMinTop + (10 - $CuttingMinTop % 10)) >= $CuttingMinBottom) {
                            //Режем верхнюю по максимуму, нижнюю - на сколько останется
                            $variant[$count] = $variant[$count] + $variant[1] - $cuttingDelta - ($CuttingMinTop + (10 - $CuttingMinTop % 10));
                            $variant[1] = ($CuttingMinTop + (10 - $CuttingMinTop % 10));
                            //Нельзя округлить до десятков мм
                        } else {
                            //Режем верхнюю по максимуму, нижнюю - на сколько останется
                            $variant[$count] = $variant[$count] + $variant[1] - $cuttingDelta - $CuttingMinTop;
                            $variant[1] = $CuttingMinTop;
                        }
                        $this->Result = 1;
                        //Новый размер нижней панели меньше минимально допустимого, но его можно увеличить за счет верхней
                    } elseif (($panel < $CuttingMinBottom) && ($variant[1] + $variant[$count] - $cuttingDelta - $CuttingMinBottom < $variant[1] - $this->ShieldMinTopCut)) {
                        //Размер нижней панели можно округлить до десятков мм
                        if ($variant[1] + $variant[$count] - $cuttingDelta - ($CuttingMinBottom + (10 - $CuttingMinBottom % 10)) >= $CuttingMinTop) {
                            //Режем нижнюю по максимуму, верхнюю - на сколько останется
                            $variant[1] = $variant[1] + $variant[$count] - $cuttingDelta - ($CuttingMinBottom + (10 - $CuttingMinBottom % 10));
                            $variant[$count] = ($CuttingMinBottom + (10 - $CuttingMinBottom % 10));
                            //Нельзя округлить до десятков мм
                        } else {
                            //Режем нижнюю по максимуму, верхнюю - на сколько останется
                            $variant[1] = $variant[1] + $variant[$count] - $cuttingDelta - $CuttingMinBottom;
                            $variant[$count] = $CuttingMinBottom;
                        }
                        $this->Result = 1;
                        //Размер нижней панели превышает максимально допустимый
                    } elseif ($panel > $variant[$count] - $this->ShieldMinBottomCut) {
                        //Уменьшить размер нижней панели до допустимого за счет увеличения верхней
                        $variant[1] = $panel + ($panel - ($variant[$count] - $this->ShieldMinBottomCut));
                        $variant[$count] = $panel - ($panel - ($variant[$count] - $this->ShieldMinBottomCut));
                        $this->Result = 1;
                        //Размер верхней панели превышает максимально допустимый
                    } elseif ($panel > $variant[1] - $this->ShieldMinTopCut) {
                        //Уменьшить размер верхней панели до допустимого за счет увеличения нижней
                        $variant[$count] = $panel + ($panel - ($variant[1] - $this->ShieldMinTopCut));
                        $variant[1] = $panel - ($panel - ($variant[1] - $this->ShieldMinTopCut));
                        $this->Result = 1;
                    //если филенка то нельзя чтобы профиль налезал на квадрат филенки
                    //} elseif ($this->ShieldPanelsWithInfill && abs($panel - (($variant[1] - $this->ShieldInfillHeight)/2 + $this->ShieldInfillHeight)) < 30) {
                    //} elseif ($this->TypeF == "Алюминиевый" && $this->ShieldPanelsWithInfill && ($panel - (($this->ShieldInfillHeight)/2 + $this->ShieldInfillHeight)) < 30 && ($variant[1] - $panel < $variant[1]/2)) {
                    } elseif (
                        $this->TypeF == "Алюминиевый" && 
                        $this->ShieldPanelsWithInfill && 
                        ($panel - (($variant[1]  - $this->ShieldInfillHeight)/2 + $this->ShieldInfillHeight)) < 30 && 
                        ($variant[1] - $panel < $variant[1]/2)
                    ) {
                        $this->Result = 0;
                    } else {
                        $variant[$count] = $panel;
                        $variant[1] = $panel;
                        $this->Result = 1;
                    }
                }
            }
        }

        if ($this->Result == 1) {
            if ($variant[$count] < $CuttingMinBottom || $variant[1] < $CuttingMinTop ||
                    $variant[$count] > $CuttingMaxBottom || $variant[1] > $CuttingMaxTop ||
                    ($variant[$count] > $variantM[$count] - $this->ShieldMinBottomCut && $variant[$count] < $variantM[$count]) ||
                    ($variant[1] > $variantM[1] - $this->ShieldMinTopCut && $variant[1] < $variantM[1])) {
                $this->Result = 0;   
            }
            
        }
        
        return array(
            $variant,
            $cuttingDelta,
            array(
                $ShieldTopPanelIsCut,
                $ShieldBottomPanelIsCut
            ),
            $this->Result
        );
    }

    /**
     * Вычисляет дельту оставшегося места.
     *
     * @param mixed $panels Размеры панель
     * @param mixed $height Высота проема
     * @param mixed $padding Отстыпы
     *
     * @return int
     */
    private function getDelta($panels, $height, $padding)
    {
        $sum = 0;
        for ($i = 1; $i <= count($panels); $i++) {
            $sum += $panels[$i] + $padding;
        }
        $sum -= $padding;
        $getDelta = $sum - $height;

        return $getDelta;
    }

    /**
     * Устанавливает ключи массива попорядку, начиная с 1.
     *
     * @param mixed $array
     *
     * @return array
     */
    private function range($array)
    {
        $k = 1;
        $temparray = array();
        foreach ($array as $key => $value) {
            $temparray[$k] = $value;
            $k++;
        }

        return $temparray;
    }

//    /**
//     * Расчитывает выбраный вариант раскроя.
//     * Используется только для расчета, на основе данных форм из десктопа.
//     * Признак того, что идет такой расчет - наличие переменной GetOriginalRaskroyInString
//     *
//     */
//    public function setCuttingVariant()
//    {
//        if (!(Yii::app()->container->GetOriginalRaskroyInString instanceof ForNull)) {
//            $arr = explode(',', Yii::app()->container->GetOriginalRaskroyInString);
//            $result = array();
//            for ($i = 0; $i < count($arr); $i++) {
//                $result[$i + 1] = $arr[$i];
//            }
//            for ($i = 1; $i <= count($this->CuttingVariantsUnmodified); $i++) {
//                if (!array_diff($this->CuttingVariantsUnmodified[$i]['cutting'], $result)) {
//                    $this->VariantCutting = $i;         //      выберем текущий вариант раскроя
//                }
//            }
//        }
//    }
    /**
    * 3. Алгоритм "Раскрой панорамных панелей"
    * 
    */
    private function panoramicCutting()
    {
        //А. Вычисление параметров сэндвич панелей
        //Количество панелей еще не задано                                                      
        $NumberIsSet = 0;
        //Нельзя модифицировать ShieldHeight!!!
        $CalcShieldHeight = $this->ShieldHeight;
        //Зазор между панелями в панорамном щите                                                 
        if ($this->PanComplectationType == 'Без защиты от защемления') {
            $this->PanPanelPadding = 2;
        }else{
            $this->PanPanelPadding = 1;
        }
        //Для верхних или нижних сэнвич панелей задано их количество
        if ($this->PanSettingType == 'Кол-во панелей' || $this->PanBottomSettingType == 'Кол-во панелей') {
            //Сэнвич панели только вверху щита и задано их количество
            if ($this->PanShieldType == 'Верх из сэндвич' && $this->PanSettingType == 'Кол-во панелей') {
                //Количество панелей уже задано
                $NumberIsSet = 1;
                //Количество верхних и нижних сэндвич панелей
                $this->SandTopCount = $this->PanPanelParameter;
                $this->SandBottomCount = 0;
                //Выбранные типоразмеры верхней и нижней сэндвич панелей                             
                $TopPanelSize = (int)$this->PanPanelType;
                $BottomPanelSize = 0;
                //Высота верхних и нижних сэндвич панелей                                       
                $this->SandTopHeight = ($TopPanelSize + $this->PanPanelPadding) * $this->SandTopCount;
                $this->SandBottomHeight = 0;

                //Добавляем панели в список - все панели имеют одинаковый типоразмер
                $SandTopPanels = array();
                for ($n = 1; $n <= $this->SandTopCount; $n++) {
                    $SandTopPanels[$n] = $TopPanelSize;
                }
                $this->SandTopPanels = $SandTopPanels;
                //Количество сэндвич панелей данного типоразмера
                $arr = $this->SandNumberOfPanels;
                $arr[$TopPanelSize] = (int)$this->SandTopCount;
                $this->SandNumberOfPanels = $arr;
            }
            //Сэнвич панели только внизу щита и задано их количество
            if ($this->PanShieldType == 'Низ из сэндвич' && $this->PanSettingType == 'Кол-во панелей') {
                //Количество панелей уже задано
                $NumberIsSet = 1;
                //Количество верхних и нижних сэндвич панелей
                $this->SandTopCount = 0;
                $this->SandBottomCount = $this->PanPanelParameter;
                //Выбранные типоразмеры верхней и нижней сэндвич панелей                             
                $TopPanelSize = 0;
                $BottomPanelSize = (int)$this->PanPanelType;
                //Высота верхних и нижних сэндвич панелей                                       
                $this->SandTopHeight = 0;
                $this->SandBottomHeight = ($BottomPanelSize + $this->PanPanelPadding) * $this->SandBottomCount;
                
                //Добавляем панели в список - все панели имеют одинаковый типоразмер
                $SandBottomPanels = array();
                for ($n = 1; $n <= $this->SandBottomCount; $n++) {
                    $SandBottomPanels[$n] = $BottomPanelSize;
                }
                $this->SandBottomPanels = $SandBottomPanels;
                //Количество сэндвич панелей данного типоразмера
                $arr = $this->SandNumberOfPanels;
                $arr[$BottomPanelSize] = (int)$this->SandBottomCount;
                $this->SandNumberOfPanels = $arr;
            }
            //Сэнвич панели и вверху и внизу щита
            if ($this->PanShieldType == 'Середина из панорамных') {
                if ($this->PanSettingType == 'Кол-во панелей') {
                    //Количество верхних сэндвич панелей                                       
                    $this->SandTopCount = $this->PanPanelParameter;
                    //Выбранный типоразмер верхней сэндвич панели                             
                    $TopPanelSize = (int)$this->PanPanelType;
                    //Высота верхних сэндвич панелей                                       
                    $this->SandTopHeight = ($TopPanelSize + $this->PanPanelPadding) * $this->SandTopCount;
                    //Добавляем панели в список - все панели имеют одинаковый типоразмер                   
                    $SandTopPanels = array();
                    for ($n = 1; $n <= $this->SandTopCount; $n++) {
                        $SandTopPanels[$n] = $TopPanelSize;
                    }
                    $this->SandTopPanels = $SandTopPanels;              
                    //Количество сэндвич панелей данного типоразмера
                    $arr = $this->SandNumberOfPanels;
                    $arr[$TopPanelSize] += $this->SandTopCount;
                    $this->SandNumberOfPanels = $arr;
                }
                //Задано количество нижних сэндвич панелей
                if ($this->PanBottomSettingType == 'Кол-во панелей') {
                    //Количество нижних сэндвич панелей
                    $this->SandBottomCount = $this->PanBottomPanelParameter;
                    //Выбранные типоразмеры нижней сэндвич панелей                             
                    $BottomPanelSize = (int)$this->PanBottomPanelType;
                    //Высота нижних сэндвич панелей                                       
                    $this->SandBottomHeight = ($BottomPanelSize + $this->PanPanelPadding) * $this->SandBottomCount;
                    
                    //Добавляем панели в список - все панели имеют одинаковый типоразмер
                    $SandBottomPanels = array();
                    for ($n = 1; $n <= $this->SandBottomCount; $n++) {
                        $SandBottomPanels[$n] = $BottomPanelSize;
                    }
                    $this->SandBottomPanels = $SandBottomPanels;
                    //Количество сэндвич панелей данного типоразмера - добавляем нижние
                    $arr = $this->SandNumberOfPanels;
                    $arr[$BottomPanelSize] += $this->SandBottomCount;
                    $this->SandNumberOfPanels = $arr;
                }
            }
        }
        
        //Для верхних или нижних сэнвич панелей заданы их типоразмеры и их количество пока еще не определено
        if (($this->PanSettingType == "Высоту из сэндвича" || $this->PanBottomSettingType == "Высоту из сэндвича") && !$NumberIsSet) {
            //Заданы типоразмеры панелей
            if ($this->PanSettingType == "Высоту из сэндвича") {
                $AvailableSizes = explode('+', (string)$this->PanPanelType);
            }
            //Заданы типоразмеры нижних панелей
            if ($this->PanBottomSettingType == "Высоту из сэндвича") {
                $AvailableSizesBottom = explode('+', (string)$this->PanBottomPanelType);
            }
            
            //Сэнвич панели только вверху щита
            if ($this->PanShieldType == "Верх из сэндвич") {
                  //Высота верхних и нижних сэндвич панелей
                  $this->SandTopHeight = $this->PanPanelParameter;
                  $this->SandBottomHeight = 0;
            }
             //Сэнвич панели только внизу щита
            if ($this->PanShieldType == "Низ из сэндвич") {
                  //Высота верхних и нижних сэндвич панелей
                  $this->SandTopHeight = 0;
                  $this->SandBottomHeight = $this->PanPanelParameter;
            }
            
            //Сэнвич панели и вверху и внизу щита
            if ($this->PanShieldType == "Середина из панорамных") {
                  //Заданы типоразмеры верхних сэндвич панелей
                  if ($this->PanSettingType == "Высоту из сэндвича") {
                       $this->SandTopHeight = $this->PanPanelParameter;
                  }
                  //Заданы типоразмеры нижних сэндвич панелей
                  if ($this->PanBottomSettingType == "Высоту из сэндвича") {
                       $this->SandBottomHeight = $this->PanBottomPanelParameter;
                  }
            }
            
            //Сэнвич панели только вверху щита и заданы типоразмеры панелей                                  
            if ($this->PanShieldType == "Верх из сэндвич" && $this->PanSettingType == "Высоту из сэндвича"){
                //Высота, которую необходимо набрать                                            
                $CalcShieldHeight = $this->SandTopHeight;                                                 
                //Доступные типоразмеры панелей                                            
                $this->ShieldPanelSizes = $AvailableSizes;
                
                list($CuttingRealHeight, $CuttingCount, $CuttingPanels) = $this->callAlgorithmB($CalcShieldHeight);
                
                if ($CuttingRealHeight == 0) {
                    //Выдать предупреждение Loc(1709, s1:CRLF)
                    $this->errorArray = str_replace('%s1', "\n", Yii::app()->container->Loc('1709'));
                    return true;
//                    return false;
                } else {
                    $this->SandTopPanels = $CuttingPanels;
                    $this->SandTopCount = $CuttingCount;
                    $this->SandBottomCount = 0;
                    $this->SandTopRealHeight = $CuttingRealHeight;
                }
                //Перечисляем все верхние панели
                $arr = $this->SandNumberOfPanels;
                for ($n = 1; $n <= $this->SandTopCount; $n++) {
                    $Panel = $this->SandTopPanels[$n];
                    $arr[$Panel] = $arr[$Panel] + 1;
                }
                $this->SandNumberOfPanels = $arr;
            }
            
            //Сэнвич панели только внизу щита и заданы типоразмеры панелей
            if ($this->PanShieldType == "Низ из сэндвич" && $this->PanSettingType == "Высоту из сэндвича"){
                //Высота, которую необходимо набрать                                            
                $CalcShieldHeight = $this->SandBottomHeight;                                                 
                //Доступные типоразмеры панелей                                            
                $this->ShieldPanelSizes = $AvailableSizes;
                //Вызвать алгоритм "Вызов алгоритма оптимального раскроя"
                list($CuttingRealHeight, $CuttingCount, $CuttingPanels) = $this->callAlgorithmB($CalcShieldHeight);
                
                if ($CuttingRealHeight == 0) {
                    //Выдать предупреждение Loc(1709, s1:CRLF)
                    $this->errorArray = str_replace('%s1', "\n", Yii::app()->container->Loc('1709'));
                    return true;
//                    return false;
                } else {
                    $this->SandBottomPanels = $CuttingPanels;
                    $this->SandBottomCount = $CuttingCount;
                    $this->SandTopCount = 0;
                    $this->SandBottomRealHeight = $CuttingRealHeight;
                }
                //Перечисляем все нижние панели
                $arr = $this->SandNumberOfPanels;
                for ($n = 1; $n <= $this->SandBottomCount; $n++) {
                    $Panel = $this->SandBottomPanels[$n];
                    $arr[$Panel] = $arr[$Panel] + 1;
                }
                $this->SandNumberOfPanels = $arr;
            }
            
            //Сэнвич панели только внизу щита и заданы типоразмеры панелей
            if ($this->PanShieldType == "Середина из панорамных"){
                //Заданы типоразмеры верхних сэндвич панелей
                if ($this->PanSettingType == "Высоту из сэндвича") {
                    //Высота, которую необходимо набрать                                            
                    $CalcShieldHeight = $this->SandTopHeight;                                                 
                    //Доступные типоразмеры панелей                                            
                    $this->ShieldPanelSizes = $AvailableSizes;
                    //Вызвать алгоритм "Вызов алгоритма оптимального раскроя"
                    list($CuttingRealHeight, $CuttingCount, $CuttingPanels) = $this->callAlgorithmB($CalcShieldHeight);
                    
                    if ($CuttingRealHeight == 0) {
                        //Выдать предупреждение Loc(1709, s1:CRLF)
                        $this->errorArray = str_replace('%s1', "\n", Yii::app()->container->Loc('1709'));
                        return true;
                    } else {
                        $this->SandTopPanels = $CuttingPanels;
                        $this->SandTopCount = $CuttingCount;
                        $this->SandTopRealHeight = $CuttingRealHeight;
                    }
                    //Перечисляем все верхние панели
                    $arr = $this->SandNumberOfPanels;
                    for ($n = 1; $n <= $this->SandTopCount; $n++) {
                        $Panel = $this->SandTopPanels[$n];
                        $arr[$Panel] = $arr[$Panel] + 1;
                    }
                    $this->SandNumberOfPanels = $arr;
                }
                //Заданы типоразмеры нижних сэндвич панелей
                if ($this->PanBottomSettingType == "Высоту из сэндвича") {
                    //Высота, которую необходимо набрать                                            
                    $CalcShieldHeight = $this->SandBottomHeight;                                                 
                    //Доступные типоразмеры панелей                                            
                    $this->ShieldPanelSizes = $AvailableSizesBottom;
                    //сбрасываем раскрой верхних сендвич панелей
                    $this->CuttingVariantsUnmodified = array();
                    //Вызвать алгоритм "Вызов алгоритма оптимального раскроя"
                    list($CuttingRealHeight, $CuttingCount, $CuttingPanels) = $this->callAlgorithmB($CalcShieldHeight);
                    
                    if ($CuttingRealHeight == 0) {
                        //Выдать предупреждение Loc(1709, s1:CRLF)
                        $this->errorArray = str_replace('%s1', "\n", Yii::app()->container->Loc('1709'));
                        return true;
                    } else {
                        $this->SandBottomPanels = $CuttingPanels;
                        $this->SandBottomCount = $CuttingCount;
                        $this->SandBottomRealHeight = $CuttingRealHeight;
                    }
                    //Перечисляем все нижние панели
                    $arr = $this->SandNumberOfPanels;
                    for ($n = 1; $n <= $this->SandBottomCount; $n++) {
                        $Panel = $this->SandBottomPanels[$n];
                        $arr[$Panel] = $arr[$Panel] + 1;
                    }
                    $this->SandNumberOfPanels = $arr;
                }
            }
            
            //Есть верхние сэндвич панели
            if ($this->SandTopCount > 0){
                //Высота верхней сэндвич панели без обреза
                $this->SandWholeTopPanel = $this->SandTopPanels[$this->SandTopCount];                                      
            }
            //Есть нижние сэндвич панели
            if ($this->SandBottomCount > 0){
                //Высота нижней сэндвич панели без обреза
                $this->SandWholeBottomPanel = $this->SandBottomPanels[1];
            }
            
            //В щите есть сэндвич панели                                                      
            if ($this->PanShieldType <> "Полностью из панорамных") {
                //Реальная высота верхних сэндвич панелей не совпадает с заданной - требуется отрез
                if ($this->SandTopRealHeight > $this->SandTopHeight) {
                    //Верхняя сэндвич панель обрезана                                       
                    $this->SandTopPanelIsCut = 1;
                    //Уменьшаем количество панелей данного типоразмера
                    //$this->SandNumberOfPanels[$this->SandWholeTopPanel] = $this->SandNumberOfPanels[$this->SandWholeTopPanel] - 1;
                    $this->SandNumberOfPanels = $this->IncArray($this->SandNumberOfPanels, $this->SandWholeTopPanel, -1);
                    //Величина отреза верхней сэндвич панели
                    $this->SandTopPanelCut = $this->SandTopRealHeight - $this->SandTopHeight;
                    //Отрезаем данную величину от верхней сэндвич панели
                    //$this->SandTopPanels[$this->SandTopCount] = $this->SandTopPanels[$this->SandTopCount] - $this->SandTopPanelCut;
                    $this->SandTopPanels = $this->IncArray($this->SandTopPanels, $this->SandTopCount, -$this->SandTopPanelCut);
                }
                //Реальная высота нижних сэндвич панелей не совпадает с заданной - требуется отрез
                if ($this->SandBottomRealHeight > $this->SandBottomHeight) {
                    //Нижняя сэндвич панель обрезана
                    $this->SandBottomPanelIsCut = 1;
                    //Уменьшаем количество панелей данного типоразмера
                    //$this->SandNumberOfPanels[$this->SandWholeBottomPanel] = $this->SandNumberOfPanels[$this->SandBottomTopPanel] - 1;
                    $this->SandNumberOfPanels = $this->IncArray($this->SandNumberOfPanels, $this->SandWholeBottomPanel, -1);
                    //Величина отреза нижней сэндвич панели
                    $this->SandBottomPanelCut = $this->SandBottomRealHeight - $this->SandBottomHeight;
                    //Отрезаем данную величину от нижней сэндвич панели
                    //$this->SandBottomPanels[1] = $this->SandBottomPanels[1] - $this->SandBottomPanelCut;
                    $this->SandBottomPanels = $this->IncArray($this->SandBottomPanels, 1, -$this->SandBottomPanelCut);
                }
            }
        }
        
        //Есть верхние сэндвич панели
        if ($this->SandTopCount > 0) {
            //Высота верхней сэндвич панели
            $this->SandTopPanel = $this->SandTopPanels[$this->SandTopCount];
        }
        //Есть нижние сэндвич панели
        if ($this->SandBottomCount > 0) {
            //Высота нижней сэндвич панели
            $this->SandBottomPanel = $this->SandBottomPanels[1];
        }
        
        $this->callAlgorithmG();

        //Вернуться в основной алгоритм
        //Завершить работу алгоритма
        return true;
    }
    
    private function IncArray($array, $index, $increment)
    {
        if (array_key_exists($index, $array)) {
            $array[$index] += $increment;
        } else {
            $array[$index] = $increment;
        }
        return $array;
    }
    
    /**
    * Б. Вызов алгоритма оптимального раскроя
    * 
    */
    private function callAlgorithmB($CalcShieldHeight)
    {
        //Смотри алгоритм Б                                                      
        //Вызвать алгоритм "Вызов алгоритма оптимального раскроя"                             
        //Общие параметры раскроя                                                      
        $this->ShieldMaxTolerance = 0;
        $this->ShieldMinTolerance = 0;
        $this->ShieldPanelPadding = 0;//$this->PanPanelPadding;
        $this->ShieldMinTop = 350;
        $this->ShieldMinBottom = 350;
        $this->ShieldTopCutDecrease = 0;
        $this->ShieldBottomCutDecrease = 0;
        $this->OnlyOneSize = 0;
        $this->ShieldMaxTop = 0;
        $this->ShieldMaxBottom = 0;
        $this->ShieldMinTopCut = 0;
        $this->ShieldMinBottomCut = 0;
        $CuttingTopEnabled = 1;
        $CuttingBottomEnabled = 1;

        //Подготовка к вызову алгоритма оптимального раскроя
        $CompletedHeight = 0;
        $CurrentVariant = array();
        $PrefferedSize = 0;
        
        //Нельзя модифицировать ShieldHeight!!!
        $OldShieldHeight = $this->ShieldHeight;
        $this->ShieldHeight = $CalcShieldHeight;
        
        //Вызвать алгоритм "Оптимальный раскрой"
        $this->optimalCutting($CompletedHeight, $CurrentVariant, $PrefferedSize, $CuttingTopEnabled, $CuttingBottomEnabled, 0, $this->ShieldHeight);
        
        $this->ShieldHeight = $OldShieldHeight;

        //Значение по умолчанию                                                      
        $CuttingRealHeight = 0;
        $CuttingCount = 0;
        $CuttingPanels = array();
        //Раскрой завершился неудачей
        if ($this->CuttingVariantsUnmodified) {
            //сортировка
            $tempCutting = $this->CuttingVariantsUnmodified;
            $deltaA = array();
            $ratingA = array();
            $cuttingA = array();
            foreach ($tempCutting as $key => $value) {
                $deltaA[$key] = abs($value['delta']);
                $ratingA[$key] = $value['rating'];
                $cuttingA[$key] = count($value['cutting']);
            }
            array_multisort($deltaA, SORT_ASC, $ratingA, SORT_ASC, $cuttingA, SORT_ASC, $tempCutting);
            $count = count($tempCutting);
            $tempToCuttings = $tempCutting;
            $tempCutting = array();
            for ($i = 1; $i <= $count; $i++) {
                $tempCutting[$i] = $tempToCuttings[$i - 1];
            }
            $this->CuttingVariantsUnmodified = $tempCutting;

            ////Берем первый элемент - самый оптимальный
            $CuttingPanels = $this->CuttingVariantsUnmodified[1]['cutting'];

            list($CuttingPanels, $TopIndex, $BottomIndex) = $this->callAlgorithmV($CuttingPanels);
            $CuttingCount = count($CuttingPanels);

            //Находим реальную набранную высоту панелей                                       
            for ($n = 1; $n <= $CuttingCount; $n++) {
                $CuttingRealHeight = $CuttingRealHeight + $CuttingPanels[$n];
            }
        }
        
        return array($CuttingRealHeight, $CuttingCount, $CuttingPanels);
    }
    
    /**
    * В. Преобразование варианта раскроя в симметричный
    * 
    */
    private function callAlgorithmV($CuttingPanels)
    {
        //Смотри алгоритм В                                                      
        //Вызвать алгоритм "Преобразование варианта раскроя в симметричный"
        //В. Преобразование варианта раскроя в симметричный    
        //Количество панелей                                                           
        $Count = count($CuttingPanels);
        $TopIndex = 1;
        $BottomIndex = $Count;
        //Алгоритм не имеет смысла для менее, чем 3-х панелей
        if ($Count > 2) {
            //Скопируем исходный вариант раскроя                                                 
            $CopyCuttingPanels = $CuttingPanels;         
            //Первый элемент должен быть максимальным                                            
            arsort($CopyCuttingPanels);                                       
            //Индексы в массиве                                                           
            $TopIndex = 1;
            $BottomIndex = $Count;
            //Перебрать все панели
            for ($n = 1; $n <= $Count; $n++) {                                       
                //Индекс нечетный                                                      
                if ($n % 2 == 1){
                    //Заполнять массив нечетными элементами сверху вниз
                    $CuttingPanels[$TopIndex] = $CopyCuttingPanels[$n];
                    $TopIndex++;
                    //Индекс четный                                                      
                } else {
                    //Заполнять массив четными элементами снизу вверх
                    $CuttingPanels[$BottomIndex] = $CopyCuttingPanels[$n];
                    $BottomIndex--;
                }
            }
        }
        
        return array($CuttingPanels, $TopIndex, $BottomIndex);
    }
    
    /**
    * Г. Расчет панорамных панелей
    * 
    */
    private function callAlgorithmG()
    {
        //Г. Расчет панорамных панелей
        
        //Определить тип профилей
        Yii::import('calculation.ProfileSelectionMC.ProfileSelectionMC');
        $profileS = new ProfileSelectionMC();
        $profileS->key = $this->key;
        $profileS->fillVariables();
        //установка атрибутов
        $profileS->Fill();
        $profileS->RegionMoscow = Yii::app()->container->RegionMoscow;
        $profileS->RegionEurope = Yii::app()->container->RegionEurope;
        $profileS->DilerVersion = Yii::app()->container->DilerVersion;            
        $profileS->WicketFutureInstallation = Yii::app()->container->WicketFutureInstallation;
        $profileS->ProfileType = Yii::app()->container->ProfileType;
        $profileS->ShieldWithAntiJamProtection = $this->ShieldWithAntiJamProtection;
        $profileS->ShieldWidth = Yii::app()->container->ShieldWidth;
        $profileS->AlFacing = Yii::app()->container->AlFacing;
        $profileS->AlFacing2010 = Yii::app()->container->AlFacing2010;
        $profileS->PanoramicPanel = Yii::app()->container->PanoramicPanel;
        $profileS->RubberInstalled = Yii::app()->container->RubberInstalled;

        $profileS->Algorithm();
        $profileS->Output();
        
        //Необходимо вызвать этот расчет здесь, т.к. расчет панорамных должен быть выполнен до возвращения в основной алгоритм
        Yii::import('calculation.ProfileCalculationMC.ProfileCalculationMC');
        $profile = new ProfileCalculationMC();
        $profile->key = $this->key;
        $profile->fillVariables();
        $profile->Fill();
        $profile->ShieldTopProfile = $profileS->InitialShieldTopProfile;
        $profile->ShieldBottomProfile = $profileS->InitialShieldBottomProfile;
        $profile->ShieldWithAntiJamProtection = $this->ShieldWithAntiJamProtection;
        $profile->Algorithm();
        $profile->Output();
        $this->ShieldTopProfileSize = $profile->ShieldTopProfileSize;
        $this->ShieldBottomProfileSize = $profile->ShieldBottomProfileSize;
        //Высота профилей панорамного щита
        $this->PanProfilesSize = $this->ShieldTopProfileSize + $this->ShieldBottomProfileSize + 15 - 25;
        //размеры профилей для фальшпанели 0
        $this->PanProfilesSize = 0;
        //Требуемая высота панорамных панелей - высота щита минус высота профилей и сэндвич панелей
        $PanDemandedHeight = $this->ShieldHeight - $this->PanProfilesSize - $this->SandTopHeight - $this->SandBottomHeight  + $this->ShieldMaxTolerance;
        if($PanDemandedHeight <= 0)
            $this->errorArray = Yii::t('steps', "Не достаточно места для установки панорамных панелей");
        //Значение по умолчанию                                                           
        $this->PanRemainder = 0;
        $this->PanFinalPanelSize = $this->PanPanelSize; //задается на форме PanoramicParametersMI
        //Размер панорамных панелей задан пользователем                                            
        if ($this->PanSetPanelSize) {
            //Размер должен быть больше 150                                                 
            if ($this->PanPanelSize < 150) {
                //Завершить работу алгоритма
                return;
            }
            //Остаток после набора щита из целых панорамных панелей                                       
            $this->PanRemainder = $PanDemandedHeight % $this->PanPanelSize;
            //Количество целых панорамных панелей
            $this->PanPanelCount = ($PanDemandedHeight - $this->PanRemainder) / $this->PanFinalPanelSize;
            //Остаток можно выделить в отдельную панель
            if ($this->PanRemainder >= 250) {
                //Добавляем целые панели
                $arr = $this->PanPanels;
                for ($n = 1; $n <= $this->PanPanelCount; $n++) {
                    //Добавить значение PanPanelSize в список PanPanels
                    $arr[$n] = $this->PanPanelSize;
                }
                //Добавляем одну панель
                $this->PanPanelCount = $this->PanPanelCount + 1;
                //Добавляем остаток
                if (!empty($arr))
                    $arr[] = $this->PanRemainder;
                else
                    $arr[1] = $this->PanRemainder;
                $this->PanPanels = $this->reverseArray($arr);
                //Остаток нельзя выделить в отдельную панель
            } else {                                                                
                //Последнюю панель можно надставить для достижения нужного размера
                if ($this->PanRemainder > 0 && $this->PanRemainder < 250 && $this->PanRemainder + $this->PanPanelSize < 700) {
                    //Добавляем целые панели
                    $arr = $this->PanPanels;
                    for ($n = 1; $n <= $this->PanPanelCount - 1; $n++) {                        
                        //Добавить значение $this->PanPanelSize в список $this->PanPanels
                        $arr[$n] = $this->PanPanelSize;
                    }
                    //Добавляем надставленную панель
                    if (!empty($arr))
                        $arr[] = $this->PanRemainder + $this->PanPanelSize;
                    else
                        $arr[1] = $this->PanRemainder + $this->PanPanelSize;
                    $this->PanPanels = $this->reverseArray($arr);
                    //Остаток - высота последней панели;
                    $this->PanRemainder = $this->PanRemainder + $this->PanPanelSize;
                    //Последнюю панель нельзя надставить
                } else {
                    //Остаток равен нулю - ничего обрезать или надставлять не нужно
                    if ($this->PanRemainder == 0) {
                        //Добавляем целые панели
                        //Добавить значение $this->PanPanelSize в список $this->PanPanels
                        $arr = $this->PanPanels;
                        for ($n = 1; $n <= $this->PanPanelCount; $n++) {                        
                            //Добавить значение $this->PanPanelSize в список $this->PanPanels
                            $arr[$n] = $this->PanPanelSize;
                        }
                        $this->PanPanels = $this->reverseArray($arr);
                        
                    //Не возможно произвести расчет панорамных панелей
                    } else {
                        //Выдать предупреждение Loc(693);
                        $this->infoArray[] = Yii::app()->container->Loc('693');
                        return true;
                    }
                }
            }
            
            
            //Размер панелей необходимо определить автоматически
        } else {
            //Максимально доступный размер панелей;
            /*if ($this->RegionEurope == 1) {
                $PanMaxSize = 750;//было 610
            } else {
                //Шкулев Р. 07.10.2015 Сможешь сделать сегодня, чтобы макс. высота панорамных панелей была 750 мм. 
                if ($this->RegionMoscow == 1) {
                    $PanMaxSize = 750;//575;
                } else {
                    $PanMaxSize = 575;
                }
            }*/
            //Начинаем с одной панели
            $this->PanPanelCount = 0;
            //Отступ между панелями
            $ShieldPanelPadding = 0;
            //Начало цикла с постусловием
            do {
                //Выполнять следующие действия
                //Добавляем одну панель
                $this->PanPanelCount = $this->PanPanelCount + 1;
                //Высота щита распределяется между панелями поровну
                $this->PanFinalPanelSize = ($PanDemandedHeight - $ShieldPanelPadding) / $this->PanPanelCount;
                //Добавляем отступ
                $ShieldPanelPadding = $ShieldPanelPadding + $this->ShieldPanelPadding;
                //Конец цикла с постусловием - нахождение макс. PanPanelSize, меньшего чем предельно допустимое PanMaxSize         
            } while ($this->PanFinalPanelSize > $this->ShieldMaxTop);
            //Округление до целых
            $this->PanFinalPanelSize = ceil($this->PanFinalPanelSize);
            //Добавляем целые панели
            $arr = array();
            for ($n = 1; $n <= $this->PanPanelCount; $n++) {
                $arr[$n] = $this->PanFinalPanelSize;
            }
            $this->PanPanels = $arr;
            //Начальное значение высоты
            $this->PanHeight = 0;
            //Перебираем все панорамные панели
            for ($n = 1; $n <= $this->PanPanelCount; $n++) {
                //Находим сумму
                $this->PanHeight = $this->PanHeight + $this->PanPanels[$n];
            }
        }
        
        return true;
    }

    /**
    * put your comment there...
    * 
    * @param mixed $CuttingOptimalVariant
    */
    public function findVariantCuttingFromDESC($CuttingOptimalVariant)
    {
        if (Yii::app()->container->GetOriginalRaskroyInString) {
            foreach ($this->CuttingVariantsUnmodified as $k => $CuttingVariant) {
                if (implode(',', $CuttingVariant['cutting']) == Yii::app()->container->GetOriginalRaskroyInString) {
                    return $k;
                }
            }
        }
        return $CuttingOptimalVariant;
    }

}
