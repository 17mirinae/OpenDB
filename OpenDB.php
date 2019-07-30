<!DOCTYPE html>
<html>
<head>
    <title>Open_DB</title>
    <style>
        body {
            background-image: url('./Paris_Sky.jpg');
            background-size: 100%;
            background-repeat: repeat;
        	}
		* {
			text-align: center;
		}
		th {
			font-style: italic;
		}
    </style>
    <h1 style="color: blue;">Open_DB</h1>
	 <h3 style="color: white;">웹서버 기말 과제</h3>
    <h3 style="color: white;">7301067_정민지</h3><hr>

	<?php		
		$link = mysql_connect("localhost", "chungmj1767", "mschungmj1767M") or die("DB Connection Failed");
		mysql_select_db("chungmj1767", $link);

		$userIp = $_SERVER['REMOTE_ADDR'];
		echo "<font style='color: white;'>접속자의 아이피 : ".$userIp."</font>";

		echo "<table border = '1' align = center><tr style='color: blue; background-color: azure;'>";
			$fieldsInDB = mysql_list_fields("chungmj1767", "open_data", $link);
			$numOfCols = mysql_num_fields($fieldsInDB);

			for($i = 0;$i < $numOfCols;$i++) {
				echo "<th>".mysql_field_name($fieldsInDB, $i)."</th>";			
			}
		echo "</tr></table>";
				
		$query = "SELECT column_name FROM information_schema.columns WHERE table_schema = 'chungmj1767' AND table_name = 'open_data';";
		$res = mysql_query($query, $link);

		$numOfAttr = 0;
		$attrArray = array();

		while($row = mysql_fetch_row($res)) {
			foreach($row as $value) {
				$attrArray[$numOfAttr] = $value;
				$numOfAttr += 1;
			}
		}

		$numOfAttr = 0;
	?>
	<form name='Open_DB_Form' action='Open_DB.php' method='GET'>
		<script>
			var scriptAttrArray = new Array();
			var scriptAttrArray = <?php echo json_encode($attrArray) ?>;
		
			for(i = 0;i < '<?=$numOfCols?>';i++) {
				document.write("<input type='checkbox' name='checkAttr[]' id = 'checkAttr' value ='" + scriptAttrArray[i]+ "'>");
				document.write("<label style = 'color: white;'>" + scriptAttrArray[i] + "  </label>");
			}

			document.write("<br>");
			document.write("<br>");

			document.write("<input type = 'button' onclick = 'checkAll();' value = 'Check All' style = 'margin-right: 5px;' />");
			document.write("<input type = 'button' onclick = 'checkNone();' value = 'Check None' style = 'margin-right: 5px;' />");

			document.write("<input type = 'text' name = 'searchText' style = 'margin-right: 5px;' />");
			document.write("<input type = 'submit' onclick = 'searchData();' value = 'Search' />");

			document.write("<br>");
			document.write("<br>");
		</script>

		<script>
			function checkAll() {
				for(i = 0;i < Open_DB_Form.checkAttr.length;i++)
					Open_DB_Form.checkAttr[i].checked = true;
				alert("All Checked!");
			}

			function checkNone() {
				for(i = 0;i < Open_DB_Form.checkAttr.length;i++)
					Open_DB_Form.checkAttr[i].checked = false;
				alert("All Unchecked!");
			}

			function searchData() {
				var searchAttrArray = new Array();
				var count = 0;

				for(i = 0;i < Open_DB_Form.checkAttr.length;i++) {
					if(Open_DB_Form.checkAttr[i].checked) {
						searchAttrArray[count] = Open_DB_Form.checkAttr[i].value;
						count += 1;
					}
				}
			}
		</script>
	</form>
