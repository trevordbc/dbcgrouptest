document.addEventListener('DOMContentLoaded', function () {
    // Constants
    const podcastElements = document.querySelectorAll('.podcast-element');
    const audioPlayer = document.getElementById('player');
    const currentTitle = document.getElementById('currentTitle');
    const playBtn = document.getElementById('play');
    const pauseBtn = document.getElementById('pause');
    const stopBtn = document.getElementById('stop');
    const seekBar = document.getElementById('seekbar');
    const currentTimeDisplay = document.getElementById('current-time');
    const durationDisplay = document.getElementById('duration');
    const loadMoreBtn = document.getElementById('load-more');
    const podcastPlayerElements = document.querySelector('.podcast-player-elements');

    // Helper function to format time (convert seconds to mm:ss)
    function formatTime(seconds) {
        const minutes = Math.floor(seconds / 60);
        const remainingSeconds = Math.floor(seconds % 60);
        return `${minutes}:${remainingSeconds < 10 ? '0' + remainingSeconds : remainingSeconds}`;
    }

    // Function to add click listener to podcast elements
    function addPodcastClickListener(element) {
        element.addEventListener('click', function (event) {
            event.preventDefault();
            const url = this.getAttribute('data-url');
            const title = this.getAttribute('data-title');
            
            const activeElement = document.querySelector('.podcast-element.active');
            if (activeElement) {
                activeElement.classList.remove('active');
            }
            this.classList.add('active');

            audioPlayer.src = url;
            currentTitle.textContent = title;
            audioPlayer.load();
            audioPlayer.play();
        });
    }

    // Function to load initial podcasts
    function loadInitialPodcasts() {
        const podcastPlayerElements = document.querySelector('.podcast-player-elements');
        const rssFeedUrl = loadMoreBtn.getAttribute('data-rss-feed-url');
        const podcastLimit = parseInt(loadMoreBtn.getAttribute('data-podcast-limit'), 10);

        fetch(`${PodcastPlayer.ajaxurl}?action=load_more_podcasts&rss_feed_url=${encodeURIComponent(rssFeedUrl)}&offset=0&limit=${podcastLimit}`, {
            method: 'GET',
            headers: new Headers({
                'Content-Type': 'application/x-www-form-urlencoded'
            }),
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                data.data.forEach((podcast, index) => {
                    const existingPodcastElement = podcastPlayerElements.querySelector(`[data-guid="${podcast.guid}"]`);
                    if (existingPodcastElement) {
                        return; // Skip adding this podcast
                    }
                    const newElement = document.createElement('div');
                    newElement.classList.add('podcast-element');
                    newElement.setAttribute('data-url', podcast.url);
                    newElement.setAttribute('data-title', podcast.title);
                    newElement.setAttribute('data-guid', podcast.guid);
                    
                    if (index === 0) {
                        newElement.classList.add('active');
                    }

                    const container = document.createElement('div');
                    container.classList.add('podcast-element-container');

                    const title = document.createElement('span');
                    title.textContent = podcast.title;
                    container.appendChild(title);

                    const formattedDuration = formatTime(podcast.duration);
                    const duration = document.createElement('span');
                    duration.textContent = `Total Duration: ${formattedDuration}`;
                    container.appendChild(duration);

                    newElement.appendChild(container);
                    podcastPlayerElements.appendChild(newElement);
                    addPodcastClickListener(newElement);
                });

                audioPlayer.setAttribute('data-audio-limit', podcastLimit);

                // Select the first podcast after loading the initial podcasts
                if (data.data.length > 0) {
                    const firstPodcast = data.data[0];
					audioPlayer.src = firstPodcast.url;
					currentTitle.textContent = firstPodcast.title;
					audioPlayer.load();
				}
			} else {
				console.error('Error fetching initial podcasts:', data);
			}
		})
		.catch(error => console.error('Error fetching initial podcasts:', error));
	}
	function playNextPodcast(autoPlay = false) {
    const activeElement = document.querySelector('.podcast-element.active');
    if (activeElement) {
        const nextElement = activeElement.nextElementSibling;
        if (nextElement) {
            nextElement.click();
        } else {
            // No more podcasts in the list, load more and then play the next one
            loadMorePodcasts(() => {
                const newNextElement = activeElement.nextElementSibling;
                if (newNextElement) {
                    if (autoPlay) {
                        newNextElement.click();
                    }
                }
            });
        }
    }
}

// Function to load more podcasts
function loadMorePodcasts(callback) {
    const currentLimit = parseInt(audioPlayer.getAttribute('data-audio-limit'), 10);
    const newLimit = currentLimit + parseInt(loadMoreBtn.getAttribute('data-podcast-limit'), 10);
    const rssFeedUrl = loadMoreBtn.getAttribute('data-rss-feed-url');

    fetch(`${PodcastPlayer.ajaxurl}?action=load_more_podcasts&rss_feed_url=${encodeURIComponent(rssFeedUrl)}&offset=${currentLimit}&limit=${newLimit}`, {
        method: 'GET',
        headers: new Headers({
            'Content-Type': 'application/x-www-form-urlencoded'
        }),
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            data.data.forEach((podcast, index) => {
                const newElement = document.createElement('div');
                newElement.classList.add('podcast-element');
                newElement.setAttribute('data-url', podcast.url);
                newElement.setAttribute('data-title', podcast.title);
                newElement.setAttribute('data-guid', podcast.guid);
                
                const existingPodcastElement = podcastPlayerElements.querySelector(`[data-guid="${podcast.guid}"]`);
                if (existingPodcastElement) {
                    return; // Skip adding this podcast
                }

                const container = document.createElement('div');
                container.classList.add('podcast-element-container');

                const title = document.createElement('span');
                title.textContent = podcast.title;
                container.appendChild(title);

                const formattedDuration = formatTime(podcast.duration);
                const duration = document.createElement('span');
                duration.textContent = `Total Duration: ${formattedDuration}`;
                container.appendChild(duration);

                newElement.appendChild(container);
                podcastPlayerElements.appendChild(newElement);
                addPodcastClickListener(newElement);
            });

            audioPlayer.setAttribute('data-audio-limit', newLimit);
            if (typeof callback === 'function' && data.data.length > 0) {
                callback();
            }
        } else {
            console.error('Error fetching more podcasts:', data);
        }
    })
    .catch(error => console.error('Error fetching more podcasts:', error));
	}

	// Adding event listeners
	podcastElements.forEach(addPodcastClickListener);
	playBtn.addEventListener('click', () => audioPlayer.play());
	pauseBtn.addEventListener('click', () => audioPlayer.pause());
	stopBtn.addEventListener('click', () => {
		audioPlayer.pause();
		audioPlayer.currentTime = 0;
	});

	audioPlayer.addEventListener('timeupdate', () => {
		seekBar.value = (audioPlayer.currentTime / audioPlayer.duration) * 100;
		//currentTimeDisplay.textContent = formatTime(audioPlayer.currentTime);
	});

	audioPlayer.addEventListener('loadedmetadata', () => {
		durationDisplay.textContent = formatTime(audioPlayer.duration);
	});

	seekBar.addEventListener('input', () => {
		audioPlayer.currentTime = (seekBar.value / 100) * audioPlayer.duration;
	});

	audioPlayer.addEventListener('ended', () => {
		playNextPodcast(true);
	});

	loadMoreBtn.addEventListener('click', (event) => {
		event.preventDefault();
		loadMorePodcasts();
	});

	audioPlayer.addEventListener('timeupdate', function () {
	  seekBar.value = (audioPlayer.currentTime / audioPlayer.duration) * 100;

	  // Update tooltip position and text
	  const tooltipPercentage = (audioPlayer.currentTime / audioPlayer.duration) * 100;
	  const seekbarTooltip = document.getElementById('seekbar-tooltip');
	  const handlePosition = seekBar.clientWidth * (tooltipPercentage / 100) - seekbarTooltip.clientWidth / 2;
	  seekbarTooltip.style.left = `${handlePosition}px`;
	  seekbarTooltip.textContent = formatTime(audioPlayer.currentTime);
	});

    //seekBar.addEventListener('input', () => {
    //    audioPlayer.currentTime = (seekBar.value / 100) * audioPlayer.duration;
    //});
	seekBar.addEventListener('input', function () {
	  audioPlayer.currentTime = (seekBar.value / 100) * audioPlayer.duration;
	  seekBar.style.setProperty('--seekbar-value', seekBar.value);
	});
	// Load initial podcasts
	loadInitialPodcasts();
});