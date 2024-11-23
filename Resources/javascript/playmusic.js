let currentPlaylist = [];
let currentIndex = -1;
let currentSong = null;
let isPlaying = false;
let currentPlaylistId = null;

const floatingControls = {
    playPauseBtn: document.getElementById("play-pause-button"),
    prevBtn: document.getElementById("prev-button"),
    nextBtn: document.getElementById("next-button"),
    nowPlaying: document.getElementById("current-song-title")
};

// Function to play a single song
function playSingleSong(audioId, playlistId) {
    // Pause the current song
    if (currentSong) currentSong.pause();

    // Update the current song
    currentSong = document.getElementById(audioId);

    if (playlistId) {
        // If a playlist is provided, set the playlist context
        currentPlaylist = Array.from(document.querySelectorAll(`#${playlistId} audio`));
        currentIndex = currentPlaylist.findIndex(song => song.id === audioId);
        currentPlaylistId = playlistId;
    } else {
        // If no playlist, reset the playlist context for single-song playback
        currentPlaylist = [currentSong];
        currentIndex = 0;
        currentPlaylistId = null;
    }

    // Update floating controls
    updateFloatingWindow(currentSong);

    // Play the selected song
    currentSong.play();
    isPlaying = true;

    // Set up the onended listener to handle single song or playlist playback
    currentSong.onended = function () {
        if (currentPlaylistId && currentIndex < currentPlaylist.length - 1) {
            playNext(); // Play the next song in the playlist
        } else {
            isPlaying = false; // End single-song playback
            floatingControls.playPauseBtn.textContent = "▶ Play";
            updateFloatingWindow(currentSong); // Maintain the now-playing title
        }
    };
}

// Function to play a playlist
function playPlaylist(playlistId) {
    // Initialize playlist
    currentPlaylist = Array.from(document.querySelectorAll(`#${playlistId} audio`));
    currentPlaylistId = playlistId;
    currentIndex = 0;

    // Play first song in playlist
    if (currentPlaylist.length > 0) {
        playSingleSong(currentPlaylist[currentIndex].id, playlistId);
    }
}

function updateFloatingWindow(song) {
    const songTitle = song.closest("li")?.dataset.title || "Unknown Song";
    floatingControls.nowPlaying.textContent = `Now Playing: ${songTitle}`;

    if (currentPlaylistId) {
        // Enable "Next" and "Previous" for playlists
        floatingControls.prevBtn.disabled = currentIndex <= 0;
        floatingControls.nextBtn.disabled = currentIndex >= currentPlaylist.length - 1;
    } else {
        // Disable "Next" and "Previous" for single-song playback
        floatingControls.prevBtn.disabled = true;
        floatingControls.nextBtn.disabled = true;
    }

    // Update play/pause button
    floatingControls.playPauseBtn.textContent = isPlaying ? "⏸ Pause" : "▶ Play";
}


// Function to play/pause the current song
function togglePlayPause() {
    if (currentSong) {
        if (currentSong.paused) {
            currentSong.play();
            floatingControls.playPauseBtn.textContent = "⏸ Pause";
        } else {
            currentSong.pause();
            floatingControls.playPauseBtn.textContent = "▶ Play";
        }
        isPlaying = !currentSong.paused;
    }
}

function playNext() {
    if (currentPlaylistId && currentIndex < currentPlaylist.length - 1) {
        currentIndex++;
        playSingleSong(currentPlaylist[currentIndex].id, currentPlaylistId);
    } else {
        console.log("No next song available.");
    }
}

function playPrevious() {
    if (currentPlaylistId && currentIndex > 0) {
        currentIndex--;
        playSingleSong(currentPlaylist[currentIndex].id, currentPlaylistId);
    } else {
        console.log("No previous song available.");
    }
}


// Initialize controls
floatingControls.playPauseBtn.addEventListener('click', togglePlayPause);
floatingControls.prevBtn.addEventListener('click', playPrevious);
floatingControls.nextBtn.addEventListener('click', playNext);

// Handle end of a song
document.addEventListener("ended", function (e) {
    if (e.target === currentSong) {
        playNext();
    }
}, true);

//function to handle adding a song to the playlist
function addToPlaylist(songId) {
    // Send the song ID to PHP via AJAX
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "musicplayer.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function() {
        if (xhr.readyState == 4 && xhr.status == 200) {
            alert(xhr.responseText); // Show success or failure message
        }
    };
    xhr.send("addToPlaylist=true&song_id=" + songId);
}