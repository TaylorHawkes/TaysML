<?php
namespace TaysML ;


class DataLoader{

    public const MAGIC_IMAGE = 0x00000803;
    public const MAGIC_LABEL = 0x00000801;
    public const IMAGE_ROWS = 28;
    public const IMAGE_COLS = 28;

    public function readImages(string $imagePath): array
    {
        $stream = fopen($imagePath, 'rb');

        if ($stream === false) {
            throw new InvalidArgumentException('Could not open file: '.$imagePath);
        }

        $images = [];
        try {
            $header = fread($stream, 16);
            $fields = unpack('Nmagic/Nsize/Nrows/Ncols', (string) $header);

            if ($fields['magic'] !== self::MAGIC_IMAGE) {
                throw new InvalidArgumentException('Invalid magic number: '.$imagePath);
            }

            if ($fields['rows'] != self::IMAGE_ROWS) {
                throw new InvalidArgumentException('Invalid number of image rows: '.$imagePath);
            }

            if ($fields['cols'] != self::IMAGE_COLS) {
                throw new InvalidArgumentException('Invalid number of image cols: '.$imagePath);
            }

            for ($i = 0; $i < $fields['size']; $i++) {
                $imageBytes = fread($stream, $fields['rows'] * $fields['cols']);

                // Convert to float between 0 and 1
                $images[] = array_map(function ($b) {
                    return $b / 255;
                }, array_values(unpack('C*', (string) $imageBytes)));
            }
        } finally {
            fclose($stream);
        }

        return $images;
    }

    public function readLabels(string $labelPath): array
    {
        $stream = fopen($labelPath, 'rb');

        if ($stream === false) {
            throw new InvalidArgumentException('Could not open file: '.$labelPath);
        }

        $labels = [];

        try {
            $header = fread($stream, 8);

            $fields = unpack('Nmagic/Nsize', (string) $header);

            if ($fields['magic'] !== self::MAGIC_LABEL) {
                throw new InvalidArgumentException('Invalid magic number: '.$labelPath);
            }

            $labels = fread($stream, $fields['size']);
        } finally {
            fclose($stream);
        }

        return array_values(unpack('C*', (string) $labels));
    }



}

