<?php

/**
 * Модуль расчета операций для линии
 * PHP version 5.5
 * @category Yii
 */
class OperationsForLineMC extends AbstractModelCalculation
{

    /**
     * Переменная хранит имя класса
     * 
     * @var string 
     */
    public $nameModule = 'OperationsForLineMC';

    /**
     * Алгоритм
     * 
     * @return bool
     */
    public function Algorithm()
    {
        /**
         * A. Подготовка к расчету
         */
        //  Это будет список таблиц - для кадой панели перечислены необходимые операции
        $operations = $this->Operations;
        //  Это будет список таблиц - для кадой панели перечислены необходимые операции
        $operationsTables = $this->OperationsTables;
        $operationsCounts = $this->OperationsCounts;
        //  Инициализация списка пустыми подсписками
        for ($i = 1; $i <= $this->ShieldPanelCount; $i++) {
            $operations[$i] = 0;
            $operationsCounts[$i] = 0;
        }
        /**
         * Б. Получение коэффициентов
         */        
        $operations = array();
        $operationsTables = array();
        $K0 = 0;
        $K1 = 0;
        $K2 = 0;
        switch ($this->Region) {
            case 'Москва':
                $K0 = 890;
                $K1 = 185;
                $K2 = 2305;
                break;
            case 'Владивосток':
                $K0 = 822;
                $K1 = 322;
                $K2 = 2143;
                break;
            case 'Казань':
                $K0 = 676;
                $K1 = 174;
                $K2 = 2321;
                break;
            case 'Новосибирск':
                $K0 = 740;
                $K1 = 240;
                $K2 = 2245;
                break;
            case 'Волгорад':
                $K0 = 740;
                $K1 = 266;
                $K2 = 2243;
                break;
            case 'Киев':
                $K0 = 761;
                $K1 = 263;
                $K2 = 2220;
                break;
            case 'Тюмень':
                $K0 = 917;
                $K1 = 206;
                $K2 = 2286;
                break;
            case 'Санкт-Петербург':
                $K0 = 891;
                $K1 = 181;
                $K2 = 2300;
                break;
            case 'Красноярск':
                $K0 = 684;
                $K1 = 186;
                $K2 = 2343;
                break;
            case 'Нижний Новгород':
                $K0 = 881;
                $K1 = 176;
                $K2 = 2324;
                break;
            case 'Воронеж':
                $K0 = 834;
                $K1 = 172;
                $K2 = 2355;
                break;
            case 'Mumbai':
                $K0 = 870;
                $K1 = 167;
                $K2 = 2326;
                break;
        }

        /**
         * В. Сверление под петли																
         * 
         * В десктопе первая панель это нижняя
         * В вебе первая это верхняя
         */
        //  Ширина петли
        $HingeWidth = 45;
        
        //  Перебираем все панели снизу вверх
        for ($n = 1; $n <= $this->ShieldPanelCount; $n++) {
            //  Номер панели
            $Panel = $n;
            $holes1 = 0;
            $holes2 = 0;
            $holesCount1 = 0;
            $holesCount2 = 0;
            //  Ищем два множества петель - для текущей панели (нижний ряд) и для следующей (верхний ряд)
            for ($j = $Panel; $j <= $Panel + 1; $j++) {
                $Y = 0;
                //  Подготовить список координат X отверстий
                $holes = array();
                $holeCount = 0;
                $CurrentPanel = $this->ShieldPanelCount;
                //  Перебираем все петли в поисках пренадлижащих указанным панелям
                for ($i = 1; $i <= ($this->HingeCount + $this->HingeForWicketCount); $i++) {
                    //  Это самая первая петля - сохраняем ее Y
                    if ($i == 1) {
                        $Y = $this->Hinges[$i - 1]["Y"];
                    } elseif ($Y != $this->Hinges[$i - 1]["Y"]) {   //  Y поменялся - переходим к следующей панели
                        $CurrentPanel = $CurrentPanel - 1;
                        //  Уже проверили нужные панели - нет смысла искать дальше
                        if ($CurrentPanel < $j) {
                            break;
                        }
                        //  Сохранить новое значение Y
                        $Y = $this->Hinges[$i - 1]["Y"];
                    }
                    //  Координата X - понадобится в дальнейшем
                    $X = $this->Hinges[$i - 1]["X"];
                    //  Нашли петли, которые принадлежать текущей панели
                    if ($CurrentPanel == $j) {
                        //  Это обычная петля (не калиточная) - добавляем ее
                        if (($this->WicketInstalled == 0) || ($X <= $this->WicketX) || ($X >= $this->WicketX + $this->WicketWidth)) {
                            //  Добавляем расстояния между петлями в порядке справа налево
                            $holes[] = $X;
                            $holeCount = $holeCount + 1;
                        }
                    }
                }
                
                //  Новый массив для обратных координат
                $ReversedHoles = array();
                //  Нет петель - не выполняем эту операцию
                if ($holeCount > 0) {
                    //  Старое значение X
                    $OldX = $this->ShieldWidth - $HingeWidth;
                    //  Создаем массив обратных координат
                    $ReversedHoles = array();
                    for ($i = 1; $i <= $holeCount; $i++) {
                        //  Текущее значение X
                        $CurrentX = $holes[$holeCount - $i];
                        //  Сохраняем обратную координату
                        $ReversedHoles[$i] = $OldX - $CurrentX;
                        //  Старое значение X
                        $OldX = $CurrentX;
                    }
                }
                //  Нижний ряд
                if ($j == $Panel) {
                    $holes1 = $ReversedHoles;
                    $holesCount1 = $holeCount;
                } else {    //  Верхний ряд
                    $holes2 = $ReversedHoles;
                    $holesCount2 = $holeCount;
                }
            }
            //  Найдем общее число операция сверления на данной панели - равно большему из двух числу петель
            if ($holesCount1 > $holesCount2) {
                $DrillCount = $holesCount1;
            } else {
                $DrillCount = $holesCount2;
            }
            //  Текущее значение координаты X
            $CurrentX1 = 0;
            $CurrentX2 = 0;
            //  Коэффициент K - отступ при сверлении первого отверстия
            $K = $K0 - $K1;
            //  Предыдущее значение координаты X
            $OldX1 = $K;
            $OldX2 = $K;
            //  Текущий индекс петли, в доке с 1 в PHP c 0
            $l1 = 1;
            $l2 = 1;
            //  Тип операции - сверление	
            $OperationType = "СВ";
            //  Перебираем все операции сверления
            for ($l = 1; $l <= $DrillCount; $l++) {
                //  Есть сверление по пазу (нижний ряд)
                if ($holesCount1 > 0) {
                    //  В доке этого условия нету
                    if (isset($holes1[$l1])) {
                        //  Следующее значение координаты X
                        $CurrentX1 = $OldX1 + $holes1[$l1];
                    }
                } else {    //  Нет сверления по пазу
                    //  Отметим это специальным значением	
                    $CurrentX1 = -1;
                }
                //  Есть сверление по шипу (верхний ряд)
                if ($holesCount2 > 0) {
                    //  В доке этого условия нету
                    if (isset($holes2[$l2])) {
                        //  Следующее значение координаты X
                        $CurrentX2 = $OldX2 + $holes2[$l2];
                    }
                } else { //  Нет сверления по шипу
                    //  Отметим это специяльным значением
                    $CurrentX2 = -1;
                }
                //  Свреление по пазу и шипу происходит одновременно
                if ($CurrentX1 == $CurrentX2) {
                    //  Одновременное сверление - нет модификатора
                    $OperationParameter1 = $CurrentX1;
                    $OperationParameter2 = "";
                    //  Перейдем к следующим петлям по обоим рядам
                    $OldX1 = $CurrentX1;
                    $OldX2 = $CurrentX2;
                    $l1 = $l1 + 1;
                    $l2 = $l2 + 1;
                } elseif ($CurrentX2 > $CurrentX1) { //  Сверление у паза раньше, чем у шипа
                    //  Сверление у паза не производится
                    if ($CurrentX1 == -1) {
                        //  Добавить только сверление у шипа
                        $OperationParameter1 = $CurrentX2;
                        $OperationParameter2 = "Ш";
                        //  Перейдем к следующей петле у паза
                        $OldX2 = $CurrentX2;
                        $l2 = $l2 + 1;
                    } else {    //  	Есть сверление у паза
                        //  Добавить только сверление у паза
                        $OperationParameter1 = $CurrentX1;
                        $OperationParameter2 = "П";
                        //  Перейдем к следующей петле у шипа
                        $OldX1 = $CurrentX1;
                        $l1 = $l1 + 1;
                    }
                } else {    //  Сверление у шипа раньше, чем у паза
                    //  Сверление у шипа не производится
                    if ($CurrentX2 == -1) {
                        //  Добавить только сверление у паза
                        $OperationParameter1 = $CurrentX1;
                        $OperationParameter2 = "П";
                        //  Перейдем к следующей петле у шипа
                        $OldX1 = $CurrentX1;
                        $l1 = $l1 + 1;
                    } else {    //  Есть сверление у шипа
                        //  Добавить только сверление у шипа
                        $OperationParameter1 = $CurrentX2;
                        $OperationParameter2 = "Ш";
                        //  Перейдем к следующей петле у паза
                        $OldX2 = $CurrentX2;
                        $l2 = $l2 + 1;
                    }
                }
                //  Добавим эту операцию к списку операций для текущей панели
                $operations[$n][] = array(
                    'OperationType' => $OperationType,
                    'OperationParameter1' => $OperationParameter1,
                    'OperationParameter2' => $OperationParameter2
                );
            }
        }
        /**
         * Г. Фрезерование окон																
         */
        //  Коэффициент - сдвиг при фрезеровании
        $K = $K0 + $K2;
        //  Тип операции - фрезерование
        $OperationType = "ФР";
        //  Перечисляем группы окон в обратном порядке - будем рассматривать N-ю группу
        foreach ($this->WindowSizes as $n => $value) {
            //  Расстояние между правым краем последнего окна в группе и правым краем панели
            $StartX = $this->ShieldWidth - $this->WindowNewPadding[$n][1] - $this->WindowSizes[$n][1]['X'] - ($this->WindowCounts[$n] - 1) * ($this->WindowSizes[$n][1]['X'] + $this->WindowNewSteps[$n][1]);
            //  Перечислим все окна в N-й группе
            for ($l = 1; $l <= count($value); $l++) {
                //  Для каждого окна - расстояние от правого его края до правого края панели
                $CurrentX = $StartX + ($l - 1) * ($this->WindowSizes[$n][1]['X'] + $this->WindowNewSteps[$n][1]);
                //  Добавляем сдвиг
                $CurrentX = $CurrentX + $K;
                //  Устанавливаем параметры операции
                $OperationParameter1 = $CurrentX;
                $OperationParameter2 = "";
                //  Добавим эту операцию к списку операций для текущей панели
                $correctOfPanelNumber = $this->ShieldPanelCount - $this->WindowPanels[$n][$l] + 1;
                $operations[$correctOfPanelNumber][] = array(
                    'OperationType' => $OperationType,
                    'OperationParameter1' => $OperationParameter1,
                    'OperationParameter2' => $OperationParameter2
                );
            }
        }
        /**
         * Д. Фрезирование под ручку
         */
        //  Есть ручка DHF09 и есть возможность ее установки в нижнюю панель
        if (($this->StepHandleInstalled || $this->StepHandleWithLogoInstalled) && ($this->WicketDHF09 !== 0) && ($this->ShieldBottomPanel >= 290)) {
            //  Коэффициент - сдвиг при фрезеровании
            $K = $K0 + $K2;
            //  Тип операции - фрезерование
            $OperationType = "ФР";
            //  Ручка устанавливается справа
            if ($this->WicketDHF09 == "Справа") {
                //  Сдвиг + расстояние до ручки от края щита + размер боковой крышки
                $OperationParameter1 = $K + 350 + floor(($this->ShieldWidth - $this->Bh) / 2);
            } else {    //  Ручка устанавливается слева
                //  Сдвиг + ширина щита - (расстояние до ручки от края щита + размер боковой крышки) + ширина ручки
                $OperationParameter1 = $K + $this->ShieldWidth - (350 + floor(($this->ShieldWidth - $this->Bh) / 2)) + 208;
            }
            $OperationParameter2 = "";
            //  Добавим эту операцию к списку операций для нижней панели
            $operations[1][] = array(
                'OperationType' => $OperationType,
                'OperationParameter1' => $OperationParameter1,
                'OperationParameter2' => $OperationParameter2
            );
        }
        /**
         * Е. Поперечная резка
         */
        //  Тип операции - поперечная резка
        $OperationType = "ПП";
        //  Для всех панелей продольная резка одинаковая
        for ($n = 1; $n <= $this->ShieldPanelCount; $n++) {
            //  Ширина щита
            $OperationParameter1 = $this->ShieldWidth;
            $OperationParameter2 = "";
            //  Добавим эту операцию к списку операций для текущей панели
            $operations[$n][] = array(
                'OperationType' => $OperationType,
                'OperationParameter1' => $OperationParameter1,
                'OperationParameter2' => $OperationParameter2
            );
        }
        //  Резка под калитку - только в случае наличия калитки
        if ($this->WicketInstalled) {
            //  Все панели, в которые встроена калитка, кроме первой и последней
            for ($n = 2; $n <= count($this->WicketPanels) - 1; $n++) {
                //  Координата правого края калитки
                $OperationParameter1 = $this->ShieldWidth - $this->WicketX - $this->WicketWidth;
                $OperationParameter2 = "";
                //  Добавим эту операцию к списку операций для текущей панели
                $operations[$n][] = array(
                    'OperationType' => $OperationType,
                    'OperationParameter1' => $OperationParameter1,
                    'OperationParameter2' => $OperationParameter2
                );
                //  Координата левого края калитки
                $OperationParameter1 = $this->ShieldWidth - $this->WicketX;
                $OperationParameter2 = "";
                //  Добавим эту операцию к списку операций для текущей панели
                $operations[$n][] = array(
                    'OperationType' => $OperationType,
                    'OperationParameter1' => $OperationParameter1,
                    'OperationParameter2' => $OperationParameter2
                );
            }
        }

        /**
         * Ж. Сортировка операций
         */
        //  Сортируем операции по всем панелям
        for ($n = 1; $n <= $this->ShieldPanelCount; $n++) {
             $operations[$n] = $this->multiSort($operations[$n], 'OperationParameter1');
        }
        /**
         * З. Продольная резка
         */
        //  Тип операции - продольная резка
        $OperationType = "ПР";
        //  Нижнюю панель необходимо обрезать
        if ($this->ShieldBottomPanelIsCut == 1) {
            //  Сколько необходимо отрезать
            $OperationParameter1 = $this->ShieldWholeBottomPanel - $this->ShieldBottomPanel;
            $OperationParameter2 = "";
            //  Добавим эту операцию к списку операций для нижней панели
            $operations[1][] = array(
                'OperationType' => $OperationType,
                'OperationParameter1' => $OperationParameter1,
                'OperationParameter2' => $OperationParameter2
            );
        }
        //  Верхнюю панель необходимо обрезать
        if ($this->ShieldTopPanelIsCut == 1) {
            //  Сколько необходимо отрезать
            $OperationParameter1 = /*$this->ShieldWholeTopPanel - */$this->ShieldTopPanel;
            $OperationParameter2 = "";
            //  Добавим эту операцию к списку операций для верхней панели
            $operations[$this->ShieldPanelCount][] = array(
                'OperationType' => $OperationType,
                'OperationParameter1' => $OperationParameter1,
                'OperationParameter2' => $OperationParameter2
            );
        }
        /**
         * И. Формирование таблиц для листа "Раскрой для линии"
         */
        //  Формируем таблицы для каждой из панелей
        for ($i = 1; $i <= $this->ShieldPanelCount + 1; $i++) {
            $operationsTables[$i] = 0;
        }
        for ($i = 1; $i <= $this->ShieldPanelCount; $i++) {
            //  Установка заголовков столбцов
            $operationsTables[$i] = array('1' => array('1' => '№', '2' => 'СВ', '3' => 'ФР', '4' => 'ПП', '5' => 'ПР'));
            
            //  Добавляем операции в таблицы
            foreach($operations[$i] as $l => $panelOperations){
                $operationsCounts[$i] = count($operations[$i]);
                if (isset($operations[$i][$l])) {
                    //  Заголовок строки	
                    $operationsTables[$i][$l + 2][1] = $l + 1;
                    //  Операция одна и та же - важен столбец, в который она записывается
                    $operation = round($operations[$i][$l]['OperationParameter1']) . $operations[$i][$l]['OperationParameter2'];
                    if ($operations[$i][$l]['OperationType'] == "СВ") {
                        $operationsTables[$i][$l + 2][2] = $operation;
                    } else {
                        $operationsTables[$i][$l + 2][2] = '';
                    }
                    if ($operations[$i][$l]['OperationType'] == "ФР") {
                        $operationsTables[$i][$l + 2][3] = $operation;
                    } else {
                        $operationsTables[$i][$l + 2][3] = '';
                    }
                    if ($operations[$i][$l]['OperationType'] == "ПП") {
                        $operationsTables[$i][$l + 2][4] = $operation;
                    } else {
                        $operationsTables[$i][$l + 2][4] = '';
                    }
                    if ($operations[$i][$l]['OperationType'] == "ПР") {
                        $operationsTables[$i][$l + 2][5] = $operation;
                    } else {
                        $operationsTables[$i][$l + 2][5] = '';
                    }
                }
            }
        }
        $this->Operations = $operations;
        $this->OperationsTables = $operationsTables;
        $this->OperationsCounts = $operationsCounts;

        return true;
    }

    /**
     * Название модуля
     * 
     * @return string
     */
    public function getTitle()
    {
        return 'Расчет операций для линии';
    }
    
    /**
    * Сортирует массивы.
    * Описание можно найти тут:
    * http://php.net/manual/ru/function.usort.php#105764
    * 
    * @return array
    */
    function multiSort() {
        //get args of the function
        $args = func_get_args();
        $c = count($args);
        if ($c < 2) {
            return false;
        }
        //get the array to sort
        $array = array_splice($args, 0, 1);
        $array = $array[0];
        //sort with an anoymous function using args
        usort($array, function($a, $b) use($args) {

            $i = 0;
            $c = count($args);
            $cmp = 0;
            while($cmp == 0 && $i < $c)
            {
                $cmp = strcmp($a[ $args[ $i ] ], $b[ $args[ $i ] ]);
                $i++;
            }

            return $cmp;

        });

        return $array;

    }

}
