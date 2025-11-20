<?php

declare(strict_types=1);

namespace League\MimeTypeDetection;

/**
 * Minimal replacement for the generated mime type map normally shipped with the League package.
 * Covers the most common file types used in this project so Flysystem can still infer mime types.
 */
class GeneratedExtensionToMimeTypeMap implements ExtensionToMimeTypeMap, ExtensionLookup
{
    /**
     * @var array<string, string>
     */
    private const EXTENSION_TO_MIME = [
        'aac' => 'audio/aac',
        'avi' => 'video/x-msvideo',
        'bmp' => 'image/bmp',
        'csv' => 'text/csv',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'eps' => 'application/postscript',
        'gif' => 'image/gif',
        'gz' => 'application/gzip',
        'heic' => 'image/heic',
        'htm' => 'text/html',
        'html' => 'text/html',
        'ico' => 'image/vnd.microsoft.icon',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'json' => 'application/json',
        'mov' => 'video/quicktime',
        'mp3' => 'audio/mpeg',
        'mp4' => 'video/mp4',
        'oga' => 'audio/ogg',
        'ogg' => 'audio/ogg',
        'ogv' => 'video/ogg',
        'pdf' => 'application/pdf',
        'png' => 'image/png',
        'psd' => 'image/vnd.adobe.photoshop',
        'rar' => 'application/vnd.rar',
        'rtf' => 'application/rtf',
        'svg' => 'image/svg+xml',
        'tar' => 'application/x-tar',
        'tif' => 'image/tiff',
        'tiff' => 'image/tiff',
        'txt' => 'text/plain',
        'wav' => 'audio/wav',
        'webm' => 'video/webm',
        'webp' => 'image/webp',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'xml' => 'application/xml',
        'zip' => 'application/zip',
    ];

    /**
     * @var array<string, string[]>
     */
    private const MIME_TO_EXTENSIONS = [
        'application/gzip' => ['gz'],
        'application/json' => ['json'],
        'application/msword' => ['doc'],
        'application/pdf' => ['pdf'],
        'application/postscript' => ['eps'],
        'application/rtf' => ['rtf'],
        'application/vnd.adobe.photoshop' => ['psd'],
        'application/vnd.microsoft.icon' => ['ico'],
        'application/vnd.ms-excel' => ['xls'],
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => ['xlsx'],
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => ['docx'],
        'application/vnd.rar' => ['rar'],
        'application/x-tar' => ['tar'],
        'application/xml' => ['xml'],
        'application/zip' => ['zip'],
        'audio/aac' => ['aac'],
        'audio/mpeg' => ['mp3'],
        'audio/ogg' => ['oga', 'ogg'],
        'audio/wav' => ['wav'],
        'image/bmp' => ['bmp'],
        'image/gif' => ['gif'],
        'image/heic' => ['heic'],
        'image/jpeg' => ['jpg', 'jpeg'],
        'image/png' => ['png'],
        'image/svg+xml' => ['svg'],
        'image/tiff' => ['tif', 'tiff'],
        'image/vnd.adobe.photoshop' => ['psd'],
        'image/vnd.microsoft.icon' => ['ico'],
        'image/webp' => ['webp'],
        'text/csv' => ['csv'],
        'text/html' => ['html', 'htm'],
        'text/plain' => ['txt'],
        'video/mp4' => ['mp4'],
        'video/ogg' => ['ogv'],
        'video/quicktime' => ['mov'],
        'video/webm' => ['webm'],
        'video/x-msvideo' => ['avi'],
    ];

    public function lookupMimeType(string $extension): ?string
    {
        $extension = strtolower($extension);

        return self::EXTENSION_TO_MIME[$extension] ?? null;
    }

    public function lookupExtension(string $mimetype): ?string
    {
        $extensions = $this->lookupAllExtensions($mimetype);

        return $extensions[0] ?? null;
    }

    /**
     * @return string[]
     */
    public function lookupAllExtensions(string $mimetype): array
    {
        $mimetype = strtolower($mimetype);

        return self::MIME_TO_EXTENSIONS[$mimetype] ?? [];
    }
}
