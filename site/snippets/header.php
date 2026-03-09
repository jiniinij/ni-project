<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <?= css("assets/css/styles.css") ?>
  <title><?= $site->title() ?> | <?= $page->title() ?></title>
</head>

<body class="template-<?= $page->template()->name() ?>">
  
  <!-- Mobile Block -->
<div class="mobile-block">
  <p class="mobile-block-text">
    This page is not available on mobile.<br>
    Please visit on desktop.
  </p>
</div>

<!-- Navigation -->
<nav class="nav">
  <ul class="nav-list">
    <?php foreach($site->navigation()->toPages() as $subpage): ?>
      <li>
        <a href="<?= $subpage->url() ?>" class="<?= $subpage->isOpen() ? 'active' : '' ?>">
          <?= $subpage->title() ?>
        </a>
      </li>
    <?php endforeach ?>
  </ul>
</nav>



