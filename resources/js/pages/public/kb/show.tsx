import { Head, Link } from '@inertiajs/react';

type Props = {
    article: {
        title: string;
        content: string;
        category: string | null;
        views: number;
        published_at: string | null;
    };
};

export default function KnowledgeBaseShow({ article }: Props) {
    return (
        <>
            <Head title={article.title} />

            <article className="mx-auto max-w-3xl rounded-xl border border-slate-200 bg-white p-8 shadow-sm">
                <Link href="/knowledge-base" className="text-sm text-blue-600 hover:underline">← Kembali ke Knowledge Base</Link>

                {article.category && (
                    <span className="mt-4 block w-fit rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-medium text-blue-700">{article.category}</span>
                )}

                <h1 className="mt-3 text-3xl font-bold">{article.title}</h1>
                <div className="mt-2 text-xs text-slate-500">
                    Dipublikasikan {article.published_at ?? '-'} · {article.views}x dilihat
                </div>

                <div className="mt-6 space-y-4 leading-relaxed text-slate-700">
                    {article.content.split(/\n{2,}/).map((paragraph, i) => (
                        <p key={i} className="whitespace-pre-line">{paragraph}</p>
                    ))}
                </div>
            </article>
        </>
    );
}
