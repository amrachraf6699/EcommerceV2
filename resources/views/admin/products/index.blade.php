@php
    $title = 'المنتجات';
    $pageTitle = 'إدارة المنتجات';
    $pageDescription = 'شاشة واحدة للبحث والتصفية ومراجعة حالة المنتجات والنسخ.';
    $breadcrumbs = ['الإدارة', 'المنتجات'];
@endphp

@extends('layouts.admin')

@section('content')
    <section class="admin-card">
        @if (session('product_import_summary'))
            @php($importSummary = session('product_import_summary'))
            <div class="mb-6 rounded-3xl border border-white/10 bg-white/5 p-5">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-white">نتيجة استيراد المنتجات</h2>
                        <p class="mt-1 text-sm text-slate-300">ملخص آخر عملية استيراد تم تنفيذها من ملف Excel.</p>
                    </div>
                    @if (! empty($importSummary['error_report_token']))
                        <a class="admin-btn-secondary" href="{{ route('admin.products.import.errors', $importSummary['error_report_token']) }}">
                            تنزيل تقرير الأخطاء
                        </a>
                    @endif
                </div>
                <div class="mt-4 grid gap-3 md:grid-cols-4">
                    <div class="rounded-2xl border border-slate-300 bg-white p-4 shadow-sm">
                        <p class="text-xs font-medium text-slate-500">إجمالي الصفوف</p>
                        <p class="mt-2 text-2xl font-black text-slate-900">{{ $importSummary['total_rows'] ?? 0 }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-300 bg-white p-4 shadow-sm">
                        <p class="text-xs font-medium text-slate-500">تمت إضافتها</p>
                        <p class="mt-2 text-2xl font-black text-emerald-600">{{ $importSummary['imported_count'] ?? 0 }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-300 bg-white p-4 shadow-sm">
                        <p class="text-xs font-medium text-slate-500">تم تحديثها</p>
                        <p class="mt-2 text-2xl font-black text-sky-600">{{ $importSummary['updated_count'] ?? 0 }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-300 bg-white p-4 shadow-sm">
                        <p class="text-xs font-medium text-slate-500">فشل / تم تخطيه</p>
                        <p class="mt-2 text-2xl font-black text-rose-600">{{ $importSummary['failed_count'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
        @endif

        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <form method="GET" class="grid gap-3 xl:grid-cols-5 lg:flex-1">
                <input class="admin-input" type="text" name="search" placeholder="ابحث بالاسم أو الرابط" value="{{ request('search') }}">
                <select class="admin-select" name="category">
                    <option value="">كل الأقسام</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((int) request('category') === $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                <select class="admin-select" name="status">
                    <option value="">كل الحالات</option>
                    <option value="active" @selected(request('status') === 'active')>مفعل</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>غير مفعل</option>
                </select>
                <select class="admin-select" name="featured">
                    <option value="">الكل</option>
                    <option value="yes" @selected(request('featured') === 'yes')>مميز فقط</option>
                    <option value="no" @selected(request('featured') === 'no')>غير مميز فقط</option>
                </select>
                <button class="admin-btn-secondary" type="submit">تطبيق</button>
            </form>
            @can('products.create')
                <div class="flex flex-wrap gap-3">
                    <button type="button" class="admin-btn-secondary" data-import-modal-open>إستيراد</button>
                    <a class="admin-btn-primary" href="{{ route('admin.products.create') }}">إضافة منتج</a>
                </div>
            @endcan
        </div>

        @can('products.create')
            <div class="admin-import-modal hidden" data-import-modal aria-hidden="true">
                <div class="admin-import-modal__backdrop" data-import-modal-close></div>
                <div class="admin-import-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="products-import-modal-title">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 id="products-import-modal-title" class="text-xl font-black text-white">استيراد المنتجات عبر Excel</h2>
                            <p class="mt-2 text-sm text-slate-300">حمّل الملف النموذجي، عدّل البيانات، ثم ارفع ملف <span dir="ltr">.xlsx</span> لتنفيذ الاستيراد الجماعي.</p>
                        </div>
                        <button type="button" class="admin-btn-icon" aria-label="إغلاق نافذة الاستيراد" data-import-modal-close>
                            <i class="bx bx-x" aria-hidden="true"></i>
                        </button>
                    </div>

                    <div class="mt-5 rounded-2xl border border-cyan-400/20 bg-cyan-400/10 p-4">
                        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                            <div>
                                <h3 class="text-sm font-bold text-white">ملف مرجعي جاهز</h3>
                                <p class="mt-1 text-sm text-cyan-100/85">يحتوي على منتجات مثال لتوضيح طريقة تعبئة الأعمدة والنسخ والصور.</p>
                            </div>
                            <a class="admin-btn-secondary admin-import-modal__sample-btn" href="{{ route('admin.products.import.template') }}">تنزيل العينة</a>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('admin.products.import.store') }}" enctype="multipart/form-data" class="mt-6 grid gap-4">
                        @csrf
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-200" for="products-import-file">ملف Excel</label>
                            <input id="products-import-file" class="admin-input" type="file" name="file" accept=".xlsx" required>
                            @error('file')
                                <p class="mt-2 text-sm text-rose-300">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-200" for="products-import-mode">وضع الاستيراد</label>
                            <select id="products-import-mode" class="admin-select" name="mode" required>
                                <option value="create-only" @selected(old('mode') === 'create-only')>إضافة فقط</option>
                                <option value="upsert" @selected(old('mode') === 'upsert')>إضافة أو تحديث بالـ slug / SKU</option>
                            </select>
                            @error('mode')
                                <p class="mt-2 text-sm text-rose-300">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                            <button type="button" class="admin-btn-secondary" data-import-modal-close>إلغاء</button>
                            <button class="admin-btn-primary" type="submit">بدء الاستيراد</button>
                        </div>
                    </form>
                </div>
            </div>
        @endcan

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead class="bg-slate-950/50">
                    <tr>
                        <th>المنتج</th>
                        <th>الأقسام</th>
                        <th>النسخ</th>
                        <th>الحالة</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10 bg-white/5">
                    @forelse ($products as $product)
                        <tr>
                            <td>
                                <p class="font-bold text-white">{{ $product->name }}</p>
                                <p class="mt-1 text-xs text-slate-400">{{ $product->slug }}</p>
                            </td>
                            <td>{{ $product->categories->pluck('name')->join(' / ') ?: 'بدون أقسام' }}</td>
                            <td>{{ $product->variants_count }}</td>
                            <td>
                                <span class="px-3 py-1 text-xs {{ $product->is_active ? 'bg-emerald-400/10 text-emerald-200' : 'bg-slate-400/10 text-slate-300' }}">
                                    {{ $product->is_active ? 'مفعل' : 'غير مفعل' }}
                                </span>
                                @if ($product->is_featured)
                                    <span class="mr-2 bg-amber-300/10 px-3 py-1 text-xs text-amber-200">مميز</span>
                                @endif
                            </td>
                            <td>
                                <div class="flex flex-wrap gap-2">
                                    <a class="admin-btn-icon" href="{{ route('admin.products.edit', $product) }}" aria-label="تعديل المنتج" title="تعديل المنتج">
                                        <i class="bx bx-pencil" aria-hidden="true"></i>
                                    </a>
                                    @can('products.delete')
                                        <form method="POST"
                                              action="{{ route('admin.products.destroy', $product) }}"
                                              data-loading-form
                                              data-confirm-title="حذف المنتج"
                                              data-confirm-text="سيتم إخفاء المنتج من الإدارة والمتجر مع الاحتفاظ بالسجلات المرتبطة به حسب النظام.">
                                            @csrf
                                            @method('DELETE')
                                            <button class="admin-btn-icon admin-btn-icon--danger" type="submit" aria-label="حذف المنتج" title="حذف المنتج" data-loading-label="جاري الحذف...">
                                                <i class="bx bx-trash" aria-hidden="true"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <x-admin.empty-state title="لا توجد منتجات" description="ابدأ بإضافة أول منتج ثم أضف نسخه وصوره من شاشة التعديل." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-5">{{ $products->links() }}</div>
    </section>
@endsection

@push('styles')
    <style>
        .admin-import-modal{
            position:fixed;
            inset:0;
            z-index:80;
            display:grid;
            place-items:center;
            padding:24px;
        }
        .admin-import-modal.hidden{display:none;}
        .admin-import-modal__backdrop{
            position:absolute;
            inset:0;
            background:rgb(15 15 15 / .55);
            backdrop-filter:blur(4px);
        }
        .admin-import-modal__dialog{
            position:relative;
            width:min(100%, 720px);
            max-height:calc(100vh - 48px);
            overflow:auto;
            border:1px solid rgb(226 232 240 / .9);
            border-radius:32px;
            background:linear-gradient(180deg, #ffffff, #f8fafc);
            box-shadow:0 32px 80px rgb(15 23 42 / .16);
            padding:28px;
            color:#0f172a;
        }
        .admin-import-modal__dialog h2,
        .admin-import-modal__dialog h3{
            color:#0f172a;
        }
        .admin-import-modal__dialog p{
            color:#475569;
        }
        .admin-import-modal__dialog .admin-btn-icon{
            width:44px;
            height:44px;
            border:1px solid rgb(203 213 225);
            border-radius:14px;
            background:#fff;
            color:#0f172a;
            box-shadow:0 10px 24px rgb(15 23 42 / .08);
        }
        .admin-import-modal__dialog .admin-btn-icon:hover{
            background:#f8fafc;
        }
        .admin-import-modal__dialog .admin-input,
        .admin-import-modal__dialog .admin-select{
            border-color:rgb(203 213 225);
            background:#fff;
            color:#0f172a;
            min-height:56px;
            box-shadow:inset 0 1px 2px rgb(15 23 42 / .04);
        }
        .admin-import-modal__dialog .admin-input:focus,
        .admin-import-modal__dialog .admin-select:focus{
            border-color:rgb(15 23 42);
            box-shadow:0 0 0 3px rgb(15 23 42 / .08);
        }
        .admin-import-modal__dialog label{
            color:#334155;
            font-weight:700;
        }
        .admin-import-modal__dialog .admin-btn-primary{
            min-width:150px;
            justify-content:center;
        }
        .admin-import-modal__dialog .admin-btn-secondary{
            min-width:120px;
            justify-content:center;
            border-color:rgb(203 213 225);
            background:#fff;
            color:#0f172a;
        }
        .admin-import-modal__dialog .admin-btn-secondary:hover{
            background:#f8fafc;
        }
        .admin-import-modal__sample-btn{
            white-space:nowrap;
        }
        .admin-import-modal__dialog .rounded-2xl{
            border-color:rgb(186 230 253 / .9);
            background:linear-gradient(180deg, rgb(240 249 255), rgb(224 242 254));
        }
        .admin-import-modal__dialog .rounded-2xl h3{
            color:#0c4a6e;
        }
        .admin-import-modal__dialog .rounded-2xl p{
            color:#075985;
        }
        @media (max-width: 640px){
            .admin-import-modal{
                padding:16px;
            }
            .admin-import-modal__dialog{
                border-radius:24px;
                padding:20px;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        (() => {
            const modal = document.querySelector('[data-import-modal]');
            const openButtons = document.querySelectorAll('[data-import-modal-open]');
            const closeButtons = document.querySelectorAll('[data-import-modal-close]');

            if (!modal || !openButtons.length) return;

            const setOpen = (open) => {
                modal.classList.toggle('hidden', !open);
                modal.setAttribute('aria-hidden', open ? 'false' : 'true');
                document.body.classList.toggle('overflow-hidden', open);
            };

            openButtons.forEach((button) => {
                button.addEventListener('click', () => setOpen(true));
            });

            closeButtons.forEach((button) => {
                button.addEventListener('click', () => setOpen(false));
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    setOpen(false);
                }
            });

            @if ($errors->has('file') || $errors->has('mode'))
                setOpen(true);
            @endif
        })();
    </script>
@endpush
