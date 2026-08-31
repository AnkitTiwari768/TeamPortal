<?php

declare(strict_types=1);

namespace App\Domain\Audit;

trait Auditable
{
    public static function bootAuditable()
    {
        static::created(function ($model) {
            $model->audit('created', null, $model->getAttributes());
        });

        static::updating(function ($model) {
            $model->audit(
                'updated',
                $model->getOriginal(),
                $model->getDirty()
            );
        });

        static::deleted(function ($model) {
            $model->audit('deleted', $model->getOriginal(), null);
        });
    }

    protected function audit($event, $old, $new)
    {
        Audit::create([
            'auditable_type' => get_class($this),
            'auditable_id'   => $this->getKey(),
            'event'          => $event,
            'old_values'     => $old,
            'new_values'     => $new,
            'user_id'        => auth()->id(),
            'ip_address'     => request()?->ip(),
            'user_agent'     => request()?->userAgent(),
            'url'            => request()?->fullUrl(),
        ]);
    }
}
