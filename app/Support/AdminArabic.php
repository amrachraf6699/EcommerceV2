<?php

namespace App\Support;

use Illuminate\Support\Str;

class AdminArabic
{
    public static function settingsGroup(string $group): string
    {
        return [
            'analytics' => 'التحليلات',
            'appearance' => 'المظهر',
            'brand' => 'الهوية',
            'mail' => 'البريد',
            'marketing' => 'التسويق',
            'notifications' => 'الإشعارات',
            'payment' => 'الدفع',
            'security' => 'الحماية',
            'shipping' => 'الشحن والضريبة',
            'social' => 'وسائل التواصل',
        ][$group] ?? Str::title(str_replace('_', ' ', $group));
    }

    public static function settingsLabel(string $key, ?string $fallback = null): string
    {
        return [
            'facebook' => 'فيسبوك',
            'instagram' => 'إنستغرام',
            'snapchat' => 'سناب شات',
            'tiktok' => 'تيك توك',
            'twitter' => 'إكس',
            'name' => 'اسم العلامة',
            'logo' => 'الشعار',
            'header_text_ar' => 'النص العلوي بالعربية',
            'header_text_en' => 'النص العلوي بالإنجليزية',
            'default_theme' => 'الوضع الافتراضي',
            'email' => 'البريد الإلكتروني',
            'phone' => 'رقم الهاتف',
            'whatsapp_phone' => 'رقم واتساب',
            'address_ar' => 'العنوان بالعربية',
            'address_en' => 'العنوان بالإنجليزية',
            'mail_host' => 'خادم البريد',
            'mail_port' => 'منفذ البريد',
            'mail_username' => 'اسم مستخدم البريد',
            'mail_password' => 'كلمة مرور البريد',
            'mail_encryption' => 'تشفير البريد',
            'mail_from_name' => 'اسم المرسل',
            'mail_from_address' => 'بريد المرسل',
            'welcome_coupon_enabled' => 'تفعيل كوبون الترحيب',
            'welcome_coupon_discount_mode' => 'نوع خصم كوبون الترحيب',
            'welcome_coupon_value' => 'قيمة كوبون الترحيب',
            'welcome_coupon_min_value' => 'أقل قيمة عشوائية لكوبون الترحيب',
            'welcome_coupon_max_value' => 'أعلى قيمة عشوائية لكوبون الترحيب',
            'track_order_enabled' => 'تفعيل تتبع الطلب',
            'chatbot_enabled' => 'تفعيل المحادثة الآلية',
            'shipping_gulf_cost' => 'شحن الخليج',
            'shipping_others_cost' => 'شحن باقي الدول',
            'enable_vat' => 'تفعيل ضريبة القيمة المضافة',
            'vat_value' => 'قيمة الضريبة',
            'categories_title_ar' => 'عنوان قسم الأقسام بالعربية',
            'categories_title_en' => 'عنوان قسم الأقسام بالإنجليزية',
            'categories_appearance' => 'مظهر الأقسام',
            'products_appearance' => 'مظهر المنتجات',
            'clients_appearance' => 'مظهر العملاء',
            'home_brands_section_background_color' => 'لون خلفية قسم علاماتنا الرياضية',
            'home_shop_by_size_section_background_color' => 'لون خلفية قسم الاختر مقاسك',
            'home_shop_by_size_card_background_image' => 'صورة خلفية مقاسات الصفحة الرئيسية',
            'home_new_arrivals_section_background_color' => 'لون خلفية قسم أحدث الإضافات',
            'google_analytics_measurement_id' => 'معرف Google Analytics',
            'google_tag_manager_id' => 'معرف Google Tag Manager',
            'google_search_console_verification_id' => 'رمز Google Search Console',
            'google_ads_conversion_id' => 'معرف تحويلات Google Ads',
            'google_ads_conversion_label' => 'تصنيف تحويلات Google Ads',
            'facebook_pixel_id' => 'معرف Facebook Pixel',
            'meta_domain_verification_id' => 'رمز توثيق Meta',
            'tiktok_pixel_id' => 'معرف TikTok Pixel',
            'snapchat_pixel_id' => 'معرف Snapchat Pixel',
            'pinterest_tag_id' => 'معرف Pinterest Tag',
            'microsoft_clarity_project_id' => 'معرف Microsoft Clarity',
            'bing_uet_tag_id' => 'معرف Bing UET',
            'afs_environment' => 'AFS Environment',
            'afs_sandbox_entity_id' => 'AFS Sandbox Entity ID',
            'afs_sandbox_access_token' => 'AFS Sandbox Access Token',
            'afs_sandbox_base_url' => 'AFS Sandbox Base URL',
            'afs_live_entity_id' => 'AFS Live Entity ID',
            'afs_live_access_token' => 'AFS Live Access Token',
            'afs_live_base_url' => 'AFS Live Base URL',
            'afs_brands' => 'AFS Payment Brands',
            'recaptcha_site_key' => 'reCAPTCHA Site Key',
            'recaptcha_secret_key' => 'reCAPTCHA Secret Key',
            'customer_order_placed_notification_enabled' => 'إشعار العميل عند إنشاء الطلب',
            'customer_order_paid_notification_enabled' => 'إشعار العميل عند تأكيد الدفع',
            'customer_order_shipped_notification_enabled' => 'إشعار العميل عند شحن الطلب',
            'customer_order_delivered_notification_enabled' => 'إشعار العميل عند تسليم الطلب',
            'customer_order_canceled_notification_enabled' => 'إشعار العميل عند إلغاء الطلب',
            'admin_new_order_notification_enabled' => 'إشعار الإدارة عند وصول طلب جديد',
            'whatsapp_message' => 'رسالة الواتساب الخاصة بالتواصل',
            'working_hours' => 'ساعات العمل',
            'po_box' => 'صندوق البريد',
            'country' => 'الدولة',
            'cr_number' => 'رقم السجل التجاري',
        ][$key] ?? ($fallback ?: Str::title(str_replace('_', ' ', $key)));
    }

