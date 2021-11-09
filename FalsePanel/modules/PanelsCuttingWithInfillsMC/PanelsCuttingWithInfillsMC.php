<?php

/**
 * Раскрой панелей с филенкой
 * PHP version 5.4
 * @category Yii
 * @author   Kuznetsov Y. <kuznetsovyuriial@mail.ru>
 */
class PanelsCuttingWithInfillsMC extends AbstractModelCalculation
{
    /**
    * Переменная хранит имя класса
    * 
    * @var string 
    */
    public $nameModule = 'PanelsCuttingWithInfillsMC';

    /**
    * Алгоритм
    * 
    * @return bool
    */
    public function Algorithm()
    {
        $ShieldPanelsReplaced = array();
        if ($this->ShieldPanelsWithInfill == 1) {
            $model = ShieldModel::model()->find(array(
                'condition' => 'id = :id',
                'params'    => array(':id' => Yii::app()->container->CurrentPanelType)
            ));
            
            $this->ShieldInfillCount = 0;
            $this->ShieldInfillCountAdd = 0;
            
            if (!is_array($this->ShieldInfills) || (count($this->ShieldInfills) == 0)) {
                $fills = array();
            } else {
                $fills = $this->ShieldInfills;
            }
            if (!is_array($this->ShieldInfillsAdd) || (count($this->ShieldInfillsAdd) == 0)) {
                $fillsAdd = array();
            } else {
                $fillsAdd = $this->ShieldInfillsAdd;
            }
            if ($this->VerticalPanel == 0) {
                if ($this->ShieldPanelCount > 0) {
                    $Y = 0;
                    for ($n = 1; $n <= $this->ShieldPanelCount; $n++) {
                        $Y = $Y + ($this->ShieldPanels[$n] + $this->ShieldPanelPadding);
                    }
                    $Y = $Y + $this->ShieldPanelPadding;

                    $count   = round(($this->ShieldWidth + $this->ShieldInfillStep)/($this->ShieldInfillWidth + $this->ShieldInfillStep));
                    $indentX = (($this->ShieldWidth + $this->ShieldInfillStep) - $count *($this->ShieldInfillWidth + $this->ShieldInfillStep))/2;
                    if ($indentX < -60) {
                        $count   = floor(($this->ShieldWidth + $this->ShieldInfillStep)/($this->ShieldInfillWidth + $this->ShieldInfillStep));
                        $indentX = (($this->ShieldWidth + $this->ShieldInfillStep) - $count *($this->ShieldInfillWidth + $this->ShieldInfillStep))/2;
                    }

                    for ($n = $this->ShieldPanelCount; $n >= 1; $n--) {
                        if ($this->ShieldPanels[$n] - ($this->ShieldInfillHeight + 100) >= 0) {
                            $ShieldPanelsReplaced[] = 0;
                            if ($n == $this->ShieldPanelCount) {
                                $DeltaY = -($this->ShieldWholePanels[$n] - $this->ShieldPanels[$n]);
                            } else {
                                $DeltaY = 0;
                            }
                            $IndentY = $Y - ($this->ShieldWholePanels[$n] + $this->ShieldInfillHeight) / 2 - $DeltaY;
                            $X       = $indentX;
                            while ($X <= $this->ShieldWidth - $indentX) {
                                $dxInfill = $X;
                                $dyInfill = $IndentY;
                                $Width  = $this->ShieldInfillWidth;
                                $Height = $this->ShieldInfillHeight;
                                if ($X <= 0) {
                                    $dxInfill = 0;
                                    $Width = $this->ShieldInfillWidth - $X;
                                } elseif ($X + $this->ShieldInfillWidth  >= $this->ShieldWidth){
                                    $Width = $this->ShieldInfillWidth + ($this->ShieldWidth - ($X + $this->ShieldInfillWidth));
                                }

                                if ($IndentY + $this->ShieldInfillHeight >=  $this->ShieldHeight) {
                                    $Height = $this->ShieldInfillHeight - ($IndentY + $this->ShieldInfillHeight - $this->ShieldHeight);
                                }
                                if ($IndentY <= 0) {
                                    $Height = $this->ShieldInfillHeight + $IndentY;
                                    $dyInfill = 0;
                                }


                                $fills[] = array(
                                    "X"      => $dxInfill,
                                    "Y"      => $dyInfill,
                                    "Width"  => $Width,
                                    "Height" => $Height
                                );

                                $this->ShieldInfillCount++;
                                $X = $X + ($this->ShieldInfillWidth + $this->ShieldInfillStep);
                            }
                            if ($indentX > $this->ShieldInfillStep) {
                                $fills[]                 = array(
                                    "X"      => 0,
                                    "Y"      => $IndentY,
                                    "Width"  => $indentX - $this->ShieldInfillStep,
                                    "Height" => $this->ShieldInfillHeight
                                );
                                $fills[]                 = array(
                                    "X"      => $this->ShieldWidth - ($indentX - $this->ShieldInfillStep),
                                    "Y"      => $IndentY,
                                    "Width"  => $indentX - $this->ShieldInfillStep,
                                    "Height" => $this->ShieldInfillHeight
                                );
                                $this->ShieldInfillCount = $this->ShieldInfillCount + 2;
                            }
                        } else {
                            $ShieldPanelsReplaced[] = 1;
                        }
                        $Y = $Y - ($this->ShieldPanels[$n] + $this->ShieldPanelPadding);
                    }
                }
                if ($this->ShieldPanelCountAdd > 0) {
                    $Y = 0;
                    for ($n = 1; $n <= $this->ShieldPanelCountAdd; $n++) {
                        $Y = $Y + ($this->ShieldPanelsAdd[$n] + $this->ShieldPanelPadding);
                    }
                    $Y = $Y + $this->ShieldPanelPadding;

                    $count   = round(($this->ShieldWidth + $this->ShieldInfillStep)/($this->ShieldInfillWidth + $this->ShieldInfillStep));
                    $indentX = (($this->ShieldWidth + $this->ShieldInfillStep) - $count *($this->ShieldInfillWidth + $this->ShieldInfillStep))/2;
                    if ($indentX < -60) {
                        $count   = floor(($this->ShieldWidth + $this->ShieldInfillStep)/($this->ShieldInfillWidth + $this->ShieldInfillStep));
                        $indentX = (($this->ShieldWidth + $this->ShieldInfillStep) - $count *($this->ShieldInfillWidth + $this->ShieldInfillStep))/2;
                    }

                    for ($n = $this->ShieldPanelCountAdd; $n >= 1; $n--) {
                        if ($this->ShieldPanelsAdd[$n] - ($this->ShieldInfillHeight + 100) >= 0) {
                            $ShieldPanelsReplaced[] = 0;
                            if ($n == $this->ShieldPanelCountAdd) {
                                $DeltaY = -($this->ShieldWholePanelsAdd[$n] - $this->ShieldPanelsAdd[$n]);
                            } else {
                                $DeltaY = 0;
                            }
                            $IndentY = $Y - ($this->ShieldWholePanelsAdd[$n] + $this->ShieldInfillHeight) / 2 - $DeltaY;
                            $X       = $indentX;
                            while ($X <= $this->ShieldWidth - $indentX) {
                                $dxInfill = $X;
                                $dyInfill = $IndentY;
                                $Width  = $this->ShieldInfillWidth;
                                $Height = $this->ShieldInfillHeight;
                                if ($X <= 0) {
                                    $dxInfill = 0;
                                    $Width = $this->ShieldInfillWidth - $X;
                                } elseif ($X + $this->ShieldInfillWidth  >= $this->ShieldWidth){
                                    $Width = $this->ShieldInfillWidth + ($this->ShieldWidth - ($X + $this->ShieldInfillWidth));
                                }

                                if ($IndentY + $this->ShieldInfillHeight >=  $this->ShieldHeight) {
                                    $Height = $this->ShieldInfillHeight - ($IndentY + $this->ShieldInfillHeight - $this->ShieldHeight);
                                }
                                if ($IndentY <= 0) {
                                    $Height = $this->ShieldInfillHeight + $IndentY;
                                    $dyInfill = 0;
                                }


                                $fillsAdd[] = array(
                                    "X"      => $dxInfill,
                                    "Y"      => $dyInfill,
                                    "Width"  => $Width,
                                    "Height" => $Height
                                );

                                $this->ShieldInfillCountAdd++;
                                $X = $X + ($this->ShieldInfillWidth + $this->ShieldInfillStep);
                            }
                            if ($indentX > $this->ShieldInfillStep) {
                                $fillsAdd[]                 = array(
                                    "X"      => 0,
                                    "Y"      => $IndentY,
                                    "Width"  => $indentX - $this->ShieldInfillStep,
                                    "Height" => $this->ShieldInfillHeight
                                );
                                $fillsAdd[]                 = array(
                                    "X"      => $this->ShieldWidth - ($indentX - $this->ShieldInfillStep),
                                    "Y"      => $IndentY,
                                    "Width"  => $indentX - $this->ShieldInfillStep,
                                    "Height" => $this->ShieldInfillHeight
                                );
                                $this->ShieldInfillCountAdd = $this->ShieldInfillCountAdd + 2;
                            }
                        } else {
                            $ShieldPanelsReplaced[] = 1;
                        }
                        $Y = $Y - ($this->ShieldPanelsAdd[$n] + $this->ShieldPanelPadding);
                    }
                }
            }
            
            
            if ($this->VerticalPanel == 1) {
                if ($this->ShieldPanelCount > 0) {
                    $X = 0;
                    for ($n = 1; $n <= $this->ShieldPanelCount; $n++) {
                        $X = $X + ($this->ShieldPanels[$n] + $this->ShieldPanelPadding);
                    }
                    $X = $X + $this->ShieldPanelPadding;

                    $count   = round(($this->ShieldHeight)/($this->ShieldInfillWidth + $this->ShieldInfillStep));
                    $IndentY = (($this->ShieldHeight + $this->ShieldInfillStep) - $count *($this->ShieldInfillWidth + $this->ShieldInfillStep))/2;
                    if ($IndentY < -60) {
                        $count   = floor(($this->ShieldHeight)/($this->ShieldInfillWidth + $this->ShieldInfillStep));
                        $IndentY = (($this->ShieldHeight + $this->ShieldInfillStep) - $count *($this->ShieldInfillWidth + $this->ShieldInfillStep))/2;
                    }

                    //for ($n = $this->ShieldPanelCount; $n >= 1; $n--) {
                    for ($n = $this->ShieldPanelCount; $n >= 1; $n--) {
                        $a = $this->ShieldPanels[$n] - ($this->ShieldInfillHeight + 100);
                        if ($this->ShieldPanels[$n] - ($this->ShieldInfillHeight + 100) >= 0) {
                            $ShieldPanelsReplaced[] = 0;
                            if ($n == $this->ShieldPanelCount) {
                                $DeltaY = -($this->ShieldWholePanels[$n] - $this->ShieldPanels[$n]);
                                //$DeltaY = 0;
                            } else {
                                $DeltaY = 0;
                            }
                            $IndentX = $X - ($this->ShieldWholePanels[$n] + $this->ShieldInfillHeight) / 2 - $DeltaY;
                            $Y       = $IndentY;
                            while ($Y <= $this->ShieldHeight - $IndentY) {
                                $dxInfill = $IndentX;
                                $dyInfill = $Y;
                                $Width  = $this->ShieldInfillHeight;
                                $Height = $this->ShieldInfillWidth;
                                /*if ($X <= 0) {
                                    $dxInfill = 0;
                                    $Width = $this->ShieldInfillWidth - $X;
                                } elseif ($X + $this->ShieldInfillWidth  >= $this->ShieldWidth){
                                    $Width = $this->ShieldInfillWidth + ($this->ShieldWidth - ($X + $this->ShieldInfillWidth));
                                }
                                */
                                if ($Y + $this->ShieldInfillWidth >=  $this->ShieldHeight) {
                                    $Height = $this->ShieldInfillWidth - ($Y + $this->ShieldInfillWidth - $this->ShieldHeight);
                                }
                                if ($Y <= 0) {
                                    $Height = $this->ShieldInfillWidth + $Y;
                                    $dyInfill = 0;
                                }


                                $fills[] = array(
                                    "X"      => $dxInfill,
                                    "Y"      => $dyInfill,
                                    "Width"  => $Width,
                                    "Height" => $Height
                                );

                                $this->ShieldInfillCount++;
                                $Y = $Y + ($this->ShieldInfillWidth + $this->ShieldInfillStep);
                            }
                            /*if ($indentY > $this->ShieldInfillStep) {
                                $fills[]                 = array(
                                    "X"      => 0,
                                    "Y"      => $IndentY,
                                    "Width"  => $indentY - $this->ShieldInfillStep,
                                    "Height" => $this->ShieldInfillHeight
                                );
                                $fills[]                 = array(
                                    "X"      => $this->ShieldWidth - ($indentY - $this->ShieldInfillStep),
                                    "Y"      => $IndentY,
                                    "Width"  => $indentY - $this->ShieldInfillStep,
                                    "Height" => $this->ShieldInfillHeight
                                );
                                $this->ShieldInfillCount = $this->ShieldInfillCount + 2;
                            }*/
                        } else {
                            $ShieldPanelsReplaced[] = 1;
                        }
                        $X = $X - ($this->ShieldPanels[$n] + $this->ShieldPanelPadding);
                    }
                }
            
                if ($this->ShieldPanelCountAdd > 0) {
                    $X = 0;
                    for ($n = 1; $n <= $this->ShieldPanelCountAdd; $n++) {
                        $X = $X + ($this->ShieldPanelsAdd[$n] + $this->ShieldPanelPadding);
                    }
                    $X = $X + $this->ShieldPanelPadding;

                    $count   = round(($this->H_n)/($this->ShieldInfillWidth + $this->ShieldInfillStep));
                    $IndentY = (($this->H_n + $this->ShieldInfillStep) - $count *($this->ShieldInfillWidth + $this->ShieldInfillStep))/2;
                    if ($IndentY < -60) {
                        $count   = floor(($this->H_n)/($this->ShieldInfillWidth + $this->ShieldInfillStep));
                        $IndentY = (($this->H_n + $this->ShieldInfillStep) - $count *($this->ShieldInfillWidth + $this->ShieldInfillStep))/2;
                    }

                    //for ($n = $this->ShieldPanelCount; $n >= 1; $n--) {
                    for ($n = $this->ShieldPanelCountAdd; $n >= 1; $n--) {
                        $a = $this->ShieldPanelsAdd[$n] - ($this->ShieldInfillHeight + 100);
                        if ($this->ShieldPanelsAdd[$n] - ($this->ShieldInfillHeight + 100) >= 0) {
                            $ShieldPanelsReplaced[] = 0;
                            if ($n == $this->ShieldPanelCountAdd) {
                                $DeltaY = -($this->ShieldWholePanelsAdd[$n] - $this->ShieldPanelsAdd[$n]);
                                //$DeltaY = 0;
                            } else {
                                $DeltaY = 0;
                            }
                            $IndentX = $X - ($this->ShieldWholePanelsAdd[$n] + $this->ShieldInfillHeight) / 2 - $DeltaY;
                            $Y       = $IndentY;
                            while ($Y <= $this->H_n - $IndentY) {
                                $dxInfill = $IndentX;
                                $dyInfill = $Y;
                                $Width  = $this->ShieldInfillHeight;
                                $Height = $this->ShieldInfillWidth;
                                /*if ($X <= 0) {
                                    $dxInfill = 0;
                                    $Width = $this->ShieldInfillWidth - $X;
                                } elseif ($X + $this->ShieldInfillWidth  >= $this->ShieldWidth){
                                    $Width = $this->ShieldInfillWidth + ($this->ShieldWidth - ($X + $this->ShieldInfillWidth));
                                }
                                */
                                if ($Y + $this->ShieldInfillWidth >=  $this->H_n) {
                                    $Height = $this->ShieldInfillWidth - ($Y + $this->ShieldInfillWidth - $this->H_n);
                                }
                                if ($Y <= 0) {
                                    $Height = $this->ShieldInfillWidth + $Y;
                                    $dyInfill = 0;
                                }


                                $fillsAdd[] = array(
                                    "X"      => $dxInfill,
                                    "Y"      => $dyInfill,
                                    "Width"  => $Width,
                                    "Height" => $Height
                                );

                                $this->ShieldInfillCount++;
                                $Y = $Y + ($this->ShieldInfillWidth + $this->ShieldInfillStep);
                            }
                            /*if ($indentY > $this->ShieldInfillStep) {
                                $fills[]                 = array(
                                    "X"      => 0,
                                    "Y"      => $IndentY,
                                    "Width"  => $indentY - $this->ShieldInfillStep,
                                    "Height" => $this->ShieldInfillHeight
                                );
                                $fills[]                 = array(
                                    "X"      => $this->ShieldWidth - ($indentY - $this->ShieldInfillStep),
                                    "Y"      => $IndentY,
                                    "Width"  => $indentY - $this->ShieldInfillStep,
                                    "Height" => $this->ShieldInfillHeight
                                );
                                $this->ShieldInfillCount = $this->ShieldInfillCount + 2;
                            }*/
                        } else {
                            $ShieldPanelsReplaced[] = 1;
                        }
                        $X = $X - ($this->ShieldPanelsAdd[$n] + $this->ShieldPanelPadding);
                    }
                }
            }
            
            $this->ShieldInfills = $fills;
            $this->ShieldInfillsAdd = $fillsAdd;
            $str = substr($this->Index, -2);
            //отбираем первые 2 символа из индекса
            //$str = substr($str, -3, 2);
            $mH = $this->ShieldHeight;
            $RastOtr = 0 - $mH;
            $mW = $this->ShieldWidth;
            $mWpan = 0;
            if ($this->ShieldWholePanels)
                $mWpan = $this->ShieldWholePanels[1];
            $mBhorze = 0;
            if ($this->VerticalPanel == 0)
                $mBhorze = 1; 
            $mPstep = $this->ShieldInfillStep;

            $w_doppan1 = 0;

            $mRaddpan1 = 0;

            $shit = 0;

            $Key = 0;

            $Look_DlgPhil = 0;
            /*if ($Look_DlgPhil == false) {
                if ($mBhorze == 0) {
                    $mRaddpan1 = ShowDlgPhil($mW, $mWpan, $mBhorze);
                    $mRaddpan2 = $mRaddpan1;
                } else {
                    $mRaddpan = ShowDlgPhil($H_v, $mWpan, $mBhorze);
                    if ( ($mRaddpan == -1) || ($mRaddpan == -2) ) {
                        $mRaddpan1 = 0;
                        $mRaddpan2 = ShowDlgPhil($H_n, $mWpan, $mBhorze);
                    } else {
                        $mRaddpan1 = $mRaddpan;
                        $mRaddpan2 = $mRaddpan;
                    }
                }
            }*/

            if (
                    $str == "00" || $str == "01" || $str == "20" || $str == "21" || 
                    $str == "30" || $str == "31" || $str == "40" || $str == "41" || 
                    $str == "50" || $str == "51" || $str == "60" || $str == "61" || 
                    $str == "70" || $str == "71"
            ) {
                if ($mPstep != 0) {
                    $Panels1 = "ПанельДоп1";
                    $Panels2 = "ПанельДоп2";
                } else {
                    $Panels1 = "Панель";
                    $Panels2 = "Панель";
                }
                if ($mBhorze == 0) {
                } else {
                    $this->FillShit($shit, 0, $mH+$RastOtr, $mW, $mH, $mWpan, $mBhorze, $Key, $mPstep, $Panels1, $w_doppan1, $mRaddpan1, "Панель", 1, false);
                }

            }
        }
        $this->ShieldPanelsReplaced = array_reverse($ShieldPanelsReplaced);
        return true;
    }

