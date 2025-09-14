<?php

namespace App\Rules;

use App\Enums\TaskStatus;
use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class DueDateNotPast implements ValidationRule
{
    protected $status;

    public function __construct($status)
    {
        $this->status = $status;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Validate if the status is Pending/In Progress
        if (in_array($this->status, [TaskStatus::PENDING->value, TaskStatus::IN_PROGRESS->value])) {

            $dueDate = Carbon::parse($value);

            // Check the date is not a past date
            if ($dueDate->isPast()) {
                $fail('The due date is not allowed a past day');
            }
        }
    }
}
