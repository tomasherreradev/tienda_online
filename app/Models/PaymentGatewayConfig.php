<?php

namespace App\Models;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class PaymentGatewayConfig extends Model
{
    protected $fillable = ['gateway_id', 'key_name', 'key_value', 'is_encrypted', 'environment'];

    public function gateway()
    {
        return $this->belongsTo(PaymentGateway::class, 'gateway_id');
    }

    public function getKeyValueAttribute($value)
    {
        return $this->resolveStoredValue($value);
    }

    public function setKeyValueAttribute($value)
    {
        $this->attributes['key_value'] = $this->is_encrypted && $value
            ? Crypt::encryptString($value)
            : $value;
    }

    /**
     * Valor listo para usar en APIs (descifra si corresponde).
     */
    public function resolvedValue(): ?string
    {
        return $this->resolveStoredValue($this->attributes['key_value'] ?? null);
    }

    protected function resolveStoredValue(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return $value;
        }

        if (! $this->is_encrypted) {
            return $value;
        }

        try {
            return Crypt::decryptString($value);
        } catch (DecryptException) {
            return $value;
        }
    }

    /**
     * @return array<string, string|null>
     */
    public static function mapForGateway(int $gatewayId, ?string $environment = null): array
    {
        $query = static::query()->where('gateway_id', $gatewayId);

        if ($environment !== null) {
            $query->where('environment', $environment);
        }

        $mapped = [];

        foreach ($query->get() as $config) {
            $mapped[$config->key_name] = $config->resolvedValue();
        }

        return $mapped;
    }
}
