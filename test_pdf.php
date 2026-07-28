<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo class_exists('Barryvdh\\DomPDF\\PDF') ? 'dompdf_ok' : 'dompdf_MISSING';
echo PHP_EOL;
echo class_exists('Barryvdh\\Snappy\\Pdf') ? 'snappy_LEAK' : 'snappy_removed';
echo PHP_EOL;
echo class_exists('App\\Pdf\\CompatPdf') ? 'compat_ok' : 'compat_MISSING';
echo PHP_EOL;

$pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML(
    '<html><body><h1>Laporan Test</h1><p>Hello world</p></body></html>'
)->setOptions([
    'margin-top' => 20,
    'margin-bottom' => 15,
    'margin-left' => 25,
    'margin-right' => 20,
    'header-html' => '<div style="text-align:center"><b>HEADER</b></div>',
    'header-line' => true,
    'enable-local-file-access' => true,
]);
$pdf->setPaper('A4', 'portrait');

$out = $pdf->output();
file_put_contents(__DIR__ . '/storage/test_pelaporan.pdf', $out);
echo 'pdf size: ' . strlen($out) . ' bytes' . PHP_EOL;
