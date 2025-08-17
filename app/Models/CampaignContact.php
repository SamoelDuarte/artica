<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CampaignContact extends Model
{
    use HasFactory;

    protected $table = 'campaign_contact';

    protected $fillable = [
        'campaign_id',
        'contact_list_id',
        'send',
    ];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function contactList()
    {
        return $this->belongsTo(ContactList::class);
    }

    /**
     * Add a phone number to the contact list if it doesn't already exist.
     */
    public static function addIfNotExists($phone, $contactId)
    {
        $formattedPhone = self::formatPhone($phone);

        // Check if the phone number already exists for the contact
        $existingContact = self::where('phone', $formattedPhone)
            ->where('contact_id', $contactId)
            ->first();

        if (!$existingContact) {
            return self::create([
                'phone' => $formattedPhone,
                'contact_id' => $contactId,
            ]);
        }

        return null;
    }

    /**
     * Format the phone number.
     */
    public static function formatPhone($phone)
    {
        // Implement your phone formatting logic here
        return preg_replace('/\D/', '', $phone); // Example: Remove non-numeric characters
    }
}
