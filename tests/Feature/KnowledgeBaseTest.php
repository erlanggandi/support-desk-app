<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Category;
use App\Models\KnowledgeArticle;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KnowledgeBaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_published_articles_are_listed_publicly(): void
    {
        $category = Category::create(['name' => 'Jaringan', 'status' => 'active']);
        $published = KnowledgeArticle::create(['title' => 'Cara Reset WiFi', 'category_id' => $category->id, 'content' => 'Langkah reset.', 'status' => 'published', 'published_at' => now()]);
        KnowledgeArticle::create(['title' => 'Draft VPN', 'content' => 'Belum jadi.', 'status' => 'draft']);
        KnowledgeArticle::create(['title' => 'Arsip Printer', 'content' => 'Lama.', 'status' => 'archived']);

        $response = $this->get('/knowledge-base');

        $response->assertInertia(
            fn ($page) => $page
                ->component('public/kb/index')
                ->where('articles.0.title', $published->title)
                ->missing('articles.1')
        );
    }

    public function test_search_filters_articles_by_title(): void
    {
        KnowledgeArticle::create(['title' => 'Reset Password Windows', 'content' => 'x', 'status' => 'published', 'published_at' => now()]);
        KnowledgeArticle::create(['title' => 'Pasang Printer', 'content' => 'x', 'status' => 'published', 'published_at' => now()]);

        $response = $this->get('/knowledge-base?q=password');

        $response->assertInertia(
            fn ($page) => $page
                ->where('q', 'password')
                ->where('articles.0.title', 'Reset Password Windows')
                ->missing('articles.1')
        );
    }

    public function test_show_increments_views_and_draft_returns_404(): void
    {
        $published = KnowledgeArticle::create(['title' => 'Panduan Email', 'content' => 'Isi.', 'status' => 'published', 'published_at' => now()]);
        $draft = KnowledgeArticle::create(['title' => 'Draft Rahasia', 'content' => 'Isi.', 'status' => 'draft']);

        $this->get("/knowledge-base/{$published->slug}")->assertOk();
        $this->assertSame(1, $published->fresh()->views);

        $this->get("/knowledge-base/{$draft->slug}")->assertNotFound();
    }

    public function test_admin_can_create_and_publish_article_with_audit(): void
    {
        $admin = User::factory()->create();
        $category = Category::create(['name' => 'Email', 'status' => 'active']);

        $response = $this->actingAs($admin)->post('/admin/knowledge-base', [
            'title' => 'Setup Outlook',
            'category_id' => $category->id,
            'content' => 'Langkah setup.',
            'status' => 'published',
        ]);

        $response->assertRedirect(route('admin.kb.index'));

        $article = KnowledgeArticle::first();
        $this->assertSame('setup-outlook', $article->slug);
        $this->assertSame('published', $article->status);
        $this->assertNotNull($article->published_at);

        $this->assertTrue(AuditLog::where('action', 'kb.published')->where('entity_id', $article->id)->exists());
    }

    public function test_admin_can_archive_and_delete_article(): void
    {
        $admin = User::factory()->create();
        $article = KnowledgeArticle::create(['title' => 'Lama', 'content' => 'x', 'status' => 'published', 'published_at' => now()]);

        $this->actingAs($admin)->put("/admin/knowledge-base/{$article->id}", [
            'title' => 'Lama',
            'content' => 'x',
            'status' => 'archived',
        ])->assertRedirect(route('admin.kb.index'));

        $this->assertSame('archived', $article->fresh()->status);

        $this->actingAs($admin)->delete("/admin/knowledge-base/{$article->id}")->assertRedirect();
        $this->assertSame(0, KnowledgeArticle::count());
    }
}
