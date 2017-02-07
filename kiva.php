<?php

/*
Task 1

With this information create a script (using any language you feel most comfortable in) that queries the API for funded status loans, e.g. http://api.kivaws.org/v1/loans/search.json?status=funded

 

Choose a loan from the list and pull its information, e.g. http://api.kivaws.org/v1/loans/300000.json

 

Then, also pull a list of that loan’s lenders, e.g http://api.kivaws.org/v1/loans/300000/lenders.json

 

There is more background information on the API and its use on build.kiva.org as well as at https://github.com/kiva/API

 
*/

$str = file_get_contents("http://api.kivaws.org/v1/loans/search.json?status=funded");

$obj = json_decode($str);

$loans = $obj->loans;

$lid = '';
foreach($loans as $l)
{
	$lid = $l->id; break;
}

$str2 = file_get_contents("http://api.kivaws.org/v1/loans/".$lid.".json");

$loan = json_decode($str2);

$str3 = file_get_contents(" http://api.kivaws.org/v1/loans/".$lid."/lenders.json");

$lenders = json_decode($str3);

/*
Task 2

 

Use the loan information you gathered above ( e.g:  "loan_amount":100 (in USD), "repayment_term":7 (in months)) to build out a loan repayment schedule into a database table.  

 

Then use the list of lenders and determine an estimated repayment schedule for each one. Create a database schema to hold this information and a script that distributes the repayments equally across the lenders. This creates an audit trail that each lender received back the amount they put into the loan.

 
*/

/*
--
-- Table structure for table `loan_repayment`
--

CREATE TABLE IF NOT EXISTS `loan_repayment` (
  `loan_id` int(11) NOT NULL,
  `lender_id` int(9) NOT NULL,
  `uid` varchar(20) NOT NULL,
  `loan_amount` int(8) NOT NULL,
  `repayment_term` int(2) NOT NULL,
  `created_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

*/

// set loan repayment for each lenader
foreach($loans as $l)
{
	set_repay($l);
}

function set_repay($l)
{
	$str2 = file_get_contents("http://api.kivaws.org/v1/loans/".$l->id.".json");

	$loan = json_decode($str2);

	$str3 = file_get_contents(" http://api.kivaws.org/v1/loans/".$l->id."/lenders.json");

	$ls = json_decode($str3);
	
	$lenders = $ls->loans;
	$total_lenders = count($lenders);
	$each_payment = $loan->loan_amount/$total_lenders; 

	foreach($lenders as $ld)
	{
		// supposed have db obj to process db function
		$db->query("
		INSERT INTO loan_repayment (loan_id,lender_id,uid,loan_amount,repayment_term,created_date) 
		VALUE ('".$loan->id."','".$ld->lender_id."','".$ld->uid."',$each_payment,'".date()."')
		");

		echo 'One record is set into loan_repayment. ';
	} 

}

// unit test

$str = '{"loans":[{"id":1229448,"name":"Gloria","description":{"languages":["en"],"texts":{"en":"Gloria is 60 years old and works hard to help her husband to earn income for the family.<br \/><br \/>She works hard to provide for her family. She runs a vegetables retail business in the Philippines and requested a loan of 20,000 PHP through NWTF to purchase additional vegetables to sell, such as carrots, tomatoes, potatoes, spices, beans, garlic, cabbage, and other vegetables.<br \/><br \/>Gloria has borrowed and repaid 29 loans from NWTF before this loan. She has been running this business for two years and also earns an income from farming rice, buying and selling dried fish, and running a general store.<br \/><br \/>She successfully paid back her previous loan, and now she has requested an additional loan to further build her business.<br \/><br \/>Gloria aspires to save enough to provide a secure future for her family."}},"status":"funded","funded_amount":425,"image":{"id":2136185,"template_id":1},"activity":"Fruits & Vegetables","sector":"Food","use":"To purchase additional vegetables to sell, such as carrots, tomatoes, potatoes, spices, beans, garlic, cabbage, and other vegetables.","location":{"country_code":"PH","country":"Philippines","town":"Narra, Palawan","geo":{"level":"town","pairs":"13 122","type":"point"}},"partner_id":145,"posted_date":"2017-02-07T20:00:04Z","planned_expiration_date":"2017-03-09T20:00:04Z","loan_amount":425,"lender_count":1,"bonus_credit_eligibility":true,"tags":[{"name":"#Woman Owned Biz"},{"name":"#Elderly"},{"name":"#Repeat Borrower"}],"borrowers":[{"first_name":"Gloria","last_name":"","gender":"F","pictured":true}],"terms":{"disbursal_date":"2017-01-20T08:00:00Z","disbursal_currency":"PHP","disbursal_amount":20000,"repayment_term":8,"loan_amount":425,"local_payments":[],"scheduled_payments":[],"loss_liability":{"nonpayment":"lender","currency_exchange":"shared","currency_exchange_coverage_rate":0.1}},"payments":[],"funded_date":"2017-02-07T20:02:02Z","journal_totals":{"entries":0,"bulkEntries":0}}]}';

set_repay(json_decode($str));
