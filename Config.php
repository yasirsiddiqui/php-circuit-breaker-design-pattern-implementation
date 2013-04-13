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

/**
 * Array key "servicename" is the name of service you want to track. Use this same name when creating object of the CircuitBreaker Class
 * 
 * Array key "maxfailures" is the maximum number of failer attempts after which circut breaker will report circuit as open
 * 
 * Array key "timeout" is the time in seconds after which circuit breaker should close circuit again to give service one more chance
 */ 

$mysqlserver = array('servicename' => 'mysql','maxfailures' => 3, 'timeout' => 40); 
$twitter 	 = array('servicename' => 'twittersearch','maxfailures' => 5, 'timeout' => 20);

$services = array($mysqlserver,$twitter);
?>