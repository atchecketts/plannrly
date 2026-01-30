<?php

namespace App\Http\Controllers;

use App\Http\Requests\TenantSettings\UpdateTenantSettingsRequest;
use App\Models\TenantSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TenantSettingsController extends Controller
{
    public function edit(): View
    {
        $user = auth()->user();

        if (! $user->isSuperAdmin() && ! $user->isAdmin()) {
            abort(403);
        }

        $settings = TenantSettings::firstOrCreate(
            ['tenant_id' => $user->tenant_id],
            $this->getDefaultSettings()
        );

        $timezones = $this->getCommonTimezones();
        $currencies = $this->getCommonCurrencies();
        $carryoverModes = $this->getCarryoverModes();
        $weekDays = $this->getWeekDays();
        $dateFormats = $this->getDateFormats();
        $timeFormats = $this->getTimeFormats();

        return view('settings.edit', compact(
            'settings',
            'timezones',
            'currencies',
            'carryoverModes',
            'weekDays',
            'dateFormats',
            'timeFormats'
        ));
    }

    public function update(UpdateTenantSettingsRequest $request): RedirectResponse
    {
        $user = auth()->user();

        $settings = TenantSettings::firstOrCreate(
            ['tenant_id' => $user->tenant_id],
            $this->getDefaultSettings()
        );

        $settings->update($request->validated());

        return redirect()
            ->route('settings.edit')
            ->with('success', 'Settings updated successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function getDefaultSettings(): array
    {
        return [
            'enable_clock_in_out' => false,
            'enable_shift_acknowledgement' => false,
            'day_starts_at' => '06:00',
            'day_ends_at' => '22:00',
            'week_starts_on' => 1,
            'timezone' => 'Europe/London',
            'date_format' => 'd/m/Y',
            'time_format' => 'H:i',
            'missed_grace_minutes' => 15,
            'notify_on_publish' => true,
            'leave_carryover_mode' => 'none',
            'default_currency' => 'GBP',
            'primary_color' => '#6366f1',
            'clock_in_grace_minutes' => 15,
            'require_gps_clock_in' => false,
            'auto_clock_out_enabled' => false,
            'auto_clock_out_time' => null,
            'overtime_threshold_minutes' => 480,
            'require_manager_approval' => false,
        ];
    }

    /**
     * @return array<string, string>
     */
    private function getCommonTimezones(): array
    {
        return [
            'Europe/London' => 'London (GMT/BST)',
            'Europe/Paris' => 'Paris (CET/CEST)',
            'Europe/Berlin' => 'Berlin (CET/CEST)',
            'Europe/Amsterdam' => 'Amsterdam (CET/CEST)',
            'America/New_York' => 'New York (EST/EDT)',
            'America/Chicago' => 'Chicago (CST/CDT)',
            'America/Denver' => 'Denver (MST/MDT)',
            'America/Los_Angeles' => 'Los Angeles (PST/PDT)',
            'Asia/Tokyo' => 'Tokyo (JST)',
            'Asia/Singapore' => 'Singapore (SGT)',
            'Asia/Dubai' => 'Dubai (GST)',
            'Australia/Sydney' => 'Sydney (AEST/AEDT)',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function getCommonCurrencies(): array
    {
        return [
            'GBP' => 'British Pound (GBP)',
            'USD' => 'US Dollar (USD)',
            'EUR' => 'Euro (EUR)',
            'CAD' => 'Canadian Dollar (CAD)',
            'AUD' => 'Australian Dollar (AUD)',
            'JPY' => 'Japanese Yen (JPY)',
            'CHF' => 'Swiss Franc (CHF)',
            'SGD' => 'Singapore Dollar (SGD)',
            'AED' => 'UAE Dirham (AED)',
            'INR' => 'Indian Rupee (INR)',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function getCarryoverModes(): array
    {
        return [
            'none' => 'No Carryover - Unused days are lost',
            'partial' => 'Partial Carryover - Up to 5 days carried over',
            'full' => 'Full Carryover - All unused days carried over',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function getWeekDays(): array
    {
        return [
            0 => 'Sunday',
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function getDateFormats(): array
    {
        return [
            'Y-m-d' => 'YYYY-MM-DD (2024-12-25)',
            'd/m/Y' => 'DD/MM/YYYY (25/12/2024)',
            'm/d/Y' => 'MM/DD/YYYY (12/25/2024)',
            'd-m-Y' => 'DD-MM-YYYY (25-12-2024)',
            'd.m.Y' => 'DD.MM.YYYY (25.12.2024)',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function getTimeFormats(): array
    {
        return [
            'H:i' => '24-hour (14:30)',
            'h:i A' => '12-hour uppercase (02:30 PM)',
            'g:i a' => '12-hour lowercase (2:30 pm)',
        ];
    }
}
