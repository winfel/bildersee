<!DOCTYPE html>
<html>
<head>
<title>DJK Rheda Termine</title>
<style>
* {font-family:arial, sans-serif; font-size:13px}
</style>
</head>
<body>
<?php

if (file_exists('djkcalendar.temp')){
	$age=filemtime('djkcalendar.temp');
	if (time()-$age > 10*60) unlink('djkcalendar.temp');
}

if (file_exists('djkcalendar.temp')){
	echo (file_get_contents('djkcalendar.temp'));
} else {
	
	
	//webcal://www.facebook.com/ical/u.php?uid=1453718869&key=AQB4_so-QduHW-Qr
	$file=file_get_contents('http://www.facebook.com/ical/u.php?uid=1453718869&key=AQB4_so-QduHW-Qr');
	
	$events=explode('BEGIN:VEVENT',$file);
	
	
	//var_dump($events);
	
	$begin=$events[0];
	
	$out='';
	
	foreach ($events as $event){
		if (stripos($event,'djk')) {
			$data=array();
			foreach(explode("\n",$event) as $rawdata){
			   $rawdata=explode(':',$rawdata);
			   $data[$rawdata[0]]=trim($rawdata[1]);
			}
			$time=$data['DTSTART'];
			$year=substr($time,0,4);
			$month=substr($time,4,2);
			$day=substr($time,6,2);
			$hour=substr($time,9,2);
			$minutes=substr($time,11,2);
			
			$desc=$data['DESCRIPTION'];
			$desc=explode('\n',$desc);
			$desc=$desc[0];
			
			if ($desc) $desc="($desc)";
			
			$location=$data['LOCATION'];
			if ($location) $location=", $location";

			$time=gmmktime ($hour, $minute, 0, $month, $day, $year,0);
			$time=date("d.m.Y G:i",$time);
			$out.= "<p>".$time.$location.':<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$data['SUMMARY']." ".$desc."</p>";
		}
	}
	
	file_put_contents('djkcalendar.temp',$out);
	echo ($out);
}

?>
</body>
</html>