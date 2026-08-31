<?php

declare(strict_types=1);

namespace App\Domain\ForgotUdyam;

use App\Traits\Respond;
use Illuminate\Http\Request;

class ForgotUdyamController
{
    use Respond;

    public function __invoke(Request $request, ForgotUdyamAction $action)
    {
        dd($request->all());
        $validated = $request->validate([
            'email' => 'required|exists:users,email',
            'mobile_number' => 'required|exists:users,mobile',
        ]);

        return $this->success(
            data: $action->execute($validated['email'],$validated['mobile_number']),
            message: 'Your udyam number has been successfully sent to your registered email address.'
        );
    }
}
