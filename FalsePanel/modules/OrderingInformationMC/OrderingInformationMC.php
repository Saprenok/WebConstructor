<?php

/**
 * Информация о заказе
 * PHP version 5.5
 * @category Yii
 * @author   Charnou Vitaliy <graffov87@gmail.com>
 */
class OrderingInformationMC extends AbstractModelCalculation
{

    /**
    * Название модуля
    * 
    * @var string Название модуля
    */
    public $nameModule = 'OrderingInformationMC';

    /**
    * Алгоритм
    * 
    * @return bool
    */
    public function Algorithm()
    {
        //Закгрузка данных из 1С по Тип щита
        $TypeS = Yii::app()->container->TypeS;
        //проверяем на наличие существование значения в $TypeS
        if (empty($TypeS)) {
            $TypeSFrom1C = Yii::app()->container->TypeSFrom1C;
            if (!empty($TypeSFrom1C)) {
                switch ($TypeSFrom1C) {
                    case "алюминиевый":
                        $TypeS = "Алюминиевый";
                        break;
                    case "для секционных ворот":
                        $TypeS = "Для секционных ворот";
                        break;
                    case "панорамная панель":
                        $TypeS = "Панорамная панель";
                        break;
                }
            }  
            $this->TypeS = $TypeS;
        }
        
        //загрузка данных из 1С по материалу стен
        $MaterialWalls = Yii::app()->container->MaterialWalls;
        //проверяем на наличие существование значения в $MaterialWalls
        if (empty($MaterialWalls)) {
            $MaterialWallsFrom1C = Yii::app()->container->MaterialWallsFrom1C;
            if (!empty($MaterialWallsFrom1C)) {
                switch ($MaterialWallsFrom1C) {
                    case "cp_wall_material_concrete":
                        $MaterialWalls = "бетон";
                        break;
                    case "cp_wall_material_wood":
                        $MaterialWalls = "дерево";
                        break;
                    case "cp_wall_material_brick":
                        $MaterialWalls = "кирпич";
                        break;
                    case "cp_wall_material_metal":
                        $MaterialWalls = "металл";
                        break;
                    case "cp_wall_material_panel":
                        $MaterialWalls = "панели стеновые";
                        break;
                    case "cp_wall_material_plate":
                        $MaterialWalls = "плиты";
                        break;
                    case "cp_wall_material_perforated_brick":
                        $MaterialWalls = "пустотелый кирпич";
                        break;
                    case "cp_wall_material_siding":
                        $MaterialWalls = "сайдинг";
                        break;
                }
            }  
            $this->MaterialWalls = $MaterialWalls;
        } else {
            $this->MaterialWalls = Yii::app()->container->MaterialWalls;
        }
        
        //загрузка данных из 1С по материалу потолка
        $MaterialCeil = Yii::app()->container->MaterialCeil;
        //проверяем на наличие существование значения в $MaterialCeil
        if (empty($MaterialCeil)) {
            $MaterialCeilFrom1C = Yii::app()->container->MaterialCeilFrom1C;
            if (!empty($MaterialCeilFrom1C)) {
                switch ($MaterialCeilFrom1C) {
                    case "cp_ceiling_material_concrete":
                        $MaterialCeil = "бетон";
                        break;
                    case "cp_ceiling_material_wood":
                        $MaterialCeil = "дерево";
                        break;
                    case "cp_ceiling_material_brick":
                        $MaterialCeil = "кирпич";
                        break;
                    case "cp_ceiling_material_metal":
                        $MaterialCeil = "металл";
                        break;
                    case "cp_ceiling_material_perforated_brick":
                        $MaterialCeil = "пустотелый кирпич";
                        break;
                }
            }  
            $this->MaterialCeil = $MaterialCeil;
        } else {
            $this->MaterialCeil = Yii::app()->container->MaterialCeil;
        }
        
        //загрузка данных из 1С по материалу притолоки
        $MaterialLintel = Yii::app()->container->MaterialLintel;
        //проверяем на наличие существование значения в $MaterialLintel
        if (empty($MaterialLintel)) {
            $MaterialLintelFrom1C = Yii::app()->container->MaterialLintelFrom1C;
            if (!empty($MaterialLintelFrom1C)) {
                switch ($MaterialLintelFrom1C) {
                    case "cp_lintel_material_concrete":
                        $MaterialLintel = "бетон";
                        break;
                    case "cp_lintel_material_wood":
                        $MaterialLintel = "дерево";
                        break;
                    case "cp_lintel_material_brick":
                        $MaterialLintel = "кирпич";
                        break;
                    case "cp_lintel_material_metal":
                        $MaterialLintel = "металл";
                        break;
                    case "cp_lintel_material_panel":
                        $MaterialLintel = "панели стеновые";
                        break;
                    case "cp_lintel_material_perforated_brick":
                        $MaterialLintel = "пустотелый кирпич";
                        break;
                }
            }  
            $this->MaterialLintel = $MaterialLintel;
        } else {
            $this->MaterialLintel = Yii::app()->container->MaterialLintel;
        }
        
        
        return true;
    }

    /**
    * Имя модуля
    * 
    * @return string возвращает имя модуля
    */
    public function getTitle()
    {
        return 'Информация о заказе';
    }
}