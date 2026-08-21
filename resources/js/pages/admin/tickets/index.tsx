import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';

type TicketRow = {
    id: number;
    ticket_number: string | null;
    requester_name: string;
    description: string;
    status: string;
    status_label: string;
    priority: string | null;
    department: string | null;
    technician: string | null;
    created_at: string | null;
};

type Option = { id: number; name: string };

type Props = {
    tickets: { data: TicketRow[]; links: { url: string | null; label: string; active: boolean }[] };
    filters: { q: string; status: string; priority_id: number | null; department_id: number | null };
    statuses: Record<string, string>;
    priorities: Option[];
    departments: Option[];
};

const selectClass = 'rounded-md border border-slate-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none';

export default function TicketIndex({ tickets, filters, statuses, priorities, departments }: Props) {
    const [q, setQ] = useState(filters.q);

    const applyFilter = (key: string, value: string) => {
        router.get('/admin/tickets', { ...filters, q, [key]: value }, { preserveState: true });
    };

    return (
        <>
            <Head title="Tickets" />

            <h1 className="text-xl font-bold">Daftar Ticket</h1>

            <div className="mt-4 flex flex-wrap items-center gap-2">
                <form
                    onSubmit={(e) => {
                        e.preventDefault();
                        applyFilter('q', q);
                    }}
                    className="flex gap-2"
                >
                    <input
                        value={q}
                        onChange={(e) => setQ(e.target.value)}
                        placeholder="Cari nomor / pemohon / deskripsi..."
                        className={`${selectClass} w-64`}
                    />
                    <button type="submit" className="rounded-md bg-blue-600 px-3 py-2 text-sm font-medium text-white hover:bg-blue-700">Cari</button>
                </form>

                <select value={filters.status} onChange={(e) => applyFilter('status', e.target.value)} className={selectClass}>
                    <option value="all">Semua Status</option>
                    {Object.entries(statuses).map(([value, label]) => (
                        <option key={value} value={value}>{label}</option>
                    ))}
                </select>

                <select
                    value={filters.priority_id ?? ''}
                    onChange={(e) => applyFilter('priority_id', e.target.value)}
                    className={selectClass}
                >
                    <option value="">Semua Prioritas</option>
                    {priorities.map((p) => (
                        <option key={p.id} value={p.id}>{p.name}</option>
                    ))}
                </select>

                <select
                    value={filters.department_id ?? ''}
                    onChange={(e) => applyFilter('department_id', e.target.value)}
                    className={selectClass}
                >
                    <option value="">Semua Departemen</option>
                    {departments.map((d) => (
                        <option key={d.id} value={d.id}>{d.name}</option>
                    ))}
                </select>
            </div>

            <div className="mt-4 overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
                <table className="w-full text-left text-sm">
                    <thead className="text-xs tracking-wide text-slate-500 uppercase">
                        <tr>
                            <th className="px-4 py-2">Ticket Number</th>
                            <th className="px-4 py-2">Pemohon</th>
                            <th className="px-4 py-2">Deskripsi</th>
                            <th className="px-4 py-2">Status</th>
                            <th className="px-4 py-2">Prioritas</th>
                            <th className="px-4 py-2">Departemen</th>
                            <th className="px-4 py-2">Teknisi</th>
                            <th className="px-4 py-2">Dibuat</th>
                        </tr>
                    </thead>
                    <tbody>
                        {tickets.data.map((t) => (
                            <tr key={t.id} className="border-t border-slate-100 hover:bg-slate-50">
                                <td className="px-4 py-2">
                                    <Link href={`/admin/tickets/${t.id}`} className="font-mono text-blue-600 hover:underline">
                                        {t.ticket_number}
                                    </Link>
                                </td>
                                <td className="px-4 py-2">{t.requester_name}</td>
                                <td className="max-w-xs px-4 py-2 text-slate-600">{t.description}</td>
                                <td className="px-4 py-2">{t.status_label}</td>
                                <td className="px-4 py-2">{t.priority ?? '—'}</td>
                                <td className="px-4 py-2">{t.department ?? '—'}</td>
                                <td className="px-4 py-2">{t.technician ?? '—'}</td>
                                <td className="px-4 py-2 whitespace-nowrap text-slate-500">{t.created_at}</td>
                            </tr>
                        ))}
                        {tickets.data.length === 0 && (
                            <tr><td colSpan={8} className="px-4 py-6 text-center text-slate-500">Tidak ada ticket yang cocok.</td></tr>
                        )}
                    </tbody>
                </table>
            </div>

            {tickets.links.length > 3 && (
                <div className="mt-4 flex flex-wrap gap-1">
                    {tickets.links.map((link, i) => (
                        <button
                            key={i}
                            disabled={!link.url}
                            onClick={() => link.url && router.get(link.url)}
                            className={`rounded-md border px-3 py-1.5 text-sm ${link.active ? 'border-blue-600 bg-blue-600 text-white' : 'border-slate-300 bg-white hover:bg-slate-50'} disabled:opacity-40`}
                            dangerouslySetInnerHTML={{ __html: link.label }}
                        />
                    ))}
                </div>
            )}
        </>
    );
}
