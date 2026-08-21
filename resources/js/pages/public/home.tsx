import { Head, Link } from '@inertiajs/react';
import { BookOpen, Search, Ticket } from 'lucide-react';

const cards = [
    {
        title: 'Buat Ticket',
        description: 'Ajukan keluhan atau permintaan layanan TI baru.',
        href: '/tickets/create',
        icon: Ticket,
        button: 'Buat Sekarang',
    },
    {
        title: 'Lacak Ticket',
        description: 'Pantau progres ticket dengan Ticket Number & Tracking Code.',
        href: '/track',
        icon: Search,
        button: 'Lacak',
    },
    {
        title: 'Knowledge Base',
        description: 'Temukan solusi mandiri dari artikel panduan TI.',
        href: '/knowledge-base',
        icon: BookOpen,
        button: 'Jelajahi',
    },
];

export default function Home() {
    return (
        <>
            <Head title="Beranda" />

            <section className="rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-700 px-6 py-16 text-center text-white">
                <h1 className="text-3xl font-bold sm:text-4xl">Layanan Tiket Bantuan TI</h1>
                <p className="mx-auto mt-4 max-w-2xl text-blue-100">
                    Sampaikan keluhan TI Anda, lacak progres penyelesaian, dan temukan solusi mandiri melalui Knowledge Base.
                </p>
            </section>

            <section className="mt-8 grid gap-4 md:grid-cols-3">
                {cards.map((card) => (
                    <div key={card.title} className="flex flex-col rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                        <card.icon className="size-8 text-blue-600" />
                        <h2 className="mt-4 text-lg font-semibold">{card.title}</h2>
                        <p className="mt-1 flex-1 text-sm text-slate-600">{card.description}</p>
                        <Link
                            href={card.href}
                            className="mt-4 inline-flex items-center justify-center rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700"
                        >
                            {card.button}
                        </Link>
                    </div>
                ))}
            </section>
        </>
    );
}
