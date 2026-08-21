import { Form, Head } from '@inertiajs/react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';

type TimelineStep = { label: string; at: string | null };
type Option = { id: number; name: string };

type TicketDetail = {
    id: number;
    ticket_number: string | null;
    tracking_code: string;
    requester_name: string;
    requester_contact: string | null;
    description: string;
    resolution_notes: string | null;
    status: string;
    status_label: string;
    next_statuses: { value: string; label: string }[];
    timeline: TimelineStep[];
    department: string | null;
    category: string | null;
    problem_type: string | null;
    priority: string | null;
    technician: string | null;
    created_at: string | null;
};

type Audit = {
    id: number;
    actor: string | null;
    action: string;
    metadata: Record<string, unknown> | null;
    created_at: string | null;
};

type Props = {
    ticket: TicketDetail;
    technicians: Option[];
    priorities: Option[];
    categories: Option[];
    problemTypes: (Option & { category_id: number })[];
    departments: Option[];
    audits: Audit[];
};

const selectClass = 'mt-1 w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none';

export default function TicketShow({ ticket, technicians, priorities, categories, problemTypes, departments, audits }: Props) {
    const [action, setAction] = useState<string | null>(null);

    return (
        <>
            <Head title={ticket.ticket_number ?? 'Ticket'} />

            <div className="flex flex-wrap items-center justify-between gap-2">
                <h1 className="font-mono text-xl font-bold">{ticket.ticket_number}</h1>
                <span className="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700">{ticket.status_label}</span>
            </div>

            <div className="mt-4 grid gap-4 lg:grid-cols-3">
                <div className="space-y-4 lg:col-span-2">
                    <div className="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                        <dl className="grid gap-x-6 gap-y-2 text-sm sm:grid-cols-2">
                            <div><dt className="text-slate-500">Pemohon</dt><dd className="font-medium">{ticket.requester_name}</dd></div>
                            <div><dt className="text-slate-500">Kontak</dt><dd>{ticket.requester_contact ?? '—'}</dd></div>
                            <div><dt className="text-slate-500">Departemen</dt><dd>{ticket.department ?? '—'}</dd></div>
                            <div><dt className="text-slate-500">Kategori</dt><dd>{ticket.category ?? '—'}</dd></div>
                            <div><dt className="text-slate-500">Tipe Masalah</dt><dd>{ticket.problem_type ?? '—'}</dd></div>
                            <div><dt className="text-slate-500">Prioritas</dt><dd>{ticket.priority ?? '—'}</dd></div>
                            <div><dt className="text-slate-500">Teknisi</dt><dd>{ticket.technician ?? '—'}</dd></div>
                            <div><dt className="text-slate-500">Dibuat</dt><dd>{ticket.created_at}</dd></div>
                        </dl>

                        <h3 className="mt-4 text-sm font-semibold text-slate-700">Deskripsi</h3>
                        <p className="mt-1 text-sm whitespace-pre-line text-slate-700">{ticket.description}</p>

                        {ticket.resolution_notes && (
                            <>
                                <h3 className="mt-4 text-sm font-semibold text-slate-700">Catatan Penyelesaian</h3>
                                <p className="mt-1 text-sm whitespace-pre-line text-slate-700">{ticket.resolution_notes}</p>
                            </>
                        )}
                    </div>

                    <div className="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                        <h3 className="text-sm font-semibold text-slate-700">Riwayat Status</h3>
                        <ol className="mt-3 space-y-3">
                            {ticket.timeline.map((step) => (
                                <li key={step.label} className="flex items-start gap-3">
                                    <span className={`mt-1 size-2.5 shrink-0 rounded-full ${step.at ? 'bg-green-500' : 'bg-slate-300'}`} />
                                    <div className="text-sm">
                                        <div className="font-medium">{step.label}</div>
                                        <div className="text-slate-500">{step.at ?? 'Belum'}</div>
                                    </div>
                                </li>
                            ))}
                        </ol>

                        <h3 className="mt-6 text-sm font-semibold text-slate-700">Audit Terakhir</h3>
                        <ul className="mt-2 space-y-2 text-xs text-slate-600">
                            {audits.map((a) => (
                                <li key={a.id} className="border-t border-slate-100 pt-2">
                                    <span className="font-mono font-medium">{a.action}</span> — {a.actor} · {a.created_at}
                                    {a.metadata && <div className="text-slate-400">{JSON.stringify(a.metadata)}</div>}
                                </li>
                            ))}
                            {audits.length === 0 && <li>Belum ada aktivitas.</li>}
                        </ul>
                    </div>
                </div>

                <div className="space-y-4">
                    <div className="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                        <h3 className="text-sm font-semibold text-slate-700">Aksi Status</h3>

                        {ticket.next_statuses.length === 0 ? (
                            <p className="mt-2 text-sm text-slate-500">Ticket sudah Closed — tidak ada transisi lagi.</p>
                        ) : (
                            <div className="mt-3 space-y-2">
                                {ticket.next_statuses.map((next) => (
                                    <Button
                                        key={next.value}
                                        variant={action === next.value ? 'default' : 'outline'}
                                        className={`w-full ${action === next.value ? 'bg-blue-600 hover:bg-blue-700' : ''}`}
                                        onClick={() => setAction(action === next.value ? null : next.value)}
                                    >
                                        {next.label}
                                    </Button>
                                ))}
                            </div>
                        )}

                        {action && (
                            <Form action={`/admin/tickets/${ticket.id}/transition`} method="post" className="mt-4 space-y-3 border-t border-slate-100 pt-4">
                                {({ processing, errors }) => (
                                    <>
                                        <input type="hidden" name="status" value={action} />

                                        {action === 'escalated' && (
                                            <div>
                                                <label htmlFor="technician_id" className="text-sm font-medium">Teknisi *</label>
                                                <select id="technician_id" name="technician_id" required defaultValue="" className={selectClass}>
                                                    <option value="" disabled>Pilih teknisi</option>
                                                    {technicians.map((t) => (
                                                        <option key={t.id} value={t.id}>{t.name}</option>
                                                    ))}
                                                </select>
                                                {errors.technician_id && <p className="mt-1 text-xs text-red-600">{errors.technician_id}</p>}
                                            </div>
                                        )}

                                        {action === 'resolved' && (
                                            <div>
                                                <label htmlFor="resolution_notes" className="text-sm font-medium">Catatan Penyelesaian *</label>
                                                <textarea id="resolution_notes" name="resolution_notes" required rows={4} maxLength={5000} className={selectClass} />
                                                {errors.resolution_notes && <p className="mt-1 text-xs text-red-600">{errors.resolution_notes}</p>}
                                            </div>
                                        )}

                                        <Button type="submit" disabled={processing} className="w-full bg-blue-600 hover:bg-blue-700">
                                            {processing ? 'Memproses...' : `Konfirmasi: ${ticket.next_statuses.find((s) => s.value === action)?.label}`}
                                        </Button>
                                    </>
                                )}
                            </Form>
                        )}
                    </div>

                    <div className="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                        <h3 className="text-sm font-semibold text-slate-700">Ubah Klasifikasi</h3>

                        <Form action={`/admin/tickets/${ticket.id}`} method="patch" className="mt-3 space-y-3">
                            {({ processing, errors }) => (
                                <>
                                    <div>
                                        <label htmlFor="priority_id" className="text-sm font-medium">Prioritas</label>
                                        <select id="priority_id" name="priority_id" defaultValue={ticket.priority_id ?? ''} className={selectClass}>
                                            <option value="">—</option>
                                            {priorities.map((p) => (
                                                <option key={p.id} value={p.id}>{p.name}</option>
                                            ))}
                                        </select>
                                        {errors.priority_id && <p className="mt-1 text-xs text-red-600">{errors.priority_id}</p>}
                                    </div>
                                    <div>
                                        <label htmlFor="category_id" className="text-sm font-medium">Kategori</label>
                                        <select id="category_id" name="category_id" defaultValue={ticket.category_id ?? ''} className={selectClass}>
                                            <option value="">—</option>
                                            {categories.map((c) => (
                                                <option key={c.id} value={c.id}>{c.name}</option>
                                            ))}
                                        </select>
                                        {errors.category_id && <p className="mt-1 text-xs text-red-600">{errors.category_id}</p>}
                                    </div>
                                    <div>
                                        <label htmlFor="problem_type_id" className="text-sm font-medium">Tipe Masalah</label>
                                        <select id="problem_type_id" name="problem_type_id" defaultValue={ticket.problem_type_id ?? ''} className={selectClass}>
                                            <option value="">—</option>
                                            {problemTypes.map((pt) => (
                                                <option key={pt.id} value={pt.id}>{pt.name}</option>
                                            ))}
                                        </select>
                                        {errors.problem_type_id && <p className="mt-1 text-xs text-red-600">{errors.problem_type_id}</p>}
                                    </div>
                                    <div>
                                        <label htmlFor="department_id" className="text-sm font-medium">Departemen</label>
                                        <select id="department_id" name="department_id" defaultValue={ticket.department_id ?? ''} className={selectClass}>
                                            <option value="">—</option>
                                            {departments.map((d) => (
                                                <option key={d.id} value={d.id}>{d.name}</option>
                                            ))}
                                        </select>
                                        {errors.department_id && <p className="mt-1 text-xs text-red-600">{errors.department_id}</p>}
                                    </div>
                                    <Button type="submit" disabled={processing} variant="outline" className="w-full">
                                        {processing ? 'Menyimpan...' : 'Simpan Perubahan'}
                                    </Button>
                                </>
                            )}
                        </Form>
                    </div>
                </div>
            </div>
        </>
    );
}
