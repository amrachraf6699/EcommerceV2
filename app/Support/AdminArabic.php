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
            'shipping_type' => 'نوع الشحن',
            'shipping_gulf_cost' => 'شحن الخليج',
            'shipping_europe_america_1_2_cost' => 'أوروبا وأمريكا (من 1 إلى 2 شوز)',
            'shipping_europe_america_3_plus_cost' => 'أوروبا وأمريكا (3 شوز فأكثر)',
            'enable_vat' => 'تفعيل ضريبة القيمة المضافة',
            'vat_value' => 'قيمة الضريبة',
            'categories_appearance' => 'مظهر الأقسام',
            'products_appearance' => 'مظهر المنتجات',
            'clients_appearance' => 'مظهر العملاء',
            'home_brands_section_background_color' => 'لون خلفية قسم علاماتنا الرياضية',
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
            'tap_secret_key' => 'Tap Secret Key',
            'tap_public_key' => 'Tap Public Key',
            'tap_webhook_secret' => 'Tap Webhook Secret',
            'recaptcha_site_key' => 'reCAPTCHA Site Key',
            'recaptcha_secret_key' => 'reCAPTCHA Secret Key',
            'customer_order_placed_notification_enabled' => 'إشعار العميل عند إنشاء الطلب',
            'customer_order_paid_notification_enabled' => 'إشعار العميل عند تأكيد الدفع',
            'customer_order_shipped_notification_enabled' => 'إشعار العميل عند شحن الطلب',
            'customer_order_delivered_notification_enabled' => 'إشعار العميل عند تسليم الطلب',
            'customer_order_canceled_notification_enabled' => 'إشعار العميل عند إلغاء الطلب',
            'admin_new_order_notification_enabled' => 'إشعار الإدارة عند وصول طلب جديد',
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
            'shipping_type' => 'حدد ما إذا كانت تكلفة الشحن نسبة مئوية أو قيمة ثابتة.',
            'shipping_gulf_cost' => 'قيمة شحن دول الخليج. ضع 0 للشحن المجاني.',
            'shipping_europe_america_1_2_cost' => 'قيمة الشحن لكل شوز عند طلب 1 إلى 2 شوز لأوروبا وأمريكا.',
            'shipping_europe_america_3_plus_cost' => 'قيمة الشحن لكل شوز عند طلب 3 شوز فأكثر لأوروبا وأمريكا.',
            'enable_vat' => 'تفعيل احتساب ضريبة القيمة المضافة على الطلبات.',
            'vat_value' => 'أدخل نسبة أو قيمة الضريبة حسب نظام المتجر.',
            'categories_appearance' => 'حدد شكل عرض الأقسام بين Masonry أو تمرير أفقي.',
            'products_appearance' => 'حدد شكل عرض المنتجات بين Masonry أو تمرير أفقي أو شبكة.',
            'clients_appearance' => 'حدد شكل عرض العملاء بين Masonry أو تمرير أفقي أو شبكة.',
            'home_brands_section_background_color' => 'اختر لون خلفية قسم علاماتنا الرياضية في الصفحة الرئيسية.',
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
            'tap_secret_key' => 'المفتاح السري الخاص بـ Tap والمستخدم في طلبات الدفع من جهة الخادم.',
            'tap_public_key' => 'المفتاح العام الخاص بـ Tap والمرتبط بتكامل واجهة المتجر.',
            'tap_webhook_secret' => 'المفتاح السري المستخدم للتحقق من Webhook الخاص بـ Tap عند الحاجة.',
            'recaptcha_site_key' => 'المفتاح العام المستخدم لإظهار أداة Google reCAPTCHA داخل نموذج التواصل.',
            'recaptcha_secret_key' => 'المفتاح السري المستخدم للتحقق من استجابة Google reCAPTCHA على الخادم.',
            'customer_order_placed_notification_enabled' => 'إرسال رسالة بريدية للعميل بعد تثبيت الطلب بنجاح.',
            'customer_order_paid_notification_enabled' => 'إرسال إشعار للعميل عند انتقال حالة الدفع إلى paid.',
            'customer_order_shipped_notification_enabled' => 'إرسال إشعار للعميل عند انتقال حالة التجهيز إلى shipped.',
            'customer_order_delivered_notification_enabled' => 'إرسال إشعار للعميل عند انتقال حالة التجهيز إلى delivered.',
            'customer_order_canceled_notification_enabled' => 'إرسال إشعار للعميل عند انتقال الطلب أو الدفع إلى canceled.',
            'admin_new_order_notification_enabled' => 'إنشاء إشعار داخل لوحة الإدارة عند وصول طلب جديد للمسؤولين الذين لديهم صلاحية عرض الطلبات.',
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
