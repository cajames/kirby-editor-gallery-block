<?php if ($prev === null || $prev->type() !== 'gallery'): ?>
    <div class="k-gallery-block" itemscope itemtype="http://schema.org/ImageGallery" style="margin-top: 1.5em; margin-bottom: 1.5em;">
<?php endif ?>

<?php if (!empty($images)) : ?>
    <div class="row" style="position: relative; display: flex;">
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
                $imageStyle = "position: relative; width: $ratioWidth%; padding-left: 2px; padding-right: 2px; padding-bottom: $ratioPercent%;";
            ?>
            <div class="k-gallery-block-image" style="<?= $imageStyle ?>">
                <img style="display: block; position:absolute; width:100%; height:100%; padding: 1px; top:0; left:0;" srcset="<?= $image['image']->srcset([400, 800, 1200]) ?>" sizes="(max-width: 640px) 400px, (max-width: 1024px) 800px, 1200px" itemprop="thumbnail" alt="" />
            </div>
        <?php endforeach ?>
    </div>
<?php endif ?>

<?php if ($next === null || $next->type() !== 'gallery'): ?>
    </div>
<?php endif ?>
