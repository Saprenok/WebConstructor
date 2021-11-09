<?php

/**
 * Информация о месте монтажа
 * PHP version 5.5
 * @category Yii
 * @author   Charnou Vitaliy <graffov87@gmail.com>
 */
class InstallationPlaceInformationMC extends AbstractModelCalculation
{
    /**
    * Переменная хранит имя класса
    * 
    * @var string 
    */
    public $nameModule = 'InstallationPlaceInformationMC';

    /**
    * Алгоритм
    * 
    * @return bool
    */
    public function Algorithm()
    {
        $this->MaterialLintel         = $this->formMaterialLintel;
        $this->MaterialCeil           = $this->formMaterialCeil;
        $this->MaterialWalls          = $this->formMaterialWalls;
        $this->MaterialPosts          = $this->formMaterialPosts;
        $this->MaterialGroundSurface  = $this->formMaterialGroundSurface;
        $this->MaterialFence          = $this->formMaterialFence;
        $this->MaterialGateFrameColor = $this->formMaterialGateFrameColor;
        
        $this->MaterialLintelTranslate         = Yii::t('steps', $this->MaterialLintel);
        $this->MaterialCeilTranslate           = Yii::t('steps', $this->MaterialCeil);
        $this->MaterialWallsTranslate          = Yii::t('steps', $this->MaterialWalls);
        $this->MaterialPostsTranslate          = Yii::t('steps', $this->MaterialPosts);
        $this->MaterialGroundSurfaceTranslate  = Yii::t('steps', $this->MaterialGroundSurface);
        $this->MaterialFenceTranslate          = Yii::t('steps', $this->MaterialFence);
        $this->MaterialGateFrameColorTranslate = Yii::t('steps', $this->MaterialGateFrameColor);

        return true;
    }

    /**
    * Название модуля
    * 
    * @return string
    */
    public function getTitle()
    {
        return Yii::t('steps', 'Информация о месте монтажа');
    }
} 