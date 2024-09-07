var audioFiles = [
   "/sound/r_tele1.wav",
   "/sound/r_tele2.wav",
   "/sound/r_tele3.wav",
   "/sound/r_tele4.wav",
   "/sound/r_tele5.wav"
];

var audio = document.getElementById("auto-play");

function getRandomAudio() {
   var randomIndex = Math.floor(Math.random() * audioFiles.length);
   audio.src = audioFiles[randomIndex];
}

window.addEventListener('load', function () {
   getRandomAudio()
   audio.play();
});
