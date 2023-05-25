<?php


namespace App\Models;


/**
 * Class CardDeliveryAddress
 * @package App\Models
 * @property string $id
 * @property string $wallester_account_detail_id
 * @property string $first_name
 * @property string $last_name
 * @property string $address_1
 * @property string $address_2
 * @property string $postal_code
 * @property string $city
 * @property string $country_code
 * @property $created_at
 * @property $updated_at
 *
 * @property WallesterAccountDetail $wallesterAccountDetail
 */
class CardDeliveryAddress extends BaseModel
{
    protected $fillable = [
        'wallester_account_detail_id', 'first_name', 'last_name', 'address_1', 'address_2', 'postal_code', 'city', 'country_code'
    ];

    public function wallesterAccountDetail()
    {
        return $this->belongsTo(WallesterAccountDetail::class, 'wallester_account_detail_id', 'id');
    }

}
