<?php

return [
    'logto' => [
        'endpoint' => env('LOGTO_ENDPOINT'),
        'app_id' => env('LOGTO_APP_ID'),
        'app_secret' => env('LOGTO_APP_SECRET'),
    ],
    
    'oss' => [
        'access_key_id' => env('OSS_ACCESS_KEY_ID'),
        'access_key_secret' => env('OSS_ACCESS_KEY_SECRET'),
        'bucket' => env('OSS_BUCKET'),
        'endpoint' => env('OSS_ENDPOINT'),
        'cdn_domain' => env('OSS_CDN_DOMAIN'),
    ],
    
    'content_moderation' => [
        'access_key_id' => env('ALIYUN_ACCESS_KEY_ID'),
        'access_key_secret' => env('ALIYUN_ACCESS_KEY_SECRET'),
        'region' => env('ALIYUN_REGION', 'cn-shanghai'),
    ],
    
    'quiz' => [
        'questions_per_quiz' => 10,
        'passing_score' => 80,
        'total_questions' => 50,
    ],
    
    'limits' => [
        'guest_daily_thumbnails' => 100,
        'guest_daily_details' => 20,
        'user_hourly_details' => 600,
        'max_file_size' => 100 * 1024 * 1024, // 100MB
        'batch_upload_limit' => 50,
        'daily_original_downloads' => 20,
        'max_follows' => 5000,
        'max_tags_per_work' => 10,
    ],
    
    'shutter_time' => [
        'daily_login_reward' => 1, // 1/100 seconds
        'member_multiplier' => 2,
        'yuan_to_shutter_rate' => 100, // 1 yuan = 100 shutter time
    ],
    
    'membership' => [
        'monthly_price' => 1800, // cents
        'yearly_price' => 15800, // cents
        'student_discount' => 0.5,
    ],
];