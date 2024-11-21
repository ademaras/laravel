<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SurveyResource;
use App\Models\Catalog;
use App\Models\CorporateLinkInfo;
use App\Models\Survey;
use App\Models\SurveyParticipant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SurveyController extends Controller
{
    public function index()
    {
        $surveys = Survey::with('questions.choices')->get();

        $data = SurveyResource::collection($surveys);

        return $this->mobile(true, 'anketler', $data);
    }

    public function store(Request $request, Survey $survey)
    {
        $user = Auth::user();

        $answers = $request->input('answers');

        $userLinkOrders = CorporateLinkInfo::where('user_id', $user->id)->get();
        foreach ($userLinkOrders as $userLinkOrder) {
            $userLinkOrder->order += 1;
            $userLinkOrder->save();
        }

        $getCatalogs = Catalog::where('user_id', $user->id)->get();
        foreach ($getCatalogs as $getCatalog) {
            $getCatalog->order += 1;
            $getCatalog->save();
        }

        foreach ($answers as $answer) {
            $survey->answers()->create([
                'question_id' => $answer['question_id'],
                'choice_id' => $answer['choice_id'],
                'user_id' => Auth::user()->id,
            ]);
        }

        $existingParticipant = SurveyParticipant::where([
            'survey_id' => $survey->id,
            'user_id' => Auth::user()->id,
        ])->first();

        if (!$existingParticipant) {
            SurveyParticipant::create([
                'survey_id' => $survey->id,
                'user_id' => Auth::user()->id,
            ]);
        }

        return response()->json(['message' => 'Anket cevapları başarıyla kaydedildi.']);
    }

    public function reorder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->mobile(false, 'Doğrulama Hatası', $validator->errors());
        }

        $ids = $request->ids;
        $surveyModels = [];

        foreach ($ids as $key => $id) {
            $survey = Survey::find($id);
            if ($survey) {
                $survey->order = $key;
                $surveyModels[] = $survey;
            }
        }

        foreach ($surveyModels as $survey) {
            $survey->save();
        }

        return $this->mobile(true, 'Anket yeniden sıralandı', $ids);
    }
}
