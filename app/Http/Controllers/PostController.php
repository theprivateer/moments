<?php

namespace App\Http\Controllers;

use App\Actions\ResolvePostImageAction;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PostController extends Controller
{
    public function index(): View
    {
        $posts = Post::query()->latest()->get();

        return view('posts.index', compact('posts'));
    }

    public function store(StorePostRequest $request, ResolvePostImageAction $resolveImage): RedirectResponse
    {
        $validated = $request->validated();

        Post::create([
            'user_id' => $request->user()->id,
            'body' => $validated['body'],
            'image_path' => $resolveImage(null, $request->file('image')),
        ]);

        return redirect()->route('posts.index');
    }

    public function edit(Post $post): View
    {
        $this->authorize('update', $post);

        return view('posts.edit', compact('post'));
    }

    public function update(UpdatePostRequest $request, Post $post, ResolvePostImageAction $resolveImage): RedirectResponse
    {
        $this->authorize('update', $post);

        $validated = $request->validated();

        $post->update([
            'body' => $validated['body'],
            'image_path' => $resolveImage(
                $post->image_path,
                $request->file('image'),
                (bool) ($validated['remove_image'] ?? false),
            ),
        ]);

        return redirect()->route('posts.index');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $this->authorize('delete', $post);

        if ($post->image_path) {
            Storage::disk('public')->delete($post->image_path);
        }

        $post->delete();

        return redirect()->route('posts.index');
    }
}
