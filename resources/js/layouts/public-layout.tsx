import { Link, usePage } from '@inertiajs/react';
import { Head } from '@inertiajs/react';

const navLinks = [
    { title: 'Beranda', href: '/' },
    { title: 'Buat Ticket', href: '/tickets/create' },
    { title: 'Lacak Ticket', href: '/track' },
    { title: 'Knowledge Base', href: '/knowledge-base' },
];

export default function PublicLayout({ children }: { children: React.ReactNode }) {
    const { auth } = usePage().props;

    return (
        <div className="flex min-h-screen flex-col bg-slate-50 text-slate-900">
            <Head title="IT Helpdesk" />

            <header className="sticky top-0 z-40 border-b border-slate-200 bg-white/95 backdrop-blur">
                <div className="mx-auto flex h-16 max-w-6xl items-center justify-between px-4">
                    <Link href="/" className="flex items-center gap-2 text-lg font-bold tracking-tight">
                        <span className="flex size-8 items-center justify-center rounded-lg bg-blue-600 text-sm font-bold text-white">IT</span>
                        IT Helpdesk
                    </Link>

                    <nav className="hidden items-center gap-1 md:flex">
                        {navLinks.map((link) => (
                            <Link
                                key={link.href}
                                href={link.href}
                                className="rounded-md px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-slate-900"
                            >
                                {link.title}
                            </Link>
                        ))}
                    </nav>

                    <Link
                        href={auth.user ? '/admin' : '/login'}
                        className="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700"
                    >
                        {auth.user ? 'Portal Admin' : 'Login Admin'}
                    </Link>
                </div>

                {/* Mobile nav */}
                <nav className="flex gap-1 overflow-x-auto border-t border-slate-200 px-4 py-2 md:hidden">
                    {navLinks.map((link) => (
                        <Link key={link.href} href={link.href} className="whitespace-nowrap rounded-md px-3 py-1.5 text-sm font-medium text-slate-600 hover:bg-slate-100">
                            {link.title}
                        </Link>
                    ))}
                </nav>
            </header>

            <main className="mx-auto w-full max-w-6xl flex-1 px-4 py-8">{children}</main>

            <footer className="border-t border-slate-200 bg-white py-6">
                <div className="mx-auto max-w-6xl px-4 text-center text-sm text-slate-500">
                    IT Helpdesk — Layanan tiket bantuan TI internal.
                </div>
            </footer>
        </div>
    );
}
