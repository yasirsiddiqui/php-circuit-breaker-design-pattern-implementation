<?php
/**
 * PHP implementation of Circuit breaker design pattern
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

date_default_timezone_set('Asia/Karachi');
include_once 'Config.php';

Class CircutBreaker {
	
	private $servicesconfig;
	private $servicesettings;
	private $arrayindex;
	protected $servicename;
	
	/**
	 * Function __construct
	 *
	 * @param Service name $servicename   // Provided service should be there in Config.php
	 * 
	 */
	public function  __construct($servicename) {
		
		global $services;
		
		$this->arrayindex = 0;
		$this->servicename = $servicename;
		$this->servicesconfig = $services;
		
		// Checke either service is configured or not
		if(!$this->serviceExsists()) {
			
			// If not configured then throw exception
			throw new Exception("Please first configure the service in Config.php");
			exit;
		}
		else { // Service is configured so check if service settings are there in APC. If not then add service settings to APC
			
			// We have to update the settings from config so that if user changes any setting in config they are updated in the class
			$settings = $this->getServiceSettings();
			
			// Update settings from config
			$settings['servicename'] = $this->servicesconfig[$this->arrayindex]['servicename'];
			$settings['maxfailures'] = $this->servicesconfig[$this->arrayindex]['maxfailures'];
			$settings['timeout'] 	 = $this->servicesconfig[$this->arrayindex]['timeout'];
			// Update values in APC
			$this->setServiceSettings($settings);
			
			// Fetch updated values from APC
			$settings = $this->getServiceSettings();
			if($settings==false) {  // As service settings are not there in APC so add service settings to APC
								
				// Set default settings for the service
				$settings = array('servicename' => $this->servicesconfig[$this->arrayindex]['servicename'],'maxfailures'=> $this->servicesconfig[$this->arrayindex]['maxfailures'],
				'timeout' => $this->servicesconfig[$this->arrayindex]['timeout'],'failurecount'=> 0,'circutstate'=>'close','lastopentime'=>date("Y-m-d H:i:s"));
				$this->servicesettings = $settings;
				$this->setServiceSettings($settings); 
			}
			else {
				
				$this->servicesettings = $settings;
			}
		}	
	}
	
	/**
	 * Function isCircuitClose
	 *
	 * Checks either ciruit for the service is closed or not
	 *
	 */
	public function isCircuitClose() {
	
		// If circuit is close then return true
		if($this->servicesettings['circutstate']=='close') {
			
			return true;
		}
		else { // As circuit is open so first we need to check either service time out has passed. If not passed then return false
			   // and if time out has passed then close the circuit
			$timepassed = $this->calculateTimeDifference(date("Y-m-d H:i:s"), $this->servicesettings['lastopentime']);	
			
			// Time out has reached so close the circuit again to give it one more chance
			if($timepassed>$this->servicesettings['timeout']) {
				
				// Also update service settings and save to APC
				$this->servicesettings['failurecount'] = $this->servicesettings['maxfailures']-1;
				$this->servicesettings['circutstate']  = 'close';
				$this->setServiceSettings($this->servicesettings);
				return true;
			}
			else { // As time out has not reached so keep circuit open
				
				return false;
			}
			
		}
	}
	
	/**
	 * Function serviceFailed
	 *
	 * Reports that service has failed
	 *
	 */
	public function serviceFailed() {
		
		
		// Check if max failures for the service have been reached
		if($this->servicesettings['failurecount']>=$this->servicesettings['maxfailures']) {
			
			// If max failures reached then open the circuit and update fields
			$this->servicesettings['lastopentime'] = date("Y-m-d H:i:s");
			$this->servicesettings['circutstate']  = 'open';
			$this->setServiceSettings($this->servicesettings);
		}
		else { // Although service has failed but max failure count has not reached
			
			// Increase failure count and save settings
			$this->servicesettings['failurecount'] = $this->servicesettings['failurecount']+1;
			$this->setServiceSettings($this->servicesettings);
		}
	} 
	
	/**
	 * Function serviceSuccess
	 *
	 * Reports service has response
	 *
	 */
	public function serviceSuccess() {
		
		// As service succeeded so reset failure count and close circuit
		$this->servicesettings['failurecount'] = 0;
		$this->servicesettings['circutstate']  = 'close';
		$this->setServiceSettings($this->servicesettings);
	}
	
	/**
	 * Function calculateTimeDifference
	 *
	 * Calculates difference in seconds between two dates
	 *
	 */
	private function calculateTimeDifference($date1,$date2) {
		 
		$seconds = strtotime($date1) - strtotime($date2);
		echo "<br> Service: ".$this->servicename." Time passed:".$seconds."<br>";
		return $seconds;
	}
	
	/**
	 * Function serviceExsists
	 *
	 * Checks either service is configured in config.php or not
	 *
	 */
	private function serviceExsists() {
		$index = 0;
		foreach ($this->servicesconfig as $config) {
			
			if($config['servicename']===$this->servicename) {
				// Service is configured so return true
				$this->arrayindex = $index;
				return true;
				break;
			}
			$index++;
		}
		// Service is not configutred so return false
		return false;	
	}
	
	/**
	 * Function getServiceSettings
	 *
	 * Gets service settings from APC
	 *
	 */
	private function getServiceSettings() {
		
		// Check APC is configured, if not then throw exception
		if( !function_exists("apc_fetch")) {
			
			throw new Exception("Please configure APC extension and try again");
			exit;	
		}
		// Fetch service settings
		$settings = apc_fetch($this->servicename);
		return $settings;
	}
	
	/**
	 * Function getServiceSettings
	 *
	 * Sets service settings to APC
	 *
	 */
	private function setServiceSettings($settings) {
		
		// Check APC is configured, if not then throw exception
		if( !function_exists("apc_store")) {
		
			throw new Exception("Please configure APC extension and try again");
			exit;
		}
		// Store settings array to APC
		apc_store($this->servicename,$settings);
	}
	
}