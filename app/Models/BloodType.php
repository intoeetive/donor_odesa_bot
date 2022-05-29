<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BloodType extends Model
{
    protected $fillable = [
        'type',
        'rh'
    ];

    public const BLOOD_TYPES = [
        10 => 'I+ (O+)',
        20 => 'II+ (A+)',
        30 => 'III+ (B+)',
        40 => 'IV+ (AB+)',
        11 => 'I- (O-)',
        21 => 'II- (A-)',
        31 => 'III- (B-)',
        41 => 'IV- (AB-)'
    ];

   public static function getBloodTypeID($bloodType)
   {
      return array_search($bloodType, self::BLOOD_TYPES);
   }

   public function getBloodTypeAttribute()
   {
      return self::BLOOD_TYPES[ $this->attributes['blood_type_id'] ];
   }

   public function setBloodTypeAttribute($value)
   {
      $bloodTypeID = self::getBloodTypeID($value);
      if ($bloodTypeID) {
         $this->attributes['blood_type_id'] = $bloodTypeID;
      }
   }
}
