<!-- Playlist Containers -->
<div id="playlist-toggle-btn" class="playlist-toggle">DOWN</div>
<div id="bottom-stack"></div>
<div id="side-playlist"></div>

<script>
(function() {
    'use strict';

    /* =========================================
       DOM REFERENCES & CONSTANTS
    =========================================== */
    const sideContainerRoot = document.getElementById('side-playlist');
    const bottomContainer   = document.getElementById('bottom-stack');
    const toggleBtn         = document.getElementById('playlist-toggle-btn');
    const STORAGE_KEY       = 'clean_playlist_v4_drag';

    let listContainer;
    let playlist    = [];
    let playAllBtn;

    let isPlaylistVisible        = false;
    window.isGlobalAudioPlaying  = false;

    /* =========================================
       INITIALIZATION
    =========================================== */
    function init() {
        initLayout();
        restorePlaylist();
        setupScrollListener();
        setupToggleButton();
        setupPlayAllButton();
    }

    function initLayout() {
        if (sideContainerRoot.querySelector('#pl-header')) return;

        sideContainerRoot.innerHTML = `
            <div id="pl-header">
                <span>your collection</span>
                <button id="play-all-btn" class="play-all-btn">PLAY ALL</button>
            </div>
            <div id="pl-list-container"></div>
        `;

        listContainer = document.getElementById('pl-list-container');
        playAllBtn    = document.getElementById('play-all-btn');
    }

    function setupScrollListener() {
        window.addEventListener('scroll', () => {
            const currentScroll = window.scrollY;
            const triggerPoint  = 10;

            if (currentScroll > triggerPoint) {
                if (!isPlaylistVisible) {
                    isPlaylistVisible = true;
                    toggleBtn.innerHTML = 'UP';
                    toggleBtn.classList.add('is-active');
                }
            } else {
                if (isPlaylistVisible) {
                    isPlaylistVisible = false;
                    toggleBtn.innerHTML = 'DOWN';
                    toggleBtn.classList.remove('is-active');
                }
            }
        });
    }

    function setupToggleButton() {
        toggleBtn.addEventListener('click', () => {
            if (!isPlaylistVisible) {
                window.scrollTo({ top: window.innerHeight, behavior: 'smooth' });
            } else {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });
    }

    /* =========================================
       PLAY ALL / PAUSE ALL
    =========================================== */
    function setupPlayAllButton() {
        if (!playAllBtn) return;

        playAllBtn.addEventListener('click', () => {
            const isAnyPlaying = playlist.some(t => !t.audio.paused);
            isAnyPlaying ? pauseAll() : playAll();
            updatePlayAllButton();
        });
    }

    function playAll() {
        playlist.forEach(track => {
            if (track.audio.paused) {
                track.audio.play().catch(e => console.error('Play error:', e));
            }
        });
    }

    function pauseAll() {
        playlist.forEach(track => {
            if (!track.audio.paused) track.audio.pause();
        });
    }

    function updatePlayAllButton() {
        if (!playAllBtn || playlist.length === 0) {
            if (playAllBtn) playAllBtn.style.display = 'none';
            return;
        }

        playAllBtn.style.display = 'block';

        const isAnyPlaying = playlist.some(t => !t.audio.paused);
        if (isAnyPlaying) {
            playAllBtn.textContent = 'PAUSE ALL';
            playAllBtn.classList.add('is-pausing');
        } else {
            playAllBtn.textContent = 'PLAY ALL';
            playAllBtn.classList.remove('is-pausing');
        }
    }

    /* =========================================
       TRACK MANAGEMENT
    =========================================== */
    function addTrack(url, title, durationStr, startTime = 0, autoPlay = true) {
        if (!url) return;

        const existingIndex = playlist.findIndex(t => t.url === url);
        if (existingIndex !== -1) {
            const track = playlist[existingIndex];
            if (listContainer.contains(track.domSide)) {
                highlightAndPlay(track, autoPlay);
                return;
            } else {
                removeTrack(track.id);
            }
        }

        const trackId  = generateTrackId();
        const num      = playlist.length + 1;
        const audio    = createAudio(url, startTime);
        const sideItem = createSideItem(num, title, durationStr);
        const ctrlItem = createCtrlItem(num);

        listContainer.appendChild(sideItem);
        bottomContainer.appendChild(ctrlItem);

        const trackObj = { id: trackId, url, title, durationStr, audio, domSide: sideItem, domCtrl: ctrlItem };
        playlist.push(trackObj);

        setupTrackEvents(trackObj);
        savePlaylist();

        if (autoPlay) {
            setTimeout(() => {
                audio.play().catch(e => console.log('Autoplay prevented:', e));
            }, 50);
        }

        updateGlobalState();
        updatePlayAllButton();
    }

    function highlightAndPlay(track, autoPlay) {
        if (autoPlay) track.audio.play().catch(e => console.error('Play error:', e));
        track.domSide.classList.add('highlight');
        setTimeout(() => track.domSide.classList.remove('highlight'), 500);
    }

    function removeTrack(id) {
        const index = playlist.findIndex(t => t.id === id);
        if (index === -1) return;

        const track = playlist[index];
        track.audio.pause();
        track.audio.src = '';
        track.domSide.remove();
        track.domCtrl.remove();
        playlist.splice(index, 1);

        renumberPlaylist();
        savePlaylist();
        updateGlobalState();
        updatePlayAllButton();
    }

    function renumberPlaylist() {
        playlist.forEach((track, idx) => {
            const num = idx + 1;
            track.domSide.querySelector('.pl-num').innerText  = num;
            track.domCtrl.querySelector('.ctrl-num').innerText = num;
        });
    }

    /* =========================================
       DOM CREATION
    =========================================== */
    function createAudio(url, startTime) {
        const audio = new Audio(url);
        audio.currentTime = startTime;
        audio.loop = true;
        return audio;
    }

    function createSideItem(num, title, durationStr) {
        const item = document.createElement('div');
        item.className = 'pl-item';
        item.innerHTML = `
            <span class="pl-num">${num}</span>
            <button class="pl-play-btn">PLAY</button>
            <span class="pl-title">${title}</span>
            <span class="pl-duration">${durationStr || '--:--'}</span>
            <div class="pl-close">CLOSE</div>
        `;
        return item;
    }

    function createCtrlItem(num) {
        const item = document.createElement('div');
        item.className = 'ctrl-row';
        item.innerHTML = `
            <div class="ctrl-inner">
                <span class="ctrl-num">${num}</span>
                <div class="ctrl-range-wrap">
                    <input type="range" class="ctrl-range" min="0" max="100" value="0" step="0.01">
                </div>
            </div>
        `;
        return item;
    }

    /* =========================================
       EVENT SETUP
    =========================================== */
    function setupTrackEvents(track) {
        const { audio, domSide, domCtrl } = track;
        const playBtn    = domSide.querySelector('.pl-play-btn');
        const range      = domCtrl.querySelector('.ctrl-range');
        const durationEl = domSide.querySelector('.pl-duration');
        const closeBtn   = domSide.querySelector('.pl-close');
        const ctrlNum    = domCtrl.querySelector('.ctrl-num');
        const rangeWrap  = domCtrl.querySelector('.ctrl-range-wrap');

        let rafId;
        let lastValue        = 0;
        let lastTime         = performance.now();
        let currentRotation  = 0;
        let targetRotation   = 0;
        let isUserDragging   = false;

        // Slider thumb tilt animation
        const updateSliderRotation = () => {
            const now       = performance.now();
            const deltaTime = now - lastTime;
            const currentValue = parseFloat(range.value);
            const deltaValue   = currentValue - lastValue;

            if (deltaTime > 0 && deltaTime < 100) {
                const velocity = deltaValue / deltaTime * 1000;
                targetRotation = Math.abs(velocity) > 0.1
                    ? Math.max(-20, Math.min(20, velocity * 0.5))
                    : 0;
            } else {
                targetRotation = 0;
            }

            const smoothness = isUserDragging ? 0.3 : 0.15;
            currentRotation += (targetRotation - currentRotation) * smoothness;
            if (Math.abs(currentRotation) < 0.1 && Math.abs(targetRotation) < 0.1) {
                currentRotation = 0;
            }

            rangeWrap.style.setProperty('--thumb-rotation', `${currentRotation}deg`);
            lastValue = currentValue;
            lastTime  = now;

            requestAnimationFrame(updateSliderRotation);
        };

        updateSliderRotation();

        range.addEventListener('mousedown',  () => isUserDragging = true);
        range.addEventListener('mouseup',    () => isUserDragging = false);
        range.addEventListener('touchstart', () => isUserDragging = true);
        range.addEventListener('touchend',   () => isUserDragging = false);

        audio.addEventListener('play', () => {
            updatePlayButton(playBtn, false);
            domSide.classList.add('playing');
            ctrlNum.classList.add('is-active');
            updateGlobalState();
            updatePlayAllButton();
            setTimeout(() => initSlider(), 100);
            startSlider();
        });

        audio.addEventListener('pause', () => {
            updatePlayButton(playBtn, true);
            domSide.classList.remove('playing');
            ctrlNum.classList.remove('is-active');
            updateGlobalState();
            updatePlayAllButton();
            stopSlider();
        });

        audio.addEventListener('ended', () => {
            updatePlayButton(playBtn, true);
            domSide.classList.remove('playing');
            ctrlNum.classList.remove('is-active');
            updateGlobalState();
            updatePlayAllButton();
            stopSlider();
        });

        audio.addEventListener('loadedmetadata', () => {
            initSlider();
            if (!audio.paused) startSlider();
        });

        const togglePlay = () => {
            audio.paused
                ? audio.play().catch(e => console.error(e))
                : audio.pause();
        };

        playBtn.addEventListener('click',  (e) => { e.stopPropagation(); togglePlay(); });
        ctrlNum.addEventListener('click',  (e) => { e.stopPropagation(); togglePlay(); });
        closeBtn.addEventListener('click', (e) => { e.stopPropagation(); removeTrack(track.id); });

        range.addEventListener('input',  () => updateSliderBackground(range));
        range.addEventListener('change', () => {
            if (audio.duration) {
                audio.currentTime = (range.value / 100) * audio.duration;
            }
        });

        function initSlider() {
            if (audio.duration) {
                if (!track.durationStr) durationEl.innerText = formatTime(audio.duration);
                const progress = (audio.currentTime / audio.duration) * 100;
                range.value = progress;
                lastValue   = progress;
                updateSliderBackground(range);
            }
        }

        function updateSlider() {
            if (!audio.paused && audio.duration) {
                range.value = (audio.currentTime / audio.duration) * 100;
                updateSliderBackground(range);
                rafId = requestAnimationFrame(updateSlider);
            }
        }

        function startSlider() {
            cancelAnimationFrame(rafId);
            updateSlider();
        }

        function stopSlider() {
            cancelAnimationFrame(rafId);
            targetRotation = 0;
        }
    }

    /* =========================================
       UTILITIES
    =========================================== */
    function updatePlayButton(button, isPaused) {
        button.innerText = isPaused ? 'PLAY' : 'PAUSE';
    }

    function updateSliderBackground(range) {
        const p = range.value;
        range.style.background = `linear-gradient(to right, #000 ${p}%, #ddd ${p}%)`;
    }

    function updateGlobalState() {
        window.isGlobalAudioPlaying = playlist.some(t => !t.audio.paused);
    }

    function formatTime(seconds) {
        if (isNaN(seconds)) return '0:00';
        const mins = Math.floor(seconds / 60);
        const secs = Math.floor(seconds % 60).toString().padStart(2, '0');
        return `${mins}:${secs}`;
    }

    function generateTrackId() {
        return 't-' + Date.now() + Math.random().toString(36).substr(2, 5);
    }

    /* =========================================
       SAVE & RESTORE
    =========================================== */
    function savePlaylist() {
        const data = playlist.map(t => ({
            url:         t.url,
            title:       t.title,
            durationStr: t.durationStr,
            currentTime: t.audio.currentTime
        }));
        localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
    }

    function restorePlaylist() {
        const saved = localStorage.getItem(STORAGE_KEY);
        if (!saved) return;

        try {
            const data = JSON.parse(saved);
            data.forEach(item => {
                addTrack(item.url, item.title, item.durationStr, item.currentTime, false);
            });
        } catch (e) {
            console.error('Failed to restore playlist:', e);
            localStorage.removeItem(STORAGE_KEY);
        }
    }

    /* =========================================
       GLOBAL API
    =========================================== */
    window.addTrackToPlaylist = function(url, title, durationStr) {
        addTrack(url, title, durationStr, 0, true);
    };

    window.addEventListener('beforeunload', savePlaylist);

    init();

})();
</script>