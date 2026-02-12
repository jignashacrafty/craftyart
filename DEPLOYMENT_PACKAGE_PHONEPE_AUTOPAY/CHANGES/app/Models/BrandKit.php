<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\BrandKit
 *
 * @property int $id
 * @property string $user_id
 * @property string|null $brand_logo
 * @property string|null $profile_pic
 * @property string|null $name
 * @property string|null $business_name
 * @property string|null $business_designation
 * @property string|null $business_tagline
 * @property string|null $primary_number
 * @property string|null $secondary_number
 * @property string|null $email
 * @property string|null $website
 * @property string|null $address
 * @property string|null $facebook
 * @property string|null $facebook_url
 * @property string|null $linkedin
 * @property string|null $linkedin_url
 * @property string|null $instagram
 * @property string|null $instagram_url
 * @property string|null $twitter
 * @property string|null $twitter_url
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|BrandKit newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BrandKit newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BrandKit query()
 * @method static \Illuminate\Database\Eloquent\Builder|BrandKit whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BrandKit whereBrandLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BrandKit whereBusinessDesignation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BrandKit whereBusinessName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BrandKit whereBusinessTagline($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BrandKit whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BrandKit whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BrandKit whereFacebook($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BrandKit whereFacebookUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BrandKit whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BrandKit whereInstagram($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BrandKit whereInstagramUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BrandKit whereLinkedin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BrandKit whereLinkedinUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BrandKit whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BrandKit wherePrimaryNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BrandKit whereProfilePic($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BrandKit whereSecondaryNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BrandKit whereTwitter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BrandKit whereTwitterUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BrandKit whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BrandKit whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BrandKit whereWebsite($value)
 * @mixin \Eloquent
 */
class BrandKit extends Model
{
	protected $table = 'brand_kit';
	protected $connection = 'brand_kit_mysql';
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'brand_logo',
        'profile_pic',
        'name',
        'business_name',
        'business_designation',
        'business_tagline',
        'primary_number',
        'secondary_number',
        'email',
        'website',
        'role',
        'usage',
        'address',
        'facebook',
        'facebook_url',
        'linkedin',
        'linkedin_url',
        'instagram',
        'instagram_url',
        'twitter',
        'twitter_url',
    ];
}
