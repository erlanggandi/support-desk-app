import { Head, Link } from '@inertiajs/react';

type StatusCount = { label: string; total: number };

type Props = {
    kpi: {
        total: number;
        statuses: Record<string, StatusCount>;
        backlog: number;
        avg_response_minutes: number;
        avg_resolution_hours: number;
        overdue: number | null;
    };
    recentTickets: {
        id: number;
        ticket_number: string | null;
        requester_name: string;
        status_label: string;
        created_at: string | null;
    }[];
};

export default function Dashboard({ kpi, recentTickets }: Props) {
    const cards = [
        { title: 'Total Ticket', value: kpi.total },
        ...Object.values(kpi.statuses).map((s) => ({ title: s.label, value: s.total })),
        { title: 'Backlog (belum Closed)', value: kpi.backlog },
        { title: 'Rata-rata Respons (menit)', value: kpi.avg_response_minutes },
        { title: 'Rata-rata Resolusi (jam)', value: kpi.avg_resolution_hours },
        // Overdue menunggu SLA target [TBD] — tampil "—" tanpa keputusan bisnis.
        { title: 'Overdue SLA', value: kpi.overdue ?? '—' },
    ];

    return (
        <>
            <Head title="Dashboard" />

            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
                {cards.map((card) => (
                    <div key={card.title} className="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                        <div className="text-xs font-medium text-slate-500">{card.title}</div>
                        <div className="mt-1 text-2xl font-bold">{card.value}</div>
                    </div>
                ))}
            </div>

            <div className="mt-6 rounded-xl border border-slate-200 bg-white shadow-sm">
                <div className="flex items-center justify-between border-b border-slate-200 px-4 py-3">
                    <h2 className="font-semibold">Ticket Terbaru</h2>
                    <Link href="/admin/tickets" className="text-sm text-blue-600 hover:underline">Lihat semua</Link>
                </div>

                <table className="w-full text-left text-sm">
                    <thead className="text-xs tracking-wide text-slate-500 uppercase">
                        <tr>
                            <th className="px-4 py-2">Ticket Number</th>
                            <th className="px-4 py-2">Pemohon</th>
                            <th className="px-4 py-2">Status</th>
                            <th className="px-4 py-2">Dibuat</th>
                        </tr>
                    </thead>
                    <tbody>
                        {recentTickets.map((t) => (
                            <tr key={t.id} className="border-t border-slate-100 hover:bg-slate-50">
                                <td className="px-4 py-2">
                                    <Link href={`/admin/tickets/${t.id}`} className="font-mono text-blue-600 hover:underline">
                                        {t.ticket_number}
                                    </Link>
                                </td>
                                <td className="px-4 py-2">{t.requester_name}</td>
                                <td className="px-4 py-2">{t.status_label}</td>
                                <td className="px-4 py-2 text-slate-500">{t.created_at}</td>
                            </tr>
                        ))}
                        {recentTickets.length === 0 && (
                            <tr><td colSpan={4} className="px-4 py-6 text-center text-slate-500">Belum ada ticket.</td></tr>
                        )}
                    </tbody>
                </table>
            </div>
        </>
    );
}
