<?php

/**
 * Модуль расчета доп комплектации
 * @category Yii 
 */
class AdditionalComplectationMC extends AbstractModelCalculation
{

    /**
     * Переменная хранит имя класса
     * 
     * @var string 
    */
    public $nameModule = 'AdditionalComplectationMC';
    private $SheildComplectationCode = array();

    /**
     * Список кодов приводов и комплектации привода без нолей в начале
     * 
     * @var array
     */
    private $zeroDeletedDriveComplectation = array();

    /**
     * Алгоритм
     * 
     * @return bool
     */
    public function Algorithm()
    {
        $modal = array();
        
        /*
         * удаление нулей из списка применненых приводов 
         * для правильного сравнения со значениями из таблицы 1
         * */
        for ($i = 0; $i < count($this->DriveComplectation); $i++) {
            if (mb_substr($this->DriveComplectation[$i], 0, 1, 'UTF-8') === '0') {     //проверка, начинается ли строка с 0
                $this->zeroDeletedDriveComplectation[$i] = (string) ((int) $this->DriveComplectation[$i]);
            } else {
                $this->zeroDeletedDriveComplectation[$i] = $this->DriveComplectation[$i];
            }
        }
        //     $this->SheildComplectationCode = $this->getCodeComplecation($this->formSheildComplectation);
        $complectaction = $this->ShieldComplectation;
        $complectaction = $this->preparedShieldComplectationData($complectaction, $this->formViewAllComplectation);
        $this->ShieldComplectation = $complectaction;
        $complectationCount = $this->ShieldComplectationCount;
        $complectationCount = $this->preparedShieldComplectationDataСount($complectationCount, $this->formViewAllComplectation);
        $this->ShieldComplectationCount = $complectationCount;
        $this->ManualChainDriveInstalled = $this->isManualChainDriveInstalled();
        $this->ManualChainWithoutGearDriveInstalled = $this->checkInstalledDrived(array('244', '13954'));
        $this->PhotocellsInstalled = $this->isPhotocellsInstalled();
        $this->StepHandleInstalled = $this->isStepHandleInstalled();
        $this->RubberInstalled = $this->isRubberInstalled();
        $this->StepHandleWithLogoInstalled = $this->checkInstalled(array('145092'));
        $this->AluminiumHandleInstalled = $this->checkInstalled(array('CB000159866'));
        $this->OneSideLockInstalled = $this->checkInstalled(array('131699', '133525', '131698', '103904', '230','CB000183095'));
        $this->OneSideLockKitInstalled = $this->checkInstalled(array('131699', '133525', '131698','CB000183095'));
        $this->SpringProtectionInstalled = $this->checkInstalled(array('11262', '14699', '69', '14700'));
        $this->CableProtectionInstalled = $this->checkInstalled(array('182'));
        $this->CableProtectionLowInstalled = $this->checkInstalled(array('14671'));
        $this->CloserDoorMaxInstalled = $this->checkInstalled(array('102459'));
        $this->CloserTSInstalled = $this->checkInstalled(array('115197'));
        $this->CloserHiddenInstalled = $this->checkInstalled(array('148021'));

        $this->DriveD600Installed = $this->checkInstalledDrived(array('120282'));
        $this->DriveD600KITInstalled = $this->checkInstalledDrived(array('120857'));
        $this->DriveD1000Installed = $this->checkInstalledDrived(array('120388'));
        $this->DriveD1000KITInstalled = $this->checkInstalledDrived(array('120858'));
        $this->DriveSE500KITInstalled = $this->checkInstalledDrived(array('139290'));
        $this->DriveISE500KITInstalled = $this->checkInstalledDrived(array('134081'));
        $this->DriveSE750KITInstalled = $this->checkInstalledDrived(array('126543'));
        $this->DriveSE1200KITInstalled = $this->checkInstalledDrived(array('131197'));

        $this->GuidesSK3600Installed = $this->checkInstalledDrived(array('103524'));
        $this->GuidesSK4600Installed = $this->checkInstalledDrived(array('130190'));
        $this->SlidingBusDoorMaxInstalled = $this->checkInstalled(array('114672'));
        $this->SlidingBusTSInstalled = $this->checkInstalled(array('102460'));
        
        $CalcBottomPanel = 385;
        if ($this-> ShieldBottomPanelIsCut) {
            $CalcBottomPanel = $this->ShieldBottomPanel;
        }
        
        if (($this->StepHandleInstalled || $this->StepHandleWithLogoInstalled) && $CalcBottomPanel < 345) {
            if (!$this->CheckDHF09Modal) {
                $modal[] = array(
                    "text" => Yii::app()->container->Loc(110028),
                    "paramModule" => "CheckDHF09Modal",
                    "paramBool" => "CheckDHF09Bool"
                );
            }
        }
        
        $this->modalDialogAfterForm = $modal;

        return true;
    }

