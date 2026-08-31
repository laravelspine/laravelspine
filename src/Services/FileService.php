<?php

declare(strict_types=1);

namespace Spine\Services;

/**
 * FileService — file helpers.
 *
 * Adopted functions:
 *   - bytesToSize → bytes_to_size
 *   - file_upload_max_size
 *   - parse_size
 *   - is_image
 *   - get_file_extension
 *   - sanitize_file_name
 *   - unique_filename
 *
 */
class FileService
{
    /**
     * Format bytes into a human-readable string (e.g. "1.5 MB").
     *
     * @param int $bytes
     * @param int $precision
     * @return string
     */
    public function bytes_to_size(int $bytes, int $precision = 2): string
    {
        if ($bytes < 0) {
            $bytes = 0;
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * Get the max upload size from PHP config (in bytes).
     *
     * @return int
     */
    public function file_upload_max_size(): int
    {
        $max = ini_get('upload_max_filesize');
        $max = $this->parse_size($max);

        $post = ini_get('post_max_size');
        $post = $this->parse_size($post);

        return min($max, $post);
    }

    /**
     * Parse a size string (e.g. "2M", "512K") into bytes.
     *
     * @param string $size
     * @return int
     */
    public function parse_size(string $size): int
    {
        $size = trim($size);

        if (ctype_digit($size)) {
            return (int) $size;
        }

        $unit = strtoupper(substr($size, -1));
        $value = (float) substr($size, 0, -1);

        return match ($unit) {
            'P' => (int) ($value * 1024 * 1024 * 1024 * 1024),
            'T' => (int) ($value * 1024 * 1024 * 1024 * 1024 * 1024),
            'G' => (int) ($value * 1024 * 1024 * 1024),
            'M' => (int) ($value * 1024 * 1024),
            'K' => (int) ($value * 1024),
            default => (int) $value,
        };
    }

    /**
     * Whether the file is an image (based on extension).
     *
     * @param string $filename filename or path
     * @return bool
     */
    public function is_image(string $filename): bool
    {
        $ext = strtolower($this->get_file_extension($filename));
        return in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg', 'ico', 'tiff', 'tif'], true);
    }

    /**
     * Get the file extension from a name/path.
     *
     * @param string $filename
     * @return string lowercase without the dot
     */
    public function get_file_extension(string $filename): string
    {
        $pathinfo = pathinfo($filename);
        return strtolower($pathinfo['extension'] ?? '');
    }

    /**
     * Sanitize a file name: remove dangerous characters, spaces → underscore.
     *
     * @param string $filename
     * @return string
     */
    public function sanitize_file_name(string $filename): string
    {
        $filename = basename($filename);
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
        $filename = preg_replace('/_{2,}/', '_', $filename);
        $filename = trim($filename, '_ .');
        return $filename;
    }

    /**
     * Generate a unique file name using a timestamp + random string.
     *
     * @param string $originalName or extension
     * @param string $prefix optional
     * @return string
     */
    public function unique_filename(string $originalName = '', string $prefix = ''): string
    {
        $ext = $this->get_file_extension($originalName);

        $base = $prefix;
        if ($base !== '') {
            $base .= '_' . strtolower(
                preg_replace('/[^a-zA-Z0-9]+/', '-', pathinfo($originalName, PATHINFO_FILENAME) ?: 'file')
            );
        }

        $unique = bin2hex(random_bytes(4)) . '_' . time();

        if ($ext !== '') {
            return ($base !== '' ? $base . '_' : '') . $unique . '.' . $ext;
        }

        return ($base !== '' ? $base . '_' : '') . $unique;
    }

    /**
     * Store an uploaded file to disk (Laravel Storage), per-tenant path.
     *
     * Standard Laravel pattern with an uploads/{rel_type}/{rel_id}/ directory
     * structure. Physical files go to storage; metadata is recorded by the
     * caller (Attachment model).
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param string $relType  e.g. 'invoice'
     * @param int    $relId
     * @param int|null $tenantId
     * @param string $disk     'local' (private) | 'public'
     * @return string relative path returned by store()
     */
    public function storeUpload(
        \Illuminate\Http\UploadedFile $file,
        string $relType,
        int $relId,
        ?int $tenantId = null,
        string $disk = 'local'
    ): string {
        \Spine\Events\FileUploading::dispatch($file, $relType, $relId, $tenantId, $disk);

        $dir = 'tenants/' . ($tenantId ?? 'global') . '/' . $relType . '/' . $relId;
        $name = $this->unique_filename($file->getClientOriginalName());

        $path = $file->storeAs($dir, $name, $disk);

        \Spine\Events\FileUploaded::dispatch($path, $relType, $relId, $tenantId, $disk);

        return $path;
    }

    /**
     * Delete an attachment: dispatch FileDeleting (veto point), remove the
     * physical file, delete the metadata row, then dispatch FileDeleted.
     *
     * @param \Spine\Models\Attachment $attachment
     * @return void
     */
    public function deleteUpload(\Spine\Models\Attachment $attachment): void
    {
        \Spine\Events\FileDeleting::dispatch($attachment);

        \Illuminate\Support\Facades\Storage::disk($attachment->disk)->delete($attachment->path);
        $attachment->delete();

        \Spine\Events\FileDeleted::dispatch($attachment);
    }

    /**
     * Build a download/inline response for an attachment.
     *
     * @param \Spine\Models\Attachment $attachment
     * @param bool $inline true=preview (image), false=force download
     */
    public function downloadResponse(\Spine\Models\Attachment $attachment, bool $inline = false): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $storage = \Illuminate\Support\Facades\Storage::disk($attachment->disk);
        $fullPath = $storage->path($attachment->path);

        $disposition = $inline ? 'inline' : 'attachment';
        $filename = $attachment->original_name;

        return response()->file($fullPath, [
            'Content-Type' => $attachment->mime_type ?? 'application/octet-stream',
            'Content-Disposition' => $disposition . '; filename="' . $filename . '"',
        ]);
    }
}
