<?php

/**
 * Форма Проем
 * PHP version 5.5
 * @category Yii
 * @author   Charnou Vitaliy <graffov87@gmail.com>
 */
class FormShieldOptionsMC extends AbstractModelCalculation
{

    /**
     * Название модуля
     * 
     * @var string Название модуля
     */
    public $nameModule = 'FormShieldOptionsMC';

    /**
     * Алгоритм
     * 
     * @return bool
     */
    public function Algorithm()
    {
        $this->TurnOverShveller      = $this->TurnOverShveller;
        $this->UseEdging             = $this->UseEdging;
        $this->VidS                  = $this->VidS;
        $this->Index                 = $this->Index;
        $this->naklon                = $this->naklon;
        $this->ErrShieldParam = 0;
        
        //Тип профилей
        $this->UsualPanoramic = 0;
        $this->WithBeadings = 0;
        $this->WarmProfiles = 0;

        if ($this->ProfileType == "Обычные") {$this->UsualPanoramic = 1;}
        if ($this->ProfileType == "Теплые профиля") {$this->WarmProfiles = 1;}
        if ($this->ProfileType == "Со штапиками") {$this->WithBeadings = 1;}
        
        $this->VerticalPanel = 0;
        $this->SandPanel = 1;
        if ($this->TypeF == "Алюминиевый") {
            if ($this->Index == "DHPF180") {
                $this->n_resh_m = round(0.25 * $this->Hh);
            }
            //Для данных индексов щитов считается высота арки\решетки
            if (
                $this->Index == 'DHPF020' || $this->Index == 'DHPF021' || $this->Index == 'DHPF022' || $this->Index == 'DHPF023' || $this->Index == 'DHPF024' ||
                $this->Index == 'DHPF030' || $this->Index == 'DHPF031' || $this->Index == 'DHPF032' || $this->Index == 'DHPF033' || $this->Index == 'DHPF034' ||
                $this->Index == 'DHPF040' || $this->Index == 'DHPF041' || $this->Index == 'DHPF042' || $this->Index == 'DHPF043' || $this->Index == 'DHPF044' ||
                $this->Index == 'DHPF050' || $this->Index == 'DHPF051' || $this->Index == 'DHPF052' || $this->Index == 'DHPF053' || $this->Index == 'DHPF054' ||
                $this->Index == 'DHPF060' || $this->Index == 'DHPF061' || $this->Index == 'DHPF062' || $this->Index == 'DHPF063' || $this->Index == 'DHPF064' ||
                $this->Index == 'DHPF070' || $this->Index == 'DHPF071' || $this->Index == 'DHPF072' || $this->Index == 'DHPF073' || $this->Index == 'DHPF074' ||
                $this->Index == 'DHPF100' || $this->Index == 'DHPF101' || $this->Index == 'DHPF102' || $this->Index == 'DHPF103' || $this->Index == 'DHPF104' || $this->Index == 'DHPF105' || $this->Index == 'DHPF106' ||
                $this->Index == 'DHPF110' || $this->Index == 'DHPF111' || $this->Index == 'DHPF112' || $this->Index == 'DHPF113' ||
                $this->Index == 'DHPF120' || $this->Index == 'DHPF121' || $this->Index == 'DHPF122' || $this->Index == 'DHPF123' || $this->Index == 'DHPF124' ||
                $this->Index == 'DHPF130' || $this->Index == 'DHPF131' || $this->Index == 'DHPF132' || $this->Index == 'DHPF133' || $this->Index == 'DHPF134' ||
                $this->Index == 'DHPF140' || $this->Index == 'DHPF141' || $this->Index == 'DHPF142' || $this->Index == 'DHPF143' || $this->Index == 'DHPF144' ||
                $this->Index == 'DHPF150' || $this->Index == 'DHPF151' || $this->Index == 'DHPF152' || $this->Index == 'DHPF153' || $this->Index == 'DHPF154' ||
                $this->Index == 'DHPF160' || $this->Index == 'DHPF161' || $this->Index == 'DHPF162' || $this->Index == 'DHPF163' || $this->Index == 'DHPF164' ||
                $this->Index == 'DHPF170' || $this->Index == 'DHPF171' || $this->Index == 'DHPF172' || $this->Index == 'DHPF173' || $this->Index == 'DHPF180' ||
                $this->Index == 'DHPF200' || $this->Index == 'DHPF201' || $this->Index == 'DHPF210' || $this->Index == 'DHPF211' || $this->Index == 'DHPF212' || $this->Index == 'DHPF213' ||
                $this->Index == 'DHPF220' || $this->Index == 'DHPF221' || $this->Index == 'DHPF222' || $this->Index == 'DHPF223' || $this->Index == 'DHPF224' ||
                $this->Index == 'DHPF230' || $this->Index == 'DHPF231' || $this->Index == 'DHPF232' || $this->Index == 'DHPF233' || $this->Index == 'DHPF234' ||
                $this->Index == 'DHPF240' || $this->Index == 'DHPF241' || $this->Index == 'DHPF242' || $this->Index == 'DHPF243' || $this->Index == 'DHPF244' ||
                $this->Index == 'DHPF250' || $this->Index == 'DHPF251' || $this->Index == 'DHPF252' || $this->Index == 'DHPF253' || $this->Index == 'DHPF254' ||
                $this->Index == 'DHPF260' || $this->Index == 'DHPF300' || $this->Index == 'DHPF301' ||
                $this->Index == 'DHPF310' || $this->Index == 'DHPF311' || $this->Index == 'DHPF312' ||
                $this->Index == 'DHPF320' || $this->Index == 'DHPF321' || $this->Index == 'DHPF322' || $this->Index == 'DHPF323' || $this->Index == 'DHPF324' ||
                $this->Index == 'DHPF330' || $this->Index == 'DHPF331' || $this->Index == 'DHPF332' || $this->Index == 'DHPF333' || $this->Index == 'DHPF334' ||
                $this->Index == 'DHPF340' || $this->Index == 'DHPF341' || $this->Index == 'DHPF342' || $this->Index == 'DHPF343' || $this->Index == 'DHPF344'
            ) {
                if ($this->VidS != 'Верх арки' && $this->VidS != 'Верх волны') {
                    $this->b_resh = 1;
                }
            } else {
                $this->b_resh = 0;
            }
            
            if ((
                $this->Index == 'DHPF040' || $this->Index == 'DHPF041' || $this->Index == 'DHPF042' || $this->Index == 'DHPF043' || $this->Index == 'DHPF044' ||
                $this->Index == 'DHPF050' || $this->Index == 'DHPF051' || $this->Index == 'DHPF052' || $this->Index == 'DHPF053' || $this->Index == 'DHPF054' ||
                $this->Index == 'DHPF060' || $this->Index == 'DHPF061' || $this->Index == 'DHPF062' || $this->Index == 'DHPF063' || $this->Index == 'DHPF064' ||
                $this->Index == 'DHPF070' || $this->Index == 'DHPF071' || $this->Index == 'DHPF072' || $this->Index == 'DHPF073' || $this->Index == 'DHPF074' ||
                $this->Index == 'DHPF140' || $this->Index == 'DHPF141' || $this->Index == 'DHPF142' || $this->Index == 'DHPF143' || $this->Index == 'DHPF144' ||
                $this->Index == 'DHPF150' || $this->Index == 'DHPF151' || $this->Index == 'DHPF152' || $this->Index == 'DHPF153' || $this->Index == 'DHPF154' ||
                $this->Index == 'DHPF160' || $this->Index == 'DHPF161' || $this->Index == 'DHPF162' || $this->Index == 'DHPF163' || $this->Index == 'DHPF164' ||
                $this->Index == 'DHPF170' || $this->Index == 'DHPF171' || $this->Index == 'DHPF172' || $this->Index == 'DHPF173' || $this->Index == 'DHPF180' ||
                $this->Index == 'DHPF240' || $this->Index == 'DHPF241' || $this->Index == 'DHPF242' || $this->Index == 'DHPF243' || $this->Index == 'DHPF244' ||
                $this->Index == 'DHPF250' || $this->Index == 'DHPF251' || $this->Index == 'DHPF252' || $this->Index == 'DHPF253' || $this->Index == 'DHPF254' || $this->Index == 'DHPF260' ||
                $this->Index == 'DHPF340' || $this->Index == 'DHPF341' || $this->Index == 'DHPF342' || $this->Index == 'DHPF343' || $this->Index == 'DHPF344' ||
                $this->Index == 'DHPF440' || $this->Index == 'DHPF450' || $this->Index == 'DHPF540'
            )) {
                $this->b_piki = 1;
            } else {
                $this->b_piki = 0;
            }
            if ($this->b_piki == 1 && $this->piki < 150) {
                $this->errorArray = "Высота наконечников должна быть больше 150";
                return true;
            }
            //Выделим щиты (индексы щитов), которые сделаны из сендвич панелей
            $this->SandPanel = 0;
            if (
                $this->Index == 'DHPF000' || $this->Index == 'DHPF001' || $this->Index == 'DHPF020' || $this->Index == 'DHPF021' || $this->Index == 'DHPF030' || $this->Index == 'DHPF031' || 
                $this->Index == 'DHPF040' || $this->Index == 'DHPF041' || $this->Index == 'DHPF050' || $this->Index == 'DHPF051' || $this->Index == 'DHPF060' || $this->Index == 'DHPF061' || 
                $this->Index == 'DHPF070' || $this->Index == 'DHPF071' || 
                $this->Index == 'DHPF100' || $this->Index == 'DHPF101' || $this->Index == 'DHPF105' || $this->Index == 'DHPF106' || $this->Index == 'DHPF120' || $this->Index == 'DHPF121' || 
                $this->Index == 'DHPF130' || $this->Index == 'DHPF131' || $this->Index == 'DHPF140' || $this->Index == 'DHPF141' || $this->Index == 'DHPF150' || $this->Index == 'DHPF151' || 
                $this->Index == 'DHPF160' || $this->Index == 'DHPF161' || 
                $this->Index == 'DHPF220' || $this->Index == 'DHPF221' || $this->Index == 'DHPF230' || $this->Index == 'DHPF231' || $this->Index == 'DHPF240' || $this->Index == 'DHPF241' || $this->Index == 'DHPF250' || $this->Index == 'DHPF251' || 
                $this->Index == 'DHPF320' || $this->Index == 'DHPF330' || $this->Index == 'DHPF340' || $this->Index == 'DHPF321' || $this->Index == 'DHPF331' || $this->Index == 'DHPF341'
            ) {
                $this->SandPanel = 1;
            }
            //Выделим щиты (индексы щитов), в которых используются сендвич панели и они расположены вертикально
            if (
                $this->Index == 'DHPF000' || $this->Index == 'DHPF020' || $this->Index == 'DHPF030' || $this->Index == 'DHPF040' || $this->Index == 'DHPF050' || $this->Index == 'DHPF060' || $this->Index == 'DHPF070' || 
                $this->Index == 'DHPF100' || $this->Index == 'DHPF105' || $this->Index == 'DHPF120' || $this->Index == 'DHPF130' || $this->Index == 'DHPF140' || $this->Index == 'DHPF150' || $this->Index == 'DHPF160' || 
                $this->Index == 'DHPF220' || $this->Index == 'DHPF230' || $this->Index == 'DHPF240' || $this->Index == 'DHPF250' || 
                $this->Index == 'DHPF320' || $this->Index == 'DHPF330' || $this->Index == 'DHPF340'
            ) {
                $this->VerticalPanel = 1;
            }
        
            $Bh = $this->Bh;
            $Hh = $this->Hh;
            $this->b_send = 0;
            if ($this->Index == "DHPF000" || $this->Index == "DHPF001" || $this->Index == "Из ламелей") {
                $this->SandShieldWidth = $Bh - 143;
                $this->SandShieldHeight = $Hh - 143;
                $this->b_send = 1;
            } else if ($this->Index == "DHPF002" || $this->Index == "DHPF003" || $this->Index == "DHPF004" || $this->Index == "DHPF010" || $this->Index == "DHPF011" || $this->Index == "DHPF012" || $this->Index == "DHPF013") {
                $this->SandShieldWidth = $Bh - 144;
                $this->SandShieldHeight = $Hh - 144;
                $this->b_send = 0;
            } else if ($this->Index == "DHPF022" || $this->Index == "DHPF023" || $this->Index == "DHPF024" ||
                $this->Index == "DHPF032" || $this->Index == "DHPF033" || $this->Index == "DHPF034" ||
                $this->Index == "DHPF042" || $this->Index == "DHPF043" || $this->Index == "DHPF044" ||
                $this->Index == "DHPF052" || $this->Index == "DHPF053" || $this->Index == "DHPF054" ||
                $this->Index == "DHPF062" || $this->Index == "DHPF063" || $this->Index == "DHPF064" ||
                $this->Index == "DHPF072" || $this->Index == "DHPF073" || $this->Index == "DHPF074"
            ) {
                $this->SandShieldWidth = $Bh - 144;
                $this->SandShieldHeight = $Hh - $this->resh - 224;
                $this->b_send = 0;
            } else if ($this->Index == "DHPF102" || $this->Index == "DHPF103" || $this->Index == "DHPF104" ||
                $this->Index == "DHPF110" || $this->Index == "DHPF111" || $this->Index == "DHPF112" || $this->Index == "DHPF113" ||
                $this->Index == "DHPF122" || $this->Index == "DHPF123" || $this->Index == "DHPF124" ||
                $this->Index == "DHPF132" || $this->Index == "DHPF133" || $this->Index == "DHPF134" ||
                $this->Index == "DHPF142" || $this->Index == "DHPF143" || $this->Index == "DHPF144" ||
                $this->Index == "DHPF152" || $this->Index == "DHPF153" || $this->Index == "DHPF154" ||
                $this->Index == "DHPF300" || $this->Index == "DHPF301" || $this->Index == "DHPF302" ||
                $this->Index == "DHPF310" || $this->Index == "DHPF311" || $this->Index == "DHPF312" ||
                $this->Index == "DHPF322" || $this->Index == "DHPF323" || $this->Index == "DHPF324" ||
                $this->Index == "DHPF332" || $this->Index == "DHPF333" || $this->Index == "DHPF334" ||
                $this->Index == "DHPF342" || $this->Index == "DHPF343" || $this->Index == "DHPF344"
            ) {
                $this->SandShieldWidth = $Bh - 144;
                $this->SandShieldHeight = $Hh - $this->resh - 141;
                $this->b_send = 0;
            } else if ($this->Index == "DHPF020" || $this->Index == "DHPF021" || $this->Index == "DHPF030" || $this->Index == "DHPF031" || $this->Index == "DHPF040" || $this->Index == "DHPF041" ||
                $this->Index == "DHPF050" || $this->Index == "DHPF051" || $this->Index == "DHPF060" || $this->Index == "DHPF061" || $this->Index == "DHPF070" || $this->Index == "DHPF071"
            ) {
                $this->SandShieldWidth = $Bh - 143;
                $this->SandShieldHeight = $Hh - $this->resh - 223;
                $this->b_send = 1;
            } else if ($this->Index == "DHPF100" || $this->Index == "DHPF101" || $this->Index == "DHPF105" || $this->Index == "DHPF106" || $this->Index == "DHPF120" || $this->Index == "DHPF121" ||
                $this->Index == "DHPF130" || $this->Index == "DHPF131" || $this->Index == "DHPF140" || $this->Index == "DHPF141" || $this->Index == "DHPF150" || $this->Index == "DHPF151" ||
                $this->Index == "DHPF320" || $this->Index == "DHPF321" || $this->Index == "DHPF330" || $this->Index == "DHPF331" || $this->Index == "DHPF340" || $this->Index == "DHPF341"
            ) {
                $this->SandShieldWidth = $Bh - 143;
                $this->SandShieldHeight = $Hh - $this->resh - 140;
                $this->b_send = 1;
            } else if ($this->Index == "DHPF160" || $this->Index == "DHPF161") {
                $this->SandShieldWidth = $Bh - 143;
                $this->SandShieldHeight = $Hh - $this->resh - $this->n_resh - 244 + 20;
                $this->b_send = 1;
            } else if ($this->Index == "DHPF162" || $this->Index == "DHPF163" || $this->Index == "DHPF164" || $this->Index == "DHPF170" || $this->Index == "DHPF171" || $this->Index == "DHPF172" || $this->Index == "DHPF173") {
                $this->SandShieldWidth = $Bh - 143;
                $this->SandShieldHeight = $Hh - $this->resh - $this->n_resh - 245 + 20;
                $this->b_send = 0;
            } else if ($this->Index == "DHPF180") {
                $this->SandShieldWidth = $Bh - 144;
                $this->SandShieldHeight = $Hh - $this->resh - $this->n_resh_m - 141;
                $this->b_send = 0;
            } else if ($this->Index == "DHPF200" || $this->Index == "DHPF201" || $this->Index == "DHPF210" || $this->Index == "DHPF211" || $this->Index == "DHPF212" || $this->Index == "DHPF213") {
                $this->SandShieldWidth = $Bh - 144;
                $this->SandShieldHeight = $Hh - 92;
                $this->b_send = 0;
            } else if ($this->Index == "DHPF220" || $this->Index == "DHPF221" || $this->Index == "DHPF230" || $this->Index == "DHPF231" || $this->Index == "DHPF240" || $this->Index == "DHPF241" || $this->Index == "DHPF250" || $this->Index == "DHPF251") {
                $this->SandShieldWidth = $Bh - 144;
                $this->SandShieldHeight = $Hh - $this->resh - $this->n_resh - 170;
                $this->b_send = 1;
            } else if ($this->Index == "DHPF222" || $this->Index == "DHPF223" || $this->Index == "DHPF224" || $this->Index == "DHPF232" || $this->Index == "DHPF233" || $this->Index == "DHPF234" || $this->Index == "DHPF242" || $this->Index == "DHPF243" || $this->Index == "DHPF244" ||
                $this->Index == "DHPF252" || $this->Index == "DHPF253" || $this->Index == "DHPF254"
            ) {
                $this->SandShieldWidth = $Bh - 144;
                $this->SandShieldHeight = $Hh - $this->resh - $this->n_resh - 171;
                $this->b_send = 0;
            } else if ($this->Index == "DHPF260") {
                $this->SandShieldWidth = $Bh - 144;
                $this->SandShieldHeight = $Hh - $this->n_resh - 0.2 * $this->n_resh - 141;
                $this->b_send = 0;
            } else if ($this->Index == "DHPF400" || $this->Index == "DHPF420" || $this->Index == "DHPF430" || $this->Index == "DHPF440" || $this->Index == "DHPF450" || $this->Index == "DHPF500" || $this->Index == "DHPF520" || $this->Index == "DHPF530" || $this->Index == "DHPF540") {
                $this->SandShieldWidth = $Bh - 144;
                $this->SandShieldHeight = $Hh - 44;
                $this->b_send = 0;
            } 
            if ($this->SandShieldWidth <= 0 || $this->SandShieldHeight <= 0) {
                $this->errorArray = Yii::t('steps', "Значение ширины или высоты щита меньше или равно 0!");
            }
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
        return 'Форма Опции щита';
    }

}