    /**
     * Название модуля
     * 
     * @return string
     */
    public function getTitle()
    {
        return 'Расчет дополнительной комплектации';
    }

    /**
     * Проверяет наличие совпадений массива приводов с массивом передаваемым на вход ф-и.
     * Возвращает true если есть совпадения
     * 
     * @param mixed $arrayBool Массив кодов элементов
     * 
     * @return bool
     */
    private function checkInstalled($arrayBool)
    {
        if (array_intersect($arrayBool, $this->ShieldComplectation)) {
            return true;
        }

        return false;
    }

    private function checkInstalledDrived($arrayBool)
    {
        if (array_intersect($arrayBool, $this->zeroDeletedDriveComplectation)) {
            return true;
        }

        return false;
    }

    /**
     * Если установлен профиль Rubber функция вернет true, иначе false
     * 
     * @return bool
     */
    private function isRubberInstalled()
    {
        $codeWithRubberInstalled = array('120844', 'CB000252505');
        if (array_intersect($codeWithRubberInstalled, $this->zeroDeletedDriveComplectation)) {
            return true;
        }

        return false;
    }

    /**
     * Если установлен ручной цепной привод функция вернет true, иначе false
     * 
     * @return bool
     */
    private function isManualChainDriveInstalled()
    {
        $codeWithManualChainDriveInstalled = array('244', '13954', '15520');
        if (array_intersect($codeWithManualChainDriveInstalled, $this->zeroDeletedDriveComplectation)) {
            return true;
        }

        return false;
    }

    /**
     * Если установлены фотоэлементы функция вернет true, иначе false
     * 
     * @return bool
     */
    private function isPhotocellsInstalled()
    {
        $articleWithPhotocellsInstalled = array('107088', '100575', '14562', '11877', '1101', '15276', '11816', '13867');
        if (array_intersect($articleWithPhotocellsInstalled, $this->zeroDeletedDriveComplectation)) {
            return true;
        }

        return false;
    }

    /**
     * Если установлена ручка-ступенька функция вернет true, иначе false
     * 
     * @return bool
     */
    private function isStepHandleInstalled()
    {
        $codeWithStepHandleInstalled = array('123014');
        if (array_intersect($codeWithStepHandleInstalled, $this->ShieldComplectation)) {
            return true;
        }

        return false;
    }

    /**
     * Соединяет два массива с данными о комплектации щита
     * 
     * @param array $array
     * @param array $data
     * 
     * @return array Объединенный массив
     */
    private function preparedShieldComplectationData($array, $data)
    {
        if (!is_array($array)) {
            $preparedData = array();
        } else {
            $preparedData = $array;
        }
        $data = $data ? json_decode($data, true) : array();
        foreach ($data as $row) {
            /*     $preparedData[$row['code']] = array(
              'article' => $row['article'],
              'title' => $row['title'],
              'unit' => $row['unit'],
              'count' => $row['count'],
              'specification_id' => $row['specification_id'],
              'cards_id' => explode(',', $row['cards_id'])
              ); */
            $preparedData[] = (is_numeric($row['code'])) ? (int) $row['code'] : $row['code'];
        }

        return $preparedData;
    }

    /**
     * Соединяет два массива с данными о количестве комплектации щита
     * 
     * @param array $array
     * @param array $data
     * 
     * @return array Объединенный массив
     */
    private function preparedShieldComplectationDataСount($array, $data)
    {
        if (!is_array($array)) {
            $preparedData = array();
        } else {
            $preparedData = $array;
        }
        $data = $data ? json_decode($data, true) : array();
        foreach ($data as $row) {
            $preparedData[(is_numeric($row['code'])) ? (int) $row['code'] : $row['code']] = $row['count'];
        }

        return $preparedData;
    }

}
