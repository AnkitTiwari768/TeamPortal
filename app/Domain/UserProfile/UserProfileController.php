<?php

declare(strict_types=1);

namespace App\Domain\UserProfile;

use Illuminate\Http\Request;

class UserProfileController
{
    public function __construct(
        private UserProfileService $userProfileService
    ) {}

    public function index()
    {
        $profileDetails = $this->userProfileService->getProfileViewDetails(authId());

        return view($profileDetails['view'])->with($profileDetails['data']);
    }

    public function histories()
    {
        $title = 'Profile Histories';
        $revisions = $this->userProfileService->getProfileRevisions(authId());

        return view('profile.history', compact('revisions', 'title'));
    }

    public function show(string $id)
    {
        $title = 'Profile Snapshot';
        $isReviewed = true;
        $networkProvider = $this->userProfileService->getProfileSnaphotByAuditId($id);
        $viewOnly = true;
        return view('snp.details', compact('title', 'networkProvider', 'isReviewed', 'viewOnly'));
    }
}
