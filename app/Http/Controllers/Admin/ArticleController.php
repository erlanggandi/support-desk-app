<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\KnowledgeArticle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ArticleController extends Controller
{
    public function index(Request $request): Response
    {
        $q = trim((string) $request->string('q'));

        $articles = KnowledgeArticle::query()
            ->with('category:id,name')
            ->when($q !== '', fn ($query) => $query->where('title', 'like', "%{$q}%"))
            ->latest()
            ->get(['id', 'title', 'slug', 'category_id', 'status', 'views', 'published_at', 'updated_at']);

        return Inertia::render('admin/kb/index', [
            'articles' => $articles,
            'q' => $q,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/kb/form', [
            'article' => null,
            'categories' => Category::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        // published_at diisi saat artikel langsung dibuat berstatus published.
        if ($data['status'] === 'published') {
            $data['published_at'] = now();
        }

        $article = KnowledgeArticle::create($data);

        AuditLog::record($data['status'] === 'published' ? 'kb.published' : 'kb.created', 'knowledge_article', $article->id, ['title' => $article->title]);

        return redirect()->route('admin.kb.index');
    }

    public function edit(KnowledgeArticle $article): Response
    {
        return Inertia::render('admin/kb/form', [
            'article' => $article->only(['id', 'title', 'category_id', 'content', 'status']),
            'categories' => Category::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, KnowledgeArticle $article): RedirectResponse
    {
        $data = $this->validated($request);

        // published_at diisi saat pertama kali publish (FR-027).
        if (($data['status'] ?? null) === 'published' && $article->published_at === null) {
            $data['published_at'] = now();
        }

        $wasPublished = $article->status;
        $article->update($data);

        AuditLog::record(
            $wasPublished !== $article->status ? "kb.{$article->status}" : 'kb.updated',
            'knowledge_article',
            $article->id,
            ['title' => $article->title, 'status' => $article->status],
        );

        return redirect()->route('admin.kb.index');
    }

    public function destroy(KnowledgeArticle $article): RedirectResponse
    {
        AuditLog::record('kb.deleted', 'knowledge_article', $article->id, ['title' => $article->title]);
        $article->delete();

        return back();
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'content' => ['required', 'string'],
            'status' => ['required', 'in:draft,published,archived'],
        ]);
    }
}
