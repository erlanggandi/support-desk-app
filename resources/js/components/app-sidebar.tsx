import { Link } from '@inertiajs/react';
import { BookOpen, ClipboardList, FolderGit2, LayoutDashboard, LifeBuoy, ListChecks, ScrollText, Settings2, Tags, Users, Wrench } from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    { title: 'Dashboard', href: '/admin', icon: LayoutDashboard },
    { title: 'Tickets', href: '/admin/tickets', icon: ClipboardList },
    { title: 'Departemen', href: '/admin/master/departments', icon: Users },
    { title: 'Kategori', href: '/admin/master/categories', icon: Tags },
    { title: 'Tipe Masalah', href: '/admin/master/problem-types', icon: ListChecks },
    { title: 'Prioritas', href: '/admin/master/priorities', icon: LifeBuoy },
    { title: 'Teknisi', href: '/admin/master/technicians', icon: Wrench },
    { title: 'Knowledge Base', href: '/admin/knowledge-base', icon: BookOpen },
    { title: 'Audit Log', href: '/admin/audit-logs', icon: ScrollText },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Portal Publik',
        href: '/',
        icon: Settings2,
    },
    {
        title: 'Repository',
        href: 'https://github.com/laravel/react-starter-kit',
        icon: FolderGit2,
    },
];

export function AppSidebar() {
    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href="/admin" prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
