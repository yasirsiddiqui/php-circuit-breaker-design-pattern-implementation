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

include_once 'CircuitBreakerClass.php';

echo "Mysql Circuit Breaker";
try {
	$cb = new CircutBreaker("mysql");
	
	// Check circuit is closed?
	if($cb->isCircuitClose()) {
		
		echo "<br>Mysql Circuit is close so we can continue our operation";
		
		$mysqli = @ new mysqli("localhost", "username", "password", "databasename");
		
		if ($mysqli->connect_errno) {
			
			echo "<br>Mysql Connection to server failed. Please try again later";
			//Tell circuit breaker about failure
			$cb->serviceFailed();
		}
		else {
			
			//Tell circuit breaker about success
			$cb->serviceSuccess();
		}	
	}
	else {
		
		echo "We are having problems please try again later";
	}
}
catch (Exception $e) {
	
	echo "Exception: ".$e->getMessage();
}

echo "<br>===============================================================";
echo "<br> Twitter Circuit Breaker";

try {
	
	$twcb = new CircutBreaker("twittersearch");
	
	// check circut is closed?
	if($twcb->isCircuitClose()) {
	
		echo "<br>Twitter Circuit is close so we can continue our operation";
		
		$twitterurl = 'http://search.twitter.com/search.json?q=birthday';
		$response = file_get_contents($twitterurl);
		if($response == false) {
			
			echo "<br>No response from Twitter. Please try agian later!";
			//Tell circuit breaker about failure
			$twcb->serviceFailed();
		}
		else {
			
			// decode response from twitter
			$jsondata = @json_decode($response);

			if($jsondata == false) {
				
				echo "<br>Parsing error. Twitter search failed Please try agian later!";
				//Tell circuit breaker about failure
				$twcb->serviceFailed();
			}
			else {
				
				echo "<br>Got response from twitter Hurrayyyyyy";
				//Tell circuit breaker about success
				$twcb->serviceSuccess();	
			}
			
		}
		
	}
	else {
	
		echo "We are having problems please try again later";
	}
	
}
catch (Exception $e) {
	
	echo "Exception: ".$e->getMessage();
}