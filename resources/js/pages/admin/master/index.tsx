import { Form, Head, router } from '@inertiajs/react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';

type Field = {
    name: string;
    label: string;
    type: 'text' | 'textarea' | 'number' | 'select';
    options?: Record<string, string>;
    options_from?: string;
};

type Item = Record<string, unknown> & {
    id: number;
    category?: { name: string } | null;
};

type Props = {
    entity: string;
    label: string;
    fields: Field[];
    items: Item[];
    options: Record<string, { id: number; name: string }[]>;
};

const inputClass = 'mt-1 w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none';

export default function MasterIndex({ entity, label, fields, items, options }: Props) {
    const [editing, setEditing] = useState<Item | null>(null);
    const [creating, setCreating] = useState(false);

    const close = () => {
        setEditing(null);
        setCreating(false);
    };

    const displayValue = (item: Item, field: Field) => {
        if (field.name === 'category_id') {
return item.category?.name ?? '—';
}

        if (field.type === 'select') {
return field.options?.[String(item[field.name])] ?? String(item[field.name] ?? '—');
}

        const value = item[field.name];

        return value === null || value === undefined || value === '' ? '—' : String(value);
    };

    const remove = (id: number) => {
        if (!window.confirm(`Hapus data ini? Tindakan tidak dapat dibatalkan.`)) {
return;
}

        router.delete(`/admin/master/${entity}/${id}`);
    };

    return (
        <>
            <Head title={label} />

            <div className="flex items-center justify-between">
                <h1 className="text-xl font-bold">Master Data — {label}</h1>
                <Button onClick={() => setCreating(true)} className="bg-blue-600 hover:bg-blue-700">+ Tambah</Button>
            </div>

            <div className="mt-4 overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
                <table className="w-full text-left text-sm">
                    <thead className="text-xs tracking-wide text-slate-500 uppercase">
                        <tr>
                            {fields.map((f) => (
                                <th key={f.name} className="px-4 py-2">{f.label}</th>
                            ))}
                            <th className="px-4 py-2 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        {items.map((item) => (
                            <tr key={item.id} className="border-t border-slate-100 hover:bg-slate-50">
                                {fields.map((f) => (
                                    <td key={f.name} className="px-4 py-2">{displayValue(item, f)}</td>
                                ))}
                                <td className="px-4 py-2 text-right whitespace-nowrap">
                                    <button onClick={() => setEditing(item)} className="mr-2 text-sm text-blue-600 hover:underline">Edit</button>
                                    <button onClick={() => remove(item.id)} className="text-sm text-red-600 hover:underline">Hapus</button>
                                </td>
                            </tr>
                        ))}
                        {items.length === 0 && (
                            <tr><td colSpan={fields.length + 1} className="px-4 py-6 text-center text-slate-500">Belum ada data.</td></tr>
                        )}
                    </tbody>
                </table>
            </div>

            {(creating || editing) && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" onClick={close}>
                    <div className="w-full max-w-md rounded-xl bg-white p-6 shadow-lg" onClick={(e) => e.stopPropagation()}>
                        <h2 className="text-lg font-semibold">{editing ? `Edit ${label}` : `Tambah ${label}`}</h2>

                        <Form
                            action={editing ? `/admin/master/${entity}/${editing.id}` : `/admin/master/${entity}`}
                            method={editing ? 'put' : 'post'}
                            onSubmit={() => setTimeout(close, 0)}
                            className="mt-4 space-y-3"
                        >
                            {({ processing, errors }) => (
                                <>
                                    {fields.map((field) => {
                                        const initial = editing ? editing[field.name] : '';
                                        const dynamic = field.options_from ? options[field.options_from] ?? [] : null;

                                        return (
                                            <div key={field.name}>
                                                <label htmlFor={`f-${field.name}`} className="text-sm font-medium">{field.label}</label>

                                                {field.type === 'textarea' ? (
                                                    <textarea id={`f-${field.name}`} name={field.name} defaultValue={String(initial ?? '')} rows={3} className={inputClass} />
                                                ) : field.type === 'select' && dynamic ? (
                                                    <select id={`f-${field.name}`} name={field.name} required defaultValue={String(initial ?? '')} className={inputClass}>
                                                        <option value="" disabled>Pilih...</option>
                                                        {dynamic.map((o) => (
                                                            <option key={o.id} value={o.id}>{o.name}</option>
                                                        ))}
                                                    </select>
                                                ) : field.type === 'select' ? (
                                                    <select id={`f-${field.name}`} name={field.name} required defaultValue={String(initial ?? '')} className={inputClass}>
                                                        {Object.entries(field.options ?? {}).map(([value, text]) => (
                                                            <option key={value} value={value}>{text}</option>
                                                        ))}
                                                    </select>
                                                ) : (
                                                    <input
                                                        id={`f-${field.name}`}
                                                        name={field.name}
                                                        type={field.type === 'number' ? 'number' : 'text'}
                                                        required={field.name === 'name'}
                                                        defaultValue={String(initial ?? '')}
                                                        className={inputClass}
                                                    />
                                                )}

                                                {errors[field.name] && <p className="mt-1 text-xs text-red-600">{errors[field.name]}</p>}
                                            </div>
                                        );
                                    })}

                                    <div className="flex justify-end gap-2 pt-2">
                                        <Button type="button" variant="outline" onClick={close}>Batal</Button>
                                        <Button type="submit" disabled={processing} className="bg-blue-600 hover:bg-blue-700">
                                            {processing ? 'Menyimpan...' : 'Simpan'}
                                        </Button>
                                    </div>
                                </>
                            )}
                        </Form>
                    </div>
                </div>
            )}
        </>
    );
}
