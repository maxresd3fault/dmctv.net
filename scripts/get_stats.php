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
		$whereClause = "WHERE steamid LIKE '%$search%' OR last_username LIKE '%$search%'";
	}
	
	$countSql = "SELECT COUNT(*) AS total FROM player_stats $whereClause";
	$countResult = $conn->query($countSql);
	$totalRows = $countResult ? $countResult->fetch_assoc()['total'] : 0;
	$totalPages = ceil($totalRows / $limit);
	
	$sql = "
	SELECT steamid, last_username, time_played, kills, bot_kills, deaths, bot_deaths,
	axe_kills, shotgun_kills, doubleshotgun_kills, nailgun_kills,
	supernail_kills, grenadelauncher_kills, rocketlauncher_kills, lightninggun_kills
	FROM player_stats
	$whereClause
	ORDER BY $orderBy
	LIMIT $limit OFFSET $offset
";
	
	$result = $conn->query($sql);
	$players = [];
	
	if ($result && $result->num_rows > 0) {
		while ($row = $result->fetch_assoc()) {
			$player_kdr = $row['deaths'] == 0 ? ($row['kills'] > 0 ? '∞' : '0.00') : round($row['kills'] / $row['deaths'], 2);
			$bot_kdr = $row['bot_deaths'] == 0 ? ($row['bot_kills'] > 0 ? '∞' : '0.00') : round($row['bot_kills'] / $row['bot_deaths'], 2);
			$total_deaths = $row['deaths'] + $row['bot_deaths'];
			$total_kills = $row['kills'] + $row['bot_kills'];
			if ($total_deaths == 0) {
				$total_kdr = $total_kills > 0 ? '∞' : '0.00';
			} else {
				$total_kdr = round($total_kills / $total_deaths, 2);
			}
			
			$weapons = [
				'axe_kills', 'shotgun_kills', 'doubleshotgun_kills',
				'nailgun_kills', 'supernailgun_kills',
				'grenadelauncher_kills', 'rocketlauncher_kills', 'lightninggun_kills'
			];
			
			$weaponNames = [
				'axe_kills' => 'Crowbar',
				'shotgun_kills' => 'Shotgun',
				'doubleshotgun_kills' => 'Super Shotgun',
				'nailgun_kills' => 'Nailgun',
				'supernailgun_kills' => 'Super Nailgun',
				'grenadelauncher_kills' => 'Grenade Launcher',
				'rocketlauncher_kills' => 'Rocket Launcher',
				'lightninggun_kills' => 'Lightning Gun'
			];
			
			$maxKills = 0;
			$favorite_weapon = 'None';
			
			foreach ($weapons as $weapon) {
				if ((int)$row[$weapon] > $maxKills) {
					$maxKills = (int)$row[$weapon];
					$favorite_weapon = $weaponNames[$weapon];
				}
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
				'favorite_weapon' => $favorite_weapon
			];
		}
	}
	
	$conn->close();
	
	echo json_encode([
		'players' => $players,
		'total_pages' => $totalPages,
		'current_page' => $page
	]);
