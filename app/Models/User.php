<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'nama',
        'email',
        'password',
        'role',
        'id_unit',
        'avatar',
        'provider_name',
        'provider_id',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'two_factor_recovery_codes' => 'array',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    /**
     * Accessor and Mutator for 'name' to maintain compatibility with Breeze while preserving 'nama'.
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->attributes['nama'] ?? '',
            set: fn ($value) => ['nama' => $value]
        );
    }

    public function unit()
    {
        return $this->belongsTo(UnitUsaha::class, 'id_unit', 'id_unit');
    }

    public function transactions()
    {
        return $this->hasMany(Transaksi::class, 'id_user', 'id');
    }

    public function isBendaharaUnit(): bool
    {
        return $this->role === 'bendahara_unit';
    }

    public function isBendaharaUmum(): bool
    {
        return $this->role === 'bendahara_umum';
    }

    public function isDirektur(): bool
    {
        return $this->role === 'direktur';
    }

    /**
     * Check if user has Two-Factor Authentication enabled and confirmed.
     */
    public function hasTwoFactorEnabled(): bool
    {
        return !empty($this->two_factor_secret) && !is_null($this->two_factor_confirmed_at);
    }

    /**
     * Get QR Code SVG for Two-Factor Authentication.
     */
    public function twoFactorQrCodeSvg(): string
    {
        if (empty($this->two_factor_secret)) {
            return '';
        }

        $google2fa = new Google2FA();
        $companyName = config('app.name', 'BUMDes Kuala Alam');
        $qrCodeUrl = $google2fa->getQRCodeUrl($companyName, $this->email, decrypt($this->two_factor_secret));

        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd()
        );

        $writer = new Writer($renderer);
        return $writer->writeString($qrCodeUrl);
    }

    /**
     * Generate new set of recovery codes.
     */
    public function generateTwoFactorRecoveryCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $codes[] = Str::random(10) . '-' . Str::random(10);
        }

        $this->forceFill([
            'two_factor_recovery_codes' => $codes,
        ])->save();

        return $codes;
    }
}
