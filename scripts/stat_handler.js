document.addEventListener("DOMContentLoaded", function () {
	const table = document.querySelector(".main-content table");
	const searchInput = document.getElementById("player-search");
	const searchBtn = document.getElementById("search");
	const filterSelect = document.querySelector("select[name='filter']");
	const showSteamIDToggle = document.querySelector("input[name='show-steamid']");
	const refreshBtn = document.getElementById("refresh");
	const weaponSelect = document.querySelector("select[name='weapon-select']");
	
	const weaponAbbreviations = {
		axe_kills: "CB Kills",
		shotgun_kills: "SG Kills",
		doubleshotgun_kills: "SS Kills",
		nailgun_kills: "NG Kills",
		supernail_kills: "SN Kills",
		grenadelauncher_kills: "GL Kills",
		rocketlauncher_kills: "RL Kills",
		lightninggun_kills: "LG Kills"
	};
	
	let showSteamID = false;
	let currentPage = 1;
	let totalPages = 1;
	
	const paginationDiv = document.createElement("div");
	paginationDiv.style.marginTop = "4px";
	paginationDiv.style.textAlign = "center";
	
	const prevBtn = document.createElement("a");
	prevBtn.href = "#";
	prevBtn.textContent = "<--";
	prevBtn.style.margin = "0 10px";
	prevBtn.style.textDecoration = "none";
	prevBtn.style.color = "#ffbd9b";
	prevBtn.style.userSelect = "none";
	prevBtn.style.fontWeight = "bold";
	prevBtn.style.fontSize = "18px";
	
	const nextBtn = document.createElement("a");
	nextBtn.href = "#";
	nextBtn.textContent = "-->";
	nextBtn.style.margin = "0 10px";
	nextBtn.style.textDecoration = "none";
	nextBtn.style.color = "#ffbd9b";
	nextBtn.style.userSelect = "none";
	nextBtn.style.fontWeight = "bold";
	nextBtn.style.fontSize = "18px";
	
	function setLinkDisabled(link, disabled) {
		if (disabled) {
			link.style.pointerEvents = "none";
			link.style.color = "#aaa";
		} else {
			link.style.pointerEvents = "auto";
			link.style.color = "#007bff";
		}
	}
	
	const pageIndicator = document.createElement("span");
	pageIndicator.style.margin = "0 15px";
	
	paginationDiv.appendChild(prevBtn);
	paginationDiv.appendChild(pageIndicator);
	paginationDiv.appendChild(nextBtn);
	
	table.parentNode.appendChild(paginationDiv);
	
	function updatePaginationControls() {
		pageIndicator.textContent = `Page ${currentPage} of ${totalPages}`;
		prevBtn.disabled = currentPage <= 1;
		nextBtn.disabled = currentPage >= totalPages;
	}
	
	function fetchStats(page = 1) {
		const search = searchInput.value.trim();
		const filter = filterSelect.value;
		const weapon = weaponSelect.value;
		const url = `/scripts/get_stats.php?search=${encodeURIComponent(search)}&filter=${encodeURIComponent(filter)}&whichWeapon=${encodeURIComponent(weapon)}&page=${page}`;
		
		fetch(url)
			.then(response => response.json())
			.then(data => {
				table.querySelectorAll("tr:not(:first-child)").forEach(row => row.remove());

				if (!data.players || data.players.length === 0) {
					const tr = document.createElement("tr");
					const td = document.createElement("td");
					td.colSpan = 8;
					td.style.textAlign = "center";
					td.textContent = "No data.";
					tr.appendChild(td);
					table.appendChild(tr);
					totalPages = 1;
					currentPage = 1;
					updatePaginationControls();
					return;
				}

				data.players.forEach(player => {
					const tr = document.createElement("tr");
					tr.innerHTML = `
						<td>${showSteamID ? player.steamid : player.last_username}</td>
						<td>${player.time_played}</td>
						<td>${player.total_kdr}</td>
						<td>${player.bot_kdr}</td>
						<td>${player.player_kdr}</td>
						<td>${player.kills}</td>
						<td>${player.deaths}</td>
						<td>${player.weapon_kills}</td>
					`;
					table.appendChild(tr);
				});
				
				totalPages = data.total_pages || 1;
				currentPage = data.current_page || 1;
				updatePaginationControls();
			})
			.catch(err => {
				console.error("Error fetching stats:", err);
			});
	}
	
	searchBtn.addEventListener("click", () => {
		currentPage = 1;
		fetchStats(currentPage);
	});
	refreshBtn.addEventListener("click", () => {
		currentPage = 1;
		fetchStats(currentPage);
		const target = document.getElementById('blink-me');
		target.classList.remove('blink');
		void target.offsetWidth;
		target.classList.add('blink');
	});
	filterSelect.addEventListener("change", () => {
		currentPage = 1;
		fetchStats(currentPage);
	});
	weaponSelect.addEventListener("change", () => {
		const weaponHeader = table.querySelector("th#selected-weapon");
		const selectedWeapon = weaponSelect.value;
		weaponHeader.textContent = weaponAbbreviations[selectedWeapon];
		
		currentPage = 1;
		fetchStats(currentPage);
	});
	showSteamIDToggle.addEventListener("change", function () {
		showSteamID = this.checked;
		const playerHeader = table.querySelector("th#player-id");
		playerHeader.textContent = showSteamID ? "SteamID" : "Username";
		if (showSteamID) {
			const usernameOption = [...filterSelect.options].find(opt => opt.value === "Username");
			if (usernameOption) {
				usernameOption.value = "SteamID";
				usernameOption.textContent = "SteamID";
			}
		} else {
			const usernameOption = [...filterSelect.options].find(opt => opt.value === "SteamID");
			if (usernameOption && usernameOption.value === "SteamID") {
				usernameOption.value = "Username";
				usernameOption.textContent = "Username";
			}
		}
		fetchStats(currentPage);
	});
	prevBtn.addEventListener("click", () => {
		if (currentPage > 1) {
			currentPage--;
			fetchStats(currentPage);
		}
	});
	nextBtn.addEventListener("click", () => {
		if (currentPage < totalPages) {
			currentPage++;
			fetchStats(currentPage);
		}
	});
	
	fetchStats(currentPage);
});
