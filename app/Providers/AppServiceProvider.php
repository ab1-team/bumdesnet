<?php

namespace App\Providers;

use App\Pdf\CompatPdf;
use Barryvdh\DomPDF\PDF as DomPDFBase;
use Illuminate\Support\ServiceProvider;
use Dompdf\Dompdf;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\View\Factory as ViewFactory;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind('dompdf.wrapper', function ($app) {
            return new CompatPdf(
                $app->make(Dompdf::class),
                $app->make(ConfigRepository::class),
                $app->make(Filesystem::class),
                $app->make(ViewFactory::class)
            );
        });

        $this->app->bind(DomPDFBase::class, function ($app) {
            return $app->make('dompdf.wrapper');
        });
    }

    public function boot(): void
    {
        //
    }
}
