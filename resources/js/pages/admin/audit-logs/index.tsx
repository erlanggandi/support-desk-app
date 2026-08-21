import { Head, router } from '@inertiajs/react';

type Log = {
    id: number;
    actor: string | null;
    action: string;
    entity: string;
    entity_id: number | null;
    metadata: Record<string, unknown> | null;
    created_at: string | null;
};

type Props = {
    logs: { data: Log[]; links: { url: string | null; label: string; active: boolean }[] };
};

export default function AuditLogIndex({ logs }: Props) {
    return (
        <>
            <Head title="Audit Log" />

            <h1 className="text-xl font-bold">Audit Log</h1>
            <p className="mt-1 text-sm text-slate-600">Jejak aktivitas ticket, master data, artikel KB, dan autentikasi.</p>

            <div className="mt-4 overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
                <table className="w-full text-left text-sm">
                    <thead className="text-xs tracking-wide text-slate-500 uppercase">
                        <tr>
                            <th className="px-4 py-2">Waktu</th>
                            <th className="px-4 py-2">Aktor</th>
                            <th className="px-4 py-2">Aksi</th>
                            <th className="px-4 py-2">Entitas</th>
                            <th className="px-4 py-2">Detail</th>
                        </tr>
                    </thead>
                    <tbody>
                        {logs.data.map((log) => (
                            <tr key={log.id} className="border-t border-slate-100 hover:bg-slate-50">
                                <td className="px-4 py-2 whitespace-nowrap text-slate-500">{log.created_at}</td>
                                <td className="px-4 py-2">{log.actor ?? '—'}</td>
                                <td className="px-4 py-2 font-mono text-xs">{log.action}</td>
                                <td className="px-4 py-2">{log.entity}{log.entity_id ? ` #${log.entity_id}` : ''}</td>
                                <td className="max-w-sm px-4 py-2 font-mono text-xs break-all text-slate-500">
                                    {log.metadata ? JSON.stringify(log.metadata) : '—'}
                                </td>
                            </tr>
                        ))}
                        {logs.data.length === 0 && (
                            <tr><td colSpan={5} className="px-4 py-6 text-center text-slate-500">Belum ada aktivitas.</td></tr>
                        )}
                    </tbody>
                </table>
            </div>

            {logs.links.length > 3 && (
                <div className="mt-4 flex flex-wrap gap-1">
                    {logs.links.map((link, i) => (
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
