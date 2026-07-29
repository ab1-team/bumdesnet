<?php

namespace App\Pdf;

use Barryvdh\DomPDF\PDF as BasePDF;
use Illuminate\Http\Response;

/**
 * Snappy → DomPDF bridge.
 * Accepts Snappy option keys (margin-*, orientation, header-html, …)
 * and maps them to DomPDF (@page CSS + setPaper).
 */
class CompatPdf extends BasePDF
{
    protected ?string $headerHtml = null;

    protected float $headerSpacing = 5;

    /** @var array{top: float, right: float, bottom: float, left: float} mm */
    protected array $margins = [
        'top' => 20,
        'right' => 20,
        'bottom' => 15,
        'left' => 25,
    ];

    protected string $paper = 'a4';

    protected string $orientation = 'portrait';

    /** Raw HTML before chrome injection — allows setOptions after loadHTML. */
    protected ?string $rawHtml = null;

    protected ?string $encoding = null;

    /** DomPDF Options keys only. */
    protected array $dompdfOptionKeys = [
        'font_dir', 'font_cache', 'temp_dir', 'chroot', 'allowed_protocols',
        'artifactPathValidation', 'log_output_file', 'enable_font_subsetting',
        'pdf_backend', 'default_media_type', 'default_paper_size',
        'default_paper_orientation', 'default_font', 'dpi', 'font_height_ratio',
        'enable_php', 'enable_javascript', 'enable_remote', 'allowed_remote_hosts',
        'enable_html5_parser', 'isRemoteEnabled', 'isHtml5ParserEnabled',
        'isFontSubsettingEnabled', 'isPhpEnabled', 'isJavascriptEnabled',
        'debugPng', 'debugKeepTemp', 'debugCss', 'debugLayout',
        'debugLayoutLines', 'debugLayoutBlocks', 'debugLayoutInline',
        'debugLayoutPaddingBox', 'isPdfAEnabled',
    ];

    public function loadHTML(string $string, ?string $encoding = null): self
    {
        $this->rawHtml = $string;
        $this->encoding = $encoding;

        return $this->reloadHtml();
    }

    public function setOptions(array $options, bool $mergeWithDefaults = false): self
    {
        if (isset($options['header-html'])) {
            $this->headerHtml = (string) $options['header-html'];
            unset($options['header-html']);
        }
        if (isset($options['header-spacing'])) {
            $this->headerSpacing = (float) $options['header-spacing'] * 10;
            unset($options['header-spacing']);
        }
        unset(
            $options['header-line'],
            $options['enable-local-file-access'],
            $options['lowquality'],
            $options['zoom']
        );

        $this->captureMargins($options);
        $this->capturePaper($options);

        $dompdfKeys = [];
        foreach ($options as $k => $v) {
            if (in_array($k, $this->dompdfOptionKeys, true)) {
                $dompdfKeys[$k] = $v;
            }
        }

        if (! empty($dompdfKeys)) {
            $defaults = app()->bound('dompdf.options') ? app()->make('dompdf.options') : [];
            // Always merge with package defaults — never wipe Options with a partial set.
            parent::setOptions(array_merge($defaults, $dompdfKeys), false);
        }

        // Re-apply chrome if HTML already loaded (call sites do loadHTML→setOptions).
        if ($this->rawHtml !== null) {
            $this->reloadHtml();
        }

        return $this;
    }

    public function setOrientation(string $orientation): self
    {
        $this->orientation = strtolower($orientation) === 'landscape' ? 'landscape' : 'portrait';
        $this->getDomPDF()->setPaper($this->paper, $this->orientation);

        return $this;
    }

    public function setPaper($paper, $orientation = 'portrait'): self
    {
        if (is_string($paper)) {
            $this->paper = $paper;
        }
        if (is_string($orientation) && $orientation !== '') {
            $this->orientation = strtolower($orientation) === 'landscape' ? 'landscape' : 'portrait';
        }

        // BasePDF has no setPaper method — it routes via __call to Dompdf.
        // parent::setPaper() would re-enter $this->setPaper → infinite loop.
        $this->getDomPDF()->setPaper($paper, $this->orientation);

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

    public function stream($filename = 'document.pdf'): Response
    {
        try {
            return parent::stream($filename);
        } catch (\Throwable $e) {
            if ($this->isExecutableMissing($e)) {
                return $this->htmlFallback($filename);
            }

            throw $e;
        }
    }

    protected function reloadHtml(): self
    {
        $string = $this->rawHtml ?? '';
        $string = $this->injectPageChrome($string);

        if ($this->headerHtml !== null) {
            $string = $this->injectHeader($string, $this->headerHtml, $this->headerSpacing);
        }

        $this->getDomPDF()->setPaper($this->paper, $this->orientation);

        return parent::loadHTML($string, $this->encoding);
    }

    protected function captureMargins(array &$options): void
    {
        $map = [
            'margin-top' => 'top', 'marginTop' => 'top',
            'margin-right' => 'right', 'marginRight' => 'right',
            'margin-bottom' => 'bottom', 'marginBottom' => 'bottom',
            'margin-left' => 'left', 'marginLeft' => 'left',
        ];

        foreach ($map as $key => $side) {
            if (array_key_exists($key, $options)) {
                $this->margins[$side] = (float) $options[$key];
                unset($options[$key]);
            }
        }
    }

    protected function capturePaper(array &$options): void
    {
        if (isset($options['orientation'])) {
            $this->orientation = strtolower((string) $options['orientation']) === 'landscape'
                ? 'landscape'
                : 'portrait';
            unset($options['orientation']);
        }
        if (isset($options['page-size'])) {
            $this->paper = (string) $options['page-size'];
            unset($options['page-size']);
        }
        if (isset($options['paper'])) {
            $this->paper = (string) $options['paper'];
            unset($options['paper']);
        }
    }

    /** Snappy margins (mm) → DomPDF @page. */
    protected function injectPageChrome(string $html): string
    {
        $t = $this->margins['top'];
        $r = $this->margins['right'];
        $b = $this->margins['bottom'];
        $l = $this->margins['left'];

        $style = "<style>@page{margin:{$t}mm {$r}mm {$b}mm {$l}mm}</style>";

        if (preg_match('/<head[^>]*>/i', $html, $m, PREG_OFFSET_CAPTURE)) {
            $pos = $m[0][1] + strlen($m[0][0]);

            return substr($html, 0, $pos).$style.substr($html, $pos);
        }

        if (preg_match('/<body[^>]*>/i', $html, $m, PREG_OFFSET_CAPTURE)) {
            return substr($html, 0, $m[0][1]).$style.substr($html, $m[0][1]);
        }

        return $style.$html;
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
        $dom = $this->getDomPDF();
        $html = method_exists($dom, 'outputHtml') ? $dom->outputHtml() : ($this->rawHtml ?? '');

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
        $style = '<style>.pdf-page-header{position:fixed;top:-55px;left:0;right:0;height:50px}.pdf-page-content{padding-top:'.$offset.'px}</style>';
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
