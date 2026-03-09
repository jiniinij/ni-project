<?php snippet("header") ?>

<?php 
$contentHtml = $page->text()->isNotEmpty() 
    ? $page->text()->kirbytext() 
    : ($page->description()->isNotEmpty() 
        ? $page->description()->kirbytext() 
        : '');
?>

<div class="center-desc-layout">
    <div class="desc-inner-scroll">
        <?= $contentHtml ?>
    </div>
</div>

<?php snippet("footer") ?>