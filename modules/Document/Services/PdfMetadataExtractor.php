<?php

namespace Modules\Document\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser;
use Throwable;

class PdfMetadataExtractor
{
    /**
     *  Count pages in a PDF stored on the documents' disk
     *
     *  Returns null when the file cannot be parsed (corrupted, encrypted, missing)
     *
     * @param  string  $relativePath
     * @return int|null
     */
    public function countPages(string $relativePath): ?int
    {
        try {
            $disk = Storage::disk('documents');
            $normalized = ltrim($relativePath, '/');

            if (! $disk->exists($normalized)) {
                return null;
            }

            $parser = new Parser;
            $pdf = $parser->parseFile($disk->path($normalized));
            $count = count($pdf->getPages());

            return $count > 0 ? $count : null;
        } catch (Throwable $e) {
            Log::warning('PDF page count extraction failed', [
                'path' => $relativePath,
                'exception' => $e::class,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
