<?php
function getWorkedTimeBetween(
	$start,
	$end,
	$workingHours = array(
		0 => array(
			// Sunday
		),
		1 => array(
			// Monday
			array('8:00','12:00'),
			array('14:00', '18:00')
		),
		2 => array(
			// Tuesday
			array('8:00','12:00'),
			array('14:00', '18:00')
		),
		3 => array(
			// Wednesday
			array('8:00','12:00'),
			array('14:00', '18:00')
		),
		4 => array(
			// Thursday
			array('8:00','12:00'),
			array('14:00', '18:00')
		),
		5 => array(
			// Friday
			array('8:00','12:00'),
			array('14:00', '17:00')
		),
		6 => array(
			// Saturday
		),

	)
)
{
	$result = false;

	$debug = false;

	if($debug) echo '<hr />';

	if($debug) echo date('r', $start).' (DST : '.date('I', $start).') >> '.date('r', $end).' (DST : '.date('I', $end).')<br /><br />';

	// In case of incoherent values
	if($start > $end) return $result;

	// Start from total diffTime between begin and end
	$diffTime = $end - $start;
	$totalWorkTime = 0;

	// Usefull calculations
	$secondsMinutes = 60;
	$secondsHour = 60 * $secondsMinutes;
	$secondsDay = 24 * $secondsHour;
	$secondsWeek = 7 * $secondsDay;
	$secondsYear = 365 * $secondsWeek;

	// Check if there is several years in the interval
	$diffYear = date('Y', $start) - date('Y', $end);
	
	// Daylight saving time during a working periode
	$daylightSavingTime = false;

	// Loop in case of start year is different from end year
	for ($i = 0; $i <= $diffYear; $i++)
	{
		$year = (int) date('Y', $start) + $i;

		// Unworked days (France)
		$holidays[] = '1_1_'.$year; // Jour de l'an
		$holidays[] = '1_5_'.$year; // Fete du travail
		$holidays[] = '8_5_'.$year; // Victoire 1945
		$holidays[] = '14_7_'.$year; // Fete nationale
		$holidays[] = '15_8_'.$year; // Assomption
		$holidays[] = '1_11_'.$year; // Toussaint
		$holidays[] = '11_11_'.$year; // Armistice 1918
		$holidays[] = '25_12_'.$year; // Noël

		// Get easter date to obtain next days : Ascenssion and Pentecôte
		$easter = easter_date($year);
		$holidays[] = date('j_n_'.$year, $easter + 86400); // Paques
		$holidays[] = date('j_n_'.$year, $easter + (86400*39)); // Ascenssion
		$holidays[] = date('j_n_'.$year, $easter + (86400*50)); // Pentecôte

		// Loop on each day in the periode
		$current = $start;

		if($end - $start <= $secondsDay)
		{
			if($debug) echo date('Y-m-d', $current).' - unique day';

			if(!in_array(date('j_n_'.date('Y', $current), $current), $holidays))
			{
				$workedTime = 0;

				// Get timestamp of 0:00:00 on the current day
				$currentDay = strtotime(date('Y-m-d', $current).' 00:00:00');

				foreach($workingHours[date('w',$current)] as $dayPeriodes)
				{
					$workedTime = 0;

					// Parsing the working hours of the current day
					$beginPeriode = explode(':',$dayPeriodes[0]);
					$endPeriode = explode(':',$dayPeriodes[1]);

					//if($debug) echo ' - ['.$beginPeriode[0].':'.$beginPeriode[1].' >> '.$endPeriode[0].':'.$endPeriode[1].']';

					// Convert working hours into timestamps
					$beginTime = $currentDay + $beginPeriode[0] * $secondsHour + $beginPeriode[1] * $secondsMinutes;
					$endTime = $currentDay + $endPeriode[0] * $secondsHour + $endPeriode[1] * $secondsMinutes;
					
					// Check if daylight saving time happens during the periode
					if(date('I', $beginTime) != date('I', $endTime)) $daylightSavingTime = true;

					if($debug) echo ' - ['.date('H:i:s',$beginTime).' >> '.date('H:i:s',$endTime).']';

					if ($start <= $beginTime && $beginTime <= $end && $end <= $endTime)
					{
						$workedTime += $end - $beginTime; // Left straddling
						if($debug) echo ' - '.date('H:i:s',$workedTime).' (left) ';
					}
					else if ($beginTime <= $start && $end <= $endTime)
						{
							$workedTime += $end - $start; // Inside stradling
							if($debug) echo ' - '.date('H:i:s',$workedTime).' (inside) ';
						}
					else if ($beginTime <= $start && $start <= $endTime && $endTime <= $end)
						{
							$workedTime += $endTime - $start; // Right straddling
							if($debug) echo ' - '.date('H:i:s',$workedTime).' (right) ';
						}
					else if ($start <= $beginTime && $endTime <= $end)
						{
							$workedTime += $endTime - $beginTime; // Over straddling
							if($debug) echo ' - '.date('H:i:s',$workedTime).' (over) ';
						}
					
					$totalWorkTime += $workedTime;
				}
			}
			else
				if($debug) echo ' - unworked day<br />';
		}
		else
		{
			$j = 0;
			while($current < $end)
			{
				// Check closed days
				if(!in_array(date('j_n_'.date('Y', $current), $current), $holidays))
				{
					if($debug) echo date('Y-m-d', $current);

					// Dissociate partial starting and ending day
					if($current == $start)
					{
						// Partial starting day

						$workedTime = 0;

						// Get timestamp of 0:00:00 on the current day
						$currentDay = strtotime(date('Y-m-d', $current).' 00:00:00');


						foreach($workingHours[date('w',$current)] as $dayPeriodes)
						{
							// Parsing the working hours of the current day
							$beginPeriode = explode(':',$dayPeriodes[0]);
							$endPeriode = explode(':',$dayPeriodes[1]);

							if($debug) echo ' - ['.$beginPeriode[0].':'.$beginPeriode[1].' >> '.$endPeriode[0].':'.$endPeriode[1].']';

							// Convert working hours into timestamps
							$beginTime = $currentDay + $beginPeriode[0] * $secondsHour + $beginPeriode[1] * $secondsMinutes;
							$endTime = $currentDay + $endPeriode[0] * $secondsHour + $endPeriode[1] * $secondsMinutes;

							// Check if daylight saving time happens during the periode
							if(date('I', $beginTime) != date('I', $endTime)) $daylightSavingTime = true;
							
							if($current <= $beginTime && $endTime <= $end)
							{
								// Taking the whole periode
								$workedTime += $endTime - $beginTime;
							}
							else if ($endTime <= $end)
								{
									// Taking the remaining time of the current periode
									$workedTime += $endTime - $current;
								}
						}
						$totalWorkTime += $workedTime;
						if($debug) echo ' - '.date('H:i:s',$workedTime).' (begin day)';
					}
					else if (($end - $current) < $secondsDay)
						{
							// Partial ending day

							$workedTime = 0;

							// Get timestamp of 00:00:00 on the end day
							$currentDay = strtotime(date('Y-m-d', $end).' 00:00:00');

							foreach($workingHours[date('w',$end)] as $dayPeriodes)
							{
								// Parsing the working hours of the current day
								$beginPeriode = explode(':',$dayPeriodes[0]);
								$endPeriode = explode(':',$dayPeriodes[1]);

								if($debug) echo ' - ['.$beginPeriode[0].':'.$beginPeriode[1].' >> '.$endPeriode[0].':'.$endPeriode[1].']';

								// Convert working hours into timestamps
								$beginTime = $currentDay + $beginPeriode[0] * $secondsHour + $beginPeriode[1] * $secondsMinutes;
								$endTime = $currentDay + $endPeriode[0] * $secondsHour + $endPeriode[1] * $secondsMinutes;

								// Check if daylight saving time happens during the periode
								if(date('I', $beginTime) != date('I', $endTime)) $daylightSavingTime = true;
								
								if($end >= $endTime)
								{
									// Taking the whole periode
									$workedTime += $endTime - $beginTime;
									if($debug) echo ' - ['.$beginPeriode[0].':'.$beginPeriode[1].' >> '.$endPeriode[0].':'.$endPeriode[1].']';
								}
								else
								{
									// Taking the remaining time of the current periode
									$workedTime += $end - $beginTime;
								}

							}
							$totalWorkTime += $workedTime;
							if($debug) echo ' - '.date('H:i:s',$workedTime).' (end day)';
						}
					else
					{
						// Calculate total working time for the current day (all the periodes defined in the table)

						$workedTime = 0;
						foreach($workingHours[date('w',$current)] as $dayPeriodes)
						{
							// Parsing the working hours of the current day
							$beginPeriode = explode(':',$dayPeriodes[0]);
							$endPeriode = explode(':',$dayPeriodes[1]);

							if($debug) echo ' - ['.$beginPeriode[0].':'.$beginPeriode[1].' >> '.$endPeriode[0].':'.$endPeriode[1].']';

							// Convert working hours into timestamps
							$beginTime = $current + $beginPeriode[0] * $secondsHour + $beginPeriode[1] * $secondsMinutes;
							$endTime = $current + $endPeriode[0] * $secondsHour + $endPeriode[1] * $secondsMinutes;
							
							// Check if daylight saving time happens during the periode
							if(date('I', $beginTime) != date('I', $endTime)) $daylightSavingTime = true;

							// Differentiate timestamps to obtain worktime (duration) in seconds
							$workedTime += $endTime - $beginTime;
						}
						$totalWorkTime += $workedTime;
						if($debug) echo ' - '.date('H:i:s',$workedTime).' ';
					}
				}
				else
					if($debug) echo date('Y-m-d', $current).' - unworked day';

					$current = $current + $secondsDay;
				$j++;

				if($debug) echo '<br />';
			}
		}

		if($debug) echo '<br /><br />';
	}

	//if($debug) echo "<br />Total loops : ".$j.'<br />';

	// Handle daylight saving time
	if ($daylightSavingTime)
	{
		$dst = (int) date('I', $start) - (int) date('I', $end);
		

		// Specific case when start and end are over a daytime saving hour change with no working periode between
		if($totalWorkTime <= 0)
			$totalWorkTime = 0;
		else
			$totalWorkTime += $dst * $secondsHour;

		if($debug) echo '<br />DST : '.($dst * $secondsHour);
	}

	if($debug) echo '<br />Total time : '.$totalWorkTime.'<br />';


	// Conversion to H:m:s

	$hours = abs(floor($totalWorkTime / 3600));
	$minutes = abs(floor(($totalWorkTime / 60) % 60));
	$seconds = abs($totalWorkTime % 60);



	return $hours.':'.$minutes.':'.$seconds;
}

?>