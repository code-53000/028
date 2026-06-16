<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Club;
use App\Models\RecruitmentPost;
use App\Models\InterviewSlot;
use App\Models\Application;
use App\Models\InterviewResult;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('开始创建测试数据...');

        $clubLeader1 = User::create([
            'name' => '张三',
            'email' => 'leader1@example.com',
            'password' => Hash::make('password123'),
            'phone' => '13800138001',
            'role' => 'club_leader',
            'student_id' => '2021001001',
            'major' => '计算机科学与技术',
            'grade' => '大三',
            'bio' => '计算机协会会长，热爱开源技术',
        ]);

        $clubLeader2 = User::create([
            'name' => '李四',
            'email' => 'leader2@example.com',
            'password' => Hash::make('password123'),
            'phone' => '13800138002',
            'role' => 'club_leader',
            'student_id' => '2021002001',
            'major' => '电子工程',
            'grade' => '大三',
            'bio' => '电子科技协会副会长',
        ]);

        $clubLeader3 = User::create([
            'name' => '王五',
            'email' => 'leader3@example.com',
            'password' => Hash::make('password123'),
            'phone' => '13800138003',
            'role' => 'club_leader',
            'student_id' => '2021003001',
            'major' => '市场营销',
            'grade' => '大四',
            'bio' => '创业协会创始人',
        ]);

        $student1 = User::create([
            'name' => '小明',
            'email' => 'student1@example.com',
            'password' => Hash::make('password123'),
            'phone' => '13900139001',
            'role' => 'student',
            'student_id' => '2023001001',
            'major' => '软件工程',
            'grade' => '大一',
            'bio' => '热爱编程，希望加入技术社团学习',
        ]);

        $student2 = User::create([
            'name' => '小红',
            'email' => 'student2@example.com',
            'password' => Hash::make('password123'),
            'phone' => '13900139002',
            'role' => 'student',
            'student_id' => '2023001002',
            'major' => '计算机科学与技术',
            'grade' => '大一',
            'bio' => '喜欢前端开发和UI设计',
        ]);

        $student3 = User::create([
            'name' => '小刚',
            'email' => 'student3@example.com',
            'password' => Hash::make('password123'),
            'phone' => '13900139003',
            'role' => 'student',
            'student_id' => '2022002001',
            'major' => '电子信息工程',
            'grade' => '大二',
            'bio' => '对嵌入式开发感兴趣',
        ]);

        $student4 = User::create([
            'name' => '小丽',
            'email' => 'student4@example.com',
            'password' => Hash::make('password123'),
            'phone' => '13900139004',
            'role' => 'student',
            'student_id' => '2022003001',
            'major' => '工商管理',
            'grade' => '大二',
            'bio' => '对创业和商业感兴趣',
        ]);

        $club1 = Club::create([
            'name' => '计算机协会',
            'logo' => null,
            'description' => '计算机协会是一个面向全校计算机爱好者的学生社团，致力于推广计算机技术，培养同学们的编程能力和创新思维。我们定期举办技术分享会、编程比赛、项目实践等活动。',
            'category' => '学术科技',
            'member_count' => 120,
            'is_active' => true,
        ]);
        $club1->members()->attach($clubLeader1->id, ['role' => 'leader']);

        $club2 = Club::create([
            'name' => '电子科技协会',
            'logo' => null,
            'description' => '电子科技协会专注于电子设计、嵌入式系统和物联网技术。协会拥有完整的实验室设备，成员多次在全国电子设计大赛中获奖。',
            'category' => '学术科技',
            'member_count' => 85,
            'is_active' => true,
        ]);
        $club2->members()->attach($clubLeader2->id, ['role' => 'leader']);

        $club3 = Club::create([
            'name' => '创业协会',
            'logo' => null,
            'description' => '创业协会为有志于创业的同学提供交流平台和资源支持。我们与多家创业孵化器合作，定期举办创业沙龙、路演活动和创业培训课程。',
            'category' => '实践创业',
            'member_count' => 65,
            'is_active' => true,
        ]);
        $club3->members()->attach($clubLeader3->id, ['role' => 'leader']);

        $post1 = RecruitmentPost::create([
            'club_id' => $club1->id,
            'title' => '技术部干事',
            'description' => '参与协会技术项目开发，负责技术分享会筹备，学习和实践前沿技术。',
            'requirements' => "1. 对编程有浓厚兴趣\n2. 至少掌握一门编程语言\n3. 有较强的学习能力和团队协作精神",
            'benefits' => "1. 系统的技术培训\n2. 真实项目实践经验\n3. 学长学姐一对一指导\n4. 丰富的社团活动",
            'quota' => 15,
            'status' => 'open',
            'deadline' => Carbon::now()->addDays(30),
        ]);

        $post2 = RecruitmentPost::create([
            'club_id' => $club1->id,
            'title' => '宣传部干事',
            'description' => '负责协会活动宣传、公众号运营、海报设计等工作。',
            'requirements' => "1. 有一定的文字功底或设计能力\n2. 熟练使用 Office 软件\n3. 有公众号运营经验者优先",
            'benefits' => "1. 新媒体运营技能培训\n2. 设计软件使用教学\n3. 锻炼沟通协调能力",
            'quota' => 8,
            'status' => 'open',
            'deadline' => Carbon::now()->addDays(30),
        ]);

        $post3 = RecruitmentPost::create([
            'club_id' => $club2->id,
            'title' => '硬件开发部成员',
            'description' => '参与电子设计项目，学习PCB设计、单片机开发等技能。',
            'requirements' => "1. 对电子技术有兴趣\n2. 了解基础电路知识\n3. 动手能力强",
            'benefits' => "1. 专业实验室使用权限\n2. 学长学姐技术指导\n3. 参加电子设计大赛机会",
            'quota' => 10,
            'status' => 'open',
            'deadline' => Carbon::now()->addDays(25),
        ]);

        $post4 = RecruitmentPost::create([
            'club_id' => $club3->id,
            'title' => '项目孵化部成员',
            'description' => '参与创业项目筛选、孵化跟踪、资源对接等工作。',
            'requirements' => "1. 对创业有热情\n2. 有良好的沟通能力\n3. 有创业想法或项目经验者优先",
            'benefits' => "1. 与创业者面对面交流\n2. 创业知识系统培训\n3. 优质项目实习机会",
            'quota' => 12,
            'status' => 'open',
            'deadline' => Carbon::now()->addDays(20),
        ]);

        $baseDate = Carbon::now()->addDays(7);

        $slot1 = InterviewSlot::create([
            'recruitment_post_id' => $post1->id,
            'club_id' => $club1->id,
            'start_time' => $baseDate->copy()->hour(14)->minute(0),
            'end_time' => $baseDate->copy()->hour(14)->minute(30),
            'location' => '计算机学院楼 302 室',
            'capacity' => 5,
            'booked_count' => 0,
            'status' => 'open',
            'notes' => '请提前10分钟到达',
        ]);

        $slot2 = InterviewSlot::create([
            'recruitment_post_id' => $post1->id,
            'club_id' => $club1->id,
            'start_time' => $baseDate->copy()->hour(15)->minute(0),
            'end_time' => $baseDate->copy()->hour(15)->minute(30),
            'location' => '计算机学院楼 302 室',
            'capacity' => 5,
            'booked_count' => 0,
            'status' => 'open',
            'notes' => '请提前10分钟到达',
        ]);

        $slot3 = InterviewSlot::create([
            'recruitment_post_id' => $post1->id,
            'club_id' => $club1->id,
            'start_time' => $baseDate->copy()->addDay()->hour(14)->minute(0),
            'end_time' => $baseDate->copy()->addDay()->hour(14)->minute(30),
            'location' => '计算机学院楼 302 室',
            'capacity' => 5,
            'booked_count' => 0,
            'status' => 'open',
            'notes' => '请提前10分钟到达',
        ]);

        $slot4 = InterviewSlot::create([
            'recruitment_post_id' => $post3->id,
            'club_id' => $club2->id,
            'start_time' => $baseDate->copy()->hour(14)->minute(0),
            'end_time' => $baseDate->copy()->hour(15)->minute(0),
            'location' => '电子实验楼 201 室',
            'capacity' => 8,
            'booked_count' => 0,
            'status' => 'open',
            'notes' => '',
        ]);

        $slot5 = InterviewSlot::create([
            'recruitment_post_id' => $post4->id,
            'club_id' => $club3->id,
            'start_time' => $baseDate->copy()->addDays(2)->hour(19)->minute(0),
            'end_time' => $baseDate->copy()->addDays(2)->hour(20)->minute(0),
            'location' => '创业孵化基地 会议室A',
            'capacity' => 10,
            'booked_count' => 0,
            'status' => 'open',
            'notes' => '请准备1分钟自我介绍',
        ]);

        $app1 = Application::create([
            'user_id' => $student1->id,
            'recruitment_post_id' => $post1->id,
            'motivation' => '我非常热爱编程，希望能在计算机协会学到更多技术知识，认识志同道合的朋友。',
            'experience' => '自学过Python和C++，做过一些小项目',
            'skills' => 'Python, C++, 基础算法',
            'status' => 'pending',
        ]);

        $app2 = Application::create([
            'user_id' => $student2->id,
            'recruitment_post_id' => $post1->id,
            'motivation' => '对前端开发很感兴趣，希望加入技术部提升自己的能力。',
            'experience' => '做过个人博客网站，熟悉HTML/CSS/JavaScript',
            'skills' => 'HTML, CSS, JavaScript, Vue',
            'status' => 'reviewing',
        ]);

        $app3 = Application::create([
            'user_id' => $student3->id,
            'recruitment_post_id' => $post3->id,
            'motivation' => '一直对硬件开发很感兴趣，希望能加入电子科技协会系统学习。',
            'experience' => '参加过高中机器人竞赛，有一定基础',
            'skills' => 'C语言, 51单片机',
            'status' => 'pending',
        ]);

        $this->command->info('测试数据创建完成！');
        $this->command->info('');
        $this->command->info('测试账号：');
        $this->command->info('  社团负责人（计算机协会）: leader1@example.com / password123');
        $this->command->info('  社团负责人（电子科技协会）: leader2@example.com / password123');
        $this->command->info('  社团负责人（创业协会）: leader3@example.com / password123');
        $this->command->info('  学生账号1: student1@example.com / password123');
        $this->command->info('  学生账号2: student2@example.com / password123');
        $this->command->info('  学生账号3: student3@example.com / password123');
        $this->command->info('  学生账号4: student4@example.com / password123');
    }
}
