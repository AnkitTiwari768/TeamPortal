<?php

declare(strict_types=1);

namespace App\Domain\Upload;

use App\Traits\Respond;

final readonly class UploadController
{
    use Respond;

    public function __invoke(UploadRequest $request, UploadAction $action)
    {
        return $this->created(
            message: __('Uploaded successful'),
            data: $action->execute($request)
        );
    }
}
