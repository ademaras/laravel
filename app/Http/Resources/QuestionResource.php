<?php

namespace App\Http\Resources;

use App\Models\SurveyParticipant;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionResource extends JsonResource
{
    public function toArray($request)
    {
        $totalAnswersForQuestion = $this->answers ? $this->answers->count() : 0; // Veri eksikliği durumunda 0 olarak ayarla
        $totalParticipants = SurveyParticipant::where('survey_id', $this->survey->id)->count();
        $answerPercentageForQuestion = $totalParticipants > 0 ? ($totalAnswersForQuestion / $totalParticipants) * 100 : 0;
    
        return [
            'id' => $this->id,
            'list_no' => $this->list_no,
            'title' => $this->title,
            'total_answers' => $totalAnswersForQuestion,
            'answer_percentage' => $answerPercentageForQuestion,
            'choices' => ChoiceResource::collection($this->choices),
        ];
    }
}
