<?php

namespace App\Pdf;

use Barryvdh\DomPDF\PDF as BasePDF;
use Illuminate\Http\Response;

class CompatPdf extends BasePDF
{
    protected ?string $headerHtml = null;

    protected float $headerSpacing = 5;

    public function loadHTML(string $string, ?string $encoding = null): self
    {
        if ($this->headerHtml !== null) {
            $string = $this->injectHeader($string, $this->headerHtml, $this->headerSpacing);
        }

        return parent::loadHTML($string, $encoding);
    }

    public function setOptions(array $options, bool $mergeWithDefaults = false): self
    {
        $snappy = [
            'margin-top', 'margin-bottom', 'margin-left', 'margin-right',
            'marginTop', 'marginBottom', 'marginLeft', 'marginRight',
            'header-html', 'header-line', 'header-spacing',
            'enable-local-file-access', 'dpi', 'lowquality', 'zoom',
        ];

        if (isset($options['header-html'])) {
            $this->headerHtml = (string) $options['header-html'];
            unset($options['header-html']);
        }
        if (isset($options['header-spacing'])) {
            $this->headerSpacing = (float) $options['header-spacing'] * 10;
            unset($options['header-spacing']);
        }
        if (array_key_exists('header-line', $options)) {
            unset($options['header-line']);
        }
        if (array_key_exists('enable-local-file-access', $options)) {
            unset($options['enable-local-file-access']);
        }

        $dompdfKeys = [];
        foreach ($options as $k => $v) {
            if (in_array($k, $snappy, true)) {
                continue;
            }
            $dompdfKeys[$k] = $v;
        }

        if (! empty($dompdfKeys)) {
            parent::setOptions($dompdfKeys, $mergeWithDefaults);
        }

        return $this;
    }

    public function inline(string $filename = 'document.pdf'): Response
    {
        try {
            return $this->stream($filename);
        } catch (\Throwable $e) {
            if ($this->isExecutableMissing($e)) {
                return $this->htmlFallback($filename);
            }

            throw $e;
        }
    }

    public function stream($filename = 'document.pdf', array $options = []): Response
    {
        try {
            return parent::stream($filename, $options);
        } catch (\Throwable $e) {
            if ($this->isExecutableMissing($e)) {
                return $this->htmlFallback($filename);
            }

            throw $e;
        }
    }

    protected function isExecutableMissing(\Throwable $e): bool
    {
        $msg = strtolower($e->getMessage());

        return str_contains($msg, 'permission denied')
            || str_contains($msg, 'executable not found')
            || str_contains($msg, 'wkhtmltopdf')
            || str_contains($msg, 'sh: line')
            || (int) $e->getCode() === 126;
    }

    protected function htmlFallback(string $filename): Response
    {
        $html = $this->getDomPDF()->outputHtml();

        return new Response($html, 200, [
            'Content-Type' => 'text/html; charset=utf-8',
            'X-Pdf-Fallback' => '1',
            'Content-Disposition' => 'inline; filename="'.str_replace('.pdf', '.html', $filename).'"',
        ]);
    }

    protected function injectHeader(string $html, string $headerHtml, float $spacingPx): string
    {
        $header = $this->extractBody($headerHtml);
        $offset = max(0, 30 + (int) $spacingPx);
        $style = '<style>@page{margin:75.59px 75.59px 75.59px 94.48px}.pdf-page-header{position:fixed;top:-55px;left:0;right:0;height:50px}.pdf-page-content{padding-top:'.$offset.'px}</style>';
        $block = $style.'<header class="pdf-page-header">'.$header.'</header><main class="pdf-page-content">';

        if (preg_match('/<body([^>]*)>/i', $html, $match, PREG_OFFSET_CAPTURE)) {
            $start = $match[0][1] + strlen($match[0][0]);
            $html = substr($html, 0, $start).$block.substr($html, $start);

            return preg_replace('/<\/body>/i', '</main></body>', $html, 1) ?? $html;
        }

        return '<html><body>'.$block.$html.'</main></body></html>';
    }

    protected function extractBody(string $html): string
    {
        if (preg_match('/<body[^>]*>(.*)<\/body>/is', $html, $match)) {
            return $match[1];
        }

        return $html;
    }
}
