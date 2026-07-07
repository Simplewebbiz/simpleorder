<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant\{Media, Setting};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Intervention\Image\Laravel\Facades\Image;

class MediaController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Media/Index', [
            'media' => Media::orderByDesc('created_at')->get()->map(fn ($media) => [
                'id' => $media->id,
                'name' => $media->name,
                'alt' => $media->alt,
                'src' => $media->src,
                'folder' => $media->folder,
                'permalink' => $media->permalink,
                'url' => $media->permalink,
                'mime' => $media->mime,
                'size' => $media->size,
                'created_at' => $media->created_at->toDateString(),
            ]),
            'logo_url' => Setting::get('logo_url'),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:jpeg,jpg,png,gif,webp,svg|max:5120',
            'alt' => 'nullable|string|max:200',
            'folder' => 'nullable|string|in:general,logos,items,categories,pages,storefront',
        ]);

        $file = $request->file('file');
        $folder = $request->input('folder', 'general');
        $ext = strtolower($file->getClientOriginalExtension());
        $name = Str::uuid() . '.' . $ext;
        $path = 'media/' . $folder . '/' . $name;

        if (in_array($ext, ['jpeg', 'jpg', 'png', 'webp', 'gif'], true)) {
            $image = Image::read($file)->scaleDown(width: 2000);
            Storage::disk('public')->put($path, (string) $image->encode());
        } else {
            Storage::disk('public')->putFileAs('media/' . $folder, $file, $name);
        }

        $media = Media::create([
            'name' => $file->getClientOriginalName(),
            'alt' => $request->input('alt', ''),
            'src' => $path,
            'mime' => $file->getMimeType(),
            'size' => $file->getSize(),
            'folder' => $folder,
        ]);

        if ($folder === 'logos') {
            Setting::set('logo_url', $media->permalink);
            Setting::set('logo_media_id', $media->id);
        }

        return response()->json([
            'id' => $media->id,
            'url' => $media->permalink,
            'permalink' => $media->permalink,
            'name' => $media->name,
        ]);
    }

    public function destroy(Media $media)
    {
        Storage::disk('public')->delete($media->src);

        if (Setting::get('logo_media_id') == $media->id) {
            Setting::set('logo_url', null);
            Setting::set('logo_media_id', null);
        }

        $media->delete();

        return back()->with('success', 'File deleted.');
    }
}