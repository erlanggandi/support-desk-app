<?php

namespace App\Http\Controllers\PublicPortal;

use App\Http\Controllers\Controller;
use App\Models\KnowledgeArticle;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class KnowledgeBaseController extends Controller
{
    public function index(Request $request): Response
    {
        $q = trim((string) $request->string('q'));

        $articles = KnowledgeArticle::published()
            ->with('category:id,name')
            ->when($q !== '', fn ($query) => $query->where(fn ($w) => $w
                ->where('title', 'like', "%{$q}%")
                ->orWhere('content', 'like', "%{$q}%")))
            ->latest('published_at')
            ->get(['id', 'slug', 'title', 'category_id', 'views', 'published_at']);

        return Inertia::render('public/kb/index', [
            'articles' => $articles,
            'q' => $q,
        ]);
    }

    public function show(KnowledgeArticle $article): Response
    {
        abort_unless($article->status === 'published', 404);

        $article->increment('views');

        return Inertia::render('public/kb/show', [
            'article' => [
                'title' => $article->title,
                'content' => $article->content,
                'category' => $article->category?->name,
                'views' => $article->views + 1,
                'published_at' => $article->published_at?->toDateTimeString(),
            ],
        ]);
    }
}
