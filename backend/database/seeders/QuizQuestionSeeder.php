<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\QuizQuestion;

class QuizQuestionSeeder extends Seeder
{
    public function run()
    {
        $questions = [
            [
                'question' => '什么是光圈？',
                'options' => [
                    '控制快门速度的装置',
                    '控制进光量大小的装置',
                    '控制感光度的装置',
                    '控制焦距的装置'
                ],
                'correct_option' => 1,
                'explanation' => '光圈是控制进光量大小的装置，影响景深和曝光。'
            ],
            [
                'question' => 'ISO 数值越高，照片会出现什么效果？',
                'options' => [
                    '噪点越少',
                    '噪点越多',
                    '颜色越鲜艳',
                    '对比度越高'
                ],
                'correct_option' => 1,
                'explanation' => 'ISO 数值越高，感光度越高，但同时噪点也会增加。'
            ],
            [
                'question' => '下列哪个光圈值能产生最浅的景深？',
                'options' => [
                    'f/1.4',
                    'f/2.8',
                    'f/5.6',
                    'f/11'
                ],
                'correct_option' => 0,
                'explanation' => 'f/1.4 是最大光圈，能产生最浅的景深效果。'
            ],
            [
                'question' => '快门速度 1/60s 比 1/125s：',
                'options' => [
                    '更快',
                    '更慢',
                    '一样快',
                    '无法比较'
                ],
                'correct_option' => 1,
                'explanation' => '1/60s 表示曝光时间更长，所以比 1/125s 更慢。'
            ],
            [
                'question' => '什么是白平衡？',
                'options' => [
                    '调整图像亮度',
                    '调整图像对比度',
                    '调整色温，还原真实色彩',
                    '调整图像锐度'
                ],
                'correct_option' => 2,
                'explanation' => '白平衡用于调整色温，确保在不同光源下都能还原真实的色彩。'
            ],
            [
                'question' => 'RAW 格式相比 JPEG 格式的优势是：',
                'options' => [
                    '文件更小',
                    '包含更多图像信息，后期处理空间更大',
                    '相机处理速度更快',
                    '可以直接在网上分享'
                ],
                'correct_option' => 1,
                'explanation' => 'RAW 格式保留了更多的图像信息，为后期处理提供更大的调整空间。'
            ],
            [
                'question' => '什么是黄金分割构图法？',
                'options' => [
                    '将画面分成三等份',
                    '将主体放在画面中央',
                    '按照 1:1.618 的比例分割画面',
                    '使用对角线构图'
                ],
                'correct_option' => 2,
                'explanation' => '黄金分割构图法基于 1:1.618 的黄金比例，创造出视觉上最舒适的构图。'
            ],
            [
                'question' => '在弱光环境下拍摄，以下哪种方法最不推荐？',
                'options' => [
                    '使用三脚架',
                    '提高 ISO',
                    '使用闪光灯',
                    '过度曝光后期降亮度'
                ],
                'correct_option' => 3,
                'explanation' => '过度曝光会丢失高光细节，即使后期降亮度也无法恢复。'
            ],
            [
                'question' => '景深的大小主要受什么因素影响？',
                'options' => [
                    '只受光圈影响',
                    '只受焦距影响',
                    '受光圈、焦距、拍摄距离共同影响',
                    '只受拍摄距离影响'
                ],
                'correct_option' => 2,
                'explanation' => '景深受光圈大小、焦距长短、拍摄距离远近三个因素共同影响。'
            ],
            [
                'question' => '什么是曝光补偿？',
                'options' => [
                    '修改 ISO 数值',
                    '在自动模式下微调曝光亮度',
                    '更换镜头',
                    '调整白平衡'
                ],
                'correct_option' => 1,
                'explanation' => '曝光补偿允许在自动曝光模式下微调照片的明暗程度。'
            ],
            [
                'question' => '长焦镜头的优势是：',
                'options' => [
                    '视角更广',
                    '可以拍摄更远的物体',
                    '体积更小',
                    '价格更便宜'
                ],
                'correct_option' => 1,
                'explanation' => '长焦镜头可以将远处的被摄体拉近，适合拍摄距离较远的物体。'
            ],
            [
                'question' => '什么是过曝？',
                'options' => [
                    '照片太暗',
                    '照片太亮，丢失高光细节',
                    '照片模糊',
                    '照片色彩不准'
                ],
                'correct_option' => 1,
                'explanation' => '过曝是指照片过度曝光，导致高光部分变成纯白，丢失细节。'
            ]
        ];

        foreach ($questions as $questionData) {
            QuizQuestion::create($questionData);
        }
    }
}