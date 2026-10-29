<?php

namespace App\Policies;

use App\Models\LearningSession;
use App\Models\User;

class LearningSessionPolicy
{
    private function resolveTeacherId(User $user): string
    {
        return $user->teacher?->teacher_id ?? (string) $user->id;
    }

    public function viewNavigation(User $user, LearningSession $session): bool
    {
        return $this->resolveTeacherId($user) === $session->teacher_id 
            && in_array(strtolower($session->learning_method), ['home_visit', 'onsite', 'offline']);
    }

    public function clockIn(User $user, LearningSession $session): bool
    {
        return $this->resolveTeacherId($user) === $session->teacher_id;
    }

    public function clockOut(User $user, LearningSession $session): bool
    {
        return $this->resolveTeacherId($user) === $session->teacher_id;
    }
}