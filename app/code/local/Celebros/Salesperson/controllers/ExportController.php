<?php
/**
 * Celebros Qwiser - Magento Extension
 *
 * @category    Celebros
 * @package     Celebros_Salesperson
 * @author (this controller only)	3BaseGroup Co. - Miri Meltzer & Shulamit Tal (email: mirim@3basegroup.com)
 *
 */
class Celebros_Salesperson_ExportController extends Mage_Core_Controller_Front_Action
{

	public function export_celebrosAction()
	{
		$model =Mage::getModel('salesperson/observer');
		$model->export_celebros();
  	}
  	
	public function schedule_exportAction() {
		
		$SCHEDULE_EVERY_MINUTES = 30;
	
	    //Flooring the minutes
	    $startTimeSeconds = ((int)(time()/60))*60;
	    //Ceiling to the next 5 minutes
	    $startTimeMinutes = $startTimeSeconds/60;
	    $startTimeMinutes = ((int)($startTimeMinutes/5))*5 + 5;
	    $startTimeSeconds = $startTimeMinutes * 60;
	    
	    $bAddedFromXml = false;
	    $config = Mage::getConfig()->getNode('crontab/jobs');
        if ($config instanceof Mage_Core_Model_Config_Element) {
        	$jobs = $config->children();
        	
        	$i = 0;
			foreach ($jobs as $jobCode => $jobConfig) {
				if(strpos($jobCode, 'salesperson') === false) continue;
				$timecreated   = strftime('%Y-%m-%d %H:%M:%S', time());
				$timescheduled = strftime('%Y-%m-%d %H:%M:%S', $startTimeSeconds + $i*60*$SCHEDULE_EVERY_MINUTES);
				
				try {
					$schedule = Mage::getModel('cron/schedule');
					$schedule->setJobCode($jobCode)
					->setCreatedAt($timecreated)
					->setScheduledAt($timescheduled)
					->setStatus(Mage_Cron_Model_Schedule::STATUS_PENDING)
					->save();
					echo "{$jobCode} cron job is scheduled at $timescheduled <br/>";
				
				} catch (Exception $e) {
					throw new Exception(Mage::helper('cron')->__('Unable to schedule Cron'));
				}				
			
				$bAddedFromXml = true;
				$i++;
			}
        }
        
        if(!$bAddedFromXml) {
	        $config = Mage::getConfig()->getNode('default/crontab/jobs');
	        if ($config instanceof Mage_Core_Model_Config_Element) {
	            $jobs = $config->children();
	        	$i = 0;
				foreach ($jobs as $jobCode => $jobConfig) {
					if(strpos($jobCode, 'salesperson') === false) continue;
					$timecreated   = strftime('%Y-%m-%d %H:%M:%S', time());
					$timescheduled = strftime('%Y-%m-%d %H:%M:%S', $startTimeSeconds + $i*60*$SCHEDULE_EVERY_MINUTES);
					
					try {
						$schedule = Mage::getModel('cron/schedule');
						$schedule->setJobCode($jobCode)
						->setCreatedAt($timecreated)
						->setScheduledAt($timescheduled)
						->setStatus(Mage_Cron_Model_Schedule::STATUS_PENDING)
						->save();
						echo "{$jobCode} cron job is scheduled at $timescheduled <br/>";
					
					} catch (Exception $e) {
						throw new Exception(Mage::helper('cron')->__('Unable to schedule Cron'));
					}				

					$i++;
				}
	        } 
        } 	
	}
}

?>
