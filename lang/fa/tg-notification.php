<?php

return [
    'request_send_message'    => "پیام خود را ارسال کنید:",
    'end_send_message'        => "پیام همگانی در صف قرار گرفت",
    'request_forward_message' => "پیام خود را جهت فوروارد همگانی ارسال کنید:",
    'end_forward_message'     => "فوروارد همگانی در صف قرار گرفت",

    'log' => [
        'template' => <<<TEXT
            :title


            :all
            :success
            :failed

            :progress
            TEXT,

        'title' => [
            'created'     => "♻️ ارسال پیام همگانی آغاز شد",
            'progressing' => "♻️ در حال ارسال پیام همگانی...",
            'completed'   => "✅ پیام همگانی تکمیل شد",
            'failed'      => "⚠️ پیام همگانی با خطا مواجه شد",
        ],

        'counter' => [
            'all'     => "⚪ :number از :all",
            'success' => "🟢 :number موفق",
            'failed'  => "🔴 :number ناموفق",
        ],

        'error_template' => <<<TEXT
            ⚠️ خطایی در سیستم پیامرسانی (همگانی) رخ داد


            :message
            TEXT,
    ],
];
