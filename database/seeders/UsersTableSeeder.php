<?php

namespace Database\Seeders;

use App\Models\User; // استيراد موديل User
use Illuminate\Database\Seeder;
use Faker\Factory as Faker; // استيراد Faker بشكل صحيح
use App\Models\Account; // تأكد من أنك تستورد النموذج بشكل صحيح
use Carbon\Carbon; // استيراد مكتبة Carbon لإدارة التواريخ


class UsersTableSeeder extends Seeder
{
    
    public function run()
    {
        // إضافة 10 سجلات وهمية في قاعدة البيانات
        foreach (range(1, 10) as $index) {
            Account::create([
                'User_Name' => 'JohnDoe', // اسم المستخدم
                'Email' => 'johndoe@example.com', // البريد الإلكتروني
                'Password' => bcrypt('your_password_here'), // تأكد من استخدام bcrypt لتشفير كلمة المرور
                'Address' => '123 Main Street, Springfield', // العنوان
                'Phone_Number' => '+1 (555) 123-4567', // رقم الهاتف
                'Ststus' => 1, // الحالة (1 تعني مفعل)
                'First_Name' => 'John', // الاسم الأول
                'Last_Name' => 'Doe', // الاسم الأخير
                'D_Experince_years' => 5, // سنوات الخبرة
                'D_Partial_certificate' => 'Diploma in Software Engineering', // الشهادة الجزئية
                'Birth_date' => Carbon::create('1990', '01', '01'), // تاريخ الميلاد
                'Role_id' => 1, // ID للدور
                'CreatedBy' => 1, // من قام بإنشاء الحساب
                'image' => 'profile_image.jpg', // صورة الملف الشخصي
                'created_at' => now(), // تاريخ الإنشاء
                'updated_at' => now(), // تاريخ التحديث
            ]);
        }
    }
}
