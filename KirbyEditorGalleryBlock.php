<?php

namespace Kirby\Editor;

use Throwable;

class GalleryBlock extends Block
{
    public function controller(): array
    {
        $data = parent::controller();
        $arrayData = $this->toArray();
        $data['images'] = $arrayData['attrs']['images'];
        return $data;
    }

    public function image($filename)
    {
        try {
            return $this->parent()->file($filename);
        } catch (Throwable $e) {
            return null;
        }
    }

    public function isEmpty(): bool
    {
        $data = $this->toArray();
        $images = $data['attr']['images'];
        return count($images) > 0;
    }

    public function toArray(bool $toStorage = false): array
    {
        $data = parent::toArray();
        $attrs = $data['attrs'] ?? [];
        $images = $attrs['images'] ?? [];
        $newImages = [];

        foreach ($images as $imageData) {
            $image = null;
            $altText = isset($imageData['altText']) ? $imageData['altText'] : '';
            $imageClass = isset($imageData['imageClass']) ? $imageData['imageClass'] : '';

            if (isset($imageData['filename'])) {
                $image = $this->image($imageData['filename']);
            } else {
                $image = $this->imageGuid($imageData['guid']);
            }
            if ($image) {
                $imageRecord = [
                    'filename' => $image->filename(),
                    'guid' => $image->panelUrl(true),
                    'ratio' => $image->ratio(),
                    'height' => $image->height(),
                    'width' => $image->width(),
                    'id' => $image->id(),
                    'src' => $image->resize(1000)->url(),
                    'image' => $image,
                    'altText' => $altText,
                    'imageClass' => $imageClass,
                ];
                if ($toStorage === true) {
                    unset($imageRecord['ratio']);
                    unset($imageRecord['src']);
                    unset($imageRecord['width']);
                    unset($imageRecord['height']);
                    unset($imageRecord['id']);
                    unset($imageRecord['image']);
                }
                array_push($newImages, $imageRecord);
            }
        }

        $data['attrs'] = array_merge($data['attrs'] ?? [], [
            'images' => $newImages
        ]);

        return $data;
    }

    public function toStorage(): array
    {
        return $this->toArray(true);
    }

}
