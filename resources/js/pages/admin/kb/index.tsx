import { Form, Head, Link, router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';

type Article = {
    id: number;
    title: string;
    slug: string;
    category?: { name: string } | null;
    status: string;
    views: number;
    published_at: string | null;
    updated_at: string | null;
};

type Props = {
    articles: Article[];
    q: string;
};

const statusBadge: Record<string, string> = {
    draft: 'bg-slate-100 text-slate-600',
    published: 'bg-green-100 text-green-700',
    archived: 'bg-orange-100 text-orange-700',
};

const statusLabel: Record<string, string> = {
    draft: 'Draft',
    published: 'Published',
    archived: 'Archived',
};

export default function KbIndex({ articles, q }: Props) {
    const remove = (id: number) => {
        if (!window.confirm('Hapus artikel ini? Tindakan tidak dapat dibatalkan.')) {
return;
}

        router.delete(`/admin/knowledge-base/${id}`);
    };

    return (
        <>
            <Head title="Knowledge Base" />

            <div className="flex flex-wrap items-center justify-between gap-2">
                <h1 className="text-xl font-bold">Kelola Knowledge Base</h1>
                <Link href="/admin/knowledge-base/create" className="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                    + Artikel Baru
                </Link>
            </div>

            <Form action="/admin/knowledge-base" className="mt-4 max-w-md">
                {({ processing }) => (
                    <div className="flex gap-2">
                        <input name="q" defaultValue={q} placeholder="Cari judul..." className="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none" />
                        <Button type="submit" disabled={processing} variant="outline">Cari</Button>
                    </div>
                )}
            </Form>

            <div className="mt-4 overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
                <table className="w-full text-left text-sm">
                    <thead className="text-xs tracking-wide text-slate-500 uppercase">
                        <tr>
                            <th className="px-4 py-2">Judul</th>
                            <th className="px-4 py-2">Kategori</th>
                            <th className="px-4 py-2">Status</th>
                            <th className="px-4 py-2">Dilihat</th>
                            <th className="px-4 py-2 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        {articles.map((a) => (
                            <tr key={a.id} className="border-t border-slate-100 hover:bg-slate-50">
                                <td className="px-4 py-2 font-medium">{a.title}</td>
                                <td className="px-4 py-2">{a.category?.name ?? '—'}</td>
                                <td className="px-4 py-2">
                                    <span className={`rounded-full px-2.5 py-0.5 text-xs font-medium ${statusBadge[a.status] ?? ''}`}>
                                        {statusLabel[a.status] ?? a.status}
                                    </span>
                                </td>
                                <td className="px-4 py-2">{a.views}</td>
                                <td className="px-4 py-2 text-right whitespace-nowrap">
                                    <Link href={`/admin/knowledge-base/${a.id}/edit`} className="mr-2 text-sm text-blue-600 hover:underline">Edit</Link>
                                    <button onClick={() => remove(a.id)} className="text-sm text-red-600 hover:underline">Hapus</button>
                                </td>
                            </tr>
                        ))}
                        {articles.length === 0 && (
                            <tr><td colSpan={5} className="px-4 py-6 text-center text-slate-500">Belum ada artikel.</td></tr>
                        )}
                    </tbody>
                </table>
            </div>

            <p className="mt-3 text-xs text-slate-500">
                Publish / Archive dilakukan dari halaman Edit melalui dropdown Status.
            </p>
        </>
    );
}
