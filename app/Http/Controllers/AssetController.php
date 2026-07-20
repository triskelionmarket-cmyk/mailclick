<?php

namespace Acelle\Http\Controllers;

class AssetController extends Controller
{
    public function userFiles($uid, $name)
    {
        // Do not use $user->getAssetsPath($name), avoid one SQL query!
        $path = storage_path('app/users/' . $uid . '/home/files/' . $name);
        $mime_type = \Acelle\Library\File::getFileType($path);
        if (\Illuminate\Support\Facades\File::exists($path)) {
            return response()->file($path, array('Content-Type' => $mime_type));
        } else {
            abort(404);
        }
    }

    public function userThumbs($uid, $name)
    {
        // Do not use $user->getThumbsPath($name), avoid one SQL query!
        $path = storage_path('app/users/' . $uid . '/home/thumbs/' . $name);
        if (\Illuminate\Support\Facades\File::exists($path)) {
            $mime_type = \Acelle\Library\File::getFileType($path);
            return response()->file($path, array('Content-Type' => $mime_type));
        } else {
            abort(404);
        }
    }

    public function publicAssetsDeprecated($token)
    {
        // Notice $path should be relative only for acellemail/storage/ folder
        // For example, with a real path of /home/deploy/acellemail/storage/app/sub/example.png => $path = "app/sub/example.png"
        $decodedPath = \Acelle\Library\StringHelper::base64UrlDecode($token);
        $absPath = storage_path($decodedPath);

        if (\Illuminate\Support\Facades\File::exists($absPath)) {
            $mime_type = \Acelle\Library\File::getFileType($absPath);
            return response()->file($absPath, array(
                'Content-Type' => $mime_type,
                'Content-Length' => filesize($absPath),
            ));
        } else {
            abort(404);
        }
    }

    public function publicAssets($dirname, $basename)
    {
        $dirname = \Acelle\Library\StringHelper::base64UrlDecode($dirname);
        $absPath = storage_path(join_paths($dirname, $basename));

        if (\Illuminate\Support\Facades\File::exists($absPath)) {
            $mimetype = \Acelle\Library\File::getFileType($absPath);
            return response()->file($absPath, array(
                'Content-Type' => $mimetype,
                'Content-Length' => filesize($absPath),
            ));
        } else {
            abort(404);
        }
    }

    public function download($name)
    {
        $path = storage_path('app/download/' . $name);
        if (\Illuminate\Support\Facades\File::exists($path)) {
            return response()->download($path);
        } else {
            abort(404);
        }
    }
}
