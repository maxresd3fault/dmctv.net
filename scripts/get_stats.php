<?php
	header('Content-Type: application/json');
	include('config.php');
	
	$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
	$limit = 30;
	$offset = ($page - 1) * $limit;
	
	$conn = new mysqli(DB_IP, DB_USERNAME, DB_PASSWORD, DB_NAME);
	if ($conn->connect_error) {
		http_response_code(500);
		echo json_encode(['error' => 'Database connection failed']);
		exit;
	}
	
	$filter = $_GET['filter'] ?? 'Total KDR';
	$whichWeapon = $_GET['whichWeapon'] ?? 'rocketlauncher_kills';
	$search = $_GET['search'] ?? '';
	$columns = [
		'Total KDR' => '(kills + bot_kills) / NULLIF(deaths + bot_deaths, 0) DESC',
		'Username' => 'last_username ASC',
		'SteamID' => 'steamid ASC',
		'Total Kills' => '(kills + bot_kills) DESC',
		'Time Played' => 'time_played DESC'
	];
	$orderBy = $columns[$filter] ?? $columns['Total KDR'];
	
	$whereClause = '';
	if ($search !== '') {
		$search = $conn->real_escape_string($search);
		$search = preg_replace('/^STEAM_/', '', $search);
		$whereClause = "WHERE SUBSTRING(steamid, 7) LIKE '%$search%' OR last_username LIKE '%$search%'";
	}
	
	$countSql = "SELECT COUNT(*) AS total FROM player_stats $whereClause";
	$countResult = $conn->query($countSql);
	$totalRows = $countResult ? $countResult->fetch_assoc()['total'] : 0;
	$totalPages = ceil($totalRows / $limit);
	
	$sql = "
	SELECT steamid, last_username, time_played, kills, bot_kills, deaths, bot_deaths,
	$whichWeapon
	FROM player_stats
	$whereClause
	ORDER BY $orderBy
	LIMIT $limit OFFSET $offset
";
	
	$result = $conn->query($sql);
	$players = [];
	
	if ($result && $result->num_rows > 0) {
		while ($row = $result->fetch_assoc()) {
			$total_deaths = $row['deaths'] + $row['bot_deaths'];
			$total_kills = $row['kills'] + $row['bot_kills'];
			$weaponKills = $row[$whichWeapon];
			
			// KDR calculations
			if ($row['kills'] == 0 && $row['deaths'] == 0) {
				$player_kdr = '-.--'; // No KDR data
			} elseif ($row['kills'] == 0 && $row['deaths'] > 0) {
				$player_kdr = '0.00'; // 0 kills and > 0 deaths
			} elseif ($row['deaths'] == 0) {
				$player_kdr = '∞'; // 0 deaths and >0 kills
			} else {
				$player_kdr = number_format($row['kills'] / $row['deaths'], 2, '.', '');
			}
			
			if ($row['bot_kills'] == 0 && $row['bot_deaths'] == 0) {
				$bot_kdr = '-.--';
			} elseif ($row['bot_kills'] == 0 && $row['bot_deaths'] > 0) {
				$bot_kdr = '0.00';
			} elseif ($row['bot_deaths'] == 0) {
				$bot_kdr = '∞';
			} else {
				$bot_kdr = number_format($row['bot_kills'] / $row['bot_deaths'], 2, '.', '');
			}

			if ($total_kills == 0 && $total_deaths == 0) {
				$total_kdr = '-.--';
			} elseif ($total_kills == 0 && $total_deaths > 0) {
				$total_kdr = '0.00';
			} elseif ($total_deaths == 0) {
				$total_kdr = '∞';
			} else {
				$total_kdr = number_format($total_kills / $total_deaths, 2, '.', '');
			}
			
			$minutes = (int) round($row['time_played'] / 60);
			$hours = intdiv($minutes, 60);
			$remaining_minutes = $minutes % 60;
			$formatted_time = "{$hours}h {$remaining_minutes}m";
			
			$players[] = [
				'steamid' => $row['steamid'],
				'last_username' => $row['last_username'],
				'time_played' => $formatted_time,
				'total_kdr' => $total_kdr,
				'player_kdr' => $player_kdr,
				'bot_kdr' => $bot_kdr,
				'kills' => $total_kills,
				'deaths' => $total_deaths,
				'weapon_kills' => $weaponKills
			];
		}
	}
	
	$conn->close();
	
	echo json_encode([
		'players' => $players,
		'total_pages' => $totalPages,
		'current_page' => $page
	]);
	