    public static function settingsDescription(string $key, ?string $fallback = null): ?string
    {
        return [
            'header_text_ar' => 'نص قصير يظهر في واجهة المتجر باللغة العربية.',
            'header_text_en' => 'نص قصير يظهر في واجهة المتجر باللغة الإنجليزية.',
            'default_theme' => 'اختر الوضع الذي يبدأ به المتجر إذا لم يسبق للزائر اختيار وضع مختلف.',
            'email' => 'البريد الإلكتروني الرئيسي للتواصل مع العملاء.',
            'phone' => 'رقم الهاتف الرئيسي للمتجر.',
            'whatsapp_phone' => 'رقم واتساب المخصص للتواصل السريع.',
            'address_ar' => 'العنوان الذي يظهر للزوار باللغة العربية.',
            'address_en' => 'العنوان الذي يظهر للزوار باللغة الإنجليزية.',
            'mail_host' => 'اسم خادم SMTP.',
            'mail_port' => 'رقم منفذ SMTP.',
            'mail_username' => 'اسم المستخدم لحساب SMTP.',
            'mail_password' => 'كلمة مرور حساب SMTP.',
            'mail_encryption' => 'نوع التشفير المستخدم في SMTP.',
            'mail_from_name' => 'الاسم الظاهر للمرسل.',
            'mail_from_address' => 'البريد الإلكتروني الظاهر للمرسل.',
            'welcome_coupon_enabled' => 'إظهار نافذة ترحيبية تجمع البريد الإلكتروني وترسل كوبوناً شخصياً للزائر.',
            'welcome_coupon_discount_mode' => 'حدد هل الكوبون ثابت أو عشوائي، وهل الخصم نسبة مئوية أو مبلغ ثابت.',
            'welcome_coupon_value' => 'القيمة المستخدمة مع الخصم الثابت فقط.',
            'welcome_coupon_min_value' => 'أقل قيمة ممكنة عند اختيار خصم عشوائي.',
            'welcome_coupon_max_value' => 'أعلى قيمة ممكنة عند اختيار خصم عشوائي.',
            'chatbot_enabled' => 'إظهار مساعد محادثة آلي في واجهة المتجر ليساعد الزائر على اختيار القسم والمنتج والمقاس ثم إضافته إلى السلة.',
            'shipping_gulf_cost' => 'قيمة شحن دول الخليج. ضع 0 للشحن المجاني.',
            'shipping_others_cost' => 'قيمة الشحن لكل قطعة لجميع الدول خارج الخليج. ضع 0 للشحن المجاني.',
            'enable_vat' => 'تفعيل احتساب ضريبة القيمة المضافة على الطلبات.',
            'vat_value' => 'أدخل نسبة أو قيمة الضريبة حسب نظام المتجر.',
            'categories_title_ar' => 'العنوان الذي يظهر أعلى قسم الأقسام في الصفحة الرئيسية باللغة العربية.',
            'categories_title_en' => 'العنوان الذي يظهر أعلى قسم الأقسام في الصفحة الرئيسية باللغة الإنجليزية.',
            'categories_appearance' => 'حدد شكل عرض الأقسام بين Masonry أو تمرير أفقي.',
            'products_appearance' => 'حدد شكل عرض المنتجات بين Masonry أو تمرير أفقي أو شبكة.',
            'clients_appearance' => 'حدد شكل عرض العملاء بين Masonry أو تمرير أفقي أو شبكة.',
            'home_brands_section_background_color' => 'اختر لون خلفية قسم علاماتنا الرياضية في الصفحة الرئيسية.',
            'home_shop_by_size_section_background_color' => 'اختر لون خلفية قسم الاختر مقاسك في الصفحة الرئيسية.',
            'home_shop_by_size_card_background_image' => 'اختر الصورة التي تظهر كخلفية لكل دائرة في قسم اختر مقاسك في الصفحة الرئيسية.',
            'home_new_arrivals_section_background_color' => 'اختر لون خلفية قسم أحدث الإضافات في الصفحة الرئيسية.',
            'google_analytics_measurement_id' => 'معرف GA4 مثل G-XXXXXXXXXX.',
            'google_tag_manager_id' => 'معرف الحاوية مثل GTM-XXXXXXX.',
            'google_search_console_verification_id' => 'قيمة التحقق الخاصة بوسم Google Search Console.',
            'google_ads_conversion_id' => 'معرف التحويلات مثل AW-123456789.',
            'google_ads_conversion_label' => 'تصنيف التحويل المرتبط بمعرف Google Ads.',
            'facebook_pixel_id' => 'معرف Meta أو Facebook Pixel.',
            'meta_domain_verification_id' => 'رمز توثيق النطاق في Meta.',
            'tiktok_pixel_id' => 'معرف TikTok Pixel.',
            'snapchat_pixel_id' => 'معرف Snapchat Pixel.',
            'pinterest_tag_id' => 'معرف Pinterest Tag.',
            'microsoft_clarity_project_id' => 'معرف مشروع Microsoft Clarity.',
            'bing_uet_tag_id' => 'معرف Microsoft Advertising أو Bing UET.',
            'afs_environment' => 'اختر بيئة AFS النشطة: Sandbox أو Live.',
            'afs_sandbox_entity_id' => 'معرف كيان AFS لبيئة الاختبار.',
            'afs_sandbox_access_token' => 'رمز وصول AFS لبيئة الاختبار.',
            'afs_sandbox_base_url' => 'رابط AFS الأساسي لبيئة الاختبار.',
            'afs_live_entity_id' => 'معرف كيان AFS لبيئة الإنتاج.',
            'afs_live_access_token' => 'رمز وصول AFS لبيئة الإنتاج.',
            'afs_live_base_url' => 'رابط AFS الأساسي لبيئة الإنتاج.',
            'afs_brands' => 'رموز العلامات المدعومة في AFS مفصولة بمسافات.',
            'recaptcha_site_key' => 'المفتاح العام المستخدم لإظهار أداة Google reCAPTCHA داخل نموذج التواصل.',
            'recaptcha_secret_key' => 'المفتاح السري المستخدم للتحقق من استجابة Google reCAPTCHA على الخادم.',
            'customer_order_placed_notification_enabled' => 'إرسال رسالة بريدية للعميل بعد تثبيت الطلب بنجاح.',
            'customer_order_paid_notification_enabled' => 'إرسال إشعار للعميل عند انتقال حالة الدفع إلى paid.',
            'customer_order_shipped_notification_enabled' => 'إرسال إشعار للعميل عند انتقال حالة التجهيز إلى shipped.',
            'customer_order_delivered_notification_enabled' => 'إرسال إشعار للعميل عند انتقال حالة التجهيز إلى delivered.',
            'customer_order_canceled_notification_enabled' => 'إرسال إشعار للعميل عند انتقال الطلب أو الدفع إلى canceled.',
            'admin_new_order_notification_enabled' => 'إنشاء إشعار داخل لوحة الإدارة عند وصول طلب جديد للمسؤولين الذين لديهم صلاحية عرض الطلبات.',
            'working_hours' => 'ساعات العمل التي تظهر لزوار المتجر.',
            'po_box' => 'رقم صندوق البريد الخاص بالعلامة التجارية.',
            'country' => 'الدولة التي تعمل فيها العلامة التجارية.',
            'cr_number' => 'رقم السجل التجاري الخاص بالعلامة التجارية.',
        ][$key] ?? $fallback;
    }

