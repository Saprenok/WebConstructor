<?php

/**
 * Модуль расчета Окантовка профилем
 * @category Yii
 */
class AlumShieldMC extends AbstractModelCalculation
{
    /**
    * Переменная хранит имя класса
    * 
    * @var string 
    */
    public $nameModule = 'AlumShieldMC';
    
    /**
    * Алгоритм
    * 
    * @return bool
    */
    public function Algorithm()
    {
        if ($this->TypeF == "Алюминиевый") {
            $this->gainEdgingProfileAlgorithm();
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
        return 'Окантовка профилем';
    }

    /**
    * Отрисовка окантовки профилем
    * 
    * @return
    */
    private function gainEdgingProfileAlgorithm()
    {
        $col_sh = "неокрашенный";
        $this->infoArray = array();
        $profiles = array();
        $H = $this->ShieldHeight;
        $W = $this->ShieldWidth;
        $Bh = $this->Bh;
        $pwi = $this->ShieldWholeBottomPanel;
        $w_pol = 0;
        $w_resh = 0;
        $x = 0;
        $str = substr($this->Index, -3);
        $n_nak = 0;
        //высота
        $resh = $this->resh;
        $piki = $this->piki;
        $piki_min = $this->piki_min;
        $this->HeightTopProfiles = 0;
        $this->HeightTopProfilesPiki = 0;
        $this->HeightAlumProfilesShield = 0;
        $this->HeightTopTube = 0;
        $n_pr = 0;
        //счетчик профилей
        $i = 1;
        $pik = 0;
        $vel = 0;
        $l_pik = 0;
        $l_reshet = 0;
        $n_resh = $this->n_resh;;
        //код номенклатуры используемой панели
        $panel_code = "00000123149";
        $CurrentPanelType = $this->CurrentPanelType;
        if (!empty($CurrentPanelType)) {
            //поиск кода элемента панели
            $model = ShieldModel::model()->find(array(
                'condition' => 'id=:id',
                'params' => array(
                    ':id' => $CurrentPanelType,
                )
            ));
            $sizes = ShieldGroupSizesModel::model()->findAll('shiled_group_id=' . $model->id);
            foreach ($sizes as $value) {
                $panel_code = $value->panel_code;
            }
        }

        if ($this->VidS == "Прямоугольный") {
            $n_sam = 0;
            $n_sam_ug = 0;

            //отбираем первые 2 символа из индекса
            $str = substr($str, -3, 2);

            if ($str == "02") {
                $a = (int)($W/140);
                $n_sht = $a;
                $n_sam = $n_sam + $a*4 + 8;
                $x =  $W - $a*140;
                $vel = $resh + 20;
                
                //for ($z=1; $z <= $a; $z++){
                for ($z = 1; $z <= $a; $z++){
                    $profiles[$i]["X"]      = $x;
                    //$profiles[$i]["Y"]      = -$vel - 20;
                    $profiles[$i]["Y"]      = -0;
                    $profiles[$i]["Width"]  = 80;
                    $profiles[$i]["Height"] = $vel;
                    $profiles[$i]["Packing"] = "Упаковка.Щит";
                    $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                    $profiles[$i]["Code"] = $this->checkShtapik($col_sh);//"CB000186634";
                    $profiles[$i]["Angle"] = 90;
                    $x = $x + 140;
                    $i++;
                }
                $this->HeightTopProfiles = $vel;
            }
            if ($str == "03") {
                $a = (int)($W/100);
                $n_sht = $a;
                $n_sam = $n_sam + 8;
                $n_nak = $a + 2;
                $x =  $W - $a*100;
                $vel = $resh + 20;
                for ($z=1; $z <= $a; $z++){
                    $profiles[$i]["X"]      = $x;
                    //$profiles[$i]["Y"]      = -$vel - 20;
                    $profiles[$i]["Y"]      = 0;
                    $profiles[$i]["Width"]  = 22;
                    $profiles[$i]["Height"] = $vel;
                    $profiles[$i]["Packing"] = "Упаковка.Щит";
                    $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                    $profiles[$i]["Code"] = $this->checkProfileTube($col_sh);
                    $profiles[$i]["Angle"] = 90;
                    $x = $x + 100;
                    $i++;
                }
                $this->HeightTopProfiles = $vel;
            }
            //щит с пиками сверху
            if ($str == "04" || $str == "06") {
                $a = (int)($W/100);
                $n_sht = $a;
                $n_sam = $n_sam + 8;
                $n_nak = $a + 2;
                $n_ven = ((int)($a/2))*2;
                $x =  $W - $a*100;
                $vel = $resh + 20;
                $pik = $piki + 10 - 92;
                for ($z = 1; $z <= $a; $z++){
                    $profiles[$i]["X"]      = $x;
                    //$profiles[$i]["Y"]      = -$vel - 20;
                    $profiles[$i]["Y"]      = $pik;
                    $profiles[$i]["Width"]  = 22;
                    $profiles[$i]["Height"] = $vel;
                    $profiles[$i]["Packing"] = "Упаковка.Щит";
                    $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                    $profiles[$i]["Code"] = $this->checkProfileTube($col_sh);
                    $profiles[$i]["Angle"] = 90;
                    $x = $x + 100;
                    $i++;
                }
                $x =  $W - $a*100  - 100;
                for ($z = 0; $z <= $a + 1; $z++){
                    $profiles[$i]["X"]      = $x;
                    //$profiles[$i]["Y"]      = -$pik;
                    $profiles[$i]["Y"]      = 0;
                    $profiles[$i]["Width"]  = 22;
                    $profiles[$i]["Height"] = $pik;
                    $profiles[$i]["Packing"] = "Упаковка.Щит";
                    $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                    $profiles[$i]["Code"] = $this->checkProfileTube($col_sh);
                    $profiles[$i]["Angle"] = 90;
                    $x = $x + 100;
                    $i++;
                }
                $this->HeightTopProfiles = $vel;
                $this->HeightTopProfilesPiki = $pik;
            }
            
            //щит с пиками сверху
            if ($str == "05" || $str == "07") {
                $a = (int)($W/100);
                $n_sht = $a;
                $n_sam = $n_sam + 8;
                $n_nak = $a + 2;
                $n_ven = ((int)($a/2))*2;
                $x =  $W - $a*100;
                $vel = $resh + 24;
                $pik = $piki + 68 - 144;
                $l_zap = 0;
                $x =  -40;
                $Bh = $this->Bh;
                for ($z=0; $z <= $a + 1; $z++){
                    $l_pik = round(4*($pik - ($piki_min - 92))*($z*100/$Bh - (pow($z*100, 2))/(pow($Bh, 2))));
                    $l_zap = $l_zap + ($piki_min - 92) + $l_pik;
                    $profiles[$i]["X"]      = $x;
                    $profiles[$i]["Y"]      = $pik - ($piki_min - 92 + $l_pik);
                    $profiles[$i]["Width"]  = 22;
                    if ($z == 0 || $z == $a + 1)
                        $profiles[$i]["Height"] = $piki_min - 92 + $l_pik;
                    else 
                        $profiles[$i]["Height"] = $piki_min - 92 + $l_pik + $vel;
                        
                    $profiles[$i]["Packing"] = "Упаковка.Щит";
                    $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                    $profiles[$i]["Code"] = $this->checkProfileTube($col_sh);
                    $profiles[$i]["Angle"] = 90;
                    $x = $x + 100;
                    $i++;
                }
                $this->HeightTopProfiles = $vel;
                $this->HeightTopProfilesPiki = $pik;
            }
            
            $str = substr($this->Index, -3);
            if ($str == "002" || $str == "022" || $str == "032" || $str == "042" || $str == "052" || $str == "062" || $str == "072") {
                $a = (int)($W/95);
                $n_pr = $a;
                $n_sam = $n_sam + $a*2;
                $x =  0;
                for ($z=1; $z <= $a; $z++){
                    $profiles[$i]["X"]      = $x;
                    $profiles[$i]["Y"]      = $vel + $pik;
                    $profiles[$i]["Width"]  = 95;
                    $profiles[$i]["Height"] = $H;
                    $profiles[$i]["Packing"] = "Упаковка.Щит";
                    $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                    $profiles[$i]["Code"] = $this->checkShtapikWhole($col_sh);
                    $profiles[$i]["Angle"] = 90;
                    $x = $x + 95;
                    $i++;
                }
                if ($W - $x - 5 > 0) {
                    $n_pr = $n_pr + 2;
                    $profiles[$i]["X"]      = $x;
                    $profiles[$i]["Y"]      = $vel + $pik;
                    $profiles[$i]["Width"]  = $W - $x - 5;
                    $profiles[$i]["Height"] = $H;
                    $profiles[$i]["Packing"] = "Упаковка.Щит";
                    $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                    $profiles[$i]["Code"] = $this->checkShtapikWhole($col_sh);
                    $profiles[$i]["Angle"] = 90;
                    $i++;
                }
                //$this->HeightTopProfiles = $H;
                $this->HeightAlumProfilesShield = $H;
            }
            if ($str == "003" || $str == "023" || $str == "033" || $str == "043" || $str == "053" || $str == "063" || $str == "073") {
                $a = (int)($H/95);
                $n_pr = $a;
                $n_sam = $n_sam + $a*2;
                $y =  0;
                for ($z=1; $z <= $a; $z++){
                    $profiles[$i]["X"]      = 0;
                    $profiles[$i]["Y"]      = $vel + $pik + $y;
                    $profiles[$i]["Width"]  = 95;//$W;
                    $profiles[$i]["Height"] = $W;//95;
                    $profiles[$i]["Packing"] = "Упаковка.Щит";
                    $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                    $profiles[$i]["Code"] = $this->checkShtapikWhole($col_sh);
                    $profiles[$i]["Angle"] = 0;
                    $profiles[$i]["LeftToRight"] = 1;
                    $y = $y + 95;
                    $i++;
                }
                if ($H - $y - 5 > 0) {
                    $n_pr = $n_pr + 2;
                    $profiles[$i]["X"]      = 0;
                    $profiles[$i]["Y"]      = $vel + $pik + $y;
                    $profiles[$i]["Width"]  = $H - $y - 5;//$W;
                    $profiles[$i]["Height"] = $W;//$H - $y - 5;
                    $profiles[$i]["Packing"] = "Упаковка.Щит";
                    $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                    $profiles[$i]["Code"] = $this->checkShtapikWhole($col_sh);
                    $profiles[$i]["Angle"] = 0;
                    $profiles[$i]["LeftToRight"] = 1;
                    $i++;
                }
                $this->HeightAlumProfilesShield = $H;
            }
            if ($str == "004" || $str == "024" || $str == "034" || $str == "044" || $str == "054" || $str == "064" || $str == "074") {
                $a = ceil(($H + $W)/134.4);
                $n_pr = $a;
                $n_sam = $n_sam + $a*2;
                $l_zap =  0;
                for ($z = 1; $z <= $a; $z++){
                    if ($this->naklon == "Вправо") {
                        if ($x <= $H) {
                            if ($x + 134.4 < $H) {
                                if ($x == 0) {
                                    $Li = sqrt(2*(pow(($x + 141.4), 2)));
                                } else {
                                    if ($x + 134.4 > $W) {
                                        $Li = sqrt(2*(pow($W, 2))) + 95;
                                    } else {
                                        $Li = sqrt(2*(pow($x + 134.4, 2)));
                                    }
                                }
                                $l_zap = $l_zap + $Li;
                                //file.shit.adddetal 0, "ШтапикСпл", -67, x+134.4, 95, Li, 45, 128, RGB(210, 210, 210)
                                $profiles[$i]["X"]      = -67;
                                $profiles[$i]["Y"]      = $x + 134.4;
                                $profiles[$i]["Width"]  = 95;
                                $profiles[$i]["Height"] = round($Li);
                                $profiles[$i]["Packing"] = "Упаковка.Щит";
                                $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                                $profiles[$i]["Code"] = $this->checkShtapikWhole($col_sh);
                                $profiles[$i]["Angle"] = 45;
                                $i++;
                            } else {
                                if ($x > $W) {
                                    $Li = sqrt(2*(pow($W, 2))) + 95;
                                    $l_zap = $l_zap + $Li;
                                    //file.shit.adddetal 0, "ШтапикСпл", -67, x+134.4, 95, Li, 45, 128, RGB(210, 210, 210)
                                    $profiles[$i]["X"]      = -67;
                                    $profiles[$i]["Y"]      = $x + 134.4;
                                    $profiles[$i]["Width"]  = 95;
                                    $profiles[$i]["Height"] = round($Li);
                                    $profiles[$i]["Packing"] = "Упаковка.Щит";
                                    $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                                    $profiles[$i]["Code"] = $this->checkShtapikWhole($col_sh);
                                    $profiles[$i]["Angle"] = 45;
                                    $i++;
                                } else {
                                    $Li = sqrt(2*(pow($H, 2))) + 95;
                                    $l_zap = $l_zap + $Li;
                                    //file.shit.adddetal 0, "ШтапикСпл", x - H, H+67, 95, Li, 45, 128, RGB(210, 210, 210)
                                    $profiles[$i]["X"]      = $x - $H;
                                    $profiles[$i]["Y"]      = $H + 67;
                                    $profiles[$i]["Width"]  = 95;
                                    $profiles[$i]["Height"] = round($Li);
                                    $profiles[$i]["Packing"] = "Упаковка.Щит";
                                    $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                                    $profiles[$i]["Code"] = $this->checkShtapikWhole($col_sh);
                                    $profiles[$i]["Angle"] = 45;
                                    $i++;
                                }
                            }
                        } else {
                            if ($x <= $W) {
                                $Li = sqrt(2*(pow($H,2))) + 95;
                            } else {
                                $Li = sqrt(2*((pow(($H + $W - $x), 2))));
                            }
                            $l_zap = $l_zap + $Li;
                            //file.shit.adddetal 0, "ШтапикСпл", x - H, H+67, 95, Li, 45, 128, RGB(210, 210, 210)
                            $profiles[$i]["X"]      = $x - $H;
                            $profiles[$i]["Y"]      = $H + 67;
                            $profiles[$i]["Width"]  = 95;
                            $profiles[$i]["Height"] = round($Li);
                            $profiles[$i]["Packing"] = "Упаковка.Щит";
                            $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                            $profiles[$i]["Code"] = $this->checkShtapikWhole($col_sh);
                            $profiles[$i]["Angle"] = 45;
                            $i++;
                        }
                        if ($x == 0) {
                            $x = $x + 141.4;
                        } else {
                            $x = $x + 134.4;
                        }
                    } else {
                    //наклон влево
                        if ($x <= $H) {
                            if ($x + 134.4 < $H) {
                                if ($x == 0) {
                                    $Li = sqrt(2*(pow(($x+141.4), 2)));
                                    $l_zap = $l_zap + $Li;
                                    //file.shit.adddetal 0, "ШтапикСпл", x+67, H+141.4, 95, Li, 315, 128, RGB(210, 210, 210)
                                    $profiles[$i]["X"]      = $x + 67;
                                    $profiles[$i]["Y"]      = $H + 141.4;
                                    $profiles[$i]["Width"]  = 95;
                                    $profiles[$i]["Height"] = round($Li);
                                    $profiles[$i]["Packing"] = "Упаковка.Щит";
                                    $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                                    $profiles[$i]["Code"] = $this->checkShtapikWhole($col_sh);
                                    $profiles[$i]["Angle"] = 315;
                                    $i++;
                                } else {
                                    if ($x + 134.4 > $W) {
                                        $Li = sqrt(2*(pow($W, 2))) + 95;
                                        $l_zap = $l_zap + $Li;
                                        //file.shit.adddetal 0, "ШтапикСпл", W, H+W-x+67, 95, Li, 315, 128, RGB(210, 210, 210)  
                                        $profiles[$i]["X"]      = $W;
                                        $profiles[$i]["Y"]      = $H + $W - $x + 67;
                                        $profiles[$i]["Width"]  = 95;
                                        $profiles[$i]["Height"] = round($Li);
                                        $profiles[$i]["Packing"] = "Упаковка.Щит";
                                        $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                                        $profiles[$i]["Code"] = $this->checkShtapikWhole($col_sh);
                                        $profiles[$i]["Angle"] = 315;
                                        $i++;
                                    } else {
                                        $Li = sqrt(2*(pow(($x+134.4), 2)));
                                        $l_zap = $l_zap + $Li;
                                        //file.shit.adddetal 0, "ШтапикСпл", x+67, H+134.4, 95, Li, 315, 128, RGB(210, 210, 210)
                                        $profiles[$i]["X"]      = $x + 67;
                                        $profiles[$i]["Y"]      = $H + 134.4;
                                        $profiles[$i]["Width"]  = 95;
                                        $profiles[$i]["Height"] = round($Li);
                                        $profiles[$i]["Packing"] = "Упаковка.Щит";
                                        $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                                        $profiles[$i]["Code"] = $this->checkShtapikWhole($col_sh);
                                        $profiles[$i]["Angle"] = 315;
                                        $i++;
                                    }
                                }
                            } else {
                                if ($x > $W) {
                                    $Li = sqrt(2*(pow($W, 2))) + 95;
                                    $l_zap = $l_zap + $Li;
                                    //file.shit.adddetal 0, "ШтапикСпл", W, H+W-x+67, 95, Li, 315, 128, RGB(210, 210, 210)  
                                    $profiles[$i]["X"]      = $W;
                                    $profiles[$i]["Y"]      = $H + $W - $x + 67;
                                    $profiles[$i]["Width"]  = 95;
                                    $profiles[$i]["Height"] = round($Li);
                                    $profiles[$i]["Packing"] = "Упаковка.Щит";
                                    $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                                    $profiles[$i]["Code"] = $this->checkShtapikWhole($col_sh);
                                    $profiles[$i]["Angle"] = 315;
                                    $i++;
                                } else {
                                    $Li = sqrt(2*(pow($H, 2))) + 95;
                                    $l_zap = $l_zap + $Li;
                                    //file.shit.adddetal 0, "ШтапикСпл", x+67, H+134.4, 95, Li, 315, 128, RGB(210, 210, 210)
                                    $profiles[$i]["X"]      = $x + 67;
                                    $profiles[$i]["Y"]      = $H + 134.4;
                                    $profiles[$i]["Width"]  = 95;
                                    $profiles[$i]["Height"] = round($Li);
                                    $profiles[$i]["Packing"] = "Упаковка.Щит";
                                    $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                                    $profiles[$i]["Code"] = $this->checkShtapikWhole($col_sh);
                                    $profiles[$i]["Angle"] = 315;
                                    $i++;
                                }
                            }
                        } else {
                            if ($x <= $W) {
                                $Li = sqrt(2*(pow($H, 2))) + 95;
                                $l_zap = $l_zap + $Li;
                                //file.shit.adddetal 0, "ШтапикСпл", x+67, H+134.4, 95, Li, 315, 128, RGB(210, 210, 210)
                                $profiles[$i]["X"]      = $x + 67;
                                $profiles[$i]["Y"]      = $H + 134.4;
                                $profiles[$i]["Width"]  = 95;
                                $profiles[$i]["Height"] = round($Li);
                                $profiles[$i]["Packing"] = "Упаковка.Щит";
                                $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                                $profiles[$i]["Code"] = $this->checkShtapikWhole($col_sh);
                                $profiles[$i]["Angle"] = 315;
                                $i++;
                            } else {
                                $Li = sqrt(2*(pow($H + $W - $x, 2)));
                                $l_zap = $l_zap + $Li;
                                //file.shit.adddetal 0, "ШтапикСпл", W, H+W-x+67, 95, Li, 315, 128, RGB(210, 210, 210)
                                $profiles[$i]["X"]      = $W;
                                $profiles[$i]["Y"]      = $H + $W - $x + 67;
                                $profiles[$i]["Width"]  = 95;
                                $profiles[$i]["Height"] = round($Li);
                                $profiles[$i]["Packing"] = "Упаковка.Щит";
                                $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                                $profiles[$i]["Code"] = $this->checkShtapikWhole($col_sh);
                                $profiles[$i]["Angle"] = 315;
                                $i++;
                            }
                        }
                        if ($x == 0) {
                            $x = $x + 141.4;
                        } else {
                            $x = $x + 134.4;
                        }
                    }
                }
                $this->HeightAlumProfilesShield = $H;
            }
            if ($str == "010" || $str == "011") {
                $a = (int)($W/140);
                $n_pr = $a;
                $n_sam = $n_sam + $a*4;
                $x = $W - $a*140;
                $w_dop = 0;
                if ($str == "011") {
                    $dh = $H/3;
                    //Стяжка
                    $profiles[$i]["X"]      = 11;
                    $profiles[$i]["Y"]      = $H - $dh;
                    $profiles[$i]["Width"]  = 80;
                    $profiles[$i]["Height"] = $W - 22;
                    $profiles[$i]["Packing"] = "Упаковка.Щит";
                    $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                    $profiles[$i]["Code"] = $this->checkProfileFence($col_sh);
                    $profiles[$i]["Angle"] = 0;
                    $i++;
                    //Крышка
                    $profiles[$i]["X"]      = 11;
                    $profiles[$i]["Y"]      = $H - $dh;
                    $profiles[$i]["Width"]  = 80;
                    $profiles[$i]["Height"] = $W - 22;
                    $profiles[$i]["Packing"] = "Упаковка.Щит";
                    $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                    $profiles[$i]["Code"] = $this->checkProfileCap($col_sh);
                    $profiles[$i]["Angle"] = 0;
                    $i++;
                    //Стяжка
                    $profiles[$i]["X"]      = 11;
                    $profiles[$i]["Y"]      = $H - 2*$dh;
                    $profiles[$i]["Width"]  = 80;
                    $profiles[$i]["Height"] = $W - 22;
                    $profiles[$i]["Packing"] = "Упаковка.Щит";
                    $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                    $profiles[$i]["Code"] = $this->checkProfileFence($col_sh);
                    $profiles[$i]["Angle"] = 0;
                    $i++;
                    //Крышка
                    $profiles[$i]["X"]      = 11;
                    $profiles[$i]["Y"]      = $H - 2*$dh;
                    $profiles[$i]["Width"]  = 80;
                    $profiles[$i]["Height"] = $W - 22;
                    $profiles[$i]["Packing"] = "Упаковка.Щит";
                    $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                    $profiles[$i]["Code"] = $this->checkProfileCap($col_sh);
                    $profiles[$i]["Angle"] = 0;
                    $i++;
                    $w_dop = 2*($W - 22)*(0.74 + 0.221);
                }
                for ($z = 1; $z <= $a; $z++){
                    $profiles[$i]["X"]      = $x;
                    $profiles[$i]["Y"]      = 0;
                    $profiles[$i]["Width"]  = 80;
                    $profiles[$i]["Height"] = $H;
                    $profiles[$i]["Packing"] = "Упаковка.Щит";
                    $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                    $profiles[$i]["Code"] = $this->checkShtapik($col_sh);
                    $profiles[$i]["Angle"] = 90;
                    $x = $x + 140;
                    $i++;
                }
                
                $this->HeightAlumProfilesShield = $H;
            }
            if ($str == "012") {
                $a = (int)($H/140);
                $n_pr = $a;
                $n_sam = $n_sam + $a*4;
                $x = 0;
                $y = 60;
                $dh = $W/3;
                //Стяжка
                $profiles[$i]["X"]      = $dh;
                $profiles[$i]["Y"]      = 11;
                $profiles[$i]["Width"]  = 80;
                $profiles[$i]["Height"] = $H - 22;
                $profiles[$i]["Packing"] = "Упаковка.Щит";
                $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                $profiles[$i]["Code"] = $this->checkProfileFence($col_sh);
                $profiles[$i]["Angle"] = 90;
                $i++;
                //Крышка
                $profiles[$i]["X"]      = $dh;
                $profiles[$i]["Y"]      = 11;
                $profiles[$i]["Width"]  = 80;
                $profiles[$i]["Height"] = $H - 22;
                $profiles[$i]["Packing"] = "Упаковка.Щит";
                $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                $profiles[$i]["Code"] = $this->checkProfileCap($col_sh);
                $profiles[$i]["Angle"] = 90;
                $i++;
                //Стяжка
                $profiles[$i]["X"]      = 2*$dh;
                $profiles[$i]["Y"]      = 11;
                $profiles[$i]["Width"]  = 80;
                $profiles[$i]["Height"] = $H - 22;
                $profiles[$i]["Packing"] = "Упаковка.Щит";
                $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                $profiles[$i]["Code"] = $this->checkProfileFence($col_sh);
                $profiles[$i]["Angle"] = 90;
                $i++;
                //Крышка
                $profiles[$i]["X"]      = 2*$dh;
                $profiles[$i]["Y"]      = 11;
                $profiles[$i]["Width"]  = 80;
                $profiles[$i]["Height"] = $H - 22;
                $profiles[$i]["Packing"] = "Упаковка.Щит";
                $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                $profiles[$i]["Code"] = $this->checkProfileCap($col_sh);
                $profiles[$i]["Angle"] = 90;
                $i++;
                for ($z = 1; $z <= $a; $z++){
                    $profiles[$i]["X"]      = $x;
                    $profiles[$i]["Y"]      = $y;
                    $profiles[$i]["Width"]  = 80;
                    $profiles[$i]["Height"] = $W;
                    $profiles[$i]["Packing"] = "Упаковка.Щит";
                    $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                    $profiles[$i]["Code"] = $this->checkShtapik($col_sh);
                    $profiles[$i]["Angle"] = 0;
                    $y = $y + 140;
                    $i++;
                }
                
                $this->HeightAlumProfilesShield = $H;
            }
            if ($str == "013") {
                $dx = 0;
		$dh = $H/4;
                //Стяжка
                $profiles[$i]["X"]      = 11;
                $profiles[$i]["Y"]      = 40 + $dh;
                $profiles[$i]["Width"]  = 80;
                $profiles[$i]["Height"] = $W - 22;
                $profiles[$i]["Packing"] = "Упаковка.Щит";
                $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                $profiles[$i]["Code"] = $this->checkProfileFence($col_sh);
                $profiles[$i]["Angle"] = 0;
                $i++;
                //Крышка
                $profiles[$i]["X"]      = 11;
                $profiles[$i]["Y"]      = 40 + $dh;
                $profiles[$i]["Width"]  = 80;
                $profiles[$i]["Height"] = $W - 22;
                $profiles[$i]["Packing"] = "Упаковка.Щит";
                $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                $profiles[$i]["Code"] = $this->checkProfileCap($col_sh);
                $profiles[$i]["Angle"] = 0;
                $i++;
                //Стяжка
                $profiles[$i]["X"]      = 11;
                $profiles[$i]["Y"]      = 40 + 3*$dh;
                $profiles[$i]["Width"]  = 80;
                $profiles[$i]["Height"] = $W - 22;
                $profiles[$i]["Packing"] = "Упаковка.Щит";
                $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                $profiles[$i]["Code"] = $this->checkProfileFence($col_sh);
                $profiles[$i]["Angle"] = 0;
                $i++;
                //Крышка
                $profiles[$i]["X"]      = 11;
                $profiles[$i]["Y"]      = 40 + 3*$dh;
                $profiles[$i]["Width"]  = 80;
                $profiles[$i]["Height"] = $W - 22;
                $profiles[$i]["Packing"] = "Упаковка.Щит";
                $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                $profiles[$i]["Code"] = $this->checkProfileCap($col_sh);
                $profiles[$i]["Angle"] = 0;
                $i++;
                $a = round(($H + $W)/226.3, 0, PHP_ROUND_HALF_EVEN);
                $n_pr = $a;
                $n_sam = $n_sam + $a*4;
                $l_zap =  0;
                for ($z = 1; $z <= $a; $z++){
                    if ($this->naklon == "Вправо") {
                        if ($x <= $H) {
                            if ($x + 113.2 < $H) {
                                if ($x + 113.2 > $W) {
                                    $Li = sqrt(2*(pow($W, 2))) + 80;
                                } else {
                                    $Li = sqrt(2*(pow(($x + 226.3), 2)));
                                }
                                $l_zap = $l_zap + $Li;
                                //file.shit.adddetal 0, "Штапик", -56.6, x+226.3, 80, Li, 45, 128, RGB(210, 210, 210)
                                $profiles[$i]["X"]      = -56.6;
                                $profiles[$i]["Y"]      = $x + 226.3;
                                $profiles[$i]["Width"]  = 80;
                                $profiles[$i]["Height"] = round($Li);
                                $profiles[$i]["Packing"] = "Упаковка.Щит";
                                $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                                $profiles[$i]["Code"] = $this->checkShtapik($col_sh);
                                $profiles[$i]["Angle"] = 45;
                                $i++;
                            } else {
                                $dx = 113.2 - ($H - $x);
                                if ($x > $W) {
                                    $Li = sqrt(2*(pow(($W + $H - $x - 113.2), 2)));
                                } else {
                                    $Li = sqrt(2*(pow($H,2))) + 80;
                                }
                                $l_zap = $l_zap + $Li;
                                //file.shit.adddetal 0, "Штапик", dx, H+56.6, 80, Li, 45, 128, RGB(210, 210, 210)
                                $profiles[$i]["X"]      = $dx;
                                $profiles[$i]["Y"]      = $H + 56.6;
                                $profiles[$i]["Width"]  = 80;
                                $profiles[$i]["Height"] = round($Li);
                                $profiles[$i]["Packing"] = "Упаковка.Щит";
                                $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                                $profiles[$i]["Code"] = $this->checkShtapik($col_sh);
                                $profiles[$i]["Angle"] = 45;
                                $i++;
                            }
                        } else {
                            if ($x + 113.2 <= $W) {
                                $Li = sqrt(2*(pow($H, 2))) + 80;
                                $l_zap = $l_zap + $Li;
                                //file.shit.adddetal 0, "Штапик", x - H + 113.2, H+56.6, 80, Li, 45, 128, RGB(210, 210, 210)
                                $profiles[$i]["X"]      = $x - $H + 113.2;
                                $profiles[$i]["Y"]      = $H + 56.6;
                                $profiles[$i]["Width"]  = 80;
                                $profiles[$i]["Height"] = round($Li);
                                $profiles[$i]["Packing"] = "Упаковка.Щит";
                                $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                                $profiles[$i]["Code"] = $this->checkShtapik($col_sh);
                                $profiles[$i]["Angle"] = 45;
                                $i++;
                            } else {
                                $Li = sqrt(2*(pow(($H + $W - $x - 113.2), 2)));
                                $l_zap = $l_zap + $Li;
                                //file.shit.adddetal 0, "Штапик", x - H + 113.2, H+56.6, 80, Li, 45, 128, RGB(210, 210, 210)
                                $profiles[$i]["X"]      = $x - $H + 113.2;
                                $profiles[$i]["Y"]      = $H + 56.6;
                                $profiles[$i]["Width"]  = 80;
                                $profiles[$i]["Height"] = round($Li);
                                $profiles[$i]["Packing"] = "Упаковка.Щит";
                                $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                                $profiles[$i]["Code"] = $this->checkShtapik($col_sh);
                                $profiles[$i]["Angle"] = 45;
                                $i++;
                            }
                        }
                        $x = $x + 226.3;
                    //Влево
                    } else {
                        if ($x <= $H) {
                            if ($x + 113.2 < $H) {
                                if ($x + 113.2 > $W) {
                                    $Li = sqrt(2*(pow(($W), 2))) + 80;
                                    $l_zap = $l_zap + $Li;
                                    //file.shit.adddetal 0, "Штапик", W, H+W-x-56.6, 80, Li, 315, 128, RGB(210, 210, 210)
                                    $profiles[$i]["X"]      = $W;
                                    $profiles[$i]["Y"]      = $H + $W - $x - 56.6;
                                    $profiles[$i]["Width"]  = 80;
                                    $profiles[$i]["Height"] = round($Li);
                                    $profiles[$i]["Packing"] = "Упаковка.Щит";
                                    $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                                    $profiles[$i]["Code"] = $this->checkShtapik($col_sh);
                                    $profiles[$i]["Angle"] = 45;
                                    $i++;
                                } else {
                                    $Li = sqrt(2*(pow(($x + 226.3), 2)));
                                    $l_zap = $l_zap + $Li;
                                    //file.shit.adddetal 0, "Штапик", x+226.3-56.6, H+113.2, 80, Li, 315, 128, RGB(210, 210, 210)
                                    $profiles[$i]["X"]      = $x + 226.3 - 56.6;
                                    $profiles[$i]["Y"]      = $H + 113.2;
                                    $profiles[$i]["Width"]  = 80;
                                    $profiles[$i]["Height"] = round($Li);
                                    $profiles[$i]["Packing"] = "Упаковка.Щит";
                                    $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                                    $profiles[$i]["Code"] = $this->checkShtapik($col_sh);
                                    $profiles[$i]["Angle"] = 45;
                                    $i++;
                                }
                            } else {
                                if ($x > $W) {
                                    $Li = sqrt(2*(pow(($H + $W - $x - 113.2), 2)));
                                    $l_zap = $l_zap + $Li;
                                    //file.shit.adddetal 0, "Штапик", W, H+W-x-56.6, 80, Li, 315, 128, RGB(210, 210, 210)
                                    $profiles[$i]["X"]      = $W;
                                    $profiles[$i]["Y"]      = $H + $W - $x - 56.6;
                                    $profiles[$i]["Width"]  = 80;
                                    $profiles[$i]["Height"] = round($Li);
                                    $profiles[$i]["Packing"] = "Упаковка.Щит";
                                    $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                                    $profiles[$i]["Code"] = $this->checkShtapik($col_sh);
                                    $profiles[$i]["Angle"] = 45;
                                    $i++;
                                } else {
                                    $Li = sqrt(2*(pow($H, 2))) + 80;
                                    $l_zap = $l_zap + $Li;
                                    //file.shit.adddetal 0, "Штапик", x+226.3-56.6, H+113.2, 80, Li, 315, 128, RGB(210, 210, 210) 
                                    $profiles[$i]["X"]      = $x + 226.3 - 56.6;
                                    $profiles[$i]["Y"]      = $H + 113.2;
                                    $profiles[$i]["Width"]  = 80;
                                    $profiles[$i]["Height"] = round($Li);
                                    $profiles[$i]["Packing"] = "Упаковка.Щит";
                                    $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                                    $profiles[$i]["Code"] = $this->checkShtapik($col_sh);
                                    $profiles[$i]["Angle"] = 45;
                                    $i++;
                                }
                            }
                        } else {
                            if ($x + 113.2 <= $W) {
                                $Li = sqrt(2*(pow($H, 2))) + 80;
                                $l_zap = $l_zap + $Li;
                                //file.shit.adddetal 0, "Штапик", x+226.3-56.6, H+113.2, 80, Li, 315, 128, RGB(210, 210, 210)
                                $profiles[$i]["X"]      = $x + 226.3 - 56.6;
                                $profiles[$i]["Y"]      = $H + 113.2;
                                $profiles[$i]["Width"]  = 80;
                                $profiles[$i]["Height"] = round($Li);
                                $profiles[$i]["Packing"] = "Упаковка.Щит";
                                $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                                $profiles[$i]["Code"] = $this->checkShtapik($col_sh);
                                $profiles[$i]["Angle"] = 45;
                                $i++;
                            } else {
                                $dx = 113.2 - ($H - $x);
                                $Li = sqrt(2*(pow(($H + $W - $x - 113.2), 2)));
                                $l_zap = $l_zap + $Li;
                                //file.shit.adddetal 0, "Штапик", W, H+W-x-56.6, 80, Li, 315, 128, RGB(210, 210, 210)
                                $profiles[$i]["X"]      = $W;
                                $profiles[$i]["Y"]      = $H + $W - $x - 56.6;
                                $profiles[$i]["Width"]  = 80;
                                $profiles[$i]["Height"] = round($Li);
                                $profiles[$i]["Packing"] = "Упаковка.Щит";
                                $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                                $profiles[$i]["Code"] = $this->checkShtapik($col_sh);
                                $profiles[$i]["Angle"] = 45;
                                $i++;
                            }
                        }
                        $x = $x + 226.3;
                    }
                }
                $this->HeightAlumProfilesShield = $H;
            }
        }

        if ($this->VidS == "Арочный") {
            $n_sam = 4;
            $n_sam_ug = 16;
            $x = 0;
            $R = 0.5*(pow($resh, 2) + pow(0.5*$Bh, 2))/$resh;

            //отбираем первые 2 символа из индекса
            $str = substr($str, -3, 2);
            $Index = substr($this->Index, -3);
            
            if ($str == "10") {
                if ($Index != "105" && $Index != "106") {
                    $a = ceil(($Bh + 100 - 209)/95);
                    $n_sht = $a;
                    $n_sam = $n_sam + $a*2;
                    $x =  0;
                    $vel = $resh + 20;
                    $l_zap_ark = 0;

                    for ($z = 1; $z <= $a; $z++){
                        $Li = 4*($resh - 10)*($z*95/($Bh + 100 - 95) - pow($z*95, 2)/pow(($Bh + 100 - 95), 2));
                        $l_zap_ark = $l_zap_ark + $Li;
                        $profiles[$i]["X"]      = $x;
                        $profiles[$i]["Y"]      = $resh - 10 - $Li;
                        $profiles[$i]["Width"]  = 95;
                        $profiles[$i]["Height"] = round($Li);
                        $profiles[$i]["Packing"] = "Упаковка.Щит";
                        $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                        $profiles[$i]["Code"] = $this->checkShtapikWhole($col_sh);//"CB000186634";
                        $profiles[$i]["Angle"] = 90;
                        $x = $x + 95;
                        $i++;
                    }
                    $this->HeightTopProfiles = $resh - 10;
                } else {
                    if (empty($pwi)) {
                        $this->infoArray = Yii::t("steps", "Значение высоты нижней панели не может быть 0, иначе деленеи на 0, модуль AlumShieldMC!");
                        return false;
                    }
                    $a = ceil(($Bh + 100 - 209)/$pwi);
                    $x = 0;
                    $n_sht = $a;
                    $l_zap_ark = 0;
                    
                    for ($z = 1; $z <= $a; $z++){
                        if ($x <= ($Bh + 100 - 209)/2) {
                            $Li = 4*($resh - 10)*($z*$pwi/($Bh + 100 - 209) - pow($z*$pwi, 2)/pow(($Bh + 100 - 209), 2));
                        } else {
                            $Li = 4*($resh - 10)*(($z - 1)*$pwi/($Bh + 100 - 209)-pow((($z-1)*$pwi), 2)/pow(($Bh + 100 - 209), 2));
                        }
                        $l_zap_ark = $l_zap_ark + $Li;
                               
                        if ($x + $pwi > $W) {
                            if ($W - $x > 0) {
                                $profiles[$i]["X"]      = $x;
                                $profiles[$i]["Y"]      = $resh - 10 - $Li;
                                $profiles[$i]["Width"]  = $W - $x;
                                $profiles[$i]["Height"] = round($Li);
                                $profiles[$i]["Packing"] = "Упаковка.Щит";
                                $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                                $profiles[$i]["Code"] = $panel_code;
                                $profiles[$i]["Angle"] = 90;
                            }
                        } else {
                            $profiles[$i]["X"]      = $x;
                            $profiles[$i]["Y"]      = $resh - 10 - $Li;
                            $profiles[$i]["Width"]  = $pwi;
                            $profiles[$i]["Height"] = round($Li);
                            $profiles[$i]["Packing"] = "Упаковка.Щит";
                            $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                            $profiles[$i]["Code"] = $panel_code;
                            $profiles[$i]["Angle"] = 90;
                        }
                        $x = $x + $pwi;
                        $i++;
                    }
                    $this->HeightTopProfiles = $resh - 10;
                }
            }
            if ($str == "11" || $str == "12") {
                $a = ((int)(($Bh + 100 - 209)/140)/2)*2;
                $n_sht = $a;
                $n_sam = $n_sam + $a*4;
                $x = 60;
                $l_zap_ark = 0;
                for ($z = 1; $z <= $a; $z++){
                    $Li = 4*($resh - 10)*($z*140/($Bh + 100 - 140) - pow(($z*140), 2)/pow(($Bh + 100 - 140), 2));
                    $l_zap_ark = $l_zap_ark + $Li; 
                    $profiles[$i]["X"]      = $x;
                    $profiles[$i]["Y"]      = $resh - 10 - $Li;
                    $profiles[$i]["Width"]  = 80;
                    $profiles[$i]["Height"] = round($Li);
                    $profiles[$i]["Packing"] = "Упаковка.Щит";
                    $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                    $profiles[$i]["Code"] = $this->checkShtapik($col_sh);
                    $profiles[$i]["Angle"] = 90;
                    $x = $x + 140;
                    $i++;
                }
                $this->HeightTopProfiles = $resh - 10;
            }
            if ($str == "13" || $str == "14" || $str == "15") {
                $a = (int)(($Bh + 100 - 209)/100);
                $n_sht = $a;
                $n_sam = $n_sam + $a*4;
                $x = 100;
                $otst = 78;
                $l_pik = 0;
                $l_zap_ark = 0;
                if ($this->b_piki) {
                    $l_pik = $piki + 10 - 92;
                    $profiles[$i]["X"]      = $otst - 122;
                    $profiles[$i]["Y"]      = $resh - 10;
                    $profiles[$i]["Width"]  = 22;
                    $profiles[$i]["Height"] = round($l_pik);
                    $profiles[$i]["Packing"] = "Упаковка.Щит";
                    $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                    $profiles[$i]["Code"] = $this->checkProfileTube($col_sh);
                    $profiles[$i]["Angle"] = 90;
                    $i++;
                }
                for ($z = 1; $z <= $a; $z++){
                    $Li = 4*($resh - 10)*($x/($Bh - 109) - pow($x, 2) /pow(($Bh - 109), 2));
                    $l_zap_ark = $l_zap_ark + $Li + $l_zap_ark; 
                    $profiles[$i]["X"]      = $otst - 22;
                    $profiles[$i]["Y"]      = $resh - 10 - $Li + $l_pik;
                    $profiles[$i]["Width"]  = 22;
                    $profiles[$i]["Height"] = round($Li);
                    $profiles[$i]["Packing"] = "Упаковка.Щит";
                    $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                    $profiles[$i]["Code"] = $this->checkProfileTube($col_sh);
                    $profiles[$i]["Angle"] = 90;
                    $i++;
                    $l_zap_ark = $l_zap_ark + $Li + $l_pik;
                    if ($this->b_piki) {
                        $profiles[$i]["X"]      = $otst - 22;
                        $profiles[$i]["Y"]      = $resh - 10 - $Li;
                        $profiles[$i]["Width"]  = 22;
                        $profiles[$i]["Height"] = round($l_pik);
                        $profiles[$i]["Packing"] = "Упаковка.Щит";
                        $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                        $profiles[$i]["Code"] = $this->checkProfileTube($col_sh);
                        $profiles[$i]["Angle"] = 90;
                        $i++;
                    }
                    $x = $x + 100;
                    $otst = $otst + 100;
                }
                if ($this->b_piki) {
                    $l_pik = $piki + 10 - 92;
                    $profiles[$i]["X"]      = $otst - 22;
                    $profiles[$i]["Y"]      = $resh - 10;
                    $profiles[$i]["Width"]  = 22;
                    $profiles[$i]["Height"] = round($l_pik);
                    $profiles[$i]["Packing"] = "Упаковка.Щит";
                    $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                    $profiles[$i]["Code"] = $this->checkProfileTube($col_sh);
                    $profiles[$i]["Angle"] = 90;
                    $i++;
                }
                $this->HeightTopProfiles = $resh - 10;
                $this->HeightTopProfilesPiki = $l_pik;
            }
            if ($str == "16" || $str == "17") {
                $a = (int)(($Bh - 109)/260);
                $n_sam = $n_sam + 8;
                $n_nak = $a + 2;
                $n_ven = $a*2;
                $x = ($W - $a*260)/2 + 120;
                $l_pik = $piki + 10 - 92;
                $l_reshet = $n_resh + 20;
                $l_zap_ark = 0;
                $Li = 0;
                $profiles[$i]["X"]      = $x - 260;
                $profiles[$i]["Y"]      = $resh - 10;
                $profiles[$i]["Width"]  = 20;
                $profiles[$i]["Height"] = round($l_pik);
                $profiles[$i]["Packing"] = "Упаковка.Щит";
                $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                $profiles[$i]["Code"] = $this->checkProfileTube($col_sh);
                $profiles[$i]["Angle"] = 90;
                $i++;
                
                for ($z = 1; $z <= $a; $z++){
                    $Li = 4*($resh - 10)*($x/($Bh - 109)-pow($x, 2)/pow(($Bh - 109), 2));
                    $l_zap_ark = $l_zap_ark + $Li + $l_pik + $l_reshet;
                    //пики
                    $profiles[$i]["X"]      = $x - 20;
                    $profiles[$i]["Y"]      =  $resh - 10 - $Li + $l_pik;
                    $profiles[$i]["Width"]  = 20;
                    $profiles[$i]["Height"] = round($Li);
                    $profiles[$i]["Packing"] = "Упаковка.Щит";
                    $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                    $profiles[$i]["Code"] = $this->checkProfileTube($col_sh);
                    $profiles[$i]["Angle"] = 90;
                    $i++;
                    //сразу после основного щита (сендвич либо алюм.)
                    $profiles[$i]["X"]      = $x - 20;
                    $profiles[$i]["Y"]      = $resh - 10 + $l_pik;
                    $profiles[$i]["Width"]  = 20;
                    $profiles[$i]["Height"] = round($l_reshet);
                    $profiles[$i]["Packing"] = "Упаковка.Щит";
                    $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                    $profiles[$i]["Code"] = $this->checkProfileTube($col_sh);
                    $profiles[$i]["Angle"] = 90;
                    $i++;
                    //самые верхние трубы
                    $profiles[$i]["X"]      = $x - 20;
                    $profiles[$i]["Y"]      = $resh - 10 - $Li;
                    $profiles[$i]["Width"]  = 20;
                    $profiles[$i]["Height"] = round($l_pik);
                    $profiles[$i]["Packing"] = "Упаковка.Щит";
                    $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                    $profiles[$i]["Code"] = $this->checkProfileTube($col_sh);
                    $profiles[$i]["Angle"] = 90;
                    $i++;
                    
                    $x = $x + 260;
                }
                
                $profiles[$i]["X"]      = $x - 20;
                $profiles[$i]["Y"]      = $resh - 10;
                $profiles[$i]["Width"]  = 20;
                $profiles[$i]["Height"] = round($l_pik);
                $profiles[$i]["Packing"] = "Упаковка.Щит";
                $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                $profiles[$i]["Code"] = $this->checkProfileTube($col_sh);
                $profiles[$i]["Angle"] = 90;
                $i++;
                
                $this->HeightTopProfiles = $l_reshet;
                $this->HeightTopProfilesPiki = $resh - 10;
                $this->HeightTopTube = $l_pik;
                
            }
            $str = substr($this->Index, -3);
            if ($str == "102" || $str == "122" || $str == "132" || $str == "142" || $str == "152" || $str == "162") {
                $a = (int)($W/95);
                $n_pr = $a;
                $n_sam = $n_sam + $a*2;
                $x =  0;
                for ($z=1; $z <= $a; $z++){
                    $profiles[$i]["X"]      = $x;
                    $profiles[$i]["Y"]      = $resh - 10 + $l_pik + $l_reshet;
                    $profiles[$i]["Width"]  = 95;
                    $profiles[$i]["Height"] = $H;
                    $profiles[$i]["Packing"] = "Упаковка.Щит";
                    $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                    $profiles[$i]["Code"] = $this->checkShtapikWhole($col_sh);
                    $profiles[$i]["Angle"] = 90;
                    $x = $x + 95;
                    $i++;
                }
                if ($W - $x - 5 > 0) {
                    $n_pr = $n_pr + 2;
                    $profiles[$i]["X"]      = $x;
                    $profiles[$i]["Y"]      = $resh - 10 + $l_pik + $l_reshet;
                    $profiles[$i]["Width"]  = $W - $x - 5;
                    $profiles[$i]["Height"] = $H;
                    $profiles[$i]["Packing"] = "Упаковка.Щит";
                    $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                    $profiles[$i]["Code"] = $this->checkShtapikWhole($col_sh);
                    $profiles[$i]["Angle"] = 90;
                    $i++;
                }
                //$this->HeightTopProfiles = $H;
                $this->HeightAlumProfilesShield = $H;
            }
            if ($str == "103" || $str == "123" || $str == "133" || $str == "143" || $str == "153" || $str == "163") {
                $a = (int)($H/95);
                $n_pr = $a;
                $n_sam = $n_sam + $a*2;
                $y =  0;
                for ($z=1; $z <= $a; $z++){
                    $profiles[$i]["X"]      = 0;
                    $profiles[$i]["Y"]      = $resh - 10 + $y + $l_pik + $l_reshet;
                    $profiles[$i]["Width"]  = 95;//$W;
                    $profiles[$i]["Height"] = $W;//95;
                    $profiles[$i]["Packing"] = "Упаковка.Щит";
                    $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                    $profiles[$i]["Code"] = $this->checkShtapikWhole($col_sh);
                    $profiles[$i]["Angle"] = 0;
                    $y = $y + 95;
                    $i++;
                }
                if ($H - $y - 5 > 0) {
                    $n_pr = $n_pr + 2;
                    $profiles[$i]["X"]      = 0;
                    $profiles[$i]["Y"]      = $resh - 10 + $y + $l_pik + $l_reshet;
                    $profiles[$i]["Width"]  = $H - $y - 5;//$W;
                    $profiles[$i]["Height"] = $W;//$H - $y - 5;
                    $profiles[$i]["Packing"] = "Упаковка.Щит";
                    $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                    $profiles[$i]["Code"] = $this->checkShtapikWhole($col_sh);
                    $profiles[$i]["Angle"] = 0;
                    $i++;
                }
                $this->HeightAlumProfilesShield = $H;
            }
            if ($str == "104" || $str == "124" || $str == "134" || $str == "144" || $str == "154" || $str == "164") {
                $a = ceil(($H + $W)/134.4);
                $n_pr = $a;
                $n_sam = $n_sam + $a*2;
                $l_zap =  0;
                $x = 0;
                for ($z = 1; $z <= $a; $z++){
                    if ($this->naklon == "Вправо") {
                        if ($x <= $H) {
                            if ($x + 134.4 < $H) {
                                if ($x == 0) {
                                    $Li = sqrt(2*(pow(($x + 141.4), 2)));
                                } else {
                                    if ($x + 134.4 > $W) {
                                        $Li = sqrt(2*(pow($W, 2))) + 95;
                                    } else {
                                        $Li = sqrt(2*(pow($x + 134.4, 2)));
                                    }
                                }
                                $l_zap = $l_zap + $Li;
                                //file.shit.adddetal 0, "ШтапикСпл", -67, x+134.4, 95, Li, 45, 128, RGB(210, 210, 210)
                                $profiles[$i]["X"]      = -67;
                                $profiles[$i]["Y"]      = $x + 134.4;
                                $profiles[$i]["Width"]  = 95;
                                $profiles[$i]["Height"] = round($Li);
                                $profiles[$i]["Packing"] = "Упаковка.Щит";
                                $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                                $profiles[$i]["Code"] = $this->checkShtapikWhole($col_sh);
                                $profiles[$i]["Angle"] = 45;
                                $i++;
                            } else {
                                if ($x > $W) {
                                    $Li = sqrt(2*(pow($W, 2))) + 95;
                                    $l_zap = $l_zap + $Li;
                                    //file.shit.adddetal 0, "ШтапикСпл", -67, x+134.4, 95, Li, 45, 128, RGB(210, 210, 210)
                                    $profiles[$i]["X"]      = -67;
                                    $profiles[$i]["Y"]      = $x + 134.4;
                                    $profiles[$i]["Width"]  = 95;
                                    $profiles[$i]["Height"] = round($Li);
                                    $profiles[$i]["Packing"] = "Упаковка.Щит";
                                    $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                                    $profiles[$i]["Code"] = $this->checkShtapikWhole($col_sh);
                                    $profiles[$i]["Angle"] = 45;
                                    $i++;
                                } else {
                                    $Li = sqrt(2*(pow($H, 2))) + 95;
                                    $l_zap = $l_zap + $Li;
                                    //file.shit.adddetal 0, "ШтапикСпл", x - H, H+67, 95, Li, 45, 128, RGB(210, 210, 210)
                                    $profiles[$i]["X"]      = $x - $H;
                                    $profiles[$i]["Y"]      = $H + 67;
                                    $profiles[$i]["Width"]  = 95;
                                    $profiles[$i]["Height"] = round($Li);
                                    $profiles[$i]["Packing"] = "Упаковка.Щит";
                                    $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                                    $profiles[$i]["Code"] = $this->checkShtapikWhole($col_sh);
                                    $profiles[$i]["Angle"] = 45;
                                    $i++;
                                }
                            }
                        } else {
                            if ($x <= $W) {
                                $Li = sqrt(2*(pow($H,2))) + 95;
                            } else {
                                $Li = sqrt(2*((pow(($H + $W - $x), 2))));
                            }
                            $l_zap = $l_zap + $Li;
                            //file.shit.adddetal 0, "ШтапикСпл", x - H, H+67, 95, Li, 45, 128, RGB(210, 210, 210)
                            $profiles[$i]["X"]      = $x - $H;
                            $profiles[$i]["Y"]      = $H + 67;
                            $profiles[$i]["Width"]  = 95;
                            $profiles[$i]["Height"] = round($Li);
                            $profiles[$i]["Packing"] = "Упаковка.Щит";
                            $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                            $profiles[$i]["Code"] = $this->checkShtapikWhole($col_sh);
                            $profiles[$i]["Angle"] = 45;
                            $i++;
                        }
                        if ($x == 0) {
                            $x = $x + 141.4;
                        } else {
                            $x = $x + 134.4;
                        }
                    } else {
                    //наклон влево
                        if ($x <= $H) {
                            if ($x + 134.4 < $H) {
                                if ($x == 0) {
                                    $Li = sqrt(2*(pow(($x+141.4), 2)));
                                    $l_zap = $l_zap + $Li;
                                    //file.shit.adddetal 0, "ШтапикСпл", x+67, H+141.4, 95, Li, 315, 128, RGB(210, 210, 210)
                                    $profiles[$i]["X"]      = $x + 67;
                                    $profiles[$i]["Y"]      = $H + 141.4;
                                    $profiles[$i]["Width"]  = 95;
                                    $profiles[$i]["Height"] = round($Li);
                                    $profiles[$i]["Packing"] = "Упаковка.Щит";
                                    $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                                    $profiles[$i]["Code"] = $this->checkShtapikWhole($col_sh);
                                    $profiles[$i]["Angle"] = 315;
                                    $i++;
                                } else {
                                    if ($x + 134.4 > $W) {
                                        $Li = sqrt(2*(pow($W, 2))) + 95;
                                        $l_zap = $l_zap + $Li;
                                        //file.shit.adddetal 0, "ШтапикСпл", W, H+W-x+67, 95, Li, 315, 128, RGB(210, 210, 210)  
                                        $profiles[$i]["X"]      = $W;
                                        $profiles[$i]["Y"]      = $H + $W - $x + 67;
                                        $profiles[$i]["Width"]  = 95;
                                        $profiles[$i]["Height"] = round($Li);
                                        $profiles[$i]["Packing"] = "Упаковка.Щит";
                                        $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                                        $profiles[$i]["Code"] = $this->checkShtapikWhole($col_sh);
                                        $profiles[$i]["Angle"] = 315;
                                        $i++;
                                    } else {
                                        $Li = sqrt(2*(pow(($x+134.4), 2)));
                                        $l_zap = $l_zap + $Li;
                                        //file.shit.adddetal 0, "ШтапикСпл", x+67, H+134.4, 95, Li, 315, 128, RGB(210, 210, 210)
                                        $profiles[$i]["X"]      = $x + 67;
                                        $profiles[$i]["Y"]      = $H + 134.4;
                                        $profiles[$i]["Width"]  = 95;
                                        $profiles[$i]["Height"] = round($Li);
                                        $profiles[$i]["Packing"] = "Упаковка.Щит";
                                        $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                                        $profiles[$i]["Code"] = $this->checkShtapikWhole($col_sh);
                                        $profiles[$i]["Angle"] = 315;
                                        $i++;
                                    }
                                }
                            } else {
                                if ($x > $W) {
                                    $Li = sqrt(2*(pow($W, 2))) + 95;
                                    $l_zap = $l_zap + $Li;
                                    //file.shit.adddetal 0, "ШтапикСпл", W, H+W-x+67, 95, Li, 315, 128, RGB(210, 210, 210)  
                                    $profiles[$i]["X"]      = $W;
                                    $profiles[$i]["Y"]      = $H + $W - $x + 67;
                                    $profiles[$i]["Width"]  = 95;
                                    $profiles[$i]["Height"] = round($Li);
                                    $profiles[$i]["Packing"] = "Упаковка.Щит";
                                    $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                                    $profiles[$i]["Code"] = $this->checkShtapikWhole($col_sh);
                                    $profiles[$i]["Angle"] = 315;
                                    $i++;
                                } else {
                                    $Li = sqrt(2*(pow($H, 2))) + 95;
                                    $l_zap = $l_zap + $Li;
                                    //file.shit.adddetal 0, "ШтапикСпл", x+67, H+134.4, 95, Li, 315, 128, RGB(210, 210, 210)
                                    $profiles[$i]["X"]      = $x + 67;
                                    $profiles[$i]["Y"]      = $H + 134.4;
                                    $profiles[$i]["Width"]  = 95;
                                    $profiles[$i]["Height"] = round($Li);
                                    $profiles[$i]["Packing"] = "Упаковка.Щит";
                                    $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                                    $profiles[$i]["Code"] = $this->checkShtapikWhole($col_sh);
                                    $profiles[$i]["Angle"] = 315;
                                    $i++;
                                }
                            }
                        } else {
                            if ($x <= $W) {
                                $Li = sqrt(2*(pow($H, 2))) + 95;
                                $l_zap = $l_zap + $Li;
                                //file.shit.adddetal 0, "ШтапикСпл", x+67, H+134.4, 95, Li, 315, 128, RGB(210, 210, 210)
                                $profiles[$i]["X"]      = $x + 67;
                                $profiles[$i]["Y"]      = $H + 134.4;
                                $profiles[$i]["Width"]  = 95;
                                $profiles[$i]["Height"] = round($Li);
                                $profiles[$i]["Packing"] = "Упаковка.Щит";
                                $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                                $profiles[$i]["Code"] = $this->checkShtapikWhole($col_sh);
                                $profiles[$i]["Angle"] = 315;
                                $i++;
                            } else {
                                $Li = sqrt(2*(pow($H + $W - $x, 2)));
                                $l_zap = $l_zap + $Li;
                                //file.shit.adddetal 0, "ШтапикСпл", W, H+W-x+67, 95, Li, 315, 128, RGB(210, 210, 210)
                                $profiles[$i]["X"]      = $W;
                                $profiles[$i]["Y"]      = $H + $W - $x + 67;
                                $profiles[$i]["Width"]  = 95;
                                $profiles[$i]["Height"] = round($Li);
                                $profiles[$i]["Packing"] = "Упаковка.Щит";
                                $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                                $profiles[$i]["Code"] = $this->checkShtapikWhole($col_sh);
                                $profiles[$i]["Angle"] = 315;
                                $i++;
                            }
                        }
                        if ($x == 0) {
                            $x = $x + 141.4;
                        } else {
                            $x = $x + 134.4;
                        }
                    }
                }
                $this->HeightAlumProfilesShield = $H;
            }
            if ($str == "110" || $str == "111" || $str == "170" || $str == "171") {
                $a = (int)($W/140);
                $n_pr = $a;
                $n_sam = $n_sam + $a*4;
                $x = $W - $a*140;
                $w_dop = 0;
                if ($str == "111" || $str == "171") {
                    $dh = $H/3;
                    //Стяжка
                    $profiles[$i]["X"]      = 11;
                    $profiles[$i]["Y"]      = $resh - 10 + $H - $dh + $l_pik + $l_reshet;
                    $profiles[$i]["Width"]  = 80;
                    $profiles[$i]["Height"] = $W - 22;
                    $profiles[$i]["Packing"] = "Упаковка.Щит";
                    $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                    $profiles[$i]["Code"] = $this->checkProfileFence($col_sh);
                    $profiles[$i]["Angle"] = 0;
                    $i++;
                    //Крышка
                    $profiles[$i]["X"]      = 11;
                    $profiles[$i]["Y"]      = $resh - 10 + $H - $dh + $l_pik + $l_reshet;
                    $profiles[$i]["Width"]  = 80;
                    $profiles[$i]["Height"] = $W - 22;
                    $profiles[$i]["Packing"] = "Упаковка.Щит";
                    $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                    $profiles[$i]["Code"] = $this->checkProfileCap($col_sh);
                    $profiles[$i]["Angle"] = 0;
                    $i++;
                    //Стяжка
                    $profiles[$i]["X"]      = 11;
                    $profiles[$i]["Y"]      = $resh - 10 + $H - 2*$dh + $l_pik + $l_reshet;
                    $profiles[$i]["Width"]  = 80;
                    $profiles[$i]["Height"] = $W - 22;
                    $profiles[$i]["Packing"] = "Упаковка.Щит";
                    $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                    $profiles[$i]["Code"] = $this->checkProfileFence($col_sh);
                    $profiles[$i]["Angle"] = 0;
                    $i++;
                    //Крышка
                    $profiles[$i]["X"]      = 11;
                    $profiles[$i]["Y"]      = $resh - 10 + $H - 2*$dh + $l_pik + $l_reshet;
                    $profiles[$i]["Width"]  = 80;
                    $profiles[$i]["Height"] = $W - 22;
                    $profiles[$i]["Packing"] = "Упаковка.Щит";
                    $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                    $profiles[$i]["Code"] = $this->checkProfileCap($col_sh);
                    $profiles[$i]["Angle"] = 0;
                    $i++;
                    $w_dop = 2*($W - 22)*(0.74 + 0.221);
                }
                for ($z = 1; $z <= $a; $z++){
                    $profiles[$i]["X"]      = $x;
                    $profiles[$i]["Y"]      = $resh - 10 + $l_pik + $l_reshet;
                    $profiles[$i]["Width"]  = 80;
                    $profiles[$i]["Height"] = $H;
                    $profiles[$i]["Packing"] = "Упаковка.Щит";
                    $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                    $profiles[$i]["Code"] = $this->checkShtapik($col_sh);
                    $profiles[$i]["Angle"] = 90;
                    $x = $x + 140;
                    $i++;
                }
                
                $this->HeightAlumProfilesShield = $H;
            }
            if ($str == "112" || $str == "172") {
                $a = (int)($H/140);
                $n_pr = $a;
                $n_sam = $n_sam + $a*4;
                $x = 0;
                $y = 60;
                $dh = $W/3;
                //Стяжка
                $profiles[$i]["X"]      = $dh;
                $profiles[$i]["Y"]      = $resh - 10 + 11 + $l_pik + $l_reshet;
                $profiles[$i]["Width"]  = 80;
                $profiles[$i]["Height"] = $H - 22;
                $profiles[$i]["Packing"] = "Упаковка.Щит";
                $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                $profiles[$i]["Code"] = $this->checkProfileFence($col_sh);
                $profiles[$i]["Angle"] = 90;
                $i++;
                //Крышка
                $profiles[$i]["X"]      = $dh;
                $profiles[$i]["Y"]      = $resh - 10 + 11 + $l_pik + $l_reshet;
                $profiles[$i]["Width"]  = 80;
                $profiles[$i]["Height"] = $H - 22;
                $profiles[$i]["Packing"] = "Упаковка.Щит";
                $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                $profiles[$i]["Code"] = $this->checkProfileCap($col_sh);
                $profiles[$i]["Angle"] = 90;
                $i++;
                //Стяжка
                $profiles[$i]["X"]      = 2*$dh;
                $profiles[$i]["Y"]      = $resh - 10 + 11 + $l_pik + $l_reshet;
                $profiles[$i]["Width"]  = 80;
                $profiles[$i]["Height"] = $H - 22;
                $profiles[$i]["Packing"] = "Упаковка.Щит";
                $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                $profiles[$i]["Code"] = $this->checkProfileFence($col_sh);
                $profiles[$i]["Angle"] = 90;
                $i++;
                //Крышка
                $profiles[$i]["X"]      = 2*$dh;
                $profiles[$i]["Y"]      = $resh - 10 + 11 + $l_pik + $l_reshet;
                $profiles[$i]["Width"]  = 80;
                $profiles[$i]["Height"] = $H - 22;
                $profiles[$i]["Packing"] = "Упаковка.Щит";
                $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                $profiles[$i]["Code"] = $this->checkProfileCap($col_sh);
                $profiles[$i]["Angle"] = 90;
                $i++;
                for ($z = 1; $z <= $a; $z++){
                    $profiles[$i]["X"]      = $x;
                    $profiles[$i]["Y"]      = $resh - 10 + $y + $l_pik + $l_reshet;
                    $profiles[$i]["Width"]  = 80;
                    $profiles[$i]["Height"] = $W;
                    $profiles[$i]["Packing"] = "Упаковка.Щит";
                    $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                    $profiles[$i]["Code"] = $this->checkShtapik($col_sh);
                    $profiles[$i]["Angle"] = 0;
                    $y = $y + 140;
                    $i++;
                }
                
                $this->HeightAlumProfilesShield = $H;
            }
            if ($str == "113" || $str == "173") {
                $x = 0;
                $dx = 0;
		$dh = $H/4;
                //Стяжка
                $profiles[$i]["X"]      = 11;
                $profiles[$i]["Y"]      = $resh - 10 + 40 + $dh + $l_pik + $l_reshet;
                $profiles[$i]["Width"]  = 80;
                $profiles[$i]["Height"] = $W - 22;
                $profiles[$i]["Packing"] = "Упаковка.Щит";
                $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                $profiles[$i]["Code"] = $this->checkProfileFence($col_sh);
                $profiles[$i]["Angle"] = 0;
                $i++;
                //Крышка
                $profiles[$i]["X"]      = 11;
                $profiles[$i]["Y"]      = $resh - 10 + 40 + $dh + $l_pik + $l_reshet;
                $profiles[$i]["Width"]  = 80;
                $profiles[$i]["Height"] = $W - 22;
                $profiles[$i]["Packing"] = "Упаковка.Щит";
                $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                $profiles[$i]["Code"] = $this->checkProfileCap($col_sh);
                $profiles[$i]["Angle"] = 0;
                $i++;
                //Стяжка
                $profiles[$i]["X"]      = 11;
                $profiles[$i]["Y"]      = $resh - 10 + 40 + 3*$dh + $l_pik + $l_reshet;
                $profiles[$i]["Width"]  = 80;
                $profiles[$i]["Height"] = $W - 22;
                $profiles[$i]["Packing"] = "Упаковка.Щит";
                $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                $profiles[$i]["Code"] = $this->checkProfileFence($col_sh);
                $profiles[$i]["Angle"] = 0;
                $i++;
                //Крышка
                $profiles[$i]["X"]      = 11;
                $profiles[$i]["Y"]      = $resh - 10 + 40 + 3*$dh + $l_pik + $l_reshet;
                $profiles[$i]["Width"]  = 80;
                $profiles[$i]["Height"] = $W - 22;
                $profiles[$i]["Packing"] = "Упаковка.Щит";
                $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                $profiles[$i]["Code"] = $this->checkProfileCap($col_sh);
                $profiles[$i]["Angle"] = 0;
                $i++;
                $a = round((($H + $W)/226.3), 0, PHP_ROUND_HALF_EVEN);
                $n_pr = $a;
                $n_sam = $n_sam + $a*4;
                $l_zap =  0;
                for ($z = 1; $z <= $a; $z++){
                    if ($this->naklon == "Вправо") {
                        if ($x <= $H) {
                            if ($x + 113.2 < $H) {
                                if ($x + 113.2 > $W) {
                                    $Li = sqrt(2*(pow($W, 2))) + 80;
                                } else {
                                    $Li = sqrt(2*(pow(($x + 226.3), 2)));
                                }
                                $l_zap = $l_zap + $Li;
                                //file.shit.adddetal 0, "Штапик", -56.6, x+226.3, 80, Li, 45, 128, RGB(210, 210, 210)
                                $profiles[$i]["X"]      = -56.6;
                                $profiles[$i]["Y"]      = $resh - 10 + $x + 226.3 + $l_pik + $l_reshet;
                                $profiles[$i]["Width"]  = 80;
                                $profiles[$i]["Height"] = round($Li);
                                $profiles[$i]["Packing"] = "Упаковка.Щит";
                                $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                                $profiles[$i]["Code"] = $this->checkShtapik($col_sh);
                                $profiles[$i]["Angle"] = 45;
                                $i++;
                            } else {
                                $dx = 113.2 - ($H - $x);
                                if ($x > $W) {
                                    $Li = sqrt(2*(pow(($W + $H - $x - 113.2), 2)));
                                } else {
                                    $Li = sqrt(2*(pow($H,2))) + 80;
                                }
                                $l_zap = $l_zap + $Li;
                                //file.shit.adddetal 0, "Штапик", dx, H+56.6, 80, Li, 45, 128, RGB(210, 210, 210)
                                $profiles[$i]["X"]      = $dx;
                                $profiles[$i]["Y"]      = $resh - 10 + $H + 56.6 + $l_pik + $l_reshet;
                                $profiles[$i]["Width"]  = 80;
                                $profiles[$i]["Height"] = round($Li);
                                $profiles[$i]["Packing"] = "Упаковка.Щит";
                                $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                                $profiles[$i]["Code"] = $this->checkShtapik($col_sh);
                                $profiles[$i]["Angle"] = 45;
                                $i++;
                            }
                        } else {
                            if ($x + 113.2 <= $W) {
                                $Li = sqrt(2*(pow($H, 2))) + 80;
                                $l_zap = $l_zap + $Li;
                                //file.shit.adddetal 0, "Штапик", x - H + 113.2, H+56.6, 80, Li, 45, 128, RGB(210, 210, 210)
                                $profiles[$i]["X"]      = $x - $H + 113.2;
                                $profiles[$i]["Y"]      = $resh - 10 + $H + 56.6 + $l_pik + $l_reshet;
                                $profiles[$i]["Width"]  = 80;
                                $profiles[$i]["Height"] = round($Li);
                                $profiles[$i]["Packing"] = "Упаковка.Щит";
                                $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                                $profiles[$i]["Code"] = $this->checkShtapik($col_sh);
                                $profiles[$i]["Angle"] = 45;
                                $i++;
                            } else {
                                $Li = sqrt(2*(pow(($H + $W - $x - 113.2), 2)));
                                $l_zap = $l_zap + $Li;
                                //file.shit.adddetal 0, "Штапик", x - H + 113.2, H+56.6, 80, Li, 45, 128, RGB(210, 210, 210)
                                $profiles[$i]["X"]      = $x - $H + 113.2;
                                $profiles[$i]["Y"]      = $resh - 10 + $H + 56.6 + $l_pik + $l_reshet;
                                $profiles[$i]["Width"]  = 80;
                                $profiles[$i]["Height"] = round($Li);
                                $profiles[$i]["Packing"] = "Упаковка.Щит";
                                $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                                $profiles[$i]["Code"] = $this->checkShtapik($col_sh);
                                $profiles[$i]["Angle"] = 45;
                                $i++;
                            }
                        }
                        $x = $x + 226.3;
                    //Влево
                    } else {
                        if ($x <= $H) {
                            if ($x + 113.2 < $H) {
                                if ($x + 113.2 > $W) {
                                    $Li = sqrt(2*(pow(($W), 2))) + 80;
                                    $l_zap = $l_zap + $Li;
                                    //file.shit.adddetal 0, "Штапик", W, H+W-x-56.6, 80, Li, 315, 128, RGB(210, 210, 210)
                                    $profiles[$i]["X"]      = $W;
                                    $profiles[$i]["Y"]      = $resh - 10 + $H + $W - $x - 56.6 + $l_pik + $l_reshet;
                                    $profiles[$i]["Width"]  = 80;
                                    $profiles[$i]["Height"] = round($Li);
                                    $profiles[$i]["Packing"] = "Упаковка.Щит";
                                    $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                                    $profiles[$i]["Code"] = $this->checkShtapik($col_sh);
                                    $profiles[$i]["Angle"] = 45;
                                    $i++;
                                } else {
                                    $Li = sqrt(2*(pow(($x + 226.3), 2)));
                                    $l_zap = $l_zap + $Li;
                                    //file.shit.adddetal 0, "Штапик", x+226.3-56.6, H+113.2, 80, Li, 315, 128, RGB(210, 210, 210)
                                    $profiles[$i]["X"]      = $x + 226.3 - 56.6;
                                    $profiles[$i]["Y"]      = $resh - 10 + $H + 113.2 + $l_pik + $l_reshet;
                                    $profiles[$i]["Width"]  = 80;
                                    $profiles[$i]["Height"] = round($Li);
                                    $profiles[$i]["Packing"] = "Упаковка.Щит";
                                    $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                                    $profiles[$i]["Code"] = $this->checkShtapik($col_sh);
                                    $profiles[$i]["Angle"] = 45;
                                    $i++;
                                }
                            } else {
                                if ($x > $W) {
                                    $Li = sqrt(2*(pow(($H + $W - $x - 113.2), 2)));
                                    $l_zap = $l_zap + $Li;
                                    //file.shit.adddetal 0, "Штапик", W, H+W-x-56.6, 80, Li, 315, 128, RGB(210, 210, 210)
                                    $profiles[$i]["X"]      = $W;
                                    $profiles[$i]["Y"]      = $resh - 10 + $H + $W - $x - 56.6 + $l_pik + $l_reshet;
                                    $profiles[$i]["Width"]  = 80;
                                    $profiles[$i]["Height"] = round($Li);
                                    $profiles[$i]["Packing"] = "Упаковка.Щит";
                                    $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                                    $profiles[$i]["Code"] = $this->checkShtapik($col_sh);
                                    $profiles[$i]["Angle"] = 45;
                                    $i++;
                                } else {
                                    $Li = sqrt(2*(pow($H, 2))) + 80;
                                    $l_zap = $l_zap + $Li;
                                    //file.shit.adddetal 0, "Штапик", x+226.3-56.6, H+113.2, 80, Li, 315, 128, RGB(210, 210, 210) 
                                    $profiles[$i]["X"]      = $x + 226.3 - 56.6;
                                    $profiles[$i]["Y"]      = $resh - 10 + $H + 113.2 + $l_pik + $l_reshet;
                                    $profiles[$i]["Width"]  = 80;
                                    $profiles[$i]["Height"] = round($Li);
                                    $profiles[$i]["Packing"] = "Упаковка.Щит";
                                    $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                                    $profiles[$i]["Code"] = $this->checkShtapik($col_sh);
                                    $profiles[$i]["Angle"] = 45;
                                    $i++;
                                }
                            }
                        } else {
                            if ($x + 113.2 <= $W) {
                                $Li = sqrt(2*(pow($H, 2))) + 80;
                                $l_zap = $l_zap + $Li;
                                //file.shit.adddetal 0, "Штапик", x+226.3-56.6, H+113.2, 80, Li, 315, 128, RGB(210, 210, 210)
                                $profiles[$i]["X"]      = $x + 226.3 - 56.6;
                                $profiles[$i]["Y"]      = $resh - 10 + $H + 113.2 + $l_pik + $l_reshet;
                                $profiles[$i]["Width"]  = 80;
                                $profiles[$i]["Height"] = round($Li);
                                $profiles[$i]["Packing"] = "Упаковка.Щит";
                                $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                                $profiles[$i]["Code"] = $this->checkShtapik($col_sh);
                                $profiles[$i]["Angle"] = 45;
                                $i++;
                            } else {
                                $dx = 113.2 - ($H - $x);
                                $Li = sqrt(2*(pow(($H + $W - $x - 113.2), 2)));
                                $l_zap = $l_zap + $Li;
                                //file.shit.adddetal 0, "Штапик", W, H+W-x-56.6, 80, Li, 315, 128, RGB(210, 210, 210)
                                $profiles[$i]["X"]      = $W;
                                $profiles[$i]["Y"]      = $resh - 10 + $H + $W - $x - 56.6 + $l_pik + $l_reshet;
                                $profiles[$i]["Width"]  = 80;
                                $profiles[$i]["Height"] = round($Li);
                                $profiles[$i]["Packing"] = "Упаковка.Щит";
                                $profiles[$i]["Cards"] = ["Покраска", "Кладовщик", "Изготовление щита"];
                                $profiles[$i]["Code"] = $this->checkShtapik($col_sh);
                                $profiles[$i]["Angle"] = 45;
                                $i++;
                            }
                        }
                        $x = $x + 226.3;
                    }
                }
                $this->HeightAlumProfilesShield = $H;
            }
        }
        //округление выходных данных
        /*foreach($profiles as &$val){
            $val['X'] = $this->bround($val['X']);
            $val['Y'] = $this->bround($val['Y']);
            $val['Width'] = $this->bround($val['Width']);
            $val['Height'] = $this->bround($val['Height']);
        }*/
        $this->AddDetal = $profiles;
            
        return true;
    }

    /**
    * Выбираем необходимы код элемента штапика checkShtapik
    * 
    * @param mixed $value - искомый цвет штапика
    * 
    * @return код элемента штапика, группы DH0651
    */
    private function checkShtapik($value)
    {
        $this->infoArray = array();
        $DH0651["неокрашенный"] = "CB000178479";
        $DH0651["9003"] = "CB000186634";
        $DH0651["8014"] = "CB000161484";
        $DH0651["5005"] = "CB000186635";
        $DH0651["6005"] = "CB000161483";
        $DH0651["3005"] = "CB000161482";
        $DH0651["7004"] = "CB000186636";
        $DH0651["1014"] = "CB000186637";
        $DH0651["9006"] = "CB000186638";
        $DH0651["8017"] = "CB000161481";
        $DH0651["3000"] = "CB000178479";
        if (empty($DH0651[$value])) {
            $DH0651[$value] = "CB000178479";
        }
        
        return $DH0651[$value];
    }
    
    /**
    * Выбираем необходимы код элемента штапика сплошного checkShtapikWhole
    * 
    * @param mixed $value - искомый цвет штапика сплошного
    * 
    * @return код элемента штапика сплошного, группы DH0652
    */
    private function checkShtapikWhole($value)
    {
        $this->infoArray = array();
        $DH0652["неокрашенный"] = "CB000182782";
        $DH0652["9003"] = "CB000186641";
        $DH0652["8014"] = "CB000161804";
        $DH0652["5005"] = "CB000186643";
        $DH0652["6005"] = "CB000161808";
        $DH0652["3005"] = "CB000161853";
        $DH0652["7004"] = "CB000186644";
        $DH0652["1014"] = "CB000186645";
        $DH0652["9006"] = "CB000186642";
        $DH0652["8017"] = "CB000161870";
        if (empty($DH0652[$value])) {
            $DH0652[$value] = "CB000182782";
        }
        
        return $DH0652[$value];
    }
    
    /**
    * Выбираем необходимы код элемента профиля крышки сплошного checkProfileCap
    * 
    * @param mixed $value - искомый цвет профиля крышки
    * 
    * @return код элемента прояиля крышки, группы DH3579
    */
    private function checkProfileCap($value)
    {
        $this->infoArray = array();
        $DH3579["неокрашенный"] = "CB000182790";
        $DH3579["9003"] = "CB000186698";
        $DH3579["8014"] = "CB000161467";
        $DH3579["5005"] = "CB000186699";
        $DH3579["6005"] = "CB000161468";
        $DH3579["3005"] = "CB000161469";
        $DH3579["7004"] = "CB000186700";
        $DH3579["1014"] = "CB000186701";
        $DH3579["9006"] = "CB000186452";
        $DH3579["8017"] = "CB000161470";
        $DH3579["3000"] = "CB000182790";
        $DH3579["8017MM"] = "CB000227654";
        if (empty($DH3579[$value])) {
            $DH3579[$value] = "CB000182790";
        }
        
        return $DH3579[$value];
    }
    
    /**
    * Выбираем необходимы код элемента профиля труба сплошного checkProfileTube
    * 
    * @param mixed $value - искомый цвет профиля труба
    * 
    * @return код элемента профиля труба, группы DH3584
    */
    private function checkProfileTube($value)
    {
        $this->infoArray = array();
        $DH3584["неокрашенный"] = "CB000182893";
        $DH3584["9003"] = "CB000192697";
        $DH3584["8014"] = "CB000182992";
        $DH3584["5005"] = "CB000192698";
        $DH3584["6005"] = "CB000183005";
        $DH3584["3005"] = "CB000182991";
        $DH3584["7004"] = "CB000192699";
        $DH3584["1014"] = "CB000192700";
        $DH3584["9006"] = "CB000192726";
        $DH3584["8017"] = "CB000182995";
        $DH3584["3000"] = "CB000182893";
        if (empty($DH3584[$value])) {
            $DH3584[$value] = "CB000182893";
        }
        
        return $DH3584[$value];
    }
    
    /**
    * Выбираем необходимы код элемента профиля штакетник сплошного checkProfileFence
    * 
    * @param mixed $value - искомый цвет профиля штакетник
    * 
    * @return код элемента профиля штакетник, группы DH3580
    */
    private function checkProfileFence($value)
    {
        $this->infoArray = array();
        $DH3580["неокрашенный"] = "00000014347";
        $DH3580["9003"] = "00000015130";
        $DH3580["8014"] = "00000015131";
        $DH3580["5005"] = "00000015132";
        $DH3580["6005"] = "00000015133";
        $DH3580["3005"] = "00000015134";
        $DH3580["7004"] = "00000111908";
        $DH3580["1014"] = "00000111909";
        $DH3580["9006"] = "00000111910";
        if (empty($DH3580[$value])) {
            $DH3580[$value] = "00000014347";
        }
        
        return $DH3580[$value];
    }
}