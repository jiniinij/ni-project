<?php snippet("header") ?>

<div id="center-info-grid" class="center-info-grid">
    <div class="grid-item"><div id="r-meta"></div></div>
    <div class="grid-item"><div id="r-category"></div></div>
    <div class="grid-item"><div id="r-participants"></div></div>
    <div class="grid-item"><div id="r-desc"></div></div>
    <div class="grid-item"></div>
    <div class="grid-item"><div id="r-photos" class="gallery-container"></div></div>
</div>

<div class="records-list">
    <ul>
        <?php foreach ($page->children()->listed()->sortBy('date', 'desc') as $record):
            $date         = $record->date()->toDate('Y.m.d');
            $location     = $record->location()->value();
            $category     = $record->category()->value();
            $descHtml     = $record->description()->kirbytext();

            $participants = [];
            foreach ($record->participants()->toPages() as $p) {
                $participants[] = $p->title()->value();
            }
            $partText = implode('<br>', $participants);

            $galleryHtml = '';
            foreach ($record->photos()->toFiles() as $photo) {
                $galleryHtml .= '<div class="img-frame"><img src="' . $photo->url() . '" alt=""></div>';
            }
        ?>
            <li>
                <button class="record-btn"
                    data-date="<?= $date ?>"
                    data-location="<?= htmlspecialchars($location, ENT_QUOTES, 'UTF-8') ?>"
                    data-category="<?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?>">
                    <span class="title"><?= $record->title() ?></span>
                    <span class="sr-only data-participants"><?= $partText ?></span>
                    <span class="sr-only data-desc"><?= $descHtml ?></span>
                    <span class="sr-only data-photos"><?= $galleryHtml ?></span>
                </button>
            </li>
        <?php endforeach ?>
    </ul>
</div>

<?php snippet("footer") ?>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const gridPanel   = document.getElementById("center-info-grid");
    const recordBtns  = document.querySelectorAll(".record-btn");

    const ui = {
        meta:         document.getElementById("r-meta"),
        category:     document.getElementById("r-category"),
        participants: document.getElementById("r-participants"),
        desc:         document.getElementById("r-desc"),
        photos:       document.getElementById("r-photos")
    };

    const initPhotoZoom = (container) => {
        container.querySelectorAll('.img-frame').forEach(frame => {
            const img = frame.querySelector('img');

            frame.addEventListener('mousemove', (e) => {
                const rect = frame.getBoundingClientRect();
                img.style.setProperty('--x', `${((e.clientX - rect.left) / rect.width) * 100}%`);
                img.style.setProperty('--y', `${((e.clientY - rect.top) / rect.height) * 100}%`);
            });

            frame.addEventListener('mouseleave', () => {
                img.style.setProperty('--x', '50%');
                img.style.setProperty('--y', '50%');
            });
        });
    };

    recordBtns.forEach(btn => {
        btn.addEventListener("click", function() {
            recordBtns.forEach(b => b.classList.remove("active"));
            this.classList.add("active");

            ui.meta.innerHTML = `
                <div class="data-content">${this.dataset.date}</div>
                <div class="data-content">${this.dataset.location}</div>
            `;
            ui.category.innerText     = this.dataset.category;
            ui.participants.innerHTML = this.querySelector('.data-participants').innerHTML;
            ui.desc.innerHTML         = this.querySelector('.data-desc').innerHTML;
            ui.photos.innerHTML       = this.querySelector('.data-photos').innerHTML;

            initPhotoZoom(ui.photos);

            gridPanel.style.display = "grid";
            gridPanel.classList.add("active");
        });
    });
});
</script>