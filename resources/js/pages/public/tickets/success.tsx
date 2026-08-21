import { Head, Link } from '@inertiajs/react';

type Props = {
    createdTicket: { ticket_number: string; tracking_code: string };
};

export default function TicketSuccess({ createdTicket }: Props) {
    return (
        <>
            <Head title="Ticket Dibuat" />

            <div className="mx-auto max-w-xl rounded-xl border border-green-200 bg-white p-8 text-center shadow-sm">
                <div className="mx-auto flex size-14 items-center justify-center rounded-full bg-green-100 text-3xl">✓</div>
                <h1 className="mt-4 text-2xl font-bold text-green-700">Ticket Berhasil Dibuat</h1>
                <p className="mt-2 text-sm text-slate-600">Simpan kredensial berikut untuk melacak progres ticket Anda.</p>

                <div className="mt-6 space-y-3 text-left">
                    <div className="rounded-lg bg-slate-50 p-4">
                        <div className="text-xs font-medium tracking-wide text-slate-500 uppercase">Ticket Number</div>
                        <div className="mt-1 font-mono text-lg font-bold">{createdTicket.ticket_number}</div>
                    </div>
                    <div className="rounded-lg bg-slate-50 p-4">
                        <div className="text-xs font-medium tracking-wide text-slate-500 uppercase">Tracking Code</div>
                        <div className="mt-1 font-mono text-sm break-all font-bold">{createdTicket.tracking_code}</div>
                    </div>
                </div>

                <Link
                    href="/track"
                    className="mt-6 inline-flex items-center justify-center rounded-md bg-blue-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-blue-700"
                >
                    Lacak Ticket Ini
                </Link>
            </div>
        </>
    );
}
