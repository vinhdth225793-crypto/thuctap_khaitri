<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class SystemSetting extends Model
{
    protected $table = 'system_settings';

    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Get setting value by key
     */
    public static function get($key, $default = null)
    {
        $storage = self::storage();

        if ($storage === null) {
            return $default;
        }

        $setting = DB::table($storage['table'])
            ->where($storage['key'], $key)
            ->first();

        return $setting ? $setting->{$storage['value']} : $default;
    }

    /**
     * Set setting value by key
     */
    public static function set($key, $value)
    {
        $storage = self::storage();

        if ($storage === null) {
            return null;
        }

        $table = $storage['table'];
        $now = now();
        $data = [
            $storage['value'] => $value,
        ];

        if (Schema::hasColumn($table, 'updated_at')) {
            $data['updated_at'] = $now;
        }

        if ($storage['group'] !== null && Schema::hasColumn($table, $storage['group'])) {
            $data[$storage['group']] = self::groupFor($key);
        }

        $query = DB::table($table)->where($storage['key'], $key);

        if ($query->exists()) {
            $query->update($data);
        } else {
            $data[$storage['key']] = $key;

            if (Schema::hasColumn($table, 'created_at')) {
                $data['created_at'] = $now;
            }

            DB::table($table)->insert($data);
        }

        return DB::table($table)
            ->where($storage['key'], $key)
            ->first();
    }

    /**
     * Resolve the settings table used by the current database.
     *
     * Some local databases still use the legacy cai_dat_he_thong table.
     *
     * @return array{table: string, key: string, value: string, group: string|null}|null
     */
    private static function storage(): ?array
    {
        try {
            if (Schema::hasTable('system_settings')) {
                return [
                    'table' => 'system_settings',
                    'key' => 'key',
                    'value' => 'value',
                    'group' => null,
                ];
            }

            if (Schema::hasTable('cai_dat_he_thong')) {
                return [
                    'table' => 'cai_dat_he_thong',
                    'key' => 'khoa',
                    'value' => 'gia_tri',
                    'group' => 'nhom',
                ];
            }
        } catch (Throwable $e) {
            return null;
        }

        return null;
    }

    private static function groupFor(string $key): string
    {
        return match ($key) {
            'site_name', 'site_logo', 'hotline', 'email', 'address', 'general_notification' => 'contact',
            'facebook', 'zalo' => 'social',
            default => 'general',
        };
    }
}
