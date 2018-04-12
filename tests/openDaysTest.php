<?php
	include('lib/openDays.php');
	
	date_default_timezone_set('Europe/Paris');
	
	use PHPUnit\Framework\TestCase;
	class OpenDaysTest extends TestCase
	{
		public function testOpenDaysBasic()
		{
			$this->assertSame(getWorkedTimeBetween(strtotime('2018-04-10'), strtotime('2018-04-09')),false);
			$this->assertSame(getWorkedTimeBetween(strtotime('2018-04-10 00:00:00'), strtotime('2018-04-10 23:59:59')),'8:0:0');
			$this->assertSame(getWorkedTimeBetween(strtotime('2018-04-10 12:00:00'), strtotime('2018-04-10 23:59:59')),'4:0:0');
			$this->assertSame(getWorkedTimeBetween(strtotime('2018-04-10 16:00:00'), strtotime('2018-04-10 23:59:59')),'2:0:0');
			$this->assertSame(getWorkedTimeBetween(strtotime('2018-04-10 10:00:00'), strtotime('2018-04-10 11:00:00')),'1:0:0');
			$this->assertSame(getWorkedTimeBetween(strtotime('2018-04-10 00:00:00'), strtotime('2018-04-11 23:59:59')),'16:0:0');
		}
		
		public function testOpenDaysOneDay()
		{
			$this->assertSame(getWorkedTimeBetween(strtotime('2018-04-10 06:00:00'), strtotime('2018-04-10 07:00:00')),'0:0:0');
			$this->assertSame(getWorkedTimeBetween(strtotime('2018-04-10 06:00:00'), strtotime('2018-04-10 09:00:00')),'1:0:0');
			$this->assertSame(getWorkedTimeBetween(strtotime('2018-04-10 09:00:00'), strtotime('2018-04-10 11:00:00')),'2:0:0');
			$this->assertSame(getWorkedTimeBetween(strtotime('2018-04-10 11:00:00'), strtotime('2018-04-10 13:00:00')),'1:0:0');
			$this->assertSame(getWorkedTimeBetween(strtotime('2018-04-10 12:00:00'), strtotime('2018-04-10 14:00:00')),'0:0:0');
			$this->assertSame(getWorkedTimeBetween(strtotime('2018-04-10 7:00:00'), strtotime('2018-04-10 16:00:00')),'6:0:0');
		}
		
		public function testOpenDaysDst()
		{
			// No effect because during a weekend
			$this->assertSame(getWorkedTimeBetween(strtotime('2018-03-24 07:00:00'), strtotime('2018-03-25 20:00:00')),'0:0:0');
			$this->assertSame(getWorkedTimeBetween(strtotime('2018-03-23 07:00:00'), strtotime('2018-03-26 20:00:00')),'15:0:0');
			$this->assertSame(getWorkedTimeBetween(strtotime('2017-10-28 07:00:00'), strtotime('2017-10-29 20:00:00')),'0:0:0');
			$this->assertSame(getWorkedTimeBetween(strtotime('2017-10-27 07:00:00'), strtotime('2017-10-30 20:00:00')),'15:0:0');
			
			
			// Working hours during changing DST (need to put working hours during the weekend change)
			$workingHours = array(0=>array(array('1:00','9:00'),array('14:00','18:00')),1=>array(array('8:00','12:00'),array('14:00','18:00')),2=>array(array('8:00','12:00'),array('14:00','18:00')),3 =>array(array('8:00','12:00'),array('14:00','18:00')),4=>array(array('8:00','12:00'),array('14:00','18:00')), 5 => array( array('8:00','12:00'),array('14:00','17:00')),6=>array(array('8:00','12:00'),array('14:00','18:00')));
			
			$this->assertSame(getWorkedTimeBetween(strtotime('2018-03-24 07:00:00'), strtotime('2018-03-25 20:00:00'), $workingHours),'19:0:0');
			$this->assertSame(getWorkedTimeBetween(strtotime('2017-10-28 07:00:00'), strtotime('2017-10-29 20:00:00'), $workingHours),'21:0:0');
		}
		
		public function testClosedDays()
		{
			$this->assertSame(getWorkedTimeBetween(strtotime('2017-12-22 00:00:00'), strtotime('2017-12-26 23:59:59')),'15:0:0');
		}
	}
?>