</head>
<body>
<?php
	$searchText = $_GET['searchText']; // get text from input type text name searchText

	// Print What You Search, Check
	$checkBox = implode(", ", $_GET[checkAttr]);

	echo "<div style = 'margin: 0 auto; background-color: azure; width: 1000px;'><b style = 'color: blue;'>"."당신이 고른 필드는 ".$checkBox."입니다."."</b></div>";
	echo "<br>";

	echo "<div style = 'margin: 0 auto; background-color: azure; width: 1000px;'><b style = 'color: blue;'>"."당신이 입력한 검색어는 ".$searchText."입니다."."<b></div>";
	echo "<br>";

	// Data For Print Data
	$numPrintCheck = count($_GET[checkAttr]); // num of checked boxes
	
	$printCheck = $_GET[checkAttr];

	$arrayForQuery = array($numPrintCheck);

	for($i = 0;$i < $numPrintCheck;$i++) {	
		$arrayForQuery[$i] = $printCheck[$i]." LIKE '%".$searchText."%'";
	}

	$implodeArrayForQuery = implode(" OR ", $arrayForQuery);

	// Print Top 5 Search Content
	$query = "SELECT contentInput FROM content_count";
	$res = mysql_query($query, $link);

	$isExist = false;

	while($row = mysql_fetch_row($res)) {
		foreach($row as $value) {
			if($value == $searchText) {
				$isExist = true;
			}
		}
	}

	$query = "SELECT contentCount FROM content_count WHERE contentInput = '".$searchText."'";
	$res = mysql_query($query, $link);

	while($row = mysql_fetch_row($res)) {
		$count = $row[0];
	}

	$count += 1;

	if($isExist) {
		$query = "UPDATE content_count SET contentCount = '".$count."' WHERE contentInput = '".$searchText."'";
		$res = mysql_query($query, $link) or die("Update Content Failed");
	} else {
		$query = "INSERT INTO content_count(contentInput) VALUES('".$searchText."')";
		$res = mysql_query($query, $link) or die("Update Content Failed");
	}

	$isExist = false;

	$query = "SELECT * FROM content_count ORDER BY contentCount DESC LIMIT 5";
	$res = mysql_query($query, $link);

	echo "<table align = center>";
	echo "<tr><th colspan = 2 style = 'width: 400px; padding: 10px; background-color: blue; color: white;'>Top 5 인기 검색어</th></tr>";
	echo "<tr><th style = 'width: 300px; padding: 10px; background-color: blue; color: white;'>검색어</th><th style = 'width: 100px; padding: 10px; background-color: blue; color: white;'>검색 횟수</th></tr>";
	while($row = mysql_fetch_row($res)) {
		echo "<tr style = 'background-color: white; color: black;'><td>$row[0]</td><td style = 'width: 100px;'>$row[1]</td></tr>";
	}
	echo "</table>";
	echo "<br>";

	// Print & Save User Data
	date_default_timezone_set('Asia/Seoul');
	$date = Date("Y/n/j, H:i:s");

	$query = "SELECT userIp FROM content_search";
	$res = mysql_query($query, $link) or die("No IP Exists");

	$isFollowing = false;
    
    while($row = mysql_fetch_row($res)) {
		foreach($row as $value) {
			if($value == $userIp) {
				$isFollowing = true;
			}
		}
	}
    
    $query = "SELECT count FROM content_search";
    $res = mysql_query($query, $link) or die;
    
    while($row = mysql_fetch_row($res)) {
        $counts = $row[0];
    }
    
    $counts += 1;

	if($isFollowing && ($searchText != '') && ($checkBox != '')) {
		$query = "UPDATE content_search SET contentInput='".$searchText."', checkBoxChecked='".$checkBox."', date='".$date."', count='".$counts."' WHERE userIp = '".$userIp."'";
		$res = mysql_query($query, $link);
	} else if ((!$isFollowing) && ($searchText != '') && ($checkBox != '')) {
		$query = "INSERT INTO content_search(userIp, contentInput, checkBoxChecked, date) VALUES('".$userIp."', '".$searchText."', '".$checkBox."', '".$date."')";
		$res = mysql_query($query, $link);
	}

	$isFollowing = false;

	$query = "SELECT * FROM content_search ORDER BY date DESC LIMIT 5";
	$res = mysql_query($query, $link);

	echo "<table align = center>";
	echo "<tr><th colspan = 5 style = 'width: 210px; padding: 10px; background-color: blue; color: white;'>최근 검색 (5명)</th></tr>";
	echo "<tr><th style = 'width: 150px; padding: 10px; background-color: blue; color: white;'>방문자 아이피</th><th style = 'width: 150px; padding: 10px; background-color: blue; color: white;'>검색어</th><th style = 'width: 600px; padding: 10px; background-color: blue; color: white;'>선택한 검색 필드</th><th style = 'width: 100px; padding: 10px; background-color: blue; color: white;'>검색 시간</th><th style = 'width: 80px; padding: 10px; background-color: blue; color: white;'>조회수</th></tr>";
	while($row = mysql_fetch_row($res)) {
		echo "<tr style = 'background-color: white; color: black;'><td>$row[0]</td><td>$row[1]</td><td>$row[2]</td><td>$row[3]</td><td>$row[4]</td></tr>";
	}
	echo "</table>";
	echo "<br><br>";

	// Start Print Data
	$query = "SELECT ".$checkBox." FROM open_data WHERE ".$implodeArrayForQuery.";";
	$res = mysql_query($query, $link) or die("Select Any CheckBox Please");

	// Print Data Structure
	echo "<table align = center><tr style = 'width: 210px; background-color: blue; color: white;'>";
	echo "<th colspan = ".$numPrintCheck." style = 'padding: 10px;'>데이터 결과</th></tr><tr style = 'background-color: blue; color: white;'>";
	for($i = 0;$i < $numPrintCheck;$i++) {
		echo "<th style = 'padding: 10px;'>".$printCheck[$i]."</th>";
	}
	echo "</tr>";
	
	$countData = 0;

	// Print Data
	while($row = mysql_fetch_row($res)) {
		echo "<tr style = 'background-color: white; color: black;'>";
		foreach($row as $value) echo "<td>".$value."</td>";
		echo "</tr>";
		$countData += 1;
	}

	echo "<tr style = 'background-color: azure; color: blue;'><td colspan = ".$numPrintCheck." style = 'padding: 5px;'>찾은 데이터 개수 : 총 ".$countData."개</td></tr>";
	echo "<tr style = 'background-color: azure; color: blue;'><td colspan = ".$numPrintCheck." style = 'padding: 5px;'>End Of Search</td></tr>";
	echo "</table>";

	echo "<br><br><br><br>";
?>
</body>
</html>