<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    public function serve(string $file)
    {
        $path = 'public/' . $file;

        if (!Storage::exists($path)) {
            abort(404);
        }

        return response()->file(Storage::path($path));
    }
}