    public static function roleName(string $name): string
    {
        return [
            'super-admin' => 'مدير عام',
            'admin' => 'مشرف',
        ][$name] ?? $name;
    }

    public static function permissionGroup(string $group): string
    {
        return [
            'dashboard' => 'لوحة التحكم',
            'admins' => 'المسؤولون',
            'roles' => 'الأدوار',
            'settings' => 'الإعدادات',
            'pages' => 'الصفحات',
            'products' => 'المنتجات',
            'categories' => 'الأقسام',
            'customers' => 'العملاء',
            'sliders' => 'السلايدر',
            'clients' => 'العملاء المميزون',
            'orders' => 'الطلبات',
            'carts' => 'السلات',
            'coupons' => 'الكوبونات',
            'welcome_coupons' => 'كوبونات الترحيب',
            'contact_messages' => 'رسائل التواصل',
        ][$group] ?? $group;
    }

    public static function permissionName(string $permission): string
    {
        [$resource, $action] = array_pad(explode('.', $permission, 2), 2, null);

        if ($resource === null || $action === null) {
            return $permission;
        }

        $resources = [
            'dashboard' => 'لوحة التحكم',
            'admins' => 'المسؤولين',
            'roles' => 'الأدوار',
            'settings' => 'الإعدادات',
            'pages' => 'الصفحات',
            'products' => 'المنتجات',
            'categories' => 'الأقسام',
            'customers' => 'العملاء',
            'sliders' => 'السلايدر',
            'clients' => 'العملاء المميزين',
            'orders' => 'الطلبات',
            'carts' => 'السلات',
            'coupons' => 'الكوبونات',
            'welcome_coupons' => 'كوبونات الترحيب',
            'contact_messages' => 'رسائل التواصل',
        ];

        $actions = [
            'view' => 'عرض',
            'create' => 'إنشاء',
            'update' => 'تعديل',
            'delete' => 'حذف',
        ];

        return isset($resources[$resource], $actions[$action])
            ? $actions[$action] . ' ' . $resources[$resource]
            : $permission;
    }
}
