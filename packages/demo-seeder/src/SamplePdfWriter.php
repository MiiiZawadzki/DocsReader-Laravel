<?php

namespace DocsReader\DemoSeeder;

/**
 * Minimal PDF writer for seeded demo documents.
 */
final class SamplePdfWriter
{
    private const PAGE_WIDTH = 595;

    private const PAGE_HEIGHT = 842;

    private const MARGIN = 56;

    private const BODY_SIZE = 11;

    private const LINE_HEIGHT = 15;

    private const WRAP_COLUMNS = 82;

    /**
     * @param  string  $title
     * @param  array<int, array{heading: string, body: array<int, string>}>  $pages
     * @return string
     */
    public function render(string $title, array $pages): string
    {
        $objects = [];
        $kids = [];
        $pageCount = count($pages);

        foreach (array_values($pages) as $index => $page) {
            $pageObject = 5 + ($index * 2);
            $contentObject = $pageObject + 1;
            $kids[] = $pageObject . ' 0 R';

            $objects[$pageObject] = sprintf(
                '<</Type /Page /Parent 2 0 R /MediaBox [0 0 %d %d]'
                . ' /Resources <</Font <</F1 3 0 R /F2 4 0 R>>>> /Contents %d 0 R>>',
                self::PAGE_WIDTH,
                self::PAGE_HEIGHT,
                $contentObject
            );

            $stream = $this->pageStream($title, $page, $index + 1, $pageCount);
            $objects[$contentObject] = sprintf(
                "<</Length %d>>\nstream\n%s\nendstream",
                strlen($stream),
                $stream
            );
        }

        $objects[1] = '<</Type /Catalog /Pages 2 0 R>>';
        $objects[2] = sprintf('<</Type /Pages /Kids [%s] /Count %d>>', implode(' ', $kids), $pageCount);
        $objects[3] = '<</Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding>>';
        $objects[4] = '<</Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding>>';

        ksort($objects);

        return $this->assemble($objects);
    }

    /**
     * Lays the objects out in order, recording byte offsets for the xref table.
     *
     * @param  array<int, string>  $objects
     * @return string
     */
    private function assemble(array $objects): string
    {
        $pdf = "%PDF-1.4\n";
        $offsets = [];

        foreach ($objects as $number => $body) {
            $offsets[$number] = strlen($pdf);
            $pdf .= $number . " 0 obj\n" . $body . "\nendobj\n";
        }

        $startXref = strlen($pdf);
        $size = count($objects) + 1;

        $pdf .= "xref\n0 " . $size . "\n";
        $pdf .= sprintf("%010d %05d %s \n", 0, 65535, 'f');
        foreach ($offsets as $offset) {
            $pdf .= sprintf("%010d %05d %s \n", $offset, 0, 'n');
        }

        $pdf .= "trailer\n<</Size " . $size . " /Root 1 0 R>>\n";
        $pdf .= "startxref\n" . $startXref . "\n%%EOF\n";

        return $pdf;
    }

    /**
     * @param  string  $title
     * @param  array{heading: string, body: array<int, string>}  $page
     * @param  int  $number
     * @param  int  $total
     * @return string
     */
    private function pageStream(string $title, array $page, int $number, int $total): string
    {
        $ops = [];
        $y = self::PAGE_HEIGHT - self::MARGIN;

        $ops[] = sprintf('BT /F2 15 Tf %d %d Td (%s) Tj ET', self::MARGIN, $y, $this->escape($page['heading']));

        $y -= 12;
        $ops[] = sprintf(
            '0.6 w %d %d m %d %d l S',
            self::MARGIN,
            $y,
            self::PAGE_WIDTH - self::MARGIN,
            $y
        );

        $y -= 28;
        $ops[] = sprintf('BT /F1 %d Tf %d %d Td %d TL', self::BODY_SIZE, self::MARGIN, $y, self::LINE_HEIGHT);

        foreach ($this->wrap($page['body']) as $line) {
            $ops[] = '(' . $this->escape($line) . ') Tj T*';
        }

        $ops[] = 'ET';

        $footer = sprintf('%s  -  page %d of %d', $title, $number, $total);
        $ops[] = sprintf(
            'BT /F1 8 Tf 0.45 g %d %d Td (%s) Tj ET',
            self::MARGIN,
            self::MARGIN - 18,
            $this->escape($footer)
        );

        return implode("\n", $ops);
    }

    /**
     * @param  array<int, string>  $paragraphs
     * @return array<int, string>
     */
    private function wrap(array $paragraphs): array
    {
        $lines = [];

        foreach ($paragraphs as $paragraph) {
            foreach (explode("\n", wordwrap($paragraph, self::WRAP_COLUMNS, "\n")) as $line) {
                $lines[] = $line;
            }
            $lines[] = '';
        }

        return $lines;
    }

    /**
     * Strips anything outside printable ASCII so the WinAnsi fonts above stay
     * honest, then escapes the three characters PDF string literals reserve.
     *
     * @param  string  $text
     * @return string
     */
    private function escape(string $text): string
    {
        $ascii = preg_replace('/[^\x20-\x7E]/', '', $text) ?? '';

        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $ascii);
    }
}
