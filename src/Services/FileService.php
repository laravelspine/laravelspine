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
     * Extensions accepted by storeUpload(), lowercased.
     *
     * @return array<int, string>
     */
    public function allowedExtensions(): array
    {
        return $this->normalizeExtensions(
            (array) config('spine.files.allowed_extensions', [])
        );
    }

    /**
     * Extensions always refused, regardless of the allow-list.
     *
     * @return array<int, string>
     */
    public function blockedExtensions(): array
    {
        return $this->normalizeExtensions(
            (array) config('spine.files.blocked_extensions', [])
        );
    }

    /**
     * Every dot-delimited suffix of a filename, lowercased.
     *
     * "report.final.pdf" yields ['final', 'pdf']; ".htaccess" yields
     * ['htaccess']; "README" yields []. All of them are checked, not just the
     * effective extension, so "invoice.php.pdf" is refused at the boundary
     * instead of relying on unique_filename() happening to rename it.
     *
     * @param string $filename
     * @return array<int, string>
     */
    public function extensionSegments(string $filename): array
    {
        $parts = explode('.', basename($filename));

        if (count($parts) < 2) {
            return [];
        }

        array_shift($parts); // the stem, not an extension

        $segments = [];
        foreach ($parts as $part) {
            $part = strtolower(trim($part));
            if ($part !== '') {
                $segments[] = $part;
            }
        }

        return $segments;
    }

    /**
     * Refuse an upload whose extension is not explicitly allowed.
     *
     * Default-deny. Throws ValidationException keyed on "file" so controllers
     * and Inertia surface it as a field error rather than a generic failure.
     *
     * Runs before Spine\Events\FileUploading is dispatched, so a module
     * listener can only tighten the policy, never widen it back open.
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function assertUploadAllowed(\Illuminate\Http\UploadedFile $file): void
    {
        $segments = $this->extensionSegments((string) $file->getClientOriginalName());
        $blocked = $this->blockedExtensions();

        foreach ($segments as $segment) {
            if (in_array($segment, $blocked, true)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'file' => sprintf('Files of type "%s" cannot be uploaded.', $this->safeExtension($segment)),
                ]);
            }
        }

        $effective = $segments === [] ? '' : $segments[count($segments) - 1];

        if ($effective === '') {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'file' => 'The file must have a file extension.',
            ]);
        }

        if (!in_array($effective, $this->allowedExtensions(), true)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'file' => sprintf('Files of type "%s" are not allowed.', $this->safeExtension($effective)),
            ]);
        }
    }

    /**
     * Lowercase, strip the dot, and drop anything that is not [a-z0-9].
     *
     * Keeps a client-supplied name out of validation messages verbatim.
     *
     * @param array<int, mixed> $extensions
     * @return array<int, string>
     */
    protected function normalizeExtensions(array $extensions): array
    {
        $normalized = [];

        foreach ($extensions as $extension) {
            if (!is_string($extension)) {
                continue;
            }

            $extension = strtolower(ltrim(trim($extension), '.'));
            $extension = preg_replace('/[^a-z0-9]/', '', $extension) ?? '';

            if ($extension !== '' && !in_array($extension, $normalized, true)) {
                $normalized[] = $extension;
            }
        }

        return $normalized;
    }

    /**
     * Make an extension safe to echo back to the client.
     *
     * @param string $extension
     * @return string
     */
    protected function safeExtension(string $extension): string
    {
        return preg_replace('/[^a-z0-9]/', '', strtolower($extension)) ?? '';
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
        $this->assertUploadAllowed($file);

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
