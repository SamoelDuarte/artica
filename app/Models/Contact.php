<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    /**
     * Get the contact lists for the contact.
     */
    public function contactLists()
    {
        return $this->hasMany(ContactList::class, 'contact_id');
    }
}
