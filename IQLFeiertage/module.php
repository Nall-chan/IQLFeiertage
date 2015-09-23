<?

class IQLFeiertage extends IPSModule
{

    public function Create()
    {
        //Never delete this line!
        parent::Create();
        //These lines are parsed on Symcon Startup or Instance creation
        //You cannot use variables here. Just static values.
        $this->RegisterPropertyString("area", "NI");
    }

    public function ApplyChanges()
    {
        //Never delete this line!
        parent::ApplyChanges();

        $this->RegisterVariableBoolean("IsHoliday", "Is Holiday");
        $this->RegisterVariableString("Holiday", "Feiertag");
        // 15 Minuten Timer
        $this->RegisterTimer("UpdateIQLFeiertage", 15*60, 'IQLFT_Update($_IPS[\'TARGET\']);');        
        // Nach übernahme der Einstellungen oder IPS-Neustart einmal Update durchführen.
        $this->Update();
        //$this->RegisterEventCyclic("UpdateTimer", "Automatische aktualisierung", 15);
    }

    private function GetFeiertag()
    {
        $datum = date("Y-m-d", time());
        $bundesland = $this->ReadPropertyString("area");

        $bundesland = strtoupper($bundesland);
        if (is_object($datum))
        {
            $datum = date("Y-m-d", $datum);
        }
        $datum = explode("-", $datum);

        $datum[1] = str_pad($datum[1], 2, "0", STR_PAD_LEFT);
        $datum[2] = str_pad($datum[2], 2, "0", STR_PAD_LEFT);

        if (!checkdate($datum[1], $datum[2], $datum[0]))
            return false;

        $datum_arr = getdate(mktime(0, 0, 0, $datum[1], $datum[2], $datum[0]));

        $easter_d = date("d", easter_date($datum[0]));
        $easter_m = date("m", easter_date($datum[0]));

        $status = 'Arbeitstag';
        if ($datum_arr['wday'] == 0 || $datum_arr['wday'] == 6)
            $status = 'Wochenende';

        if ($datum[1] . $datum[2] == '0101')
        {
            $status = 'Neujahr';
        }
        elseif ($datum[1] . $datum[2] == '0106' && ($bundesland == 'BW' || $bundesland == 'BY' || $bundesland == 'ST'))
        {
            $status = 'Heilige Drei Könige';
        }
        elseif ($datum[1] . $datum[2] == date("md", mktime(0, 0, 0, $easter_m, $easter_d - 2, $datum[0])))
        {
            $status = 'Karfreitag';
        }
        elseif ($datum[1] . $datum[2] == $easter_m . $easter_d)
        {
            $status = 'Ostersonntag';
        }
        elseif ($datum[1] . $datum[2] == date("md", mktime(0, 0, 0, $easter_m, $easter_d + 1, $datum[0])))
        {
            $status = 'Ostermontag';
        }
        elseif ($datum[1] . $datum[2] == '0501')
        {
            $status = 'Erster Mai';
        }
        elseif ($datum[1] . $datum[2] == date("md", mktime(0, 0, 0, $easter_m, $easter_d + 39, $datum[0])))
        {
            $status = 'Christi Himmelfahrt';
        }
        elseif ($datum[1] . $datum[2] == date("md", mktime(0, 0, 0, $easter_m, $easter_d + 49, $datum[0])))
        {
            $status = 'Pfingstsonntag';
        }
        elseif ($datum[1] . $datum[2] == date("md", mktime(0, 0, 0, $easter_m, $easter_d + 50, $datum[0])))
        {
            $status = 'Pfingstmontag';
        }
        elseif ($datum[1] . $datum[2] == date("md", mktime(0, 0, 0, $easter_m, $easter_d + 60, $datum[0])) && ($bundesland == 'BW' || $bundesland == 'BY' || $bundesland == 'HE' || $bundesland == 'NW' || $bundesland == 'RP' || $bundesland == 'SL' || $bundesland == 'SN' || $bundesland == 'TH'))
        {
            $status = 'Fronleichnam';
        }
        elseif ($datum[1] . $datum[2] == '0815' && ($bundesland == 'SL' || $bundesland == 'BY'))
        {
            $status = 'Mariä Himmelfahrt';
        }
        elseif ($datum[1] . $datum[2] == '1003')
        {
            $status = 'Tag der deutschen Einheit';
        }
        elseif ($datum[1] . $datum[2] == '1031' && ($bundesland == 'BB' || $bundesland == 'MV' || $bundesland == 'SN' || $bundesland == 'ST' || $bundesland == 'TH'))
        {
            $status = 'Reformationstag';
        }
        elseif ($datum[1] . $datum[2] == '1101' && ($bundesland == 'BW' || $bundesland == 'BY' || $bundesland == 'NW' || $bundesland == 'RP' || $bundesland == 'SL'))
        {
            $status = 'Allerheiligen';
        }
        elseif ($datum[1] . $datum[2] == strtotime("-11 days", strtotime("1 sunday", mktime(0, 0, 0, 11, 26, $datum[0]))) && $bundesland == 'SN')
        {
            $status = 'Buß- und Bettag';
        }
        elseif ($datum[1] . $datum[2] == '1224')
        {
            $status = 'Heiliger Abend (Bankfeiertag)';
        }
        elseif ($datum[1] . $datum[2] == '1225')
        {
            $status = '1. Weihnachtsfeiertag';
        }
        elseif ($datum[1] . $datum[2] == '1226')
        {
            $status = '2. Weihnachtsfeiertag';
        }
        elseif ($datum[1] . $datum[2] == '1231')
        {
            $status = 'Silvester (Bankfeiertag)';
        }
        //else
        //{
        //    return $status;
        //}
        return $status;
    }

