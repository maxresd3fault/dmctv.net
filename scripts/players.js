let players = 0;

fetch('/scripts/fetch_players.php')
	.then(response => {
		if (!response.ok) {
			throw new Error('Fetch PHP error');
		}
		return response.json();
	})
	.then(data => {
		players = data.response.player_count;
		updateCounter();
	})
	.catch(error => {
		console.error(error);
	});

function updateCounter() {
	let oldelem = document.querySelector("div#players");
	let newelem = document.createElement("div");
	newelem.id = oldelem.id;
	newelem.className = oldelem.className;
	newelem.innerHTML = "<p><strong>" + players + "</strong></p>";
	oldelem.parentNode.replaceChild(newelem, oldelem);
}
