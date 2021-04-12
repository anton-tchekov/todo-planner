<?php
	function day_diff($date0, $date1)
	{
		return (new DateTime($date0))->diff(new DateTime($date1))->days;
	}

	date_default_timezone_set('Europe/Berlin');

	$format = 'd.m.Y';
	$start_time = $st = strtotime('monday this week');
	$start_date = date('Y-m-d', $start_time);
	$end_time = strtotime($start_date . "+4 weeks");

	$weeks = array();
	$date = new DateTime();
	$num_weeks = 0;
	while($st < $end_time)
	{
		$date->setISODate(date('Y', $st), date('W', $st));
		$monday = $date->format($format);
		$weeks[$num_weeks] = $monday . ' - ' . date($format, strtotime($monday . "+6 days"));
		$st += strtotime('+1 week', 0);
		++$num_weeks;
	}

	$today = day_diff($start_date, date('Y-m-d', time()));
	$assignments = array();
	$descrs = array();
	$sql = 'SELECT assignments.date, assignments.name, assignments.description, subject.sname FROM assignments JOIN subject ON assignments.sid = subject.sid WHERE assignments.date BETWEEN ? AND ?';

	$mysqli = new mysqli('localhost', 'dbuser', 'dbpwd', 'db');
	if($mysqli->connect_error)
	{
		echo $mysqli->connect_error;
		exit(1);
	}

	if(!$mysqli->set_charset('utf8'))
	{
		echo 'Error setting MySQLi charset to UTF-8';
		exit(1);
	}

	$stmt = $mysqli->prepare($sql);
	if(!$stmt)
	{
		echo 'ERROR';
		exit(1);
	}

	$stmt->bind_param('ss', $start_date, date('Y-m-d', $end_time));
	$stmt->execute();
	$stmt->bind_result($date, $name, $desc, $subject);
	$id = 0;
	while($stmt->fetch())
	{
		$index = day_diff($start_date, $date);
		$assignments[$index] .= "<li><a href=\"#\" onclick=\"show_desc(this, $id)\">$subject ($name)</a></li>";
		$descrs[$id] = $desc;
		++$id;
	}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Abgabetermine der Aufgaben</title>
<link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
<div class="nav">
	<span class="title">Übersicht</span>
</div>
<div id="content">
	<table>
		<thead>
			<th>Montag</th>
			<th>Dienstag</th>
			<th>Mittwoch</th>
			<th>Donnerstag</th>
			<th>Freitag</th>
			<th>Samstag</th>
			<th>Sonntag</th>
		</thead>
		<tbody>
		<?php
			$day = 0;
			for($i = 0; $i < $num_weeks; ++$i)
			{
				echo
					"<tr>\n" .
					"<td colspan=\"7\" class=\"date\">\n" .
						$weeks[$i] .
					"</td>\n" .
					"</tr>\n" .
					"<tr>\n";

				for($j = 0; $j < 7; ++$j)
				{
					if($day == $today)
					{
						echo "<td class=\"cell mark\">";
					}
					else
					{
						echo "<td class=\"cell\">";
					}

					echo "<ul>" . $assignments[$day] . "</ul>";
					if($day < $today)
					{
						echo "<span class=\"x\"></span>\n";
					}

					echo "</td>\n";
					++$day;
				}

				echo "</tr>\n";
			}
		?>
		</tbody>
	</table>
</div>
<div id="overlay">
<div class="nav">
	<span class="title" id="title"></span>
	<a href="#" class="navlink" onclick="back()">Schließen</a>
</div>
<div id="desc"></div>
</div>
<?php
for($i = 0; $i < $id; ++$i)
{
	echo "<div class=\"hidden\" id=\"desc$i\">" . $descrs[$i] . "</div>\n";
}
?>
<script type="text/javascript">
<!--
	var overlay = document.getElementById("overlay");
	var text = document.getElementById("desc");
	var title = document.getElementById("title");
	function show_desc(elem, id)
	{
		title.innerText = elem.innerText;
		text.innerHTML = document.getElementById("desc" + id).innerHTML;
		overlay.style.display = "block";
	}

	function back()
	{
		overlay.style.display = "none";
	}
-->
</script>
</body>
</html>
