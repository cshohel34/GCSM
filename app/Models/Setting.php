<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Setting extends Model {
    protected $guarded = ['id'];
    public static function get(string $key, $default = null) {
        return optional(static::where('key', $key)->first())->value ?? $default;
    }
    public static function put(string $key, $value): void {
        static::updateOrInsert(['key' => $key], ['value' => $value, 'updated_at' => now(), 'created_at' => now()]);
    }
}
