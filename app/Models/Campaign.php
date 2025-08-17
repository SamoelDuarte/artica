<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'titulo',
        'texto',
        'contact_id',
        'imagem_id',
        'status',
    ];

    /**
     * Get the contact associated with the campaign.
     */
    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function devices()
    {
        return $this->belongsToMany(Device::class, 'campaign_device');
    }


    /**
     * Get the image associated with the campaign.
     */
    public function imagem()
    {
        return $this->belongsTo(ImagemEmMassa::class);
    }

    /**
     * Get the contact lists associated with the campaign.
     */
    public function contactList()
    {
        return $this->belongsToMany(ContactList::class, 'campaign_contact')
            ->withPivot('send')
            ->withTimestamps();
    }
}
