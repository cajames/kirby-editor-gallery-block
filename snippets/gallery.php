<?php if ($prev === null || $prev->type() !== 'gallery'): ?>
    <style>
        .k-editor-gallery-block {
            margin-top: 1.5em;
            margin-bottom: 1.5em;
        }
        .k-editor-gallery-block-row {
            display: flex;
        }
        .k-editor-gallery-image-wrapper {
            position: relative;
        }
        .k-editor-gallery-image {
            display: block;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            padding: 1px;
        }
    </style>
    <div class="k-editor-gallery-block <?= $attrs->blockClass() ?>" itemscope itemtype="http://schema.org/ImageGallery">
<?php endif ?>

<?php if (!empty($images)) : ?>
    <div class="k-editor-gallery-block-row <?= $attrs->rowClass() ?>">
        <?php
            $rowRatio = 0;
            foreach($images as $image) {
                $rowRatio = $rowRatio + ($image['width'] / $image['height']);
            }
        ?>
        <?php foreach ($images as $image) : ?>
            <?php
                $ratio = ($image['width']) / ($image['height']);
                $ratioPercent = (($image['height']) / ($image['width'])) * ($ratio / $rowRatio) * 100;
                $ratioWidth = ($ratio / $rowRatio) * 100;
                $imageStyle = "width: $ratioWidth%; padding-bottom: $ratioPercent%;";
            ?>
            <div class="k-editor-gallery-image-wrapper" style="<?= $imageStyle ?>">
                <img class="k-editor-gallery-image <?= $image['imageClass'] ?>" srcset="<?= $image['image']->srcset([500, 1000, 1500]) ?>" sizes="(max-width: 640px) 500px, (max-width: 1200px) 1000px, 1500px" itemprop="thumbnail" alt="<?= $image['altText'] ?>" />
            </div>
        <?php endforeach ?>
    </div>
<?php endif ?>

<?php if ($next === null || $next->type() !== 'gallery'): ?>
    </div>
<?php endif ?>
