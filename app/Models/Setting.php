<?php
// app/Models/Setting.php - Para o sistema de configurações
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key', 'value', 'type', 'group', 'description', 'is_public'
    ];

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
        ];
    }

    const TYPES = [
        'string' => 'Texto',
        'number' => 'Número',
        'boolean' => 'Sim/Não',
        'json' => 'JSON',
        'file' => 'Arquivo'
    ];

    const GROUPS = [
        'general' => 'Geral',
        'delivery' => 'Entrega',
        'payment' => 'Pagamento',
        'notification' => 'Notificação',
        'appearance' => 'Aparência'
    ];

    public function getCastedValue()
    {
        return match($this->type) {
            'boolean' => (bool) $this->value,
            'number' => is_numeric($this->value) ? (float) $this->value : $this->value,
            'json' => json_decode($this->value, true),
            default => $this->value
        };
    }

    public static function get($key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->getCastedValue() : $default;
    }

    public static function set($key, $value, $type = 'string', $group = 'general')
    {
        $processedValue = match($type) {
            'boolean' => $value ? '1' : '0',
            'json' => json_encode($value),
            default => (string) $value
        };

        return static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $processedValue,
                'type' => $type,
                'group' => $group
            ]
        );
    }

    public function getTypeLabel()
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function getGroupLabel()
    {
        return self::GROUPS[$this->group] ?? $this->group;
    }
}
