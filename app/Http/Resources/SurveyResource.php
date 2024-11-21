<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SurveyResource extends JsonResource
{
    public function toArray($request)
    {
        $participantCount = $this->participants->count();
        $isParticipant = $this->participants->where('user_id', 1)->count() > 0;

        return [
            'id' => $this->id,
            'title' => $this->title,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'total_answered' => $participantCount,
            'order' => $this->order,
            'status' => $isParticipant,
            'questions' => QuestionResource::collection($this->questions),
        ];
    }
}
