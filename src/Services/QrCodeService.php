<?php

declare(strict_types=1);

namespace Spine\Services;

use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Storage;

/**
 * QrCodeService — QR code generator.
 *
 * Supports PNG (binary/base64), SVG, and data URI output.
 */
class QrCodeService
{
    /**
     * Generate a QR code as binary PNG.
     *
     * @param  array{content: string, size?: int, margin?: int}  $payload
     */
    public function png(array $payload): string
    {
        $qr = new QrCode(
            data: $payload['content'],
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: $this->errorCorrection((string) config('qrcode.error_correction', 'medium')),
            size: $payload['size'] ?? (int) config('qrcode.size', 300),
            margin: $payload['margin'] ?? (int) config('qrcode.margin', 10),
        );

        return (new PngWriter)->write($qr)->getString();
    }

    /**
     * Generate a QR code and return a PNG data URI (usable directly in <img src>).
     *
     * @param  array{content: string, size?: int, margin?: int}  $payload
     */
    public function dataUri(array $payload): string
    {
        return 'data:image/png;base64,'.base64_encode($this->png($payload));
    }

    /**
     * Generate a QR code and return a base64 string.
     *
     * @param  array{content: string, size?: int, margin?: int}  $payload
     */
    public function base64(array $payload): string
    {
        return base64_encode($this->png($payload));
    }

    /**
     * Store a QR PNG in storage and return its path.
     *
     * @param  array{content: string, filename?: string, dir?: string, size?: int, margin?: int}  $payload
     */
    public function store(array $payload): string
    {
        $disk = Storage::disk((string) config('qrcode.disk', 'public'));
        $dir = trim($payload['dir'] ?? 'qrcodes', '/');
        $filename = ($payload['filename'] ?? 'qr_'.bin2hex(random_bytes(4))).'.png';

        $disk->put($dir.'/'.$filename, $this->png($payload));

        return $dir.'/'.$filename;
    }

    /**
     * Convert an error correction level string (low/medium/quartile/high).
     */
    private function errorCorrection(string $level): ErrorCorrectionLevel
    {
        return match ($level) {
            'low' => ErrorCorrectionLevel::Low,
            'quartile' => ErrorCorrectionLevel::Quartile,
            'high' => ErrorCorrectionLevel::High,
            default => ErrorCorrectionLevel::Medium,
        };
    }
}
