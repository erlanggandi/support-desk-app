import { Form, Head } from '@inertiajs/react';
import { useState } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type Option = { id: number; name: string };
type ProblemTypeOption = Option & { category_id: number };

type Props = {
    departments: Option[];
    categories: Option[];
    problemTypes: ProblemTypeOption[];
    priorities: Option[];
    formToken: string;
};

const fieldClass = 'mt-1 w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none';

export default function CreateTicket({ departments, categories, problemTypes, priorities, formToken }: Props) {
    const [categoryId, setCategoryId] = useState<string>('');
    const filteredProblemTypes = problemTypes.filter((pt) => String(pt.category_id) === categoryId);

    return (
        <>
            <Head title="Buat Ticket" />

            <div className="mx-auto max-w-2xl">
                <h1 className="text-2xl font-bold">Buat Ticket Baru</h1>
                <p className="mt-1 text-sm text-slate-600">Isi formulir berikut. Anda akan menerima Ticket Number & Tracking Code untuk melacak progres.</p>

                <Form
                    action="/tickets"
                    method="post"
                    className="mt-6 space-y-5 rounded-xl border border-slate-200 bg-white p-6 shadow-sm"
                >
                    {({ processing, errors }) => (
                        <>
                            <input type="hidden" name="form_token" value={formToken} />

                            <div className="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <Label htmlFor="requester_name">Nama Lengkap *</Label>
                                    <Input id="requester_name" name="requester_name" required maxLength={255} className={fieldClass} />
                                    <InputError message={errors.requester_name} />
                                </div>
                                <div>
                                    {/* ponytail: kontak opsional sesuai OQ-004 [TBD] */}
                                    <Label htmlFor="requester_contact">Kontak (opsional)</Label>
                                    <Input id="requester_contact" name="requester_contact" maxLength={255} className={fieldClass} />
                                    <InputError message={errors.requester_contact} />
                                </div>
                            </div>

                            <div className="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <Label htmlFor="department_id">Departemen *</Label>
                                    <select id="department_id" name="department_id" required defaultValue="" className={fieldClass}>
                                        <option value="" disabled>Pilih departemen</option>
                                        {departments.map((d) => (
                                            <option key={d.id} value={d.id}>{d.name}</option>
                                        ))}
                                    </select>
                                    <InputError message={errors.department_id} />
                                </div>
                                <div>
                                    <Label htmlFor="priority_id">Prioritas *</Label>
                                    <select id="priority_id" name="priority_id" required defaultValue="" className={fieldClass}>
                                        <option value="" disabled>Pilih prioritas</option>
                                        {priorities.map((p) => (
                                            <option key={p.id} value={p.id}>{p.name}</option>
                                        ))}
                                    </select>
                                    <InputError message={errors.priority_id} />
                                </div>
                            </div>

                            <div className="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <Label htmlFor="category_id">Kategori *</Label>
                                    <select
                                        id="category_id"
                                        name="category_id"
                                        required
                                        value={categoryId}
                                        onChange={(e) => setCategoryId(e.target.value)}
                                        className={fieldClass}
                                    >
                                        <option value="" disabled>Pilih kategori</option>
                                        {categories.map((c) => (
                                            <option key={c.id} value={c.id}>{c.name}</option>
                                        ))}
                                    </select>
                                    <InputError message={errors.category_id} />
                                </div>
                                <div>
                                    <Label htmlFor="problem_type_id">Tipe Masalah</Label>
                                    <select id="problem_type_id" name="problem_type_id" defaultValue="" disabled={!categoryId} className={`${fieldClass} disabled:bg-slate-100`}>
                                        <option value="">— Tidak ada / lainnya —</option>
                                        {filteredProblemTypes.map((pt) => (
                                            <option key={pt.id} value={pt.id}>{pt.name}</option>
                                        ))}
                                    </select>
                                    <InputError message={errors.problem_type_id} />
                                </div>
                            </div>

                            <div>
                                <Label htmlFor="description">Deskripsi Masalah *</Label>
                                <textarea
                                    id="description"
                                    name="description"
                                    required
                                    rows={5}
                                    maxLength={5000}
                                    placeholder="Jelaskan masalah sedetail mungkin..."
                                    className={fieldClass}
                                />
                                <InputError message={errors.description} />
                            </div>

                            <Button type="submit" disabled={processing} className="w-full bg-blue-600 hover:bg-blue-700">
                                {processing ? 'Mengirim...' : 'Kirim Ticket'}
                            </Button>
                        </>
                    )}
                </Form>
            </div>
        </>
    );
}
