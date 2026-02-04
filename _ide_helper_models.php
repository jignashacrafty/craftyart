<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * App\Models\MailSendDetail
 *
 * @property int $id
 * @property string $log_id
 * @property int $user_id
 * @property string $email
 * @property string $status
 * @property string $error_message
 * @property int $send_type
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|MailSendDetail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MailSendDetail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MailSendDetail query()
 * @method static \Illuminate\Database\Eloquent\Builder|MailSendDetail whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MailSendDetail whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MailSendDetail whereErrorMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MailSendDetail whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MailSendDetail whereLogId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MailSendDetail whereSendType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MailSendDetail whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MailSendDetail whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MailSendDetail whereUserId($value)
 */
	class MailSendDetail extends \Eloquent {}
}

