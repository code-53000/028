<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Club;
use App\Models\RecruitmentPost;
use App\Models\InterviewSlot;
use App\Models\Application;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('创建社团负责人账号...');
        
        $leader1 = User::firstOrCreate(
            ['email' => 'leader1@example.com'],
            [
                'name' => '张社长',
                'password' => Hash::make('password123'),
                'phone' => '13800000001',
                'role' => 'club_leader',
                'student_id' => '2021001',
                'major' => '计算机科学与技术',
                'grade' => '2021级',
                'bio' => '计算机协会会长，热爱编程和开源',
            ]
        );

        $leader2 = User::firstOrCreate(
            ['email' => 'leader2@example.com'],
            [
                'name' => '李社长',
                'password' => Hash::make('password123'),
                'phone' => '13800000002',
                'role' => 'club_leader',
                'student_id' => '2021002',
                'major' => '电子信息工程',
                'grade' => '2021级',
                'bio' => '电子科技协会会长，擅长嵌入式开发',
            ]
        );

        $leader3 = User::firstOrCreate(
            ['email' => 'leader3@example.com'],
            [
                'name' => '王社长',
                'password' => Hash::make('password123'),
                'phone' => '13800000003',
                'role' => 'club_leader',
                'student_id' => '2021003',
                'major' => '工商管理',
                'grade' => '2021级',
                'bio' => '创业协会会长，拥有丰富的创业经验',
            ]
        );

        $this->command->info('创建学生账号...');

        $students = [];
        $studentData = [
            ['name' => '小明', 'email' => 'student1@example.com', 'student_id' => '2024001', 'major' => '计算机科学与技术', 'grade' => '2024级'],
            ['name' => '小红', 'email' => 'student2@example.com', 'student_id' => '2024002', 'major' => '软件工程', 'grade' => '2024级'],
            ['name' => '小刚', 'email' => 'student3@example.com', 'student_id' => '2024003', 'major' => '电子信息工程', 'grade' => '2024级'],
            ['name' => '小丽', 'email' => 'student4@example.com', 'student_id' => '2024004', 'major' => '市场营销', 'grade' => '2024级'],
        ];

        foreach ($studentData as $i => $data) {
            $students[] = User::firstOrCreate(
                ['email' => $data['email']],
                array_merge($data, [
                    'password' => Hash::make('password123'),
                    'phone' => '1390000000' . ($i + 1),
                    'role' => 'student',
                    'bio' => '热爱学习的新生一枚~',
                ])
            );
        }

        $this->command->info('创建社团...');

        $club1 = Club::firstOrCreate(
            ['name' => '计算机协会'],
            [
                'description' => '计算机协会是一个致力于推广计算机技术、培养编程人才的学术科技类社团。我们定期举办编程讲座、技术沙龙、编程竞赛等活动，为热爱技术的同学提供交流和成长的平台。',
                'category' => '学术科技',
                'logo' => null,
                'contact_email' => 'cs_club@university.edu',
                'contact_phone' => '13800000001',
                'member_count' => 120,
            ]
        );

        $club2 = Club::firstOrCreate(
            ['name' => '电子科技协会'],
            [
                'description' => '电子科技协会专注于嵌入式开发、机器人制作、电子设计等领域。协会拥有完善的电子实验室，配备示波器、信号发生器等专业设备，欢迎对电子制作感兴趣的同学加入！',
                'category' => '学术科技',
                'logo' => null,
                'contact_email' => 'ee_club@university.edu',
                'contact_phone' => '13800000002',
                'member_count' => 85,
            ]
        );

        $club3 = Club::firstOrCreate(
            ['name' => '创业协会'],
            [
                'description' => '创业协会是一个培养创业意识、提升创业能力的实践类社团。我们与多家企业合作，定期举办创业讲座、商业计划大赛、企业参访等活动，助力每一位有创业梦想的同学！',
                'category' => '社会实践',
                'logo' => null,
                'contact_email' => 'startup_club@university.edu',
                'contact_phone' => '13800000003',
                'member_count' => 95,
            ]
        );

        $club1->leaders()->syncWithoutDetaching([$leader1->id => ['role' => 'president']]);
        $club2->leaders()->syncWithoutDetaching([$leader2->id => ['role' => 'president']]);
        $club3->leaders()->syncWithoutDetaching([$leader3->id => ['role' => 'president']]);

        $this->command->info('创建招新岗位...');

        $post1 = RecruitmentPost::firstOrCreate(
            ['club_id' => $club1->id, 'title' => '后端开发工程师'],
            [
                'description' => '负责协会项目的后端开发工作，使用 PHP/Laravel、Node.js 等技术栈。',
                'requirements' => '1. 熟悉至少一门后端编程语言（PHP/Java/Python/Node.js等）
2. 了解 MySQL、Redis 等数据库
3. 有良好的代码规范意识
4. 有实际项目经验者优先',
                'benefits' => '1. 参与真实项目开发，积累实战经验
2. 获得学长学姐的技术指导
3. 优先推荐实习机会
4. 丰厚的社团福利',
                'deadline' => Carbon::now()->addDays(30),
                'status' => 'published',
                'position_type' => '技术岗',
            ]
        );

        $post2 = RecruitmentPost::firstOrCreate(
            ['club_id' => $club1->id, 'title' => '前端开发工程师'],
            [
                'description' => '负责协会项目的前端开发工作，使用 Vue3、React 等现代前端框架。',
                'requirements' => '1. 熟悉 HTML、CSS、JavaScript
2. 了解至少一个前端框架（Vue/React 等）
3. 有良好的审美和用户体验意识
4. 有 UI/UX 经验者优先',
                'benefits' => '1. 参与真实项目开发，积累实战经验
2. 获得学长学姐的技术指导
3. 优先推荐实习机会',
                'deadline' => Carbon::now()->addDays(30),
                'status' => 'published',
                'position_type' => '技术岗',
            ]
        );

        $post3 = RecruitmentPost::firstOrCreate(
            ['club_id' => $club2->id, 'title' => '嵌入式开发工程师'],
            [
                'description' => '负责机器人、智能小车等项目的嵌入式开发工作。',
                'requirements' => '1. 了解 C/C++ 编程语言
2. 有 51 单片机或 STM32 开发经验
3. 了解基本的电路知识
4. 动手能力强，有电子制作经验者优先',
                'benefits' => '1. 免费使用协会实验室
2. 参与各类电子设计竞赛
3. 获得专业老师指导',
                'deadline' => Carbon::now()->addDays(30),
                'status' => 'published',
                'position_type' => '技术岗',
            ]
        );

        $post4 = RecruitmentPost::firstOrCreate(
            ['club_id' => $club3->id, 'title' => '项目策划专员'],
            [
                'description' => '负责协会活动策划、商业计划书撰写等工作。',
                'requirements' => '1. 有良好的文字功底和沟通能力
2. 思维活跃，有创新意识
3. 了解基本的商业知识
4. 有社团或学生组织工作经验者优先',
                'benefits' => '1. 参与创业项目策划
2. 接触优秀创业者
3. 提升商业思维能力',
                'deadline' => Carbon::now()->addDays(30),
                'status' => 'published',
                'position_type' => '策划岗',
            ]
        );

        $this->command->info('创建面试时段...');

        $baseDate = Carbon::now()->addDays(7);

        $slots = [
            [
                'recruitment_post_id' => $post1->id,
                'club_id' => $club1->id,
                'start_time' => $baseDate->copy()->setHour(14)->setMinute(0),
                'end_time' => $baseDate->copy()->setHour(15)->setMinute(0),
                'location' => '计算机楼 A301',
                'capacity' => 5,
                'status' => 'open',
            ],
            [
                'recruitment_post_id' => $post1->id,
                'club_id' => $club1->id,
                'start_time' => $baseDate->copy()->setHour(16)->setMinute(0),
                'end_time' => $baseDate->copy()->setHour(17)->setMinute(0),
                'location' => '计算机楼 A301',
                'capacity' => 5,
                'status' => 'open',
            ],
            [
                'recruitment_post_id' => $post2->id,
                'club_id' => $club1->id,
                'start_time' => $baseDate->copy()->setHour(15)->setMinute(0),
                'end_time' => $baseDate->copy()->setHour(16)->setMinute(0),
                'location' => '计算机楼 A302',
                'capacity' => 5,
                'status' => 'open',
            ],
            [
                'recruitment_post_id' => $post3->id,
                'club_id' => $club2->id,
                'start_time' => $baseDate->copy()->addDay()->setHour(14)->setMinute(0),
                'end_time' => $baseDate->copy()->addDay()->setHour(16)->setMinute(0),
                'location' => '电子楼 B201',
                'capacity' => 8,
                'status' => 'open',
            ],
            [
                'recruitment_post_id' => $post4->id,
                'club_id' => $club3->id,
                'start_time' => $baseDate->copy()->addDays(2)->setHour(19)->setMinute(0),
                'end_time' => $baseDate->copy()->addDays(2)->setHour(21)->setMinute(0),
                'location' => '创业楼 C101',
                'capacity' => 10,
                'status' => 'open',
            ],
        ];

        foreach ($slots as $slotData) {
            InterviewSlot::firstOrCreate(
                [
                    'recruitment_post_id' => $slotData['recruitment_post_id'],
                    'start_time' => $slotData['start_time'],
                ],
                $slotData
            );
        }

        $this->command->info('创建示例报名记录...');

        $app1 = Application::firstOrCreate(
            ['user_id' => $students[0]->id, 'recruitment_post_id' => $post1->id],
            [
                'motivation' => '我非常喜欢后端开发，平时也自己做过一些小项目。希望能加入计算机协会，向学长学姐学习，参与真实项目开发。',
                'experience' => '做过个人博客系统（Laravel）、在线Todo应用（Vue+Node.js）',
                'skills' => 'PHP, Laravel, MySQL, JavaScript, Vue',
                'status' => 'pending',
            ]
        );

        $app2 = Application::firstOrCreate(
            ['user_id' => $students[1]->id, 'recruitment_post_id' => $post2->id],
            [
                'motivation' => '对前端开发很感兴趣，喜欢研究各种酷炫的交互效果。希望能在协会锻炼自己的技术能力。',
                'experience' => '做过个人作品集网站、在线商城前端页面',
                'skills' => 'HTML, CSS, JavaScript, Vue3, Element Plus',
                'status' => 'reviewing',
            ]
        );

        $app3 = Application::firstOrCreate(
            ['user_id' => $students[2]->id, 'recruitment_post_id' => $post3->id],
            [
                'motivation' => '从小就喜欢拆拆装装，对电子制作非常感兴趣。希望能加入电子科技协会，学习更多知识。',
                'experience' => '高中时参加过机器人竞赛，做过简单的循迹小车',
                'skills' => 'C语言, 51单片机, 基础电路设计',
                'status' => 'pending',
            ]
        );

        $this->command->info('数据库测试数据填充完成！');
        $this->command->info('=== 测试账号 ===');
        $this->command->info('社长账号：leader1/2/3@example.com / password123');
        $this->command->info('学生账号：student1/2/3/4@example.com / password123');
        $this->command->info('==============');
    }
}
