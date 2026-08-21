import { Form, Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';

type Option = { id: number; name: string };

type Props = {
    article: { id: number; title: string; category_id: number | null; content: string; status: string } | null;
    categories: Option[];
};

const inputClass = 'mt-1 w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none';

export default function KbForm({ article, categories }: Props) {
    const isEdit = article !== null;

    return (
        <>
            <Head title={isEdit ? 'Edit Artikel' : 'Artikel Baru'} />

            <div className="mx-auto max-w-3xl">
                <Link href="/admin/knowledge-base" className="text-sm text-blue-600 hover:underline">← Kembali</Link>
                <h1 className="mt-2 text-xl font-bold">{isEdit ? 'Edit Artikel' : 'Artikel Baru'}</h1>

                <Form
                    action={isEdit ? `/admin/knowledge-base/${article.id}` : '/admin/knowledge-base'}
                    method={isEdit ? 'put' : 'post'}
                    className="mt-4 space-y-4 rounded-xl border border-slate-200 bg-white p-6 shadow-sm"
                >
                    {({ processing, errors }) => (
                        <>
                            <div>
                                <label htmlFor="title" className="text-sm font-medium">Judul *</label>
                                <input id="title" name="title" required defaultValue={article?.title ?? ''} className={inputClass} />
                                {errors.title && <p className="mt-1 text-xs text-red-600">{errors.title}</p>}
                            </div>

                            <div className="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label htmlFor="category_id" className="text-sm font-medium">Kategori</label>
                                    <select id="category_id" name="category_id" defaultValue={article?.category_id ?? ''} className={inputClass}>
                                        <option value="">— Tanpa kategori —</option>
                                        {categories.map((c) => (
                                            <option key={c.id} value={c.id}>{c.name}</option>
                                        ))}
                                    </select>
                                    {errors.category_id && <p className="mt-1 text-xs text-red-600">{errors.category_id}</p>}
                                </div>
                                <div>
                                    <label htmlFor="status" className="text-sm font-medium">Status *</label>
                                    <select id="status" name="status" required defaultValue={article?.status ?? 'draft'} className={inputClass}>
                                        <option value="draft">Draft</option>
                                        <option value="published">Published</option>
                                        <option value="archived">Archived</option>
                                    </select>
                                    {errors.status && <p className="mt-1 text-xs text-red-600">{errors.status}</p>}
                                </div>
                            </div>

                            <div>
                                <label htmlFor="content" className="text-sm font-medium">Konten *</label>
                                <textarea id="content" name="content" required rows={12} defaultValue={article?.content ?? ''} className={inputClass} />
                                {errors.content && <p className="mt-1 text-xs text-red-600">{errors.content}</p>}
                            </div>

                            <div className="flex justify-end gap-2">
                                <Button type="button" variant="outline" asChild>
                                    <Link href="/admin/knowledge-base">Batal</Link>
                                </Button>
                                <Button type="submit" disabled={processing} className="bg-blue-600 hover:bg-blue-700">
                                    {processing ? 'Menyimpan...' : 'Simpan'}
                                </Button>
                            </div>
                        </>
                    )}
                </Form>
            </div>
        </>
    );
}
