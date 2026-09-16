<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ValidateFileUploads
{
    // Types MIME autorisés par champ
    const ALLOWED = [
        'image'        => ['image/jpeg','image/png','image/webp','image/gif'],
        'avatar'       => ['image/jpeg','image/png','image/webp'],
        'attachment'   => ['image/jpeg','image/png','image/webp','application/pdf'],
        'document'       => ['image/jpeg','image/png','application/pdf'],
        'id_document'  => ['image/jpeg','image/png','application/pdf'],
        'audio'        => ['audio/webm','audio/ogg','audio/mpeg','audio/mp4','audio/wav','audio/x-m4a','audio/mpga','video/mp4'],
    ];

    const MAX_SIZE = 5 * 1024 * 1024; // 5 Mo

    public function handle(Request $request, Closure $next)
    {
        foreach ($request->allFiles() as $field => $file) {
            $files = is_array($file) ? $file : [$file];

            foreach ($files as $f) {
                // Vérifier la taille
                if ($f->getSize() > self::MAX_SIZE) {
                    return back()->withErrors([
                        $field => 'Le fichier est trop volumineux (max 5 Mo).',
                    ]);
                }

                // Vérifier le MIME réel (pas juste l'extension)
                $realMime   = $f->getMimeType();
                $allowedKey = array_key_exists($field, self::ALLOWED) ? $field : 'image';
                $allowed    = self::ALLOWED[$allowedKey];

                if (!in_array($realMime, $allowed)) {
                    Log::warning("Upload suspect bloqué", [
                        'field'    => $field,
                        'mime'     => $realMime,
                        'ip'       => $request->ip(),
                        'filename' => $f->getClientOriginalName(),
                    ]);

                    return back()->withErrors([
                        $field => "Type de fichier non autorisé ({$realMime}).",
                    ]);
                }

                // Vérifier que l'extension correspond au MIME (anti spoofing)
                $ext = strtolower($f->getClientOriginalExtension());
                $safeExts = ['jpg','jpeg','png','webp','gif','pdf','webm','ogg','mp3','mp4','wav','m4a','mpga'];
                if (!in_array($ext, $safeExts)) {
                    return back()->withErrors([
                        $field => "Extension de fichier non autorisée.",
                    ]);
                }
            }
        }

        return $next($request);
    }
}