<?php
	/*
		This script is addressed by the OSMP system (By a QIWI terminal).
		
		It is responsible for checking whether all the information is precise or not.
		There are two possible types of the query. It might be query for checking the state of an order. The second possible query is for accepting the payment. The former one always comes before the latter.
		I.e. in the CHECK query the accuracy of an order will be checked:
			- Is there such an order in the database?
			- Is it active?
			- What is the sum of this order?
			- and so on...
		If everything is all right with the order, then the answer to the OSMP system will be formed by answerInXML function (functions.php). (Code 0 means, that everything is OK: the order exists and is active.)
		
		PAY query comes after CHECK respectively. It will mark the order as paid and change its state to inactive.
	*/
	
	include("./functions.php");

	$command = $_GET["command"];
	$txn_id = $_GET["txn_id"];
	$account = $_GET["account"];
	$sum = $_GET["sum"];
	$txn_date = $_GET["txn_date"];
	
	if(empty($command) or empty($txn_id) or empty($account) or empty($sum)) {
		answerInXML("check",$txn_id,"",0.00,"300","Error! No GET parameters...",0.00);
		exit();
	}

	include("./dbConnect.php");
		
	$result = mysql_fetch_array(mysql_query("SELECT COUNT(`id`) AS `adExist`,`moderated`,`orderIsClosed`,`userEmail`,`amount`,`alreadyPaid` FROM `ads` WHERE `paymentCode` = $account;"));
	$userEmail = $result["userEmail"];
	
	$toBePaid = $result["amount"] - $result["alreadyPaid"];
	$newAlreadyPaid = $result["alreadyPaid"] + $sum;
	
	if($command == "check") {
		if($result['adExist'] == 1) {
			if($result['moderated'] == 1 && $result['orderIsClosed'] == 0) {
				answerInXML("check",$txn_id,"",0.00,"0","OK! The order exists and is active.",$toBePaid);
			}
			else {
				answerInXML("check",$txn_id,"",0.00,"5","Error! The order is not active...",0.00);
			}
		}
		else {
			answerInXML("check",$txn_id,"",0.00,"5","Error! Wrong order number...",0.00);
		}
	}
	else if($command == "pay") {
		$existsOrNot = mysql_fetch_array(mysql_query("SELECT COUNT(`id`) AS `Exist`, `id`, `sum` FROM `payments` WHERE `txn_id` = $txn_id;"));
		if($existsOrNot['Exist'] != 0) {
			
			$paymentID = $existsOrNot[`id`];
			$paymentSum = $existsOrNot['sum'];
			answerInXML("pay",$txn_id,"$paymentID",$paymentSum,"0","OK! Payment already exists! The transaction was successfully finished.",0.00);			
		}
		else if($existsOrNot['Exist'] == 0) {
						
			mysql_query("INSERT INTO `payments`(`paymentCode`,`txn_id`,`sum`,`txn_date`) VALUES('$account', '$txn_id', $sum, '$txn_date');") or die(mysql_error());
			$paymentID = mysql_fetch_array(mysql_query("SELECT MAX(`id`) AS `highestPaymentId` FROM `payments`;"));
			$paymentID = $paymentID['highestPaymentId'];
			
			if($toBePaid <= $sum) {
				mysql_query("UPDATE `ads` SET `alreadyPaid` = $newAlreadyPaid, `orderIsClosed` = 1 WHERE `paymentCode` = $account;");
				
				answerInXML("pay",$txn_id,"$paymentID",$sum,"0","OK! The transaction was successfully finished.",0.00);
			}
			else {
				mysql_query("UPDATE `ads` SET `alreadyPaid` = $newAlreadyPaid WHERE `paymentCode` = $account;");
				
				answerInXML("pay",$txn_id,"$paymentID",$sum,"0","OK! The transaction was successfully finished.",0.00);
			}
		}
	}
	mysql_close();
?>