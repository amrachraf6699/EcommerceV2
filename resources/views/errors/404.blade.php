@php
    $title = '404 | '.config('app.name');
    $statusCode = '404';
    $heading = app()->getLocale() === 'ar' ? 'الصفحة غير موجودة' : 'This page is off the map';
    $copy = app()->getLocale() === 'ar'
        ? 'قد يكون الرابط غير صحيح، أو ربما تم حذف الصفحة أو نقلها. يمكنك العودة للواجهة الرئيسية ومتابعة التصفح من هناك.'
        : 'The link may be outdated, the page may have moved, or the address may be incorrect. Head back to the storefront and continue from there.';
    $primaryAction = app()->getLocale() === 'ar' ? 'العودة للرئيسية' : 'Back To Home';
    $secondaryAction = app()->getLocale() === 'ar' ? 'الرجوع للخلف' : 'Go Back';
    $sideTitle = app()->getLocale() === 'ar' ? 'ماذا يمكنك أن تفعل؟' : 'What You Can Do';
    $tips = app()->getLocale() === 'ar'
        ? [
            ['title' => 'تحقق من الرابط', 'copy' => 'تأكد من عدم وجود خطأ في كتابة العنوان أو المسار.'],
            ['title' => 'ابدأ من الرئيسية', 'copy' => 'استخدم الصفحة الرئيسية أو الأقسام للوصول للمحتوى المطلوب.'],
            ['title' => 'جرّب صفحة أخرى', 'copy' => 'قد يكون المنتج أو الصفحة تم نقلها إلى مكان مختلف داخل المتجر.'],
        ]
        : [
            ['title' => 'Check the URL', 'copy' => 'Make sure the address was typed correctly and the path is complete.'],
            ['title' => 'Start from home', 'copy' => 'Use the homepage or category pages to navigate back into the storefront.'],
            ['title' => 'Try another route', 'copy' => 'The product or page may have been moved somewhere else in the catalog.'],
        ];
@endphp

@include('errors.layout')
