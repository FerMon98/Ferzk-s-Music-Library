// Function to slugify text for HTML id attributes
function slugify(text) {
    if (!text) {
        console.error('Text is null or undefined:', text);
        return ''; // Return an empty string if the text is invalid
    }
    return text.toString()
        .toLowerCase()
        .replace(/\s+/g, '-') // Replace spaces with -
        .replace(/[^\w\-]+/g, '') // Remove all non-word chars
        .replace(/\-\-+/g, '-') // Replace multiple - with single -
        .replace(/^-+/, '') // Trim - from start of text
        .replace(/-+$/, ''); // Trim - from end of text
}

// Function to toggle play/pause for an audio element
function togglePlay(audioId) {
    const audioElement = document.getElementById(audioId);

    if (!audioElement) {
        console.error(`Audio element with ID '${audioId}' not found.`);
        return;
    }

    if (audioElement.paused) {
        // Pause any other playing audio
        const allAudios = document.querySelectorAll("audio");
        allAudios.forEach(audio => {
            if (!audio.paused) {
                audio.pause();
            }
        });
        audioElement.play();
    } else {
        audioElement.pause();
    }
}

let currentPlaylist = [];
let currentIndex = -1; // Track the current song index
let isPlaying = false; // Track play/pause state
let currentPlaylistId = null; // Track the current playlist ID

const prevButton = document.getElementById('prev-button');
const playPauseButton = document.getElementById('play-pause-button');
const nextButton = document.getElementById('next-button');
const nowPlaying = document.getElementById('current-song-title');

// Function to update the "Now Playing" text
function updateNowPlaying(title) {
    nowPlaying.textContent = title ? `Now Playing: ${title}` : 'No song playing';
}

// Play a song by index in the current playlist
function playSongByIndex(index) {
    if (currentPlaylist.length === 0 || index < 0 || index >= currentPlaylist.length) {
        console.error('No song to play.');
        return;
    }

    // Pause any currently playing audio
    document.querySelectorAll('audio').forEach(audio => {
        audio.pause();
        audio.currentTime = 0; // Reset time to 0
    });

    // Get the current audio element
    const audioElement = currentPlaylist[index];

    // Play the selected audio
    audioElement.play();
    isPlaying = true;

    // Update current index and now playing info
    currentIndex = index; // Update the currentIndex to the selected song
    const title = audioElement.parentElement.querySelector('a').textContent;
    updateNowPlaying(title); // Update the "Now Playing" text

    // Update button text
    playPauseButton.textContent = '⏸ Pause';

    // Set up the onended listener to play the next song
    audioElement.onended = function () {
        playNext(); // Automatically play the next song
    };
}

// Pause the currently playing song
function pauseCurrentSong() {
    if (currentIndex !== -1 && currentPlaylist[currentIndex]) {
        const audioElement = currentPlaylist[currentIndex];
        audioElement.pause();
        isPlaying = false;
        playPauseButton.textContent = '▶ Play';
        updateNowPlaying(null); // Clear the "Now Playing" text
    }
}

// Play the next song
function playNext() {
    if (currentIndex < currentPlaylist.length - 1) {
        currentIndex++; // Increment the index correctly
        playSongByIndex(currentIndex); // Play the next song by updated index
    } else {
        console.log('End of playlist.');
        updateNowPlaying(null); // Clear the "Now Playing" text
    }
}

// Play the previous song
function playPrevious() {
    if (currentIndex > 0) {
        playSongByIndex(currentIndex - 1);
    } else {
        console.log('Start of playlist.');
    }
}

// Toggle play/pause
function togglePlayPause() {
    if (isPlaying) {
        pauseCurrentSong();
    } else if (currentIndex !== -1) {
        playSongByIndex(currentIndex);
    }
}

// Initialize controls
playPauseButton.addEventListener('click', togglePlayPause);
prevButton.addEventListener('click', playPrevious);
nextButton.addEventListener('click', playNext);

// Play a playlist (update the currentPlaylist array)
function playPlaylist(button) {
    const playlist = button.parentElement.parentElement.querySelector('ul');
    if (!playlist) {
        console.error('Playlist not found.');
        return;
    }

    currentPlaylist = Array.from(playlist.querySelectorAll('audio'));
    currentPlaylistId = playlist.id; // Set the current playlist ID
    if (currentPlaylist.length > 0) {
        playSongByIndex(0); // Start from the first song
    } else {
        console.error('No songs in playlist.');
    }
}

// Keep playing the next song automatically when a song is played directly
function playSingleSong(audioId, playlistId) {
    console.log("Audio ID to play:", audioId);
    console.log("Playlist ID to load:", playlistId);

    // Initialize the playlist if it hasn't been set or is different
    if (!currentPlaylist.length || currentPlaylistId !== playlistId) {
        const playlist = document.getElementById(playlistId);
        if (playlist) {
            currentPlaylist = Array.from(playlist.querySelectorAll('audio'));
            currentPlaylistId = playlistId; // Keep track of the current playlist
            console.log("Playlist loaded successfully:", currentPlaylist);
        } else {
            console.error(`Playlist with ID '${playlistId}' not found.`);
            return; // Valid return inside a function
        }
    }

    // Find the index of the selected song in the playlist
    const index = currentPlaylist.findIndex(audio => audio.id === audioId);
    if (index !== -1) {
        currentIndex = index; // Update current index
        playSongByIndex(index); // Play the selected song
    } else {
        console.error(`Audio element with ID '${audioId}' not found in playlist '${playlistId}'.`);
        console.log("Available audio IDs:", currentPlaylist.map(audio => audio.id));
    }
}


//Debugging