<?php

/**
 * порядок вызова форм, модулей
 * PHP version 5.5 1
 * @category Yii
 * @author    Charnou Vitaliy <graffov87@gmail.com>
 */
class ListModulesFalsePanel extends AbstractProduct
{

    /**
     * Массив модулей форм
     * @var array
     */
    private $interface;

    /**
     * Массив модулей расчетов
     * @var array
     */
    private $calculation;

    /**
     * массив очередностей вызова форм
     * @var array
     */
    private $steps;

    /**
     * массив модулей для использования в формулах
     * @var array
     */
    private $forFormuls;

    /**
     * массив модулей для использования в расчетах
     *
     * @var array
     */
    private $order;

    /**
     * массив модулей для использования в расчетах в конце
     *
     * @var array
     */
    private $losts;

    /**
     * Конструторк класса
     *
     * заполняет поля данными
     *
     */
    public function __construct()
    {
        $this->interface = array(
            "GateFrameMI",
            "ShieldOptionsMI",
            "TypePanelsColorShieldMI",
            "EmbeddedObjectsMI",
            'ColorEdgingMI',
            "AdvancedOptionsMI",
            "AdditionalComplectationMI",
            "ExtraMaterialsMI",
            "ServiceMI",
            "InstallationPlaceInformationMI",

        );
        $this->calculation = array(
            "AdditionalComplectationMC",
            'HelperConfigurationMC',
            'GateFrameMC',
            'FormGateFrameMC',
            'FormShieldOptionsMC',
            'FormShieldMC',
            'ProfileSelectionMC',
            "ShieldMC",
            "ServiceMC",
            'ShieldColorMC',
            "OptimalPanelsCuttingMC",
            "EmbeddedObjectsMC",
            "WindowsCalculationMC",
            'ColorEdgingMC',
            "DeleteObjectsMC",
            "ExtraMaterialsMC",
            'FormExtraMaterialsMC',
            'FormExtraServicesMC',
            "CuttingPostCheckMC",
            "OperationsForLineMC",
            'AdvancedOptionsMC',
            "ShieldWeightCalculationMC",
            "InstallationPlaceInformationMC",
            "ProfileCalculationMC",
            'DrawingsMC',
            'OrderingInformationMC',
            'EdgingProfileMC',
            'UsersInformationMC',
            'HandleCalculationMC',
            'FromSandwichParametersMC',
            'FormPanoramicParametersMC',
            'PanoramicCalculationMC',
            'AlumShieldMC',
            "CalcReshMC",
            "PanelsCuttingWithInfillsMC",
        );
        $this->steps = array(
            "GateFrameMI",
            "ShieldOptionsMI",
            "TypePanelsColorShieldMI",
            "EmbeddedObjectsMI",
            'ColorEdgingMI',
            "AdvancedOptionsMI",
            "AdditionalComplectationMI",
            "ExtraMaterialsMI",
            "ServiceMI",
            "InstallationPlaceInformationMI",
        );
        $this->forFormuls = array(
            "AdditionalComplectationMC",
            'HelperConfigurationMC',
            'FormGateFrameMC',
            'FormShieldOptionsMC',
            'FormPanoramicParametersMC',
            'FromSandwichParametersMC',
            'GateFrameMC',
            'FormShieldMC',
            'ProfileSelectionMC',
            "ShieldMC",
            'AdvancedOptionsMC',
            "ServiceMC",
            "OptimalPanelsCuttingMC",
            'EmbeddedObjectsMC',
            "WindowsCalculationMC",
            'ColorEdgingMC',
            "DeleteObjectsMC",
            "ExtraMaterialsMC",
            'FormExtraMaterialsMC',
            'FormExtraServicesMC',
            'ShieldColorMC',
            "CuttingPostCheckMC",
            "OperationsForLineMC",
            "ShieldWeightCalculationMC",
            "InstallationPlaceInformationMC",
            "ProfileCalculationMC",
            'DrawingsMC',
            'OrderingInformationMC',
            'EdgingProfileMC',
            'UsersInformationMC',
            'HandleCalculationMC',
            'SystemVariablesMC',
            'PanGlassSelectionMC',
            'PanoramicCalculationMC',
            'PanPanelsSelectionMC',
            'AlumShieldMC',
            "CalcReshMC",
            "PanelsCuttingWithInfillsMC",
        );
        $this->order = array(
            'GateFrameMC',
            'ProfileSelectionMC',
            'ProfileCalculationMC',
            'ShieldMC',
            'OptimalPanelsCuttingMC',
            'EdgingProfileMC',
            'OperationsForLineMC',
            'ShieldWeightCalculationMC'
        );

        $this->losts = array(
            'DrawingsMC',
        );

        /*$this->beforeSend = array(
            'BeforeSendMC',
        );*/
    }

    /**
     * Возращает массив порядка вызова форм
     *
     * @return array
     */
    public function getSteps()
    {
        return $this->steps;
    }

    /**
     * ВОзращает массив модулей расчета
     *
     * @return array
     */
    public function getCalculation()
    {
        return $this->calculation;
    }

    /**
     * Возращает массив модулей форм
     *
     * @return array
     */
    public function getInterface()
    {
        return $this->interface;
    }

    /**
     * ВОзрашает массив для формул
     *
     * @return array
     *
     */
    public function getforFormuls()
    {
        return $this->forFormuls;
    }

    /**
     * Возхращает массив модулей для серийного расчета
     *
     * @return array
     */
    public function getforOrders()
    {
        return $this->order;
    }

    /**
     * Возращает массив модулей последних к выполнению
     *
     * @return array
     */
    public function getLosts()
    {
        return $this->losts;
    }

}
