<?php snippet("header") ?>

<!-- Info Grid -->
<div id="center-info-grid" class="center-info-grid">
    <div class="grid-item"><div id="g-meta"></div></div>
    <div class="grid-item"><div id="g-composer"></div></div>
    <div class="grid-item"></div>
    <div class="grid-item"><div id="g-instrumentation"></div></div>
    <div class="grid-item"><div id="g-date"></div></div>
    <div class="grid-item"><div id="g-description"></div></div>
</div>

<!-- Compositions List -->
<div class="compositions-list">
    <ul>
        <?php foreach ($page->children() as $composition):

            // Audio file
            $audioFile = $composition->audio()->toFile();
            if (!$audioFile) {
                $fileId = $composition->content()->get('audio')->toData('yaml')[0] ?? null;
                if ($fileId) {
                    $audioFile = site()->files()->find($fileId);
                }
            }
            $audioUrl = $audioFile ? $audioFile->url() : null;

            // Composers
            $composers = [];
            foreach ($composition->composer()->toPages() as $c) {
                $composers[] = $c->title()->value();
            }
            $composerTxt = implode(', ', $composers);

            // Duration (YAML first, then fallback)
            $duration = $composition->content()->get('duration')->value();
            if (empty($duration)) {
                $duration = $composition->duration();
            }

            $displayTitle = htmlspecialchars($composition->title()->value(), ENT_QUOTES, 'UTF-8');
            $dataAttrs = [
                'title'           => $displayTitle,
                'composer'        => htmlspecialchars($composerTxt, ENT_QUOTES, 'UTF-8'),
                'instrumentation' => htmlspecialchars(trim($composition->content()->get('Instrumentation')->kti()), ENT_QUOTES, 'UTF-8'),
                'date'            => $composition->date()->toDate('Y-m-d'),
                'duration'        => htmlspecialchars($duration, ENT_QUOTES, 'UTF-8'),
                'description'     => htmlspecialchars(trim($composition->description()->kti()), ENT_QUOTES, 'UTF-8')
            ];
        ?>
            <li>
                <button class="play-btn"
                    <?php if ($audioUrl): ?>
                        data-audio="<?= $audioUrl ?>"
                        style="color: black;"
                    <?php else: ?>
                        onclick="alert('No Audio File'); return false;"
                        style="color: red; opacity: 0.5;"
                    <?php endif ?>
                    <?php foreach ($dataAttrs as $key => $value): ?>
                        data-<?= $key ?>="<?= $value ?>"
                    <?php endforeach ?>>
                    <?= $displayTitle ?>
                </button>
            </li>
        <?php endforeach ?>
    </ul>
</div>

<?php snippet("footer") ?>

<script src="<?= url('assets/js/animations.js') ?>"></script>
<script>
document.addEventListener("DOMContentLoaded", () => {

    const elements = {
        gridPanel: document.getElementById("center-info-grid"),
        playButtons: document.querySelectorAll(".play-btn"),
        ui: {
            meta:        document.getElementById("g-meta"),
            composer:    document.getElementById("g-composer"),
            instrument:  document.getElementById("g-instrumentation"),
            date:        document.getElementById("g-date"),
            desc:        document.getElementById("g-description")
        }
    };

    const cleanHTML = (html) =>
        html ? html.replace(/^(<br\s*\/?>|\s+|&nbsp;|<p>|<\/p>)+/i, '') : '';

    const updateInfoPanel = (button) => {
        const { ui } = elements;
        const data = button.dataset;

        if (ui.meta) ui.meta.innerHTML = `
            <div class="data-content">${data.title}</div>
            <div class="data-content">${data.duration}</div>
        `;
        if (ui.composer)   ui.composer.innerText = data.composer;
        if (ui.instrument) ui.instrument.innerHTML = cleanHTML(data.instrumentation);
        if (ui.date)       ui.date.innerText = data.date;
        if (ui.desc)       ui.desc.innerHTML = cleanHTML(data.description);
    };

    const showInfoPanel = () => {
        if (elements.gridPanel) {
            elements.gridPanel.style.display = "grid";
            elements.gridPanel.classList.add("active");
        }
    };

    const playAudio = (button) => {
        const { audio, title, duration } = button.dataset;
        if (window.addTrackToPlaylist && audio) {
            window.addTrackToPlaylist(audio, title, duration);
        }
    };

    elements.playButtons.forEach(button => {
        button.addEventListener("click", function() {
            elements.playButtons.forEach(btn => btn.classList.remove("active"));
            this.classList.add("active");
            updateInfoPanel(this);
            showInfoPanel();
            playAudio(this);
        });
    });

    // Shared button & letter animations (animations.js)
    window.initButtonAnimations('.compositions-list');
});
</script>