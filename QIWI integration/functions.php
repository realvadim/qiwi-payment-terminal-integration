<?php
	/* 
		1) This function is responsible for the answer to the QIWI terminals. It is evoked from 
		checkTransaction.php script.
		
		2) The answer should be provided in XML format. Encoding is UTF-8.
	*/ 
	function answerInXML($_type, $_txn_id, $_prv_txn, $_sum, $_result, $_comment, $_toBePaid) {
		$answer = "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
		
		$answer .= "<response>";
		
		$answer .= "<osmp_txn_id>$_txn_id</osmp_txn_id>";
		
		if($_type == "pay" && $_result == "0") {
		
			$answer .= "<sum>$_sum</sum>";
		}
		
		$answer .= "<result>$_result</result>";
		$answer .= "<comment>$_comment</comment>";
		
		if($_type == "check" && $_result == "0") {
			
			$answer .= "<fields>";
			$answer .= "<field1 name=\"name1\">Сумма к оплате $_toBePaid.00 тенге</field1>";
			$answer .= "<field2 name=\"name2\">$_toBePaid.00</field2>";
			$answer .= "</fields>";
		}
		
		$answer .= "</response>";
		
		header("Content-Type: text/xml; charset=UTF-8");
		echo $answer;
	}
?>