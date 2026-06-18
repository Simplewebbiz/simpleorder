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
            'media'   => Media::orderByDesc('created_at')->get()->map(fn($m) => [
                'id'          => $m->id,
                'name'        => $m->name,
                'alt'         => $m->alt,
                'url'         => $m->permalink,
                'mime'        => $m->mime,
                'size'        => $m->size,
                'created_at'  => $m->created_at->toDateString(),
            ]),
            'logo_url' => Setting::get('logo_url'),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'file'   => 'required|file|mimes:jpeg,png,gif,webp,svg|max:5120',
            'alt'    => 'nullable|string|max:200',
            'folder' => 'nullable|string|in:general,logos,items,categories',
        ]);

        $file   = $request->file('file');
        $folder = $request->input('folder', 'general');
        $ext    = $file->getClientOriginalExtension();
        $name   = Str::uuid() . '.' . $ext;

        // Resize images > 2000px wide to keep file sizes sane
        if (in_array($ext, ['jpeg', 'jpg', 'png', 'webp', 'gif'])) {
            $image = Image::read($file)->scaleDown(width: 2000);
            Storage::disk('public')->put('media/' . $folder . '/' . $name, (string) $image->encode());
        } else {
            Storage::disk('public')->putFileAs('media/' . $folder, $file, $name);
        }

        $media = Media::create([
            'name'   => $file->getClientOriginalName(),
            'alt'    => $request->input('alt', ''),
            'src'    => 'media/' . $folder . '/' . $name,
            'mime'   => $file->getMimeType(),
            'size'   => $file->getSize(),
            'folder' => $folder,
        ]);

        // If this is a logo upload, save as the tenant logo setting
        if ($folder === 'logos') {
            Setting::set('logo_url', $media->permalink);
            Setting::set('logo_media_id', $media->id);
        }

        return response()->json([
            'id'   => $media->id,
            'url'  => $media->permalink,
            'name' => $media->name,
        ]);
    }

    public function destroy(Media $media)
    {
        Storage::disk('public')->delete($media->src);

        // Clear logo setting if this was the logo
        if (Setting::get('logo_media_id') == $media->id) {
            Setting::set('logo_url', null);
            Setting::set('logo_media_id', null);
        }

        $media->delete();

        return back()->with('success', 'File deleted.');
    }

    // Serve tenant media files
    public function serve(string $file)
    {
        $path = storage_path('app/public/media/' . $file);
        if (!file_exists($path)) abort(404);
        return response()->file($path);
    }
}
