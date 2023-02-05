<?php

declare(strict_types=1);

namespace App;

use Imagine\Gd\Imagine;
use Imagine\Image\Box;

class ImageOptimizer
{
    private const MAX_WIDTH = 200;

    private const MAX_HEIGHT = 150;

    private readonly Imagine $imagine;

    public function __construct()
    {
        $this->imagine = new Imagine();
    }

    public function resize(string $filename): void
    {
        if (($imageSize = getimagesize($filename)) !== false) {
            [$iwidth, $iheight] = $imageSize;
            $ratio = $iwidth / $iheight;
            $width = self::MAX_WIDTH;
            $height = self::MAX_HEIGHT;
            if ($width / $height > $ratio) {
                $width = $height * $ratio;
            } else {
                $height = $width / $ratio;
            }

            $photo = $this->imagine->open($filename);
            $photo->resize(new Box((int) round($width), (int) round($height)))->save($filename);
        }
    }
}
