@php
    $title = '505 | '.config('app.name');
    $statusCode = '505';
    $statusLabel = 'Request Blocked';
    $heading = app()->getLocale() === 'ar' ? 'تعذر إتمام هذا الطلب' : 'This request could not be completed';
    $copy = app()->getLocale() === 'ar'
        ? 'حدث تعارض تقني أثناء محاولة تحميل هذه الصفحة. يمكنك المحاولة مرة أخرى أو العودة للمتجر ومتابعة التصفح من هناك.'
        : 'A technical mismatch prevented this page from loading correctly. You can try again or return to the storefront and continue browsing from there.';
    $primaryAction = app()->getLocale() === 'ar' ? 'العودة للرئيسية' : 'Return Home';
    $secondaryAction = app()->getLocale() === 'ar' ? 'إعادة المحاولة' : 'Try Again';
    $sideTitle = app()->getLocale() === 'ar' ? 'خطوات سريعة' : 'Quick Recovery';
    $tips = app()->getLocale() === 'ar'
        ? [
            ['title' => 'حدّث الصفحة', 'copy' => 'في بعض الحالات، إعادة تحميل الصفحة تكفي لحل المشكلة المؤقتة.'],
            ['title' => 'ارجع للمتجر', 'copy' => 'يمكنك العودة للصفحة الرئيسية ثم محاولة الوصول للمحتوى من جديد.'],
            ['title' => 'جرّب لاحقاً', 'copy' => 'إذا استمرت المشكلة فقد تكون حالة مؤقتة في الاتصال أو الخادم.'],
        ]
        : [
            ['title' => 'Refresh the page', 'copy' => 'A fresh request may be enough if this was only a temporary mismatch.'],
            ['title' => 'Return to the store', 'copy' => 'Go back to the homepage and reopen the destination from there.'],
            ['title' => 'Try again later', 'copy' => 'If the issue persists, it may be a short-lived server or connection problem.'],
        ];
    $brandMark = '55';
@endphp

@include('errors.layout')
