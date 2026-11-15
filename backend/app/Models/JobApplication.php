<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobApplication extends Model
{
    protected $table = 'applications';
    protected $guarded = ['id'];
    protected $hidden = ['cv_path'];

    public function job()
    {
        return $this->belongsTo(JobPost::class, 'job_post_id')->withTrashed();
    }

    public function candidate()
    {
        return $this->belongsTo(User::class, 'candidate_id');
    }

    public function events()
    {
        return $this->hasMany(ApplicationEvent::class, 'application_id')->orderBy('id');
    }
}
