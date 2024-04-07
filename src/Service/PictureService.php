<?php

namespace BitrixModels\Service;

use CFile;

class PictureService
{
    const SIZE_SMALL = 'small';
    const SIZE_MEDIUM = 'medium';
    const SIZE_BIG = 'big';
    const SIZE_REFERENCE = 'reference';

    protected int $compression = 80;
    protected ?string $watermark = null;

    protected array $config = [
        self::SIZE_SMALL => ['width' => 380, 'height' => 300],
        self::SIZE_MEDIUM => ['width' => 860, 'height' => 860],
        self::SIZE_BIG => ['width' => 1920, 'height' => 1920],
        self::SIZE_REFERENCE => ['width' => 100000, 'height' => 100000],
    ];

    public function setWatermark(string $watermark): void
    {
        $this->watermark = $_SERVER['DOCUMENT_ROOT'] . $watermark;
    }

    public function setCompression(int $compression): void
    {
        $this->compression = $compression;
    }

    public function setSize(string $code, $width, $height): void
    {
        $this->config[$code] = ['width' => $width, 'height' => $height];
    }

    protected function getSizeConfig($size): array
    {
        if (isset($this->config[$size])) {
            return $this->config[$size];
        }

        return $this->config[self::SIZE_SMALL];
    }

    public function getPicture($imgId, $size = self::SIZE_SMALL, bool $fullPath = false): ?string
    {
        $compression = $this->compression;
        if ($size == self::SIZE_REFERENCE) {
            $compression = 0;
        }

        $size = $this->getSizeConfig($size);

        return $this->getPictureWithCustomSize($imgId, $size['width'], $size['height'], $compression, $fullPath);
    }

    public function getPictureWithWatermark($imgId, $size = self::SIZE_SMALL, bool $fullPath = false): ?string
    {
        $compression = $this->compression;
        if ($size == self::SIZE_REFERENCE) {
            $compression = 0;
        }

        $size = $this->getSizeConfig($size);

        return $this->getPictureWithCustomSize($imgId, $size['width'], $size['height'], $compression, $fullPath, $this->watermark);
    }

    public function getPictureWithCustomSize($imgId, int $width = 300, int $height = 300, int $compression = 80, bool $fullPath = false, ?string $watermarkPath = ''): ?string
    {
        if (!$imgId) {
            return null;
        }

        $arWatermark = null;
        if ($watermarkPath) {
            $arWatermark = [
                'name' => 'watermark',
                'position' => 'center',
                'type' => 'file',
                'size' => 'medium',
                'precision' => 0,
                'alpha_level' => 80,
                'file' => $watermarkPath,
            ];
        }

        $file = CFile::ResizeImageGet(
            $imgId,
            [
                'width' => $width,
                'height' => $height
            ],
            BX_RESIZE_IMAGE_PROPORTIONAL,
            true,
            $arWatermark ? [$arWatermark] : null,
            false,
            $compression
        );

        $link = $file['src'];

        if (strpos($link, 'http') === false && $fullPath) {
            $protocol = $_SERVER['PROTOCOL'] = (!empty($_SERVER['HTTPS']) || $_SERVER["SERVER_PORT"] == 443) ? 'https' : 'http';
            $link = $protocol . '://' . SITE_SERVER_NAME . $link;
        }

        return $link;
    }
}
