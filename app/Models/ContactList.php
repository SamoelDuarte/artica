<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactList extends Model
{
    use HasFactory;

    protected $table = 'contact_list'; // Certifique-se de que o nome da tabela está correto
    protected $fillable = [
        'phone',
        'contact_id',
    ];
    /**
     * Get the contact that owns the contact list.
     */
    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    public function campaigns()
    {
        return $this->belongsToMany(Campaign::class, 'campaign_contact')
            ->withPivot('send')
            ->withTimestamps();
    }
}