    /**
    * Название модуля
    * 
    * @return string
    */
    public function getTitle()
    {
        return 'Раскрой панелей с филенкой';
    }
    public function FillShit($shit, $x0, $y0, $dx, $dy, $w_pan, $b_horze, $n_hingle, $phil_step, $name_doppan, $w_doppan, $rasp_addpan, $b_basic, $poz_arrow, $bVerticalJumper ) {
	//shit file.shit щит +
	//w_pan ширина панелей +
	//b_horze расположение(вертикальное/горизонтальное) +
	//n_hingle ручка(0-нет 1-справа 2-слева) +
	//phil_step шаг филенки(0-нет филенки !0-есть филенка и собственно ее шаг) +
	//code_pan код панелей
	//cod_addpan код дополнительных панелей по краям
	//b_show_all что то типа показывать имя панели
	//rasp_addpan расположение дополнительных панелей 0-симметрично 1-справа/снизу 2-слева/сверху +

	//FS_H = shit.Height-5
	//FS_W = shit.Width
	//file.shit.adddetal 0, "Прямоугольник", 0, shit.Height, shit.Width, shit.Height, 0, 128, RGB(210, 210,210)

	
	
	//file.mymsgbox cstr(isnull(x0))+","+cstr(isnull(y0))+","+cstr(isnull(dx))+","+cstr(isnull(dy))+","+ _
	//cstr(isnull(w_pan))+","+cstr(isnull(b_horze))+","+cstr(isnull(n_hingle))+","+cstr(isnull(phil_step))+"," + cstr(isnull(name_doppan))+ _
	//","+cstr(isnull(w_doppan))+","+cstr(isnull(rasp_addpan))+","+cstr(isnull(b_basic))+","+cstr(isnull(poz_arrow))+","+cstr(isnull(bVerticalJumper))
        $this->infoArray = array();
	
	$FS_W = $dx;
	$FS_H = $dy;

	if ($b_horze == 0) {
		$x = $x0;
        } else {
		$x = $y0;
        }
	$FS_pwi = $w_pan;
	$FS_ost_x = 0;
	$FS_ost_y = 0;
	$FS_otst_phil = 0;
	$FS_otst_rect = 0;
	$FS_y_phil = 0;
	$FS_b_add_pan = false;
	
	$this->PhilMessage = "";
        
        $Look_DlgPhil = false;
        
        $ost = 0;
	$fills = array();
	
	switch ($b_horze) {//рассчет филенки по вертикали/горизонтали
            //====================================================================================
            Case 0:
            //====================================================================================
                $FS_b_add_pan = false;
                if ($phil_step != 0) {
                    $FS_n_rect = round($FS_H / (450 + $phil_step), 0, PHP_ROUND_HALF_EVEN);
                    $FS_ost_y = $FS_H - $FS_n_rect * (450 + $phil_step);
                    if ( ((abs($FS_ost_y) > $phil_step + 20) && (abs($FS_ost_y) - 450 < 60)) || ( $FS_H - (450*$FS_n_rect) - ($phil_step*($FS_n_rect-1))<60 ) ) {
                        if ($Look_DlgPhil == false) {
                            $this->infoArray[] = Yii::t('steps', 'Внимание, профиль будет налезать на квадраты!');
                        }
                        $this->PhilMessage = OrderModel::translitatorForPhantomJS("Внимание, профиль налезает на квадраты!", !Yii::app()->container->autoCalc);
                    } else {
                        $this->PhilMessage = "";
                    }
                    if ($FS_ost_y > $phil_step + 20) {
                        if ($FS_ost_y - 450 < 60) {
                            $FS_otst_rect = ($FS_ost_y - 450) / 2;
                            $FS_n_rect = $FS_n_rect + 1;
                        }
                    } else {
                        $FS_otst_rect = ($FS_ost_y + $phil_step) / 2;
                    }
                }
                $this->CalcOst($FS_W, $w_pan, $phil_step, $ost, $FS_ost_x, $FS_b_add_pan);
                if ($phil_step != 0) {
                    $FS_otst_phil = $FS_ost_x / 2;
                    if ($FS_b_add_pan == true) {
                        if ($rasp_addpan == 1) {
                            $FS_otst_phil = $FS_ost_x + 2;
                        }
                        if ($rasp_addpan == 2) {
                            $FS_otst_phil = 0;
                        }
                    }
                } else {
                    $FS_otst_phil = $ost / 2;
                    //FS_b_add_pan=false	
                    switch ($rasp_addpan) {
                        Case 1:
                            $FS_otst_phil = $ost + 2;
                            break;
                        Case 2:
                            $FS_otst_phil = 0;
                            break;
                    }
                }
                break;
            //====================================================================================	
            Case 1:
                $FS_b_add_pan = false;
                if ($phil_step != 0) {
                        //FS_b_add_pan = false
                        $FS_n_rect = round(($FS_W / (450 + $phil_step)), 0, PHP_ROUND_HALF_EVEN);
                        $FS_ost_x = $FS_W - $FS_n_rect * (450 + $phil_step);
                        //if (abs(FS_ost_x) > phil_step + 20) and (abs(FS_ost_x) - 450 < 60) and file("Look_DlgPhil") = false then
                        if ( ((abs($FS_ost_x) > $phil_step + 20) && (abs($FS_ost_x) - 450 < 60)) || ($FS_W - (450*$FS_n_rect) - ($phil_step*($FS_n_rect - 1)) < 60 ) ) {
                            if ($Look_DlgPhil == false) {
                                $this->infoArray[] = Yii::t('steps', 'Внимание, профиль будет налезать на квадраты!');
                            }
                            $this->PhilMessage = OrderModel::translitatorForPhantomJS("Внимание, профиль налезает на квадраты!", !Yii::app()->container->autoCalc);
                        } else {
                            $this->PhilMessage = "";
                        }

                        if ($FS_ost_x > $phil_step + 20) {
                            if ($FS_ost_x - 450 < 60) {
                                $FS_otst_rect = ($FS_ost_x - 450) / 2;
                                $FS_n_rect = $FS_n_rect + 1;
                            }
                        } else {
                            $FS_otst_rect = ($FS_ost_x + $phil_step) / 2;
                        }
                }

                $this->CalcOst($FS_H, $w_pan, $phil_step, $ost, $FS_ost_y, $FS_b_add_pan);

                if ($phil_step != 0) {
                    $FS_otst_phil = $FS_ost_y / 2;
                    if ($FS_b_add_pan == true || $phil_step != 0) {
                        if ($rasp_addpan == 1) {
                            $FS_otst_phil = $FS_ost_y + 2;
                        }

                        if ($rasp_addpan == 2) {
                            $FS_otst_phil = 0;
                        }
                    }
                } else {
                    $FS_otst_phil = $FS_ost_y / 2;
                    switch ($rasp_addpan) {
                        Case 1:
                            $FS_otst_phil = $FS_ost_y + 2;
                            break;
                        Case 2:
                            $FS_otst_phil = 0;
                            break;
                    }
                }
	//====================================================================================	
        }

	$H = $FS_H;
	$W = $FS_W;
	if ($n_hingle != 0) {
            //------for showing hole of handle---------------------------
            $b_new_handle = false;
            $s_type_pr = "";
            if ($H <= 2000) {
                $s_type_pr = "vert1";
                $y_handle = round(($H)/2, PHP_ROUND_HALF_EVEN) - 91;
            } else {
                $s_type_pr = "vert2";
                $y_handle = 1300 - $pr2 - $h_bal - 72 + 25;
            }
            //-----------------------------------------------------------
        }

	switch ($b_horze) {
		Case 0:
		//====================================================================================
		//Вертикальные
		//====================================================================================
			/*$x = 0;
			//H = H - 5
			$a = floor($W/($FS_pwi + 2));
			file("n_pan") = a
			if FS_otst_phil > 0 then
				if FS_otst_phil > 15 then
					//--------------------------------------------------
					Select Case poz_arrow
						Case 0,1
							shit.addarrow 0, FS_otst_phil, x+x0, H+y0, FS_otst_phil, 0, -1, 3, 75
						Case 2,3
							shit.addarrow 0, FS_otst_phil, FS_otst_phil+x0, 0+y0, FS_otst_phil, 180, -1, 3, 150
					End Select
					//--------------------------------------------------
				end if
				if FS_b_add_pan=true then
					if FS_otst_phil > 15 then
						shit.adddetal 0, cstr(name_doppan), x+x0, y0+H, FS_otst_phil, H, 0, 128, RGB(120, 210,210)
						w_doppan = FS_otst_phil
						file.shit.addtext 0, "Отрезать "+cstr(w_doppan), x+x0, y0+H, FS_pwi, 5, 90, RGB(120, 210,210),90 

					end if
					x = x + FS_otst_phil+2
				else	
					//msgbox "b_basic = " + b_basic
					shit.adddetal 0, cstr(b_basic), x+x0, y0+H, FS_otst_phil, H, 0, 128, RGB(210, 210,210)

					
					if phil_step <> 0 then
						r_y = FS_otst_rect+450
						for i = 1 to FS_n_rect
							Select Case i
								Case 1
									if abs(r_y) < 450 then
										shit.adddetal 0, "Прямоугольник", x+(2*FS_otst_phil-FS_pwi-300)/2+x0 , y0+r_y, 300, 450 - abs(FS_otst_rect), 0, 128, RGB(210, 210,210)
										r_y = r_y + 450 + phil_step
									else 
										shit.adddetal 0, "Прямоугольник", x+(2*FS_otst_phil-FS_pwi-300)/2+x0 , y0+r_y, 300, 450, 0, 128, RGB(210, 210,210)
										r_y = r_y + 450 + phil_step
									end if
								Case FS_n_rect
									if r_y > H then
										shit.adddetal 0, "Прямоугольник", x+(2*FS_otst_phil-FS_pwi-300)/2+x0, y0+r_y - abs(H - r_y), 300, 450 - abs(H - r_y), 0, 128, RGB(210, 210,210)
										r_y = r_y + 450 + phil_step
									else
										shit.adddetal 0, "Прямоугольник", x+(2*FS_otst_phil-FS_pwi-300)/2+x0 , y0+r_y, 300, 450, 0, 128, RGB(210, 210,210)
										r_y = r_y + 450 + phil_step
									end if
								Case else
									shit.adddetal 0, "Прямоугольник", x+(2*FS_otst_phil-FS_pwi-300)/2+x0 , y0+r_y, 300, 450, 0, 128, RGB(210, 210,210)
									r_y = r_y + 450 + phil_step
							end select
						next
						a = a - 1
					end if
					x = x + FS_otst_phil+2
				end if
			end if

			if n_hingle <> 0 then
				//----------------------------------------------------
				if rasp_addpan = 0 then
					file("w_add") = (W - a*(FS_pwi+2))/2
				else
					file("w_add") = W - a*(FS_pwi+2)
				end if
				file("w_add") = W - a*(FS_pwi+2)  //размер остатка панели
				if n_hingle = 2 then
					if file("w_add") > 350 then  
						x_first = x+x0 + file("n_pan") * FS_pwi
					else
						x_first = x+x0 + (file("n_pan") - 1) * FS_pwi
					end if
				else					
					if file("w_add") >= 350 then 
						x_first = 0
					else
						x_first = 0 + file("w_add")
					end if
				end if
				//----------------------------------------------------
			end if
	
			for z = 1 to a
				shit.adddetal 0, cstr(b_basic), x+x0, y0+H, FS_pwi, H, 0, 128, RGB(210, 210,210)
				
				if phil_step <> 0 then
					r_y = FS_otst_rect+450
					for i = 1 to FS_n_rect
						Select Case i
							Case 1
								if abs(r_y) < 450 then
									shit.adddetal 0, "Прямоугольник", x+(FS_pwi-300)/2+x0 , y0+r_y, 300, 450 - abs(FS_otst_rect), 0, 128, RGB(210, 210,210)
									r_y = r_y + 450 + phil_step
								else 
									shit.adddetal 0, "Прямоугольник", x+(FS_pwi-300)/2+x0 , y0+r_y, 300, 450, 0, 128, RGB(210, 210,210)
									r_y = r_y + 450 + phil_step
								end if
							Case FS_n_rect
								if r_y > H then
									shit.adddetal 0, "Прямоугольник", x+(FS_pwi-300)/2+x0, y0+r_y - abs(H - r_y), 300, 450 - abs(H - r_y), 0, 128, RGB(210, 210,210)
									r_y = r_y + 450 + phil_step
								else
									shit.adddetal 0, "Прямоугольник", x+(FS_pwi-300)/2+x0 , y0+r_y, 300, 450, 0, 128, RGB(210, 210,210)
									r_y = r_y + 450 + phil_step
								end if
							Case else
								shit.adddetal 0, "Прямоугольник", x+(FS_pwi-300)/2+x0 , y0+r_y, 300, 450, 0, 128, RGB(210, 210,210)
								r_y = r_y + 450 + phil_step
						end select
					next
				end if
				x = x + FS_pwi + 2
			next
	
			if W-x > 5 then
				file("n_pan") = file("n_pan") + 1
				if x <> 0 then
					if W-x > 15 then
						//--------------------------------------------------
						Select Case poz_arrow
							Case 0,1
								shit.addarrow 0, W-x, x+x0, H+y0, W-x, 0, -1, 3, 75
							Case 2,3
								shit.addarrow 0, W-x, x+x0+(W-x), 0+y0, W-x, 180, -1, 3, 150
						End Select
						//--------------------------------------------------
					end if
				end if
				if FS_b_add_pan=true and a > 0 then
					if W-x > 15 then
						shit.adddetal 0, cstr(name_doppan), x+x0, y0+H, W-x, H, 0, 128, RGB(210, 210,210)
						//file.addmessage "coord:"+cstr(cstr(x+x0))
						
						file.shit.addtext 0, "Отрезать "+cstr(W-x), x+x0, y0+H, FS_pwi, 5, 90, RGB(210, 210,210),90 

						//w_doppan = FS_otst_phil 
						w_doppan = W-x
					end if
				else
					shit.adddetal 0, cstr(b_basic), x+x0, y0+H, W-x, H, 0, 128, RGB(210, 210,210)
									
					if phil_step <> 0 then
						r_y = FS_otst_rect+450
						for i = 1 to FS_n_rect
							Select Case i
								Case 1
									if abs(r_y) < 450 then
										shit.adddetal 0, "Прямоугольник", x+(FS_pwi-300)/2+x0 , y0+r_y, 300, 450 - abs(FS_otst_rect), 0, 128, RGB(210, 210,210)
										r_y = r_y + 450 + phil_step
									else 
										shit.adddetal 0, "Прямоугольник", x+(FS_pwi-300)/2+x0 , y0+r_y, 300, 450, 0, 128, RGB(210, 210,210)
										r_y = r_y + 450 + phil_step
									end if
								Case FS_n_rect
									if r_y > H then
										shit.adddetal 0, "Прямоугольник", x+(FS_pwi-300)/2+x0, y0+r_y - abs(H - r_y), 300, 450 - abs(H - r_y), 0, 128, RGB(210, 210,210)
										r_y = r_y + 450 + phil_step
									else
										shit.adddetal 0, "Прямоугольник", x+(FS_pwi-300)/2+x0 , y0+r_y, 300, 450, 0, 128, RGB(210, 210,210)
										r_y = r_y + 450 + phil_step
									end if
								Case else
									shit.adddetal 0, "Прямоугольник", x+(FS_pwi-300)/2+x0 , y0+r_y, 300, 450, 0, 128, RGB(210, 210,210)
									r_y = r_y + 450 + phil_step
							end select
						next
					end if
				end if  
			end if
			if phil_step = 0 and n_hingle <> 0 then
				if n_hingle = 2 then
					shit.addarrow 0, y_handle, x_first+FS_pwi/2-91, y0+H-y_handle, y_handle, 90, -1, 3, 50
				else
					shit.addarrow 0, y_handle, x_first+FS_pwi/2-91+182, y0+H-y_handle, y_handle, 90, 1, 3, 50
				end if 
				file("b_new_handle") = true
				shit.adddetal 0, "HandleHole",    x_first+FS_pwi/2-91+x0, y0+H-y_handle,   180, 180, 0, 128, RGB(0, 0, 0)
				shit.adddetal 0, "Прямоугольник", x_first+FS_pwi/2-90+x0, y0+H-y_handle+1, 182, 182, 0, 128, RGB(255, 255, 255)
			end if
	
			//Select Case poz_arrow
			//	Case 0
			//		shit.addarrow 1, dx, 0+x0, H+y0, W, 0, -1, 3, 300 
			//		shit.addarrow 1, dy, x0+W, y0+H, dy, 270, -1, 3, 300
			//	Case 1
			//		shit.addarrow 1, dx, 0+x0, H+y0, W, 0, -1, 3, 300 
			//		shit.addarrow 1, dy, x0+0, y0+0, dy, 90, -1, 3, 300
			//	Case 2
			//		shit.addarrow 1, dx, W+x0, 0+y0, W, 180, -1, 3, 300 
			//		shit.addarrow 1, dy, x0+0, y0+0, dy, 90, -1, 3, 300
			//	Case 3
			//		shit.addarrow 1, dx, W+x0, 0+y0, W, 180, -1, 3, 300 
			//		shit.addarrow 1, dy, x0+W, y0+H, dy, 270, -1, 3, 300
			//End Select
                        */
                        break;
		//====================================================================================
		Case 1:
		//====================================================================================
			$a = floor($H/($FS_pwi + 2));
			$n_pan = $a;
			if ($FS_otst_phil > 0) {
				if ($FS_otst_phil > 15) {
					//--------------------------------------------------
					if ($FS_otst_phil != $H) {
						switch ($poz_arrow) {
							Case 0:
                                                            //shit.addarrow 0, FS_otst_phil, x0+W, x, -FS_otst_phil, 270, -1, 3, 150
                                                            break;
                                                        Case 3:
                                                            //shit.addarrow 0, FS_otst_phil, x0+W, x, -FS_otst_phil, 270, -1, 3, 150
                                                            break;
							Case 1:
                                                            //shit.addarrow 0, FS_otst_phil, x0, x+FS_otst_phil, -FS_otst_phil, 90, -1, 3, 75
                                                            break;
                                                        Case 2:
                                                            //shit.addarrow 0, FS_otst_phil, x0, x+FS_otst_phil, -FS_otst_phil, 90, -1, 3, 75
                                                            break;
                                                }
                                        }
					//--------------------------------------------------
					//shit.addarrow 0, FS_otst_phil, x0+W, x, -FS_otst_phil, 270, -1, 3, 150
                                }
				if ($FS_b_add_pan == true) {
					if ($FS_otst_phil > 15) {
						//shit.adddetal 0, cstr(name_doppan), x0+0, x, FS_otst_phil, W, 90, 128, RGB(210, 210,210)
						$w_doppan = $FS_otst_phil;
                                        }
					$x = $x + $FS_otst_phil + 2;
                                } else {
					//shit.adddetal 0, cstr(b_basic), x0+0, x, FS_otst_phil, W, 90, 128, RGB(210, 210,210)
										
					if ($phil_step != 0) {
						$r_x = $FS_otst_rect;
						//MsgBox "x0 = " + cstr(x0) + "; r_x = " + cstr(r_x)
						for ($i = 1; $i <= $FS_n_rect; $i++) {
							switch ($i) {
								Case 1:
									if ($r_x < 0) {
										//shit.adddetal 0, "Прямоугольник", x0+r_x + abs(r_x) , x+300+(2*FS_otst_phil-FS_pwi-300)/2, 450 - abs(r_x), 300, 0, 128, RGB(210, 210,210)
                                                                                $fills[]                 = array(
                                                                                    "X"      => $x0 + $r_x + abs($r_x),
                                                                                    "Y"      => $x+300+(2*$FS_otst_phil-$FS_pwi-300)/2,
                                                                                    "Width"  => $this->ShieldInfillStep - abs($r_x),
                                                                                    "Height" => $this->ShieldInfillHeight
                                                                                );
										$r_x = $r_x + 450 + $phil_step;
                                                                        } else {
										//shit.adddetal 0, "Прямоугольник", x0+r_x, x+300+(2*FS_otst_phil-FS_pwi-300)/2, 450, 300, 0, 128, RGB(210, 210,210)
										$r_x = $r_x + 450 + $phil_step;
                                                                        }
                                                                        break;
								Case $FS_n_rect:
									if ($r_x + 450 > $W) {
										//shit.adddetal 0, "Прямоугольник", x0+r_x, x+300+(2*FS_otst_phil-FS_pwi-300)/2,abs(W - r_x), 300, 0, 128, RGB(210, 210,210)
										$r_x = $r_x + 450 + $phil_step;
                                                                        } else {
										//shit.adddetal 0, "Прямоугольник", x0+r_x, x+300+(2*FS_otst_phil-FS_pwi-300)/2, 450, 300, 0, 128, RGB(210, 210,210)
										$r_x = $r_x + 450 + $phil_step;
                                                                        }
                                                                        break;
								default:
									//shit.adddetal 0, "Прямоугольник", x0+r_x, x+300+(2*FS_otst_phil-FS_pwi-300)/2, 450, 300, 0, 128, RGB(210, 210,210)
									$r_x = $r_x + 450 + $phil_step;
                                                                        break;
                                                        }
                                                }
                                        }
					$a = $a - 1;
					$x = $x + $FS_otst_phil + 2;
                                }
                        }
			$b_calc = false;
			for ($z = 1; $z <= $a; $z++) {
				if ($n_hingle != 0) {
					//----------------------------------------------------
					if ($x + $FS_pwi > $y_handle && $b_calc == false) {
						$y_handle = $H - $x0 - $x - round($FS_pwi/2, PHP_ROUND_HALF_EVEN) - 91 - $FS_pwi;
						
						if ($y0 + $y_handle > 1300) {
                                                    $y_handle = $y_handle - $FS_pwi;
                                                }
						if ($y0 + $y_handle < 690) {
                                                    $y_handle = $y_handle + $FS_pwi;
                                                }
						$b_calc = true;
                                        }
					//----------------------------------------------------
                                }
				//shit.adddetal 0, cstr(b_basic), x0+0, x, FS_pwi, W, 90, 128, RGB(210, 210,210)
									
				if ($phil_step != 0) {
					$r_x = $FS_otst_rect;
					for ($i = 1; $i <= $FS_n_rect; $i++) {
						switch ($i) {
							Case 1:
								if ($r_x < 0) {
									//shit.adddetal 0, "Прямоугольник", x0+r_x + abs(r_x) , x+300+(FS_pwi-300)/2, 450 - abs(r_x), 300, 0, 128, RGB(210, 210,210)
                                                                        $fills[]                 = array(
                                                                            "X"      => $x0 + $r_x + abs($r_x),
                                                                            "Y"      => $x + $this->ShieldInfillHeight + ($FS_pwi - $this->ShieldInfillHeight)/2,
                                                                            "Width"  => $this->ShieldInfillStep - abs($r_x),
                                                                            "Height" => $this->ShieldInfillHeight
                                                                        );
									$r_x = $r_x + 450 + $phil_step;
                                                                } else {
									//shit.adddetal 0, "Прямоугольник", x0+r_x, x+300+(FS_pwi-300)/2, 450, 300, 0, 128, RGB(210, 210,210)
									$r_x = $r_x + 450 + $phil_step;
                                                                }
                                                                break;
							Case $FS_n_rect:
								if ($r_x + 450 > $W) {
									//shit.adddetal 0, "Прямоугольник", x0+r_x, x+300+(FS_pwi-300)/2,abs(W - r_x), 300, 0, 128, RGB(210, 210,210)
									$r_x = $r_x + 450 + $phil_step;
                                                                } else {
									//shit.adddetal 0, "Прямоугольник", x0+r_x, x+300+(FS_pwi-300)/2, 450, 300, 0, 128, RGB(210, 210,210)
									$r_x = $r_x + 450 + $phil_step;
                                                                }
                                                                break;
							default:
								//shit.adddetal 0, "Прямоугольник", x0+r_x, x+300+(FS_pwi-300)/2, 450, 300, 0, 128, RGB(210, 210,210)
								$r_x = $r_x + 450 + $phil_step;
                                                                break;
                                                }
                                        }
                                }
				$x = $x + $FS_pwi + 2;
                        }

			if ($y0 + $H - $x > 5) {
				$n_pan = $n_pan + 1;
				if ($y0 + $H - $x > 15) {
					//--------------------------------------------------
					if ($y0 + $H - $x != $H) {
						switch ($poz_arrow) {
							case 0:
                                                            //shit.addarrow 0, y0+H-x, x0+W, x, -(y0+H-x), 270, -1, 3, 150
                                                            break;
                                                        case 3:
                                                            //shit.addarrow 0, y0+H-x, x0+W, x, -(y0+H-x), 270, -1, 3, 150
                                                            break;
							case 1:
                                                            //shit.addarrow 0, y0+H-x, x0, x+(y0+H-x), -(y0+H-x), 90, -1, 3, 75
                                                            break;
                                                        case 2:
                                                            //shit.addarrow 0, y0+H-x, x0, x+(y0+H-x), -(y0+H-x), 90, -1, 3, 75
                                                            break;
                                                }
                                        }
					//--------------------------------------------------
					//shit.addarrow 0, y0+H-x, x0+W, x, -(y0+H-x), 270, -1, 3, 150
                                }
				if ($FS_b_add_pan == true) {
					if ($y0 + $H - $x > 15) {
						//shit.adddetal 0, cstr(name_doppan), x0+0, x, y0+H-x, W, 90, 128, RGB(210, 210,210)
						$w_doppan = $y0 + $H - $x;
                                        }
                                } else {
					//shit.adddetal 0, cstr(b_basic), x0+0, x, y0+H-x, W, 90, 128, RGB(210, 210,210)
					
					if ($phil_step != 0) {
						$r_x = $FS_otst_rect;
						for ($i = 1; $i <= $FS_n_rect; $i++) {
							switch ($i) {
								case 1:
									if ($r_x < 0) {
										//shit.adddetal 0, "Прямоугольник", x0+r_x + abs(r_x) , x+300+(FS_pwi-300)/2, 450 - abs(r_x), 300, 0, 128, RGB(210, 210,210)
										$r_x = $r_x + 450 + $phil_step;
                                                                        } else {
										//shit.adddetal 0, "Прямоугольник", x0+r_x, x+300+(FS_pwi-300)/2, 450, 300, 0, 128, RGB(210, 210,210)
										$r_x = $r_x + 450 + $phil_step;
                                                                        }
                                                                        break;
								case $FS_n_rect:
									if ($r_x + 450 > $W) {
										//shit.adddetal 0, "Прямоугольник", x0+r_x, x+300+(FS_pwi-300)/2,abs(W - r_x), 300, 0, 128, RGB(210, 210,210)
										$r_x = $r_x + 450 + $phil_step;
                                                                        } else {
										//shit.adddetal 0, "Прямоугольник", x0+r_x, x+300+(FS_pwi-300)/2, 450, 300, 0, 128, RGB(210, 210,210)
										$r_x = $r_x + 450 + $phil_step;
                                                                        }
                                                                        break;
								default:
									//shit.adddetal 0, "Прямоугольник", x0+r_x, x+300+(FS_pwi-300)/2, 450, 300, 0, 128, RGB(210, 210,210)
									$r_x = $r_x + 450 + $phil_step;
                                                                        break;
                                                        }
                                                }
                                        }
                                }
                        }

			if ($phil_step == 0 && $n_hingle != 0) {
				if ($n_hingle == 1) {
					$x_start = 250;
                                } else {
					$x_start = $W - 250 - 182;
					if ($bVerticalJumper == true) $x_start = 2*$W - 250 - 182;
                                }
				
				$b_new_handle = true;
				$s_type_pr = "horz";
				
				//shit.addarrow 0, y0+y_handle, 		x_start, 	y0+H, 			-y0-y_handle, 	90, -1, 3, 50							
				//shit.adddetal 0, "HandleHole", 		x_start,   y0+H-y_handle,   180, 			180, 0, 128, RGB(0,   0,   0)
				//shit.adddetal 0, "Прямоугольник", 	x_start,   y0+H-y_handle+1, 182, 			182, 0, 128, RGB(255,   255,   255)
				//file.shit.adddetal 0, "HandleHole",    x_first+pwi/2-91, H-y_handle,   180, 180, 0, 128, RGB(0, 0, 0)
				//file.shit.adddetal 0, "Прямоугольник", x_first+pwi/2-90, H-y_handle+1, 182, 182, 0, 128, RGB(255, 255, 255)
                        }
			//shit.addarrow 1, dx, 0+x0, H+y0, W, 0, -1, 3, 150 
			//shit.addarrow 1, dy, x0+W, y0+H, dy, 270, -1, 3, 300
			//====================================================================================
                        break;
	}

	switch ($poz_arrow) {
		Case 0:
                    //shit.addarrow 1, dx, 0+x0, H+y0, W, 0, -1, 3, 200 
                    //shit.addarrow 1, dy, x0+W, y0+H, dy, 270, -1, 3, 400
                    break;
		Case 1:
                    //shit.addarrow 1, dx, 0+x0, H+y0, W, 0, -1, 3, 200 
                    //shit.addarrow 1, dy, x0+0, y0+0, dy, 90, -1, 3, 400
                    break;
		Case 2:
                    //shit.addarrow 1, dx, W+x0, 0+y0, W, 180, -1, 3, 200 
                    //shit.addarrow 1, dy, x0+0, y0+0, dy, 90, -1, 3, 400
                    break;
		Case 3:
                    //shit.addarrow 1, dx, W+x0, 0+y0, W, 180, -1, 3, 200 
                    //shit.addarrow 1, dy, x0+W, y0+H, dy, 270, -1, 3, 400
                    break;
        }
	$b_ligth = 1;
        $w_pol_FS = 0;
        
	if ($b_ligth == 1) {
            //file("w_pol_FS") = file("w_pol_FS") + ( W*H*11.6/1000000 + 2*(W+H)*(0.3+1.409)/1000 )
            $w_pol_FS = $w_pol_FS + ($W*$H*11.6/1000000);
        } else {
            //file("w_pol_FS") = file("w_pol_FS") + ( W*H*11.6/1000000 + 2*(W+H)*(0.3+1.732)/1000 )
            $w_pol_FS = $w_pol_FS + ($W*$H*11.6/1000000);
        }
        //$this->ShieldInfills = $fills;
    }
    //=====================================================================================================================================================
    //====================================================================================================================================================='
    public function CalcOst($W, $w_pan, $phil, $ost, $ost_x, $b_add_pan) {
	
	$ost = $W - floor($W / ($w_pan + 2))  * ($w_pan + 2) - 2;
        if ($phil != 0) {
            $b_add_pan = false;
        } else {
            $b_add_pan = true;
        }
	$SDP_y_phil = ($w_pan - 300) / 2;
	$ost_x = $W - (floor($W / ($w_pan + 2)) - 1) * ($w_pan + 2) - 2;
	if ($ost_x < 2 * (300 + $SDP_y_phil) + 60) {
            $ost_x = $W - floor($W / ($w_pan + 2)) * ($w_pan + 2) - 2;
            if ($ost_x/2 <= 385) {
		if ($ost_x <= 385) {
                    $b_add_pan = true;
                    $ost_x = $W - (floor($W / ($w_pan + 2)) ) * ($w_pan + 2) - 2;
                }
            }
        }
    }
    //'=====================================================================================================================================================
    //'=====================================================================================================================================================
}