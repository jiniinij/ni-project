<!-- Info Grid -->
<div id="center-info-grid" class="center-info-grid">
    <div class="grid-item"><div id="g-meta"></div></div>
    <div class="grid-item"></div>
    <div class="grid-item"><div id="g-instrument"></div></div>
    <div class="grid-item"><div id="g-dateloc"></div></div>
    <div class="grid-item"><div id="g-player"></div></div>
    <div class="grid-item"><div id="g-description"></div></div>
</div>

<!-- Sound Samples List -->
<div class="soundsamples">
    <ul>
        <?php
        $soundFiles = $page->sounds()->toFiles();

        if ($soundFiles->count() > 0):
            foreach ($soundFiles as $sample):

                // Player names
                $playerLinks = [];
                if ($sample->player()->isNotEmpty()) {
                    foreach ($sample->player()->toPages() as $p) {
                        $playerLinks[] = $p->title()->value();
                    }
                }
                $playersTxt = implode(', ', $playerLinks);

                // Duration (YAML first, then fallback)
                $duration = $sample->content()->get('duration')->value();
                if (empty($duration)) {
                    $duration = $sample->duration();
                }

                $displayTitle = htmlspecialchars($sample->filename() ?? '', ENT_QUOTES, 'UTF-8');

                $dataAttrs = [
                    'audio'       => $sample->url(),
                    'title'       => $displayTitle,
                    'instrument'  => htmlspecialchars($sample->content()->get('Instrument')->value() ?? '', ENT_QUOTES, 'UTF-8'),
                    'duration'    => htmlspecialchars($duration, ENT_QUOTES, 'UTF-8'),
                    'date'        => $sample->date()->toDate('Y-m-d'),
                    'location'    => $sample->location(),
                    'player'      => htmlspecialchars($playersTxt, ENT_QUOTES, 'UTF-8'),
                    'description' => htmlspecialchars(trim($sample->description()->kti()), ENT_QUOTES, 'UTF-8')
                ];
        ?>
                <li>
                    <button class="play-btn"
                        <?php foreach ($dataAttrs as $key => $value): ?>
                            data-<?= $key ?>="<?= $value ?>"
                        <?php endforeach ?>>
                        <?= $displayTitle ?>
                    </button>
                </li>
        <?php
            endforeach;
        endif;
        ?>
    </ul>
</div>

<script src="<?= url('assets/js/animations.js') ?>"></script>
<script>
document.addEventListener("DOMContentLoaded", () => {

    const elements = {
        gridPanel: document.getElementById("center-info-grid"),
        playButtons: document.querySelectorAll(".play-btn"),
        ui: {
            meta:       document.getElementById("g-meta"),
            instrument: document.getElementById("g-instrument"),
            dateloc:    document.getElementById("g-dateloc"),
            player:     document.getElementById("g-player"),
            desc:       document.getElementById("g-description")
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
        if (ui.instrument) ui.instrument.innerText = data.instrument;
        if (ui.dateloc) ui.dateloc.innerHTML = `
            <div class="data-content">${data.date}</div>
            <div class="data-content">${data.location}</div>
        `;
        if (ui.player) ui.player.innerText = data.player;
        if (ui.desc)   ui.desc.innerHTML = cleanHTML(data.description);
    };

    const showInfoPanel = () => {
        if (elements.gridPanel) {
            elements.gridPanel.style.display = "grid";
            elements.gridPanel.classList.add("active");
        }
    };

    elements.playButtons.forEach(button => {
        button.addEventListener("click", function() {
            elements.playButtons.forEach(btn => btn.classList.remove("active"));
            this.classList.add("active");
            updateInfoPanel(this);
            showInfoPanel();

            const { audio, title, duration } = this.dataset;
            if (window.addTrackToPlaylist) {
                window.addTrackToPlaylist(audio, title, duration);
            }
        });
    });

    // Shared button & letter animations (animations.js)
    window.initButtonAnimations('.soundsamples');
});
</script>