    public function Update()
    {
        $holiday = $this->GetFeiertag();
        if (!IPS_GetObject($this->GetIDForIdent("IsHoliday"))['ObjectIsHidden'])
        {
            IPS_SetHidden($this->GetIDForIdent("IsHoliday"), true);
        }
        $this->SetValueString("Holiday", $holiday);
        if ($holiday != "Arbeitstag" and $holiday != "Wochenende")
        {
            $this->SetValueBoolean("IsHoliday", true);
        }
        else
        {
            $this->SetValueBoolean("IsHoliday", false);
        }
    }

    protected function RegisterTimer($Name, $Interval, $Script)
    {
        $id = @IPS_GetObjectIDByIdent($Name, $this->InstanceID);
        if ($id === false)
        {
            $id = IPS_CreateEvent(1);
            IPS_SetParent($id, $this->InstanceID);
            IPS_SetIdent($id, $Name);
        }
        IPS_SetName($id, $Name);
        IPS_SetHidden($id, true);
        IPS_SetEventScript($id, $Script);
        if ($Interval > 0)
        {
            IPS_SetEventCyclic($id, 0, 0, 0, 0, 1, $Interval);

            IPS_SetEventActive($id, true);
        }
        else
        {
            IPS_SetEventCyclic($id, 0, 0, 0, 0, 1, 1);

            IPS_SetEventActive($id, false);
        }
    }

    protected function SetTimerInterval($Name, $Interval)
    {
        $id = @IPS_GetObjectIDByIdent($Name, $this->InstanceID);
        if ($id === false)
            throw new Exception('Timer not present');
        $Event = IPS_GetEvent($id);

        if ($Interval < 1)
        {
            if ($Event['EventActive'])
                IPS_SetEventActive($id, false);
        }
        else
        {
            if ($Event['CyclicTimeValue'] <> $Interval)
                IPS_SetEventCyclic($id, 0, 0, 0, 0, 1, $Interval);
            if (!$Event['EventActive'])
                IPS_SetEventActive($id, true);
        }
    }
    private function SetValueBoolean($Ident, $value)
    {
        $id = $this->GetIDForIdent($Ident);
        if (GetValueBoolean($id) <> $value)
            SetValueBoolean($id, $value);
    }

    private function SetValueString($Ident, $value)
    {
        $id = $this->GetIDForIdent($Ident);
        if (GetValueString($id) <> $value)
            SetValueString($id, $value);
    }
}

?>