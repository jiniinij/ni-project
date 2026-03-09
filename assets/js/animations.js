/**
 * Shared button & letter animations
 * Used by: compositions.php, soundsamples (snippet)
 *
 * Expects:
 *   - window.isGlobalAudioPlaying (set by footer playlist)
 *   - selector: string for the list container (e.g. '.compositions-list')
 *   - selector: '.play-btn' buttons inside the page
 */

window.initButtonAnimations = function(listContainerSelector) {
    const CONFIG = {
        ANIMATION: {
            BUTTON_Y_AMOUNT: 1,
            BUTTON_Y_AMOUNT_PLAYING: 20,
            LETTER_VIBRATION_AMOUNT: 2,
            BUTTON_FREQUENCY: 2,
            LETTER_SPEED: 10
        }
    };

    const state = {
        letterAnimations: [],
        buttonAnimations: [],
        buttonStates: new Map(),
        startTime: performance.now()
    };

    const playButtons = document.querySelectorAll('.play-btn');
    const listContainer = document.querySelector(listContainerSelector);

    /* ---- Helpers ---- */

    const wrapLettersInSpans = (button) => {
        const text = button.textContent;
        button.textContent = '';
        text.split('').forEach((char) => {
            const span = document.createElement('span');
            span.textContent = char;
            span.style.display = 'inline-block';
            span.style.position = 'relative';
            button.appendChild(span);
            state.letterAnimations.push({
                el: span,
                button: button,
                vibrationPhase: Math.random() * Math.PI * 2
            });
        });
    };

    const animateButtons = () => {
        const t = (performance.now() - state.startTime) / 1000;
        const isPlaying = window.isGlobalAudioPlaying;
        const baseSpeed = CONFIG.ANIMATION.BUTTON_FREQUENCY;
        const yAmount = isPlaying
            ? CONFIG.ANIMATION.BUTTON_Y_AMOUNT_PLAYING
            : CONFIG.ANIMATION.BUTTON_Y_AMOUNT;

        state.buttonAnimations.forEach(anim => {
            if (!state.buttonStates.get(anim.button)) {
                const speed = t * anim.randomFactor * baseSpeed;
                const y = Math.sin(speed + anim.phase) * yAmount;
                anim.button.style.transform = `rotate(180deg) translateY(${y}px)`;
            } else {
                anim.button.style.transform = `rotate(180deg) translateY(0px)`;
            }
        });

        requestAnimationFrame(animateButtons);
    };

    const animateLetters = () => {
        const t = (performance.now() - state.startTime) / 1000;
        const isPlaying = window.isGlobalAudioPlaying;
        const letterSpeed = CONFIG.ANIMATION.LETTER_SPEED;

        state.letterAnimations.forEach(anim => {
            if (!state.buttonStates.get(anim.button) && isPlaying) {
                const vibrationX = Math.sin(t * letterSpeed + anim.vibrationPhase)
                    * CONFIG.ANIMATION.LETTER_VIBRATION_AMOUNT;
                anim.el.style.transform = `translateX(${vibrationX}px)`;
            } else {
                anim.el.style.transform = 'translateX(0px)';
            }
        });

        requestAnimationFrame(animateLetters);
    };

    /* ---- Init ---- */

    playButtons.forEach(button => {
        state.buttonStates.set(button, false);

        button.addEventListener('mouseenter', () => state.buttonStates.set(button, true));
        button.addEventListener('mouseleave', () => state.buttonStates.set(button, false));

        state.buttonAnimations.push({
            button,
            phase: Math.random() * Math.PI * 2,
            randomFactor: 0.8 + Math.random() * 0.4
        });

        wrapLettersInSpans(button);
    });

    if (listContainer) {
        listContainer.addEventListener('wheel', (e) => {
            e.preventDefault();
            listContainer.scrollLeft += (e.deltaY + e.deltaX);
        });
    }

    animateButtons();
    animateLetters();
};