<?php

return [

    'backup' => [

        // Shown in notifications and used to namespace the backup files.
        'name' => env('APP_NAME', 'nyumba'),

        'source' => [
            'files' => [
                // Intentionally empty — uploaded files (logos etc.) already
                // live independently on R2 and don't need to be re-backed-up
                // here. Only the database is dumped by this package.
                'include' => [],
                'exclude' => [],
                'follow_links' => false,
                'ignore_unreadable_directories' => false,
                'relative_path' => null,
            ],

            'databases' => [
                'mysql',
            ],
        ],

        'database_dump_compressor' => \Spatie\DbDumper\Compressors\GzipCompressor::class,

        'destination' => [
            // Prefix inside the bucket, e.g. nyumba/2026-07-13-02-00-00.zip
            'filename_prefix' => '',

            // The R2 disk we already configured in config/filesystems.php.
            // This is a genuinely independent copy of your data, separate
            // from Railway's own infrastructure and volume backups.
            'disks' => [
                'r2',
            ],
        ],

        'temporary_directory' => storage_path('app/backup-temp'),
    ],

    'notifications' => [
        'notifications' => [
            \Spatie\Backup\Notifications\Notifications\BackupHasFailedNotification::class => ['mail'],
            \Spatie\Backup\Notifications\Notifications\UnhealthyBackupWasFoundNotification::class => ['mail'],
            \Spatie\Backup\Notifications\Notifications\CleanupHasFailedNotification::class => ['mail'],
            \Spatie\Backup\Notifications\Notifications\BackupWasSuccessfulNotification::class => [],
            \Spatie\Backup\Notifications\Notifications\HealthyBackupWasFoundNotification::class => [],
            \Spatie\Backup\Notifications\Notifications\CleanupWasSuccessfulNotification::class => [],
        ],

        'notifiable' => \Spatie\Backup\Notifications\Notifiable::class,

        'mail' => [
            'to' => env('BACKUP_NOTIFICATION_EMAIL', 'admin@nyumbapc.co.ke'),
            'from' => [
                'address' => env('MAIL_FROM_ADDRESS', 'noreply@nyumbapc.co.ke'),
                'name' => env('APP_NAME', 'Nyumba'),
            ],
        ],

        'slack' => [
            'webhook_url' => '',
            'channel' => null,
            'username' => null,
            'icon' => null,
        ],
    ],

    'monitor_backups' => [
        [
            'name' => env('APP_NAME', 'nyumba'),
            'disks' => ['r2'],
            'health_checks' => [
                \Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumAgeInDays::class => 2,
                \Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumStorageInMegabytes::class => 5000,
            ],
        ],
    ],

    'cleanup' => [
        'strategy' => \Spatie\Backup\Tasks\Cleanup\Strategies\DefaultStrategy::class,

        'default_strategy' => [
            // Keep everything from the last 14 days...
            'keep_all_backups_for_days' => 14,

            // ...then thin out to one per day for the next ~2 months...
            'keep_daily_backups_for_days' => 60,

            // ...then one per week for the rest of the year...
            'keep_weekly_backups_for_weeks' => 52,

            // ...then one per month indefinitely.
            'keep_monthly_backups_for_months' => 999,
            'keep_yearly_backups_for_years' => 999,

            // Delete the oldest backup if this size is exceeded.
            'delete_oldest_backups_when_using_more_megabytes_than' => 9000,
        ],
    ],

];