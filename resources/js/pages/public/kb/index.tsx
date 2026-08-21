import { Form, Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

type Article = {
    id: number;
    slug: string;
    title: string;
    category?: { name: string } | null;
    views: number;
    published_at: string | null;
};

type Props = {
    articles: Article[];
    q: string;
};

export default function KnowledgeBaseIndex({ articles, q }: Props) {
    return (
        <>
            <Head title="Knowledge Base" />

            <h1 className="text-2xl font-bold">Knowledge Base</h1>
            <p className="mt-1 text-sm text-slate-600">Panduan dan solusi mandiri untuk masalah TI yang umum.</p>

            <Form action="/knowledge-base" className="mt-4 flex max-w-md gap-2">
                {({ processing }) => (
                    <>
                        <Input name="q" defaultValue={q} placeholder="Cari artikel..." className="rounded-md border border-slate-300 px-3 py-2 text-sm" />
                        <Button type="submit" disabled={processing} variant="outline">Cari</Button>
                    </>
                )}
            </Form>

            {articles.length === 0 ? (
                <p className="mt-8 rounded-xl border border-dashed border-slate-300 bg-white p-8 text-center text-sm text-slate-500">
                    Belum ada artikel yang ditemukan.
                </p>
            ) : (
                <div className="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    {articles.map((article) => (
                        <Link
                            key={article.id}
                            href={`/knowledge-base/${article.slug}`}
                            className="flex flex-col rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-blue-300 hover:shadow"
                        >
                            {article.category && (
                                <span className="w-fit rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-medium text-blue-700">{article.category.name}</span>
                            )}
                            <h2 className="mt-3 font-semibold">{article.title}</h2>
                            <div className="mt-auto pt-3 text-xs text-slate-500">{article.views}x dilihat</div>
                        </Link>
                    ))}
                </div>
            )}
        </>
    );
}
