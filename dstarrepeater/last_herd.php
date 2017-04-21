<?php include_once $_SERVER['DOCUMENT_ROOT'].'/config/ircddblocal.php';
$configs = array();

if ($configfile = fopen($gatewayConfigPath,'r')) {
        while ($line = fgets($configfile)) {
                list($key,$value) = split("=",$line);
                $value = trim(str_replace('"','',$value));
                if ($key != 'ircddbPassword' && strlen($value) > 0)
                $configs[$key] = $value;
        }

}
$progname = basename($_SERVER['SCRIPT_FILENAME'],".php");
$rev="20141101";
$MYCALL=strtoupper($callsign);
?>
    <b>Last 20 calls heard via this Gateway</b>
    <table>
    <tr>
    <th><a class=tooltip href="#">Date &amp; Time (<?php echo date('T')?>)</a></th>
    <th><a class=tooltip href="#">Call</a></th>
    <th><a class=tooltip href="#">Your Call</a></th>
    <th><a class=tooltip href="#">Repeater 1</a></th>
    <th><a class=tooltip href="#">Repeater 2</a></th>
    </tr>
<?php
// Headers.log sample:
// 0000000001111111111222222222233333333334444444444555555555566666666667777777777888888888899999999990000000000111111111122
// 1234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901
// 2012-06-05 12:18:41: DCS header - My: PU2ZHZ  /T     Your: CQCQCQ    Rpt1: PU2ZHZ B  Rpt2: DCS007 B  Flags: 00 00 00
// 2012-05-29 21:33:56: DPlus header - My: PD1RB   /IC92  Your: CQCQCQ    Rpt1: PE1RJV B  Rpt2: REF017 A  Flags: 00 00 00
// 2013-02-09 13:49:57: DExtra header - My: DO7MT   /      Your: CQCQCQ    Rpt1: XRF001 G  Rpt2: XRF001 C  Flags: 00 00 00
//
    exec('(grep -v "  /TIME" '.$hdrLogPath.'|sort -r -k7,7|sort -u -k7,8|sort -r|head -20 >/tmp/lastheard.log) 2>&1 &');
    $ci = 0;
    if ($LastHeardLog = fopen("/tmp/lastheard.log",'r')) {
	while ($linkLine = fgets($LastHeardLog)) {
            if(preg_match_all('/^(.{19}).*My: (.*).*Your: (.*).*Rpt1: (.*).*Rpt2: (.*).*Flags: (.*)$/',$linkLine,$linx) > 0){
		$ci++;
		if($ci > 1) { $ci = 0; }
		print "<tr>";
                $QSODate = date("d-M-Y H:i:s", strtotime(substr($linx[1][0],0,19)));
                $MyCall = str_replace(' ', '', substr($linx[2][0],0,8));
                $MyId = str_replace(' ', '', substr($linx[2][0],9,4));
                $YourCall = str_replace(' ', '&nbsp;', substr($linx[3][0],0,8));
                $Rpt1 = str_replace(' ', '&nbsp;', substr($linx[4][0],0,8));
                $Rpt2 = str_replace(' ', '&nbsp;', substr($linx[5][0],0,8));
		    $utc_time = $QSODate;
                    $utc_tz =  new DateTimeZone('UTC');
                    $local_tz = new DateTimeZone(date_default_timezone_get ());
                    $dt = new DateTime($utc_time, $utc_tz);
                    $dt->setTimeZone($local_tz);
                    $local_time = $dt->format('Y-m-d H:i:s');
		print "<td align=left>$local_time</td>";
		print "<td align=left width=180><a href=\"http://www.qrz.com/db/$MyCall\" target=\"_blank\" alt=\"Lookup Callsign\">$MyCall</a>";
		if($MyId) { print "/".$MyId."</td>"; } else { print "</td>"; }
                print "<td align=left width=100>$YourCall</td>";
		print "<td align=left width=100>$Rpt1</td>";
		print "<td align=left width=100>$Rpt2</td>";
		print "</tr>\n";
	    }
	}
	fclose($LastHeardLog);
    }
?>
</tr>
</table>
