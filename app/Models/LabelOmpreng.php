<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LabelOmpreng extends Model
{
    protected $table = 'label_ompreng';

    protected $fillable = [
        'profil_mbg_id',
        'created_by',
        'template_default',
        'lokasi_sppg',
        'peringatan',
        'himbauan',
        'jumlah_item',
    ];

    public function profilMbg(): BelongsTo
    {
        return $this->belongsTo(ProfilMbg::class, 'profil_mbg_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(LabelOmprengItem::class, 'label_ompreng_id')->orderBy('urutan');
    }
}
