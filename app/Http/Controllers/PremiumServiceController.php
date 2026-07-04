<?php

namespace App\Http\Controllers;

use App\Support\ThemeHelper;

class PremiumServiceController extends Controller
{
    public function qrVideo()
    {
        return view(ThemeHelper::view('services.qr-video'));
    }

    public function arPreview()
    {
        return view(ThemeHelper::view('services.ar-preview'));
    }

    public function floralAssistant()
    {
        return view(ThemeHelper::view('services.floral-assistant'));
    }

    public function corporateEvents()
    {
        return view(ThemeHelper::view('services.corporate-events'));
    }
}
