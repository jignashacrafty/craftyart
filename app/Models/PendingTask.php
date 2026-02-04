<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * App\Models\PendingTask
 *
 * @property int $id
 * @property string $string_id
 * @property string $emp_id
 * @property int $status
 * @property string|null $reason
 * @property string|null $changes_title
 * @property string|null $changes_desc
 * @property string|null $preview_route
 * @property int|null $approve_by
 * @property string $data
 * @property string $table_name
 * @property string|null $id_name
 * @property int $page_type
 * @property string $action
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property int|null $record_id
 * @property string|null $change_log
 * @property-read mixed $group_leader_name
 * @property-read mixed $requestor_name
 * @property-read mixed $status_type
 * @property-read \App\Models\User|null $user
 * @property-read \App\Models\User|null $user2
 * @method static \Illuminate\Database\Eloquent\Builder|PendingTask newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PendingTask newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PendingTask query()
 * @method static \Illuminate\Database\Eloquent\Builder|PendingTask whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PendingTask whereApproveBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PendingTask whereChangeLog($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PendingTask whereChangesDesc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PendingTask whereChangesTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PendingTask whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PendingTask whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PendingTask whereEmpId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PendingTask whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PendingTask whereIdName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PendingTask wherePageType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PendingTask wherePreviewRoute($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PendingTask whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PendingTask whereRecordId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PendingTask whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PendingTask whereStringId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PendingTask whereTableName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PendingTask whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PendingTask extends Model
{
  protected $connection = 'mysql';
  use HasFactory;

  protected $fillable = [
    'string_id',
    'table_name',
    'record_id',
    'action',
    'changes_title',
    'changes_desc',
    'preview_route',
    'data',
    'emp_id',
    'change_log',
    'id_name',
    'page_type'
  ];

  public function getStatusTypeAttribute()
  {
    return $this->status == 0
      ? "Pending"
      : ($this->status == 1
        ? "Approve"
        : "Rejected");
  }

  public function user()
  {
    return $this->belongsTo(User::class, 'emp_id', 'id');
  }

  public function user2()
  {
    return $this->belongsTo(User::class, 'group_leader_id', 'id');
  }

  /**
   * Get the requestor's name from the users table.
   */
  public function getRequestorNameAttribute()
  {
    return $this->user ? $this->user->name : 'Unknown';
  }

  public function getGroupLeaderNameAttribute()
  {
    if ($this->group_leader_id == auth()->user()->id) {
      return auth()->user()->name;
    } else {
      return $this->user2 ? $this->user2->name : 'Unknown';
    }
  }

}