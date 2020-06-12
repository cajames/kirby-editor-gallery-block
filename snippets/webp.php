<?php if ($prev === null || $prev->type() !== 'gallery'): ?>
    <div class="k-gallery-block" itemscope itemtype="http://schema.org/ImageGallery">
<?php endif ?>
<?php if (!empty($images)) : ?>
    <div class="row" style="position: relative;">
        <?php
            $rowRatio = 0;
            foreach($images as $image) {
                $rowRatio = $rowRatio + ($image['width'] / $image['height']);
            }
        ?>
        <?php foreach ($images as $image) : ?>
            <?php
            $ratio = $image['width'] / $image['height'];
            $ratioPercent = ($image['height'] / $image['width']) * ($ratio / $rowRatio) * 100;
            $ratioWidth = ($ratio / $rowRatio) * 100;
            $imageStyle = "position: relative; width: $ratioWidth%; padding-left: 2px; padding-right: 2px; padding-bottom: $ratioPercent%;";
            $zoomImage = $image['image']->toWebp()->resize(1920);
            ?>
            <div class="k-gallery-block-image" style="<?= $imageStyle ?>">
                <a class="image block" href="<?= $zoomImage->url() ?>" itemprop="contentUrl" data-height="<?= $zoomImage->height() ?>" data-width="<?= $zoomImage->width() ?>">
                    <?= snippet('webp', ['sizes' => [1920, 1140, 640, 320], 'src' => $image['image'],  'class' => 'lazy absolute', 'width' => $image['width'], 'height' => $image['height']]) ?>
                </a>
            </div>
        <?php endforeach ?>
    </div>
<?php endif ?>
<?php if ($next === null || $next->type() !== 'gallery'): ?>
    </div>
<?php endif ?>
