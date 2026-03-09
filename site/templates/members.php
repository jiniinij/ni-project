<?php snippet("header") ?>

<div id="center-info-grid" class="center-info-grid">
    <div class="grid-item" id="m-name"></div>
    <div class="grid-item" id="m-category"></div>
    <div class="grid-item" id="m-bio"></div>
    <div class="grid-item"></div>
    <div class="grid-item" id="m-intro"></div>
    <div class="grid-item">
        <div id="m-email" class="data-content"></div>
        <div id="m-insta" class="data-content"></div>
    </div>
</div>

<div class="members-list">
    <ul>
        <?php foreach ($page->children()->listed() as $member):
            $categories = $member->category()->split(',');
            $catText    = implode(', ', $categories);
            $bioText    = $member->bio()->kirbytext();
            $introText  = $member->introduction()->kirbytext();
            $email      = $member->email()->value();
            $insta      = $member->instagram()->value();
        ?>
            <li>
                <button class="member-btn"
                    data-name="<?= htmlspecialchars($member->title()->value(), ENT_QUOTES, 'UTF-8') ?>"
                    data-category="<?= htmlspecialchars($catText, ENT_QUOTES, 'UTF-8') ?>"
                    data-email="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>"
                    data-insta="<?= htmlspecialchars($insta, ENT_QUOTES, 'UTF-8') ?>">
                    <span class="sr-only data-bio"><?= $bioText ?></span>
                    <span class="sr-only data-intro"><?= $introText ?></span>
                    <?= $member->title() ?>
                </button>
            </li>
        <?php endforeach ?>
    </ul>
</div>

<?php snippet("footer") ?>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const gridPanel  = document.getElementById("center-info-grid");
    const memberBtns = document.querySelectorAll(".member-btn");

    const ui = {
        name:     document.getElementById("m-name"),
        category: document.getElementById("m-category"),
        bio:      document.getElementById("m-bio"),
        intro:    document.getElementById("m-intro"),
        email:    document.getElementById("m-email"),
        insta:    document.getElementById("m-insta")
    };

    memberBtns.forEach(btn => {
        btn.addEventListener("click", function() {
            memberBtns.forEach(b => b.classList.remove("active"));
            this.classList.add("active");

            ui.name.innerText     = this.dataset.name;
            ui.category.innerText = this.dataset.category;
            ui.bio.innerHTML      = this.querySelector('.data-bio').innerHTML;
            ui.intro.innerHTML    = this.querySelector('.data-intro').innerHTML;

            ui.email.innerHTML = this.dataset.email
                ? `<a href="mailto:${this.dataset.email}">${this.dataset.email}</a>`
                : '';

            ui.insta.innerHTML = this.dataset.insta
                ? `<a href="${this.dataset.insta}" target="_blank">Instagram ↗</a>`
                : '';

            gridPanel.style.display = "grid";
            gridPanel.classList.add("active");
        });
    });
});
</script>