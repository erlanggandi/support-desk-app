import { Form, Head } from '@inertiajs/react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type TimelineStep = { label: string; at: string | null };

type TrackedTicket = {
    ticket_number: string;
    status: string;
    status_label: string;
    requester_name: string;
    description: string;
    created_at: string | null;
    technician_name: string | null;
    resolution_notes: string | null;
    timeline: TimelineStep[];
};

type Props = {
    ticket?: TrackedTicket;
};

const fieldClass = 'mt-1 w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none';

export default function TrackTicket({ ticket }: Props) {
    return (
        <>
            <Head title="Lacak Ticket" />

            <div className="mx-auto max-w-2xl">
                <h1 className="text-2xl font-bold">Lacak Ticket</h1>
                <p className="mt-1 text-sm text-slate-600">Masukkan Ticket Number dan Tracking Code yang Anda terima.</p>

                <Form action="/track" method="post" className="mt-6 space-y-4 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    {({ processing, errors }) => (
                        <>
                            <div>
                                <Label htmlFor="ticket_number">Ticket Number</Label>
                                <Input id="ticket_number" name="ticket_number" required placeholder="TKT-20260821-0001" className={fieldClass} />
                                <InputError message={errors.ticket_number} />
                            </div>
                            <div>
                                <Label htmlFor="tracking_code">Tracking Code</Label>
                                <Input id="tracking_code" name="tracking_code" required className={fieldClass} />
                                <InputError message={errors.tracking} />
                            </div>
                            <Button type="submit" disabled={processing} className="w-full bg-blue-600 hover:bg-blue-700">
                                {processing ? 'Mencari...' : 'Lacak'}
                            </Button>
                        </>
                    )}
                </Form>

                {ticket && (
                    <div className="mt-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div className="flex flex-wrap items-center justify-between gap-2">
                            <h2 className="font-mono text-lg font-bold">{ticket.ticket_number}</h2>
                            <span className="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700">{ticket.status_label}</span>
                        </div>

                        <dl className="mt-4 grid gap-x-6 gap-y-2 text-sm sm:grid-cols-2">
                            <div><dt className="text-slate-500">Pemohon</dt><dd className="font-medium">{ticket.requester_name}</dd></div>
                            <div><dt className="text-slate-500">Teknisi</dt><dd className="font-medium">{ticket.technician_name ?? '—'}</dd></div>
                            <div className="sm:col-span-2"><dt className="text-slate-500">Deskripsi</dt><dd className="whitespace-pre-line">{ticket.description}</dd></div>
                            {ticket.resolution_notes && (
                                <div className="sm:col-span-2"><dt className="text-slate-500">Catatan Penyelesaian</dt><dd className="whitespace-pre-line">{ticket.resolution_notes}</dd></div>
                            )}
                        </dl>

                        <h3 className="mt-6 text-sm font-semibold text-slate-700">Riwayat Status</h3>
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
                    </div>
                )}
            </div>
        </>
    );
}
