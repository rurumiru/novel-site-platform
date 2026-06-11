<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogLivewireUploads
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!str_contains($request->path(), 'livewire/upload-file')) {
            return $next($request);
        }

        $reqInfo = [];
        try {
            $files = $request->allFiles();
            foreach ($files as $key => $entry) {
                $list = is_array($entry) ? $entry : [$entry];
                foreach ($list as $i => $f) {
                    $reqInfo[$key . '[' . $i . ']'] = [
                        'class'        => is_object($f) ? get_class($f) : gettype($f),
                        'orig_name'    => is_object($f) && method_exists($f, 'getClientOriginalName')   ? $f->getClientOriginalName()   : null,
                        'orig_ext'     => is_object($f) && method_exists($f, 'getClientOriginalExtension') ? $f->getClientOriginalExtension() : null,
                        'mime'         => is_object($f) && method_exists($f, 'getClientMimeType')       ? $f->getClientMimeType()       : null,
                        'size'         => is_object($f) && method_exists($f, 'getSize')                 ? @$f->getSize()                : null,
                        'is_valid'     => is_object($f) && method_exists($f, 'isValid')                 ? $f->isValid()                 : null,
                        'error_code'   => is_object($f) && method_exists($f, 'getError')                ? $f->getError()                : null,
                        'real_path'    => is_object($f) && method_exists($f, 'getRealPath')             ? @$f->getRealPath()            : null,
                        'real_exists'  => is_object($f) && method_exists($f, 'getRealPath') && @$f->getRealPath() ? @file_exists(@$f->getRealPath()) : null,
                    ];
                }
            }
        } catch (\Throwable $e) {
            $reqInfo['_error'] = $e->getMessage();
        }

        $response = $next($request);

        try {
            $body = method_exists($response, 'getContent') ? (string) $response->getContent() : '';
            \Log::error('LIVEWIRE UPLOAD DEBUG', [
                'request_files' => $reqInfo,
                'response_status' => $response->getStatusCode(),
                'response_body'   => mb_substr($body, 0, 2000),
                'response_length' => strlen($body),
                'upload_tmp_dir'  => ini_get('upload_tmp_dir') ?: 'default',
                'sys_tmp_dir'     => sys_get_temp_dir(),
                'storage_writable' => is_writable(storage_path('app/private/livewire-tmp')),
                'storage_path'    => storage_path('app/private/livewire-tmp'),
                'php_upload_max'  => ini_get('upload_max_filesize'),
                'php_post_max'    => ini_get('post_max_size'),
            ]);
        } catch (\Throwable $e) {
            \Log::error('LIVEWIRE UPLOAD DEBUG: logging failed: ' . $e->getMessage());
        }

        return $response;
    }
}
