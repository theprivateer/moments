<?php

namespace App\Http\Controllers;

use GuzzleHttp\Psr7\HttpFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Glide\Filesystem\FileNotFoundException;
use League\Glide\Responses\PsrResponseFactory;
use League\Glide\ServerFactory;
use League\Glide\Signatures\SignatureException;
use League\Glide\Signatures\SignatureFactory;

class GlideController extends Controller
{
    public function __invoke(Request $request, string $path): Response
    {
        try {
            SignatureFactory::create(config('moments.glide_sign_key'))
                ->validateRequest('/img/'.$path, $request->query());
        } catch (SignatureException) {
            abort(403);
        }

        $disk = $request->query('disk', config('moments.image_disk'));

        $psrFactory = new HttpFactory;
        $server = ServerFactory::create([
            'source' => Storage::disk($disk)->getDriver(),
            'cache' => new Filesystem(new LocalFilesystemAdapter(storage_path('framework/cache/glide'))),
            'response' => new PsrResponseFactory(
                new \GuzzleHttp\Psr7\Response,
                fn ($stream) => $psrFactory->createStreamFromResource($stream),
            ),
        ]);

        try {
            $psrResponse = $server->getImageResponse($path, $request->except('disk'));
        } catch (FileNotFoundException) {
            abort(404);
        }

        $laravelResponse = response((string) $psrResponse->getBody(), $psrResponse->getStatusCode());

        foreach ($psrResponse->getHeaders() as $name => $values) {
            $laravelResponse->header($name, implode(', ', $values));
        }

        return $laravelResponse;
    }
}
