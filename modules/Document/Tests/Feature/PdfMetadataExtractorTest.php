<?php

namespace Modules\Document\Tests\Feature;

use Illuminate\Support\Facades\Storage;
use Modules\Document\Services\PdfMetadataExtractor;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

#[Group('feature')]
#[Group('Document')]
class PdfMetadataExtractorTest extends FeatureTestCase
{
    public function test_missing_file_returns_null(): void
    {
        Storage::fake('documents');

        $this->assertNull($this->extractor()->countPages('uploads/missing/file.pdf'));
    }

    public function test_unparseable_file_returns_null(): void
    {
        Storage::fake('documents');
        Storage::disk('documents')->put('uploads/bad/file.pdf', 'this is not a pdf');

        // Parser throws on garbage input; the extractor must degrade to null
        // rather than fail the upload.
        $this->assertNull($this->extractor()->countPages('uploads/bad/file.pdf'));
    }

    private function extractor(): PdfMetadataExtractor
    {
        return app(PdfMetadataExtractor::class);
    }
}
