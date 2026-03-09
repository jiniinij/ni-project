<?php snippet("header") ?>

<?php 
$descText = $page->description()->isNotEmpty() 
    ? $page->description()->kirbytext() 
    : '';
?>

<div id="ni-center-desc" class="center-desc-layout">
    <div class="desc-inner-scroll">
        <?= $descText ?>
    </div>
</div>

<?php snippet("footer") ?>