<?php

namespace App\Enums;

enum FeatureAddon: string
{
    case AiScheduling = 'ai_scheduling';
    case AdvancedAnalytics = 'advanced_analytics';
    case ApiAccess = 'api_access';
    case PrioritySupport = 'priority_support';
    case TimeAttendance = 'time_attendance';

    public function label(): string
    {
        return match ($this) {
            self::AiScheduling => 'AI Scheduling',
            self::AdvancedAnalytics => 'Advanced Analytics',
            self::ApiAccess => 'API Access',
            self::PrioritySupport => 'Priority Support',
            self::TimeAttendance => 'Time & Attendance',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::AiScheduling => 'Intelligent schedule optimization and auto-fill suggestions',
            self::AdvancedAnalytics => 'Detailed reports, forecasting, and insights dashboard',
            self::ApiAccess => 'Full REST API access for integrations and automation',
            self::PrioritySupport => '24/7 priority support with dedicated account manager',
            self::TimeAttendance => 'Clock in/out, time tracking, and attendance management',
        };
    }

    public function monthlyPrice(): int
    {
        return match ($this) {
            self::AiScheduling => 19,
            self::AdvancedAnalytics => 14,
            self::ApiAccess => 29,
            self::PrioritySupport => 49,
            self::TimeAttendance => 12,
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::AiScheduling => 'sparkles',
            self::AdvancedAnalytics => 'chart-bar',
            self::ApiAccess => 'code-bracket',
            self::PrioritySupport => 'lifebuoy',
            self::TimeAttendance => 'clock',
        };
    }
}
