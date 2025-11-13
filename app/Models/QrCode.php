<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class QrCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'file_path',
        'description',
        'payment_method_number',
    ];

    public function getImageDataAttribute(): ?string
    {
        $disk = Storage::disk('public');
        if (!$this->file_path || !$disk->exists($this->file_path)) {
            return null;
        }

        $mime = $disk->mimeType($this->file_path) ?? 'image/png';
        $content = $disk->get($this->file_path);

        return 'data:' . $mime . ';base64,' . base64_encode($content);
    }
}
